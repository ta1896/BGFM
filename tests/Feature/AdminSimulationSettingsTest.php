<?php

namespace Tests\Feature;

use App\Models\SimulationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSimulationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_simulation_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.simulation.settings.update'), [
                'simulation' => [
                    'scheduler' => [
                        'interval_minutes' => 12,
                        'default_limit' => 4,
                        'default_minutes_per_run' => 6,
                        'default_types' => ['league', 'cup'],
                        'claim_stale_after_seconds' => 240,
                        'runner_lock_seconds' => 150,
                    ],
                    'position_fit' => [
                        'main' => 1.00,
                        'second' => 0.91,
                        'third' => 0.83,
                        'foreign' => 0.75,
                        'foreign_gk' => 0.54,
                    ],
                    'live_changes' => [
                        'planned_substitutions' => [
                            'max_per_club' => 4,
                            'min_minutes_ahead' => 3,
                            'min_interval_minutes' => 4,
                        ],
                    ],
                    'lineup' => [
                        'max_bench_players' => 9,
                    ],
                    'observers' => [
                        'match_finished' => [
                            'enabled' => 1,
                            'rebuild_match_player_stats' => 1,
                            'aggregate_player_competition_stats' => 1,
                            'apply_match_availability' => 1,
                            'update_competition_after_match' => 1,
                            'settle_match_finance' => 0,
                        ],
                    ],
                ],
            ]);

        $response
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('status');

        $this->assertSame(12, (int) config('simulation.scheduler.interval_minutes'));
        $this->assertSame(['league', 'cup'], (array) config('simulation.scheduler.default_types'));
        $this->assertSame(150, (int) config('simulation.scheduler.runner_lock_seconds'));
        $this->assertSame(9, (int) config('simulation.lineup.max_bench_players'));
        $this->assertTrue((bool) config('simulation.observers.match_finished.enabled'));
        $this->assertFalse((bool) config('simulation.observers.match_finished.settle_match_finance'));

        $typesSetting = SimulationSetting::query()
            ->where('key', 'simulation.scheduler.default_types')
            ->first();
        $benchSetting = SimulationSetting::query()
            ->where('key', 'simulation.lineup.max_bench_players')
            ->first();

        $this->assertNotNull($typesSetting);
        $this->assertNotNull($benchSetting);
        $this->assertSame(['league', 'cup'], json_decode((string) $typesSetting->value, true));
        $this->assertSame(9, json_decode((string) $benchSetting->value, true));
    }

    public function test_non_admin_cannot_update_simulation_settings(): void
    {
        $manager = User::factory()->create(['is_admin' => false]);

        $this
            ->actingAs($manager)
            ->post(route('admin.simulation.settings.update'), [
                'simulation' => [
                    'scheduler' => [
                        'interval_minutes' => 10,
                        'default_limit' => 2,
                        'default_minutes_per_run' => 5,
                        'default_types' => ['league'],
                        'claim_stale_after_seconds' => 180,
                        'runner_lock_seconds' => 120,
                    ],
                    'position_fit' => [
                        'main' => 1.00,
                        'second' => 0.92,
                        'third' => 0.84,
                        'foreign' => 0.76,
                        'foreign_gk' => 0.55,
                    ],
                    'live_changes' => [
                        'planned_substitutions' => [
                            'max_per_club' => 5,
                            'min_minutes_ahead' => 2,
                            'min_interval_minutes' => 3,
                        ],
                    ],
                    'lineup' => [
                        'max_bench_players' => 5,
                    ],
                    'observers' => [
                        'match_finished' => [
                            'enabled' => 1,
                            'rebuild_match_player_stats' => 1,
                            'aggregate_player_competition_stats' => 1,
                            'apply_match_availability' => 1,
                            'update_competition_after_match' => 1,
                            'settle_match_finance' => 1,
                        ],
                    ],
                ],
            ])
            ->assertForbidden();
    }
}
