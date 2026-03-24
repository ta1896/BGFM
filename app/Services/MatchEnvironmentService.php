<?php

namespace App\Services;

use App\Models\Club;

class MatchEnvironmentService
{
    public function __construct(private readonly MatchRandomService $random)
    {
    }

    public function attendance(Club $homeClub, bool $deterministic = false): int
    {
        $homeClub->loadMissing('stadium');
        $capacity = (int) ($homeClub->stadium?->capacity ?? 18000);
        $experience = (int) ($homeClub->stadium?->fan_experience ?? 60);

        $base = max(4500, (int) round($homeClub->fanbase * (0.10 + ($experience / 1000))));
        $variation = $this->random->randomInt(-2500, 4200, $deterministic);
        $attendance = max(2500, $base + $variation);

        return min($capacity, $attendance);
    }

    public function weather(bool $deterministic = false): string
    {
        $weather = ['clear', 'cloudy', 'rainy', 'windy'];

        return $weather[$this->random->randomArrayKey($weather, $deterministic)];
    }
}
