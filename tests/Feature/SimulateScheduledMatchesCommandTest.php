<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\User;
use App\Services\LiveMatchTickerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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

        Artisan::call('game:simulate-matches', ['--limit' => 1, '--minutes-per-run' => 5]);

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'status' => 'live',
            'live_paused' => 1,
        ]);
        $this->assertStringContainsString('Sim failed', (string) GameMatch::query()->find($match->id)?->live_error_message);

        Carbon::setTestNow();
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
}
