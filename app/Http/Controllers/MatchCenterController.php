<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Services\MatchSimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchCenterController extends Controller
{
    public function show(Request $request, GameMatch $match): View
    {
        $this->ensureReadable($request, $match);

        $match->load([
            'homeClub',
            'awayClub',
            'events.player',
            'events.club',
            'playerStats.player',
            'playerStats.club',
        ]);

        return view('leagues.matchcenter', [
            'match' => $match,
            'canSimulate' => $this->canSimulate($request, $match),
            'manageableClubIds' => $this->manageableClubIds($request, $match),
        ]);
    }

    public function simulate(
        Request $request,
        GameMatch $match,
        MatchSimulationService $simulationService
    ): RedirectResponse {
        abort_unless($this->canSimulate($request, $match), 403);

        $simulationService->simulate($match);

        return redirect()
            ->route('matches.show', $match)
            ->with('status', 'Spiel wurde simuliert.');
    }

    private function ensureReadable(Request $request, GameMatch $match): void
    {
        if ($request->user()->isAdmin()) {
            return;
        }

        $ownsHome = $request->user()->clubs()->whereKey($match->home_club_id)->exists();
        $ownsAway = $request->user()->clubs()->whereKey($match->away_club_id)->exists();
        abort_unless($ownsHome || $ownsAway, 403);
    }

    private function canSimulate(Request $request, GameMatch $match): bool
    {
        if ($match->status === 'played') {
            return false;
        }

        if ($request->user()->isAdmin()) {
            return true;
        }

        return $request->user()->clubs()->whereKey($match->home_club_id)->exists()
            || $request->user()->clubs()->whereKey($match->away_club_id)->exists();
    }

    private function manageableClubIds(Request $request, GameMatch $match): array
    {
        if ($request->user()->isAdmin()) {
            return [$match->home_club_id, $match->away_club_id];
        }

        return $request->user()->clubs()
            ->whereIn('id', [$match->home_club_id, $match->away_club_id])
            ->pluck('id')
            ->all();
    }
}
