<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\ManagerPresence;

class LiveOverviewService
{
    public function onlineWindowMinutes(): int
    {
        return max(1, min(30, (int) config('simulation.modules.live_center.online_window_minutes', 5)));
    }

    public function overview(): array
    {
        $onlineManagers = $this->onlineManagers();
        $liveMatches = $this->liveMatches();

        return [
            'onlineManagers' => $onlineManagers,
            'onlineManagersCount' => count($onlineManagers),
            'liveMatches' => $liveMatches,
            'liveMatchesCount' => count($liveMatches),
            'onlineWindowMinutes' => $this->onlineWindowMinutes(),
        ];
    }

    public function onlineManagers(): array
    {
        $onlineWindow = now()->subMinutes($this->onlineWindowMinutes());

        return ManagerPresence::query()
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
                    'last_seen_at' => $presence->last_seen_at?->toIso8601String(),
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
            ->values()
            ->all();
    }

    public function liveMatches(): array
    {
        return GameMatch::query()
            ->with(['homeClub:id,name,logo_path', 'awayClub:id,name,logo_path'])
            ->where('status', 'live')
            ->orderByDesc('live_minute')
            ->get()
            ->map(fn (GameMatch $match) => $this->matchSummary($match))
            ->values()
            ->all();
    }

    public function matchSummary(GameMatch $match): array
    {
        $match->loadMissing(['homeClub:id,name,logo_path', 'awayClub:id,name,logo_path']);

        return [
            'id' => $match->id,
            'status' => (string) $match->status,
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
        ];
    }
}
