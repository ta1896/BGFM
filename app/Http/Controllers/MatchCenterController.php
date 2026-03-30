<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Services\LeagueTableService;
use App\Services\LiveMatchTickerService;
use App\Services\MatchCenterPanelService;
use App\Services\MatchPreviewService;
use App\Services\MatchCenterStateService;
use App\Services\MatchLiveLineupService;
use App\Services\MatchSimulationService;
use App\Services\FormationPlannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchCenterController extends Controller
{
    public function show(
        Request $request,
        GameMatch $match,
        LeagueTableService $leagueTableService,
        MatchPreviewService $matchPreviewService,
        MatchCenterStateService $matchCenterStateService,
        MatchCenterPanelService $matchCenterPanelService
    ): \Inertia\Response
    {
        $this->ensureReadable($request, $match);

        $this->loadMatchStateRelations($match, true);

        $comparison = $matchPreviewService->comparison($match);
        $preMatchReport = $matchPreviewService->preMatchReport($match, $leagueTableService, $comparison);

        $state = $this->statePayload(
            $request,
            $match,
            $leagueTableService,
            $matchPreviewService,
            $matchCenterStateService,
            $matchCenterPanelService,
        );

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
            'is_derby'          => $match->homeClub->isRival((int) $match->away_club_id),
            'comparison'        => $comparison,
            'pre_match_report'  => $preMatchReport,
            'module_panels'     => $state['module_panels'] ?? [],
        ]));
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

        return response()->json($this->statePayload(
            $request,
            $state,
            app(LeagueTableService::class),
            app(MatchPreviewService::class),
            app(MatchCenterStateService::class),
            app(MatchCenterPanelService::class),
        ));
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

        return response()->json($this->statePayload(
            $request,
            $state,
            app(LeagueTableService::class),
            app(MatchPreviewService::class),
            app(MatchCenterStateService::class),
            app(MatchCenterPanelService::class),
        ));
    }

    public function liveSetPieceStrategy(
        Request $request,
        GameMatch $match,
        LiveMatchTickerService $tickerService
    ): JsonResponse {
        $manageableClubIds = $this->manageableClubIds($request, $match);
        $clubId = (int) $request->input('club_id');
        abort_unless(in_array($clubId, $manageableClubIds, true), 403);

        $type = (string) $request->input('type', 'corner');
        $strategy = (string) $request->input('strategy', '');
        $state = $tickerService->setSetPieceStrategy($match, $clubId, $type, $strategy);

        return response()->json($this->statePayload(
            $request,
            $state,
            app(LeagueTableService::class),
            app(MatchPreviewService::class),
            app(MatchCenterStateService::class),
            app(MatchCenterPanelService::class),
        ));
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

        return response()->json($this->statePayload(
            $request,
            $state,
            app(LeagueTableService::class),
            app(MatchPreviewService::class),
            app(MatchCenterStateService::class),
            app(MatchCenterPanelService::class),
        ));
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

        return response()->json($this->statePayload(
            $request,
            $state,
            app(LeagueTableService::class),
            app(MatchPreviewService::class),
            app(MatchCenterStateService::class),
            app(MatchCenterPanelService::class),
        ));
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

        return response()->json($this->statePayload(
            $request,
            $state,
            app(LeagueTableService::class),
            app(MatchPreviewService::class),
            app(MatchCenterStateService::class),
            app(MatchCenterPanelService::class),
        ));
    }

    public function liveSyncLineup(
        Request $request,
        GameMatch $match,
        MatchLiveLineupService $matchLiveLineupService
    ): JsonResponse {
        $manageableClubIds = $this->manageableClubIds($request, $match);
        $validated = $request->validate([
            'club_id' => ['required', 'integer'],
            'formation' => ['required', 'string', 'max:20'],
            'mentality' => ['nullable', 'string', 'in:defensive,counter,normal,offensive,all_out'],
            'aggression' => ['nullable', 'string', 'in:cautious,normal,aggressive'],
            'line_height' => ['nullable', 'string', 'in:deep,normal,high,very_high'],
            'attack_focus' => ['nullable', 'string', 'in:center,left,right,both_wings'],
            'offside_trap' => ['nullable', 'boolean'],
            'time_wasting' => ['nullable', 'boolean'],
            'captain_player_id' => ['nullable', 'integer'],
            'penalty_taker_player_id' => ['nullable', 'integer'],
            'free_kick_near_player_id' => ['nullable', 'integer'],
            'free_kick_far_player_id' => ['nullable', 'integer'],
            'corner_left_taker_player_id' => ['nullable', 'integer'],
            'corner_right_taker_player_id' => ['nullable', 'integer'],
            'corner_marking_strategy' => ['nullable', 'string', 'in:zonal,player,hybrid'],
            'free_kick_marking_strategy' => ['nullable', 'string', 'in:zonal,player,hybrid'],
            'pressing_intensity' => ['nullable', 'string', 'in:low,normal,high,extreme'],
            'line_of_engagement' => ['nullable', 'string', 'in:deep,normal,high'],
            'pressing_trap' => ['nullable', 'string', 'in:none,inside,outside'],
            'cross_engagement' => ['nullable', 'string', 'in:none,stop,invite'],
            'starter_slots' => ['required', 'array'],
            'bench_slots' => ['nullable', 'array'],
            'player_instructions' => ['nullable', 'array'],
        ]);

        $clubId = (int) $validated['club_id'];
        abort_unless(in_array($clubId, $manageableClubIds, true), 403);

        $matchLiveLineupService->sync($match, $clubId, $validated);
        \Illuminate\Support\Facades\Cache::forget("match_lineups_payload_{$match->id}");
        $match->refresh();
        $this->loadMatchStateRelations($match, false);

        return response()->json($this->statePayload(
            $request,
            $match,
            app(LeagueTableService::class),
            app(MatchPreviewService::class),
            app(MatchCenterStateService::class),
            app(MatchCenterPanelService::class),
        ));
    }

    public function liveState(Request $request, GameMatch $match): JsonResponse
    {
        $this->ensureReadable($request, $match);

        $this->loadMatchStateRelations($match, false);

        return response()->json($this->statePayload(
            $request,
            $match,
            app(LeagueTableService::class),
            app(MatchPreviewService::class),
            app(MatchCenterStateService::class),
            app(MatchCenterPanelService::class),
        ));
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
        $isPlayed = $match->status === 'played';

        $relations = [
            'homeClub',
            'homeClub.stadium:id,name',
            'awayClub',
            'events' => fn ($q) => $q->select(['id', 'match_id', 'minute', 'second', 'event_type', 'club_id', 'player_id', 'assister_player_id', 'narrative', 'metadata'])
                ->orderByDesc('minute')->orderByDesc('second')->limit(400),
            'events.player:id,first_name,last_name,photo_path',
            'events.assister:id,first_name,last_name',
            'events.club',
            'playerStats:id,match_id,player_id,club_id,rating,goals,assists,minutes_played,shots',
            'playerStats.player:id,first_name,last_name,photo_path,position',
            'liveTeamStates:id,match_id,club_id,tactical_style,phase,possession_seconds,actions_count,dangerous_attacks,pass_attempts,pass_completions,tackle_attempts,tackle_won,fouls_committed,corners_won,shots,shots_on_target,expected_goals,yellow_cards,red_cards,substitutions_used,tactical_changes_count,last_tactical_change_minute,last_substitution_minute,current_ball_carrier_player_id,last_set_piece_taker_player_id,last_set_piece_type,last_set_piece_minute,corner_strategy,free_kick_strategy',
            'livePlayerStates:id,match_id,club_id,player_id,slot,is_on_pitch,is_sent_off,is_injured,fit_factor,minutes_played,ball_contacts,pass_attempts,pass_completions,tackle_attempts,tackle_won,fouls_committed,fouls_suffered,shots,shots_on_target,goals,assists,yellow_cards,red_cards,saves',
            'livePlayerStates.player:id,first_name,last_name,photo_path',
            // Constrained: load only the 400 most recent actions instead of the full table
            'liveActions' => fn ($q) => $q->select(['id', 'match_id', 'club_id', 'player_id', 'opponent_player_id', 'minute', 'second', 'sequence', 'action_type', 'outcome', 'narrative', 'x_coord', 'y_coord', 'metadata'])
                ->orderByDesc('minute')->orderByDesc('second')->orderByDesc('sequence')->limit(400)
                ->with([
                    'club:id,name,short_name,logo_path',
                    'player:id,first_name,last_name,photo_path',
                    'opponentPlayer:id,first_name,last_name,photo_path',
                ]),
            // Constrained: load only the 30 most recent snapshots
            'liveMinuteSnapshots' => fn ($q) => $q->select(['id', 'match_id', 'minute', 'home_score', 'away_score', 'home_phase', 'away_phase', 'home_tactical_style', 'away_tactical_style', 'pending_plans', 'executed_plans', 'skipped_plans', 'invalid_plans'])
                ->orderByDesc('minute')->limit(30),
        ];

        // plannedSubstitutions are irrelevant for finished matches
        if (!$isPlayed) {
            $relations['plannedSubstitutions'] = fn ($q) => $q->select(['id', 'match_id', 'club_id', 'player_out_id', 'player_in_id', 'planned_minute', 'score_condition', 'target_slot', 'status', 'executed_minute', 'metadata'])
                ->orderBy('planned_minute')
                ->with([
                    'playerOut:id,first_name,last_name',
                    'playerIn:id,first_name,last_name',
                ]);
        }

        $relations[] = 'competitionSeason.competition:id,name';

        $match->load($relations);
    }

    private function statePayload(
        Request $request,
        GameMatch $match,
        LeagueTableService $leagueTableService,
        MatchPreviewService $matchPreviewService,
        MatchCenterStateService $matchCenterStateService,
        MatchCenterPanelService $matchCenterPanelService
    ): array
    {
        // Lineups rarely change mid-match — cache for 30 s to avoid redundant queries on every live poll
        $lineups = \Illuminate\Support\Facades\Cache::remember(
            "match_lineups_payload_{$match->id}",
            30,
            fn () => $matchPreviewService->lineupsPayload($match),
        );

        $payload = $matchCenterStateService->build(
            $match,
            $leagueTableService,
            $lineups,
            $this->canSimulate($request, $match),
            $this->manageableClubIds($request, $match),
        );

        $payload['module_panels'] = $matchCenterPanelService->build($match, $payload);
        $payload['live_lineup_editor'] = $this->liveLineupEditorPayload(app(FormationPlannerService::class));

        return $payload;
    }

    private function liveLineupEditorPayload(FormationPlannerService $formationPlanner): array
    {
        $formations = $formationPlanner->supportedFormations();

        return [
            'formations' => $formations,
            'slots_by_formation' => collect($formations)
                ->mapWithKeys(fn (string $formation): array => [
                    $formation => $formationPlanner->starterSlots($formation),
                ])
                ->all(),
            'max_bench_players' => max(1, min(10, (int) config('simulation.lineup.max_bench_players', 5))),
        ];
    }

}
