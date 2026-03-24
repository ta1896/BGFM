<?php

namespace App\Services;

use Illuminate\Support\Collection;

class MatchStrengthService
{
    public function fromPlayers(Collection $players, bool $isHome, float $multiplier = 1.0): float
    {
        return $this->applyModifiers(
            $this->weightedAverage($players, false),
            $isHome,
            $multiplier,
            0.0
        );
    }

    public function fromLiveStates(Collection $states, bool $isHome, float $bonus = 0.0): float
    {
        return $this->applyModifiers(
            $this->weightedAverage($states, true),
            $isHome,
            1.0,
            $bonus
        );
    }

    public function liveStyleBonus(string $style): float
    {
        return match ($style) {
            'offensive' => 2.6,
            'defensive' => 1.1,
            'counter' => 1.5,
            default => 0.0,
        };
    }

    private function weightedAverage(Collection $items, bool $useFitFactor): float
    {
        $weights = (array) config('simulation.match_strength.weights', []);
        if ($items->isEmpty() || $weights === []) {
            return 0.0;
        }

        return (float) $items->avg(function ($item) use ($weights, $useFitFactor): float {
            $subject = $useFitFactor ? $item->player : $item;
            $fitFactor = $useFitFactor ? (float) ($item->fit_factor ?? 1.0) : 1.0;
            $score = 0.0;

            foreach ($weights as $attribute => $weight) {
                $score += ((float) ($subject->{$attribute} ?? 0)) * (float) $weight;
            }

            return $score * $fitFactor;
        });
    }

    private function applyModifiers(float $score, bool $isHome, float $multiplier, float $bonus): float
    {
        $score *= $multiplier;

        if ($isHome) {
            $score += (float) config('simulation.match_strength.home_bonus', 3.5);
        }

        return $score + $bonus;
    }
}
