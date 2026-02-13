<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\GameNotification;
use App\Models\MatchLivePlayerState;
use App\Models\Player;
use App\Models\User;
use App\Services\LiveMatchTickerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchAftermathConsequencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_match_aftermath_applies_context_rules_and_creates_notifications(): void
    {
        config()->set('simulation.aftermath.injury.min_matches', 2);
        config()->set('simulation.aftermath.injury.max_matches', 2);
        config()->set('simulation.aftermath.suspension.league.min_matches', 3);
        config()->set('simulation.aftermath.suspension.league.max_matches', 3);
        config()->set('simulation.aftermath.notifications.enabled', true);
        config()->set('simulation.aftermath.contract_alert.enabled', true);
        config()->set('simulation.aftermath.contract_alert.days_threshold', 45);

        [$homeClub, $awayClub] = $this->createMatchClubs();
        $match = $this->createLeagueMatch($homeClub, $awayClub);
        $ticker = app(LiveMatchTickerService::class);

        $ticker->start($match);

        $injuredPlayer = $homeClub->players()->firstOrFail();
        $sentOffPlayer = $awayClub->players()->firstOrFail();
        $injuredPlayer->update([
            'contract_expires_on' => now()->addDays(20)->toDateString(),
        ]);

        MatchLivePlayerState::query()
            ->where('match_id', $match->id)
            ->where('player_id', $injuredPlayer->id)
            ->update([
                'is_injured' => true,
                'is_on_pitch' => false,
                'slot' => 'OUT-88',
            ]);

        MatchLivePlayerState::query()
            ->where('match_id', $match->id)
            ->where('player_id', $sentOffPlayer->id)
            ->update([
                'red_cards' => 1,
                'is_sent_off' => true,
                'is_on_pitch' => false,
                'slot' => 'OUT-88',
            ]);

        $match->update([
            'status' => 'live',
            'live_minute' => 90,
            'home_score' => 1,
            'away_score' => 0,
        ]);

        $ticker->tick($match->fresh(), 1);

        $injuredPlayer->refresh();
        $sentOffPlayer->refresh();

        $this->assertSame(2, (int) $injuredPlayer->injury_matches_remaining);
        $this->assertSame(0, (int) $injuredPlayer->suspension_league_remaining);
        $this->assertSame('injured', (string) $injuredPlayer->status);

        $this->assertSame(3, (int) $sentOffPlayer->suspension_league_remaining);
        $this->assertSame(0, (int) $sentOffPlayer->suspension_friendly_remaining);
        $this->assertSame(3, (int) $sentOffPlayer->suspension_matches_remaining);
        $this->assertSame('suspended', (string) $sentOffPlayer->status);

        $this->assertDatabaseHas('game_notifications', [
            'user_id' => $homeClub->user_id,
            'club_id' => $homeClub->id,
            'type' => 'match_aftermath',
            'title' => 'Nachwirkungen nach Spiel',
        ]);

        $this->assertDatabaseHas('game_notifications', [
            'user_id' => $awayClub->user_id,
            'club_id' => $awayClub->id,
            'type' => 'match_aftermath',
            'title' => 'Nachwirkungen nach Spiel',
        ]);

        $this->assertDatabaseHas('game_notifications', [
            'user_id' => $homeClub->user_id,
            'club_id' => $homeClub->id,
            'type' => 'contract_attention',
            'title' => 'Vertragsrisiko bei Ausfall',
        ]);

        $awayContractAlerts = GameNotification::query()
            ->where('user_id', $awayClub->user_id)
            ->where('type', 'contract_attention')
            ->count();
        $this->assertSame(0, $awayContractAlerts);
    }

    public function test_yellow_card_accumulation_triggers_context_suspension_in_match_series(): void
    {
        config()->set('simulation.sequence.min_per_minute', 0);
        config()->set('simulation.sequence.max_per_minute', 0);
        config()->set('simulation.probabilities.random_injury_per_minute', 0.0);
        config()->set('simulation.aftermath.yellow_cards.enabled', true);
        config()->set('simulation.aftermath.yellow_cards.league.threshold', 2);
        config()->set('simulation.aftermath.yellow_cards.league.suspension_matches', 1);
        config()->set('simulation.aftermath.contract_alert.enabled', false);

        [$homeClub, $awayClub] = $this->createMatchClubs();
        $player = $homeClub->players()->where('position', '!=', 'TW')->firstOrFail();
        $ticker = app(LiveMatchTickerService::class);

        $matchOne = $this->createLeagueMatch($homeClub, $awayClub, 70501);
        $ticker->start($matchOne);
        MatchLivePlayerState::query()
            ->where('match_id', $matchOne->id)
            ->where('player_id', $player->id)
            ->update(['yellow_cards' => 1]);
        $matchOne->update([
            'status' => 'live',
            'live_minute' => 90,
            'home_score' => 0,
            'away_score' => 0,
        ]);
        $ticker->tick($matchOne->fresh(), 1);

        $player->refresh();
        $this->assertSame(1, (int) $player->yellow_cards_league_accumulated);
        $this->assertSame(0, (int) $player->suspension_league_remaining);

        $matchTwo = $this->createLeagueMatch($homeClub, $awayClub, 70502);
        $ticker->start($matchTwo);
        MatchLivePlayerState::query()
            ->where('match_id', $matchTwo->id)
            ->where('player_id', $player->id)
            ->update(['yellow_cards' => 1]);
        $matchTwo->update([
            'status' => 'live',
            'live_minute' => 90,
            'home_score' => 0,
            'away_score' => 0,
        ]);
        $ticker->tick($matchTwo->fresh(), 1);

        $player->refresh();
        $this->assertSame(0, (int) $player->yellow_cards_league_accumulated);
        $this->assertSame(1, (int) $player->suspension_league_remaining);
        $this->assertSame(1, (int) $player->suspension_matches_remaining);

        $this->assertDatabaseHas('game_notifications', [
            'user_id' => $homeClub->user_id,
            'club_id' => $homeClub->id,
            'type' => 'match_aftermath',
        ]);

        $this->assertTrue(
            GameNotification::query()
                ->where('user_id', $homeClub->user_id)
                ->where('club_id', $homeClub->id)
                ->where('type', 'match_aftermath')
                ->where('message', 'like', '%Gelb-Sperre%')
                ->exists()
        );
    }

    /**
     * @return array{0: Club, 1: Club}
     */
    private function createMatchClubs(): array
    {
        $homeUser = User::factory()->create();
        $awayUser = User::factory()->create();

        $homeClub = $this->createClub($homeUser, 'Aftermath Home');
        $awayClub = $this->createClub($awayUser, 'Aftermath Away');

        $this->createSquad($homeClub, 'AH');
        $this->createSquad($awayClub, 'AA');

        return [$homeClub, $awayClub];
    }

    private function createClub(User $user, string $name): Club
    {
        return Club::create([
            'user_id' => $user->id,
            'name' => $name,
            'country' => 'Deutschland',
            'league' => 'Aftermath League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 60,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
        ]);
    }

    private function createLeagueMatch(Club $homeClub, Club $awayClub, int $seed = 70101): GameMatch
    {
        return GameMatch::create([
            'type' => 'league',
            'stage' => 'League',
            'kickoff_at' => now()->subHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => $seed,
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
