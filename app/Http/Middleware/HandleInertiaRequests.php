<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Club;
use Illuminate\Support\Collection;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $isAdmin = $user?->isAdmin() ?? false;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'default_club_id' => $user->default_club_id,
                    'is_admin' => $isAdmin,
                ] : null,
                'isAdmin' => $isAdmin,
                'theme' => $user?->theme ?? 'catalyst',
            ],
            'activeClub' => fn () => $this->sharedActiveClub($request),
            'userClubs' => fn () => $this->sharedUserClubs($request, $isAdmin),
            'flash' => [
                'status' => session('status'),
            ],
            'features' => [
                'player_conversations_enabled' => (bool) config('simulation.features.player_conversations_enabled', false),
            ],
        ];
    }

    private function sharedActiveClub(Request $request): ?array
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;

        if (!$activeClub) {
            $clubs = $this->resolveUserClubs($request, $request->user()?->isAdmin() ?? false);
            $activeClub = $clubs->first();
        }

        return $activeClub ? $this->formatClubForShare($activeClub) : null;
    }

    private function sharedUserClubs(Request $request, bool $isAdmin): array
    {
        return $this->resolveUserClubs($request, $isAdmin)
            ->map(fn (Club $club) => $this->formatClubForShare($club))
            ->values()
            ->all();
    }

    private function resolveUserClubs(Request $request, bool $isAdmin): Collection
    {
        $user = $request->user();

        if (!$user) {
            return collect();
        }

        if ($isAdmin) {
            return Club::query()
                ->where('is_cpu', false)
                ->orderBy('name')
                ->get();
        }

        return $user->clubs()
            ->where('is_cpu', false)
            ->orderBy('name')
            ->get();
    }

    private function formatClubForShare(Club $club): array
    {
        return [
            'id' => $club->id,
            'name' => $club->name,
            'logo_url' => $club->logo_url,
        ];
    }
}
