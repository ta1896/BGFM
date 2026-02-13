<?php

namespace App\Services;

use App\Models\Club;
use App\Models\CompetitionSeason;
use App\Models\GameMatch;
use App\Models\SeasonClubStatistic;
use Illuminate\Support\Facades\DB;

class StatisticsAggregationService
{
    public function __construct(
        private readonly CompetitionContextService $contextService
    ) {
    }

    public function rebuildLeagueTable(CompetitionSeason $competitionSeason): void
    {
        $rowsByClubId = $this->calculateLeagueRows($competitionSeason);

        DB::transaction(function () use ($competitionSeason, $rowsByClubId): void {
            SeasonClubStatistic::query()
                ->where('competition_season_id', $competitionSeason->id)
                ->whereNotIn('club_id', array_keys($rowsByClubId))
                ->delete();

            if ($rowsByClubId === []) {
                return;
            }

            SeasonClubStatistic::query()->upsert(
                array_values($rowsByClubId),
                ['competition_season_id', 'club_id'],
                [
                    'matches_played',
                    'wins',
                    'draws',
                    'losses',
                    'goals_for',
                    'goals_against',
                    'goal_diff',
                    'points',
                    'home_points',
                    'away_points',
                    'form_last5',
                    'updated_at',
                ]
            );
        });
    }

    /**
     * @return array{
     *   matches:int,
     *   wins:int,
     *   draws:int,
     *   losses:int,
     *   goals_for:int,
     *   goals_against:int,
     *   points:int
     * }
     */
    public function clubSummaryForClub(Club $club, ?int $seasonId = null): array
    {
        $matches = GameMatch::query()
            ->where('status', 'played')
            ->when($seasonId !== null, fn ($query) => $query->where('season_id', $seasonId))
            ->where(function ($query) use ($club): void {
                $query->where('home_club_id', $club->id)
                    ->orWhere('away_club_id', $club->id);
            })
            ->get(['home_club_id', 'away_club_id', 'home_score', 'away_score']);

        $summary = $this->blankClubSummary();

        foreach ($matches as $match) {
            $isHome = (int) $match->home_club_id === (int) $club->id;
            $gf = (int) ($isHome ? $match->home_score : $match->away_score);
            $ga = (int) ($isHome ? $match->away_score : $match->home_score);
            $this->applyClubResult($summary, $gf, $ga);
        }

        return $summary;
    }

    /**
     * @return array<string, array{
     *   matches:int,
     *   wins:int,
     *   draws:int,
     *   losses:int,
     *   goals_for:int,
     *   goals_against:int,
     *   points:int
     * }>
     */
    public function clubSummaryByContextForClub(Club $club, ?int $seasonId = null): array
    {
        $summaryByContext = [];
        foreach ($this->contextService->allContexts() as $context) {
            $summaryByContext[$context] = $this->blankClubSummary();
        }

        $rows = DB::table('matches as m')
            ->leftJoin('competition_seasons as cs', 'cs.id', '=', 'm.competition_season_id')
            ->leftJoin('competitions as c', 'c.id', '=', 'cs.competition_id')
            ->where('m.status', 'played')
            ->when($seasonId !== null, fn ($query) => $query->where('m.season_id', $seasonId))
            ->where(function ($query) use ($club): void {
                $query->where('m.home_club_id', $club->id)
                    ->orWhere('m.away_club_id', $club->id);
            })
            ->select([
                'm.home_club_id',
                'm.away_club_id',
                'm.home_score',
                'm.away_score',
                'm.type as match_type',
                'm.competition_context as match_context',
                'c.country_id as competition_country_id',
                'c.scope as competition_scope',
            ])
            ->get();

        foreach ($rows as $row) {
            $context = $this->contextService->fromStoredOrRaw(
                $row->match_context ? (string) $row->match_context : null,
                (string) $row->match_type,
                $row->competition_country_id !== null ? (int) $row->competition_country_id : null,
                $row->competition_scope ? (string) $row->competition_scope : null
            );

            if (!isset($summaryByContext[$context])) {
                $summaryByContext[$context] = $this->blankClubSummary();
            }

            $isHome = (int) $row->home_club_id === (int) $club->id;
            $gf = (int) ($isHome ? $row->home_score : $row->away_score);
            $ga = (int) ($isHome ? $row->away_score : $row->home_score);
            $this->applyClubResult($summaryByContext[$context], $gf, $ga);
        }

        return $summaryByContext;
    }

    /**
     * @return array<int, array{
     *   season_id:int,
     *   season_name:string,
     *   matches:int,
     *   wins:int,
     *   draws:int,
     *   losses:int,
     *   goals_for:int,
     *   goals_against:int,
     *   points:int
     * }>
     */
    public function clubSeasonHistoryForClub(Club $club, int $limit = 5): array
    {
        $matches = DB::table('matches as m')
            ->join('seasons as s', 's.id', '=', 'm.season_id')
            ->where('m.status', 'played')
            ->whereNotNull('m.season_id')
            ->where(function ($query) use ($club): void {
                $query->where('m.home_club_id', $club->id)
                    ->orWhere('m.away_club_id', $club->id);
            })
            ->select([
                'm.season_id',
                's.name as season_name',
                'm.home_club_id',
                'm.away_club_id',
                'm.home_score',
                'm.away_score',
            ])
            ->orderByDesc('m.season_id')
            ->get();

        $history = [];
        foreach ($matches as $match) {
            $seasonId = (int) $match->season_id;
            if (!isset($history[$seasonId])) {
                $history[$seasonId] = array_merge(
                    [
                        'season_id' => $seasonId,
                        'season_name' => (string) $match->season_name,
                    ],
                    $this->blankClubSummary()
                );
            }

            $isHome = (int) $match->home_club_id === (int) $club->id;
            $gf = (int) ($isHome ? $match->home_score : $match->away_score);
            $ga = (int) ($isHome ? $match->away_score : $match->home_score);
            $this->applyClubResult($history[$seasonId], $gf, $ga);
        }

        return collect($history)
            ->sortByDesc(fn (array $row): int => (int) $row['season_id'])
            ->take(max(1, $limit))
            ->values()
            ->all();
    }

    public function rebuildPlayerCompetitionStatsForMatch(GameMatch $match): void
    {
        $playerIds = DB::table('match_player_stats')
            ->where('match_id', $match->id)
            ->pluck('player_id')
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        $this->rebuildPlayerCompetitionStatsForPlayers($playerIds);
    }

    public function rebuildAllPlayerCompetitionStats(): void
    {
        $playerIds = DB::table('match_player_stats as mps')
            ->join('matches as m', 'm.id', '=', 'mps.match_id')
            ->where('m.status', 'played')
            ->distinct()
            ->pluck('mps.player_id')
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->values()
            ->all();

        $this->rebuildPlayerCompetitionStatsForPlayers($playerIds);
    }

    /**
     * @param array<int, int> $playerIds
     */
    public function rebuildPlayerCompetitionStatsForPlayers(array $playerIds): void
    {
        $playerIds = collect($playerIds)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
        if ($playerIds === []) {
            return;
        }

        $rows = DB::table('match_player_stats as mps')
            ->join('matches as m', 'm.id', '=', 'mps.match_id')
            ->leftJoin('competition_seasons as cs', 'cs.id', '=', 'm.competition_season_id')
            ->leftJoin('competitions as c', 'c.id', '=', 'cs.competition_id')
            ->whereIn('mps.player_id', $playerIds)
            ->where('m.status', 'played')
            ->select([
                'mps.player_id',
                'mps.minutes_played',
                'mps.goals',
                'mps.assists',
                'mps.yellow_cards',
                'mps.red_cards',
                'm.type as match_type',
                'm.competition_context as match_context',
                'm.season_id as match_season_id',
                'cs.season_id as competition_season_id',
                'c.country_id as competition_country_id',
                'c.scope as competition_scope',
            ])
            ->get();

        $now = now();
        $seasonRows = [];
        $careerRows = [];

        foreach ($rows as $row) {
            $playerId = (int) $row->player_id;
            if ($playerId < 1) {
                continue;
            }

            $context = $this->contextService->fromStoredOrRaw(
                $row->match_context ? (string) $row->match_context : null,
                (string) $row->match_type,
                $row->competition_country_id !== null ? (int) $row->competition_country_id : null,
                $row->competition_scope ? (string) $row->competition_scope : null
            );
            $seasonId = (int) ($row->match_season_id ?? $row->competition_season_id ?? 0);
            if ($seasonId < 1) {
                continue;
            }

            $seasonKey = $playerId.'|'.$seasonId.'|'.$context;
            if (!isset($seasonRows[$seasonKey])) {
                $seasonRows[$seasonKey] = [
                    'player_id' => $playerId,
                    'season_id' => $seasonId,
                    'competition_context' => $context,
                    'appearances' => 0,
                    'minutes_played' => 0,
                    'goals' => 0,
                    'assists' => 0,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $seasonRows[$seasonKey]['appearances']++;
            $seasonRows[$seasonKey]['minutes_played'] += (int) $row->minutes_played;
            $seasonRows[$seasonKey]['goals'] += (int) $row->goals;
            $seasonRows[$seasonKey]['assists'] += (int) $row->assists;
            $seasonRows[$seasonKey]['yellow_cards'] += (int) $row->yellow_cards;
            $seasonRows[$seasonKey]['red_cards'] += (int) $row->red_cards;

            $careerKey = $playerId.'|'.$context;
            if (!isset($careerRows[$careerKey])) {
                $careerRows[$careerKey] = [
                    'player_id' => $playerId,
                    'competition_context' => $context,
                    'appearances' => 0,
                    'minutes_played' => 0,
                    'goals' => 0,
                    'assists' => 0,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $careerRows[$careerKey]['appearances']++;
            $careerRows[$careerKey]['minutes_played'] += (int) $row->minutes_played;
            $careerRows[$careerKey]['goals'] += (int) $row->goals;
            $careerRows[$careerKey]['assists'] += (int) $row->assists;
            $careerRows[$careerKey]['yellow_cards'] += (int) $row->yellow_cards;
            $careerRows[$careerKey]['red_cards'] += (int) $row->red_cards;
        }

        DB::transaction(function () use ($playerIds, $seasonRows, $careerRows): void {
            DB::table('player_season_competition_statistics')
                ->whereIn('player_id', $playerIds)
                ->delete();

            DB::table('player_career_competition_statistics')
                ->whereIn('player_id', $playerIds)
                ->delete();

            if ($seasonRows !== []) {
                DB::table('player_season_competition_statistics')->insert(array_values($seasonRows));
            }

            if ($careerRows !== []) {
                DB::table('player_career_competition_statistics')->insert(array_values($careerRows));
            }
        });
    }

    /**
     * @return array<string, int>
     */
    public function auditStatisticsIntegrity(?int $competitionSeasonId = null): array
    {
        $competitionSeasons = CompetitionSeason::query()
            ->when($competitionSeasonId !== null, fn ($query) => $query->whereKey($competitionSeasonId))
            ->get();

        $leagueRowsMissing = 0;
        $leagueRowsMismatch = 0;
        $leagueRowsExtra = 0;

        foreach ($competitionSeasons as $competitionSeason) {
            $expectedRows = $this->calculateLeagueRows($competitionSeason);
            $existingRows = SeasonClubStatistic::query()
                ->where('competition_season_id', $competitionSeason->id)
                ->get()
                ->keyBy(fn (SeasonClubStatistic $stat): int => (int) $stat->club_id);

            foreach ($expectedRows as $clubId => $expected) {
                $existing = $existingRows->get($clubId);
                if (!$existing) {
                    $leagueRowsMissing++;
                    continue;
                }

                $fields = [
                    'matches_played',
                    'wins',
                    'draws',
                    'losses',
                    'goals_for',
                    'goals_against',
                    'goal_diff',
                    'points',
                    'home_points',
                    'away_points',
                    'form_last5',
                ];

                foreach ($fields as $field) {
                    if ((string) ($expected[$field] ?? '') !== (string) ($existing->{$field} ?? '')) {
                        $leagueRowsMismatch++;
                        break;
                    }
                }
            }

            foreach ($existingRows as $existingRow) {
                if (!isset($expectedRows[(int) $existingRow->club_id])) {
                    $leagueRowsExtra++;
                }
            }
        }

        $playerAudit = $this->auditPlayerCompetitionStats();

        return [
            'competition_seasons_scanned' => $competitionSeasons->count(),
            'league_rows_missing' => $leagueRowsMissing,
            'league_rows_mismatch' => $leagueRowsMismatch,
            'league_rows_extra' => $leagueRowsExtra,
            'player_season_rows_missing' => $playerAudit['season_missing'],
            'player_season_rows_mismatch' => $playerAudit['season_mismatch'],
            'player_season_rows_extra' => $playerAudit['season_extra'],
            'player_career_rows_missing' => $playerAudit['career_missing'],
            'player_career_rows_mismatch' => $playerAudit['career_mismatch'],
            'player_career_rows_extra' => $playerAudit['career_extra'],
        ];
    }

    /**
     * @return array{
     *   season_missing:int,
     *   season_mismatch:int,
     *   season_extra:int,
     *   career_missing:int,
     *   career_mismatch:int,
     *   career_extra:int
     * }
     */
    private function auditPlayerCompetitionStats(): array
    {
        $rows = DB::table('match_player_stats as mps')
            ->join('matches as m', 'm.id', '=', 'mps.match_id')
            ->leftJoin('competition_seasons as cs', 'cs.id', '=', 'm.competition_season_id')
            ->leftJoin('competitions as c', 'c.id', '=', 'cs.competition_id')
            ->where('m.status', 'played')
            ->select([
                'mps.player_id',
                'mps.minutes_played',
                'mps.goals',
                'mps.assists',
                'mps.yellow_cards',
                'mps.red_cards',
                'm.type as match_type',
                'm.competition_context as match_context',
                'm.season_id as match_season_id',
                'cs.season_id as competition_season_id',
                'c.country_id as competition_country_id',
                'c.scope as competition_scope',
            ])
            ->get();

        $expectedSeason = [];
        $expectedCareer = [];
        foreach ($rows as $row) {
            $playerId = (int) $row->player_id;
            if ($playerId < 1) {
                continue;
            }

            $context = $this->contextService->fromStoredOrRaw(
                $row->match_context ? (string) $row->match_context : null,
                (string) $row->match_type,
                $row->competition_country_id !== null ? (int) $row->competition_country_id : null,
                $row->competition_scope ? (string) $row->competition_scope : null
            );
            $seasonId = (int) ($row->match_season_id ?? $row->competition_season_id ?? 0);
            if ($seasonId < 1) {
                continue;
            }

            $seasonKey = $playerId.'|'.$seasonId.'|'.$context;
            if (!isset($expectedSeason[$seasonKey])) {
                $expectedSeason[$seasonKey] = [
                    'appearances' => 0,
                    'minutes_played' => 0,
                    'goals' => 0,
                    'assists' => 0,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                ];
            }
            $expectedSeason[$seasonKey]['appearances']++;
            $expectedSeason[$seasonKey]['minutes_played'] += (int) $row->minutes_played;
            $expectedSeason[$seasonKey]['goals'] += (int) $row->goals;
            $expectedSeason[$seasonKey]['assists'] += (int) $row->assists;
            $expectedSeason[$seasonKey]['yellow_cards'] += (int) $row->yellow_cards;
            $expectedSeason[$seasonKey]['red_cards'] += (int) $row->red_cards;

            $careerKey = $playerId.'|'.$context;
            if (!isset($expectedCareer[$careerKey])) {
                $expectedCareer[$careerKey] = [
                    'appearances' => 0,
                    'minutes_played' => 0,
                    'goals' => 0,
                    'assists' => 0,
                    'yellow_cards' => 0,
                    'red_cards' => 0,
                ];
            }
            $expectedCareer[$careerKey]['appearances']++;
            $expectedCareer[$careerKey]['minutes_played'] += (int) $row->minutes_played;
            $expectedCareer[$careerKey]['goals'] += (int) $row->goals;
            $expectedCareer[$careerKey]['assists'] += (int) $row->assists;
            $expectedCareer[$careerKey]['yellow_cards'] += (int) $row->yellow_cards;
            $expectedCareer[$careerKey]['red_cards'] += (int) $row->red_cards;
        }

        $storedSeason = DB::table('player_season_competition_statistics')
            ->get()
            ->keyBy(fn ($row) => (int) $row->player_id.'|'.(int) $row->season_id.'|'.(string) $row->competition_context);
        $storedCareer = DB::table('player_career_competition_statistics')
            ->get()
            ->keyBy(fn ($row) => (int) $row->player_id.'|'.(string) $row->competition_context);

        [$seasonMissing, $seasonMismatch, $seasonExtra] = $this->compareExpectedAndStored($expectedSeason, $storedSeason);
        [$careerMissing, $careerMismatch, $careerExtra] = $this->compareExpectedAndStored($expectedCareer, $storedCareer);

        return [
            'season_missing' => $seasonMissing,
            'season_mismatch' => $seasonMismatch,
            'season_extra' => $seasonExtra,
            'career_missing' => $careerMissing,
            'career_mismatch' => $careerMismatch,
            'career_extra' => $careerExtra,
        ];
    }

    /**
     * @param array<string, array<string, int>> $expected
     * @return array{int,int,int}
     */
    private function compareExpectedAndStored(array $expected, \Illuminate\Support\Collection $stored): array
    {
        $missing = 0;
        $mismatch = 0;
        $extra = 0;
        $fields = ['appearances', 'minutes_played', 'goals', 'assists', 'yellow_cards', 'red_cards'];

        foreach ($expected as $key => $values) {
            $storedRow = $stored->get($key);
            if (!$storedRow) {
                $missing++;
                continue;
            }

            foreach ($fields as $field) {
                if ((int) ($storedRow->{$field} ?? 0) !== (int) ($values[$field] ?? 0)) {
                    $mismatch++;
                    break;
                }
            }
        }

        foreach ($stored as $key => $storedRow) {
            if (!isset($expected[(string) $key])) {
                $extra++;
            }
        }

        return [$missing, $mismatch, $extra];
    }

    /**
     * @return array{
     *   matches:int,
     *   wins:int,
     *   draws:int,
     *   losses:int,
     *   goals_for:int,
     *   goals_against:int,
     *   points:int
     * }
     */
    private function blankClubSummary(): array
    {
        return [
            'matches' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'points' => 0,
        ];
    }

    /**
     * @param array{
     *   matches:int,
     *   wins:int,
     *   draws:int,
     *   losses:int,
     *   goals_for:int,
     *   goals_against:int,
     *   points:int
     * } $summary
     */
    private function applyClubResult(array &$summary, int $goalsFor, int $goalsAgainst): void
    {
        $summary['matches']++;
        $summary['goals_for'] += $goalsFor;
        $summary['goals_against'] += $goalsAgainst;

        if ($goalsFor > $goalsAgainst) {
            $summary['wins']++;
            $summary['points'] += 3;

            return;
        }

        if ($goalsFor === $goalsAgainst) {
            $summary['draws']++;
            $summary['points'] += 1;

            return;
        }

        $summary['losses']++;
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function calculateLeagueRows(CompetitionSeason $competitionSeason): array
    {
        $clubIds = $competitionSeason->registrations()
            ->pluck('club_id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        if ($clubIds === []) {
            return [];
        }

        $stats = [];
        $forms = [];
        foreach ($clubIds as $clubId) {
            $stats[$clubId] = [
                'competition_season_id' => $competitionSeason->id,
                'club_id' => $clubId,
                'matches_played' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_diff' => 0,
                'points' => 0,
                'home_points' => 0,
                'away_points' => 0,
                'form_last5' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $forms[$clubId] = [];
        }

        $matches = GameMatch::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->where('status', 'played')
            ->orderByRaw('COALESCE(played_at, kickoff_at)')
            ->orderBy('id')
            ->get(['home_club_id', 'away_club_id', 'home_score', 'away_score']);

        foreach ($matches as $match) {
            $homeClubId = (int) $match->home_club_id;
            $awayClubId = (int) $match->away_club_id;
            if (!isset($stats[$homeClubId]) || !isset($stats[$awayClubId])) {
                continue;
            }

            $homeGoals = (int) $match->home_score;
            $awayGoals = (int) $match->away_score;

            $this->registerClubMatchResult(
                $stats[$homeClubId],
                $forms[$homeClubId],
                $homeGoals,
                $awayGoals,
                $competitionSeason->points_win,
                $competitionSeason->points_draw,
                true
            );

            $this->registerClubMatchResult(
                $stats[$awayClubId],
                $forms[$awayClubId],
                $awayGoals,
                $homeGoals,
                $competitionSeason->points_win,
                $competitionSeason->points_draw,
                false
            );
        }

        foreach ($stats as $clubId => $row) {
            $row['goal_diff'] = (int) $row['goals_for'] - (int) $row['goals_against'];
            $row['form_last5'] = implode('', array_slice($forms[$clubId], -5));
            $stats[$clubId] = $row;
        }

        return $stats;
    }

    /**
     * @param array<string, int|string> $row
     * @param array<int, string> $form
     */
    private function registerClubMatchResult(
        array &$row,
        array &$form,
        int $goalsFor,
        int $goalsAgainst,
        int $pointsWin,
        int $pointsDraw,
        bool $isHome
    ): void {
        $row['matches_played'] = (int) $row['matches_played'] + 1;
        $row['goals_for'] = (int) $row['goals_for'] + $goalsFor;
        $row['goals_against'] = (int) $row['goals_against'] + $goalsAgainst;

        if ($goalsFor > $goalsAgainst) {
            $row['wins'] = (int) $row['wins'] + 1;
            $row['points'] = (int) $row['points'] + $pointsWin;
            if ($isHome) {
                $row['home_points'] = (int) $row['home_points'] + $pointsWin;
            } else {
                $row['away_points'] = (int) $row['away_points'] + $pointsWin;
            }
            $form[] = 'W';

            return;
        }

        if ($goalsFor === $goalsAgainst) {
            $row['draws'] = (int) $row['draws'] + 1;
            $row['points'] = (int) $row['points'] + $pointsDraw;
            if ($isHome) {
                $row['home_points'] = (int) $row['home_points'] + $pointsDraw;
            } else {
                $row['away_points'] = (int) $row['away_points'] + $pointsDraw;
            }
            $form[] = 'D';

            return;
        }

        $row['losses'] = (int) $row['losses'] + 1;
        $form[] = 'L';
    }
}
