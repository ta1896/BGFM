<?php

namespace Tests\Unit;

use App\Models\Lineup;
use App\Models\Player;
use App\Services\MatchStrengthService;
use App\Services\PlayerPositionService;
use App\Services\TeamStrengthCalculator;
use stdClass;
use Tests\TestCase;

class StrengthCalculationServicesTest extends TestCase
{
    public function test_team_strength_calculator_respects_runtime_weights(): void
    {
        config([
            'simulation.position_fit.main' => 1.0,
            'simulation.position_fit.second' => 1.0,
            'simulation.position_fit.third' => 1.0,
            'simulation.position_fit.foreign' => 1.0,
            'simulation.position_fit.foreign_gk' => 1.0,
            'simulation.team_strength.weights.attack' => [
                'overall' => 0.0,
                'shooting' => 0.0,
                'attr_attacking' => 1.0,
            ],
            'simulation.team_strength.weights.midfield' => [
                'overall' => 0.0,
                'passing' => 0.0,
                'attr_tactical' => 1.0,
            ],
            'simulation.team_strength.weights.defense' => [
                'overall' => 0.0,
                'defending' => 0.0,
                'attr_defending' => 1.0,
            ],
            'simulation.team_strength.formation_factor.complete_lineup' => 1.0,
            'simulation.team_strength.formation_factor.incomplete_lineup' => 1.0,
            'simulation.team_strength.formation_factor.minimum_players' => 1,
            'simulation.team_strength.chemistry.size_bonus_cap' => 10,
            'simulation.team_strength.chemistry.fit_modifier_min' => 1.0,
            'simulation.team_strength.chemistry.fit_modifier_max' => 1.0,
        ]);

        $calculator = new TeamStrengthCalculator($this->app->make(PlayerPositionService::class));

        $strongAttackLineup = new Lineup(['formation' => '4-3-3']);
        $strongAttackLineup->setRelation('players', collect([
            $this->makePlayer('MS', ['attr_attacking' => 90]),
            $this->makePlayer('ZM', ['attr_tactical' => 88]),
            $this->makePlayer('IV', ['attr_defending' => 86]),
        ]));

        $weakAttackLineup = new Lineup(['formation' => '4-3-3']);
        $weakAttackLineup->setRelation('players', collect([
            $this->makePlayer('MS', ['attr_attacking' => 40]),
            $this->makePlayer('ZM', ['attr_tactical' => 88]),
            $this->makePlayer('IV', ['attr_defending' => 86]),
        ]));

        $strongMetrics = $calculator->calculate($strongAttackLineup);
        $weakMetrics = $calculator->calculate($weakAttackLineup);

        $this->assertGreaterThan($weakMetrics['attack'], $strongMetrics['attack']);
        $this->assertSame($strongMetrics['midfield'], $weakMetrics['midfield']);
        $this->assertSame($strongMetrics['defense'], $weakMetrics['defense']);
    }

    public function test_match_strength_service_uses_extended_attributes_and_home_bonus(): void
    {
        config([
            'simulation.match_strength.weights' => [
                'overall' => 0.0,
                'shooting' => 0.0,
                'passing' => 0.0,
                'defending' => 0.0,
                'stamina' => 0.0,
                'morale' => 0.0,
                'attr_attacking' => 0.5,
                'attr_technical' => 0.25,
                'attr_market' => 0.25,
            ],
            'simulation.match_strength.home_bonus' => 4.0,
        ]);

        $service = new MatchStrengthService();
        $players = collect([
            new Player(['attr_attacking' => 80, 'attr_technical' => 60, 'attr_market' => 40]),
            new Player(['attr_attacking' => 80, 'attr_technical' => 60, 'attr_market' => 40]),
        ]);

        $away = $service->fromPlayers($players, false);
        $home = $service->fromPlayers($players, true);

        $this->assertSame(65.0, $away);
        $this->assertSame(69.0, $home);
    }

    private function makePlayer(string $position, array $overrides = []): Player
    {
        $player = new Player(array_merge([
            'position' => $position,
            'position_main' => $position,
            'position_second' => null,
            'position_third' => null,
            'overall' => 70,
            'shooting' => 50,
            'passing' => 50,
            'defending' => 50,
            'physical' => 50,
            'stamina' => 50,
            'morale' => 50,
            'attr_attacking' => 50,
            'attr_tactical' => 50,
            'attr_defending' => 50,
            'attr_technical' => 50,
            'attr_creativity' => 50,
            'attr_market' => 50,
            'potential' => 50,
        ], $overrides));

        $pivot = new stdClass();
        $pivot->pitch_position = $position;
        $pivot->is_bench = false;
        $player->setRelation('pivot', $pivot);

        return $player;
    }
}
