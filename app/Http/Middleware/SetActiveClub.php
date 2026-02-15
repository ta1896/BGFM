<?php

namespace App\Http\Middleware;

use App\Models\Club;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetActiveClub
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $activeClub = null;

        if (!$user) {
            return $next($request);
        }

        // 1. Check Query Parameter and update Session if valid
        if ($request->has('club')) {
            $clubId = $request->input('club');
            Log::info("SetActiveClub: Request has club query param: $clubId");

            // Validate ownership/access
            $canAccess = false;
            if ($user->isAdmin()) {
                $canAccess = Club::whereKey($clubId)->exists();
            } else {
                $canAccess = $user->clubs()->whereKey($clubId)->exists();
            }

            if ($canAccess) {
                session(['active_club_id' => (int) $clubId]);
                Log::info("SetActiveClub: Session updated to $clubId");
            } else {
                Log::warning("SetActiveClub: Access denied or club not found for $clubId");
            }
        }

        // 2. Retrieve Active Club ID from Session
        $activeClubId = session('active_club_id');

        // 3. Try to fetch the club from DB based on Session
        if ($activeClubId) {
            if ($user->isAdmin()) {
                $activeClub = Club::find($activeClubId);
            } else {
                $activeClub = $user->clubs()->whereKey($activeClubId)->first();
            }
        }

        // 4. Try Default Club if no active club yet
        if (!$activeClub && $user->default_club_id) {
            if ($user->isAdmin()) {
                $activeClub = Club::find($user->default_club_id);
            } else {
                $activeClub = $user->clubs()->whereKey($user->default_club_id)->first();
            }

            if ($activeClub) {
                session(['active_club_id' => $activeClub->id]);
            }
        }

        // 5. Fallback: If no valid active club, pick the first one
        if (!$activeClub) {
            // Prefer non-cpu clubs first
            if ($user->isAdmin()) {
                $activeClub = Club::where('is_cpu', false)->first() ?? Club::first();
            } else {
                $activeClub = $user->clubs()->where('is_cpu', false)->first() ?? $user->clubs()->first();
            }

            // Sync session if we found a fallback
            if ($activeClub) {
                session(['active_club_id' => $activeClub->id]);
            }
        }

        // 6. Share with all views AND bind to container (Singleton for this request)
        if ($activeClub) {
            // Share with View
            View::share('activeClub', $activeClub);

            // Bind to Service Container for Controllers
            if (!app()->has('activeClub')) {
                app()->instance('activeClub', $activeClub);
            }
        }

        return $next($request);
    }
}
