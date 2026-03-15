<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Player;
use App\Models\SeasonClubStatistic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamComparisonController extends Controller
{
    public function index(Request $request)
    {
        $club1Id = $request->query('club1');
        $club2Id = $request->query('club2');

        $clubs = Club::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Club $club) => [
                'id' => $club->id,
                'name' => $club->name,
                'logo_url' => $club->logo_url,
            ]);

        $club1 = null;
        $club2 = null;
        $comparisonData = null;

        if ($club1Id && $club2Id) {
            $club1 = Club::findOrFail($club1Id);
            $club2 = Club::findOrFail($club2Id);
            $club1Stats = SeasonClubStatistic::query()
                ->where('club_id', $club1->id)
                ->orderByDesc('competition_season_id')
                ->first();
            $club2Stats = SeasonClubStatistic::query()
                ->where('club_id', $club2->id)
                ->orderByDesc('competition_season_id')
                ->first();

            $comparisonData = [
                'club1' => [
                    'id' => $club1->id,
                    'name' => $club1->name,
                    'logo_url' => $club1->logo_url,
                    'points' => $club1Stats->points ?? 0,
                    'goals_for' => $club1Stats->goals_for ?? 0,
                    'goals_against' => $club1Stats->goals_against ?? 0,
                    'goal_difference' => $club1Stats->goal_difference ?? $club1Stats->goal_diff ?? 0,
                    'played' => $club1Stats->matches_played ?? 0,
                    'wins' => $club1Stats->wins ?? 0,
                    'draws' => $club1Stats->draws ?? 0,
                    'losses' => $club1Stats->losses ?? 0,
                    'average_rating' => Player::where('club_id', $club1->id)->avg('overall') ?? 0,
                    'total_value' => Player::where('club_id', $club1->id)->sum('market_value') ?? 0,
                ],
                'club2' => [
                    'id' => $club2->id,
                    'name' => $club2->name,
                    'logo_url' => $club2->logo_url,
                    'points' => $club2Stats->points ?? 0,
                    'goals_for' => $club2Stats->goals_for ?? 0,
                    'goals_against' => $club2Stats->goals_against ?? 0,
                    'goal_difference' => $club2Stats->goal_difference ?? $club2Stats->goal_diff ?? 0,
                    'played' => $club2Stats->matches_played ?? 0,
                    'wins' => $club2Stats->wins ?? 0,
                    'draws' => $club2Stats->draws ?? 0,
                    'losses' => $club2Stats->losses ?? 0,
                    'average_rating' => Player::where('club_id', $club2->id)->avg('overall') ?? 0,
                    'total_value' => Player::where('club_id', $club2->id)->sum('market_value') ?? 0,
                ]
            ];
            
            // Format numbers
            $comparisonData['club1']['average_rating'] = number_format($comparisonData['club1']['average_rating'], 1);
            $comparisonData['club2']['average_rating'] = number_format($comparisonData['club2']['average_rating'], 1);
        }

        return Inertia::render('Teams/Compare', [
            'clubs' => $clubs,
            'club1Id' => $club1Id,
            'club2Id' => $club2Id,
            'comparisonData' => $comparisonData,
        ]);
    }
}
