<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\LeagueTable;
use App\Models\MatchResult; // Assuming this model exists or similar for matches
use App\Models\Player;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamComparisonController extends Controller
{
    public function index(Request $request)
    {
        $club1Id = $request->query('club1');
        $club2Id = $request->query('club2');

        $clubs = Club::orderBy('name')->get(['id', 'name', 'logo_path']);

        $club1 = null;
        $club2 = null;
        $comparisonData = null;

        if ($club1Id && $club2Id) {
            $club1 = Club::with('leagueTable')->findOrFail($club1Id);
            $club2 = Club::with('leagueTable')->findOrFail($club2Id);

            // Basic comparison stats. We can expand this later.
            $comparisonData = [
                'club1' => [
                    'id' => $club1->id,
                    'name' => $club1->name,
                    'logo_url' => $club1->logo_url,
                    'points' => $club1->leagueTable->points ?? 0,
                    'goals_for' => $club1->leagueTable->goals_for ?? 0,
                    'goals_against' => $club1->leagueTable->goals_against ?? 0,
                    'goal_difference' => $club1->leagueTable->goal_difference ?? 0,
                    'played' => $club1->leagueTable->played ?? 0,
                    'wins' => $club1->leagueTable->won ?? 0,
                    'draws' => $club1->leagueTable->drawn ?? 0,
                    'losses' => $club1->leagueTable->lost ?? 0,
                    'average_rating' => Player::where('club_id', $club1->id)->avg('overall') ?? 0,
                    'total_value' => Player::where('club_id', $club1->id)->sum('market_value') ?? 0,
                ],
                'club2' => [
                    'id' => $club2->id,
                    'name' => $club2->name,
                    'logo_url' => $club2->logo_url,
                    'points' => $club2->leagueTable->points ?? 0,
                    'goals_for' => $club2->leagueTable->goals_for ?? 0,
                    'goals_against' => $club2->leagueTable->goals_against ?? 0,
                    'goal_difference' => $club2->leagueTable->goal_difference ?? 0,
                    'played' => $club2->leagueTable->played ?? 0,
                    'wins' => $club2->leagueTable->won ?? 0,
                    'draws' => $club2->leagueTable->drawn ?? 0,
                    'losses' => $club2->leagueTable->lost ?? 0,
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
