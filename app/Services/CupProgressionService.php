<?php

namespace App\Services;

use App\Models\CompetitionSeason;
use App\Models\GameMatch;

class CupProgressionService
{
    public function __construct(
        private readonly CompetitionContextService $competitionContextService
    ) {
    }

    public function progressRoundIfNeeded(CompetitionSeason $competitionSeason, GameMatch $match): void
    {
        if (!$this->competitionContextService->isCup($match) || !$match->competition_season_id) {
            return;
        }

        $round = (int) ($match->round_number ?? 1);
        $currentRoundMatches = GameMatch::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->where('type', 'cup')
            ->where('round_number', $round)
            ->orderBy('id')
            ->get();

        if ($currentRoundMatches->isEmpty() || $currentRoundMatches->contains(fn (GameMatch $m): bool => $m->status !== 'played')) {
            return;
        }

        $nextRound = $round + 1;
        $nextRoundExists = GameMatch::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->where('type', 'cup')
            ->where('round_number', $nextRound)
            ->exists();
        if ($nextRoundExists) {
            return;
        }

        $winnerClubIds = $currentRoundMatches
            ->map(function (GameMatch $m): ?int {
                if ((int) $m->home_score > (int) $m->away_score) {
                    return (int) $m->home_club_id;
                }
                if ((int) $m->away_score > (int) $m->home_score) {
                    return (int) $m->away_club_id;
                }
                if ($m->penalties_home !== null && $m->penalties_away !== null) {
                    return (int) ($m->penalties_home > $m->penalties_away ? $m->home_club_id : $m->away_club_id);
                }

                return null;
            })
            ->filter()
            ->values();

        if ($winnerClubIds->count() <= 1) {
            return;
        }

        if ($winnerClubIds->count() % 2 !== 0) {
            $winnerClubIds = $winnerClubIds->slice(0, $winnerClubIds->count() - 1)->values();
        }

        if ($winnerClubIds->count() < 2) {
            return;
        }

        $kickoffAt = $currentRoundMatches->max('kickoff_at')
            ? $currentRoundMatches->max('kickoff_at')->copy()->addDays(7)
            : now()->addDays(7);

        foreach ($winnerClubIds->chunk(2) as $pair) {
            if ($pair->count() < 2) {
                continue;
            }

            GameMatch::query()->create([
                'competition_season_id' => $competitionSeason->id,
                'season_id' => $competitionSeason->season_id,
                'type' => 'cup',
                'competition_context' => $this->competitionContextService->isNationalCup($match)
                    ? CompetitionContextService::CUP_NATIONAL
                    : CompetitionContextService::CUP_INTERNATIONAL,
                'stage' => 'Cup Runde '.$nextRound,
                'round_number' => $nextRound,
                'kickoff_at' => $kickoffAt,
                'status' => 'scheduled',
                'home_club_id' => (int) $pair->get(0),
                'away_club_id' => (int) $pair->get(1),
                'stadium_club_id' => (int) $pair->get(0),
                'simulation_seed' => random_int(10000, 99999),
            ]);

            $kickoffAt = $kickoffAt->copy()->addMinutes(30);
        }
    }
}
