<?php

namespace Tests\Unit;

use App\Services\PlayerPositionService;
use Tests\TestCase;

class PlayerPositionServiceTest extends TestCase
{
    public function test_fit_factor_uses_main_second_third_and_foreign_profiles(): void
    {
        config()->set('simulation.position_fit.main', 1.00);
        config()->set('simulation.position_fit.second', 0.92);
        config()->set('simulation.position_fit.third', 0.84);
        config()->set('simulation.position_fit.foreign', 0.76);
        config()->set('simulation.position_fit.foreign_gk', 0.55);

        $service = new PlayerPositionService();

        $this->assertEqualsWithDelta(1.00, $service->fitFactorWithProfile('ZM', 'ST', 'LV', 'ZM'), 0.0001);
        $this->assertEqualsWithDelta(0.92, $service->fitFactorWithProfile('ZM', 'ST', 'LV', 'ST'), 0.0001);
        $this->assertEqualsWithDelta(0.84, $service->fitFactorWithProfile('ZM', 'ST', 'LV', 'LV'), 0.0001);
        $this->assertEqualsWithDelta(0.76, $service->fitFactorWithProfile('ZM', 'DM', 'OM', 'ST'), 0.0001);
        $this->assertEqualsWithDelta(0.55, $service->fitFactorWithProfile('TW', null, null, 'ST'), 0.0001);
    }

    public function test_fit_values_are_clamped_to_zero_and_one(): void
    {
        config()->set('simulation.position_fit.second', 1.4);
        config()->set('simulation.position_fit.third', -0.4);

        $service = new PlayerPositionService();

        $this->assertEqualsWithDelta(1.00, $service->fitFactorWithProfile('ZM', 'ST', 'LV', 'ST'), 0.0001);
        $this->assertEqualsWithDelta(0.00, $service->fitFactorWithProfile('ZM', 'ST', 'LV', 'LV'), 0.0001);
    }
}
