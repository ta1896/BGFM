<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\Country;
use App\Models\GameMatch;
use App\Models\Season;
use App\Models\SeasonClubRegistration;
use App\Models\User;
use App\Services\CompetitionContextService;
use App\Services\CupQualificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CupQualificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_assigns_international_cup_qualifiers_and_generates_round_one(): void
    {
        config()->set('simulation.cup.qualification.enabled', true);
        config()->set('simulation.cup.qualification.source_league_tier', 1);
        config()->set('simulation.cup.qualification.slots_by_competition_tier', [1 => 4, 2 => 2]);
        config()->set('simulation.cup.qualification.auto_generate_fixtures', true);

        $country = Country::create([
            'name' => 'Deutschland',
            'iso_code' => 'DE',
            'fifa_code' => 'GER',
        ]);

        $currentSeason = Season::create([
            'name' => '2026/27',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_current' => true,
        ]);
        $nextSeason = Season::create([
            'name' => '2027/28',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
            'is_current' => false,
        ]);

        $league = Competition::create([
            'country_id' => $country->id,
            'name' => 'Bundesliga',
            'short_name' => 'BL',
            'type' => 'league',
            'tier' => 1,
            'is_active' => true,
        ]);
        $leagueSeason = CompetitionSeason::create([
            'competition_id' => $league->id,
            'season_id' => $currentSeason->id,
            'format' => 'round_robin',
            'matchdays' => 34,
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 0,
            'relegated_slots' => 0,
            'is_finished' => false,
        ]);

        $internationalCupA = Competition::create([
            'country_id' => null,
            'name' => 'Continental Cup A',
            'short_name' => 'CCA',
            'type' => 'cup',
            'scope' => 'international',
            'tier' => 1,
            'is_active' => true,
        ]);
        $internationalCupB = Competition::create([
            'country_id' => null,
            'name' => 'Continental Cup B',
            'short_name' => 'CCB',
            'type' => 'cup',
            'scope' => 'international',
            'tier' => 2,
            'is_active' => true,
        ]);

        $clubs = collect(range(1, 8))
            ->map(fn (int $i): Club => $this->createClub('Quali Club '.$i, 'Deutschland'));

        $table = $clubs
            ->values()
            ->map(fn (Club $club): array => ['club_id' => (int) $club->id]);

        $service = app(CupQualificationService::class);
        $service->syncForLeagueSeason($leagueSeason, $nextSeason, $table);
        $service->syncForLeagueSeason($leagueSeason, $nextSeason, $table);

        $cupSeasonA = CompetitionSeason::query()
            ->where('competition_id', $internationalCupA->id)
            ->where('season_id', $nextSeason->id)
            ->first();
        $cupSeasonB = CompetitionSeason::query()
            ->where('competition_id', $internationalCupB->id)
            ->where('season_id', $nextSeason->id)
            ->first();

        $this->assertNotNull($cupSeasonA);
        $this->assertNotNull($cupSeasonB);

        $registrationsA = SeasonClubRegistration::query()
            ->where('competition_season_id', $cupSeasonA->id)
            ->orderBy('club_id')
            ->pluck('club_id')
            ->all();
        $registrationsB = SeasonClubRegistration::query()
            ->where('competition_season_id', $cupSeasonB->id)
            ->orderBy('club_id')
            ->pluck('club_id')
            ->all();

        $this->assertSame(
            $clubs->slice(0, 4)->pluck('id')->values()->all(),
            $registrationsA
        );
        $this->assertSame(
            $clubs->slice(4, 2)->pluck('id')->values()->all(),
            $registrationsB
        );

        $matchesA = GameMatch::query()
            ->where('competition_season_id', $cupSeasonA->id)
            ->where('type', 'cup')
            ->get();
        $matchesB = GameMatch::query()
            ->where('competition_season_id', $cupSeasonB->id)
            ->where('type', 'cup')
            ->get();

        $this->assertCount(2, $matchesA);
        $this->assertCount(1, $matchesB);
        $this->assertSame(
            3,
            GameMatch::query()
                ->whereIn('competition_season_id', [$cupSeasonA->id, $cupSeasonB->id])
                ->where('competition_context', CompetitionContextService::CUP_INTERNATIONAL)
                ->count()
        );
    }

    public function test_service_ignores_non_configured_league_tier(): void
    {
        config()->set('simulation.cup.qualification.enabled', true);
        config()->set('simulation.cup.qualification.source_league_tier', 1);

        $country = Country::create([
            'name' => 'Spanien',
            'iso_code' => 'ES',
            'fifa_code' => 'ESP',
        ]);
        $season = Season::create([
            'name' => '2026/27',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_current' => true,
        ]);
        $nextSeason = Season::create([
            'name' => '2027/28',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
            'is_current' => false,
        ]);

        $tierTwoLeague = Competition::create([
            'country_id' => $country->id,
            'name' => 'Liga 2',
            'short_name' => 'L2',
            'type' => 'league',
            'tier' => 2,
            'is_active' => true,
        ]);
        $leagueSeason = CompetitionSeason::create([
            'competition_id' => $tierTwoLeague->id,
            'season_id' => $season->id,
            'format' => 'round_robin',
            'matchdays' => 34,
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 0,
            'relegated_slots' => 0,
            'is_finished' => false,
        ]);

        Competition::create([
            'country_id' => null,
            'name' => 'Tier Cup',
            'short_name' => 'TC',
            'type' => 'cup',
            'scope' => 'international',
            'tier' => 1,
            'is_active' => true,
        ]);

        $clubs = collect(range(1, 4))
            ->map(fn (int $i): Club => $this->createClub('Tier Club '.$i, 'Spanien'));
        $table = $clubs->map(fn (Club $club): array => ['club_id' => (int) $club->id]);

        app(CupQualificationService::class)->syncForLeagueSeason($leagueSeason, $nextSeason, $table);

        $this->assertSame(0, CompetitionSeason::query()->where('season_id', $nextSeason->id)->count());
    }

    private function createClub(string $name, string $country): Club
    {
        $user = User::factory()->create();

        return Club::create([
            'user_id' => $user->id,
            'is_cpu' => false,
            'name' => $name,
            'short_name' => strtoupper(substr($name, 0, 12)),
            'slug' => str()->slug($name).'-'.$user->id,
            'country' => $country,
            'league' => 'League',
            'budget' => 500000,
            'wage_budget' => 200000,
            'reputation' => 55,
            'fan_mood' => 55,
            'fanbase' => 100000,
            'board_confidence' => 55,
            'training_level' => 1,
        ]);
    }
}

