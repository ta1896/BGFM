<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Collection;

class FormationPlannerService
{
    public function __construct(
        private readonly PlayerPositionService $positionService,
        private readonly PositionMetadataService $positionMetadata
    )
    {
    }

    /**
     * @return array<int, string>
     */
    public function supportedFormations(): array
    {
        return array_keys($this->layouts());
    }

    public function defaultFormation(): string
    {
        $default = (string) config('formations.default', '4-4-2');
        $fallback = array_key_first($this->layouts()) ?: '4-4-2';

        return isset($this->layouts()[$default]) ? $default : $fallback;
    }

    public function normalizeFormation(?string $formation): string
    {
        $normalized = trim((string) $formation);

        return isset($this->layouts()[$normalized]) ? $normalized : $this->defaultFormation();
    }

    /**
     * @return array<int, array{slot:string,label:string,group:string,x:int,y:int}>
     */
    public function starterSlots(string $formation): array
    {
        $normalized = $this->normalizeFormation($formation);

        /** @var array<int, array{slot:string,label:string,group:string,x:int,y:int}> $slots */
        $slots = $this->layouts()[$normalized] ?? $this->layouts()[$this->defaultFormation()];

        return $slots;
    }

    /**
     * @param Collection<int, Player> $players
     * @return array{starters: array<string, int|null>, bench: array<int, int>}
     */
    public function strongestByFormation(Collection $players, string $formation, int $maxBenchPlayers = 5): array
    {
        $benchLimit = max(1, min(10, $maxBenchPlayers));
        $slots = $this->starterSlots($formation);
        $available = $players
            ->sortByDesc(fn(Player $player) => ($player->overall * 2) + $player->stamina + $player->morale)
            ->values();
        $slotOrder = collect($slots)
            ->sortBy(fn (array $slot): array => [
                $this->slotCandidateCount($available, $slot),
                $this->slotPriority($slot),
            ])
            ->values()
            ->all();

        $usedIds = [];
        $starters = [];

        foreach ($slotOrder as $slot) {
            $pick = $available
                ->reject(fn(Player $player): bool => in_array($player->id, $usedIds, true))
                ->sortByDesc(fn(Player $player): float => $this->slotScore($player, $slot))
                ->first();

            if ($pick) {
                $starters[$slot['slot']] = $pick->id;
                $usedIds[] = $pick->id;
            } else {
                $starters[$slot['slot']] = null;
            }
        }

        $bench = $available
            ->whereNotIn('id', $usedIds)
            ->take($benchLimit)
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
        $playerGroup = $this->positionService->groupFromPosition($position);
        if (!$playerGroup) {
            return false;
        }

        return in_array($playerGroup, $this->positionMetadata->compatibleGroups($group), true);
    }

    /**
     * @param array{slot:string,label:string,group:string,x:int,y:int} $slot
     */
    private function slotScore(Player $player, array $slot): float
    {
        $mainPosition = $player->position_main ?: $player->position;
        $fit = $this->positionService->fitFactorWithProfile(
            $mainPosition,
            $player->position_second,
            $player->position_third,
            $slot['slot']
        );

        if ($slot['group'] === 'GK' && $this->positionService->groupFromPosition($mainPosition) !== 'GK') {
            return -100000.0;
        }

        $base = ($player->overall * 12)
            + ($player->stamina * 0.8)
            + ($player->morale * 0.6)
            + (($player->sharpness ?? 50) * 0.4)
            - (($player->fatigue ?? 0) * 0.7);
        $role = $this->roleAttributeScore($player, $slot);

        $bonus = 0.0;
        $mainBonus = (float) config('simulation.lineup_scoring.slot_score_bonuses.main', 120.0);
        $secondBonus = (float) config('simulation.lineup_scoring.slot_score_bonuses.second', 70.0);
        $thirdBonus = (float) config('simulation.lineup_scoring.slot_score_bonuses.third', 35.0);
        foreach ([
            [$player->position_main ?: $player->position, $mainBonus],
            [$player->position_second, $secondBonus],
            [$player->position_third, $thirdBonus],
        ] as [$position, $weight]) {
            if ($this->positionMatchesSlot((string) $position, $slot)) {
                $bonus = max($bonus, $weight);
            }
        }

        if ($bonus === 0.0 && $this->positionFitsGroup((string) ($player->position_main ?: $player->position), $slot['group'])) {
            $bonus = (float) config('simulation.lineup_scoring.slot_score_bonuses.group_fallback', 20.0);
        }

        $fitWeight = (float) config('simulation.lineup_scoring.fit_weight', 260.0);
        $roleWeight = (float) config('simulation.lineup_scoring.role_weight', 3.0);
        $lowFitPenalty = $fit < 0.80 ? (float) config('simulation.lineup_scoring.low_fit_penalty', 220.0) : 0.0;

        return $base + ($role * $roleWeight) + ($fit * $fitWeight) + $bonus - $lowFitPenalty;
    }

    /**
     * @param array{slot:string,label:string,group:string,x:int,y:int} $slot
     */
    private function positionMatchesSlot(string $position, array $slot): bool
    {
        $normalizedPosition = $this->normalizePositionCode($position);
        if ($normalizedPosition === '') {
            return false;
        }

        return in_array($normalizedPosition, $this->slotAliases($slot['slot'], $slot['label']), true);
    }

    /**
     * @return array<int, string>
     */
    private function slotAliases(string $slotCode, string $slotLabel): array
    {
        return $this->positionMetadata->slotAliases($slotCode, $slotLabel);
    }

    private function normalizePositionCode(?string $value): string
    {
        return $this->positionMetadata->normalizeCode($value);
    }

    /**
     * @return array<string, array<int, array{slot:string,label:string,group:string,x:int,y:int}>>
     */
    private function layouts(): array
    {
        /** @var array<string, array<int, array{slot:string,label:string,group:string,x:int,y:int}>> $layouts */
        $layouts = config('formations.layouts', []);

        return $layouts;
    }

    /**
     * @param array{slot:string,label:string,group:string,x:int,y:int} $slot
     */
    private function slotCandidateCount(Collection $players, array $slot): int
    {
        return $players
            ->filter(function (Player $player) use ($slot): bool {
                $fit = $this->positionService->fitFactorWithProfile(
                    $player->position_main ?: $player->position,
                    $player->position_second,
                    $player->position_third,
                    $slot['slot']
                );

                return $fit >= 0.84;
            })
            ->count();
    }

    /**
     * @param array{slot:string,label:string,group:string,x:int,y:int} $slot
     */
    private function slotPriority(array $slot): int
    {
        $normalized = $this->normalizePositionCode($slot['label'] ?: $slot['slot']);

        return match ($normalized) {
            'TW' => 0,
            'LV', 'RV', 'LWB', 'RWB' => 1,
            'IV' => 2,
            'DM' => 3,
            'LM', 'RM', 'LF', 'RF', 'LS', 'RS' => 4,
            'OM', 'ZOM', 'LAM', 'RAM' => 5,
            'ZM' => 6,
            'MS', 'ST', 'HS' => 7,
            default => 8,
        };
    }

    /**
     * @param array{slot:string,label:string,group:string,x:int,y:int} $slot
     */
    private function roleAttributeScore(Player $player, array $slot): float
    {
        $normalized = $this->normalizePositionCode($slot['label'] ?: $slot['slot']);
        $weights = $this->roleWeightsForSlot($normalized, $slot['group']);
        $score = 0.0;

        foreach ($weights as $attribute => $weight) {
            $score += ((float) data_get($player, $attribute, 0)) * $weight;
        }

        return $score;
    }

    /**
     * @return array<string, float>
     */
    private function roleWeightsForSlot(string $normalizedSlot, string $group): array
    {
        return match ($normalizedSlot) {
            'TW' => [
                'overall' => 0.18,
                'attr_defending' => 0.34,
                'attr_tactical' => 0.18,
                'technical' => 0.14,
                'stamina' => 0.04,
                'morale' => 0.04,
            ],
            'LV', 'RV', 'LWB', 'RWB' => [
                'overall' => 0.14,
                'pace' => 0.10,
                'stamina' => 0.08,
                'physical' => 0.07,
                'passing' => 0.06,
                'attr_defending' => 0.22,
                'attr_tactical' => 0.15,
                'technical' => 0.10,
            ],
            'IV' => [
                'overall' => 0.16,
                'physical' => 0.10,
                'defending' => 0.08,
                'passing' => 0.04,
                'attr_defending' => 0.28,
                'attr_tactical' => 0.18,
                'technical' => 0.06,
            ],
            'DM' => [
                'overall' => 0.14,
                'stamina' => 0.08,
                'passing' => 0.07,
                'defending' => 0.06,
                'attr_defending' => 0.18,
                'attr_tactical' => 0.22,
                'technical' => 0.12,
                'attr_creativity' => 0.06,
            ],
            'LM', 'RM' => [
                'overall' => 0.14,
                'pace' => 0.10,
                'passing' => 0.08,
                'stamina' => 0.08,
                'attr_attacking' => 0.12,
                'technical' => 0.16,
                'attr_tactical' => 0.10,
                'attr_creativity' => 0.12,
            ],
            'OM', 'ZOM', 'LAM', 'RAM' => [
                'overall' => 0.14,
                'passing' => 0.08,
                'shooting' => 0.06,
                'attr_attacking' => 0.14,
                'technical' => 0.18,
                'attr_tactical' => 0.12,
                'attr_creativity' => 0.18,
            ],
            'LF', 'RF', 'LS', 'RS' => [
                'overall' => 0.14,
                'pace' => 0.12,
                'shooting' => 0.09,
                'passing' => 0.05,
                'attr_attacking' => 0.20,
                'technical' => 0.14,
                'attr_creativity' => 0.10,
                'physical' => 0.04,
            ],
            'MS', 'ST', 'HS' => [
                'overall' => 0.15,
                'shooting' => 0.12,
                'physical' => 0.08,
                'pace' => 0.06,
                'attr_attacking' => 0.24,
                'technical' => 0.12,
                'attr_tactical' => 0.06,
                'attr_creativity' => 0.04,
            ],
            default => match ($group) {
                'GK' => ['overall' => 0.20, 'attr_defending' => 0.35, 'attr_tactical' => 0.20],
                'DEF' => ['overall' => 0.18, 'attr_defending' => 0.28, 'attr_tactical' => 0.18, 'physical' => 0.08],
                'MID' => ['overall' => 0.16, 'technical' => 0.18, 'attr_tactical' => 0.18, 'attr_creativity' => 0.12, 'passing' => 0.08],
                default => ['overall' => 0.16, 'attr_attacking' => 0.22, 'technical' => 0.14, 'shooting' => 0.10, 'pace' => 0.08],
            },
        };
    }
}
