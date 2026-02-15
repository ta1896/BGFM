<?php

namespace App\Services;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\MatchLiveAction;
use App\Models\MatchLiveMinuteSnapshot;
use App\Models\MatchLivePlayerState;
use App\Models\MatchLiveStateTransition;
use App\Models\MatchLiveTeamState;
use App\Models\MatchPlannedSubstitution;
use App\Models\Player;
use App\Services\MatchEngine\ActionEngine;
use App\Services\MatchEngine\LiveStateRepository;
use App\Services\MatchEngine\SubstitutionManager;
use App\Services\MatchEngine\TacticalManager;
use App\Services\Simulation\DefaultSimulationStrategy;
use App\Services\Simulation\MatchSimulationExecutor;
use App\Services\Simulation\Observers\MatchFinishedContext;
use App\Services\Simulation\Observers\MatchFinishedObserverPipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LiveMatchTickerService
{
    private const MAX_SUBSTITUTIONS = 5;

    private const MAX_TACTICAL_CHANGES = 3;

    private const MIN_MINUTE_FOR_TACTICAL_CHANGE = 5;

    private const MINUTES_BETWEEN_TACTICAL_CHANGES = 10;

    private const MINUTES_BETWEEN_SUBSTITUTIONS = 3;

    private const MAX_PLANNED_SUBSTITUTIONS_PER_CLUB = 5;

    private const MIN_MINUTES_AHEAD_FOR_PLANNED_SUBSTITUTION = 2;

    public function __construct(
        private readonly CpuClubDecisionService $cpuDecisionService,
        private readonly PlayerPositionService $positionService,
        private readonly CompetitionContextService $competitionContextService,
        private readonly FormationPlannerService $formationPlannerService,
        private readonly PlayerAvailabilityService $availabilityService,
        private readonly ActionEngine $actionEngine,
        private readonly SubstitutionManager $substitutionManager,
        private readonly LiveStateRepository $stateRepository,
        private readonly TacticalManager $tacticalManager,
        private readonly MatchSimulationExecutor $simulationExecutor,
        private readonly DefaultSimulationStrategy $simulationStrategy,
        private readonly MatchFinishedObserverPipeline $matchFinishedObserverPipeline
    ) {
    }

    public function start(GameMatch $match): GameMatch
    {
        if ($match->status === 'played' || $match->status === 'live') {
            return $this->loadState($match);
        }

        if ($match->status !== 'scheduled') {
            return $this->loadState($match);
        }

        $this->cpuDecisionService->prepareForMatch($match);
        $match->loadMissing(['homeClub.players', 'awayClub.players']);

        $this->ensureMatchLineup($match, $match->homeClub);
        $this->ensureMatchLineup($match, $match->awayClub);

        $homePlayers = $this->resolveMatchSquad($match->homeClub, $match);
        $awayPlayers = $this->resolveMatchSquad($match->awayClub, $match);
        if ($homePlayers->isEmpty() || $awayPlayers->isEmpty()) {
            return $this->loadState($match);
        }

        DB::transaction(function () use ($match): void {
            $lockedMatch = GameMatch::query()
                ->whereKey($match->id)
                ->lockForUpdate()
                ->first();
            if (!$lockedMatch || $lockedMatch->status !== 'scheduled') {
                return;
            }

            $this->seedDeterministicScope($lockedMatch, 'start');

            $lockedMatch->loadMissing(['homeClub', 'awayClub']);
            $context = $this->competitionContextService->forMatch($lockedMatch);
            $lockedMatch->update([
                'status' => 'live',
                'competition_context' => $context,
                'home_score' => (int) ($lockedMatch->home_score ?? 0),
                'away_score' => (int) ($lockedMatch->away_score ?? 0),
                'attendance' => $lockedMatch->attendance ?: $this->attendance($lockedMatch->homeClub),
                'weather' => $lockedMatch->weather ?: $this->weather(),
                'live_minute' => max(0, (int) $lockedMatch->live_minute),
                'live_paused' => false,
                'live_error_message' => null,
                'live_last_tick_at' => now(),
            ]);

            $this->initializeLiveState($lockedMatch->fresh(['homeClub', 'awayClub']));

            $this->recordStateTransition(
                $lockedMatch,
                0,
                0,
                null,
                'match_start',
                null,
                'pre_match',
                ['status' => 'live']
            );
        });

        return $this->loadState($match);
    }

    public function resume(GameMatch $match): GameMatch
    {
        if ($match->status !== 'live') {
            return $this->loadState($match);
        }

        $wasPaused = (bool) $match->live_paused;

        $match->update([
            'live_paused' => false,
            'live_error_message' => null,
            'live_last_tick_at' => now(),
        ]);

        if ($wasPaused) {
            $this->recordStateTransition(
                $match,
                (int) $match->live_minute,
                0,
                null,
                'match_resume',
                null,
                null,
                null
            );
        }

        return $this->loadState($match);
    }

    public function setTacticalStyle(GameMatch $match, int $clubId, string $style): GameMatch
    {
        $allowed = ['balanced', 'offensive', 'defensive', 'counter'];
        if (!in_array($style, $allowed, true)) {
            return $this->loadState($match);
        }

        if ($match->status !== 'live' || $match->live_paused) {
            return $this->loadState($match);
        }

        if (!in_array($clubId, [(int) $match->home_club_id, (int) $match->away_club_id], true)) {
            return $this->loadState($match);
        }

        /** @var Club|null $club */
        $club = Club::query()->find($clubId);
        if (!$club) {
            return $this->loadState($match);
        }

        $minute = max(0, (int) $match->live_minute);
        $teamState = $this->teamStateFor($match, $clubId);
        if ($minute < self::MIN_MINUTE_FOR_TACTICAL_CHANGE || $minute > 110) {
            return $this->loadState($match);
        }

        if ($this->activePlayerStates($match, $clubId)->count() < 7) {
            return $this->loadState($match);
        }

        if ((string) $teamState->tactical_style === $style) {
            return $this->loadState($match);
        }

        if ((int) $teamState->tactical_changes_count >= self::MAX_TACTICAL_CHANGES) {
            return $this->loadState($match);
        }

        $lastChangeMinute = (int) ($teamState->last_tactical_change_minute ?? 0);
        if ($lastChangeMinute > 0 && ($minute - $lastChangeMinute) < self::MINUTES_BETWEEN_TACTICAL_CHANGES) {
            return $this->loadState($match);
        }

        DB::transaction(function () use ($match, $club, $teamState, $style, $minute, $clubId): void {
            $lineup = $this->ensureMatchLineup($match, $club);
            if ($lineup->tactical_style !== $style) {
                $lineup->update(['tactical_style' => $style]);
            }

            $teamState->update([
                'tactical_style' => $style,
                'tactical_changes_count' => (int) $teamState->tactical_changes_count + 1,
                'last_tactical_change_minute' => $minute,
            ]);

            $this->recordAction(
                $match,
                $minute,
                $this->randomInt(0, 59),
                0,
                $clubId,
                null,
                null,
                'tactical_change',
                $style,
                ['style' => $style]
            );

            $this->recordStateTransition(
                $match,
                $minute,
                0,
                $clubId,
                'tactical_change',
                null,
                null,
                ['style' => $style]
            );
        });

        return $this->loadState($match);
    }

    public function makeSubstitution(
        GameMatch $match,
        int $clubId,
        int $playerOutId,
        int $playerInId,
        ?string $targetSlot = null
    ): GameMatch {
        if ($match->status !== 'live' || $match->live_paused) {
            return $this->loadState($match);
        }

        if (!in_array($clubId, [(int) $match->home_club_id, (int) $match->away_club_id], true)) {
            return $this->loadState($match);
        }

        /** @var Club|null $club */
        $club = Club::query()->find($clubId);
        if (!$club) {
            return $this->loadState($match);
        }

        $currentMinute = (int) $match->live_minute;
        if ($currentMinute < 1) {
            return $this->loadState($match);
        }

        $minute = max(1, $currentMinute);
        if ($minute > $this->matchMinuteLimit($match)) {
            return $this->loadState($match);
        }

        $teamState = $this->teamStateFor($match, $clubId);
        if ((int) $teamState->substitutions_used >= self::MAX_SUBSTITUTIONS) {
            return $this->loadState($match);
        }

        $lastSubMinute = (int) ($teamState->last_substitution_minute ?? 0);
        if ($lastSubMinute > 0 && ($minute - $lastSubMinute) < self::MINUTES_BETWEEN_SUBSTITUTIONS) {
            return $this->loadState($match);
        }

        $lineup = $this->ensureMatchLineup($match, $club);
        $lineup->load('players');

        /** @var Player|null $playerOut */
        $playerOut = $lineup->players->firstWhere('id', $playerOutId);
        /** @var Player|null $playerIn */
        $playerIn = $lineup->players->firstWhere('id', $playerInId);
        if (!$playerOut || !$playerIn || $playerOut->id === $playerIn->id) {
            return $this->loadState($match);
        }

        $playerOutState = $this->playerStateFor($match, $playerOut->id);
        $playerInState = $this->playerStateFor($match, $playerIn->id);
        if (!$playerOutState || !$playerInState) {
            return $this->loadState($match);
        }

        if (!$this->availabilityService->isPlayerAvailableForLiveMatch($playerIn, $match)) {
            return $this->loadState($match);
        }

        if (!(bool) $playerOutState->is_on_pitch || (bool) $playerOutState->is_sent_off || (bool) $playerOutState->is_injured) {
            return $this->loadState($match);
        }

        if ((bool) $playerInState->is_on_pitch || (bool) $playerInState->is_sent_off || (bool) $playerInState->is_injured) {
            return $this->loadState($match);
        }

        $outSlot = strtoupper((string) $playerOut->pivot->pitch_position);
        if ((bool) $playerOut->pivot->is_bench || str_starts_with($outSlot, 'OUT-')) {
            return $this->loadState($match);
        }

        $inSlot = strtoupper((string) $playerIn->pivot->pitch_position);
        if (!(bool) $playerIn->pivot->is_bench || str_starts_with($inSlot, 'OUT-')) {
            return $this->loadState($match);
        }

        $resolvedTargetSlot = strtoupper(trim((string) $targetSlot));
        if ($resolvedTargetSlot === '' || str_starts_with($resolvedTargetSlot, 'BANK-') || str_starts_with($resolvedTargetSlot, 'OUT-')) {
            $resolvedTargetSlot = $outSlot;
        }

        if (!$this->isValidTargetSlotForSubstitution($lineup, $resolvedTargetSlot, $outSlot)) {
            return $this->loadState($match);
        }

        if (!$this->canSubstituteGoalkeeper($match, $clubId, $playerOut, $playerIn)) {
            return $this->loadState($match);
        }

        if ($this->activePlayerStates($match, $clubId)->count() < 7) {
            return $this->loadState($match);
        }

        /** @var Player|null $targetSlotOccupant */
        $targetSlotOccupant = $lineup->players->first(function (Player $player) use ($playerOutId, $resolvedTargetSlot): bool {
            return $player->id !== $playerOutId
                && !(bool) $player->pivot->is_bench
                && strtoupper((string) $player->pivot->pitch_position) === $resolvedTargetSlot;
        });

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

        return $this->loadState($match);
    }

    public function planSubstitution(
        GameMatch $match,
        int $clubId,
        int $playerOutId,
        int $playerInId,
        int $plannedMinute,
        string $scoreCondition = 'any',
        ?string $targetSlot = null
    ): GameMatch {
        if ($match->status !== 'live' || $match->live_paused) {
            return $this->loadState($match);
        }

        if (!in_array($clubId, [(int) $match->home_club_id, (int) $match->away_club_id], true)) {
            return $this->loadState($match);
        }

        $allowedConditions = ['any', 'leading', 'drawing', 'trailing'];
        if (!in_array($scoreCondition, $allowedConditions, true)) {
            return $this->loadState($match);
        }

        $minute = max(1, $plannedMinute);
        $currentMinute = (int) $match->live_minute;
        if ($minute < ($currentMinute + $this->minMinutesAheadForPlannedSubstitution())) {
            return $this->loadState($match);
        }

        if ($minute > $this->matchMinuteLimit($match)) {
            return $this->loadState($match);
        }

        $teamState = $this->teamStateFor($match, $clubId);
        $remainingSubs = max(0, self::MAX_SUBSTITUTIONS - (int) $teamState->substitutions_used);
        if ($remainingSubs < 1) {
            return $this->loadState($match);
        }

        $pendingPlansCount = MatchPlannedSubstitution::query()
            ->where('match_id', $match->id)
            ->where('club_id', $clubId)
            ->where('status', 'pending')
            ->count();
        if ($pendingPlansCount >= min($remainingSubs, $this->maxPlannedSubstitutionsPerClub())) {
            return $this->loadState($match);
        }

        $interval = $this->plannedSubstitutionIntervalMinutes();
        $hasTimingConflict = MatchPlannedSubstitution::query()
            ->where('match_id', $match->id)
            ->where('club_id', $clubId)
            ->where('status', 'pending')
            ->whereBetween('planned_minute', [$minute - ($interval - 1), $minute + ($interval - 1)])
            ->exists();
        if ($hasTimingConflict) {
            return $this->loadState($match);
        }

        /** @var Club|null $club */
        $club = Club::query()->find($clubId);
        if (!$club) {
            return $this->loadState($match);
        }

        $lineup = $this->ensureMatchLineup($match, $club);
        $lineup->load('players');

        /** @var Player|null $playerOut */
        $playerOut = $lineup->players->firstWhere('id', $playerOutId);
        /** @var Player|null $playerIn */
        $playerIn = $lineup->players->firstWhere('id', $playerInId);
        if (!$playerOut || !$playerIn || $playerOut->id === $playerIn->id) {
            return $this->loadState($match);
        }

        if (!$this->availabilityService->isPlayerAvailableForLiveMatch($playerIn, $match)) {
            return $this->loadState($match);
        }

        $outSlot = strtoupper((string) $playerOut->pivot->pitch_position);
        if ((bool) $playerOut->pivot->is_bench || str_starts_with($outSlot, 'OUT-')) {
            return $this->loadState($match);
        }

        $inSlot = strtoupper((string) $playerIn->pivot->pitch_position);
        if (!(bool) $playerIn->pivot->is_bench || str_starts_with($inSlot, 'OUT-')) {
            return $this->loadState($match);
        }

        $resolvedTargetSlot = strtoupper(trim((string) $targetSlot));
        if ($resolvedTargetSlot === '' || str_starts_with($resolvedTargetSlot, 'BANK-') || str_starts_with($resolvedTargetSlot, 'OUT-')) {
            $resolvedTargetSlot = $outSlot;
        }

        if (!$this->isValidTargetSlotForSubstitution($lineup, $resolvedTargetSlot, $outSlot)) {
            return $this->loadState($match);
        }

        if (!$this->canSubstituteGoalkeeper($match, $clubId, $playerOut, $playerIn)) {
            return $this->loadState($match);
        }

        $plan = MatchPlannedSubstitution::query()->create([
            'match_id' => $match->id,
            'club_id' => $clubId,
            'player_out_id' => $playerOutId,
            'player_in_id' => $playerInId,
            'planned_minute' => $minute,
            'score_condition' => $scoreCondition,
            'target_slot' => $resolvedTargetSlot,
            'status' => 'pending',
        ]);

        $this->recordAction(
            $match,
            max($currentMinute, 1),
            $this->randomInt(0, 59),
            0,
            $clubId,
            $playerInId,
            $playerOutId,
            'substitution_plan',
            'scheduled',
            [
                'planned_minute' => $minute,
                'score_condition' => $scoreCondition,
                'target_slot' => $resolvedTargetSlot,
            ]
        );

        $this->recordStateTransition(
            $match,
            max($currentMinute, 1),
            0,
            $clubId,
            'substitution_plan_scheduled',
            null,
            null,
            [
                'plan_id' => (int) $plan->id,
                'planned_minute' => $minute,
                'score_condition' => $scoreCondition,
                'target_slot' => $resolvedTargetSlot,
            ]
        );

        return $this->loadState($match);
    }

    public function tick(GameMatch $match, int $minutes = 1): GameMatch
    {
        if (!$match->competition_context) {
            $this->competitionContextService->persistForMatch($match);
        }

        return $this->simulationExecutor->run(
            $match,
            $minutes,
            fn(GameMatch $match): GameMatch => $this->start($match),
            fn(GameMatch $match): GameMatch => $this->loadState($match),
            function (GameMatch $match, int $minute): void {
                $this->simulateMinute($match, $minute);
            },
            fn(GameMatch $match): int => $this->matchMinuteLimit($match),
            fn(GameMatch $match): bool => $this->canFinish($match),
            function (GameMatch $match): void {
                $this->finish($match);
            },
        );
    }

    public function syncLiveLineupState(GameMatch $match, Club $club): void
    {
        if ($match->status !== 'live') {
            return;
        }

        /** @var Lineup|null $lineup */
        $lineup = Lineup::query()
            ->with('players')
            ->where('match_id', $match->id)
            ->where('club_id', $club->id)
            ->first();
        if (!$lineup) {
            return;
        }

        $existingStates = MatchLivePlayerState::query()
            ->where('match_id', $match->id)
            ->where('club_id', $club->id)
            ->get()
            ->keyBy(fn(MatchLivePlayerState $state): int => (int) $state->player_id);

        $timestamp = now();
        $lineupPlayerIds = [];
        $rows = [];

        foreach ($lineup->players as $player) {
            $playerId = (int) $player->id;
            $lineupPlayerIds[] = $playerId;
            $slot = strtoupper((string) $player->pivot->pitch_position);

            /** @var MatchLivePlayerState|null $existingState */
            $existingState = $existingStates->get($playerId);
            $isUnavailable = $existingState
                ? ((bool) $existingState->is_sent_off || (bool) $existingState->is_injured)
                : false;

            $rows[] = [
                'match_id' => $match->id,
                'club_id' => $club->id,
                'player_id' => $playerId,
                'slot' => $slot,
                'is_on_pitch' => !(bool) $player->pivot->is_bench && !str_starts_with($slot, 'OUT-') && !$isUnavailable,
                'fit_factor' => round($this->positionService->fitFactorWithProfile(
                    (string) ($player->position_main ?: $player->position),
                    (string) $player->position_second,
                    (string) $player->position_third,
                    $slot
                ), 2),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::transaction(function () use ($rows, $match, $club, $lineupPlayerIds, $timestamp, $lineup): void {
            if ($rows !== []) {
                MatchLivePlayerState::query()->upsert(
                    $rows,
                    ['match_id', 'player_id'],
                    ['club_id', 'slot', 'is_on_pitch', 'fit_factor', 'updated_at']
                );
            }

            MatchLivePlayerState::query()
                ->where('match_id', $match->id)
                ->where('club_id', $club->id)
                ->when(
                    $lineupPlayerIds !== [],
                    fn($query) => $query->whereNotIn('player_id', $lineupPlayerIds)
                )
                ->where('is_on_pitch', true)
                ->update([
                    'is_on_pitch' => false,
                    'slot' => 'OUT-MANUAL',
                    'updated_at' => $timestamp,
                ]);

            MatchLiveTeamState::query()->updateOrCreate(
                [
                    'match_id' => $match->id,
                    'club_id' => $club->id,
                ],
                [
                    'tactical_style' => (string) ($lineup->tactical_style ?: 'balanced'),
                ]
            );
        });
    }

    private function applySubstitution(
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
        $outSlot = strtoupper((string) $playerOut->pivot->pitch_position);
        $outSort = (int) $playerOut->pivot->sort_order;
        $outX = $playerOut->pivot->x_coord;
        $outY = $playerOut->pivot->y_coord;
        $targetSort = $targetSlotOccupant ? (int) $targetSlotOccupant->pivot->sort_order : $outSort;
        $targetX = $targetSlotOccupant ? $targetSlotOccupant->pivot->x_coord : $outX;
        $targetY = $targetSlotOccupant ? $targetSlotOccupant->pivot->y_coord : $outY;

        DB::transaction(function () use ($lineup, $match, $clubId, $minute, $teamState, $playerOut, $playerOutState, $playerIn, $playerInState, $targetSlotOccupant, $resolvedTargetSlot, $outSort, $outX, $outY, $outSlot, $targetSort, $targetX, $targetY): void {
            $lineup->players()->updateExistingPivot($playerOut->id, [
                'pitch_position' => 'OUT-' . $minute,
                'sort_order' => min(255, 200 + (int) $teamState->substitutions_used + 1),
                'x_coord' => null,
                'y_coord' => null,
                'is_bench' => true,
                'bench_order' => null,
            ]);

            if ($targetSlotOccupant) {
                $lineup->players()->updateExistingPivot($targetSlotOccupant->id, [
                    'pitch_position' => $outSlot,
                    'sort_order' => $outSort,
                    'x_coord' => $outX,
                    'y_coord' => $outY,
                    'is_bench' => false,
                    'bench_order' => null,
                ]);
            }

            $lineup->players()->updateExistingPivot($playerIn->id, [
                'pitch_position' => $resolvedTargetSlot,
                'sort_order' => $targetSort,
                'x_coord' => $targetX,
                'y_coord' => $targetY,
                'is_bench' => false,
                'bench_order' => null,
            ]);

            $match->events()->create([
                'minute' => $minute,
                'second' => $this->randomInt(0, 59),
                'club_id' => $clubId,
                'player_id' => $playerIn->id,
                'assister_player_id' => $playerOut->id,
                'event_type' => 'substitution',
                'metadata' => [
                    'player_in_id' => $playerIn->id,
                    'player_out_id' => $playerOut->id,
                    'target_slot' => $resolvedTargetSlot,
                    'fit_factor' => $this->positionService->fitFactorWithProfile(
                        (string) ($playerIn->position_main ?: $playerIn->position),
                        (string) $playerIn->position_second,
                        (string) $playerIn->position_third,
                        $resolvedTargetSlot
                    ),
                ],
            ]);

            $teamState->update([
                'substitutions_used' => (int) $teamState->substitutions_used + 1,
                'last_substitution_minute' => $minute,
                'current_ball_carrier_player_id' => (int) $teamState->current_ball_carrier_player_id === (int) $playerOut->id
                    ? null
                    : $teamState->current_ball_carrier_player_id,
                'last_set_piece_taker_player_id' => (int) $playerIn->id,
                'last_set_piece_type' => 'substitution',
                'last_set_piece_minute' => $minute,
            ]);

            $playerOutState->update([
                'is_on_pitch' => false,
                'slot' => 'OUT-' . $minute,
            ]);

            $playerInState->update([
                'is_on_pitch' => true,
                'slot' => $resolvedTargetSlot,
                'fit_factor' => round($this->positionService->fitFactorWithProfile(
                    (string) ($playerIn->position_main ?: $playerIn->position),
                    (string) $playerIn->position_second,
                    (string) $playerIn->position_third,
                    $resolvedTargetSlot
                ), 2),
            ]);

            if ($targetSlotOccupant) {
                $targetState = $this->playerStateFor($match, (int) $targetSlotOccupant->id);
                if ($targetState) {
                    $targetState->update([
                        'is_on_pitch' => true,
                        'slot' => $outSlot,
                        'fit_factor' => round($this->positionService->fitFactorWithProfile(
                            (string) ($targetSlotOccupant->position_main ?: $targetSlotOccupant->position),
                            (string) $targetSlotOccupant->position_second,
                            (string) $targetSlotOccupant->position_third,
                            $outSlot
                        ), 2),
                    ]);
                }
            }

            $this->recordAction(
                $match,
                $minute,
                $this->randomInt(0, 59),
                0,
                $clubId,
                $playerIn->id,
                $playerOut->id,
                'substitution',
                'manual',
                [
                    'player_in_id' => $playerIn->id,
                    'player_out_id' => $playerOut->id,
                    'target_slot' => $resolvedTargetSlot,
                ]
            );

            $this->recordStateTransition(
                $match,
                $minute,
                0,
                $clubId,
                'substitution',
                null,
                null,
                [
                    'player_in_id' => $playerIn->id,
                    'player_out_id' => $playerOut->id,
                    'target_slot' => $resolvedTargetSlot,
                ]
            );
        });
    }

    private function finish(GameMatch $match): void
    {
        $this->seedDeterministicScope($match, 'finish', (int) $match->live_minute);

        /** @var GameMatch|null $finalizedMatch */
        $finalizedMatch = DB::transaction(function () use ($match): ?GameMatch {
            $lockedMatch = GameMatch::query()
                ->whereKey($match->id)
                ->lockForUpdate()
                ->first();
            if (!$lockedMatch || $lockedMatch->status !== 'live' || $lockedMatch->played_at !== null) {
                return null;
            }

            $this->availabilityService->decrementCountersForMatch((int) $lockedMatch->home_club_id, $lockedMatch);
            $this->availabilityService->decrementCountersForMatch((int) $lockedMatch->away_club_id, $lockedMatch);

            if ($this->isCup($lockedMatch) && (int) $lockedMatch->home_score === (int) $lockedMatch->away_score) {
                $this->resolvePenaltyShootout($lockedMatch);
            }

            $lockedMatch->update([
                'status' => 'played',
                'live_minute' => min((int) $lockedMatch->live_minute, $this->matchMinuteLimit($lockedMatch)),
                'live_paused' => false,
                'live_error_message' => null,
                'played_at' => now(),
            ]);

            $this->recordStateTransition(
                $lockedMatch,
                (int) $lockedMatch->live_minute,
                59,
                null,
                'match_finish',
                null,
                'finished',
                [
                    'home_score' => (int) $lockedMatch->home_score,
                    'away_score' => (int) $lockedMatch->away_score,
                ]
            );

            return $lockedMatch->fresh();
        });
        if (!$finalizedMatch) {
            return;
        }

        $finalizedMatch->loadMissing(['homeClub.players', 'awayClub.players']);
        $homePlayers = $this->resolveMatchParticipants($finalizedMatch->homeClub, $finalizedMatch);
        $awayPlayers = $this->resolveMatchParticipants($finalizedMatch->awayClub, $finalizedMatch);

        if ((bool) config('simulation.observers.match_finished.enabled', true)) {
            $this->matchFinishedObserverPipeline->process(new MatchFinishedContext(
                $finalizedMatch->fresh(),
                $homePlayers,
                $awayPlayers
            ));
        }
    }

    private function simulateMinute(GameMatch $match, int $minute): void
    {
        $this->seedDeterministicScope($match, 'simulate_minute', $minute);

        // 1. Automatic Extra Time handling (for Cups)
        if ($this->isCup($match) && $minute === 91 && (int) $match->home_score === (int) $match->away_score && !$match->extra_time) {
            $match->update(['extra_time' => true]);
            $this->stateRepository->recordAction($match, $minute, 0, 0, null, null, null, 'phase', 'extra_time_start', 'Verlängerung!', null);
        }

        // 2. Increment minutes played for all active players
        MatchLivePlayerState::query()
            ->where('match_id', $match->id)
            ->where('is_on_pitch', true)
            ->increment('minutes_played');

        // 3. Delegation to specialized engines
        $this->substitutionManager->executePlannedSubstitutions($match, $minute);

        $homeStates = $this->stateRepository->activePlayerStates($match, (int) $match->home_club_id);
        $awayStates = $this->stateRepository->activePlayerStates($match, (int) $match->away_club_id);

        if ($homeStates->isEmpty() || $awayStates->isEmpty()) {
            $this->stateRepository->persistMinuteSnapshot($match, $minute);
            return;
        }

        // 4. Tactical influences and simulation
        $homeStyle = $this->stateRepository->teamStateFor($match, (int) $match->home_club_id)->tactical_style;
        $awayStyle = $this->stateRepository->teamStateFor($match, (int) $match->away_club_id)->tactical_style;

        $homeStrength = $this->teamStrengthFromStates($homeStates, true, (string) $homeStyle);
        $awayStrength = $this->teamStrengthFromStates($awayStates, false, (string) $awayStyle);

        $this->actionEngine->simulateActionSequence($match, $minute, 1, (int) $match->home_club_id, (int) $match->away_club_id, $homeStrength, $awayStrength);

        $this->stateRepository->persistMinuteSnapshot($match, $minute);
    }


    private function isScoreConditionSatisfied(GameMatch $match, int $clubId, string $condition): bool
    {
        if ($condition === 'any') {
            return true;
        }

        $goalDiff = (int) $match->home_score - (int) $match->away_score;
        if ($clubId === (int) $match->away_club_id) {
            $goalDiff *= -1;
        }

        return match ($condition) {
            'leading' => $goalDiff > 0,
            'drawing' => $goalDiff === 0,
            'trailing' => $goalDiff < 0,
            default => true,
        };
    }

    private function maxPlannedSubstitutionsPerClub(): int
    {
        return max(1, (int) config(
            'simulation.live_changes.planned_substitutions.max_per_club',
            self::MAX_PLANNED_SUBSTITUTIONS_PER_CLUB
        ));
    }

    private function minMinutesAheadForPlannedSubstitution(): int
    {
        return max(1, (int) config(
            'simulation.live_changes.planned_substitutions.min_minutes_ahead',
            self::MIN_MINUTES_AHEAD_FOR_PLANNED_SUBSTITUTION
        ));
    }

    private function plannedSubstitutionIntervalMinutes(): int
    {
        return max(1, (int) config(
            'simulation.live_changes.planned_substitutions.min_interval_minutes',
            self::MINUTES_BETWEEN_SUBSTITUTIONS
        ));
    }

    private function maxBenchPlayers(): int
    {
        return max(1, min(10, (int) config('simulation.lineup.max_bench_players', 5)));
    }

    private function simulateActionSequence(
        GameMatch $match,
        int $minute,
        int $sequence,
        int $attackerClubId,
        int $defenderClubId,
        float $homeStrength,
        float $awayStrength
    ): void {
        $attackers = $this->activePlayerStates($match, $attackerClubId);
        $defenders = $this->activePlayerStates($match, $defenderClubId);
        if ($attackers->isEmpty() || $defenders->isEmpty()) {
            return;
        }

        $ballCarrier = $this->weightedStatePick(
            $attackers,
            fn(MatchLivePlayerState $state): int => max(5, (int) round((($state->player->passing + $state->player->pace + $state->player->shooting) / 3) * (float) $state->fit_factor))
        );
        $this->rememberBallCarrier($match, $attackerClubId, (int) $ballCarrier->player_id);

        $this->incrementTeamState($match, $attackerClubId, ['actions_count' => 1, 'pass_attempts' => 1]);
        $this->incrementPlayerState($ballCarrier, ['ball_contacts' => 1, 'pass_attempts' => 1]);
        $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, null, 'possession', 'start', null);

        if (
            !$this->simulationStrategy->isPassSuccessful(
                (float) $ballCarrier->player->passing,
                (float) $ballCarrier->fit_factor
            )
        ) {
            $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, null, 'pass', 'failed', null);

            return;
        }

        $this->incrementTeamState($match, $attackerClubId, ['pass_completions' => 1]);
        $this->incrementPlayerState($ballCarrier, ['pass_completions' => 1]);
        $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, null, 'pass', 'complete', null);

        if ($this->simulationStrategy->shouldAttemptTackle()) {
            $tackler = $this->weightedStatePick(
                $defenders,
                fn(MatchLivePlayerState $state): int => max(5, (int) round((($state->player->defending + $state->player->physical) / 2) * (float) $state->fit_factor))
            );

            $this->incrementTeamState($match, $defenderClubId, ['tackle_attempts' => 1]);
            $this->incrementPlayerState($tackler, ['tackle_attempts' => 1]);

            if (
                $this->simulationStrategy->isTackleWon(
                    (float) $tackler->player->defending,
                    (float) $ballCarrier->player->pace
                )
            ) {
                $this->rememberBallCarrier($match, $defenderClubId, (int) $tackler->player_id);
                $this->incrementTeamState($match, $defenderClubId, ['tackle_won' => 1]);
                $this->incrementPlayerState($tackler, ['tackle_won' => 1]);
                $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $defenderClubId, (int) $tackler->player_id, (int) $ballCarrier->player_id, 'tackle', 'won', null);

                if ($this->simulationStrategy->shouldCommitFoulAfterTackleWin()) {
                    $this->handleFoulAndSetPiece($match, $minute, $sequence, $defenderClubId, $attackerClubId, $tackler, $ballCarrier);
                }

                return;
            }

            $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $defenderClubId, (int) $tackler->player_id, (int) $ballCarrier->player_id, 'tackle', 'lost', null);
        }

        $this->incrementTeamState($match, $attackerClubId, ['dangerous_attacks' => 1, 'shots' => 1]);

        $xg = $this->simulationStrategy->chanceXg(
            $attackerClubId,
            (int) $match->home_club_id,
            $homeStrength,
            $awayStrength
        );
        $this->incrementTeamState($match, $attackerClubId, [], [
            'expected_goals' => (float) $this->teamStateFor($match, $attackerClubId)->expected_goals + $xg,
        ]);

        $match->events()->create([
            'minute' => $minute,
            'second' => $this->randomInt(0, 59),
            'club_id' => $attackerClubId,
            'player_id' => $ballCarrier->player_id,
            'event_type' => 'chance',
            'metadata' => [
                'quality' => $this->simulationStrategy->chanceQuality($xg),
                'xg_bucket' => round($xg, 2),
                'sequence' => $sequence,
            ],
        ]);

        $this->incrementPlayerState($ballCarrier, ['shots' => 1]);
        if ($this->simulationStrategy->shouldWinCornerAfterShot()) {
            $this->incrementTeamState($match, $attackerClubId, ['corners_won' => 1]);
            $this->rememberSetPieceTaker($match, $attackerClubId, (int) $ballCarrier->player_id, 'corner', $minute);
            $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, null, 'set_piece', 'corner', null);
        }

        if (
            !$this->simulationStrategy->isShotOnTarget(
                (float) $ballCarrier->player->shooting,
                (float) $ballCarrier->fit_factor
            )
        ) {
            $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, null, 'shot', 'off_target', ['xg' => round($xg, 2)]);

            return;
        }

        $this->incrementTeamState($match, $attackerClubId, ['shots_on_target' => 1]);
        $this->incrementPlayerState($ballCarrier, ['shots_on_target' => 1]);

        $goalkeeper = $this->goalkeeperState($defenders) ?? $this->randomCollectionItem($defenders);
        if (
            $this->simulationStrategy->isSave(
                (float) $goalkeeper->player->overall,
                (float) $goalkeeper->fit_factor,
                (float) $ballCarrier->player->shooting,
                (float) $ballCarrier->fit_factor,
                $xg
            )
        ) {
            $match->events()->create([
                'minute' => $minute,
                'second' => $this->randomInt(0, 59),
                'club_id' => $defenderClubId,
                'player_id' => $goalkeeper->player_id,
                'event_type' => 'save',
                'metadata' => [
                    'against_player_id' => $ballCarrier->player_id,
                    'xg' => round($xg, 2),
                    'sequence' => $sequence,
                ],
            ]);

            $this->incrementPlayerState($goalkeeper, ['saves' => 1]);
            $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $defenderClubId, (int) $goalkeeper->player_id, (int) $ballCarrier->player_id, 'shot', 'saved', ['xg' => round($xg, 2)]);

            return;
        }

        $assistState = null;
        if ($attackers->count() > 1 && $this->simulationStrategy->shouldCreateAssist()) {
            $assistCandidates = $attackers->where('player_id', '!=', $ballCarrier->player_id)->values();
            if ($assistCandidates->isNotEmpty()) {
                $assistState = $this->randomCollectionItem($assistCandidates);
            }
        }

        $match->events()->create([
            'minute' => $minute,
            'second' => $this->randomInt(0, 59),
            'club_id' => $attackerClubId,
            'player_id' => $ballCarrier->player_id,
            'assister_player_id' => $assistState?->player_id,
            'event_type' => 'goal',
            'metadata' => [
                'xg_bucket' => round($xg, 2),
                'sequence' => $sequence,
            ],
        ]);

        if ($attackerClubId === (int) $match->home_club_id) {
            $match->increment('home_score');
        } else {
            $match->increment('away_score');
        }

        $this->incrementPlayerState($ballCarrier, ['goals' => 1]);
        if ($assistState) {
            $this->incrementPlayerState($assistState, ['assists' => 1]);
        }

        $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, (int) $goalkeeper->player_id, 'shot', 'goal', ['xg' => round($xg, 2)]);
    }

    private function handleFoulAndSetPiece(
        GameMatch $match,
        int $minute,
        int $sequence,
        int $defenderClubId,
        int $attackerClubId,
        MatchLivePlayerState $foulingPlayer,
        MatchLivePlayerState $victim
    ): void {
        $this->incrementTeamState($match, $defenderClubId, ['fouls_committed' => 1]);
        $this->incrementPlayerState($foulingPlayer, ['fouls_committed' => 1]);
        $this->incrementPlayerState($victim, ['fouls_suffered' => 1]);
        $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $defenderClubId, (int) $foulingPlayer->player_id, (int) $victim->player_id, 'foul', 'committed', null);

        $isRed = $this->simulationStrategy->isRedCardFromFoul();
        $isYellow = $this->simulationStrategy->isYellowCardFromFoul($isRed);
        if ($isYellow) {
            $match->events()->create([
                'minute' => $minute,
                'second' => $this->randomInt(0, 59),
                'club_id' => $defenderClubId,
                'player_id' => $foulingPlayer->player_id,
                'event_type' => 'yellow_card',
                'metadata' => ['sequence' => $sequence],
            ]);
            $this->incrementTeamState($match, $defenderClubId, ['yellow_cards' => 1]);
            $this->incrementPlayerState($foulingPlayer, ['yellow_cards' => 1]);
        }

        if ($isRed) {
            $match->events()->create([
                'minute' => $minute,
                'second' => $this->randomInt(0, 59),
                'club_id' => $defenderClubId,
                'player_id' => $foulingPlayer->player_id,
                'event_type' => 'red_card',
                'metadata' => ['sequence' => $sequence],
            ]);

            $this->incrementTeamState($match, $defenderClubId, ['red_cards' => 1]);
            $this->incrementPlayerState($foulingPlayer, ['red_cards' => 1], [
                'is_sent_off' => true,
                'is_on_pitch' => false,
                'slot' => 'OUT-' . $minute,
            ]);
            $this->markPlayerUnavailableInLineup($match, $defenderClubId, (int) $foulingPlayer->player_id, $minute);
            $this->recordStateTransition(
                $match,
                $minute,
                0,
                $defenderClubId,
                'send_off',
                null,
                null,
                ['player_id' => (int) $foulingPlayer->player_id]
            );
        }

        if ($this->simulationStrategy->isPenaltyAwardedFromFoul()) {
            $this->rememberSetPieceTaker($match, $attackerClubId, (int) $victim->player_id, 'penalty', $minute);
            $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $attackerClubId, (int) $victim->player_id, (int) $foulingPlayer->player_id, 'set_piece', 'penalty_awarded', null);
            $this->simulatePenaltyAttemptInPlay($match, $minute, $sequence, $attackerClubId, $defenderClubId, $victim);
        } else {
            $this->rememberSetPieceTaker($match, $attackerClubId, (int) $victim->player_id, 'free_kick', $minute);
            $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $attackerClubId, (int) $victim->player_id, null, 'set_piece', 'free_kick', null);
        }
    }

    private function simulatePenaltyAttemptInPlay(
        GameMatch $match,
        int $minute,
        int $sequence,
        int $attackerClubId,
        int $defenderClubId,
        MatchLivePlayerState $preferredTaker
    ): void {
        $attackers = $this->activePlayerStates($match, $attackerClubId);
        $defenders = $this->activePlayerStates($match, $defenderClubId);
        if ($attackers->isEmpty() || $defenders->isEmpty()) {
            return;
        }

        $taker = $attackers->firstWhere('player_id', $preferredTaker->player_id) ?? $this->weightedStatePick(
            $attackers,
            fn(MatchLivePlayerState $state): int => max(5, (int) round($state->player->shooting * (float) $state->fit_factor))
        );
        $goalkeeper = $this->goalkeeperState($defenders) ?? $this->randomCollectionItem($defenders);
        $this->rememberSetPieceTaker($match, $attackerClubId, (int) $taker->player_id, 'penalty', $minute);

        $this->incrementTeamState($match, $attackerClubId, ['shots' => 1, 'shots_on_target' => 1], [
            'expected_goals' => (float) $this->teamStateFor($match, $attackerClubId)->expected_goals + 0.78,
        ]);
        $this->incrementPlayerState($taker, ['shots' => 1, 'shots_on_target' => 1]);

        if (
            $this->simulationStrategy->isPenaltyScoredInPlay(
                (float) $taker->player->shooting,
                (float) $goalkeeper->player->overall
            )
        ) {
            $match->events()->create([
                'minute' => $minute,
                'second' => $this->randomInt(0, 59),
                'club_id' => $attackerClubId,
                'player_id' => $taker->player_id,
                'event_type' => 'penalty_scored',
                'metadata' => ['sequence' => $sequence],
            ]);
            if ($attackerClubId === (int) $match->home_club_id) {
                $match->increment('home_score');
            } else {
                $match->increment('away_score');
            }
            $this->incrementPlayerState($taker, ['goals' => 1]);
            $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $attackerClubId, (int) $taker->player_id, (int) $goalkeeper->player_id, 'penalty', 'scored', null);

            return;
        }

        $match->events()->create([
            'minute' => $minute,
            'second' => $this->randomInt(0, 59),
            'club_id' => $attackerClubId,
            'player_id' => $taker->player_id,
            'event_type' => 'penalty_missed',
            'metadata' => ['sequence' => $sequence],
        ]);
        if ($this->simulationStrategy->shouldCreatePenaltySaveEventInPlay()) {
            $match->events()->create([
                'minute' => $minute,
                'second' => $this->randomInt(0, 59),
                'club_id' => $defenderClubId,
                'player_id' => $goalkeeper->player_id,
                'event_type' => 'save',
                'metadata' => ['sequence' => $sequence, 'penalty' => true],
            ]);
            $this->incrementPlayerState($goalkeeper, ['saves' => 1]);
        }

        $this->recordAction($match, $minute, $this->randomInt(0, 59), $sequence, $attackerClubId, (int) $taker->player_id, (int) $goalkeeper->player_id, 'penalty', 'missed', null);
    }

    private function simulateRandomInjury(GameMatch $match, int $minute, int $clubId): void
    {
        if (!$this->simulationStrategy->shouldRandomInjuryOccur()) {
            return;
        }

        $squad = $this->activePlayerStates($match, $clubId);
        if ($squad->isEmpty()) {
            return;
        }

        $injured = $this->weightedStatePick(
            $squad,
            fn(MatchLivePlayerState $state): int => max(5, 120 - $state->player->stamina)
        );

        $injured->update([
            'is_injured' => true,
            'is_on_pitch' => false,
            'slot' => 'OUT-' . $minute,
        ]);
        $this->markPlayerUnavailableInLineup($match, $clubId, (int) $injured->player_id, $minute);

        $match->events()->create([
            'minute' => $minute,
            'second' => $this->randomInt(0, 59),
            'club_id' => $clubId,
            'player_id' => $injured->player_id,
            'event_type' => 'injury',
            'metadata' => ['severity_hint' => $this->randomInt(1, 100)],
        ]);

        $this->recordAction($match, $minute, $this->randomInt(0, 59), 0, $clubId, (int) $injured->player_id, null, 'injury', 'forced_off', null);
        $this->recordStateTransition(
            $match,
            $minute,
            0,
            $clubId,
            'injury_forced_off',
            null,
            null,
            ['player_id' => (int) $injured->player_id]
        );
    }

    private function resolvePenaltyShootout(GameMatch $match): void
    {
        $this->seedDeterministicScope($match, 'penalty_shootout', (int) $match->live_minute);

        if (!$this->isCup($match) || (int) $match->home_score !== (int) $match->away_score) {
            return;
        }

        if ($match->penalties_home !== null || $match->penalties_away !== null) {
            return;
        }

        $homeKicks = 0;
        $awayKicks = 0;
        for ($kick = 1; $kick <= 5; $kick++) {
            if ($this->executePenaltyKick($match, (int) $match->home_club_id, (int) $match->away_club_id, $kick)) {
                $homeKicks++;
            }
            if ($this->executePenaltyKick($match, (int) $match->away_club_id, (int) $match->home_club_id, $kick)) {
                $awayKicks++;
            }
        }

        $suddenKick = 6;
        while ($homeKicks === $awayKicks && $suddenKick <= 20) {
            if ($this->executePenaltyKick($match, (int) $match->home_club_id, (int) $match->away_club_id, $suddenKick)) {
                $homeKicks++;
            }
            if ($this->executePenaltyKick($match, (int) $match->away_club_id, (int) $match->home_club_id, $suddenKick)) {
                $awayKicks++;
            }
            $suddenKick++;
        }

        if ($homeKicks === $awayKicks) {
            if ($this->simulationStrategy->shouldHomeWinShootoutCoinflip()) {
                $homeKicks++;
            } else {
                $awayKicks++;
            }
        }

        $match->update([
            'extra_time' => true,
            'penalties_home' => $homeKicks,
            'penalties_away' => $awayKicks,
        ]);

        $this->recordAction($match, 120, 59, 0, null, null, null, 'penalty_shootout', 'finished', ['home' => $homeKicks, 'away' => $awayKicks]);
    }

    private function executePenaltyKick(GameMatch $match, int $attackerClubId, int $defenderClubId, int $kickNumber): bool
    {
        $attackers = $this->penaltyEligibleStates($match, $attackerClubId);
        $defenders = $this->penaltyEligibleStates($match, $defenderClubId);
        if ($attackers->isEmpty() || $defenders->isEmpty()) {
            return false;
        }

        $taker = $this->weightedStatePick(
            $attackers,
            fn(MatchLivePlayerState $state): int => max(5, (int) round((($state->player->shooting + $state->player->morale) / 2) * (float) $state->fit_factor))
        );
        $goalkeeper = $this->goalkeeperState($defenders) ?? $this->randomCollectionItem($defenders);
        $this->rememberSetPieceTaker($match, $attackerClubId, (int) $taker->player_id, 'penalty_shootout', 120);

        $isGoal = $this->simulationStrategy->isPenaltyScoredInShootout(
            (float) $taker->player->shooting,
            (float) $goalkeeper->player->overall
        );

        $match->events()->create([
            'minute' => 120,
            'second' => min(59, 10 + $kickNumber),
            'club_id' => $attackerClubId,
            'player_id' => $taker->player_id,
            'event_type' => $isGoal ? 'penalty_scored' : 'penalty_missed',
            'metadata' => [
                'shootout' => true,
                'kick' => $kickNumber,
            ],
        ]);

        if ($isGoal) {
            $this->recordAction($match, 120, min(59, 10 + $kickNumber), $kickNumber, $attackerClubId, (int) $taker->player_id, (int) $goalkeeper->player_id, 'penalty', 'scored', ['shootout' => true]);
            $this->incrementPlayerState($taker, ['goals' => 1]);

            return true;
        }

        $this->recordAction($match, 120, min(59, 20 + $kickNumber), $kickNumber, $attackerClubId, (int) $taker->player_id, (int) $goalkeeper->player_id, 'penalty', 'missed', ['shootout' => true]);
        if ($this->simulationStrategy->shouldCreatePenaltySaveEventInShootout()) {
            $match->events()->create([
                'minute' => 120,
                'second' => min(59, 20 + $kickNumber),
                'club_id' => $defenderClubId,
                'player_id' => $goalkeeper->player_id,
                'event_type' => 'save',
                'metadata' => [
                    'shootout' => true,
                    'kick' => $kickNumber,
                ],
            ]);
            $this->incrementPlayerState($goalkeeper, ['saves' => 1]);
        }

        return false;
    }

    private function canFinish(GameMatch $match): bool
    {
        if ($this->isCup($match)) {
            if ((int) $match->home_score === (int) $match->away_score) {
                return (int) $match->live_minute >= 120;
            }

            return (int) $match->live_minute >= 90;
        }

        return (int) $match->live_minute >= 90;
    }

    private function matchMinuteLimit(GameMatch $match): int
    {
        if (
            $this->isCup($match)
            && (int) $match->live_minute >= 90
            && (int) $match->home_score === (int) $match->away_score
        ) {
            return 120;
        }

        return 90;
    }

    private function isCup(GameMatch $match): bool
    {
        return $this->competitionContextService->isCup($match);
    }

    private function phaseFromMinute(int $minute): string
    {
        if ($minute <= 45) {
            return 'first_half';
        }
        if ($minute <= 90) {
            return 'second_half';
        }
        if ($minute <= 105) {
            return 'extra_time_first';
        }

        return 'extra_time_second';
    }

    private function initializeLiveState(GameMatch $match): void
    {
        $match->loadMissing(['homeClub.players', 'awayClub.players']);
        MatchLiveAction::query()->where('match_id', $match->id)->delete();
        MatchLiveStateTransition::query()->where('match_id', $match->id)->delete();
        MatchLivePlayerState::query()->where('match_id', $match->id)->delete();

        foreach ([(int) $match->home_club_id, (int) $match->away_club_id] as $clubId) {
            $club = $clubId === (int) $match->home_club_id ? $match->homeClub : $match->awayClub;
            $style = $this->lineupStyle($club, $match);

            MatchLiveTeamState::query()->updateOrCreate(
                ['match_id' => $match->id, 'club_id' => $clubId],
                [
                    'tactical_style' => $style,
                    'phase' => 'pre_match',
                    'possession_seconds' => 0,
                    'actions_count' => 0,
                    'dangerous_attacks' => 0,
                    'pass_attempts' => 0,
                    'pass_completions' => 0,
                    'tackle_attempts' => 0,
                    'tackle_won' => 0,
                    'fouls_committed' => 0,
                    'corners_won' => 0,
                    'shots' => 0,
                    'shots_on_target' => 0,
                    'expected_goals' => 0,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                    'substitutions_used' => 0,
                    'tactical_changes_count' => 0,
                    'last_tactical_change_minute' => null,
                    'last_substitution_minute' => null,
                    'current_ball_carrier_player_id' => null,
                    'last_set_piece_taker_player_id' => null,
                    'last_set_piece_type' => null,
                    'last_set_piece_minute' => null,
                ]
            );

            $lineup = $this->ensureMatchLineup($match, $club)->load('players');
            $rows = [];
            foreach ($lineup->players as $player) {
                $slot = strtoupper((string) $player->pivot->pitch_position);
                $rows[] = [
                    'match_id' => $match->id,
                    'club_id' => $clubId,
                    'player_id' => $player->id,
                    'slot' => $slot,
                    'is_on_pitch' => !(bool) $player->pivot->is_bench && !str_starts_with($slot, 'OUT-'),
                    'is_sent_off' => false,
                    'is_injured' => false,
                    'fit_factor' => round($this->positionService->fitFactorWithProfile(
                        (string) ($player->position_main ?: $player->position),
                        (string) $player->position_second,
                        (string) $player->position_third,
                        $slot
                    ), 2),
                    'minutes_played' => 0,
                    'ball_contacts' => 0,
                    'pass_attempts' => 0,
                    'pass_completions' => 0,
                    'tackle_attempts' => 0,
                    'tackle_won' => 0,
                    'fouls_committed' => 0,
                    'fouls_suffered' => 0,
                    'shots' => 0,
                    'shots_on_target' => 0,
                    'goals' => 0,
                    'assists' => 0,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                    'saves' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($rows !== []) {
                MatchLivePlayerState::query()->upsert(
                    $rows,
                    ['match_id', 'player_id'],
                    ['club_id', 'slot', 'is_on_pitch', 'is_sent_off', 'is_injured', 'fit_factor', 'updated_at']
                );
            }
        }
    }

    private function activePlayerStates(GameMatch $match, int $clubId): Collection
    {
        return MatchLivePlayerState::query()
            ->with('player')
            ->where('match_id', $match->id)
            ->where('club_id', $clubId)
            ->where('is_on_pitch', true)
            ->where('is_sent_off', false)
            ->where('is_injured', false)
            ->get()
            ->filter(fn(MatchLivePlayerState $state): bool => $state->player !== null)
            ->values();
    }

    private function penaltyEligibleStates(GameMatch $match, int $clubId): Collection
    {
        return MatchLivePlayerState::query()
            ->with('player')
            ->where('match_id', $match->id)
            ->where('club_id', $clubId)
            ->where('is_sent_off', false)
            ->where('is_injured', false)
            ->get()
            ->filter(fn(MatchLivePlayerState $state): bool => $state->player !== null)
            ->values();
    }

    private function goalkeeperState(Collection $states): ?MatchLivePlayerState
    {
        /** @var MatchLivePlayerState|null $goalkeeper */
        $goalkeeper = $states->first(function (MatchLivePlayerState $state): bool {
            return $this->positionService->groupFromPosition(
                (string) ($state->player?->position_main ?: $state->player?->position)
            ) === 'GK';
        });

        return $goalkeeper;
    }

    private function weightedStatePick(Collection $states, callable $weightResolver): MatchLivePlayerState
    {
        $total = max(1, (int) $states->sum(function (MatchLivePlayerState $state) use ($weightResolver): int {
            return max(1, (int) $weightResolver($state));
        }));
        $hit = $this->randomInt(1, $total);
        $cursor = 0;

        /** @var MatchLivePlayerState $state */
        foreach ($states as $state) {
            $cursor += max(1, (int) $weightResolver($state));
            if ($cursor >= $hit) {
                return $state;
            }
        }

        return $states->first();
    }

    private function teamStateFor(GameMatch $match, int $clubId): MatchLiveTeamState
    {
        $state = MatchLiveTeamState::query()
            ->where('match_id', $match->id)
            ->where('club_id', $clubId)
            ->first();

        if ($state) {
            return $state;
        }

        /** @var Club|null $club */
        $club = Club::query()->find($clubId);
        $style = $club ? $this->lineupStyle($club, $match) : 'balanced';

        return MatchLiveTeamState::query()->create([
            'match_id' => $match->id,
            'club_id' => $clubId,
            'tactical_style' => $style,
            'phase' => 'pre_match',
            'current_ball_carrier_player_id' => null,
            'last_set_piece_taker_player_id' => null,
            'last_set_piece_type' => null,
            'last_set_piece_minute' => null,
        ]);
    }

    private function playerStateFor(GameMatch $match, int $playerId): ?MatchLivePlayerState
    {
        return MatchLivePlayerState::query()
            ->where('match_id', $match->id)
            ->where('player_id', $playerId)
            ->first();
    }

    private function incrementTeamState(GameMatch $match, int $clubId, array $increments = [], array $overrides = []): void
    {
        $state = $this->teamStateFor($match, $clubId);
        foreach ($increments as $column => $delta) {
            $state->{$column} = (float) ($state->{$column} ?? 0) + $delta;
        }
        foreach ($overrides as $column => $value) {
            $state->{$column} = $value;
        }
        $state->save();
    }

    private function syncTeamPhase(GameMatch $match, int $clubId, string $phase, int $minute): void
    {
        $state = $this->teamStateFor($match, $clubId);
        $fromPhase = $state->phase ? (string) $state->phase : null;

        if ($fromPhase !== $phase) {
            $this->recordStateTransition(
                $match,
                $minute,
                0,
                $clubId,
                'phase_change',
                $fromPhase,
                $phase,
                null
            );
        }

        if ($fromPhase !== $phase) {
            $state->phase = $phase;
            $state->save();
        }
    }

    private function rememberBallCarrier(GameMatch $match, int $clubId, int $playerId): void
    {
        $this->incrementTeamState($match, $clubId, [], [
            'current_ball_carrier_player_id' => $playerId > 0 ? $playerId : null,
        ]);
    }

    private function rememberSetPieceTaker(
        GameMatch $match,
        int $clubId,
        int $playerId,
        string $setPieceType,
        int $minute
    ): void {
        $this->incrementTeamState($match, $clubId, [], [
            'last_set_piece_taker_player_id' => $playerId > 0 ? $playerId : null,
            'last_set_piece_type' => $setPieceType,
            'last_set_piece_minute' => max(0, min(120, $minute)),
        ]);
    }

    private function incrementPlayerState(MatchLivePlayerState $state, array $increments = [], array $overrides = []): void
    {
        foreach ($increments as $column => $delta) {
            $state->{$column} = (float) ($state->{$column} ?? 0) + $delta;
        }
        foreach ($overrides as $column => $value) {
            $state->{$column} = $value;
        }
        $state->save();
    }

    private function recordAction(
        GameMatch $match,
        int $minute,
        int $second,
        int $sequence,
        ?int $clubId,
        ?int $playerId,
        ?int $opponentPlayerId,
        string $actionType,
        ?string $outcome,
        ?array $metadata
    ): void {
        MatchLiveAction::query()->create([
            'match_id' => $match->id,
            'minute' => max(0, min(120, $minute)),
            'second' => max(0, min(59, $second)),
            'sequence' => max(0, min(999, $sequence)),
            'club_id' => $clubId,
            'player_id' => $playerId,
            'opponent_player_id' => $opponentPlayerId,
            'action_type' => $actionType,
            'outcome' => $outcome,
            'metadata' => $metadata,
        ]);
    }

    private function recordStateTransition(
        GameMatch $match,
        int $minute,
        int $second,
        ?int $clubId,
        string $transitionType,
        ?string $fromPhase,
        ?string $toPhase,
        ?array $metadata
    ): void {
        MatchLiveStateTransition::query()->create([
            'match_id' => $match->id,
            'club_id' => $clubId,
            'minute' => max(0, min(120, $minute)),
            'second' => max(0, min(59, $second)),
            'transition_type' => $transitionType,
            'from_phase' => $fromPhase,
            'to_phase' => $toPhase,
            'metadata' => $metadata,
        ]);
    }

    private function persistMinuteSnapshot(GameMatch $match, int $minute): void
    {
        $homeClubId = (int) $match->home_club_id;
        $awayClubId = (int) $match->away_club_id;
        $homeState = $this->teamStateFor($match, $homeClubId);
        $awayState = $this->teamStateFor($match, $awayClubId);

        $planCounts = MatchPlannedSubstitution::query()
            ->where('match_id', $match->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        MatchLiveMinuteSnapshot::query()->updateOrCreate(
            [
                'match_id' => $match->id,
                'minute' => max(0, min(120, $minute)),
            ],
            [
                'home_score' => max(0, (int) $match->home_score),
                'away_score' => max(0, (int) $match->away_score),
                'home_phase' => $homeState->phase ? (string) $homeState->phase : null,
                'away_phase' => $awayState->phase ? (string) $awayState->phase : null,
                'home_tactical_style' => $homeState->tactical_style ? (string) $homeState->tactical_style : null,
                'away_tactical_style' => $awayState->tactical_style ? (string) $awayState->tactical_style : null,
                'pending_plans' => min(255, (int) ($planCounts['pending'] ?? 0)),
                'executed_plans' => min(255, (int) ($planCounts['executed'] ?? 0)),
                'skipped_plans' => min(255, (int) ($planCounts['skipped'] ?? 0)),
                'invalid_plans' => min(255, (int) ($planCounts['invalid'] ?? 0)),
                'payload' => [
                    'home' => [
                        'possession_seconds' => (int) $homeState->possession_seconds,
                        'actions_count' => (int) $homeState->actions_count,
                        'dangerous_attacks' => (int) $homeState->dangerous_attacks,
                        'substitutions_used' => (int) $homeState->substitutions_used,
                        'current_ball_carrier_player_id' => $homeState->current_ball_carrier_player_id !== null
                            ? (int) $homeState->current_ball_carrier_player_id
                            : null,
                        'last_set_piece_taker_player_id' => $homeState->last_set_piece_taker_player_id !== null
                            ? (int) $homeState->last_set_piece_taker_player_id
                            : null,
                        'last_set_piece_type' => $homeState->last_set_piece_type ? (string) $homeState->last_set_piece_type : null,
                        'last_set_piece_minute' => $homeState->last_set_piece_minute !== null
                            ? (int) $homeState->last_set_piece_minute
                            : null,
                    ],
                    'away' => [
                        'possession_seconds' => (int) $awayState->possession_seconds,
                        'actions_count' => (int) $awayState->actions_count,
                        'dangerous_attacks' => (int) $awayState->dangerous_attacks,
                        'substitutions_used' => (int) $awayState->substitutions_used,
                        'current_ball_carrier_player_id' => $awayState->current_ball_carrier_player_id !== null
                            ? (int) $awayState->current_ball_carrier_player_id
                            : null,
                        'last_set_piece_taker_player_id' => $awayState->last_set_piece_taker_player_id !== null
                            ? (int) $awayState->last_set_piece_taker_player_id
                            : null,
                        'last_set_piece_type' => $awayState->last_set_piece_type ? (string) $awayState->last_set_piece_type : null,
                        'last_set_piece_minute' => $awayState->last_set_piece_minute !== null
                            ? (int) $awayState->last_set_piece_minute
                            : null,
                    ],
                ],
            ]
        );
    }

    private function markPlayerUnavailableInLineup(GameMatch $match, int $clubId, int $playerId, int $minute): void
    {
        $lineup = Lineup::query()
            ->with('players')
            ->where('match_id', $match->id)
            ->where('club_id', $clubId)
            ->first();
        if (!$lineup) {
            return;
        }

        /** @var Player|null $player */
        $player = $lineup->players->firstWhere('id', $playerId);
        if (!$player) {
            return;
        }

        $slot = strtoupper((string) $player->pivot->pitch_position);
        if ((bool) $player->pivot->is_bench || str_starts_with($slot, 'OUT-')) {
            return;
        }

        $lineup->players()->updateExistingPivot($playerId, [
            'pitch_position' => 'OUT-' . $minute,
            'is_bench' => true,
            'x_coord' => null,
            'y_coord' => null,
            'bench_order' => null,
            'sort_order' => min(255, 230 + $this->randomInt(1, 20)),
        ]);
    }

    private function isValidTargetSlotForSubstitution(Lineup $lineup, string $targetSlot, string $fallbackOutSlot): bool
    {
        $validSlots = $lineup->players
            ->filter(function (Player $player): bool {
                $slot = strtoupper((string) $player->pivot->pitch_position);

                return !(bool) $player->pivot->is_bench && !str_starts_with($slot, 'OUT-');
            })
            ->map(fn(Player $player): string => strtoupper((string) $player->pivot->pitch_position))
            ->unique()
            ->values();

        return $targetSlot === $fallbackOutSlot || $validSlots->contains($targetSlot);
    }

    private function canSubstituteGoalkeeper(GameMatch $match, int $clubId, Player $playerOut, Player $playerIn): bool
    {
        $outIsGoalkeeper = $this->positionService->groupFromPosition(
            (string) ($playerOut->position_main ?: $playerOut->position)
        ) === 'GK';
        if (!$outIsGoalkeeper) {
            return true;
        }

        $inIsGoalkeeper = $this->positionService->groupFromPosition(
            (string) ($playerIn->position_main ?: $playerIn->position)
        ) === 'GK';
        if ($inIsGoalkeeper) {
            return true;
        }

        $remainingGoalkeepers = $this->activePlayerStates($match, $clubId)
            ->filter(function (MatchLivePlayerState $state) use ($playerOut): bool {
                if ((int) $state->player_id === (int) $playerOut->id) {
                    return false;
                }

                return $this->positionService->groupFromPosition(
                    (string) ($state->player?->position_main ?: $state->player?->position)
                ) === 'GK';
            });

        return $remainingGoalkeepers->isNotEmpty();
    }

    private function lineupStyle(Club $club, GameMatch $match): string
    {
        /** @var Lineup|null $lineup */
        $lineup = $club->lineups()->where('match_id', $match->id)->first();
        if (!$lineup) {
            $lineup = $club->lineups()->where('is_active', true)->first();
        }

        return (string) ($lineup?->tactical_style ?: 'balanced');
    }

    private function teamStrengthFromStates(Collection $states, bool $isHome, string $style): float
    {
        $avg = function (string $field) use ($states): float {
            return (float) $states->avg(fn(MatchLivePlayerState $state): float => ((float) $state->player->{$field}) * ((float) $state->fit_factor));
        };

        $overall = $avg('overall');
        $attack = $avg('shooting');
        $buildUp = $avg('passing');
        $defense = $avg('defending');
        $condition = ((float) $states->avg(fn(MatchLivePlayerState $state): float => (float) $state->player->stamina)
            + (float) $states->avg(fn(MatchLivePlayerState $state): float => (float) $state->player->morale)) / 2;

        $score = ($overall * 0.4) + ($attack * 0.2) + ($buildUp * 0.15) + ($defense * 0.15) + ($condition * 0.1);
        if ($isHome) {
            $score += 3.5;
        }

        return $score + match ($style) {
            'offensive' => 2.6,
            'defensive' => 1.1,
            'counter' => 1.5,
            default => 0.0,
        };
    }

    private function resolveMatchSquad(Club $club, GameMatch $match): Collection
    {
        $lineup = $this->ensureMatchLineup($match, $club)->load('players');
        if ($lineup->players->isNotEmpty()) {
            $starters = $lineup->players
                ->filter(function (Player $player): bool {
                    $slot = strtoupper((string) $player->pivot->pitch_position);

                    return !(bool) $player->pivot->is_bench && !str_starts_with($slot, 'OUT-');
                })
                ->sortBy(fn(Player $player) => (int) $player->pivot->sort_order)
                ->take(11)
                ->values();

            if ($starters->isNotEmpty()) {
                return $starters;
            }
        }

        return $club->players()
            ->orderByDesc('overall')
            ->limit(11)
            ->get();
    }

    private function resolveMatchParticipants(Club $club, GameMatch $match): Collection
    {
        $lineup = $this->ensureMatchLineup($match, $club)->load('players');
        if ($lineup->players->isNotEmpty()) {
            return $lineup->players->values();
        }

        return $this->resolveMatchSquad($club, $match);
    }

    private function ensureMatchLineup(GameMatch $match, Club $club): Lineup
    {
        /** @var Lineup|null $lineup */
        $lineup = $club->lineups()
            ->with('players')
            ->where('match_id', $match->id)
            ->first();

        if ($lineup && $lineup->players->isNotEmpty()) {
            return $lineup;
        }

        /** @var Lineup|null $source */
        $source = $club->lineups()
            ->with('players')
            ->whereNull('match_id')
            ->where('is_active', true)
            ->first();
        if (!$source) {
            $source = $club->lineups()
                ->with('players')
                ->whereNull('match_id')
                ->where('is_template', true)
                ->orderBy('id')
                ->first();
        }

        $lineup = $club->lineups()->updateOrCreate(
            ['match_id' => $match->id],
            [
                'name' => 'Live Match ' . $match->id,
                'formation' => $source?->formation ?: '4-4-2',
                'tactical_style' => $source?->tactical_style ?: 'balanced',
                'attack_focus' => $source?->attack_focus ?: 'center',
                'penalty_taker_player_id' => $source?->penalty_taker_player_id,
                'free_kick_taker_player_id' => $source?->free_kick_taker_player_id,
                'corner_left_taker_player_id' => $source?->corner_left_taker_player_id,
                'corner_right_taker_player_id' => $source?->corner_right_taker_player_id,
                'is_active' => true,
                'is_template' => false,
            ]
        );

        if ($source && $source->players->isNotEmpty()) {
            $pivot = $source->players->mapWithKeys(function (Player $player): array {
                return [
                    $player->id => [
                        'pitch_position' => $player->pivot->pitch_position,
                        'sort_order' => $player->pivot->sort_order,
                        'x_coord' => $player->pivot->x_coord,
                        'y_coord' => $player->pivot->y_coord,
                        'is_captain' => (bool) $player->pivot->is_captain,
                        'is_set_piece_taker' => (bool) $player->pivot->is_set_piece_taker,
                        'is_bench' => (bool) $player->pivot->is_bench,
                        'bench_order' => $player->pivot->bench_order,
                    ],
                ];
            })->all();
            $lineup->players()->sync($pivot);

            return $lineup->load('players');
        }

        $availablePlayers = $club->players()
            ->whereIn('status', ['active', 'transfer_listed', 'suspended'])
            ->orderByDesc('overall')
            ->get();
        $selection = $this->formationPlannerService->strongestByFormation(
            $availablePlayers,
            '4-4-2',
            $this->maxBenchPlayers()
        );
        $slots = collect($this->formationPlannerService->starterSlots('4-4-2'))->keyBy('slot');

        $pivot = [];
        $starterOrder = 1;
        foreach ($selection['starters'] as $slot => $playerId) {
            if (!$playerId) {
                continue;
            }

            $slotInfo = $slots->get($slot);
            $pivot[$playerId] = [
                'pitch_position' => $slot,
                'sort_order' => $starterOrder,
                'x_coord' => $slotInfo['x'] ?? null,
                'y_coord' => $slotInfo['y'] ?? null,
                'is_captain' => $starterOrder === 1,
                'is_set_piece_taker' => false,
                'is_bench' => false,
                'bench_order' => null,
            ];
            $starterOrder++;
        }

        foreach ($selection['bench'] as $index => $playerId) {
            if (isset($pivot[$playerId])) {
                continue;
            }

            $benchOrder = $index + 1;
            $pivot[$playerId] = [
                'pitch_position' => 'BANK-' . $benchOrder,
                'sort_order' => 100 + $benchOrder,
                'x_coord' => null,
                'y_coord' => null,
                'is_captain' => false,
                'is_set_piece_taker' => false,
                'is_bench' => true,
                'bench_order' => $benchOrder,
            ];
        }

        if ($pivot !== []) {
            $lineup->players()->sync($pivot);
        }

        return $lineup->load('players');
    }

    private function attendance(Club $homeClub): int
    {
        $homeClub->loadMissing('stadium');
        $capacity = (int) ($homeClub->stadium?->capacity ?? 18000);
        $experience = (int) ($homeClub->stadium?->fan_experience ?? 60);
        $base = max(4500, (int) round($homeClub->fanbase * (0.10 + ($experience / 1000))));
        $variation = $this->randomInt(-2500, 4200);
        $attendance = max(2500, $base + $variation);

        return min($capacity, $attendance);
    }

    private function weather(): string
    {
        $weather = ['clear', 'cloudy', 'rainy', 'windy'];

        return $weather[$this->randomArrayKey($weather)];
    }

    private function randomInt(int $min, int $max): int
    {
        if ($this->deterministicEnabled()) {
            return mt_rand($min, $max);
        }

        return random_int($min, $max);
    }

    private function randomArrayKey(array $values): int|string
    {
        if ($values === []) {
            return 0;
        }

        if ($this->deterministicEnabled()) {
            $keys = array_keys($values);
            $index = mt_rand(0, max(0, count($keys) - 1));

            return $keys[$index];
        }

        return array_rand($values);
    }

    private function randomCollectionItem(Collection $states): MatchLivePlayerState
    {
        /** @var MatchLivePlayerState|null $fallback */
        $fallback = $states->first();
        if (!$fallback) {
            throw new \RuntimeException('Cannot pick random state from empty collection.');
        }

        $items = $states->values();
        $index = $this->randomInt(0, max(0, $items->count() - 1));

        /** @var MatchLivePlayerState|null $picked */
        $picked = $items->get($index);

        return $picked ?? $fallback;
    }

    private function deterministicEnabled(): bool
    {
        return (bool) config('simulation.deterministic.enabled', false);
    }

    private function seedDeterministicScope(GameMatch $match, string $scope, ?int $minute = null): void
    {
        if (!$this->deterministicEnabled()) {
            return;
        }

        $baseSeed = (int) ($match->simulation_seed ?: 1);
        $minuteSeed = max(0, (int) ($minute ?? 0)) * 997;
        $scopeSeed = abs((int) crc32($scope)) % 100_000;

        mt_srand($baseSeed + $minuteSeed + $scopeSeed);
    }

    private function loadState(GameMatch $match): GameMatch
    {
        return $match->fresh([
            'homeClub',
            'awayClub',
            'events.player',
            'events.assister',
            'events.club',
            'playerStats.player',
            'playerStats.club',
            'liveTeamStates.club',
            'livePlayerStates.player',
            'liveActions.club',
            'liveActions.player',
            'liveActions.opponentPlayer',
            'liveStateTransitions.club',
            'liveMinuteSnapshots',
            'plannedSubstitutions.playerOut',
            'plannedSubstitutions.playerIn',
        ]);
    }
}
