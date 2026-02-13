<?php

namespace App\Services;

use App\Models\GameNotification;
use App\Models\Player;
use App\Models\PlayerContract;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function renew(Player $player, User $actor, float $newSalary, int $months, ?float $releaseClause = null): void
    {
        $player->loadMissing('club');

        DB::transaction(function () use ($player, $actor, $newSalary, $months, $releaseClause): void {
            PlayerContract::query()
                ->where('player_id', $player->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $start = now()->toDateString();
            $expires = now()->addMonths(max(1, $months))->toDateString();

            PlayerContract::create([
                'player_id' => $player->id,
                'club_id' => $player->club_id,
                'wage' => $newSalary,
                'bonus_goal' => 0,
                'signed_on' => $start,
                'starts_on' => $start,
                'expires_on' => $expires,
                'release_clause' => $releaseClause,
                'is_active' => true,
            ]);

            $player->update([
                'salary' => $newSalary,
                'contract_expires_on' => $expires,
                'status' => 'active',
            ]);

            DB::table('club_financial_transactions')->insert([
                'club_id' => $player->club_id,
                'user_id' => $actor->id,
                'context_type' => 'salary',
                'asset_type' => 'budget',
                'direction' => 'expense',
                'amount' => max(0, $newSalary),
                'balance_after' => null,
                'reference_type' => 'player_contracts',
                'reference_id' => null,
                'booked_at' => now(),
                'note' => 'Vertragsverlaengerung: '.$player->full_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($player->club?->user_id) {
                GameNotification::create([
                    'user_id' => $player->club->user_id,
                    'club_id' => $player->club_id,
                    'type' => 'contract_renewed',
                    'title' => 'Vertrag verlaengert',
                    'message' => $player->full_name.' hat bis '.$expires.' verlaengert.',
                    'action_url' => '/contracts',
                ]);
            }
        });
    }
}
