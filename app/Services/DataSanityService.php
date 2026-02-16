<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\MatchEvent;
use App\Models\MatchLiveAction;
use App\Models\Club;
use App\Models\Lineup;
use Illuminate\Support\Collection;

class DataSanityService
{
    /**
     * Run all diagnostic checks and return a report.
     */
    public function runDiagnostics(bool $forceRefresh = false): array
    {
        $cacheKey = 'admin_monitoring_diagnostics';

        if ($forceRefresh) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(5), function () use ($cacheKey) {
            $data = [
                'matches' => $this->checkMatches(),
                'events' => $this->checkEvents(),
                'live_actions' => $this->checkLiveActions(),
                'clubs' => $this->checkClubs(),
                'finances' => $this->checkFinances(),
                'performance' => $this->checkPerformance(),
                'inactivity' => $this->checkInactivity(),
                'generated_at' => now()->format('H:i:s'),
            ];
            \Illuminate\Support\Facades\Cache::put($cacheKey . '_timestamp', $data['generated_at'], now()->addMinutes(10));
            return $data;
        });
    }

    /**
     * Check for issues in the matches table.
     */
    private function checkMatches(): array
    {
        $playedWithoutScores = GameMatch::where('status', 'played')
            ->where(fn($q) => $q->whereNull('home_score')->orWhereNull('away_score'))
            ->get();

        $playedWithoutSeed = GameMatch::where('status', 'played')
            ->whereNull('simulation_seed')
            ->get();

        $issues = [];
        foreach ($playedWithoutScores as $m) {
            $issues[] = [
                'id' => $m->id,
                'description' => "Status is 'played' but scores are missing.",
                'severity' => 'CRITICAL',
                'reason' => "Match simulation likely failed mid-transaction or manual DB edit occurred."
            ];
        }

        foreach ($playedWithoutSeed as $m) {
            $issues[] = [
                'id' => $m->id,
                'description' => "Status is 'played' but simulation_seed is missing.",
                'severity' => 'WARNING',
                'reason' => "Seed is required for deterministic re-simulations."
            ];
        }

        return ['count' => count($issues), 'issues' => array_slice($issues, 0, 5)];
    }

    /**
     * Check for issues in match events.
     */
    private function checkEvents(): array
    {
        $missingNarratives = MatchEvent::where(fn($q) => $q->whereNull('narrative')->orWhere('narrative', ''))
            ->limit(50)
            ->get();

        $missingPlayers = MatchEvent::whereNull('player_id')
            ->whereIn('event_type', ['goal', 'yellow_card', 'red_card', 'substitution'])
            ->get();

        $issues = [];
        foreach ($missingNarratives as $e) {
            $issues[] = [
                'id' => $e->id,
                'description' => "Event (Type: {$e->event_type}) has no narrative text.",
                'severity' => 'WARNING',
                'reason' => "NarrativeEngine failed to pick a template or template seeding is incomplete."
            ];
        }

        foreach ($missingPlayers as $e) {
            $issues[] = [
                'id' => $e->id,
                'description' => "Major event (Type: {$e->event_type}) is missing player_id.",
                'severity' => 'CRITICAL',
                'reason' => "Weighted player pick in SimulationService failed due to empty squad or invalid weights."
            ];
        }

        return ['count' => count($issues), 'issues' => array_slice($issues, 0, 5)];
    }

    /**
     * Check for issues in live actions.
     */
    private function checkLiveActions(): array
    {
        $missingCoords = MatchLiveAction::where(fn($q) => $q->whereNull('x_coord')->orWhereNull('y_coord'))->limit(50)->get();

        $issues = [];

        if ($missingCoords->count() > 0) {
            $issues[] = [
                'description' => "{$missingCoords->count()} actions have no pitch coordinates.",
                'severity' => 'WARNING',
                'reason' => "Visualization might be broken for these actions."
            ];
        }

        return ['count' => count($issues), 'issues' => $issues];
    }

    /**
     * Check for structural inconsistencies in clubs/lineups.
     */
    private function checkClubs(): array
    {
        $clubsWithoutActiveLineup = Club::whereDoesntHave('lineups', fn($q) => $q->where('is_active', true))->get();
        $emptyClubs = Club::whereDoesntHave('players')->get();

        $issues = [];
        foreach ($clubsWithoutActiveLineup as $c) {
            $issues[] = [
                'id' => $c->id,
                'name' => $c->name,
                'description' => "Club has no active lineup.",
                'severity' => 'CRITICAL',
                'reason' => "Match simulation will use fallback (random) players if no lineup is set."
            ];
        }

        foreach ($emptyClubs as $c) {
            $issues[] = [
                'id' => $c->id,
                'name' => $c->name,
                'description' => "Club has 0 players.",
                'severity' => 'CRITICAL',
                'reason' => "Club seeder failed or players were deleted. Simulation will crash."
            ];
        }

        return ['count' => count($issues), 'issues' => array_slice($issues, 0, 5)];
    }

    /**
     * Check for realistic financial values.
     */
    private function checkFinances(): array
    {
        $tooHighWages = \App\Models\Player::where('salary', '>', 500000)->get(); // > 500k/week is rare in small sims
        $tooHighMarketValue = \App\Models\Player::where('market_value', '>', 300000000)->get(); // > 300M

        $issues = [];
        foreach ($tooHighWages as $p) {
            $issues[] = [
                'id' => $p->id,
                'description' => "Player {$p->full_name} has very high salary: " . number_format($p->salary) . "€",
                'severity' => 'WARNING',
                'reason' => "Possible bug in contract generation or initial player seeding."
            ];
        }

        foreach ($tooHighMarketValue as $p) {
            $issues[] = [
                'id' => $p->id,
                'description' => "Player {$p->full_name} has extreme market value: " . number_format($p->market_value) . "€",
                'severity' => 'WARNING',
                'reason' => "Market value algorithm might be overvaluing potential/performance."
            ];
        }

        return ['count' => count($issues), 'issues' => array_slice($issues, 0, 5)];
    }

    /**
     * Check for simulation performance.
     */
    private function checkPerformance(): array
    {
        // Calculate avg simulation time per match from scheduler runs
        $recentRuns = \Illuminate\Support\Facades\DB::table('simulation_scheduler_runs')
            ->whereNotNull('finished_at')
            ->where('processed_matches', '>', 0)
            ->latest()
            ->limit(10)
            ->get();

        $avgTimePerMatch = 0;
        if ($recentRuns->count() > 0) {
            $totalMs = 0;
            $totalMatches = 0;
            foreach ($recentRuns as $run) {
                $duration = strtotime($run->finished_at) - strtotime($run->started_at);
                $totalMs += ($duration * 1000);
                $totalMatches += $run->processed_matches;
            }
            $avgTimePerMatch = $totalMatches > 0 ? $totalMs / $totalMatches : 0;
        }

        // Heavy matches check (count of actions)
        $heavyMatches = \App\Models\MatchLiveAction::select('match_id', \DB::raw('count(*) as action_count'))
            ->groupBy('match_id')
            ->having('action_count', '>', 250)
            ->limit(5)
            ->get();

        $issues = [];
        foreach ($heavyMatches as $m) {
            $issues[] = [
                'id' => $m->match_id,
                'description' => "Match {$m->match_id} has extreme action count: {$m->action_count}",
                'severity' => 'WARNING',
                'reason' => "High action density might increase simulation time and UI load."
            ];
        }

        if ($avgTimePerMatch > 500) { // More than 0.5s per match is slow
            $issues[] = [
                'description' => "Average simulation time is high: " . round($avgTimePerMatch, 2) . "ms/match",
                'severity' => 'WARNING',
                'reason' => "Match simulation engine is performing slower than expected."
            ];
        }

        return [
            'avg_time_ms' => round($avgTimePerMatch, 2),
            'avg_actions' => (int) (\App\Models\MatchLiveAction::count() / max(1, \App\Models\GameMatch::where('status', 'played')->count())),
            'issues' => $issues
        ];
    }

    /**
     * Check for inactive clubs (no lineup changes).
     */
    private function checkInactivity(): array
    {
        $inactiveClubs = \App\Models\Club::whereHas('lineups', function ($q) {
            $q->where('is_active', true)
                ->where('updated_at', '<', now()->subDays(7));
        })->get();

        $issues = [];
        foreach ($inactiveClubs as $c) {
            $issues[] = [
                'id' => $c->id,
                'name' => $c->name,
                'description' => "Manager hasn't updated lineup for over 7 days.",
                'severity' => 'WARNING',
                'reason' => "Possible inactive user or bot-manager not rotating players."
            ];
        }

        return ['count' => count($issues), 'issues' => array_slice($issues, 0, 5)];
    }

    /**
     * Diagnose a single match and return specific issues.
     */
    public function diagnoseMatch(\App\Models\GameMatch $match): array
    {
        $issues = [];

        // 1. Stuck Status Check
        if ($match->status === 'live' && $match->updated_at < now()->subMinutes(15)) {
            $issues[] = [
                'type' => 'stuck_live',
                'severity' => 'CRITICAL',
                'description' => "Match appears stuck in 'LIVE' status (no updates for 15+ mins).",
                'action_label' => "Match auf 'Played' setzen",
                'action_type' => 'match_status_fix'
            ];
        }

        // 2. Score Inconsistency
        $homeGoals = $match->events()->where('event_type', 'goal')->where('club_id', $match->home_club_id)->count();
        $awayGoals = $match->events()->where('event_type', 'goal')->where('club_id', $match->away_club_id)->count();
        if ($match->status === 'played' && ($homeGoals != $match->home_score || $awayGoals != $match->away_score)) {
            $issues[] = [
                'type' => 'score_mismatch',
                'severity' => 'WARNING',
                'description' => "Score mismatch: DB shows {$match->home_score}:{$match->away_score}, but events show {$homeGoals}:{$awayGoals}.",
                'action_label' => "Score aus Events syncen",
                'action_type' => 'match_score_sync'
            ];
        }

        // 3. Missing Events Check
        if ($match->status === 'played' && $match->events()->count() === 0) {
            $issues[] = [
                'type' => 'missing_events',
                'severity' => 'CRITICAL',
                'description' => "Match is 'Played' but has 0 events.",
                'action_label' => "Re-Simulations Queue",
                'action_type' => 'match_re_simulate'
            ];
        }

        // 4. Narrative / Ticker Text Check
        $brokenNarratives = $match->events()
            ->where(function ($q) {
                $q->whereNull('narrative')
                    ->orWhere('narrative', '')
                    ->orWhere('narrative', 'like', '%[%')
                    ->orWhere('narrative', 'like', '%]%');
            })->count();

        if ($brokenNarratives > 0) {
            $issues[] = [
                'type' => 'broken_narratives',
                'severity' => 'WARNING',
                'description' => "{$brokenNarratives} Events haben fehlerhafte oder unvollständige Ticker-Texte (Platzhalter erkannt).",
                'action_label' => "Match Re-Simulieren",
                'action_type' => 'match_re_simulate'
            ];
        }

        return $issues;
    }
}
