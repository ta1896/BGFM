<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagedClubAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_club_is_redirected_from_club_required_pages(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('players.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status');
    }

    public function test_user_without_club_can_claim_free_club(): void
    {
        $user = User::factory()->create();
        $freeClub = Club::create([
            'user_id' => null,
            'is_cpu' => false,
            'name' => 'Freier Verein',
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 50,
            'fanbase' => 100000,
            'board_confidence' => 50,
        ]);

        $listResponse = $this->actingAs($user)->get(route('clubs.free'));
        $listResponse->assertOk();
        $listResponse->assertSee('Freier Verein');

        $claimResponse = $this->actingAs($user)->post(route('clubs.claim', $freeClub));
        $claimResponse->assertRedirect(route('dashboard', ['club' => $freeClub->id]));

        $this->assertDatabaseHas('clubs', [
            'id' => $freeClub->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_manager_routes_for_player_creation_are_not_available_anymore(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/players/create')->assertNotFound();
        $this->actingAs($user)->post('/players')->assertStatus(405);
    }

    public function test_user_with_club_can_access_players_index(): void
    {
        $user = User::factory()->create();
        Club::create([
            'user_id' => $user->id,
            'is_cpu' => false,
            'name' => 'Owned Club',
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 50,
            'fanbase' => 100000,
            'board_confidence' => 50,
        ]);

        $response = $this->actingAs($user)->get(route('players.index'));

        $response->assertOk();
    }
}
