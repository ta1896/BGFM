<?php

namespace App\Services;

use App\Models\Club;
use App\Models\CompetitionSeason;
use App\Models\GameNotification;
use Illuminate\Support\Facades\DB;

class CupRewardService
{
    public function __construct(private readonly ClubFinanceLedgerService $financeLedger)
    {
    }

    /**
     * @param array<int, int> $clubIds
     */
    public function rewardAdvancements(
        CompetitionSeason $competitionSeason,
        int $sourceRoundNumber,
        int $targetRoundNumber,
        string $targetStage,
        array $clubIds
    ): void {
        if (!(bool) config('simulation.cup.rewards.enabled', true)) {
            return;
        }

        $amount = $this->advancementAmountForStage($targetStage);
        foreach (array_values(array_unique(array_map('intval', $clubIds))) as $clubId) {
            $this->reward(
                $competitionSeason,
                $clubId,
                'advance:r'.$sourceRoundNumber.':to:r'.$targetRoundNumber,
                $targetStage,
                $sourceRoundNumber,
                $targetRoundNumber,
                $amount
            );
        }
    }

    public function rewardChampion(CompetitionSeason $competitionSeason, int $sourceRoundNumber, int $clubId): void
    {
        if (!(bool) config('simulation.cup.rewards.enabled', true)) {
            return;
        }

        $amount = max(0.0, (float) config('simulation.cup.rewards.champion', 300000.0));
        $this->reward(
            $competitionSeason,
            (int) $clubId,
            'champion:r'.$sourceRoundNumber,
            'Pokalsieger',
            $sourceRoundNumber,
            null,
            $amount
        );
    }

    private function reward(
        CompetitionSeason $competitionSeason,
        int $clubId,
        string $eventKey,
        string $stage,
        int $sourceRoundNumber,
        ?int $targetRoundNumber,
        float $amount
    ): void {
        $eventKey = substr(trim($eventKey), 0, 120);
        if ($clubId < 1 || $eventKey === '') {
            return;
        }

        DB::transaction(function () use (
            $competitionSeason,
            $clubId,
            $eventKey,
            $stage,
            $sourceRoundNumber,
            $targetRoundNumber,
            $amount
        ): void {
            $inserted = DB::table('cup_reward_logs')->insertOrIgnore([
                'competition_season_id' => $competitionSeason->id,
                'club_id' => $clubId,
                'event_key' => $eventKey,
                'stage' => substr($stage, 0, 80),
                'source_round_number' => max(1, $sourceRoundNumber),
                'target_round_number' => $targetRoundNumber ? max(1, $targetRoundNumber) : null,
                'amount' => round(max(0.0, $amount), 2),
                'rewarded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($inserted !== 1) {
                return;
            }

            /** @var Club|null $club */
            $club = Club::query()
                ->with('user')
                ->whereKey($clubId)
                ->lockForUpdate()
                ->first();
            if (!$club) {
                return;
            }

            $payout = round(max(0.0, $amount), 2);
            if ($payout > 0) {
                $this->financeLedger->applyBudgetChange($club, $payout, [
                    'context_type' => 'other',
                    'reference_type' => 'cup_reward_logs',
                    'note' => 'Pokalpraemie: '.$stage,
                ]);
            }

            if (!$club->user_id || !(bool) config('simulation.cup.rewards.notifications.enabled', true)) {
                return;
            }

            $competitionSeason->loadMissing('competition');
            $competitionName = (string) ($competitionSeason->competition?->name ?: 'Pokal');
            $amountText = $payout > 0 ? ' +'.number_format($payout, 2, ',', '.').' EUR' : '';

            GameNotification::query()->create([
                'user_id' => (int) $club->user_id,
                'club_id' => (int) $club->id,
                'type' => 'cup_achievement',
                'title' => 'Pokal-Fortschritt',
                'message' => $competitionName.': '.$stage.' erreicht.'.$amountText,
                'action_url' => '/table',
            ]);
        });
    }

    private function advancementAmountForStage(string $stage): float
    {
        $default = max(0.0, (float) config('simulation.cup.rewards.advancement.default', 50000.0));
        $normalizedStage = strtolower(trim($stage));

        return match (true) {
            str_contains($normalizedStage, 'achtelfinale') => max(0.0, (float) config('simulation.cup.rewards.advancement.achtelfinale', $default)),
            str_contains($normalizedStage, 'viertelfinale') => max(0.0, (float) config('simulation.cup.rewards.advancement.viertelfinale', $default)),
            str_contains($normalizedStage, 'halbfinale') => max(0.0, (float) config('simulation.cup.rewards.advancement.halbfinale', $default)),
            str_contains($normalizedStage, 'finale') => max(0.0, (float) config('simulation.cup.rewards.advancement.finale', $default)),
            default => $default,
        };
    }
}
