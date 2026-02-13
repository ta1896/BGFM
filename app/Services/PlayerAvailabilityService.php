<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\MatchLivePlayerState;
use App\Models\Player;

class PlayerAvailabilityService
{
    private const SUSPENSION_COLUMN_BY_CONTEXT = [
        CompetitionContextService::LEAGUE => 'suspension_league_remaining',
        CompetitionContextService::CUP_NATIONAL => 'suspension_cup_national_remaining',
        CompetitionContextService::CUP_INTERNATIONAL => 'suspension_cup_international_remaining',
        CompetitionContextService::FRIENDLY => 'suspension_friendly_remaining',
    ];
    private const BOOKING_COLUMN_BY_CONTEXT = [
        CompetitionContextService::LEAGUE => 'yellow_cards_league_accumulated',
        CompetitionContextService::CUP_NATIONAL => 'yellow_cards_cup_national_accumulated',
        CompetitionContextService::CUP_INTERNATIONAL => 'yellow_cards_cup_international_accumulated',
        CompetitionContextService::FRIENDLY => 'yellow_cards_friendly_accumulated',
    ];

    public function __construct(
        private readonly CompetitionContextService $contextService
    ) {
    }

    /**
     * @return array<int, array{
     *   player_id:int,
     *   club_id:int|null,
     *   context:string,
     *   injury_before:int,
     *   injury_after:int,
     *   injury_assigned:int,
     *   suspension_before:int,
     *   suspension_after:int,
     *   suspension_assigned:int,
     *   yellow_cards_in_match:int,
     *   yellow_suspension_assigned:int,
     *   bookings_before:int,
     *   bookings_after:int,
     *   yellow_threshold:int
     * }>
     */
    public function applyMatchConsequences(GameMatch $match): array
    {
        $states = MatchLivePlayerState::query()->where('match_id', $match->id)->get();
        if ($states->isEmpty()) {
            return [];
        }

        $context = $this->contextService->forMatch($match);
        $suspensionColumn = $this->suspensionColumnForContext($context);
        $bookingColumn = $this->bookingColumnForContext($context);
        $players = Player::query()->whereIn('id', $states->pluck('player_id')->all())->get()->keyBy('id');
        $changes = [];

        foreach ($states as $state) {
            /** @var Player|null $player */
            $player = $players->get((int) $state->player_id);
            if (!$player) {
                continue;
            }

            $injuryBefore = (int) $player->injury_matches_remaining;
            $suspensionBefore = (int) $player->{$suspensionColumn};
            $bookingsBefore = (int) $player->{$bookingColumn};
            $injuryAfter = $injuryBefore;
            $suspensionAfter = $suspensionBefore;
            $bookingsAfter = $bookingsBefore;
            $yellowCardsInMatch = max(0, (int) $state->yellow_cards);
            $yellowThreshold = $this->yellowCardThreshold($context);
            $yellowSuspensionAssigned = 0;
            $changed = false;

            if ((bool) $state->is_injured) {
                $injuryAfter = max(
                    $injuryBefore,
                    $this->rollInjuryMatches()
                );
                $changed = true;
            }

            if ((int) $state->red_cards > 0) {
                $suspensionAfter = max(
                    $suspensionAfter,
                    $this->rollSuspensionMatches($context, (int) $state->red_cards)
                );
                $changed = true;
            }

            if ((int) $state->red_cards < 1 && $yellowCardsInMatch > 0 && $this->yellowCardRulesEnabled()) {
                $bookingsAfter = min(99, $bookingsBefore + $yellowCardsInMatch);
                if ($yellowThreshold > 0) {
                    $suspensionByYellow = $this->yellowCardSuspensionMatches($context);
                    while ($bookingsAfter >= $yellowThreshold) {
                        $bookingsAfter -= $yellowThreshold;
                        $yellowSuspensionAssigned += $suspensionByYellow;
                    }
                }

                if ($yellowSuspensionAssigned > 0) {
                    $suspensionAfter = min(10, $suspensionAfter + $yellowSuspensionAssigned);
                }

                $changed = true;
            }

            if ($changed) {
                $player->injury_matches_remaining = $injuryAfter;
                $player->{$suspensionColumn} = $suspensionAfter;
                $player->{$bookingColumn} = $bookingsAfter;
                $this->syncLegacySuspensionCounter($player);
                $player->status = $this->resolveStatus(
                    (int) $player->injury_matches_remaining,
                    $this->maxContextSuspensionRemaining($player),
                    (string) $player->status
                );
                $player->save();

                $changes[] = [
                    'player_id' => (int) $player->id,
                    'club_id' => $player->club_id ? (int) $player->club_id : null,
                    'context' => $context,
                    'injury_before' => $injuryBefore,
                    'injury_after' => $injuryAfter,
                    'injury_assigned' => max(0, $injuryAfter - $injuryBefore),
                    'suspension_before' => $suspensionBefore,
                    'suspension_after' => $suspensionAfter,
                    'suspension_assigned' => max(0, $suspensionAfter - $suspensionBefore),
                    'yellow_cards_in_match' => $yellowCardsInMatch,
                    'yellow_suspension_assigned' => $yellowSuspensionAssigned,
                    'bookings_before' => $bookingsBefore,
                    'bookings_after' => $bookingsAfter,
                    'yellow_threshold' => $yellowThreshold,
                ];
            }
        }

        return $changes;
    }

    public function decrementCountersForClub(int $clubId): void
    {
        $this->decrementCountersForContext($clubId, CompetitionContextService::LEAGUE);
    }

    public function decrementCountersForMatch(int $clubId, GameMatch $match): void
    {
        $this->decrementCountersForContext($clubId, $this->contextService->forMatch($match));
    }

    public function resetSeasonalBookingCounters(): void
    {
        Player::query()->update([
            'yellow_cards_league_accumulated' => 0,
            'yellow_cards_cup_national_accumulated' => 0,
            'yellow_cards_cup_international_accumulated' => 0,
            'yellow_cards_friendly_accumulated' => 0,
        ]);
    }

    public function isPlayerAvailableForLiveMatch(Player $player, ?GameMatch $match = null): bool
    {
        if (!in_array((string) $player->status, ['active', 'transfer_listed', 'suspended'], true)) {
            return false;
        }

        if ((int) $player->injury_matches_remaining > 0) {
            return false;
        }

        if ($match !== null) {
            $context = $this->contextService->forMatch($match);
            $suspensionColumn = $this->suspensionColumnForContext($context);

            return (int) $player->{$suspensionColumn} < 1;
        }

        return (int) $player->suspension_matches_remaining < 1;
    }

    private function decrementCountersForContext(int $clubId, string $context): void
    {
        $suspensionColumn = $this->suspensionColumnForContext($context);
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
            $contextSuspensionRemaining = max(0, (int) $player->{$suspensionColumn} - 1);
            $player->{$suspensionColumn} = $contextSuspensionRemaining;
            $legacySuspension = $this->maxContextSuspensionRemaining($player);

            $player->update([
                'injury_matches_remaining' => $injuryRemaining,
                $suspensionColumn => $contextSuspensionRemaining,
                'suspension_matches_remaining' => $legacySuspension,
                'status' => $this->resolveStatus(
                    $injuryRemaining,
                    $legacySuspension,
                    (string) $player->status
                ),
            ]);
        }
    }

    private function suspensionColumnForContext(string $context): string
    {
        return self::SUSPENSION_COLUMN_BY_CONTEXT[$context] ?? 'suspension_league_remaining';
    }

    private function bookingColumnForContext(string $context): string
    {
        return self::BOOKING_COLUMN_BY_CONTEXT[$context] ?? 'yellow_cards_league_accumulated';
    }

    private function maxContextSuspensionRemaining(Player $player): int
    {
        return max(
            0,
            (int) $player->suspension_league_remaining,
            (int) $player->suspension_cup_national_remaining,
            (int) $player->suspension_cup_international_remaining,
            (int) $player->suspension_friendly_remaining
        );
    }

    private function syncLegacySuspensionCounter(Player $player): void
    {
        $player->suspension_matches_remaining = $this->maxContextSuspensionRemaining($player);
    }

    private function resolveStatus(int $injuryRemaining, int $suspensionRemaining, string $currentStatus): string
    {
        if ($injuryRemaining > 0) {
            return 'injured';
        }

        if ($suspensionRemaining > 0) {
            return 'suspended';
        }

        if (in_array($currentStatus, ['injured', 'suspended'], true)) {
            return 'active';
        }

        return $currentStatus;
    }

    private function rollInjuryMatches(): int
    {
        $min = max(0, (int) config('simulation.aftermath.injury.min_matches', 1));
        $max = max($min, (int) config('simulation.aftermath.injury.max_matches', 4));

        return random_int($min, $max);
    }

    private function rollSuspensionMatches(string $context, int $redCards): int
    {
        $scope = in_array($context, $this->contextService->allContexts(), true) ? $context : 'default';

        $min = (int) config('simulation.aftermath.suspension.'.$scope.'.min_matches');
        $max = (int) config('simulation.aftermath.suspension.'.$scope.'.max_matches');

        if ($min < 1 || $max < 1) {
            $min = max(1, (int) config('simulation.aftermath.suspension.default.min_matches', 1));
            $max = max($min, (int) config('simulation.aftermath.suspension.default.max_matches', 3));
        }

        $base = random_int($min, max($min, $max));
        $extra = max(0, $redCards - 1);

        return min(10, $base + $extra);
    }

    private function yellowCardRulesEnabled(): bool
    {
        return (bool) config('simulation.aftermath.yellow_cards.enabled', true);
    }

    private function yellowCardThreshold(string $context): int
    {
        $scope = in_array($context, $this->contextService->allContexts(), true) ? $context : 'default';
        $threshold = (int) config('simulation.aftermath.yellow_cards.'.$scope.'.threshold', -1);

        if ($threshold < 1) {
            $threshold = max(1, (int) config('simulation.aftermath.yellow_cards.default.threshold', 5));
        }

        return $threshold;
    }

    private function yellowCardSuspensionMatches(string $context): int
    {
        $scope = in_array($context, $this->contextService->allContexts(), true) ? $context : 'default';
        $matches = (int) config('simulation.aftermath.yellow_cards.'.$scope.'.suspension_matches', -1);

        if ($matches < 0) {
            $matches = max(0, (int) config('simulation.aftermath.yellow_cards.default.suspension_matches', 1));
        }

        return $matches;
    }
}
