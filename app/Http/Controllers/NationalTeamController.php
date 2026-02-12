<?php

namespace App\Http\Controllers;

use App\Models\NationalTeam;
use App\Services\NationalTeamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NationalTeamController extends Controller
{
    public function index(Request $request): View
    {
        $teams = NationalTeam::query()
            ->with(['country', 'manager'])
            ->orderBy('name')
            ->get();

        $activeTeam = $teams->firstWhere('id', (int) $request->query('team')) ?? $teams->first();

        $squad = collect();
        if ($activeTeam) {
            $squad = $activeTeam->activeCallups()
                ->with(['player.club'])
                ->orderByRaw("case role when 'starter' then 1 when 'bench' then 2 else 3 end")
                ->orderBy('id')
                ->get();
        }

        return view('national-teams.index', [
            'teams' => $teams,
            'activeTeam' => $activeTeam,
            'squad' => $squad,
        ]);
    }

    public function refresh(
        Request $request,
        NationalTeam $nationalTeam,
        NationalTeamService $nationalTeamService
    ): RedirectResponse {
        abort_unless($request->user()->isAdmin(), 403);

        $count = $nationalTeamService->refreshSquad($nationalTeam, $request->user());

        return redirect()
            ->route('national-teams.index', ['team' => $nationalTeam->id])
            ->with('status', 'Nationalteam wurde aktualisiert ('.$count.' Spieler).');
    }
}
