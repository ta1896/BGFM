<?php

namespace App\Services\Simulation\Observers;

use App\Services\MatchAftermathService;
use App\Services\MatchProcessingStepService;

class ApplyMatchAvailabilityObserver implements MatchFinishedObserver
{
    public function __construct(
        private readonly MatchAftermathService $aftermathService,
        private readonly MatchProcessingStepService $processingStepService
    ) {
    }

    public function handle(MatchFinishedContext $context): void
    {
        if (!$this->processingStepService->claim($context->match, 'apply_match_availability')) {
            return;
        }

        $this->aftermathService->apply($context->match);
    }
}
