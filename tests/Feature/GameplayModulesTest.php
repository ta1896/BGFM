<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\TrainingSession;
use App\Models\TransferBid;
use App\Models\TransferListing;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameplayModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_simulate_match_in_matchcenter(): void
    {
        $user = User::factory()->create();
        $opponent = User::factory()->create();

        $homeClub = $this->createClub($user, 'Home Club');
        $awayClub = $this->createClub($opponent, 'Away Club');

        $this->createPlayer($homeClub, 'Home');
        $this->createPlayer($awayClub, 'Away');

        $match = GameMatch::create([
            'type' => 'friendly',
            'kickoff_at' => now()->addHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'simulation_seed' => 12345,
        ]);

        $response = $this->actingAs($user)->post(route('matches.simulate', $match));

        $response->assertRedirect(route('matches.show', $match));
        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'status' => 'played',
        ]);
        $this->assertGreaterThan(0, $match->fresh()->events()->count());
    }

    public function test_transfer_acceptance_moves_player_and_updates_listing(): void
    {
        Carbon::setTestNow('2026-07-10 12:00:00');

        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $sellerClub = $this->createClub($seller, 'Seller Club', 500000);
        $buyerClub = $this->createClub($buyer, 'Buyer Club', 700000);
        $player = $this->createPlayer($sellerClub, 'Transfer');

        $listing = TransferListing::create([
            'player_id' => $player->id,
            'seller_club_id' => $sellerClub->id,
            'listed_by_user_id' => $seller->id,
            'min_price' => 150000,
            'listed_at' => now(),
            'expires_at' => now()->addDay(),
            'status' => 'open',
        ]);

        $bid = TransferBid::create([
            'transfer_listing_id' => $listing->id,
            'bidder_club_id' => $buyerClub->id,
            'bidder_user_id' => $buyer->id,
            'amount' => 170000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($seller)->post(route('transfers.bids.accept', [
            'listing' => $listing,
            'bid' => $bid,
        ]));

        $response->assertRedirect(route('transfers.index'));
        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'club_id' => $buyerClub->id,
        ]);
        $this->assertDatabaseHas('transfer_listings', [
            'id' => $listing->id,
            'status' => 'sold',
        ]);

        Carbon::setTestNow();
    }

    public function test_training_apply_changes_player_stats(): void
    {
        $user = User::factory()->create();
        $club = $this->createClub($user, 'Training Club');
        $player = $this->createPlayer($club, 'Trainee');

        $session = TrainingSession::create([
            'club_id' => $club->id,
            'created_by_user_id' => $user->id,
            'type' => 'recovery',
            'intensity' => 'low',
            'session_date' => now()->toDateString(),
            'morale_effect' => 2,
            'stamina_effect' => 1,
            'form_effect' => 0,
        ]);

        $session->players()->sync([
            $player->id => [
                'role' => 'participant',
                'stamina_delta' => 1,
                'morale_delta' => 2,
                'overall_delta' => 0,
            ],
        ]);

        $response = $this->actingAs($user)->post(route('training.apply', $session));

        $response->assertRedirect(route('training.index'));
        $this->assertDatabaseHas('training_sessions', [
            'id' => $session->id,
            'is_applied' => true,
        ]);
        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'stamina' => 81,
            'morale' => 52,
        ]);
    }

    private function createClub(User $user, string $name, float $budget = 500000): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'name' => $name,
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => $budget,
            'wage_budget' => 250000,
            'reputation' => 55,
            'fan_mood' => 55,
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
            'overall' => 65,
            'potential' => 70,
            'pace' => 66,
            'shooting' => 64,
            'passing' => 67,
            'defending' => 58,
            'physical' => 61,
            'stamina' => 80,
            'morale' => 50,
            'status' => 'active',
            'market_value' => 100000,
            'salary' => 8000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ]);
    }
}
