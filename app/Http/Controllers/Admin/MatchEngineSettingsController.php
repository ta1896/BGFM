<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SimulationSetting;
use App\Services\MatchEngine\EngineConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class MatchEngineSettingsController extends Controller
{
    public function index(): Response
    {
        $settings = SimulationSetting::where('key', 'like', 'match_engine.%')
            ->get()
            ->mapWithKeys(fn($item) => [$item->key => $item->value]);

        // Default values if not in DB
        $defaults = [
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

        return Inertia::render('Admin/MatchEngine/Settings', [
            'settings' => $settings->merge($defaults) // Merge to ensure we have all keys
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings.match_engine_duration' => 'required|integer|min:10|max:120',
            'settings.match_engine_chance_probability' => 'required|numeric|min:0.01|max:0.50',
            'settings.match_engine_goal_conversion' => 'required|numeric|min:0.01|max:1.00',
            'settings.match_engine_tactic_attack_bonus' => 'required|numeric|min:1.0|max:2.0',
            'settings.match_engine_tactic_defense_penalty' => 'required|numeric|min:0.1|max:1.0',
            'settings.match_engine_counter_attack_bonus' => 'required|numeric|min:1.0|max:3.0',
            'settings.match_engine_yellow_card_chance' => 'required|numeric|min:0.001|max:0.2',
            'settings.match_engine_red_card_chance' => 'required|numeric|min:0.0001|max:0.1',
            'settings.match_engine_home_advantage' => 'required|numeric|min:1.0|max:1.5',
        ]);

        $map = [
            'match_engine_duration'               => 'match_engine.duration',
            'match_engine_chance_probability'     => 'match_engine.chance_probability',
            'match_engine_goal_conversion'        => 'match_engine.goal_conversion',
            'match_engine_tactic_attack_bonus'    => 'match_engine.tactic_attack_bonus',
            'match_engine_tactic_defense_penalty' => 'match_engine.tactic_defense_penalty',
            'match_engine_counter_attack_bonus'   => 'match_engine.counter_attack_bonus',
            'match_engine_yellow_card_chance'     => 'match_engine.yellow_card_chance',
            'match_engine_red_card_chance'        => 'match_engine.red_card_chance',
            'match_engine_home_advantage'         => 'match_engine.home_advantage',
        ];

        foreach ($validated['settings'] as $key => $value) {
            if (isset($map[$key])) {
                SimulationSetting::updateOrCreate(
                    ['key' => $map[$key]],
                    ['value' => (string)$value]
                );
                
                // Clear specific cache key
                Cache::forget('match_engine_settings.' . str_replace('match_engine.', '', $map[$key]));
            }
        }

        return redirect()->route('admin.match-engine.index')
            ->with('status', 'Match-Engine Konfiguration gespeichert.');
    }
}
