<?php

namespace App\Services\Simulation\Observers;

use App\Services\FinanceCycleService;
use App\Services\MatchProcessingStepService;

class SettleMatchFinanceObserver implements MatchFinishedObserver
{
    public function __construct(
        private readonly FinanceCycleService $financeCycleService,
        private readonly MatchProcessingStepService $processingStepService
    ) {
    }

    public function handle(MatchFinishedContext $context): void
    {
        if (!$this->processingStepService->claim($context->match, 'settle_match_finance')) {
            return;
        }

        $this->financeCycleService->settleMatch($context->match->fresh());
    }
}
