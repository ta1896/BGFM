<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\User;
use App\Services\LiveMatchTickerService;
use App\Services\Simulation\Observers\MatchFinishedContext;
use App\Services\Simulation\Observers\MatchFinishedObserverPipeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MatchProcessingIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_match_postprocessing_steps_are_idempotent_on_retry(): void
    {
        [$homeClub, $awayClub] = $this->createMatchClubs();
        $match = GameMatch::create([
            'type' => 'friendly',
            'stage' => 'Friendly',
            'kickoff_at' => now()->subHour(),
            'status' => 'scheduled',
            'home_club_id' => $homeClub->id,
            'away_club_id' => $awayClub->id,
            'stadium_club_id' => $homeClub->id,
            'simulation_seed' => 61201,
        ]);

        $ticker = app(LiveMatchTickerService::class);
        $ticker->tick($match, 95);

        $match->refresh();
        $this->assertSame('played', $match->status);

        $stepsBefore = DB::table('match_processing_steps')
            ->where('match_id', $match->id)
            ->count();
        $settlementsBefore = DB::table('match_financial_settlements')
            ->where('match_id', $match->id)
            ->count();
        $transactionsBefore = DB::table('club_financial_transactions')
            ->where('reference_type', 'matches')
            ->count();
        $careerStatsBefore = DB::table('player_career_competition_statistics')->count();

        $this->assertSame(5, $stepsBefore);
        $this->assertSame(1, $settlementsBefore);

        $pipeline = app(MatchFinishedObserverPipeline::class);
        $pipeline->process(new MatchFinishedContext($match->fresh(), collect(), collect()));

        $finish = new \ReflectionMethod(LiveMatchTickerService::class, 'finish');
        $finish->setAccessible(true);
        $finish->invoke($ticker, $match->fresh());

        $stepsAfter = DB::table('match_processing_steps')
            ->where('match_id', $match->id)
            ->count();
        $settlementsAfter = DB::table('match_financial_settlements')
            ->where('match_id', $match->id)
            ->count();
        $transactionsAfter = DB::table('club_financial_transactions')
            ->where('reference_type', 'matches')
            ->count();
        $careerStatsAfter = DB::table('player_career_competition_statistics')->count();

        $this->assertSame($stepsBefore, $stepsAfter);
        $this->assertSame($settlementsBefore, $settlementsAfter);
        $this->assertSame($transactionsBefore, $transactionsAfter);
        $this->assertSame($careerStatsBefore, $careerStatsAfter);
    }

    /**
     * @return array{0: Club, 1: Club}
     */
    private function createMatchClubs(): array
    {
        $user = User::factory()->create();
        $opponent = User::factory()->create();

        $homeClub = $this->createClub($user, 'Idempotent Home');
        $awayClub = $this->createClub($opponent, 'Idempotent Away');
        $this->createSquad($homeClub, 'IH');
        $this->createSquad($awayClub, 'IA');

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
                'overall' => 73 - $index,
                'potential' => 80 - $index,
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
}
