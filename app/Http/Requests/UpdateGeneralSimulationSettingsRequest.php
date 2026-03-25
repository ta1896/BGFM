<?php

namespace App\Http\Requests;

use App\Modules\ModuleManager;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSimulationSettingsRequest extends FormRequest
{
    /**
     * @var array<string, array<string, mixed>>|null
     */
    private ?array $moduleFieldDefinitions = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->schedulerRules(),
            $this->positionFitRules(),
            $this->liveChangesRules(),
            $this->lineupRules(),
            $this->lineupScoringRules(),
            $this->teamStrengthRules(),
            $this->matchStrengthRules(),
            $this->featureRules(),
            $this->observerRules(),
            $this->moduleFieldRules()
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function moduleFieldDefinitions(): array
    {
        if ($this->moduleFieldDefinitions !== null) {
            return $this->moduleFieldDefinitions;
        }

        return $this->moduleFieldDefinitions = app(ModuleManager::class)->settingsFieldDefinitions();
    }

    /**
     * @return array<string, mixed>
     */
    private function schedulerRules(): array
    {
        return [
            'simulation.scheduler.interval_minutes' => $this->requiredInteger(1, 60),
            'simulation.scheduler.default_limit' => $this->requiredInteger(0, 500),
            'simulation.scheduler.max_concurrency' => $this->requiredInteger(1, 50),
            'simulation.scheduler.default_minutes_per_run' => $this->requiredInteger(1, 90),
            'simulation.scheduler.default_types' => ['required', 'array', 'min:1'],
            'simulation.scheduler.default_types.*' => ['string', 'in:friendly,league,cup'],
            'simulation.scheduler.claim_stale_after_seconds' => $this->requiredInteger(30, 3600),
            'simulation.scheduler.runner_lock_seconds' => $this->requiredInteger(30, 3600),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function positionFitRules(): array
    {
        return [
            'simulation.position_fit.main' => $this->requiredNumeric(0.50, 1.20),
            'simulation.position_fit.second' => $this->requiredNumeric(0.50, 1.20),
            'simulation.position_fit.third' => $this->requiredNumeric(0.50, 1.20),
            'simulation.position_fit.foreign' => $this->requiredNumeric(0.30, 1.20),
            'simulation.position_fit.foreign_gk' => $this->requiredNumeric(0.20, 1.20),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function liveChangesRules(): array
    {
        return [
            'simulation.live_changes.planned_substitutions.max_per_club' => $this->requiredInteger(1, 5),
            'simulation.live_changes.planned_substitutions.min_minutes_ahead' => $this->requiredInteger(1, 30),
            'simulation.live_changes.planned_substitutions.min_interval_minutes' => $this->requiredInteger(1, 30),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function lineupRules(): array
    {
        return [
            'simulation.lineup.max_bench_players' => $this->requiredInteger(1, 10),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function lineupScoringRules(): array
    {
        return [
            'simulation.lineup_scoring.slot_score_bonuses.main' => $this->requiredNumeric(0, 500),
            'simulation.lineup_scoring.slot_score_bonuses.second' => $this->requiredNumeric(0, 500),
            'simulation.lineup_scoring.slot_score_bonuses.third' => $this->requiredNumeric(0, 500),
            'simulation.lineup_scoring.slot_score_bonuses.group_fallback' => $this->requiredNumeric(0, 500),
            'simulation.lineup_scoring.fit_weight' => $this->requiredNumeric(0, 1000),
            'simulation.lineup_scoring.role_weight' => $this->requiredNumeric(0, 25),
            'simulation.lineup_scoring.low_fit_penalty' => $this->requiredNumeric(0, 1000),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function teamStrengthRules(): array
    {
        return [
            'simulation.team_strength.weights.attack' => ['required', 'array'],
            'simulation.team_strength.weights.attack.*' => $this->requiredNumeric(0, 1),
            'simulation.team_strength.weights.midfield' => ['required', 'array'],
            'simulation.team_strength.weights.midfield.*' => $this->requiredNumeric(0, 1),
            'simulation.team_strength.weights.defense' => ['required', 'array'],
            'simulation.team_strength.weights.defense.*' => $this->requiredNumeric(0, 1),
            'simulation.team_strength.formation_factor.complete_lineup' => $this->requiredNumeric(0.1, 2),
            'simulation.team_strength.formation_factor.incomplete_lineup' => $this->requiredNumeric(0.1, 2),
            'simulation.team_strength.formation_factor.minimum_players' => $this->requiredInteger(1, 11),
            'simulation.team_strength.chemistry.size_bonus_cap' => $this->requiredInteger(0, 25),
            'simulation.team_strength.chemistry.fit_modifier_min' => $this->requiredNumeric(0.1, 1.5),
            'simulation.team_strength.chemistry.fit_modifier_max' => $this->requiredNumeric(0.1, 1.5),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function matchStrengthRules(): array
    {
        return [
            'simulation.match_strength.weights' => ['required', 'array'],
            'simulation.match_strength.weights.*' => $this->requiredNumeric(0, 1),
            'simulation.match_strength.home_bonus' => $this->requiredNumeric(0, 25),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function featureRules(): array
    {
        return [
            'simulation.features.player_conversations_enabled' => $this->requiredBoolean(),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function observerRules(): array
    {
        return [
            'simulation.observers.match_finished.enabled' => $this->requiredBoolean(),
            'simulation.observers.match_finished.rebuild_match_player_stats' => $this->requiredBoolean(),
            'simulation.observers.match_finished.aggregate_player_competition_stats' => $this->requiredBoolean(),
            'simulation.observers.match_finished.apply_match_availability' => $this->requiredBoolean(),
            'simulation.observers.match_finished.update_competition_after_match' => $this->requiredBoolean(),
            'simulation.observers.match_finished.settle_match_finance' => $this->requiredBoolean(),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function moduleFieldRules(): array
    {
        $rules = [];

        foreach ($this->moduleFieldDefinitions() as $key => $field) {
            $rules[$key] = $this->rulesForModuleField($field);
        }

        return $rules;
    }

    /**
     * @param array<string, mixed> $field
     * @return array<int, string>
     */
    private function rulesForModuleField(array $field): array
    {
        $type = (string) ($field['type'] ?? 'boolean');

        return match ($type) {
            'integer' => $this->requiredInteger((int) ($field['min'] ?? 0), (int) ($field['max'] ?? 1000)),
            'number' => $this->requiredNumeric((float) ($field['min'] ?? 0), (float) ($field['max'] ?? 1000)),
            'select' => [
                'required',
                'string',
                'in:'.implode(',', array_map('strval', (array) ($field['options'] ?? []))),
            ],
            'text' => [
                'nullable',
                'string',
                'max:'.(int) ($field['max_length'] ?? 255),
            ],
            default => $this->requiredBoolean(),
        };
    }

    /**
     * @return array<int, string>
     */
    private function requiredInteger(int $min, int $max): array
    {
        return ['required', 'integer', 'min:'.$min, 'max:'.$max];
    }

    /**
     * @return array<int, string>
     */
    private function requiredNumeric(float|int $min, float|int $max): array
    {
        return ['required', 'numeric', 'between:'.$min.','.$max];
    }

    /**
     * @return array<int, string>
     */
    private function requiredBoolean(): array
    {
        return ['required', 'boolean'];
    }
}
