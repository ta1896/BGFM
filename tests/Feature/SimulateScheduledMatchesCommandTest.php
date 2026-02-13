<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\SimulationSchedulerRun;
use App\Models\User;
use App\Services\LiveMatchTickerService;
use App\Services\SimulationSettingsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class SimulateScheduledMatchesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_respects_limit_and_due_date(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');

        $homeClub = $this->createClub('Home Club');
        $awayClub = $this->createClub('Away Club');
        $this->createPlayer($homeClub, 'Home');
        $this->createPlayer($awayClub, 'Away');

        $first = $this->createMatch($homeClub, $awayClub, 'league', now()->subHours(3), 11111);
        $second = $this->createMatch($homeClub, $awayClub, 'cup', now()->subHours(2), 22222);
        $third = $this->createMatch($homeClub, $awayClub, 'friendly', now()->subHour(), 33333);
        $future = $this->createMatch($homeClub, $awayClub, 'league', now()->addHour(), 44444);

        Artisan::call('game:simulate-matches', ['--limit' => 2, '--minutes-per-run' => 5]);

        $this->assertDatabaseHas('matches', ['id' => $first->id, 'status' => 'live', 'live_minute' => 5]);
        $this->assertDatabaseHas('matches', ['id' => $second->id, 'status' => 'live', 'live_minute' => 5]);
        $this->assertDatabaseHas('matches', ['id' => $third->id, 'status' => 'scheduled']);
        $this->assertDatabaseHas('matches', ['id' => $future->id, 'status' => 'scheduled']);
        $this->assertSame(2, GameMatch::query()->where('status', 'live')->count());
        $this->assertNull(GameMatch::query()->find($first->id)?->live_processing_token);
        $this->assertNotNull(GameMatch::query()->find($first->id)?->live_processing_last_run_at);
        $this->assertSame(1, (int) (GameMatch::query()->find($first->id)?->live_processing_attempts ?? 0));

        $run = $this->latestSchedulerRun();
        $this->assertNotNull($run);
        $this->assertSame('completed', (string) $run->status);
        $this->assertSame(2, (int) $run->candidate_matches);
        $this->assertSame(2, (int) $run->claimed_matches);
        $this->assertSame(2, (int) $run->processed_matches);
        $this->assertSame(0, (int) $run->failed_matches);
        $this->assertSame(['friendly', 'league', 'cup'], (array) $run->requested_types);

        Carbon::setTestNow();
    }

    public function test_command_can_filter_match_types(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');

        $homeClub = $this->createClub('Type Home');
        $awayClub = $this->createClub('Type Away');
        $this->createPlayer($homeClub, 'TypeHome');
        $this->createPlayer($awayClub, 'TypeAway');

        $friendly = $this->createMatch($homeClub, $awayClub, 'friendly', now()->subHours(2), 55555);
        $league = $this->createMatch($homeClub, $awayClub, 'league', now()->subHour(), 66666);
        $cup = $this->createMatch($homeClub, $awayClub, 'cup', now()->subMinutes(30), 77777);

        Artisan::call('game:simulate-matches', ['--limit' => 10, '--types' => 'friendly,cup', '--minutes-per-run' => 5]);

        $this->assertDatabaseHas('matches', ['id' => $friendly->id, 'status' => 'live', 'live_minute' => 5]);
        $this->assertDatabaseHas('matches', ['id' => $cup->id, 'status' => 'live', 'live_minute' => 5]);
        $this->assertDatabaseHas('matches', ['id' => $league->id, 'status' => 'scheduled']);

        Carbon::setTestNow();
    }

    public function test_command_pauses_match_when_simulation_errors_occur(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');

        $homeClub = $this->createClub('Error Home');
        $awayClub = $this->createClub('Error Away');
        $this->createPlayer($homeClub, 'ErrorH');
        $this->createPlayer($awayClub, 'ErrorA');

        $match = $this->createMatch($homeClub, $awayClub, 'friendly', now()->subHour(), 88888);

        $mock = Mockery::mock(LiveMatchTickerService::class);
        $mock->shouldReceive('tick')
            ->once()
            ->with(Mockery::type(GameMatch::class), 5)
            ->andThrow(new \RuntimeException('Sim failed'));
        $this->app->instance(LiveMatchTickerService::class, $mock);

        Artisan::call('game:simulate-matches', ['--limit' => 1, '--minutes-per-run' => 5, '--force' => true]);

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'status' => 'live',
            'live_paused' => 1,
            'live_processing_token' => null,
            'live_processing_started_at' => null,
        ]);
        $match->refresh();
        $this->assertStringContainsString('Sim failed', (string) $match->live_error_message);
        $this->assertStringContainsString('Sim failed', (string) $match->live_processing_last_error);
        $this->assertNotNull($match->live_processing_last_run_at);
        $this->assertSame(1, (int) $match->live_processing_attempts);

        $run = $this->latestSchedulerRun();
        $this->assertNotNull($run);
        $this->assertSame('completed_with_errors', (string) $run->status);
        $this->assertSame(1, (int) $run->failed_matches);
        $this->assertSame(0, (int) $run->processed_matches);

        Carbon::setTestNow();
    }

    public function test_command_skips_active_claim_and_processes_stale_claim(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');

        config()->set('simulation.scheduler.claim_stale_after_seconds', 180);

        $homeClub = $this->createClub('Claim Home');
        $awayClub = $this->createClub('Claim Away');
        $this->createPlayer($homeClub, 'ClaimH');
        $this->createPlayer($awayClub, 'ClaimA');

        $match = $this->createMatch($homeClub, $awayClub, 'league', now()->subHour(), 99999);

        $match->update([
            'live_processing_token' => 'foreign-runner',
            'live_processing_started_at' => now(),
        ]);

        Artisan::call('game:simulate-matches', ['--limit' => 1, '--minutes-per-run' => 5, '--force' => true]);

        $match->refresh();
        $this->assertSame('scheduled', (string) $match->status);
        $this->assertSame(0, (int) $match->live_minute);
        $this->assertSame('foreign-runner', (string) $match->live_processing_token);

        $firstRun = $this->latestSchedulerRun();
        $this->assertNotNull($firstRun);
        $this->assertSame('completed', (string) $firstRun->status);
        $this->assertSame(0, (int) $firstRun->candidate_matches);
        $this->assertSame(0, (int) $firstRun->skipped_active_claims);
        $this->assertSame(0, (int) $firstRun->processed_matches);

        $match->update([
            'live_processing_started_at' => now()->subMinutes(10),
        ]);

        Artisan::call('game:simulate-matches', ['--limit' => 1, '--minutes-per-run' => 5, '--force' => true]);

        $match->refresh();
        $this->assertSame('live', (string) $match->status);
        $this->assertSame(5, (int) $match->live_minute);
        $this->assertNull($match->live_processing_token);
        $this->assertNull($match->live_processing_started_at);
        $this->assertNotNull($match->live_processing_last_run_at);
        $this->assertSame(1, (int) $match->live_processing_attempts);

        $secondRun = $this->latestSchedulerRun();
        $this->assertNotNull($secondRun);
        $this->assertSame('completed', (string) $secondRun->status);
        $this->assertSame(1, (int) $secondRun->stale_claim_takeovers);
        $this->assertSame(1, (int) $secondRun->processed_matches);

        Carbon::setTestNow();
    }

    public function test_command_uses_persisted_defaults_and_scheduler_interval_guard(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');

        $this->setSimulationSetting('simulation.scheduler.interval_minutes', 15);
        $this->setSimulationSetting('simulation.scheduler.default_limit', 1);
        $this->setSimulationSetting('simulation.scheduler.default_minutes_per_run', 7);
        $this->setSimulationSetting('simulation.scheduler.default_types', ['league']);

        $homeClub = $this->createClub('Defaults Home');
        $awayClub = $this->createClub('Defaults Away');
        $this->createPlayer($homeClub, 'DefaultsH');
        $this->createPlayer($awayClub, 'DefaultsA');

        $match = $this->createMatch($homeClub, $awayClub, 'league', now()->subHour(), 12121);

        Artisan::call('game:simulate-matches');

        $match->refresh();
        $this->assertSame('live', (string) $match->status);
        $this->assertSame(7, (int) $match->live_minute);

        Artisan::call('game:simulate-matches');

        $match->refresh();
        $this->assertSame(7, (int) $match->live_minute);
        $skippedRun = $this->latestSchedulerRun();
        $this->assertNotNull($skippedRun);
        $this->assertSame('skipped_interval', (string) $skippedRun->status);

        Carbon::setTestNow('2026-02-12 12:16:00');

        Artisan::call('game:simulate-matches');

        $match->refresh();
        $this->assertSame(14, (int) $match->live_minute);

        Carbon::setTestNow();
    }

    public function test_command_skips_run_when_global_runner_lock_is_active(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');
        config()->set('simulation.scheduler.runner_lock_seconds', 120);

        $homeClub = $this->createClub('RunnerLock Home');
        $awayClub = $this->createClub('RunnerLock Away');
        $this->createPlayer($homeClub, 'RunnerLockH');
        $this->createPlayer($awayClub, 'RunnerLockA');

        $match = $this->createMatch($homeClub, $awayClub, 'league', now()->subHour(), 45454);

        $lock = Cache::lock('simulation:scheduler:runner', 120);
        $this->assertTrue($lock->get());

        try {
            Artisan::call('game:simulate-matches', ['--force' => true, '--limit' => 1, '--minutes-per-run' => 5]);
        } finally {
            $lock->release();
        }

        $match->refresh();
        $this->assertSame('scheduled', (string) $match->status);
        $this->assertSame(0, (int) $match->live_minute);

        $run = $this->latestSchedulerRun();
        $this->assertNotNull($run);
        $this->assertSame('skipped_locked', (string) $run->status);
    }

    public function test_command_handles_high_volume_matches_without_duplicate_processing(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');

        $homeClub = $this->createClub('Volume Home');
        $awayClub = $this->createClub('Volume Away');
        $this->createPlayer($homeClub, 'VolumeH');
        $this->createPlayer($awayClub, 'VolumeA');

        foreach (range(1, 24) as $index) {
            $this->createMatch($homeClub, $awayClub, 'league', now()->subMinutes(90 - $index), 50000 + $index);
        }

        Artisan::call('game:simulate-matches', ['--force' => true, '--limit' => 0, '--minutes-per-run' => 1]);

        $this->assertSame(24, GameMatch::query()->where('status', 'live')->count());
        $this->assertSame(24, GameMatch::query()->where('live_minute', 1)->count());
        $this->assertSame(24, GameMatch::query()->where('live_processing_attempts', 1)->count());

        $run = $this->latestSchedulerRun();
        $this->assertNotNull($run);
        $this->assertSame('completed', (string) $run->status);
        $this->assertSame(24, (int) $run->candidate_matches);
        $this->assertSame(24, (int) $run->claimed_matches);
        $this->assertSame(24, (int) $run->processed_matches);
        $this->assertSame(0, (int) $run->failed_matches);

        Carbon::setTestNow();
    }

    public function test_command_marks_stale_running_scheduler_runs_as_abandoned_before_new_run(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');
        config()->set('simulation.scheduler.health.running_stale_after_seconds', 120);

        $staleRun = $this->createSchedulerRun([
            'status' => 'running',
            'started_at' => now()->subMinutes(10),
        ]);

        $homeClub = $this->createClub('Recovery Home');
        $awayClub = $this->createClub('Recovery Away');
        $this->createPlayer($homeClub, 'RecoveryH');
        $this->createPlayer($awayClub, 'RecoveryA');
        $this->createMatch($homeClub, $awayClub, 'league', now()->subHour(), 81818);

        Artisan::call('game:simulate-matches', ['--force' => true, '--limit' => 1, '--minutes-per-run' => 5]);

        $staleRun->refresh();
        $this->assertSame('abandoned', (string) $staleRun->status);
        $this->assertNotNull($staleRun->finished_at);

        $newRun = $this->latestSchedulerRun();
        $this->assertNotNull($newRun);
        $this->assertNotSame($staleRun->id, $newRun->id);
        $this->assertStringContainsString('Recovered stale runs: 1', (string) $newRun->message);

        Carbon::setTestNow();
    }

    public function test_simulation_health_strict_mode_returns_failure_when_thresholds_are_exceeded(): void
    {
        $this->createSchedulerRun([
            'status' => 'failed',
            'failed_matches' => 2,
            'started_at' => now()->subMinute(),
            'finished_at' => now()->subSeconds(30),
        ]);
        $this->createSchedulerRun([
            'status' => 'abandoned',
            'started_at' => now()->subMinutes(5),
            'finished_at' => now()->subMinutes(4),
        ]);

        config()->set('simulation.scheduler.health.failed_runs_alert_threshold', 0);
        config()->set('simulation.scheduler.health.abandoned_runs_alert_threshold', 0);

        $exitCode = Artisan::call('game:simulation-health', ['--limit' => 20, '--strict' => true]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Strict mode', Artisan::output());
    }

    public function test_simulation_health_check_logs_error_and_returns_failure_in_strict_mode(): void
    {
        $this->createSchedulerRun([
            'status' => 'failed',
            'failed_matches' => 1,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
        ]);

        config()->set('simulation.scheduler.health.failed_runs_alert_threshold', 0);
        config()->set('simulation.scheduler.health.check_limit', 20);
        config()->set('simulation.scheduler.health.check_strict', true);

        Log::spy();

        $exitCode = Artisan::call('game:simulation-health-check');

        $this->assertSame(1, $exitCode);
        Log::shouldHaveReceived('error')->once();
    }

    public function test_simulation_health_check_can_log_success_when_enabled(): void
    {
        $this->createSchedulerRun([
            'status' => 'completed',
            'processed_matches' => 3,
            'failed_matches' => 0,
            'stale_claim_takeovers' => 0,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
        ]);

        config()->set('simulation.scheduler.health.failed_runs_alert_threshold', 0);
        config()->set('simulation.scheduler.health.skipped_locked_alert_threshold', 1);
        config()->set('simulation.scheduler.health.stale_takeovers_alert_threshold', 0);
        config()->set('simulation.scheduler.health.abandoned_runs_alert_threshold', 0);
        config()->set('simulation.scheduler.health.check_strict', true);
        config()->set('simulation.scheduler.health.log_success', true);

        Log::spy();

        $exitCode = Artisan::call('game:simulation-health-check', ['--limit' => 20]);

        $this->assertSame(0, $exitCode);
        Log::shouldHaveReceived('info')->once();
    }

    private function createClub(string $name): Club
    {
        $user = User::factory()->create();

        return Club::create([
            'user_id' => $user->id,
            'name' => $name,
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
        ]);
    }

    private function createPlayer(Club $club, string $name): Player
    {
        return Player::create([
            'club_id' => $club->id,
            'first_name' => $name,
            'last_name' => 'Player',
            'position' => 'ZM',
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => 67,
            'potential' => 72,
            'pace' => 66,
            'shooting' => 66,
            'passing' => 66,
            'defending' => 66,
            'physical' => 66,
            'stamina' => 74,
            'morale' => 70,
            'status' => 'active',
            'market_value' => 150000,
            'salary' => 9000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ]);
    }

    private function createMatch(Club $homeClub, Club $awayClub, string $type, Carbon $kickoffAt, int $seed): GameMatch
    {
        return GameMatch::create([
            'type' => $type,
            'stage' => ucfirst($type),
            'kickoff_at' => $kickoffAt,
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => $seed,
        ]);
    }

    private function setSimulationSetting(string $key, mixed $value): void
    {
        $this->app->make(SimulationSettingsService::class)->set($key, $value);
    }

    private function latestSchedulerRun(): ?SimulationSchedulerRun
    {
        return SimulationSchedulerRun::query()->orderByDesc('id')->first();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createSchedulerRun(array $overrides = []): SimulationSchedulerRun
    {
        return SimulationSchedulerRun::query()->create(array_merge([
            'run_token' => (string) \Illuminate\Support\Str::uuid(),
            'status' => 'completed',
            'trigger' => 'scheduled',
            'forced' => false,
            'requested_limit' => 0,
            'requested_minutes_per_run' => 5,
            'requested_types' => ['friendly', 'league', 'cup'],
            'runner_lock_seconds' => 120,
            'candidate_matches' => 0,
            'claimed_matches' => 0,
            'processed_matches' => 0,
            'failed_matches' => 0,
            'skipped_active_claims' => 0,
            'skipped_unclaimable' => 0,
            'stale_claim_takeovers' => 0,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'message' => null,
        ], $overrides));
    }
}
