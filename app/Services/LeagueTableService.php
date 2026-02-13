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
    }

    public function table(CompetitionSeason $competitionSeason): Collection
    {
        return $competitionSeason->statistics()
            ->with('club')
            ->orderByDesc('points')
            ->orderByDesc('goal_diff')
            ->orderByDesc('goals_for')
            ->get();
    }
}
