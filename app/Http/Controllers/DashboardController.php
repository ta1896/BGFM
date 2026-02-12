<?php

namespace App\Http\Controllers;

use App\Services\TeamStrengthCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, TeamStrengthCalculator $calculator): View
    {
        $clubs = $request->user()
            ->clubs()
            ->withCount(['players', 'lineups'])
            ->orderBy('name')
            ->get();

        $activeClub = $clubs->firstWhere('id', (int) $request->query('club')) ?? $clubs->first();

        $activeLineup = null;
        $metrics = [
            'overall' => 0,
            'attack' => 0,
            'midfield' => 0,
            'defense' => 0,
            'chemistry' => 0,
        ];

        if ($activeClub) {
            $activeClub->loadMissing(['stadium', 'activeSponsorContract.sponsor']);

            $activeLineup = $activeClub->lineups()
                ->with('players')
                ->where('is_active', true)
                ->first() ?? $activeClub->lineups()->with('players')->first();

            if ($activeLineup) {
                $metrics = $calculator->calculate($activeLineup);
            }
        }

        $nextMatch = null;
        if ($activeClub) {
            $nextMatch = \App\Models\GameMatch::query()
                ->with(['homeClub', 'awayClub'])
                ->where(function ($query) use ($activeClub) {
                    $query->where('home_club_id', $activeClub->id)
                        ->orWhere('away_club_id', $activeClub->id);
                })
                ->where('status', 'scheduled')
                ->orderBy('kickoff_at')
                ->first();
        }

        $notifications = $request->user()
            ->gameNotifications()
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'clubs' => $clubs,
            'activeClub' => $activeClub,
            'activeLineup' => $activeLineup,
            'metrics' => $metrics,
            'nextMatch' => $nextMatch,
            'notifications' => $notifications,
            'activeSponsorContract' => $activeClub?->activeSponsorContract,
            'stadium' => $activeClub?->stadium,
        ]);
    }
}
