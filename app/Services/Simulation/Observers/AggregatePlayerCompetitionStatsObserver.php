<?php

namespace App\Services\Simulation\Observers;

use App\Services\MatchProcessingStepService;
use App\Services\PlayerCompetitionStatsService;

class AggregatePlayerCompetitionStatsObserver implements MatchFinishedObserver
{
    public function __construct(
        private readonly PlayerCompetitionStatsService $playerCompetitionStatsService,
        private readonly MatchProcessingStepService $processingStepService
    ) {
    }

    public function handle(MatchFinishedContext $context): void
    {
        if (!$this->processingStepService->claim($context->match, 'aggregate_player_competition_stats')) {
            return;
        }

        $this->playerCompetitionStatsService->rebuildForMatchPlayers($context->match->fresh());
    }
}
