<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Services\ContractService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $clubs = $request->user()->clubs()->orderBy('name')->get();
        $activeClub = $clubs->firstWhere('id', (int) $request->query('club')) ?? $clubs->first();

        $players = collect();
        if ($activeClub) {
            $players = Player::query()
                ->with(['contracts' => fn ($q) => $q->where('is_active', true)->latest('expires_on')])
                ->where('club_id', $activeClub->id)
                ->orderBy('contract_expires_on')
                ->orderByDesc('overall')
                ->get();
        }

        return view('contracts.index', [
            'clubs' => $clubs,
            'activeClub' => $activeClub,
            'players' => $players,
        ]);
    }

    public function renew(Request $request, Player $player, ContractService $contractService): RedirectResponse
    {
        abort_unless($request->user()->clubs()->whereKey($player->club_id)->exists(), 403);
        abort_if($player->parent_club_id !== null, 422, 'Vertragsverlaengerung waehrend Leihe nicht erlaubt.');

        $validated = $request->validate([
            'salary' => ['required', 'numeric', 'min:0'],
            'months' => ['required', 'integer', 'min:1', 'max:84'],
            'release_clause' => ['nullable', 'numeric', 'min:0'],
        ]);

        $contractService->renew(
            $player,
            $request->user(),
            (float) $validated['salary'],
            (int) $validated['months'],
            isset($validated['release_clause']) ? (float) $validated['release_clause'] : null
        );

        return redirect()
            ->route('contracts.index', ['club' => $player->club_id])
            ->with('status', 'Vertrag wurde verlaengert.');
    }
}
