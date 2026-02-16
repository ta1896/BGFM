<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemLogService;
use App\Services\DataSanityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function __construct(
        private readonly SystemLogService $logService,
        private readonly DataSanityService $sanityService,
        private readonly \App\Services\MatchSimulationService $simulationService
    ) {
    }

    /**
     * Display the system monitoring dashboard.
     */
    public function index(Request $request): View
    {
        $health = $this->getSystemHealth();
        $logStats = $this->logService->getLogStats();
        $recentLogs = $this->logService->getRecentLogs(15);
        $diagnostics = $this->sanityService->runDiagnostics($request->has('refresh'));
        $dataTimestamp = \Illuminate\Support\Facades\Cache::get('admin_monitoring_diagnostics_timestamp', now()->format('H:i:s'));

        return view('admin.monitoring.index', compact('health', 'logStats', 'recentLogs', 'diagnostics', 'dataTimestamp'));
    }

    public function analysis(Request $request): View
    {
        $matchId = $request->input('match_id');
        $match = null;
        $matchDiagnostics = [];
        if ($matchId) {
            $match = \App\Models\GameMatch::with(['homeClub', 'awayClub', 'events', 'liveActions'])->find($matchId);
            if ($match) {
                $matchDiagnostics = $this->sanityService->diagnoseMatch($match);
            }
        }

        return view('admin.monitoring.analysis', compact('match', 'matchDiagnostics'));
    }

    public function lab(): View
    {
        $clubs = \App\Models\Club::orderBy('name')->get();
        return view('admin.monitoring.lab', compact('clubs'));
    }

    public function internals(): View
    {
        $stats = [
            'cache_driver' => config('cache.default'),
            'db_connection' => config('database.default'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'storage_size' => $this->getDirectorySize(storage_path('app')),
            'log_size' => \Illuminate\Support\Facades\File::exists(storage_path('logs/laravel.log')) ? \Illuminate\Support\Facades\File::size(storage_path('logs/laravel.log')) : 0,
        ];

        return view('admin.monitoring.internals', compact('stats'));
    }

    public function scheduler(): View
    {
        $runs = \DB::table('simulation_scheduler_runs')
            ->orderBy('started_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.monitoring.scheduler', compact('runs'));
    }

    public function clearCache(): RedirectResponse
    {
        \Illuminate\Support\Facades\Cache::flush();
        return back()->with('success', 'System-Cache wurde vollständig geleert.');
    }

    private function getDirectorySize($path)
    {
        $size = 0;
        if (!\Illuminate\Support\Facades\File::isDirectory($path))
            return 0;
        foreach (\Illuminate\Support\Facades\File::allFiles($path) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    /**
     * Get detailed logs.
     */
    public function logs(Request $request): View
    {
        $logs = $this->logService->getRecentLogs(200);
        return view('admin.monitoring.logs', compact('logs'));
    }

    /**
     * Clear all system logs.
     */
    public function clearLogs(): RedirectResponse
    {
        $this->logService->clearLogs();
        return redirect()->route('admin.monitoring.index')->with('success', 'Logs cleared successfully.');
    }

    /**
     * Perform automated repair.
     */
    public function repair(Request $request): RedirectResponse
    {
        $type = $request->input('type');
        $id = $request->input('id');

        try {
            switch ($type) {
                case 'club_lineup':
                    $this->repairClubLineup((int) $id);
                    break;
                case 'match_status_fix':
                    $m = \App\Models\GameMatch::find($id);
                    if ($m) {
                        $m->update(['status' => 'played']);
                    }
                    break;
                case 'match_score_sync':
                    $m = \App\Models\GameMatch::find($id);
                    if ($m) {
                        $homeGoals = $m->events()->where('event_type', 'goal')->where('club_id', $m->home_club_id)->count();
                        $awayGoals = $m->events()->where('event_type', 'goal')->where('club_id', $m->away_club_id)->count();
                        $m->update(['home_score' => $homeGoals, 'away_score' => $awayGoals]);
                    }
                    break;
                case 'match_re_simulate':
                    return redirect()->route('matches.show', $id)->with('info', 'Bitte Re-Simulation manuell starten.');
                default:
                    return back()->with('error', 'Unbekannter Reparatur-Typ.');
            }

            return back()->with('success', 'Reparatur erfolgreich durchgeführt.');
        } catch (\Exception $e) {
            return back()->with('error', 'Fehler bei der Reparatur: ' . $e->getMessage());
        }
    }

    public function runLabSimulation(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'home_club_id' => ['required', 'integer', 'exists:clubs,id'],
            'away_club_id' => ['required', 'integer', 'exists:clubs,id', 'different:home_club_id'],
            'stadium_id' => ['nullable', 'exists:stadia,id'],
        ]);

        try {
            // Create a virtual match for simulation
            $match = new \App\Models\GameMatch();
            $match->home_club_id = $validated['home_club_id'];
            $match->away_club_id = $validated['away_club_id'];
            $match->stadium_id = $validated['stadium_id'] ?? null;
            $match->status = 'scheduled'; // Needed for some logic

            // Load clubs for the service
            $match->setRelation('homeClub', \App\Models\Club::find($validated['home_club_id']));
            $match->setRelation('awayClub', \App\Models\Club::find($validated['away_club_id']));

            $options = [];

            $result = $this->simulationService->calculateSimulation($match, $options);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Simulation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix club missing active lineup.
     */
    private function repairClubLineup(int $clubId): void
    {
        $club = \App\Models\Club::findOrFail($clubId);
        if ($club->lineups()->where('is_active', true)->exists()) {
            return;
        }

        $lineup = $club->lineups()->create([
            'name' => 'Standard (Auto-Fix)',
            'formation' => '4-4-2',
            'is_active' => true,
            'is_template' => true,
        ]);

        $topPlayers = $club->players()->orderByDesc('overall')->limit(11)->get();
        $positions = ['TW', 'LV', 'IV', 'IV', 'RV', 'LM', 'ZM', 'ZM', 'RM', 'MS', 'MS'];

        $pivotData = [];
        foreach ($topPlayers as $index => $p) {
            $pivotData[$p->id] = [
                'pitch_position' => $positions[$index] ?? 'ZM',
                'sort_order' => $index,
            ];
        }

        $lineup->players()->sync($pivotData);
    }

    /**
     * Get system health metrics.
     */
    private function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabaseConnection(),
            'storage' => $this->getStorageInfo(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
        ];
    }

    /**
     * Check if database is responding.
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get storage disk information.
     */
    private function getStorageInfo(): array
    {
        $path = storage_path();
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;

        return [
            'total' => $this->formatBytes($total),
            'free' => $this->formatBytes($free),
            'used' => $this->formatBytes($used),
            'percentage' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
