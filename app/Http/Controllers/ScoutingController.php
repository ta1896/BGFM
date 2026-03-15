<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Player;
use App\Models\ScoutingWatchlist;
use App\Services\ScoutingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScoutingController extends Controller
{
    public function index(Request $request): Response
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
                ->with(['player.club', 'reports' => fn ($query) => $query->latest('id')->limit(1)])
                ->latest('updated_at')
                ->get();
        }

        return Inertia::render('Scouting/Index', [
            'club' => $activeClub ? [
                'id' => $activeClub->id,
                'name' => $activeClub->name,
                'logo_url' => $activeClub->logo_url,
            ] : null,
            'targets' => $query->map(fn (Player $player) => [
                'id' => $player->id,
                'name' => $player->full_name,
                'photo_url' => $player->photo_url,
                'position' => $player->display_position,
                'age' => (int) $player->age,
                'club_name' => $player->club?->name,
                'market_value' => number_format((float) $player->market_value, 0, ',', '.').' EUR',
                'potential_hint' => $player->potential >= 80 ? 'Elite-Profil' : ($player->potential >= 72 ? 'Spannend' : 'Breite'),
            ])->values()->all(),
            'watchlist' => $watchlist->map(function (ScoutingWatchlist $entry) {
                $report = $entry->reports->first();

                return [
                    'id' => $entry->id,
                    'priority' => $entry->priority,
                    'status' => $entry->status,
                    'notes' => $entry->notes,
                    'player' => [
                        'id' => $entry->player?->id,
                        'name' => $entry->player?->full_name,
                        'photo_url' => $entry->player?->photo_url,
                        'position' => $entry->player?->display_position,
                        'club_name' => $entry->player?->club?->name,
                    ],
                    'latest_report' => $report ? [
                        'confidence' => (int) $report->confidence,
                        'overall_band' => $report->overall_min.'-'.$report->overall_max,
                        'potential_band' => $report->potential_min.'-'.$report->potential_max,
                        'injury_risk_band' => $report->injury_risk_band,
                        'personality_band' => $report->personality_band,
                        'summary' => $report->summary,
                    ] : null,
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
