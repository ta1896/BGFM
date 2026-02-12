<?php

namespace App\Services;

use App\Models\Club;
use App\Models\CompetitionSeason;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatchSimulationService
{
    public function __construct(private readonly LeagueTableService $tableService)
    {
    }

    public function simulate(GameMatch $match): GameMatch
    {
        if ($match->status === 'played') {
            return $match->load([
                'homeClub',
                'awayClub',
                'events.player',
                'events.club',
                'playerStats.player',
                'playerStats.club',
            ]);
        }

        $match->loadMissing(['homeClub.players', 'awayClub.players']);

        $homePlayers = $this->resolveMatchSquad($match->homeClub, $match);
        $awayPlayers = $this->resolveMatchSquad($match->awayClub, $match);

        if ($homePlayers->isEmpty() || $awayPlayers->isEmpty()) {
            return $match;
        }

        $seed = $match->simulation_seed ?: random_int(10000, 99999);
        mt_srand($seed);

        $homeStrength = $this->teamStrength($homePlayers, true);
        $awayStrength = $this->teamStrength($awayPlayers, false);

        $homeGoals = $this->rollGoals($homeStrength, $awayStrength);
        $awayGoals = $this->rollGoals($awayStrength, $homeStrength);

        $events = array_merge(
            $this->buildGoalEvents($match, $match->home_club_id, $homeGoals, $homePlayers),
            $this->buildGoalEvents($match, $match->away_club_id, $awayGoals, $awayPlayers),
            $this->buildCardAndChanceEvents($match, $match->home_club_id, $homePlayers),
            $this->buildCardAndChanceEvents($match, $match->away_club_id, $awayPlayers)
        );

        usort($events, static fn (array $a, array $b) => [$a['minute'], $a['second']] <=> [$b['minute'], $b['second']]);

        DB::transaction(function () use ($match, $events, $homeGoals, $awayGoals, $homePlayers, $awayPlayers, $seed): void {
            $match->events()->delete();
            $match->playerStats()->delete();

            $match->update([
                'status' => 'played',
                'home_score' => $homeGoals,
                'away_score' => $awayGoals,
                'attendance' => $this->attendance($match->homeClub),
                'weather' => $this->weather(),
                'played_at' => now(),
                'simulation_seed' => $seed,
            ]);

            if ($events !== []) {
                $match->events()->createMany($events);
            }

            $this->createPlayerStats($match, $homePlayers, $awayPlayers);
        });

        if ($match->competition_season_id) {
            /** @var CompetitionSeason|null $competitionSeason */
            $competitionSeason = CompetitionSeason::find($match->competition_season_id);
            if ($competitionSeason) {
                $this->tableService->rebuild($competitionSeason);
            }
        }

        return $match->fresh([
            'homeClub',
            'awayClub',
            'events.player',
            'events.club',
            'playerStats.player',
            'playerStats.club',
        ]);
    }

    private function resolveMatchSquad(Club $club, GameMatch $match): Collection
    {
        /** @var Lineup|null $lineup */
        $lineup = $club->lineups()
            ->with('players')
            ->where('match_id', $match->id)
            ->first();

        if (!$lineup) {
            $lineup = $club->lineups()
                ->with('players')
                ->where('is_active', true)
                ->first();
        }

        if ($lineup && $lineup->players->isNotEmpty()) {
            $starters = $lineup->players
                ->filter(fn (Player $player) => !$player->pivot->is_bench)
                ->take(11)
                ->values();

            if ($starters->count() < 11) {
                $fallback = $lineup->players
                    ->whereNotIn('id', $starters->pluck('id'))
                    ->take(11 - $starters->count());
                $starters = $starters->concat($fallback)->values();
            }

            if ($starters->isNotEmpty()) {
                return $starters->take(11)->values();
            }
        }

        return $club->players()
            ->orderByDesc('overall')
            ->limit(11)
            ->get();
    }

    private function teamStrength(Collection $players, bool $isHome): float
    {
        $overall = (float) $players->avg('overall');
        $attack = (float) $players->avg('shooting');
        $buildUp = (float) $players->avg('passing');
        $defense = (float) $players->avg('defending');
        $condition = ((float) $players->avg('stamina') + (float) $players->avg('morale')) / 2;

        $score = ($overall * 0.4) + ($attack * 0.2) + ($buildUp * 0.15) + ($defense * 0.15) + ($condition * 0.1);

        if ($isHome) {
            $score += 3.5;
        }

        return $score;
    }

    private function rollGoals(float $attackStrength, float $defenseStrength): int
    {
        $expected = 1.35 + (($attackStrength - $defenseStrength) / 28);
        $expected = max(0.2, min(4.2, $expected + (mt_rand(0, 100) / 100) - 0.5));

        $goals = 0;
        for ($i = 0; $i < 6; $i++) {
            if ((mt_rand(1, 1000) / 1000) < ($expected / 6)) {
                $goals++;
            }
        }

        return min(8, $goals);
    }

    private function buildGoalEvents(GameMatch $match, int $clubId, int $goalCount, Collection $squad): array
    {
        if ($goalCount < 1) {
            return [];
        }

        $events = [];
        for ($i = 0; $i < $goalCount; $i++) {
            /** @var Player $scorer */
            $scorer = $this->weightedPlayerPick($squad, static fn (Player $player) => $player->shooting + $player->overall);

            $assist = null;
            if ($squad->count() > 1 && mt_rand(1, 100) <= 72) {
                $assist = $squad->where('id', '!=', $scorer->id)->random();
            }

            $events[] = [
                'minute' => mt_rand(4, 90),
                'second' => mt_rand(0, 59),
                'club_id' => $clubId,
                'player_id' => $scorer->id,
                'assister_player_id' => $assist?->id,
                'event_type' => 'goal',
                'metadata' => ['xg_bucket' => mt_rand(8, 35) / 100],
            ];
        }

        return $events;
    }

    private function buildCardAndChanceEvents(GameMatch $match, int $clubId, Collection $squad): array
    {
        $events = [];

        $yellowCount = mt_rand(0, 3);
        for ($i = 0; $i < $yellowCount; $i++) {
            /** @var Player $player */
            $player = $this->weightedPlayerPick($squad, static fn (Player $p) => max(10, 120 - $p->defending));
            $events[] = [
                'minute' => mt_rand(8, 90),
                'second' => mt_rand(0, 59),
                'club_id' => $clubId,
                'player_id' => $player->id,
                'event_type' => 'yellow_card',
                'metadata' => null,
            ];
        }

        if (mt_rand(1, 100) <= 8) {
            /** @var Player $player */
            $player = $squad->random();
            $events[] = [
                'minute' => mt_rand(35, 90),
                'second' => mt_rand(0, 59),
                'club_id' => $clubId,
                'player_id' => $player->id,
                'event_type' => 'red_card',
                'metadata' => null,
            ];
        }

        $chanceCount = mt_rand(1, 3);
        for ($i = 0; $i < $chanceCount; $i++) {
            /** @var Player $player */
            $player = $this->weightedPlayerPick($squad, static fn (Player $p) => $p->shooting + $p->pace);
            $events[] = [
                'minute' => mt_rand(2, 90),
                'second' => mt_rand(0, 59),
                'club_id' => $clubId,
                'player_id' => $player->id,
                'event_type' => 'chance',
                'metadata' => ['quality' => mt_rand(1, 100) <= 35 ? 'big' : 'normal'],
            ];
        }

        return $events;
    }

    private function weightedPlayerPick(Collection $squad, callable $weightResolver): Player
    {
        $total = max(1, (int) $squad->sum($weightResolver));
        $hit = mt_rand(1, $total);
        $cursor = 0;

        /** @var Player $player */
        foreach ($squad as $player) {
            $cursor += max(1, (int) $weightResolver($player));
            if ($cursor >= $hit) {
                return $player;
            }
        }

        return $squad->first();
    }

    private function createPlayerStats(GameMatch $match, Collection $homePlayers, Collection $awayPlayers): void
    {
        $goalEvents = $match->events()->where('event_type', 'goal')->get();
        $yellowEvents = $match->events()->where('event_type', 'yellow_card')->get();
        $redEvents = $match->events()->where('event_type', 'red_card')->get();

        $build = function (Collection $players, int $clubId) use ($match, $goalEvents, $yellowEvents, $redEvents): array {
            return $players->values()->map(function (Player $player, int $index) use ($match, $clubId, $goalEvents, $yellowEvents, $redEvents) {
                $goals = $goalEvents->where('player_id', $player->id)->count();
                $assists = $goalEvents->where('assister_player_id', $player->id)->count();
                $yellow = $yellowEvents->where('player_id', $player->id)->count();
                $red = $redEvents->where('player_id', $player->id)->count();

                $baseRating = 5.8
                    + ($player->overall / 50)
                    + ($goals * 0.7)
                    + ($assists * 0.4)
                    - ($yellow * 0.25)
                    - ($red * 0.9)
                    + ((mt_rand(0, 30) - 15) / 100);

                $role = $index < 11 ? 'starter' : 'bench';

                return [
                    'match_id' => $match->id,
                    'club_id' => $clubId,
                    'player_id' => $player->id,
                    'lineup_role' => $role,
                    'position_code' => $player->position,
                    'rating' => max(3.5, min(10.0, round($baseRating, 2))),
                    'minutes_played' => $role === 'starter' ? mt_rand(65, 96) : mt_rand(0, 26),
                    'goals' => $goals,
                    'assists' => $assists,
                    'yellow_cards' => $yellow,
                    'red_cards' => $red,
                    'shots' => max(0, $goals + mt_rand(0, 4)),
                    'passes_completed' => mt_rand(12, 74),
                    'passes_failed' => mt_rand(2, 19),
                    'tackles_won' => mt_rand(0, 8),
                    'tackles_lost' => mt_rand(0, 5),
                    'saves' => $player->position === 'GK' ? mt_rand(1, 8) : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();
        };

        DB::table('match_player_stats')->insert(array_merge(
            $build($homePlayers, $match->home_club_id),
            $build($awayPlayers, $match->away_club_id)
        ));
    }

    private function attendance(Club $homeClub): int
    {
        $homeClub->loadMissing('stadium');
        $capacity = (int) ($homeClub->stadium?->capacity ?? 18000);
        $experience = (int) ($homeClub->stadium?->fan_experience ?? 60);

        $base = max(4500, (int) round($homeClub->fanbase * (0.10 + ($experience / 1000))));
        $variation = mt_rand(-2500, 4200);
        $attendance = max(2500, $base + $variation);

        return min($capacity, $attendance);
    }

    private function weather(): string
    {
        $weather = ['clear', 'cloudy', 'rainy', 'windy'];

        return $weather[array_rand($weather)];
    }
}
