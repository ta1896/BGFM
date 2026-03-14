<?php

namespace App\Jobs;

use App\Models\GameMatch;
use App\Services\LiveMatchTickerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SimulateScheduledMatchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const CLAIM_REASON_CLAIMED = 'claimed';
    private const CLAIM_REASON_ACTIVE_CLAIM = 'active_claim';
    private const CLAIM_REASON_UNCLAIMABLE = 'unclaimable';

    /**
     * @param int $limit maximum number of matches to process per run, 0 = all open matches
     * @param array<int, string> $types
     * @param int $minutesPerRun live minutes simulated per run and match
     */
    public function __construct(
        public readonly int $limit = 0,
        public readonly array $types = ['friendly', 'league', 'cup'],
        public readonly int $minutesPerRun = 5,
        public readonly array $matchIds = []
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function handle(LiveMatchTickerService $tickerService): array
    {
        $limit = max(0, $this->limit);
        $types = array_values(array_unique(array_filter($this->types)));
        $minutesPerRun = max(1, min(90, $this->minutesPerRun));
        $runToken = (string) Str::uuid();

        // Get the dynamic concurrency limit from settings
        $maxConcurrency = (int) config('simulation.scheduler.max_concurrency', 5);

        $staleAfterSeconds = max(30, (int) config('simulation.scheduler.claim_stale_after_seconds', 180));
        $claimStaleBefore = now()->subSeconds($staleAfterSeconds);

        $query = GameMatch::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->where(function ($statusQuery): void {
                $statusQuery
                    ->where('status', 'scheduled')
                    ->orWhere(function ($liveQuery): void {
                        $liveQuery->where('status', 'live')
                            ->where('live_paused', false);
                    });
            })
            ->whereIn('type', $types)
            ->whereNotNull('kickoff_at')
            ->where('kickoff_at', '<=', now())
            ->where(function ($processingQuery) use ($claimStaleBefore): void {
                $processingQuery
                    ->whereNull('live_processing_started_at')
                    ->orWhere('live_processing_started_at', '<=', $claimStaleBefore);
            });

        if ($this->matchIds !== []) {
            $query->whereIn('id', $this->matchIds);
        }

        $query->orderBy('kickoff_at')
            ->orderBy('id');

        // Limit the dispatcher to the max_concurrency or the provided limit
        $effectiveLimit = ($limit > 0) ? min($limit, $maxConcurrency) : $maxConcurrency;
        $query->limit($effectiveLimit);

        /** @var array<int, int> $candidateIds */
        $candidateIds = $query->pluck('id')->map(fn($id): int => (int) $id)->all();

        foreach ($candidateIds as $matchId) {
            SimulateSingleMatchJob::dispatch(
                $matchId,
                $minutesPerRun,
                $runToken,
                $types
            );
        }

        return [
            'dispatched_matches' => count($candidateIds),
            'run_token' => $runToken,
        ];
    }
}
