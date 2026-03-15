<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\ClubFinancialTransaction;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\FriendlyMatchRequest;
use App\Models\GameMatch;
use App\Models\GameNotification;
use App\Models\ManagerPresence;
use App\Models\MatchLiveAction;
use App\Models\MatchPlayerStat;
use App\Models\Player;
use App\Models\PlayerConversation;
use App\Models\PlayerInjury;
use App\Models\PlayerPlaytimePromise;
use App\Models\PlayerRecoveryLog;
use App\Models\ScoutingReport;
use App\Models\ScoutingWatchlist;
use App\Models\SeasonClubStatistic;
use App\Models\Sponsor;
use App\Models\SponsorContract;
use App\Models\TeamOfTheDay;
use App\Models\TeamOfTheDayPlayer;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\SeasonAwardsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DemoFeatureSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $competition = Competition::query()->where('short_name', 'TSTL1')->first();
            $competitionSeason = CompetitionSeason::query()
                ->with(['matches', 'statistics'])
                ->when($competition, fn ($query) => $query->where('competition_id', $competition->id))
                ->latest('id')
                ->first();

            if (!$competition || !$competitionSeason) {
                return;
            }

            $managedClubs = Club::query()
                ->where('league_id', $competition->id)
                ->orderBy('id')
                ->take(4)
                ->get();

            if ($managedClubs->count() < 4) {
                return;
            }

            $managerUsers = $this->ensureManagers($managedClubs);
            $primaryManager = $managerUsers->first();
            $primaryClub = $managedClubs->first();

            $this->seedFinance($primaryClub, $primaryManager);
            $this->seedNotifications($primaryClub, $primaryManager);
            $this->seedTraining($primaryClub, $primaryManager);
            $this->seedDynamics($primaryClub, $primaryManager);
            $this->seedScouting($primaryClub, $primaryManager, $managedClubs->slice(1));
            $this->seedSponsor($primaryClub, $primaryManager);
            $this->seedFriendlies($primaryClub, $managedClubs->get(1), $primaryManager);
            $this->seedLiveMatches($competitionSeason, $managedClubs);
            $this->seedPlayedMatchStats($competitionSeason);
            $this->seedTeamOfTheDay($competitionSeason, $primaryManager);
            app(SeasonAwardsService::class)->generateForCompetitionSeason($competitionSeason);
            $this->seedManagerPresence($managerUsers, $managedClubs);
        });
    }

    private function ensureManagers(Collection $clubs): Collection
    {
        $definitions = [
            ['name' => 'Test Manager', 'email' => 'test.manager@openws.local'],
            ['name' => 'Test Manager 2', 'email' => 'test.manager2@openws.local'],
            ['name' => 'Test Manager 3', 'email' => 'test.manager3@openws.local'],
            ['name' => 'Test Manager 4', 'email' => 'test.manager4@openws.local'],
        ];

        return collect($definitions)->values()->map(function (array $definition, int $index) use ($clubs) {
            $club = $clubs->get($index);

            /** @var User $user */
            $user = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => bcrypt('password'),
                    'is_admin' => false,
                    'default_club_id' => $club?->id,
                    'theme' => ['catalyst', 'tactical', 'elite', 'classic'][$index] ?? 'catalyst',
                ]
            );

            if ($club) {
                $club->update([
                    'user_id' => $user->id,
                    'is_cpu' => false,
                ]);
            }

            return $user;
        });
    }

    private function seedFinance(Club $club, User $user): void
    {
        ClubFinancialTransaction::query()->where('club_id', $club->id)->delete();

        $entries = [
            ['context_type' => 'sponsor', 'asset_type' => 'budget', 'direction' => 'income', 'amount' => 120000, 'balance_after' => 1020000, 'note' => 'Signing Bonus NovaWear'],
            ['context_type' => 'salary', 'asset_type' => 'budget', 'direction' => 'expense', 'amount' => 48500, 'balance_after' => 971500, 'note' => 'Weekly wages'],
            ['context_type' => 'match_income', 'asset_type' => 'budget', 'direction' => 'income', 'amount' => 64250, 'balance_after' => 1035750, 'note' => 'Home gate receipts'],
            ['context_type' => 'training', 'asset_type' => 'budget', 'direction' => 'expense', 'amount' => 12500, 'balance_after' => 1023250, 'note' => 'Medical treatment'],
            ['context_type' => 'transfer', 'asset_type' => 'budget', 'direction' => 'expense', 'amount' => 87500, 'balance_after' => 935750, 'note' => 'Scout package and bonuses'],
        ];

        foreach ($entries as $offset => $entry) {
            ClubFinancialTransaction::create([
                'club_id' => $club->id,
                'user_id' => $user->id,
                ...$entry,
                'reference_type' => null,
                'reference_id' => null,
                'booked_at' => now()->subDays(6 - $offset),
            ]);
        }
    }

    private function seedNotifications(Club $club, User $user): void
    {
        GameNotification::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['promise_at_risk', 'promise_broken', 'medical_warning', 'scouting_update', 'match_live'])
            ->delete();

        $notifications = [
            ['type' => 'promise_at_risk', 'title' => 'Spielzeitversprechen kritisch', 'message' => 'Ein wichtiger Stammspieler droht sein Minutenversprechen zu verfehlen.', 'action_url' => route('players.index')],
            ['type' => 'medical_warning', 'title' => 'Rueckfallrisiko erhoeht', 'message' => 'Ein Spieler ist im Return-to-Play-Prozess und sollte vorsichtig belastet werden.', 'action_url' => route('medical.index')],
            ['type' => 'scouting_update', 'title' => 'Scoutreport eingetroffen', 'message' => 'Ein priorisiertes Ziel hat einen neuen Report mit engerer OVR-Spanne.', 'action_url' => route('scouting.index')],
            ['type' => 'match_live', 'title' => 'Live-Spiel laeuft', 'message' => 'In deiner Liga laufen gerade mehrere Partien live.', 'action_url' => route('manager-live.index')],
        ];

        foreach ($notifications as $notification) {
            GameNotification::create([
                'user_id' => $user->id,
                'club_id' => $club->id,
                ...$notification,
                'seen_at' => null,
            ]);
        }
    }

    private function seedTraining(Club $club, User $user): void
    {
        TrainingSession::query()->where('club_id', $club->id)->delete();

        $types = [
            ['type' => 'fitness', 'intensity' => 'high', 'notes' => 'Explosive interval work'],
            ['type' => 'tactics', 'intensity' => 'medium', 'notes' => 'Pressing triggers and compactness'],
            ['type' => 'recovery', 'intensity' => 'low', 'notes' => 'Cooldown and mobility'],
            ['type' => 'technical', 'intensity' => 'medium', 'notes' => 'Final-third combinations'],
        ];

        foreach ($types as $offset => $definition) {
            $session = TrainingSession::create([
                'club_id' => $club->id,
                'created_by_user_id' => $user->id,
                'type' => $definition['type'],
                'intensity' => $definition['intensity'],
                'focus_position' => null,
                'session_date' => now()->startOfWeek()->addDays($offset),
                'morale_effect' => $definition['type'] === 'recovery' ? 3 : 1,
                'stamina_effect' => $definition['type'] === 'fitness' ? -4 : 1,
                'form_effect' => 2,
                'notes' => $definition['notes'],
                'is_applied' => $offset < 2,
                'applied_at' => $offset < 2 ? now()->subDays(2 - $offset) : null,
            ]);

            $club->players()->orderByDesc('overall')->take(14)->get()->each(function (Player $player, int $index) use ($session) {
                $session->players()->attach($player->id, [
                    'role' => $index < 11 ? 'participant' : 'rest',
                    'stamina_delta' => $index < 6 ? -2 : -1,
                    'morale_delta' => $index < 11 ? 1 : 0,
                    'overall_delta' => 0,
                ]);
            });
        }
    }

    private function seedDynamics(Club $club, User $user): void
    {
        $players = $club->players()->orderByDesc('overall')->get();
        $star = $players->get(0);
        $key = $players->get(1);
        $rotation = $players->get(8);
        $injured = $players->get(12);

        foreach ([$star, $key, $rotation, $injured] as $player) {
            if (!$player) {
                continue;
            }

            $player->update([
                'squad_role' => $player->id === $star?->id ? 'star_player' : ($player->id === $key?->id ? 'important_first_team' : 'rotation'),
                'leadership_level' => $player->id === $star?->id ? 'captain_group' : 'regular',
                'team_status' => $player->id === $rotation?->id ? 'restless' : 'settled',
                'expected_playtime' => $player->id === $rotation?->id ? 55 : 80,
                'happiness' => $player->id === $rotation?->id ? 41 : ($player->id === $injured?->id ? 46 : 72),
                'happiness_trend' => $player->id === $rotation?->id ? -8 : 4,
                'fatigue' => $player->id === $injured?->id ? 68 : ($player->id === $key?->id ? 59 : 33),
                'sharpness' => $player->id === $injured?->id ? 51 : 74,
                'injury_proneness' => $player->id === $injured?->id ? 74 : 42,
                'match_load' => $player->id === $key?->id ? 83 : 41,
                'training_load' => $player->id === $key?->id ? 72 : 38,
                'medical_status' => $player->id === $injured?->id ? 'rehab' : ($player->id === $key?->id ? 'watch' : 'fit'),
                'last_morale_reason' => $player->id === $rotation?->id ? 'Versprochene Minuten wurden zuletzt verfehlt.' : 'Gute Form und klare Rolle.',
            ]);
        }

        PlayerPlaytimePromise::query()->where('club_id', $club->id)->delete();
        PlayerInjury::query()->where('club_id', $club->id)->delete();
        PlayerRecoveryLog::query()->where('club_id', $club->id)->delete();
        PlayerConversation::query()->where('club_id', $club->id)->delete();

        if ($rotation) {
            PlayerPlaytimePromise::create([
                'player_id' => $rotation->id,
                'club_id' => $club->id,
                'promise_type' => 'rotation_minutes',
                'expected_minutes_share' => 60,
                'deadline_at' => now()->addWeeks(3),
                'status' => 'at_risk',
                'fulfilled_ratio' => 38,
                'notes' => 'Spieler erwartet regelmaessige Einsaetze.',
            ]);

            PlayerConversation::create([
                'player_id' => $rotation->id,
                'club_id' => $club->id,
                'user_id' => $user->id,
                'topic' => 'playtime',
                'approach' => 'supportive',
                'outcome' => 'mixed',
                'happiness_delta' => -3,
                'happiness_after' => 41,
                'manager_message' => 'Du bekommst deine Minuten, aber ich brauche Geduld.',
                'player_response' => 'Ich akzeptiere es vorerst, erwarte aber bald Einsaetze.',
                'summary' => 'Gespräch beruhigt nur kurzfristig.',
            ]);
        }

        if ($injured) {
            PlayerInjury::create([
                'player_id' => $injured->id,
                'club_id' => $club->id,
                'injury_type' => 'Muskelverletzung',
                'body_area' => 'Oberschenkel',
                'severity' => 'moderate',
                'started_at' => now()->subDays(9),
                'expected_return_at' => now()->addDays(12),
                'actual_return_at' => null,
                'status' => 'rehab',
                'source' => 'training',
                'rehab_intensity' => 'controlled',
                'return_phase' => 'integration',
                'setback_risk' => 34,
                'notes' => 'Steht kurz vor Teamintegration.',
            ]);

            foreach (range(0, 4) as $dayOffset) {
                PlayerRecoveryLog::create([
                    'player_id' => $injured->id,
                    'club_id' => $club->id,
                    'day' => now()->subDays(4 - $dayOffset)->toDateString(),
                    'training_load' => 18 + ($dayOffset * 5),
                    'match_load' => 0,
                    'fatigue_before' => 70 - $dayOffset,
                    'fatigue_after' => 67 - $dayOffset,
                    'sharpness_before' => 42 + $dayOffset,
                    'sharpness_after' => 45 + $dayOffset,
                    'injury_risk' => 40 - ($dayOffset * 3),
                ]);
            }
        }
    }

    private function seedScouting(Club $club, User $user, Collection $otherClubs): void
    {
        ScoutingWatchlist::query()->where('club_id', $club->id)->delete();
        ScoutingReport::query()->where('club_id', $club->id)->delete();

        $targets = $otherClubs
            ->flatMap(fn (Club $otherClub) => $otherClub->players()->orderByDesc('potential')->take(2)->get())
            ->take(4)
            ->values();

        foreach ($targets as $index => $player) {
            $watchlist = ScoutingWatchlist::create([
                'club_id' => $club->id,
                'player_id' => $player->id,
                'created_by_user_id' => $user->id,
                'priority' => $index < 2 ? 'high' : 'medium',
                'status' => $index === 0 ? 'shortlist' : 'watching',
                'notes' => 'Interessanter Markt-Case fuer Sommerfenster.',
            ]);

            ScoutingReport::create([
                'club_id' => $club->id,
                'player_id' => $player->id,
                'watchlist_id' => $watchlist->id,
                'created_by_user_id' => $user->id,
                'confidence' => 58 + ($index * 8),
                'overall_min' => max(45, $player->overall - 3),
                'overall_max' => min(99, $player->overall + 2),
                'potential_min' => max(50, $player->potential - 5),
                'potential_max' => min(99, $player->potential + 2),
                'pace_min' => max(40, $player->pace - 4),
                'pace_max' => min(99, $player->pace + 3),
                'passing_min' => max(40, $player->passing - 4),
                'passing_max' => min(99, $player->passing + 3),
                'physical_min' => max(40, $player->physical - 4),
                'physical_max' => min(99, $player->physical + 3),
                'injury_risk_band' => $index === 0 ? 'medium' : 'low',
                'personality_band' => $index === 2 ? 'volatile' : 'professional',
                'summary' => 'Scout sieht passendes Profil, aber finale Einordnung braucht mehr Beobachtung.',
            ]);
        }
    }

    private function seedSponsor(Club $club, User $user): void
    {
        $sponsor = Sponsor::query()->updateOrCreate(
            ['name' => 'NovaWear'],
            [
                'tier' => 'national',
                'reputation_min' => 55,
                'base_weekly_amount' => 28000,
                'signing_bonus_min' => 90000,
                'signing_bonus_max' => 140000,
                'is_active' => true,
            ]
        );

        SponsorContract::query()->where('club_id', $club->id)->delete();

        SponsorContract::create([
            'club_id' => $club->id,
            'sponsor_id' => $sponsor->id,
            'signed_by_user_id' => $user->id,
            'weekly_amount' => 32500,
            'signing_bonus' => 120000,
            'starts_on' => now()->subWeeks(4)->toDateString(),
            'ends_on' => now()->addMonths(9)->toDateString(),
            'status' => 'active',
            'last_payout_on' => now()->subDays(3)->toDateString(),
            'objectives' => ['finish_top_half' => true, 'score_45_goals' => true],
        ]);
    }

    private function seedFriendlies(Club $primaryClub, Club $otherClub, User $user): void
    {
        FriendlyMatchRequest::query()
            ->where(function ($query) use ($primaryClub, $otherClub) {
                $query->where('challenger_club_id', $primaryClub->id)
                    ->orWhere('challenged_club_id', $primaryClub->id)
                    ->orWhere('challenger_club_id', $otherClub->id)
                    ->orWhere('challenged_club_id', $otherClub->id);
            })
            ->delete();

        FriendlyMatchRequest::create([
            'challenger_club_id' => $primaryClub->id,
            'challenged_club_id' => $otherClub->id,
            'requested_by_user_id' => $user->id,
            'accepted_match_id' => null,
            'kickoff_at' => now()->addDays(5)->setTime(18, 30),
            'stadium_club_id' => $primaryClub->id,
            'status' => 'pending',
            'message' => 'Perfekter Test fuer Rotation und Rueckkehrer.',
            'responded_at' => null,
        ]);
    }

    private function seedLiveMatches(CompetitionSeason $competitionSeason, Collection $clubs): void
    {
        $liveMatches = GameMatch::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->whereIn('status', ['scheduled', 'live'])
            ->orderBy('kickoff_at')
            ->take(2)
            ->get();

        foreach ($liveMatches as $index => $match) {
            $match->update([
                'status' => 'live',
                'live_minute' => 18 + ($index * 21),
                'home_score' => $index === 0 ? 1 : 0,
                'away_score' => $index === 0 ? 0 : 1,
                'live_paused' => false,
                'kickoff_at' => now()->subMinutes(25 + ($index * 10)),
            ]);

            MatchLiveAction::query()->where('match_id', $match->id)->delete();

            $homeScorer = $match->homeClub?->players()->orderByDesc('overall')->first();
            $awayScorer = $match->awayClub?->players()->orderByDesc('overall')->first();

            MatchLiveAction::create([
                'match_id' => $match->id,
                'minute' => max(2, ($match->live_minute ?? 1) - 4),
                'second' => 11,
                'sequence' => 1,
                'club_id' => $index === 0 ? $match->home_club_id : $match->away_club_id,
                'player_id' => $index === 0 ? $homeScorer?->id : $awayScorer?->id,
                'opponent_player_id' => null,
                'action_type' => 'goal',
                'outcome' => 'scored',
                'narrative' => $index === 0 ? 'Frueher Treffer nach schnellem Konter.' : 'Auswaerts fuehrt nach ruhendem Ball.',
                'x_coord' => 90,
                'y_coord' => 48,
                'xg' => 0.41,
                'metadata' => ['demo' => true],
            ]);
        }
    }

    private function seedPlayedMatchStats(CompetitionSeason $competitionSeason): void
    {
        $playedMatches = GameMatch::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->where('status', 'played')
            ->orderBy('id')
            ->take(3)
            ->get();

        foreach ($playedMatches as $match) {
            MatchPlayerStat::query()->where('match_id', $match->id)->delete();

            $homePlayers = $match->homeClub?->players()->orderByDesc('overall')->take(11)->get() ?? collect();
            $awayPlayers = $match->awayClub?->players()->orderByDesc('overall')->take(11)->get() ?? collect();

            $this->seedStatsForSide($match, $match->home_club_id, $homePlayers, (int) $match->home_score, true);
            $this->seedStatsForSide($match, $match->away_club_id, $awayPlayers, (int) $match->away_score, false);
        }

        $tableRows = SeasonClubStatistic::query()
            ->where('competition_season_id', $competitionSeason->id)
            ->with('club')
            ->get()
            ->sortByDesc(fn (SeasonClubStatistic $row) => [$row->points, $row->goal_diff, $row->goals_for])
            ->values();

        foreach ($tableRows as $index => $row) {
            $row->update([
                'rank' => $index + 1,
                'form_last5' => $index === 0 ? 'WWDWW' : ($index === 1 ? 'WDWLW' : 'LDWDL'),
            ]);
        }
    }

    private function seedStatsForSide(GameMatch $match, int $clubId, Collection $players, int $goals, bool $home): void
    {
        $assistIndex = 1;

        foreach ($players as $index => $player) {
            MatchPlayerStat::create([
                'match_id' => $match->id,
                'club_id' => $clubId,
                'player_id' => $player->id,
                'lineup_role' => $index < 11 ? 'starter' : 'bench',
                'position_code' => $player->position_main,
                'rating' => $index === 0 && $goals > 0 ? 8.2 : (6.4 + (($index % 5) * 0.3)),
                'minutes_played' => $index < 11 ? 90 : 0,
                'goals' => $index === 0 ? $goals : 0,
                'assists' => $index === $assistIndex && $goals > 0 ? min(1, $goals) : 0,
                'yellow_cards' => $index === 4 && !$home ? 1 : 0,
                'red_cards' => 0,
                'shots' => $index < 3 ? 3 - $index : 0,
                'passes_completed' => 22 + ($index * 2),
                'passes_failed' => 3 + ($index % 4),
                'tackles_won' => $index >= 3 && $index <= 6 ? 2 + ($index % 2) : 0,
                'tackles_lost' => $index >= 3 && $index <= 6 ? 1 : 0,
                'saves' => $index === 0 && $player->position_main === 'TW' ? 3 : 0,
            ]);
        }
    }

    private function seedTeamOfTheDay(CompetitionSeason $competitionSeason, User $manager): void
    {
        TeamOfTheDay::query()->where('competition_season_id', $competitionSeason->id)->delete();

        $playedPlayers = MatchPlayerStat::query()
            ->whereHas('match', fn ($query) => $query->where('competition_season_id', $competitionSeason->id)->where('status', 'played'))
            ->with(['player', 'club'])
            ->orderByDesc('rating')
            ->take(11)
            ->get()
            ->values();

        if ($playedPlayers->count() < 11) {
            return;
        }

        $team = TeamOfTheDay::create([
            'for_date' => now()->toDateString(),
            'competition_season_id' => $competitionSeason->id,
            'matchday' => 1,
            'label' => 'Demo Team der Woche',
            'formation' => '4-3-3',
            'generated_by_user_id' => $manager->id,
            'generation_context' => 'demo_seed',
            'notes' => 'Vorgefertigte Aufstellung fuer Feature-Tests.',
        ]);

        $slots = ['GK1', 'DEF1', 'DEF2', 'DEF3', 'DEF4', 'MID1', 'MID2', 'MID3', 'FWD1', 'FWD2', 'FWD3'];

        foreach ($playedPlayers as $index => $stat) {
            TeamOfTheDayPlayer::create([
                'team_of_the_day_id' => $team->id,
                'player_id' => $stat->player_id,
                'club_id' => $stat->club_id,
                'position_code' => $slots[$index] ?? ('MID'.($index + 1)),
                'rating' => $stat->rating,
                'stats_snapshot' => [
                    'goals' => $stat->goals,
                    'assists' => $stat->assists,
                    'minutes' => $stat->minutes_played,
                ],
            ]);
        }
    }

    private function seedManagerPresence(Collection $users, Collection $clubs): void
    {
        ManagerPresence::query()->delete();

        $activities = [
            ['route_name' => 'matches.show', 'path' => '/matches/1', 'activity_label' => 'Im Matchcenter', 'matchIndex' => 0],
            ['route_name' => 'scouting.index', 'path' => '/scouting', 'activity_label' => 'Im Scouting', 'matchIndex' => null],
            ['route_name' => 'training.index', 'path' => '/training', 'activity_label' => 'Im Training', 'matchIndex' => null],
            ['route_name' => 'players.show', 'path' => '/players/1', 'activity_label' => 'Im Spielerprofil', 'matchIndex' => null],
        ];

        $liveMatches = GameMatch::query()->where('status', 'live')->orderBy('id')->get();

        foreach ($users as $index => $user) {
            $activity = $activities[$index] ?? $activities[0];
            $club = $clubs->get($index);
            $match = $activity['matchIndex'] !== null ? $liveMatches->get($activity['matchIndex']) : null;

            ManagerPresence::create([
                'user_id' => $user->id,
                'club_id' => $club?->id,
                'match_id' => $match?->id,
                'route_name' => $activity['route_name'],
                'path' => $activity['path'],
                'activity_label' => $activity['activity_label'],
                'last_seen_at' => now()->subMinutes($index),
            ]);
        }
    }
}
