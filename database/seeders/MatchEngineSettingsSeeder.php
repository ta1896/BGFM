<?php

namespace Database\Seeders;

use App\Models\SimulationSetting;
use App\Services\MatchEngine\EngineConfiguration;
use Illuminate\Database\Seeder;

class MatchEngineSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'match_engine.duration' => EngineConfiguration::MATCH_MINUTES,
            'match_engine.chance_probability' => EngineConfiguration::CHANCE_PROBABILITY_BASE,
            'match_engine.goal_conversion' => EngineConfiguration::GOAL_CONVERSION_BASE,
            'match_engine.tactic_attack_bonus' => EngineConfiguration::TACTIC_ATTACK_BONUS,
            'match_engine.tactic_defense_penalty' => EngineConfiguration::TACTIC_DEFENSE_PENALTY,
            'match_engine.counter_attack_bonus' => EngineConfiguration::COUNTER_ATTACK_BONUS,
            'match_engine.yellow_card_chance' => EngineConfiguration::YELLOW_CARD_CHANCE,
            'match_engine.red_card_chance' => EngineConfiguration::RED_CARD_CHANCE,
            'match_engine.home_advantage' => EngineConfiguration::HOME_ADVANTAGE_STRENGTH,
        ];

        foreach ($settings as $key => $value) {
            SimulationSetting::updateOrCreate(
            ['key' => $key],
            ['value' => (string)$value]
            );
        }
    }
}
