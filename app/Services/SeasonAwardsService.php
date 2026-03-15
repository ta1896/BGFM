<?php

namespace App\Services;

use App\Models\CompetitionSeason;
use App\Models\MatchPlayerStat;
use App\Models\SeasonAward;
use App\Models\SeasonClubStatistic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SeasonAwardsService
{
    public function generateForCompetitionSeason(CompetitionSeason $competitionSeason): Collection
    {
        $competitionSeason->loadMissing(['season', 'competition']);

        $matchIds = DB::table('matches')
            ->where('competition_season_id', $competitionSeason->id)
            ->where('status', 'played')
            ->pluck('id');

        $playerStats = collect();
        if ($matchIds->isNotEmpty()) {
            $playerStats = MatchPlayerStat::query()
                ->with(['player.club.user', 'club'])
                ->select(
                    'player_id',
                    'club_id',
                    DB::raw('SUM(goals) as total_goals'),
                    DB::raw('SUM(assists) as total_assists'),
                    DB::raw('SUM(minutes_played) as total_minutes'),
                    DB::raw('AVG(rating) as avg_rating')
                )
                ->whereIn('match_id', $matchIds)
                ->groupBy('player_id', 'club_id')
                ->get();
        }

        $clubStats = SeasonClubStatistic::query()
            ->with('club.user')
            ->where('competition_season_id', $competitionSeason->id)
            ->orderByDesc('points')
            ->orderByDesc('goal_diff')
            ->orderByDesc('goals_for')
            ->get();

        $awards = collect([
            $this->resolveGoldenBoot($competitionSeason, $playerStats),
            $this->resolvePlaymaker($competitionSeason, $playerStats),
            $this->resolvePlayerOfTheSeason($competitionSeason, $playerStats),
            $this->resolveBestU21($competitionSeason, $playerStats),
            $this->resolveClubOfTheSeason($competitionSeason, $clubStats),
            $this->resolveManagerOfTheSeason($competitionSeason, $clubStats),
        ])->filter();

        foreach ($awards as $award) {
            SeasonAward::query()->updateOrCreate(
                [
                    'competition_season_id' => $competitionSeason->id,
                    'award_key' => $award['award_key'],
                ],
                $award
            );
        }

        return SeasonAward::query()
            ->with(['player.club', 'club', 'user'])
            ->where('competition_season_id', $competitionSeason->id)
            ->orderBy('label')
            ->get();
    }

    private function resolveGoldenBoot(CompetitionSeason $season, Collection $playerStats): ?array
    {
        $winner = $playerStats
            ->where('total_goals', '>', 0)
            ->sortBy([
                ['total_goals', 'desc'],
                ['total_minutes', 'asc'],
            ])
            ->first();

        return $winner ? $this->playerAward(
            $season,
            'golden_boot',
            'Golden Boot',
            $winner,
            (float) $winner->total_goals,
            (string) $winner->total_goals.' Tore'
        ) : null;
    }

    private function resolvePlaymaker(CompetitionSeason $season, Collection $playerStats): ?array
    {
        $winner = $playerStats
            ->where('total_assists', '>', 0)
            ->sortBy([
                ['total_assists', 'desc'],
                ['total_minutes', 'asc'],
            ])
            ->first();

        return $winner ? $this->playerAward(
            $season,
            'playmaker',
            'Playmaker des Jahres',
            $winner,
            (float) $winner->total_assists,
            (string) $winner->total_assists.' Assists'
        ) : null;
    }

    private function resolvePlayerOfTheSeason(CompetitionSeason $season, Collection $playerStats): ?array
    {
        $winner = $playerStats
            ->where('total_minutes', '>=', 360)
            ->sortByDesc(function ($entry) {
                return ((float) $entry->avg_rating * 12) + ((int) $entry->total_goals * 4) + ((int) $entry->total_assists * 3);
            })
            ->first();

        return $winner ? $this->playerAward(
            $season,
            'player_of_the_season',
            'Spieler der Saison',
            $winner,
            (float) $winner->avg_rating,
            number_format((float) $winner->avg_rating, 2).' Rating'
        ) : null;
    }

    private function resolveBestU21(CompetitionSeason $season, Collection $playerStats): ?array
    {
        $winner = $playerStats
            ->filter(fn ($entry) => $entry->player && (int) $entry->player->age <= 21 && (int) $entry->total_minutes >= 180)
            ->sortByDesc(function ($entry) {
                return ((float) $entry->avg_rating * 10) + ((int) $entry->total_goals * 3) + ((int) $entry->total_assists * 2);
            })
            ->first();

        return $winner ? $this->playerAward(
            $season,
            'best_u21',
            'Bester U21-Spieler',
            $winner,
            (float) $winner->avg_rating,
            'U21 / '.number_format((float) $winner->avg_rating, 2).' Rating'
        ) : null;
    }

    private function resolveClubOfTheSeason(CompetitionSeason $season, Collection $clubStats): ?array
    {
        $winner = $clubStats->first();
        if (!$winner) {
            return null;
        }

        return [
            'competition_season_id' => $season->id,
            'award_key' => 'club_of_the_season',
            'label' => 'Verein der Saison',
            'player_id' => null,
            'club_id' => $winner->club_id,
            'user_id' => $winner->club?->user_id,
            'value_numeric' => (float) $winner->points,
            'value_label' => $winner->points.' Punkte',
            'summary' => 'Beste Saisonbilanz in '.$season->competition?->name.' mit '.$winner->goal_diff.' Toren Differenz.',
        ];
    }

    private function resolveManagerOfTheSeason(CompetitionSeason $season, Collection $clubStats): ?array
    {
        $winner = $clubStats->first(fn ($entry) => $entry->club?->user_id);
        if (!$winner) {
            return null;
        }

        return [
            'competition_season_id' => $season->id,
            'award_key' => 'manager_of_the_season',
            'label' => 'Manager der Saison',
            'player_id' => null,
            'club_id' => $winner->club_id,
            'user_id' => $winner->club?->user_id,
            'value_numeric' => (float) $winner->points,
            'value_label' => $winner->club?->user?->name ?? 'Manager',
            'summary' => 'Fuehrte '.$winner->club?->name.' zur besten Platzierung der Saison.',
        ];
    }

    private function playerAward(
        CompetitionSeason $season,
        string $awardKey,
        string $label,
        object $winner,
        float $valueNumeric,
        string $valueLabel
    ): array {
        return [
            'competition_season_id' => $season->id,
            'award_key' => $awardKey,
            'label' => $label,
            'player_id' => $winner->player_id,
            'club_id' => $winner->club_id,
            'user_id' => $winner->player?->club?->user_id,
            'value_numeric' => $valueNumeric,
            'value_label' => $valueLabel,
            'summary' => $winner->player?->full_name.' ueberzeugte fuer '.$winner->club?->name.'.',
        ];
    }
}
