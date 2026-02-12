<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Loan;
use App\Models\LoanBid;
use App\Models\LoanListing;
use App\Models\Player;
use App\Models\PlayerContract;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractLoanWindowTest extends TestCase
{
    use RefreshDatabase;

    public function test_contract_renewal_creates_new_active_contract(): void
    {
        $user = User::factory()->create();
        $club = $this->createClub($user, 'Contract Club');
        $player = $this->createPlayer($club, 'Renew');

        PlayerContract::create([
            'player_id' => $player->id,
            'club_id' => $club->id,
            'wage' => 8000,
            'bonus_goal' => 0,
            'signed_on' => now()->subMonths(6)->toDateString(),
            'starts_on' => now()->subMonths(6)->toDateString(),
            'expires_on' => now()->addMonths(6)->toDateString(),
            'release_clause' => 500000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('contracts.renew', $player), [
            'salary' => 12000,
            'months' => 24,
            'release_clause' => 900000,
        ]);

        $response->assertRedirect(route('contracts.index', ['club' => $club->id]));

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'salary' => 12000.00,
        ]);
        $this->assertDatabaseHas('player_contracts', [
            'player_id' => $player->id,
            'club_id' => $club->id,
            'wage' => 12000.00,
            'is_active' => true,
        ]);
    }

    public function test_loan_acceptance_moves_player_to_borrower(): void
    {
        Carbon::setTestNow('2026-07-10 12:00:00');

        $lenderUser = User::factory()->create();
        $borrowerUser = User::factory()->create();
        $lenderClub = $this->createClub($lenderUser, 'Lender');
        $borrowerClub = $this->createClub($borrowerUser, 'Borrower');
        $player = $this->createPlayer($lenderClub, 'Loan');

        $listing = LoanListing::create([
            'player_id' => $player->id,
            'lender_club_id' => $lenderClub->id,
            'listed_by_user_id' => $lenderUser->id,
            'min_weekly_fee' => 6000,
            'loan_months' => 6,
            'listed_at' => now(),
            'expires_at' => now()->addDays(7),
            'status' => 'open',
        ]);

        $bid = LoanBid::create([
            'loan_listing_id' => $listing->id,
            'borrower_club_id' => $borrowerClub->id,
            'bidder_user_id' => $borrowerUser->id,
            'weekly_fee' => 7000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($lenderUser)->post(route('loans.bids.accept', [
            'listing' => $listing,
            'bid' => $bid,
        ]));

        $response->assertRedirect(route('loans.index'));

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'club_id' => $borrowerClub->id,
            'parent_club_id' => $lenderClub->id,
        ]);
        $this->assertDatabaseHas('loans', [
            'player_id' => $player->id,
            'lender_club_id' => $lenderClub->id,
            'borrower_club_id' => $borrowerClub->id,
            'status' => 'active',
        ]);

        Carbon::setTestNow();
    }

    public function test_transfer_listing_is_blocked_when_window_is_closed(): void
    {
        Carbon::setTestNow('2026-03-10 12:00:00');

        $user = User::factory()->create();
        $club = $this->createClub($user, 'Window Club');
        $player = $this->createPlayer($club, 'Window');

        $response = $this->actingAs($user)->post(route('transfers.listings.store'), [
            'player_id' => $player->id,
            'min_price' => 100000,
            'duration_days' => 7,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('transfer_listings', 0);

        Carbon::setTestNow();
    }

    public function test_borrower_can_exercise_buy_option_on_active_loan(): void
    {
        Carbon::setTestNow('2026-07-10 12:00:00');

        $lenderUser = User::factory()->create();
        $borrowerUser = User::factory()->create();
        $lenderClub = $this->createClub($lenderUser, 'Seller');
        $borrowerClub = $this->createClub($borrowerUser, 'Buyer');
        $player = $this->createPlayer($lenderClub, 'Option');

        $loan = Loan::create([
            'player_id' => $player->id,
            'lender_club_id' => $lenderClub->id,
            'borrower_club_id' => $borrowerClub->id,
            'weekly_fee' => 8000,
            'buy_option_price' => 120000,
            'starts_on' => now()->subMonth()->toDateString(),
            'ends_on' => now()->addMonth()->toDateString(),
            'status' => 'active',
            'buy_option_state' => 'pending',
        ]);

        $player->update([
            'club_id' => $borrowerClub->id,
            'parent_club_id' => $lenderClub->id,
            'loan_ends_on' => now()->addMonth()->toDateString(),
        ]);

        $oldBorrowerBudget = (float) $borrowerClub->budget;
        $oldLenderBudget = (float) $lenderClub->budget;

        $response = $this->actingAs($borrowerUser)->post(route('loans.option.exercise', $loan));

        $response->assertRedirect(route('loans.index'));
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'completed',
            'buy_option_state' => 'exercised',
        ]);
        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'club_id' => $borrowerClub->id,
            'parent_club_id' => null,
        ]);

        $this->assertEquals($oldBorrowerBudget - 120000, (float) $borrowerClub->fresh()->budget);
        $this->assertEquals($oldLenderBudget + 120000, (float) $lenderClub->fresh()->budget);

        Carbon::setTestNow();
    }

    public function test_borrower_can_decline_buy_option_on_active_loan(): void
    {
        Carbon::setTestNow('2026-07-10 12:00:00');

        $lenderUser = User::factory()->create();
        $borrowerUser = User::factory()->create();
        $lenderClub = $this->createClub($lenderUser, 'Lender Club');
        $borrowerClub = $this->createClub($borrowerUser, 'Borrow Club');
        $player = $this->createPlayer($lenderClub, 'Decline');

        $loan = Loan::create([
            'player_id' => $player->id,
            'lender_club_id' => $lenderClub->id,
            'borrower_club_id' => $borrowerClub->id,
            'weekly_fee' => 7000,
            'buy_option_price' => 90000,
            'starts_on' => now()->subMonth()->toDateString(),
            'ends_on' => now()->addMonth()->toDateString(),
            'status' => 'active',
            'buy_option_state' => 'pending',
        ]);

        $response = $this->actingAs($borrowerUser)->post(route('loans.option.decline', $loan));

        $response->assertRedirect(route('loans.index'));
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'active',
            'buy_option_state' => 'declined',
        ]);

        Carbon::setTestNow();
    }

    private function createClub(User $user, string $name): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'name' => $name,
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => 500000,
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
