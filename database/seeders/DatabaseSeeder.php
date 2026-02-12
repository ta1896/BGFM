<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Lineup;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_admin' => false,
        ]);

        $cpuUser = User::factory()->create([
            'name' => 'CPU Bot',
            'email' => 'cpu@example.com',
            'is_admin' => false,
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $countryId = DB::table('countries')->insertGetId([
            'name' => 'Deutschland',
            'iso_code' => 'DE',
            'fifa_code' => 'GER',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $competitionId = DB::table('competitions')->insertGetId([
            'country_id' => $countryId,
            'name' => 'Starter League',
            'short_name' => 'SL1',
            'type' => 'league',
            'tier' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $seasonId = DB::table('seasons')->insertGetId([
            'name' => '2026/27',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_current' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $competitionSeasonId = DB::table('competition_seasons')->insertGetId([
            'competition_id' => $competitionId,
            'season_id' => $seasonId,
            'format' => 'round_robin',
            'matchdays' => 34,
            'points_win' => 3,
            'points_draw' => 1,
            'points_loss' => 0,
            'promoted_slots' => 0,
            'relegated_slots' => 2,
            'is_finished' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $club = Club::create([
            'user_id' => $user->id,
            'is_cpu' => false,
            'name' => 'NewGen United',
            'slug' => 'newgen-united',
            'short_name' => 'NGU',
            'country' => 'Deutschland',
            'league' => 'Starter League',
            'league_id' => $competitionId,
            'founded_year' => 2001,
            'reputation' => 62,
            'fan_mood' => 57,
            'fanbase' => 285000,
            'board_confidence' => 60,
            'training_level' => 2,
            'budget' => 950000,
            'wage_budget' => 380000,
        ]);

        $opponentClub = Club::create([
            'user_id' => $cpuUser->id,
            'is_cpu' => true,
            'name' => 'Dockyard FC',
            'slug' => 'dockyard-fc',
            'short_name' => 'DYF',
            'country' => 'Deutschland',
            'league' => 'Starter League',
            'league_id' => $competitionId,
            'founded_year' => 1998,
            'reputation' => 58,
            'fan_mood' => 52,
            'fanbase' => 190000,
            'board_confidence' => 55,
            'training_level' => 2,
            'budget' => 780000,
            'wage_budget' => 310000,
        ]);

        $sponsorAId = DB::table('sponsors')->insertGetId([
            'name' => 'NordBank Gruppe',
            'tier' => 'regional',
            'reputation_min' => 40,
            'base_weekly_amount' => 28000,
            'signing_bonus_min' => 90000,
            'signing_bonus_max' => 180000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sponsors')->insert([
            [
                'name' => 'SternMobil',
                'tier' => 'national',
                'reputation_min' => 55,
                'base_weekly_amount' => 42000,
                'signing_bonus_min' => 120000,
                'signing_bonus_max' => 260000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'UrbanGrid Energy',
                'tier' => 'local',
                'reputation_min' => 20,
                'base_weekly_amount' => 18000,
                'signing_bonus_min' => 45000,
                'signing_bonus_max' => 85000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('sponsor_contracts')->insert([
            'club_id' => $club->id,
            'sponsor_id' => $sponsorAId,
            'signed_by_user_id' => $user->id,
            'weekly_amount' => 31500,
            'signing_bonus' => 125000,
            'starts_on' => Carbon::today()->subDays(10)->toDateString(),
            'ends_on' => Carbon::today()->addMonths(10)->toDateString(),
            'status' => 'active',
            'last_payout_on' => Carbon::today()->subDay()->toDateString(),
            'objectives' => json_encode(['min_table_rank' => 8]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clubStadiumId = DB::table('stadiums')->insertGetId([
            'club_id' => $club->id,
            'name' => 'NewGen Park',
            'capacity' => 24000,
            'covered_seats' => 14000,
            'vip_seats' => 1200,
            'ticket_price' => 21.5,
            'maintenance_cost' => 29000,
            'facility_level' => 2,
            'pitch_quality' => 67,
            'fan_experience' => 65,
            'security_level' => 62,
            'environment_level' => 60,
            'last_maintenance_at' => now()->subWeeks(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stadiums')->insert([
            'club_id' => $opponentClub->id,
            'name' => 'Dockyard Ground',
            'capacity' => 21000,
            'covered_seats' => 11000,
            'vip_seats' => 950,
            'ticket_price' => 18.0,
            'maintenance_cost' => 25000,
            'facility_level' => 2,
            'pitch_quality' => 63,
            'fan_experience' => 61,
            'security_level' => 60,
            'environment_level' => 58,
            'last_maintenance_at' => now()->subWeeks(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stadium_projects')->insert([
            'stadium_id' => $clubStadiumId,
            'project_type' => 'pitch',
            'level_from' => 67,
            'level_to' => 73,
            'cost' => 128000,
            'started_on' => Carbon::today()->subDays(3)->toDateString(),
            'completes_on' => Carbon::today()->addDays(5)->toDateString(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('season_club_registrations')->insert([
            [
                'competition_season_id' => $competitionSeasonId,
                'club_id' => $club->id,
                'squad_limit' => 25,
                'wage_cap' => 450000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'competition_season_id' => $competitionSeasonId,
                'club_id' => $opponentClub->id,
                'squad_limit' => 25,
                'wage_cap' => 420000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('season_club_statistics')->insert([
            [
                'competition_season_id' => $competitionSeasonId,
                'club_id' => $club->id,
                'matches_played' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_diff' => 0,
                'points' => 0,
                'home_points' => 0,
                'away_points' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'competition_season_id' => $competitionSeasonId,
                'club_id' => $opponentClub->id,
                'matches_played' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_diff' => 0,
                'points' => 0,
                'home_points' => 0,
                'away_points' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $players = collect([
            ['Max', 'Torwart', 'TW', 69, 45, 35, 58, 73, 70],
            ['Jonas', 'Links', 'LV', 66, 63, 44, 55, 71, 72],
            ['Luca', 'Innen', 'IV', 70, 55, 43, 59, 76, 75],
            ['Niko', 'Innen', 'IV', 68, 56, 42, 60, 74, 74],
            ['Elias', 'Rechts', 'RV', 67, 64, 47, 56, 70, 72],
            ['Can', 'Sechs', 'DM', 71, 61, 58, 72, 68, 73],
            ['Rami', 'Acht', 'ZM', 69, 66, 61, 70, 62, 69],
            ['Noah', 'Acht', 'ZM', 68, 65, 57, 68, 61, 67],
            ['Mika', 'Links', 'LW', 72, 79, 73, 60, 40, 66],
            ['Emir', 'Sturm', 'ST', 74, 78, 76, 57, 39, 70],
            ['Timo', 'Rechts', 'RW', 71, 77, 71, 62, 38, 65],
        ])->map(function (array $data) use ($club) {
            return Player::create([
                'club_id' => $club->id,
                'first_name' => $data[0],
                'last_name' => $data[1],
                'position' => $data[2],
                'preferred_foot' => Arr::random(['right', 'left']),
                'age' => rand(19, 31),
                'overall' => $data[3],
                'potential' => min(99, $data[3] + rand(3, 12)),
                'pace' => $data[4],
                'shooting' => $data[5],
                'passing' => $data[6],
                'defending' => $data[7],
                'physical' => $data[8],
                'stamina' => rand(65, 90),
                'morale' => rand(55, 80),
                'status' => 'active',
                'market_value' => rand(200000, 2400000),
                'salary' => rand(8000, 35000),
                'contract_expires_on' => Carbon::now()->addMonths(rand(10, 36))->toDateString(),
                'last_training_at' => now()->subDays(rand(0, 5)),
            ]);
        });

        $opponentPlayers = collect([
            ['Jan', 'Keeper', 'TW', 67, 41, 32, 55, 71, 68],
            ['Miro', 'Links', 'LV', 64, 60, 40, 53, 68, 70],
            ['Ben', 'Innen', 'IV', 66, 54, 38, 56, 72, 72],
            ['Tom', 'Innen', 'IV', 65, 55, 37, 55, 71, 71],
            ['Leo', 'Rechts', 'RV', 64, 62, 42, 54, 67, 70],
            ['Pavel', 'Sechs', 'DM', 67, 58, 52, 68, 66, 70],
            ['Kaan', 'Acht', 'ZM', 66, 62, 55, 66, 60, 66],
            ['Sven', 'Acht', 'ZM', 65, 61, 53, 64, 58, 65],
            ['Ali', 'Links', 'LW', 69, 75, 70, 56, 38, 64],
            ['Nils', 'Sturm', 'ST', 70, 74, 72, 54, 37, 68],
            ['Eren', 'Rechts', 'RW', 68, 73, 69, 58, 36, 63],
        ])->map(function (array $data) use ($opponentClub) {
            return Player::create([
                'club_id' => $opponentClub->id,
                'first_name' => $data[0],
                'last_name' => $data[1],
                'position' => $data[2],
                'preferred_foot' => Arr::random(['right', 'left']),
                'age' => rand(19, 31),
                'overall' => $data[3],
                'potential' => min(99, $data[3] + rand(3, 10)),
                'pace' => $data[4],
                'shooting' => $data[5],
                'passing' => $data[6],
                'defending' => $data[7],
                'physical' => $data[8],
                'stamina' => rand(62, 88),
                'morale' => rand(52, 78),
                'status' => 'active',
                'market_value' => rand(180000, 2100000),
                'salary' => rand(7000, 28000),
                'contract_expires_on' => Carbon::now()->addMonths(rand(8, 30))->toDateString(),
                'last_training_at' => now()->subDays(rand(0, 5)),
            ]);
        });

        $players->each(function (Player $player) use ($club) {
            DB::table('player_contracts')->insert([
                'player_id' => $player->id,
                'club_id' => $club->id,
                'wage' => (float) $player->salary,
                'bonus_goal' => 2000,
                'signed_on' => Carbon::now()->subMonths(rand(1, 18))->toDateString(),
                'starts_on' => Carbon::now()->subMonths(rand(0, 2))->toDateString(),
                'expires_on' => $player->contract_expires_on,
                'release_clause' => (float) $player->market_value * 1.8,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $opponentPlayers->each(function (Player $player) use ($opponentClub) {
            DB::table('player_contracts')->insert([
                'player_id' => $player->id,
                'club_id' => $opponentClub->id,
                'wage' => (float) $player->salary,
                'bonus_goal' => 1500,
                'signed_on' => Carbon::now()->subMonths(rand(1, 16))->toDateString(),
                'starts_on' => Carbon::now()->subMonths(rand(0, 2))->toDateString(),
                'expires_on' => $player->contract_expires_on,
                'release_clause' => (float) $player->market_value * 1.7,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $lineup = Lineup::create([
            'club_id' => $club->id,
            'name' => 'Standard 4-3-3',
            'formation' => '4-3-3',
            'tactical_style' => 'balanced',
            'is_active' => true,
            'is_template' => true,
        ]);

        $lineup->players()->sync(
            $players->values()->mapWithKeys(function (Player $player, int $index) {
                return [
                    $player->id => [
                        'pitch_position' => null,
                        'sort_order' => $index,
                        'is_captain' => $index === 0,
                        'is_set_piece_taker' => in_array($index, [5, 9], true),
                    ],
                ];
            })->all()
        );

        $matchId = DB::table('matches')->insertGetId([
            'competition_season_id' => $competitionSeasonId,
            'season_id' => $seasonId,
            'type' => 'league',
            'stage' => 'Regular Season',
            'round_number' => 1,
            'matchday' => 1,
            'kickoff_at' => Carbon::now()->addDay()->setTime(18, 30),
            'status' => 'scheduled',
            'home_club_id' => $club->id,
            'away_club_id' => $opponentClub->id,
            'stadium_club_id' => $club->id,
            'weather' => 'clear',
            'simulation_seed' => random_int(10000, 99999),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $lineup->update(['match_id' => $matchId]);

        $firstPlayer = $players->first();
        $listingId = DB::table('transfer_listings')->insertGetId([
            'player_id' => $firstPlayer->id,
            'seller_club_id' => $club->id,
            'listed_by_user_id' => $user->id,
            'min_price' => 1250000,
            'buy_now_price' => 1800000,
            'listed_at' => now(),
            'expires_at' => now()->addDays(7),
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $loanListingId = DB::table('loan_listings')->insertGetId([
            'player_id' => $players->values()->get(1)->id,
            'lender_club_id' => $club->id,
            'listed_by_user_id' => $user->id,
            'min_weekly_fee' => 9000,
            'buy_option_price' => 750000,
            'loan_months' => 6,
            'listed_at' => now(),
            'expires_at' => now()->addDays(7),
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('transfer_bids')->insert([
            'transfer_listing_id' => $listingId,
            'bidder_club_id' => $opponentClub->id,
            'bidder_user_id' => $cpuUser->id,
            'amount' => 1300000,
            'message' => 'Direktes Angebot fuer den Startkeeper.',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('loan_bids')->insert([
            'loan_listing_id' => $loanListingId,
            'borrower_club_id' => $opponentClub->id,
            'bidder_user_id' => $cpuUser->id,
            'weekly_fee' => 11000,
            'message' => 'Wir brauchen kurzfristig einen Defensivspieler.',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('club_financial_transactions')->insert([
            [
                'club_id' => $club->id,
                'user_id' => $user->id,
                'context_type' => 'sponsor',
                'direction' => 'income',
                'amount' => 95000,
                'balance_after' => (float) $club->budget + 95000,
                'booked_at' => now()->subDays(2),
                'note' => 'Saisonauftakt Sponsorenrate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'club_id' => $club->id,
                'user_id' => $user->id,
                'context_type' => 'salary',
                'direction' => 'expense',
                'amount' => 47000,
                'balance_after' => (float) $club->budget + 48000,
                'booked_at' => now()->subDay(),
                'note' => 'Woechentliche Gehaelter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $trainingSessionId = DB::table('training_sessions')->insertGetId([
            'club_id' => $club->id,
            'created_by_user_id' => $user->id,
            'type' => 'tactics',
            'intensity' => 'medium',
            'focus_position' => 'MID',
            'session_date' => Carbon::today()->toDateString(),
            'morale_effect' => 2,
            'stamina_effect' => -1,
            'form_effect' => 1,
            'notes' => 'Einruecken und Pressingausloeser trainieren.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('training_session_player')->insert(
            $players->take(7)->values()->map(function (Player $player) use ($trainingSessionId) {
                return [
                    'training_session_id' => $trainingSessionId,
                    'player_id' => $player->id,
                    'role' => 'participant',
                    'stamina_delta' => -1,
                    'morale_delta' => 1,
                    'overall_delta' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all()
        );

        DB::table('training_camps')->insert([
            [
                'club_id' => $club->id,
                'created_by_user_id' => $user->id,
                'name' => 'Sommercamp Alpen',
                'focus' => 'fitness',
                'intensity' => 'medium',
                'starts_on' => Carbon::today()->subDays(2)->toDateString(),
                'ends_on' => Carbon::today()->addDays(3)->toDateString(),
                'cost' => 92000,
                'stamina_effect' => 3,
                'morale_effect' => 1,
                'overall_effect' => 0,
                'status' => 'active',
                'notes' => 'Hoehentraining und Athletik.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'club_id' => $opponentClub->id,
                'created_by_user_id' => $cpuUser->id,
                'name' => 'Taktikcamp Nord',
                'focus' => 'tactics',
                'intensity' => 'low',
                'starts_on' => Carbon::today()->addDays(4)->toDateString(),
                'ends_on' => Carbon::today()->addDays(8)->toDateString(),
                'cost' => 47000,
                'stamina_effect' => 1,
                'morale_effect' => 1,
                'overall_effect' => 1,
                'status' => 'planned',
                'notes' => 'Positionsspiel und Pressing.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('game_notifications')->insert([
            'user_id' => $user->id,
            'club_id' => $club->id,
            'type' => 'fixture',
            'title' => 'Naechstes Ligaspiel geplant',
            'message' => 'NewGen United spielt morgen gegen Dockyard FC.',
            'action_url' => '/dashboard',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $friendlyMatchId = DB::table('matches')->insertGetId([
            'competition_season_id' => null,
            'season_id' => $seasonId,
            'type' => 'friendly',
            'stage' => 'Friendly',
            'round_number' => null,
            'matchday' => null,
            'kickoff_at' => Carbon::now()->addDays(3)->setTime(19, 0),
            'status' => 'scheduled',
            'home_club_id' => $club->id,
            'away_club_id' => $opponentClub->id,
            'stadium_club_id' => $club->id,
            'weather' => null,
            'simulation_seed' => random_int(10000, 99999),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('friendly_match_requests')->insert([
            'challenger_club_id' => $club->id,
            'challenged_club_id' => $opponentClub->id,
            'requested_by_user_id' => $user->id,
            'accepted_match_id' => $friendlyMatchId,
            'kickoff_at' => Carbon::now()->addDays(3)->setTime(19, 0),
            'stadium_club_id' => $club->id,
            'status' => 'auto_accepted',
            'message' => 'Demo-Freundschaftsspiel',
            'responded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nationalTeamId = DB::table('national_teams')->insertGetId([
            'country_id' => $countryId,
            'name' => 'Deutschland',
            'short_name' => 'GER',
            'manager_user_id' => $user->id,
            'reputation' => 74,
            'tactical_style' => 'balanced',
            'notes' => 'Automatisch generierter Testkader.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nationalPool = $players
            ->merge($opponentPlayers)
            ->sortByDesc('overall')
            ->take(18)
            ->values();

        DB::table('national_team_callups')->insert(
            $nationalPool->map(function (Player $player, int $index) use ($nationalTeamId, $user) {
                return [
                    'national_team_id' => $nationalTeamId,
                    'player_id' => $player->id,
                    'created_by_user_id' => $user->id,
                    'called_up_on' => Carbon::today()->toDateString(),
                    'released_on' => null,
                    'role' => $index < 11 ? 'starter' : 'bench',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all()
        );

        $teamOfTheDayId = DB::table('team_of_the_days')->insertGetId([
            'for_date' => Carbon::today()->toDateString(),
            'label' => 'Team des Tages '.Carbon::today()->toDateString(),
            'formation' => '4-3-3',
            'generated_by_user_id' => $user->id,
            'generation_context' => 'seed_demo',
            'notes' => 'Demo-Zusammenstellung fuer UI-Tests.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $slots = ['GK1', 'DEF1', 'DEF2', 'DEF3', 'DEF4', 'MID1', 'MID2', 'MID3', 'FWD1', 'FWD2', 'FWD3'];
        $todPlayers = $nationalPool->take(11)->values();
        DB::table('team_of_the_day_players')->insert(
            $todPlayers->map(function (Player $player, int $index) use ($teamOfTheDayId, $slots) {
                return [
                    'team_of_the_day_id' => $teamOfTheDayId,
                    'player_id' => $player->id,
                    'club_id' => $player->club_id,
                    'position_code' => $slots[$index] ?? 'SUB'.($index + 1),
                    'rating' => round(max(6.2, min(9.6, ($player->overall / 10) + (lcg_value() * 1.2))), 2),
                    'stats_snapshot' => json_encode([
                        'goals' => random_int(0, 2),
                        'assists' => random_int(0, 2),
                        'minutes_played' => random_int(60, 90),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all()
        );

        DB::table('random_event_templates')->insert([
            [
                'name' => 'Unerwartete TV-Praemie',
                'category' => 'finance',
                'rarity' => 'common',
                'min_reputation' => 40,
                'max_reputation' => null,
                'budget_delta_min' => 50000,
                'budget_delta_max' => 120000,
                'morale_delta' => 0,
                'stamina_delta' => 0,
                'overall_delta' => 0,
                'fan_mood_delta' => 2,
                'board_confidence_delta' => 1,
                'probability_weight' => 110,
                'is_active' => true,
                'description_template' => '{club} erhaelt TV-Einnahmen in Hoehe von {amount}.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Buspanne am Spieltag',
                'category' => 'club',
                'rarity' => 'uncommon',
                'min_reputation' => null,
                'max_reputation' => null,
                'budget_delta_min' => -60000,
                'budget_delta_max' => -20000,
                'morale_delta' => -2,
                'stamina_delta' => -1,
                'overall_delta' => 0,
                'fan_mood_delta' => -1,
                'board_confidence_delta' => -2,
                'probability_weight' => 80,
                'is_active' => true,
                'description_template' => '{club} muss kurzfristige Mehrkosten tragen ({amount}).',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Durchbruch im Abschlusstraining',
                'category' => 'player',
                'rarity' => 'rare',
                'min_reputation' => null,
                'max_reputation' => null,
                'budget_delta_min' => 0,
                'budget_delta_max' => 0,
                'morale_delta' => 4,
                'stamina_delta' => 1,
                'overall_delta' => 1,
                'fan_mood_delta' => 1,
                'board_confidence_delta' => 1,
                'probability_weight' => 60,
                'is_active' => true,
                'description_template' => '{player} legt einen starken Trainingssprung hin.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Leichte Muskelverletzung',
                'category' => 'medical',
                'rarity' => 'uncommon',
                'min_reputation' => null,
                'max_reputation' => null,
                'budget_delta_min' => -20000,
                'budget_delta_max' => -5000,
                'morale_delta' => -3,
                'stamina_delta' => -4,
                'overall_delta' => -1,
                'fan_mood_delta' => -1,
                'board_confidence_delta' => -1,
                'probability_weight' => 45,
                'is_active' => true,
                'description_template' => '{player} faellt kurzzeitig aus. Behandlungskosten: {amount}.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $templateForSeed = DB::table('random_event_templates')
            ->where('category', 'finance')
            ->value('id');

        DB::table('random_event_occurrences')->insert([
            'template_id' => $templateForSeed,
            'club_id' => $club->id,
            'player_id' => $players->first()->id,
            'triggered_by_user_id' => $user->id,
            'status' => 'pending',
            'title' => 'Unerwartete TV-Praemie',
            'message' => 'Demo-Event: Bonus kann im Modul angewendet werden.',
            'happened_on' => Carbon::today()->toDateString(),
            'applied_at' => null,
            'effect_payload' => json_encode([
                'budget_delta' => 85000,
                'morale_delta' => 1,
                'stamina_delta' => 0,
                'overall_delta' => 0,
                'fan_mood_delta' => 2,
                'board_confidence_delta' => 1,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
