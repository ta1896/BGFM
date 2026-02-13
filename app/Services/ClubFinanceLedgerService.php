<?php

namespace App\Services;

use App\Models\Club;
use App\Models\ClubFinancialTransaction;
use Illuminate\Support\Facades\DB;

class ClubFinanceLedgerService
{
    public function applyBudgetChange(Club $club, float $delta, array $meta = []): float
    {
        return $this->applyChange($club, 'budget', round($delta, 2), $meta);
    }

    public function applyCoinChange(Club $club, int $delta, array $meta = []): int
    {
        return (int) $this->applyChange($club, 'coins', $delta, $meta);
    }

    private function applyChange(Club $club, string $assetType, float|int $delta, array $meta): float
    {
        if ($delta === 0 || $delta === 0.0) {
            return $assetType === 'coins' ? (float) $club->coins : (float) $club->budget;
        }

        return DB::transaction(function () use ($club, $assetType, $delta, $meta): float {
            /** @var Club $lockedClub */
            $lockedClub = Club::query()
                ->whereKey($club->id)
                ->lockForUpdate()
                ->firstOrFail();

            $currentBalance = $assetType === 'coins'
                ? (float) $lockedClub->coins
                : (float) $lockedClub->budget;

            $nextBalance = $currentBalance + $delta;

            if ($assetType === 'coins') {
                $nextBalance = (float) ((int) round($nextBalance));
            } else {
                $nextBalance = round($nextBalance, 2);
            }

            abort_if(
                $nextBalance < 0,
                422,
                $assetType === 'coins' ? 'Nicht genug Coins.' : 'Nicht genug Budget.'
            );

            if ($assetType === 'coins') {
                $lockedClub->coins = (int) $nextBalance;
            } else {
                $lockedClub->budget = $nextBalance;
            }

            $lockedClub->save();

            ClubFinancialTransaction::create([
                'club_id' => $lockedClub->id,
                'user_id' => $meta['user_id'] ?? null,
                'context_type' => $meta['context_type'] ?? 'other',
                'asset_type' => $assetType,
                'direction' => $delta > 0 ? 'income' : 'expense',
                'amount' => abs((float) $delta),
                'balance_after' => $nextBalance,
                'reference_type' => $meta['reference_type'] ?? null,
                'reference_id' => $meta['reference_id'] ?? null,
                'booked_at' => $meta['booked_at'] ?? now(),
                'note' => $meta['note'] ?? null,
            ]);

            $club->setAttribute('budget', $lockedClub->budget);
            $club->setAttribute('coins', $lockedClub->coins);

            return $nextBalance;
        });
    }
}
