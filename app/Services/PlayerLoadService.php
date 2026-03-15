<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\MatchPlayerStat;
use App\Models\Player;
use App\Models\PlayerRecoveryLog;
use App\Models\TrainingSession;

class PlayerLoadService
{
    public function applyTrainingLoad(Player $player, TrainingSession $session, int $staminaDelta): array
    {
        $intensityLoad = match ((string) $session->intensity) {
            'high' => 28,
            'medium' => 18,
            default => 10,
        };

        $recoveryBoost = $session->type === 'recovery' ? 16 : 0;
        $fatigueBefore = (int) $player->fatigue;
        $sharpnessBefore = (int) $player->sharpness;

        $fatigueAfter = max(0, min(100, $fatigueBefore + $intensityLoad - $recoveryBoost + max(0, abs(min(0, $staminaDelta)))));
        $sharpnessAfter = max(0, min(100, $sharpnessBefore + ($session->type === 'recovery' ? 2 : 6) - ($session->intensity === 'high' ? 1 : 0)));
        $trainingLoad = max(0, min(100, (int) round((((int) $player->training_load) + $intensityLoad) / 2)));
        $injuryRisk = $this->injuryRisk($player, $fatigueAfter, $trainingLoad);

        $player->forceFill([
            'fatigue' => $fatigueAfter,
            'sharpness' => $sharpnessAfter,
            'training_load' => $trainingLoad,
            'medical_status' => $injuryRisk >= 75 ? 'risk' : ($fatigueAfter >= 70 ? 'monitoring' : 'fit'),
        ])->save();

        PlayerRecoveryLog::create([
            'player_id' => $player->id,
            'club_id' => $player->club_id,
            'day' => $session->session_date,
            'training_load' => $trainingLoad,
            'match_load' => (int) $player->match_load,
            'fatigue_before' => $fatigueBefore,
            'fatigue_after' => $fatigueAfter,
            'sharpness_before' => $sharpnessBefore,
            'sharpness_after' => $sharpnessAfter,
            'injury_risk' => $injuryRisk,
        ]);

        return [
            'fatigue' => $fatigueAfter,
            'sharpness' => $sharpnessAfter,
            'injury_risk' => $injuryRisk,
        ];
    }

    public function injuryRisk(Player $player, ?int $fatigue = null, ?int $trainingLoad = null): int
    {
        $fatigueValue = $fatigue ?? (int) $player->fatigue;
        $trainingValue = $trainingLoad ?? (int) $player->training_load;

        return max(1, min(99, (int) round(
            ($fatigueValue * 0.55)
            + ($trainingValue * 0.15)
            + ((int) $player->injury_proneness * 0.2)
            + ((int) $player->injury_matches_remaining > 0 ? 10 : 0)
        )));
    }

    public function applyMatchLoad(Player $player, MatchPlayerStat $stat, ?GameMatch $match = null): array
    {
        $fatigueBefore = (int) $player->fatigue;
        $sharpnessBefore = (int) $player->sharpness;
        $minutes = max(0, (int) $stat->minutes_played);
        $baseLoad = min(100, (int) round(($minutes / 90) * 65));
        $intensityBonus = ((int) $stat->goals * 2) + ((int) $stat->assists * 2) + ((int) $stat->red_cards * 5) + ((int) $stat->yellow_cards * 2);

        $fatigueAfter = max(0, min(100, $fatigueBefore + $baseLoad + $intensityBonus - 12));
        $sharpnessAfter = max(0, min(100, $sharpnessBefore + max(4, (int) round($minutes / 12)) - ((int) $player->injury_matches_remaining > 0 ? 10 : 0)));
        $matchLoad = max(0, min(100, (int) round((((int) $player->match_load) + $baseLoad) / 2)));
        $injuryRisk = $this->injuryRisk($player, $fatigueAfter, (int) $player->training_load);

        $player->forceFill([
            'fatigue' => $fatigueAfter,
            'sharpness' => $sharpnessAfter,
            'match_load' => $matchLoad,
            'medical_status' => $injuryRisk >= 75 ? 'risk' : ($fatigueAfter >= 70 ? 'monitoring' : $player->medical_status),
        ])->save();

        PlayerRecoveryLog::create([
            'player_id' => $player->id,
            'club_id' => $player->club_id,
            'day' => $match?->played_at?->toDateString() ?? now()->toDateString(),
            'training_load' => (int) $player->training_load,
            'match_load' => $matchLoad,
            'fatigue_before' => $fatigueBefore,
            'fatigue_after' => $fatigueAfter,
            'sharpness_before' => $sharpnessBefore,
            'sharpness_after' => $sharpnessAfter,
            'injury_risk' => $injuryRisk,
        ]);

        return [
            'fatigue' => $fatigueAfter,
            'sharpness' => $sharpnessAfter,
            'match_load' => $matchLoad,
            'injury_risk' => $injuryRisk,
        ];
    }
}
