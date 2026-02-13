<?php

namespace App\Services;

use App\Models\GameMatch;
use Illuminate\Support\Facades\DB;

class PlayerCompetitionStatsService
{
    public function __construct(
        private readonly CompetitionContextService $contextService
    ) {
    }

    public function rebuildForMatchPlayers(GameMatch $match): void
    {
        $playerIds = $match->playerStats()
            ->pluck('player_id')
            ->map(fn ($playerId): int => (int) $playerId)
            ->filter(fn (int $playerId): bool => $playerId > 0)
            ->unique()
            ->values();

        if ($playerIds->isEmpty()) {
            return;
        }

        $rows = DB::table('match_player_stats as mps')
            ->join('matches as m', 'm.id', '=', 'mps.match_id')
            ->leftJoin('competition_seasons as cs', 'cs.id', '=', 'm.competition_season_id')
            ->leftJoin('competitions as c', 'c.id', '=', 'cs.competition_id')
            ->whereIn('mps.player_id', $playerIds->all())
            ->where('m.status', 'played')
            ->select([
                'mps.player_id',
                'mps.minutes_played',
                'mps.goals',
                'mps.assists',
                'mps.yellow_cards',
                'mps.red_cards',
                'm.type as match_type',
                'm.competition_context as match_context',
                'm.season_id as match_season_id',
                'cs.season_id as competition_season_id',
                'c.country_id as competition_country_id',
            ])
            ->get();

        $seasonRows = [];
        $careerRows = [];

        foreach ($rows as $row) {
            $playerId = (int) $row->player_id;
            if ($playerId < 1) {
                continue;
            }

            $context = $this->contextService->fromStoredOrRaw(
                $row->match_context ? (string) $row->match_context : null,
                (string) $row->match_type,
                $row->competition_country_id !== null ? (int) $row->competition_country_id : null
            );
            $seasonId = (int) ($row->match_season_id ?? $row->competition_season_id ?? 0);
            if ($seasonId < 1) {
                continue;
            }

            $seasonKey = $playerId.'|'.$seasonId.'|'.$context;
            if (!isset($seasonRows[$seasonKey])) {
                $seasonRows[$seasonKey] = [
                    'player_id' => $playerId,
                    'season_id' => $seasonId,
                    'competition_context' => $context,
                    'appearances' => 0,
                    'minutes_played' => 0,
                    'goals' => 0,
                    'assists' => 0,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $seasonRows[$seasonKey]['appearances']++;
            $seasonRows[$seasonKey]['minutes_played'] += (int) $row->minutes_played;
            $seasonRows[$seasonKey]['goals'] += (int) $row->goals;
            $seasonRows[$seasonKey]['assists'] += (int) $row->assists;
            $seasonRows[$seasonKey]['yellow_cards'] += (int) $row->yellow_cards;
            $seasonRows[$seasonKey]['red_cards'] += (int) $row->red_cards;

            $careerKey = $playerId.'|'.$context;
            if (!isset($careerRows[$careerKey])) {
                $careerRows[$careerKey] = [
                    'player_id' => $playerId,
                    'competition_context' => $context,
                    'appearances' => 0,
                    'minutes_played' => 0,
                    'goals' => 0,
                    'assists' => 0,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $careerRows[$careerKey]['appearances']++;
            $careerRows[$careerKey]['minutes_played'] += (int) $row->minutes_played;
            $careerRows[$careerKey]['goals'] += (int) $row->goals;
            $careerRows[$careerKey]['assists'] += (int) $row->assists;
            $careerRows[$careerKey]['yellow_cards'] += (int) $row->yellow_cards;
            $careerRows[$careerKey]['red_cards'] += (int) $row->red_cards;
        }

        DB::transaction(function () use ($playerIds, $seasonRows, $careerRows): void {
            DB::table('player_season_competition_statistics')
                ->whereIn('player_id', $playerIds->all())
                ->delete();

            DB::table('player_career_competition_statistics')
                ->whereIn('player_id', $playerIds->all())
                ->delete();

            if ($seasonRows !== []) {
                DB::table('player_season_competition_statistics')->insert(array_values($seasonRows));
            }

            if ($careerRows !== []) {
                DB::table('player_career_competition_statistics')->insert(array_values($careerRows));
            }
        });
    }
}
