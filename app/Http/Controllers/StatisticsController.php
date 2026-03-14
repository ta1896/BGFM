<?php

namespace App\Http\Controllers;

use App\Models\SeasonClubStatistic;
use App\Models\MatchPlayerStat;
use App\Models\CompetitionSeason;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $seasonId = $request->query('season_id');
        
        $seasons = CompetitionSeason::with('competition')
            ->orderByDesc('id')
            ->get();
            
        $activeSeason = null;
        if ($seasonId) {
            $activeSeason = $seasons->firstWhere('id', $seasonId);
        }
        
        if (!$activeSeason && $seasons->isNotEmpty()) {
            $activeSeason = $seasons->first();
            $seasonId = $activeSeason->id;
        }

        $topScorers = [];
        $topAssists = [];
        $topRatings = [];
        $teamStats = [];

        if ($seasonId) {
            // Get all match IDs for this season
            $matchIds = DB::table('game_matches')
                ->where('competition_season_id', $seasonId)
                ->where('status', 'played')
                ->pluck('id');

            if ($matchIds->isNotEmpty()) {
                // Top Scorers
                $topScorers = MatchPlayerStat::with(['player', 'club'])
                    ->select('player_id', 'club_id', DB::raw('SUM(goals) as total_goals'), DB::raw('SUM(minutes_played) as total_minutes'))
                    ->whereIn('match_id', $matchIds)
                    ->groupBy('player_id', 'club_id')
                    ->having('total_goals', '>', 0)
                    ->orderByDesc('total_goals')
                    ->orderBy('total_minutes')
                    ->limit(10)
                    ->get();

                // Top Assists
                $topAssists = MatchPlayerStat::with(['player', 'club'])
                    ->select('player_id', 'club_id', DB::raw('SUM(assists) as total_assists'), DB::raw('SUM(minutes_played) as total_minutes'))
                    ->whereIn('match_id', $matchIds)
                    ->groupBy('player_id', 'club_id')
                    ->having('total_assists', '>', 0)
                    ->orderByDesc('total_assists')
                    ->orderBy('total_minutes')
                    ->limit(10)
                    ->get();

                // Top Ratings (min 3 games played equivalent)
                $topRatings = MatchPlayerStat::with(['player', 'club'])
                    ->select('player_id', 'club_id', DB::raw('AVG(rating) as avg_rating'), DB::raw('SUM(minutes_played) as total_minutes'))
                    ->whereIn('match_id', $matchIds)
                    ->groupBy('player_id', 'club_id')
                    ->having('total_minutes', '>=', 270) // At least 270 minutes played
                    ->orderByDesc('avg_rating')
                    ->limit(10)
                    ->get()
                    ->map(function ($stat) {
                        $stat->avg_rating = round($stat->avg_rating, 2);
                        return $stat;
                    });
            }

            // Team Stats
            $teamStats = SeasonClubStatistic::with('club')
                ->where('competition_season_id', $seasonId)
                ->orderByDesc('points')
                ->orderByDesc('goal_difference')
                ->orderByDesc('goals_for')
                ->get()
                ->map(function ($stat) {
                    return [
                        'club_id' => $stat->club_id,
                        'name' => $stat->club->name,
                        'logo_url' => $stat->club->logo_url,
                        'points' => $stat->points,
                        'goals_for' => $stat->goals_for,
                        'goals_against' => $stat->goals_against,
                        'goal_difference' => $stat->goal_difference,
                        'clean_sheets' => $stat->clean_sheets,
                        'form' => join(' ', str_split($stat->form_last5 ?? '')),
                    ];
                });
        }

        return Inertia::render('Statistics/Index', [
            'seasons' => $seasons->map(fn($s) => ['id' => $s->id, 'name' => $s->competition->name . ' - ' . $s->name]),
            'activeSeasonId' => (int) $seasonId,
            'topScorers' => $topScorers,
            'topAssists' => $topAssists,
            'topRatings' => $topRatings,
            'teamStats' => $teamStats,
        ]);
    }
}
