<?php

namespace App\Services;

use App\Models\Player;
use App\Models\PlayerPlaytimePromise;

class PlayerMoraleService
{
    public function refresh(Player $player): array
    {
        $promise = $player->playtimePromises()
            ->where('status', 'active')
            ->latest('id')
            ->first();

        $promisePressure = $promise ? max(0, (int) $promise->expected_minutes_share - (int) $promise->fulfilled_ratio) : 0;
        $injuryPenalty = $player->currentInjury && $player->currentInjury->status === 'active' ? 12 : 0;
        $fatiguePenalty = max(0, (int) $player->fatigue - 55);
        $sharpnessBonus = max(0, (int) $player->sharpness - 60);
        $leadershipBonus = match ($player->leadership_level) {
            'captain_group' => 8,
            'senior_core' => 4,
            default => 0,
        };

        $base = (int) $player->morale;
        $happiness = max(1, min(100, $base + $sharpnessBonus + $leadershipBonus - $fatiguePenalty - $injuryPenalty - (int) round($promisePressure / 2)));
        $trend = max(-10, min(10, (int) round(($happiness - (int) $player->happiness) / 4)));
        $reason = $this->resolveReason($player, $promise, $promisePressure, $fatiguePenalty, $injuryPenalty);

        $player->forceFill([
            'happiness' => $happiness,
            'happiness_trend' => $trend,
            'last_morale_reason' => $reason,
        ])->save();

        if ($promise) {
            $promise->forceFill([
                'status' => $promisePressure > 15 ? 'at_risk' : 'active',
            ])->save();
        }

        return [
            'happiness' => $happiness,
            'trend' => $trend,
            'reason' => $reason,
            'promise_pressure' => $promisePressure,
        ];
    }

    private function resolveReason(Player $player, ?PlayerPlaytimePromise $promise, int $promisePressure, int $fatiguePenalty, int $injuryPenalty): string
    {
        if ($injuryPenalty > 0) {
            return 'Reha und Ausfall bremsen die Stimmung.';
        }

        if ($promise && $promisePressure > 15) {
            return 'Spielzeitversprechen ist gefaehrdet.';
        }

        if ($fatiguePenalty > 15) {
            return 'Hohe Belastung drueckt auf die Zufriedenheit.';
        }

        if ((int) $player->sharpness >= 75) {
            return 'Gute Rhythmus- und Trainingsphase.';
        }

        return 'Stabile Kabinenlage.';
    }
}
