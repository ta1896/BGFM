<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\GameMatch;
use App\Models\GameNotification;
use App\Models\Season;
use App\Models\SeasonClubRegistration;
use App\Models\SeasonClubStatistic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SeasonProgressionService
{
    public function __construct(
        private readonly CpuClubDecisionService $cpuDecisionService,
        private readonly MatchSimulationService $simulationService,
        private readonly LeagueTableService $tableService,
        private readonly FixtureGeneratorService $fixtureGenerator,
        private readonly LoanService $loanService,
        private readonly FinanceCycleService $financeCycleService,
        private readonly StadiumService $stadiumService,
        private readonly TrainingCampService $trainingCampService,
        private readonly SponsorService $sponsorService,
        private readonly PlayerAvailabilityService $playerAvailabilityService,
        private readonly CupQualificationService $cupQualificationService,
        private readonly TeamOfTheDayService $teamOfTheDayService,
        private readonly RandomEventService $randomEventService
    ) {
    }

    /**
     * @return array{
     *   processed_competitions:int,
     *   matches_simulated:int,
     *   seasons_finalized:int,
     *   promotions:int,
     *   relegations:int,
     *   loans_completed:int,
     *   match_settlements:int,
     *   stadium_projects_completed:int,
     *   training_camps_activated:int,
     *   training_camps_completed:int,
     *   sponsor_contracts_expired:int,
     *   team_of_the_day_generated:int,
     *   random_events_generated:int,
     *   random_events_applied:int
     * }
     */
    public function processNextMatchday(?CompetitionSeason $targetCompetitionSeason = null): array
    {
        $summary = [
            'processed_competitions' => 0,
            'matches_simulated' => 0,
            'seasons_finalized' => 0,
            'promotions' => 0,
            'relegations' => 0,
            'loans_completed' => 0,
            'match_settlements' => 0,
            'stadium_projects_completed' => 0,
            'training_camps_activated' => 0,
            'training_camps_completed' => 0,
            'sponsor_contracts_expired' => 0,
            'team_of_the_day_generated' => 0,
            'random_events_generated' => 0,
            'random_events_applied' => 0,
        ];

        $competitionSeasons = $this->competitionSeasonsForRun($targetCompetitionSeason);

        foreach ($competitionSeasons as $competitionSeason) {
            $competitionSeason->loadMissing(['competition', 'season']);
            $summary['processed_competitions']++;

            $nextMatchday = $competitionSeason->matches()
                ->where('type', 'league')
                ->where('status', 'scheduled')
                ->min('matchday');

            if ($nextMatchday !== null) {
                $simulatedInMatchday = false;
                $matches = $competitionSeason->matches()
                    ->where('type', 'league')
                    ->where('status', 'scheduled')
                    ->where('matchday', $nextMatchday)
                    ->with(['homeClub.players', 'awayClub.players'])
                    ->orderBy('kickoff_at')
                    ->get();

                foreach ($matches as $match) {
                    $this->cpuDecisionService->prepareForMatch($match);
                    $simulatedMatch = $this->simulationService->simulate($match);
                    if ($simulatedMatch->status === 'played') {
                        if ($this->financeCycleService->settleMatch($simulatedMatch)) {
                            $summary['match_settlements']++;
                        }

                        $simulatedInMatchday = true;
                        $summary['matches_simulated']++;
                    }
                }

                if ($simulatedInMatchday) {
                    $this->teamOfTheDayService->generateForCompetitionMatchday(
                        $competitionSeason,
                        (int) $nextMatchday
                    );
                    $summary['team_of_the_day_generated']++;
                }
            }

            $hasScheduledLeagueMatches = $competitionSeason->matches()
                ->where('type', 'league')
                ->where('status', 'scheduled')
                ->exists();

            if (!$hasScheduledLeagueMatches && !$competitionSeason->is_finished) {
                $result = $this->finalizeCompetitionSeason($competitionSeason);
                if ($result['finalized']) {
                    $summary['seasons_finalized']++;
                    $summary['promotions'] += $result['promotions'];
                    $summary['relegations'] += $result['relegations'];
                }
            }
        }

        $summary['stadium_projects_completed'] = $this->stadiumService->completeDueProjects();
        $campProgress = $this->trainingCampService->progressDueCamps();
        $summary['training_camps_activated'] = $campProgress['activated'];
        $summary['training_camps_completed'] = $campProgress['completed'];
        $summary['sponsor_contracts_expired'] = $this->sponsorService->expireEndedContracts();
        $summary['loans_completed'] = $this->loanService->completeExpiredLoans();

        $randomEventSummary = $this->randomEventService->triggerAutomatedEvents();
        $summary['random_events_generated'] = $randomEventSummary['generated'];
        $summary['random_events_applied'] = $randomEventSummary['applied'];

        return $summary;
    }

    /**
     * @return array{finalized:bool,promotions:int,relegations:int}
     */
    public function finalizeCompetitionSeason(CompetitionSeason $competitionSeason): array
    {
        $competitionSeason->loadMissing(['competition', 'season']);

        if ($competitionSeason->is_finished) {
            return ['finalized' => false, 'promotions' => 0, 'relegations' => 0];
        }

        $openMatches = $competitionSeason->matches()
            ->where('type', 'league')
            ->where('status', 'scheduled')
            ->exists();

        if ($openMatches) {
            return ['finalized' => false, 'promotions' => 0, 'relegations' => 0];
        }

        $this->tableService->rebuild($competitionSeason);

        $table = $this->tableService->table($competitionSeason)->values();
        $nextSeason = $this->ensureNextSeason($competitionSeason->season);

        $currentNextCompetitionSeason = $this->ensureNextCompetitionSeason(
            $competitionSeason->competition,
            $nextSeason,
            $competitionSeason
        );

        $countryId = $competitionSeason->competition->country_id;
        $tier = $competitionSeason->competition->tier;

        $higherCompetition = $this->findLeagueByTier($countryId, $tier - 1);
        $lowerCompetition = $this->findLeagueByTier($countryId, $tier + 1);

        $promotions = 0;
        $relegations = 0;
        $nextCompetitionSeasonsToRefresh = collect([$currentNextCompetitionSeason]);

        if ($higherCompetition && $competitionSeason->promoted_slots > 0) {
            $promotedClubIds = $table
                ->take((int) $competitionSeason->promoted_slots)
                ->pluck('club_id')
                ->filter()
                ->values();

            $higherNextCompetitionSeason = $this->ensureNextCompetitionSeason(
                $higherCompetition,
                $nextSeason,
                $competitionSeason
            );
            $nextCompetitionSeasonsToRefresh->push($higherNextCompetitionSeason);

            foreach ($promotedClubIds as $clubId) {
                $this->moveClubToCompetition($clubId, $currentNextCompetitionSeason, $higherNextCompetitionSeason, 'promotion');
                $promotions++;
            }
        }

        if ($lowerCompetition && $competitionSeason->relegated_slots > 0) {
            $relegatedClubIds = $table
                ->reverse()
                ->take((int) $competitionSeason->relegated_slots)
                ->pluck('club_id')
                ->filter()
                ->values();

            $lowerNextCompetitionSeason = $this->ensureNextCompetitionSeason(
                $lowerCompetition,
                $nextSeason,
                $competitionSeason
            );
            $nextCompetitionSeasonsToRefresh->push($lowerNextCompetitionSeason);

            foreach ($relegatedClubIds as $clubId) {
                $this->moveClubToCompetition($clubId, $currentNextCompetitionSeason, $lowerNextCompetitionSeason, 'relegation');
                $relegations++;
            }
        }

        $nextCompetitionSeasonsToRefresh
            ->unique('id')
            ->each(function (CompetitionSeason $nextCompetitionSeason): void {
                $clubCount = $nextCompetitionSeason->registrations()->count();
                if ($clubCount >= 2) {
                    $this->fixtureGenerator->generateRoundRobin($nextCompetitionSeason->load('season'));
                    $this->tableService->rebuild($nextCompetitionSeason);
                }
            });

        $this->cupQualificationService->syncForLeagueSeason($competitionSeason, $nextSeason, $table);

        DB::transaction(function () use ($competitionSeason, $nextSeason): void {
            $competitionSeason->update(['is_finished' => true]);

            $unfinishedCurrentSeasonCompetitions = CompetitionSeason::query()
                ->where('season_id', $competitionSeason->season_id)
                ->where('is_finished', false)
                ->exists();

            if (!$unfinishedCurrentSeasonCompetitions) {
                if ((bool) config('simulation.aftermath.yellow_cards.reset_on_season_rollover', true)) {
                    $this->playerAvailabilityService->resetSeasonalBookingCounters();
                }
                $competitionSeason->season->update(['is_current' => false]);
                $nextSeason->update(['is_current' => true]);
            }
        });

        return ['finalized' => true, 'promotions' => $promotions, 'relegations' => $relegations];
    }

    /**
     * @return Collection<int, CompetitionSeason>
     */
    private function competitionSeasonsForRun(?CompetitionSeason $targetCompetitionSeason): Collection
    {
        $query = CompetitionSeason::query()
            ->with(['competition', 'season'])
            ->where('is_finished', false)
            ->whereHas('competition', fn ($q) => $q->where('type', 'league'))
            ->orderBy('id');

        if ($targetCompetitionSeason) {
            $query->whereKey($targetCompetitionSeason->id);
        }

        return $query->get();
    }

    private function ensureNextSeason(Season $season): Season
    {
        $nextStartDate = $season->start_date->copy()->addYear();
        $nextEndDate = $season->end_date->copy()->addYear();
        $name = $this->nextSeasonName($season->name, (int) $nextStartDate->year, (int) $nextEndDate->year);

        return Season::firstOrCreate(
            ['name' => $name],
            [
                'start_date' => $nextStartDate->toDateString(),
                'end_date' => $nextEndDate->toDateString(),
                'is_current' => false,
            ]
        );
    }

    private function nextSeasonName(string $currentName, int $nextStartYear, int $nextEndYear): string
    {
        if (preg_match('/^(\d{4})\/(\d{2,4})$/', $currentName) === 1) {
            return sprintf('%d/%02d', $nextStartYear, $nextEndYear % 100);
        }

        return sprintf('%d/%02d', $nextStartYear, $nextEndYear % 100);
    }

    private function findLeagueByTier(?int $countryId, int $tier): ?Competition
    {
        if (!$countryId || $tier < 1) {
            return null;
        }

        return Competition::query()
            ->where('country_id', $countryId)
            ->where('type', 'league')
            ->where('tier', $tier)
            ->where('is_active', true)
            ->first();
    }

    private function ensureNextCompetitionSeason(
        Competition $competition,
        Season $nextSeason,
        CompetitionSeason $templateCompetitionSeason
    ): CompetitionSeason {
        $nextCompetitionSeason = CompetitionSeason::firstOrCreate(
            [
                'competition_id' => $competition->id,
                'season_id' => $nextSeason->id,
            ],
            [
                'format' => $templateCompetitionSeason->format,
                'matchdays' => $templateCompetitionSeason->matchdays,
                'points_win' => $templateCompetitionSeason->points_win,
                'points_draw' => $templateCompetitionSeason->points_draw,
                'points_loss' => $templateCompetitionSeason->points_loss,
                'promoted_slots' => $templateCompetitionSeason->promoted_slots,
                'relegated_slots' => $templateCompetitionSeason->relegated_slots,
                'is_finished' => false,
            ]
        );

        if (!$nextCompetitionSeason->registrations()->exists()) {
            $sourceCompetitionSeason = $competition->competitionSeasons()
                ->where('season_id', '!=', $nextSeason->id)
                ->orderByDesc('season_id')
                ->first();

            $clubIds = $sourceCompetitionSeason
                ? $sourceCompetitionSeason->registrations()->pluck('club_id')->all()
                : [];

            foreach ($clubIds as $clubId) {
                $this->registerClubForSeason($nextCompetitionSeason, (int) $clubId);
            }

            if (count($clubIds) >= 2) {
                $this->fixtureGenerator->generateRoundRobin($nextCompetitionSeason->load('season'));
                $this->tableService->rebuild($nextCompetitionSeason);
            }
        }

        return $nextCompetitionSeason;
    }

    private function moveClubToCompetition(
        int $clubId,
        CompetitionSeason $fromCompetitionSeason,
        CompetitionSeason $toCompetitionSeason,
        string $movementType
    ): void {
        DB::transaction(function () use ($clubId, $fromCompetitionSeason, $toCompetitionSeason, $movementType): void {
            SeasonClubRegistration::query()
                ->where('competition_season_id', $fromCompetitionSeason->id)
                ->where('club_id', $clubId)
                ->delete();

            SeasonClubStatistic::query()
                ->where('competition_season_id', $fromCompetitionSeason->id)
                ->where('club_id', $clubId)
                ->delete();

            $this->registerClubForSeason($toCompetitionSeason, $clubId);

            $club = Club::query()->find($clubId);
            if ($club) {
                $toCompetitionSeason->loadMissing('competition');

                $club->update([
                    'league_id' => $toCompetitionSeason->competition_id,
                    'league' => $toCompetitionSeason->competition->name,
                ]);

                if ($club->user_id) {
                    $title = $movementType === 'promotion' ? 'Aufstieg geschafft' : 'Abstieg bestaetigt';
                    $message = $movementType === 'promotion'
                        ? 'Dein Verein spielt naechste Saison in '.$toCompetitionSeason->competition->name.'.'
                        : 'Dein Verein spielt naechste Saison in '.$toCompetitionSeason->competition->name.'.';

                    GameNotification::create([
                        'user_id' => $club->user_id,
                        'club_id' => $club->id,
                        'type' => $movementType,
                        'title' => $title,
                        'message' => $message,
                        'action_url' => '/table?competition_season='.$toCompetitionSeason->id,
                    ]);
                }
            }
        });
    }

    private function registerClubForSeason(CompetitionSeason $competitionSeason, int $clubId): void
    {
        SeasonClubRegistration::updateOrCreate(
            [
                'competition_season_id' => $competitionSeason->id,
                'club_id' => $clubId,
            ],
            []
        );

        SeasonClubStatistic::updateOrCreate(
            [
                'competition_season_id' => $competitionSeason->id,
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
