<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\MatchPlayerStat;
use App\Models\Player;
use App\Models\PlayerPlaytimePromise;
use App\Services\InjuryManagementService;
use App\Services\PlayerLoadService;
use App\Services\PlayerMoraleService;
use App\Services\SquadHierarchyService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

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
            ->with(['club'])
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
    ): \Inertia\Response
    {
        $squadHierarchyService->refreshForClub($player->club);

        $player->load([
            'club',
            'seasonCompetitionStatistics.season:id,name,start_date',
            'playtimePromises',
            'injuries',
            'recoveryLogs' => fn ($query) => $query->latest('day')->limit(7),
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
            ->map(function ($stat) {
                return [
                    'minutes_played' => (int) $stat->minutes_played,
                    'goals' => (int) $stat->goals,
                    'assists' => (int) $stat->assists,
                    'rating' => (float) $stat->rating,
                    'match' => $stat->match ? [
                        'home_club_id' => (int) $stat->match->home_club_id,
                        'away_club_id' => (int) $stat->match->away_club_id,
                        'home_score' => $stat->match->home_score,
                        'away_score' => $stat->match->away_score,
                        'kickoff_date_formatted' => $stat->match->kickoff_at?->format('d.m.y'),
                        'competition_season' => [
                            'competition' => [
                                'code' => $stat->match->competitionSeason?->competition?->code,
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
                'age' => $player->age,
                'overall' => $player->overall,
                'potential' => $player->potential,
                'pace' => $player->pace,
                'shooting' => $player->shooting,
                'passing' => $player->passing,
                'dribbling' => $player->dribbling,
                'defending' => $player->defending,
                'physical' => $player->physical,
                'stamina' => $player->stamina,
                'morale' => $player->morale,
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
            ],
            'currentSeasonStats' => $currentSeasonStats,
            'careerStats' => $careerStats,
            'recentMatches' => $recentMatches,
            'isOwner' => $player->club && $request->user()->id === $player->club->user_id,
            'positions' => array_keys($this->positions()),
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
                'age' => ['required', 'integer', 'min:15', 'max:45'],
                'overall' => ['required', 'integer', 'min:1', 'max:99'],
                'pace' => ['required', 'integer', 'min:1', 'max:99'],
                'shooting' => ['required', 'integer', 'min:1', 'max:99'],
                'passing' => ['required', 'integer', 'min:1', 'max:99'],
                'defending' => ['required', 'integer', 'min:1', 'max:99'],
                'physical' => ['required', 'integer', 'min:1', 'max:99'],
                'stamina' => ['required', 'integer', 'min:1', 'max:100'],
                'morale' => ['required', 'integer', 'min:1', 'max:100'],
                'market_value' => ['required', 'numeric', 'min:0'],
                'salary' => ['required', 'numeric', 'min:0'],
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
}
