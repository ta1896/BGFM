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
use Illuminate\Support\Str;

class MatchSimulationService
{
    public function __construct(
        private readonly StatisticsAggregationService $statisticsAggregationService,
        private readonly PlayerPositionService $positionService,
        private readonly TacticalManager $tacticalManager,
        private readonly NarrativeEngine $narrativeEngine,
        private readonly SimulationSettingsService $settingsService
    ) {
    }

    public function simulate(GameMatch $match): GameMatch
    {
        $result = $this->calculateSimulation($match);

        DB::transaction(function () use ($match, $result): void {
            // Clean up any existing simulation data first
            DB::table('match_live_actions')->where('match_id', $match->id)->delete();
            DB::table('match_live_team_states')->where('match_id', $match->id)->delete();
            DB::table('match_player_stats')->where('match_id', $match->id)->delete();
            $match->events()->delete();

            $match->update([
                'status' => 'played',
                'home_score' => $result['home_score'],
                'away_score' => $result['away_score'],
                'attendance' => $result['attendance'],
                'weather' => $result['weather'],
                'played_at' => now(),
                'simulation_seed' => $result['seed'],
                'live_minute' => 0,
                'live_paused' => false,
            ]);

            if ($result['events'] !== []) {
                $match->events()->createMany($result['events']);
            }

            $playerStats = $this->createPlayerStats($match, $result['home_players'], $result['away_players']);
            DB::table('match_player_stats')->insert($playerStats);
            $this->createLiveTeamStats($match, $playerStats);
            $this->createLiveActions($match, $result['events']);
        });

        if ($match->competition_season_id) {
            /** @var CompetitionSeason|null $competitionSeason */
            $competitionSeason = CompetitionSeason::find($match->competition_season_id);
            if ($competitionSeason) {
                $this->statisticsAggregationService->rebuildLeagueTable($competitionSeason);
            }
        }

        $this->statisticsAggregationService->rebuildPlayerCompetitionStatsForMatch($match->fresh());

        return $match;
    }

    /**
     * Calculate simulation results without persisting to DB.
     */
    public function calculateSimulation(GameMatch $match, array $options = []): array
    {
        if ($match->status === 'played' && !($options['is_sandbox'] ?? false)) {
            $match->refresh();
        }

        $match->load([
            'homeClub.stadium',
            'awayClub',
            'competitionSeason',
        ]);

        $homeLineup = $this->resolveLineup($match->homeClub, $match);
        $awayLineup = $this->resolveLineup($match->awayClub, $match);

        $homePlayers = $this->extractPlayers($homeLineup, $match->homeClub);
        $awayPlayers = $this->extractPlayers($awayLineup, $match->awayClub);

        $homeStrength = $this->teamStrength($homePlayers, true, $homeLineup);
        $awayStrength = $this->teamStrength($awayPlayers, false, $awayLineup);


        $homeGoals = $this->rollGoals($homeStrength, $awayStrength);
        $awayGoals = $this->rollGoals($awayStrength, $homeStrength);

        $matchEvents = [];
        $matchEvents = array_merge($matchEvents, $this->buildGoalEvents($match, $match->home_club_id, $homeGoals, $homePlayers));
        $matchEvents = array_merge($matchEvents, $this->buildGoalEvents($match, $match->away_club_id, $awayGoals, $awayPlayers));
        $matchEvents = array_merge($matchEvents, $this->buildCardAndChanceEvents($match, $match->home_club_id, $homePlayers));
        $matchEvents = array_merge($matchEvents, $this->buildCardAndChanceEvents($match, $match->away_club_id, $awayPlayers));
        $matchEvents = array_merge($matchEvents, $this->buildGenericEvents($match, $match->home_club_id, $homePlayers, $awayPlayers));
        $matchEvents = array_merge($matchEvents, $this->buildGenericEvents($match, $match->away_club_id, $awayPlayers, $homePlayers));
        $matchEvents = array_merge($matchEvents, $this->buildInjuryEvents($match, $match->home_club_id, $homePlayers));
        $matchEvents = array_merge($matchEvents, $this->buildInjuryEvents($match, $match->away_club_id, $awayPlayers));
        $matchEvents = array_merge($matchEvents, $this->buildSubstitutionEvents($match, $match->home_club_id, $homePlayers));
        $matchEvents = array_merge($matchEvents, $this->buildSubstitutionEvents($match, $match->away_club_id, $awayPlayers));

        // Sort events by time
        usort($matchEvents, static fn($a, $b) => ($a['minute'] * 60 + $a['second']) <=> ($b['minute'] * 60 + $b['second']));

        // Add sequence numbers and narratives
        $events = [];
        $sequence = 1;
        $currentHomeScore = 0;
        $currentAwayScore = 0;

        foreach ($matchEvents as $eventData) {
            if ($eventData['event_type'] === 'goal') {
                if ($eventData['club_id'] === $match->home_club_id) {
                    $currentHomeScore++;
                } else {
                    $currentAwayScore++;
                }
            }

            $eventData['score'] = "{$currentHomeScore}:{$currentAwayScore}";
            $eventData['sequence'] = $sequence++;
            $eventData['narrative'] = $this->narrativeEngine->generate($eventData['event_type'], $eventData);
            $events[] = $eventData;
        }

        return [
            'home_score' => $homeGoals,
            'away_score' => $awayGoals,
            'events' => $events,
            'attendance' => min(60000, $this->attendance($match->homeClub)),
            'weather' => $this->weather(),
            'seed' => mt_rand(100000, 999999999),
            'home_players' => $homePlayers,
            'away_players' => $awayPlayers,
        ];
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
                'player' => $scorer->full_name,
                'player_name' => $scorer->full_name,
                'club' => ($clubId === (int) $match->home_club_id) ? $match->homeClub->name : $match->awayClub->name,
                'club_name' => ($clubId === (int) $match->home_club_id) ? $match->homeClub->name : $match->awayClub->name,
                'assister_name' => $assist?->full_name,
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
                'player' => $player->full_name,
                'player_name' => $player->full_name,
                'club' => ($clubId === (int) $match->home_club_id) ? $match->homeClub->name : $match->awayClub->name,
                'club_name' => ($clubId === (int) $match->home_club_id) ? $match->homeClub->name : $match->awayClub->name,
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
                'player' => $player->full_name,
                'player_name' => $player->full_name,
                'club' => ($clubId === (int) $match->home_club_id) ? $match->homeClub->name : $match->awayClub->name,
                'club_name' => ($clubId === (int) $match->home_club_id) ? $match->homeClub->name : $match->awayClub->name,
                'event_type' => 'red_card',
                'metadata' => null,
            ];
        }

        $chanceCount = mt_rand(2, 4);
        for ($i = 0; $i < $chanceCount; $i++) {
            /** @var Player $player */
            $player = $this->weightedPlayerPick($squad, static fn(Player $p) => $p->shooting + $p->pace);
            $events[] = [
                'minute' => mt_rand(2, 90),
                'second' => mt_rand(0, 59),
                'club_id' => $clubId,
                'player_id' => $player->id,
                'player' => $player->full_name,
                'player_name' => $player->full_name,
                'club' => ($clubId === (int) $match->home_club_id) ? $match->homeClub->name : $match->awayClub->name,
                'club_name' => ($clubId === (int) $match->home_club_id) ? $match->homeClub->name : $match->awayClub->name,
                'event_type' => 'chance',
                'metadata' => ['quality' => mt_rand(1, 100) <= 35 ? 'big' : 'normal'],
            ];
        }

        return $events;
    }

    private function buildInjuryEvents(GameMatch $match, int $clubId, Collection $squad): array
    {
        if (mt_rand(1, 100) > 12) { // 12% chance for an injury per team
            return [];
        }

        /** @var Player $player */
        $player = $this->randomCollectionItem($squad);
        if (!$player) {
            return [];
        }

        $clubName = ($clubId === (int) $match->home_club_id)
            ? ($match->homeClub->short_name ?? $match->homeClub->name)
            : ($match->awayClub->short_name ?? $match->awayClub->name ?? 'Club');

        return [
            [
                'minute' => mt_rand(5, 88),
                'second' => mt_rand(0, 59),
                'club_id' => $clubId,
                'player_id' => $player->id,
                'player' => $player->full_name,
                'player_name' => $player->full_name,
                'club' => $clubName,
                'club_name' => $clubName,
                'event_type' => 'injury',
                'metadata' => ['is_serious' => mt_rand(1, 100) <= 20],
            ]
        ];
    }

    private function buildSubstitutionEvents(GameMatch $match, int $clubId, Collection $squad): array
    {
        $events = [];
        $count = mt_rand(1, 3); // Simulating 1-3 subs per match

        for ($i = 0; $i < $count; $i++) {
            /** @var Player $playerOut */
            $playerOut = $this->randomCollectionItem($squad);
            if (!$playerOut) {
                continue;
            }

            $clubName = ($clubId === (int) $match->home_club_id)
                ? ($match->homeClub->short_name ?? $match->homeClub->name)
                : ($match->awayClub->short_name ?? $match->awayClub->name ?? 'Club');

            $events[] = [
                'minute' => mt_rand(45, 89),
                'second' => mt_rand(0, 59),
                'club_id' => $clubId,
                'player_id' => $playerOut->id,
                'player' => $playerOut->full_name,
                'player_name' => $playerOut->full_name,
                'club' => $clubName,
                'club_name' => $clubName,
                'event_type' => 'substitution',
                'metadata' => [
                    'player_in' => 'Einwechselspieler', // Simplified for lab
                ],
            ];
        }

        return $events;
    }

    private function buildGenericEvents(GameMatch $match, int $clubId, Collection $squad, Collection $opponentSquad): array
    {
        $events = [];
        // Increased frequency: generate 8-15 generic events per team
        $count = mt_rand(8, 15);

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
            $clubName = ($clubId === (int) $match->home_club_id) ? ($match->homeClub->short_name ?? $match->homeClub->name) : ($match->awayClub->short_name ?? $match->awayClub->name);

            $eventData = [
                'player' => $player->full_name,
                'player_name' => $player->full_name,
                'club' => $clubName,
                'club_name' => $clubName,
            ];

            if (in_array($type, ['foul', 'free_kick', 'turnover'])) {
                /** @var Player $opponent */
                $opponent = $this->randomCollectionItem($opponentSquad);
                $eventData['opponent'] = $opponent->full_name;
                $eventData['opponent_name'] = $opponent->full_name;
                $eventData['opponent_player_id'] = $opponent->id;
            }

            $events[] = array_merge([
                'minute' => mt_rand(1, 90),
                'second' => mt_rand(0, 59),
                'club_id' => $clubId,
                'player_id' => $player->id,
                'event_type' => $type,
                'metadata' => null,
            ], $eventData);
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

    private function createPlayerStats(GameMatch $match, Collection $homePlayers, Collection $awayPlayers): array
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
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
        };

        return array_merge(
            $build($homePlayers, $match->home_club_id),
            $build($awayPlayers, $match->away_club_id)
        );
    }

    private function createLiveTeamStats(GameMatch $match, array $playerStats): void
    {
        $homeStats = collect($playerStats)->where('club_id', $match->home_club_id);
        $awayStats = collect($playerStats)->where('club_id', $match->away_club_id);

        $teamStats = [];
        foreach ([$homeStats, $awayStats] as $stats) {
            $clubId = $stats->first()['club_id'];
            $teamStats[] = [
                'match_id' => $match->id,
                'club_id' => $clubId,
                'possession_seconds' => mt_rand(1200, 3200), // Placeholder
                'actions_count' => mt_rand(200, 600),
                'dangerous_attacks' => mt_rand(10, 50),
                'pass_attempts' => $stats->sum('passes_completed') + mt_rand(20, 100),
                'pass_completions' => $stats->sum('passes_completed'),
                'tackle_attempts' => mt_rand(15, 45),
                'tackle_won' => mt_rand(5, 40),
                'fouls_committed' => mt_rand(5, 20),
                'corners_won' => mt_rand(0, 12),
                'shots' => $stats->sum('shots'),
                'shots_on_target' => $stats->sum('goals') + mt_rand(0, 5),
                'expected_goals' => mt_rand(50, 400) / 100, // 0.5 - 4.0
                'yellow_cards' => $stats->sum('yellow_cards'),
                'red_cards' => $stats->sum('red_cards'),
                'substitutions_used' => 0,
                'tactical_changes_count' => 0,
                'last_tactical_change_minute' => null,
                'last_substitution_minute' => null,
                'tactical_style' => 'balanced',
                'phase' => 'play',
                'current_ball_carrier_player_id' => null,
                'last_set_piece_taker_player_id' => null,
                'last_set_piece_type' => null,
                'last_set_piece_minute' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('match_live_team_states')->insert($teamStats);
    }

    private function createLiveActions(GameMatch $match, array $events): void
    {
        $actions = [];
        $globalSequence = 1;

        foreach ($events as $event) {
            $type = $event['event_type'];
            $clubId = $event['club_id'];
            $isHome = $clubId === $match->home_club_id;

            // Prepare metadata
            $eventMetadata = $event['metadata'] ?? [];
            if (!is_array($eventMetadata)) {
                $eventMetadata = [];
            }
            $mergedMetadata = array_merge($eventMetadata, ['simulated' => true]);

            // Create action for this event
            $actions[] = [
                'match_id' => $match->id,
                'club_id' => $clubId,
                'player_id' => $event['player_id'],
                'opponent_player_id' => $event['opponent_player_id'] ?? null,
                'action_type' => $type,
                'minute' => $event['minute'],
                'second' => $event['second'] ?? mt_rand(0, 59),
                'sequence' => $globalSequence++,
                'x_coord' => $isHome ? mt_rand(75, 95) : mt_rand(5, 25),
                'y_coord' => mt_rand(10, 90),
                'xg' => $eventMetadata['xg_bucket'] ?? null,
                'narrative' => $event['narrative'] ?? null,
                'metadata' => json_encode($mergedMetadata),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($actions)) {
            DB::table('match_live_actions')->insert($actions);
        }
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
