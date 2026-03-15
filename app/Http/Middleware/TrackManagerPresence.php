<?php

namespace App\Http\Middleware;

use App\Events\LiveOverviewUpdated;
use App\Models\ManagerPresence;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackManagerPresence
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if (!$user || !$request->route()) {
            return $response;
        }

        $routeName = $request->route()?->getName();
        if (!$routeName) {
            return $response;
        }

        $club = app()->has('activeClub') ? app('activeClub') : $user->clubs()->first();
        $matchParameter = $request->route('match');
        $matchId = null;

        if (is_object($matchParameter) && isset($matchParameter->id)) {
            $matchId = (int) $matchParameter->id;
        } elseif (is_numeric($matchParameter)) {
            $matchId = (int) $matchParameter;
        }

        ManagerPresence::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'club_id' => $club?->id,
                'match_id' => $matchId && $matchId > 0 ? $matchId : null,
                'route_name' => $routeName,
                'path' => '/'.ltrim($request->path(), '/'),
                'activity_label' => $this->activityLabel($routeName),
                'last_seen_at' => now(),
            ]
        );

        broadcast(new LiveOverviewUpdated(app(\App\Services\LiveOverviewService::class)->overview()));

        return $response;
    }

    private function activityLabel(string $routeName): string
    {
        return match (true) {
            str_starts_with($routeName, 'dashboard') => 'Im Dashboard',
            str_starts_with($routeName, 'matches.show') => 'Im Matchcenter',
            str_starts_with($routeName, 'matches.live') => 'Im Live-Match',
            str_starts_with($routeName, 'league.matches') => 'Im Spielplan',
            str_starts_with($routeName, 'lineups') => 'An der Aufstellung',
            str_starts_with($routeName, 'players.show') => 'Im Spielerprofil',
            str_starts_with($routeName, 'players.') => 'Im Kader',
            str_starts_with($routeName, 'training') => 'Im Training',
            str_starts_with($routeName, 'medical') => 'Im Medical Center',
            str_starts_with($routeName, 'scouting') => 'Im Scouting',
            str_starts_with($routeName, 'finances') => 'In den Finanzen',
            str_starts_with($routeName, 'notifications') => 'Im Postfach',
            str_starts_with($routeName, 'team-of-the-day') => 'Bei Team der Woche',
            str_starts_with($routeName, 'statistics') => 'In den Statistiken',
            str_starts_with($routeName, 'awards') => 'Bei den Awards',
            str_starts_with($routeName, 'friendlies') => 'Bei Freundschaftsspielen',
            str_starts_with($routeName, 'manager-live') => 'Beobachtet die Manager-Live-Ansicht',
            str_starts_with($routeName, 'live-ticker') => 'Beobachtet den Live-Ticker',
            default => 'Navigiert durch den Verein',
        };
    }
}
