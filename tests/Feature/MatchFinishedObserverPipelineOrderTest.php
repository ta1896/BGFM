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

    public function test_pipeline_respects_granular_observer_toggles(): void
    {
        config()->set('simulation.observers.match_finished.enabled', true);
        config()->set('simulation.observers.match_finished.rebuild_match_player_stats', true);
        config()->set('simulation.observers.match_finished.aggregate_player_competition_stats', false);
        config()->set('simulation.observers.match_finished.apply_match_availability', true);
        config()->set('simulation.observers.match_finished.update_competition_after_match', true);
        config()->set('simulation.observers.match_finished.settle_match_finance', false);

        $pipeline = app(MatchFinishedObserverPipeline::class);

        $this->assertSame([
            RebuildMatchPlayerStatsObserver::class,
            ApplyMatchAvailabilityObserver::class,
            UpdateCompetitionAfterMatchObserver::class,
        ], $pipeline->observerClassNames());
    }
}
