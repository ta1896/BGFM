<?php

namespace App\Http\Controllers;

use App\Models\MatchPlayerStat;
use App\Models\CompetitionSeason;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $seasonId = (int) $request->query('season_id');
        
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

        if ($seasonId) {
            $cacheKey = "statistics.index.{$seasonId}";

            $statisticsPayload = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($seasonId): array {
                $baseQuery = MatchPlayerStat::query()
                    ->join('matches', 'matches.id', '=', 'match_player_stats.match_id')
                    ->where('matches.competition_season_id', $seasonId)
                    ->where('matches.status', 'played');

                $topScorers = (clone $baseQuery)
                    ->with(['player', 'club'])
                    ->select(
                        'match_player_stats.player_id',
                        'match_player_stats.club_id',
                        DB::raw('SUM(match_player_stats.goals) as total_goals'),
                        DB::raw('SUM(match_player_stats.minutes_played) as total_minutes')
                    )
                    ->groupBy('match_player_stats.player_id', 'match_player_stats.club_id')
                    ->havingRaw('SUM(match_player_stats.goals) > 0')
                    ->orderByDesc('total_goals')
                    ->orderBy('total_minutes')
                    ->limit(10)
                    ->get();

                $topAssists = (clone $baseQuery)
                    ->with(['player', 'club'])
                    ->select(
                        'match_player_stats.player_id',
                        'match_player_stats.club_id',
                        DB::raw('SUM(match_player_stats.assists) as total_assists'),
                        DB::raw('SUM(match_player_stats.minutes_played) as total_minutes')
                    )
                    ->groupBy('match_player_stats.player_id', 'match_player_stats.club_id')
                    ->havingRaw('SUM(match_player_stats.assists) > 0')
                    ->orderByDesc('total_assists')
                    ->orderBy('total_minutes')
                    ->limit(10)
                    ->get();

                $topRatings = (clone $baseQuery)
                    ->with(['player', 'club'])
                    ->select(
                        'match_player_stats.player_id',
                        'match_player_stats.club_id',
                        DB::raw('AVG(match_player_stats.rating) as avg_rating'),
                        DB::raw('SUM(match_player_stats.minutes_played) as total_minutes')
                    )
                    ->groupBy('match_player_stats.player_id', 'match_player_stats.club_id')
                    ->havingRaw('SUM(match_player_stats.minutes_played) >= 270')
                    ->orderByDesc('avg_rating')
                    ->limit(10)
                    ->get()
                    ->map(function ($stat) {
                        $stat->avg_rating = round((float) $stat->avg_rating, 2);
                        return $stat;
                    });

                return [
                    'topScorers' => $topScorers,
                    'topAssists' => $topAssists,
                    'topRatings' => $topRatings,
                ];
            });

            $topScorers = $statisticsPayload['topScorers'];
            $topAssists = $statisticsPayload['topAssists'];
            $topRatings = $statisticsPayload['topRatings'];
        }

        return Inertia::render('Statistics/Index', [
            'seasons' => $seasons->map(fn($s) => ['id' => $s->id, 'name' => $s->competition->name . ' - ' . $s->name]),
            'activeSeasonId' => (int) $seasonId,
            'topScorers' => $topScorers,
            'topAssists' => $topAssists,
            'topRatings' => $topRatings,
        ]);
    }
}
