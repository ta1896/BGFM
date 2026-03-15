<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FreeClubController extends Controller
{
    public function index(Request $request): Response
    {
        $freeClubs = Club::query()
            ->whereNull('user_id')
            ->where('is_cpu', false)
            ->withCount('players')
            ->orderByDesc('reputation')
            ->orderBy('name')
            ->paginate(12)
            ->through(function ($club) {
                return [
                    'id' => $club->id,
                    'name' => $club->name,
                    'short_name' => $club->short_name,
                    'logo_url' => $club->logo_url,
                    'country' => $club->country,
                    'league' => $club->league,
                    'reputation' => $club->reputation,
                    'players_count' => $club->players_count,
                ];
            });

        return Inertia::render('Clubs/Free', [
            'freeClubs' => $freeClubs,
            'hasOwnedClub' => $request->user()->clubs()->exists(),
        ]);
    }

    public function claim(Request $request, Club $club): RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            abort(403);
        }

        if ($user->clubs()->exists()) {
            return redirect()
                ->route('dashboard')
                ->with('status', 'Du verwaltest bereits einen Verein.');
        }

        $claimed = Club::query()
            ->whereKey($club->id)
            ->whereNull('user_id')
            ->where('is_cpu', false)
            ->update([
                'user_id' => $user->id,
            ]);

        if ($claimed === 0) {
            return redirect()
                ->route('clubs.free')
                ->withErrors(['club' => 'Dieser Verein ist nicht mehr frei.']);
        }

        return redirect()
            ->route('dashboard', ['club' => $club->id])
            ->with('status', 'Verein uebernommen: '.$club->name);
    }
}
