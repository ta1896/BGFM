<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\MatchPlayerStat;
use App\Models\Player;
use App\Models\PlayerConversation;
use App\Models\PlayerPlaytimePromise;
use App\Modules\ModuleManager;
use App\Services\InjuryManagementService;
use App\Services\PlayerConversationService;
use App\Services\PlayerLoadService;
use App\Services\PlayerMoraleService;
use App\Services\SquadHierarchyService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\PlayerTransferHistory;
use App\Modules\DataCenter\Services\ScraperService;

class PlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Inertia\Response
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;
        $clubs = $request->user()->isAdmin() 
            ? \App\Models\Club::where('is_cpu', false)->orderBy('name')->get()
            : $request->user()->clubs()->orderBy('name')->get();

        if (!$activeClub && $clubs->isNotEmpty()) {
            $activeClub = $clubs->first();
        }

        $playerQuery = Player::query()
            ->with(['club', 'playtimePromises'])
            ->orderByRaw("FIELD(position, 'TW', 'LV', 'IV', 'RV', 'DM', 'LM', 'ZM', 'RM', 'OM', 'LF', 'HS', 'MS', 'RF')")
            ->orderByDesc('overall');

        if ($activeClub) {
            $playerQuery->where('club_id', $activeClub->id);
        } elseif (!$request->user()->isAdmin()) {
            $playerQuery->whereHas('club', fn($query) => $query->where('user_id', $request->user()->id));
        }

        $players = $playerQuery->get()->map(function($p) {
            $p->append('photo_url');
            $p->market_value_formatted = number_format($p->market_value, 0, ',', '.') . ' €';
            $p->display_position = $p->position; // Or use a translation map
            $activePromise = $p->playtimePromises
                ->sortByDesc('id')
                ->first(fn ($promise) => in_array($promise->status, ['active', 'at_risk', 'broken', 'fulfilled'], true));
            $p->role_override_active = (bool) $p->role_override_active;
            $p->promise_status = $activePromise?->status;
            return $p;
        });

        $squadStats = [
            'count' => $players->count(),
            'avg_age' => $players->isNotEmpty() ? round($players->avg('age'), 1) : 0,
            'avg_rating' => $players->isNotEmpty() ? round($players->avg('overall'), 1) : 0,
            'total_value' => $players->sum('market_value'),
            'total_value_formatted' => number_format($players->sum('market_value'), 0, ',', '.') . ' €',
            'avg_value' => $players->isNotEmpty() ? $players->avg('market_value') : 0,
            'avg_value_formatted' => number_format($players->isNotEmpty() ? $players->avg('market_value') : 0, 0, ',', '.') . ' €',
            'injured_count' => $players->where('is_injured', true)->count(),
            'suspended_count' => $players->where('is_suspended', true)->count(),
        ];

        $groupedPlayers = $players->groupBy(fn($player) => match (true) {
            in_array($player->position, ['GK', 'TW']) => 'Torhüter',
            in_array($player->position, ['LB', 'CB', 'RB', 'LWB', 'RWB', 'LV', 'IV', 'RV']) => 'Abwehr',
            in_array($player->position, ['CDM', 'CM', 'CAM', 'LM', 'RM', 'DM', 'ZM', 'OM']) => 'Mittelfeld',
            default => 'Sturm',
        })->sortBy(fn($group, $key) => match ($key) {
                'Torhüter' => 1,
                'Abwehr' => 2,
                'Mittelfeld' => 3,
                'Sturm' => 4,
                default => 99,
            });

        return \Inertia\Inertia::render('Players/Index', [
            'groupedPlayers' => $groupedPlayers,
            'squadStats' => $squadStats,
            'clubs' => $request->user()->clubs()->orderBy('name')->get(),
            'activeClubId' => $activeClub?->id,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): \Inertia\Response
    {
        return \Inertia\Inertia::render('Players/Form', [
            'clubs' => $request->user()->isAdmin()
                ? \App\Models\Club::where('is_cpu', false)->orderBy('name')->get()
                : $request->user()->clubs()->orderBy('name')->get(),
            'positions' => $this->positions(),
            'player' => null,
        ]);
    }

    public function hierarchy(
        Request $request,
        SquadHierarchyService $squadHierarchyService,
        PlayerMoraleService $playerMoraleService,
        PlayerLoadService $playerLoadService,
        InjuryManagementService $injuryManagementService,
    ): \Inertia\Response {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;
        $clubs = $request->user()->isAdmin()
            ? Club::query()->where('is_cpu', false)->orderBy('name')->get()
            : $request->user()->clubs()->orderBy('name')->get();

        if (!$activeClub && $clubs->isNotEmpty()) {
            $activeClub = $clubs->first();
        }

        $levels = $this->hierarchyLevels();
        $summary = [
            'satisfied_count' => 0,
            'unsettled_count' => 0,
            'fair_role_count' => 0,
            'critical_role_count' => 0,
        ];
        $allPlayers = collect();

        if ($activeClub) {
            $activeClub->loadMissing(['players.playtimePromises', 'players.injuries']);
            $squadHierarchyService->refreshForClub($activeClub);

            $activeClub->loadMissing(['players.playtimePromises', 'players.injuries']);

            $minuteShares = MatchPlayerStat::query()
                ->whereIn('player_id', $activeClub->players->pluck('id'))
                ->latest('id')
                ->get(['player_id', 'minutes_played'])
                ->groupBy('player_id')
                ->map(fn ($stats) => max(
                    0,
                    min(100, (int) round((((float) $stats->take(8)->avg('minutes_played')) / 90) * 100))
                ));

            foreach ($activeClub->players->sortByDesc('overall')->values() as $player) {
                $injuryManagementService->syncCurrentInjury($player);
                $playerMoraleService->refresh($player->loadMissing(['playtimePromises', 'injuries']));

                $mappedPlayer = $this->mapHierarchyPlayer(
                    $player->fresh()->loadMissing(['playtimePromises', 'injuries']),
                    $playerLoadService,
                    (int) ($minuteShares[$player->id] ?? 0)
                );

                $levels[$mappedPlayer['pyramid_level']]['players'][] = $mappedPlayer;

                if ($mappedPlayer['mood']['status'] === 'happy') {
                    $summary['satisfied_count']++;
                }

                if ($mappedPlayer['mood']['status'] === 'unsettled') {
                    $summary['unsettled_count']++;
                }

                if ($mappedPlayer['role_fit']['status'] === 'aligned') {
                    $summary['fair_role_count']++;
                }

                if ($mappedPlayer['role_fit']['status'] === 'critical') {
                    $summary['critical_role_count']++;
                }

                $allPlayers->push($mappedPlayer);
            }
        }

        return \Inertia\Inertia::render('Players/Hierarchy', [
            'clubs' => $clubs->map(fn ($club) => [
                'id' => $club->id,
                'name' => $club->name,
                'logo_url' => $club->logo_url,
            ])->values()->all(),
            'activeClub' => $activeClub ? [
                'id' => $activeClub->id,
                'name' => $activeClub->name,
                'logo_url' => $activeClub->logo_url,
            ] : null,
            'hierarchyLevels' => array_values($levels),
            'summary' => $summary,
            'hierarchyInsights' => [
                'captain_group' => $allPlayers
                    ->where('leadership_level', 'captain_group')
                    ->values()
                    ->take(4)
                    ->all(),
                'unsettled_players' => $allPlayers
                    ->where('mood.status', 'unsettled')
                    ->sortBy('happiness')
                    ->values()
                    ->take(5)
                    ->all(),
                'role_conflicts' => $allPlayers
                    ->filter(fn ($player) => in_array($player['role_fit']['status'], ['watching', 'critical'], true))
                    ->sortBy([
                        ['role_fit.status', 'desc'],
                        ['happiness', 'asc'],
                    ])
                    ->values()
                    ->take(5)
                    ->all(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'position' => ['required', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
            'age' => ['required', 'integer', 'min:15', 'max:45'],
            'overall' => ['required', 'integer', 'min:1', 'max:99'],
            'pace' => ['required', 'integer', 'min:1', 'max:99'],
            'shooting' => ['required', 'integer', 'min:1', 'max:99'],
            'passing' => ['required', 'integer', 'min:1', 'max:99'],
            'technical' => ['required', 'integer', 'min:1', 'max:99'],
            'defending' => ['required', 'integer', 'min:1', 'max:99'],
            'physical' => ['required', 'integer', 'min:1', 'max:99'],
            'stamina' => ['required', 'integer', 'min:1', 'max:100'],
            'morale' => ['required', 'integer', 'min:1', 'max:100'],
            'market_value' => ['required', 'numeric', 'min:0'],
            'salary' => ['required', 'numeric', 'min:0'],
        ]);

        $club = $this->ownedClub($request, (int) $validated['club_id']);
        $validated = $this->handlePhotoUpload($request, $validated);

        $club->players()->create($validated);

        return redirect()
            ->route('players.index', ['club' => $club->id])
            ->with('status', 'Spieler wurde hinzugefuegt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(
        Request $request,
        Player $player,
        SquadHierarchyService $squadHierarchyService,
        PlayerMoraleService $playerMoraleService,
        PlayerLoadService $playerLoadService,
        InjuryManagementService $injuryManagementService,
        ModuleManager $modules,
    ): \Inertia\Response
    {
        $conversationsEnabled = (bool) config('simulation.features.player_conversations_enabled', false);
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;

        $squadHierarchyService->refreshForClub($player->club);

        $player->load([
            'club',
            'seasonCompetitionStatistics.season:id,name,start_date',
            'playtimePromises',
            'injuries',
            'transferHistories.leftClub',
            'transferHistories.joinedClub',
            'recoveryLogs' => fn ($query) => $query->latest('day')->limit(7),
            'conversations' => fn ($query) => $query->latest('id')->limit(8),
        ]);

        $injury = $injuryManagementService->syncCurrentInjury($player);
        $morale = $playerMoraleService->refresh($player);

        
        // Add formatted value for easy display
        $player->market_value_formatted = number_format($player->market_value, 0, ',', '.') . ' €';

        $currentSeasonStats = $player->seasonCompetitionStatistics
            ->sortByDesc(fn($stat) => $stat->season?->start_date ?? '')
            ->take(1)
            ->values()
            ->map(fn($stat) => $this->mapSeasonStat($stat))
            ->all();

        $managerDecisions = collect();

        if ($player->role_override_active && $player->role_override_set_at) {
            $managerDecisions->push([
                'kind' => 'role_override',
                'title' => 'Rolle manuell gesetzt',
                'accent' => 'fuchsia',
                'impact_label' => $this->roleLabel($player->squad_role),
                'summary' => 'Manager hat die automatische Kaderrolle bewusst ueberschrieben.',
                'created_at' => $player->role_override_set_at?->format('d.m.Y H:i'),
                'sort_at' => $player->role_override_set_at?->toIso8601String(),
                'evaluation' => $this->decisionEvaluation($player, 'role_override'),
            ]);
        }

        foreach ($player->playtimePromises as $promise) {
            $managerDecisions->push([
                'kind' => 'promise',
                'title' => 'Spielzeitversprechen',
                'accent' => match ($promise->status) {
                    'broken' => 'rose',
                    'at_risk' => 'amber',
                    'fulfilled' => 'emerald',
                    default => 'cyan',
                },
                'impact_label' => strtoupper((string) $promise->status),
                'summary' => sprintf(
                    '%d%% Einsatzzeit zugesagt, aktuell %d%% erfuellt.',
                    (int) $promise->expected_minutes_share,
                    (int) $promise->fulfilled_ratio
                ),
                'created_at' => $promise->created_at?->format('d.m.Y H:i'),
                'sort_at' => $promise->created_at?->toIso8601String(),
                'evaluation' => $this->decisionEvaluation($player, 'promise', $promise->status),
            ]);
        }

        if ($conversationsEnabled) {
            foreach ($player->conversations as $conversation) {
                $managerDecisions->push([
                    'kind' => 'conversation',
                    'title' => 'Gespraech: ' . $this->conversationTopicLabel($conversation->topic),
                    'accent' => match ($conversation->outcome) {
                        'breakthrough' => 'emerald',
                        'positive' => 'cyan',
                        'steady' => 'slate',
                        default => 'rose',
                    },
                    'impact_label' => strtoupper((string) $conversation->outcome),
                    'summary' => $conversation->summary,
                    'created_at' => $conversation->created_at?->format('d.m.Y H:i'),
                    'sort_at' => $conversation->created_at?->toIso8601String(),
                    'evaluation' => $this->decisionEvaluation($player, 'conversation'),
                ]);
            }
        }

        $managerDecisions = $managerDecisions
            ->filter(fn ($entry) => !empty($entry['sort_at']))
            ->sortByDesc('sort_at')
            ->values()
            ->map(function (array $entry) {
                unset($entry['sort_at']);

                return $entry;
            })
            ->all();

        // Use season stats for career history, sorted by season desc
        $careerStats = $player->seasonCompetitionStatistics
            ->sortByDesc(fn($stat) => $stat->season?->start_date ?? '')
            ->values()
            ->map(fn($stat) => $this->mapSeasonStat($stat))
            ->all();

        // Fetch recent matches
        $recentMatches = \App\Models\MatchPlayerStat::query()
            ->where('player_id', $player->id)
            ->with(['match.homeClub', 'match.awayClub', 'match.competitionSeason.competition'])
            ->whereHas('match', fn($query) => $query->where('status', 'played'))
            ->orderByDesc(
                \App\Models\GameMatch::select('kickoff_at')
                    ->whereColumn('matches.id', 'match_player_stats.match_id')
            )
            ->take(10)
            ->get()
            ->map(function ($stat) use ($player) {
                return [
                    'minutes_played' => (int) $stat->minutes_played,
                    'goals' => (int) $stat->goals,
                    'assists' => (int) $stat->assists,
                    'yellow_cards' => (int) $stat->yellow_cards,
                    'red_cards' => (int) $stat->red_cards,
                    'rating' => (float) $stat->rating,
                    'result' => $this->calculateMatchResult($stat->match, $player->club_id),
                    'match' => $stat->match ? [
                        'home_club_id' => (int) $stat->match->home_club_id,
                        'away_club_id' => (int) $stat->match->away_club_id,
                        'home_score' => $stat->match->home_score,
                        'away_score' => $stat->match->away_score,
                        'kickoff_date_formatted' => $stat->match->kickoff_at?->format('d.m.y'),
                        'competition_season' => [
                            'competition' => [
                                'code' => $stat->match->competitionSeason?->competition?->code,
                                'logo_url' => $stat->match->competitionSeason?->competition?->logo_url,
                            ],
                        ],
                        'home_club' => $stat->match->homeClub ? [
                            'short_name' => $stat->match->homeClub->short_name,
                            'logo_url' => $stat->match->homeClub->logo_url,
                        ] : null,
                        'away_club' => $stat->match->awayClub ? [
                            'short_name' => $stat->match->awayClub->short_name,
                            'logo_url' => $stat->match->awayClub->logo_url,
                        ] : null,
                    ] : null,
                ];
            })
            ->values()
            ->all();

        return \Inertia\Inertia::render('Players/Show', [
            'player' => [
                'id' => $player->id,
                'club_id' => $player->club_id,
                'first_name' => $player->first_name,
                'last_name' => $player->last_name,
                'full_name' => $player->full_name,
                'photo_url' => $player->photo_url,
                'position' => $player->position,
                'position_second' => $player->position_second,
                'position_third' => $player->position_third,
                'position_long' => $player->position_long,
                'nationality' => $player->nationality,
                'nationality_code' => $player->nationality_code,
                'birthday' => $player->birthday?->format('d.m.Y'),
                'height' => $player->height,
                'shirt_number' => $player->shirt_number,
                'preferred_foot' => $player->preferred_foot,
                'age' => $player->age,
                'overall' => $player->overall,
                'potential' => $player->potential,
                'pace' => $player->pace,
                'shooting' => $player->shooting,
                'passing' => $player->passing,
                'technical' => $player->technical,
                'dribbling' => $player->dribbling,
                'defending' => $player->defending,
                'physical' => $player->physical,
                'stamina' => $player->stamina,
                'morale' => $player->morale,
                'attr_attacking' => $player->attr_attacking,
                'attr_technical' => $player->attr_technical,
                'attr_tactical' => $player->attr_tactical,
                'attr_defending' => $player->attr_defending,
                'attr_creativity' => $player->attr_creativity,
                'attr_market' => $player->attr_market,
                'salary' => $player->salary,
                'market_value' => $player->market_value,
                'market_value_formatted' => number_format($player->market_value, 0, ',', '.') . ' EUR',
                'club' => $player->club ? [
                    'id' => $player->club->id,
                    'name' => $player->club->name,
                    'logo_url' => $player->club->logo_url,
                ] : null,
                'squad_role' => $player->squad_role,
                'leadership_level' => $player->leadership_level,
                'team_status' => $player->team_status,
                'expected_playtime' => (int) $player->expected_playtime,
                'happiness' => (int) $player->happiness,
                'happiness_trend' => (int) $player->happiness_trend,
                'fatigue' => (int) $player->fatigue,
                'sharpness' => (int) $player->sharpness,
                'training_load' => (int) $player->training_load,
                'match_load' => (int) $player->match_load,
                'medical_status' => $player->medical_status,
                'last_morale_reason' => $player->last_morale_reason,
                'injury_risk' => $playerLoadService->injuryRisk($player),
                'promise_pressure' => $morale['promise_pressure'],
                'injury' => $injury ? [
                    'type' => $injury->injury_type,
                    'severity' => $injury->severity,
                    'expected_return' => $injury->expected_return_at?->format('d.m.Y'),
                ] : null,
                'tm_profile_url' => $player->tm_profile_url,
                'sofa_profile_url' => $player->sofa_profile_url,
                'transfer_history' => $player->transferHistories->map(fn($history) => [
                    'id' => $history->id,
                    'season' => $history->season,
                    'transfer_date' => $history->transfer_date?->format('d.m.Y'),
                    'left_club_name' => $history->left_club_name,
                    'left_club_id' => $history->left_club_id,
                    'left_club_logo' => $history->leftClub?->logo_url,
                    'joined_club_name' => $history->joined_club_name,
                    'joined_club_id' => $history->joined_club_id,
                    'joined_club_logo' => $history->joinedClub?->logo_url,
                    'market_value' => $history->market_value,
                    'fee' => $history->fee,
                    'is_loan' => $history->is_loan,
                ]),
            ],
            'currentSeasonStats' => $currentSeasonStats,
            'careerStats' => $careerStats,
            'recentMatches' => $recentMatches,
            'isOwner' => $player->club && $request->user()->id === $player->club->user_id,
            'positions' => array_keys($this->positions()),
            'modulePlayerActions' => $this->modulePlayerActionsPayload($modules, $player, $activeClub, $request->user()->isAdmin()),
            'squadDynamics' => [
                'promises' => $player->playtimePromises->map(fn ($promise) => [
                    'promise_type' => $promise->promise_type,
                    'expected_minutes_share' => (int) $promise->expected_minutes_share,
                    'fulfilled_ratio' => (int) $promise->fulfilled_ratio,
                    'status' => $promise->status,
                    'deadline_at' => $promise->deadline_at?->format('d.m.Y'),
                ])->values()->all(),
                'recovery' => $player->recoveryLogs->map(fn ($log) => [
                    'day' => $log->day?->format('d.m'),
                    'fatigue_after' => (int) $log->fatigue_after,
                    'sharpness_after' => (int) $log->sharpness_after,
                    'injury_risk' => (int) $log->injury_risk,
                ])->values()->all(),
                'conversations' => $conversationsEnabled ? $player->conversations->map(fn (PlayerConversation $conversation) => [
                    'topic' => $conversation->topic,
                    'topic_label' => $this->conversationTopicLabel($conversation->topic),
                    'approach' => $conversation->approach,
                    'approach_label' => $this->conversationApproachLabel($conversation->approach),
                    'outcome' => $conversation->outcome,
                    'happiness_delta' => (int) $conversation->happiness_delta,
                    'happiness_after' => (int) $conversation->happiness_after,
                    'manager_message' => $conversation->manager_message,
                    'player_response' => $conversation->player_response,
                    'summary' => $conversation->summary,
                    'created_at' => $conversation->created_at?->format('d.m.Y H:i'),
                ])->values()->all() : [],
                'manager_decisions' => $managerDecisions,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Player $player): \Inertia\Response
    {
        $this->ensureOwnership($request, $player);

        return \Inertia\Inertia::render('Players/Form', [
            'player' => $player->append('photo_url'),
            'clubs' => $request->user()->isAdmin()
                ? \App\Models\Club::where('is_cpu', false)->orderBy('name')->get()
                : $request->user()->clubs()->orderBy('name')->get(),
            'positions' => $this->positions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Player $player): RedirectResponse
    {
        $this->ensureOwnership($request, $player);

        // Validation relaxed for "Customize" tab usage, but keeping strict for full edit if needed.
        // We check if it's a full update or just a customize update based on presence of fields.

        $rules = [
            'market_value' => ['nullable', 'numeric', 'min:0'],
            'position' => ['nullable', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
            'position_second' => ['nullable', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
            'position_third' => ['nullable', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'photo_url' => ['nullable', 'url', 'max:255'],
        ];

        // If it's a standard update (from ACP or edit form), we might expect other fields.
        // But for now, we merge valid data.

        $validated = $request->validate($rules);

        // Handle Photo
        // Priority: Upload > URL > Existing
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/player-photos');
            $validated['photo_path'] = $path;
            if ($player->photo_path) {
                Storage::delete($player->photo_path);
            }
        } elseif (!empty($validated['photo_url'])) {
            // If URL is provided and no file uploaded
            $validated['photo_path'] = $validated['photo_url'];
            // If previous was a file, strictly speaking we should delete it if we overwrite with URL, 
            // but maybe we keep it? Let's delete to be clean if it was a storage path.
            if ($player->photo_path && !str_starts_with($player->photo_path, 'http')) {
                Storage::delete($player->photo_path);
            }
        }

        // Filter nulls to avoid overwriting with null if partial update
        $dataToUpdate = array_filter($validated, fn($value) => !is_null($value));

        // Handle position mapping if coming from customize form
        if (isset($validated['position']))
            $dataToUpdate['position'] = $validated['position'];
        if (isset($validated['position_second']))
            $dataToUpdate['position_second'] = $validated['position_second'];
        if (isset($validated['position_third']))
            $dataToUpdate['position_third'] = $validated['position_third'];


        // Special case: standard update fields might need to be preserved if missing? 
        // The original update required EVERYTHING. 
        // If we want to support the "Customize" form AND the "Edit" form, we need to be careful.
        // The "Edit" form sends all fields. The "Customize" form sends specific fields.

        // If 'first_name' is missing, it's likely a partial update from "Customize".
        if (!$request->has('first_name')) {
            $player->update($dataToUpdate);
        } else {
            // Full update logic (legacy/ACP)
            $fullRules = [
                'club_id' => ['required', 'integer', 'exists:clubs,id'],
                'first_name' => ['required', 'string', 'max:80'],
                'last_name' => ['required', 'string', 'max:80'],
                'position' => ['required', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
                'position_second' => ['nullable', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
                'position_third' => ['nullable', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
                'age' => ['required', 'integer', 'min:15', 'max:45'],
                'birthday' => ['nullable', 'date'],
                'height' => ['nullable', 'integer'],
                'shirt_number' => ['nullable', 'integer'],
                'preferred_foot' => ['nullable', 'string'],
                'overall' => ['required', 'integer', 'min:1', 'max:99'],
                'pace' => ['required', 'integer', 'min:1', 'max:99'],
                'shooting' => ['required', 'integer', 'min:1', 'max:99'],
                'passing' => ['required', 'integer', 'min:1', 'max:99'],
                'technical' => ['required', 'integer', 'min:1', 'max:99'],
                'defending' => ['required', 'integer', 'min:1', 'max:99'],
                'physical' => ['required', 'integer', 'min:1', 'max:99'],
                'stamina' => ['required', 'integer', 'min:1', 'max:100'],
                'morale' => ['required', 'integer', 'min:1', 'max:100'],
                'market_value' => ['required', 'numeric', 'min:0'],
                'salary' => ['required', 'numeric', 'min:0'],
                'transfermarkt_id' => ['nullable', 'string'],
                'sofascore_id' => ['nullable', 'string'],
                'sofascore_url' => ['nullable', 'string'],
                'attr_attacking' => ['nullable', 'integer'],
                'attr_technical' => ['nullable', 'integer'],
                'attr_tactical' => ['nullable', 'integer'],
                'attr_defending' => ['nullable', 'integer'],
                'attr_creativity' => ['nullable', 'integer'],
                'attr_market' => ['nullable', 'integer'],
                'player_style' => ['nullable', 'string'],
                'is_imported' => ['nullable', 'boolean'],
                'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            ];
            $fullValidated = $request->validate($fullRules);

            // Handle photo for full update
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/player-photos');
                $fullValidated['photo_path'] = $path;
                if ($player->photo_path)
                    Storage::delete($player->photo_path);
            }

            $club = $this->ownedClub($request, (int) $fullValidated['club_id']);
            $player->update(array_merge($fullValidated, ['club_id' => $club->id]));
        }

        return redirect()
            ->route('players.show', $player)
            ->with('status', 'Spieler wurde aktualisiert.');
    }

    public function storePlaytimePromise(
        Request $request,
        Player $player,
        PlayerMoraleService $playerMoraleService,
    ): RedirectResponse {
        $this->ensureOwnership($request, $player);

        $validated = $request->validate([
            'promise_type' => ['required', 'in:starter,regular_rotation,impact_sub,youth_path'],
            'expected_minutes_share' => ['required', 'integer', 'min:5', 'max:100'],
            'deadline_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $player->playtimePromises()
            ->whereIn('status', ['active', 'at_risk'])
            ->update(['status' => 'replaced']);

        PlayerPlaytimePromise::create([
            'player_id' => $player->id,
            'club_id' => $player->club_id,
            'promise_type' => $validated['promise_type'],
            'expected_minutes_share' => (int) $validated['expected_minutes_share'],
            'deadline_at' => !empty($validated['deadline_at']) ? Carbon::parse($validated['deadline_at']) : now()->addWeeks(6),
            'status' => 'active',
            'fulfilled_ratio' => $this->resolveFulfilledRatio($player),
            'notes' => $validated['notes'] ?? null,
        ]);

        $playerMoraleService->refresh($player->fresh()->loadMissing(['playtimePromises', 'injuries']));

        return redirect()
            ->route('players.show', $player)
            ->with('status', 'Spielzeitversprechen wurde gespeichert.');
    }

    public function storeConversation(
        Request $request,
        Player $player,
        PlayerConversationService $conversationService,
    ): RedirectResponse {
        $this->ensureOwnership($request, $player);

        if (!(bool) config('simulation.features.player_conversations_enabled', false)) {
            return back()->withErrors([
                'conversation' => 'Spielergespraeche sind aktuell in den Game-Einstellungen deaktiviert.',
            ]);
        }

        $validated = $request->validate([
            'topic' => ['required', 'in:role,playtime,load,morale'],
            'approach' => ['required', 'in:supportive,honest,demanding,protective'],
            'manager_message' => ['nullable', 'string', 'max:500'],
        ]);

        $conversationService->logConversation($player->fresh()->loadMissing(['club', 'playtimePromises', 'injuries']), (int) $request->user()->id, $validated);

        return back()->with('status', 'Spielergespraech wurde protokolliert.');
    }

    public function updateHierarchyRole(
        Request $request,
        Player $player,
        SquadHierarchyService $squadHierarchyService,
        PlayerMoraleService $playerMoraleService,
    ): RedirectResponse {
        $this->ensureOwnership($request, $player);

        $validated = $request->validate([
            'squad_role' => ['required', 'in:auto,star_player,important_first_team,rotation,prospect,backup,surplus'],
        ]);

        $role = $validated['squad_role'];

        if ($role === 'auto') {
            $player->forceFill([
                'role_override_active' => false,
                'role_override_set_at' => null,
            ])->save();

            $club = $player->club()->firstOrFail();
            $squadHierarchyService->refreshForClub($club);
            $player = $player->fresh();
        } else {
            $player->forceFill([
                'squad_role' => $role,
                'team_status' => $squadHierarchyService->teamStatusForRole($role),
                'expected_playtime' => $squadHierarchyService->expectedPlaytimeForRole($role),
                'role_override_active' => true,
                'role_override_set_at' => now(),
            ])->save();
        }

        $playerMoraleService->refresh($player->fresh()->loadMissing(['playtimePromises', 'injuries']));

        return back()->with('status', $role === 'auto' ? 'Rolle wurde auf Automatik zurueckgesetzt.' : 'Kaderrolle wurde angepasst.');
    }

    public function storeQuickPromise(
        Request $request,
        Player $player,
        PlayerMoraleService $playerMoraleService,
    ): RedirectResponse {
        $this->ensureOwnership($request, $player);

        $validated = $request->validate([
            'template' => ['required', 'in:starter,rotation,development'],
        ]);

        [$promiseType, $expectedMinutesShare, $weeks] = match ($validated['template']) {
            'starter' => ['starter', 78, 8],
            'rotation' => ['regular_rotation', 52, 6],
            default => ['youth_path', 30, 10],
        };

        $player->playtimePromises()
            ->whereIn('status', ['active', 'at_risk'])
            ->update(['status' => 'replaced']);

        PlayerPlaytimePromise::create([
            'player_id' => $player->id,
            'club_id' => $player->club_id,
            'promise_type' => $promiseType,
            'expected_minutes_share' => $expectedMinutesShare,
            'deadline_at' => now()->addWeeks($weeks),
            'status' => 'active',
            'fulfilled_ratio' => $this->resolveFulfilledRatio($player),
            'notes' => 'Schnellversprechen aus der Hierarchieansicht.',
        ]);

        $playerMoraleService->refresh($player->fresh()->loadMissing(['playtimePromises', 'injuries']));

        return back()->with('status', 'Spielzeitversprechen wurde direkt gesetzt.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Player $player): RedirectResponse
    {
        $this->ensureOwnership($request, $player);

        $clubId = $player->club_id;

        if ($player->photo_path) {
            Storage::delete($player->photo_path);
        }

        $player->delete();

        return redirect()
            ->route('players.index', ['club' => $clubId])
            ->with('status', 'Spieler wurde geloescht.');
    }

    protected function calculateMatchResult($match, $clubId)
    {
        if (!$match) return 'D';
        if ($match->home_score === null || $match->away_score === null) return 'D';

        $isHome = (int) $match->home_club_id === (int) $clubId;
        $homeScore = (int) $match->home_score;
        $awayScore = (int) $match->away_score;

        if ($homeScore === $awayScore) return 'D';

        if ($isHome) {
            return $homeScore > $awayScore ? 'W' : 'L';
        } else {
            return $awayScore > $homeScore ? 'W' : 'L';
        }
    }

    public function syncTransferHistory(Player $player, ScraperService $scraper): RedirectResponse
    {
        return $this->transfer_history($player, $scraper);
    }

    public function transfer_history(Player $player, ScraperService $scraper): RedirectResponse
    {
        $this->ensureOwnership(request(), $player);

        if (!$player->tm_profile_url) {
            return back()->withErrors(['tm_url' => 'Keine Transfermarkt-URL hinterlegt.']);
        }

        $historyData = $scraper->getPlayerTransferHistory($player->tm_profile_url);

        if (empty($historyData)) {
            return back()->with('status', 'Keine Transferhistorie gefunden oder Fehler beim Scraper.');
        }

        foreach ($historyData as $data) {
            // Find existing clubs
            $leftClubId = null;
            if (isset($data['left_club_tm_id'])) {
                $leftClubId = Club::where('transfermarkt_id', $data['left_club_tm_id'])->value('id');
            }
            if (!$leftClubId && !empty($data['left_club_name'])) {
                $leftClubId = Club::where('name', $data['left_club_name'])->value('id');
            }

            $joinedClubId = null;
            if (isset($data['joined_club_tm_id'])) {
                $joinedClubId = Club::where('transfermarkt_id', $data['joined_club_tm_id'])->value('id');
            }
            if (!$joinedClubId && !empty($data['joined_club_name'])) {
                $joinedClubId = Club::where('name', $data['joined_club_name'])->value('id');
            }

            $player->transferHistories()->updateOrCreate(
                [
                    'season' => $data['season'],
                    'transfer_date' => Carbon::parse($data['transfer_date']),
                    'left_club_name' => $data['left_club_name'] ?? 'Unbekannt',
                    'joined_club_name' => $data['joined_club_name'] ?? 'Unbekannt',
                ],
                [
                    'left_club_tm_id' => $data['left_club_tm_id'] ?? null,
                    'left_club_id' => $leftClubId,
                    'joined_club_tm_id' => $data['joined_club_tm_id'] ?? null,
                    'joined_club_id' => $joinedClubId,
                    'market_value' => $this->parseValue($data['market_value'] ?? null),
                    'fee' => $data['fee'] ?? '?',
                    'is_loan' => $data['is_loan'] ?? false,
                ]
            );
        }

        return back()->with('status', 'Transferhistorie wurde erfolgreich synchronisiert.');
    }

    public function syncSofascore(Player $player): RedirectResponse
    {
        $this->ensureOwnership(request(), $player);

        if (!$player->sofascore_id) {
            return back()->with('error', 'Keine Sofascore-ID beim Spieler hinterlegt.');
        }

        try {
            \App\Jobs\SyncPlayerSofascoreJob::dispatchSync($player);
            return back()->with('status', 'Sofascore-Daten wurden erfolgreich synchronisiert.');
        } catch (\Exception $e) {
            return back()->with('error', 'Fehler beim Sofascore-Sync: ' . $e->getMessage());
        }
    }

    private function parseValue(?string $value): ?int
    {
        if (!$value || $value === '?' || $value === '-') return null;
        
        $value = str_replace(['.', ','], ['', '.'], $value);
        $factor = 1;
        
        if (Str::contains($value, 'Mio')) {
            $factor = 1000000;
        } elseif (Str::contains($value, 'Tsd')) {
            $factor = 1000;
        }
        
        $amount = (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        return (int) ($amount * $factor);
    }

    private function ensureOwnership(Request $request, Player $player): void
    {
        abort_unless($player->club()->where('user_id', $request->user()->id)->exists(), 403);
    }

    private function ownedClub(Request $request, int $clubId): Club
    {
        $club = $request->user()->clubs()->whereKey($clubId)->first();
        abort_unless($club, 403);

        return $club;
    }

    private function positions(): array
    {
        return [
            'TW' => 'Torwart',
            'IV' => 'Innenverteidiger',
            'LV' => 'Linksverteidiger',
            'RV' => 'Rechtsverteidiger',
            'ZM' => 'Zentrales Mittelfeld',
            'DM' => 'Defensives Mittelfeld',
            'OM' => 'Offensives Mittelfeld',
            'LM' => 'Linkes Mittelfeld',
            'RM' => 'Rechtes Mittelfeld',
            'LF' => 'Linker Fluegel',
            'MS' => 'Mittelstuermer',
            'HS' => 'Haengende Spitze',
            'RF' => 'Rechter Fluegel',
        ];
    }

    private function modulePlayerActionsPayload(ModuleManager $modules, Player $player, ?Club $activeClub, bool $isAdmin): array
    {
        return collect($modules->frontendRegistry()['player_actions'] ?? [])
            ->filter(fn ($action) => is_array($action) && is_string($action['route'] ?? null))
            ->filter(function (array $action) use ($player, $activeClub, $isAdmin): bool {
                if (!Route::has((string) $action['route'])) {
                    return false;
                }

                $scope = (string) ($action['scope'] ?? 'all');
                $ownsPlayer = $isAdmin || ($activeClub && (int) $activeClub->id === (int) $player->club_id);

                return match ($scope) {
                    'owned_only' => $ownsPlayer,
                    'external_only' => !$ownsPlayer,
                    default => true,
                };
            })
            ->map(function (array $action) use ($player): array {
                $payload = collect((array) ($action['payload'] ?? []))
                    ->map(fn ($value) => $this->replacePlayerTokens($value, $player))
                    ->all();

                $query = collect((array) ($action['query'] ?? []))
                    ->map(fn ($value) => $this->replacePlayerTokens($value, $player))
                    ->all();

                $routeParameters = Route::getRoutes()
                    ->getByName((string) $action['route'])
                    ?->parameterNames() ?? [];

                $parameters = $routeParameters !== []
                    ? [(string) $player->getRouteKey()]
                    : [];

                if ($query !== []) {
                    $parameters = array_merge($parameters, $query);
                }

                return [
                    'key' => (string) ($action['key'] ?? Str::slug((string) $action['title'])),
                    'title' => (string) ($action['title'] ?? 'Module Action'),
                    'description' => (string) ($action['description'] ?? ''),
                    'method' => strtolower((string) ($action['method'] ?? 'get')),
                    'href' => route((string) $action['route'], $parameters),
                    'payload' => $payload,
                    'accent' => (string) ($action['accent'] ?? 'slate'),
                    'icon' => (string) ($action['icon'] ?? 'gear'),
                    'placement' => (string) ($action['placement'] ?? 'overview'),
                ];
            })
            ->values()
            ->all();
    }

    private function replacePlayerTokens(mixed $value, Player $player): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return str_replace(
            ['{player_id}', '{player_name}', '{club_id}'],
            [(string) $player->id, $player->full_name, (string) $player->club_id],
            $value
        );
    }

    private function handlePhotoUpload(Request $request, array $validated, ?string $previousPath = null): array
    {
        if (!$request->hasFile('photo')) {
            unset($validated['photo']);

            return $validated;
        }

        $path = $request->file('photo')->store('public/player-photos');
        $validated['photo_path'] = $path;
        unset($validated['photo']);

        if ($previousPath) {
            Storage::delete($previousPath);
        }

        return $validated;
    }

    private function mapSeasonStat($stat): array
    {
        return [
            'season' => $stat->season ? [
                'name' => $stat->season->name,
            ] : null,
            'competition_context' => $stat->competition_context,
            'appearances' => (int) $stat->appearances,
            'goals' => (int) $stat->goals,
            'assists' => (int) $stat->assists,
            'yellow_cards' => (int) $stat->yellow_cards,
            'red_cards' => (int) $stat->red_cards,
            'average_rating' => (float) $stat->average_rating,
        ];
    }

    private function hierarchyLevels(): array
    {
        return [
            'apex' => [
                'key' => 'apex',
                'label' => 'Spitze',
                'description' => 'Kabinenfuehrung und absolute Schluesselspieler.',
                'players' => [],
            ],
            'core' => [
                'key' => 'core',
                'label' => 'Kern',
                'description' => 'Tragende Stammspieler des Projekts.',
                'players' => [],
            ],
            'rotation' => [
                'key' => 'rotation',
                'label' => 'Rotation',
                'description' => 'Regelmaessige Optionen fuer Spieltag und Taktik.',
                'players' => [],
            ],
            'development' => [
                'key' => 'development',
                'label' => 'Entwicklung',
                'description' => 'Talente und Aufbauprofile mit Perspektive.',
                'players' => [],
            ],
            'fringe' => [
                'key' => 'fringe',
                'label' => 'Rand',
                'description' => 'Ergaenzungsspieler und Kandidaten fuer Gespraeche.',
                'players' => [],
            ],
        ];
    }

    private function mapHierarchyPlayer(Player $player, PlayerLoadService $playerLoadService, int $recentMinutesShare): array
    {
        $activePromise = $player->playtimePromises
            ->sortByDesc('id')
            ->first(fn ($promise) => in_array($promise->status, ['active', 'at_risk', 'broken', 'fulfilled'], true));

        $mood = $this->resolveHierarchyMood((int) $player->happiness);
        $roleFit = $this->resolveRoleFit($player, $recentMinutesShare, $activePromise);

        return [
            'id' => $player->id,
            'full_name' => $player->full_name,
            'first_name' => $player->first_name,
            'last_name' => $player->last_name,
            'photo_url' => $player->photo_url,
            'position' => $player->display_position,
            'overall' => (int) $player->overall,
            'age' => (int) $player->age,
            'squad_role' => $player->squad_role,
            'squad_role_label' => $this->roleLabel($player->squad_role),
            'leadership_level' => $player->leadership_level,
            'leadership_label' => $this->leadershipLabel($player->leadership_level),
            'team_status' => $player->team_status,
            'team_status_label' => $this->teamStatusLabel($player->team_status),
            'expected_playtime' => (int) $player->expected_playtime,
            'role_override_active' => (bool) $player->role_override_active,
            'role_override_set_at' => $player->role_override_set_at?->format('d.m.Y H:i'),
            'recent_minutes_share' => $recentMinutesShare,
            'happiness' => (int) $player->happiness,
            'happiness_trend' => (int) $player->happiness_trend,
            'fatigue' => (int) $player->fatigue,
            'sharpness' => (int) $player->sharpness,
            'medical_status' => $player->medical_status,
            'injury_risk' => $playerLoadService->injuryRisk($player),
            'last_morale_reason' => $player->last_morale_reason,
            'pyramid_level' => $this->resolvePyramidLevel($player),
            'mood' => $mood,
            'role_fit' => $roleFit,
            'promise' => $activePromise ? [
                'status' => $activePromise->status,
                'expected_minutes_share' => (int) $activePromise->expected_minutes_share,
                'fulfilled_ratio' => (int) $activePromise->fulfilled_ratio,
                'deadline_at' => $activePromise->deadline_at?->format('d.m.Y'),
            ] : null,
        ];
    }

    private function resolvePyramidLevel(Player $player): string
    {
        if ($player->leadership_level === 'captain_group' || $player->squad_role === 'star_player') {
            return 'apex';
        }

        if ($player->squad_role === 'important_first_team' || $player->leadership_level === 'senior_core') {
            return 'core';
        }

        if ($player->squad_role === 'rotation') {
            return 'rotation';
        }

        if ($player->squad_role === 'prospect') {
            return 'development';
        }

        return 'fringe';
    }

    private function resolveHierarchyMood(int $happiness): array
    {
        return match (true) {
            $happiness >= 72 => ['status' => 'happy', 'label' => 'Zufrieden'],
            $happiness >= 50 => ['status' => 'steady', 'label' => 'Stabil'],
            default => ['status' => 'unsettled', 'label' => 'Unruhig'],
        };
    }

    private function resolveRoleFit(Player $player, int $recentMinutesShare, ?PlayerPlaytimePromise $promise): array
    {
        $delta = $recentMinutesShare - (int) $player->expected_playtime;
        $status = match (true) {
            $promise && $promise->status === 'broken' => 'critical',
            $promise && $promise->status === 'at_risk' => 'watching',
            $delta >= -8 => 'aligned',
            $delta >= -20 => 'watching',
            default => 'critical',
        };

        $label = match ($status) {
            'aligned' => 'Rolle wirkt gerecht',
            'watching' => 'Rolle beobachten',
            default => 'Rolle wirkt nicht gerecht',
        };

        $reason = match ($status) {
            'aligned' => 'Aktuelle Einsatzzeit passt weitgehend zur erwarteten Rolle.',
            'watching' => 'Die Einsatzzeit liegt spuerbar unter der Rollenerwartung.',
            default => 'Die aktuelle Rolle und die letzten Einsatzzeiten driften klar auseinander.',
        };

        if ($promise && $promise->status === 'broken') {
            $reason = 'Ein Spielzeitversprechen wurde bereits gebrochen.';
        } elseif ($promise && $promise->status === 'at_risk') {
            $reason = 'Ein aktives Spielzeitversprechen steht unter Druck.';
        }

        return [
            'status' => $status,
            'label' => $label,
            'reason' => $reason,
            'delta' => $delta,
        ];
    }

    private function roleLabel(?string $role): string
    {
        return match ($role) {
            'star_player' => 'Schluesselspieler',
            'important_first_team' => 'Wichtiger Stammspieler',
            'rotation' => 'Rotationsspieler',
            'prospect' => 'Perspektivspieler',
            'backup' => 'Backup',
            'surplus' => 'Randspieler',
            default => 'Ohne Rolle',
        };
    }

    private function leadershipLabel(?string $level): string
    {
        return match ($level) {
            'captain_group' => 'Kapitaensgruppe',
            'senior_core' => 'Senior Core',
            'regular' => 'Kabinenstimme',
            'low' => 'Im Hintergrund',
            default => 'Keine',
        };
    }

    private function teamStatusLabel(?string $status): string
    {
        return match ($status) {
            'leader' => 'Leader',
            'core' => 'Kern',
            'rotation' => 'Rotation',
            'development' => 'Entwicklung',
            'support' => 'Support',
            'fringe' => 'Rand',
            default => 'Offen',
        };
    }

    private function resolveFulfilledRatio(Player $player): int
    {
        $recentStats = \App\Models\MatchPlayerStat::query()
            ->where('player_id', $player->id)
            ->latest('id')
            ->limit(8)
            ->get(['minutes_played']);

        if ($recentStats->isEmpty()) {
            return 0;
        }

        $averageMinutes = (float) $recentStats->avg('minutes_played');

        return max(0, min(100, (int) round(($averageMinutes / 90) * 100)));
    }

    private function conversationTopicLabel(string $topic): string
    {
        return match ($topic) {
            'role' => 'Rolle',
            'playtime' => 'Spielzeit',
            'load' => 'Belastung',
            'morale' => 'Stimmung',
            default => $topic,
        };
    }

    private function conversationApproachLabel(string $approach): string
    {
        return match ($approach) {
            'supportive' => 'Supportiv',
            'honest' => 'Offen',
            'demanding' => 'Hart',
            'protective' => 'Vorsichtig',
            default => $approach,
        };
    }

    private function decisionEvaluation(Player $player, string $kind, ?string $promiseStatus = null): array
    {
        return match ($kind) {
            'promise' => match ($promiseStatus) {
                'fulfilled' => ['label' => 'Hat geholfen', 'accent' => 'emerald'],
                'broken' => ['label' => 'Hat verschaerft', 'accent' => 'rose'],
                default => ['label' => 'Noch offen', 'accent' => 'amber'],
            },
            'role_override' => $player->happiness >= 55
                ? ['label' => 'Hat geholfen', 'accent' => 'emerald']
                : ($player->happiness < 45 ? ['label' => 'Hat verschaerft', 'accent' => 'rose'] : ['label' => 'Neutral', 'accent' => 'slate']),
            default => $player->happiness >= 60
                ? ['label' => 'Hat geholfen', 'accent' => 'emerald']
                : ($player->happiness < 45 ? ['label' => 'Hat verschaerft', 'accent' => 'rose'] : ['label' => 'Neutral', 'accent' => 'slate']),
        };
    }
}
