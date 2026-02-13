<?php

namespace App\Services\Simulation;

use App\Models\GameMatch;

class MatchSimulationExecutor
{
    /**
     * @param callable(GameMatch):GameMatch $startMatch
     * @param callable(GameMatch):GameMatch $loadState
     * @param callable(GameMatch,int):void $simulateMinute
     * @param callable(GameMatch):int $minuteLimit
     * @param callable(GameMatch):bool $canFinish
     * @param callable(GameMatch):void $finishMatch
     */
    public function run(
        GameMatch $match,
        int $minutes,
        callable $startMatch,
        callable $loadState,
        callable $simulateMinute,
        callable $minuteLimit,
        callable $canFinish,
        callable $finishMatch
    ): GameMatch {
        $minutes = max(1, min(120, $minutes));

        if ($match->status === 'scheduled') {
            $startMatch($match);
            $match = $match->fresh();
        }

        if ($match->status !== 'live' || $match->live_paused) {
            return $loadState($match);
        }

        for ($i = 0; $i < $minutes; $i++) {
            $match->refresh();
            if ($match->status !== 'live' || $match->live_paused) {
                break;
            }

            $nextMinute = ((int) $match->live_minute) + 1;
            if ($nextMinute > $minuteLimit($match)) {
                break;
            }

            $simulateMinute($match, $nextMinute);
            $match->update([
                'live_minute' => $nextMinute,
                'live_last_tick_at' => now(),
            ]);
        }

        $match->refresh();
        if ($match->status === 'live' && $canFinish($match)) {
            $finishMatch($match);
        }

        return $loadState($match);
    }
}
