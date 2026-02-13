<?php

namespace App\Services\Simulation\Observers;

use App\Services\MatchPlayerStatsService;
use App\Services\MatchProcessingStepService;

class RebuildMatchPlayerStatsObserver implements MatchFinishedObserver
{
    public function __construct(
        private readonly MatchPlayerStatsService $matchPlayerStatsService,
        private readonly MatchProcessingStepService $processingStepService
    ) {
    }

    public function handle(MatchFinishedContext $context): void
    {
        if (!$this->processingStepService->claim($context->match, 'rebuild_match_player_stats')) {
            return;
        }

        $this->matchPlayerStatsService->rebuild($context->match, $context->homePlayers, $context->awayPlayers);
    }
}
