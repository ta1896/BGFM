<?php

namespace App\Services;

use App\Models\Player;
use App\Models\ScoutingReport;
use App\Models\ScoutingWatchlist;

class ScoutingService
{
    public function generateReport(Player $player, int $clubId, ?int $watchlistId, ?int $userId = null): ScoutingReport
    {
        $confidence = random_int(42, 86);
        $spread = max(3, (int) round((100 - $confidence) / 8));

        return ScoutingReport::query()->create([
            'club_id' => $clubId,
            'player_id' => $player->id,
            'watchlist_id' => $watchlistId,
            'created_by_user_id' => $userId,
            'confidence' => $confidence,
            'overall_min' => max(1, $player->overall - $spread),
            'overall_max' => min(99, $player->overall + $spread),
            'potential_min' => max(1, $player->potential - ($spread + 1)),
            'potential_max' => min(99, $player->potential + ($spread + 1)),
            'pace_min' => max(1, $player->pace - $spread),
            'pace_max' => min(99, $player->pace + $spread),
            'passing_min' => max(1, $player->passing - $spread),
            'passing_max' => min(99, $player->passing + $spread),
            'physical_min' => max(1, $player->physical - $spread),
            'physical_max' => min(99, $player->physical + $spread),
            'injury_risk_band' => $this->injuryRiskBand($player),
            'personality_band' => $this->personalityBand($player),
            'summary' => $this->summary($player, $confidence),
        ]);
    }

    public function upsertWatchlist(Player $player, int $clubId, ?int $userId, array $data): ScoutingWatchlist
    {
        return ScoutingWatchlist::query()->updateOrCreate(
            [
                'club_id' => $clubId,
                'player_id' => $player->id,
            ],
            [
                'created_by_user_id' => $userId,
                'priority' => $data['priority'] ?? 'medium',
                'status' => $data['status'] ?? 'watching',
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    private function injuryRiskBand(Player $player): string
    {
        return match (true) {
            (int) $player->injury_proneness >= 70 => 'hoch',
            (int) $player->injury_proneness >= 45 => 'mittel',
            default => 'niedrig',
        };
    }

    private function personalityBand(Player $player): string
    {
        return match (true) {
            (int) $player->happiness >= 70 => 'professionell',
            (int) $player->happiness >= 50 => 'neutral',
            default => 'sensibel',
        };
    }

    private function summary(Player $player, int $confidence): string
    {
        $trajectory = $player->potential > $player->overall + 6 ? 'deutliche Entwicklung' : 'begrenzte Reserve';
        $readiness = $player->age <= 21 ? 'Projektprofil' : ($player->overall >= 72 ? 'sofort nutzbar' : 'Aufbaukandidat');

        return sprintf('Scout-Sicherheit %d%%. %s mit %s und %s.', $confidence, $player->full_name, $trajectory, $readiness);
    }
}
