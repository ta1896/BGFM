<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Player;
use App\Models\ScoutingReport;
use App\Models\ScoutingWatchlist;
use App\Services\ScoutingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScoutingController extends Controller
{
    public function index(Request $request, ScoutingService $scoutingService): Response
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;

        if (!$activeClub) {
            $activeClub = $request->user()->isAdmin()
                ? Club::query()->where('is_cpu', false)->orderBy('name')->first()
                : $request->user()->clubs()->where('is_cpu', false)->orderBy('name')->first();
        }

        $search = trim((string) $request->query('search', ''));
        $query = Player::query()
            ->with(['club', 'injuries', 'recoveryLogs'])
            ->when($activeClub, fn ($builder) => $builder->where('club_id', '!=', $activeClub->id))
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($inner) use ($search): void {
                    $inner
                        ->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('potential')
            ->limit(20)
            ->get();

        $watchlist = collect();
        if ($activeClub) {
            $watchlist = ScoutingWatchlist::query()
                ->where('club_id', $activeClub->id)
                ->with(['player.club', 'reports' => fn ($query) => $query->latest('id')->limit(3)])
                ->latest('updated_at')
                ->get();
        }

        return Inertia::render('Scouting/Index', [
            'club' => $activeClub ? [
                'id' => $activeClub->id,
                'name' => $activeClub->name,
                'logo_url' => $activeClub->logo_url,
                'budget' => (float) $activeClub->budget,
            ] : null,
            'scoutOptions' => [
                'levels' => ['junior', 'experienced', 'elite'],
                'regions' => ['domestic', 'continental', 'global'],
                'types' => ['live', 'video', 'data'],
                'focuses' => ['general', 'tactical', 'medical', 'personality'],
            ],
            'targets' => $query->map(fn (Player $player) => [
                'id' => $player->id,
                'name' => $player->full_name,
                'photo_url' => $player->photo_url,
                'position' => $player->display_position,
                'age' => (int) $player->age,
                'club_name' => $player->club?->name,
                'country' => $player->club?->country,
                'market_value' => number_format((float) $player->market_value, 0, ',', '.').' EUR',
                'potential_hint' => $player->potential >= 80 ? 'Elite-Profil' : ($player->potential >= 72 ? 'Spannend' : 'Breite'),
            ])->values()->all(),
            'watchlist' => $watchlist->map(function (ScoutingWatchlist $entry) {
                $report = $entry->reports->first();

                return [
                    'id' => $entry->id,
                    'priority' => $entry->priority,
                    'status' => $entry->status,
                    'focus' => $entry->focus,
                    'scout_level' => $entry->scout_level,
                    'scout_region' => $entry->scout_region,
                    'scout_type' => $entry->scout_type,
                    'progress' => (int) $entry->progress,
                    'reports_requested' => (int) $entry->reports_requested,
                    'mission_days_left' => (int) $entry->mission_days_left,
                    'last_mission_cost' => (float) $entry->last_mission_cost,
                    'last_scouted_at' => $entry->last_scouted_at?->format('d.m.Y H:i'),
                    'next_report_due_at' => $entry->next_report_due_at?->format('d.m.Y'),
                    'notes' => $entry->notes,
                    'player' => [
                        'id' => $entry->player?->id,
                        'name' => $entry->player?->full_name,
                        'photo_url' => $entry->player?->photo_url,
                        'position' => $entry->player?->display_position,
                        'club_name' => $entry->player?->club?->name,
                        'country' => $entry->player?->club?->country,
                    ],
                    'mission_preview' => $entry->player ? $scoutingService->previewMission(
                        $activeClub,
                        $entry->player,
                        $entry->priority,
                        $entry->focus,
                        $entry->scout_level,
                        $entry->scout_region,
                        $entry->scout_type,
                    ) : null,
                    'latest_report' => $report ? [
                        'created_at' => $report->created_at?->format('d.m.Y H:i'),
                        'confidence' => (int) $report->confidence,
                        'overall_band' => $report->overall_min.'-'.$report->overall_max,
                        'potential_band' => $report->potential_min.'-'.$report->potential_max,
                        'injury_risk_band' => $report->injury_risk_band,
                        'personality_band' => $report->personality_band,
                        'summary' => $report->summary,
                    ] : null,
                    'report_history' => $entry->reports->map(fn (ScoutingReport $history) => [
                        'id' => $history->id,
                        'confidence' => (int) $history->confidence,
                        'created_at' => $history->created_at?->format('d.m.Y H:i'),
                    ])->values()->all(),
                ];
            })->values()->all(),
        ]);
    }

    public function storeWatchlist(Request $request, Player $player, ScoutingService $scoutingService): RedirectResponse
    {
        $club = $this->resolveManagedClub($request);
        abort_if(!$club || $player->club_id === $club->id, 422);

        $validated = $request->validate([
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:watching,priority,negotiating'],
            'focus' => ['nullable', 'in:general,tactical,medical,personality'],
            'scout_level' => ['nullable', 'in:junior,experienced,elite'],
            'scout_region' => ['nullable', 'in:domestic,continental,global'],
            'scout_type' => ['nullable', 'in:live,video,data'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $scoutingService->upsertWatchlist($player, $club->id, $request->user()->id, $validated);

        return back()->with('status', 'Spieler wurde auf die Watchlist gesetzt.');
    }

    public function updateWatchlist(Request $request, ScoutingWatchlist $watchlist): RedirectResponse
    {
        $club = $this->resolveManagedClub($request);
        abort_unless($club && $watchlist->club_id === $club->id, 403);

        $validated = $request->validate([
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:watching,priority,negotiating'],
            'focus' => ['nullable', 'in:general,tactical,medical,personality'],
            'scout_level' => ['nullable', 'in:junior,experienced,elite'],
            'scout_region' => ['nullable', 'in:domestic,continental,global'],
            'scout_type' => ['nullable', 'in:live,video,data'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $watchlist->update($validated);

        return back()->with('status', 'Watchlist-Eintrag aktualisiert.');
    }

    public function destroyWatchlist(Request $request, ScoutingWatchlist $watchlist): RedirectResponse
    {
        $club = $this->resolveManagedClub($request);
        abort_unless($club && $watchlist->club_id === $club->id, 403);
        $watchlist->delete();

        return back()->with('status', 'Watchlist-Eintrag entfernt.');
    }

    public function generateReport(Request $request, Player $player, ScoutingService $scoutingService): RedirectResponse
    {
        $club = $this->resolveManagedClub($request);
        abort_if(!$club || $player->club_id === $club->id, 422);

        $watchlist = ScoutingWatchlist::query()
            ->where('club_id', $club->id)
            ->where('player_id', $player->id)
            ->first();

        $scoutingService->generateReport($player, $club->id, $watchlist?->id, $request->user()->id);

        return back()->with('status', 'Scout-Report wurde aktualisiert.');
    }

    public function advanceWatchlist(Request $request, ScoutingWatchlist $watchlist, ScoutingService $scoutingService): RedirectResponse
    {
        $club = $this->resolveManagedClub($request);
        abort_unless($club && $watchlist->club_id === $club->id, 403);

        $report = $scoutingService->advanceWatchlist($watchlist->loadMissing('player'), $request->user()->id);

        return back()->with('status', $report ? 'Scout-Mission abgeschlossen, neuer Report eingegangen.' : 'Scout-Mission wurde vorangetrieben.');
    }

    private function resolveManagedClub(Request $request): ?Club
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;

        if ($activeClub) {
            return $activeClub;
        }

        return $request->user()->isAdmin()
            ? Club::query()->where('is_cpu', false)->orderBy('name')->first()
            : $request->user()->clubs()->where('is_cpu', false)->orderBy('name')->first();
    }
}
