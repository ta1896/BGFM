<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubTeamRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_captain_and_vice_captain_roles(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create(['is_admin' => false]);
        $club = $this->createClub($owner, 'Role Club');
        $captain = $this->createPlayer($club, 'Captain');
        $viceCaptain = $this->createPlayer($club, 'Vice');

        $response = $this
            ->actingAs($admin)
            ->put(route('admin.clubs.update', $club), array_merge(
                $this->clubPayload($club, [
                    'captain_player_id' => $captain->id,
                    'vice_captain_player_id' => $viceCaptain->id,
                ]),
                ['user_id' => $owner->id]
            ));

        $response
            ->assertRedirect(route('admin.clubs.edit', $club))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('clubs', [
            'id' => $club->id,
            'captain_player_id' => $captain->id,
            'vice_captain_player_id' => $viceCaptain->id,
        ]);
    }

    public function test_admin_cannot_assign_role_player_from_other_club(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create(['is_admin' => false]);
        $club = $this->createClub($owner, 'Role Home');
        $otherClub = $this->createClub(User::factory()->create(), 'Role Away');

        $captain = $this->createPlayer($club, 'Home Captain');
        $foreignVice = $this->createPlayer($otherClub, 'Away Vice');

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.clubs.edit', $club))
            ->put(route('admin.clubs.update', $club), array_merge(
                $this->clubPayload($club, [
                    'captain_player_id' => $captain->id,
                    'vice_captain_player_id' => $foreignVice->id,
                ]),
                ['user_id' => $owner->id]
            ));

        $response
            ->assertRedirect(route('admin.clubs.edit', $club))
            ->assertSessionHasErrors(['vice_captain_player_id']);

        $club->refresh();
        $this->assertNull($club->vice_captain_player_id);
    }

    public function test_admin_can_set_roles_in_acp_for_existing_club_players(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create(['is_admin' => false]);
        $club = $this->createClub($owner, 'ACP Role Club');
        $captain = $this->createPlayer($club, 'ACP Captain');
        $viceCaptain = $this->createPlayer($club, 'ACP Vice');

        $response = $this
            ->actingAs($admin)
            ->put(route('admin.clubs.update', $club), array_merge(
                $this->clubPayload($club, [
                    'captain_player_id' => $captain->id,
                    'vice_captain_player_id' => $viceCaptain->id,
                ]),
                ['user_id' => $owner->id]
            ));

        $response
            ->assertRedirect(route('admin.clubs.edit', $club))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('clubs', [
            'id' => $club->id,
            'captain_player_id' => $captain->id,
            'vice_captain_player_id' => $viceCaptain->id,
        ]);
    }

    public function test_manager_club_profile_displays_team_roles(): void
    {
        $manager = User::factory()->create(['is_admin' => false]);
        $club = $this->createClub($manager, 'Role Profile');
        $captain = $this->createPlayer($club, 'Profile Captain');
        $viceCaptain = $this->createPlayer($club, 'Profile Vice');

        $club->update([
            'captain_player_id' => $captain->id,
            'vice_captain_player_id' => $viceCaptain->id,
        ]);

        $response = $this
            ->actingAs($manager)
            ->get(route('clubs.show', $club));

        $response
            ->assertOk()
            ->assertSee('Profile Captain Player')
            ->assertSee('Profile Vice Player');
    }

    private function createClub(User $user, string $name): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'name' => $name,
            'country' => 'Deutschland',
            'league' => 'Role League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
            'training_level' => 1,
            'season_objective' => 'mid_table',
        ]);
    }

    private function createPlayer(Club $club, string $name): Player
    {
        return Player::create([
            'club_id' => $club->id,
            'first_name' => $name,
            'last_name' => 'Player',
            'position' => 'ZM',
            'position_main' => 'ZM',
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => 70,
            'potential' => 75,
            'pace' => 68,
            'shooting' => 67,
            'passing' => 70,
            'defending' => 65,
            'physical' => 66,
            'stamina' => 74,
            'morale' => 70,
            'status' => 'active',
            'market_value' => 200000,
            'salary' => 9000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function clubPayload(Club $club, array $overrides = []): array
    {
        return array_merge([
            'name' => $club->name,
            'short_name' => $club->short_name,
            'country' => $club->country,
            'league' => $club->league,
            'founded_year' => $club->founded_year,
            'budget' => (float) $club->budget,
            'wage_budget' => (float) $club->wage_budget,
            'reputation' => $club->reputation,
            'fan_mood' => $club->fan_mood,
            'season_objective' => $club->season_objective,
            'notes' => $club->notes,
        ], $overrides);
    }
}
