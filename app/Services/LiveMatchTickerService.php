<?php

namespace App\Services;

use App\Models\Club;
use App\Models\CompetitionSeason;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\MatchLiveAction;
use App\Models\MatchLivePlayerState;
use App\Models\MatchLiveTeamState;
use App\Models\Player;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LiveMatchTickerService
{
    private const MAX_SUBSTITUTIONS = 5;

    private const MAX_TACTICAL_CHANGES = 3;

    private const MIN_MINUTE_FOR_TACTICAL_CHANGE = 5;

    private const MINUTES_BETWEEN_TACTICAL_CHANGES = 10;

    private const MINUTES_BETWEEN_SUBSTITUTIONS = 3;

    public function __construct(
        private readonly CpuClubDecisionService $cpuDecisionService,
        private readonly LeagueTableService $tableService,
        private readonly PlayerPositionService $positionService,
        private readonly FinanceCycleService $financeCycleService,
        private readonly FormationPlannerService $formationPlannerService
    ) {
    }

    public function start(GameMatch $match): GameMatch
    {
        if ($match->status === 'played') {
            return $this->loadState($match);
        }

        if ($match->status === 'live') {
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
            $match->update([
                'status' => 'live',
                'home_score' => (int) ($match->home_score ?? 0),
                'away_score' => (int) ($match->away_score ?? 0),
                'attendance' => $match->attendance ?: $this->attendance($match->homeClub),
                'weather' => $match->weather ?: $this->weather(),
                'live_minute' => max(0, (int) $match->live_minute),
                'live_paused' => false,
                'live_error_message' => null,
                'live_last_tick_at' => now(),
            ]);

            $this->initializeLiveState($match->fresh(['homeClub', 'awayClub']));
        });

        return $this->loadState($match);
    }

    public function resume(GameMatch $match): GameMatch
    {
        if ($match->status !== 'live') {
            return $this->loadState($match);
        }

        $match->update([
            'live_paused' => false,
            'live_error_message' => null,
            'live_last_tick_at' => now(),
        ]);

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
                random_int(0, 59),
                0,
                $clubId,
                null,
                null,
                'tactical_change',
                $style,
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

        if (!$this->isPlayerAvailableForLive($playerIn)) {
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

    public function tick(GameMatch $match, int $minutes = 1): GameMatch
    {
        $minutes = max(1, min(120, $minutes));

        if ($match->status === 'scheduled') {
            $this->start($match);
            $match = $match->fresh();
        }

        if ($match->status !== 'live' || $match->live_paused) {
            return $this->loadState($match);
        }

        for ($i = 0; $i < $minutes; $i++) {
            $match->refresh();
            if ($match->status !== 'live' || $match->live_paused) {
                break;
            }

            $nextMinute = ((int) $match->live_minute) + 1;
            if ($nextMinute > $this->matchMinuteLimit($match)) {
                break;
            }

            $this->simulateMinute($match, $nextMinute);
            $match->update([
                'live_minute' => $nextMinute,
                'live_last_tick_at' => now(),
            ]);
        }

        $match->refresh();
        if ($match->status === 'live' && $this->canFinish($match)) {
            $this->finish($match);
        }

        return $this->loadState($match);
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

        DB::transaction(function () use (
            $lineup,
            $match,
            $clubId,
            $minute,
            $teamState,
            $playerOut,
            $playerOutState,
            $playerIn,
            $playerInState,
            $targetSlotOccupant,
            $resolvedTargetSlot,
            $outSort,
            $outX,
            $outY,
            $outSlot,
            $targetSort,
            $targetX,
            $targetY
        ): void {
            $lineup->players()->updateExistingPivot($playerOut->id, [
                'pitch_position' => 'OUT-'.$minute,
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
                'second' => random_int(0, 59),
                'club_id' => $clubId,
                'player_id' => $playerIn->id,
                'assister_player_id' => $playerOut->id,
                'event_type' => 'substitution',
                'metadata' => [
                    'player_in_id' => $playerIn->id,
                    'player_out_id' => $playerOut->id,
                    'target_slot' => $resolvedTargetSlot,
                    'fit_factor' => $this->positionService->fitFactor((string) $playerIn->position, $resolvedTargetSlot),
                ],
            ]);

            $teamState->update([
                'substitutions_used' => (int) $teamState->substitutions_used + 1,
                'last_substitution_minute' => $minute,
            ]);

            $playerOutState->update([
                'is_on_pitch' => false,
                'slot' => 'OUT-'.$minute,
            ]);

            $playerInState->update([
                'is_on_pitch' => true,
                'slot' => $resolvedTargetSlot,
                'fit_factor' => round($this->positionService->fitFactor((string) $playerIn->position, $resolvedTargetSlot), 2),
            ]);

            if ($targetSlotOccupant) {
                $targetState = $this->playerStateFor($match, (int) $targetSlotOccupant->id);
                if ($targetState) {
                    $targetState->update([
                        'is_on_pitch' => true,
                        'slot' => $outSlot,
                        'fit_factor' => round($this->positionService->fitFactor((string) $targetSlotOccupant->position, $outSlot), 2),
                    ]);
                }
            }

            $this->recordAction(
                $match,
                $minute,
                random_int(0, 59),
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
        });
    }

    private function finish(GameMatch $match): void
    {
        $match->loadMissing(['homeClub.players', 'awayClub.players']);
        $homePlayers = $this->resolveMatchParticipants($match->homeClub, $match);
        $awayPlayers = $this->resolveMatchParticipants($match->awayClub, $match);

        DB::transaction(function () use ($match, $homePlayers, $awayPlayers): void {
            $this->decrementAvailabilityCountersForClub((int) $match->home_club_id);
            $this->decrementAvailabilityCountersForClub((int) $match->away_club_id);

            if ($this->isCup($match) && (int) $match->home_score === (int) $match->away_score) {
                $this->resolvePenaltyShootout($match);
            }

            $match->update([
                'status' => 'played',
                'live_minute' => min((int) $match->live_minute, $this->matchMinuteLimit($match)),
                'live_paused' => false,
                'live_error_message' => null,
                'played_at' => now(),
            ]);

            $match->playerStats()->delete();
            $this->createPlayerStats($match, $homePlayers, $awayPlayers);
            $this->applyAvailabilityConsequences($match);
        });

        if ($match->competition_season_id) {
            $competitionSeason = CompetitionSeason::query()->find($match->competition_season_id);
            if ($competitionSeason) {
                if ((string) $match->type === 'league') {
                    $this->tableService->rebuild($competitionSeason);
                }

                if ($this->isCup($match)) {
                    $this->progressCupRoundIfNeeded($competitionSeason, $match);
                }
            }
        }

        $this->financeCycleService->settleMatch($match->fresh());
    }

    private function simulateMinute(GameMatch $match, int $minute): void
    {
        if ($this->isCup($match)
            && $minute === 91
            && (int) $match->home_score === (int) $match->away_score
            && !$match->extra_time) {
            $match->update(['extra_time' => true]);
            $this->recordAction($match, $minute, 0, 0, null, null, null, 'phase', 'extra_time_start', ['minute' => $minute]);
        }

        MatchLivePlayerState::query()
            ->where('match_id', $match->id)
            ->where('is_on_pitch', true)
            ->where('is_sent_off', false)
            ->where('is_injured', false)
            ->increment('minutes_played');

        $homeStates = $this->activePlayerStates($match, (int) $match->home_club_id);
        $awayStates = $this->activePlayerStates($match, (int) $match->away_club_id);
        if ($homeStates->isEmpty() || $awayStates->isEmpty()) {
            return;
        }

        $homeStyle = $this->teamStateFor($match, (int) $match->home_club_id)->tactical_style;
        $awayStyle = $this->teamStateFor($match, (int) $match->away_club_id)->tactical_style;
        $homeStrength = $this->teamStrengthFromStates($homeStates, true, (string) $homeStyle);
        $awayStrength = $this->teamStrengthFromStates($awayStates, false, (string) $awayStyle);

        $homePossession = max(22, min(78, 50 + (($homeStrength - $awayStrength) / 4) + random_int(-5, 5)));
        $homeSeconds = max(15, min(45, (int) round(($homePossession / 100) * 60)));

        $this->incrementTeamState($match, (int) $match->home_club_id, ['possession_seconds' => $homeSeconds], ['phase' => $this->phaseFromMinute($minute)]);
        $this->incrementTeamState($match, (int) $match->away_club_id, ['possession_seconds' => (60 - $homeSeconds)], ['phase' => $this->phaseFromMinute($minute)]);

        $sequences = random_int(3, 5);
        for ($sequence = 1; $sequence <= $sequences; $sequence++) {
            $attackerClubId = random_int(1, 100) <= $homePossession
                ? (int) $match->home_club_id
                : (int) $match->away_club_id;
            $defenderClubId = $attackerClubId === (int) $match->home_club_id
                ? (int) $match->away_club_id
                : (int) $match->home_club_id;

            $this->simulateActionSequence($match, $minute, $sequence, $attackerClubId, $defenderClubId, $homeStrength, $awayStrength);
        }

        $this->simulateRandomInjury($match, $minute, (int) $match->home_club_id);
        $this->simulateRandomInjury($match, $minute, (int) $match->away_club_id);
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
            fn (MatchLivePlayerState $state): int => max(5, (int) round((($state->player->passing + $state->player->pace + $state->player->shooting) / 3) * (float) $state->fit_factor))
        );

        $this->incrementTeamState($match, $attackerClubId, ['actions_count' => 1, 'pass_attempts' => 1]);
        $this->incrementPlayerState($ballCarrier, ['ball_contacts' => 1, 'pass_attempts' => 1]);
        $this->recordAction($match, $minute, random_int(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, null, 'possession', 'start', null);

        $passProbability = max(0.56, min(0.93, 0.70 + ((((float) $ballCarrier->player->passing * (float) $ballCarrier->fit_factor) - 60) / 180)));
        if (!$this->roll($passProbability)) {
            $this->recordAction($match, $minute, random_int(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, null, 'pass', 'failed', null);

            return;
        }

        $this->incrementTeamState($match, $attackerClubId, ['pass_completions' => 1]);
        $this->incrementPlayerState($ballCarrier, ['pass_completions' => 1]);
        $this->recordAction($match, $minute, random_int(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, null, 'pass', 'complete', null);

        if ($this->roll(0.45)) {
            $tackler = $this->weightedStatePick(
                $defenders,
                fn (MatchLivePlayerState $state): int => max(5, (int) round((($state->player->defending + $state->player->physical) / 2) * (float) $state->fit_factor))
            );

            $this->incrementTeamState($match, $defenderClubId, ['tackle_attempts' => 1]);
            $this->incrementPlayerState($tackler, ['tackle_attempts' => 1]);

            $tackleWinProbability = max(0.25, min(0.82, 0.50 + (((float) $tackler->player->defending - (float) $ballCarrier->player->pace) / 260)));
            if ($this->roll($tackleWinProbability)) {
                $this->incrementTeamState($match, $defenderClubId, ['tackle_won' => 1]);
                $this->incrementPlayerState($tackler, ['tackle_won' => 1]);
                $this->recordAction($match, $minute, random_int(0, 59), $sequence, $defenderClubId, (int) $tackler->player_id, (int) $ballCarrier->player_id, 'tackle', 'won', null);

                if ($this->roll(0.18)) {
                    $this->handleFoulAndSetPiece($match, $minute, $sequence, $defenderClubId, $attackerClubId, $tackler, $ballCarrier);
                }

                return;
            }

            $this->recordAction($match, $minute, random_int(0, 59), $sequence, $defenderClubId, (int) $tackler->player_id, (int) $ballCarrier->player_id, 'tackle', 'lost', null);
        }

        $this->incrementTeamState($match, $attackerClubId, ['dangerous_attacks' => 1, 'shots' => 1]);

        $xg = max(0.03, min(0.48, 0.10 + (((($attackerClubId === (int) $match->home_club_id) ? $homeStrength : $awayStrength) - ((($attackerClubId === (int) $match->home_club_id) ? $awayStrength : $homeStrength))) / 400) + (random_int(0, 12) / 100)));
        $this->incrementTeamState($match, $attackerClubId, [], [
            'expected_goals' => (float) $this->teamStateFor($match, $attackerClubId)->expected_goals + $xg,
        ]);

        $match->events()->create([
            'minute' => $minute,
            'second' => random_int(0, 59),
            'club_id' => $attackerClubId,
            'player_id' => $ballCarrier->player_id,
            'event_type' => 'chance',
            'metadata' => [
                'quality' => $xg >= 0.24 || random_int(1, 100) <= 38 ? 'big' : 'normal',
                'xg_bucket' => round($xg, 2),
                'sequence' => $sequence,
            ],
        ]);

        $this->incrementPlayerState($ballCarrier, ['shots' => 1]);
        if ($this->roll(0.12)) {
            $this->incrementTeamState($match, $attackerClubId, ['corners_won' => 1]);
            $this->recordAction($match, $minute, random_int(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, null, 'set_piece', 'corner', null);
        }

        $onTargetProbability = max(0.22, min(0.78, 0.34 + ((((float) $ballCarrier->player->shooting * (float) $ballCarrier->fit_factor) - 58) / 220)));
        if (!$this->roll($onTargetProbability)) {
            $this->recordAction($match, $minute, random_int(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, null, 'shot', 'off_target', ['xg' => round($xg, 2)]);

            return;
        }

        $this->incrementTeamState($match, $attackerClubId, ['shots_on_target' => 1]);
        $this->incrementPlayerState($ballCarrier, ['shots_on_target' => 1]);

        $goalkeeper = $this->goalkeeperState($defenders) ?? $defenders->random();
        $saveProbability = max(0.18, min(0.86, 0.55 + ((((float) $goalkeeper->player->overall * (float) $goalkeeper->fit_factor) - ((float) $ballCarrier->player->shooting * (float) $ballCarrier->fit_factor)) / 300) - ($xg / 2.5)));

        if ($this->roll($saveProbability)) {
            $match->events()->create([
                'minute' => $minute,
                'second' => random_int(0, 59),
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
            $this->recordAction($match, $minute, random_int(0, 59), $sequence, $defenderClubId, (int) $goalkeeper->player_id, (int) $ballCarrier->player_id, 'shot', 'saved', ['xg' => round($xg, 2)]);

            return;
        }

        $assistState = null;
        if ($attackers->count() > 1 && random_int(1, 100) <= 68) {
            $assistCandidates = $attackers->where('player_id', '!=', $ballCarrier->player_id)->values();
            if ($assistCandidates->isNotEmpty()) {
                $assistState = $assistCandidates->random();
            }
        }

        $match->events()->create([
            'minute' => $minute,
            'second' => random_int(0, 59),
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

        $this->recordAction($match, $minute, random_int(0, 59), $sequence, $attackerClubId, (int) $ballCarrier->player_id, (int) $goalkeeper->player_id, 'shot', 'goal', ['xg' => round($xg, 2)]);
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
        $this->recordAction($match, $minute, random_int(0, 59), $sequence, $defenderClubId, (int) $foulingPlayer->player_id, (int) $victim->player_id, 'foul', 'committed', null);

        $isRed = $this->roll(0.04);
        $isYellow = !$isRed && $this->roll(0.30);
        if ($isYellow) {
            $match->events()->create([
                'minute' => $minute,
                'second' => random_int(0, 59),
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
                'second' => random_int(0, 59),
                'club_id' => $defenderClubId,
                'player_id' => $foulingPlayer->player_id,
                'event_type' => 'red_card',
                'metadata' => ['sequence' => $sequence],
            ]);

            $this->incrementTeamState($match, $defenderClubId, ['red_cards' => 1]);
            $this->incrementPlayerState($foulingPlayer, ['red_cards' => 1], [
                'is_sent_off' => true,
                'is_on_pitch' => false,
                'slot' => 'OUT-'.$minute,
            ]);
            $this->markPlayerUnavailableInLineup($match, $defenderClubId, (int) $foulingPlayer->player_id, $minute);
        }

        if ($this->roll(0.14)) {
            $this->recordAction($match, $minute, random_int(0, 59), $sequence, $attackerClubId, (int) $victim->player_id, (int) $foulingPlayer->player_id, 'set_piece', 'penalty_awarded', null);
            $this->simulatePenaltyAttemptInPlay($match, $minute, $sequence, $attackerClubId, $defenderClubId, $victim);
        } else {
            $this->recordAction($match, $minute, random_int(0, 59), $sequence, $attackerClubId, (int) $victim->player_id, null, 'set_piece', 'free_kick', null);
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
            fn (MatchLivePlayerState $state): int => max(5, (int) round($state->player->shooting * (float) $state->fit_factor))
        );
        $goalkeeper = $this->goalkeeperState($defenders) ?? $defenders->random();

        $this->incrementTeamState($match, $attackerClubId, ['shots' => 1, 'shots_on_target' => 1], [
            'expected_goals' => (float) $this->teamStateFor($match, $attackerClubId)->expected_goals + 0.78,
        ]);
        $this->incrementPlayerState($taker, ['shots' => 1, 'shots_on_target' => 1]);

        $scoreProbability = max(0.55, min(0.94, 0.75 + (((float) $taker->player->shooting - (float) $goalkeeper->player->overall) / 300)));
        if ($this->roll($scoreProbability)) {
            $match->events()->create([
                'minute' => $minute,
                'second' => random_int(0, 59),
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
            $this->recordAction($match, $minute, random_int(0, 59), $sequence, $attackerClubId, (int) $taker->player_id, (int) $goalkeeper->player_id, 'penalty', 'scored', null);

            return;
        }

        $match->events()->create([
            'minute' => $minute,
            'second' => random_int(0, 59),
            'club_id' => $attackerClubId,
            'player_id' => $taker->player_id,
            'event_type' => 'penalty_missed',
            'metadata' => ['sequence' => $sequence],
        ]);
        if ($this->roll(0.68)) {
            $match->events()->create([
                'minute' => $minute,
                'second' => random_int(0, 59),
                'club_id' => $defenderClubId,
                'player_id' => $goalkeeper->player_id,
                'event_type' => 'save',
                'metadata' => ['sequence' => $sequence, 'penalty' => true],
            ]);
            $this->incrementPlayerState($goalkeeper, ['saves' => 1]);
        }

        $this->recordAction($match, $minute, random_int(0, 59), $sequence, $attackerClubId, (int) $taker->player_id, (int) $goalkeeper->player_id, 'penalty', 'missed', null);
    }

    private function simulateRandomInjury(GameMatch $match, int $minute, int $clubId): void
    {
        if (!$this->roll(0.012)) {
            return;
        }

        $squad = $this->activePlayerStates($match, $clubId);
        if ($squad->isEmpty()) {
            return;
        }

        $injured = $this->weightedStatePick(
            $squad,
            fn (MatchLivePlayerState $state): int => max(5, 120 - $state->player->stamina)
        );

        $injured->update([
            'is_injured' => true,
            'is_on_pitch' => false,
            'slot' => 'OUT-'.$minute,
        ]);
        $this->markPlayerUnavailableInLineup($match, $clubId, (int) $injured->player_id, $minute);

        $match->events()->create([
            'minute' => $minute,
            'second' => random_int(0, 59),
            'club_id' => $clubId,
            'player_id' => $injured->player_id,
            'event_type' => 'injury',
            'metadata' => ['severity_hint' => random_int(1, 100)],
        ]);

        $this->recordAction($match, $minute, random_int(0, 59), 0, $clubId, (int) $injured->player_id, null, 'injury', 'forced_off', null);
    }

    private function resolvePenaltyShootout(GameMatch $match): void
    {
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
            if ($this->roll(0.5)) {
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
            fn (MatchLivePlayerState $state): int => max(5, (int) round((($state->player->shooting + $state->player->morale) / 2) * (float) $state->fit_factor))
        );
        $goalkeeper = $this->goalkeeperState($defenders) ?? $defenders->random();

        $scoreProbability = max(0.55, min(0.94, 0.76 + (((float) $taker->player->shooting - (float) $goalkeeper->player->overall) / 300)));
        $isGoal = $this->roll($scoreProbability);

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
        if ($this->roll(0.66)) {
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

    private function progressCupRoundIfNeeded(CompetitionSeason $competitionSeason, GameMatch $match): void
    {
        if (!$this->isCup($match) || !$match->competition_season_id) {
            return;
        }

        $round = (int) ($match->round_number ?? 1);
        $currentRoundMatches = GameMatch::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->where('type', 'cup')
            ->where('round_number', $round)
            ->orderBy('id')
            ->get();

        if ($currentRoundMatches->isEmpty() || $currentRoundMatches->contains(fn (GameMatch $m): bool => $m->status !== 'played')) {
            return;
        }

        $nextRound = $round + 1;
        $nextRoundExists = GameMatch::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->where('type', 'cup')
            ->where('round_number', $nextRound)
            ->exists();
        if ($nextRoundExists) {
            return;
        }

        $winnerClubIds = $currentRoundMatches
            ->map(function (GameMatch $m): ?int {
                if ((int) $m->home_score > (int) $m->away_score) {
                    return (int) $m->home_club_id;
                }
                if ((int) $m->away_score > (int) $m->home_score) {
                    return (int) $m->away_club_id;
                }
                if ($m->penalties_home !== null && $m->penalties_away !== null) {
                    return (int) ($m->penalties_home > $m->penalties_away ? $m->home_club_id : $m->away_club_id);
                }

                return null;
            })
            ->filter()
            ->values();

        if ($winnerClubIds->count() <= 1) {
            return;
        }

        if ($winnerClubIds->count() % 2 !== 0) {
            $winnerClubIds = $winnerClubIds->slice(0, $winnerClubIds->count() - 1)->values();
        }

        if ($winnerClubIds->count() < 2) {
            return;
        }

        $kickoffAt = $currentRoundMatches->max('kickoff_at')
            ? $currentRoundMatches->max('kickoff_at')->copy()->addDays(7)
            : now()->addDays(7);

        foreach ($winnerClubIds->chunk(2) as $pair) {
            if ($pair->count() < 2) {
                continue;
            }

            GameMatch::query()->create([
                'competition_season_id' => $competitionSeason->id,
                'season_id' => $competitionSeason->season_id,
                'type' => 'cup',
                'stage' => 'Cup Runde '.$nextRound,
                'round_number' => $nextRound,
                'kickoff_at' => $kickoffAt,
                'status' => 'scheduled',
                'home_club_id' => (int) $pair->get(0),
                'away_club_id' => (int) $pair->get(1),
                'stadium_club_id' => (int) $pair->get(0),
                'simulation_seed' => random_int(10000, 99999),
            ]);

            $kickoffAt = $kickoffAt->copy()->addMinutes(30);
        }
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
        if ($this->isCup($match)
            && (int) $match->live_minute >= 90
            && (int) $match->home_score === (int) $match->away_score) {
            return 120;
        }

        return 90;
    }

    private function isCup(GameMatch $match): bool
    {
        return (string) $match->type === 'cup';
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
                    'fit_factor' => round($this->positionService->fitFactor((string) $player->position, $slot), 2),
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
            ->filter(fn (MatchLivePlayerState $state): bool => $state->player !== null)
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
            ->filter(fn (MatchLivePlayerState $state): bool => $state->player !== null)
            ->values();
    }

    private function goalkeeperState(Collection $states): ?MatchLivePlayerState
    {
        /** @var MatchLivePlayerState|null $goalkeeper */
        $goalkeeper = $states->first(function (MatchLivePlayerState $state): bool {
            return $this->positionService->groupFromPosition((string) $state->player?->position) === 'GK';
        });

        return $goalkeeper;
    }

    private function weightedStatePick(Collection $states, callable $weightResolver): MatchLivePlayerState
    {
        $total = max(1, (int) $states->sum(function (MatchLivePlayerState $state) use ($weightResolver): int {
            return max(1, (int) $weightResolver($state));
        }));
        $hit = random_int(1, $total);
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
            'pitch_position' => 'OUT-'.$minute,
            'is_bench' => true,
            'x_coord' => null,
            'y_coord' => null,
            'bench_order' => null,
            'sort_order' => min(255, 230 + random_int(1, 20)),
        ]);
    }

    private function applyAvailabilityConsequences(GameMatch $match): void
    {
        $states = MatchLivePlayerState::query()->where('match_id', $match->id)->get();
        if ($states->isEmpty()) {
            return;
        }

        $players = Player::query()->whereIn('id', $states->pluck('player_id')->all())->get()->keyBy('id');
        foreach ($states as $state) {
            /** @var Player|null $player */
            $player = $players->get((int) $state->player_id);
            if (!$player) {
                continue;
            }

            $changed = false;
            if ((bool) $state->is_injured) {
                $player->injury_matches_remaining = max((int) $player->injury_matches_remaining, random_int(1, 4));
                $player->status = 'injured';
                $changed = true;
            }

            if ((int) $state->red_cards > 0) {
                $player->suspension_matches_remaining = max((int) $player->suspension_matches_remaining, random_int(1, 3));
                if ((int) $player->injury_matches_remaining < 1) {
                    $player->status = 'suspended';
                }
                $changed = true;
            }

            if ($changed) {
                $player->save();
            }
        }
    }

    private function decrementAvailabilityCountersForClub(int $clubId): void
    {
        $players = Player::query()
            ->where('club_id', $clubId)
            ->where(function ($query): void {
                $query->where('injury_matches_remaining', '>', 0)
                    ->orWhere('suspension_matches_remaining', '>', 0)
                    ->orWhereIn('status', ['injured', 'suspended']);
            })
            ->get();

        foreach ($players as $player) {
            $injuryRemaining = max(0, (int) $player->injury_matches_remaining - 1);
            $suspensionRemaining = max(0, (int) $player->suspension_matches_remaining - 1);

            $nextStatus = $player->status;
            if ($injuryRemaining > 0) {
                $nextStatus = 'injured';
            } elseif ($suspensionRemaining > 0) {
                $nextStatus = 'suspended';
            } elseif (in_array($player->status, ['injured', 'suspended'], true)) {
                $nextStatus = 'active';
            }

            $player->update([
                'injury_matches_remaining' => $injuryRemaining,
                'suspension_matches_remaining' => $suspensionRemaining,
                'status' => $nextStatus,
            ]);
        }
    }

    private function isPlayerAvailableForLive(Player $player): bool
    {
        if (!in_array((string) $player->status, ['active', 'transfer_listed'], true)) {
            return false;
        }

        if ((int) $player->injury_matches_remaining > 0) {
            return false;
        }

        return (int) $player->suspension_matches_remaining < 1;
    }

    private function isValidTargetSlotForSubstitution(Lineup $lineup, string $targetSlot, string $fallbackOutSlot): bool
    {
        $validSlots = $lineup->players
            ->filter(function (Player $player): bool {
                $slot = strtoupper((string) $player->pivot->pitch_position);

                return !(bool) $player->pivot->is_bench && !str_starts_with($slot, 'OUT-');
            })
            ->map(fn (Player $player): string => strtoupper((string) $player->pivot->pitch_position))
            ->unique()
            ->values();

        return $targetSlot === $fallbackOutSlot || $validSlots->contains($targetSlot);
    }

    private function canSubstituteGoalkeeper(GameMatch $match, int $clubId, Player $playerOut, Player $playerIn): bool
    {
        $outIsGoalkeeper = $this->positionService->groupFromPosition((string) $playerOut->position) === 'GK';
        if (!$outIsGoalkeeper) {
            return true;
        }

        $inIsGoalkeeper = $this->positionService->groupFromPosition((string) $playerIn->position) === 'GK';
        if ($inIsGoalkeeper) {
            return true;
        }

        $remainingGoalkeepers = $this->activePlayerStates($match, $clubId)
            ->filter(function (MatchLivePlayerState $state) use ($playerOut): bool {
                if ((int) $state->player_id === (int) $playerOut->id) {
                    return false;
                }

                return $this->positionService->groupFromPosition((string) $state->player?->position) === 'GK';
            });

        return $remainingGoalkeepers->isNotEmpty();
    }

    private function roll(float $probability): bool
    {
        return (random_int(1, 10000) / 10000) <= $probability;
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
            return (float) $states->avg(fn (MatchLivePlayerState $state): float => ((float) $state->player->{$field}) * ((float) $state->fit_factor));
        };

        $overall = $avg('overall');
        $attack = $avg('shooting');
        $buildUp = $avg('passing');
        $defense = $avg('defending');
        $condition = ((float) $states->avg(fn (MatchLivePlayerState $state): float => (float) $state->player->stamina)
                + (float) $states->avg(fn (MatchLivePlayerState $state): float => (float) $state->player->morale)) / 2;

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
                ->sortBy(fn (Player $player) => (int) $player->pivot->sort_order)
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
                'name' => 'Live Match '.$match->id,
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
            ->whereIn('status', ['active', 'transfer_listed'])
            ->orderByDesc('overall')
            ->get();
        $selection = $this->formationPlannerService->strongestByFormation($availablePlayers, '4-4-2');
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
                'pitch_position' => 'BANK-'.$benchOrder,
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

    private function createPlayerStats(GameMatch $match, Collection $homePlayers, Collection $awayPlayers): void
    {
        $goalEvents = $match->events()->whereIn('event_type', ['goal', 'penalty_scored'])->get();
        $yellowEvents = $match->events()->where('event_type', 'yellow_card')->get();
        $redEvents = $match->events()->where('event_type', 'red_card')->get();
        $homeSubstitutions = $this->substitutionMinutes($match, (int) $match->home_club_id);
        $awaySubstitutions = $this->substitutionMinutes($match, (int) $match->away_club_id);
        $stateByPlayerId = MatchLivePlayerState::query()
            ->where('match_id', $match->id)
            ->get()
            ->keyBy('player_id');

        $build = function (
            Collection $players,
            int $clubId,
            array $substitutions
        ) use ($match, $goalEvents, $yellowEvents, $redEvents, $stateByPlayerId): array {
            return $players->values()->map(function (Player $player) use (
                $match,
                $clubId,
                $goalEvents,
                $yellowEvents,
                $redEvents,
                $substitutions,
                $stateByPlayerId
            ) {
                /** @var MatchLivePlayerState|null $state */
                $state = $stateByPlayerId->get($player->id);
                $goals = $state ? (int) $state->goals : $goalEvents->where('player_id', $player->id)->count();
                $assists = $state ? (int) $state->assists : $goalEvents->where('assister_player_id', $player->id)->count();
                $yellow = $state ? (int) $state->yellow_cards : $yellowEvents->where('player_id', $player->id)->count();
                $red = $state ? (int) $state->red_cards : $redEvents->where('player_id', $player->id)->count();
                $fit = $state ? (float) $state->fit_factor : $this->playerFit($player);

                $role = 'bench';
                if (isset($substitutions['out'][$player->id])) {
                    $role = 'sub_off';
                } elseif (isset($substitutions['in'][$player->id])) {
                    $role = 'sub_on';
                } else {
                    $slot = strtoupper((string) ($state?->slot ?: ($player->pivot?->pitch_position ?? '')));
                    if (!(bool) ($player->pivot?->is_bench ?? false) && !str_starts_with($slot, 'OUT-')) {
                        $role = 'starter';
                    }
                }

                $minutesPlayed = $state ? max(0, min((int) $match->live_minute, (int) $state->minutes_played)) : (int) $match->live_minute;
                $baseRating = 5.8
                    + (($player->overall * $fit) / 50)
                    + ($goals * 0.7)
                    + ($assists * 0.4)
                    - ($yellow * 0.25)
                    - ($red * 0.9)
                    - ((1 - $fit) * 1.6)
                    + ((random_int(0, 30) - 15) / 100);

                $shots = $state ? (int) $state->shots : max(0, $goals + random_int(0, 4));
                $passesCompleted = $state ? (int) $state->pass_completions : random_int(12, 74);
                $passAttempts = $state ? (int) $state->pass_attempts : ($passesCompleted + random_int(2, 19));
                $tacklesWon = $state ? (int) $state->tackle_won : random_int(0, 8);
                $tackleAttempts = $state ? (int) $state->tackle_attempts : ($tacklesWon + random_int(0, 5));
                $saves = $state ? (int) $state->saves : ($this->isGoalkeeper($player) ? random_int(1, 8) : 0);

                return [
                    'match_id' => $match->id,
                    'club_id' => $clubId,
                    'player_id' => $player->id,
                    'lineup_role' => $role,
                    'position_code' => $this->positionCodeForStat($player, $state?->slot),
                    'rating' => max(3.5, min(10.0, round($baseRating, 2))),
                    'minutes_played' => $minutesPlayed,
                    'goals' => $goals,
                    'assists' => $assists,
                    'yellow_cards' => $yellow,
                    'red_cards' => $red,
                    'shots' => $shots,
                    'passes_completed' => $passesCompleted,
                    'passes_failed' => max(0, $passAttempts - $passesCompleted),
                    'tackles_won' => $tacklesWon,
                    'tackles_lost' => max(0, $tackleAttempts - $tacklesWon),
                    'saves' => $this->isGoalkeeper($player) ? $saves : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();
        };

        DB::table('match_player_stats')->insert(array_merge(
            $build($homePlayers, (int) $match->home_club_id, $homeSubstitutions),
            $build($awayPlayers, (int) $match->away_club_id, $awaySubstitutions)
        ));
    }

    private function substitutionMinutes(GameMatch $match, int $clubId): array
    {
        $in = [];
        $out = [];
        $subEvents = $match->events()
            ->where('event_type', 'substitution')
            ->where('club_id', $clubId)
            ->get();

        foreach ($subEvents as $event) {
            $playerInId = (int) ($event->metadata['player_in_id'] ?? 0);
            $playerOutId = (int) ($event->metadata['player_out_id'] ?? 0);
            if ($playerInId > 0 && !isset($in[$playerInId])) {
                $in[$playerInId] = (int) $event->minute;
            }
            if ($playerOutId > 0 && !isset($out[$playerOutId])) {
                $out[$playerOutId] = (int) $event->minute;
            }
        }

        return ['in' => $in, 'out' => $out];
    }

    private function positionCodeForStat(Player $player, ?string $slot = null): string
    {
        $resolvedSlot = strtoupper(trim((string) ($slot ?: ($player->pivot?->pitch_position ?? ''))));
        if ($resolvedSlot !== '') {
            if (str_starts_with($resolvedSlot, 'BANK-')) {
                return 'SUB';
            }
            if (str_starts_with($resolvedSlot, 'OUT-')) {
                $position = strtoupper((string) $player->position);

                return strlen($position) <= 4 ? $position : substr($position, 0, 4);
            }
            if (strlen($resolvedSlot) <= 4) {
                return $resolvedSlot;
            }
        }

        $position = strtoupper((string) $player->position);

        return strlen($position) <= 4 ? $position : substr($position, 0, 4);
    }

    private function playerFit(Player $player): float
    {
        return $this->positionService->fitFactor((string) $player->position, $player->pivot?->pitch_position);
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
        $variation = random_int(-2500, 4200);
        $attendance = max(2500, $base + $variation);

        return min($capacity, $attendance);
    }

    private function weather(): string
    {
        $weather = ['clear', 'cloudy', 'rainy', 'windy'];

        return $weather[array_rand($weather)];
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
        ]);
    }
}
