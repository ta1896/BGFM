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
        public readonly int $minutesPerRun = 5
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
            })
            ->orderBy('kickoff_at')
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        /** @var array<int, int> $candidateIds */
        $candidateIds = $query->pluck('id')->map(fn ($id): int => (int) $id)->all();

        $summary = [
            'candidate_matches' => count($candidateIds),
            'claimed_matches' => 0,
            'processed_matches' => 0,
            'failed_matches' => 0,
            'skipped_active_claims' => 0,
            'skipped_unclaimable' => 0,
            'stale_claim_takeovers' => 0,
        ];

        foreach ($candidateIds as $matchId) {
            $claim = $this->claimMatch(
                $matchId,
                $runToken,
                $types,
                $claimStaleBefore
            );
            /** @var GameMatch|null $match */
            $match = $claim['match'];
            $reason = (string) ($claim['reason'] ?? self::CLAIM_REASON_UNCLAIMABLE);
            $staleTakeover = (bool) ($claim['stale_takeover'] ?? false);

            if (!$match) {
                if ($reason === self::CLAIM_REASON_ACTIVE_CLAIM) {
                    $summary['skipped_active_claims']++;
                } else {
                    $summary['skipped_unclaimable']++;
                }

                continue;
            }

            $summary['claimed_matches']++;
            if ($staleTakeover) {
                $summary['stale_claim_takeovers']++;
            }

            try {
                $tickerService->tick($match, $minutesPerRun);
                $this->releaseClaim($match->id, $runToken, null);
                $summary['processed_matches']++;
            } catch (Throwable $exception) {
                report($exception);
                $this->releaseClaim($match->id, $runToken, $exception);
                $summary['failed_matches']++;
            }
        }

        return $summary;
    }

    /**
     * @param array<int, string> $allowedTypes
     * @return array{match: ?GameMatch, reason: string, stale_takeover: bool}
     */
    private function claimMatch(
        int $matchId,
        string $runToken,
        array $allowedTypes,
        Carbon $claimStaleBefore
    ): array {
        /** @var array{match: ?GameMatch, reason: string, stale_takeover: bool} $claim */
        $claim = DB::transaction(function () use ($matchId, $runToken, $allowedTypes, $claimStaleBefore): array {
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

        return $claim;
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
