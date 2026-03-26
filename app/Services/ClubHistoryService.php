<?php

namespace App\Services;

use App\Models\Club;
use App\Models\ClubHallOfFame;
use App\Models\ClubRecord;
use App\Models\Player;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClubHistoryService
{
    /**
     * Get all hall of fame entries for a club.
     */
    public function getHallOfFame(Club $club): Collection
    {
        return ClubHallOfFame::with('player')
            ->where('club_id', $club->id)
            ->orderByDesc('inducted_at')
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'player' => [
                        'id' => $entry->player->id,
                        'name' => $entry->player->full_name,
                        'photo_url' => $entry->player->photo_url,
                        'position' => $entry->player->position,
                        'overall' => $entry->player->overall,
                    ],
                    'legend_type' => $entry->legend_type,
                    'legend_type_label' => $this->getLegendTypeLabel($entry->legend_type),
                    'description' => $entry->description,
                    'inducted_at' => $entry->inducted_at?->format('d.m.Y'),
                ];
            });
    }

    /**
     * Get all records for a club.
     */
    public function getRecords(Club $club): Collection
    {
        return ClubRecord::where('club_id', $club->id)
            ->orderBy('record_key')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'key' => $record->record_key,
                    'label' => $this->getRecordLabel($record->record_key),
                    'value' => $record->record_value,
                    'achieved_at' => $record->achieved_at?->format('d.m.Y'),
                ];
            });
    }

    /**
     * Get historical comparisons for the club.
     */
    public function getHistoricalComparison(Club $club, array $currentSeasonStats): array
    {
        $records = ClubRecord::where('club_id', $club->id)->get()->keyBy('record_key');
        
        $comparisons = [];

        // Example: Points comparison
        $bestPoints = $records->get('points_record_season')?->record_value ?? 0;
        if ($bestPoints > 0) {
            $comparisons['points'] = [
                'current' => $currentSeasonStats['points'] ?? 0,
                'record' => (int) $bestPoints,
                'delta' => ($currentSeasonStats['points'] ?? 0) - (int) $bestPoints,
                'label' => 'Punkte-Rekord',
            ];
        }

        // Example: Goals comparison
        $bestGoals = $records->get('goals_record_season')?->record_value ?? 0;
        if ($bestGoals > 0) {
            $comparisons['goals'] = [
                'current' => $currentSeasonStats['goals_for'] ?? 0,
                'record' => (int) $bestGoals,
                'delta' => ($currentSeasonStats['goals_for'] ?? 0) - (int) $bestGoals,
                'label' => 'Tore-Rekord',
            ];
        }

        return $comparisons;
    }

    private function getLegendTypeLabel(string $type): string
    {
        return match ($type) {
            'icon' => 'Ikone',
            'one_club_man' => 'Vereinstreue Legende',
            'record_holder' => 'Rekordhalter',
            'fan_favorite' => 'Publikumsliebling',
            default => 'Legende',
        };
    }

    private function getRecordLabel(string $key): string
    {
        return match ($key) {
            'all_time_goals' => 'Meiste Tore (Alle Zeiten)',
            'all_time_apps' => 'Meiste Einsätze (Alle Zeiten)',
            'highest_win' => 'Höchster Sieg',
            'points_record_season' => 'Meiste Punkte (Saison)',
            'goals_record_season' => 'Meiste Tore (Saison)',
            default => str_replace('_', ' ', ucfirst($key)),
        };
    }
}
