<?php

namespace App\Services\MatchEngine;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use App\Models\MatchLivePlayerState;
use App\Models\MatchLiveTeamState;
use App\Models\MatchPlannedSubstitution;
use App\Services\PlayerPositionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SubstitutionManager
{
    public function __construct(
        private readonly LiveStateRepository $stateRepository,
        private readonly PlayerPositionService $positionService
    ) {
    }

    public function makeSubstitution(
        GameMatch $match,
        int $clubId,
        int $playerOutId,
        int $playerInId,
        int $minute,
        ?string $targetSlot = null
    ): void {
        $teamState = $this->stateRepository->teamStateFor($match, $clubId);
        if (!$teamState || $teamState->substitutions_made >= $this->maxSubstitutions()) {
            throw new \RuntimeException('Maximum substitutions reached.');
        }

        $playerOut = Player::findOrFail($playerOutId);
        $playerIn = Player::findOrFail($playerInId);
        $playerOutState = $this->stateRepository->playerStateFor($match, $playerOutId);
        $playerInState = $this->stateRepository->playerStateFor($match, $playerInId);

        if (!$playerOutState || !$playerOutState->is_on_pitch) {
            throw new \RuntimeException('Player to substitute out is not on pitch.');
        }
        if (!$playerInState || $playerInState->is_on_pitch || $playerInState->has_played) {
            throw new \RuntimeException('Player to substitute in is already on pitch or has been subbed out.');
        }

        $lineup = $match->lineups()->where('club_id', $clubId)->first();
        $outSlot = (string) $playerOutState->pitch_slot;
        $resolvedTargetSlot = $targetSlot ?? $outSlot;

        $targetSlotOccupant = null;
        if ($resolvedTargetSlot !== $outSlot) {
            $targetSlotOccupant = MatchLivePlayerState::where('match_id', $match->id)
                ->where('club_id', $clubId)
                ->where('pitch_slot', $resolvedTargetSlot)
                ->where('is_on_pitch', true)
                ->first()?->player;
        }

        $this->applySubstitution(
            $match,
            $clubId,
            $minute,
            $teamState,
            $lineup,
            $playerOut,
            $playerIn,
            $playerOutState,
            $playerInState,
            $targetSlotOccupant,
            $resolvedTargetSlot
        );
    }

    public function applySubstitution(
        GameMatch $match,
        int $clubId,
        int $minute,
        MatchLiveTeamState $teamState,
        Lineup $lineup,
        Player $playerOut,
        Player $playerIn,
        MatchLivePlayerState $playerOutState,
        MatchLivePlayerState $playerInState,
        ?Player $targetSlotOccupant,
        string $resolvedTargetSlot
    ): void {
        DB::transaction(function () use ($match, $clubId, $minute, $teamState, $lineup, $playerOut, $playerIn, $playerOutState, $playerInState, $targetSlotOccupant, $resolvedTargetSlot) {
            // 1. Player Out
            $this->stateRepository->incrementPlayerState($playerOutState, [], [
                'is_on_pitch' => false,
                'has_played' => true,
                'off_at' => $minute,
                'pitch_slot' => null,
            ]);

            // 2. Target Slot handling (if someone else was there)
            if ($targetSlotOccupant && $targetSlotOccupant->id !== $playerOut->id) {
                $occupantState = $this->stateRepository->playerStateFor($match, $targetSlotOccupant->id);
                $this->stateRepository->incrementPlayerState($occupantState, [], [
                    'pitch_slot' => $playerOutState->pitch_slot // Shifted
                ]);
            }

            // 3. Player In
            $this->stateRepository->incrementPlayerState($playerInState, [], [
                'is_on_pitch' => true,
                'has_played' => true,
                'on_at' => $minute,
                'pitch_slot' => $resolvedTargetSlot,
            ]);

            // 4. Team Metrics
            $teamState->increment('substitutions_made');

            // 5. Narrative - We'll integrate NarrativeEngine later in ActionEngine or here
        });
    }

    public function executePlannedSubstitutions(GameMatch $match, int $minute): void
    {
        $planned = MatchPlannedSubstitution::where('match_id', $match->id)
            ->where('planned_minute', '<=', $minute)
            ->where('is_executed', false)
            ->get();

        foreach ($planned as $sub) {
            if ($this->isScoreConditionSatisfied($match, $sub->club_id, $sub->score_condition)) {
                try {
                    $this->makeSubstitution($match, $sub->club_id, $sub->player_out_id, $sub->player_in_id, $minute, $sub->target_slot);
                    $sub->update(['is_executed' => true, 'executed_at' => now(), 'actual_minute' => $minute]);
                } catch (\Exception $e) {
                    // Log or handle failed planned sub
                    $sub->update(['is_executed' => true, 'metadata' => ['error' => $e->getMessage()]]);
                }
            }
        }
    }

    private function isScoreConditionSatisfied(GameMatch $match, int $clubId, string $condition): bool
    {
        if ($condition === 'any')
            return true;

        $isHome = $match->home_club_id === $clubId;
        $leading = $isHome ? $match->home_score > $match->away_score : $match->away_score > $match->home_score;
        $trailing = $isHome ? $match->home_score < $match->away_score : $match->away_score < $match->home_score;
        $draw = $match->home_score === $match->away_score;

        return match ($condition) {
            'leading' => $leading,
            'trailing' => $trailing,
            'draw' => $draw,
            'not_leading' => !$leading,
            'not_trailing' => !$trailing,
            default => true,
        };
    }

    public function maxSubstitutions(): int
    {
        return 5; // Configurable
    }
}
