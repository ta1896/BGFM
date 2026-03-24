<?php

namespace App\Services;

use App\Models\Player;

class MatchPlayerRoleService
{
    public function __construct(private readonly PlayerPositionService $positionService)
    {
    }

    public function positionCodeForStat(Player $player, ?string $slot = null): string
    {
        $resolvedSlot = strtoupper(trim((string) ($slot ?: ($player->pivot?->pitch_position ?? ''))));
        if ($resolvedSlot !== '') {
            if (str_starts_with($resolvedSlot, 'BANK-')) {
                return 'SUB';
            }

            if (str_starts_with($resolvedSlot, 'OUT-')) {
                return $this->fallbackPositionCode($player);
            }

            if (strlen($resolvedSlot) <= 4) {
                return $resolvedSlot;
            }
        }

        return $this->fallbackPositionCode($player);
    }

    public function isGoalkeeper(Player $player): bool
    {
        return $this->positionService->groupFromPosition((string) ($player->position_main ?: $player->position)) === 'GK';
    }

    private function fallbackPositionCode(Player $player): string
    {
        $position = strtoupper((string) $player->position);

        return strlen($position) <= 4 ? $position : substr($position, 0, 4);
    }
}
