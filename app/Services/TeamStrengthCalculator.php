<?php

namespace App\Services;

use App\Models\Lineup;
use Illuminate\Support\Collection;

class TeamStrengthCalculator
{
    public function __construct(private readonly PlayerPositionService $positionService)
    {
    }

    public function calculate(Lineup $lineup): array
    {
        $entries = $lineup->players
            ->filter(fn ($player) => !$player->pivot->is_bench)
            ->map(function ($player) {
                $slot = (string) ($player->pivot->pitch_position ?? '');

                return [
                    'player' => $player,
                    'group' => $this->positionService->slotGroup($slot, $player->position),
                    'fit' => $this->positionService->fitFactor($player->position, $slot),
                ];
            })
            ->values();

        if ($entries->isEmpty()) {
            return [
                'overall' => 0,
                'attack' => 0,
                'midfield' => 0,
                'defense' => 0,
                'chemistry' => 0,
            ];
        }

        $attackers = $entries->where('group', 'FWD');
        $midfielders = $entries->where('group', 'MID');
        $defenders = $entries->whereIn('group', ['DEF', 'GK']);

        $attackScore = $this->positionScore($attackers, 'attack');
        $midfieldScore = $this->positionScore($midfielders, 'midfield');
        $defenseScore = $this->positionScore($defenders, 'defense');

        $baseOverall = round(($attackScore + $midfieldScore + $defenseScore) / 3);
        $chemistry = $this->chemistry($entries);

        $formationFactor = $this->formationFactor($lineup->formation, $entries->count());
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

        return $players->avg(function (array $entry) use ($type) {
            $player = $entry['player'];
            $fit = (float) $entry['fit'];

            $base = match ($type) {
                'attack' => ($player->shooting * 0.4) + ($player->pace * 0.2) + ($player->physical * 0.15) + ($player->overall * 0.25),
                'midfield' => ($player->passing * 0.35) + ($player->pace * 0.15) + ($player->defending * 0.2) + ($player->overall * 0.3),
                default => ($player->defending * 0.4) + ($player->physical * 0.2) + ($player->passing * 0.1) + ($player->overall * 0.3),
            };

            $conditionFactor = (($player->stamina + $player->morale) / 200) + 0.5;

            return min(99, $base * $conditionFactor * $fit);
        });
    }

    private function chemistry(Collection $players): float
    {
        $morale = $players->avg(fn (array $entry) => (float) $entry['player']->morale);
        $stamina = $players->avg(fn (array $entry) => (float) $entry['player']->stamina);
        $fit = $players->avg(fn (array $entry) => (float) $entry['fit']);

        $sizeBonus = min(10, $players->count());
        $fitModifier = max(0.82, min(1.0, $fit ?: 1.0));

        return min(100, ((($morale + $stamina) / 2) + ($sizeBonus / 2)) * $fitModifier);
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
