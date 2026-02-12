<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Collection;

class FormationPlannerService
{
    /**
     * @return array<int, string>
     */
    public function supportedFormations(): array
    {
        return ['4-4-2', '4-3-3', '4-2-3-1', '3-5-2', '5-3-2'];
    }

    /**
     * @return array<int, array{slot:string,label:string,group:string,x:int,y:int}>
     */
    public function starterSlots(string $formation): array
    {
        return match ($formation) {
            '4-4-2' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'LM', 'label' => 'LM', 'group' => 'MID', 'x' => 14, 'y' => 52],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 38, 'y' => 52],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 62, 'y' => 52],
                ['slot' => 'RM', 'label' => 'RM', 'group' => 'MID', 'x' => 86, 'y' => 52],
                ['slot' => 'ST-L', 'label' => 'ST', 'group' => 'FWD', 'x' => 43, 'y' => 32],
                ['slot' => 'ST-R', 'label' => 'ST', 'group' => 'FWD', 'x' => 57, 'y' => 32],
            ],
            '4-2-3-1' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'DM-L', 'label' => 'DM', 'group' => 'MID', 'x' => 40, 'y' => 58],
                ['slot' => 'DM-R', 'label' => 'DM', 'group' => 'MID', 'x' => 60, 'y' => 58],
                ['slot' => 'LAM', 'label' => 'LAM', 'group' => 'MID', 'x' => 22, 'y' => 43],
                ['slot' => 'ZOM', 'label' => 'ZOM', 'group' => 'MID', 'x' => 50, 'y' => 40],
                ['slot' => 'RAM', 'label' => 'RAM', 'group' => 'MID', 'x' => 78, 'y' => 43],
                ['slot' => 'ST', 'label' => 'ST', 'group' => 'FWD', 'x' => 50, 'y' => 26],
            ],
            '3-5-2' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 30, 'y' => 74],
                ['slot' => 'IV', 'label' => 'IV', 'group' => 'DEF', 'x' => 50, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 70, 'y' => 74],
                ['slot' => 'LWB', 'label' => 'LWB', 'group' => 'MID', 'x' => 12, 'y' => 56],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 35, 'y' => 52],
                ['slot' => 'ZM', 'label' => 'ZM', 'group' => 'MID', 'x' => 50, 'y' => 48],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 65, 'y' => 52],
                ['slot' => 'RWB', 'label' => 'RWB', 'group' => 'MID', 'x' => 88, 'y' => 56],
                ['slot' => 'ST-L', 'label' => 'ST', 'group' => 'FWD', 'x' => 43, 'y' => 30],
                ['slot' => 'ST-R', 'label' => 'ST', 'group' => 'FWD', 'x' => 57, 'y' => 30],
            ],
            '5-3-2' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LWB', 'label' => 'LWB', 'group' => 'DEF', 'x' => 10, 'y' => 70],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 30, 'y' => 74],
                ['slot' => 'IV', 'label' => 'IV', 'group' => 'DEF', 'x' => 50, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 70, 'y' => 74],
                ['slot' => 'RWB', 'label' => 'RWB', 'group' => 'DEF', 'x' => 90, 'y' => 70],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 35, 'y' => 52],
                ['slot' => 'ZM', 'label' => 'ZM', 'group' => 'MID', 'x' => 50, 'y' => 48],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 65, 'y' => 52],
                ['slot' => 'ST-L', 'label' => 'ST', 'group' => 'FWD', 'x' => 43, 'y' => 30],
                ['slot' => 'ST-R', 'label' => 'ST', 'group' => 'FWD', 'x' => 57, 'y' => 30],
            ],
            default => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 35, 'y' => 52],
                ['slot' => 'ZM', 'label' => 'ZM', 'group' => 'MID', 'x' => 50, 'y' => 48],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 65, 'y' => 52],
                ['slot' => 'LW', 'label' => 'LW', 'group' => 'FWD', 'x' => 22, 'y' => 30],
                ['slot' => 'ST', 'label' => 'ST', 'group' => 'FWD', 'x' => 50, 'y' => 24],
                ['slot' => 'RW', 'label' => 'RW', 'group' => 'FWD', 'x' => 78, 'y' => 30],
            ],
        };
    }

    /**
     * @param Collection<int, Player> $players
     * @return array{starters: array<string, int|null>, bench: array<int, int>}
     */
    public function strongestByFormation(Collection $players, string $formation): array
    {
        $slots = $this->starterSlots($formation);
        $available = $players
            ->sortByDesc(fn (Player $player) => ($player->overall * 2) + $player->stamina + $player->morale)
            ->values();

        $usedIds = [];
        $starters = [];

        foreach ($slots as $slot) {
            $pick = $available->first(function (Player $player) use ($slot, $usedIds): bool {
                if (in_array($player->id, $usedIds, true)) {
                    return false;
                }

                return $this->positionFitsGroup($player->position, $slot['group']);
            });

            if (!$pick) {
                $pick = $available->first(fn (Player $player): bool => !in_array($player->id, $usedIds, true));
            }

            if ($pick) {
                $starters[$slot['slot']] = $pick->id;
                $usedIds[] = $pick->id;
            } else {
                $starters[$slot['slot']] = null;
            }
        }

        $bench = $available
            ->whereNotIn('id', $usedIds)
            ->take(5)
            ->pluck('id')
            ->values()
            ->all();

        return [
            'starters' => $starters,
            'bench' => $bench,
        ];
    }

    private function positionFitsGroup(string $position, string $group): bool
    {
        return match ($group) {
            'GK' => $position === 'GK',
            'DEF' => in_array($position, ['DEF', 'MID'], true),
            'MID' => in_array($position, ['MID', 'DEF', 'FWD'], true),
            'FWD' => in_array($position, ['FWD', 'MID'], true),
            default => false,
        };
    }
}
