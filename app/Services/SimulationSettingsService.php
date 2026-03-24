<?php

namespace App\Services;

use App\Models\SimulationSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use JsonException;
use Throwable;

class SimulationSettingsService
{
    private const CACHE_KEY = 'simulation_settings.runtime_overrides';

    /**
     * @var array<int, string>
     */
    private const ALLOWED_MATCH_TYPES = ['friendly', 'league', 'cup'];

    /**
     * @var array<int, string>
     */
    private const TEAM_STRENGTH_AREAS = ['attack', 'midfield', 'defense'];

    /**
     * @var array<int, string>
     */
    private const TEAM_STRENGTH_ATTRIBUTES = [
        'shooting',
        'pace',
        'physical',
        'technical',
        'overall',
        'attr_attacking',
        'attr_tactical',
        'attr_creativity',
        'attr_market',
        'potential',
        'passing',
        'defending',
        'attr_defending',
    ];

    /**
     * @var array<int, string>
     */
    private const MATCH_STRENGTH_ATTRIBUTES = [
        'overall',
        'shooting',
        'passing',
        'defending',
        'stamina',
        'morale',
        'technical',
        'attr_attacking',
        'attr_tactical',
        'attr_defending',
        'attr_creativity',
        'attr_market',
        'potential',
    ];

    /**
     * @var array<int, string>
     */
    private const SLOT_SCORE_BONUS_KEYS = ['main', 'second', 'third', 'group_fallback'];

    /**
     * @var array<int, string>
     */
    private const LINEUP_SCORING_WEIGHT_KEYS = ['fit_weight', 'role_weight', 'low_fit_penalty'];

    public function applyRuntimeOverrides(): void
    {
        foreach ($this->loadSettingsMap() as $key => $value) {
            if (!str_starts_with($key, 'simulation.')) {
                continue;
            }

            config([$key => $value]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function adminSettings(): array
    {
        return [
            'scheduler' => [
                'interval_minutes' => max(1, (int) config('simulation.scheduler.interval_minutes', 1)),
                'default_limit' => max(0, (int) config('simulation.scheduler.default_limit', 0)),
                'default_minutes_per_run' => max(1, min(90, (int) config('simulation.scheduler.default_minutes_per_run', 5))),
                'default_types' => $this->normalizeTypes((array) config('simulation.scheduler.default_types', self::ALLOWED_MATCH_TYPES)),
                'claim_stale_after_seconds' => max(30, (int) config('simulation.scheduler.claim_stale_after_seconds', 180)),
                'runner_lock_seconds' => max(30, (int) config('simulation.scheduler.runner_lock_seconds', 120)),
                'max_concurrency' => max(1, (int) config('simulation.scheduler.max_concurrency', 5)),
            ],
            'position_fit' => [
                'main' => (float) config('simulation.position_fit.main', 1.00),
                'second' => (float) config('simulation.position_fit.second', 0.92),
                'third' => (float) config('simulation.position_fit.third', 0.84),
                'foreign' => (float) config('simulation.position_fit.foreign', 0.76),
                'foreign_gk' => (float) config('simulation.position_fit.foreign_gk', 0.55),
            ],
            'live_changes' => [
                'planned_substitutions' => [
                    'max_per_club' => max(1, (int) config('simulation.live_changes.planned_substitutions.max_per_club', 5)),
                    'min_minutes_ahead' => max(1, (int) config('simulation.live_changes.planned_substitutions.min_minutes_ahead', 2)),
                    'min_interval_minutes' => max(1, (int) config('simulation.live_changes.planned_substitutions.min_interval_minutes', 3)),
                ],
            ],
            'lineup' => [
                'max_bench_players' => $this->normalizeMaxBenchPlayers(
                    config('simulation.lineup.max_bench_players', 5)
                ),
            ],
            'team_strength' => [
                'weights' => $this->adminTeamStrengthWeights(),
                'formation_factor' => [
                    'complete_lineup' => round((float) config('simulation.team_strength.formation_factor.complete_lineup', 1.0), 2),
                    'incomplete_lineup' => round((float) config('simulation.team_strength.formation_factor.incomplete_lineup', 0.8), 2),
                    'minimum_players' => max(1, (int) config('simulation.team_strength.formation_factor.minimum_players', 8)),
                ],
                'chemistry' => [
                    'size_bonus_cap' => max(0, (int) config('simulation.team_strength.chemistry.size_bonus_cap', 10)),
                    'fit_modifier_min' => round((float) config('simulation.team_strength.chemistry.fit_modifier_min', 0.82), 2),
                    'fit_modifier_max' => round((float) config('simulation.team_strength.chemistry.fit_modifier_max', 1.0), 2),
                ],
            ],
            'match_strength' => [
                'weights' => $this->adminMatchStrengthWeights(),
                'home_bonus' => round((float) config('simulation.match_strength.home_bonus', 3.5), 2),
            ],
            'lineup_scoring' => [
                'slot_score_bonuses' => [
                    'main' => round((float) config('simulation.lineup_scoring.slot_score_bonuses.main', 120.0), 2),
                    'second' => round((float) config('simulation.lineup_scoring.slot_score_bonuses.second', 70.0), 2),
                    'third' => round((float) config('simulation.lineup_scoring.slot_score_bonuses.third', 35.0), 2),
                    'group_fallback' => round((float) config('simulation.lineup_scoring.slot_score_bonuses.group_fallback', 20.0), 2),
                ],
                'fit_weight' => round((float) config('simulation.lineup_scoring.fit_weight', 260.0), 2),
                'role_weight' => round((float) config('simulation.lineup_scoring.role_weight', 3.0), 2),
                'low_fit_penalty' => round((float) config('simulation.lineup_scoring.low_fit_penalty', 220.0), 2),
            ],
            'features' => [
                'player_conversations_enabled' => (bool) config('simulation.features.player_conversations_enabled', false),
            ],
            'observers' => [
                'match_finished' => [
                    'enabled' => (bool) config('simulation.observers.match_finished.enabled', true),
                    'rebuild_match_player_stats' => (bool) config('simulation.observers.match_finished.rebuild_match_player_stats', true),
                    'aggregate_player_competition_stats' => (bool) config('simulation.observers.match_finished.aggregate_player_competition_stats', true),
                    'apply_match_availability' => (bool) config('simulation.observers.match_finished.apply_match_availability', true),
                    'update_competition_after_match' => (bool) config('simulation.observers.match_finished.update_competition_after_match', true),
                    'settle_match_finance' => (bool) config('simulation.observers.match_finished.settle_match_finance', true),
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function updateFromAdminPayload(array $payload): void
    {
        $settings = [
            'simulation.scheduler.interval_minutes' => max(1, (int) data_get($payload, 'scheduler.interval_minutes', 1)),
            'simulation.scheduler.default_limit' => max(0, (int) data_get($payload, 'scheduler.default_limit', 0)),
            'simulation.scheduler.default_minutes_per_run' => max(1, min(90, (int) data_get($payload, 'scheduler.default_minutes_per_run', 5))),
            'simulation.scheduler.default_types' => $this->normalizeTypes((array) data_get($payload, 'scheduler.default_types', self::ALLOWED_MATCH_TYPES)),
            'simulation.scheduler.claim_stale_after_seconds' => max(30, (int) data_get($payload, 'scheduler.claim_stale_after_seconds', 180)),
            'simulation.scheduler.runner_lock_seconds' => max(30, (int) data_get($payload, 'scheduler.runner_lock_seconds', 120)),
            'simulation.scheduler.max_concurrency' => max(1, min(50, (int) data_get($payload, 'scheduler.max_concurrency', 5))),
            'simulation.position_fit.main' => round((float) data_get($payload, 'position_fit.main', 1.00), 2),
            'simulation.position_fit.second' => round((float) data_get($payload, 'position_fit.second', 0.92), 2),
            'simulation.position_fit.third' => round((float) data_get($payload, 'position_fit.third', 0.84), 2),
            'simulation.position_fit.foreign' => round((float) data_get($payload, 'position_fit.foreign', 0.76), 2),
            'simulation.position_fit.foreign_gk' => round((float) data_get($payload, 'position_fit.foreign_gk', 0.55), 2),
            'simulation.live_changes.planned_substitutions.max_per_club' => max(1, min(5, (int) data_get($payload, 'live_changes.planned_substitutions.max_per_club', 5))),
            'simulation.live_changes.planned_substitutions.min_minutes_ahead' => max(1, min(30, (int) data_get($payload, 'live_changes.planned_substitutions.min_minutes_ahead', 2))),
            'simulation.live_changes.planned_substitutions.min_interval_minutes' => max(1, min(30, (int) data_get($payload, 'live_changes.planned_substitutions.min_interval_minutes', 3))),
            'simulation.lineup.max_bench_players' => $this->normalizeMaxBenchPlayers(
                data_get($payload, 'lineup.max_bench_players', 5)
            ),
            'simulation.team_strength.formation_factor.complete_lineup' => round((float) data_get($payload, 'team_strength.formation_factor.complete_lineup', 1.0), 2),
            'simulation.team_strength.formation_factor.incomplete_lineup' => round((float) data_get($payload, 'team_strength.formation_factor.incomplete_lineup', 0.8), 2),
            'simulation.team_strength.formation_factor.minimum_players' => max(1, min(11, (int) data_get($payload, 'team_strength.formation_factor.minimum_players', 8))),
            'simulation.team_strength.chemistry.size_bonus_cap' => max(0, min(25, (int) data_get($payload, 'team_strength.chemistry.size_bonus_cap', 10))),
            'simulation.team_strength.chemistry.fit_modifier_min' => round((float) data_get($payload, 'team_strength.chemistry.fit_modifier_min', 0.82), 2),
            'simulation.team_strength.chemistry.fit_modifier_max' => round((float) data_get($payload, 'team_strength.chemistry.fit_modifier_max', 1.0), 2),
            'simulation.match_strength.home_bonus' => round((float) data_get($payload, 'match_strength.home_bonus', 3.5), 2),
            'simulation.features.player_conversations_enabled' => (bool) data_get($payload, 'features.player_conversations_enabled', false),
            'simulation.observers.match_finished.enabled' => (bool) data_get($payload, 'observers.match_finished.enabled', true),
            'simulation.observers.match_finished.rebuild_match_player_stats' => (bool) data_get($payload, 'observers.match_finished.rebuild_match_player_stats', true),
            'simulation.observers.match_finished.aggregate_player_competition_stats' => (bool) data_get($payload, 'observers.match_finished.aggregate_player_competition_stats', true),
            'simulation.observers.match_finished.apply_match_availability' => (bool) data_get($payload, 'observers.match_finished.apply_match_availability', true),
            'simulation.observers.match_finished.update_competition_after_match' => (bool) data_get($payload, 'observers.match_finished.update_competition_after_match', true),
            'simulation.observers.match_finished.settle_match_finance' => (bool) data_get($payload, 'observers.match_finished.settle_match_finance', true),
        ];

        foreach (self::TEAM_STRENGTH_AREAS as $area) {
            foreach (self::TEAM_STRENGTH_ATTRIBUTES as $attribute) {
                $settings["simulation.team_strength.weights.{$area}.{$attribute}"] = round(
                    (float) data_get(
                        $payload,
                        "team_strength.weights.{$area}.{$attribute}",
                        config("simulation.team_strength.weights.{$area}.{$attribute}", 0.0)
                    ),
                    3
                );
            }
        }

        foreach (self::MATCH_STRENGTH_ATTRIBUTES as $attribute) {
            $settings["simulation.match_strength.weights.{$attribute}"] = round(
                (float) data_get(
                    $payload,
                    "match_strength.weights.{$attribute}",
                    config("simulation.match_strength.weights.{$attribute}", 0.0)
                ),
                3
            );
        }

        foreach (self::SLOT_SCORE_BONUS_KEYS as $key) {
            $settings["simulation.lineup_scoring.slot_score_bonuses.{$key}"] = round(
                (float) data_get(
                    $payload,
                    "lineup_scoring.slot_score_bonuses.{$key}",
                    config("simulation.lineup_scoring.slot_score_bonuses.{$key}", 0.0)
                ),
                2
            );
        }

        foreach (self::LINEUP_SCORING_WEIGHT_KEYS as $key) {
            $settings["simulation.lineup_scoring.{$key}"] = round(
                (float) data_get(
                    $payload,
                    "lineup_scoring.{$key}",
                    config("simulation.lineup_scoring.{$key}", 0.0)
                ),
                2
            );
        }

        $this->persistMany($settings);
        $this->applyRuntimeOverrides();
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, array<string, mixed>> $fieldDefinitions
     */
    public function persistModuleFieldValues(array $payload, array $fieldDefinitions): void
    {
        foreach ($fieldDefinitions as $key => $field) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $value = data_get($payload, $key, $field['default'] ?? null);
            $this->set($key, $this->normalizeModuleFieldValue($value, $field));
        }
    }

    public function schedulerDefaultLimit(): int
    {
        return max(0, (int) config('simulation.scheduler.default_limit', 0));
    }

    public function schedulerDefaultMinutesPerRun(): int
    {
        return max(1, min(90, (int) config('simulation.scheduler.default_minutes_per_run', 5)));
    }

    public function maxBenchPlayers(): int
    {
        return $this->normalizeMaxBenchPlayers(config('simulation.lineup.max_bench_players', 5));
    }

    /**
     * @return array<int, string>
     */
    public function schedulerDefaultTypes(): array
    {
        return $this->normalizeTypes((array) config('simulation.scheduler.default_types', self::ALLOWED_MATCH_TYPES));
    }

    public function scheduledSimulationLastRunAt(): ?CarbonImmutable
    {
        $value = $this->get('simulation.scheduler.last_run_at');
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    public function isScheduledSimulationDue(?CarbonImmutable $at = null): bool
    {
        $at ??= CarbonImmutable::now();

        $lastRunAt = $this->scheduledSimulationLastRunAt();
        if (!$lastRunAt) {
            return true;
        }

        $intervalMinutes = max(1, (int) config('simulation.scheduler.interval_minutes', 1));
        $diffSeconds = $lastRunAt->diffInSeconds($at, false);

        return $diffSeconds >= ($intervalMinutes * 60);
    }

    public function markScheduledSimulationRun(?CarbonImmutable $at = null): void
    {
        $at ??= CarbonImmutable::now();
        $this->set('simulation.scheduler.last_run_at', $at->toIso8601String());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->loadSettingsMap();

        return $settings[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        if (!$this->settingsTableExists()) {
            return;
        }

        SimulationSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $this->encodeValue($value)]
        );

        Cache::forget(self::CACHE_KEY);
        config([$key => $value]);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function persistMany(array $settings): void
    {
        if (!$this->settingsTableExists() || $settings === []) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($settings as $key => $value) {
            $rows[] = [
                'key' => $key,
                'value' => $this->encodeValue($value),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        SimulationSetting::query()->upsert($rows, ['key'], ['value', 'updated_at']);
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadSettingsMap(): array
    {
        if (!$this->settingsTableExists()) {
            return [];
        }

        try {
            /** @var array<string, mixed> $cached */
            $cached = Cache::rememberForever(self::CACHE_KEY, fn(): array => $this->fetchSettingsFromDatabase());

            return $cached;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchSettingsFromDatabase(): array
    {
        if (!$this->settingsTableExists()) {
            return [];
        }

        return SimulationSetting::query()
            ->get(['key', 'value'])
            ->reduce(function (array $carry, SimulationSetting $setting): array {
                $carry[(string) $setting->key] = $this->decodeValue((string) $setting->value);

                return $carry;
            }, []);
    }

    private function settingsTableExists(): bool
    {
        try {
            return Schema::hasTable('simulation_settings');
        } catch (Throwable) {
            return false;
        }
    }

    private function encodeValue(mixed $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return json_encode((string) $value);
        }
    }

    private function decodeValue(string $value): mixed
    {
        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $value;
        }
    }

    /**
     * @param array<int, mixed> $values
     * @return array<int, string>
     */
    private function normalizeTypes(array $values): array
    {
        $types = array_values(array_intersect(
            self::ALLOWED_MATCH_TYPES,
            array_map(
                static fn(mixed $value): string => trim(strtolower((string) $value)),
                $values
            )
        ));

        return $types === [] ? self::ALLOWED_MATCH_TYPES : $types;
    }

    private function normalizeMaxBenchPlayers(mixed $value): int
    {
        return max(1, min(10, (int) $value));
    }

    /**
     * @param array<string, mixed> $field
     */
    private function normalizeModuleFieldValue(mixed $value, array $field): mixed
    {
        return match ((string) ($field['type'] ?? 'boolean')) {
            'integer' => max(
                (int) ($field['min'] ?? 0),
                min((int) ($field['max'] ?? 1000), (int) $value)
            ),
            'number' => max(
                (float) ($field['min'] ?? 0),
                min((float) ($field['max'] ?? 1000), (float) $value)
            ),
            'select' => in_array((string) $value, array_map('strval', (array) ($field['options'] ?? [])), true)
                ? (string) $value
                : (string) ($field['default'] ?? ''),
            'text' => mb_substr(trim((string) $value), 0, (int) ($field['max_length'] ?? 255)),
            default => (bool) $value,
        };
    }

    /**
     * @return array<string, array<string, float>>
     */
    private function adminTeamStrengthWeights(): array
    {
        $weights = [];

        foreach (self::TEAM_STRENGTH_AREAS as $area) {
            foreach (self::TEAM_STRENGTH_ATTRIBUTES as $attribute) {
                $weights[$area][$attribute] = round(
                    (float) config(
                        "simulation.team_strength.weights.{$area}.{$attribute}",
                        $attribute === 'technical'
                            ? (float) config("simulation.team_strength.weights.{$area}.attr_technical", 0.0)
                            : 0.0
                    ),
                    3
                );
            }
        }

        return $weights;
    }

    /**
     * @return array<string, float>
     */
    private function adminMatchStrengthWeights(): array
    {
        $weights = [];

        foreach (self::MATCH_STRENGTH_ATTRIBUTES as $attribute) {
            $weights[$attribute] = round(
                (float) config(
                    "simulation.match_strength.weights.{$attribute}",
                    $attribute === 'technical'
                        ? (float) config('simulation.match_strength.weights.attr_technical', 0.0)
                        : 0.0
                ),
                3
            );
        }

        return $weights;
    }
}
