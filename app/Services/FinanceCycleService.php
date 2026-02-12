<?php

namespace App\Services;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\MatchFinancialSettlement;
use App\Models\Stadium;
use Illuminate\Support\Facades\DB;

class FinanceCycleService
{
    public function __construct(
        private readonly StadiumService $stadiumService,
        private readonly SponsorService $sponsorService
    ) {
    }

    public function settleMatch(GameMatch $match): ?MatchFinancialSettlement
    {
        if ($match->status !== 'played') {
            return null;
        }

        if (MatchFinancialSettlement::query()->where('match_id', $match->id)->exists()) {
            return null;
        }

        $match->loadMissing(['homeClub.players', 'awayClub.players']);
        $homeClub = $match->homeClub;
        $awayClub = $match->awayClub;
        if (!$homeClub || !$awayClub) {
            return null;
        }

        $homeStadium = $this->stadiumService->ensureForClub($homeClub);

        $attendance = (int) ($match->attendance ?: 0);
        $attendance = min($attendance, (int) $homeStadium->capacity);

        $ticketIncome = $this->ticketIncome($homeStadium, $attendance);
        $homeSponsorIncome = $this->sponsorService->payoutShareForMatch($homeClub);
        $awaySponsorIncome = $this->sponsorService->payoutShareForMatch($awayClub);
        $homeWageExpense = $this->wageExpense($homeClub);
        $awayWageExpense = $this->wageExpense($awayClub);
        $homeMaintenance = round((float) $homeStadium->maintenance_cost / 4, 2);
        $awayTravelExpense = $this->travelExpense($homeClub, $awayClub);

        return DB::transaction(function () use (
            $match,
            $homeClub,
            $awayClub,
            $ticketIncome,
            $homeSponsorIncome,
            $awaySponsorIncome,
            $homeWageExpense,
            $awayWageExpense,
            $homeMaintenance,
            $awayTravelExpense
        ): MatchFinancialSettlement {
            $homeIncome = round($ticketIncome + $homeSponsorIncome, 2);
            $homeExpense = round($homeWageExpense + $homeMaintenance, 2);
            $awayIncome = round($awaySponsorIncome, 2);
            $awayExpense = round($awayWageExpense + $awayTravelExpense, 2);

            $homeClub->increment('budget', $homeIncome - $homeExpense);
            $awayClub->increment('budget', $awayIncome - $awayExpense);

            $homeFresh = $homeClub->fresh();
            $awayFresh = $awayClub->fresh();

            $this->bookTransactions(
                $homeClub->id,
                $homeIncome,
                $homeExpense,
                $homeFresh?->budget,
                'Matchday '.$match->id.' (Heim)'
            );
            $this->bookTransactions(
                $awayClub->id,
                $awayIncome,
                $awayExpense,
                $awayFresh?->budget,
                'Matchday '.$match->id.' (Auswaerts)'
            );

            return MatchFinancialSettlement::create([
                'match_id' => $match->id,
                'home_income' => $homeIncome,
                'home_expense' => $homeExpense,
                'away_income' => $awayIncome,
                'away_expense' => $awayExpense,
                'processed_at' => now(),
            ]);
        });
    }

    private function ticketIncome(Stadium $stadium, int $attendance): float
    {
        $vipAudience = min($attendance, (int) $stadium->vip_seats);
        $regularAudience = max(0, $attendance - $vipAudience);
        $basePrice = (float) $stadium->ticket_price;
        $experienceFactor = 1 + (($stadium->fan_experience - 50) / 500);

        return round(
            (($regularAudience * $basePrice) + ($vipAudience * $basePrice * 1.65)) * $experienceFactor,
            2
        );
    }

    private function wageExpense(Club $club): float
    {
        $salarySum = (float) $club->players->sum('salary');

        return round($salarySum / 4, 2);
    }

    private function travelExpense(Club $homeClub, Club $awayClub): float
    {
        $base = 6500.0;
        if ($homeClub->country !== $awayClub->country) {
            $base += 2500;
        }

        return round($base, 2);
    }

    private function bookTransactions(int $clubId, float $income, float $expense, ?float $balanceAfter, string $note): void
    {
        $rows = [];
        if ($income > 0) {
            $rows[] = [
                'club_id' => $clubId,
                'user_id' => null,
                'context_type' => 'match_income',
                'direction' => 'income',
                'amount' => $income,
                'balance_after' => $balanceAfter,
                'reference_type' => 'matches',
                'reference_id' => null,
                'booked_at' => now(),
                'note' => $note,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($expense > 0) {
            $rows[] = [
                'club_id' => $clubId,
                'user_id' => null,
                'context_type' => 'salary',
                'direction' => 'expense',
                'amount' => $expense,
                'balance_after' => $balanceAfter,
                'reference_type' => 'matches',
                'reference_id' => null,
                'booked_at' => now(),
                'note' => $note,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows !== []) {
            DB::table('club_financial_transactions')->insert($rows);
        }
    }
}
