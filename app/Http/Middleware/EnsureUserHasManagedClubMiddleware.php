<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasManagedClubMiddleware
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || $user->isAdmin()) {
            return $next($request);
        }

        if ($user->clubs()->exists()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Dieser Bereich ist erst verfuegbar, sobald du einen Verein uebernommen hast.',
            ], 403);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Du hast noch keinen Verein. Waehle zuerst einen freien Verein aus.');
    }
}
