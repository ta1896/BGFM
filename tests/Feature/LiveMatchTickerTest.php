<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LiveMatchTickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cron_advances_live_match_and_user_can_intervene_with_tactics_and_substitutions(): void
    {
        $user = User::factory()->create();
        $opponent = User::factory()->create();

        $homeClub = $this->createClub($user, 'Live Home');
        $awayClub = $this->createClub($opponent, 'Live Away');
        $this->createSquad($homeClub, 'Home');
        $this->createSquad($awayClub, 'Away');

        $match = GameMatch::create([
            'type' => 'friendly',
            'stage' => 'Friendly',
            'kickoff_at' => now()->subHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 91111,
        ]);

        Artisan::call('game:simulate-matches', [
            '--limit' => 0,
            '--minutes-per-run' => 5,
            '--types' => 'friendly',
        ]);

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'status' => 'live',
            'live_minute' => 5,
            'live_paused' => 0,
        ]);

        $this->actingAs($user)
            ->postJson(route('matches.live.style', $match), [
                'club_id' => $homeClub->id,
                'tactical_style' => 'offensive',
            ])
            ->assertOk();

        $this->assertDatabaseHas('lineups', [
            'club_id' => $homeClub->id,
            'match_id' => $match->id,
            'tactical_style' => 'offensive',
        ]);

        $stateResponse = $this->actingAs($user)
            ->getJson(route('matches.live.state', $match))
            ->assertOk();
        $stateResponse->assertJsonStructure([
            'team_states',
            'player_states',
            'actions',
        ]);
        $this->assertNotEmpty($stateResponse->json('team_states'));
        $this->assertNotEmpty($stateResponse->json('actions'));

        $homeLineup = $stateResponse->json('lineups')[(string) $homeClub->id] ?? null;
        $this->assertNotNull($homeLineup);
        $starterOut = collect($homeLineup['starters'] ?? [])->first(fn (array $p): bool => $p['slot'] !== 'TW');
        $benchIn = collect($homeLineup['bench'] ?? [])->first(fn (array $p): bool => strtoupper((string) $p['position']) !== 'TW');
        $this->assertNotNull($starterOut);
        $this->assertNotNull($benchIn);

        $subResponse = $this->actingAs($user)
            ->postJson(route('matches.live.substitute', $match), [
                'club_id' => $homeClub->id,
                'player_out_id' => $starterOut['id'],
                'player_in_id' => $benchIn['id'],
                'target_slot' => 'TW',
            ])
            ->assertOk();

        $lineupsAfterSub = $subResponse->json('lineups');
        $homeLineupAfterSub = $lineupsAfterSub[(string) $homeClub->id] ?? [];
        $incomingStarter = collect($homeLineupAfterSub['starters'] ?? [])
            ->first(fn (array $p): bool => (int) $p['id'] === (int) $benchIn['id']);
        $this->assertNotNull($incomingStarter);
        $this->assertSame('TW', strtoupper((string) $incomingStarter['slot']));
        $this->assertLessThan(1.0, (float) $incomingStarter['fit_factor']);
        $this->assertDatabaseHas('match_events', [
            'match_id' => $match->id,
            'club_id' => $homeClub->id,
            'event_type' => 'substitution',
        ]);

        $match->update([
            'live_paused' => true,
            'live_error_message' => 'Simulation error',
        ]);

        $this->actingAs($user)
            ->postJson(route('matches.live.resume', $match))
            ->assertOk()
            ->assertJsonPath('live_paused', false);

        Artisan::call('game:simulate-matches', [
            '--limit' => 0,
            '--minutes-per-run' => 90,
            '--types' => 'friendly',
        ]);

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'status' => 'played',
            'live_minute' => 90,
        ]);
        $this->assertGreaterThan(0, Lineup::where('match_id', $match->id)->count());
    }

    public function test_user_can_plan_live_substitution_via_matchcenter_endpoint(): void
    {
        $user = User::factory()->create();
        $opponent = User::factory()->create();

        $homeClub = $this->createClub($user, 'Plan Home');
        $awayClub = $this->createClub($opponent, 'Plan Away');
        $this->createSquad($homeClub, 'PH');
        $this->createSquad($awayClub, 'PA');

        $match = GameMatch::create([
            'type' => 'friendly',
            'stage' => 'Friendly',
            'kickoff_at' => now()->subHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 92222,
        ]);

        Artisan::call('game:simulate-matches', [
            '--limit' => 0,
            '--minutes-per-run' => 5,
            '--types' => 'friendly',
        ]);

        $state = $this->actingAs($user)
            ->getJson(route('matches.live.state', $match))
            ->assertOk()
            ->json();

        $homeLineup = $state['lineups'][(string) $homeClub->id] ?? null;
        $this->assertNotNull($homeLineup);

        $starterOut = collect($homeLineup['starters'] ?? [])->first(fn (array $p): bool => strtoupper((string) $p['position']) !== 'TW');
        $benchIn = collect($homeLineup['bench'] ?? [])->first(fn (array $p): bool => strtoupper((string) $p['position']) !== 'TW');
        $this->assertNotNull($starterOut);
        $this->assertNotNull($benchIn);

        $plannedMinute = (int) ($state['live_minute'] ?? 0) + 2;

        $response = $this->actingAs($user)
            ->postJson(route('matches.live.substitute.plan', $match), [
                'club_id' => $homeClub->id,
                'player_out_id' => $starterOut['id'],
                'player_in_id' => $benchIn['id'],
                'planned_minute' => $plannedMinute,
                'score_condition' => 'any',
                'target_slot' => $starterOut['slot'],
            ])
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'planned_substitutions',
            ]);

        $this->assertNotEmpty($response->json('planned_substitutions'));
        $this->assertDatabaseHas('match_planned_substitutions', [
            'match_id' => $match->id,
            'club_id' => $homeClub->id,
            'player_out_id' => $starterOut['id'],
            'player_in_id' => $benchIn['id'],
            'planned_minute' => $plannedMinute,
            'score_condition' => 'any',
            'status' => 'pending',
        ]);
    }

    private function createClub(User $user, string $name): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'is_cpu' => false,
            'name' => $name,
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 60,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
        ]);
    }

    private function createSquad(Club $club, string $prefix): void
    {
        $positions = [
            'TW', 'LV', 'IV', 'IV', 'RV', 'LM', 'ZM', 'ZM', 'RM', 'ST', 'ST',
            'ZM', 'ST', 'LV', 'RM', 'ST',
        ];

        foreach ($positions as $index => $position) {
            $this->createPlayer($club, $prefix.($index + 1), $position, 75 - $index);
        }
    }

    private function createPlayer(Club $club, string $name, string $position, int $overall): Player
    {
        return Player::create([
            'club_id' => $club->id,
            'first_name' => $name,
            'last_name' => 'Player',
            'position' => $position,
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => $overall,
            'potential' => min(99, $overall + 5),
            'pace' => 68,
            'shooting' => 67,
            'passing' => 68,
            'defending' => 66,
            'physical' => 67,
            'stamina' => 74,
            'morale' => 72,
            'status' => 'active',
            'market_value' => 250000,
            'salary' => 9000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ]);
    }
}
