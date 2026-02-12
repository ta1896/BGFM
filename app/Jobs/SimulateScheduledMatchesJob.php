<?php

namespace App\Jobs;

use App\Models\GameMatch;
use App\Services\LiveMatchTickerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class SimulateScheduledMatchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param int $limit maximum number of matches to process per run, 0 = all open matches
     * @param array<int, string> $types
     * @param int $minutesPerRun live minutes simulated per run and match
     */
    public function __construct(
        public readonly int $limit = 0,
        public readonly array $types = ['friendly', 'league', 'cup'],
        public readonly int $minutesPerRun = 5
    ) {
    }

    public function handle(LiveMatchTickerService $tickerService): void
    {
        $limit = max(0, $this->limit);
        $types = array_values(array_unique(array_filter($this->types)));
        $minutesPerRun = max(1, min(90, $this->minutesPerRun));

        $query = GameMatch::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->whereIn('type', $types)
            ->whereNotNull('kickoff_at')
            ->where('kickoff_at', '<=', now())
            ->orderBy('kickoff_at')
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $matches = $query->get();

        foreach ($matches as $match) {
            if ($match->status === 'live' && $match->live_paused) {
                continue;
            }

            try {
                $tickerService->tick($match, $minutesPerRun);
            } catch (Throwable $exception) {
                report($exception);

                $match->update([
                    'status' => 'live',
                    'live_paused' => true,
                    'live_error_message' => Str::limit($exception->getMessage(), 255),
                    'live_last_tick_at' => now(),
                ]);
            }
        }
    }
}
