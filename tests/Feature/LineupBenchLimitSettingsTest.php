<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Lineup;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LineupBenchLimitSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_up_to_configured_bench_limit(): void
    {
        config()->set('simulation.lineup.max_bench_players', 10);

        $user = User::factory()->create();
        $club = $this->createClub($user, 'Bench FC');
        $lineup = Lineup::create([
            'club_id' => $club->id,
            'name' => 'Standard',
            'formation' => '4-4-2',
            'is_active' => true,
        ]);

        $players = collect();
        foreach (range(1, 21) as $i) {
            $position = $i === 1 ? 'TW' : ($i <= 8 ? 'IV' : ($i <= 15 ? 'ZM' : 'ST'));
            $players->push($this->createPlayer($club, 'Player'.$i, $position, 60 + $i));
        }

        $starterSlots = [
            'TW' => $players[0]->id,
            'LV' => $players[1]->id,
            'IV-L' => $players[2]->id,
            'IV-R' => $players[3]->id,
            'RV' => $players[4]->id,
            'LM' => $players[5]->id,
            'ZM-L' => $players[6]->id,
            'ZM-R' => $players[7]->id,
            'RM' => $players[8]->id,
            'ST-L' => $players[9]->id,
            'ST-R' => $players[10]->id,
        ];
        $benchSlots = $players->slice(11, 10)->pluck('id')->values()->all();

        $response = $this->actingAs($user)->put(route('lineups.update', $lineup), [
            'name' => 'Standard',
            'formation' => '4-4-2',
            'tactical_style' => 'balanced',
            'attack_focus' => 'center',
            'starter_slots' => $starterSlots,
            'bench_slots' => $benchSlots,
            'action' => 'save',
        ]);

        $response->assertRedirect(route('lineups.show', $lineup));

        $lineup->refresh()->load('players');
        $this->assertCount(10, $lineup->players->filter(fn (Player $player): bool => (bool) $player->pivot->is_bench));
    }

    public function test_user_cannot_exceed_configured_bench_limit(): void
    {
        config()->set('simulation.lineup.max_bench_players', 3);

        $user = User::factory()->create();
        $club = $this->createClub($user, 'Strict Bench FC');
        $lineup = Lineup::create([
            'club_id' => $club->id,
            'name' => 'Strict',
            'formation' => '4-4-2',
            'is_active' => true,
        ]);

        $players = collect();
        foreach (range(1, 16) as $i) {
            $position = $i === 1 ? 'TW' : ($i <= 8 ? 'IV' : ($i <= 12 ? 'ZM' : 'ST'));
            $players->push($this->createPlayer($club, 'Strict'.$i, $position, 58 + $i));
        }

        $starterSlots = [
            'TW' => $players[0]->id,
            'LV' => $players[1]->id,
            'IV-L' => $players[2]->id,
            'IV-R' => $players[3]->id,
            'RV' => $players[4]->id,
            'LM' => $players[5]->id,
            'ZM-L' => $players[6]->id,
            'ZM-R' => $players[7]->id,
            'RM' => $players[8]->id,
            'ST-L' => $players[9]->id,
            'ST-R' => $players[10]->id,
        ];

        $response = $this->actingAs($user)->from(route('lineups.edit', $lineup))->put(route('lineups.update', $lineup), [
            'name' => 'Strict',
            'formation' => '4-4-2',
            'tactical_style' => 'balanced',
            'attack_focus' => 'center',
            'starter_slots' => $starterSlots,
            'bench_slots' => [$players[11]->id, $players[12]->id, $players[13]->id, $players[14]->id],
            'action' => 'save',
        ]);

        $response->assertRedirect(route('lineups.edit', $lineup));
        $response->assertSessionHasErrors(['starter_slots']);
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
