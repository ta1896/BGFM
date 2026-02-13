<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\Country;
use App\Models\GameMatch;
use App\Models\MatchPlayerStat;
use App\Models\Player;
use App\Models\Season;
use App\Models\User;
use App\Services\PlayerCompetitionStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerCompetitionStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_rebuilds_season_and_career_stats_per_context(): void
    {
        [$homeClub, $awayClub] = $this->createClubs();
        $player = $this->createPlayer($homeClub);
        $season = Season::create([
            'name' => '2026/27',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_current' => true,
        ]);

        $country = Country::create([
            'name' => 'Deutschland',
            'iso_code' => 'DE',
            'fifa_code' => 'GER',
        ]);
        $league = Competition::create([
            'country_id' => $country->id,
            'name' => 'Stats League',
            'short_name' => 'SL',
            'type' => 'league',
            'tier' => 1,
            'is_active' => true,
        ]);
        $nationalCup = Competition::create([
            'country_id' => $country->id,
            'name' => 'Stats Pokal',
            'short_name' => 'SP',
            'type' => 'cup',
            'tier' => 1,
            'is_active' => true,
        ]);
        $internationalCup = Competition::create([
            'country_id' => null,
            'name' => 'Stats Euro Cup',
            'short_name' => 'SEC',
            'type' => 'cup',
            'tier' => 1,
            'is_active' => true,
        ]);

        $leagueSeason = $this->createCompetitionSeason($league, $season);
        $nationalCupSeason = $this->createCompetitionSeason($nationalCup, $season, 'knockout');
        $internationalCupSeason = $this->createCompetitionSeason($internationalCup, $season, 'knockout');

        $leagueMatch = $this->createPlayedMatch($homeClub, $awayClub, [
            'competition_season_id' => $leagueSeason->id,
            'season_id' => $season->id,
            'type' => 'league',
            'simulation_seed' => 51101,
        ]);
        $friendlyMatch = $this->createPlayedMatch($homeClub, $awayClub, [
            'competition_season_id' => null,
            'season_id' => $season->id,
            'type' => 'friendly',
            'simulation_seed' => 51102,
        ]);
        $internationalCupMatch = $this->createPlayedMatch($homeClub, $awayClub, [
            'competition_season_id' => $internationalCupSeason->id,
            'season_id' => $season->id,
            'type' => 'cup',
            'simulation_seed' => 51103,
        ]);

        MatchPlayerStat::create([
            'match_id' => $leagueMatch->id,
            'club_id' => $homeClub->id,
            'player_id' => $player->id,
            'lineup_role' => 'starter',
            'position_code' => 'ZM',
            'rating' => 7.6,
            'minutes_played' => 90,
            'goals' => 2,
            'assists' => 1,
            'yellow_cards' => 1,
            'red_cards' => 0,
            'shots' => 4,
            'passes_completed' => 34,
            'passes_failed' => 6,
            'tackles_won' => 2,
            'tackles_lost' => 1,
            'saves' => 0,
        ]);
        MatchPlayerStat::create([
            'match_id' => $friendlyMatch->id,
            'club_id' => $homeClub->id,
            'player_id' => $player->id,
            'lineup_role' => 'starter',
            'position_code' => 'ZM',
            'rating' => 7.1,
            'minutes_played' => 45,
            'goals' => 0,
            'assists' => 1,
            'yellow_cards' => 0,
            'red_cards' => 0,
            'shots' => 1,
            'passes_completed' => 22,
            'passes_failed' => 4,
            'tackles_won' => 1,
            'tackles_lost' => 1,
            'saves' => 0,
        ]);
        MatchPlayerStat::create([
            'match_id' => $internationalCupMatch->id,
            'club_id' => $homeClub->id,
            'player_id' => $player->id,
            'lineup_role' => 'starter',
            'position_code' => 'ZM',
            'rating' => 6.9,
            'minutes_played' => 90,
            'goals' => 1,
            'assists' => 0,
            'yellow_cards' => 0,
            'red_cards' => 1,
            'shots' => 3,
            'passes_completed' => 29,
            'passes_failed' => 5,
            'tackles_won' => 3,
            'tackles_lost' => 1,
            'saves' => 0,
        ]);

        $service = app(PlayerCompetitionStatsService::class);
        $service->rebuildForMatchPlayers($leagueMatch);

        $this->assertDatabaseHas('player_season_competition_statistics', [
            'player_id' => $player->id,
            'season_id' => $season->id,
            'competition_context' => 'league',
            'appearances' => 1,
            'minutes_played' => 90,
            'goals' => 2,
            'assists' => 1,
        ]);
        $this->assertDatabaseHas('player_season_competition_statistics', [
            'player_id' => $player->id,
            'season_id' => $season->id,
            'competition_context' => 'friendly',
            'appearances' => 1,
            'minutes_played' => 45,
            'goals' => 0,
            'assists' => 1,
        ]);
        $this->assertDatabaseHas('player_season_competition_statistics', [
            'player_id' => $player->id,
            'season_id' => $season->id,
            'competition_context' => 'cup_international',
            'appearances' => 1,
            'minutes_played' => 90,
            'goals' => 1,
            'assists' => 0,
            'red_cards' => 1,
        ]);

        $this->assertDatabaseHas('player_career_competition_statistics', [
            'player_id' => $player->id,
            'competition_context' => 'league',
            'goals' => 2,
            'assists' => 1,
        ]);
        $this->assertDatabaseHas('player_career_competition_statistics', [
            'player_id' => $player->id,
            'competition_context' => 'friendly',
            'goals' => 0,
            'assists' => 1,
        ]);
        $this->assertDatabaseHas('player_career_competition_statistics', [
            'player_id' => $player->id,
            'competition_context' => 'cup_international',
            'goals' => 1,
            'assists' => 0,
            'red_cards' => 1,
        ]);

        $this->assertDatabaseMissing('player_career_competition_statistics', [
            'player_id' => $player->id,
            'competition_context' => 'cup_national',
        ]);

        // Rebuild again: no double accumulation, same absolute aggregates.
        $service->rebuildForMatchPlayers($leagueMatch);

        $this->assertSame(
            3,
            \DB::table('player_career_competition_statistics')
                ->where('player_id', $player->id)
                ->count()
        );
    }

    /**
     * @return array{0: Club, 1: Club}
     */
    private function createClubs(): array
    {
        $homeUser = User::factory()->create();
        $awayUser = User::factory()->create();

        $homeClub = Club::create([
            'user_id' => $homeUser->id,
            'name' => 'Stats Home',
            'country' => 'Deutschland',
            'league' => 'Stats League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
        ]);
        $awayClub = Club::create([
            'user_id' => $awayUser->id,
            'name' => 'Stats Away',
            'country' => 'Deutschland',
            'league' => 'Stats League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
        ]);

        return [$homeClub, $awayClub];
    }

    private function createPlayer(Club $club): Player
    {
        return Player::create([
            'club_id' => $club->id,
            'first_name' => 'Stats',
            'last_name' => 'Player',
            'position' => 'ZM',
            'position_main' => 'ZM',
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => 72,
            'potential' => 78,
            'pace' => 68,
            'shooting' => 70,
            'passing' => 74,
            'defending' => 65,
            'physical' => 67,
            'stamina' => 75,
            'morale' => 72,
            'status' => 'active',
            'market_value' => 300000,
            'salary' => 10000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ]);
    }

    private function createCompetitionSeason(Competition $competition, Season $season, string $format = 'round_robin'): CompetitionSeason
    {
        return CompetitionSeason::create([
            'competition_id' => $competition->id,
            'season_id' => $season->id,
            'format' => $format,
            'matchdays' => $format === 'round_robin' ? 34 : null,
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 0,
            'relegated_slots' => 0,
            'is_finished' => false,
        ]);
    }

    /**
     * @param array<string, int|string|null> $overrides
     */
    private function createPlayedMatch(Club $homeClub, Club $awayClub, array $overrides = []): GameMatch
    {
        return GameMatch::create(array_merge([
            'competition_season_id' => null,
            'season_id' => null,
            'type' => 'friendly',
            'stage' => 'Played Match',
            'kickoff_at' => now()->subHour(),
            'status' => 'played',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'home_score' => 1,
            'away_score' => 0,
            'simulation_seed' => 51100,
            'played_at' => now(),
        ], $overrides));
    }
}
