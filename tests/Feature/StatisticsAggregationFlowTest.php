<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\Country;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Season;
use App\Models\SeasonClubRegistration;
use App\Models\User;
use App\Services\MatchSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StatisticsAggregationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_match_simulation_rebuilds_league_and_player_aggregates(): void
    {
        $setup = $this->createLeagueSetup();
        $homeClub = $setup['home_club'];
        $awayClub = $setup['away_club'];
        $competitionSeason = $setup['competition_season'];
        $season = $setup['season'];

        $this->createSquad($homeClub, 'HS');
        $this->createSquad($awayClub, 'AS');

        $match = GameMatch::create([
            'competition_season_id' => $competitionSeason->id,
            'season_id' => $season->id,
            'type' => 'league',
            'competition_context' => 'league',
            'stage' => 'Matchday 1',
            'matchday' => 1,
            'kickoff_at' => now()->subHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 55555,
        ]);

        app(MatchSimulationService::class)->simulate($match);

        $this->assertDatabaseHas('season_club_statistics', [
            'competition_season_id' => $competitionSeason->id,
            'club_id' => $homeClub->id,
            'matches_played' => 1,
        ]);
        $this->assertDatabaseHas('season_club_statistics', [
            'competition_season_id' => $competitionSeason->id,
            'club_id' => $awayClub->id,
            'matches_played' => 1,
        ]);

        $this->assertGreaterThan(
            0,
            DB::table('player_career_competition_statistics')->count()
        );
        $this->assertGreaterThan(
            0,
            DB::table('player_season_competition_statistics')
                ->where('season_id', $season->id)
                ->count()
        );
    }

    public function test_rebuild_statistics_command_repairs_league_table_drift(): void
    {
        $setup = $this->createLeagueSetup();
        $homeClub = $setup['home_club'];
        $awayClub = $setup['away_club'];
        $competitionSeason = $setup['competition_season'];
        $season = $setup['season'];

        GameMatch::create([
            'competition_season_id' => $competitionSeason->id,
            'season_id' => $season->id,
            'type' => 'league',
            'competition_context' => 'league',
            'stage' => 'Matchday 1',
            'matchday' => 1,
            'kickoff_at' => now()->subDay(),
            'status' => 'played',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'home_score' => 2,
            'away_score' => 1,
            'played_at' => now()->subDay(),
            'simulation_seed' => 44444,
        ]);

        Artisan::call('game:rebuild-statistics', [
            '--competition-season' => $competitionSeason->id,
        ]);

        DB::table('season_club_statistics')
            ->where('competition_season_id', $competitionSeason->id)
            ->where('club_id', $homeClub->id)
            ->update([
                'points' => 0,
                'wins' => 0,
                'goal_diff' => -99,
            ]);

        Artisan::call('game:rebuild-statistics', [
            '--competition-season' => $competitionSeason->id,
            '--audit' => true,
        ]);

        $this->assertDatabaseHas('season_club_statistics', [
            'competition_season_id' => $competitionSeason->id,
            'club_id' => $homeClub->id,
            'wins' => 1,
            'points' => 3,
            'goal_diff' => 1,
        ]);
        $this->assertDatabaseHas('season_club_statistics', [
            'competition_season_id' => $competitionSeason->id,
            'club_id' => $awayClub->id,
            'wins' => 0,
            'points' => 0,
            'goal_diff' => -1,
        ]);
    }

    /**
     * @return array{
     *   home_club: Club,
     *   away_club: Club,
     *   competition_season: CompetitionSeason,
     *   season: Season
     * }
     */
    private function createLeagueSetup(): array
    {
        $homeUser = User::factory()->create();
        $awayUser = User::factory()->create();

        $country = Country::create([
            'name' => 'Deutschland',
            'iso_code' => 'DE',
            'fifa_code' => 'GER',
        ]);

        $competition = Competition::create([
            'country_id' => $country->id,
            'name' => 'Test Liga',
            'short_name' => 'TL',
            'type' => 'league',
            'tier' => 1,
            'is_active' => true,
        ]);

        $season = Season::create([
            'name' => '2026/27',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_current' => true,
        ]);

        $competitionSeason = CompetitionSeason::create([
            'competition_id' => $competition->id,
            'season_id' => $season->id,
            'format' => 'round_robin',
            'matchdays' => 2,
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 0,
            'relegated_slots' => 0,
            'is_finished' => false,
        ]);

        $homeClub = $this->createClub($homeUser, 'Home Aggregation FC');
        $awayClub = $this->createClub($awayUser, 'Away Aggregation FC');

        SeasonClubRegistration::create([
            'competition_season_id' => $competitionSeason->id,
            'club_id' => $homeClub->id,
        ]);
        SeasonClubRegistration::create([
            'competition_season_id' => $competitionSeason->id,
            'club_id' => $awayClub->id,
        ]);

        return [
            'home_club' => $homeClub,
            'away_club' => $awayClub,
            'competition_season' => $competitionSeason,
            'season' => $season,
        ];
    }

    private function createClub(User $owner, string $name): Club
    {
        return Club::create([
            'user_id' => $owner->id,
            'is_cpu' => false,
            'name' => $name,
            'short_name' => substr(strtoupper($name), 0, 12),
            'slug' => str()->slug($name),
            'country' => 'Deutschland',
            'league' => 'Test Liga',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 60,
            'fan_mood' => 55,
            'fanbase' => 120000,
            'board_confidence' => 55,
            'training_level' => 1,
        ]);
    }

    private function createSquad(Club $club, string $prefix): void
    {
        $positions = ['TW', 'LV', 'IV', 'IV', 'RV', 'LM', 'ZM', 'ZM', 'RM', 'ST', 'ST'];

        foreach ($positions as $idx => $position) {
            Player::create([
                'club_id' => $club->id,
                'first_name' => $prefix.($idx + 1),
                'last_name' => 'Player',
                'position' => $position,
                'position_main' => $position,
                'preferred_foot' => 'right',
                'age' => 24,
                'overall' => 70 - $idx,
                'potential' => 80 - $idx,
                'pace' => 68,
                'shooting' => 67,
                'passing' => 68,
                'defending' => 66,
                'physical' => 67,
                'stamina' => 74,
                'morale' => 72,
                'status' => 'active',
                'market_value' => 300000,
                'salary' => 9000,
                'contract_expires_on' => now()->addYear()->toDateString(),
            ]);
        }
    }
}

