<?php

namespace App\Services;

use App\Models\Player;
use App\Models\ScoutingReport;
use App\Models\ScoutingWatchlist;

class ScoutingService
{
    public function generateReport(Player $player, int $clubId, ?int $watchlistId, ?int $userId = null): ScoutingReport
    {
        $watchlist = $watchlistId
            ? ScoutingWatchlist::query()->find($watchlistId)
            : null;

        $confidenceFloor = $watchlist ? max(45, min(88, 38 + (int) round($watchlist->progress * 0.45))) : 42;
        $confidenceCeiling = $watchlist ? max($confidenceFloor, min(96, $confidenceFloor + 14)) : 86;
        $confidence = random_int($confidenceFloor, $confidenceCeiling);
        $spread = max(3, (int) round((100 - $confidence) / 8));

        $report = ScoutingReport::query()->create([
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

        if ($watchlist) {
            $watchlist->forceFill([
                'progress' => min(100, max((int) $watchlist->progress, $confidence)),
                'reports_requested' => (int) $watchlist->reports_requested + 1,
                'last_scouted_at' => now(),
                'next_report_due_at' => now()->addDays($watchlist->priority === 'high' ? 2 : 4),
            ])->save();
        }

        return $report;
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
                'focus' => $data['focus'] ?? 'general',
                'progress' => $data['progress'] ?? 0,
                'next_report_due_at' => $data['next_report_due_at'] ?? now()->addDays(3),
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    public function advanceWatchlist(ScoutingWatchlist $watchlist, ?int $userId = null): ?ScoutingReport
    {
        $gain = match ($watchlist->priority) {
            'high' => random_int(24, 34),
            'low' => random_int(10, 18),
            default => random_int(16, 26),
        };

        if ($watchlist->focus === 'medical') {
            $gain += 4;
        }

        if ($watchlist->focus === 'personality') {
            $gain += 2;
        }

        $watchlist->forceFill([
            'progress' => min(100, (int) $watchlist->progress + $gain),
            'last_scouted_at' => now(),
            'next_report_due_at' => now()->addDays($watchlist->priority === 'high' ? 2 : 4),
        ])->save();

        if ((int) $watchlist->progress < 55) {
            return null;
        }

        return $this->generateReport($watchlist->player, $watchlist->club_id, $watchlist->id, $userId);
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
