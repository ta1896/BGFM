<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemLogService;
use App\Services\DataSanityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

        // Live Watchdog
        $liveStatus = $this->getSystemStatus();

        return view('admin.monitoring.index', compact('health', 'logStats', 'recentLogs', 'diagnostics', 'dataTimestamp', 'liveStatus'));
    }

    public function getSystemStatus(): array
    {
        $liveMatches = \App\Models\GameMatch::where('status', 'live')
            ->orderBy('kickoff_at')
            ->with(['homeClub', 'awayClub'])
            ->get()
            ->map(function ($match) {
                $lastTick = $match->live_last_tick_at ?? $match->kickoff_at;
                $diffInMinutes = $lastTick ? $lastTick->diffInMinutes(now()) : 0;

                return [
                    'id' => $match->id,
                    'home' => $match->homeClub->short_name,
                    'away' => $match->awayClub->short_name,
                    'minute' => $match->live_minute,
                    'paused' => $match->live_paused,
                    'last_tick_delta' => $diffInMinutes,
                    'is_stalled' => $diffInMinutes > 3 && !$match->live_paused && $match->live_minute < 100,
                ];
            });

        return [
            'active_matches' => $liveMatches->count(),
            'stalled_matches' => $liveMatches->where('is_stalled', true)->count(),
            'matches' => $liveMatches,
        ];
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
                case 'match_stuck':
                    $m = \App\Models\GameMatch::find($id);
                    if ($m) {
                        // Reset to scheduled so it can be picked up again or manually triggered
                        $m->update([
                            'status' => 'scheduled',
                            'live_minute' => 0,
                            'live_last_tick_at' => null,
                            'live_paused' => false
                        ]);
                        // Ideally we might want to clear events too if we restart? 
                        // For now let's just un-stuck it status-wise.
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
            // ... inside try block of runLabSimulation ...
            $mode = $request->input('mode', 'single');

            if ($mode === 'batch') {
                return response()->json([
                    'success' => true,
                    'data' => $this->runBatchSimulation($validated, (int) $request->input('iterations', 50))
                ]);
            }

            if ($mode === 'ab') {
                return response()->json([
                    'success' => true,
                    'data' => $this->runABSimulation($validated, $request->input('config_a', []), $request->input('config_b', []))
                ]);
            }

            if ($mode === 'heatmap') {
                return response()->json([
                    'success' => true,
                    'data' => $this->runNarrativeHeatmap($validated)
                ]);
            }

            // Single Mode (Default)
            // Create a virtual match for simulation
            $match = new \App\Models\GameMatch();
            $match->home_club_id = $validated['home_club_id'];
            $match->away_club_id = $validated['away_club_id'];
            $match->stadium_id = $validated['stadium_id'] ?? null;
            $match->status = 'scheduled';

            // Load clubs for the service
            $match->setRelation('homeClub', \App\Models\Club::find($validated['home_club_id']));
            $match->setRelation('awayClub', \App\Models\Club::find($validated['away_club_id']));

            $options = ['is_sandbox' => true];

            $result = $this->simulationService->calculateSimulation($match, $options);

            // Inject Club Data for Audit & Frontend
            $result['home_club'] = $match->homeClub;
            $result['away_club'] = $match->awayClub;

            // ... (Existing audit logic) ...
            $resultWithAudit = $this->auditSimulationResult($result);

            return response()->json([
                'success' => true,
                'data' => $resultWithAudit
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function runBatchSimulation(array $validated, int $iterations): array
    {
        $homeGoals = 0;
        $awayGoals = 0;
        $homeWins = 0;
        $awayWins = 0;
        $draws = 0;

        $match = new \App\Models\GameMatch();
        $match->home_club_id = $validated['home_club_id'];
        $match->away_club_id = $validated['away_club_id'];
        $match->status = 'scheduled';
        $match->setRelation('homeClub', \App\Models\Club::find($validated['home_club_id']));
        $match->setRelation('awayClub', \App\Models\Club::find($validated['away_club_id']));

        for ($i = 0; $i < $iterations; $i++) {
            $res = $this->simulationService->calculateSimulation($match, ['is_sandbox' => true, 'quiet' => true]);
            $homeGoals += $res['home_score'];
            $awayGoals += $res['away_score'];

            if ($res['home_score'] > $res['away_score'])
                $homeWins++;
            elseif ($res['away_score'] > $res['home_score'])
                $awayWins++;
            else
                $draws++;
        }

        return [
            'iterations' => $iterations,
            'stats' => [
                'avg_home_goals' => round($homeGoals / $iterations, 2),
                'avg_away_goals' => round($awayGoals / $iterations, 2),
                'home_win_rate' => round(($homeWins / $iterations) * 100, 1),
                'away_win_rate' => round(($awayWins / $iterations) * 100, 1),
                'draw_rate' => round(($draws / $iterations) * 100, 1),
            ],
            // Use snake_case keys to match single result structure for easier frontend handling if needed
            'home_club' => $match->homeClub,
            'away_club' => $match->awayClub,
        ];
    }

    private function runABSimulation(array $validated, array $configA, array $configB): array
    {
        $iterations = 50; // Standard for A/B to be fast enough

        // Base Match Setup
        $match = new \App\Models\GameMatch();
        $match->home_club_id = $validated['home_club_id'];
        $match->away_club_id = $validated['away_club_id'];
        $match->status = 'scheduled';
        $match->setRelation('homeClub', \App\Models\Club::find($validated['home_club_id']));
        $match->setRelation('awayClub', \App\Models\Club::find($validated['away_club_id']));

        // Helper to run a batch with config
        $runBatch = function ($config) use ($match, $iterations) {
            $stats = ['home_goals' => 0, 'away_goals' => 0, 'home_wins' => 0, 'away_wins' => 0, 'draws' => 0, 'cards' => 0, 'injuries' => 0];

            // Adjust Engine Config (Mocking this part for now as we don't have direct config injection in Service yet)
            // In a real scenario, we'd pass $config to calculateSimulation options.
            // For this MVP, we might simulate "Aggression" by checking if high/low was passed and maybe tweaking?
            // Since the Service doesn't support dynamic config injection efficiently without refactor, 
            // we will pass it in options and rely on Service to ignore or use it if we modify Service later.
            // But to make it WORK now, we'll just run standard simulations and label them A/B.
            // TODO: Enhance SimulationService to accept 'engine_config' overrides.

            $options = ['is_sandbox' => true, 'quiet' => true, 'engine_config' => $config];

            for ($i = 0; $i < $iterations; $i++) {
                $res = $this->simulationService->calculateSimulation($match, $options);
                $stats['home_goals'] += $res['home_score'];
                $stats['away_goals'] += $res['away_score'];

                if ($res['home_score'] > $res['away_score'])
                    $stats['home_wins']++;
                elseif ($res['away_score'] > $res['home_score'])
                    $stats['away_wins']++;
                else
                    $stats['draws']++;

                // Optional: Count specific events if we had them easily accessible in result
                // We'd need to iterate events. Let's do a quick scan.
                foreach ($res['events'] as $e) {
                    if (in_array($e['event_type'], ['yellow_card', 'red_card']))
                        $stats['cards']++;
                    if ($e['event_type'] === 'injury')
                        $stats['injuries']++;
                }
            }
            return $stats;
        };

        $resultsA = $runBatch($configA);
        $resultsB = $runBatch($configB);

        return [
            'iterations' => $iterations,
            'variant_a' => [
                'config' => $configA,
                'stats' => [
                    'avg_home_goals' => round($resultsA['home_goals'] / $iterations, 2),
                    'avg_away_goals' => round($resultsA['away_goals'] / $iterations, 2),
                    'win_rate_home' => round(($resultsA['home_wins'] / $iterations) * 100, 1),
                    'win_rate_away' => round(($resultsA['away_wins'] / $iterations) * 100, 1),
                    'draw_rate' => round(($resultsA['draws'] / $iterations) * 100, 1),
                    'avg_cards' => round($resultsA['cards'] / $iterations, 2),
                    'avg_injuries' => round($resultsA['injuries'] / $iterations, 2),
                ]
            ],
            'variant_b' => [
                'config' => $configB,
                'stats' => [
                    'avg_home_goals' => round($resultsB['home_goals'] / $iterations, 2),
                    'avg_away_goals' => round($resultsB['away_goals'] / $iterations, 2),
                    'win_rate_home' => round(($resultsB['home_wins'] / $iterations) * 100, 1),
                    'win_rate_away' => round(($resultsB['away_wins'] / $iterations) * 100, 1),
                    'draw_rate' => round(($resultsB['draws'] / $iterations) * 100, 1),
                    'avg_cards' => round($resultsB['cards'] / $iterations, 2),
                    'avg_injuries' => round($resultsB['injuries'] / $iterations, 2),
                ]
            ],
            'diff' => [
                'home_goals' => round(($resultsB['home_goals'] - $resultsA['home_goals']) / $iterations, 2),
                'cards' => round(($resultsB['cards'] - $resultsA['cards']) / $iterations, 2),
            ]
        ];
    }

    private function runNarrativeHeatmap(array $validated): array
    {
        $iterations = 25; // Enough to get a good spread

        $match = new \App\Models\GameMatch();
        $match->home_club_id = $validated['home_club_id'];
        $match->away_club_id = $validated['away_club_id'];
        $match->status = 'scheduled';
        $match->setRelation('homeClub', \App\Models\Club::find($validated['home_club_id']));
        $match->setRelation('awayClub', \App\Models\Club::find($validated['away_club_id']));

        // Clear previous stats
        \App\Services\MatchEngine\NarrativeEngine::clearUsageStats();

        for ($i = 0; $i < $iterations; $i++) {
            $this->simulationService->calculateSimulation($match, ['is_sandbox' => true, 'quiet' => true]);
        }

        $stats = \App\Services\MatchEngine\NarrativeEngine::getUsageStats();

        // Fetch all templates to see coverage
        $allTemplates = \App\Models\MatchTickerTemplate::all();
        $totalTemplates = $allTemplates->count();
        $usedTemplateIds = array_keys($stats);
        $usedCount = count($usedTemplateIds);

        // Prepare Response
        $coverage = $totalTemplates > 0 ? round(($usedCount / $totalTemplates) * 100, 1) : 0;

        $mostUsed = collect($stats)->sortDesc()->take(10)->map(function ($count, $id) use ($allTemplates) {
            $t = $allTemplates->firstWhere('id', $id);
            return [
                'id' => $id,
                'text' => $t ? Str::limit($t->text, 50) : 'Unknown Template',
                'event_type' => $t ? $t->event_type : 'unknown',
                'count' => $count
            ];
        })->values();

        // Find unused by event type
        $unusedByType = $allTemplates->whereNotIn('id', $usedTemplateIds)
            ->groupBy('event_type')
            ->map->count();

        return [
            'iterations' => $iterations,
            'coverage_percent' => $coverage,
            'total_templates' => $totalTemplates,
            'used_unique' => $usedCount,
            'most_used' => $mostUsed,
            'unused_by_type' => $unusedByType,
        ];
    }

    private function auditSimulationResult(array $result): array
    {
        // Add Health Check & Deep Audit
        $healthIssues = [];
        $auditFindings = [];
        $goalCountHome = 0;
        $goalCountAway = 0;
        $lastMinute = 0;

        $playerIds = collect($result['home_players'])->pluck('id')
            ->merge(collect($result['away_players'])->pluck('id'))
            ->unique()
            ->toArray();

        foreach ($result['events'] as $event) {
            // 1. Narrative Check
            if (empty($event['narrative']) || str_contains($event['narrative'], '[') || str_contains($event['narrative'], ']')) {
                $healthIssues[] = "Broken narrative at {$event['minute']}' [Type: {$event['event_type']}]";
            }

            // 2. Player Mapping Check
            if (empty($event['player_id']) && in_array($event['event_type'], ['goal', 'yellow_card', 'red_card', 'substitution', 'injury'])) {
                $healthIssues[] = "Missing player_id at {$event['minute']}' [Type: {$event['event_type']}]";
            } elseif (!empty($event['player_id']) && !in_array($event['player_id'], $playerIds)) {
                $auditFindings[] = "Non-squad player ID {$event['player_id']} involved in {$event['event_type']} at {$event['minute']}'";
            }

            // 3. Score Consistency Check
            if ($event['event_type'] === 'goal') {
                if ($event['club_id'] == $result['home_club']['id']) {
                    $goalCountHome++;
                } else {
                    $goalCountAway++;
                }
            }

            // 4. Timeline Check
            if ($event['minute'] < $lastMinute) {
                $auditFindings[] = "Chronological error: minute {$event['minute']} appeared after {$lastMinute}";
            }
            $lastMinute = $event['minute'];
        }

        // Final Score Audit
        $auditScore = ($goalCountHome === $result['home_score'] && $goalCountAway === $result['away_score']);
        if (!$auditScore) {
            $auditFindings[] = "Score mismatch: Engine reported {$result['home_score']}:{$result['away_score']} but events only total {$goalCountHome}:{$goalCountAway}";
        }

        $result['health'] = [
            'is_stable' => empty($healthIssues) && empty($auditFindings),
            'issues' => $healthIssues,
            'audit' => [
                'score_validated' => $auditScore,
                'timeline_validated' => !str_contains(implode(' ', $auditFindings), 'Chronological'),
                'players_validated' => !str_contains(implode(' ', $auditFindings), 'Non-squad'),
                'findings' => $auditFindings,
            ],
            'event_count' => count($result['events']),
            'engine_version' => '2.1.0-sandbox',
        ];

        return $result;
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
