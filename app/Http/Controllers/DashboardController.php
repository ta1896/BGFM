<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\ManagerPresence;
use App\Models\PlayerConversation;
use App\Models\ScoutingWatchlist;
use App\Models\SeasonClubStatistic;
use App\Models\TrainingSession;
use App\Services\InjuryManagementService;
use App\Services\PlayerMoraleService;
use App\Services\SquadHierarchyService;
use App\Services\TeamStrengthCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
class DashboardController extends Controller
{
    public function index(
        Request $request,
        TeamStrengthCalculator $calculator,
        SquadHierarchyService $squadHierarchyService,
        PlayerMoraleService $playerMoraleService,
        InjuryManagementService $injuryManagementService,
    ): \Inertia\Response
    {
        $allowedDashboardVariants = ['modern', 'compact', 'classic'];
        $requestedDashboardVariant = strtolower((string) $request->query('variant'));

        if (in_array($requestedDashboardVariant, $allowedDashboardVariants, true)) {
            $request->session()->put('dashboard.variant', $requestedDashboardVariant);
        }

        $dashboardVariant = (string) $request->session()->get('dashboard.variant', 'modern');
        if (!in_array($dashboardVariant, $allowedDashboardVariants, true)) {
            $dashboardVariant = 'modern';
        }

        $clubs = $request->user()
            ->clubs()
            ->orderBy('name')
            ->get();

        // Use standard activeClub from container
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;

        // Fallback for Dashboard specifically if middleware somehow failed or session is empty
        if (!$activeClub) {
            $activeClub = $clubs->first();
        }

        // Reload relationships needed for dashboard if they aren't already loaded
        // (Middleware might have loaded basic model, but Dashboard needs more)
        $todayMatchesCount = GameMatch::query()
            ->whereDate('kickoff_at', now()->toDateString())
            ->count();

        $activeLineup = null;
        $metrics = [
            'overall' => 0,
            'attack' => 0,
            'midfield' => 0,
            'defense' => 0,
            'chemistry' => 0,
        ];
        $clubRank = null;
        $clubPoints = null;
        $recentForm = [];
        $nextMatchTypeLabel = null;
        $activeClubReadyForNextMatch = false;
        $opponentReadyForNextMatch = false;
        $weekDays = collect();
        $trainingGroupACount = 0;
        $trainingGroupBCount = 0;
        $trainingPlanComplete = false;
        $assistantTasks = [];
        $selectedCompetitionSeasonId = null;
        $squadAlerts = [];
        $squadPulse = [
            'manual_roles_count' => 0,
            'promise_pressure_count' => 0,
            'manual_role_players' => [],
            'pressure_players' => [],
        ];
        $managerDecisions = [];
        $scoutingDesk = [
            'watchlist_count' => 0,
            'priority_targets' => [],
        ];
        $todayFocus = [];
        $clubPulseOverview = [];
        $comparisonStats = [];
        $quickActions = [];
        $liveMatches = [];
        $onlineManagers = [];
        $conversationsEnabled = (bool) config('simulation.features.player_conversations_enabled', false);

        if ($activeClub) {
            $activeClub->loadMissing(['stadium', 'activeSponsorContract.sponsor']);
            $squadHierarchyService->refreshForClub($activeClub);

            $activeClub->loadMissing(['players.playtimePromises']);
            $activeClub->players->each(function ($player) use ($playerMoraleService, $injuryManagementService): void {
                $injuryManagementService->syncCurrentInjury($player);
                $playerMoraleService->refresh($player->loadMissing(['playtimePromises', 'injuries']));
            });

            $activeLineup = Lineup::query()
                ->where('club_id', $activeClub->id)
                ->with('players')
                ->where('is_active', true)
                ->first() ?? Lineup::query()
                                ->where('club_id', $activeClub->id)
                                ->with('players')
                                ->first();

            if ($activeLineup) {
                $metrics = $calculator->calculate($activeLineup);
                // Unset players relation to prevent sending full player objects via Inertia
                $activeLineup->unsetRelation('players');
                $activeClubReadyForNextMatch = true;
            }

            $latestClubStat = SeasonClubStatistic::query()
                ->where('club_id', $activeClub->id)
                ->latest('updated_at')
                ->first(['competition_season_id', 'points', 'form_last5']);

            $clubPoints = $latestClubStat?->points;
            $selectedCompetitionSeasonId = $latestClubStat?->competition_season_id;

            if ($latestClubStat?->competition_season_id) {
                $cacheKey = "club_rank_{$activeClub->id}_{$latestClubStat->competition_season_id}";
                $clubRank = Cache::remember($cacheKey, 600, function () use ($latestClubStat) {
                    return SeasonClubStatistic::query()
                        ->where('competition_season_id', $latestClubStat->competition_season_id)
                        ->where('points', '>', (int) $latestClubStat->points)
                        ->count() + 1;
                });
            } elseif ($activeClub->league_id) {
                $cacheKey = "club_rank_legacy_{$activeClub->id}_{$activeClub->league_id}";
                $clubRank = Cache::remember($cacheKey, 600, function () use ($activeClub) {
                    return Club::query()
                        ->where('league_id', $activeClub->league_id)
                        ->where('reputation', '>', (int) $activeClub->reputation)
                        ->count() + 1;
                });
            }

            $recentMatches = GameMatch::query()
                ->where('status', 'played')
                ->where(function ($query) use ($activeClub) {
                    $query->where('home_club_id', $activeClub->id)
                        ->orWhere('away_club_id', $activeClub->id);
                })
                ->orderByRaw('COALESCE(played_at, kickoff_at) DESC')
                ->limit(5)
                ->get(['home_club_id', 'away_club_id', 'home_score', 'away_score']);

            $recentForm = $recentMatches
                ->map(function (GameMatch $match) use ($activeClub): string {
                    $isHomeClub = (int) $match->home_club_id === (int) $activeClub->id;
                    $goalsFor = (int) ($isHomeClub ? $match->home_score : $match->away_score);
                    $goalsAgainst = (int) ($isHomeClub ? $match->away_score : $match->home_score);

                    if ($goalsFor > $goalsAgainst) {
                        return 'W';
                    }

                    if ($goalsFor < $goalsAgainst) {
                        return 'L';
                    }

                    return 'D';
                })
                ->all();

            if ($recentForm === [] && !empty($latestClubStat?->form_last5)) {
                $recentForm = str_split((string) $latestClubStat->form_last5);
            }
        }

        $nextMatch = null;
        if ($activeClub) {
            $nextMatch = GameMatch::query()
                ->with(['homeClub', 'awayClub'])
                ->where(function ($query) use ($activeClub) {
                    $query->where('home_club_id', $activeClub->id)
                        ->orWhere('away_club_id', $activeClub->id);
                })
                ->where('status', 'scheduled')
                ->orderBy('kickoff_at')
                ->first();

            if ($nextMatch) {
                $selectedCompetitionSeasonId = $nextMatch->competition_season_id ?? $selectedCompetitionSeasonId;
                $nextMatchTypeLabel = match ((string) $nextMatch->type) {
                    'friendly' => 'Freundschaftsspiel',
                    'cup' => 'Pokal',
                    'league' => 'Liga',
                    default => ucfirst((string) $nextMatch->type),
                };

                $opponentClubId = (int) $nextMatch->home_club_id === (int) $activeClub->id
                    ? (int) $nextMatch->away_club_id
                    : (int) $nextMatch->home_club_id;

                $opponentReadyForNextMatch = Lineup::query()
                    ->where('club_id', $opponentClubId)
                    ->where('is_active', true)
                    ->exists();
            }

            $weekStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

            $trainingSessionsThisWeek = TrainingSession::query()
                ->where('club_id', $activeClub->id)
                ->whereBetween('session_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->get(['session_date', 'type']);

            $trainingByDate = $trainingSessionsThisWeek->groupBy(
                fn(TrainingSession $session): string => $session->session_date->format('Y-m-d')
            );

            $trainingGroupACount = $trainingSessionsThisWeek
                ->whereIn('type', ['fitness', 'technical'])
                ->count();
            $trainingGroupBCount = $trainingSessionsThisWeek
                ->whereIn('type', ['tactics', 'recovery', 'friendly'])
                ->count();
            $trainingPlanComplete = $trainingGroupACount > 0 && $trainingGroupBCount > 0;

            $matchesThisWeek = GameMatch::query()
                ->whereBetween('kickoff_at', [$weekStart, $weekEnd])
                ->where(function ($query) use ($activeClub) {
                    $query->where('home_club_id', $activeClub->id)
                        ->orWhere('away_club_id', $activeClub->id);
                })
                ->get(['kickoff_at']);

            $matchesByDate = $matchesThisWeek->groupBy(
                fn(GameMatch $match): string => $match->kickoff_at->toDateString()
            );

            $weekdayLabels = [
                1 => 'MO.',
                2 => 'DI.',
                3 => 'MI.',
                4 => 'DO.',
                5 => 'FR.',
                6 => 'SA.',
                7 => 'SO.',
            ];

            $weekDays = collect(range(0, 6))
                ->map(function (int $offset) use ($weekStart, $weekdayLabels, $trainingByDate, $matchesByDate): array {
                    $date = $weekStart->copy()->addDays($offset);
                    $dateKey = $date->toDateString();

                    return [
                        'label' => $weekdayLabels[$date->dayOfWeekIso] ?? strtoupper($date->format('D')),
                        'date' => $date->format('d.m'),
                        'iso_date' => $date->toDateString(),
                        'is_today' => $date->isToday(),
                        'training_count' => $trainingByDate->get($dateKey)?->count() ?? 0,
                        'match_count' => $matchesByDate->get($dateKey)?->count() ?? 0,
                    ];
                });
        }

        $unreadNotificationsCount = $request->user()
            ->gameNotifications()
            ->whereNull('seen_at')
            ->count();

        $liveMatches = GameMatch::query()
            ->with(['homeClub:id,name,logo_path', 'awayClub:id,name,logo_path'])
            ->where('status', 'live')
            ->orderByDesc('live_minute')
            ->limit(6)
            ->get()
            ->map(function (GameMatch $match): array {
                return [
                    'id' => $match->id,
                    'live_minute' => (int) ($match->live_minute ?? 0),
                    'home_score' => (int) ($match->home_score ?? 0),
                    'away_score' => (int) ($match->away_score ?? 0),
                    'home_club' => $match->homeClub ? [
                        'id' => $match->homeClub->id,
                        'name' => $match->homeClub->name,
                        'logo_url' => $match->homeClub->logo_url,
                    ] : null,
                    'away_club' => $match->awayClub ? [
                        'id' => $match->awayClub->id,
                        'name' => $match->awayClub->name,
                        'logo_url' => $match->awayClub->logo_url,
                    ] : null,
                ];
            })
            ->all();

        $onlineManagers = ManagerPresence::query()
            ->with(['user:id,name', 'club:id,name,logo_path'])
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->whereHas('user', fn ($query) => $query->where('is_admin', false))
            ->orderByDesc('last_seen_at')
            ->limit(5)
            ->get()
            ->map(function (ManagerPresence $presence): array {
                return [
                    'id' => $presence->id,
                    'manager' => $presence->user?->name,
                    'club' => $presence->club ? [
                        'name' => $presence->club->name,
                        'logo_url' => $presence->club->logo_url,
                    ] : null,
                    'activity_label' => $presence->activity_label,
                    'last_seen_label' => $presence->last_seen_at?->diffForHumans(),
                ];
            })
            ->all();

        if ($activeClub) {
            if (!$activeLineup) {
                $assistantTasks[] = [
                    'kind' => 'warning',
                    'priority' => 'sofort',
                    'domain' => 'kader',
                    'metric' => '0 aktiv',
                    'label' => 'Keine aktive Aufstellung',
                    'description' => 'Lege eine aktive Aufstellung fest, damit das Team spielbereit ist.',
                    'url' => route('lineups.index'),
                    'cta' => 'Aufstellung setzen',
                ];
            }

            if ($nextMatch && !$activeClubReadyForNextMatch) {
                $assistantTasks[] = [
                    'kind' => 'warning',
                    'priority' => 'sofort',
                    'domain' => 'matchday',
                    'metric' => 'Lineup fehlt',
                    'label' => 'Naechstes Spiel ohne Setup',
                    'description' => 'Fuer die naechste Partie ist noch keine einsatzfaehige Match-Aufstellung hinterlegt.',
                    'url' => route('matches.lineup.edit', ['match' => $nextMatch->id, 'club' => $activeClub->id]),
                    'cta' => 'Match-Aufstellung',
                ];
            } elseif ($nextMatch) {
                $assistantTasks[] = [
                    'kind' => 'info',
                    'priority' => 'heute',
                    'domain' => 'matchday',
                    'metric' => $nextMatch->kickoff_at?->format('d.m H:i'),
                    'label' => 'Naechstes Spiel vorbereiten',
                    'description' => 'Pruefe Taktik, Rollen und Bank fuer die anstehende Begegnung.',
                    'url' => route('matches.show', $nextMatch),
                    'cta' => 'Matchcenter',
                ];
            }

            if (!$trainingPlanComplete) {
                $assistantTasks[] = [
                    'kind' => 'warning',
                    'priority' => 'heute',
                    'domain' => 'training',
                    'metric' => $trainingGroupACount.'/'.$trainingGroupBCount.' Gruppen',
                    'label' => 'Trainingsplan unvollstaendig',
                    'description' => 'Plane diese Woche mindestens je eine Einheit fuer Gruppe A und Gruppe B.',
                    'url' => route('training.index', ['club' => $activeClub->id, 'range' => 'week']),
                    'cta' => 'Training planen',
                ];
            }

            if ($unreadNotificationsCount > 0) {
                $assistantTasks[] = [
                    'kind' => 'info',
                    'priority' => 'beobachten',
                    'domain' => 'postfach',
                    'metric' => $unreadNotificationsCount.' offen',
                    'label' => $unreadNotificationsCount . ' ungelesene Hinweise',
                    'description' => 'Match- und Verwaltungsupdates warten in der Inbox.',
                    'url' => route('notifications.index'),
                    'cta' => 'Inbox oeffnen',
                ];
            }

            $unhappyCount = $activeClub->players->where('happiness', '<', 45)->count();
            $riskCount = $activeClub->players->where('fatigue', '>=', 70)->count();
            $promiseCount = $activeClub->players->filter(
                fn ($player) => $player->playtimePromises->whereIn('status', ['active', 'at_risk'])->isNotEmpty()
            )->count();
            $brokenPromiseCount = $activeClub->players->filter(
                fn ($player) => $player->playtimePromises->where('status', 'broken')->isNotEmpty()
            )->count();
            $manualRolePlayers = $activeClub->players
                ->filter(fn ($player) => (bool) $player->role_override_active)
                ->sortByDesc('overall')
                ->take(4)
                ->values();
            $pressurePlayers = $activeClub->players
                ->filter(function ($player): bool {
                    $promise = $player->playtimePromises->sortByDesc('id')->first();

                    return $promise && in_array($promise->status, ['at_risk', 'broken'], true);
                })
                ->sortBy('happiness')
                ->take(4)
                ->values();

            $squadAlerts = [
                'unhappy_count' => $unhappyCount,
                'high_risk_count' => $riskCount,
                'promise_count' => $promiseCount,
                'broken_promise_count' => $brokenPromiseCount,
            ];
            $squadPulse = [
                'manual_roles_count' => $manualRolePlayers->count(),
                'promise_pressure_count' => $pressurePlayers->count(),
                'manual_role_players' => $manualRolePlayers->map(fn ($player) => [
                    'id' => $player->id,
                    'full_name' => $player->full_name,
                    'photo_url' => $player->photo_url,
                    'squad_role' => $player->squad_role,
                ])->all(),
                'pressure_players' => $pressurePlayers->map(function ($player) {
                    $promise = $player->playtimePromises->sortByDesc('id')->first();

                    return [
                        'id' => $player->id,
                        'full_name' => $player->full_name,
                        'photo_url' => $player->photo_url,
                        'promise_status' => $promise?->status,
                        'happiness' => (int) $player->happiness,
                    ];
                })->all(),
            ];
            $watchlistEntries = ScoutingWatchlist::query()
                ->where('club_id', $activeClub->id)
                ->with(['player.club', 'reports' => fn ($query) => $query->latest('id')->limit(1)])
                ->latest('updated_at')
                ->get();
            $scoutingDesk = [
                'watchlist_count' => $watchlistEntries->count(),
                'priority_targets' => $watchlistEntries
                    ->sortByDesc(fn ($entry) => match ($entry->priority) {
                        'high' => 3,
                        'medium' => 2,
                        default => 1,
                    })
                    ->take(3)
                    ->map(function ($entry) {
                        $report = $entry->reports->first();

                        return [
                            'id' => $entry->player?->id,
                            'name' => $entry->player?->full_name,
                            'photo_url' => $entry->player?->photo_url,
                            'club_name' => $entry->player?->club?->name,
                            'priority' => $entry->priority,
                            'status' => $entry->status,
                            'confidence' => $report?->confidence,
                            'overall_band' => $report ? $report->overall_min.'-'.$report->overall_max : null,
                        ];
                    })
                    ->values()
                    ->all(),
            ];
            $conversationDecisions = $conversationsEnabled
                ? PlayerConversation::query()
                    ->where('club_id', $activeClub->id)
                    ->with('player')
                    ->latest('id')
                    ->limit(5)
                    ->get()
                    ->map(fn (PlayerConversation $conversation) => [
                        'kind' => 'conversation',
                        'title' => 'Gespraech: '.$this->conversationTopicLabel($conversation->topic),
                        'player_name' => $conversation->player?->full_name ?? 'Spieler',
                        'player_id' => $conversation->player_id,
                        'photo_url' => $conversation->player?->photo_url,
                        'accent' => $conversation->happiness_delta >= 0 ? 'emerald' : 'rose',
                        'impact_label' => ($conversation->happiness_delta >= 0 ? '+' : '').$conversation->happiness_delta.' Mood',
                        'summary' => $conversation->summary ?: $conversation->player_response,
                        'created_at' => $conversation->created_at?->format('d.m H:i'),
                        'evaluation' => $this->decisionEvaluation($conversation->player, 'conversation'),
                    ])
                : collect();
            $roleDecisions = $activeClub->players
                ->filter(fn ($player) => (bool) $player->role_override_active && $player->role_override_set_at)
                ->sortByDesc('role_override_set_at')
                ->take(5)
                ->map(fn ($player) => [
                    'kind' => 'role_override',
                    'title' => 'Rolle manuell gesetzt',
                    'player_name' => $player->full_name,
                    'player_id' => $player->id,
                    'photo_url' => $player->photo_url,
                    'accent' => 'fuchsia',
                    'impact_label' => strtoupper((string) $player->squad_role),
                    'summary' => 'Systematik wurde fuer diesen Spieler bewusst ueberschrieben.',
                    'created_at' => $player->role_override_set_at?->format('d.m H:i'),
                    'evaluation' => $this->decisionEvaluation($player, 'role_override'),
                ]);
            $promiseDecisions = $activeClub->players
                ->map(function ($player) {
                    $promise = $player->playtimePromises
                        ->sortByDesc('id')
                        ->first(fn ($item) => in_array($item->status, ['active', 'at_risk', 'broken', 'fulfilled'], true));

                    if (!$promise) {
                        return null;
                    }

                    return [
                        'kind' => 'promise',
                        'title' => 'Spielzeitversprechen',
                        'player_name' => $player->full_name,
                        'player_id' => $player->id,
                        'photo_url' => $player->photo_url,
                        'accent' => match ($promise->status) {
                            'broken' => 'rose',
                            'at_risk' => 'amber',
                            default => 'cyan',
                        },
                        'impact_label' => strtoupper((string) $promise->status),
                        'summary' => 'Ziel: '.$promise->expected_minutes_share.'% Minuten, aktuell '.$promise->fulfilled_ratio.'%.',
                        'created_at' => $promise->created_at?->format('d.m H:i'),
                        'evaluation' => $this->decisionEvaluation($player, 'promise', $promise->status),
                    ];
                })
                ->filter()
                ->sortByDesc('created_at')
                ->take(5);
            $managerDecisions = $conversationDecisions
                ->concat($roleDecisions)
                ->concat($promiseDecisions)
                ->sortByDesc('created_at')
                ->take(7)
                ->values()
                ->all();

            if ($unhappyCount > 0) {
                $assistantTasks[] = [
                    'kind' => 'warning',
                    'priority' => 'heute',
                    'domain' => 'kader',
                    'metric' => $unhappyCount.' Spieler',
                    'label' => $unhappyCount.' unzufriedene Spieler',
                    'description' => 'Rollen, Einsatzzeiten oder Belastung sorgen fuer Unruhe im Kader.',
                    'url' => route('players.index'),
                    'cta' => 'Kader pruefen',
                ];
            }

            if ($promiseCount > 0) {
                $assistantTasks[] = [
                    'kind' => $brokenPromiseCount > 0 ? 'warning' : 'info',
                    'priority' => $brokenPromiseCount > 0 ? 'sofort' : 'beobachten',
                    'domain' => 'versprechen',
                    'metric' => $brokenPromiseCount > 0 ? $brokenPromiseCount.' gebrochen' : $promiseCount.' aktiv',
                    'label' => $brokenPromiseCount > 0
                        ? $brokenPromiseCount.' gebrochene Versprechen'
                        : $promiseCount.' laufende Spielzeitversprechen',
                    'description' => $brokenPromiseCount > 0
                        ? 'Mindestens ein zugesagter Minutenanteil wurde verfehlt. Das drueckt sofort auf die Moral.'
                        : 'Mehrere Spieler erwarten definierte Einsatzzeiten. Behalte die Rotation im Blick.',
                    'url' => route('players.index'),
                    'cta' => 'Versprechen pruefen',
                ];
            }

            $formPoints = collect($recentForm)->sum(fn ($result) => match ($result) {
                'W' => 3,
                'D' => 1,
                default => 0,
            });
            $trainingCoverage = min(100, (int) round((($trainingGroupACount > 0 ? 1 : 0) + ($trainingGroupBCount > 0 ? 1 : 0)) / 2 * 100));
            $clubPulseOverview = [
                [
                    'label' => 'Stimmung',
                    'value' => (int) $activeClub->fan_mood,
                    'suffix' => '%',
                    'tone' => $activeClub->fan_mood >= 70 ? 'emerald' : ($activeClub->fan_mood >= 45 ? 'amber' : 'rose'),
                ],
                [
                    'label' => 'Belastungsrisiko',
                    'value' => $riskCount,
                    'suffix' => ' Spieler',
                    'tone' => $riskCount === 0 ? 'emerald' : ($riskCount <= 2 ? 'amber' : 'rose'),
                ],
                [
                    'label' => 'Promise-Druck',
                    'value' => $promiseCount,
                    'suffix' => ' offen',
                    'tone' => $brokenPromiseCount > 0 ? 'rose' : ($promiseCount > 0 ? 'amber' : 'emerald'),
                ],
                [
                    'label' => 'Inbox',
                    'value' => $unreadNotificationsCount,
                    'suffix' => ' offen',
                    'tone' => $unreadNotificationsCount > 0 ? 'cyan' : 'slate',
                ],
            ];
            $comparisonStats = [
                [
                    'label' => 'Fanmood vs Basis',
                    'value' => ((int) $activeClub->fan_mood - 50),
                    'display' => sprintf('%+d', (int) $activeClub->fan_mood - 50),
                    'suffix' => ' zu neutral',
                    'tone' => $activeClub->fan_mood >= 50 ? 'emerald' : 'rose',
                ],
                [
                    'label' => 'Form letzte 5',
                    'value' => $formPoints,
                    'display' => $formPoints.'/15',
                    'suffix' => ' Punkte',
                    'tone' => $formPoints >= 9 ? 'emerald' : ($formPoints >= 5 ? 'amber' : 'rose'),
                ],
                [
                    'label' => 'Trainingsbalance',
                    'value' => $trainingCoverage,
                    'display' => $trainingCoverage.'%',
                    'suffix' => $trainingGroupACount.'/'.$trainingGroupBCount.' aktiv',
                    'tone' => $trainingPlanComplete ? 'emerald' : 'amber',
                ],
                [
                    'label' => 'Spielbereitschaft',
                    'value' => $activeClubReadyForNextMatch ? 1 : 0,
                    'display' => $activeClubReadyForNextMatch ? 'bereit' : 'offen',
                    'suffix' => $opponentReadyForNextMatch ? ' Gegner bereit' : ' Gegner offen',
                    'tone' => $activeClubReadyForNextMatch ? 'emerald' : 'rose',
                ],
            ];
            $todayFocus = array_values(array_filter([
                $nextMatch ? [
                    'label' => 'Naechstes Spiel',
                    'value' => $nextMatchTypeLabel,
                    'detail' => $nextMatch->kickoff_at?->format('d.m H:i'),
                    'tone' => 'amber',
                    'url' => route('matches.show', $nextMatch),
                    'cta' => 'Zum Match',
                ] : null,
                [
                    'label' => 'Pflichtaktion',
                    'value' => $assistantTasks[0]['label'] ?? 'Keine Eskalation',
                    'detail' => $assistantTasks[0]['metric'] ?? 'Alles stabil',
                    'tone' => ($assistantTasks[0]['priority'] ?? null) === 'sofort' ? 'rose' : 'cyan',
                    'url' => $assistantTasks[0]['url'] ?? route('dashboard'),
                    'cta' => $assistantTasks[0]['cta'] ?? 'Dashboard',
                ],
                [
                    'label' => 'Live-Betrieb',
                    'value' => count($liveMatches).' Live',
                    'detail' => count($onlineManagers).' Manager online',
                    'tone' => count($liveMatches) > 0 ? 'emerald' : 'slate',
                    'url' => route('live-ticker.index'),
                    'cta' => 'Live-Ticker',
                ],
                [
                    'label' => 'Kaderdruck',
                    'value' => $unhappyCount.' Unruhe',
                    'detail' => $promiseCount.' Promises / '.$riskCount.' Risiko',
                    'tone' => ($unhappyCount > 0 || $brokenPromiseCount > 0) ? 'rose' : ($promiseCount > 0 ? 'amber' : 'emerald'),
                    'url' => route('players.index'),
                    'cta' => 'Kader',
                ],
            ]));
            $quickActions = [
                [
                    'label' => 'Aufstellung',
                    'description' => 'Lineup und Rollen pruefen',
                    'url' => route('lineups.index'),
                    'tone' => 'amber',
                ],
                [
                    'label' => 'Training',
                    'description' => 'Wochenplan schliessen',
                    'url' => route('training.index'),
                    'tone' => 'cyan',
                ],
                [
                    'label' => 'Medical',
                    'description' => 'Risiken und Reha checken',
                    'url' => route('medical.index'),
                    'tone' => 'rose',
                ],
                [
                    'label' => 'Scouting',
                    'description' => 'Reports und Ziele pflegen',
                    'url' => route('scouting.index'),
                    'tone' => 'emerald',
                ],
            ];
        }

        return \Inertia\Inertia::render('Dashboard', [
            'activeClub' => $activeClub ? [
                'id' => $activeClub->id,
                'name' => $activeClub->name,
                'budget' => $activeClub->budget,
                'fan_mood' => $activeClub->fan_mood,
                'logo_url' => $activeClub->logo_url,
            ] : null,
            'metrics' => $metrics,
            'nextMatch' => $nextMatch ? [
                'id' => $nextMatch->id,
                'kickoff_at_formatted' => $nextMatch->kickoff_at?->format('d.m.Y H:i'),
                'stadium_name' => $nextMatch->stadium_name,
                'home_club' => $nextMatch->homeClub ? [
                    'id' => $nextMatch->homeClub->id,
                    'name' => $nextMatch->homeClub->name,
                    'logo_url' => $nextMatch->homeClub->logo_url,
                ] : null,
                'away_club' => $nextMatch->awayClub ? [
                    'id' => $nextMatch->awayClub->id,
                    'name' => $nextMatch->awayClub->name,
                    'logo_url' => $nextMatch->awayClub->logo_url,
                ] : null,
            ] : null,
            'nextMatchTypeLabel' => $nextMatchTypeLabel,
            'activeClubReadyForNextMatch' => $activeClubReadyForNextMatch,
            'opponentReadyForNextMatch' => $opponentReadyForNextMatch,
            'todayMatchesCount' => $todayMatchesCount,
            'clubRank' => $clubRank,
            'clubPoints' => $clubPoints,
            'recentForm' => $recentForm,
            'weekDays' => $weekDays,
            'trainingGroupACount' => $trainingGroupACount,
            'trainingGroupBCount' => $trainingGroupBCount,
            'trainingPlanComplete' => $trainingPlanComplete,
            'dashboardVariant' => $dashboardVariant,
            'assistantTasks' => $assistantTasks,
            'todayFocus' => $todayFocus,
            'clubPulseOverview' => $clubPulseOverview,
            'comparisonStats' => $comparisonStats,
            'quickActions' => $quickActions,
            'squadAlerts' => $squadAlerts,
            'squadPulse' => $squadPulse,
            'scoutingDesk' => $scoutingDesk,
            'managerDecisions' => $managerDecisions,
            'liveMatches' => $liveMatches,
            'onlineManagers' => $onlineManagers,
        ]);
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

    private function decisionEvaluation($player, string $kind, ?string $promiseStatus = null): array
    {
        if (!$player) {
            return ['label' => 'Neutral', 'accent' => 'slate'];
        }

        return match ($kind) {
            'promise' => match ($promiseStatus) {
                'fulfilled' => ['label' => 'Hat geholfen', 'accent' => 'emerald'],
                'broken' => ['label' => 'Hat verschaerft', 'accent' => 'rose'],
                default => ['label' => 'Noch offen', 'accent' => 'amber'],
            },
            'role_override' => $player->happiness >= 55 && $player->expected_playtime <= 100
                ? ['label' => 'Hat geholfen', 'accent' => 'emerald']
                : ($player->happiness < 45 ? ['label' => 'Hat verschaerft', 'accent' => 'rose'] : ['label' => 'Neutral', 'accent' => 'slate']),
            default => $player->happiness >= 60
                ? ['label' => 'Hat geholfen', 'accent' => 'emerald']
                : ($player->happiness < 45 ? ['label' => 'Hat verschaerft', 'accent' => 'rose'] : ['label' => 'Neutral', 'accent' => 'slate']),
        };
    }
}
