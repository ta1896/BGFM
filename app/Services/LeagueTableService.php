<?php

namespace App\Services;

use App\Models\CompetitionSeason;
use App\Models\SeasonClubStatistic;
use Illuminate\Support\Collection;

class LeagueTableService
{
    public function rebuild(CompetitionSeason $competitionSeason): void
    {
        $clubIds = $competitionSeason->registrations()->pluck('club_id')->all();

        foreach ($clubIds as $clubId) {
            $stats = $this->calculateClub($competitionSeason->id, $clubId, $competitionSeason->points_win, $competitionSeason->points_draw);

            SeasonClubStatistic::updateOrCreate(
                [
                    'competition_season_id' => $competitionSeason->id,
                    'club_id' => $clubId,
                ],
                $stats
            );
        }
    }

    public function table(CompetitionSeason $competitionSeason): Collection
    {
        return $competitionSeason->statistics()
            ->with('club')
            ->orderByDesc('points')
            ->orderByDesc('goal_diff')
            ->orderByDesc('goals_for')
            ->get();
    }

    private function calculateClub(int $competitionSeasonId, int $clubId, int $pointsWin, int $pointsDraw): array
    {
        $homeMatches = \App\Models\GameMatch::query()
            ->where('competition_season_id', $competitionSeasonId)
            ->where('status', 'played')
            ->where('home_club_id', $clubId)
            ->get();

        $awayMatches = \App\Models\GameMatch::query()
            ->where('competition_season_id', $competitionSeasonId)
            ->where('status', 'played')
            ->where('away_club_id', $clubId)
            ->get();

        $wins = 0;
        $draws = 0;
        $losses = 0;
        $goalsFor = 0;
        $goalsAgainst = 0;
        $homePoints = 0;
        $awayPoints = 0;
        $form = [];

        foreach ($homeMatches as $match) {
            $gf = (int) $match->home_score;
            $ga = (int) $match->away_score;
            $goalsFor += $gf;
            $goalsAgainst += $ga;

            if ($gf > $ga) {
                $wins++;
                $homePoints += $pointsWin;
                $form[] = 'W';
            } elseif ($gf === $ga) {
                $draws++;
                $homePoints += $pointsDraw;
                $form[] = 'D';
            } else {
                $losses++;
                $form[] = 'L';
            }
        }

        foreach ($awayMatches as $match) {
            $gf = (int) $match->away_score;
            $ga = (int) $match->home_score;
            $goalsFor += $gf;
            $goalsAgainst += $ga;

            if ($gf > $ga) {
                $wins++;
                $awayPoints += $pointsWin;
                $form[] = 'W';
            } elseif ($gf === $ga) {
                $draws++;
                $awayPoints += $pointsDraw;
                $form[] = 'D';
            } else {
                $losses++;
                $form[] = 'L';
            }
        }

        $matchesPlayed = count($homeMatches) + count($awayMatches);
        $points = ($wins * $pointsWin) + ($draws * $pointsDraw);
        $formLast5 = implode('', array_slice($form, -5));

        return [
            'matches_played' => $matchesPlayed,
            'wins' => $wins,
            'draws' => $draws,
            'losses' => $losses,
            'goals_for' => $goalsFor,
            'goals_against' => $goalsAgainst,
            'goal_diff' => $goalsFor - $goalsAgainst,
            'points' => $points,
            'home_points' => $homePoints,
            'away_points' => $awayPoints,
            'form_last5' => $formLast5,
        ];
    }
}
