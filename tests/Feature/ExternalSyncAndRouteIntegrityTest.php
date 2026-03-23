<?php

namespace Tests\Feature;

use App\Jobs\SyncPlayerSofascoreJob;
use App\Models\Club;
use App\Models\Player;
use App\Models\User;
use App\Modules\DataCenter\Services\ScraperService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ExternalSyncAndRouteIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_transfer_history_sync_route_resolves_and_redirects(): void
    {
        $user = User::factory()->create();
        $club = $this->createClub($user, 'Owned Club');
        $player = $this->createPlayer($club, [
            'transfermarkt_url' => 'https://www.transfermarkt.de/test/profil/spieler/123',
        ]);

        $scraper = \Mockery::mock(ScraperService::class);
        $scraper->shouldReceive('getPlayerTransferHistory')->once()->andReturn([]);
        $this->app->instance(ScraperService::class, $scraper);

        $response = $this
            ->actingAs($user)
            ->from('/players/'.$player->id)
            ->post(route('players.sync-history', $player));

        $response->assertRedirect('/players/'.$player->id);
        $response->assertSessionHas('status');
    }

    public function test_admin_player_sync_route_dispatches_without_club_ownership(): void
    {
        Bus::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $club = $this->createClub($owner, 'Other Club');
        $player = $this->createPlayer($club, [
            'sofascore_id' => '998877',
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.players.edit', $player))
            ->post(route('admin.players.sync-sofascore', $player));

        $response->assertRedirect(route('admin.players.edit', $player));

        Bus::assertDispatched(SyncPlayerSofascoreJob::class);
    }

    public function test_external_sync_treats_empty_identifiers_as_missing(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $club = $this->createClub($admin, 'Sync Club');
        $player = $this->createPlayer($club, [
            'sofascore_id' => '',
            'transfermarkt_id' => '',
            'transfermarkt_url' => '',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.external-sync.index'));

        $response->assertOk();

        $page = $response->viewData('page');
        $props = $page['props'] ?? [];

        $this->assertSame(0, $props['stats']['with_sofascore']);
        $this->assertSame(0, $props['stats']['with_transfermarkt']);
        $this->assertContains($player->id, array_column($props['missingPlayers']['sofascore'], 'id'));
        $this->assertContains($player->id, array_column($props['missingPlayers']['transfermarkt'], 'id'));
    }

    private function createClub(User $user, string $name): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'is_cpu' => false,
            'name' => $name,
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 60,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createPlayer(Club $club, array $overrides = []): Player
    {
        return Player::create(array_merge([
            'club_id' => $club->id,
            'first_name' => 'Test',
            'last_name' => 'Player',
            'position' => 'ZM',
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => 68,
            'potential' => 73,
            'status' => 'active',
            'market_value' => 300000,
            'salary' => 10000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ], $overrides));
    }
}
