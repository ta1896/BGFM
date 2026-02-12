<?php

namespace App\Services;

use App\Models\CompetitionSeason;
use App\Models\MatchPlayerStat;
use App\Models\TeamOfTheDay;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeamOfTheDayService
{
    public function generateForDate(CarbonInterface $date, ?User $actor = null): TeamOfTheDay
    {
        $dateString = $date->toDateString();

        $stats = $this->statsForDate($dateString);
        $context = 'date';
        $notes = null;

        if ($stats->isEmpty()) {
            $stats = $this->statsForRecentDays($dateString, 7);
            $context = 'date_fallback_recent';
            $notes = 'Kein Spieltag am Datum, daher letzte 7 Tage verwendet.';
        }

        $entries = $this->buildEntriesFromStats($stats);

        return DB::transaction(function () use ($dateString, $actor, $context, $notes, $entries): TeamOfTheDay {
            $teamOfTheDay = TeamOfTheDay::query()->updateOrCreate(
                [
                    'for_date' => $dateString,
                    'competition_season_id' => null,
                    'matchday' => null,
                ],
                [
                    'label' => 'Team des Tages '.$dateString,
                    'formation' => '4-3-3',
                    'generated_by_user_id' => $actor?->id,
                    'generation_context' => $context,
                    'notes' => $notes,
                ]
            );

            $teamOfTheDay->players()->delete();
            if (!empty($entries)) {
                $teamOfTheDay->players()->createMany($entries);
            }

            return $teamOfTheDay->fresh(['competitionSeason.competition', 'competitionSeason.season', 'players.player.club']);
        });
    }

    public function generateForCompetitionMatchday(
        CompetitionSeason $competitionSeason,
        int $matchday,
        ?User $actor = null
    ): TeamOfTheDay {
        $competitionSeason->loadMissing(['competition', 'season']);
        $stats = $this->statsForCompetitionMatchday($competitionSeason, $matchday);

        if ($stats->isEmpty()) {
            $date = now();

            return TeamOfTheDay::query()->updateOrCreate(
                [
                    'competition_season_id' => $competitionSeason->id,
                    'matchday' => $matchday,
                    'generation_context' => 'matchday',
                ],
                [
                    'for_date' => $date->toDateString(),
                    'label' => $this->matchdayLabel($competitionSeason, $matchday),
                    'formation' => '4-3-3',
                    'generated_by_user_id' => $actor?->id,
                    'notes' => 'Keine bewerteten Spielerstatistiken fuer diesen Spieltag vorhanden.',
                ]
            );
        }

        $forDate = $this->resolveReferenceDate($stats)->toDateString();
        $entries = $this->buildEntriesFromStats($stats);

        return DB::transaction(function () use ($competitionSeason, $matchday, $actor, $forDate, $entries): TeamOfTheDay {
            $teamOfTheDay = TeamOfTheDay::query()->updateOrCreate(
                [
                    'competition_season_id' => $competitionSeason->id,
                    'matchday' => $matchday,
                    'generation_context' => 'matchday',
                ],
                [
                    'for_date' => $forDate,
                    'label' => $this->matchdayLabel($competitionSeason, $matchday),
                    'formation' => '4-3-3',
                    'generated_by_user_id' => $actor?->id,
                    'notes' => null,
                ]
            );

            $teamOfTheDay->players()->delete();
            if (!empty($entries)) {
                $teamOfTheDay->players()->createMany($entries);
            }

            return $teamOfTheDay->fresh(['competitionSeason.competition', 'competitionSeason.season', 'players.player.club']);
        });
    }

    /**
     * @return Collection<int, MatchPlayerStat>
     */
    private function statsForDate(string $date): Collection
    {
        return MatchPlayerStat::query()
            ->with(['player', 'club', 'match'])
            ->whereNotNull('rating')
            ->whereHas('match', function ($query) use ($date): void {
                $query->where('status', 'played')
                    ->whereDate(DB::raw('COALESCE(played_at, kickoff_at)'), $date);
            })
            ->orderByDesc('rating')
            ->orderByDesc('goals')
            ->orderByDesc('assists')
            ->orderByDesc('minutes_played')
            ->get();
    }

    /**
     * @return Collection<int, MatchPlayerStat>
     */
    private function statsForRecentDays(string $date, int $days): Collection
    {
        $from = Carbon::parse($date)->subDays(max(1, $days - 1))->toDateString();

        return MatchPlayerStat::query()
            ->with(['player', 'club', 'match'])
            ->whereNotNull('rating')
            ->whereHas('match', function ($query) use ($from, $date): void {
                $query->where('status', 'played')
                    ->whereBetween(DB::raw('DATE(COALESCE(played_at, kickoff_at))'), [$from, $date]);
            })
            ->orderByDesc('rating')
            ->orderByDesc('goals')
            ->orderByDesc('assists')
            ->orderByDesc('minutes_played')
            ->get();
    }

    /**
     * @return Collection<int, MatchPlayerStat>
     */
    private function statsForCompetitionMatchday(CompetitionSeason $competitionSeason, int $matchday): Collection
    {
        return MatchPlayerStat::query()
            ->with(['player', 'club', 'match'])
            ->whereNotNull('rating')
            ->whereHas('match', function ($query) use ($competitionSeason, $matchday): void {
                $query->where('status', 'played')
                    ->where('competition_season_id', $competitionSeason->id)
                    ->where('matchday', $matchday);
            })
            ->orderByDesc('rating')
            ->orderByDesc('goals')
            ->orderByDesc('assists')
            ->orderByDesc('minutes_played')
            ->get();
    }

    /**
     * @param Collection<int, MatchPlayerStat> $stats
     * @return array<int, array<string, mixed>>
     */
    private function buildEntriesFromStats(Collection $stats): array
    {
        $slots = [
            'GK1' => 'GK',
            'DEF1' => 'DEF',
            'DEF2' => 'DEF',
            'DEF3' => 'DEF',
            'DEF4' => 'DEF',
            'MID1' => 'MID',
            'MID2' => 'MID',
            'MID3' => 'MID',
            'FWD1' => 'FWD',
            'FWD2' => 'FWD',
            'FWD3' => 'FWD',
        ];

        $usedPlayerIds = [];
        $entries = [];

        foreach ($slots as $slot => $group) {
            $candidate = $stats
                ->first(function (MatchPlayerStat $stat) use ($group, $usedPlayerIds): bool {
                    return !in_array($stat->player_id, $usedPlayerIds, true)
                        && $this->positionGroupForStat($stat) === $group;
                });

            if (!$candidate) {
                $candidate = $stats
                    ->first(fn (MatchPlayerStat $stat): bool => !in_array($stat->player_id, $usedPlayerIds, true));
            }

            if (!$candidate) {
                continue;
            }

            $usedPlayerIds[] = (int) $candidate->player_id;
            $entries[] = [
                'player_id' => $candidate->player_id,
                'club_id' => $candidate->club_id,
                'position_code' => $slot,
                'rating' => $candidate->rating,
                'stats_snapshot' => [
                    'goals' => (int) $candidate->goals,
                    'assists' => (int) $candidate->assists,
                    'minutes_played' => (int) $candidate->minutes_played,
                    'yellow_cards' => (int) $candidate->yellow_cards,
                    'red_cards' => (int) $candidate->red_cards,
                    'lineup_role' => $candidate->lineup_role,
                ],
            ];
        }

        return $entries;
    }

    /**
     * @param Collection<int, MatchPlayerStat> $stats
     */
    private function resolveReferenceDate(Collection $stats): Carbon
    {
        $dates = $stats
            ->map(function (MatchPlayerStat $stat): ?Carbon {
                $match = $stat->match;
                if (!$match) {
                    return null;
                }

                return $match->played_at ?? $match->kickoff_at;
            })
            ->filter()
            ->map(fn ($date) => Carbon::parse($date));

        return $dates->sortDesc()->first() ?? now();
    }

    private function matchdayLabel(CompetitionSeason $competitionSeason, int $matchday): string
    {
        $competitionName = $competitionSeason->competition->short_name
            ?: $competitionSeason->competition->name;
        $seasonName = $competitionSeason->season->name;

        return 'Team des Spieltags '.$competitionName.' '.$seasonName.' #'.$matchday;
    }

    private function positionGroupForStat(MatchPlayerStat $stat): string
    {
        $code = strtoupper((string) ($stat->position_code ?? ''));
        $positionService = new PlayerPositionService();
        $group = $positionService->slotGroup($code, $stat->player?->position);

        return $group ?? 'MID';
    }
}
