<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use App\Services\ClubFinanceLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubFinanceLedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_changes_are_persisted_with_budget_asset_type(): void
    {
        $user = User::factory()->create();
        $club = $this->createClub($user, 500000, 40);

        /** @var ClubFinanceLedgerService $ledger */
        $ledger = app(ClubFinanceLedgerService::class);
        $ledger->applyBudgetChange($club, 25000, [
            'user_id' => $user->id,
            'context_type' => 'other',
            'reference_type' => 'tests',
            'reference_id' => 1,
            'note' => 'Test budget income',
        ]);

        $this->assertSame(525000.0, (float) $club->fresh()->budget);
        $this->assertDatabaseHas('club_financial_transactions', [
            'club_id' => $club->id,
            'asset_type' => 'budget',
            'direction' => 'income',
            'amount' => 25000.00,
            'balance_after' => 525000.00,
            'reference_type' => 'tests',
            'reference_id' => 1,
            'note' => 'Test budget income',
        ]);
    }

    public function test_coin_changes_are_persisted_with_coin_asset_type(): void
    {
        $user = User::factory()->create();
        $club = $this->createClub($user, 500000, 40);

        /** @var ClubFinanceLedgerService $ledger */
        $ledger = app(ClubFinanceLedgerService::class);
        $ledger->applyCoinChange($club, -7, [
            'user_id' => $user->id,
            'context_type' => 'other',
            'reference_type' => 'tests',
            'reference_id' => 2,
            'note' => 'Test coin expense',
        ]);

        $this->assertSame(33, (int) $club->fresh()->coins);
        $this->assertDatabaseHas('club_financial_transactions', [
            'club_id' => $club->id,
            'asset_type' => 'coins',
            'direction' => 'expense',
            'amount' => 7.00,
            'balance_after' => 33.00,
            'reference_type' => 'tests',
            'reference_id' => 2,
            'note' => 'Test coin expense',
        ]);
    }

    private function createClub(User $user, float $budget, int $coins): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'name' => 'Ledger FC',
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => $budget,
            'coins' => $coins,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
        ]);
    }
}
