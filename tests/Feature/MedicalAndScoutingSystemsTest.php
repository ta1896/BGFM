<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Player;
use App\Models\PlayerInjury;
use App\Models\ScoutingDiscovery;
use App\Models\ScoutingScout;
use App\Models\ScoutingWatchlist;
use App\Models\User;
use App\Services\InjuryManagementService;
use App\Services\ScoutingService;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalAndScoutingSystemsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_medical_clearance_updates_player_and_injury_status(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $club = $this->createClub($user, 'Medical FC', false);
        $player = $this->createPlayer($club, 'Medical', 'Case', 'IV', 71);

        $injury = PlayerInjury::create([
            'player_id' => $player->id,
            'club_id' => $club->id,
            'injury_type' => 'Muskelverletzung',
            'body_area' => 'Bein',
            'severity' => 'major',
            'started_at' => now()->subDays(4),
            'expected_return_at' => now()->addDays(2),
            'status' => 'active',
            'source' => 'training',
            'rehab_intensity' => 'medium',
            'return_phase' => 'partial',
            'availability_status' => 'bench_only',
            'setback_risk' => 28,
        ]);

        app(InjuryManagementService::class)->updateClearance($player, [
            'availability_status' => 'available',
            'return_phase' => 'full',
            'notes' => 'Return approved.',
        ]);

        $player->refresh();
        $injury->refresh();

        $this->assertSame('fit', $player->medical_status);
        $this->assertSame('available', $injury->availability_status);
        $this->assertNotNull($injury->cleared_at);
        $this->assertStringContainsString('Return approved.', (string) $injury->notes);
    }

    public function test_advancing_watchlist_builds_progress_and_creates_report_when_threshold_reached(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $managerClub = $this->createClub($user, 'Scout FC', false);
        $cpu = User::factory()->create();
        $targetClub = $this->createClub($cpu, 'Target FC', true);
        $player = $this->createPlayer($targetClub, 'Scout', 'Target', 'ZM', 74);

        $watchlist = ScoutingWatchlist::create([
            'club_id' => $managerClub->id,
            'player_id' => $player->id,
            'created_by_user_id' => $user->id,
            'priority' => 'high',
            'status' => 'watching',
            'focus' => 'medical',
            'scout_level' => 'elite',
            'scout_region' => 'global',
            'scout_type' => 'video',
            'progress' => 54,
            'reports_requested' => 0,
            'notes' => 'Initial tracking',
        ]);

        $budgetBefore = (float) $managerClub->budget;
        $report = app(ScoutingService::class)->advanceWatchlist($watchlist->loadMissing('player'), $user->id);

        $managerClub->refresh();
        $watchlist->refresh();

        $this->assertGreaterThanOrEqual(55, $watchlist->progress);
        $this->assertNotNull($watchlist->last_scouted_at);
        $this->assertNotNull($watchlist->next_report_due_at);
        $this->assertGreaterThanOrEqual(0, $watchlist->mission_days_left);
        $this->assertGreaterThan(0, (float) $watchlist->last_mission_cost);
        $this->assertGreaterThan(0, $watchlist->reports_requested);
        $this->assertNotNull($report);
        $this->assertLessThan($budgetBefore, (float) $managerClub->budget);
        $this->assertDatabaseHas('scouting_reports', [
            'watchlist_id' => $watchlist->id,
            'player_id' => $player->id,
            'club_id' => $managerClub->id,
        ]);
        $this->assertDatabaseHas('club_financial_transactions', [
            'club_id' => $managerClub->id,
            'reference_type' => 'scouting_mission',
            'reference_id' => $watchlist->id,
        ]);
    }

    public function test_scout_pool_is_created_and_watchlist_gets_assigned_busy_scout(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $managerClub = $this->createClub($user, 'Staff FC', false);
        $cpu = User::factory()->create();
        $targetClub = $this->createClub($cpu, 'Remote FC', true);
        $player = $this->createPlayer($targetClub, 'Scout', 'Asset', 'MS', 73);

        config(['simulation.modules.scouting_center.scout_slots' => 3]);

        $service = app(ScoutingService::class);
        $scouts = $service->ensureScoutPool($managerClub, $user->id);

        $this->assertCount(3, $scouts);

        $watchlist = $service->upsertWatchlist($player, $managerClub->id, $user->id, [
            'priority' => 'medium',
            'status' => 'watching',
            'focus' => 'medical',
            'scout_level' => 'experienced',
            'scout_region' => 'continental',
            'scout_type' => 'video',
            'scout_id' => $scouts->last()->id,
        ]);

        $assignedScout = $service->assignScoutToWatchlist($watchlist->fresh(['club', 'player.club', 'scout']), $scouts->last()->id);

        $this->assertSame((int) $scouts->last()->id, (int) $assignedScout->id);
        $this->assertSame((int) $assignedScout->id, (int) $watchlist->fresh()->scout_id);

        $service->advanceWatchlist($watchlist->fresh(['player', 'club', 'scout']), $user->id);

        $assignedScout->refresh();

        $this->assertSame('traveling', $assignedScout->status);
        $this->assertSame((int) $watchlist->id, (int) $assignedScout->active_watchlist_id);
        $this->assertNotNull($assignedScout->available_at);
        $this->assertGreaterThan(0, (int) $assignedScout->workload);
        $this->assertDatabaseHas('scouting_scouts', [
            'club_id' => $managerClub->id,
            'id' => $assignedScout->id,
            'status' => 'traveling',
        ]);
    }

    public function test_discover_targets_creates_persisted_leads_and_charges_budget(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $managerClub = $this->createClub($user, 'Discovery FC', false);
        $cpu = User::factory()->create();
        $targetClub = $this->createClub($cpu, 'Lead FC', true);

        $this->createPlayer($targetClub, 'Lead', 'One', 'ST', 72);
        $this->createPlayer($targetClub, 'Lead', 'Two', 'RW', 74);
        $this->createPlayer($targetClub, 'Lead', 'Three', 'LW', 71);

        $budgetBefore = (float) $managerClub->budget;

        $result = app(ScoutingService::class)->discoverTargets($managerClub, [
            'market' => 'domestic',
            'position' => 'ATT',
            'age_band' => 'all',
            'value_band' => 'all',
            'discovery_level' => 'experienced',
        ], $user->id);

        $managerClub->refresh();

        $this->assertGreaterThan(0, $result['count']);
        $this->assertLessThan($budgetBefore, (float) $managerClub->budget);
        $this->assertDatabaseHas('club_financial_transactions', [
            'club_id' => $managerClub->id,
            'reference_type' => 'scouting_discovery_scan',
        ]);
        $this->assertGreaterThan(0, ScoutingDiscovery::query()->where('club_id', $managerClub->id)->count());
    }

    public function test_discovery_scan_respects_cooldown_for_same_filter_set(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $managerClub = $this->createClub($user, 'Cooldown FC', false);
        $cpu = User::factory()->create();
        $targetClub = $this->createClub($cpu, 'Cooldown Leads', true);

        $this->createPlayer($targetClub, 'Lead', 'One', 'ST', 72);
        $this->createPlayer($targetClub, 'Lead', 'Two', 'RW', 74);
        $this->createPlayer($targetClub, 'Lead', 'Three', 'LW', 71);

        config(['simulation.modules.scouting_center.scan_cooldown_minutes' => 60]);

        $service = app(ScoutingService::class);
        $filters = [
            'market' => 'domestic',
            'position' => 'ATT',
            'age_band' => 'all',
            'value_band' => 'all',
            'discovery_level' => 'experienced',
        ];

        $service->discoverTargets($managerClub, $filters, $user->id);

        $this->expectException(ValidationException::class);
        $service->discoverTargets($managerClub, $filters, $user->id);
    }

    public function test_discovery_pool_rotates_into_new_leads_after_rotation_window(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $managerClub = $this->createClub($user, 'Rotation FC', false);
        $cpu = User::factory()->create();
        $targetClub = $this->createClub($cpu, 'Rotation Leads', true);

        foreach (range(1, 14) as $index) {
            $this->createPlayer($targetClub, 'Lead', 'Pool '.$index, $index % 2 === 0 ? 'RW' : 'ST', 65 + $index);
        }

        config([
            'simulation.modules.scouting_center.scan_cooldown_minutes' => 10,
            'simulation.modules.scouting_center.rotation_window_minutes' => 30,
        ]);

        $service = app(ScoutingService::class);
        $filters = [
            'market' => 'domestic',
            'position' => 'ATT',
            'age_band' => 'all',
            'value_band' => 'all',
            'discovery_level' => 'experienced',
        ];

        $service->discoverTargets($managerClub, $filters, $user->id);
        $firstIds = ScoutingDiscovery::query()
            ->where('club_id', $managerClub->id)
            ->pluck('player_id')
            ->sort()
            ->values()
            ->all();
        $firstCount = count($firstIds);

        $this->travel(31)->minutes();

        $service->discoverTargets($managerClub, $filters, $user->id);
        $secondIds = ScoutingDiscovery::query()
            ->where('club_id', $managerClub->id)
            ->pluck('player_id')
            ->sort()
            ->values()
            ->all();

        $this->assertNotSame($firstIds, $secondIds);
        $this->assertGreaterThan($firstCount, count($secondIds));
    }

    private function createClub(User $user, string $name, bool $isCpu): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'is_cpu' => $isCpu,
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

    private function createPlayer(Club $club, string $firstName, string $lastName, string $position, int $overall): Player
    {
        return Player::create([
            'club_id' => $club->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'position' => $position,
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => $overall,
            'potential' => min(99, $overall + 5),
            'pace' => 66,
            'shooting' => 66,
            'passing' => 66,
            'defending' => 66,
            'physical' => 66,
            'stamina' => 70,
            'morale' => 65,
            'status' => 'active',
            'market_value' => 300000,
            'salary' => 10000,
            'contract_expires_on' => now()->addYear()->toDateString(),
        ]);
    }
}
