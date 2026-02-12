<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\Country;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Sponsor;
use App\Models\Stadium;
use App\Models\StadiumProject;
use App\Models\TrainingCamp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WaveTwoModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_sign_sponsor_contract(): void
    {
        $user = User::factory()->create();
        $club = $this->createClub($user, 'Sponsor Club', 60);

        $sponsor = Sponsor::create([
            'name' => 'Test Sponsor',
            'tier' => 'regional',
            'reputation_min' => 50,
            'base_weekly_amount' => 25000,
            'signing_bonus_min' => 90000,
            'signing_bonus_max' => 100000,
            'is_active' => true,
        ]);

        $startBudget = (float) $club->budget;

        $response = $this->actingAs($user)->post(route('sponsors.sign', $sponsor), [
            'club_id' => $club->id,
            'months' => 12,
        ]);

        $response->assertRedirect(route('sponsors.index', ['club' => $club->id]));
        $this->assertDatabaseHas('sponsor_contracts', [
            'club_id' => $club->id,
            'sponsor_id' => $sponsor->id,
            'status' => 'active',
        ]);
        $this->assertTrue((float) $club->fresh()->budget > $startBudget);
    }

    public function test_due_stadium_project_and_training_camp_are_processed_by_matchday_command(): void
    {
        $user = User::factory()->create();
        $club = $this->createClub($user, 'Infra Club', 80, 850000);

        $stadium = Stadium::create([
            'club_id' => $club->id,
            'name' => 'Infra Arena',
            'capacity' => 20000,
            'covered_seats' => 10000,
            'vip_seats' => 1000,
            'ticket_price' => 20,
            'maintenance_cost' => 20000,
            'facility_level' => 2,
            'pitch_quality' => 62,
            'fan_experience' => 60,
            'security_level' => 58,
            'environment_level' => 57,
        ]);

        StadiumProject::create([
            'stadium_id' => $stadium->id,
            'project_type' => 'pitch',
            'level_from' => 62,
            'level_to' => 68,
            'cost' => 120000,
            'started_on' => now()->subDays(5)->toDateString(),
            'completes_on' => now()->subDay()->toDateString(),
            'status' => 'active',
        ]);

        $player = $this->createPlayer($club, 'Camp');
        TrainingCamp::create([
            'club_id' => $club->id,
            'created_by_user_id' => $user->id,
            'name' => 'Camp Done',
            'focus' => 'fitness',
            'intensity' => 'low',
            'starts_on' => now()->subDays(3)->toDateString(),
            'ends_on' => now()->subDay()->toDateString(),
            'cost' => 10000,
            'stamina_effect' => 2,
            'morale_effect' => 1,
            'overall_effect' => 0,
            'status' => 'active',
        ]);

        Artisan::call('game:process-matchday');

        $this->assertDatabaseHas('stadium_projects', [
            'stadium_id' => $stadium->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('stadiums', [
            'id' => $stadium->id,
            'pitch_quality' => 68,
        ]);
        $this->assertDatabaseHas('training_camps', [
            'club_id' => $club->id,
            'status' => 'completed',
        ]);
        $this->assertTrue((int) $player->fresh()->stamina >= 81);
    }

    public function test_matchday_command_creates_financial_settlement_for_simulated_match(): void
    {
        Carbon::setTestNow('2026-07-10 12:00:00');

        $homeUser = User::factory()->create();
        $awayUser = User::factory()->create();
        $homeClub = $this->createClub($homeUser, 'Home FC', 62, 600000);
        $awayClub = $this->createClub($awayUser, 'Away FC', 59, 600000);

        Stadium::create([
            'club_id' => $homeClub->id,
            'name' => 'Home Park',
            'capacity' => 22000,
            'covered_seats' => 12000,
            'vip_seats' => 1200,
            'ticket_price' => 19,
            'maintenance_cost' => 22000,
            'facility_level' => 2,
            'pitch_quality' => 64,
            'fan_experience' => 62,
            'security_level' => 60,
            'environment_level' => 60,
        ]);
        Stadium::create([
            'club_id' => $awayClub->id,
            'name' => 'Away Park',
            'capacity' => 21000,
            'covered_seats' => 11000,
            'vip_seats' => 1000,
            'ticket_price' => 18,
            'maintenance_cost' => 21000,
            'facility_level' => 2,
            'pitch_quality' => 62,
            'fan_experience' => 60,
            'security_level' => 59,
            'environment_level' => 58,
        ]);

        foreach (range(1, 12) as $i) {
            $this->createPlayer($homeClub, 'H'.$i);
            $this->createPlayer($awayClub, 'A'.$i);
        }

        $country = Country::create([
            'name' => 'Deutschland',
            'iso_code' => 'DE',
            'fifa_code' => 'GER',
        ]);

        $competition = Competition::create([
            'country_id' => $country->id,
            'name' => 'Settlement Liga',
            'short_name' => 'SL',
            'type' => 'league',
            'tier' => 1,
            'is_active' => true,
        ]);

        $season = \App\Models\Season::create([
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

        DB::table('season_club_registrations')->insert([
            [
                'competition_season_id' => $competitionSeason->id,
                'club_id' => $homeClub->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'competition_season_id' => $competitionSeason->id,
                'club_id' => $awayClub->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        GameMatch::create([
            'competition_season_id' => $competitionSeason->id,
            'season_id' => $season->id,
            'type' => 'league',
            'stage' => 'Regular Season',
            'round_number' => 1,
            'matchday' => 1,
            'kickoff_at' => now()->addDay(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 55555,
        ]);

        $homeBudgetBefore = (float) $homeClub->budget;
        $awayBudgetBefore = (float) $awayClub->budget;

        Artisan::call('game:process-matchday', [
            '--competition-season' => $competitionSeason->id,
        ]);

        $this->assertDatabaseCount('match_financial_settlements', 1);
        $this->assertNotEquals($homeBudgetBefore, (float) $homeClub->fresh()->budget);
        $this->assertNotEquals($awayBudgetBefore, (float) $awayClub->fresh()->budget);

        Carbon::setTestNow();
    }

    private function createClub(User $user, string $name, int $reputation = 60, float $budget = 500000): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'name' => $name,
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => $budget,
            'wage_budget' => 250000,
            'reputation' => $reputation,
            'fan_mood' => 55,
            'fanbase' => 150000,
        ]);
    }

    private function createPlayer(Club $club, string $name): Player
    {
        return Player::create([
            'club_id' => $club->id,
            'first_name' => $name,
            'last_name' => 'Player',
            'position' => 'ZM',
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => 65,
            'potential' => 70,
            'pace' => 66,
            'shooting' => 64,
            'passing' => 67,
            'defending' => 58,
            'physical' => 61,
            'stamina' => 80,
            'morale' => 50,
            'status' => 'active',
            'market_value' => 100000,
            'salary' => 8000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ]);
    }
}
