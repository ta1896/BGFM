<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BulkSyncSofascoreJob;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\DataCenter\Models\ImportLog;

class ExternalSyncController extends Controller
{
    /**
     * Display the external sync dashboard.
     */
    public function index(): Response
    {
        $playerStats = [
            'total' => Player::count(),
            'with_sofascore' => Player::whereNotNull('sofascore_id')->count(),
            'with_transfermarkt' => Player::where(function($q) {
                $q->whereNotNull('transfermarkt_id')->orWhereNotNull('transfermarkt_url');
            })->count(),
        ];

        $missingPlayers = [
            'sofascore' => Player::with('club:id,name')->whereNull('sofascore_id')->get(['id', 'first_name', 'last_name', 'club_id']),
            'transfermarkt' => Player::with('club:id,name')->where(function($q) {
                $q->whereNull('transfermarkt_id')->whereNull('transfermarkt_url');
            })->get(['id', 'first_name', 'last_name', 'club_id']),
        ];

        $latestLogs = ImportLog::where('league_id', 'bulk_sync_sofascore')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/ExternalSync/Index', [
            'stats' => $playerStats,
            'missingPlayers' => $missingPlayers,
            'latestLogs' => $latestLogs,
        ]);
    }

    /**
     * Start the bulk synchronization.
     * 
     * @param Request $request - can contain 'mode': 'both' | 'sofascore' | 'transfermarkt'
     */
    public function startSync(Request $request): RedirectResponse
    {
        $mode = $request->input('mode', 'both');
        
        // Validate mode
        if (!in_array($mode, ['both', 'sofascore', 'transfermarkt'])) {
            $mode = 'both';
        }
        
        BulkSyncSofascoreJob::dispatch($mode);

        $labels = [
            'both' => 'Sofascore & Transfermarkt',
            'sofascore' => 'Nur Sofascore',
            'transfermarkt' => 'Nur Transfermarkt',
        ];

        return back()->with('status', "Daten-Synchronisation ({$labels[$mode]}) wurde im Hintergrund gestartet.");
    }

    /**
     * Clear the sync logs.
     */
    public function clearLogs(): RedirectResponse
    {
        ImportLog::where('league_id', 'bulk_sync_sofascore')->delete();

        return back()->with('status', 'Synchronisations-Journal wurde geleert.');
    }
}
