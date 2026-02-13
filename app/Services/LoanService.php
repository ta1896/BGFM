<?php

namespace App\Services;

use App\Models\GameNotification;
use App\Models\Loan;
use App\Models\LoanBid;
use App\Models\LoanListing;
use App\Models\PlayerContract;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(
        private readonly TransferWindowService $windowService,
        private readonly ClubFinanceLedgerService $financeLedger
    ) {
    }

    public function acceptBid(LoanListing $listing, LoanBid $acceptedBid, User $actor): void
    {
        abort_if(!$this->windowService->isOpen(), 422, $this->windowService->closedMessage());

        DB::transaction(function () use ($listing, $acceptedBid, $actor): void {
            $listing->loadMissing(['player', 'lenderClub.user']);
            $acceptedBid->loadMissing(['borrowerClub.user']);

            $player = $listing->player;
            $lenderClub = $listing->lenderClub;
            $borrowerClub = $acceptedBid->borrowerClub;
            abort_if(!$player || !$lenderClub || !$borrowerClub, 422);
            abort_if($listing->status !== 'open', 422);
            abort_if($acceptedBid->status !== 'pending', 422);
            abort_if($borrowerClub->id === $lenderClub->id, 422);
            abort_if((float) $borrowerClub->budget < (float) $acceptedBid->weekly_fee, 422, 'Nicht genug Budget.');

            $startsOn = now()->toDateString();
            $endsOn = now()->addMonths(max(1, (int) $listing->loan_months))->toDateString();

            $loan = Loan::create([
                'loan_listing_id' => $listing->id,
                'loan_bid_id' => $acceptedBid->id,
                'player_id' => $player->id,
                'lender_club_id' => $lenderClub->id,
                'borrower_club_id' => $borrowerClub->id,
                'weekly_fee' => $acceptedBid->weekly_fee,
                'buy_option_price' => $listing->buy_option_price,
                'starts_on' => $startsOn,
                'ends_on' => $endsOn,
                'status' => 'active',
                'buy_option_state' => $listing->buy_option_price ? 'pending' : 'none',
            ]);

            $listing->update(['status' => 'loaned']);
            $listing->bids()->where('status', 'pending')->update([
                'status' => 'rejected',
                'decided_at' => now(),
            ]);
            $acceptedBid->update([
                'status' => 'accepted',
                'decided_at' => now(),
            ]);

            $player->update([
                'parent_club_id' => $lenderClub->id,
                'club_id' => $borrowerClub->id,
                'loan_ends_on' => $endsOn,
                'status' => 'active',
            ]);

            $this->financeLedger->applyBudgetChange($lenderClub, (float) $acceptedBid->weekly_fee, [
                'user_id' => $actor->id,
                'context_type' => 'other',
                'reference_type' => 'loans',
                'reference_id' => $loan->id,
                'note' => 'Leihgebuehr erhalten: '.$player->full_name,
            ]);
            $this->financeLedger->applyBudgetChange($borrowerClub, -((float) $acceptedBid->weekly_fee), [
                'user_id' => $actor->id,
                'context_type' => 'other',
                'reference_type' => 'loans',
                'reference_id' => $loan->id,
                'note' => 'Leihgebuehr gezahlt: '.$player->full_name,
            ]);

            $this->notify(
                $lenderClub->user_id,
                $lenderClub->id,
                'loan_sent',
                'Spieler verliehen',
                $player->full_name.' wurde an '.$borrowerClub->name.' verliehen.'
            );
            $this->notify(
                $borrowerClub->user_id,
                $borrowerClub->id,
                'loan_received',
                'Leihe abgeschlossen',
                $player->full_name.' kommt bis '.$endsOn.' auf Leihbasis.'
            );
        });
    }

    public function exerciseBuyOption(Loan $loan, User $actor): void
    {
        abort_if(!$this->windowService->isOpen(), 422, $this->windowService->closedMessage());

        DB::transaction(function () use ($loan, $actor): void {
            $loan->loadMissing(['player', 'lenderClub.user', 'borrowerClub.user', 'listing']);

            abort_if($loan->status !== 'active', 422, 'Leihe ist nicht aktiv.');
            abort_if($loan->buy_option_state !== 'pending', 422, 'Kaufoption ist nicht verfuegbar.');
            abort_if(!$loan->buy_option_price || (float) $loan->buy_option_price <= 0, 422, 'Keine Kaufoption gesetzt.');

            $buyerClub = $loan->borrowerClub;
            $sellerClub = $loan->lenderClub;
            $player = $loan->player;
            abort_if(!$buyerClub || !$sellerClub || !$player, 422);

            $price = (float) $loan->buy_option_price;
            abort_if((float) $buyerClub->budget < $price, 422, 'Nicht genug Budget fuer Kaufoption.');

            $this->financeLedger->applyBudgetChange($buyerClub, -$price, [
                'user_id' => $actor->id,
                'context_type' => 'transfer',
                'reference_type' => 'loans',
                'reference_id' => $loan->id,
                'note' => 'Kaufoption gezogen: '.$player->full_name,
            ]);
            $this->financeLedger->applyBudgetChange($sellerClub, $price, [
                'user_id' => $actor->id,
                'context_type' => 'transfer',
                'reference_type' => 'loans',
                'reference_id' => $loan->id,
                'note' => 'Kaufoption gezogen: '.$player->full_name,
            ]);

            $loan->update([
                'status' => 'completed',
                'buy_option_state' => 'exercised',
                'buy_option_decided_at' => now(),
                'bought_at' => now(),
                'returned_at' => now(),
            ]);

            if ($loan->listing && $loan->listing->status === 'loaned') {
                $loan->listing->update(['status' => 'completed']);
            }

            $player->update([
                'club_id' => $buyerClub->id,
                'parent_club_id' => null,
                'loan_ends_on' => null,
                'status' => 'active',
            ]);

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

            $this->notify(
                $buyerClub->user_id,
                $buyerClub->id,
                'loan_option_exercised',
                'Kaufoption gezogen',
                $player->full_name.' wurde fest verpflichtet.'
            );
            $this->notify(
                $sellerClub->user_id,
                $sellerClub->id,
                'loan_option_exercised_against',
                'Spieler fest abgegeben',
                $player->full_name.' wurde per Kaufoption verpflichtet.'
            );
        });
    }

    public function declineBuyOption(Loan $loan, User $actor): void
    {
        DB::transaction(function () use ($loan, $actor): void {
            $loan->loadMissing(['player', 'lenderClub.user', 'borrowerClub.user']);

            abort_if($loan->status !== 'active', 422, 'Leihe ist nicht aktiv.');
            abort_if($loan->buy_option_state !== 'pending', 422, 'Kaufoption ist nicht verfuegbar.');

            $loan->update([
                'buy_option_state' => 'declined',
                'buy_option_decided_at' => now(),
            ]);

            $borrowerClub = $loan->borrowerClub;
            $lenderClub = $loan->lenderClub;
            $playerName = $loan->player?->full_name ?? 'Spieler';

            $this->notify(
                $borrowerClub?->user_id,
                (int) $loan->borrower_club_id,
                'loan_option_declined',
                'Kaufoption abgelehnt',
                'Keine feste Verpflichtung fuer '.$playerName.'.'
            );
            $this->notify(
                $lenderClub?->user_id,
                (int) $loan->lender_club_id,
                'loan_option_declined_by_other',
                'Kaufoption nicht gezogen',
                $playerName.' bleibt nach Leihende bei dir.'
            );
        });
    }

    public function completeExpiredLoans(): int
    {
        $loans = Loan::query()
            ->with(['player', 'lenderClub'])
            ->where('status', 'active')
            ->whereDate('ends_on', '<=', now()->toDateString())
            ->get();

        foreach ($loans as $loan) {
            DB::transaction(function () use ($loan): void {
                $loan->update([
                    'status' => 'completed',
                    'buy_option_state' => $loan->buy_option_state === 'pending' ? 'declined' : $loan->buy_option_state,
                    'buy_option_decided_at' => $loan->buy_option_state === 'pending' ? now() : $loan->buy_option_decided_at,
                    'returned_at' => now(),
                ]);

                if ($loan->listing && $loan->listing->status === 'loaned') {
                    $loan->listing->update(['status' => 'completed']);
                }

                $loan->player?->update([
                    'club_id' => $loan->lender_club_id,
                    'parent_club_id' => null,
                    'loan_ends_on' => null,
                    'status' => 'active',
                ]);

                $lenderUserId = $loan->lenderClub?->user_id;
                if ($lenderUserId) {
                    GameNotification::create([
                        'user_id' => $lenderUserId,
                        'club_id' => $loan->lender_club_id,
                        'type' => 'loan_returned',
                        'title' => 'Leihe beendet',
                        'message' => ($loan->player?->full_name ?? 'Spieler').' ist aus der Leihe zurueck.',
                        'action_url' => '/loans',
                    ]);
                }
            });
        }

        return $loans->count();
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
            'action_url' => '/loans',
        ]);
    }
}
