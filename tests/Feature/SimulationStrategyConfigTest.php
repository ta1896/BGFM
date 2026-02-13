<?php

namespace Tests\Feature;

use App\Services\Simulation\DefaultSimulationStrategy;
use Tests\TestCase;

class SimulationStrategyConfigTest extends TestCase
{
    public function test_strategy_uses_configured_probabilities(): void
    {
        /** @var DefaultSimulationStrategy $strategy */
        $strategy = app(DefaultSimulationStrategy::class);

        config()->set('simulation.probabilities.tackle_attempt', 1.0);
        $this->assertTrue($strategy->shouldAttemptTackle());

        config()->set('simulation.probabilities.tackle_attempt', 0.0);
        $this->assertFalse($strategy->shouldAttemptTackle());

        config()->set('simulation.probabilities.penalty_awarded_after_foul', 1.0);
        $this->assertTrue($strategy->isPenaltyAwardedFromFoul());

        config()->set('simulation.probabilities.penalty_awarded_after_foul', 0.0);
        $this->assertFalse($strategy->isPenaltyAwardedFromFoul());
    }
}
