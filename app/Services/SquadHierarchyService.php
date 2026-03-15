<?php

namespace App\Services;

use App\Models\Club;

class SquadHierarchyService
{
    public function refreshForClub(Club $club): void
    {
        $players = $club->players()->orderByDesc('overall')->orderBy('age')->get();

        foreach ($players as $index => $player) {
            $role = match (true) {
                $index === 0 => 'star_player',
                $index <= 4 => 'important_first_team',
                $index <= 10 => 'rotation',
                $player->age <= 21 => 'prospect',
                $index <= 16 => 'backup',
                default => 'surplus',
            };

            $leadership = match (true) {
                (int) $club->captain_player_id === (int) $player->id,
                (int) $club->vice_captain_player_id === (int) $player->id => 'captain_group',
                $player->age >= 29 && $player->overall >= 70 => 'senior_core',
                $player->age >= 24 => 'regular',
                default => 'low',
            };

            $player->forceFill([
                'squad_role' => $role,
                'leadership_level' => $leadership,
                'team_status' => $this->teamStatusForRole($role),
                'expected_playtime' => $this->expectedPlaytimeForRole($role),
            ])->save();
        }
    }

    private function teamStatusForRole(string $role): string
    {
        return match ($role) {
            'star_player' => 'leader',
            'important_first_team' => 'core',
            'rotation' => 'rotation',
            'prospect' => 'development',
            'backup' => 'support',
            default => 'fringe',
        };
    }

    private function expectedPlaytimeForRole(string $role): int
    {
        return match ($role) {
            'star_player' => 85,
            'important_first_team' => 72,
            'rotation' => 50,
            'prospect' => 32,
            'backup' => 22,
            default => 10,
        };
    }
}
