<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FreeClubController extends Controller
{
    public function index(Request $request): View
    {
        $freeClubs = Club::query()
            ->whereNull('user_id')
            ->where('is_cpu', false)
            ->withCount('players')
            ->orderByDesc('reputation')
            ->orderBy('name')
            ->paginate(12);

        return view('clubs.free', [
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

