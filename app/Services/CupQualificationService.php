<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\GameMatch;
use App\Models\Season;
use App\Models\SeasonClubRegistration;
use App\Models\SeasonClubStatistic;
use Illuminate\Support\Collection;

class CupQualificationService
{
    public function __construct(
        private readonly CompetitionContextService $competitionContextService
    ) {
    }

    /**
     * @param Collection<int, mixed> $leagueTable
     */
    public function syncForLeagueSeason(
        CompetitionSeason $sourceLeagueSeason,
        Season $targetSeason,
        Collection $leagueTable
    ): void {
        if (!(bool) config('simulation.cup.qualification.enabled', true)) {
            return;
        }

        $sourceLeagueSeason->loadMissing('competition.country');
        if ((string) $sourceLeagueSeason->competition?->type !== 'league') {
            return;
        }

        $sourceTier = max(1, (int) config('simulation.cup.qualification.source_league_tier', 1));
        if ((int) $sourceLeagueSeason->competition?->tier !== $sourceTier) {
            return;
        }

        $countryName = trim((string) ($sourceLeagueSeason->competition?->country?->name ?? ''));
        if ($countryName === '') {
            return;
        }

        $rankedClubIds = $leagueTable
            ->pluck('club_id')
            ->map(fn ($clubId): int => (int) $clubId)
            ->filter(fn (int $clubId): bool => $clubId > 0)
            ->unique()
            ->values()
            ->all();
        if ($rankedClubIds === []) {
            return;
        }

        /** @var Collection<int, Competition> $internationalCups */
        $internationalCups = Competition::query()
            ->where('type', 'cup')
            ->where('scope', 'international')
            ->where('is_active', true)
            ->orderBy('tier')
            ->orderBy('id')
            ->get();
        if ($internationalCups->isEmpty()) {
            return;
        }

        $cursor = 0;
        foreach ($internationalCups as $cupCompetition) {
            $slots = $this->slotsForCupTier((int) $cupCompetition->tier);
            if ($slots < 1) {
                continue;
            }

            $qualifiedClubIds = array_slice($rankedClubIds, $cursor, $slots);
            if ($qualifiedClubIds === []) {
                continue;
            }

            $cursor += count($qualifiedClubIds);

            $cupSeason = CompetitionSeason::firstOrCreate(
                [
                    'competition_id' => $cupCompetition->id,
                    'season_id' => $targetSeason->id,
                ],
                [
                    'format' => 'knockout',
                    'matchdays' => null,
                    'points_win' => 3,
                    'points_draw' => 1,
                    'points_loss' => 0,
                    'promoted_slots' => 0,
                    'relegated_slots' => 0,
                    'is_finished' => false,
                ]
            );

            $this->syncQualifiedRegistrationsForCountry($cupSeason, $countryName, $qualifiedClubIds);
            $this->generateRoundOneIfMissing($cupSeason);
        }
    }

    /**
     * @param array<int, int> $qualifiedClubIds
     */
    private function syncQualifiedRegistrationsForCountry(
        CompetitionSeason $cupSeason,
        string $countryName,
        array $qualifiedClubIds
    ): void {
        $existingCountryClubIds = SeasonClubRegistration::query()
            ->where('competition_season_id', $cupSeason->id)
            ->whereHas('club', fn ($query) => $query->where('country', $countryName))
            ->pluck('club_id')
            ->map(fn ($clubId): int => (int) $clubId)
            ->values()
            ->all();

        $staleClubIds = array_values(array_diff($existingCountryClubIds, $qualifiedClubIds));
        if ($staleClubIds !== []) {
            SeasonClubRegistration::query()
                ->where('competition_season_id', $cupSeason->id)
                ->whereIn('club_id', $staleClubIds)
                ->delete();

            SeasonClubStatistic::query()
                ->where('competition_season_id', $cupSeason->id)
                ->whereIn('club_id', $staleClubIds)
                ->delete();
        }

        foreach ($qualifiedClubIds as $clubId) {
            SeasonClubRegistration::query()->updateOrCreate(
                [
                    'competition_season_id' => $cupSeason->id,
                    'club_id' => $clubId,
                ],
                []
            );

            SeasonClubStatistic::query()->updateOrCreate(
                [
                    'competition_season_id' => $cupSeason->id,
                    'club_id' => $clubId,
                ],
                [
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
                    'form_last5' => null,
                ]
            );
        }
    }

    private function generateRoundOneIfMissing(CompetitionSeason $cupSeason): void
    {
        if (!(bool) config('simulation.cup.qualification.auto_generate_fixtures', true)) {
            return;
        }

        $hasMatches = GameMatch::query()
            ->where('competition_season_id', $cupSeason->id)
            ->where('type', 'cup')
            ->exists();
        if ($hasMatches) {
            return;
        }

        $clubIds = SeasonClubRegistration::query()
            ->where('competition_season_id', $cupSeason->id)
            ->orderBy('club_id')
            ->pluck('club_id')
            ->map(fn ($clubId): int => (int) $clubId)
            ->values();
        if ($clubIds->count() < 2) {
            return;
        }

        $participantCount = $clubIds->count();
        $stage = $this->roundStageName(1, $participantCount);
        $kickoffAt = $cupSeason->season?->start_date
            ? $cupSeason->season->start_date->copy()->setTime(20, 0)
            : now()->addDays(7)->setTime(20, 0);
        $competitionContext = $this->competitionContextService->fromRawMatchData(
            'cup',
            $cupSeason->competition?->country_id,
            (string) ($cupSeason->competition?->scope ?? '')
        );

        $byeClubId = null;
        if ($clubIds->count() % 2 !== 0) {
            /** @var int $popped */
            $popped = $clubIds->pop();
            $byeClubId = $popped;
        }

        foreach ($clubIds->chunk(2) as $pair) {
            $pair = $pair->values();
            if ($pair->count() < 2) {
                continue;
            }

            GameMatch::query()->create([
                'competition_season_id' => $cupSeason->id,
                'season_id' => $cupSeason->season_id,
                'type' => 'cup',
                'competition_context' => $competitionContext,
                'stage' => $stage,
                'round_number' => 1,
                'kickoff_at' => $kickoffAt,
                'status' => 'scheduled',
                'home_club_id' => (int) $pair->get(0),
                'away_club_id' => (int) $pair->get(1),
                'stadium_club_id' => (int) $pair->get(0),
                'simulation_seed' => random_int(10000, 99999),
            ]);

            $kickoffAt = $kickoffAt->copy()->addMinutes(30);
        }

        if ($byeClubId !== null) {
            GameMatch::query()->create([
                'competition_season_id' => $cupSeason->id,
                'season_id' => $cupSeason->season_id,
                'type' => 'cup',
                'competition_context' => $competitionContext,
                'stage' => $stage.' (Freilos)',
                'round_number' => 1,
                'kickoff_at' => $kickoffAt,
                'status' => 'played',
                'home_club_id' => $byeClubId,
                'away_club_id' => $byeClubId,
                'stadium_club_id' => $byeClubId,
                'home_score' => 1,
                'away_score' => 0,
                'simulation_seed' => random_int(10000, 99999),
                'played_at' => now(),
            ]);
        }
    }

    private function slotsForCupTier(int $tier): int
    {
        /** @var array<int|string, mixed> $slotsByTier */
        $slotsByTier = (array) config('simulation.cup.qualification.slots_by_competition_tier', [1 => 4]);

        $direct = $slotsByTier[$tier] ?? null;
        if ($direct !== null) {
            return max(0, (int) $direct);
        }

        return max(0, (int) ($slotsByTier['default'] ?? 0));
    }

    private function roundStageName(int $roundNumber, int $participantCount): string
    {
        return match ($participantCount) {
            2 => 'Finale',
            4 => 'Halbfinale',
            8 => 'Viertelfinale',
            16 => 'Achtelfinale',
            default => 'Cup Runde '.$roundNumber,
        };
    }
}

