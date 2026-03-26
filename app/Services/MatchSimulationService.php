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
        private readonly MatchPlayerRoleService $matchPlayerRoleService,
        private readonly MatchLineupService $matchLineupService,
        private readonly MatchStrengthService $matchStrengthService,
        private readonly MatchEnvironmentService $matchEnvironmentService,
        private readonly MatchRandomService $matchRandomService,
        private readonly TacticalManager $tacticalManager,
        private readonly NarrativeEngine $narrativeEngine
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
        $startTime = microtime(true);

        if ($match->status === 'played' && !($options['is_sandbox'] ?? false)) {
            $match->refresh();
        }

        $match->load([
            'homeClub.stadium',
            'awayClub',
            'competitionSeason',
        ]);

        $homeLineup = $this->matchLineupService->resolvePreferredLineup($match->homeClub, $match);
        $awayLineup = $this->matchLineupService->resolvePreferredLineup($match->awayClub, $match);

        // Override Formations (for Tactics Lab)
        if (isset($options['force_home_formation']) && $homeLineup) {
            $homeLineup->formation = $options['force_home_formation'];
        }
        if (isset($options['force_away_formation']) && $awayLineup) {
            $awayLineup->formation = $options['force_away_formation'];
        }

        $homePlayers = $this->matchLineupService->resolveStarters($match->homeClub, $match);
        $awayPlayers = $this->matchLineupService->resolveStarters($match->awayClub, $match);

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

            // VAR Implementation: 15% chance for VAR on goals (open play or penalty)
            $isGoal = $eventData['event_type'] === 'goal';
            $isPenalty = ($eventData['metadata']['situation'] ?? '') === 'penalty';
            if (($isGoal || $isPenalty) && mt_rand(1, 100) <= 20) {
                $lastEvent = end($events);
                
                // Add Check Event
                $checkEvent = $lastEvent;
                $checkEvent['event_type'] = 'var_check';
                $checkEvent['sequence'] = $sequence++;
                $checkEvent['second'] = ($checkEvent['second'] + 2) % 60;
                $checkEvent['narrative'] = "VAR-CHECK: Der Schiedsrichter überprüft ein mögliches " . ($isPenalty ? 'Foulspiel' : 'Abseits') . "...";
                $checkEvent['metadata'] = array_merge($checkEvent['metadata'] ?? [], ['var_status' => 'checking']);
                $events[] = $checkEvent;

                // Add Decision Event (15 seconds later)
                $decisionEvent = $lastEvent;
                $decisionEvent['event_type'] = 'var_decision';
                $decisionEvent['sequence'] = $sequence++;
                $decisionEvent['second'] = ($decisionEvent['second'] + 15) % 60;
                if ($decisionEvent['second'] < 15) $decisionEvent['minute']++;
                
                $decisionEvent['narrative'] = "ENTSCHEIDUNG BESTÄTIGT! Das Tor zählt und die Zuschauer beben vor Freude!";
                $decisionEvent['metadata'] = array_merge($decisionEvent['metadata'] ?? [], ['var_status' => 'confirmed']);
                $events[] = $decisionEvent;
            }
        }

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'home_score' => $homeGoals,
            'away_score' => $awayGoals,
            'events' => $events,
            'attendance' => min(60000, $this->matchEnvironmentService->attendance($match->homeClub)),
            'weather' => $this->matchEnvironmentService->weather(),
            'seed' => mt_rand(100000, 999999999),
            'home_players' => $homePlayers,
            'away_players' => $awayPlayers,
            'duration_ms' => $duration,
            'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];
    }

    private function teamStrength(Collection $players, bool $isHome, ?Lineup $lineup = null): float
    {
        $multiplier = 1.0;
        if ($lineup) {
            $mods = $this->tacticalManager->getTacticalModifiers($lineup);
            
            // Average the core modifiers
            $multiplier = (($mods['attack'] + $mods['defense'] + $mods['possession']) / 3);

            // Apply individual instruction bonuses to the team multiplier
            $instructionBonus = 0;
            foreach ($players as $player) {
                /** @var Player $player */
                $instructions = $this->parseInstructions($player);
                if (in_array('playmaker', $instructions)) $instructionBonus += 0.01;
                if (in_array('tight_marking', $instructions)) $instructionBonus += 0.005;
                if (in_array('stay_back', $instructions)) $instructionBonus += 0.005;
            }
            $multiplier += $instructionBonus;
        }

        return $this->matchStrengthService->fromPlayers($players, $isHome, $multiplier);
    }

    private function parseInstructions(Player $player): array
    {
        $raw = $player->pivot->instructions ?? '[]';
        if (is_array($raw)) return $raw;
        
        try {
            return json_decode($raw, true) ?: [];
        } catch (\Exception $e) {
            return [];
        }
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
        if ($goalCount < 1 || $squad->isEmpty()) {
            return [];
        }

        $events = [];
        for ($i = 0; $i < $goalCount; $i++) {
            /** @var Player $scorer */
            $scorer = $this->weightedPlayerPick($squad, function(Player $player) {
                $weight = $player->shooting + $player->overall;
                $instructions = $this->parseInstructions($player);
                
                if (in_array('shoot_on_sight', $instructions)) $weight *= 1.25;
                if (in_array('run_behind', $instructions)) $weight *= 1.15;
                if (in_array('stay_back', $instructions)) $weight *= 0.5;
                
                return $weight;
            });

            $lineup = ($clubId === (int) $match->home_club_id) 
                ? ($match->home_lineup ?? $this->matchLineupService->resolvePreferredLineup($match->homeClub, $match))
                : ($match->away_lineup ?? $this->matchLineupService->resolvePreferredLineup($match->awayClub, $match));

            $opponentLineup = ($clubId === (int) $match->home_club_id)
                ? ($match->away_lineup ?? $this->matchLineupService->resolvePreferredLineup($match->awayClub, $match))
                : ($match->home_lineup ?? $this->matchLineupService->resolvePreferredLineup($match->homeClub, $match));

            $goalType = mt_rand(1, 100);
            $type = 'aus dem Spiel';
            $assist = null;

            if ($goalType > 95) {
                $type = 'Elfmeter';
                if ($lineup && $lineup->penalty_taker_player_id) {
                    $taker = $squad->firstWhere('id', $lineup->penalty_taker_player_id);
                    if ($taker) $scorer = $taker;
                }
            } else {
                if ($squad->count() > 1 && mt_rand(1, 100) <= 72) {
                    $assistCandidates = $squad->where('id', '!=', $scorer->id)->values();
                    $assist = $this->randomCollectionItem($assistCandidates);
                }

                // Marking Strategy Impact
                $headerChance = 25; // Base 25% (85 - 60)
                if ($opponentLineup) {
                    $strategy = $opponentLineup->corner_marking_strategy ?? 'zonal';
                    if ($strategy === 'zonal') $headerChance -= 5;
                    elseif ($strategy === 'player') $headerChance += 3;
                }

                if ($goalType <= 60)
                    $type = 'aus dem Spiel';
                elseif ($goalType <= (60 + $headerChance))
                    $type = 'Kopfball';
                else
                    $type = 'Fernschuss';
            }

            $xg = round(mt_rand(35, 95) / 100, 2);
            $xgot = round(mt_rand(75, 99) / 100, 2);
            $foot = ($type === 'Kopfball') ? 'head' : (mt_rand(1, 100) <= 65 ? 'right' : 'left');
            $situation = ($type === 'Elfmeter') ? 'penalty' : (mt_rand(1, 100) <= 15 ? 'set_piece' : 'open_play');
            $x = mt_rand(35, 65);
            $y = mt_rand(85, 98);

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
                    'xg' => $xg,
                    'xgot' => $xgot,
                    'goal_type' => $type,
                    'assister_name' => $assist?->full_name,
                    'foot' => $foot,
                    'situation' => $situation,
                    'x' => $x,
                    'y' => $y,
                ],
            ];
        }

        return $events;
    }

    private function buildCardAndChanceEvents(GameMatch $match, int $clubId, Collection $squad): array
    {
        if ($squad->isEmpty()) {
            return [];
        }

        $events = [];

        $yellowCount = mt_rand(0, 3);
        for ($i = 0; $i < $yellowCount; $i++) {
            /** @var Player $player */
            $player = $this->weightedPlayerPick($squad, function(Player $p) {
                $weight = max(10, 120 - $p->defending);
                $instructions = $this->parseInstructions($p);

                if (in_array('tight_marking', $instructions)) $weight *= 1.5;
                if (in_array('stay_back', $instructions)) $weight *= 0.8;

                return $weight;
            });
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
            $player = $this->weightedPlayerPick($squad, function(Player $p) {
                $weight = $p->shooting + $p->pace;
                $instructions = $this->parseInstructions($p);

                if (in_array('run_behind', $instructions)) $weight *= 1.3;
                if (in_array('dribble_more', $instructions)) $weight *= 1.2;
                if (in_array('target_man', $instructions)) $weight *= 1.1;

                return $weight;
            });
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
        if ($squad->isEmpty() || mt_rand(1, 100) > 12) { // 12% chance for an injury per team
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
        if ($squad->isEmpty()) {
            return [];
        }

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
        if ($squad->isEmpty()) {
            return [];
        }

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
            
            // Try to use assigned takers if available in the lineup
            $lineup = ($clubId === (int) $match->home_club_id) 
                ? ($match->home_lineup ?? $this->matchLineupService->resolvePreferredLineup($match->homeClub, $match))
                : ($match->away_lineup ?? $this->matchLineupService->resolvePreferredLineup($match->awayClub, $match));

            if ($lineup) {
                if ($type === 'corner') {
                    $takerId = (mt_rand(1, 100) <= 50) ? $lineup->corner_left_taker_player_id : $lineup->corner_right_taker_player_id;
                    $taker = $squad->firstWhere('id', $takerId);
                    if ($taker) $player = $taker;
                } elseif ($type === 'free_kick') {
                    $takerId = (mt_rand(1, 100) <= 50) ? $lineup->free_kick_near_player_id : $lineup->free_kick_far_player_id;
                    $taker = $squad->firstWhere('id', $takerId);
                    if ($taker) $player = $taker;
                }
            }

            $clubName = ($clubId === (int) $match->home_club_id) ? ($match->homeClub->short_name ?? $match->homeClub->name) : ($match->awayClub->short_name ?? $match->awayClub->name);

            $eventData = [
                'player' => $player->full_name,
                'player_name' => $player->full_name,
                'club' => $clubName,
                'club_name' => $clubName,
            ];

            if (in_array($type, ['foul', 'free_kick', 'turnover'])) {
                if ($opponentSquad->isEmpty()) {
                    continue; // Skip events requiring an opponent if none exist
                }
                /** @var Player $opponent */
                $opponent = $this->randomCollectionItem($opponentSquad);
                if (!$opponent)
                    continue;

                $eventData['opponent'] = $opponent->full_name;
                $eventData['opponent_name'] = $opponent->full_name;
                $eventData['opponent_player_id'] = $opponent->id;
            }

            $metadata = null;
            if ($type === 'shot') {
                $xg = round(mt_rand(2, 40) / 100, 2);
                $onTarget = mt_rand(1, 100) <= ($player->shooting * 0.4 + 20);
                $xgot = $onTarget ? round(mt_rand(10, 80) / 100, 2) : 0;
                $metadata = [
                    'xg' => $xg,
                    'xgot' => $xgot,
                    'on_target' => $onTarget,
                    'foot' => mt_rand(1, 100) <= 15 ? 'head' : (mt_rand(1, 100) <= 60 ? 'right' : 'left'),
                    'situation' => mt_rand(1, 100) <= 10 ? 'set_piece' : 'open_play',
                    'x' => mt_rand(20, 80),
                    'y' => mt_rand(65, 95),
                ];
            }

            $events[] = array_merge([
                'minute' => mt_rand(1, 90),
                'second' => mt_rand(0, 59),
                'club_id' => $clubId,
                'player_id' => $player->id,
                'event_type' => $type,
                'metadata' => $metadata,
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
        $allEvents = $match->events()->get();
        $goalEvents = $allEvents->where('event_type', 'goal');
        $yellowEvents = $allEvents->where('event_type', 'yellow_card');
        $redEvents = $allEvents->where('event_type', 'red_card');
        $allShotEvents = $allEvents->whereIn('event_type', ['goal', 'shot']);

        $build = function (Collection $players, int $clubId) use ($match, $goalEvents, $yellowEvents, $redEvents, $allShotEvents): array {
            return $players->values()->map(function (Player $player, int $index) use ($match, $clubId, $goalEvents, $yellowEvents, $redEvents, $allShotEvents) {
                $goals = $goalEvents->where('player_id', $player->id)->count();
                $assists = $goalEvents->where('assister_player_id', $player->id)->count();
                $yellow = $yellowEvents->where('player_id', $player->id)->count();
                $red = $redEvents->where('player_id', $player->id)->count();
                $shots = $allShotEvents->where('player_id', $player->id)->count();
                
                // Aggregate xG/xGOT from events (if stored there) or simulate
                $xg = (float) $allShotEvents->where('player_id', $player->id)->sum(fn($e) => $e->metadata['xg'] ?? 0.05);
                $xgot = (float) $allShotEvents->where('player_id', $player->id)->sum(fn($e) => $e->metadata['xgot'] ?? 0.0);

                $role = $index < 11 ? 'starter' : 'bench';
                $mins = $role === 'starter' ? mt_rand(65, 96) : ($index < 14 ? mt_rand(5, 25) : 0);

                // Simulate granular stats based on player attributes and role
                $isDef = in_array($player->position, ['IV', 'LV', 'RV', 'DM']);
                $isMid = in_array($player->position, ['ZM', 'LM', 'RM', 'OM']);
                $basePasses = $isMid ? 45 : ($isDef ? 35 : 15);
                $passesAttempted = max(5, $basePasses + mt_rand(-10, 20));
                $passAccuracy = ($player->passing / 100) * (mt_rand(85, 115) / 100);
                $passesCompleted = (int) round($passesAttempted * min(0.98, $passAccuracy));

                $longBallsAttempted = max(0, mt_rand(0, 8));
                $longBallsCompleted = (int) round($longBallsAttempted * ($player->passing / 120));

                $duelsTotal = mt_rand(5, 15);
                $duelsWon = (int) round($duelsTotal * (($player->physical + $player->defending) / 200) * (mt_rand(80, 120) / 100));

                $baseRating = 6.0
                    + ($player->overall / 60)
                    + ($goals * 0.85)
                    + ($assists * 0.5)
                    - ($yellow * 0.25)
                    - ($red * 1.0)
                    + (($passesCompleted / max(1, $passesAttempted)) * 0.5)
                    + ((mt_rand(0, 30) - 15) / 100);

                return [
                    'match_id' => $match->id,
                    'club_id' => $clubId,
                    'player_id' => $player->id,
                    'lineup_role' => $role,
                    'position_code' => $this->matchPlayerRoleService->positionCodeForStat($player),
                    'rating' => max(3.5, min(10.0, round($baseRating, 2))),
                    'minutes_played' => $mins,
                    'goals' => $goals,
                    'assists' => $assists,
                    'xg' => $xg,
                    'xgot' => $xgot,
                    'yellow_cards' => $yellow,
                    'red_cards' => $red,
                    'shots' => $shots,
                    'passes_completed' => $passesCompleted,
                    'passes_attempted' => $passesAttempted,
                    'long_balls_completed' => $longBallsCompleted,
                    'long_balls_attempted' => $longBallsAttempted,
                    'chances_created' => max(0, $assists + mt_rand(0, 2)),
                    'big_chances_created' => max(0, $assists + mt_rand(0, 1)),
                    'dribbles_completed' => mt_rand(0, 4),
                    'dribbles_attempted' => mt_rand(0, 6),
                    'duels_won' => $duelsWon,
                    'duels_total' => $duelsTotal,
                    'aerials_won' => mt_rand(0, 5),
                    'aerials_total' => mt_rand(0, 8),
                    'interceptions' => $isDef ? mt_rand(1, 6) : mt_rand(0, 2),
                    'recoveries' => mt_rand(2, 10),
                    'clearances' => $isDef ? mt_rand(2, 8) : 0,
                    'tackles_won' => (int) ($duelsWon * 0.4),
                    'tackles_lost' => (int) (($duelsTotal - $duelsWon) * 0.4),
                    'saves' => ($player->position === 'TW') ? mt_rand(1, 7) : 0,
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
    private function randomCollectionItem(Collection $collection): ?Player
    {
        /** @var Player|null $picked */
        $picked = $this->matchRandomService->randomCollectionItem($collection);

        return $picked;
    }
}
