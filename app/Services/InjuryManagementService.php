<?php

namespace App\Services;

use App\Models\Player;
use App\Models\PlayerInjury;
use Illuminate\Support\Carbon;

class InjuryManagementService
{
    public function syncCurrentInjury(Player $player): ?PlayerInjury
    {
        $injury = $player->injuries()
            ->where('status', 'active')
            ->latest('started_at')
            ->first();

        if (!$injury && (int) $player->injury_matches_remaining > 0) {
            $injury = PlayerInjury::create([
                'player_id' => $player->id,
                'club_id' => $player->club_id,
                'injury_type' => 'Muskelverletzung',
                'body_area' => 'Bein',
                'severity' => (int) $player->injury_matches_remaining >= 3 ? 'major' : 'minor',
                'started_at' => now(),
                'expected_return_at' => now()->addDays(max(3, (int) $player->injury_matches_remaining * 6)),
                'status' => 'active',
                'source' => 'match',
            ]);
        }

        if ($injury && (int) $player->injury_matches_remaining === 0) {
            $injury->forceFill([
                'status' => 'recovered',
                'actual_return_at' => now(),
            ])->save();
            $injury = null;
        }

        $player->forceFill([
            'medical_status' => $injury ? 'rehab' : ($player->medical_status === 'rehab' ? 'fit' : $player->medical_status),
        ])->save();

        return $injury;
    }

    public function rehabProgress(Player $player): array
    {
        $injury = $this->syncCurrentInjury($player);

        if (!$injury) {
            return [
                'status' => 'fit',
                'label' => 'Spielfit',
                'expected_return' => null,
            ];
        }

        return [
            'status' => 'injured',
            'label' => $injury->injury_type,
            'expected_return' => $injury->expected_return_at?->format('d.m.Y'),
        ];
    }
}
