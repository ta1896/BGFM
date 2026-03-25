<?php

namespace App\Services;

use App\Models\Player;
use App\Models\MatchPlayerStat;
use App\Models\MatchLiveAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlayerPerformanceService
{
    /**
     * Get seasonal performance summary for a player.
     */
    public function getSeasonalSummary(Player $player): array
    {
        $stats = MatchPlayerStat::where('player_id', $player->id)
            ->select([
                DB::raw('COUNT(*) as matches_played'),
                DB::raw('SUM(goals) as goals'),
                DB::raw('SUM(assists) as assists'),
                DB::raw('SUM(xg) as total_xg'),
                DB::raw('SUM(xgot) as total_xgot'),
                DB::raw('SUM(shots) as total_shots'),
                DB::raw('SUM(passes_completed) as passes_completed'),
                DB::raw('SUM(passes_attempted) as passes_attempted'),
                DB::raw('SUM(long_balls_completed) as long_balls_completed'),
                DB::raw('SUM(long_balls_attempted) as long_balls_attempted'),
                DB::raw('SUM(chances_created) as chances_created'),
                DB::raw('SUM(big_chances_created) as big_chances_created'),
                DB::raw('SUM(tackles_won) as tackles_won'),
                DB::raw('SUM(tackles_lost) as tackles_lost'),
                DB::raw('SUM(interceptions) as interceptions'),
                DB::raw('SUM(recoveries) as recoveries'),
                DB::raw('SUM(clearances) as clearances'),
                DB::raw('SUM(duels_won) as duels_won'),
                DB::raw('SUM(duels_total) as duels_total'),
                DB::raw('SUM(aerials_won) as aerials_won'),
                DB::raw('SUM(aerials_total) as aerials_total'),
                DB::raw('SUM(dribbles_completed) as dribbles_completed'),
                DB::raw('SUM(dribbles_attempted) as dribbles_attempted'),
                DB::raw('SUM(saves) as saves'),
                DB::raw('AVG(rating) as avg_rating'),
            ])
            ->first();

        if (!$stats || $stats->matches_played == 0) {
            return $this->emptySummary();
        }

        return [
            'matches' => (int) $stats->matches_played,
            'goals' => (int) $stats->goals,
            'assists' => (int) $stats->assists,
            'xg' => round((float) $stats->total_xg, 2),
            'xgot' => round((float) $stats->total_xgot, 2),
            'xg_per_90' => $this->per90($stats->total_xg, $player), // Simplified
            'rating' => round((float) $stats->avg_rating, 2),
            'passing' => [
                'completed' => (int) $stats->passes_completed,
                'attempted' => (int) $stats->passes_attempted,
                'accuracy' => $this->accuracy($stats->passes_completed, $stats->passes_attempted),
            ],
            'long_balls' => [
                'completed' => (int) $stats->long_balls_completed,
                'attempted' => (int) $stats->long_balls_attempted,
                'accuracy' => $this->accuracy($stats->long_balls_completed, $stats->long_balls_attempted),
            ],
            'creation' => [
                'chances' => (int) $stats->chances_created,
                'big_chances' => (int) $stats->big_chances_created,
            ],
            'defending' => [
                'tackles_won' => (int) $stats->tackles_won,
                'interceptions' => (int) $stats->interceptions,
                'recoveries' => (int) $stats->recoveries,
                'clearances' => (int) $stats->clearances,
            ],
            'duels' => [
                'won' => (int) $stats->duels_won,
                'total' => (int) $stats->duels_total,
                'accuracy' => $this->accuracy($stats->duels_won, $stats->duels_total),
            ],
            'aerials' => [
                'won' => (int) $stats->aerials_won,
                'total' => (int) $stats->aerials_total,
                'accuracy' => $this->accuracy($stats->aerials_won, $stats->aerials_total),
            ],
            'dribbling' => [
                'completed' => (int) $stats->dribbles_completed,
                'attempted' => (int) $stats->dribbles_attempted,
                'accuracy' => $this->accuracy($stats->dribbles_completed, $stats->dribbles_attempted),
            ],
            'goalkeeping' => [
                'saves' => (int) $stats->saves,
            ],
        ];
    }

    /**
     * Get seasonal shot map for a player.
     */
    public function getShotMap(Player $player): Collection
    {
        return MatchLiveAction::where('player_id', $player->id)
            ->whereIn('action_type', ['shot', 'goal'])
            ->get()
            ->map(function(MatchLiveAction $action) {
                $meta = $action->metadata ?? [];
                return [
                    'minute' => $action->minute,
                    'is_goal' => $action->action_type === 'goal' || ($action->outcome === 'goal'),
                    'xg' => round((float) ($action->xg ?? $meta['xg'] ?? 0), 2),
                    'xgot' => round((float) ($action->xgot ?? $meta['xgot'] ?? 0), 2),
                    'x' => $action->x_coord ?? $meta['x'] ?? 50,
                    'y' => $action->y_coord ?? $meta['y'] ?? 50,
                    'foot' => $meta['foot'] ?? 'right',
                    'situation' => $meta['situation'] ?? 'open_play',
                ];
            });
    }

    private function accuracy($completed, $total): float
    {
        if ($total <= 0) return 0;
        return round(($completed / $total) * 100, 1);
    }

    private function per90($value, Player $player): float
    {
        // For now just divide by matches, assuming full games mostly
        $matches = MatchPlayerStat::where('player_id', $player->id)->count();
        if ($matches <= 0) return 0;
        return round($value / $matches, 2);
    }

    private function emptySummary(): array
    {
        return [
            'matches' => 0,
            'goals' => 0,
            'assists' => 0,
            'xg' => 0,
            'rating' => 0,
            'passing' => ['completed' => 0, 'attempted' => 0, 'accuracy' => 0],
            'defending' => ['tackles_won' => 0, 'interceptions' => 0, 'recoveries' => 0, 'clearances' => 0],
            'duels' => ['won' => 0, 'total' => 0, 'accuracy' => 0],
        ];
    }
}
