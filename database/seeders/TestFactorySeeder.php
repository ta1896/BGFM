<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\Country;
use App\Models\Lineup;
use App\Models\Player;
use App\Models\PlayerContract;
use App\Models\Season;
use App\Models\SeasonClubRegistration;
use App\Models\SeasonClubStatistic;
use App\Models\Stadium;
use App\Models\User;
use App\Services\FixtureGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TestFactorySeeder extends Seeder
{
    private const DEFAULT_LEAGUES = 2;
    private const DEFAULT_CLUBS_PER_LEAGUE = 10;
    private const DEFAULT_PLAYERS_PER_CLUB = 20;
    private const DEFAULT_SEED_YEAR = 2026;

    /**
     * @var array<int, array{code: string, second: string|null, third: string|null}>
     */
    private const POSITION_POOL = [
        ['code' => 'TW', 'second' => null, 'third' => null],
        ['code' => 'TW', 'second' => null, 'third' => null],
        ['code' => 'LV', 'second' => 'LWB', 'third' => 'LM'],
        ['code' => 'RV', 'second' => 'RWB', 'third' => 'RM'],
        ['code' => 'IV', 'second' => 'DM', 'third' => 'RV'],
        ['code' => 'IV', 'second' => 'DM', 'third' => 'LV'],
        ['code' => 'IV', 'second' => 'DM', 'third' => 'IV'],
        ['code' => 'DM', 'second' => 'ZM', 'third' => 'IV'],
        ['code' => 'ZM', 'second' => 'DM', 'third' => 'OM'],
        ['code' => 'ZM', 'second' => 'OM', 'third' => 'DM'],
        ['code' => 'OM', 'second' => 'ZOM', 'third' => 'ZM'],
        ['code' => 'LM', 'second' => 'LW', 'third' => 'LWB'],
        ['code' => 'RM', 'second' => 'RW', 'third' => 'RWB'],
        ['code' => 'LW', 'second' => 'LM', 'third' => 'ST'],
        ['code' => 'RW', 'second' => 'RM', 'third' => 'ST'],
        ['code' => 'ST', 'second' => 'MS', 'third' => 'LS'],
        ['code' => 'ST', 'second' => 'MS', 'third' => 'RS'],
        ['code' => 'MS', 'second' => 'ST', 'third' => 'OM'],
        ['code' => 'LS', 'second' => 'ST', 'third' => 'LW'],
        ['code' => 'RS', 'second' => 'ST', 'third' => 'RW'],
    ];

    /**
     * @var array<int, string>
     */
    private const STARTER_SLOTS = ['TW', 'LV', 'IV-L', 'IV-R', 'RV', 'DM', 'ZM-L', 'ZM-R', 'LW', 'ST', 'RW'];

    public function run(): void
    {
        $leagueCount = max(1, (int) config('test_factory.leagues', self::DEFAULT_LEAGUES));
        $clubsPerLeague = max(2, (int) config('test_factory.clubs_per_league', self::DEFAULT_CLUBS_PER_LEAGUE));
        $playersPerClub = max(11, (int) config('test_factory.players_per_club', self::DEFAULT_PLAYERS_PER_CLUB));
        $seedYear = max(2020, (int) config('test_factory.seed_year', self::DEFAULT_SEED_YEAR));

        DB::transaction(function () use ($leagueCount, $clubsPerLeague, $playersPerClub, $seedYear): void {
            $this->cleanupPreviousFactoryData();

            [$admin, $manager] = $this->createUsers();
            $country = $this->createCountry();
            $season = $this->createSeason($seedYear);

            for ($leagueIndex = 1; $leagueIndex <= $leagueCount; $leagueIndex++) {
                $competition = $this->createCompetition($country, $leagueIndex);
                $competitionSeason = $this->createCompetitionSeason($competition, $season, $clubsPerLeague);

                $clubs = collect();
                for ($clubIndex = 1; $clubIndex <= $clubsPerLeague; $clubIndex++) {
                    $club = $this->createClub(
                        $competition,
                        $leagueIndex,
                        $clubIndex,
                        $manager->id,
                        $clubsPerLeague
                    );

                    $players = $this->createPlayers($club, $playersPerClub);
                    $this->createContracts($club, $players);
                    $this->createLineup($club, $players);
                    $this->createStadium($club, $leagueIndex, $clubIndex);

                    SeasonClubRegistration::create([
                        'competition_season_id' => $competitionSeason->id,
                        'club_id' => $club->id,
                        'squad_limit' => $playersPerClub,
                        'wage_cap' => (float) $club->wage_budget,
                    ]);

                    SeasonClubStatistic::create([
                        'competition_season_id' => $competitionSeason->id,
                        'club_id' => $club->id,
                    ]);

                    $clubs->push($club);
                }

                app(FixtureGeneratorService::class)->generateRoundRobin($competitionSeason->load('season'));
            }

            // Avoid "unused variable" optimizations and keep explicit that both users are part of the data set.
            $admin->refresh();
            $manager->refresh();
        });
    }

    private function cleanupPreviousFactoryData(): void
    {
        Competition::query()->where('short_name', 'like', 'TSTL%')->delete();
        Club::query()->where('slug', 'like', 'tst-l%')->delete();
        Season::query()->where('name', 'like', 'TEST-%')->delete();
        Country::query()->where('iso_code', 'TS')->delete();
        User::query()->whereIn('email', [
            'test.admin@openws.local',
            'test.manager@openws.local',
        ])->delete();
    }

    /**
     * @return array{0: User, 1: User}
     */
    private function createUsers(): array
    {
        $admin = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'test.admin@openws.local',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $manager = User::factory()->create([
            'name' => 'Test Manager',
            'email' => 'test.manager@openws.local',
            'password' => 'password',
            'is_admin' => false,
        ]);

        return [$admin, $manager];
    }

    private function createCountry(): Country
    {
        return Country::updateOrCreate(
            ['iso_code' => 'TS'],
            [
                'name' => 'Testland',
                'fifa_code' => 'TST',
            ]
        );
    }

    private function createSeason(int $seedYear): Season
    {
        Season::query()->update(['is_current' => false]);

        $start = Carbon::create($seedYear, 7, 1)->subMonths(6)->startOfDay();
        $end = $start->copy()->addYear()->subDay();

        return Season::create([
            'name' => sprintf('TEST-%d/%d', $seedYear, $seedYear + 1),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_current' => true,
        ]);
    }

    private function createCompetition(Country $country, int $leagueIndex): Competition
    {
        return Competition::create([
            'country_id' => $country->id,
            'name' => sprintf('Test Liga %d', $leagueIndex),
            'short_name' => sprintf('TSTL%d', $leagueIndex),
            'type' => 'league',
            'tier' => $leagueIndex,
            'is_active' => true,
        ]);
    }

    private function createCompetitionSeason(
        Competition $competition,
        Season $season,
        int $clubsPerLeague
    ): CompetitionSeason {
        return CompetitionSeason::create([
            'competition_id' => $competition->id,
            'season_id' => $season->id,
            'format' => 'round_robin',
            'matchdays' => ($clubsPerLeague - 1) * 2,
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 0,
            'relegated_slots' => 0,
            'is_finished' => false,
        ]);
    }

    private function createClub(
        Competition $competition,
        int $leagueIndex,
        int $clubIndex,
        int $managerUserId,
        int $clubsPerLeague
    ): Club {
        $isManagerClub = $leagueIndex === 1 && $clubIndex === 1;
        $shortCode = sprintf('L%dC%d', $leagueIndex, $clubIndex);

        return Club::create([
            'user_id' => $isManagerClub ? $managerUserId : null,
            'is_cpu' => !$isManagerClub,
            'name' => sprintf('Test Club %d-%02d', $leagueIndex, $clubIndex),
            'slug' => sprintf('tst-l%d-club-%02d', $leagueIndex, $clubIndex),
            'short_name' => $shortCode,
            'country' => 'Testland',
            'league' => $competition->name,
            'league_id' => $competition->id,
            'founded_year' => 1900 + (($leagueIndex * 7 + $clubIndex * 3) % 110),
            'reputation' => max(35, 80 - ($clubIndex * 2) - ($leagueIndex - 1) * 3),
            'fan_mood' => max(40, 75 - $clubIndex),
            'fanbase' => 70000 + (($clubsPerLeague - $clubIndex + 1) * 8000),
            'board_confidence' => 55,
            'training_level' => 2,
            'budget' => 900000 + (($clubsPerLeague - $clubIndex) * 50000),
            'wage_budget' => 350000 + (($clubsPerLeague - $clubIndex) * 15000),
            'notes' => 'Generiert durch TestFactorySeeder',
        ]);
    }

    /**
     * @return Collection<int, Player>
     */
    private function createPlayers(Club $club, int $playersPerClub): Collection
    {
        $players = collect();

        for ($i = 0; $i < $playersPerClub; $i++) {
            $profile = self::POSITION_POOL[$i % count(self::POSITION_POOL)];
            $baseOverall = max(48, 76 - (int) floor($i / 2));

            $player = Player::create([
                'club_id' => $club->id,
                'first_name' => sprintf('P%02d', $i + 1),
                'last_name' => $club->short_name ?: 'TST',
                'position' => $profile['code'],
                'position_main' => $profile['code'],
                'position_second' => $profile['second'],
                'position_third' => $profile['third'],
                'preferred_foot' => $i % 6 === 0 ? 'left' : 'right',
                'age' => 18 + ($i % 15),
                'overall' => $baseOverall,
                'potential' => min(99, $baseOverall + 8),
                'pace' => min(99, 55 + (($i * 3) % 35)),
                'shooting' => min(99, 52 + (($i * 5) % 38)),
                'passing' => min(99, 54 + (($i * 4) % 34)),
                'defending' => min(99, 50 + (($i * 6) % 35)),
                'physical' => min(99, 53 + (($i * 2) % 33)),
                'stamina' => min(100, 62 + (($i * 3) % 30)),
                'morale' => min(100, 58 + ($i % 30)),
                'status' => 'active',
                'market_value' => max(120000, $baseOverall * 25000),
                'salary' => max(2000, $baseOverall * 180),
                'contract_expires_on' => now()->addMonths(12 + ($i % 30))->toDateString(),
                'last_training_at' => now()->subDays($i % 5),
                'injury_matches_remaining' => 0,
                'suspension_matches_remaining' => 0,
                'suspension_league_remaining' => 0,
                'suspension_cup_national_remaining' => 0,
                'suspension_cup_international_remaining' => 0,
                'suspension_friendly_remaining' => 0,
            ]);

            $players->push($player);
        }

        return $players->sortByDesc('overall')->values();
    }

    /**
     * @param Collection<int, Player> $players
     */
    private function createContracts(Club $club, Collection $players): void
    {
        foreach ($players as $player) {
            PlayerContract::create([
                'player_id' => $player->id,
                'club_id' => $club->id,
                'wage' => (float) $player->salary,
                'bonus_goal' => 500,
                'signed_on' => now()->subMonths(6)->toDateString(),
                'starts_on' => now()->subMonths(6)->toDateString(),
                'expires_on' => $player->contract_expires_on,
                'release_clause' => (float) $player->market_value * 1.8,
                'is_active' => true,
            ]);
        }
    }

    /**
     * @param Collection<int, Player> $players
     */
    private function createLineup(Club $club, Collection $players): void
    {
        $lineup = Lineup::create([
            'club_id' => $club->id,
            'name' => 'Test Startelf',
            'formation' => '4-3-3',
            'tactical_style' => 'balanced',
            'attack_focus' => 'center',
            'is_active' => true,
            'is_template' => true,
            'notes' => 'Automatisch generierte Testaufstellung',
        ]);

        $starterIds = $players->take(11)->pluck('id')->values();
        $benchIds = $players->slice(11)->pluck('id')->values();

        $lineup->update([
            'penalty_taker_player_id' => $starterIds->get(9),
            'free_kick_taker_player_id' => $starterIds->get(6),
            'corner_left_taker_player_id' => $starterIds->get(8),
            'corner_right_taker_player_id' => $starterIds->get(10),
        ]);

        $syncData = [];

        foreach ($starterIds as $index => $playerId) {
            $syncData[$playerId] = [
                'pitch_position' => self::STARTER_SLOTS[$index] ?? ('POS-'.$index),
                'sort_order' => $index,
                'x_coord' => null,
                'y_coord' => null,
                'is_captain' => $index === 0,
                'is_set_piece_taker' => in_array($index, [6, 8, 9, 10], true),
                'is_bench' => false,
                'bench_order' => null,
            ];
        }

        foreach ($benchIds as $index => $playerId) {
            $syncData[$playerId] = [
                'pitch_position' => 'BANK-'.($index + 1),
                'sort_order' => $index,
                'x_coord' => null,
                'y_coord' => null,
                'is_captain' => false,
                'is_set_piece_taker' => false,
                'is_bench' => true,
                'bench_order' => $index + 1,
            ];
        }

        $lineup->players()->sync($syncData);
    }

    private function createStadium(Club $club, int $leagueIndex, int $clubIndex): void
    {
        Stadium::create([
            'club_id' => $club->id,
            'name' => sprintf('Test Arena %d-%02d', $leagueIndex, $clubIndex),
            'capacity' => 18000 + (($clubIndex - 1) * 1000),
            'covered_seats' => 10000 + (($clubIndex - 1) * 700),
            'vip_seats' => 600 + (($clubIndex - 1) * 40),
            'ticket_price' => 14 + $leagueIndex + ($clubIndex * 0.2),
            'maintenance_cost' => 18000 + (($clubIndex - 1) * 900),
            'facility_level' => 2,
            'pitch_quality' => min(99, 58 + $clubIndex),
            'fan_experience' => min(99, 55 + $clubIndex),
            'security_level' => min(99, 56 + $clubIndex),
            'environment_level' => min(99, 54 + $clubIndex),
            'last_maintenance_at' => now()->subDays(7),
        ]);
    }
}
