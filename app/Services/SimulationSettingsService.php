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
            'simulation.observers.match_finished.enabled' => (bool) data_get($payload, 'observers.match_finished.enabled', true),
            'simulation.observers.match_finished.rebuild_match_player_stats' => (bool) data_get($payload, 'observers.match_finished.rebuild_match_player_stats', true),
            'simulation.observers.match_finished.aggregate_player_competition_stats' => (bool) data_get($payload, 'observers.match_finished.aggregate_player_competition_stats', true),
            'simulation.observers.match_finished.apply_match_availability' => (bool) data_get($payload, 'observers.match_finished.apply_match_availability', true),
            'simulation.observers.match_finished.update_competition_after_match' => (bool) data_get($payload, 'observers.match_finished.update_competition_after_match', true),
            'simulation.observers.match_finished.settle_match_finance' => (bool) data_get($payload, 'observers.match_finished.settle_match_finance', true),
        ];

        $this->persistMany($settings);
        $this->applyRuntimeOverrides();
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
}
