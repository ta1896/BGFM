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
                    'group' => $this->positionService->slotGroup($slot, (string) ($player->position_main ?: $player->position)),
                    'fit' => $this->positionService->fitFactorWithProfile(
                        (string) ($player->position_main ?: $player->position),
                        (string) $player->position_second,
                        (string) $player->position_third,
                        $slot
                    ),
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
            $base = 0.0;
            foreach ((array) config('simulation.team_strength.weights.' . $type, []) as $attribute => $weight) {
                $base += ((float) $player->{$attribute}) * (float) $weight;
            }

            $conditionFactor = (($player->stamina + $player->morale) / 200) + 0.5;

            return min(99, $base * $conditionFactor * $fit);
        });
    }

    private function chemistry(Collection $players): float
    {
        $morale = $players->avg(fn (array $entry) => (float) $entry['player']->morale);
        $stamina = $players->avg(fn (array $entry) => (float) $entry['player']->stamina);
        $fit = $players->avg(fn (array $entry) => (float) $entry['fit']);

        $sizeBonus = min((int) config('simulation.team_strength.chemistry.size_bonus_cap', 10), $players->count());
        $fitModifier = max(
            (float) config('simulation.team_strength.chemistry.fit_modifier_min', 0.82),
            min((float) config('simulation.team_strength.chemistry.fit_modifier_max', 1.0), $fit ?: 1.0)
        );

        return min(100, ((($morale + $stamina) / 2) + ($sizeBonus / 2)) * $fitModifier);
    }

    private function formationFactor(string $formation, int $count): float
    {
        $minimumPlayers = (int) config('simulation.team_strength.formation_factor.minimum_players', 8);
        if ($count < $minimumPlayers) {
            return (float) config('simulation.team_strength.formation_factor.incomplete_lineup', 0.8);
        }

        return (float) config('simulation.team_strength.formation_factor.complete_lineup', 1.0);
    }
}
