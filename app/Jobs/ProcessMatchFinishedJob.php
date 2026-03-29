<?php

namespace App\Jobs;

use App\Models\GameMatch;
use App\Services\MatchLineupService;
use App\Services\Simulation\Observers\MatchFinishedContext;
use App\Services\Simulation\Observers\MatchFinishedObserverPipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMatchFinishedJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public readonly int $matchId,
    ) {
    }

    /**
     * Ensure only one job runs per match (idempotency guard).
     */
    public function uniqueId(): string
    {
        return "match_finished_{$this->matchId}";
    }

    public function handle(
        MatchFinishedObserverPipeline $pipeline,
        MatchLineupService $lineupService,
    ): void {
        $match = GameMatch::query()->find($this->matchId);

        if (!$match || $match->status !== 'played') {
            return;
        }

        $match->loadMissing(['homeClub.players', 'awayClub.players']);

        $homePlayers = $lineupService->resolveParticipants($match->homeClub, $match, true);
        $awayPlayers = $lineupService->resolveParticipants($match->awayClub, $match, true);

        $pipeline->process(new MatchFinishedContext($match, $homePlayers, $awayPlayers));
    }
}
