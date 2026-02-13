<?php

namespace App\Services;

use App\Models\GameMatch;
use Illuminate\Support\Facades\DB;

class MatchProcessingStepService
{
    public function claim(GameMatch|int $match, string $step, array $metadata = []): bool
    {
        $matchId = $match instanceof GameMatch ? (int) $match->id : (int) $match;
        if ($matchId < 1 || trim($step) === '') {
            return false;
        }

        $inserted = DB::table('match_processing_steps')->insertOrIgnore([
            'match_id' => $matchId,
            'step' => substr(trim($step), 0, 64),
            'processed_at' => now(),
            'metadata' => $metadata !== [] ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $inserted === 1;
    }
}
