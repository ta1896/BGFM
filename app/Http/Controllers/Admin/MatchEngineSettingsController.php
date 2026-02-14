<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SimulationSetting;
use App\Services\MatchEngine\EngineConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class MatchEngineSettingsController extends Controller
{
    public function index(): View
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

        return view('admin.match-engine.settings', [
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

        foreach ($validated['settings'] as $key => $value) {
            // Convert form name (underscores) back to dot notation if needed or just use the key
            // The form will send 'match_engine_duration', we want 'match_engine.duration'
            $dbKey = str_replace('_', '.', $key); // e.g. match.engine.duration -> match_engine.duration... wait
        // Actually, let's just manual map or fix the form names.
        // Let's rely on the form sending clearer keys or handle mapping here.

        // Re-mapping logic:
        // "match_engine_duration" -> "match_engine.duration" isn't direct.
        // Let's assume input names are 'match_engine.duration' (dots are allowed in input names but Laravel converts to arrays)

        // If I use name="settings[match_engine.duration]", Laravel validates against settings.match_engine.duration

        }

        // Simpler approach:
        $inputs = $request->input('settings');
        foreach ($inputs as $key => $value) {
            // key comes in as "match_engine_duration" due to PHP variable naming rules in some contexts,
            // but in Request it should be preserved if I use array syntax correctly.
            // Let's use simple names in form like "match_engine_duration" and map here.

            $realKey = str_replace('_', '.', $key);
            // match_engine_duration -> match.engine.duration (Wrong)
            // match_engine_tactic_attack_bonus -> match.engine.tactic.attack.bonus (Wrong)

            // Hard map is safer
            $map = [
                'match_engine_duration' => 'match_engine.duration',
                'match_engine_chance_probability' => 'match_engine.chance_probability',
                'match_engine_goal_conversion' => 'match_engine.goal_conversion',
                'match_engine_tactic_attack_bonus' => 'match_engine.tactic_attack_bonus',
                'match_engine_tactic_defense_penalty' => 'match_engine.tactic_defense_penalty',
                'match_engine_counter_attack_bonus' => 'match_engine.counter_attack_bonus',
                'match_engine_yellow_card_chance' => 'match_engine.yellow_card_chance',
                'match_engine_red_card_chance' => 'match_engine.red_card_chance',
                'match_engine_home_advantage' => 'match_engine.home_advantage',
            ];

            if (isset($map[$key])) {
                SimulationSetting::updateOrCreate(
                ['key' => $map[$key]],
                ['value' => (string)$value]
                );
            }
        }

        // Clear Cache
        Cache::forget('match_engine_settings.duration');
        Cache::forget('match_engine_settings.chance_probability');
        Cache::forget('match_engine_settings.goal_conversion');
        Cache::forget('match_engine_settings.tactic_attack_bonus');
        Cache::forget('match_engine_settings.tactic_defense_penalty');
        Cache::forget('match_engine_settings.counter_attack_bonus');
        Cache::forget('match_engine_settings.yellow_card_chance');
        Cache::forget('match_engine_settings.red_card_chance');
        Cache::forget('match_engine_settings.home_advantage');

        return redirect()->route('admin.match-engine.index')
            ->with('status', 'Match Engine Configuration Saved.');
    }
}
