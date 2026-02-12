<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\TransferBid;
use App\Models\TransferListing;
use App\Services\TransferMarketService;
use App\Services\TransferWindowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferMarketController extends Controller
{
    public function index(Request $request): View
    {
        $userClubIds = $request->user()->clubs()->pluck('id');

        $listings = TransferListing::query()
            ->with(['player.club', 'sellerClub', 'bids.bidderClub'])
            ->where('status', 'open')
            ->where('expires_at', '>=', now())
            ->orderByDesc('created_at')
            ->paginate(12);

        $myListings = TransferListing::query()
            ->with(['player', 'bids.bidderClub'])
            ->whereIn('seller_club_id', $userClubIds)
            ->latest()
            ->limit(20)
            ->get();

        $myPlayers = Player::query()
            ->with('club')
            ->whereIn('club_id', $userClubIds)
            ->orderByDesc('overall')
            ->get();

        return view('transfers.index', [
            'listings' => $listings,
            'myListings' => $myListings,
            'myPlayers' => $myPlayers,
            'myClubs' => $request->user()->clubs()->orderBy('name')->get(),
            'windowOpen' => app(TransferWindowService::class)->isOpen(),
            'windowLabel' => app(TransferWindowService::class)->currentWindowLabel(),
            'windowMessage' => app(TransferWindowService::class)->closedMessage(),
        ]);
    }

    public function storeListing(Request $request, TransferWindowService $windowService): RedirectResponse
    {
        abort_if(!$windowService->isOpen(), 422, $windowService->closedMessage());

        $validated = $request->validate([
            'player_id' => ['required', 'integer', 'exists:players,id'],
            'min_price' => ['required', 'numeric', 'min:1'],
            'buy_now_price' => ['nullable', 'numeric', 'gte:min_price'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:21'],
        ]);

        $player = Player::with('club')->findOrFail((int) $validated['player_id']);
        abort_unless($player->club && $player->club->user_id === $request->user()->id, 403);
        abort_if($player->parent_club_id !== null, 422, 'Leihspieler koennen nicht verkauft werden.');

        $openExists = TransferListing::query()
            ->where('player_id', $player->id)
            ->where('status', 'open')
            ->exists();
        abort_if($openExists, 422, 'Spieler ist bereits auf dem Transfermarkt.');

        TransferListing::create([
            'player_id' => $player->id,
            'seller_club_id' => $player->club_id,
            'listed_by_user_id' => $request->user()->id,
            'min_price' => $validated['min_price'],
            'buy_now_price' => $validated['buy_now_price'] ?? null,
            'listed_at' => now(),
            'expires_at' => now()->addDays((int) $validated['duration_days']),
            'status' => 'open',
        ]);

        $player->update(['status' => 'transfer_listed']);

        return redirect()->route('transfers.index')->with('status', 'Spieler wurde auf dem Transfermarkt gelistet.');
    }

    public function placeBid(Request $request, TransferListing $listing, TransferWindowService $windowService): RedirectResponse
    {
        abort_if(!$windowService->isOpen(), 422, $windowService->closedMessage());
        abort_if($listing->status !== 'open' || $listing->expires_at < now(), 422, 'Listing ist nicht mehr offen.');

        $validated = $request->validate([
            'bidder_club_id' => ['required', 'integer', 'exists:clubs,id'],
            'amount' => ['required', 'numeric', 'min:'.(float) $listing->min_price],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $club = $request->user()->clubs()->whereKey((int) $validated['bidder_club_id'])->first();
        abort_unless($club, 403);
        abort_if($club->id === $listing->seller_club_id, 422, 'Eigengebote sind nicht erlaubt.');
        abort_if((float) $club->budget < (float) $validated['amount'], 422, 'Nicht genug Budget.');

        TransferBid::create([
            'transfer_listing_id' => $listing->id,
            'bidder_club_id' => $club->id,
            'bidder_user_id' => $request->user()->id,
            'amount' => $validated['amount'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('transfers.index')->with('status', 'Gebot wurde abgegeben.');
    }

    public function acceptBid(
        Request $request,
        TransferListing $listing,
        TransferBid $bid,
        TransferMarketService $transferService,
        TransferWindowService $windowService
    ): RedirectResponse {
        abort_if(!$windowService->isOpen(), 422, $windowService->closedMessage());
        $listing->loadMissing('sellerClub');
        abort_unless($listing->sellerClub && $listing->sellerClub->user_id === $request->user()->id, 403);
        abort_unless($bid->transfer_listing_id === $listing->id, 422);
        abort_if($listing->status !== 'open' || $bid->status !== 'pending', 422);

        $transferService->acceptBid($listing, $bid, $request->user());

        return redirect()->route('transfers.index')->with('status', 'Gebot wurde akzeptiert und Transfer ausgefuehrt.');
    }

    public function closeListing(Request $request, TransferListing $listing): RedirectResponse
    {
        $listing->loadMissing(['sellerClub', 'player']);
        abort_unless($listing->sellerClub && $listing->sellerClub->user_id === $request->user()->id, 403);
        abort_if($listing->status !== 'open', 422);

        $listing->update(['status' => 'cancelled']);
        if ($listing->player) {
            $listing->player->update(['status' => 'active']);
        }

        return redirect()->route('transfers.index')->with('status', 'Listing wurde geschlossen.');
    }
}
