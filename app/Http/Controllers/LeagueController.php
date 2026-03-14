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
    public function matches(Request $request): \Inertia\Response
    {
        $competitionSeason = $this->resolveCompetitionSeason($request);
        $isAdmin = $request->user()->isAdmin();
        $userClubs = $request->user()->clubs()->orderBy('name')->get(['id', 'name']);
        $clubFilterOptions = $userClubs;

        $ownedClubIds = $isAdmin
            ? collect()
            : $userClubs->pluck('id');

        $selectedClubId = (int) $request->query('club');
        if (!$isAdmin && $selectedClubId > 0 && !$ownedClubIds->contains($selectedClubId)) {
            $selectedClubId = 0;
        }

        $statusFilter = (string) $request->query('status', '');
        if (!in_array($statusFilter, ['scheduled', 'live', 'played'], true)) {
            $statusFilter = '';
        }

        $scopeFilter = (string) $request->query('scope', '');
        if (!in_array($scopeFilter, ['today', 'week', 'upcoming'], true)) {
            $scopeFilter = '';
        }

        $typeFilter = (string) $request->query('type', '');
        if (!in_array($typeFilter, ['league', 'cup', 'friendly'], true)) {
            $typeFilter = '';
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

        $matchesQuery = GameMatch::query()
            ->when($competitionSeason, fn($query) => $query->where('competition_season_id', $competitionSeason->id))
            ->when($selectedClubId > 0, function ($query) use ($selectedClubId): void {
                $query->where(function ($clubQuery) use ($selectedClubId): void {
                    $clubQuery->where('home_club_id', $selectedClubId)
                        ->orWhere('away_club_id', $selectedClubId);
                });
            })
            ->when($typeFilter !== '', fn($query) => $query->where('type', $typeFilter))
            ->when($statusFilter !== '', fn($query) => $query->where('status', $statusFilter))
            ->when($dateFrom, fn($query) => $query->whereDate('kickoff_at', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->whereDate('kickoff_at', '<=', $dateTo))
            ->when($scopeFilter === 'upcoming', fn($query) => $query->where('kickoff_at', '>=', now())->where('status', 'scheduled'))
            ->with(['homeClub:id,name,short_name,logo_path', 'awayClub:id,name,short_name,logo_path', 'competitionSeason.competition', 'stadiumClub:id,name'])
            ->orderBy('kickoff_at', 'asc');

        $hasActiveFilters = $selectedClubId > 0 || $statusFilter !== '' || $scopeFilter !== '' || $typeFilter !== '' || $dayFilter !== null || $dateFrom !== null || $dateTo !== null;

        $rawMatches = $matchesQuery->get()->map(function ($m) {
            $m->kickoff_formatted = $m->kickoff_at?->format('d.m.Y H:i');
            $m->kickoff_date = $m->kickoff_at?->format('Y-m-d');
            $m->kickoff_day_label = $m->kickoff_at?->locale('de')->isoFormat('dddd, D. MMMM');
            return $m;
        });

        if ($competitionSeason && !$hasActiveFilters && !$typeFilter) {
            $matchesByGroup = $rawMatches->groupBy('matchday')->map->values();
            $groupType = 'matchday';
        } else {
            $matchesByGroup = $rawMatches->groupBy('kickoff_date')->map->values();
            $groupType = 'date';
        }

        $activeClub = app()->has('activeClub') ? app('activeClub') : ($userClubs->first() ?? null);

        return \Inertia\Inertia::render('League/Matches', [
            'competitionSeasons' => CompetitionSeason::with(['competition', 'season'])->orderByDesc('id')->get(),
            'activeCompetitionSeason' => $competitionSeason,
            'matchesByGroup' => $matchesByGroup,
            'groupType' => $groupType,
            'ownedClubIds' => $ownedClubIds->values(),
            'clubFilterOptions' => $clubFilterOptions,
            'activeClub' => $activeClub,
            'filters' => [
                'competition_season' => $request->query('competition_season'),
                'club' => $selectedClubId > 0 ? $selectedClubId : null,
                'status' => $statusFilter,
                'scope' => $scopeFilter,
                'type' => $typeFilter,
                'day' => $dayFilter,
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'hasActiveFilters' => $hasActiveFilters,
        ]);
    }

    public function table(Request $request, LeagueTableService $tableService): \Inertia\Response
    {
        $competitionSeason = $this->resolveCompetitionSeason($request);

        if ($competitionSeason) {
            $tableService->rebuild($competitionSeason);
        }

        $table = $competitionSeason ? $tableService->table($competitionSeason) : collect();
        $ownedClubIds = $request->user()->clubs()->pluck('id')->all();

        return \Inertia\Inertia::render('League/Table', [
            'competitionSeasons' => CompetitionSeason::with(['competition', 'season'])->orderByDesc('id')->get(),
            'competitions' => Competition::orderBy('name')->get(),
            'seasons' => Season::orderBy('name')->get(),
            'activeCompetitionSeason' => $competitionSeason,
            'table' => $table->values(),
            'ownedClubIds' => $ownedClubIds,
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
            ->with('status', $count . ' Liga-Spiele wurden neu generiert.');
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
            ->whereHas('season', fn($q) => $q->where('is_current', true))
            ->first()
            ?? $query->latest()->first();
    }
}
