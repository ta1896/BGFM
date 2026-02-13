<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\Country;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Season;
use App\Models\User;
use App\Services\PlayerAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerAvailabilityContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_league_suspension_blocks_only_league_context(): void
    {
        [$homeClub, $awayClub] = $this->createClubs();
        $leagueSeason = $this->createLeagueCompetitionSeason();
        $player = $this->createPlayer($homeClub);
        $player->update([
            'status' => 'suspended',
            'suspension_matches_remaining' => 1,
            'suspension_league_remaining' => 1,
            'suspension_friendly_remaining' => 0,
        ]);

        $leagueMatch = GameMatch::create([
            'competition_season_id' => $leagueSeason->id,
            'season_id' => $leagueSeason->season_id,
            'type' => 'league',
            'stage' => 'League',
            'kickoff_at' => now()->subHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 40101,
        ]);

        $friendlyMatch = GameMatch::create([
            'type' => 'friendly',
            'stage' => 'Friendly',
            'kickoff_at' => now()->subHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 40102,
        ]);

        $availability = app(PlayerAvailabilityService::class);

        $this->assertFalse($availability->isPlayerAvailableForLiveMatch($player->fresh(), $leagueMatch));
        $this->assertTrue($availability->isPlayerAvailableForLiveMatch($player->fresh(), $friendlyMatch));
    }

    public function test_decrement_affects_only_current_context_counter(): void
    {
        [$homeClub, $awayClub] = $this->createClubs();
        $service = app(PlayerAvailabilityService::class);
        $player = $this->createPlayer($homeClub);
        $player->update([
            'status' => 'suspended',
            'injury_matches_remaining' => 2,
            'suspension_matches_remaining' => 3,
            'suspension_league_remaining' => 2,
            'suspension_cup_national_remaining' => 3,
            'suspension_cup_international_remaining' => 0,
            'suspension_friendly_remaining' => 1,
        ]);

        $friendlyMatch = GameMatch::create([
            'type' => 'friendly',
            'stage' => 'Friendly',
            'kickoff_at' => now()->subHour(),
            'status' => 'played',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 40103,
        ]);

        $service->decrementCountersForMatch($homeClub->id, $friendlyMatch);

        $player->refresh();
        $this->assertSame(1, (int) $player->injury_matches_remaining);
        $this->assertSame(2, (int) $player->suspension_league_remaining);
        $this->assertSame(3, (int) $player->suspension_cup_national_remaining);
        $this->assertSame(0, (int) $player->suspension_friendly_remaining);
        $this->assertSame(3, (int) $player->suspension_matches_remaining);
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
            'name' => 'Ctx Home',
            'country' => 'Deutschland',
            'league' => 'Ctx League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
        ]);
        $awayClub = Club::create([
            'user_id' => $awayUser->id,
            'name' => 'Ctx Away',
            'country' => 'Deutschland',
            'league' => 'Ctx League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
        ]);

        return [$homeClub, $awayClub];
    }

    private function createLeagueCompetitionSeason(): CompetitionSeason
    {
        $country = Country::create([
            'name' => 'Deutschland',
            'iso_code' => 'DE',
            'fifa_code' => 'GER',
        ]);
        $competition = Competition::create([
            'country_id' => $country->id,
            'name' => 'Ctx Bundesliga',
            'short_name' => 'CTX',
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

        return CompetitionSeason::create([
            'competition_id' => $competition->id,
            'season_id' => $season->id,
            'format' => 'round_robin',
            'matchdays' => 34,
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 0,
            'relegated_slots' => 0,
            'is_finished' => false,
        ]);
    }

    private function createPlayer(Club $club): Player
    {
        return Player::create([
            'club_id' => $club->id,
            'first_name' => 'Ctx',
            'last_name' => 'Player',
            'position' => 'ZM',
            'position_main' => 'ZM',
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => 70,
            'potential' => 75,
            'pace' => 68,
            'shooting' => 67,
            'passing' => 70,
            'defending' => 65,
            'physical' => 66,
            'stamina' => 74,
            'morale' => 70,
            'status' => 'active',
            'market_value' => 200000,
            'salary' => 9000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ]);
    }
}
