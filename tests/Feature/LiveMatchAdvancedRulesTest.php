<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\MatchLivePlayerState;
use App\Models\MatchLiveTeamState;
use App\Models\MatchPlannedSubstitution;
use App\Models\Player;
use App\Models\User;
use App\Services\LiveMatchTickerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LiveMatchAdvancedRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_state_and_actions_are_persisted(): void
    {
        [$homeClub, $awayClub] = $this->createMatchClubs();
        $match = $this->createMatch($homeClub, $awayClub, 'friendly');

        app(LiveMatchTickerService::class)->tick($match, 5);

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'status' => 'live',
            'live_minute' => 5,
        ]);
        $this->assertSame(2, MatchLiveTeamState::query()->where('match_id', $match->id)->count());
        $this->assertGreaterThan(0, MatchLivePlayerState::query()->where('match_id', $match->id)->count());
        $this->assertGreaterThan(0, DB::table('match_live_actions')->where('match_id', $match->id)->count());
    }

    public function test_tactical_changes_are_limited_by_interval_and_maximum(): void
    {
        [$homeClub, $awayClub] = $this->createMatchClubs();
        $match = $this->createMatch($homeClub, $awayClub, 'friendly');
        $service = app(LiveMatchTickerService::class);

        $service->tick($match, 5);
        $match->refresh();

        $service->setTacticalStyle($match, $homeClub->id, 'offensive');
        $state = MatchLiveTeamState::query()
            ->where('match_id', $match->id)
            ->where('club_id', $homeClub->id)
            ->firstOrFail();
        $this->assertSame('offensive', $state->tactical_style);
        $this->assertSame(1, (int) $state->tactical_changes_count);

        $service->setTacticalStyle($match->fresh(), $homeClub->id, 'defensive');
        $state->refresh();
        $this->assertSame('offensive', $state->tactical_style);
        $this->assertSame(1, (int) $state->tactical_changes_count);

        $match->update(['live_minute' => 16]);
        $service->setTacticalStyle($match->fresh(), $homeClub->id, 'defensive');
        $state->refresh();
        $this->assertSame('defensive', $state->tactical_style);
        $this->assertSame(2, (int) $state->tactical_changes_count);

        $match->update(['live_minute' => 27]);
        $service->setTacticalStyle($match->fresh(), $homeClub->id, 'counter');
        $state->refresh();
        $this->assertSame('counter', $state->tactical_style);
        $this->assertSame(3, (int) $state->tactical_changes_count);

        $match->update(['live_minute' => 38]);
        $service->setTacticalStyle($match->fresh(), $homeClub->id, 'balanced');
        $state->refresh();
        $this->assertSame('counter', $state->tactical_style);
        $this->assertSame(3, (int) $state->tactical_changes_count);
    }

    public function test_cup_match_finishes_with_penalty_shootout_after_120_minutes(): void
    {
        [$homeClub, $awayClub] = $this->createMatchClubs();
        $match = $this->createMatch($homeClub, $awayClub, 'cup');
        $service = app(LiveMatchTickerService::class);

        $service->start($match);
        $match->update([
            'status' => 'live',
            'live_minute' => 120,
            'home_score' => 1,
            'away_score' => 1,
        ]);

        $service->tick($match->fresh(), 1);

        $match->refresh();
        $this->assertSame('played', $match->status);
        $this->assertTrue((bool) $match->extra_time);
        $this->assertNotNull($match->penalties_home);
        $this->assertNotNull($match->penalties_away);
        $this->assertNotSame((int) $match->penalties_home, (int) $match->penalties_away);
    }

    public function test_injury_and_suspension_counters_carry_over_to_next_match(): void
    {
        [$homeClub, $awayClub] = $this->createMatchClubs();
        $service = app(LiveMatchTickerService::class);

        $firstMatch = $this->createMatch($homeClub, $awayClub, 'friendly');
        $service->start($firstMatch);

        $injuredPlayer = $homeClub->players()->firstOrFail();
        $sentOffPlayer = $awayClub->players()->firstOrFail();

        MatchLivePlayerState::query()
            ->where('match_id', $firstMatch->id)
            ->where('player_id', $injuredPlayer->id)
            ->update([
                'is_injured' => true,
                'is_on_pitch' => false,
                'slot' => 'OUT-89',
            ]);
        MatchLivePlayerState::query()
            ->where('match_id', $firstMatch->id)
            ->where('player_id', $sentOffPlayer->id)
            ->update([
                'red_cards' => 1,
                'is_sent_off' => true,
                'is_on_pitch' => false,
                'slot' => 'OUT-89',
            ]);

        $firstMatch->update([
            'status' => 'live',
            'live_minute' => 90,
            'home_score' => 1,
            'away_score' => 0,
        ]);
        $service->tick($firstMatch->fresh(), 1);

        $injuredPlayer->refresh();
        $sentOffPlayer->refresh();
        $this->assertGreaterThan(0, (int) $injuredPlayer->injury_matches_remaining);
        $this->assertGreaterThan(0, (int) $sentOffPlayer->suspension_matches_remaining);

        $injuryBefore = (int) $injuredPlayer->injury_matches_remaining;
        $suspensionBefore = (int) $sentOffPlayer->suspension_matches_remaining;

        $secondMatch = $this->createMatch($homeClub, $awayClub, 'friendly');
        $service->start($secondMatch);
        $secondMatch->update([
            'status' => 'live',
            'live_minute' => 90,
            'home_score' => 0,
            'away_score' => 0,
        ]);
        $service->tick($secondMatch->fresh(), 1);

        $injuredPlayer->refresh();
        $sentOffPlayer->refresh();
        $this->assertLessThanOrEqual($injuryBefore, (int) $injuredPlayer->injury_matches_remaining);
        $this->assertLessThanOrEqual($suspensionBefore, (int) $sentOffPlayer->suspension_matches_remaining);
    }

    public function test_substitution_validation_blocks_unavailable_player_and_invalid_goalkeeper_switch(): void
    {
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

        /** @var Player $goalkeeperStarter */
        $goalkeeperStarter = $lineup->players
            ->first(fn (Player $player): bool => !(bool) $player->pivot->is_bench && strtoupper((string) $player->position) === 'TW');
        /** @var Player $benchNonGoalkeeper */
        $benchNonGoalkeeper = $lineup->players
            ->first(fn (Player $player): bool => (bool) $player->pivot->is_bench && strtoupper((string) $player->position) !== 'TW');
        /** @var Player $starterNonGoalkeeper */
        $starterNonGoalkeeper = $lineup->players
            ->first(fn (Player $player): bool => !(bool) $player->pivot->is_bench && strtoupper((string) $player->position) !== 'TW');

        $this->assertNotNull($goalkeeperStarter);
        $this->assertNotNull($benchNonGoalkeeper);
        $this->assertNotNull($starterNonGoalkeeper);

        $beforeSubs = DB::table('match_events')
            ->where('match_id', $match->id)
            ->where('event_type', 'substitution')
            ->count();

        $service->makeSubstitution(
            $match->fresh(),
            $homeClub->id,
            (int) $goalkeeperStarter->id,
            (int) $benchNonGoalkeeper->id,
            'TW'
        );

        $afterGoalkeeperTry = DB::table('match_events')
            ->where('match_id', $match->id)
            ->where('event_type', 'substitution')
            ->count();
        $this->assertSame($beforeSubs, $afterGoalkeeperTry);

        $benchNonGoalkeeper->update([
            'status' => 'injured',
            'injury_matches_remaining' => 2,
        ]);

        $service->makeSubstitution(
            $match->fresh(),
            $homeClub->id,
            (int) $starterNonGoalkeeper->id,
            (int) $benchNonGoalkeeper->id,
            (string) $starterNonGoalkeeper->pivot->pitch_position
        );

        $afterUnavailableTry = DB::table('match_events')
            ->where('match_id', $match->id)
            ->where('event_type', 'substitution')
            ->count();
        $this->assertSame($beforeSubs, $afterUnavailableTry);
    }

    public function test_planned_substitution_is_executed_when_due_and_condition_matches(): void
    {
        [$homeClub, $awayClub] = $this->createMatchClubs();
        $match = $this->createMatch($homeClub, $awayClub, 'friendly');
        $service = app(LiveMatchTickerService::class);

        config()->set('simulation.sequence.min_per_minute', 0);
        config()->set('simulation.sequence.max_per_minute', 0);
        config()->set('simulation.probabilities.random_injury_per_minute', 0.0);

        $service->tick($match, 5);
        $match->refresh();

        /** @var Lineup $lineup */
        $lineup = Lineup::query()
            ->with('players')
            ->where('match_id', $match->id)
            ->where('club_id', $homeClub->id)
            ->firstOrFail();

        /** @var Player $starterOut */
        $starterOut = $lineup->players
            ->first(fn (Player $player): bool => !(bool) $player->pivot->is_bench && strtoupper((string) $player->position) !== 'TW');
        /** @var Player $benchIn */
        $benchIn = $lineup->players
            ->first(fn (Player $player): bool => (bool) $player->pivot->is_bench && strtoupper((string) $player->position) !== 'TW');

        $this->assertNotNull($starterOut);
        $this->assertNotNull($benchIn);

        $plannedMinute = (int) $match->live_minute + 2;
        $service->planSubstitution(
            $match->fresh(),
            (int) $homeClub->id,
            (int) $starterOut->id,
            (int) $benchIn->id,
            $plannedMinute,
            'any',
            (string) $starterOut->pivot->pitch_position
        );

        /** @var MatchPlannedSubstitution $plan */
        $plan = MatchPlannedSubstitution::query()
            ->where('match_id', $match->id)
            ->where('club_id', $homeClub->id)
            ->where('player_out_id', $starterOut->id)
            ->where('player_in_id', $benchIn->id)
            ->where('planned_minute', $plannedMinute)
            ->firstOrFail();

        $this->assertSame('pending', $plan->status);

        $service->tick($match->fresh(), 2);

        $plan->refresh();
        $this->assertSame('executed', $plan->status);
        $this->assertSame($plannedMinute, (int) $plan->executed_minute);

        $this->assertDatabaseHas('match_events', [
            'match_id' => $match->id,
            'club_id' => $homeClub->id,
            'event_type' => 'substitution',
        ]);
    }

    public function test_planned_substitution_is_skipped_when_score_condition_is_not_met(): void
    {
        [$homeClub, $awayClub] = $this->createMatchClubs();
        $match = $this->createMatch($homeClub, $awayClub, 'friendly');
        $service = app(LiveMatchTickerService::class);

        config()->set('simulation.sequence.min_per_minute', 0);
        config()->set('simulation.sequence.max_per_minute', 0);
        config()->set('simulation.probabilities.random_injury_per_minute', 0.0);

        $service->tick($match, 5);
        $match->refresh();
        $match->update([
            'home_score' => 0,
            'away_score' => 2,
        ]);

        /** @var Lineup $lineup */
        $lineup = Lineup::query()
            ->with('players')
            ->where('match_id', $match->id)
            ->where('club_id', $homeClub->id)
            ->firstOrFail();

        /** @var Player $starterOut */
        $starterOut = $lineup->players
            ->first(fn (Player $player): bool => !(bool) $player->pivot->is_bench && strtoupper((string) $player->position) !== 'TW');
        /** @var Player $benchIn */
        $benchIn = $lineup->players
            ->first(fn (Player $player): bool => (bool) $player->pivot->is_bench && strtoupper((string) $player->position) !== 'TW');

        $this->assertNotNull($starterOut);
        $this->assertNotNull($benchIn);

        $plannedMinute = (int) $match->live_minute + 2;
        $service->planSubstitution(
            $match->fresh(),
            (int) $homeClub->id,
            (int) $starterOut->id,
            (int) $benchIn->id,
            $plannedMinute,
            'leading',
            (string) $starterOut->pivot->pitch_position
        );

        /** @var MatchPlannedSubstitution $plan */
        $plan = MatchPlannedSubstitution::query()
            ->where('match_id', $match->id)
            ->where('club_id', $homeClub->id)
            ->where('player_out_id', $starterOut->id)
            ->where('player_in_id', $benchIn->id)
            ->where('planned_minute', $plannedMinute)
            ->firstOrFail();

        $beforeSubs = DB::table('match_events')
            ->where('match_id', $match->id)
            ->where('event_type', 'substitution')
            ->count();

        $service->tick($match->fresh(), 2);

        $plan->refresh();
        $this->assertSame('skipped', $plan->status);
        $this->assertSame($plannedMinute, (int) $plan->executed_minute);
        $this->assertSame('condition_not_met', (string) ($plan->metadata['reason'] ?? ''));

        $afterSubs = DB::table('match_events')
            ->where('match_id', $match->id)
            ->where('event_type', 'substitution')
            ->count();
        $this->assertSame($beforeSubs, $afterSubs);
    }

    /**
     * @return array{0: Club, 1: Club}
     */
    private function createMatchClubs(): array
    {
        $user = User::factory()->create();
        $opponent = User::factory()->create();

        $homeClub = $this->createClub($user, 'Advanced Home');
        $awayClub = $this->createClub($opponent, 'Advanced Away');
        $this->createSquad($homeClub, 'AH');
        $this->createSquad($awayClub, 'AA');

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
            'simulation_seed' => random_int(10000, 99999),
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
