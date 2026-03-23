<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BulkSyncSofascoreJob;
use App\Jobs\SyncPlayerSofascoreJob;
use App\Models\Player;
use App\Services\SofascoreLinkService;
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
            'with_sofascore' => Player::whereNotNull('sofascore_id')
                ->where('sofascore_id', '!=', '')
                ->count(),
            'with_transfermarkt' => Player::where(function ($q) {
                $q->where(function ($inner) {
                    $inner->whereNotNull('transfermarkt_id')->where('transfermarkt_id', '!=', '');
                })->orWhere(function ($inner) {
                    $inner->whereNotNull('transfermarkt_url')->where('transfermarkt_url', '!=', '');
                });
            })->count(),
        ];

        $missingPlayers = [
            'sofascore' => Player::with('club:id,name')
                ->where(function ($q) {
                    $q->whereNull('sofascore_id')->orWhere('sofascore_id', '');
                })
                ->get(['id', 'first_name', 'last_name', 'club_id']),
            'transfermarkt' => Player::with('club:id,name')->where(function ($q) {
                $q->where(function ($inner) {
                    $inner->whereNull('transfermarkt_id')->orWhere('transfermarkt_id', '');
                })->where(function ($inner) {
                    $inner->whereNull('transfermarkt_url')->orWhere('transfermarkt_url', '');
                });
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

    public function linkSofascore(Player $player, SofascoreLinkService $linkService): RedirectResponse
    {
        $result = $linkService->linkPlayer($player->loadMissing('club:id,name'));

        if (!($result['linked'] ?? false)) {
            return back()->with('error', 'Kein sicherer Sofascore-Treffer gefunden.');
        }

        SyncPlayerSofascoreJob::dispatch($player->fresh());

        return back()->with(
            'status',
            "Sofascore-Verknüpfung gesetzt für {$player->full_name} (ID: {$result['id']}). Sync wurde gestartet."
        );
    }
}
