<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Collection;

class FormationPlannerService
{
    public function __construct(private readonly PlayerPositionService $positionService)
    {
    }

    /**
     * @return array<int, string>
     */
    public function supportedFormations(): array
    {
        return [
            '4-4-2',
            '4-4-2 (Holding)',
            '4-3-3',
            '4-3-3 (Attack)',
            '4-3-3 (Defend)',
            '4-3-3 (Holding)',
            '4-3-3 (False 9)',
            '4-2-3-1 (Wide)',
            '4-2-3-1 (Narrow)',
            '4-1-2-1-2 (Wide)',
            '4-1-2-1-2 (Narrow)',
            '4-3-2-1',
            '4-3-1-2',
            '4-2-2-2',
            '4-4-1-1 (Midfield)',
            '4-4-1-1 (Attack)',
            '4-5-1 (Flat)',
            '4-5-1 (Attack)',
            '4-1-4-1',
            '3-4-1-2',
            '3-4-2-1',
            '3-4-3 (Flat)',
            '3-4-3 (Diamond)',
            '3-1-4-2',
            '3-5-2',
            '3-5-1-1',
            '5-2-1-2',
            '5-2-2-1',
            '5-3-2',
            '5-4-1 (Flat)'
        ];
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
                ['slot' => 'ST-L', 'label' => 'MS', 'group' => 'FWD', 'x' => 43, 'y' => 32],
                ['slot' => 'ST-R', 'label' => 'MS', 'group' => 'FWD', 'x' => 57, 'y' => 32],
            ],
            '4-4-2 (Holding)' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'DM-L', 'label' => 'DM', 'group' => 'MID', 'x' => 40, 'y' => 58],
                ['slot' => 'DM-R', 'label' => 'DM', 'group' => 'MID', 'x' => 60, 'y' => 58],
                ['slot' => 'LM', 'label' => 'LM', 'group' => 'MID', 'x' => 14, 'y' => 45],
                ['slot' => 'RM', 'label' => 'RM', 'group' => 'MID', 'x' => 86, 'y' => 45],
                ['slot' => 'ST-L', 'label' => 'MS', 'group' => 'FWD', 'x' => 43, 'y' => 28],
                ['slot' => 'ST-R', 'label' => 'MS', 'group' => 'FWD', 'x' => 57, 'y' => 28],
            ],
            '4-3-3' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 32, 'y' => 54],
                ['slot' => 'ZM', 'label' => 'ZM', 'group' => 'MID', 'x' => 50, 'y' => 54],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 68, 'y' => 54],
                ['slot' => 'LW', 'label' => 'LF', 'group' => 'FWD', 'x' => 22, 'y' => 34],
                ['slot' => 'ST', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 28],
                ['slot' => 'RW', 'label' => 'RF', 'group' => 'FWD', 'x' => 78, 'y' => 34],
            ],
            '4-3-3 (Attack)' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 38, 'y' => 58],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 62, 'y' => 58],
                ['slot' => 'ZOM', 'label' => 'ZOM', 'group' => 'MID', 'x' => 50, 'y' => 44],
                ['slot' => 'LW', 'label' => 'LF', 'group' => 'FWD', 'x' => 22, 'y' => 34],
                ['slot' => 'ST', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 28],
                ['slot' => 'RW', 'label' => 'RF', 'group' => 'FWD', 'x' => 78, 'y' => 34],
            ],
            '4-3-3 (Defend)' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'DM-L', 'label' => 'DM', 'group' => 'MID', 'x' => 35, 'y' => 60],
                ['slot' => 'DM-R', 'label' => 'DM', 'group' => 'MID', 'x' => 65, 'y' => 60],
                ['slot' => 'ZM', 'label' => 'ZM', 'group' => 'MID', 'x' => 50, 'y' => 50],
                ['slot' => 'LW', 'label' => 'LF', 'group' => 'FWD', 'x' => 22, 'y' => 34],
                ['slot' => 'ST', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 28],
                ['slot' => 'RW', 'label' => 'RF', 'group' => 'FWD', 'x' => 78, 'y' => 34],
            ],
            '4-3-3 (Holding)' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'DM', 'label' => 'DM', 'group' => 'MID', 'x' => 50, 'y' => 62],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 35, 'y' => 50],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 65, 'y' => 50],
                ['slot' => 'LW', 'label' => 'LF', 'group' => 'FWD', 'x' => 22, 'y' => 34],
                ['slot' => 'ST', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 28],
                ['slot' => 'RW', 'label' => 'RF', 'group' => 'FWD', 'x' => 78, 'y' => 34],
            ],
            '4-3-3 (False 9)' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'DM', 'label' => 'DM', 'group' => 'MID', 'x' => 50, 'y' => 62],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 35, 'y' => 50],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 65, 'y' => 50],
                ['slot' => 'LW', 'label' => 'LF', 'group' => 'FWD', 'x' => 22, 'y' => 34],
                ['slot' => 'MS', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 36],
                ['slot' => 'RW', 'label' => 'RF', 'group' => 'FWD', 'x' => 78, 'y' => 34],
            ],
            '4-2-3-1 (Wide)' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'DM-L', 'label' => 'DM', 'group' => 'MID', 'x' => 40, 'y' => 60],
                ['slot' => 'DM-R', 'label' => 'DM', 'group' => 'MID', 'x' => 60, 'y' => 60],
                ['slot' => 'LM', 'label' => 'LM', 'group' => 'MID', 'x' => 14, 'y' => 45],
                ['slot' => 'ZOM', 'label' => 'ZOM', 'group' => 'MID', 'x' => 50, 'y' => 42],
                ['slot' => 'RM', 'label' => 'RM', 'group' => 'MID', 'x' => 86, 'y' => 45],
                ['slot' => 'ST', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 28],
            ],
            '4-2-3-1 (Narrow)' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'DM-L', 'label' => 'DM', 'group' => 'MID', 'x' => 40, 'y' => 60],
                ['slot' => 'DM-R', 'label' => 'DM', 'group' => 'MID', 'x' => 60, 'y' => 60],
                ['slot' => 'LAM', 'label' => 'LAM', 'group' => 'MID', 'x' => 35, 'y' => 44],
                ['slot' => 'ZOM', 'label' => 'ZOM', 'group' => 'MID', 'x' => 50, 'y' => 42],
                ['slot' => 'RAM', 'label' => 'RAM', 'group' => 'MID', 'x' => 65, 'y' => 44],
                ['slot' => 'ST', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 28],
            ],
            '4-1-2-1-2 (Wide)' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'DM', 'label' => 'DM', 'group' => 'MID', 'x' => 50, 'y' => 62],
                ['slot' => 'LM', 'label' => 'LM', 'group' => 'MID', 'x' => 14, 'y' => 48],
                ['slot' => 'RM', 'label' => 'RM', 'group' => 'MID', 'x' => 86, 'y' => 48],
                ['slot' => 'ZOM', 'label' => 'ZOM', 'group' => 'MID', 'x' => 50, 'y' => 42],
                ['slot' => 'ST-L', 'label' => 'MS', 'group' => 'FWD', 'x' => 43, 'y' => 28],
                ['slot' => 'ST-R', 'label' => 'MS', 'group' => 'FWD', 'x' => 57, 'y' => 28],
            ],
            '4-1-2-1-2 (Narrow)' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'DM', 'label' => 'DM', 'group' => 'MID', 'x' => 50, 'y' => 62],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 38, 'y' => 52],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 62, 'y' => 52],
                ['slot' => 'ZOM', 'label' => 'ZOM', 'group' => 'MID', 'x' => 50, 'y' => 42],
                ['slot' => 'ST-L', 'label' => 'MS', 'group' => 'FWD', 'x' => 43, 'y' => 28],
                ['slot' => 'ST-R', 'label' => 'MS', 'group' => 'FWD', 'x' => 57, 'y' => 28],
            ],
            '4-2-2-2' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'DM-L', 'label' => 'DM', 'group' => 'MID', 'x' => 40, 'y' => 60],
                ['slot' => 'DM-R', 'label' => 'DM', 'group' => 'MID', 'x' => 60, 'y' => 60],
                ['slot' => 'ZOM-L', 'label' => 'ZOM', 'group' => 'MID', 'x' => 35, 'y' => 44],
                ['slot' => 'ZOM-R', 'label' => 'ZOM', 'group' => 'MID', 'x' => 65, 'y' => 44],
                ['slot' => 'ST-L', 'label' => 'MS', 'group' => 'FWD', 'x' => 43, 'y' => 28],
                ['slot' => 'ST-R', 'label' => 'MS', 'group' => 'FWD', 'x' => 57, 'y' => 28],
            ],
            '4-3-2-1' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 32, 'y' => 54],
                ['slot' => 'ZM', 'label' => 'ZM', 'group' => 'MID', 'x' => 50, 'y' => 54],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 68, 'y' => 54],
                ['slot' => 'LS', 'label' => 'LS', 'group' => 'FWD', 'x' => 35, 'y' => 38],
                ['slot' => 'ST', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 28],
                ['slot' => 'RS', 'label' => 'RS', 'group' => 'FWD', 'x' => 65, 'y' => 38],
            ],
            '4-3-1-2' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 32, 'y' => 54],
                ['slot' => 'ZM', 'label' => 'ZM', 'group' => 'MID', 'x' => 50, 'y' => 54],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 68, 'y' => 54],
                ['slot' => 'ZOM', 'label' => 'ZOM', 'group' => 'MID', 'x' => 50, 'y' => 42],
                ['slot' => 'ST-L', 'label' => 'MS', 'group' => 'FWD', 'x' => 43, 'y' => 28],
                ['slot' => 'ST-R', 'label' => 'MS', 'group' => 'FWD', 'x' => 57, 'y' => 28],
            ],
            '4-4-1-1 (Midfield)' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'LM', 'label' => 'LM', 'group' => 'MID', 'x' => 14, 'y' => 52],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 38, 'y' => 52],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 62, 'y' => 52],
                ['slot' => 'RM', 'label' => 'RM', 'group' => 'MID', 'x' => 86, 'y' => 52],
                ['slot' => 'MS', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 38],
                ['slot' => 'ST', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 24],
            ],
            '4-1-4-1' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LV', 'label' => 'LV', 'group' => 'DEF', 'x' => 15, 'y' => 72],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 38, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 62, 'y' => 72],
                ['slot' => 'RV', 'label' => 'RV', 'group' => 'DEF', 'x' => 85, 'y' => 72],
                ['slot' => 'DM', 'label' => 'DM', 'group' => 'MID', 'x' => 50, 'y' => 62],
                ['slot' => 'LM', 'label' => 'LM', 'group' => 'MID', 'x' => 14, 'y' => 48],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 38, 'y' => 48],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 62, 'y' => 48],
                ['slot' => 'RM', 'label' => 'RM', 'group' => 'MID', 'x' => 86, 'y' => 48],
                ['slot' => 'ST', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 28],
            ],
            '3-5-2' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 30, 'y' => 74],
                ['slot' => 'IV', 'label' => 'IV', 'group' => 'DEF', 'x' => 50, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 70, 'y' => 74],
                ['slot' => 'LM', 'label' => 'LM', 'group' => 'MID', 'x' => 12, 'y' => 52],
                ['slot' => 'DM-L', 'label' => 'DM', 'group' => 'MID', 'x' => 35, 'y' => 58],
                ['slot' => 'ZOM', 'label' => 'ZOM', 'group' => 'MID', 'x' => 50, 'y' => 42],
                ['slot' => 'DM-R', 'label' => 'DM', 'group' => 'MID', 'x' => 65, 'y' => 58],
                ['slot' => 'RM', 'label' => 'RM', 'group' => 'MID', 'x' => 88, 'y' => 52],
                ['slot' => 'ST-L', 'label' => 'MS', 'group' => 'FWD', 'x' => 43, 'y' => 28],
                ['slot' => 'ST-R', 'label' => 'MS', 'group' => 'FWD', 'x' => 57, 'y' => 28],
            ],
            '3-4-3 (Flat)' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 30, 'y' => 74],
                ['slot' => 'IV', 'label' => 'IV', 'group' => 'DEF', 'x' => 50, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 70, 'y' => 74],
                ['slot' => 'LM', 'label' => 'LM', 'group' => 'MID', 'x' => 14, 'y' => 52],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 38, 'y' => 52],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 62, 'y' => 52],
                ['slot' => 'RM', 'label' => 'RM', 'group' => 'MID', 'x' => 86, 'y' => 52],
                ['slot' => 'LW', 'label' => 'LF', 'group' => 'FWD', 'x' => 22, 'y' => 32],
                ['slot' => 'ST', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 28],
                ['slot' => 'RW', 'label' => 'RF', 'group' => 'FWD', 'x' => 78, 'y' => 32],
            ],
            '5-2-1-2' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LWB', 'label' => 'LWB', 'group' => 'DEF', 'x' => 10, 'y' => 68],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 30, 'y' => 74],
                ['slot' => 'IV', 'label' => 'IV', 'group' => 'DEF', 'x' => 50, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 70, 'y' => 74],
                ['slot' => 'RWB', 'label' => 'RWB', 'group' => 'DEF', 'x' => 90, 'y' => 68],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 38, 'y' => 54],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 62, 'y' => 54],
                ['slot' => 'ZOM', 'label' => 'ZOM', 'group' => 'MID', 'x' => 50, 'y' => 44],
                ['slot' => 'ST-L', 'label' => 'MS', 'group' => 'FWD', 'x' => 43, 'y' => 28],
                ['slot' => 'ST-R', 'label' => 'MS', 'group' => 'FWD', 'x' => 57, 'y' => 28],
            ],
            '5-3-2' => [
                ['slot' => 'TW', 'label' => 'TW', 'group' => 'GK', 'x' => 50, 'y' => 88],
                ['slot' => 'LWB', 'label' => 'LWB', 'group' => 'DEF', 'x' => 10, 'y' => 68],
                ['slot' => 'IV-L', 'label' => 'IV', 'group' => 'DEF', 'x' => 30, 'y' => 74],
                ['slot' => 'IV', 'label' => 'IV', 'group' => 'DEF', 'x' => 50, 'y' => 72],
                ['slot' => 'IV-R', 'label' => 'IV', 'group' => 'DEF', 'x' => 70, 'y' => 74],
                ['slot' => 'RWB', 'label' => 'RWB', 'group' => 'DEF', 'x' => 90, 'y' => 68],
                ['slot' => 'ZM-L', 'label' => 'ZM', 'group' => 'MID', 'x' => 32, 'y' => 54],
                ['slot' => 'ZM', 'label' => 'ZM', 'group' => 'MID', 'x' => 50, 'y' => 54],
                ['slot' => 'ZM-R', 'label' => 'ZM', 'group' => 'MID', 'x' => 68, 'y' => 54],
                ['slot' => 'ST-L', 'label' => 'MS', 'group' => 'FWD', 'x' => 43, 'y' => 30],
                ['slot' => 'ST-R', 'label' => 'MS', 'group' => 'FWD', 'x' => 57, 'y' => 30],
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
                ['slot' => 'LW', 'label' => 'LF', 'group' => 'FWD', 'x' => 22, 'y' => 30],
                ['slot' => 'ST', 'label' => 'MS', 'group' => 'FWD', 'x' => 50, 'y' => 24],
                ['slot' => 'RW', 'label' => 'RF', 'group' => 'FWD', 'x' => 78, 'y' => 30],
            ],
        };
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
                $pick = $available->first(fn(Player $player): bool => !in_array($player->id, $usedIds, true));
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

        return match ($group) {
            'GK' => $playerGroup === 'GK',
            'DEF' => in_array($playerGroup, ['DEF', 'MID'], true),
            'MID' => in_array($playerGroup, ['MID', 'DEF', 'FWD'], true),
            'FWD' => in_array($playerGroup, ['FWD', 'MID'], true),
            default => false,
        };
    }
}
