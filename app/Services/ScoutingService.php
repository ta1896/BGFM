<?php

namespace App\Services;

use App\Models\Club;
use App\Models\GameNotification;
use App\Models\Player;
use App\Models\ScoutingReport;
use App\Models\ScoutingWatchlist;

class ScoutingService
{
    public function __construct(
        private readonly ClubFinanceLedgerService $financeLedger,
    ) {
    }

    public function generateReport(Player $player, int $clubId, ?int $watchlistId, ?int $userId = null): ScoutingReport
    {
        $watchlist = $watchlistId
            ? ScoutingWatchlist::query()->with(['club', 'player.club'])->find($watchlistId)
            : null;

        $expressCost = $watchlist ? $this->expressReportCost($watchlist) : 7500;
        $club = $watchlist?->club ?? Club::query()->findOrFail($clubId);

        $this->financeLedger->applyBudgetChange($club, -$expressCost, [
            'user_id' => $userId,
            'context_type' => 'transfer',
            'reference_type' => 'scouting_report',
            'reference_id' => $watchlist?->id,
            'note' => sprintf('Express-Scoutreport fuer %s', $player->full_name),
        ]);

        $confidenceRange = $this->confidenceRange($watchlist, $player);
        $confidenceFloor = $confidenceRange['floor'];
        $confidenceCeiling = $confidenceRange['ceiling'];
        $confidence = random_int($confidenceFloor, $confidenceCeiling);
        $spread = $this->spreadFromConfidence($confidence, $watchlist);

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
                'mission_days_left' => 0,
                'last_mission_cost' => $expressCost,
                'last_scouted_at' => now(),
                'next_report_due_at' => now()->addDays($watchlist->priority === 'high' ? 2 : 4),
            ])->save();

            if ($userId) {
                GameNotification::query()->create([
                    'user_id' => $userId,
                    'club_id' => $clubId,
                    'type' => 'scouting_update',
                    'title' => 'Neuer Scout-Report',
                    'message' => sprintf('%s wurde mit %d%% Sicherheit neu bewertet. OVR %d-%d.', $player->full_name, $confidence, $report->overall_min, $report->overall_max),
                    'action_url' => route('scouting.index'),
                ]);
            }
        }

        return $report;
    }

    public function upsertWatchlist(Player $player, int $clubId, ?int $userId, array $data): ScoutingWatchlist
    {
        $mission = $this->previewMission(
            Club::query()->find($clubId),
            $player,
            $data['priority'] ?? 'medium',
            $data['focus'] ?? 'general',
            $data['scout_level'] ?? 'experienced',
            $data['scout_region'] ?? 'domestic',
            $data['scout_type'] ?? 'live',
        );

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
                'scout_level' => $data['scout_level'] ?? 'experienced',
                'scout_region' => $data['scout_region'] ?? 'domestic',
                'scout_type' => $data['scout_type'] ?? 'live',
                'progress' => $data['progress'] ?? 0,
                'mission_days_left' => $data['mission_days_left'] ?? $mission['days'],
                'last_mission_cost' => $data['last_mission_cost'] ?? 0,
                'next_report_due_at' => $data['next_report_due_at'] ?? now()->addDays(3),
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    public function advanceWatchlist(ScoutingWatchlist $watchlist, ?int $userId = null): ?ScoutingReport
    {
        $watchlist->loadMissing(['player.club', 'club']);
        $mission = $this->previewMission($watchlist->club, $watchlist->player, $watchlist->priority, $watchlist->focus, $watchlist->scout_level, $watchlist->scout_region, $watchlist->scout_type);

        $this->financeLedger->applyBudgetChange($watchlist->club, -$mission['cost'], [
            'user_id' => $userId,
            'context_type' => 'transfer',
            'reference_type' => 'scouting_mission',
            'reference_id' => $watchlist->id,
            'note' => sprintf('Scout-Mission (%s/%s) fuer %s', $watchlist->scout_level, $watchlist->scout_region, $watchlist->player->full_name),
        ]);

        $watchlist->forceFill([
            'progress' => min(100, (int) $watchlist->progress + $mission['gain']),
            'mission_days_left' => $mission['days'],
            'last_mission_cost' => $mission['cost'],
            'last_scouted_at' => now(),
            'next_report_due_at' => now()->addDays($mission['days']),
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

    private function expressReportCost(ScoutingWatchlist $watchlist): float
    {
        return round(max(5000, $this->previewMission($watchlist->club, $watchlist->player, $watchlist->priority, $watchlist->focus, $watchlist->scout_level, $watchlist->scout_region, $watchlist->scout_type)['cost'] * 0.6), 2);
    }

    public function previewMission(?Club $club, Player $player, string $priority, string $focus, string $level, string $region, string $type): array
    {
        return $this->missionProfile($priority, $focus, $level, $region, $type, $this->isDomesticTarget($club, $player));
    }

    private function confidenceRange(?ScoutingWatchlist $watchlist, Player $player): array
    {
        if (!$watchlist) {
            return ['floor' => 42, 'ceiling' => 86];
        }

        $baseFloor = 34 + (int) round($watchlist->progress * 0.42);
        $levelBonus = match ($watchlist->scout_level) {
            'elite' => 12,
            'junior' => -6,
            default => 4,
        };
        $typeBonus = match ($watchlist->scout_type) {
            'data' => $watchlist->focus === 'general' ? 6 : 2,
            'video' => $watchlist->focus === 'tactical' ? 5 : 1,
            default => $watchlist->focus === 'personality' ? 4 : 2,
        };
        $regionBonus = $this->isDomesticTarget($watchlist->club, $player)
            ? ($watchlist->scout_region === 'domestic' ? 4 : 0)
            : ($watchlist->scout_region === 'global' ? 4 : -3);

        $floor = max(38, min(90, $baseFloor + $levelBonus + $typeBonus + $regionBonus));
        $ceiling = max($floor, min(97, $floor + 12 + max(0, intdiv($levelBonus, 2))));

        return ['floor' => $floor, 'ceiling' => $ceiling];
    }

    private function spreadFromConfidence(int $confidence, ?ScoutingWatchlist $watchlist): int
    {
        $base = max(2, (int) round((100 - $confidence) / 7));
        $typeAdjustment = match ($watchlist?->scout_type) {
            'data' => -1,
            'video' => 0,
            'live' => 1,
            default => 0,
        };

        return max(2, $base + $typeAdjustment);
    }

    private function missionProfile(string $priority, string $focus, string $level, string $region, string $type, bool $domesticTarget): array
    {
        $baseGain = match ($priority) {
            'high' => 26,
            'low' => 14,
            default => 20,
        };
        $baseCost = match ($priority) {
            'high' => 18000,
            'low' => 7000,
            default => 12000,
        };
        $baseDays = match ($priority) {
            'high' => 2,
            'low' => 5,
            default => 3,
        };

        $levelAdjust = match ($level) {
            'elite' => ['gain' => 10, 'cost' => 12000, 'days' => -1],
            'junior' => ['gain' => -5, 'cost' => -3500, 'days' => 1],
            default => ['gain' => 3, 'cost' => 2500, 'days' => 0],
        };
        $regionAdjust = match ($region) {
            'global' => ['gain' => $domesticTarget ? -2 : 5, 'cost' => 14000, 'days' => 3],
            'continental' => ['gain' => 2, 'cost' => 7000, 'days' => 1],
            default => ['gain' => $domesticTarget ? 4 : -3, 'cost' => 1000, 'days' => 0],
        };
        $typeAdjust = match ($type) {
            'data' => ['gain' => $focus === 'general' ? 5 : 2, 'cost' => 3500, 'days' => -1],
            'video' => ['gain' => $focus === 'tactical' ? 5 : 3, 'cost' => 2500, 'days' => 0],
            default => ['gain' => $focus === 'personality' ? 5 : 4, 'cost' => 5000, 'days' => 1],
        };
        $focusAdjust = match ($focus) {
            'medical' => ['gain' => 4, 'cost' => 1500],
            'personality' => ['gain' => 3, 'cost' => 1000],
            'tactical' => ['gain' => 2, 'cost' => 500],
            default => ['gain' => 0, 'cost' => 0],
        };

        return [
            'gain' => max(8, $baseGain + $levelAdjust['gain'] + $regionAdjust['gain'] + $typeAdjust['gain'] + $focusAdjust['gain']),
            'cost' => round(max(3000, $baseCost + $levelAdjust['cost'] + $regionAdjust['cost'] + $typeAdjust['cost'] + $focusAdjust['cost']), 2),
            'days' => max(1, $baseDays + $levelAdjust['days'] + $regionAdjust['days'] + $typeAdjust['days']),
        ];
    }

    private function isDomesticTarget(?Club $club, Player $player): bool
    {
        if (!$club) {
            return false;
        }

        return filled($club->country) && filled($player->club?->country) && $club->country === $player->club?->country;
    }
}
