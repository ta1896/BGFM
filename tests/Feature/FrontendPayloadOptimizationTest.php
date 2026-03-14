<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendPayloadOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_match_live_state_endpoint_returns_expected_lineup_payload(): void
    {
        $user = User::factory()->create();
        $cpu = User::factory()->create();

        $homeClub = $this->createClub($user, 'Home FC', false);
        $awayClub = $this->createClub($cpu, 'Away FC', true);

        $match = GameMatch::create([
            'type' => 'friendly',
            'stage' => 'Friendly',
            'status' => 'live',
            'kickoff_at' => now()->subMinutes(30),
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 45678,
            'live_minute' => 24,
        ]);

        $player = $this->createPlayer($homeClub, 'Live', 'One', 'TW', 70);

        $lineup = Lineup::create([
            'club_id' => $homeClub->id,
            'match_id' => $match->id,
            'name' => 'Live XI',
            'formation' => '4-4-2',
            'is_active' => true,
        ]);

        $lineup->players()->attach($player->id, [
            'pitch_position' => 'TW',
            'sort_order' => 1,
            'is_bench' => false,
        ]);

        $response = $this->actingAs($user)->getJson(route('matches.live.state', $match));

        $response->assertOk()->assertJsonStructure([
            'id',
            'status',
            'lineups' => [
                (string) $homeClub->id => [
                    'club_id',
                    'formation',
                    'starters',
                    'bench',
                    'removed',
                ],
            ],
            'actions',
            'team_states',
            'player_states',
        ]);

        $this->assertSame('TW', $response->json('lineups.' . $homeClub->id . '.starters.0.slot'));
        $this->assertSame($player->full_name, $response->json('lineups.' . $homeClub->id . '.starters.0.name'));
    }

    public function test_training_apply_route_updates_session_and_redirects_cleanly(): void
    {
        $user = User::factory()->create();
        $club = $this->createClub($user, 'Training FC', false);
        $player = $this->createPlayer($club, 'Train', 'Able', 'ZM', 68);

        $session = TrainingSession::create([
            'club_id' => $club->id,
            'created_by_user_id' => $user->id,
            'type' => 'technical',
            'intensity' => 'medium',
            'session_date' => now()->toDateString(),
            'morale_effect' => 1,
            'stamina_effect' => -1,
            'form_effect' => 1,
        ]);

        $session->players()->attach($player->id, [
            'role' => 'participant',
            'stamina_delta' => -1,
            'morale_delta' => 1,
            'overall_delta' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('training.apply', $session));

        $response->assertRedirect(route('training.index'));
        $this->assertNotNull($session->fresh()->applied_at);
    }

    public function test_friendlies_index_loads_for_managed_club(): void
    {
        $user = User::factory()->create();
        $cpu = User::factory()->create();

        $club = $this->createClub($user, 'Friendly FC', false);
        $cpuClub = $this->createClub($cpu, 'CPU Opponent', true);

        GameMatch::create([
            'type' => 'friendly',
            'stage' => 'Friendly',
            'status' => 'scheduled',
            'kickoff_at' => now()->addDay(),
            'home_club_id' => $club->id,
            'away_club_id' => $cpuClub->id,
            'stadium_club_id' => $club->id,
            'simulation_seed' => 11223,
        ]);

        $response = $this->actingAs($user)->get(route('friendlies.index'));

        $response->assertOk();
        $response->assertSee('Freundschaftsspiele');
        $response->assertSee('Friendly FC');
    }

    private function createClub(User $user, string $name, bool $isCpu): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'is_cpu' => $isCpu,
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

    private function createPlayer(Club $club, string $firstName, string $lastName, string $position, int $overall): Player
    {
        return Player::create([
            'club_id' => $club->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'position' => $position,
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => $overall,
            'potential' => min(99, $overall + 5),
            'pace' => 66,
            'shooting' => 66,
            'passing' => 66,
            'defending' => 66,
            'physical' => 66,
            'stamina' => 70,
            'morale' => 65,
            'status' => 'active',
            'market_value' => 300000,
            'salary' => 10000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ]);
    }
}
