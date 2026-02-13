<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use App\Models\User;
use App\Services\LiveMatchTickerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LiveStatePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_state_stores_phase_transitions_and_context_fields(): void
    {
        config()->set('simulation.deterministic.enabled', true);
        config()->set('simulation.sequence.min_per_minute', 1);
        config()->set('simulation.sequence.max_per_minute', 1);
        config()->set('simulation.probabilities.tackle_attempt', 1.0);
        config()->set('simulation.probabilities.foul_after_tackle_win', 1.0);
        config()->set('simulation.probabilities.penalty_awarded_after_foul', 1.0);
        config()->set('simulation.formulas.pass.base', 1.0);
        config()->set('simulation.formulas.pass.min', 1.0);
        config()->set('simulation.formulas.pass.max', 1.0);
        config()->set('simulation.formulas.tackle_win.base', 1.0);
        config()->set('simulation.formulas.tackle_win.min', 1.0);
        config()->set('simulation.formulas.tackle_win.max', 1.0);

        [$homeClub, $awayClub] = $this->createMatchClubs();
        $match = $this->createMatch($homeClub, $awayClub, 'friendly');
        $service = app(LiveMatchTickerService::class);

        $service->start($match);
        $match->update(['live_minute' => 44, 'status' => 'live']);
        $service->tick($match->fresh(), 2);

        $this->assertDatabaseHas('match_live_state_transitions', [
            'match_id' => $match->id,
            'transition_type' => 'phase_change',
            'to_phase' => 'second_half',
        ]);

        $homeState = DB::table('match_live_team_states')
            ->where('match_id', $match->id)
            ->where('club_id', $homeClub->id)
            ->first();
        $awayState = DB::table('match_live_team_states')
            ->where('match_id', $match->id)
            ->where('club_id', $awayClub->id)
            ->first();

        $this->assertNotNull($homeState);
        $this->assertNotNull($awayState);

        $hasBallCarrier = (int) ($homeState->current_ball_carrier_player_id ?? 0) > 0
            || (int) ($awayState->current_ball_carrier_player_id ?? 0) > 0;
        $hasSetPiece = (int) ($homeState->last_set_piece_taker_player_id ?? 0) > 0
            || (int) ($awayState->last_set_piece_taker_player_id ?? 0) > 0;

        $this->assertTrue($hasBallCarrier, 'Expected at least one persisted ball carrier.');
        $this->assertTrue($hasSetPiece, 'Expected at least one persisted set-piece taker.');
    }

    public function test_live_state_historizes_tactical_and_substitution_transitions(): void
    {
        [$homeClub, $awayClub] = $this->createMatchClubs();
        $match = $this->createMatch($homeClub, $awayClub, 'friendly');
        $service = app(LiveMatchTickerService::class);

        $service->tick($match, 5);
        $service->setTacticalStyle($match->fresh(), $homeClub->id, 'offensive');

        /** @var Lineup $lineup */
        $lineup = Lineup::query()
            ->with('players')
            ->where('match_id', $match->id)
            ->where('club_id', $homeClub->id)
            ->firstOrFail();

        /** @var Player|null $starterOut */
        $starterOut = $lineup->players
            ->first(fn (Player $player): bool => !(bool) $player->pivot->is_bench && strtoupper((string) $player->position) !== 'TW');
        /** @var Player|null $benchIn */
        $benchIn = $lineup->players
            ->first(fn (Player $player): bool => (bool) $player->pivot->is_bench && strtoupper((string) $player->position) !== 'TW');

        $this->assertNotNull($starterOut);
        $this->assertNotNull($benchIn);

        $service->makeSubstitution(
            $match->fresh(),
            $homeClub->id,
            (int) $starterOut->id,
            (int) $benchIn->id,
            (string) $starterOut->pivot->pitch_position
        );

        $this->assertDatabaseHas('match_live_state_transitions', [
            'match_id' => $match->id,
            'club_id' => $homeClub->id,
            'transition_type' => 'tactical_change',
        ]);
        $this->assertDatabaseHas('match_live_state_transitions', [
            'match_id' => $match->id,
            'club_id' => $homeClub->id,
            'transition_type' => 'substitution',
        ]);
    }

    public function test_live_state_persists_minute_snapshots_and_planned_substitution_transitions(): void
    {
        config()->set('simulation.sequence.min_per_minute', 0);
        config()->set('simulation.sequence.max_per_minute', 0);
        config()->set('simulation.probabilities.random_injury_per_minute', 0.0);

        [$homeClub, $awayClub] = $this->createMatchClubs();
        $match = $this->createMatch($homeClub, $awayClub, 'friendly');
        $service = app(LiveMatchTickerService::class);

        $service->tick($match, 5);
        $match->refresh();

        /** @var Lineup $lineup */
        $lineup = Lineup::query()
            ->with('players')
            ->where('match_id', $match->id)
            ->where('club_id', $homeClub->id)
            ->firstOrFail();

        /** @var Player|null $starterOut */
        $starterOut = $lineup->players
            ->first(fn (Player $player): bool => !(bool) $player->pivot->is_bench && strtoupper((string) $player->position) !== 'TW');
        /** @var Player|null $benchIn */
        $benchIn = $lineup->players
            ->first(fn (Player $player): bool => (bool) $player->pivot->is_bench && strtoupper((string) $player->position) !== 'TW');

        $this->assertNotNull($starterOut);
        $this->assertNotNull($benchIn);

        $plannedMinute = (int) $match->live_minute + 2;
        $service->planSubstitution(
            $match->fresh(),
            $homeClub->id,
            (int) $starterOut->id,
            (int) $benchIn->id,
            $plannedMinute,
            'any',
            (string) $starterOut->pivot->pitch_position
        );

        $this->assertDatabaseHas('match_live_state_transitions', [
            'match_id' => $match->id,
            'club_id' => $homeClub->id,
            'transition_type' => 'substitution_plan_scheduled',
        ]);

        $service->tick($match->fresh(), 2);

        $this->assertDatabaseHas('match_live_state_transitions', [
            'match_id' => $match->id,
            'club_id' => $homeClub->id,
            'transition_type' => 'substitution_plan_executed',
        ]);

        $this->assertDatabaseHas('match_live_minute_snapshots', [
            'match_id' => $match->id,
            'minute' => $plannedMinute,
            'executed_plans' => 1,
            'pending_plans' => 0,
        ]);
    }

    /**
     * @return array{0: Club, 1: Club}
     */
    private function createMatchClubs(): array
    {
        $user = User::factory()->create();
        $opponent = User::factory()->create();

        $homeClub = $this->createClub($user, 'Persist Home');
        $awayClub = $this->createClub($opponent, 'Persist Away');
        $this->createSquad($homeClub, 'PH');
        $this->createSquad($awayClub, 'PA');

        return [$homeClub, $awayClub];
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
            'season_objective' => 'mid_table',
        ]);
    }

    private function createMatch(Club $homeClub, Club $awayClub, string $type): GameMatch
    {
        return GameMatch::create([
            'type' => $type,
            'stage' => ucfirst($type),
            'kickoff_at' => now()->subHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 73331,
        ]);
    }

    private function createSquad(Club $club, string $prefix): void
    {
        $positions = [
            'TW', 'LV', 'IV', 'IV', 'RV', 'LM', 'ZM', 'ZM', 'RM', 'ST', 'ST',
            'ZM', 'ST', 'LV', 'RM', 'ST',
        ];

        foreach ($positions as $index => $position) {
            $this->createPlayer($club, $prefix.($index + 1), $position, 72 - $index);
        }
    }

    private function createPlayer(Club $club, string $name, string $position, int $overall): Player
    {
        return Player::create([
            'club_id' => $club->id,
            'first_name' => $name,
            'last_name' => 'Player',
            'position' => $position,
            'position_main' => $position,
            'preferred_foot' => 'right',
            'age' => 24,
            'overall' => $overall,
            'potential' => min(99, $overall + 5),
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
