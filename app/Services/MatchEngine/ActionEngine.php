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
        int $sequence,
        int $attackerClubId,
        int $defenderClubId,
        float $homeStrength,
        float $awayStrength
    ): void {
        $attackerStates = $this->stateRepository->activePlayerStates($match, $attackerClubId);
        $defenderStates = $this->stateRepository->activePlayerStates($match, $defenderClubId);

        if ($attackerStates->isEmpty() || $defenderStates->isEmpty())
            return;

        // Load used IDs from cache for this match to avoid repetition across minutes
        $this->usedTemplateIds = \Illuminate\Support\Facades\Cache::get("match_used_templates_{$match->id}", []);

        // Determine if an event happens (random roll based on strengths)
        $eventRoll = mt_rand(1, 100);

        if ($eventRoll <= 12) { // 12% chance for a notable action per sequence
            $this->processNotableAction($match, $minute, $sequence, $attackerClubId, $defenderClubId, $attackerStates, $defenderStates);
        }

        // Save back to cache
        \Illuminate\Support\Facades\Cache::put("match_used_templates_{$match->id}", $this->usedTemplateIds, 3600);
    }

    private function generateNarrative(string $type, array $data): string
    {
        $template = $this->narrativeEngine->pickTemplate($type, $data['locale'] ?? 'de', $this->usedTemplateIds);

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

    private function processNotableAction(
        GameMatch $match,
        int $minute,
        int $sequence,
        int $attackerClubId,
        int $defenderClubId,
        Collection $attackerStates,
        Collection $defenderStates
    ): void {
        $actionRoll = mt_rand(1, 100);

        if ($actionRoll <= 40) {
            $this->handleChance($match, $minute, $sequence, $attackerClubId, $defenderClubId, $attackerStates, $defenderStates);
        } elseif ($actionRoll <= 80) {
            $this->handleFoul($match, $minute, $sequence, $attackerClubId, $defenderClubId, $attackerStates, $defenderStates);
        } else {
            $this->handleInjury($match, $minute, $sequence, $attackerClubId, $attackerStates);
        }
    }

    private function handleChance(GameMatch $match, int $minute, int $sequence, int $attackerClubId, int $defenderClubId, Collection $attackerStates, Collection $defenderStates): void
    {
        $attacker = $this->stateRepository->weightedStatePick($attackerStates, fn($s) => $s->player->shooting + $s->player->overall);
        $defender = $this->stateRepository->weightedStatePick($defenderStates, fn($s) => $s->player->defending + $s->player->overall);

        $isGoal = mt_rand(1, 100) <= 25; // Simple 25% conversion for now

        if ($isGoal) {
            $this->recordGoal($match, $minute, $sequence, $attackerClubId, $attacker, $defenderClubId);
        } else {
            $narrative = $this->generateNarrative('chance', [
                'player' => $attacker->player->last_name,
                'club' => $match->home_club_id === $attackerClubId ? $match->homeClub->name : $match->awayClub->name,
                'opponent' => $defender->player->last_name,
            ]);

            $this->stateRepository->recordAction($match, $minute, mt_rand(0, 59), $sequence, $attackerClubId, $attacker->player_id, $defender->player_id, 'chance', 'miss', $narrative, null);
        }
    }

    private function recordGoal(GameMatch $match, int $minute, int $sequence, int $clubId, MatchLivePlayerState $scorer, int $concedingClubId): void
    {
        $isHomeGoal = $match->home_club_id === $clubId;

        DB::transaction(function () use ($match, $minute, $sequence, $clubId, $scorer, $isHomeGoal) {
            $isHomeGoal ? $match->increment('home_score') : $match->increment('away_score');

            $narrative = $this->generateNarrative('goal', [
                'player' => $scorer->player->last_name,
                'club' => $clubId === $match->home_club_id ? $match->homeClub->short_name : $match->awayClub->short_name,
                'score' => "{$match->home_score}:{$match->away_score}",
            ]);

            $this->stateRepository->recordAction($match, $minute, mt_rand(0, 59), $sequence, $clubId, $scorer->player_id, null, 'goal', 'scored', $narrative, null);
            $this->stateRepository->incrementPlayerState($scorer, ['goals' => 1]);
            $this->stateRepository->incrementTeamState($match, $clubId, ['shots' => 1, 'shots_on_target' => 1]);
        });
    }

    private function handleFoul(GameMatch $match, int $minute, int $sequence, int $attackerClubId, int $defenderClubId, Collection $attackerStates, Collection $defenderStates): void
    {
        $fouler = $this->stateRepository->weightedStatePick($defenderStates, fn($s) => max(10, 150 - $s->player->defending));
        $victim = $this->stateRepository->weightedStatePick($attackerStates, fn($s) => $s->player->dribbling + $s->player->pace);

        $cardRoll = mt_rand(1, 100);
        $card = null;
        if ($cardRoll <= 15)
            $card = 'yellow_card';
        elseif ($cardRoll <= 2)
            $card = 'red_card';

        $narrative = $this->generateNarrative($card ?? 'foul', [
            'player' => $fouler->player->last_name,
            'opponent' => $victim->player->last_name,
            'club' => $defenderClubId === $match->home_club_id ? $match->homeClub->short_name : $match->awayClub->short_name,
        ]);

        $this->stateRepository->recordAction($match, $minute, mt_rand(0, 59), $sequence, $defenderClubId, $fouler->player_id, $victim->player_id, $card ?? 'foul', 'committed', $narrative, null);

        if ($card) {
            $this->stateRepository->incrementPlayerState($fouler, [$card === 'yellow_card' ? 'yellow_cards' : 'red_cards' => 1]);
        }
    }

    private function handleInjury(GameMatch $match, int $minute, int $sequence, int $clubId, Collection $states): void
    {
        $player = $this->stateRepository->randomCollectionItem($states);

        $narrative = $this->generateNarrative('injury', [
            'player' => $player->player->last_name,
            'club' => $clubId === $match->home_club_id ? $match->homeClub->short_name : $match->awayClub->short_name,
        ]);

        $this->stateRepository->recordAction($match, $minute, mt_rand(0, 59), $sequence, $clubId, $player->player_id, null, 'injury', 'sustained', $narrative, null);
    }
}
