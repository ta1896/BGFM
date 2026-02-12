<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\RandomEventOccurrence;
use App\Services\RandomEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RandomEventController extends Controller
{
    public function index(Request $request, RandomEventService $eventService): View
    {
        $clubs = $request->user()->clubs()->orderBy('name')->get();
        $activeClub = $clubs->firstWhere('id', (int) $request->query('club')) ?? $clubs->first();

        $occurrences = collect();
        $templates = collect();

        if ($activeClub) {
            $occurrences = RandomEventOccurrence::query()
                ->with(['template', 'player'])
                ->where('club_id', $activeClub->id)
                ->orderByDesc('happened_on')
                ->orderByDesc('id')
                ->limit(25)
                ->get();

            $templates = $eventService->availableTemplatesForClub($activeClub)->take(8);
        }

        return view('random-events.index', [
            'clubs' => $clubs,
            'activeClub' => $activeClub,
            'occurrences' => $occurrences,
            'templates' => $templates,
        ]);
    }

    public function trigger(Request $request, RandomEventService $eventService): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
        ]);

        $club = $this->authorizedClub($request, (int) $validated['club_id']);

        $event = $eventService->triggerForClub($club, $request->user());
        abort_if(!$event, 422, 'Kein passendes Zufallsereignis verfuegbar.');

        return redirect()
            ->route('random-events.index', ['club' => $club->id])
            ->with('status', 'Neues Zufallsereignis erzeugt: '.$event->title);
    }

    public function apply(
        Request $request,
        RandomEventOccurrence $occurrence,
        RandomEventService $eventService
    ): RedirectResponse {
        $club = $this->authorizedClub($request, (int) $occurrence->club_id);

        $eventService->apply($occurrence);

        return redirect()
            ->route('random-events.index', ['club' => $club->id])
            ->with('status', 'Zufallsereignis angewendet.');
    }

    private function authorizedClub(Request $request, int $clubId): Club
    {
        if ($request->user()->isAdmin()) {
            return Club::query()->findOrFail($clubId);
        }

        return $request->user()->clubs()->whereKey($clubId)->firstOrFail();
    }
}
