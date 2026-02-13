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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeasonProgressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_matchday_command_simulates_next_matchday_and_creates_cpu_lineup(): void
    {
        [$competitionSeason, $managerClub, $cpuClub] = $this->createBasicLeagueWithCpuClub();

        GameMatch::create([
            'competition_season_id' => $competitionSeason->id,
            'season_id' => $competitionSeason->season_id,
            'type' => 'league',
            'stage' => 'Regular Season',
            'round_number' => 1,
            'matchday' => 1,
            'kickoff_at' => now()->addHour(),
            'status' => 'scheduled',
            'home_club_id' => $managerClub->id,
            'away_club_id' => $cpuClub->id,
            'stadium_club_id' => $managerClub->id,
            'simulation_seed' => 44444,
        ]);

        Artisan::call('game:process-matchday', [
            '--competition-season' => $competitionSeason->id,
        ]);

        $this->assertDatabaseHas('matches', [
            'competition_season_id' => $competitionSeason->id,
            'status' => 'played',
            'matchday' => 1,
        ]);

        $this->assertDatabaseHas('lineups', [
            'club_id' => $cpuClub->id,
            'match_id' => GameMatch::query()->where('competition_season_id', $competitionSeason->id)->value('id'),
            'is_active' => true,
            'is_template' => false,
        ]);
    }

    public function test_command_finalizes_season_and_moves_promoted_and_relegated_clubs(): void
    {
        $manager = User::factory()->create();

        $country = Country::create([
            'name' => 'Deutschland',
            'iso_code' => 'DE',
            'fifa_code' => 'GER',
        ]);

        $tierOne = Competition::create([
            'country_id' => $country->id,
            'name' => 'Liga 1',
            'short_name' => 'L1',
            'type' => 'league',
            'tier' => 1,
            'is_active' => true,
        ]);

        $tierTwo = Competition::create([
            'country_id' => $country->id,
            'name' => 'Liga 2',
            'short_name' => 'L2',
            'type' => 'league',
            'tier' => 2,
            'is_active' => true,
        ]);

        $season = Season::create([
            'name' => '2026/27',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_current' => true,
        ]);

        $firstLeagueSeason = CompetitionSeason::create([
            'competition_id' => $tierOne->id,
            'season_id' => $season->id,
            'format' => 'round_robin',
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 0,
            'relegated_slots' => 1,
            'is_finished' => false,
        ]);

        $secondLeagueSeason = CompetitionSeason::create([
            'competition_id' => $tierTwo->id,
            'season_id' => $season->id,
            'format' => 'round_robin',
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 1,
            'relegated_slots' => 0,
            'is_finished' => false,
        ]);

        $clubA = $this->createClub($manager, 'A FC', $tierOne);
        $clubB = $this->createClub($manager, 'B FC', $tierOne);
        $clubC = $this->createClub($manager, 'C FC', $tierTwo);
        $clubD = $this->createClub($manager, 'D FC', $tierTwo);

        $this->registerClub($firstLeagueSeason, $clubA->id);
        $this->registerClub($firstLeagueSeason, $clubB->id);
        $this->registerClub($secondLeagueSeason, $clubC->id);
        $this->registerClub($secondLeagueSeason, $clubD->id);

        $this->playedLeagueMatch($firstLeagueSeason, $clubA->id, $clubB->id, 2, 0, 1);
        $this->playedLeagueMatch($firstLeagueSeason, $clubB->id, $clubA->id, 0, 1, 2);
        $this->playedLeagueMatch($secondLeagueSeason, $clubC->id, $clubD->id, 3, 1, 1);
        $this->playedLeagueMatch($secondLeagueSeason, $clubD->id, $clubC->id, 0, 2, 2);

        Artisan::call('game:process-matchday');

        $nextSeason = Season::query()->where('name', '2027/28')->first();
        $this->assertNotNull($nextSeason);

        $tierOneNext = CompetitionSeason::query()
            ->where('competition_id', $tierOne->id)
            ->where('season_id', $nextSeason->id)
            ->first();
        $tierTwoNext = CompetitionSeason::query()
            ->where('competition_id', $tierTwo->id)
            ->where('season_id', $nextSeason->id)
            ->first();

        $this->assertNotNull($tierOneNext);
        $this->assertNotNull($tierTwoNext);

        $this->assertDatabaseHas('season_club_registrations', [
            'competition_season_id' => $tierOneNext->id,
            'club_id' => $clubC->id,
        ]);
        $this->assertDatabaseHas('season_club_registrations', [
            'competition_season_id' => $tierTwoNext->id,
            'club_id' => $clubB->id,
        ]);

        $this->assertDatabaseHas('clubs', [
            'id' => $clubC->id,
            'league_id' => $tierOne->id,
        ]);
        $this->assertDatabaseHas('clubs', [
            'id' => $clubB->id,
            'league_id' => $tierTwo->id,
        ]);
    }

    public function test_season_rollover_resets_yellow_card_accumulation_counters(): void
    {
        config()->set('simulation.aftermath.yellow_cards.reset_on_season_rollover', true);

        [$competitionSeason, $managerClub, $cpuClub] = $this->createBasicLeagueWithCpuClub();

        $managerPlayer = $managerClub->players()->firstOrFail();
        $cpuPlayer = $cpuClub->players()->firstOrFail();

        $managerPlayer->update([
            'yellow_cards_league_accumulated' => 4,
            'yellow_cards_cup_national_accumulated' => 2,
            'yellow_cards_cup_international_accumulated' => 1,
            'yellow_cards_friendly_accumulated' => 3,
        ]);
        $cpuPlayer->update([
            'yellow_cards_league_accumulated' => 3,
            'yellow_cards_cup_national_accumulated' => 1,
            'yellow_cards_cup_international_accumulated' => 2,
            'yellow_cards_friendly_accumulated' => 4,
        ]);

        Artisan::call('game:process-matchday', [
            '--competition-season' => $competitionSeason->id,
        ]);

        $managerPlayer->refresh();
        $cpuPlayer->refresh();

        $this->assertSame(0, (int) $managerPlayer->yellow_cards_league_accumulated);
        $this->assertSame(0, (int) $managerPlayer->yellow_cards_cup_national_accumulated);
        $this->assertSame(0, (int) $managerPlayer->yellow_cards_cup_international_accumulated);
        $this->assertSame(0, (int) $managerPlayer->yellow_cards_friendly_accumulated);

        $this->assertSame(0, (int) $cpuPlayer->yellow_cards_league_accumulated);
        $this->assertSame(0, (int) $cpuPlayer->yellow_cards_cup_national_accumulated);
        $this->assertSame(0, (int) $cpuPlayer->yellow_cards_cup_international_accumulated);
        $this->assertSame(0, (int) $cpuPlayer->yellow_cards_friendly_accumulated);
    }

    /**
     * @return array{CompetitionSeason, Club, Club}
     */
    private function createBasicLeagueWithCpuClub(): array
    {
        $manager = User::factory()->create();
        $cpuManager = User::factory()->create();

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
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_current' => true,
        ]);

        $competitionSeason = CompetitionSeason::create([
            'competition_id' => $competition->id,
            'season_id' => $season->id,
            'format' => 'round_robin',
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 0,
            'relegated_slots' => 0,
            'is_finished' => false,
        ]);

        $managerClub = $this->createClub($manager, 'Manager Club', $competition, false);
        $cpuClub = $this->createClub($cpuManager, 'CPU Club', $competition, true);

        $this->registerClub($competitionSeason, $managerClub->id);
        $this->registerClub($competitionSeason, $cpuClub->id);

        foreach (range(1, 12) as $index) {
            $this->createPlayerForClub($managerClub, $index);
            $this->createPlayerForClub($cpuClub, $index);
        }

        return [$competitionSeason, $managerClub, $cpuClub];
    }

    private function createClub(User $user, string $name, Competition $competition, bool $isCpu = false): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'is_cpu' => $isCpu,
            'name' => $name,
            'league' => $competition->name,
            'league_id' => $competition->id,
            'country' => 'Deutschland',
            'budget' => 500000,
            'wage_budget' => 250000,
            'reputation' => 60,
            'fan_mood' => 60,
        ]);
    }

    private function registerClub(CompetitionSeason $competitionSeason, int $clubId): void
    {
        DB::table('season_club_registrations')->insert([
            'competition_season_id' => $competitionSeason->id,
            'club_id' => $clubId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('season_club_statistics')->insert([
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPlayerForClub(Club $club, int $idx): void
    {
        $position = match (true) {
            $idx === 1 => 'TW',
            $idx <= 5 => 'IV',
            $idx <= 8 => 'ZM',
            default => 'ST',
        };

        Player::create([
            'club_id' => $club->id,
            'first_name' => 'P'.$idx,
            'last_name' => $club->id.'X',
            'position' => $position,
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => 60 + ($idx % 6),
            'potential' => 70,
            'pace' => 60,
            'shooting' => 60,
            'passing' => 60,
            'defending' => 60,
            'physical' => 60,
            'stamina' => 75,
            'morale' => 70,
            'status' => 'active',
            'market_value' => 100000,
            'salary' => 10000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ]);
    }

    private function playedLeagueMatch(
        CompetitionSeason $competitionSeason,
        int $homeClubId,
        int $awayClubId,
        int $homeScore,
        int $awayScore,
        int $matchday
    ): void {
        GameMatch::create([
            'competition_season_id' => $competitionSeason->id,
            'season_id' => $competitionSeason->season_id,
            'type' => 'league',
            'stage' => 'Regular Season',
            'round_number' => $matchday,
            'matchday' => $matchday,
            'kickoff_at' => now()->subDay(),
            'status' => 'played',
            'home_club_id' => $homeClubId,
            'away_club_id' => $awayClubId,
            'stadium_club_id' => $homeClubId,
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'played_at' => now()->subDay(),
            'simulation_seed' => 77777,
        ]);
    }
}
