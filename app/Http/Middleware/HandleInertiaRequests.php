<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

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
        
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;
        
        $userClubs = collect();
        if ($user) {
            if ($isAdmin) {
                $userClubs = \App\Models\Club::where('is_cpu', false)
                    ->orderBy('name')
                    ->get(['id', 'name', 'logo_path']);
            } else {
                $userClubs = $user->clubs()
                    ->where('is_cpu', false)
                    ->orderBy('name')
                    ->get(['id', 'name', 'logo_path']);
            }
        }

        if (!$activeClub && $userClubs->isNotEmpty()) {
            $activeClub = $userClubs->first();
        }

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
            'activeClub' => $activeClub ? [
                'id' => $activeClub->id,
                'name' => $activeClub->name,
                'logo_url' => $activeClub->logo_url,
            ] : null,
            'userClubs' => $userClubs->map(fn ($club) => [
                'id' => $club->id,
                'name' => $club->name,
                'logo_url' => $club->logo_url,
            ])->values(),
            'flash' => [
                'status' => session('status'),
            ],
        ];
    }
}
