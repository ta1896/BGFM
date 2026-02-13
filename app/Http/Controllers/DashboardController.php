<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\SeasonClubStatistic;
use App\Models\TrainingSession;
use App\Services\TeamStrengthCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, TeamStrengthCalculator $calculator): View
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
            ->withCount(['players', 'lineups'])
            ->orderBy('name')
            ->get();

        $activeClub = $clubs->firstWhere('id', (int) $request->query('club')) ?? $clubs->first();
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

        if ($activeClub) {
            $activeClub->loadMissing(['stadium', 'activeSponsorContract.sponsor']);

            $activeLineup = $activeClub->lineups()
                ->with('players')
                ->where('is_active', true)
                ->first() ?? $activeClub->lineups()->with('players')->first();

            if ($activeLineup) {
                $metrics = $calculator->calculate($activeLineup);
                $activeClubReadyForNextMatch = true;
            }

            $latestClubStat = SeasonClubStatistic::query()
                ->where('club_id', $activeClub->id)
                ->latest('updated_at')
                ->first(['competition_season_id', 'points', 'form_last5']);

            $clubPoints = $latestClubStat?->points;
            $selectedCompetitionSeasonId = $latestClubStat?->competition_season_id;

            if ($latestClubStat?->competition_season_id) {
                $clubRank = SeasonClubStatistic::query()
                    ->where('competition_season_id', $latestClubStat->competition_season_id)
                    ->where('points', '>', (int) $latestClubStat->points)
                    ->count() + 1;
            } elseif ($activeClub->league_id) {
                $clubRank = Club::query()
                    ->where('league_id', $activeClub->league_id)
                    ->where('reputation', '>', (int) $activeClub->reputation)
                    ->count() + 1;
            } elseif ($activeClub->league) {
                $clubRank = Club::query()
                    ->where('league', $activeClub->league)
                    ->where('reputation', '>', (int) $activeClub->reputation)
                    ->count() + 1;
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
                fn (TrainingSession $session): string => $session->session_date->toDateString()
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
                fn (GameMatch $match): string => $match->kickoff_at->toDateString()
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

        $notifications = $request->user()
            ->gameNotifications()
            ->latest()
            ->limit(5)
            ->get();
        $unreadNotificationsCount = $request->user()
            ->gameNotifications()
            ->whereNull('seen_at')
            ->count();

        if ($activeClub) {
            if (!$activeLineup) {
                $assistantTasks[] = [
                    'kind' => 'warning',
                    'label' => 'Keine aktive Aufstellung',
                    'description' => 'Lege eine aktive Aufstellung fest, damit das Team spielbereit ist.',
                    'url' => route('lineups.index'),
                    'cta' => 'Aufstellung setzen',
                ];
            }

            if ($nextMatch && !$activeClubReadyForNextMatch) {
                $assistantTasks[] = [
                    'kind' => 'warning',
                    'label' => 'Naechstes Spiel ohne Setup',
                    'description' => 'Fuer die naechste Partie ist noch keine einsatzfaehige Match-Aufstellung hinterlegt.',
                    'url' => route('matches.lineup.edit', ['match' => $nextMatch->id, 'club' => $activeClub->id]),
                    'cta' => 'Match-Aufstellung',
                ];
            } elseif ($nextMatch) {
                $assistantTasks[] = [
                    'kind' => 'info',
                    'label' => 'Naechstes Spiel vorbereiten',
                    'description' => 'Pruefe Taktik, Rollen und Bank fuer die anstehende Begegnung.',
                    'url' => route('matches.show', $nextMatch),
                    'cta' => 'Matchcenter',
                ];
            }

            if (!$trainingPlanComplete) {
                $assistantTasks[] = [
                    'kind' => 'warning',
                    'label' => 'Trainingsplan unvollstaendig',
                    'description' => 'Plane diese Woche mindestens je eine Einheit fuer Gruppe A und Gruppe B.',
                    'url' => route('training.index', ['club' => $activeClub->id, 'range' => 'week']),
                    'cta' => 'Training planen',
                ];
            }

            if ($unreadNotificationsCount > 0) {
                $assistantTasks[] = [
                    'kind' => 'info',
                    'label' => $unreadNotificationsCount.' ungelesene Hinweise',
                    'description' => 'Transfer-, Match- und Verwaltungsupdates warten in der Inbox.',
                    'url' => route('notifications.index'),
                    'cta' => 'Inbox oeffnen',
                ];
            }
        }

        return view('dashboard', [
            'clubs' => $clubs,
            'activeClub' => $activeClub,
            'activeLineup' => $activeLineup,
            'metrics' => $metrics,
            'nextMatch' => $nextMatch,
            'nextMatchTypeLabel' => $nextMatchTypeLabel,
            'activeClubReadyForNextMatch' => $activeClubReadyForNextMatch,
            'opponentReadyForNextMatch' => $opponentReadyForNextMatch,
            'notifications' => $notifications,
            'unreadNotificationsCount' => $unreadNotificationsCount,
            'todayMatchesCount' => $todayMatchesCount,
            'clubRank' => $clubRank,
            'clubPoints' => $clubPoints,
            'recentForm' => $recentForm,
            'weekDays' => $weekDays,
            'trainingGroupACount' => $trainingGroupACount,
            'trainingGroupBCount' => $trainingGroupBCount,
            'trainingPlanComplete' => $trainingPlanComplete,
            'assistantTasks' => $assistantTasks,
            'selectedCompetitionSeasonId' => $selectedCompetitionSeasonId,
            'activeSponsorContract' => $activeClub?->activeSponsorContract,
            'stadium' => $activeClub?->stadium,
            'dashboardVariant' => $dashboardVariant,
            'dashboardVariants' => [
                'modern' => 'Modern',
                'compact' => 'Kompakt',
                'classic' => 'Klassisch',
            ],
        ]);
    }
}
