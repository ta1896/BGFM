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

    public function __construct(
        private readonly CompetitionContextService $contextService
    ) {
    }

    public function applyMatchConsequences(GameMatch $match): void
    {
        $states = MatchLivePlayerState::query()->where('match_id', $match->id)->get();
        if ($states->isEmpty()) {
            return;
        }

        $context = $this->contextService->forMatch($match);
        $suspensionColumn = $this->suspensionColumnForContext($context);
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
                $changed = true;
            }

            if ((int) $state->red_cards > 0) {
                $player->{$suspensionColumn} = max((int) $player->{$suspensionColumn}, random_int(1, 3));
                $changed = true;
            }

            if ($changed) {
                $this->syncLegacySuspensionCounter($player);
                $player->status = $this->resolveStatus(
                    (int) $player->injury_matches_remaining,
                    $this->maxContextSuspensionRemaining($player),
                    (string) $player->status
                );
                $player->save();
            }
        }
    }

    public function decrementCountersForClub(int $clubId): void
    {
        $this->decrementCountersForContext($clubId, CompetitionContextService::LEAGUE);
    }

    public function decrementCountersForMatch(int $clubId, GameMatch $match): void
    {
        $this->decrementCountersForContext($clubId, $this->contextService->forMatch($match));
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
}
