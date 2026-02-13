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
        return $this->fitFactorWithProfile($playerPosition, null, null, $slot);
    }

    /**
     * Returns a multiplier using main/second/third position profile.
     */
    public function fitFactorWithProfile(
        ?string $positionMain,
        ?string $positionSecond,
        ?string $positionThird,
        ?string $slot
    ): float {
        $mainGroup = $this->groupFromPosition($positionMain);
        $secondGroup = $this->groupFromPosition($positionSecond);
        $thirdGroup = $this->groupFromPosition($positionThird);
        $fallback = $mainGroup ?? $secondGroup ?? $thirdGroup;
        $assigned = $this->slotGroup($slot, $fallback);

        if (!$assigned) {
            return $this->fitValue('main', 1.0);
        }

        if ($mainGroup && $assigned === $mainGroup) {
            return $this->fitValue('main', 1.0);
        }

        if ($secondGroup && $assigned === $secondGroup) {
            return $this->fitValue('second', 0.92);
        }

        if ($thirdGroup && $assigned === $thirdGroup) {
            return $this->fitValue('third', 0.84);
        }

        if (($mainGroup === 'GK') || $assigned === 'GK') {
            return $this->fitValue('foreign_gk', 0.55);
        }

        return $this->fitValue('foreign', 0.76);
    }

    private function fitValue(string $key, float $default): float
    {
        $value = (float) config('simulation.position_fit.'.$key, $default);

        return max(0.0, min(1.0, $value));
    }
}
