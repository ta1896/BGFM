<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\FriendlyMatchRequest;
use App\Models\GameMatch;
use App\Services\FriendlyMatchService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FriendlyMatchController extends Controller
{
    public function index(Request $request): View
    {
        $clubs = $request->user()->clubs()->orderBy('name')->get();
        $activeClub = $clubs->firstWhere('id', (int) $request->query('club')) ?? $clubs->first();

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
                ->get();

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

        return view('friendlies.index', [
            'clubs' => $clubs,
            'activeClub' => $activeClub,
            'opponents' => $opponents,
            'outgoingRequests' => $outgoing,
            'incomingRequests' => $incoming,
            'friendlyMatches' => $matches,
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

        $opponent = Club::query()->findOrFail((int) $validated['opponent_club_id']);
        abort_if($club->id === $opponent->id, 422, 'Bitte einen anderen Gegner waehlen.');

        $kickoffAt = Carbon::parse($validated['kickoff_at']);
        abort_if($kickoffAt->lessThan(now()->addMinutes(15)), 422, 'Der Anstoss muss in der Zukunft liegen.');

        $result = $service->createRequest(
            $club,
            $opponent,
            $request->user(),
            $kickoffAt,
            $validated['message'] ?? null
        );

        $status = $result['type'] === 'auto_accepted'
            ? 'Freundschaftsspiel wurde automatisch angenommen und terminiert.'
            : 'Anfrage wurde versendet.';

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

        $match = $service->acceptRequest($friendlyRequest, $request->user());

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

        $service->rejectRequest($friendlyRequest);

        return redirect()
            ->route('friendlies.index', ['club' => $club->id])
            ->with('status', 'Anfrage wurde abgelehnt.');
    }
}
