<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlayerClubBackfillCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfill_command_repairs_player_and_club_fields(): void
    {
        $manager = User::factory()->create();

        $club = Club::create([
            'user_id' => $manager->id,
            'is_cpu' => false,
            'name' => 'Backfill Club Eins',
            'short_name' => null,
            'slug' => null,
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 60,
            'fan_mood' => 55,
            'fanbase' => 50000,
            'board_confidence' => 55,
        ]);

        $playerFromLegacy = $this->createPlayer($club, [
            'first_name' => 'Legacy',
            'position' => 'zm',
            'position_main' => null,
            'position_second' => 'ZM',
            'position_third' => 'ZM',
            'status' => 'active',
            'suspension_matches_remaining' => 2,
            'suspension_league_remaining' => 0,
            'suspension_cup_national_remaining' => 0,
            'suspension_cup_international_remaining' => 0,
            'suspension_friendly_remaining' => 0,
        ]);

        $playerWithContextSuspension = $this->createPlayer($club, [
            'first_name' => 'Context',
            'position' => 'IV',
            'position_main' => 'iv',
            'position_second' => 'IV',
            'position_third' => 'LV',
            'status' => 'active',
            'suspension_matches_remaining' => 0,
            'suspension_league_remaining' => 0,
            'suspension_cup_national_remaining' => 1,
            'suspension_cup_international_remaining' => 0,
            'suspension_friendly_remaining' => 0,
        ]);

        $playerWithStaleStatus = $this->createPlayer($club, [
            'first_name' => 'Status',
            'position' => 'ST',
            'position_main' => 'ST',
            'status' => 'suspended',
            'suspension_matches_remaining' => 0,
            'suspension_league_remaining' => 0,
            'suspension_cup_national_remaining' => 0,
            'suspension_cup_international_remaining' => 0,
            'suspension_friendly_remaining' => 0,
        ]);

        $exitCode = Artisan::call('game:backfill-player-club-model', ['--chunk' => 100]);
        $this->assertSame(0, $exitCode);

        $this->assertDatabaseHas('clubs', [
            'id' => $club->id,
            'slug' => 'backfill-club-eins',
            'short_name' => 'BACKFILL CLU',
        ]);

        $this->assertDatabaseHas('players', [
            'id' => $playerFromLegacy->id,
            'position_main' => 'ZM',
            'position_second' => null,
            'position_third' => null,
            'suspension_league_remaining' => 2,
            'suspension_matches_remaining' => 2,
            'status' => 'suspended',
        ]);

        $this->assertDatabaseHas('players', [
            'id' => $playerWithContextSuspension->id,
            'position_main' => 'IV',
            'position_second' => 'LV',
            'position_third' => null,
            'suspension_matches_remaining' => 1,
            'status' => 'suspended',
        ]);

        $this->assertDatabaseHas('players', [
            'id' => $playerWithStaleStatus->id,
            'status' => 'active',
        ]);
    }

    public function test_backfill_command_dry_run_does_not_persist_changes(): void
    {
        $manager = User::factory()->create();
        $club = Club::create([
            'user_id' => $manager->id,
            'is_cpu' => false,
            'name' => 'Dry Run Club',
            'short_name' => null,
            'slug' => null,
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 60,
            'fan_mood' => 55,
            'fanbase' => 50000,
            'board_confidence' => 55,
        ]);

        $player = $this->createPlayer($club, [
            'position' => 'ZM',
            'position_main' => null,
            'status' => 'active',
            'suspension_matches_remaining' => 1,
            'suspension_league_remaining' => 0,
            'suspension_cup_national_remaining' => 0,
            'suspension_cup_international_remaining' => 0,
            'suspension_friendly_remaining' => 0,
        ]);

        $exitCode = Artisan::call('game:backfill-player-club-model', ['--dry-run' => true, '--chunk' => 100]);
        $this->assertSame(0, $exitCode);

        $player->refresh();
        $club->refresh();

        $this->assertNull($player->position_main);
        $this->assertSame(0, (int) $player->suspension_league_remaining);
        $this->assertNull($club->slug);
        $this->assertNull($club->short_name);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createPlayer(Club $club, array $overrides = []): Player
    {
        return Player::create(array_merge([
            'club_id' => $club->id,
            'first_name' => 'Backfill',
            'last_name' => 'Player',
            'position' => 'ZM',
            'position_main' => null,
            'position_second' => null,
            'position_third' => null,
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => 68,
            'potential' => 74,
            'pace' => 65,
            'shooting' => 65,
            'passing' => 65,
            'defending' => 65,
            'physical' => 65,
            'stamina' => 70,
            'morale' => 70,
            'status' => 'active',
            'market_value' => 300000,
            'salary' => 10000,
            'contract_expires_on' => now()->addYear()->toDateString(),
            'injury_matches_remaining' => 0,
            'suspension_matches_remaining' => 0,
            'suspension_league_remaining' => 0,
            'suspension_cup_national_remaining' => 0,
            'suspension_cup_international_remaining' => 0,
            'suspension_friendly_remaining' => 0,
        ], $overrides));
    }
}

