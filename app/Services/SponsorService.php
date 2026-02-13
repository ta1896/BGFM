<?php

namespace App\Services;

use App\Models\Club;
use App\Models\GameNotification;
use App\Models\Sponsor;
use App\Models\SponsorContract;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SponsorService
{
    public function __construct(private readonly ClubFinanceLedgerService $financeLedger)
    {
    }

    /**
     * @return Collection<int, Sponsor>
     */
    public function availableForClub(Club $club): Collection
    {
        return Sponsor::query()
            ->where('is_active', true)
            ->where('reputation_min', '<=', $club->reputation)
            ->orderBy('reputation_min')
            ->orderByDesc('base_weekly_amount')
            ->get();
    }

    public function signContract(Club $club, Sponsor $sponsor, User $actor, int $months): SponsorContract
    {
        abort_if($months < 1 || $months > 60, 422, 'Ungueltige Vertragsdauer.');
        abort_if($club->activeSponsorContract()->exists(), 422, 'Es gibt bereits einen aktiven Sponsorvertrag.');
        abort_if(!$sponsor->is_active, 422, 'Sponsor ist nicht verfuegbar.');
        abort_if($club->reputation < $sponsor->reputation_min, 422, 'Vereinsreputation zu gering fuer diesen Sponsor.');

        $startsOn = now()->toDateString();
        $endsOn = now()->addMonths($months)->toDateString();

        $repFactor = 0.85 + (($club->reputation - $sponsor->reputation_min) / 200);
        $weeklyAmount = max(500, round((float) $sponsor->base_weekly_amount * $repFactor, 2));
        $signingBonusMin = (float) $sponsor->signing_bonus_min;
        $signingBonusMax = max($signingBonusMin, (float) $sponsor->signing_bonus_max);
        $signingBonus = $signingBonusMax > $signingBonusMin
            ? round($signingBonusMin + lcg_value() * ($signingBonusMax - $signingBonusMin), 2)
            : $signingBonusMin;

        $contract = DB::transaction(function () use (
            $club,
            $sponsor,
            $actor,
            $startsOn,
            $endsOn,
            $weeklyAmount,
            $signingBonus
        ): SponsorContract {
            $contract = SponsorContract::create([
                'club_id' => $club->id,
                'sponsor_id' => $sponsor->id,
                'signed_by_user_id' => $actor->id,
                'weekly_amount' => $weeklyAmount,
                'signing_bonus' => $signingBonus,
                'starts_on' => $startsOn,
                'ends_on' => $endsOn,
                'status' => 'active',
                'objectives' => [
                    'min_table_rank' => max(1, 14 - (int) floor($club->reputation / 10)),
                ],
            ]);

            $this->financeLedger->applyBudgetChange($club, $signingBonus, [
                'user_id' => $actor->id,
                'context_type' => 'sponsor',
                'reference_type' => 'sponsor_contracts',
                'reference_id' => $contract->id,
                'note' => 'Signing Bonus: '.$sponsor->name,
            ]);

            if ($club->user_id) {
                GameNotification::create([
                    'user_id' => $club->user_id,
                    'club_id' => $club->id,
                    'type' => 'sponsor_signed',
                    'title' => 'Neuer Sponsor',
                    'message' => $sponsor->name.' zahlt '.$weeklyAmount.' EUR pro Woche.',
                    'action_url' => '/sponsors',
                ]);
            }

            return $contract;
        });

        return $contract;
    }

    public function activeContract(Club $club): ?SponsorContract
    {
        return SponsorContract::query()
            ->where('club_id', $club->id)
            ->where('status', 'active')
            ->whereDate('starts_on', '<=', now()->toDateString())
            ->whereDate('ends_on', '>=', now()->toDateString())
            ->latest('id')
            ->first();
    }

    public function payoutShareForMatch(Club $club): float
    {
        $contract = $this->activeContract($club);
        if (!$contract) {
            return 0;
        }

        return round((float) $contract->weekly_amount / 2, 2);
    }

    public function expireEndedContracts(): int
    {
        return SponsorContract::query()
            ->where('status', 'active')
            ->whereDate('ends_on', '<', now()->toDateString())
            ->update(['status' => 'expired']);
    }
}
