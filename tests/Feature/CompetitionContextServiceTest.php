<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\Country;
use App\Models\GameMatch;
use App\Models\Season;
use App\Models\User;
use App\Services\CompetitionContextService;
use App\Services\LiveMatchTickerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionContextServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_classifies_match_contexts_for_all_core_types(): void
    {
        [$homeClub, $awayClub] = $this->createClubs();
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
            'name' => 'Ctx League',
            'short_name' => 'CL',
            'type' => 'league',
            'tier' => 1,
            'is_active' => true,
        ]);
        $nationalCup = Competition::create([
            'country_id' => $country->id,
            'name' => 'Ctx National Cup',
            'short_name' => 'CNC',
            'type' => 'cup',
            'tier' => 1,
            'is_active' => true,
        ]);
        $internationalCup = Competition::create([
            'country_id' => null,
            'name' => 'Ctx International Cup',
            'short_name' => 'CIC',
            'type' => 'cup',
            'tier' => 1,
            'is_active' => true,
        ]);

        $leagueSeason = $this->createCompetitionSeason($league, $season, 'round_robin');
        $nationalCupSeason = $this->createCompetitionSeason($nationalCup, $season, 'knockout');
        $internationalCupSeason = $this->createCompetitionSeason($internationalCup, $season, 'knockout');

        $leagueMatch = $this->createMatch($homeClub, $awayClub, [
            'competition_season_id' => $leagueSeason->id,
            'season_id' => $season->id,
            'type' => 'league',
        ]);
        $nationalCupMatch = $this->createMatch($homeClub, $awayClub, [
            'competition_season_id' => $nationalCupSeason->id,
            'season_id' => $season->id,
            'type' => 'cup',
        ]);
        $internationalCupMatch = $this->createMatch($homeClub, $awayClub, [
            'competition_season_id' => $internationalCupSeason->id,
            'season_id' => $season->id,
            'type' => 'cup',
        ]);
        $friendlyMatch = $this->createMatch($homeClub, $awayClub, [
            'competition_season_id' => null,
            'season_id' => null,
            'type' => 'friendly',
        ]);

        $service = app(CompetitionContextService::class);

        $this->assertSame('league', $service->forMatch($leagueMatch));
        $this->assertSame('cup_national', $service->forMatch($nationalCupMatch));
        $this->assertSame('cup_international', $service->forMatch($internationalCupMatch));
        $this->assertSame('friendly', $service->forMatch($friendlyMatch));
    }

    public function test_live_start_persists_competition_context_on_match(): void
    {
        [$homeClub, $awayClub] = $this->createClubs();
        $this->createPlayer($homeClub, 'HCtx');
        $this->createPlayer($awayClub, 'ACtx');

        $match = $this->createMatch($homeClub, $awayClub, [
            'competition_season_id' => null,
            'season_id' => null,
            'type' => 'friendly',
            'competition_context' => null,
        ]);

        app(LiveMatchTickerService::class)->tick($match, 1);

        $match->refresh();
        $this->assertSame('friendly', $match->competition_context);
    }

    public function test_explicit_competition_scope_overrides_country_fallback_for_cup_context(): void
    {
        [$homeClub, $awayClub] = $this->createClubs();
        $season = Season::create([
            'name' => '2027/28',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_current' => false,
        ]);
        $country = Country::create([
            'name' => 'Spanien',
            'iso_code' => 'ES',
            'fifa_code' => 'ESP',
        ]);

        $forcedInternational = Competition::create([
            'country_id' => $country->id,
            'name' => 'Forced International Cup',
            'short_name' => 'FIC',
            'type' => 'cup',
            'scope' => 'international',
            'tier' => 1,
            'is_active' => true,
        ]);
        $forcedNational = Competition::create([
            'country_id' => null,
            'name' => 'Forced National Cup',
            'short_name' => 'FNC',
            'type' => 'cup',
            'scope' => 'national',
            'tier' => 1,
            'is_active' => true,
        ]);

        $internationalSeason = $this->createCompetitionSeason($forcedInternational, $season, 'knockout');
        $nationalSeason = $this->createCompetitionSeason($forcedNational, $season, 'knockout');

        $internationalMatch = $this->createMatch($homeClub, $awayClub, [
            'competition_season_id' => $internationalSeason->id,
            'season_id' => $season->id,
            'type' => 'cup',
        ]);
        $nationalMatch = $this->createMatch($homeClub, $awayClub, [
            'competition_season_id' => $nationalSeason->id,
            'season_id' => $season->id,
            'type' => 'cup',
        ]);

        $service = app(CompetitionContextService::class);

        $this->assertSame('cup_international', $service->forMatch($internationalMatch));
        $this->assertSame('cup_national', $service->forMatch($nationalMatch));
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
            'name' => 'Context Home',
            'country' => 'Deutschland',
            'league' => 'Context League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
        ]);
        $awayClub = Club::create([
            'user_id' => $awayUser->id,
            'name' => 'Context Away',
            'country' => 'Deutschland',
            'league' => 'Context League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
        ]);

        return [$homeClub, $awayClub];
    }

    private function createCompetitionSeason(Competition $competition, Season $season, string $format): CompetitionSeason
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
    private function createMatch(Club $homeClub, Club $awayClub, array $overrides): GameMatch
    {
        return GameMatch::create(array_merge([
            'type' => 'friendly',
            'competition_context' => null,
            'stage' => 'Context Match',
            'kickoff_at' => now()->subHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => random_int(10000, 99999),
        ], $overrides));
    }

    private function createPlayer(Club $club, string $prefix): void
    {
        foreach (range(1, 12) as $i) {
            $position = $i === 1 ? 'TW' : ($i <= 6 ? 'IV' : ($i <= 9 ? 'ZM' : 'ST'));

            \App\Models\Player::create([
                'club_id' => $club->id,
                'first_name' => $prefix.$i,
                'last_name' => 'Player',
                'position' => $position,
                'position_main' => $position,
                'preferred_foot' => 'right',
                'age' => 24,
                'overall' => 70,
                'potential' => 75,
                'pace' => 68,
                'shooting' => 67,
                'passing' => 68,
                'defending' => 66,
                'physical' => 67,
                'stamina' => 74,
                'morale' => 72,
                'status' => 'active',
                'market_value' => 250000,
                'salary' => 9000,
                'contract_expires_on' => now()->addYear()->toDateString(),
            ]);
        }
    }
}
