<?php

namespace App\Services;

use App\Models\NationalTeam;
use App\Models\NationalTeamCallup;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NationalTeamService
{
    public function refreshSquad(NationalTeam $nationalTeam, ?User $actor = null, int $limit = 23): int
    {
        $nationalTeam->loadMissing('country');
        $countryName = $nationalTeam->country?->name;
        if (!$countryName) {
            return 0;
        }

        $players = Player::query()
            ->where('status', 'active')
            ->whereHas('club', function ($query) use ($countryName): void {
                $query->where('country', $countryName);
            })
            ->orderByDesc('overall')
            ->orderByDesc('potential')
            ->orderByDesc('morale')
            ->limit(max(11, $limit))
            ->get();

        DB::transaction(function () use ($nationalTeam, $players, $actor): void {
            NationalTeamCallup::query()
                ->where('national_team_id', $nationalTeam->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'released',
                    'released_on' => now()->toDateString(),
                    'updated_at' => now(),
                ]);

            $rows = $players->values()->map(function (Player $player, int $index) use ($nationalTeam, $actor) {
                $role = $index < 11 ? 'starter' : ($index < 18 ? 'bench' : 'reserve');

                return [
                    'national_team_id' => $nationalTeam->id,
                    'player_id' => $player->id,
                    'created_by_user_id' => $actor?->id,
                    'called_up_on' => now()->toDateString(),
                    'released_on' => null,
                    'role' => $role,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            if (!empty($rows)) {
                NationalTeamCallup::query()->insert($rows);
            }
        });

        return $players->count();
    }
}
