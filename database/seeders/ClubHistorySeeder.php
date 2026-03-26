<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClubHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $club = \App\Models\Club::first();
        if (!$club) return;

        // Legends
        $players = $club->players()->take(3)->get();
        foreach ($players as $index => $player) {
            \App\Models\ClubHallOfFame::updateOrCreate(
                ['player_id' => $player->id, 'club_id' => $club->id],
                [
                    'inducted_at' => now()->subYears($index + 1),
                    'legend_type' => $index === 0 ? 'icon' : 'fan_favorite',
                    'description' => $index === 0 
                        ? "Unvergessener Anführer und Rekordhalter des Vereins." 
                        : "Ein Spieler, der immer alles für das Wappen gegeben hat.",
                ]
            );
        }

        // Records
        $records = [
            ['key' => 'all_time_goals', 'value' => '124', 'label' => 'Meiste Tore'],
            ['key' => 'all_time_apps', 'value' => '450', 'label' => 'Meiste Spiele'],
            ['key' => 'highest_win', 'value' => '8:0', 'label' => 'Höchster Sieg'],
            ['key' => 'points_record_season', 'value' => '82', 'label' => 'Punkte-Rekord (Saison)'],
            ['key' => 'goals_record_season', 'value' => '95', 'label' => 'Tore-Rekord (Saison)'],
        ];

        foreach ($records as $record) {
            \App\Models\ClubRecord::updateOrCreate(
                ['club_id' => $club->id, 'record_key' => $record['key']],
                [
                    'record_value' => $record['value'],
                    'achieved_at' => now()->subMonths(rand(1, 60)),
                ]
            );
        }
    }
}
