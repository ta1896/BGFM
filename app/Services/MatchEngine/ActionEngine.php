<?php

namespace App\Services\MatchEngine;

use App\Models\GameMatch;
use App\Models\MatchLivePlayerState;
use App\Models\MatchLiveTeamState;
use App\Models\Player;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ActionEngine
{
    public function __construct(
        private readonly LiveStateRepository $stateRepository,
        private readonly NarrativeEngine $narrativeEngine
    ) {
    }

    private array $usedTemplateIds = [];

    /**
     * Main sequence simulation for a match minute.
     */
    public function simulateActionSequence(
        GameMatch $match,
        int $minute,
        int $duration,
        int $homeClubId,
        int $awayClubId,
        float $homeStrength,
        float $awayStrength,
        string $commentatorStyle = 'sachlich'
    ): void {
        $sequence = 0;
        $secondsElapsed = 0;

        $attackerClubId = $homeClubId; // Default initialization, will be overwritten
        $defenderClubId = $awayClubId;

        // Determine who starts possessing based on strength/random
        if (mt_rand(0, 100) < 50) { // Should be based on possession stats ideally
            $attackerClubId = $homeClubId;
            $defenderClubId = $awayClubId;
        } else {
            $attackerClubId = $awayClubId;
            $defenderClubId = $homeClubId;
        }

        $attackerStates = $this->stateRepository->activePlayerStates($match, $attackerClubId);
        $defenderStates = $this->stateRepository->activePlayerStates($match, $defenderClubId);

        if ($attackerStates->isEmpty() || $defenderStates->isEmpty())
            return;

        // Load used IDs from cache for this match to avoid repetition across minutes
        $this->usedTemplateIds = \Illuminate\Support\Facades\Cache::get("match_used_templates_{$match->id}", []);

        // --- NEW: Calculate Modifiers (Tactics, Weather, Atmosphere, Shouts) ---
        $modifiers = $this->calculateModifiers($match, $homeClubId, $awayClubId);

        // Adjust strength based on modifiers
        $homeStrength *= $modifiers['home_strength_multiplier'];
        $awayStrength *= $modifiers['away_strength_multiplier'];

        // Determine who starts possessing based on strength/random + modifiers
        // Base 50/50 adjusted by strength difference and possession modifier
        $possessionChance = 50 + ($modifiers['home_possession_bonus'] - $modifiers['away_possession_bonus']);
        $possessionChance += ($homeStrength - $awayStrength) / 2; // Slight influence from strength

        if (mt_rand(0, 100) < $possessionChance) {
            $attackerClubId = $homeClubId;
            $defenderClubId = $awayClubId;
        } else {
            $attackerClubId = $awayClubId;
            $defenderClubId = $homeClubId;
        }

        $attackerStates = $this->stateRepository->activePlayerStates($match, $attackerClubId);
        $defenderStates = $this->stateRepository->activePlayerStates($match, $defenderClubId);

        if ($attackerStates->isEmpty() || $defenderStates->isEmpty())
            return;

        // Determine if an event happens (random roll based on strengths)
        $eventRoll = mt_rand(1, 100);

        // Adjust event chance based on "Intensity" (Shouts/Derby)
        $notableActionChance = 12 * $modifiers['intensity_multiplier'];

        if ($eventRoll <= $notableActionChance) {
            // Pass modifiers to processNotableAction to affect outcomes
            $this->processNotableAction($match, $minute, $sequence, $attackerClubId, $defenderClubId, $attackerStates, $defenderStates, $modifiers);
        } else {
            // REDUCED FREQUENCY: Only process generic actions 30% of the time to avoid ticker clutter
            if (mt_rand(1, 100) <= 30) {
                $this->processGenericAction($match, $minute, $sequence, $attackerClubId, $defenderClubId, $attackerStates, $defenderStates, $modifiers);
            }
        }

        // Save back to cache
        \Illuminate\Support\Facades\Cache::put("match_used_templates_{$match->id}", $this->usedTemplateIds, 3600);
    }

    private function calculateModifiers(GameMatch $match, int $homeId, int $awayId): array
    {
        $mods = [
            'home_strength_multiplier' => 1.0,
            'away_strength_multiplier' => 1.0,
            'home_possession_bonus' => 0,
            'away_possession_bonus' => 0,
            'intensity_multiplier' => 1.0,
            'home_conversion_bonus' => 0,
            'away_conversion_bonus' => 0,
            'foul_chance_multiplier' => 1.0,
        ];

        // 1. Tactics (Rock-Paper-Scissors)
        $homeState = $this->stateRepository->teamStateFor($match, $homeId);
        $awayState = $this->stateRepository->teamStateFor($match, $awayId);
        $homeStyle = $homeState?->tactical_style ?? 'balanced';
        $awayStyle = $awayState?->tactical_style ?? 'balanced';

        // Counter > Offensive
        if ($homeStyle === 'counter' && $awayStyle === 'offensive') {
            $mods['home_conversion_bonus'] += 15; // More clinical on counter
            $mods['away_possession_bonus'] += 10; // Offensive keeps ball
        } elseif ($awayStyle === 'counter' && $homeStyle === 'offensive') {
            $mods['away_conversion_bonus'] += 15;
            $mods['home_possession_bonus'] += 10;
        }

        // Offensive > Defensive
        if ($homeStyle === 'offensive' && $awayStyle === 'defensive') {
            $mods['home_possession_bonus'] += 20;
            $mods['home_strength_multiplier'] += 0.05;
        } elseif ($awayStyle === 'offensive' && $homeStyle === 'defensive') {
            $mods['away_possession_bonus'] += 20;
            $mods['away_strength_multiplier'] += 0.05;
        }

        // Defensive > Counter (Stifles)
        if ($homeStyle === 'defensive' && $awayStyle === 'counter') {
            $mods['away_conversion_bonus'] -= 10;
            $mods['home_strength_multiplier'] += 0.05;
        } elseif ($awayStyle === 'defensive' && $homeStyle === 'counter') {
            $mods['home_conversion_bonus'] -= 10;
            $mods['away_strength_multiplier'] += 0.05;
        }

        // 2. Weather
        if ($match->weather === 'rain') {
            $mods['intensity_multiplier'] *= 1.1; // Slick pitch, more sliding
            $mods['foul_chance_multiplier'] *= 1.2;
        } elseif ($match->weather === 'snow') {
            $mods['intensity_multiplier'] *= 0.9; // Sluggish
            $mods['home_strength_multiplier'] *= 0.95;
            $mods['away_strength_multiplier'] *= 0.95;
        }

        // 3. Atmosphere
        if ($match->homeClub->isRival($match->away_club_id)) {
            $mods['intensity_multiplier'] *= 1.2;
            $mods['foul_chance_multiplier'] *= 1.3;
            $mods['home_strength_multiplier'] *= 1.05; // Home advantage in derby
        }

        // 4. Manager Shouts (Cache driven)
        $homeShout = \Illuminate\Support\Facades\Cache::get("match_shout_{$match->id}_{$homeId}");
        if ($homeShout === 'demand_more') {
            $mods['home_strength_multiplier'] *= 1.1;
            $mods['intensity_multiplier'] *= 1.1;
        } elseif ($homeShout === 'concentrate') {
            $mods['home_strength_multiplier'] *= 1.05;
            $mods['foul_chance_multiplier'] *= 0.8; // More careful
        }
        $awayShout = \Illuminate\Support\Facades\Cache::get("match_shout_{$match->id}_{$awayId}");
        if ($awayShout) {
            // Simplified mirror for away
            $mods['away_strength_multiplier'] *= 1.05;
        }

        return $mods;
    }

    private function generateNarrative(GameMatch $match, string $type, array $data, int $actingClubId): string
    {
        $mood = $this->determineMood($match, $minute = $data['minute'] ?? 0, $actingClubId);

        $template = $this->narrativeEngine->pickTemplate($type, $data['locale'] ?? 'de', $mood, $this->usedTemplateIds);

        if (!$template) {
            return $this->narrativeEngine->getFallbackText($type, $data);
        }

        $this->usedTemplateIds[] = $template->id;
        // Keep only last 20 to avoid over-filtering
        if (count($this->usedTemplateIds) > 20) {
            array_shift($this->usedTemplateIds);
        }

        return $this->narrativeEngine->replaceTokens($template->text, $data);
    }

    private function determineMood(GameMatch $match, int $minute, int $actingClubId): string
    {
        $homeId = $match->home_club_id;
        $awayId = $match->away_club_id;
        $scoreDiff = $match->home_score - $match->away_score;

        // 1. Derby / Rivalry (Aggressive)
        // We assume homeClub and awayClub relations are loaded or checks are efficient
        // For simplicity, we check if they are rivals.
        // Ideally we should cache this or check it once per match, not every event.
        // But let's check it simply here.
        if ($match->homeClub->isRival($awayId)) {
            return 'aggressive';
        }

        // 2. Crunch Time (Late game, close score)
        if ($minute >= 80 && abs($scoreDiff) <= 1) {
            return 'crunch_time';
        }

        // 3. Frustrated (Trailing by 3+ goals)
        if ($actingClubId === $homeId && $scoreDiff <= -3) {
            return 'frustrated';
        }
        if ($actingClubId === $awayId && $scoreDiff >= 3) {
            return 'frustrated';
        }

        // 4. Excited (Comeback or leading by 1 late?)
        // Let's keep it simple for now.

        return 'neutral';
    }

    private function processNotableAction(
        GameMatch $match,
        int $minute,
        int $sequence,
        int $attackerClubId,
        int $defenderClubId,
        Collection $attackerStates,
        Collection $defenderStates,
        array $modifiers
    ): void {
        $actionRoll = mt_rand(1, 100);

        // Adjust foul chance based on weather/derby
        $foulThreshold = 40 + (10 * ($modifiers['foul_chance_multiplier'] - 1));

        if ($actionRoll <= 40) { // Keep chance logic separate from foul adjustment for now
            $this->handleChance($match, $minute, $sequence, $attackerClubId, $defenderClubId, $attackerStates, $defenderStates, $modifiers);
        } elseif ($actionRoll <= 40 + $foulThreshold) { // Slightly increased foul window if mod > 1
            $this->handleFoul($match, $minute, $sequence, $attackerClubId, $defenderClubId, $attackerStates, $defenderStates, $modifiers);
        } else {
            $this->handleInjury($match, $minute, $sequence, $attackerClubId, $attackerStates);
        }
    }

    private function handleChance(GameMatch $match, int $minute, int $sequence, int $attackerClubId, int $defenderClubId, Collection $attackerStates, Collection $defenderStates, array $modifiers): void
    {
        $attacker = $this->stateRepository->weightedStatePick($attackerStates, fn($s) => $s->player->shooting + $s->player->overall);
        $defender = $this->stateRepository->weightedStatePick($defenderStates, fn($s) => $s->player->defending + $s->player->overall);

        // Calculate Conversion Chance
        $baseChance = 25;
        $bonus = ($match->home_club_id === $attackerClubId) ? $modifiers['home_conversion_bonus'] : $modifiers['away_conversion_bonus'];

        // Final Conversion Chance
        $conversionChance = $baseChance + $bonus;
        $isGoal = mt_rand(1, 100) <= $conversionChance;

        if ($isGoal) {
            $this->recordGoal($match, $minute, $sequence, $attackerClubId, $attacker, $defenderClubId);
        } else {
            $metadata = [
                'player_name' => $attacker->player->full_name,
                'club_name' => $match->home_club_id === $attackerClubId ? $match->homeClub->short_name : $match->awayClub->short_name,
                'opponent_name' => $defender->player->full_name,
                'score' => "{$match->home_score}:{$match->away_score}",
                'quality' => 'normal', // Can be expanded
                'player_goals' => $attacker->goals,
                'player_shots' => $attacker->shots,
            ];

            $narrative = $this->generateNarrative($match, 'chance', array_merge($metadata, [
                'minute' => $minute,
                'player' => $attacker->player->full_name,
                'club' => $metadata['club_name'],
                'opponent' => $defender->player->full_name,
                'score' => $metadata['score']
            ]), $attackerClubId);

            [$x, $y] = $this->generateCoordinates($match, $attackerClubId, 'chance');
            $xg = $this->calculateXG('chance', 'miss');

            $this->stateRepository->recordAction(
                $match,
                $minute,
                mt_rand(0, 59),
                $sequence,
                $attackerClubId,
                $attacker->player_id,
                $defender->player_id,
                'chance',
                'miss',
                $narrative,
                $metadata,
                $x,
                $y,
                $xg
            );
        }
    }

    private function generateCoordinates(GameMatch $match, int $actingClubId, string $type): array
    {
        $isHome = $actingClubId === $match->home_club_id;

        // X: 0 = Away Goal, 50 = Midfield, 100 = Home Goal
        // If Home attacks, they move towards 0 (Away Goal) or 100? 
        // Let's standardise: 0 = Left (Home GK), 100 = Right (Away GK).
        // Home plays Left->Right (0->100). Away plays Right->Left (100->0).

        $x = 50;
        $y = mt_rand(0, 100); // 0=Top, 100=Bottom, 50=Center

        if ($type === 'goal' || $type === 'chance' || $type === 'miss' || $type === 'save') {
            // Action is in the penalty box of the DEFENDER
            if ($isHome) {
                // Home attacking Away Goal (Right side, > 80)
                $x = mt_rand(85, 98);
            } else {
                // Away attacking Home Goal (Left side, < 20)
                $x = mt_rand(2, 15);
            }
            $y = mt_rand(35, 65); // Central
        } elseif ($type === 'foul' || $type === 'yellow_card' || $type === 'red_card') {
            // Random field position
            $x = mt_rand(10, 90);
        } elseif ($type === 'midfield' || $type === 'substitution' || $type === 'tactical_change') {
            $x = mt_rand(40, 60);
        }

        return [$x, $y];
    }

    private function calculateXG(string $type, string $outcome): float
    {
        if ($type !== 'goal' && $type !== 'chance') {
            return 0.0;
        }

        // Base xG for a generic chance
        $xg = 0.10;

        if ($type === 'goal') {
            $xg = mt_rand(30, 95) / 100; // 0.30 - 0.95
        } elseif ($type === 'chance') {
            // Missed chance
            $xg = mt_rand(5, 45) / 100; // 0.05 - 0.45
        }

        return round($xg, 2);
    }

    private function recordGoal(GameMatch $match, int $minute, int $sequence, int $clubId, MatchLivePlayerState $scorer, int $concedingClubId): void
    {
        $isHomeGoal = $match->home_club_id === $clubId;

        DB::transaction(function () use ($match, $minute, $sequence, $clubId, $scorer, $isHomeGoal) {
            $isHomeGoal ? $match->increment('home_score') : $match->increment('away_score');

            $metadata = [
                'player_name' => $scorer->player->full_name,
                'club_name' => $clubId === $match->home_club_id ? $match->homeClub->short_name : $match->awayClub->short_name,
                'score' => "{$match->home_score}:{$match->away_score}",
                'goal_type' => 'aus dem Spiel', // Placeholder logic if needed
            ];

            $narrative = $this->generateNarrative($match, 'goal', array_merge($metadata, ['minute' => $minute, 'player' => $scorer->player->full_name, 'club' => $metadata['club_name']]), $clubId);

            [$x, $y] = $this->generateCoordinates($match, $clubId, 'goal');
            $xg = $this->calculateXG('goal', 'scored');

            $this->stateRepository->recordAction(
                $match,
                $minute,
                mt_rand(0, 59),
                $sequence,
                $clubId,
                $scorer->player_id,
                null,
                'goal',
                'scored',
                $narrative,
                $metadata,
                $x,
                $y,
                $xg
            );
            $this->stateRepository->incrementPlayerState($scorer, ['goals' => 1]);
            $this->stateRepository->incrementTeamState($match, $clubId, ['shots' => 1, 'shots_on_target' => 1]);
        });
    }

    private function handleFoul(GameMatch $match, int $minute, int $sequence, int $attackerClubId, int $defenderClubId, Collection $attackerStates, Collection $defenderStates, array $modifiers): void
    {
        $fouler = $this->stateRepository->weightedStatePick($defenderStates, fn($s) => max(10, 150 - $s->player->defending));
        $victim = $this->stateRepository->weightedStatePick($attackerStates, fn($s) => $s->player->dribbling + $s->player->pace);

        $cardRoll = mt_rand(1, 100);

        // Increase card chances if rivalry/weather exists
        $yellowThresh = 15 * $modifiers['foul_chance_multiplier'];
        $redThresh = 2 * $modifiers['foul_chance_multiplier']; // e.g. 2 * 1.3 = 2.6%

        $card = null;
        if ($cardRoll <= $yellowThresh)
            $card = 'yellow_card';
        if ($cardRoll <= $redThresh) // Check red last to override
            $card = 'red_card';

        $metadata = [
            'player_name' => $fouler->player->full_name,
            'opponent_name' => $victim->player->full_name,
            'club_name' => $defenderClubId === $match->home_club_id ? $match->homeClub->short_name : $match->awayClub->short_name,
            'card' => $card,
        ];

        $narrative = $this->generateNarrative($match, $card ?? 'foul', array_merge($metadata, ['minute' => $minute, 'player' => $fouler->player->full_name, 'opponent' => $victim->player->full_name, 'club' => $metadata['club_name']]), $defenderClubId); // Defender is the actor (fouler)

        [$x, $y] = $this->generateCoordinates($match, $defenderClubId, $card ?? 'foul');

        $this->stateRepository->recordAction(
            $match,
            $minute,
            mt_rand(0, 59),
            $sequence,
            $defenderClubId,
            $fouler->player_id,
            $victim->player_id,
            $card ?? 'foul',
            'committed',
            $narrative,
            $metadata,
            $x,
            $y,
            0.0
        );

        if ($card) {
            $this->stateRepository->incrementPlayerState($fouler, [$card === 'yellow_card' ? 'yellow_cards' : 'red_cards' => 1]);
        }
    }

    private function handleInjury(GameMatch $match, int $minute, int $sequence, int $clubId, Collection $states): void
    {
        $player = $this->stateRepository->randomCollectionItem($states);

        $metadata = [
            'player_name' => $player->player->full_name,
            'club_name' => $clubId === $match->home_club_id ? $match->homeClub->short_name : $match->awayClub->short_name,
            'score' => "{$match->home_score}:{$match->away_score}",
        ];

        $narrative = $this->generateNarrative($match, 'injury', array_merge($metadata, ['minute' => $minute, 'player' => $player->player->full_name, 'club' => $metadata['club_name'], 'score' => $metadata['score']]), $clubId);

        [$x, $y] = $this->generateCoordinates($match, $clubId, 'injury');

        $this->stateRepository->recordAction(
            $match,
            $minute,
            mt_rand(0, 59),
            $sequence,
            $clubId,
            $player->player_id,
            null,
            'injury',
            'sustained',
            $narrative,
            $metadata,
            $x,
            $y,
            0.0
        );
    }

    private function processGenericAction(
        GameMatch $match,
        int $minute,
        int $sequence,
        int $attackerClubId,
        int $defenderClubId,
        Collection $attackerStates,
        Collection $defenderStates,
        array $modifiers
    ): void {
        $actionRoll = mt_rand(1, 100);

        $type = 'possession';
        $outcome = 'success';
        $actor = $this->stateRepository->weightedStatePick($attackerStates, fn($s) => $s->player->passing + $s->player->overall);
        $actingClubId = $attackerClubId;

        if ($actionRoll <= 50) {
            // Standard Midfield Possession
            $type = 'midfield_possession';
            $metadata = [
                'player_name' => $actor->player->full_name,
                'club_name' => $match->home_club_id === $attackerClubId ? $match->homeClub->short_name : $match->awayClub->short_name,
                'score' => "{$match->home_score}:{$match->away_score}",
            ];
            $narrative = $this->generateNarrative($match, 'midfield_possession', array_merge($metadata, ['minute' => $minute, 'player' => $actor->player->full_name, 'score' => $metadata['score']]), $attackerClubId);

        } elseif ($actionRoll <= 75) {
            // Turnover / Defensive Action
            $type = 'turnover';
            $outcome = 'lost_possession';
            $actor = $this->stateRepository->weightedStatePick($defenderStates, fn($s) => $s->player->defending + $s->player->physical);
            $actingClubId = $defenderClubId;
            $opponent = $this->stateRepository->randomCollectionItem($attackerStates)->player;

            $metadata = [
                'player_name' => $actor->player->full_name,
                'opponent_name' => $opponent->full_name,
                'club_name' => $match->home_club_id === $defenderClubId ? $match->homeClub->short_name : $match->awayClub->short_name,
                'score' => "{$match->home_score}:{$match->away_score}",
            ];

            $narrative = $this->generateNarrative($match, 'turnover', array_merge($metadata, ['minute' => $minute, 'player' => $actor->player->full_name, 'opponent' => $opponent->full_name, 'score' => $metadata['score']]), $defenderClubId);

        } elseif ($actionRoll <= 90) {
            // Throw-in
            $type = 'throw_in';
            $metadata = [
                'player_name' => $actor->player->full_name,
                'club_name' => $match->home_club_id === $attackerClubId ? $match->homeClub->short_name : $match->awayClub->short_name,
                'score' => "{$match->home_score}:{$match->away_score}",
            ];
            $narrative = $this->generateNarrative($match, 'throw_in', array_merge($metadata, ['minute' => $minute, 'player' => $actor->player->full_name, 'score' => $metadata['score']]), $attackerClubId);

        } else {
            // Clearance / Long Ball
            $type = 'clearance';
            $metadata = [
                'player_name' => $actor->player->full_name,
                'club_name' => $match->home_club_id === $attackerClubId ? $match->homeClub->short_name : $match->awayClub->short_name,
                'score' => "{$match->home_score}:{$match->away_score}",
            ];
            $narrative = $this->generateNarrative($match, 'clearance', array_merge($metadata, ['minute' => $minute, 'player' => $actor->player->full_name, 'score' => $metadata['score']]), $attackerClubId);
        }

        [$x, $y] = $this->generateCoordinates($match, $actingClubId, 'midfield'); // Use midfield coords

        $this->stateRepository->recordAction(
            $match,
            $minute,
            mt_rand(0, 59),
            $sequence,
            $actingClubId,
            $actor->player_id,
            null,
            $type,
            $outcome,
            $narrative,
            $metadata,
            $x,
            $y,
            0.0
        );
    }
}
