<?php

namespace Tests\Feature;

use App\Modules\ModuleManager;
use App\Models\SimulationSetting;
use App\Models\User;
use App\Services\SimulationSettingsService;
use Illuminate\Filesystem\Filesystem;
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
                        'max_concurrency' => 3,
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
                    'features' => [
                        'player_conversations_enabled' => 1,
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
                    'modules' => [
                        'awards_center' => [
                            'dashboard_widget_enabled' => 1,
                        ],
                        'live_center' => [
                            'dashboard_widget_enabled' => 1,
                            'match_panel_enabled' => 1,
                            'online_window_minutes' => 8,
                        ],
                        'medical_center' => [
                            'dashboard_widget_enabled' => 1,
                            'match_panel_enabled' => 1,
                            'return_candidate_window_days' => 5,
                        ],
                        'scouting_center' => [
                            'default_market' => 'global',
                            'default_discovery_level' => 'elite',
                            'target_limit' => 18,
                            'discovery_limit' => 9,
                            'discovery_note_prefix' => 'Priority',
                            'dashboard_widget_enabled' => 1,
                        ],
                    ],
                ],
            ]);

        $response
            ->assertRedirect(route('admin.simulation.settings.index'))
            ->assertSessionHas('status');

        $this->assertSame(12, (int) config('simulation.scheduler.interval_minutes'));
        $this->assertSame(['league', 'cup'], (array) config('simulation.scheduler.default_types'));
        $this->assertSame(150, (int) config('simulation.scheduler.runner_lock_seconds'));
        $this->assertSame(9, (int) config('simulation.lineup.max_bench_players'));
        $this->assertTrue((bool) config('simulation.features.player_conversations_enabled'));
        $this->assertTrue((bool) config('simulation.observers.match_finished.enabled'));
        $this->assertFalse((bool) config('simulation.observers.match_finished.settle_match_finance'));
        $this->assertSame('global', config('simulation.modules.scouting_center.default_market'));
        $this->assertSame('elite', config('simulation.modules.scouting_center.default_discovery_level'));
        $this->assertSame(18, (int) config('simulation.modules.scouting_center.target_limit'));
        $this->assertSame(9, (int) config('simulation.modules.scouting_center.discovery_limit'));
        $this->assertSame('Priority', config('simulation.modules.scouting_center.discovery_note_prefix'));

        $typesSetting = SimulationSetting::query()
            ->where('key', 'simulation.scheduler.default_types')
            ->first();
        $benchSetting = SimulationSetting::query()
            ->where('key', 'simulation.lineup.max_bench_players')
            ->first();
        $marketSetting = SimulationSetting::query()
            ->where('key', 'simulation.modules.scouting_center.default_market')
            ->first();

        $this->assertNotNull($typesSetting);
        $this->assertNotNull($benchSetting);
        $this->assertNotNull($marketSetting);
        $this->assertSame(['league', 'cup'], json_decode((string) $typesSetting->value, true));
        $this->assertSame(9, json_decode((string) $benchSetting->value, true));
        $this->assertSame('global', json_decode((string) $marketSetting->value, true));
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
                        'max_concurrency' => 3,
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
                    'features' => [
                        'player_conversations_enabled' => 0,
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

    public function test_module_settings_can_hide_widgets_from_frontend_registry(): void
    {
        config(['simulation.modules.scouting_center.dashboard_widget_enabled' => false]);

        $registry = app(ModuleManager::class)->frontendRegistry();
        $widgetKeys = collect($registry['dashboard_widgets'] ?? [])->pluck('key')->all();

        $this->assertNotContains('scouting-center-desk', $widgetKeys);
        $this->assertContains('live-center-overview', $widgetKeys);
    }

    public function test_module_registry_normalizes_player_actions_and_matchcenter_panels(): void
    {
        $files = app(Filesystem::class);
        $basePath = storage_path('framework/testing-modules/'.uniqid('schema-', true));
        $modulePath = $basePath.'/SchemaLab';

        $files->ensureDirectoryExists($modulePath);

        $manifest = [
            'key' => 'schema-lab',
            'name' => 'SchemaLab',
            'version' => '1.0.0',
            'enabled' => true,
            'providers' => [],
            'frontend' => [
                'player_actions' => [
                    [
                        'key' => 'valid-action',
                        'title' => 'Broken Method Action',
                        'route' => 'dashboard',
                        'method' => 'TRACE',
                        'scope' => 'unknown',
                        'placement' => 'sidebar',
                        'payload' => 'not-an-array',
                        'query' => 'not-an-array',
                    ],
                    [
                        'key' => 'missing-route',
                        'title' => 'Missing Route',
                    ],
                    [
                        'key' => 'hidden-action',
                        'title' => 'Hidden Action',
                        'route' => 'dashboard',
                        'enabled_when' => 'simulation.modules.schema_lab.hidden_action_enabled',
                    ],
                ],
                'matchcenter_panels' => [
                    [
                        'key' => 'valid-panel',
                        'title' => 'Panel Title',
                        'route' => 'dashboard',
                        'priority' => '5',
                    ],
                    [
                        'key' => 'missing-title',
                    ],
                    [
                        'key' => 'hidden-panel',
                        'title' => 'Hidden Panel',
                        'enabled_when' => 'simulation.modules.schema_lab.hidden_panel_enabled',
                    ],
                ],
            ],
        ];

        $files->put($modulePath.'/module.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        try {
            config([
                'modules.paths' => [$basePath],
                'simulation.modules.schema_lab.hidden_action_enabled' => false,
                'simulation.modules.schema_lab.hidden_panel_enabled' => false,
            ]);

            $manager = new ModuleManager($files);
            $registry = $manager->frontendRegistry();

            $this->assertCount(1, $registry['player_actions']);
            $this->assertSame('valid-action', $registry['player_actions'][0]['key']);
            $this->assertSame('get', $registry['player_actions'][0]['method']);
            $this->assertSame('all', $registry['player_actions'][0]['scope']);
            $this->assertSame('overview', $registry['player_actions'][0]['placement']);
            $this->assertSame([], $registry['player_actions'][0]['payload']);
            $this->assertSame([], $registry['player_actions'][0]['query']);

            $this->assertCount(1, $registry['matchcenter_panels']);
            $this->assertSame('valid-panel', $registry['matchcenter_panels'][0]['key']);
            $this->assertSame('Panel Title', $registry['matchcenter_panels'][0]['title']);
            $this->assertSame(5, $registry['matchcenter_panels'][0]['priority']);
        } finally {
            $files->deleteDirectory($basePath);
        }
    }
}
