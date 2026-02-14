<?php

namespace App\Services\MatchEngine;

use App\Models\SimulationSetting;
use Illuminate\Support\Facades\Cache;

class EngineConfiguration
{
    // Defaults
    public const MATCH_MINUTES = 90;
    public const CHANCE_PROBABILITY_BASE = 0.12;
    public const GOAL_CONVERSION_BASE = 0.15;
    public const TACTIC_ATTACK_BONUS = 1.25;
    public const TACTIC_DEFENSE_PENALTY = 0.85;
    public const COUNTER_ATTACK_BONUS = 1.40;
    public const YELLOW_CARD_CHANCE = 0.04;
    public const RED_CARD_CHANCE = 0.005;
    public const HOME_ADVANTAGE_STRENGTH = 1.10;

    public function get(string $key, $default = null)
    {
        return Cache::remember('match_engine_settings.' . $key, 3600, function () use ($key, $default) {
            $setting = SimulationSetting::where('key', 'match_engine.' . $key)->first();
            return $setting ? $this->castValue($setting->value) : $default;
        });
    }

    private function castValue($value)
    {
        if (is_numeric($value)) {
            return $value + 0; // Cast to int or float
        }
        return $value;
    }
}
