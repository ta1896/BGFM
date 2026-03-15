<?php

namespace App\Services;

use App\Models\Player;
use App\Models\PlayerInjury;
use Illuminate\Support\Carbon;

class InjuryManagementService
{
    public function syncCurrentInjury(Player $player): ?PlayerInjury
    {
        $injury = $player->injuries()
            ->where('status', 'active')
            ->latest('started_at')
            ->first();

        if (!$injury && (int) $player->injury_matches_remaining > 0) {
            $injury = PlayerInjury::create([
                'player_id' => $player->id,
                'club_id' => $player->club_id,
                'injury_type' => 'Muskelverletzung',
                'body_area' => 'Bein',
                'severity' => (int) $player->injury_matches_remaining >= 3 ? 'major' : 'minor',
                'started_at' => now(),
                'expected_return_at' => now()->addDays(max(3, (int) $player->injury_matches_remaining * 6)),
                'status' => 'active',
                'source' => 'match',
            ]);
        }

        if ($injury && (int) $player->injury_matches_remaining === 0) {
            $injury->forceFill([
                'status' => 'recovered',
                'actual_return_at' => now(),
            ])->save();
            $injury = null;
        }

        $player->forceFill([
            'medical_status' => $injury
                ? match ($injury->availability_status) {
                    'available' => 'fit',
                    'limited', 'bench_only' => 'monitoring',
                    default => 'rehab',
                }
                : ($player->medical_status === 'rehab' ? 'fit' : $player->medical_status),
        ])->save();

        return $injury;
    }

    public function rehabProgress(Player $player): array
    {
        $injury = $this->syncCurrentInjury($player);

        if (!$injury) {
            return [
                'status' => 'fit',
                'label' => 'Spielfit',
                'expected_return' => null,
            ];
        }

        return [
            'status' => 'injured',
            'label' => $injury->injury_type,
            'expected_return' => $injury->expected_return_at?->format('d.m.Y'),
        ];
    }

    public function updateRehabPlan(Player $player, array $data): void
    {
        $injury = $player->injuries()
            ->where('status', 'active')
            ->latest('started_at')
            ->first();

        if ($injury) {
            $setbackRisk = match ($data['rehab_intensity']) {
                'high' => 52,
                'medium' => 28,
                default => 14,
            };

            $injury->forceFill([
                'rehab_intensity' => $data['rehab_intensity'],
                'return_phase' => $data['return_phase'],
                'availability_status' => $this->defaultAvailabilityForPhase($data['return_phase']),
                'setback_risk' => $setbackRisk,
                'notes' => $data['notes'] ?? null,
            ])->save();

            $player->forceFill([
                'medical_status' => in_array($data['return_phase'], ['partial', 'full'], true) ? 'monitoring' : 'rehab',
                'fatigue' => max(0, (int) $player->fatigue - ($data['rehab_intensity'] === 'low' ? 10 : 4)),
                'sharpness' => min(100, (int) $player->sharpness + ($data['return_phase'] === 'full' ? 8 : 4)),
            ])->save();
        }
    }

    public function updateClearance(Player $player, array $data): void
    {
        $injury = $player->injuries()
            ->where('status', 'active')
            ->latest('started_at')
            ->first();

        if (!$injury) {
            return;
        }

        $availability = $data['availability_status'];
        $setbackRisk = match ($availability) {
            'available' => max(4, (int) $injury->setback_risk - 18),
            'limited' => max(8, (int) $injury->setback_risk - 10),
            'bench_only' => max(12, (int) $injury->setback_risk - 4),
            default => min(90, max(18, (int) $injury->setback_risk + 6)),
        };

        $injury->forceFill([
            'availability_status' => $availability,
            'return_phase' => $data['return_phase'] ?? $injury->return_phase,
            'cleared_at' => $availability === 'available' ? now() : null,
            'setback_risk' => $setbackRisk,
            'notes' => trim(implode("\n", array_filter([
                $injury->notes,
                $data['notes'] ?? null,
            ]))),
        ])->save();

        $player->forceFill([
            'medical_status' => match ($availability) {
                'available' => 'fit',
                'limited', 'bench_only' => 'monitoring',
                default => 'rehab',
            },
            'fatigue' => match ($availability) {
                'available' => max(0, (int) $player->fatigue - 6),
                'limited' => max(0, (int) $player->fatigue - 2),
                'bench_only' => max(0, (int) $player->fatigue - 1),
                default => (int) $player->fatigue,
            },
            'sharpness' => match ($availability) {
                'available' => min(100, (int) $player->sharpness + 6),
                'limited' => min(100, (int) $player->sharpness + 3),
                default => (int) $player->sharpness,
            },
        ])->save();
    }

    private function defaultAvailabilityForPhase(string $phase): string
    {
        return match ($phase) {
            'full' => 'limited',
            'partial' => 'bench_only',
            default => 'unavailable',
        };
    }
}
