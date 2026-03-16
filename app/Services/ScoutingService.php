<?php

namespace App\Services;

use App\Models\Club;
use App\Models\GameNotification;
use App\Models\Player;
use App\Models\ScoutingDiscovery;
use App\Models\ScoutingReport;
use App\Models\ScoutingScout;
use App\Models\ScoutingWatchlist;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ScoutingService
{
    public function __construct(
        private readonly ClubFinanceLedgerService $financeLedger,
    ) {
    }

    public function ensureScoutPool(Club $club, ?int $userId = null): Collection
    {
        $existing = ScoutingScout::query()
            ->where('club_id', $club->id)
            ->orderBy('id')
            ->get();

        $targetSlots = max(1, min(6, (int) config('simulation.modules.scouting_center.scout_slots', 3)));

        if ($existing->count() >= $targetSlots) {
            return $existing;
        }

        $templates = [
            ['name' => 'Chief Scout', 'level' => 'elite', 'specialty' => 'general', 'region' => 'global'],
            ['name' => 'Tactical Scout', 'level' => 'experienced', 'specialty' => 'tactical', 'region' => 'continental'],
            ['name' => 'Medical Scout', 'level' => 'experienced', 'specialty' => 'medical', 'region' => 'domestic'],
            ['name' => 'Data Scout', 'level' => 'experienced', 'specialty' => 'general', 'region' => 'global'],
            ['name' => 'Youth Scout', 'level' => 'junior', 'specialty' => 'personality', 'region' => 'domestic'],
            ['name' => 'Regional Scout', 'level' => 'junior', 'specialty' => 'general', 'region' => 'continental'],
        ];

        for ($index = $existing->count(); $index < $targetSlots; $index++) {
            $template = $templates[$index] ?? [
                'name' => 'Scout '.($index + 1),
                'level' => 'experienced',
                'specialty' => 'general',
                'region' => 'domestic',
            ];

            ScoutingScout::query()->create([
                'club_id' => $club->id,
                'created_by_user_id' => $userId,
                'name' => $template['name'],
                'level' => $template['level'],
                'specialty' => $template['specialty'],
                'region' => $template['region'],
                'status' => 'available',
                'workload' => 0,
            ]);
        }

        return ScoutingScout::query()
            ->where('club_id', $club->id)
            ->orderBy('id')
            ->get();
    }

    public function availableScouts(Club $club, ?int $userId = null): Collection
    {
        return $this->ensureScoutPool($club, $userId)
            ->map(function (ScoutingScout $scout): ScoutingScout {
                if ($scout->available_at && $scout->available_at->isPast() && $scout->status !== 'available') {
                    $scout->forceFill([
                        'status' => 'available',
                        'active_watchlist_id' => null,
                    ])->save();
                }

                return $scout->refresh();
            })
            ->sortBy(fn (ScoutingScout $scout) => sprintf('%s-%03d-%06d', $scout->status, (int) $scout->workload, (int) $scout->id))
            ->values();
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
        $club = Club::query()->findOrFail($clubId);
        $this->ensureScoutPool($club, $userId);

        $mission = $this->previewMission(
            $club,
            $player,
            $data['priority'] ?? 'medium',
            $data['focus'] ?? 'general',
            $data['scout_level'] ?? 'experienced',
            $data['scout_region'] ?? 'domestic',
            $data['scout_type'] ?? 'live',
        );

        $watchlist = ScoutingWatchlist::query()->updateOrCreate(
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

        if (array_key_exists('scout_id', $data)) {
            $this->assignScoutToWatchlist($watchlist->fresh(['player', 'club']), $data['scout_id']);
        }

        return $watchlist->fresh();
    }

    public function advanceWatchlist(ScoutingWatchlist $watchlist, ?int $userId = null): ?ScoutingReport
    {
        $watchlist->loadMissing(['player.club', 'club', 'scout']);
        $assignedScout = $this->assignScoutToWatchlist($watchlist, $watchlist->scout_id);
        $mission = $this->previewMission($watchlist->club, $watchlist->player, $watchlist->priority, $watchlist->focus, $watchlist->scout_level, $watchlist->scout_region, $watchlist->scout_type, $assignedScout);

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

        $assignedScout->forceFill([
            'status' => 'traveling',
            'active_watchlist_id' => $watchlist->id,
            'workload' => min(100, max((int) $assignedScout->workload, 15) + 12),
            'available_at' => now()->addDays($mission['days']),
        ])->save();

        if ((int) $watchlist->progress < 55) {
            return null;
        }

        return $this->generateReport($watchlist->player, $watchlist->club_id, $watchlist->id, $userId);
    }

    public function discoverTargets(Club $club, array $filters, ?int $userId = null): array
    {
        $market = (string) ($filters['market'] ?? 'domestic');
        $position = (string) ($filters['position'] ?? 'all');
        $ageBand = (string) ($filters['age_band'] ?? 'all');
        $valueBand = (string) ($filters['value_band'] ?? 'all');
        $discoveryLevel = (string) ($filters['discovery_level'] ?? 'experienced');
        $search = trim((string) ($filters['search'] ?? ''));

        $query = Player::query()
            ->with('club')
            ->where('club_id', '!=', $club->id)
            ->when($search !== '', function ($builder) use ($search): void {
                $builder->where(function ($inner) use ($search): void {
                    $inner
                        ->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%');
                });
            });

        $this->applyMarketScope($query, $club, $market);
        $this->applyPositionScope($query, $position);
        $this->applyAgeScope($query, $ageBand);
        $this->applyValueScope($query, $valueBand);

        $pool = $query
            ->orderByDesc('potential')
            ->orderByDesc('overall')
            ->limit($this->discoveryPoolLimit($discoveryLevel))
            ->get();

        $leads = $pool
            ->map(function (Player $player) use ($club, $position, $discoveryLevel): array {
                $fitScore = $this->fitScore($player, $club, $position);
                $fogPenalty = match ($discoveryLevel) {
                    'junior' => random_int(7, 18),
                    'elite' => random_int(0, 6),
                    default => random_int(3, 10),
                };

                return [
                    'player' => $player,
                    'fit_score' => max(35, min(99, $fitScore - $fogPenalty + random_int(0, 8))),
                    'market_band' => $this->marketValueBand((float) $player->market_value),
                    'region_tag' => $this->regionTag($club, $player),
                    'discovery_note' => $this->discoveryNote($player, $discoveryLevel),
                ];
            })
            ->sortByDesc('fit_score')
            ->take($this->discoveryLeadCount($discoveryLevel))
            ->values();

        $cost = $this->discoveryScanCost($market, $discoveryLevel);

        $this->financeLedger->applyBudgetChange($club, -$cost, [
            'user_id' => $userId,
            'context_type' => 'transfer',
            'reference_type' => 'scouting_discovery_scan',
            'reference_id' => null,
            'note' => sprintf('Scout-Zielmarkt Scan (%s/%s)', $discoveryLevel, $market),
        ]);

        $discoveryIds = [];
        foreach ($leads as $lead) {
            $discovery = ScoutingDiscovery::query()->updateOrCreate(
                [
                    'club_id' => $club->id,
                    'player_id' => $lead['player']->id,
                ],
                [
                    'created_by_user_id' => $userId,
                    'market' => $market,
                    'position_group' => $position,
                    'age_band' => $ageBand,
                    'value_band' => $valueBand,
                    'discovery_level' => $discoveryLevel,
                    'fit_score' => $lead['fit_score'],
                    'market_band' => $lead['market_band'],
                    'region_tag' => $lead['region_tag'],
                    'discovery_note' => $lead['discovery_note'],
                    'scanned_at' => now(),
                ]
            );

            $discoveryIds[] = $discovery->id;
        }

        if ($userId && count($discoveryIds) > 0) {
            GameNotification::query()->create([
                'user_id' => $userId,
                'club_id' => $club->id,
                'type' => 'scouting_update',
                'title' => 'Neue Scout-Leads',
                'message' => sprintf('%d neue Kandidaten im %s-Markt identifiziert.', count($discoveryIds), $market),
                'action_url' => route('scouting.index'),
            ]);
        }

        return [
            'count' => count($discoveryIds),
            'cost' => $cost,
        ];
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

    public function previewMission(?Club $club, Player $player, string $priority, string $focus, string $level, string $region, string $type, ?ScoutingScout $scout = null): array
    {
        $effectiveLevel = $scout?->level ?: $level;
        $effectiveRegion = $scout?->region ?: $region;

        return $this->missionProfile($priority, $focus, $effectiveLevel, $effectiveRegion, $type, $this->isDomesticTarget($club, $player), $scout);
    }

    public function assignScoutToWatchlist(ScoutingWatchlist $watchlist, mixed $preferredScoutId = null): ScoutingScout
    {
        $watchlist->loadMissing(['club', 'player.club', 'scout']);
        $club = $watchlist->club;
        $scouts = $this->availableScouts($club, $watchlist->created_by_user_id);

        $preferredScout = null;
        if ($preferredScoutId) {
            $preferredScout = $scouts->first(fn (ScoutingScout $scout) => (int) $scout->id === (int) $preferredScoutId);
        }

        $currentScout = $watchlist->scout;
        if ($currentScout && ($currentScout->status === 'available' || ($currentScout->available_at && $currentScout->available_at->isPast()))) {
            $preferredScout = $currentScout->refresh();
        }

        $scout = $preferredScout ?: $this->selectScoutForWatchlist($watchlist, $scouts);

        if (!$scout) {
            throw ValidationException::withMessages([
                'scout_id' => 'Aktuell ist kein Scout verfuegbar. Warte auf freie Slots oder stelle Scout-Konfiguration um.',
            ]);
        }

        if ((int) $watchlist->scout_id !== (int) $scout->id) {
            $watchlist->forceFill([
                'scout_id' => $scout->id,
                'scout_level' => $scout->level,
                'scout_region' => $scout->region,
            ])->save();
        }

        return $scout->refresh();
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

    private function missionProfile(string $priority, string $focus, string $level, string $region, string $type, bool $domesticTarget, ?ScoutingScout $scout = null): array
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
        $specialtyAdjust = match ($scout?->specialty) {
            'medical' => $focus === 'medical' ? ['gain' => 4, 'cost' => 800, 'days' => -1] : ['gain' => 0, 'cost' => 0, 'days' => 0],
            'tactical' => $focus === 'tactical' ? ['gain' => 4, 'cost' => 600, 'days' => -1] : ['gain' => 0, 'cost' => 0, 'days' => 0],
            'personality' => $focus === 'personality' ? ['gain' => 4, 'cost' => 500, 'days' => -1] : ['gain' => 0, 'cost' => 0, 'days' => 0],
            default => ['gain' => $focus === 'general' ? 2 : 1, 'cost' => 0, 'days' => 0],
        };

        return [
            'gain' => max(8, $baseGain + $levelAdjust['gain'] + $regionAdjust['gain'] + $typeAdjust['gain'] + $focusAdjust['gain'] + $specialtyAdjust['gain']),
            'cost' => round(max(3000, $baseCost + $levelAdjust['cost'] + $regionAdjust['cost'] + $typeAdjust['cost'] + $focusAdjust['cost'] + $specialtyAdjust['cost']), 2),
            'days' => max(1, $baseDays + $levelAdjust['days'] + $regionAdjust['days'] + $typeAdjust['days'] + $specialtyAdjust['days']),
        ];
    }

    private function selectScoutForWatchlist(ScoutingWatchlist $watchlist, Collection $scouts): ?ScoutingScout
    {
        return $scouts
            ->filter(function (ScoutingScout $scout): bool {
                return $scout->status === 'available'
                    || ($scout->available_at && $scout->available_at->isPast());
            })
            ->sortByDesc(function (ScoutingScout $scout) use ($watchlist): int {
                $score = 0;

                $score += match ($scout->level) {
                    'elite' => 6,
                    'experienced' => 4,
                    default => 2,
                };

                $score += match (true) {
                    $scout->specialty === $watchlist->focus => 4,
                    $scout->specialty === 'general' => 2,
                    default => 0,
                };

                $score += $scout->region === $watchlist->scout_region ? 2 : 0;
                $score -= (int) floor($scout->workload / 20);

                return $score;
            })
            ->first();
    }

    private function isDomesticTarget(?Club $club, Player $player): bool
    {
        if (!$club) {
            return false;
        }

        return filled($club->country) && filled($player->club?->country) && $club->country === $player->club?->country;
    }

    private function discoveryPoolLimit(string $level): int
    {
        return match ($level) {
            'junior' => 22,
            'elite' => 54,
            default => 36,
        };
    }

    private function discoveryLeadCount(string $level): int
    {
        return match ($level) {
            'junior' => 6,
            'elite' => 12,
            default => 9,
        };
    }

    private function discoveryScanCost(string $market, string $level): float
    {
        $base = match ($market) {
            'global' => 28000,
            'continental' => 16000,
            default => 8000,
        };

        $levelAdjust = match ($level) {
            'elite' => 14000,
            'junior' => -2500,
            default => 4500,
        };

        return round(max(4000, $base + $levelAdjust), 2);
    }

    private function applyMarketScope($builder, ?Club $activeClub, string $market): void
    {
        if (!$activeClub?->country || $market === 'global') {
            return;
        }

        if ($market === 'domestic') {
            $builder->whereHas('club', fn ($clubQuery) => $clubQuery->where('country', $activeClub->country));

            return;
        }

        if ($market === 'continental') {
            $regionalPool = $this->regionalPoolForCountry($activeClub->country);
            $builder->whereHas('club', function ($clubQuery) use ($activeClub, $regionalPool): void {
                $clubQuery
                    ->whereIn('country', $regionalPool)
                    ->where('country', '!=', $activeClub->country);
            });
        }
    }

    private function applyPositionScope($builder, string $position): void
    {
        $map = [
            'GK' => ['GK'],
            'DEF' => ['LB', 'LWB', 'CB', 'RB', 'RWB', 'SW'],
            'MID' => ['CDM', 'CM', 'CAM', 'LM', 'RM'],
            'ATT' => ['LW', 'RW', 'CF', 'ST', 'LS', 'RS'],
        ];

        if (!isset($map[$position])) {
            return;
        }

        $builder->whereIn('position', $map[$position]);
    }

    private function applyAgeScope($builder, string $ageBand): void
    {
        match ($ageBand) {
            'u21' => $builder->where('age', '<=', 20),
            '21_25' => $builder->whereBetween('age', [21, 25]),
            '26_30' => $builder->whereBetween('age', [26, 30]),
            '31_plus' => $builder->where('age', '>=', 31),
            default => null,
        };
    }

    private function applyValueScope($builder, string $valueBand): void
    {
        match ($valueBand) {
            'budget' => $builder->where('market_value', '<=', 2500000),
            'mid' => $builder->whereBetween('market_value', [2500001, 12000000]),
            'premium' => $builder->whereBetween('market_value', [12000001, 30000000]),
            'elite' => $builder->where('market_value', '>', 30000000),
            default => null,
        };
    }

    private function marketValueBand(float $value): string
    {
        return match (true) {
            $value >= 30000000 => 'Elite',
            $value >= 12000000 => 'Premium',
            $value >= 2500000 => 'Mid',
            default => 'Budget',
        };
    }

    private function fitScore(Player $player, ?Club $activeClub, string $positionFilter): int
    {
        $base = (int) round(($player->overall * 0.55) + ($player->potential * 0.45));

        if ($positionFilter !== 'all' && $this->positionMatchesGroup($player->position, $positionFilter)) {
            $base += 8;
        }

        if ($activeClub && $this->isDomesticTarget($activeClub, $player)) {
            $base += 4;
        }

        if ($player->age <= 23) {
            $base += 5;
        } elseif ($player->age >= 30) {
            $base -= 4;
        }

        return max(35, min(99, $base));
    }

    private function regionTag(?Club $activeClub, Player $player): string
    {
        if (!$activeClub?->country || !$player->club?->country) {
            return 'Unklar';
        }

        if ($activeClub->country === $player->club->country) {
            return 'Domestic';
        }

        return in_array($player->club->country, $this->regionalPoolForCountry($activeClub->country), true)
            ? 'Continental'
            : 'Global';
    }

    private function discoveryNote(Player $player, string $discoveryLevel): string
    {
        $base = match (true) {
            $player->potential >= 84 => 'Sehr hohe Decke',
            $player->potential >= 76 => 'Klarer Entwicklungspfad',
            default => 'Solides Profil',
        };

        $suffix = match ($discoveryLevel) {
            'junior' => 'mit unsicherer Datenlage',
            'elite' => 'mit starker Scout-Sicherheit',
            default => 'mit brauchbarer Tendenz',
        };

        return $base.' '.$suffix;
    }

    private function regionalPoolForCountry(string $country): array
    {
        $europe = ['Deutschland', 'Frankreich', 'Spanien', 'Italien', 'England', 'Niederlande', 'Belgien', 'Portugal', 'Oesterreich', 'Schweiz'];
        $americas = ['Brasilien', 'Argentinien', 'Uruguay', 'USA', 'Mexiko', 'Kolumbien'];

        if (in_array($country, $europe, true)) {
            return $europe;
        }

        if (in_array($country, $americas, true)) {
            return $americas;
        }

        return [$country];
    }

    private function positionMatchesGroup(string $position, string $group): bool
    {
        return match ($group) {
            'GK' => $position === 'GK',
            'DEF' => in_array($position, ['LB', 'LWB', 'CB', 'RB', 'RWB', 'SW'], true),
            'MID' => in_array($position, ['CDM', 'CM', 'CAM', 'LM', 'RM'], true),
            'ATT' => in_array($position, ['LW', 'RW', 'CF', 'ST', 'LS', 'RS'], true),
            default => true,
        };
    }
}
