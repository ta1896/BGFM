<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanBid;
use App\Models\LoanListing;
use App\Models\Player;
use App\Services\LoanService;
use App\Services\TransferWindowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request, TransferWindowService $windowService): View
    {
        $userClubIds = $request->user()->clubs()->pluck('id');

        $listings = LoanListing::query()
            ->with(['player.club', 'lenderClub', 'bids.borrowerClub'])
            ->where('status', 'open')
            ->where('expires_at', '>=', now())
            ->orderByDesc('created_at')
            ->paginate(12);

        $myListings = LoanListing::query()
            ->with(['player', 'bids.borrowerClub'])
            ->whereIn('lender_club_id', $userClubIds)
            ->latest()
            ->limit(20)
            ->get();

        $myPlayers = Player::query()
            ->with('club')
            ->whereIn('club_id', $userClubIds)
            ->whereNull('parent_club_id')
            ->orderByDesc('overall')
            ->get();

        $activeLoans = Loan::query()
            ->with(['player', 'lenderClub', 'borrowerClub'])
            ->where('status', 'active')
            ->where(function ($query) use ($userClubIds) {
                $query->whereIn('lender_club_id', $userClubIds)
                    ->orWhereIn('borrower_club_id', $userClubIds);
            })
            ->orderBy('ends_on')
            ->get();

        return view('loans.index', [
            'listings' => $listings,
            'myListings' => $myListings,
            'myPlayers' => $myPlayers,
            'myClubs' => $request->user()->clubs()->orderBy('name')->get(),
            'myClubIds' => $userClubIds->all(),
            'activeLoans' => $activeLoans,
            'windowOpen' => $windowService->isOpen(),
            'windowLabel' => $windowService->currentWindowLabel(),
            'windowMessage' => $windowService->closedMessage(),
        ]);
    }

    public function storeListing(Request $request, TransferWindowService $windowService): RedirectResponse
    {
        abort_if(!$windowService->isOpen(), 422, $windowService->closedMessage());

        $validated = $request->validate([
            'player_id' => ['required', 'integer', 'exists:players,id'],
            'min_weekly_fee' => ['required', 'numeric', 'min:0'],
            'buy_option_price' => ['nullable', 'numeric', 'min:0'],
            'loan_months' => ['required', 'integer', 'min:1', 'max:24'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:21'],
        ]);

        $player = Player::with('club')->findOrFail((int) $validated['player_id']);
        abort_unless($player->club && $player->club->user_id === $request->user()->id, 403);
        abort_if($player->parent_club_id !== null, 422, 'Spieler ist bereits verliehen.');

        $activeLoan = Loan::query()
            ->where('player_id', $player->id)
            ->where('status', 'active')
            ->exists();
        abort_if($activeLoan, 422, 'Spieler hat bereits eine aktive Leihe.');

        $openExists = LoanListing::query()
            ->where('player_id', $player->id)
            ->where('status', 'open')
            ->exists();
        abort_if($openExists, 422, 'Spieler ist bereits auf dem Leihmarkt.');

        LoanListing::create([
            'player_id' => $player->id,
            'lender_club_id' => $player->club_id,
            'listed_by_user_id' => $request->user()->id,
            'min_weekly_fee' => $validated['min_weekly_fee'],
            'buy_option_price' => $validated['buy_option_price'] ?? null,
            'loan_months' => $validated['loan_months'],
            'listed_at' => now(),
            'expires_at' => now()->addDays((int) $validated['duration_days']),
            'status' => 'open',
        ]);

        return redirect()->route('loans.index')->with('status', 'Spieler wurde auf den Leihmarkt gesetzt.');
    }

    public function placeBid(Request $request, LoanListing $listing, TransferWindowService $windowService): RedirectResponse
    {
        abort_if(!$windowService->isOpen(), 422, $windowService->closedMessage());
        abort_if($listing->status !== 'open' || $listing->expires_at < now(), 422, 'Listing ist nicht mehr offen.');

        $validated = $request->validate([
            'borrower_club_id' => ['required', 'integer', 'exists:clubs,id'],
            'weekly_fee' => ['required', 'numeric', 'min:'.(float) $listing->min_weekly_fee],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $club = $request->user()->clubs()->whereKey((int) $validated['borrower_club_id'])->first();
        abort_unless($club, 403);
        abort_if($club->id === $listing->lender_club_id, 422, 'Eigene Leihangebote sind nicht erlaubt.');
        abort_if((float) $club->budget < (float) $validated['weekly_fee'], 422, 'Nicht genug Budget.');

        LoanBid::create([
            'loan_listing_id' => $listing->id,
            'borrower_club_id' => $club->id,
            'bidder_user_id' => $request->user()->id,
            'weekly_fee' => $validated['weekly_fee'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('loans.index')->with('status', 'Leihgebot wurde abgegeben.');
    }

    public function acceptBid(
        Request $request,
        LoanListing $listing,
        LoanBid $bid,
        LoanService $loanService
    ): RedirectResponse {
        $listing->loadMissing('lenderClub');
        abort_unless($listing->lenderClub && $listing->lenderClub->user_id === $request->user()->id, 403);
        abort_unless($bid->loan_listing_id === $listing->id, 422);
        abort_if($listing->status !== 'open' || $bid->status !== 'pending', 422);

        $loanService->acceptBid($listing, $bid, $request->user());

        return redirect()->route('loans.index')->with('status', 'Leihe wurde abgeschlossen.');
    }

    public function closeListing(Request $request, LoanListing $listing): RedirectResponse
    {
        $listing->loadMissing('lenderClub');
        abort_unless($listing->lenderClub && $listing->lenderClub->user_id === $request->user()->id, 403);
        abort_if($listing->status !== 'open', 422);

        $listing->update(['status' => 'cancelled']);

        return redirect()->route('loans.index')->with('status', 'Leihlisting wurde geschlossen.');
    }

    public function exerciseOption(Request $request, Loan $loan, LoanService $loanService): RedirectResponse
    {
        abort_unless($request->user()->clubs()->whereKey($loan->borrower_club_id)->exists(), 403);

        $loanService->exerciseBuyOption($loan, $request->user());

        return redirect()->route('loans.index')->with('status', 'Kaufoption wurde gezogen.');
    }

    public function declineOption(Request $request, Loan $loan, LoanService $loanService): RedirectResponse
    {
        abort_unless($request->user()->clubs()->whereKey($loan->borrower_club_id)->exists(), 403);

        $loanService->declineBuyOption($loan, $request->user());

        return redirect()->route('loans.index')->with('status', 'Kaufoption wurde abgelehnt.');
    }
}
