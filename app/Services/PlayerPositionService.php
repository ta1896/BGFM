<?php

namespace App\Services;

class PlayerPositionService
{
    /**
     * Resolves a lineup slot (e.g. IV-L, ZM, ST) to a broad role group.
     */
    public function slotGroup(?string $slot, ?string $fallbackPosition = null): ?string
    {
        $normalized = strtoupper(trim((string) $slot));
        $fallbackGroup = $this->groupFromPosition($fallbackPosition) ?? ($fallbackPosition ? strtoupper($fallbackPosition) : null);

        if ($normalized === '') {
            return $fallbackGroup;
        }

        if (str_starts_with($normalized, 'BANK-')) {
            return null;
        }

        return $this->groupFromPosition($normalized) ?? $fallbackGroup;
    }

    /**
     * Normalizes a player position (e.g. LV, ZM, ST) to a broad role group.
     */
    public function groupFromPosition(?string $position): ?string
    {
        $normalized = strtoupper(trim((string) $position));
        if ($normalized === '') {
            return null;
        }

        $map = [
            'TW' => 'GK',
            'GK' => 'GK',
            'LV' => 'DEF',
            'IV' => 'DEF',
            'RV' => 'DEF',
            'LWB' => 'DEF',
            'RWB' => 'DEF',
            'LM' => 'MID',
            'ZM' => 'MID',
            'RM' => 'MID',
            'DM' => 'MID',
            'OM' => 'MID',
            'LAM' => 'MID',
            'ZOM' => 'MID',
            'RAM' => 'MID',
            'LS' => 'FWD',
            'MS' => 'FWD',
            'RS' => 'FWD',
            'ST' => 'FWD',
            'LW' => 'FWD',
            'RW' => 'FWD',
            'DEF' => 'DEF',
            'MID' => 'MID',
            'FWD' => 'FWD',
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        $base = preg_replace('/-.*$/', '', $normalized);
        if ($base !== $normalized && isset($map[$base])) {
            return $map[$base];
        }

        if (str_starts_with($normalized, 'IV')) {
            return 'DEF';
        }
        if (str_starts_with($normalized, 'DM') || str_starts_with($normalized, 'ZM')) {
            return 'MID';
        }
        if (str_starts_with($normalized, 'ST')) {
            return 'FWD';
        }

        return null;
    }

    /**
     * Returns a multiplier for player's effective strength on assigned position.
     */
    public function fitFactor(string $playerPosition, ?string $slot): float
    {
        $player = $this->groupFromPosition($playerPosition) ?? strtoupper($playerPosition);
        $assigned = $this->slotGroup($slot, $player);

        if (!$assigned || $assigned === $player) {
            return 1.0;
        }

        if ($player === 'GK' || $assigned === 'GK') {
            return 0.55;
        }

        return match ($assigned) {
            'DEF' => match ($player) {
                'MID' => 0.84,
                'FWD' => 0.72,
                default => 0.8,
            },
            'MID' => match ($player) {
                'DEF', 'FWD' => 0.88,
                default => 0.82,
            },
            'FWD' => match ($player) {
                'MID' => 0.86,
                'DEF' => 0.72,
                default => 0.8,
            },
            default => 0.8,
        };
    }
}
