<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Country;
use App\Models\GameMatch;
use App\Models\MatchPlayerStat;
use App\Models\NationalTeam;
use App\Models\Player;
use App\Models\RandomEventOccurrence;
use App\Models\RandomEventTemplate;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaveThreeModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_admin_can_refresh_national_team_squad(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $country = Country::create([
            'name' => 'Deutschland',
            'iso_code' => 'DE',
            'fifa_code' => 'GER',
        ]);

        $club = $this->createClub($admin, 'National Pool FC', 'Deutschland');
        foreach (range(1, 14) as $i) {
            $this->createPlayer($club, 'NT'.$i, 60 + $i);
        }

        $team = NationalTeam::create([
            'country_id' => $country->id,
            'name' => 'Deutschland',
            'short_name' => 'GER',
            'manager_user_id' => $admin->id,
            'reputation' => 70,
            'tactical_style' => 'balanced',
        ]);

        $response = $this->actingAs($admin)->post(route('national-teams.refresh', $team));

        $response->assertStatus(302);
        $this->assertStringStartsWith(
            route('national-teams.index'),
            (string) $response->headers->get('Location')
        );
        $this->assertDatabaseCount('national_team_callups', 14);
        $this->assertDatabaseHas('national_team_callups', [
            'national_team_id' => $team->id,
            'status' => 'active',
            'role' => 'starter',
        ]);
    }

    public function test_admin_can_generate_team_of_the_day_from_match_stats(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $homeClub = $this->createClub($admin, 'Home TOTD', 'Deutschland');
        $awayUser = User::factory()->create();
        $awayClub = $this->createClub($awayUser, 'Away TOTD', 'Deutschland');

        $match = GameMatch::create([
            'type' => 'friendly',
            'stage' => 'Test',
            'round_number' => 1,
            'matchday' => 1,
            'kickoff_at' => now()->setTime(18, 0),
            'status' => 'played',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'home_score' => 2,
            'away_score' => 1,
            'played_at' => now()->setTime(19, 55),
            'simulation_seed' => 12345,
        ]);

        $players = collect();
        foreach (range(1, 11) as $i) {
            $position = $i === 1 ? 'GK' : ($i <= 5 ? 'DEF' : ($i <= 8 ? 'MID' : 'FWD'));
            $player = $this->createPlayer($homeClub, 'TOTD'.$i, 63 + $i, $position);
            $players->push($player);

            MatchPlayerStat::create([
                'match_id' => $match->id,
                'club_id' => $homeClub->id,
                'player_id' => $player->id,
                'lineup_role' => 'starter',
                'position_code' => $position,
                'rating' => 7.0 + ($i / 10),
                'minutes_played' => 90,
                'goals' => $position === 'FWD' ? 1 : 0,
                'assists' => $position === 'MID' ? 1 : 0,
                'yellow_cards' => 0,
                'red_cards' => 0,
                'shots' => 2,
                'passes_completed' => 20,
                'passes_failed' => 3,
                'tackles_won' => 1,
                'tackles_lost' => 0,
                'saves' => $position === 'GK' ? 3 : 0,
            ]);
        }

        $response = $this->actingAs($admin)->post(route('team-of-the-day.generate'), [
            'for_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('team_of_the_days', [
            'for_date' => now()->toDateString(),
        ]);
        $this->assertTrue(\App\Models\TeamOfTheDayPlayer::query()->count() >= 1);
    }

    public function test_user_can_trigger_and_apply_random_event(): void
    {
        $user = User::factory()->create();
        $club = $this->createClub($user, 'Event FC', 'Deutschland', 500000);

        RandomEventTemplate::create([
            'name' => 'Test Event',
            'category' => 'finance',
            'rarity' => 'common',
            'budget_delta_min' => 50000,
            'budget_delta_max' => 50000,
            'morale_delta' => 0,
            'stamina_delta' => 0,
            'overall_delta' => 0,
            'fan_mood_delta' => 1,
            'board_confidence_delta' => 1,
            'probability_weight' => 100,
            'is_active' => true,
            'description_template' => '{club} erhaelt {amount}.',
        ]);

        $triggerResponse = $this->actingAs($user)->post(route('random-events.trigger'), [
            'club_id' => $club->id,
        ]);

        $triggerResponse->assertRedirect(route('random-events.index', ['club' => $club->id]));
        $occurrence = RandomEventOccurrence::query()->latest('id')->first();
        $this->assertNotNull($occurrence);
        $this->assertSame('pending', $occurrence->status);

        $budgetBefore = (float) $club->budget;

        $applyResponse = $this->actingAs($user)->post(route('random-events.apply', [
            'occurrence' => $occurrence->id,
        ]));

        $applyResponse->assertRedirect(route('random-events.index', ['club' => $club->id]));
        $this->assertDatabaseHas('random_event_occurrences', [
            'id' => $occurrence->id,
            'status' => 'applied',
        ]);
        $this->assertTrue((float) $club->fresh()->budget > $budgetBefore);
    }

    private function createClub(User $user, string $name, string $country, float $budget = 550000): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'name' => $name,
            'country' => $country,
            'league' => 'Test League',
            'budget' => $budget,
            'wage_budget' => 220000,
            'reputation' => 60,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
        ]);
    }

    private function createPlayer(Club $club, string $name, int $overall, string $position = 'MID'): Player
    {
        return Player::create([
            'club_id' => $club->id,
            'first_name' => $name,
            'last_name' => 'Player',
            'position' => $position,
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => $overall,
            'potential' => min(99, $overall + 5),
            'pace' => 65,
            'shooting' => 64,
            'passing' => 67,
            'defending' => 60,
            'physical' => 63,
            'stamina' => 78,
            'morale' => 60,
            'status' => 'active',
            'market_value' => 350000,
            'salary' => 9000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ]);
    }
}
