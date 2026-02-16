<?php

namespace App\Services;

use App\Models\Club;
use App\Models\CompetitionSeason;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use App\Services\MatchEngine\NarrativeEngine;
use App\Services\MatchEngine\TacticalManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatchSimulationService
{
    public function __construct(
        private readonly StatisticsAggregationService $statisticsAggregationService,
        private readonly PlayerPositionService $positionService,
        private readonly TacticalManager $tacticalManager,
        private readonly NarrativeEngine $narrativeEngine
    ) {
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

        $homeLineup = $this->resolveLineup($match->homeClub, $match);
        $awayLineup = $this->resolveLineup($match->awayClub, $match);

        $homePlayers = $this->extractPlayers($homeLineup, $match->homeClub);
        $awayPlayers = $this->extractPlayers($awayLineup, $match->awayClub);

        if ($homePlayers->isEmpty() || $awayPlayers->isEmpty()) {
            return $match;
        }

        $seed = $match->simulation_seed ?: random_int(10000, 99999);
        mt_srand($seed);

        $homeStrength = $this->teamStrength($homePlayers, true, $homeLineup);
        $awayStrength = $this->teamStrength($awayPlayers, false, $awayLineup);

        $homeGoals = $this->rollGoals($homeStrength, $awayStrength);
        $awayGoals = $this->rollGoals($awayStrength, $homeStrength);

        $homeEvents = array_merge(
            $this->buildGoalEvents($match, $match->home_club_id, $homeGoals, $homePlayers),
            $this->buildCardAndChanceEvents($match, $match->home_club_id, $homePlayers),
            $this->buildGenericEvents($match, $match->home_club_id, $homePlayers)
        );

        $awayEvents = array_merge(
            $this->buildGoalEvents($match, $match->away_club_id, $awayGoals, $awayPlayers),
            $this->buildCardAndChanceEvents($match, $match->away_club_id, $awayPlayers),
            $this->buildGenericEvents($match, $match->away_club_id, $awayPlayers)
        );

        $gamePhaseEvents = [
            [
                'minute' => 0,
                'second' => 0,
                'club_id' => $match->home_club_id,
                'player_id' => null,
                'event_type' => 'kickoff',
                'metadata' => null,
            ],
            [
                'minute' => 45,
                'second' => 0,
                'club_id' => null,
                'player_id' => null,
                'event_type' => 'half_time',
                'metadata' => null,
            ],
            [
                'minute' => 90,
                'second' => 0,
                'club_id' => null,
                'player_id' => null,
                'event_type' => 'full_time',
                'metadata' => null,
            ]
        ];

        $events = array_merge($homeEvents, $awayEvents, $gamePhaseEvents);

        usort($events, static fn(array $a, array $b) => [$a['minute'], $a['second']] <=> [$b['minute'], $b['second']]);

        // Generate narrative texts for each event using ticker templates
        $usedTemplateIds = [];
        $allPlayers = $homePlayers->concat($awayPlayers)->keyBy('id');
        foreach ($events as &$event) {
            $player = $allPlayers->get($event['player_id']);
            $clubId = $event['club_id'];
            $isHome = $clubId === $match->home_club_id;
            $clubName = $isHome
                ? ($match->homeClub->short_name ?? $match->homeClub->name)
                : ($match->awayClub->short_name ?? $match->awayClub->name);

            $opponentName = !$isHome
                ? ($match->homeClub->short_name ?? $match->homeClub->name)
                : ($match->awayClub->short_name ?? $match->awayClub->name);

            $assisterName = null;
            if (!empty($event['assister_player_id'])) {
                $assister = $allPlayers->get($event['assister_player_id']);
                $assisterName = $assister?->full_name;
            }

            $event['narrative'] = $this->narrativeEngine->generate(
                $event['event_type'],
                [
                    'player' => $player?->full_name ?? 'Spieler',
                    'club' => $clubName,
                    'opponent' => $opponentName,
                    'assister' => $assisterName ?? 'Mitspieler',
                    'minute' => $event['minute'],
                    'score' => ($homeGoals ?? 0) . ':' . ($awayGoals ?? 0),
                ],
                'de',
                $usedTemplateIds
            );
        }
        unset($event);

        DB::transaction(function () use ($match, $events, $homeGoals, $awayGoals, $homePlayers, $awayPlayers, $seed): void {
            $match->events()->delete();
            $match->playerStats()->delete();

            // Clean up any live simulation data if the match was in live mode
            DB::table('match_live_actions')->where('match_id', $match->id)->delete();

            $match->update([
                'status' => 'played',
                'home_score' => $homeGoals,
                'away_score' => $awayGoals,
                'attendance' => $this->attendance($match->homeClub),
                'weather' => $this->weather(),
                'played_at' => now(),
                'simulation_seed' => $seed,
                'live_minute' => 0,
                'live_paused' => false,
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
                $this->statisticsAggregationService->rebuildLeagueTable($competitionSeason);
            }
        }

        $this->statisticsAggregationService->rebuildPlayerCompetitionStatsForMatch($match->fresh());

        return $match->fresh([
            'homeClub',
            'awayClub',
            'events.player',
            'events.club',
            'playerStats.player',
            'playerStats.club',
        ]);
    }

    private function teamStrength(Collection $players, bool $isHome, ?Lineup $lineup = null): float
    {
        $overall = (float) $players->avg('overall');
        $attack = (float) $players->avg('shooting');
        $buildUp = (float) $players->avg('passing');
        $defense = (float) $players->avg('defending');
        $condition = ((float) $players->avg('stamina') + (float) $players->avg('morale')) / 2;

        $score = ($overall * 0.4) + ($attack * 0.2) + ($buildUp * 0.15) + ($defense * 0.15) + ($condition * 0.1);

        // Apply tactical modifiers
        if ($lineup) {
            $mods = $this->tacticalManager->getTacticalModifiers($lineup);
            $score *= (($mods['attack'] + $mods['defense'] + $mods['possession']) / 3);
        }

        if ($isHome) {
            $score += 3.5;
        }

        return $score;
    }

    private function resolveLineup(Club $club, GameMatch $match): ?Lineup
    {
        return $club->lineups()
            ->with(['players'])
            ->where('match_id', $match->id)
            ->first()
            ?? $club->lineups()
                ->with(['players'])
                ->where('is_active', true)
                ->first();
    }

    private function extractPlayers(?Lineup $lineup, Club $club): Collection
    {
        if (!$lineup || $lineup->players->isEmpty()) {
            return $club->players()->orderByDesc('overall')->limit(11)->get();
        }

        $starters = $lineup->players->filter(fn($p) => !$p->pivot->is_bench)->take(11)->values();
        if ($starters->count() < 11) {
            $ids = $starters->pluck('id');
            $fallback = $club->players()->whereNotIn('id', $ids)->orderByDesc('overall')->limit(11 - $starters->count())->get();
            $starters = $starters->concat($fallback);
        }

        return $starters->take(11)->values();
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
            $scorer = $this->weightedPlayerPick($squad, static fn(Player $player) => $player->shooting + $player->overall);

            $assist = null;
            if ($squad->count() > 1 && mt_rand(1, 100) <= 72) {
                $assistCandidates = $squad->where('id', '!=', $scorer->id)->values();
                $assist = $this->randomCollectionItem($assistCandidates);
            }

            $goalType = mt_rand(1, 100);
            if ($goalType <= 60)
                $type = 'aus dem Spiel';
            elseif ($goalType <= 85)
                $type = 'Kopfball';
            elseif ($goalType <= 95)
                $type = 'Fernschuss';
            else
                $type = 'Abstauber';

            $events[] = [
                'minute' => mt_rand(4, 90),
                'second' => mt_rand(0, 59),
                'club_id' => $clubId,
                'player_id' => $scorer->id,
                'assister_player_id' => $assist?->id,
                'event_type' => 'goal',
                'metadata' => [
                    'xg_bucket' => mt_rand(8, 35) / 100,
                    'goal_type' => $type,
                    'assister_name' => $assist?->full_name,
                ],
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
            $player = $this->weightedPlayerPick($squad, static fn(Player $p) => max(10, 120 - $p->defending));
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
            $player = $this->randomCollectionItem($squad);
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
            $player = $this->weightedPlayerPick($squad, static fn(Player $p) => $p->shooting + $p->pace);
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

    private function buildGenericEvents(GameMatch $match, int $clubId, Collection $squad): array
    {
        $events = [];
        $count = mt_rand(6, 12); // Scatters 6-12 generic events per team

        for ($i = 0; $i < $count; $i++) {
            $typeRoll = mt_rand(1, 100);

            if ($typeRoll <= 20)
                $type = 'foul';
            elseif ($typeRoll <= 35)
                $type = 'corner';
            elseif ($typeRoll <= 48)
                $type = 'shot';
            elseif ($typeRoll <= 58)
                $type = 'free_kick';
            elseif ($typeRoll <= 66)
                $type = 'offside';
            elseif ($typeRoll <= 76)
                $type = 'throw_in';
            elseif ($typeRoll <= 86)
                $type = 'clearance';
            elseif ($typeRoll <= 93)
                $type = 'turnover';
            else
                $type = 'midfield_possession';

            /** @var Player $player */
            $player = $this->randomCollectionItem($squad);

            $events[] = [
                'minute' => mt_rand(1, 90),
                'second' => mt_rand(0, 59),
                'club_id' => $clubId,
                'player_id' => $player->id,
                'event_type' => $type,
                'metadata' => null,
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
                    'position_code' => $this->positionCodeForStat($player),
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
                    'saves' => $this->isGoalkeeper($player) ? mt_rand(1, 8) : 0,
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

    private function positionCodeForStat(Player $player): string
    {
        $slot = strtoupper(trim((string) ($player->pivot?->pitch_position ?? '')));
        if ($slot !== '') {
            if (str_starts_with($slot, 'BANK-')) {
                return 'SUB';
            }

            if (strlen($slot) <= 4) {
                return $slot;
            }
        }

        $position = strtoupper((string) $player->position);
        return strlen($position) <= 4 ? $position : substr($position, 0, 4);
    }

    private function isGoalkeeper(Player $player): bool
    {
        return $this->positionService->groupFromPosition($player->position) === 'GK';
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

        return $weather[$this->randomArrayKey($weather)];
    }

    private function randomArrayKey(array $values): int|string
    {
        if ($values === []) {
            return 0;
        }

        $keys = array_keys($values);
        $index = mt_rand(0, max(0, count($keys) - 1));

        return $keys[$index];
    }

    private function randomCollectionItem(Collection $collection): Player
    {
        /** @var Player|null $fallback */
        $fallback = $collection->first();
        if (!$fallback) {
            throw new \RuntimeException('Cannot pick random item from empty collection.');
        }

        $items = $collection->values();
        $index = mt_rand(0, max(0, $items->count() - 1));

        /** @var Player|null $picked */
        $picked = $items->get($index);

        return $picked ?? $fallback;
    }
}
