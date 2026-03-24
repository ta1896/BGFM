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
use Throwable;

class SimulateSingleMatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const CLAIM_REASON_CLAIMED = 'claimed';
    private const CLAIM_REASON_ACTIVE_CLAIM = 'active_claim';
    private const CLAIM_REASON_UNCLAIMABLE = 'unclaimable';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $matchId,
        public readonly int $minutesPerRun,
        public readonly string $runToken,
        public readonly array $allowedTypes
    ) {
    }

    /**
     * @return array{
     *   claimed: bool,
     *   processed: bool,
     *   failed: bool,
     *   reason: string,
     *   stale_takeover: bool
     * }
     */
    public function handle(LiveMatchTickerService $tickerService): array
    {
        $staleAfterSeconds = max(30, (int) config('simulation.scheduler.claim_stale_after_seconds', 180));
        $claimStaleBefore = now()->subSeconds($staleAfterSeconds);

        $claim = $this->claimMatch(
            $this->matchId,
            $this->runToken,
            $this->allowedTypes,
            $claimStaleBefore
        );

        /** @var GameMatch|null $match */
        $match = $claim['match'];

        if (!$match) {
            return [
                'claimed' => false,
                'processed' => false,
                'failed' => false,
                'reason' => $claim['reason'],
                'stale_takeover' => (bool) $claim['stale_takeover'],
            ];
        }

        try {
            $tickerService->tick($match, $this->minutesPerRun);
            $this->releaseClaim($match->id, $this->runToken, null);

            return [
                'claimed' => true,
                'processed' => true,
                'failed' => false,
                'reason' => $claim['reason'],
                'stale_takeover' => (bool) $claim['stale_takeover'],
            ];
        } catch (Throwable $exception) {
            report($exception);
            $this->releaseClaim($match->id, $this->runToken, $exception);

            return [
                'claimed' => true,
                'processed' => false,
                'failed' => true,
                'reason' => $claim['reason'],
                'stale_takeover' => (bool) $claim['stale_takeover'],
            ];
        }
    }

    /**
     * @param array<int, string> $allowedTypes
     * @return array{match: ?GameMatch, reason: string, stale_takeover: bool}
     */
    private function claimMatch(
        int $matchId,
        string $runToken,
        array $allowedTypes,
        \Carbon\CarbonInterface $claimStaleBefore
    ): array {
        return DB::transaction(function () use ($matchId, $runToken, $allowedTypes, $claimStaleBefore): array {
            $lockedMatch = GameMatch::query()
                ->whereKey($matchId)
                ->lockForUpdate()
                ->first();

            if (!$lockedMatch) {
                return [
                    'match' => null,
                    'reason' => self::CLAIM_REASON_UNCLAIMABLE,
                    'stale_takeover' => false,
                ];
            }

            if (!in_array((string) $lockedMatch->status, ['scheduled', 'live'], true)) {
                return [
                    'match' => null,
                    'reason' => self::CLAIM_REASON_UNCLAIMABLE,
                    'stale_takeover' => false,
                ];
            }

            if ((string) $lockedMatch->status === 'live' && (bool) $lockedMatch->live_paused) {
                return [
                    'match' => null,
                    'reason' => self::CLAIM_REASON_UNCLAIMABLE,
                    'stale_takeover' => false,
                ];
            }

            if (!in_array((string) $lockedMatch->type, $allowedTypes, true)) {
                return [
                    'match' => null,
                    'reason' => self::CLAIM_REASON_UNCLAIMABLE,
                    'stale_takeover' => false,
                ];
            }

            if (!$lockedMatch->kickoff_at || $lockedMatch->kickoff_at->isFuture()) {
                return [
                    'match' => null,
                    'reason' => self::CLAIM_REASON_UNCLAIMABLE,
                    'stale_takeover' => false,
                ];
            }

            $processingToken = (string) ($lockedMatch->live_processing_token ?? '');
            $processingStartedAt = $lockedMatch->live_processing_started_at;
            $isStale = !$processingStartedAt || $processingStartedAt->lte($claimStaleBefore);

            if ($processingToken !== '' && !$isStale) {
                return [
                    'match' => null,
                    'reason' => self::CLAIM_REASON_ACTIVE_CLAIM,
                    'stale_takeover' => false,
                ];
            }

            $isStaleTakeover = $processingToken !== '' && $isStale;

            $lockedMatch->update([
                'live_processing_token' => $runToken,
                'live_processing_started_at' => now(),
                'live_processing_attempts' => (int) $lockedMatch->live_processing_attempts + 1,
                'live_processing_last_error' => null,
            ]);

            return [
                'match' => $lockedMatch->fresh(),
                'reason' => self::CLAIM_REASON_CLAIMED,
                'stale_takeover' => $isStaleTakeover,
            ];
        });
    }

    private function releaseClaim(int $matchId, string $runToken, ?Throwable $exception): void
    {
        $isFailure = $exception !== null;
        $errorMessage = $this->truncateErrorMessage($exception?->getMessage());

        DB::transaction(function () use ($matchId, $runToken, $isFailure, $errorMessage): void {
            $lockedMatch = GameMatch::query()
                ->whereKey($matchId)
                ->lockForUpdate()
                ->first();

            if (!$lockedMatch) {
                return;
            }

            if ((string) ($lockedMatch->live_processing_token ?? '') !== $runToken) {
                return;
            }

            $updates = [
                'live_processing_token' => null,
                'live_processing_started_at' => null,
                'live_processing_last_run_at' => now(),
                'live_processing_last_error' => $errorMessage,
            ];

            if ($isFailure) {
                $updates['status'] = 'live';
                $updates['live_paused'] = true;
                $updates['live_error_message'] = $errorMessage;
                $updates['live_last_tick_at'] = now();
            }

            $lockedMatch->update($updates);
        });
    }

    private function truncateErrorMessage(?string $message, int $maxBytes = 250): ?string
    {
        if ($message === null) {
            return null;
        }

        $message = trim($message);
        if ($message === '') {
            return null;
        }

        return strlen($message) <= $maxBytes ? $message : substr($message, 0, $maxBytes);
    }
}
