<?php

namespace App\Services;

use App\Models\CompetitionSeason;
use Illuminate\Support\Collection;

class LeagueTableService
{
    public function __construct(
        private readonly StatisticsAggregationService $statisticsAggregationService
    ) {
    }

    public function rebuild(CompetitionSeason $competitionSeason): void
    {
        $this->statisticsAggregationService->rebuildLeagueTable($competitionSeason);
        \Illuminate\Support\Facades\Cache::forget("league_table_{$competitionSeason->id}");
    }

    public function table(CompetitionSeason $competitionSeason): Collection
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "league_table_{$competitionSeason->id}",
            300, // 5 minutes — keeps data fresh during live matches
            fn() => $competitionSeason->statistics()
                ->with('club')
                ->orderByDesc('points')
                ->orderByDesc('goal_diff')
                ->orderByDesc('goals_for')
                ->get()
        );
    }
}
