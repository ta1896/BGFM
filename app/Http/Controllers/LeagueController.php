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
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LeagueController extends Controller
{
    public function matches(Request $request): View
    {
        $competitionSeason = $this->resolveCompetitionSeason($request);
        $isAdmin = $request->user()->isAdmin();
        $clubFilterOptions = $request->user()->clubs()->orderBy('name')->get(['id', 'name']);

        $ownedClubIds = $isAdmin
            ? collect()
            : $request->user()->clubs()->pluck('id');

        $selectedClubId = (int) $request->query('club');
        if (!$isAdmin && $selectedClubId > 0 && !$ownedClubIds->contains($selectedClubId)) {
            $selectedClubId = 0;
        }

        $statusFilter = (string) $request->query('status', '');
        if (!in_array($statusFilter, ['scheduled', 'live', 'played'], true)) {
            $statusFilter = '';
        }

        $scopeFilter = (string) $request->query('scope', '');
        if (!in_array($scopeFilter, ['today', 'week'], true)) {
            $scopeFilter = '';
        }

        $normalizeDate = static function (?string $value): ?string {
            if (!$value) {
                return null;
            }

            try {
                return Carbon::createFromFormat('Y-m-d', $value)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        };

        $dayFilter = $normalizeDate($request->query('day'));
        $dateFrom = $normalizeDate($request->query('from'));
        $dateTo = $normalizeDate($request->query('to'));

        if ($scopeFilter === 'today') {
            $dateFrom = now()->toDateString();
            $dateTo = $dateFrom;
        } elseif ($scopeFilter === 'week') {
            $dateFrom = now()->startOfWeek(Carbon::MONDAY)->toDateString();
            $dateTo = now()->startOfWeek(Carbon::MONDAY)->addDays(6)->toDateString();
        }

        if ($dayFilter) {
            $dateFrom = $dayFilter;
            $dateTo = $dayFilter;
        }

        $matches = collect();
        if ($competitionSeason) {
            $matches = GameMatch::query()
                ->where('competition_season_id', $competitionSeason->id)
                ->when($selectedClubId > 0, function ($query) use ($selectedClubId): void {
                    $query->where(function ($clubQuery) use ($selectedClubId): void {
                        $clubQuery->where('home_club_id', $selectedClubId)
                            ->orWhere('away_club_id', $selectedClubId);
                    });
                })
                ->when($statusFilter !== '', fn ($query) => $query->where('status', $statusFilter))
                ->when($dateFrom, fn ($query) => $query->whereDate('kickoff_at', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('kickoff_at', '<=', $dateTo))
                ->with(['homeClub', 'awayClub'])
                ->orderBy('matchday')
                ->orderBy('kickoff_at')
                ->get()
                ->groupBy('matchday');
        }

        return view('leagues.matches', [
            'competitionSeasons' => CompetitionSeason::with(['competition', 'season'])->orderByDesc('id')->get(),
            'activeCompetitionSeason' => $competitionSeason,
            'matchesByDay' => $matches,
            'ownedClubIds' => $ownedClubIds,
            'clubFilterOptions' => $clubFilterOptions,
            'filters' => [
                'club' => $selectedClubId > 0 ? $selectedClubId : null,
                'status' => $statusFilter,
                'scope' => $scopeFilter,
                'day' => $dayFilter,
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'hasActiveFilters' => $selectedClubId > 0 || $statusFilter !== '' || $scopeFilter !== '' || $dayFilter !== null || $dateFrom !== null || $dateTo !== null,
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
