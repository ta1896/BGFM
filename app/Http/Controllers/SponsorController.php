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
    public function index(Request $request, SponsorService $sponsorService): \Inertia\Response
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;
        $clubs = $request->user()->isAdmin() 
            ? \App\Models\Club::where('is_cpu', false)->orderBy('name')->get()
            : $request->user()->clubs()->orderBy('name')->get();

        if (!$activeClub && $clubs->isNotEmpty()) {
            $activeClub = $clubs->first();
        }

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

            if ($activeContract) {
                $activeContract->ends_on_formatted = $activeContract->ends_on?->format('d.m.Y');
            }

            $history = SponsorContract::query()
                ->with('sponsor')
                ->where('club_id', $activeClub->id)
                ->latest('id')
                ->limit(15)
                ->get()
                ->map(function ($c) {
                    $c->starts_on_formatted = $c->starts_on?->format('d.m.Y');
                    $c->ends_on_formatted = $c->ends_on?->format('d.m.Y');
                    return $c;
                });
        }

        return \Inertia\Inertia::render('Sponsors/Index', [
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
