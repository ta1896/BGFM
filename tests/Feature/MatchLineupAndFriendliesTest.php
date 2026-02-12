<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchLineupAndFriendliesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_cpu_friendly_is_auto_accepted_and_scheduled(): void
    {
        $user = User::factory()->create();
        $cpuUser = User::factory()->create();

        $club = $this->createClub($user, 'User FC', false);
        $cpuClub = $this->createClub($cpuUser, 'CPU FC', true);

        $response = $this->actingAs($user)->post(route('friendlies.store'), [
            'club_id' => $club->id,
            'opponent_club_id' => $cpuClub->id,
            'kickoff_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'message' => 'Testspiel',
        ]);

        $response->assertRedirect(route('friendlies.index', ['club' => $club->id]));
        $this->assertDatabaseHas('friendly_match_requests', [
            'challenger_club_id' => $club->id,
            'challenged_club_id' => $cpuClub->id,
            'status' => 'auto_accepted',
        ]);
        $this->assertDatabaseHas('matches', [
            'home_club_id' => $club->id,
            'away_club_id' => $cpuClub->id,
            'type' => 'friendly',
            'status' => 'scheduled',
        ]);
    }

    public function test_user_can_save_match_lineup_and_template(): void
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $cpuUser = User::factory()->create();

        $club = $this->createClub($user, 'Planner FC', false);
        $awayClub = $this->createClub($cpuUser, 'Opp FC', true);

        $match = GameMatch::create([
            'type' => 'friendly',
            'stage' => 'Friendly',
            'kickoff_at' => now()->addDay(),
            'status' => 'scheduled',
            'home_club_id' => $club->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $club->id,
            'simulation_seed' => 11111,
        ]);

        $players = collect();
        foreach (range(1, 16) as $i) {
            $position = $i === 1 ? 'GK' : ($i <= 6 ? 'DEF' : ($i <= 12 ? 'MID' : 'FWD'));
            $players->push($this->createPlayer($club, 'P'.$i, $position, 62 + $i));
        }

        $starterSlots = [
            'TW' => $players[0]->id,
            'LV' => $players[1]->id,
            'IV-L' => $players[2]->id,
            'IV-R' => $players[3]->id,
            'RV' => $players[4]->id,
            'LM' => $players[6]->id,
            'ZM-L' => $players[7]->id,
            'ZM-R' => $players[8]->id,
            'RM' => $players[9]->id,
            'ST-L' => $players[13]->id,
            'ST-R' => $players[14]->id,
        ];

        $payload = [
            'club_id' => $club->id,
            'formation' => '4-4-2',
            'tactical_style' => 'balanced',
            'attack_focus' => 'center',
            'captain_player_id' => $players[7]->id,
            'starter_slots' => $starterSlots,
            'bench_slots' => [$players[5]->id, $players[10]->id, $players[11]->id, $players[12]->id, $players[15]->id],
            'action' => 'save_match',
        ];

        $response = $this->actingAs($user)->post(route('matches.lineup.update', ['match' => $match->id, 'club' => $club->id]), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('lineups', [
            'club_id' => $club->id,
            'match_id' => $match->id,
            'is_template' => 0,
            'formation' => '4-4-2',
        ]);

        $saveTemplatePayload = $payload;
        $saveTemplatePayload['action'] = 'save_template';
        $saveTemplatePayload['template_name'] = 'FS 4-4-2';
        $this->actingAs($user)->post(route('matches.lineup.update', ['match' => $match->id, 'club' => $club->id]), $saveTemplatePayload)
            ->assertRedirect();

        $this->assertDatabaseHas('lineups', [
            'club_id' => $club->id,
            'match_id' => null,
            'name' => 'FS 4-4-2',
            'is_template' => 1,
        ]);
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

    private function createPlayer(Club $club, string $name, string $position, int $overall): Player
    {
        return Player::create([
            'club_id' => $club->id,
            'first_name' => $name,
            'last_name' => 'Test',
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
