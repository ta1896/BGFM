<?php

namespace App\Services;

use App\Models\CompetitionSeason;
use App\Models\GameMatch;
use Illuminate\Support\Collection;

class CupProgressionService
{
    public function __construct(
        private readonly CompetitionContextService $competitionContextService,
        private readonly CupRewardService $cupRewardService
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

        $winnerClubIds = $this->resolveRoundWinnerClubIds($currentRoundMatches);

        if ($winnerClubIds->count() <= 1) {
            if ($winnerClubIds->count() === 1) {
                $this->cupRewardService->rewardChampion(
                    $competitionSeason,
                    $round,
                    (int) $winnerClubIds->first()
                );
            }

            return;
        }

        $participantCount = $winnerClubIds->count();
        $advancingClubIds = $winnerClubIds
            ->map(fn (int $clubId): int => (int) $clubId)
            ->values()
            ->all();
        $byeClubId = null;
        if ($winnerClubIds->count() % 2 !== 0) {
            $byeClubId = (int) $winnerClubIds->pop();
        }

        if ($winnerClubIds->count() < 2 && $byeClubId === null) {
            return;
        }

        $isTwoLeggedRound = $this->shouldGenerateTwoLeggedRound($participantCount);
        $kickoffAt = $currentRoundMatches->max('kickoff_at')
            ? $currentRoundMatches->max('kickoff_at')->copy()->addDays(7)
            : now()->addDays(7);
        $stage = $this->stageName($nextRound, $participantCount);
        $competitionContext = $this->competitionContextService->forMatch($match);

        $this->cupRewardService->rewardAdvancements(
            $competitionSeason,
            $round,
            $nextRound,
            $stage,
            $advancingClubIds
        );

        foreach ($winnerClubIds->chunk(2) as $pair) {
            $pair = $pair->values();
            if ($pair->count() < 2) {
                continue;
            }

            $homeClubId = (int) $pair->get(0);
            $awayClubId = (int) $pair->get(1);
            if ($isTwoLeggedRound) {
                $this->createCupMatch(
                    $competitionSeason,
                    $competitionContext,
                    $nextRound,
                    $stage.' (Hinspiel)',
                    $kickoffAt->copy(),
                    $homeClubId,
                    $awayClubId
                );
                $this->createCupMatch(
                    $competitionSeason,
                    $competitionContext,
                    $nextRound,
                    $stage.' (Rueckspiel)',
                    $kickoffAt->copy()->addDays($this->daysBetweenLegs()),
                    $awayClubId,
                    $homeClubId
                );

                $kickoffAt = $kickoffAt->copy()->addDays(1);
                continue;
            }

            $this->createCupMatch(
                $competitionSeason,
                $competitionContext,
                $nextRound,
                $stage,
                $kickoffAt,
                $homeClubId,
                $awayClubId
            );

            $kickoffAt = $kickoffAt->copy()->addMinutes(30);
        }

        if ($byeClubId !== null) {
            GameMatch::query()->create([
                'competition_season_id' => $competitionSeason->id,
                'season_id' => $competitionSeason->season_id,
                'type' => 'cup',
                'competition_context' => $competitionContext,
                'stage' => $stage.' (Freilos)',
                'round_number' => $nextRound,
                'kickoff_at' => $kickoffAt,
                'status' => 'played',
                'home_club_id' => $byeClubId,
                'away_club_id' => $byeClubId,
                'stadium_club_id' => $byeClubId,
                'home_score' => 1,
                'away_score' => 0,
                'extra_time' => false,
                'penalties_home' => null,
                'penalties_away' => null,
                'simulation_seed' => random_int(10000, 99999),
                'played_at' => now(),
            ]);
        }
    }

    /**
     * @param Collection<int, GameMatch> $matches
     * @return Collection<int, int>
     */
    private function resolveRoundWinnerClubIds(Collection $matches): Collection
    {
        return $matches
            ->groupBy(fn (GameMatch $match): string => $this->tieKey($match))
            ->map(fn (Collection $tieMatches): ?int => $this->resolveTieWinnerClubId($tieMatches->values()))
            ->filter()
            ->map(fn (int $clubId): int => (int) $clubId)
            ->values();
    }

    /**
     * @param Collection<int, GameMatch> $tieMatches
     */
    private function resolveTieWinnerClubId(Collection $tieMatches): ?int
    {
        if ($tieMatches->isEmpty()) {
            return null;
        }

        /** @var GameMatch $first */
        $first = $tieMatches->first();
        if ((int) $first->home_club_id === (int) $first->away_club_id) {
            return (int) $first->home_club_id;
        }

        if ($tieMatches->count() < 2) {
            return $this->resolveWinnerClubId($first);
        }

        $clubA = (int) $first->home_club_id;
        $clubB = (int) $first->away_club_id;
        $aggregate = [
            $clubA => 0,
            $clubB => 0,
        ];
        $awayGoals = [
            $clubA => 0,
            $clubB => 0,
        ];

        /** @var GameMatch $leg */
        foreach ($tieMatches as $leg) {
            $homeClubId = (int) $leg->home_club_id;
            $awayClubId = (int) $leg->away_club_id;

            $aggregate[$homeClubId] = (int) ($aggregate[$homeClubId] ?? 0) + (int) $leg->home_score;
            $aggregate[$awayClubId] = (int) ($aggregate[$awayClubId] ?? 0) + (int) $leg->away_score;
            $awayGoals[$awayClubId] = (int) ($awayGoals[$awayClubId] ?? 0) + (int) $leg->away_score;
        }

        if ($aggregate[$clubA] > $aggregate[$clubB]) {
            return $clubA;
        }
        if ($aggregate[$clubB] > $aggregate[$clubA]) {
            return $clubB;
        }

        if ($this->awayGoalsRuleEnabled()) {
            if ($awayGoals[$clubA] > $awayGoals[$clubB]) {
                return $clubA;
            }
            if ($awayGoals[$clubB] > $awayGoals[$clubA]) {
                return $clubB;
            }
        }

        /** @var GameMatch $decider */
        $decider = $tieMatches
            ->sort(function (GameMatch $left, GameMatch $right): int {
                $leftKickoff = $left->kickoff_at?->getTimestamp() ?? 0;
                $rightKickoff = $right->kickoff_at?->getTimestamp() ?? 0;

                if ($leftKickoff === $rightKickoff) {
                    return $left->id <=> $right->id;
                }

                return $leftKickoff <=> $rightKickoff;
            })
            ->last();

        if ($decider->penalties_home !== null && $decider->penalties_away !== null) {
            return (int) ($decider->penalties_home > $decider->penalties_away
                ? $decider->home_club_id
                : $decider->away_club_id);
        }

        return $this->resolveWinnerClubId($decider);
    }

    private function resolveWinnerClubId(GameMatch $match): ?int
    {
        $homeScore = (int) $match->home_score;
        $awayScore = (int) $match->away_score;

        if ($homeScore > $awayScore) {
            return (int) $match->home_club_id;
        }
        if ($awayScore > $homeScore) {
            return (int) $match->away_club_id;
        }

        if ($match->penalties_home !== null && $match->penalties_away !== null) {
            return (int) ($match->penalties_home > $match->penalties_away ? $match->home_club_id : $match->away_club_id);
        }

        $seed = (int) ($match->simulation_seed ?: (($match->id * 131) + ((int) ($match->round_number ?? 1) * 17)));

        return $seed % 2 === 0 ? (int) $match->home_club_id : (int) $match->away_club_id;
    }

    private function stageName(int $roundNumber, int $participantCount): string
    {
        return match ($participantCount) {
            2 => 'Finale',
            4 => 'Halbfinale',
            8 => 'Viertelfinale',
            16 => 'Achtelfinale',
            default => 'Cup Runde '.$roundNumber,
        };
    }

    private function tieKey(GameMatch $match): string
    {
        $homeClubId = (int) $match->home_club_id;
        $awayClubId = (int) $match->away_club_id;
        if ($homeClubId === $awayClubId) {
            return 'bye-'.$homeClubId;
        }

        $clubs = [$homeClubId, $awayClubId];
        sort($clubs);

        return $clubs[0].'-'.$clubs[1];
    }

    private function shouldGenerateTwoLeggedRound(int $participantCount): bool
    {
        if (!(bool) config('simulation.cup.two_legged.enabled', false)) {
            return false;
        }

        $minParticipants = max(2, (int) config('simulation.cup.two_legged.min_participants', 4));
        $maxParticipants = max($minParticipants, (int) config('simulation.cup.two_legged.max_participants', 16));

        return $participantCount >= $minParticipants && $participantCount <= $maxParticipants;
    }

    private function awayGoalsRuleEnabled(): bool
    {
        return (bool) config('simulation.cup.away_goals_rule', true);
    }

    private function daysBetweenLegs(): int
    {
        return max(1, min(14, (int) config('simulation.cup.two_legged.days_between_legs', 7)));
    }

    private function createCupMatch(
        CompetitionSeason $competitionSeason,
        string $competitionContext,
        int $roundNumber,
        string $stage,
        \Illuminate\Support\Carbon $kickoffAt,
        int $homeClubId,
        int $awayClubId
    ): void {
        GameMatch::query()->create([
            'competition_season_id' => $competitionSeason->id,
            'season_id' => $competitionSeason->season_id,
            'type' => 'cup',
            'competition_context' => $competitionContext,
            'stage' => $stage,
            'round_number' => $roundNumber,
            'kickoff_at' => $kickoffAt,
            'status' => 'scheduled',
            'home_club_id' => $homeClubId,
            'away_club_id' => $awayClubId,
            'stadium_club_id' => $homeClubId,
            'simulation_seed' => random_int(10000, 99999),
        ]);
    }
}
