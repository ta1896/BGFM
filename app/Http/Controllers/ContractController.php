<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Services\ContractService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContractController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $clubQuery = \App\Models\Club::query()->orderBy('name');
        
        if (!$user->isAdmin()) {
            $clubQuery->where('user_id', $user->id);
        }
        
        $clubs = $clubQuery->get();
        $activeClubId = (int) ($request->query('club') ?? ($user->isAdmin() && $clubs->count() > 0 ? $user->default_club_id : $clubs->first()?->id));
        $activeClub = $clubs->firstWhere('id', $activeClubId) ?? $clubs->first();

        $players = collect();
        if ($activeClub) {
            $players = Player::query()
                ->with(['contracts' => fn ($q) => $q->where('is_active', true)->latest('expires_on')])
                ->where('club_id', $activeClub->id)
                ->orderBy('contract_expires_on')
                ->orderByDesc('overall')
                ->get();

            $players = $players->map(function ($player) {
                if ($player instanceof Player) {
                    $player->append('photo_url');
                }
                $player->salary_formatted = number_format($player->salary, 0, ',', '.') . ' €';
                $player->value_formatted = number_format($player->market_value, 0, ',', '.') . ' €';
                $player->expires_on_formatted = $player->contract_expires_on ? $player->contract_expires_on->format('d.m.Y') : 'N/A';
                
                // Critical info for renewal UI
                $player->renewal_info = [
                    'current_salary' => $player->salary,
                    'current_months' => $player->contract_expires_on ? now()->diffInMonths($player->contract_expires_on) : 0,
                ];
                
                return $player;
            });
        }

        return Inertia::render('Contracts/Index', [
            'clubs' => $clubs,
            'activeClub' => $activeClub,
            'players' => $players,
            'filters' => [
                'club' => $activeClubId,
            ]
        ]);
    }

    public function renew(Request $request, Player $player, ContractService $contractService): RedirectResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort_unless($user->clubs()->whereKey($player->club_id)->exists(), 403);
        }
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
