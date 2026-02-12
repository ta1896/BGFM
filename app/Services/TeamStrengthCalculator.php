<?php

namespace App\Services;

use App\Models\Lineup;
use Illuminate\Support\Collection;

class TeamStrengthCalculator
{
    public function calculate(Lineup $lineup): array
    {
        $players = $lineup->players
            ->filter(fn ($player) => !$player->pivot->is_bench)
            ->values();

        if ($players->isEmpty()) {
            return [
                'overall' => 0,
                'attack' => 0,
                'midfield' => 0,
                'defense' => 0,
                'chemistry' => 0,
            ];
        }

        $attackers = $players->where('position', 'FWD');
        $midfielders = $players->where('position', 'MID');
        $defenders = $players->whereIn('position', ['DEF', 'GK']);

        $attackScore = $this->positionScore($attackers, 'attack');
        $midfieldScore = $this->positionScore($midfielders, 'midfield');
        $defenseScore = $this->positionScore($defenders, 'defense');

        $baseOverall = round(($attackScore + $midfieldScore + $defenseScore) / 3);
        $chemistry = $this->chemistry($players);

        $formationFactor = $this->formationFactor($lineup->formation, $players->count());
        $overall = (int) round(min(99, $baseOverall * $formationFactor * ($chemistry / 100)));

        return [
            'overall' => $overall,
            'attack' => (int) round($attackScore),
            'midfield' => (int) round($midfieldScore),
            'defense' => (int) round($defenseScore),
            'chemistry' => (int) round($chemistry),
        ];
    }

    private function positionScore(Collection $players, string $type): float
    {
        if ($players->isEmpty()) {
            return 0;
        }

        return $players->avg(function ($player) use ($type) {
            $base = match ($type) {
                'attack' => ($player->shooting * 0.4) + ($player->pace * 0.2) + ($player->physical * 0.15) + ($player->overall * 0.25),
                'midfield' => ($player->passing * 0.35) + ($player->pace * 0.15) + ($player->defending * 0.2) + ($player->overall * 0.3),
                default => ($player->defending * 0.4) + ($player->physical * 0.2) + ($player->passing * 0.1) + ($player->overall * 0.3),
            };

            $conditionFactor = (($player->stamina + $player->morale) / 200) + 0.5;

            return min(99, $base * $conditionFactor);
        });
    }

    private function chemistry(Collection $players): float
    {
        $morale = $players->avg('morale');
        $stamina = $players->avg('stamina');

        $sizeBonus = min(10, $players->count());

        return min(100, (($morale + $stamina) / 2) + ($sizeBonus / 2));
    }

    private function formationFactor(string $formation, int $count): float
    {
        if ($count < 8) {
            return 0.8;
        }

        $known = ['4-3-3', '4-4-2', '3-5-2', '4-2-3-1', '5-3-2'];

        return in_array($formation, $known, true) ? 1.0 : 0.95;
    }
}
