<?php

namespace App\Services;

use App\Models\GameNotification;
use App\Models\Player;
use App\Models\TrainingSession;
use Illuminate\Support\Facades\DB;

class TrainingService
{
    public function applySession(TrainingSession $session): void
    {
        if ($session->is_applied) {
            return;
        }

        $session->loadMissing(['club', 'players', 'club.user']);

        DB::transaction(function () use ($session): void {
            foreach ($session->players as $player) {
                /** @var Player $player */
                $staminaDelta = (int) $player->pivot->stamina_delta;
                $moraleDelta = (int) $player->pivot->morale_delta;
                $overallDelta = (int) $player->pivot->overall_delta;

                $player->update([
                    'stamina' => max(1, min(100, $player->stamina + $staminaDelta)),
                    'morale' => max(1, min(100, $player->morale + $moraleDelta)),
                    'overall' => max(1, min(99, $player->overall + $overallDelta)),
                    'last_training_at' => now(),
                ]);
            }

            $session->update([
                'is_applied' => true,
                'applied_at' => now(),
            ]);

            if ($session->club->user_id) {
                GameNotification::create([
                    'user_id' => $session->club->user_id,
                    'club_id' => $session->club_id,
                    'type' => 'training_applied',
                    'title' => 'Training abgeschlossen',
                    'message' => 'Die Session vom '.$session->session_date?->format('d.m.Y').' wurde angewendet.',
                    'action_url' => '/training',
                ]);
            }
        });
    }
}
