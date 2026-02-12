<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Sponsor;
use App\Models\SponsorContract;
use App\Services\SponsorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SponsorController extends Controller
{
    public function index(Request $request, SponsorService $sponsorService): View
    {
        $clubs = $request->user()->clubs()->orderBy('name')->get();
        $activeClub = $clubs->firstWhere('id', (int) $request->query('club')) ?? $clubs->first();

        $offers = collect();
        $activeContract = null;
        $history = collect();

        if ($activeClub) {
            $offers = $sponsorService->availableForClub($activeClub);
            $activeContract = SponsorContract::query()
                ->with('sponsor')
                ->where('club_id', $activeClub->id)
                ->where('status', 'active')
                ->whereDate('ends_on', '>=', now()->toDateString())
                ->latest('id')
                ->first();

            $history = SponsorContract::query()
                ->with('sponsor')
                ->where('club_id', $activeClub->id)
                ->latest('id')
                ->limit(15)
                ->get();
        }

        return view('sponsors.index', [
            'clubs' => $clubs,
            'activeClub' => $activeClub,
            'offers' => $offers,
            'activeContract' => $activeContract,
            'history' => $history,
        ]);
    }

    public function sign(
        Request $request,
        Sponsor $sponsor,
        SponsorService $sponsorService
    ): RedirectResponse {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'months' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        $club = $request->user()->clubs()->whereKey((int) $validated['club_id'])->first();
        abort_unless($club, 403);

        $sponsorService->signContract($club, $sponsor, $request->user(), (int) $validated['months']);

        return redirect()
            ->route('sponsors.index', ['club' => $club->id])
            ->with('status', 'Sponsorvertrag wurde unterzeichnet.');
    }

    public function terminate(Request $request, SponsorContract $contract): RedirectResponse
    {
        abort_unless($request->user()->clubs()->whereKey($contract->club_id)->exists(), 403);
        abort_if($contract->status !== 'active', 422, 'Vertrag ist nicht aktiv.');

        $contract->update(['status' => 'terminated']);

        return redirect()
            ->route('sponsors.index', ['club' => $contract->club_id])
            ->with('status', 'Sponsorvertrag wurde beendet.');
    }
}
