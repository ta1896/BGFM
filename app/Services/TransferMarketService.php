<?php

namespace App\Services;

use App\Models\GameNotification;
use App\Models\PlayerContract;
use App\Models\TransferBid;
use App\Models\TransferListing;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransferMarketService
{
    public function acceptBid(TransferListing $listing, TransferBid $acceptedBid, User $actor): void
    {
        DB::transaction(function () use ($listing, $acceptedBid, $actor): void {
            $listing->loadMissing(['player', 'sellerClub.user']);
            $acceptedBid->loadMissing(['bidderClub.user']);

            $sellerClub = $listing->sellerClub;
            $buyerClub = $acceptedBid->bidderClub;
            $player = $listing->player;
            $amount = (float) $acceptedBid->amount;

            abort_if(!$sellerClub || !$buyerClub || !$player, 422);

            abort_if($buyerClub->id === $sellerClub->id, 422);
            abort_if((float) $buyerClub->budget < $amount, 422, 'Kaeufer hat nicht genug Budget.');

            $sellerClub->update(['budget' => (float) $sellerClub->budget + $amount]);
            $buyerClub->update(['budget' => (float) $buyerClub->budget - $amount]);
            $player->update(['club_id' => $buyerClub->id, 'status' => 'active']);

            PlayerContract::query()
                ->where('player_id', $player->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            PlayerContract::create([
                'player_id' => $player->id,
                'club_id' => $buyerClub->id,
                'wage' => $player->salary,
                'bonus_goal' => 0,
                'signed_on' => now()->toDateString(),
                'starts_on' => now()->toDateString(),
                'expires_on' => $player->contract_expires_on ?: now()->addYears(2)->toDateString(),
                'release_clause' => (float) $player->market_value * 2,
                'is_active' => true,
            ]);

            $listing->update(['status' => 'sold']);
            $listing->bids()->where('status', 'pending')->update([
                'status' => 'rejected',
                'decided_at' => now(),
            ]);

            $acceptedBid->update([
                'status' => 'accepted',
                'decided_at' => now(),
            ]);

            DB::table('club_financial_transactions')->insert([
                [
                    'club_id' => $sellerClub->id,
                    'user_id' => $actor->id,
                    'context_type' => 'transfer',
                    'direction' => 'income',
                    'amount' => $amount,
                    'balance_after' => $sellerClub->budget,
                    'reference_type' => 'transfer_listings',
                    'reference_id' => $listing->id,
                    'booked_at' => now(),
                    'note' => 'Transferverkauf: '.$player->full_name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'club_id' => $buyerClub->id,
                    'user_id' => $actor->id,
                    'context_type' => 'transfer',
                    'direction' => 'expense',
                    'amount' => $amount,
                    'balance_after' => $buyerClub->budget,
                    'reference_type' => 'transfer_listings',
                    'reference_id' => $listing->id,
                    'booked_at' => now(),
                    'note' => 'Transfereinkauf: '.$player->full_name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $this->notify($sellerClub->user_id, $sellerClub->id, 'transfer_sold', 'Transfer abgeschlossen', $player->full_name.' wurde verkauft.');
            $this->notify($buyerClub->user_id, $buyerClub->id, 'transfer_bought', 'Neuzugang', $player->full_name.' wurde verpflichtet.');
        });
    }

    private function notify(?int $userId, int $clubId, string $type, string $title, string $message): void
    {
        if (!$userId) {
            return;
        }

        GameNotification::create([
            'user_id' => $userId,
            'club_id' => $clubId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => '/transfers',
        ]);
    }
}
