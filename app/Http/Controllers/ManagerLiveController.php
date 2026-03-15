<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\ManagerPresence;
use Inertia\Inertia;
use Inertia\Response;

class ManagerLiveController extends Controller
{
    public function index(): Response
    {
        $onlineWindow = now()->subMinutes(5);

        $managers = ManagerPresence::query()
            ->with([
                'user:id,name',
                'club:id,name,logo_path',
                'match:id,status,live_minute,home_club_id,away_club_id',
                'match.homeClub:id,name,logo_path',
                'match.awayClub:id,name,logo_path',
            ])
            ->where('last_seen_at', '>=', $onlineWindow)
            ->whereHas('user', fn ($query) => $query->where('is_admin', false))
            ->orderByDesc('last_seen_at')
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
                    'route_name' => $presence->route_name,
                    'path' => $presence->path,
                    'last_seen_label' => $presence->last_seen_at?->diffForHumans(),
                    'match' => $presence->match ? [
                        'id' => $presence->match->id,
                        'status' => $presence->match->status,
                        'live_minute' => $presence->match->live_minute,
                        'home_club' => $presence->match->homeClub ? [
                            'name' => $presence->match->homeClub->name,
                            'logo_url' => $presence->match->homeClub->logo_url,
                        ] : null,
                        'away_club' => $presence->match->awayClub ? [
                            'name' => $presence->match->awayClub->name,
                            'logo_url' => $presence->match->awayClub->logo_url,
                        ] : null,
                    ] : null,
                ];
            })
            ->values();

        $liveMatches = GameMatch::query()
            ->with(['homeClub:id,name,logo_path', 'awayClub:id,name,logo_path'])
            ->where('status', 'live')
            ->orderByDesc('live_minute')
            ->get()
            ->map(fn (GameMatch $match) => [
                'id' => $match->id,
                'live_minute' => (int) ($match->live_minute ?? 0),
                'home_score' => (int) ($match->home_score ?? 0),
                'away_score' => (int) ($match->away_score ?? 0),
                'home_club' => $match->homeClub ? [
                    'name' => $match->homeClub->name,
                    'logo_url' => $match->homeClub->logo_url,
                ] : null,
                'away_club' => $match->awayClub ? [
                    'name' => $match->awayClub->name,
                    'logo_url' => $match->awayClub->logo_url,
                ] : null,
            ])
            ->values();

        return Inertia::render('Managers/Live', [
            'onlineManagers' => $managers,
            'liveMatches' => $liveMatches,
            'onlineWindowMinutes' => 5,
        ]);
    }
}
