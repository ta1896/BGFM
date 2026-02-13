<?php

namespace App\Services\Simulation\Observers;

use App\Models\CompetitionSeason;
use App\Services\CompetitionContextService;
use App\Services\CupProgressionService;
use App\Services\LeagueTableService;
use App\Services\MatchProcessingStepService;

class UpdateCompetitionAfterMatchObserver implements MatchFinishedObserver
{
    public function __construct(
        private readonly LeagueTableService $tableService,
        private readonly CupProgressionService $cupProgressionService,
        private readonly CompetitionContextService $competitionContextService,
        private readonly MatchProcessingStepService $processingStepService
    ) {
    }

    public function handle(MatchFinishedContext $context): void
    {
        if (!$this->processingStepService->claim($context->match, 'update_competition_after_match')) {
            return;
        }

        if (!$context->match->competition_season_id) {
            return;
        }

        $competitionSeason = CompetitionSeason::query()->find($context->match->competition_season_id);
        if (!$competitionSeason) {
            return;
        }

        if ($this->competitionContextService->isLeague($context->match)) {
            $this->tableService->rebuild($competitionSeason);
        }

        if ($this->competitionContextService->isCup($context->match)) {
            $this->cupProgressionService->progressRoundIfNeeded($competitionSeason, $context->match);
        }
    }
}
