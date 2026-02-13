<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\Country;
use App\Models\GameMatch;
use App\Models\Season;
use App\Models\User;
use App\Services\CompetitionContextService;
use App\Services\CupProgressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CupProgressionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_round_creates_bye_match_for_odd_number_of_winners(): void
    {
        $competitionSeason = $this->createNationalCupSeason();
        $clubs = collect(range(1, 6))->map(fn (int $i): Club => $this->createClub('Cup Club '.$i));

        $first = $this->createPlayedCupMatch($competitionSeason, (int) $clubs[0]->id, (int) $clubs[1]->id, 2, 0, 50001);
        $this->createPlayedCupMatch($competitionSeason, (int) $clubs[2]->id, (int) $clubs[3]->id, 1, 2, 50002);
        $this->createPlayedCupMatch($competitionSeason, (int) $clubs[4]->id, (int) $clubs[5]->id, 3, 1, 50003);

        app(CupProgressionService::class)->progressRoundIfNeeded($competitionSeason, $first);

        $roundTwoMatches = GameMatch::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->where('type', 'cup')
            ->where('round_number', 2)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $roundTwoMatches);

        $scheduled = $roundTwoMatches->where('status', 'scheduled')->first();
        $bye = $roundTwoMatches->first(fn (GameMatch $match): bool => (int) $match->home_club_id === (int) $match->away_club_id);

        $this->assertNotNull($scheduled);
        $this->assertNotNull($bye);
        $this->assertSame('cup_national', $scheduled->competition_context);
        $this->assertSame('cup_national', $bye->competition_context);
        $this->assertSame('Cup Runde 2', $scheduled->stage);
        $this->assertSame('Cup Runde 2 (Freilos)', $bye->stage);
        $this->assertSame('played', $bye->status);
        $this->assertSame(1, (int) $bye->home_score);
        $this->assertSame(0, (int) $bye->away_score);
        $this->assertSame((int) $clubs[0]->id, (int) $scheduled->home_club_id);
        $this->assertSame((int) $clubs[3]->id, (int) $scheduled->away_club_id);
    }

    public function test_progress_round_resolves_tied_played_match_without_penalties(): void
    {
        $competitionSeason = $this->createNationalCupSeason();
        $clubs = collect(range(1, 4))->map(fn (int $i): Club => $this->createClub('Tie Cup Club '.$i));

        $first = $this->createPlayedCupMatch($competitionSeason, (int) $clubs[0]->id, (int) $clubs[1]->id, 2, 1, 61000);
        $this->createPlayedCupMatch($competitionSeason, (int) $clubs[2]->id, (int) $clubs[3]->id, 1, 1, 61001);

        app(CupProgressionService::class)->progressRoundIfNeeded($competitionSeason, $first);

        $final = GameMatch::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->where('type', 'cup')
            ->where('round_number', 2)
            ->first();

        $this->assertNotNull($final);
        $this->assertSame('scheduled', $final->status);
        $this->assertSame('Finale', $final->stage);
        $this->assertSame((int) $clubs[0]->id, (int) $final->home_club_id);
        $this->assertSame((int) $clubs[3]->id, (int) $final->away_club_id);
    }

    public function test_progress_round_can_generate_two_legged_ties_when_enabled(): void
    {
        config()->set('simulation.cup.two_legged.enabled', true);
        config()->set('simulation.cup.two_legged.min_participants', 4);
        config()->set('simulation.cup.two_legged.max_participants', 16);
        config()->set('simulation.cup.two_legged.days_between_legs', 5);

        $competitionSeason = $this->createNationalCupSeason();
        $clubs = collect(range(1, 8))->map(fn (int $i): Club => $this->createClub('TwoLeg Club '.$i));

        $first = $this->createPlayedCupMatch($competitionSeason, (int) $clubs[0]->id, (int) $clubs[1]->id, 2, 0, 71001);
        $this->createPlayedCupMatch($competitionSeason, (int) $clubs[2]->id, (int) $clubs[3]->id, 1, 0, 71002);
        $this->createPlayedCupMatch($competitionSeason, (int) $clubs[4]->id, (int) $clubs[5]->id, 3, 1, 71003);
        $this->createPlayedCupMatch($competitionSeason, (int) $clubs[6]->id, (int) $clubs[7]->id, 2, 1, 71004);

        app(CupProgressionService::class)->progressRoundIfNeeded($competitionSeason, $first);

        $roundTwoMatches = GameMatch::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->where('type', 'cup')
            ->where('round_number', 2)
            ->orderBy('id')
            ->get();

        $this->assertCount(4, $roundTwoMatches);
        $this->assertSame(2, $roundTwoMatches->filter(fn (GameMatch $match): bool => str_contains((string) $match->stage, '(Hinspiel)'))->count());
        $this->assertSame(2, $roundTwoMatches->filter(fn (GameMatch $match): bool => str_contains((string) $match->stage, '(Rueckspiel)'))->count());

        $firstTieLeg1 = $roundTwoMatches->first(fn (GameMatch $match): bool => (int) $match->home_club_id === (int) $clubs[0]->id && (int) $match->away_club_id === (int) $clubs[2]->id);
        $firstTieLeg2 = $roundTwoMatches->first(fn (GameMatch $match): bool => (int) $match->home_club_id === (int) $clubs[2]->id && (int) $match->away_club_id === (int) $clubs[0]->id);
        $this->assertNotNull($firstTieLeg1);
        $this->assertNotNull($firstTieLeg2);
        $this->assertEquals(5.0, $firstTieLeg1->kickoff_at->diffInDays($firstTieLeg2->kickoff_at));
    }

    public function test_progress_round_resolves_two_legged_tie_with_away_goals_rule(): void
    {
        config()->set('simulation.cup.away_goals_rule', true);

        $competitionSeason = $this->createNationalCupSeason();
        $clubs = collect(range(1, 4))->map(fn (int $i): Club => $this->createClub('AwayGoal Club '.$i));

        $first = $this->createPlayedCupMatch(
            $competitionSeason,
            (int) $clubs[0]->id,
            (int) $clubs[1]->id,
            1,
            0,
            72001,
            2,
            'Halbfinale (Hinspiel)',
            now()->subDays(2)
        );
        $this->createPlayedCupMatch(
            $competitionSeason,
            (int) $clubs[1]->id,
            (int) $clubs[0]->id,
            2,
            1,
            72002,
            2,
            'Halbfinale (Rueckspiel)',
            now()->subDay()
        );
        $this->createPlayedCupMatch(
            $competitionSeason,
            (int) $clubs[2]->id,
            (int) $clubs[3]->id,
            1,
            0,
            72003,
            2,
            'Halbfinale'
        );

        app(CupProgressionService::class)->progressRoundIfNeeded($competitionSeason, $first);

        $final = GameMatch::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->where('type', 'cup')
            ->where('round_number', 3)
            ->first();

        $this->assertNotNull($final);
        $this->assertSame('Finale', $final->stage);
        $this->assertSame((int) $clubs[0]->id, (int) $final->home_club_id);
        $this->assertSame((int) $clubs[2]->id, (int) $final->away_club_id);
    }

    public function test_progress_round_books_advancement_rewards_and_notifications(): void
    {
        config()->set('simulation.cup.rewards.enabled', true);
        config()->set('simulation.cup.rewards.notifications.enabled', true);
        config()->set('simulation.cup.rewards.advancement.finale', 123456.0);

        $competitionSeason = $this->createNationalCupSeason();
        $clubs = collect(range(1, 4))->map(fn (int $i): Club => $this->createClub('Reward Club '.$i));

        $first = $this->createPlayedCupMatch($competitionSeason, (int) $clubs[0]->id, (int) $clubs[1]->id, 2, 0, 73001);
        $second = $this->createPlayedCupMatch($competitionSeason, (int) $clubs[2]->id, (int) $clubs[3]->id, 1, 0, 73002);

        app(CupProgressionService::class)->progressRoundIfNeeded($competitionSeason, $first);
        app(CupProgressionService::class)->progressRoundIfNeeded($competitionSeason, $second);

        $winnerIds = [(int) $clubs[0]->id, (int) $clubs[2]->id];

        foreach ($winnerIds as $clubId) {
            $this->assertDatabaseHas('cup_reward_logs', [
                'competition_season_id' => $competitionSeason->id,
                'club_id' => $clubId,
                'event_key' => 'advance:r1:to:r2',
                'stage' => 'Finale',
            ]);

            $this->assertDatabaseHas('club_financial_transactions', [
                'club_id' => $clubId,
                'context_type' => 'other',
                'direction' => 'income',
                'amount' => 123456.00,
                'reference_type' => 'cup_reward_logs',
            ]);

            $this->assertDatabaseHas('game_notifications', [
                'club_id' => $clubId,
                'type' => 'cup_achievement',
                'title' => 'Pokal-Fortschritt',
            ]);
        }

        $rewardLogsCount = DB::table('cup_reward_logs')
            ->where('competition_season_id', $competitionSeason->id)
            ->where('event_key', 'advance:r1:to:r2')
            ->count();
        $this->assertSame(2, $rewardLogsCount);
    }

    public function test_progress_round_books_champion_reward_once(): void
    {
        config()->set('simulation.cup.rewards.enabled', true);
        config()->set('simulation.cup.rewards.notifications.enabled', true);
        config()->set('simulation.cup.rewards.champion', 444444.0);

        $competitionSeason = $this->createNationalCupSeason();
        $clubs = collect(range(1, 2))->map(fn (int $i): Club => $this->createClub('Champion Club '.$i));

        $final = $this->createPlayedCupMatch(
            $competitionSeason,
            (int) $clubs[0]->id,
            (int) $clubs[1]->id,
            3,
            1,
            74001,
            3,
            'Finale'
        );

        app(CupProgressionService::class)->progressRoundIfNeeded($competitionSeason, $final);
        app(CupProgressionService::class)->progressRoundIfNeeded($competitionSeason, $final->fresh());

        $championClubId = (int) $clubs[0]->id;

        $this->assertDatabaseHas('cup_reward_logs', [
            'competition_season_id' => $competitionSeason->id,
            'club_id' => $championClubId,
            'event_key' => 'champion:r3',
            'stage' => 'Pokalsieger',
            'amount' => 444444.00,
        ]);

        $championRewardCount = DB::table('cup_reward_logs')
            ->where('competition_season_id', $competitionSeason->id)
            ->where('club_id', $championClubId)
            ->where('event_key', 'champion:r3')
            ->count();
        $this->assertSame(1, $championRewardCount);

        $this->assertDatabaseHas('club_financial_transactions', [
            'club_id' => $championClubId,
            'context_type' => 'other',
            'direction' => 'income',
            'amount' => 444444.00,
            'reference_type' => 'cup_reward_logs',
        ]);

        $this->assertDatabaseHas('game_notifications', [
            'club_id' => $championClubId,
            'type' => 'cup_achievement',
            'title' => 'Pokal-Fortschritt',
        ]);
    }

    private function createNationalCupSeason(): CompetitionSeason
    {
        $season = Season::create([
            'name' => '2026/27-cup',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_current' => true,
        ]);

        $country = Country::create([
            'name' => 'Deutschland',
            'iso_code' => 'DE',
            'fifa_code' => 'GER',
        ]);

        $competition = Competition::create([
            'country_id' => $country->id,
            'name' => 'Test Pokal',
            'short_name' => 'TPK',
            'type' => 'cup',
            'tier' => 1,
            'is_active' => true,
        ]);

        return CompetitionSeason::create([
            'competition_id' => $competition->id,
            'season_id' => $season->id,
            'format' => 'knockout',
            'matchdays' => null,
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 0,
            'relegated_slots' => 0,
            'is_finished' => false,
        ]);
    }

    private function createClub(string $name): Club
    {
        $user = User::factory()->create();

        return Club::create([
            'user_id' => $user->id,
            'is_cpu' => false,
            'name' => $name,
            'short_name' => strtoupper(substr($name, 0, 12)),
            'slug' => str()->slug($name).'-'.$user->id,
            'country' => 'Deutschland',
            'league' => 'Cup League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
            'training_level' => 1,
        ]);
    }

    private function createPlayedCupMatch(
        CompetitionSeason $competitionSeason,
        int $homeClubId,
        int $awayClubId,
        int $homeScore,
        int $awayScore,
        int $seed,
        int $roundNumber = 1,
        string $stage = 'Cup Runde 1',
        ?\Illuminate\Support\Carbon $kickoffAt = null,
        ?int $penaltiesHome = null,
        ?int $penaltiesAway = null
    ): GameMatch {
        return GameMatch::create([
            'competition_season_id' => $competitionSeason->id,
            'season_id' => $competitionSeason->season_id,
            'type' => 'cup',
            'competition_context' => CompetitionContextService::CUP_NATIONAL,
            'stage' => $stage,
            'round_number' => $roundNumber,
            'kickoff_at' => $kickoffAt ?? now()->subHour(),
            'status' => 'played',
            'home_club_id' => $homeClubId,
            'away_club_id' => $awayClubId,
            'stadium_club_id' => $homeClubId,
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'penalties_home' => $penaltiesHome,
            'penalties_away' => $penaltiesAway,
            'simulation_seed' => $seed,
            'played_at' => now()->subMinutes(30),
        ]);
    }
}
