<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\ManagerPresence;
use App\Models\Player;
use App\Modules\ModuleManager;
use App\Services\FormationPlannerService;
use App\Services\LeagueTableService;
use App\Services\LiveMatchTickerService;
use App\Services\MatchSimulationService;
use App\Services\PlayerPositionService;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;

class MatchCenterController extends Controller
{
    public function show(Request $request, GameMatch $match, LeagueTableService $leagueTableService): \Inertia\Response
    {
        $this->ensureReadable($request, $match);

        $this->loadMatchStateRelations($match, true);

        $comparison = [
            'home' => $this->comparisonMetrics($match->homeClub),
            'away' => $this->comparisonMetrics($match->awayClub),
        ];
        $preMatchReport = $this->preMatchReport($match, $leagueTableService, $comparison);

        $state = $this->statePayload($request, $match, $leagueTableService);

        return \Inertia\Inertia::render('Matches/Show', array_merge($state, [
            'home_club' => [
                'id'         => $match->homeClub->id,
                'name'       => $match->homeClub->name,
                'short_name' => $match->homeClub->short_name,
                'logo_url'   => $match->homeClub->logo_url,
                'stadium'    => $match->homeClub->stadium?->name,
            ],
            'away_club' => [
                'id'         => $match->awayClub->id,
                'name'       => $match->awayClub->name,
                'short_name' => $match->awayClub->short_name,
                'logo_url'   => $match->awayClub->logo_url,
            ],
            'competition'       => $match->competitionSeason?->competition?->name,
            'matchday'          => $match->matchday,
            'kickoff_formatted' => $match->kickoff_at?->format('d.m.Y • H:i'),
            'weather'           => $match->weather,
            'referee'           => $match->referee,
            'type'              => $match->type,
            'comparison'        => $comparison,
            'pre_match_report'  => $preMatchReport,
            'module_panels'     => $state['module_panels'] ?? [],
        ]));
    }

    private function comparisonMetrics(\App\Models\Club $club): array
    {
        $scoreColumns = $this->availablePlayerScoreColumns();
        $selects = ['market_value', 'age', 'overall'];

        if (in_array('morale', $scoreColumns, true)) {
            $selects[] = 'morale';
        }

        if (in_array('happiness', $scoreColumns, true)) {
            $selects[] = 'happiness';
        }

        if (in_array('stamina', $scoreColumns, true)) {
            $selects[] = 'stamina';
        }

        if (in_array('sharpness', $scoreColumns, true)) {
            $selects[] = 'sharpness';
        }

        if (in_array('fatigue', $scoreColumns, true)) {
            $selects[] = 'fatigue';
        }

        $players = $club->players()
            ->where('status', 'active')
            ->get($selects);

        $corePlayers = $players->sortByDesc('overall')->take(14)->values();
        $moraleMetric = in_array('morale', $scoreColumns, true)
            ? $corePlayers->avg('morale')
            : $corePlayers->avg('happiness');
        $fitnessMetric = $this->resolveFitnessMetric($corePlayers, $scoreColumns);

        return [
            'market_value' => (float) ($players->sum('market_value') ?? 0),
            'avg_age' => round((float) ($players->avg('age') ?? 0), 1),
            'strength' => round((float) ($corePlayers->avg('overall') ?? 0), 1),
            'morale' => round((float) ($moraleMetric ?? 0), 1),
            'fitness' => round((float) ($fitnessMetric ?? 0), 1),
        ];
    }

    private function availablePlayerScoreColumns(): array
    {
        static $columns = null;

        if ($columns !== null) {
            return $columns;
        }

        return $columns = Schema::getColumnListing('players');
    }

    private function resolveFitnessMetric(Collection $players, array $scoreColumns): float
    {
        if (in_array('stamina', $scoreColumns, true)) {
            return (float) ($players->avg('stamina') ?? 0);
        }

        if (in_array('sharpness', $scoreColumns, true) && in_array('fatigue', $scoreColumns, true)) {
            return (float) ($players->avg(fn ($player) => max(0, min(100, (((int) ($player->sharpness ?? 0)) + (100 - (int) ($player->fatigue ?? 0))) / 2))) ?? 0);
        }

        if (in_array('sharpness', $scoreColumns, true)) {
            return (float) ($players->avg('sharpness') ?? 0);
        }

        if (in_array('fatigue', $scoreColumns, true)) {
            return (float) ($players->avg(fn ($player) => max(0, 100 - (int) ($player->fatigue ?? 0))) ?? 0);
        }

        return 0.0;
    }

    private function preMatchReport(GameMatch $match, LeagueTableService $leagueTableService, array $comparison): array
    {
        $keyPlayers = $this->keyPlayers($match);

        return [
            'recent_form' => [
                'home' => $this->recentForm($match->home_club_id, $match->id),
                'away' => $this->recentForm($match->away_club_id, $match->id),
            ],
            'league_snapshot' => $this->leagueSnapshot($match, $leagueTableService),
            'head_to_head' => $this->headToHead($match),
            'insights' => $this->insightBullets($match, $comparison),
            'key_players' => $keyPlayers,
            'absentees' => $this->absentees($match),
            'key_duels' => $this->keyDuels($match, $keyPlayers),
            'expected_lineup_preview' => $this->expectedLineupPreview($match),
        ];
    }

    private function recentForm(int $clubId, int $currentMatchId): array
    {
        $matches = GameMatch::query()
            ->where('status', 'played')
            ->where('id', '!=', $currentMatchId)
            ->where(function (Builder $query) use ($clubId) {
                $query->where('home_club_id', $clubId)
                    ->orWhere('away_club_id', $clubId);
            })
            ->with(['homeClub:id,name,short_name,logo_path', 'awayClub:id,name,short_name,logo_path'])
            ->orderByDesc('kickoff_at')
            ->take(5)
            ->get();

        $form = $matches->map(function (GameMatch $pastMatch) use ($clubId): array {
            $isHome = (int) $pastMatch->home_club_id === $clubId;
            $goalsFor = (int) ($isHome ? $pastMatch->home_score : $pastMatch->away_score);
            $goalsAgainst = (int) ($isHome ? $pastMatch->away_score : $pastMatch->home_score);
            $opponent = $isHome ? $pastMatch->awayClub : $pastMatch->homeClub;
            $result = $goalsFor > $goalsAgainst ? 'W' : ($goalsFor < $goalsAgainst ? 'L' : 'D');

            return [
                'id' => $pastMatch->id,
                'result' => $result,
                'score' => $goalsFor . ':' . $goalsAgainst,
                'opponent_name' => $opponent?->short_name ?: $opponent?->name ?: 'Gegner',
                'opponent_logo_url' => $opponent?->logo_url,
                'is_home' => $isHome,
                'kickoff_label' => $pastMatch->kickoff_at?->format('d.m.'),
                'relative_label' => $pastMatch->kickoff_at
                    ? sprintf('vor %d Tagen', now()->diffInDays($pastMatch->kickoff_at))
                    : null,
                'competition_name' => $pastMatch->competitionSeason?->competition?->name
                    ?? ($pastMatch->type === 'league' ? 'Liga' : ($pastMatch->type === 'friendly' ? 'Testspiel' : 'Pokal')),
                'trend_rating' => round($this->formTrendRating($goalsFor, $goalsAgainst), 2),
            ];
        })->values();

        return [
            'matches' => $form->all(),
            'wins' => $form->where('result', 'W')->count(),
            'draws' => $form->where('result', 'D')->count(),
            'losses' => $form->where('result', 'L')->count(),
            'points' => ($form->where('result', 'W')->count() * 3) + $form->where('result', 'D')->count(),
        ];
    }

    private function formTrendRating(int $goalsFor, int $goalsAgainst): float
    {
        $goalDiff = $goalsFor - $goalsAgainst;
        $base = match (true) {
            $goalDiff >= 2 => 8.8,
            $goalDiff === 1 => 8.1,
            $goalDiff === 0 => 7.1,
            $goalDiff === -1 => 6.4,
            default => 5.8,
        };

        $bonus = min(0.5, max(-0.3, ($goalsFor * 0.12) - ($goalsAgainst * 0.08)));

        return max(5.0, min(9.8, $base + $bonus));
    }

    private function leagueSnapshot(GameMatch $match, LeagueTableService $leagueTableService): ?array
    {
        if ($match->type !== 'league' || !$match->competitionSeason) {
            return null;
        }

        $table = $leagueTableService->table($match->competitionSeason)->values();
        $homeRow = $table->firstWhere('club_id', $match->home_club_id);
        $awayRow = $table->firstWhere('club_id', $match->away_club_id);

        if (!$homeRow && !$awayRow) {
            return null;
        }

        return [
            'competition' => $match->competitionSeason->competition?->name,
            'home' => $homeRow ? [
                'position' => (int) ($homeRow->position ?? 0),
                'points' => (int) ($homeRow->points ?? 0),
                'goal_diff' => (int) ($homeRow->goal_diff ?? 0),
            ] : null,
            'away' => $awayRow ? [
                'position' => (int) ($awayRow->position ?? 0),
                'points' => (int) ($awayRow->points ?? 0),
                'goal_diff' => (int) ($awayRow->goal_diff ?? 0),
            ] : null,
        ];
    }

    private function headToHead(GameMatch $match): array
    {
        $matches = GameMatch::query()
            ->where('status', 'played')
            ->where(function ($query) use ($match): void {
                $query->where(function ($inner) use ($match): void {
                    $inner->where('home_club_id', $match->home_club_id)
                        ->where('away_club_id', $match->away_club_id);
                })->orWhere(function ($inner) use ($match): void {
                    $inner->where('home_club_id', $match->away_club_id)
                        ->where('away_club_id', $match->home_club_id);
                });
            })
            ->orderByDesc('kickoff_at')
            ->take(5)
            ->get();

        $entries = $matches->map(function (GameMatch $entry) use ($match): array {
            $homeGoals = (int) ($entry->home_score ?? 0);
            $awayGoals = (int) ($entry->away_score ?? 0);
            $homePerspectiveGoals = (int) ((int) $entry->home_club_id === (int) $match->home_club_id ? $homeGoals : $awayGoals);
            $awayPerspectiveGoals = (int) ((int) $entry->home_club_id === (int) $match->home_club_id ? $awayGoals : $homeGoals);
            $winner = $homePerspectiveGoals > $awayPerspectiveGoals ? 'home' : ($homePerspectiveGoals < $awayPerspectiveGoals ? 'away' : 'draw');

            return [
                'id' => $entry->id,
                'date' => $entry->kickoff_at?->format('d.m.Y'),
                'score' => $homePerspectiveGoals . ':' . $awayPerspectiveGoals,
                'winner' => $winner,
            ];
        })->values();

        return [
            'matches' => $entries->all(),
            'home_wins' => $entries->where('winner', 'home')->count(),
            'draws' => $entries->where('winner', 'draw')->count(),
            'away_wins' => $entries->where('winner', 'away')->count(),
        ];
    }

    private function insightBullets(GameMatch $match, array $comparison): array
    {
        $homeStrength = (float) ($comparison['home']['strength'] ?? 0);
        $awayStrength = (float) ($comparison['away']['strength'] ?? 0);
        $homeMarket = (float) ($comparison['home']['market_value'] ?? 0);
        $awayMarket = (float) ($comparison['away']['market_value'] ?? 0);
        $homeFitness = (float) ($comparison['home']['fitness'] ?? 0);
        $awayFitness = (float) ($comparison['away']['fitness'] ?? 0);

        $strengthLeader = $homeStrength >= $awayStrength ? ($match->homeClub?->short_name ?: $match->homeClub?->name ?: 'Heimteam') : ($match->awayClub?->short_name ?: $match->awayClub?->name ?: 'Auswaertsteam');
        $marketLeader = $homeMarket >= $awayMarket ? ($match->homeClub?->short_name ?: $match->homeClub?->name ?: 'Heimteam') : ($match->awayClub?->short_name ?: $match->awayClub?->name ?: 'Auswaertsteam');
        $fitnessLeader = $homeFitness >= $awayFitness ? ($match->homeClub?->short_name ?: $match->homeClub?->name ?: 'Heimteam') : ($match->awayClub?->short_name ?: $match->awayClub?->name ?: 'Auswaertsteam');

        return [
            $strengthLeader . ' geht mit einem leichten Vorteil in der Kaderstaerke in dieses Duell.',
            $marketLeader . ' bringt aktuell den hoeheren Gesamtmarktwert auf den Platz.',
            $fitnessLeader . ' wirkt vor dem Anpfiff im Schnitt etwas frischer.',
        ];
    }

    private function keyPlayers(GameMatch $match): array
    {
        $suspensionField = $this->getSuspensionField($match->type ?? 'league');

        $fetcher = fn($clubId) => Player::query()
            ->where('club_id', $clubId)
            ->where('medical_status', 'fit')
            ->where('status', 'active')
            ->where($suspensionField, 0)
            ->orderByDesc('overall')
            ->take(2)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->full_name,
                'overall' => (int) $p->overall,
                'photo_url' => $p->photo_url,
                'position' => $p->display_position,
                'style' => $p->player_style,
            ]);

        return [
            'home' => $fetcher($match->home_club_id),
            'away' => $fetcher($match->away_club_id),
        ];
    }

    private function absentees(GameMatch $match): array
    {
        $suspensionField = $this->getSuspensionField($match->type ?? 'league');

        $fetcher = fn($clubId) => Player::query()
            ->where('club_id', $clubId)
            ->where(function($q) use ($suspensionField) {
                $q->where('medical_status', '!=', 'fit')
                  ->orWhere($suspensionField, '>', 0);
            })
            ->with(['injuries' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->full_name,
                'reason' => $p->$suspensionField > 0 ? 'Gesperrt' : ($p->injuries->first()?->injury_type ?? 'Verletzt'),
                'type' => $p->$suspensionField > 0 ? 'suspension' : 'injury',
            ]);

        return [
            'home' => $fetcher($match->home_club_id),
            'away' => $fetcher($match->away_club_id),
        ];
    }

    private function keyDuels(GameMatch $match, array $keyPlayers): array
    {
        $duels = [];

        // Simple heuristic: Best Home vs Best Away
        $homeP1 = $keyPlayers['home'][0] ?? null;
        $awayP1 = $keyPlayers['away'][0] ?? null;

        if ($homeP1 && $awayP1) {
            $duels[] = [
                'label' => 'Star-Vgl.',
                'home' => $homeP1,
                'away' => $awayP1,
            ];
        }

        // Try to find an offensive vs defensive duel
        $homeAttacker = Player::query()
            ->where('club_id', $match->home_club_id)
            ->where('medical_status', 'fit')
            ->whereIn('position', ['MS', 'ST', 'HS', 'LF', 'RF', 'OM'])
            ->orderByDesc('overall')
            ->first();

        $awayDefender = Player::query()
            ->where('club_id', $match->away_club_id)
            ->where('medical_status', 'fit')
            ->whereIn('position', ['IV', 'CB', 'LB', 'RB', 'LV', 'RV', 'DM'])
            ->orderByDesc('overall')
            ->first();

        if ($homeAttacker instanceof \App\Models\Player && $awayDefender instanceof \App\Models\Player) {
            $duels[] = [
                'label' => 'Angriff vs Abwehr',
                'home' => [
                    'id' => $homeAttacker->id,
                    'name' => $homeAttacker->full_name,
                    'overall' => $homeAttacker->overall,
                    'photo_url' => $homeAttacker->photo_url,
                    'position' => $homeAttacker->display_position,
                ],
                'away' => [
                    'id' => $awayDefender->id,
                    'name' => $awayDefender->full_name,
                    'overall' => $awayDefender->overall,
                    'photo_url' => $awayDefender->photo_url,
                    'position' => $awayDefender->display_position,
                ],
            ];
        }

        return $duels;
    }

    private function expectedLineupPreview(GameMatch $match): array
    {
        $payload = $this->lineupsPayload($match);
        
        $mapper = fn($l) => collect($l['starters'] ?? [])
            ->map(fn($s) => [
                'id' => $s['id'],
                'name' => $s['name'],
                'position' => $s['position'],
                'slot' => $s['slot'],
            ]);

        return [
            'home' => $mapper($payload[(string) $match->home_club_id] ?? []),
            'away' => $mapper($payload[(string) $match->away_club_id] ?? []),
        ];
    }

    private function getSuspensionField(string $type): string
    {
        return match($type) {
            'league' => 'suspension_league_remaining',
            'cup_national' => 'suspension_cup_national_remaining',
            'cup_international' => 'suspension_cup_international_remaining',
            'friendly' => 'suspension_friendly_remaining',
            default => 'suspension_matches_remaining'
        };
    }

    public function simulate(
        Request $request,
        GameMatch $match,
        MatchSimulationService $simulationService
    ): RedirectResponse {
        abort_unless($this->canSimulate($request, $match), 403);

        if ($request->isMethod('POST')) {
            $simulationService->simulate($match);
            return redirect()
                ->route('matches.show', $match)
                ->with('status', 'Spiel wurde simuliert.');
        }

        return redirect()->route('matches.show', $match);
    }

    public function liveStart(
        Request $request,
        GameMatch $match,
        LiveMatchTickerService $tickerService
    ): RedirectResponse {
        abort_unless($this->canSimulate($request, $match), 403);

        $tickerService->start($match);

        return redirect()
            ->route('matches.show', $match)
            ->with('status', 'Live-Ticker gestartet.');
    }

    public function liveResume(
        Request $request,
        GameMatch $match,
        LiveMatchTickerService $tickerService
    ): JsonResponse {
        abort_unless($this->canSimulate($request, $match), 403);

        $state = $tickerService->resume($match);

        return response()->json($this->statePayload($request, $state, app(LeagueTableService::class)));
    }

    public function liveSetStyle(
        Request $request,
        GameMatch $match,
        LiveMatchTickerService $tickerService
    ): JsonResponse {
        $manageableClubIds = $this->manageableClubIds($request, $match);
        $clubId = (int) $request->input('club_id');
        abort_unless(in_array($clubId, $manageableClubIds, true), 403);

        $style = (string) $request->input('tactical_style', 'balanced');
        $state = $tickerService->setTacticalStyle($match, $clubId, $style);

        return response()->json($this->statePayload($request, $state, app(LeagueTableService::class)));
    }

    public function liveSubstitute(
        Request $request,
        GameMatch $match,
        LiveMatchTickerService $tickerService
    ): JsonResponse {
        $manageableClubIds = $this->manageableClubIds($request, $match);
        $validated = $request->validate([
            'club_id' => ['required', 'integer'],
            'player_out_id' => ['required', 'integer'],
            'player_in_id' => ['required', 'integer'],
            'target_slot' => ['nullable', 'string', 'max:20'],
        ]);

        $clubId = (int) $validated['club_id'];
        abort_unless(in_array($clubId, $manageableClubIds, true), 403);

        $state = $tickerService->makeSubstitution(
            $match,
            $clubId,
            (int) $validated['player_out_id'],
            (int) $validated['player_in_id'],
            (string) ($validated['target_slot'] ?? '')
        );

        return response()->json($this->statePayload($request, $state, app(LeagueTableService::class)));
    }

    public function livePlanSubstitute(
        Request $request,
        GameMatch $match,
        LiveMatchTickerService $tickerService
    ): JsonResponse {
        $manageableClubIds = $this->manageableClubIds($request, $match);
        $validated = $request->validate([
            'club_id' => ['required', 'integer'],
            'player_out_id' => ['required', 'integer'],
            'player_in_id' => ['required', 'integer'],
            'planned_minute' => ['required', 'integer', 'min:1', 'max:120'],
            'score_condition' => ['nullable', 'string', 'in:any,leading,drawing,trailing'],
            'target_slot' => ['nullable', 'string', 'max:20'],
        ]);

        $clubId = (int) $validated['club_id'];
        abort_unless(in_array($clubId, $manageableClubIds, true), 403);

        $state = $tickerService->planSubstitution(
            $match,
            $clubId,
            (int) $validated['player_out_id'],
            (int) $validated['player_in_id'],
            (int) $validated['planned_minute'],
            (string) ($validated['score_condition'] ?? 'any'),
            (string) ($validated['target_slot'] ?? '')
        );

        return response()->json($this->statePayload($request, $state, app(LeagueTableService::class)));
    }

    public function liveShout(
        Request $request,
        GameMatch $match,
        LiveMatchTickerService $tickerService
    ): JsonResponse {
        $manageableClubIds = $this->manageableClubIds($request, $match);
        $validated = $request->validate([
            'club_id' => ['required', 'integer'],
            'shout' => ['required', 'string', 'in:demand_more,concentrate,encourage,calm_down'],
        ]);

        $clubId = (int) $validated['club_id'];
        abort_unless(in_array($clubId, $manageableClubIds, true), 403);

        $state = $tickerService->handleManagerShout(
            $match,
            $clubId,
            (string) $validated['shout']
        );

        return response()->json($this->statePayload($request, $state, app(LeagueTableService::class)));
    }

    public function liveState(Request $request, GameMatch $match): JsonResponse
    {
        $this->ensureReadable($request, $match);

        $this->loadMatchStateRelations($match, false);

        return response()->json($this->statePayload($request, $match, app(LeagueTableService::class)));
    }

    private function ensureReadable(Request $request, GameMatch $match): void
    {
        if ($request->user()->isAdmin()) {
            return;
        }

        $ownsHome = $request->user()->clubs()->whereKey($match->home_club_id)->exists();
        $ownsAway = $request->user()->clubs()->whereKey($match->away_club_id)->exists();
        abort_unless($ownsHome || $ownsAway, 403);
    }

    private function canSimulate(Request $request, GameMatch $match): bool
    {
        if ($request->user()->isAdmin()) {
            return true;
        }

        if ($match->status === 'played') {
            return false;
        }

        return $request->user()->clubs()->whereKey($match->home_club_id)->exists()
            || $request->user()->clubs()->whereKey($match->away_club_id)->exists();
    }

    private function manageableClubIds(Request $request, GameMatch $match): array
    {
        if ($request->user()->isAdmin()) {
            return [$match->home_club_id, $match->away_club_id];
        }

        return $request->user()->clubs()
            ->whereIn('id', [$match->home_club_id, $match->away_club_id])
            ->pluck('id')
            ->all();
    }

    private function loadMatchStateRelations(GameMatch $match, bool $withCompetition): void
    {
        $relations = [
            'homeClub',
            'homeClub.stadium:id,name',
            'awayClub',
            'events:id,match_id,minute,second,event_type,club_id,player_id,assister_player_id,narrative,metadata',
            'events.player:id,first_name,last_name,photo_path',
            'events.assister:id,first_name,last_name',
            'events.club',
            'playerStats:id,match_id,player_id,club_id,rating,goals,assists,minutes_played,shots',
            'playerStats.player:id,first_name,last_name',
            'liveTeamStates:id,match_id,club_id,tactical_style,phase,possession_seconds,actions_count,dangerous_attacks,pass_attempts,pass_completions,tackle_attempts,tackle_won,fouls_committed,corners_won,shots,shots_on_target,expected_goals,yellow_cards,red_cards,substitutions_used,tactical_changes_count,last_tactical_change_minute,last_substitution_minute',
            'livePlayerStates:id,match_id,club_id,player_id,slot,is_on_pitch,is_sent_off,is_injured,fit_factor,minutes_played,ball_contacts,pass_attempts,pass_completions,tackle_attempts,tackle_won,fouls_committed,fouls_suffered,shots,shots_on_target,goals,assists,yellow_cards,red_cards,saves',
            'livePlayerStates.player:id,first_name,last_name,photo_path',
            'liveActions:id,match_id,club_id,player_id,opponent_player_id,minute,second,sequence,action_type,outcome,narrative,x_coord,y_coord,metadata',
            'liveActions.club',
            'liveActions.player:id,first_name,last_name,photo_path',
            'liveActions.opponentPlayer:id,first_name,last_name,photo_path',
            'liveMinuteSnapshots:id,match_id,minute,home_score,away_score,home_phase,away_phase,home_tactical_style,away_tactical_style,pending_plans,executed_plans,skipped_plans,invalid_plans',
            'plannedSubstitutions:id,match_id,club_id,player_out_id,player_in_id,planned_minute,score_condition,target_slot,status,executed_minute,metadata',
            'plannedSubstitutions.playerOut:id,first_name,last_name',
            'plannedSubstitutions.playerIn:id,first_name,last_name',
        ];

        $relations[] = 'competitionSeason.competition:id,name';

        $match->load($relations);
    }

    private function statePayload(Request $request, GameMatch $match, LeagueTableService $leagueTableService): array
    {
        $statusLabel = match ($match->status) {
            'played' => 'Beendet',
            'live' => $match->live_paused ? 'Pausiert' : 'Live',
            default => ucfirst((string) $match->status),
        };

        $payload = [
            'id' => $match->id,
            'status' => $match->status,
            'status_label' => $statusLabel,
            'live_minute' => (int) $match->live_minute,
            'display_minute' => $this->displayMinute((int) $match->live_minute),
            'live_paused' => (bool) $match->live_paused,
            'live_error_message' => $match->live_error_message,
            'home_score' => $match->home_score,
            'away_score' => $match->away_score,
            'can_simulate' => $this->canSimulate($request, $match),
            'manageable_club_ids' => $this->manageableClubIds($request, $match),
            'lineups' => $this->lineupsPayload($match),
            'events' => $match->events
                ->sortByDesc(fn($event) => ($event->minute * 60) + $event->second)
                ->values()
                ->map(function ($event): array {
                    return [
                        'id' => $event->id,
                        'minute' => (int) $event->minute,
                        'display_minute' => $this->displayMinute((int) $event->minute, is_array($event->metadata) ? $event->metadata : []),
                        'second' => (int) $event->second,
                        'event_type' => (string) $event->event_type,
                        'club_id' => $event->club_id !== null ? (int) $event->club_id : null,
                        'player_id' => $event->player_id !== null ? (int) $event->player_id : null,
                        'player_name' => $event->player?->full_name,
                        'assister_name' => $event->assister?->full_name,
                        'club_short_name' => $event->club?->short_name ?: $event->club?->name,
                        'narrative' => (string) ($event->narrative ?? ''),
                        'metadata' => is_array($event->metadata) ? $event->metadata : [],
                    ];
                })
                ->all(),
            'team_states' => $match->liveTeamStates
                ->mapWithKeys(function ($state): array {
                    return [
                        (string) $state->club_id => [
                            'club_id' => (int) $state->club_id,
                            'tactical_style' => (string) $state->tactical_style,
                            'phase' => (string) ($state->phase ?? ''),
                            'possession_seconds' => (int) $state->possession_seconds,
                            'actions_count' => (int) $state->actions_count,
                            'dangerous_attacks' => (int) $state->dangerous_attacks,
                            'pass_attempts' => (int) $state->pass_attempts,
                            'pass_completions' => (int) $state->pass_completions,
                            'tackle_attempts' => (int) $state->tackle_attempts,
                            'tackle_won' => (int) $state->tackle_won,
                            'fouls_committed' => (int) $state->fouls_committed,
                            'corners_won' => (int) $state->corners_won,
                            'shots' => (int) $state->shots,
                            'shots_on_target' => (int) $state->shots_on_target,
                            'expected_goals' => (float) $state->expected_goals,
                            'yellow_cards' => (int) $state->yellow_cards,
                            'red_cards' => (int) $state->red_cards,
                            'substitutions_used' => (int) $state->substitutions_used,
                            'tactical_changes_count' => (int) $state->tactical_changes_count,
                            'last_tactical_change_minute' => $state->last_tactical_change_minute !== null ? (int) $state->last_tactical_change_minute : null,
                            'last_substitution_minute' => $state->last_substitution_minute !== null ? (int) $state->last_substitution_minute : null,
                        ],
                    ];
                })
                ->all(),
            'player_states' => $match->livePlayerStates
                ->map(function ($state): array {
                    return [
                        'player_id' => (int) $state->player_id,
                        'club_id' => (int) $state->club_id,
                        'player_name' => $state->player?->full_name,
                        'slot' => (string) ($state->slot ?? ''),
                        'is_on_pitch' => (bool) $state->is_on_pitch,
                        'is_sent_off' => (bool) $state->is_sent_off,
                        'is_injured' => (bool) $state->is_injured,
                        'fit_factor' => (float) $state->fit_factor,
                        'minutes_played' => (int) $state->minutes_played,
                        'ball_contacts' => (int) $state->ball_contacts,
                        'pass_attempts' => (int) $state->pass_attempts,
                        'pass_completions' => (int) $state->pass_completions,
                        'tackle_attempts' => (int) $state->tackle_attempts,
                        'tackle_won' => (int) $state->tackle_won,
                        'fouls_committed' => (int) $state->fouls_committed,
                        'fouls_suffered' => (int) $state->fouls_suffered,
                        'shots' => (int) $state->shots,
                        'shots_on_target' => (int) $state->shots_on_target,
                        'goals' => (int) $state->goals,
                        'assists' => (int) $state->assists,
                        'yellow_cards' => (int) $state->yellow_cards,
                        'red_cards' => (int) $state->red_cards,
                        'saves' => (int) $state->saves,
                        'photo_url' => $state->player?->photo_url,
                    ];
                })
                ->values()
                ->all(),
            'final_stats' => $match->playerStats
                ->map(function ($stat): array {
                    return [
                        'player_id' => (int) $stat->player_id,
                        'club_id' => (int) $stat->club_id,
                        'player_name' => $stat->player?->full_name,
                        'rating' => (float) $stat->rating,
                        'goals' => (int) $stat->goals,
                        'assists' => (int) $stat->assists,
                        'minutes_played' => (int) $stat->minutes_played,
                        'shots' => (int) $stat->shots,
                    ];
                })
                ->values()
                ->all(),
            'actions' => ($match->liveActions->isNotEmpty() ? $match->liveActions : $match->events)
                ->sortByDesc(fn($item) => ($item->minute * 100000) + ($item->second * 1000) + ($item->sequence ?? 0))
                ->take(400)
                ->values()
                ->map(function ($item): array {
                    // Normalize between MatchLiveAction and MatchEvent
                    $isAction = isset($item->action_type);
                    $metadata = is_array($item->metadata) ? $item->metadata : [];
                    $assisterName = $isAction ? ($metadata['assister_name'] ?? null) : $item->assister?->full_name;

                    return [
                        'id' => (int) $item->id,
                        'minute' => (int) $item->minute,
                        'display_minute' => $this->displayMinute((int) $item->minute, $metadata),
                        'second' => (int) $item->second,
                        'sequence' => (int) ($item->sequence ?? 0),
                        'club_id' => $item->club_id !== null ? (int) $item->club_id : null,
                        'club_short_name' => $item->club?->short_name ?: $item->club?->name,
                        'club_logo_url' => $item->club?->logo_url,
                        'player_id' => $item->player_id !== null ? (int) $item->player_id : null,
                        'player_name' => $item->player?->full_name,
                        'player_photo_url' => $item->player?->photo_url,
                        'assister_name' => $assisterName,
                        'opponent_player_id' => $isAction && $item->opponent_player_id !== null ? (int) $item->opponent_player_id : null,
                        'opponent_player_name' => $isAction ? $item->opponentPlayer?->full_name : null,
                        'opponent_player_photo_url' => $isAction ? $item->opponentPlayer?->photo_url : null,
                        'action_type' => (string) ($isAction ? $item->action_type : $item->event_type),
                        'outcome' => (string) ($item->outcome ?? ''),
                        'narrative' => (string) ($item->narrative ?? ''),
                        'x_coord' => $isAction ? (float) $item->x_coord : null,
                        'y_coord' => $isAction ? (float) $item->y_coord : null,
                        'metadata' => $metadata,
                    ];
                })
                ->all(),
            'planned_substitutions' => $match->plannedSubstitutions
                ->map(function ($plan): array {
                    return [
                        'id' => (int) $plan->id,
                        'club_id' => (int) $plan->club_id,
                        'player_out_id' => $plan->player_out_id !== null ? (int) $plan->player_out_id : null,
                        'player_out_name' => $plan->playerOut?->full_name,
                        'player_in_id' => $plan->player_in_id !== null ? (int) $plan->player_in_id : null,
                        'player_in_name' => $plan->playerIn?->full_name,
                        'planned_minute' => (int) $plan->planned_minute,
                        'score_condition' => (string) $plan->score_condition,
                        'target_slot' => (string) ($plan->target_slot ?? ''),
                        'status' => (string) $plan->status,
                        'executed_minute' => $plan->executed_minute !== null ? (int) $plan->executed_minute : null,
                        'metadata' => $plan->metadata,
                    ];
                })
                ->values()
                ->all(),
            'live_table' => $this->liveTablePayload($match, $leagueTableService),
            'minute_snapshots' => $match->liveMinuteSnapshots
                ->sortByDesc('minute')
                ->take(30)
                ->values()
                ->map(function ($snapshot): array {
                    return [
                        'minute' => (int) $snapshot->minute,
                        'home_score' => (int) $snapshot->home_score,
                        'away_score' => (int) $snapshot->away_score,
                        'home_phase' => (string) ($snapshot->home_phase ?? ''),
                        'away_phase' => (string) ($snapshot->away_phase ?? ''),
                        'home_tactical_style' => (string) ($snapshot->home_tactical_style ?? ''),
                        'away_tactical_style' => (string) ($snapshot->away_tactical_style ?? ''),
                        'pending_plans' => (int) $snapshot->pending_plans,
                        'executed_plans' => (int) $snapshot->executed_plans,
                        'skipped_plans' => (int) $snapshot->skipped_plans,
                        'invalid_plans' => (int) $snapshot->invalid_plans,
                    ];
                })
                ->all(),
        ];

        $payload['module_panels'] = $this->modulePanelsPayload($match, $payload);

        return $payload;
    }

    private function liveTablePayload(GameMatch $match, LeagueTableService $leagueTableService): ?array
    {
        $competitionSeason = $match->competitionSeason;

        if (!$competitionSeason || $match->type !== 'league') {
            return null;
        }

        $rows = $leagueTableService->table($competitionSeason)
            ->map(function ($row): array {
                return [
                    'club_id' => (int) $row->club_id,
                    'club_name' => (string) ($row->club?->name ?? ''),
                    'club_short_name' => (string) ($row->club?->short_name ?? $row->club?->name ?? ''),
                    'club_logo_url' => $row->club?->logo_url,
                    'played' => (int) ($row->matches_played ?? 0),
                    'won' => (int) ($row->wins ?? 0),
                    'drawn' => (int) ($row->draws ?? 0),
                    'lost' => (int) ($row->losses ?? 0),
                    'goals_for' => (int) ($row->goals_for ?? 0),
                    'goals_against' => (int) ($row->goals_against ?? 0),
                    'goal_diff' => (int) ($row->goal_diff ?? 0),
                    'points' => (int) ($row->points ?? 0),
                    'form' => collect($row->form_last5 ?? [])->values()->all(),
                ];
            })
            ->keyBy('club_id');

        if ($match->status === 'live') {
            $homeId = (int) $match->home_club_id;
            $awayId = (int) $match->away_club_id;
            $homeScore = (int) ($match->home_score ?? 0);
            $awayScore = (int) ($match->away_score ?? 0);

            if ($rows->has($homeId) && $rows->has($awayId)) {
                $home = $rows[$homeId];
                $away = $rows[$awayId];

                $home['played'] += 1;
                $away['played'] += 1;
                $home['goals_for'] += $homeScore;
                $home['goals_against'] += $awayScore;
                $away['goals_for'] += $awayScore;
                $away['goals_against'] += $homeScore;

                if ($homeScore > $awayScore) {
                    $home['won'] += 1;
                    $home['points'] += 3;
                    $away['lost'] += 1;
                    array_unshift($home['form'], 'W');
                    array_unshift($away['form'], 'L');
                } elseif ($homeScore < $awayScore) {
                    $away['won'] += 1;
                    $away['points'] += 3;
                    $home['lost'] += 1;
                    array_unshift($home['form'], 'L');
                    array_unshift($away['form'], 'W');
                } else {
                    $home['drawn'] += 1;
                    $away['drawn'] += 1;
                    $home['points'] += 1;
                    $away['points'] += 1;
                    array_unshift($home['form'], 'D');
                    array_unshift($away['form'], 'D');
                }

                $home['goal_diff'] = $home['goals_for'] - $home['goals_against'];
                $away['goal_diff'] = $away['goals_for'] - $away['goals_against'];
                $home['form'] = array_slice($home['form'], 0, 5);
                $away['form'] = array_slice($away['form'], 0, 5);

                $rows[$homeId] = $home;
                $rows[$awayId] = $away;
            }
        }

        return [
            'competition' => (string) ($competitionSeason->competition?->name ?? 'Liga'),
            'rows' => $rows->values()
                ->sort(function (array $a, array $b): int {
                    return [$b['points'], $b['goal_diff'], $b['goals_for']] <=> [$a['points'], $a['goal_diff'], $a['goals_for']];
                })
                ->values()
                ->map(fn (array $row, int $index): array => array_merge($row, ['position' => $index + 1]))
                ->all(),
            'home_club_id' => (int) $match->home_club_id,
            'away_club_id' => (int) $match->away_club_id,
            'is_live_projection' => $match->status === 'live',
        ];
    }

    private function modulePanelsPayload(GameMatch $match, array $state): array
    {
        $registry = app(ModuleManager::class)->frontendRegistry();
        $definitions = collect($registry['matchcenter_panels'] ?? [])
            ->sortBy(fn (array $panel) => (int) ($panel['priority'] ?? 999))
            ->values();

        if ($definitions->isEmpty()) {
            return [];
        }

        $lineupPlayerIds = collect($state['lineups'] ?? [])
            ->flatMap(function (array $lineup): array {
                $starters = collect($lineup['starters'] ?? [])->pluck('id');
                $bench = collect($lineup['bench'] ?? [])->pluck('id');

                return $starters->merge($bench)->filter()->values()->all();
            })
            ->unique()
            ->values();

        $lineupPlayers = $lineupPlayerIds->isNotEmpty()
            ? Player::query()
                ->with(['injuries' => fn ($query) => $query->where('status', 'active')->latest('id')])
                ->whereIn('id', $lineupPlayerIds)
                ->get(['id', 'club_id', 'first_name', 'last_name', 'photo_path', 'medical_status', 'fatigue'])
                ->keyBy('id')
            : collect();

        $injuredOnPitchCount = collect($state['player_states'] ?? [])->where('is_injured', true)->count();
        $sentOffCount = collect($state['player_states'] ?? [])->where('is_sent_off', true)->count();
        $liveManagerCount = ManagerPresence::query()
            ->where('match_id', $match->id)
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->count();

        return $definitions->map(function (array $panel) use ($match, $state, $lineupPlayers, $injuredOnPitchCount, $sentOffCount, $liveManagerCount): array {
            $data = match ($panel['key'] ?? null) {
                'live-center-match-pulse' => [
                    'headline' => (($state['status'] ?? 'scheduled') === 'live' ? 'Live '.$state['live_minute'].'\'' : ($state['status_label'] ?? 'Matchday')),
                    'summary' => $liveManagerCount > 0
                        ? $liveManagerCount.' manager channels active on this fixture.'
                        : 'No active manager presence detected for this fixture.',
                    'stats' => [
                        ['label' => 'Minute', 'value' => (int) ($state['live_minute'] ?? 0)],
                        ['label' => 'Actions', 'value' => count($state['actions'] ?? [])],
                        ['label' => 'Managers', 'value' => $liveManagerCount],
                    ],
                ],
                'medical-center-match-risk' => $this->medicalMatchcenterPanelData($match, $lineupPlayers, $injuredOnPitchCount, $sentOffCount),
                'awards-center-match-awards' => $this->awardsMatchcenterPanelData($match, $state, $lineupPlayers),
                default => [
                    'headline' => $panel['title'] ?? 'Module Panel',
                    'summary' => $panel['description'] ?? '',
                    'stats' => [],
                ],
            };

            return array_merge($panel, ['data' => $data]);
        })->all();
    }

    private function medicalMatchcenterPanelData(GameMatch $match, $lineupPlayers, int $injuredOnPitchCount, int $sentOffCount): array
    {
        $critical = $lineupPlayers
            ->filter(function (Player $player): bool {
                $injury = $player->injuries->first();

                return in_array((string) $player->medical_status, ['rehab', 'monitoring', 'risk'], true)
                    || in_array((string) ($injury?->availability_status), ['unavailable', 'bench_only', 'limited'], true)
                    || (int) $player->fatigue >= 75;
            })
            ->sortByDesc(function (Player $player): int {
                $injury = $player->injuries->first();

                return match (true) {
                    (string) ($injury?->availability_status) === 'unavailable' => 5,
                    (string) ($injury?->availability_status) === 'bench_only' => 4,
                    (string) ($injury?->availability_status) === 'limited' => 3,
                    (string) $player->medical_status === 'risk' => 2,
                    default => 1,
                };
            })
            ->take(3)
            ->values();

        return [
            'headline' => $critical->isNotEmpty() ? $critical->count().' medical flags' : 'Matchday green light',
            'summary' => $critical->isNotEmpty()
                ? 'Medical and fatigue warnings are active for selected lineup players.'
                : 'No critical medical restrictions detected in the current lineup pool.',
            'stats' => [
                ['label' => 'Flags', 'value' => $critical->count()],
                ['label' => 'On-pitch injuries', 'value' => $injuredOnPitchCount],
                ['label' => 'Sent off', 'value' => $sentOffCount],
            ],
            'players' => $critical->map(function (Player $player): array {
                $injury = $player->injuries->first();

                return [
                    'id' => $player->id,
                    'name' => $player->full_name,
                    'photo_url' => $player->photo_url,
                    'medical_status' => $player->medical_status,
                    'fatigue' => (int) $player->fatigue,
                    'availability_status' => $injury?->availability_status,
                ];
            })->all(),
        ];
    }

    private function awardsMatchcenterPanelData(GameMatch $match, array $state, Collection $lineupPlayers): array
    {
        $playerDirectory = collect($state['player_states'] ?? [])
            ->mapWithKeys(fn (array $player): array => [
                (int) $player['player_id'] => [
                    'id' => (int) $player['player_id'],
                    'name' => (string) ($player['player_name'] ?? ''),
                    'club_id' => (int) ($player['club_id'] ?? 0),
                    'photo_url' => $player['photo_url'] ?? null,
                ],
            ])
            ->merge(
                $lineupPlayers->mapWithKeys(fn (Player $player): array => [
                    (int) $player->id => [
                        'id' => (int) $player->id,
                        'name' => (string) $player->full_name,
                        'club_id' => (int) ($player->club_id ?? 0),
                        'photo_url' => $player->photo_url,
                    ],
                ])
            );

        $clubs = collect([
            (int) $match->homeClub->id => [
                'id' => (int) $match->homeClub->id,
                'name' => (string) $match->homeClub->name,
                'short_name' => (string) ($match->homeClub->short_name ?: $match->homeClub->name),
                'logo_url' => $match->homeClub->logo_url,
            ],
            (int) $match->awayClub->id => [
                'id' => (int) $match->awayClub->id,
                'name' => (string) $match->awayClub->name,
                'short_name' => (string) ($match->awayClub->short_name ?: $match->awayClub->name),
                'logo_url' => $match->awayClub->logo_url,
            ],
        ]);

        $awards = collect([
            $this->playerOfTheMatchAward($state, $playerDirectory, $clubs),
            $this->turningPointAward($state, $playerDirectory, $clubs, $match),
            $this->saveOfTheGameAward($state, $playerDirectory, $clubs, $match),
        ])->filter()->values();

        if ($awards->isEmpty()) {
            return [
                'headline' => 'Awards pending',
                'summary' => 'Start or finish the match to unlock player of the match, turning point, and save of the game.',
                'stats' => [
                    ['label' => 'Awards', 'value' => 0],
                    ['label' => 'Status', 'value' => ucfirst((string) ($state['status'] ?? 'scheduled'))],
                    ['label' => 'Minute', 'value' => (int) ($state['live_minute'] ?? 0)],
                ],
                'awards' => [],
            ];
        }

        $playerOfMatch = $awards->firstWhere('award_key', 'player_of_the_match');
        $turningPoint = $awards->firstWhere('award_key', 'turning_point');
        $saveOfGame = $awards->firstWhere('award_key', 'save_of_the_game');

        return [
            'headline' => $awards->count().' match awards ready',
            'summary' => 'A compact award layer for standout performances and key moments from this fixture.',
            'stats' => [
                ['label' => 'Awards', 'value' => $awards->count()],
                ['label' => 'Best rating', 'value' => $playerOfMatch['value_label'] ?? '-'],
                ['label' => 'Top save', 'value' => $saveOfGame['value_label'] ?? ($turningPoint['value_label'] ?? '-')],
            ],
            'awards' => $awards->all(),
        ];
    }

    private function playerOfTheMatchAward(array $state, Collection $playerDirectory, Collection $clubs): ?array
    {
        $finalStats = collect($state['final_stats'] ?? []);
        $liveStates = collect($state['player_states'] ?? []);

        $candidate = $finalStats->isNotEmpty()
            ? $finalStats
                ->map(function (array $stat): array {
                    $score = ((float) ($stat['rating'] ?? 0) * 10)
                        + ((int) ($stat['goals'] ?? 0) * 14)
                        + ((int) ($stat['assists'] ?? 0) * 8)
                        + ((int) ($stat['shots'] ?? 0) * 1.5);

                    return [...$stat, 'award_score' => $score];
                })
                ->sortByDesc('award_score')
                ->first()
            : $liveStates
                ->map(function (array $stat): array {
                    $score = ((int) ($stat['goals'] ?? 0) * 16)
                        + ((int) ($stat['assists'] ?? 0) * 10)
                        + ((int) ($stat['shots_on_target'] ?? 0) * 3)
                        + ((int) ($stat['shots'] ?? 0) * 1.5)
                        + ((int) ($stat['saves'] ?? 0) * 4)
                        + ((int) ($stat['tackle_won'] ?? 0) * 0.6)
                        + ((int) ($stat['pass_completions'] ?? 0) * 0.05)
                        - ((int) ($stat['yellow_cards'] ?? 0) * 3)
                        - ((int) ($stat['red_cards'] ?? 0) * 10);

                    return [...$stat, 'award_score' => $score];
                })
                ->sortByDesc('award_score')
                ->first();

        if (!$candidate || empty($candidate['player_id'])) {
            return null;
        }

        $player = $playerDirectory->get((int) $candidate['player_id'], []);
        $club = $clubs->get((int) ($candidate['club_id'] ?? $player['club_id'] ?? 0), []);
        $rating = $candidate['rating'] ?? null;
        $goalText = (int) ($candidate['goals'] ?? 0) > 0 ? (int) $candidate['goals'].' goals' : 'strong all-around output';

        return [
            'award_key' => 'player_of_the_match',
            'label' => 'Player of the Match',
            'value_label' => $rating ? number_format((float) $rating, 1) : $goalText,
            'summary' => $rating
                ? 'Led the match with the best overall rating and decisive contributions.'
                : 'Stood out through '.$goalText.' and a strong live stat profile.',
            'player_id' => (int) $candidate['player_id'],
            'player_name' => $player['name'] ?? ($candidate['player_name'] ?? 'Unknown'),
            'photo_url' => $player['photo_url'] ?? null,
            'club_name' => $club['name'] ?? null,
            'club_logo_url' => $club['logo_url'] ?? null,
        ];
    }

    private function turningPointAward(array $state, Collection $playerDirectory, Collection $clubs, GameMatch $match): ?array
    {
        $actions = collect($state['actions'] ?? [])
            ->sortBy(fn (array $action): array => [
                (int) ($action['minute'] ?? 0),
                (int) ($action['second'] ?? 0),
                (int) ($action['sequence'] ?? 0),
            ])
            ->values();

        if ($actions->isEmpty()) {
            return null;
        }

        $home = 0;
        $away = 0;

        $candidate = $actions
            ->map(function (array $action) use (&$home, &$away, $match): ?array {
                $type = (string) ($action['action_type'] ?? '');
                $importance = null;
                $summary = null;
                $minute = min((int) ($action['minute'] ?? 0), 95);
                $beforeHome = $home;
                $beforeAway = $away;
                $beforeDiff = abs($beforeHome - $beforeAway);

                if (in_array($type, ['goal', 'own_goal'], true)) {
                    $scoringClubId = (int) ($action['club_id'] ?? 0);

                    if ($type === 'goal') {
                        if ($scoringClubId === (int) $match->home_club_id) {
                            $home++;
                        } else {
                            $away++;
                        }
                    } else {
                        if ($scoringClubId === (int) $match->home_club_id) {
                            $away++;
                        } else {
                            $home++;
                        }
                    }

                    $afterDiff = abs($home - $away);
                    $wasDraw = $beforeHome === $beforeAway;
                    $leadChanged = ($beforeHome > $beforeAway && $home <= $away) || ($beforeAway > $beforeHome && $away <= $home);
                    $equalizer = !$wasDraw && $home === $away;
                    $decisiveLateGoal = $minute >= 70 && $afterDiff === 1;

                    $importance = 90
                        + ($wasDraw ? 42 : 0)
                        + ($equalizer ? 28 : 0)
                        + ($leadChanged ? 22 : 0)
                        + ($decisiveLateGoal ? 18 : 0)
                        + (($afterDiff > $beforeDiff) ? 10 : 0)
                        + $minute;

                    $summary = match (true) {
                        $wasDraw => 'This goal broke the deadlock and gave one side control of the match.',
                        $equalizer => 'This goal pulled the game level again and completely changed the momentum.',
                        $leadChanged => 'This moment flipped the scoreline and changed who was in command.',
                        $decisiveLateGoal => 'A late goal created the defining swing of the match.',
                        default => 'This action sharply changed the state of the scoreline.',
                    };
                } elseif (in_array($type, ['red_card', 'yellow_red_card'], true)) {
                    $importance = 78
                        + ($beforeDiff <= 1 ? 16 : 0)
                        + ($minute >= 60 ? 14 : 0)
                        + $minute;
                    $summary = $beforeDiff <= 1
                        ? 'A sending off changed a still-close match at a key moment.'
                        : 'A sending off reshaped the tactical balance of the game.';
                } elseif ($type === 'penalty') {
                    $importance = 70
                        + ($beforeDiff <= 1 ? 16 : 0)
                        + ($minute >= 70 ? 12 : 0)
                        + $minute;
                    $summary = 'A penalty situation created one of the defining swings of the match.';
                }

                if ($importance === null) {
                    return null;
                }

                return [...$action, 'importance' => $importance, 'award_summary' => $summary];
            })
            ->filter()
            ->sortByDesc('importance')
            ->first();

        if (!$candidate) {
            return null;
        }

        $playerId = (int) ($candidate['player_id'] ?? $candidate['opponent_player_id'] ?? 0);
        $player = $playerDirectory->get($playerId, []);
        $club = $clubs->get((int) ($candidate['club_id'] ?? $player['club_id'] ?? 0), []);

        return [
            'award_key' => 'turning_point',
            'label' => 'Turning Point',
            'value_label' => trim((string) (($candidate['display_minute'] ?? $candidate['minute'] ?? 0)."'")),
            'summary' => (string) ($candidate['award_summary'] ?? 'A defining moment changed the direction of this fixture.'),
            'player_id' => $playerId > 0 ? $playerId : null,
            'player_name' => $player['name'] ?? ($candidate['player_name'] ?? $candidate['opponent_player_name'] ?? ($club['short_name'] ?? 'Match Event')),
            'photo_url' => $player['photo_url'] ?? ($candidate['player_photo_url'] ?? $candidate['opponent_player_photo_url'] ?? null),
            'club_name' => $club['name'] ?? null,
            'club_logo_url' => $club['logo_url'] ?? ($candidate['club_logo_url'] ?? null),
        ];
    }

    private function saveOfTheGameAward(array $state, Collection $playerDirectory, Collection $clubs, GameMatch $match): ?array
    {
        $timeline = collect($state['actions'] ?? [])
            ->sortBy(fn (array $action): array => [
                (int) ($action['minute'] ?? 0),
                (int) ($action['second'] ?? 0),
                (int) ($action['sequence'] ?? 0),
            ])
            ->values();

        $homeScore = 0;
        $awayScore = 0;

        $saveAction = $timeline
            ->map(function (array $action) use (&$homeScore, &$awayScore, $match): ?array {
                $type = (string) ($action['action_type'] ?? '');
                $minute = min((int) ($action['minute'] ?? 0), 95);
                $beforeHome = $homeScore;
                $beforeAway = $awayScore;

                if ($type === 'goal') {
                    if ((int) ($action['club_id'] ?? 0) === (int) $match->home_club_id) {
                        $homeScore++;
                    } else {
                        $awayScore++;
                    }
                } elseif ($type === 'own_goal') {
                    if ((int) ($action['club_id'] ?? 0) === (int) $match->home_club_id) {
                        $awayScore++;
                    } else {
                        $homeScore++;
                    }
                }

                if ($type !== 'save') {
                    return null;
                }

                $xg = (float) ($action['metadata']['xg'] ?? 0);
                $keeperClubId = (int) ($action['club_id'] ?? 0);
                $isTightGame = abs($beforeHome - $beforeAway) <= 1;
                $protectingLead = ($keeperClubId === (int) $match->home_club_id && $beforeHome > $beforeAway)
                    || ($keeperClubId === (int) $match->away_club_id && $beforeAway > $beforeHome);
                $keepingDrawAlive = $beforeHome === $beforeAway;

                $score = ($xg * 150)
                    + ($minute >= 75 ? 18 : 0)
                    + ($isTightGame ? 18 : 0)
                    + ($protectingLead ? 16 : 0)
                    + ($keepingDrawAlive ? 12 : 0)
                    + $minute;

                return [
                    ...$action,
                    'award_score' => $score,
                    'award_summary' => match (true) {
                        $xg >= 0.30 && $protectingLead => 'A high-value stop protected the lead at a crucial stage.',
                        $xg >= 0.30 && $keepingDrawAlive => 'A high-value save kept the match level in a decisive moment.',
                        $xg >= 0.25 => 'A high-danger save denied one of the best chances of the match.',
                        $protectingLead => 'A timely save protected a narrow advantage.',
                        default => 'A standout save preserved the team during a dangerous moment.',
                    },
                ];
            })
            ->filter()
            ->sortByDesc('award_score')
            ->first();

        if ($saveAction) {
            $playerId = (int) ($saveAction['player_id'] ?? 0);
            $player = $playerDirectory->get($playerId, []);
            $club = $clubs->get((int) ($saveAction['club_id'] ?? $player['club_id'] ?? 0), []);

            return [
                'award_key' => 'save_of_the_game',
                'label' => 'Save of the Game',
                'value_label' => $saveAction['metadata']['xg']
                    ? 'xG '.number_format((float) $saveAction['metadata']['xg'], 2)
                    : trim((string) (($saveAction['display_minute'] ?? $saveAction['minute'] ?? 0)."'")),
                'summary' => (string) ($saveAction['award_summary'] ?? 'A standout save preserved the team during a dangerous moment.'),
                'player_id' => $playerId ?: null,
                'player_name' => $player['name'] ?? ($saveAction['player_name'] ?? 'Goalkeeper'),
                'photo_url' => $player['photo_url'] ?? ($saveAction['player_photo_url'] ?? null),
                'club_name' => $club['name'] ?? null,
                'club_logo_url' => $club['logo_url'] ?? ($saveAction['club_logo_url'] ?? null),
            ];
        }

        $goalkeeper = collect($state['player_states'] ?? [])
            ->filter(fn (array $player): bool => (int) ($player['saves'] ?? 0) > 0)
            ->sortByDesc('saves')
            ->first();

        if (!$goalkeeper) {
            return null;
        }

        $player = $playerDirectory->get((int) $goalkeeper['player_id'], []);
        $club = $clubs->get((int) ($goalkeeper['club_id'] ?? $player['club_id'] ?? 0), []);

        return [
            'award_key' => 'save_of_the_game',
            'label' => 'Save of the Game',
            'value_label' => (int) $goalkeeper['saves'].' saves',
            'summary' => 'Finished as the top shot-stopper in the match when no standout xG save was available.',
            'player_id' => (int) $goalkeeper['player_id'],
            'player_name' => $player['name'] ?? ($goalkeeper['player_name'] ?? 'Goalkeeper'),
            'photo_url' => $player['photo_url'] ?? null,
            'club_name' => $club['name'] ?? null,
            'club_logo_url' => $club['logo_url'] ?? null,
        ];
    }

    private function lineupsPayload(GameMatch $match): array
    {
        $positionService = app(PlayerPositionService::class);
        $formationPlanner = app(FormationPlannerService::class);
        $clubIds = [(int) $match->home_club_id, (int) $match->away_club_id];
        $lineups = [];

        foreach ($clubIds as $clubId) {
            /** @var Lineup|null $lineup */
            $lineup = Lineup::query()
                ->with(['players:id,first_name,last_name,position,position_main,position_second,position_third,overall,photo_path'])
                ->where('match_id', $match->id)
                ->where('club_id', $clubId)
                ->first();

            $players = collect();
            $formation = $formationPlanner->defaultFormation();

            if (!$lineup) {
                // AUTO-FILL: Generate a virtual lineup if missing
                $club = ($clubId === (int) $match->home_club_id) ? $match->homeClub : $match->awayClub;
                $selection = app(FormationPlannerService::class)->strongestByFormation(
                    $club->players()->whereIn('status', ['active', 'transfer_listed'])->get(),
                    $formationPlanner->defaultFormation(),
                    5
                );

                $formation = $formationPlanner->defaultFormation();
                $allDraftPlayerIds = array_merge(array_values($selection['starters'] ?? []), array_values($selection['bench'] ?? []));
                $allDraftPlayers = \App\Models\Player::query()
                    ->whereIn('id', $allDraftPlayerIds)
                    ->get(['id', 'first_name', 'last_name', 'position', 'position_main', 'position_second', 'position_third', 'overall', 'photo_path'])
                    ->keyBy('id');

                foreach ($selection['starters'] ?? [] as $slot => $playerId) {
                    $player = $allDraftPlayers->get($playerId);
                    if ($player) {
                        $players->push((object) [
                            'id' => $player->id,
                            'full_name' => $player->full_name,
                            'position_main' => $player->position_main,
                            'position' => $player->position,
                            'position_second' => $player->position_second,
                            'position_third' => $player->position_third,
                            'overall' => $player->overall,
                            'photo_url' => $player->photo_url,
                            'pivot' => (object) [
                                'pitch_position' => $slot,
                                'sort_order' => 1,
                                'is_bench' => false,
                                'bench_order' => null
                            ]
                        ]);
                    }
                }
                foreach ($selection['bench'] ?? [] as $idx => $playerId) {
                    $player = $allDraftPlayers->get($playerId);
                    if ($player) {
                        $players->push((object) [
                            'id' => $player->id,
                            'full_name' => $player->full_name,
                            'position_main' => $player->position_main,
                            'position' => $player->position,
                            'position_second' => $player->position_second,
                            'position_third' => $player->position_third,
                            'overall' => $player->overall,
                            'photo_url' => $player->photo_url,
                            'pivot' => (object) [
                                'pitch_position' => 'BANK-' . ($idx + 1),
                                'sort_order' => 100 + $idx,
                                'is_bench' => true,
                                'bench_order' => $idx + 1
                            ]
                        ]);
                    }
                }
            } else {
                $formation = (string) $lineup->formation;
                $players = $lineup->players;
            }

            $slotLayouts = collect($formationPlanner->starterSlots($formation))
                ->keyBy(fn (array $slot): string => strtoupper((string) ($slot['slot'] ?? '')));

            $mappedPlayers = $players->map(function ($player) use ($positionService): array {
                $slot = (string) ($player->pivot->pitch_position ?? '');
                $isRemoved = str_starts_with(strtoupper($slot), 'OUT-');
                $fitFactor = $positionService->fitFactorWithProfile(
                    (string) ($player->position_main ?: $player->position),
                    (string) $player->position_second,
                    (string) $player->position_third,
                    $slot
                );

                return [
                    'id' => (int) $player->id,
                    'name' => $player->full_name,
                    'position' => (string) ($player->position_main ?: $player->position),
                    'slot' => $slot,
                    'sort_order' => (int) $player->pivot->sort_order,
                    'is_bench' => (bool) $player->pivot->is_bench,
                    'is_removed' => $isRemoved,
                    'bench_order' => $player->pivot->bench_order !== null ? (int) $player->pivot->bench_order : null,
                    'fit_factor' => round($fitFactor, 2),
                    'overall' => (int) $player->overall,
                    'photo_url' => $player->photo_url,
                ];
            })->map(function (array $player) use ($slotLayouts): array {
                $layout = $slotLayouts->get(strtoupper((string) $player['slot']));

                if (!$layout || $player['is_bench'] || $player['is_removed']) {
                    return $player;
                }

                $player['pitch_x'] = isset($layout['x']) ? (int) $layout['x'] : null;
                $player['pitch_y'] = isset($layout['y']) ? (int) $layout['y'] : null;
                $player['slot_group'] = (string) ($layout['group'] ?? '');

                return $player;
            });

            $lineups[(string) $clubId] = [
                'club_id' => $clubId,
                'formation' => $formation,
                'tactical_style' => $lineup ? (string) $lineup->tactical_style : 'balanced',
                'attack_focus' => $lineup ? (string) $lineup->attack_focus : 'center',
                'starters' => $mappedPlayers
                    ->where('is_bench', false)
                    ->where('is_removed', false)
                    ->sortBy('sort_order')
                    ->values()
                    ->all(),
                'bench' => $mappedPlayers
                    ->where('is_bench', true)
                    ->where('is_removed', false)
                    ->sortBy('bench_order')
                    ->values()
                    ->all(),
                'removed' => $mappedPlayers
                    ->where('is_removed', true)
                    ->sortBy('sort_order')
                    ->values()
                    ->all(),
            ];
        }

        return $lineups;
    }

    private function displayMinute(int $minute, array $metadata = []): string
    {
        $explicit = $metadata['display_minute'] ?? null;
        if (is_string($explicit) && trim($explicit) !== '') {
            return trim($explicit);
        }

        if (is_numeric($explicit)) {
            return (string) (int) $explicit;
        }

        $stoppageBase = $metadata['stoppage_base'] ?? null;
        $stoppageMinutes = $metadata['stoppage_minutes'] ?? null;

        if (is_numeric($stoppageBase) && is_numeric($stoppageMinutes)) {
            return (int) $stoppageBase . '+' . (int) $stoppageMinutes;
        }

        return (string) max(0, $minute);
    }
}
