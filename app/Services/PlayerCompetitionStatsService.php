<?php

namespace App\Services;

use App\Models\GameMatch;

class PlayerCompetitionStatsService
{
    public function __construct(
        private readonly StatisticsAggregationService $statisticsAggregationService
    ) {
    }

    public function rebuildForMatchPlayers(GameMatch $match): void
    {
        $this->statisticsAggregationService->rebuildPlayerCompetitionStatsForMatch($match);
    }
}
