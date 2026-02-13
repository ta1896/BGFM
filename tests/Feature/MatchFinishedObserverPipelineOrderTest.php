<?php

namespace Tests\Feature;

use App\Services\Simulation\Observers\ApplyMatchAvailabilityObserver;
use App\Services\Simulation\Observers\AggregatePlayerCompetitionStatsObserver;
use App\Services\Simulation\Observers\MatchFinishedObserverPipeline;
use App\Services\Simulation\Observers\RebuildMatchPlayerStatsObserver;
use App\Services\Simulation\Observers\SettleMatchFinanceObserver;
use App\Services\Simulation\Observers\UpdateCompetitionAfterMatchObserver;
use Tests\TestCase;

class MatchFinishedObserverPipelineOrderTest extends TestCase
{
    public function test_pipeline_observer_order_is_stable(): void
    {
        $pipeline = app(MatchFinishedObserverPipeline::class);

        $this->assertSame([
            RebuildMatchPlayerStatsObserver::class,
            AggregatePlayerCompetitionStatsObserver::class,
            ApplyMatchAvailabilityObserver::class,
            UpdateCompetitionAfterMatchObserver::class,
            SettleMatchFinanceObserver::class,
        ], $pipeline->observerClassNames());
    }
}
