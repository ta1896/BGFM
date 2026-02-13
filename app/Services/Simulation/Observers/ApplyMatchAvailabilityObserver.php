<?php

namespace App\Services\Simulation\Observers;

use App\Services\PlayerAvailabilityService;
use App\Services\MatchProcessingStepService;

class ApplyMatchAvailabilityObserver implements MatchFinishedObserver
{
    public function __construct(
        private readonly PlayerAvailabilityService $availabilityService,
        private readonly MatchProcessingStepService $processingStepService
    ) {
    }

    public function handle(MatchFinishedContext $context): void
    {
        if (!$this->processingStepService->claim($context->match, 'apply_match_availability')) {
            return;
        }

        $this->availabilityService->applyMatchConsequences($context->match);
    }
}
