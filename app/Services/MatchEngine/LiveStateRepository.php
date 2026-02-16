<?php

namespace App\Services\MatchEngine;

use App\Models\GameMatch;
use App\Models\MatchLivePlayerState;
use App\Models\MatchLiveTeamState;
use App\Models\MatchLiveMinuteSnapshot;
use App\Models\MatchEvent;
use App\Models\MatchLiveAction;
use App\Models\MatchLiveStateTransition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LiveStateRepository
{
    public function teamStateFor(GameMatch $match, int $clubId): ?MatchLiveTeamState
    {
        return MatchLiveTeamState::where('match_id', $match->id)
            ->where('club_id', $clubId)
            ->first();
    }

    public function playerStateFor(GameMatch $match, int $playerId): ?MatchLivePlayerState
    {
        return MatchLivePlayerState::where('match_id', $match->id)
            ->where('player_id', $playerId)
            ->first();
    }

    public function activePlayerStates(GameMatch $match, int $clubId): Collection
    {
        return MatchLivePlayerState::where('match_id', $match->id)
            ->where('club_id', $clubId)
            ->where('is_on_pitch', true)
            ->get();
    }

    public function incrementTeamState(GameMatch $match, int $clubId, array $increments = [], array $overrides = []): void
    {
        $state = $this->teamStateFor($match, $clubId);
        if (!$state)
            return;

        foreach ($increments as $column => $amount) {
            $state->increment($column, $amount);
        }

        if ($overrides !== []) {
            $state->update($overrides);
        }
    }

    public function incrementPlayerState(MatchLivePlayerState $state, array $increments = [], array $overrides = []): void
    {
        foreach ($increments as $column => $amount) {
            $state->increment($column, $amount);
        }

        if ($overrides !== []) {
            $state->update($overrides);
        }
    }

    public function syncTeamPhase(GameMatch $match, int $clubId, string $phase, int $minute): void
    {
        $state = $this->teamStateFor($match, $clubId);
        if (!$state || $state->current_phase === $phase)
            return;

        $fromPhase = $state->current_phase;
        $state->update(['current_phase' => $phase]);

        $this->recordStateTransition($match, $minute, 0, $clubId, 'phase_change', $fromPhase, $phase);
    }

    public function recordAction(
        GameMatch $match,
        int $minute,
        int $second,
        int $sequence,
        ?int $clubId,
        ?int $playerId,
        ?int $opponentPlayerId,
        string $actionType,
        ?string $outcome,
        ?string $narrative,
        ?array $metadata,
        ?int $x_coord = null,
        ?int $y_coord = null,
        ?float $xg = null,
        ?int $momentum_value = null
    ): void {
        MatchLiveAction::create([
            'match_id' => $match->id,
            'minute' => $minute,
            'second' => $second,
            'sequence' => $sequence,
            'club_id' => $clubId,
            'player_id' => $playerId,
            'opponent_player_id' => $opponentPlayerId,
            'action_type' => $actionType,
            'outcome' => $outcome,
            'narrative' => $narrative,
            'metadata' => $metadata,
            'x_coord' => $x_coord,
            'y_coord' => $y_coord,
            'xg' => $xg,
            'momentum_value' => $momentum_value,
        ]);

        // Create MatchEvent for significant events to persist them permanently
        $significantTypes = ['goal', 'yellow_card', 'red_card', 'substitution', 'injury', 'chance'];
        if (in_array($actionType, $significantTypes, true)) {
            MatchEvent::create([
                'match_id' => $match->id,
                'minute' => $minute,
                'second' => $second,
                'club_id' => $clubId,
                'player_id' => $playerId,
                'assister_player_id' => $metadata['assister_id'] ?? null,
                'event_type' => $actionType,
                'metadata' => $metadata,
                'narrative' => $narrative,
            ]);
        }
    }

    public function recordStateTransition(
        GameMatch $match,
        int $minute,
        int $second,
        ?int $clubId,
        string $transitionType,
        ?string $fromPhase,
        ?string $toPhase,
        ?array $metadata = null
    ): void {
        MatchLiveStateTransition::create([
            'match_id' => $match->id,
            'minute' => $minute,
            'second' => $second,
            'club_id' => $clubId,
            'transition_type' => $transitionType,
            'from_phase' => $fromPhase,
            'to_phase' => $toPhase,
            'metadata' => $metadata,
        ]);
    }

    public function persistMinuteSnapshot(GameMatch $match, int $minute): void
    {
        $homeState = $this->teamStateFor($match, $match->home_club_id);
        $awayState = $this->teamStateFor($match, $match->away_club_id);

        MatchLiveMinuteSnapshot::create([
            'match_id' => $match->id,
            'minute' => $minute,
            'home_score' => $match->home_score,
            'away_score' => $match->away_score,
            'home_phase' => $homeState?->current_phase,
            'away_phase' => $awayState?->current_phase,
            'home_tactical_style' => (string) $homeState?->tactical_style,
            'away_tactical_style' => (string) $awayState?->tactical_style,
            'payload' => [
                'home_possession' => $homeState?->possession_percentage ?? 50,
                'away_possession' => $awayState?->possession_percentage ?? 50,
                'home_shots' => $homeState?->shots ?? 0,
                'away_shots' => $awayState?->shots ?? 0,
                'home_shots_on_target' => $homeState?->shots_on_target ?? 0,
                'away_shots_on_target' => $awayState?->shots_on_target ?? 0,
            ],
        ]);
    }

    public function weightedStatePick(Collection $states, callable $weightResolver): MatchLivePlayerState
    {
        $total = max(1, (int) $states->sum($weightResolver));
        $hit = mt_rand(1, $total);
        $cursor = 0;

        // Use foreach instead of find for better IDE compatibility with the return type
        foreach ($states as $state) {
            $cursor += max(1, (int) $weightResolver($state));
            if ($cursor >= $hit) {
                return $state;
            }
        }

        return $states->first();
    }

    public function randomCollectionItem(Collection $collection): MatchLivePlayerState
    {
        /** @var MatchLivePlayerState|null $fallback */
        $fallback = $collection->first();
        if (!$fallback) {
            throw new \RuntimeException('Cannot pick random item from empty collection.');
        }

        $items = $collection->values();
        $index = mt_rand(0, max(0, $items->count() - 1));

        return $items->get($index) ?? $fallback;
    }
}
