<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Lineup;
use App\Services\FormationPlannerService;
use App\Services\LiveMatchTickerService;
use App\Services\MatchSimulationService;
use App\Services\PlayerPositionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchCenterController extends Controller
{
    public function show(Request $request, GameMatch $match): \Inertia\Response
    {
        $this->ensureReadable($request, $match);

        $this->loadMatchStateRelations($match, true);

        $getComparisonMetrics = function(\App\Models\Club $club) {
            $players = $club->players()->where('status', 'active')->get(['market_value', 'age', 'overall']);
            return [
                'market_value' => $players->sum('market_value') ?? 0,
                'avg_age' => round($players->avg('age') ?? 0, 1),
                'strength' => round($players->sortByDesc('overall')->take(14)->avg('overall') ?? 0, 1),
            ];
        };

        $comparison = [
            'home' => $getComparisonMetrics($match->homeClub),
            'away' => $getComparisonMetrics($match->awayClub),
        ];

        $state = $this->statePayload($request, $match);

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
            'kickoff_formatted' => $match->kickoff_at?->format('d.m.Y \u2022 H:i'),
            'weather'           => $match->weather,
            'referee'           => $match->referee,
            'type'              => $match->type,
            'comparison'        => $comparison,
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

        return response()->json($this->statePayload($request, $state));
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

        return response()->json($this->statePayload($request, $state));
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

        return response()->json($this->statePayload($request, $state));
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

        return response()->json($this->statePayload($request, $state));
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

        return response()->json($this->statePayload($request, $state));
    }

    public function liveState(Request $request, GameMatch $match): JsonResponse
    {
        $this->ensureReadable($request, $match);

        $this->loadMatchStateRelations($match, false);

        return response()->json($this->statePayload($request, $match));
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
            'homeClub:id,name,short_name,logo_path',
            'homeClub.stadium:id,name',
            'awayClub:id,name,short_name,logo_path',
            'events:id,match_id,minute,second,event_type,club_id,player_id,assister_id,narrative,metadata',
            'events.player:id,first_name,last_name,photo_path',
            'events.assister:id,first_name,last_name',
            'events.club:id,name,short_name,logo_path',
            'playerStats:id,match_id,player_id,club_id,rating,goals,assists,minutes_played,shots',
            'playerStats.player:id,first_name,last_name',
            'liveTeamStates:id,match_id,club_id,tactical_style,phase,possession_seconds,actions_count,dangerous_attacks,pass_attempts,pass_completions,tackle_attempts,tackle_won,fouls_committed,corners_won,shots,shots_on_target,expected_goals,yellow_cards,red_cards,substitutions_used,tactical_changes_count,last_tactical_change_minute,last_substitution_minute',
            'livePlayerStates:id,match_id,club_id,player_id,slot,is_on_pitch,is_sent_off,is_injured,fit_factor,minutes_played,ball_contacts,pass_attempts,pass_completions,tackle_attempts,tackle_won,fouls_committed,fouls_suffered,shots,shots_on_target,goals,assists,yellow_cards,red_cards,saves',
            'livePlayerStates.player:id,first_name,last_name,photo_path',
            'liveActions:id,match_id,club_id,player_id,opponent_player_id,minute,second,sequence,action_type,outcome,narrative,x_coord,y_coord,metadata',
            'liveActions.club:id,name,short_name,logo_path',
            'liveActions.player:id,first_name,last_name',
            'liveActions.opponentPlayer:id,first_name,last_name',
            'liveMinuteSnapshots:id,match_id,minute,home_score,away_score,home_phase,away_phase,home_tactical_style,away_tactical_style,pending_plans,executed_plans,skipped_plans,invalid_plans',
            'plannedSubstitutions:id,match_id,club_id,player_out_id,player_in_id,planned_minute,score_condition,target_slot,status,executed_minute,metadata',
            'plannedSubstitutions.playerOut:id,first_name,last_name',
            'plannedSubstitutions.playerIn:id,first_name,last_name',
        ];

        if ($withCompetition) {
            $relations[] = 'competitionSeason.competition:id,name';
        }

        $match->load($relations);
    }

    private function statePayload(Request $request, GameMatch $match): array
    {
        $statusLabel = match ($match->status) {
            'played' => 'Beendet',
            'live' => $match->live_paused ? 'Pausiert' : 'Live',
            default => ucfirst((string) $match->status),
        };

        return [
            'id' => $match->id,
            'status' => $match->status,
            'status_label' => $statusLabel,
            'live_minute' => (int) $match->live_minute,
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
                        'second' => (int) $item->second,
                        'sequence' => (int) ($item->sequence ?? 0),
                        'club_id' => $item->club_id !== null ? (int) $item->club_id : null,
                        'club_short_name' => $item->club?->short_name ?: $item->club?->name,
                        'player_id' => $item->player_id !== null ? (int) $item->player_id : null,
                        'player_name' => $item->player?->full_name,
                        'assister_name' => $assisterName,
                        'opponent_player_id' => $isAction && $item->opponent_player_id !== null ? (int) $item->opponent_player_id : null,
                        'opponent_player_name' => $isAction ? $item->opponentPlayer?->full_name : null,
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
    }

    private function lineupsPayload(GameMatch $match): array
    {
        $positionService = app(PlayerPositionService::class);
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
            $formation = '4-4-2';

            if (!$lineup) {
                // AUTO-FILL: Generate a virtual lineup if missing
                $club = ($clubId === (int) $match->home_club_id) ? $match->homeClub : $match->awayClub;
                $selection = app(FormationPlannerService::class)->strongestByFormation(
                    $club->players()->whereIn('status', ['active', 'transfer_listed'])->get(),
                    '4-4-2',
                    5
                );

                $formation = '4-4-2';
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
}
