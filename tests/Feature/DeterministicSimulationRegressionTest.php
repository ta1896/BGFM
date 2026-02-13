<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\User;
use App\Services\LiveMatchTickerService;
use App\Services\MatchSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeterministicSimulationRegressionTest extends TestCase
{
    use RefreshDatabase;

    private const GOLDEN_MATCH_SIMULATION_HASH = '674f76e5a23c42dbdfe2997eb926e1bfd525b15f218ae06dcaf32d6dc3ff6f8d';

    private const GOLDEN_LIVE_TICKER_HASH = 'bff8016a28d7c429ed0612bc41091befdc7a3f0de5e981abb5397f6ffa94a38d';

    public function test_match_simulation_seed_produces_stable_golden_snapshot(): void
    {
        [$homeClub, $awayClub] = $this->createMatchClubs();
        $this->createSquad($homeClub, 'MGH');
        $this->createSquad($awayClub, 'MGA');

        $match = GameMatch::create([
            'type' => 'friendly',
            'stage' => 'Friendly',
            'kickoff_at' => now()->subHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 73011,
        ]);

        app(MatchSimulationService::class)->simulate($match);
        $snapshot = $this->matchSimulationSnapshot($match->fresh([
            'homeClub',
            'awayClub',
            'events.player',
            'events.assister',
            'events.club',
            'playerStats.player',
            'playerStats.club',
        ]));

        $hash = hash('sha256', json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->assertSame(self::GOLDEN_MATCH_SIMULATION_HASH, $hash);
    }

    public function test_live_ticker_seed_produces_stable_golden_snapshot(): void
    {
        config()->set('simulation.deterministic.enabled', true);

        [$homeClub, $awayClub] = $this->createMatchClubs();
        $this->createSquad($homeClub, 'LGH');
        $this->createSquad($awayClub, 'LGA');

        $match = GameMatch::create([
            'type' => 'friendly',
            'stage' => 'Friendly',
            'kickoff_at' => now()->subHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 73021,
        ]);

        $service = app(LiveMatchTickerService::class);
        $service->tick($match, 18);

        $snapshot = $this->liveTickerSnapshot($match->fresh([
            'homeClub',
            'awayClub',
            'events.player',
            'events.assister',
            'events.club',
            'liveTeamStates.club',
            'livePlayerStates.player',
            'liveActions.club',
            'liveActions.player',
            'liveActions.opponentPlayer',
        ]));

        $hash = hash('sha256', json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->assertSame(self::GOLDEN_LIVE_TICKER_HASH, $hash);
    }

    /**
     * @return array{0: Club, 1: Club}
     */
    private function createMatchClubs(): array
    {
        $homeUser = User::factory()->create();
        $awayUser = User::factory()->create();

        $homeClub = $this->createClub($homeUser, 'Golden Home');
        $awayClub = $this->createClub($awayUser, 'Golden Away');

        return [$homeClub, $awayClub];
    }

    private function createClub(User $user, string $name): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'is_cpu' => false,
            'name' => $name,
            'short_name' => substr(strtoupper($name), 0, 12),
            'slug' => str()->slug($name).'-'.$user->id,
            'country' => 'Deutschland',
            'league' => 'Test League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 60,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
            'training_level' => 1,
        ]);
    }

    private function createSquad(Club $club, string $prefix): void
    {
        $positions = [
            'TW', 'LV', 'IV', 'IV', 'RV', 'LM', 'ZM', 'ZM', 'RM', 'ST', 'ST',
            'ZM', 'ST', 'LV', 'RM', 'ST',
        ];

        foreach ($positions as $index => $position) {
            Player::create([
                'club_id' => $club->id,
                'first_name' => $prefix.($index + 1),
                'last_name' => 'Player',
                'position' => $position,
                'position_main' => $position,
                'preferred_foot' => 'right',
                'age' => 24,
                'overall' => 75 - $index,
                'potential' => min(99, 80 - $index),
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

    /**
     * @return array<string, mixed>
     */
    private function matchSimulationSnapshot(GameMatch $match): array
    {
        return [
            'status' => (string) $match->status,
            'score' => [(int) $match->home_score, (int) $match->away_score],
            'attendance' => (int) ($match->attendance ?? 0),
            'weather' => (string) ($match->weather ?? ''),
            'events' => $match->events
                ->sortBy(fn ($event) => ($event->minute * 100) + $event->second)
                ->values()
                ->map(fn ($event): array => [
                    'minute' => (int) $event->minute,
                    'second' => (int) $event->second,
                    'type' => (string) $event->event_type,
                    'club' => (string) ($event->club?->short_name ?: $event->club?->name),
                    'player' => (string) ($event->player?->full_name ?? ''),
                    'assister' => (string) ($event->assister?->full_name ?? ''),
                ])
                ->all(),
            'player_stats' => $match->playerStats
                ->sortBy(fn ($row) => ($row->club_id * 1000) + $row->player_id)
                ->values()
                ->map(fn ($row): array => [
                    'club' => (string) ($row->club?->short_name ?: $row->club?->name),
                    'player' => (string) ($row->player?->full_name ?? ''),
                    'role' => (string) $row->lineup_role,
                    'minutes' => (int) $row->minutes_played,
                    'goals' => (int) $row->goals,
                    'assists' => (int) $row->assists,
                    'yellow' => (int) $row->yellow_cards,
                    'red' => (int) $row->red_cards,
                    'shots' => (int) $row->shots,
                    'passes_completed' => (int) $row->passes_completed,
                    'passes_failed' => (int) $row->passes_failed,
                    'tackles_won' => (int) $row->tackles_won,
                    'tackles_lost' => (int) $row->tackles_lost,
                    'saves' => (int) $row->saves,
                    'rating' => (float) $row->rating,
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function liveTickerSnapshot(GameMatch $match): array
    {
        return [
            'status' => (string) $match->status,
            'minute' => (int) $match->live_minute,
            'score' => [(int) $match->home_score, (int) $match->away_score],
            'attendance' => (int) ($match->attendance ?? 0),
            'weather' => (string) ($match->weather ?? ''),
            'events' => $match->events
                ->sortBy(fn ($event) => ($event->minute * 100) + $event->second)
                ->values()
                ->map(fn ($event): array => [
                    'minute' => (int) $event->minute,
                    'second' => (int) $event->second,
                    'type' => (string) $event->event_type,
                    'club' => (string) ($event->club?->short_name ?: $event->club?->name),
                    'player' => (string) ($event->player?->full_name ?? ''),
                    'assister' => (string) ($event->assister?->full_name ?? ''),
                ])
                ->all(),
            'team_states' => $match->liveTeamStates
                ->sortBy('club_id')
                ->values()
                ->map(fn ($state): array => [
                    'club' => (string) ($state->club?->short_name ?: $state->club?->name),
                    'style' => (string) $state->tactical_style,
                    'possession' => (int) $state->possession_seconds,
                    'actions' => (int) $state->actions_count,
                    'dangerous' => (int) $state->dangerous_attacks,
                    'shots' => (int) $state->shots,
                    'shots_on_target' => (int) $state->shots_on_target,
                    'goals' => $state->club_id === $match->home_club_id ? (int) $match->home_score : (int) $match->away_score,
                ])
                ->all(),
            'actions' => $match->liveActions
                ->sortBy(fn ($action) => ($action->minute * 100000) + ($action->second * 1000) + $action->sequence)
                ->values()
                ->map(fn ($action): array => [
                    'minute' => (int) $action->minute,
                    'second' => (int) $action->second,
                    'seq' => (int) $action->sequence,
                    'club' => (string) ($action->club?->short_name ?: $action->club?->name),
                    'player' => (string) ($action->player?->full_name ?? ''),
                    'type' => (string) $action->action_type,
                    'outcome' => (string) ($action->outcome ?? ''),
                ])
                ->all(),
        ];
    }
}
