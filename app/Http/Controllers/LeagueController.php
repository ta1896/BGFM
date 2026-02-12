<?php

namespace App\Http\Controllers;

use App\Models\CompetitionSeason;
use App\Models\Competition;
use App\Models\GameMatch;
use App\Models\Season;
use App\Services\FixtureGeneratorService;
use App\Services\LeagueTableService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeagueController extends Controller
{
    public function matches(Request $request): View
    {
        $competitionSeason = $this->resolveCompetitionSeason($request);

        $matches = GameMatch::query()
            ->where('competition_season_id', $competitionSeason?->id)
            ->with(['homeClub', 'awayClub'])
            ->orderBy('matchday')
            ->orderBy('kickoff_at')
            ->get()
            ->groupBy('matchday');

        $ownedClubIds = $request->user()->isAdmin()
            ? collect()
            : $request->user()->clubs()->pluck('id');

        return view('leagues.matches', [
            'competitionSeasons' => CompetitionSeason::with(['competition', 'season'])->orderByDesc('id')->get(),
            'activeCompetitionSeason' => $competitionSeason,
            'matchesByDay' => $matches,
            'ownedClubIds' => $ownedClubIds,
        ]);
    }

    public function table(Request $request, LeagueTableService $tableService): View
    {
        $competitionSeason = $this->resolveCompetitionSeason($request);

        if ($competitionSeason) {
            $tableService->rebuild($competitionSeason);
        }

        return view('leagues.table', [
            'competitionSeasons' => CompetitionSeason::with(['competition', 'season'])->orderByDesc('id')->get(),
            'competitions' => Competition::orderBy('name')->get(),
            'seasons' => Season::orderByDesc('id')->get(),
            'activeCompetitionSeason' => $competitionSeason,
            'table' => $competitionSeason ? $tableService->table($competitionSeason) : collect(),
            'ownedClubIds' => $request->user()->clubs()->pluck('id')->all(),
        ]);
    }

    public function generateFixtures(
        Request $request,
        CompetitionSeason $competitionSeason,
        FixtureGeneratorService $fixtureGenerator,
        LeagueTableService $tableService
    ): RedirectResponse {
        abort_unless($request->user()->isAdmin(), 403);

        $count = $fixtureGenerator->generateRoundRobin($competitionSeason->load('season'));
        $tableService->rebuild($competitionSeason);

        return redirect()
            ->route('league.matches', ['competition_season' => $competitionSeason->id])
            ->with('status', $count.' Liga-Spiele wurden neu generiert.');
    }

    private function resolveCompetitionSeason(Request $request): ?CompetitionSeason
    {
        $id = (int) $request->query('competition_season');
        $competitionId = (int) $request->query('competition_id');
        $seasonId = (int) $request->query('season_id');

        $query = CompetitionSeason::with(['competition', 'season']);

        if ($id > 0) {
            return $query->find($id);
        }

        if ($competitionId > 0 && $seasonId > 0) {
            return $query
                ->where('competition_id', $competitionId)
                ->where('season_id', $seasonId)
                ->first();
        }

        return $query
            ->whereHas('season', fn ($q) => $q->where('is_current', true))
            ->first()
            ?? $query->latest()->first();
    }
}
