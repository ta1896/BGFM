<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\Country;
use App\Models\GameMatch;
use App\Models\Season;
use App\Models\User;
use App\Services\StatisticsAggregationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubStatisticsHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_club_stats_are_split_by_season_and_context_and_provide_history(): void
    {
        $service = app(StatisticsAggregationService::class);

        $country = Country::create([
            'name' => 'Deutschland',
            'iso_code' => 'DE',
            'fifa_code' => 'GER',
        ]);
        $seasonOne = Season::create([
            'name' => '2026/27',
            'start_date' => now()->subYears(1)->startOfYear()->toDateString(),
            'end_date' => now()->subYears(1)->endOfYear()->toDateString(),
            'is_current' => false,
        ]);
        $seasonTwo = Season::create([
            'name' => '2027/28',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_current' => true,
        ]);

        $nationalCup = Competition::create([
            'country_id' => $country->id,
            'name' => 'National Cup',
            'short_name' => 'NC',
            'type' => 'cup',
            'scope' => 'national',
            'tier' => 1,
            'is_active' => true,
        ]);
        $internationalCup = Competition::create([
            'country_id' => null,
            'name' => 'International Cup',
            'short_name' => 'IC',
            'type' => 'cup',
            'scope' => 'international',
            'tier' => 1,
            'is_active' => true,
        ]);

        $nationalCupSeason = $this->createCompetitionSeason($nationalCup, $seasonOne);
        $internationalCupSeason = $this->createCompetitionSeason($internationalCup, $seasonTwo);

        $club = $this->createClub('Stats Home');
        $opponentA = $this->createClub('Stats Away A');
        $opponentB = $this->createClub('Stats Away B');

        $this->createPlayedMatch($club, $opponentA, [
            'season_id' => $seasonOne->id,
            'type' => 'league',
            'competition_context' => 'league',
            'home_score' => 2,
            'away_score' => 1,
        ]);
        $this->createPlayedMatch($club, $opponentB, [
            'competition_season_id' => $nationalCupSeason->id,
            'season_id' => $seasonOne->id,
            'type' => 'cup',
            'competition_context' => null,
            'home_score' => 1,
            'away_score' => 1,
        ]);
        $this->createPlayedMatch($club, $opponentA, [
            'competition_season_id' => $internationalCupSeason->id,
            'season_id' => $seasonTwo->id,
            'type' => 'cup',
            'competition_context' => null,
            'home_score' => 3,
            'away_score' => 0,
        ]);
        $this->createPlayedMatch($club, $opponentB, [
            'season_id' => $seasonTwo->id,
            'type' => 'friendly',
            'competition_context' => 'friendly',
            'home_score' => 0,
            'away_score' => 2,
        ]);

        $overall = $service->clubSummaryForClub($club, null);
        $seasonOneSummary = $service->clubSummaryForClub($club, $seasonOne->id);
        $seasonTwoSummary = $service->clubSummaryForClub($club, $seasonTwo->id);
        $seasonOneByContext = $service->clubSummaryByContextForClub($club, $seasonOne->id);
        $history = $service->clubSeasonHistoryForClub($club, 5);

        $this->assertSame(4, $overall['matches']);
        $this->assertSame(2, $overall['wins']);
        $this->assertSame(1, $overall['draws']);
        $this->assertSame(1, $overall['losses']);
        $this->assertSame(6, $overall['goals_for']);
        $this->assertSame(4, $overall['goals_against']);
        $this->assertSame(7, $overall['points']);

        $this->assertSame(2, $seasonOneSummary['matches']);
        $this->assertSame(1, $seasonOneSummary['wins']);
        $this->assertSame(1, $seasonOneSummary['draws']);
        $this->assertSame(0, $seasonOneSummary['losses']);
        $this->assertSame(4, $seasonOneSummary['points']);

        $this->assertSame(2, $seasonTwoSummary['matches']);
        $this->assertSame(1, $seasonTwoSummary['wins']);
        $this->assertSame(0, $seasonTwoSummary['draws']);
        $this->assertSame(1, $seasonTwoSummary['losses']);
        $this->assertSame(3, $seasonTwoSummary['points']);

        $this->assertSame(1, $seasonOneByContext['league']['matches']);
        $this->assertSame(3, $seasonOneByContext['league']['points']);
        $this->assertSame(1, $seasonOneByContext['cup_national']['matches']);
        $this->assertSame(1, $seasonOneByContext['cup_national']['points']);
        $this->assertSame(0, $seasonOneByContext['cup_international']['matches']);
        $this->assertSame(0, $seasonOneByContext['friendly']['matches']);

        $this->assertCount(2, $history);
        $this->assertSame((int) $seasonTwo->id, (int) $history[0]['season_id']);
        $this->assertSame('2027/28', $history[0]['season_name']);
        $this->assertSame(2, (int) $history[0]['matches']);
        $this->assertSame((int) $seasonOne->id, (int) $history[1]['season_id']);
        $this->assertSame('2026/27', $history[1]['season_name']);
        $this->assertSame(2, (int) $history[1]['matches']);
    }

    private function createCompetitionSeason(Competition $competition, Season $season): CompetitionSeason
    {
        return CompetitionSeason::create([
            'competition_id' => $competition->id,
            'season_id' => $season->id,
            'format' => 'knockout',
            'matchdays' => null,
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 0,
            'relegated_slots' => 0,
            'is_finished' => false,
        ]);
    }

    private function createClub(string $name): Club
    {
        $user = User::factory()->create();

        return Club::create([
            'user_id' => $user->id,
            'is_cpu' => false,
            'name' => $name,
            'short_name' => strtoupper(substr($name, 0, 12)),
            'slug' => str()->slug($name).'-'.$user->id,
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
            'training_level' => 1,
            'season_objective' => 'mid_table',
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createPlayedMatch(Club $homeClub, Club $awayClub, array $overrides): GameMatch
    {
        return GameMatch::create(array_merge([
            'competition_season_id' => null,
            'season_id' => null,
            'type' => 'friendly',
            'competition_context' => 'friendly',
            'stage' => 'Test Match',
            'round_number' => null,
            'kickoff_at' => now()->subHour(),
            'status' => 'played',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'home_score' => 0,
            'away_score' => 0,
            'simulation_seed' => 12345,
            'played_at' => now()->subMinutes(30),
        ], $overrides));
    }
}

