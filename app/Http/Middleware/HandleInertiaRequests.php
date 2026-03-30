<?php

namespace App\Http\Middleware;

use App\Models\GameMatch;
use App\Modules\ModuleManager;
use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Club;
use App\Models\NavigationItem;
use Illuminate\Support\Collection;

class HandleInertiaRequests extends Middleware
{
    /** Memoized per-request result of resolveUserClubs() to avoid double DB query. */
    private ?Collection $memoizedClubs = null;
    private ?bool $memoizedClubsAdmin = null;

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
            'live' => [
                'matches_count' => fn () => $user ? GameMatch::query()->where('status', 'live')->count() : 0,
            ],
            'features' => [
                'player_conversations_enabled' => (bool) config('simulation.features.player_conversations_enabled', false),
            ],
            'modules' => fn () => app(ModuleManager::class)->frontendRegistry(),
            'navigation' => [
                'admin' => $isAdmin ? \Illuminate\Support\Facades\Cache::rememberForever('navigation_admin', function () {
                    return \App\Models\NavigationItem::with('children')
                        ->whereNull('parent_id')
                        ->where('group', 'admin')
                        ->orderBy('sort_order')
                        ->get();
                }) : [],
                'manager' => \Illuminate\Support\Facades\Cache::rememberForever('navigation_manager', function () {
                    return \App\Models\NavigationItem::with('children')
                        ->whereNull('parent_id')
                        ->whereIn('group', ['manager', 'manager_with_club', 'manager_without_club'])
                        ->orderBy('sort_order')
                        ->get();
                }),
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
        // Memoize within the same request: sharedActiveClub() and sharedUserClubs()
        // both call this method, so without memoization it would run two DB queries.
        if ($this->memoizedClubs !== null && $this->memoizedClubsAdmin === $isAdmin) {
            return $this->memoizedClubs;
        }

        $user = $request->user();

        if (!$user) {
            return $this->memoizedClubs = collect();
        }

        if ($isAdmin) {
            $result = Club::query()
                ->where('is_cpu', false)
                ->orderBy('name')
                ->get();
        } else {
            $result = $user->clubs()
                ->where('is_cpu', false)
                ->orderBy('name')
                ->get();
        }

        $this->memoizedClubsAdmin = $isAdmin;

        return $this->memoizedClubs = $result;
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
