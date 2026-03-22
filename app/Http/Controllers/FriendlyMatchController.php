<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\FriendlyMatchRequest;
use App\Models\GameMatch;
use App\Services\FriendlyMatchService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class FriendlyMatchController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;
        if (!$activeClub) {
            $activeClub = $request->user()->isAdmin()
                ? Club::query()->where('is_cpu', false)->orderBy('name')->first()
                : $request->user()->clubs()->where('is_cpu', false)->orderBy('name')->first();
        }

        $outgoing = collect();
        $incoming = collect();
        $matches = collect();
        $opponents = collect();

        if ($activeClub) {
            $opponents = Club::query()
                ->whereKeyNot($activeClub->id)
                ->orderByDesc('is_cpu')
                ->orderByDesc('reputation')
                ->orderBy('name')
                ->limit(50)
                ->get(['id', 'name', 'is_cpu']);

            $outgoing = FriendlyMatchRequest::query()
                ->with(['challengerClub', 'challengedClub', 'acceptedMatch'])
                ->where('challenger_club_id', $activeClub->id)
                ->latest('id')
                ->limit(20)
                ->get();

            $incoming = FriendlyMatchRequest::query()
                ->with(['challengerClub', 'challengedClub', 'acceptedMatch'])
                ->where('challenged_club_id', $activeClub->id)
                ->latest('id')
                ->limit(20)
                ->get();

            $matches = GameMatch::query()
                ->with(['homeClub', 'awayClub'])
                ->where('type', 'friendly')
                ->where(function ($query) use ($activeClub): void {
                    $query->where('home_club_id', $activeClub->id)
                        ->orWhere('away_club_id', $activeClub->id);
                })
                ->orderBy('kickoff_at')
                ->limit(30)
                ->get();
        }

        return \Inertia\Inertia::render('Friendlies/Index', [
            'activeClub' => $activeClub ? [
                'id' => $activeClub->id,
                'name' => $activeClub->name,
            ] : null,
            'opponents' => $opponents,
            'outgoingRequests' => $outgoing->map(fn (FriendlyMatchRequest $request) => [
                'id' => $request->id,
                'status' => $request->status,
                'message' => $request->message,
                'challenged_club' => $request->challengedClub ? [
                    'id' => $request->challengedClub->id,
                    'name' => $request->challengedClub->name,
                ] : null,
                'accepted_match' => $request->acceptedMatch ? [
                    'kickoff_at' => $request->acceptedMatch->kickoff_at?->format('d.m.Y H:i'),
                ] : null,
            ])->values(),
            'incomingRequests' => $incoming->map(fn (FriendlyMatchRequest $request) => [
                'id' => $request->id,
                'status' => $request->status,
                'message' => $request->message,
                'challenger_club' => $request->challengerClub ? [
                    'id' => $request->challengerClub->id,
                    'name' => $request->challengerClub->name,
                ] : null,
                'accepted_match' => $request->acceptedMatch ? [
                    'kickoff_at' => $request->acceptedMatch->kickoff_at?->format('d.m.Y H:i'),
                ] : null,
            ])->values(),
            'friendlyMatches' => $matches->map(function($m) use ($activeClub) {
                return [
                    'id' => $m->id,
                    'status' => $m->status,
                    'home_score' => $m->home_score,
                    'away_score' => $m->away_score,
                    'kickoff_formatted' => $m->kickoff_at?->format('d.m.Y H:i'),
                    'is_home' => $m->home_club_id === $activeClub?->id,
                    'home_club' => $m->homeClub ? [
                        'name' => $m->homeClub->name,
                        'short_name' => $m->homeClub->short_name,
                        'logo_url' => $m->homeClub->logo_url,
                    ] : null,
                    'away_club' => $m->awayClub ? [
                        'name' => $m->awayClub->name,
                        'short_name' => $m->awayClub->short_name,
                        'logo_url' => $m->awayClub->logo_url,
                    ] : null,
                ];
            })->values(),
        ]);
    }

    public function store(Request $request, FriendlyMatchService $service): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'opponent_club_id' => ['required', 'integer', 'exists:clubs,id'],
            'kickoff_at' => ['required', 'date'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $club = $request->user()->clubs()->whereKey((int) $validated['club_id'])->first();
        abort_unless($club, 403);

        $opponent = Club::query()->find((int) $validated['opponent_club_id']);
        abort_unless($opponent, 404, 'Gegner nicht gefunden.');

        if ($club->id === $opponent->id) {
            return back()->withErrors(['opponent_club_id' => 'Du kannst nicht gegen dich selbst spielen.']);
        }

        $kickoffAt = Carbon::parse($validated['kickoff_at']);

        // Relaxed validation: Just check if it's in the future (even 1 min is fine for testing)
        if ($kickoffAt->isPast()) {
            return back()->withErrors(['kickoff_at' => 'Der Anstoss muss in der Zukunft liegen.']);
        }

        try {
            $result = $service->createRequest(
                $club,
                $opponent,
                $request->user(),
                $kickoffAt,
                $validated['message'] ?? null
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Fehler beim Erstellen der Anfrage: ' . $e->getMessage()]);
        }

        $status = $result['type'] === 'auto_accepted'
            ? 'Freundschaftsspiel terminiert!'
            : 'Anfrage wurde an ' . $opponent->name . ' gesendet.';

        return redirect()
            ->route('friendlies.index', ['club' => $club->id])
            ->with('status', $status);
    }

    public function accept(
        Request $request,
        FriendlyMatchRequest $friendlyRequest,
        FriendlyMatchService $service
    ): RedirectResponse {
        $club = $request->user()->clubs()->whereKey($friendlyRequest->challenged_club_id)->first();
        abort_unless($club, 403);

        try {
            $match = $service->acceptRequest($friendlyRequest, $request->user());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('matches.show', $match)
            ->with('status', 'Freundschaftsspiel wurde bestaetigt.');
    }

    public function reject(
        Request $request,
        FriendlyMatchRequest $friendlyRequest,
        FriendlyMatchService $service
    ): RedirectResponse {
        $club = $request->user()->clubs()->whereKey($friendlyRequest->challenged_club_id)->first();
        abort_unless($club, 403);

        try {
            $service->rejectRequest($friendlyRequest);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('friendlies.index', ['club' => $club->id])
            ->with('status', 'Anfrage wurde abgelehnt.');
    }
}
