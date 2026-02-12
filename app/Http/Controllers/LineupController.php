<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Lineup;
use App\Services\TeamStrengthCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LineupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $lineups = Lineup::query()
            ->whereHas('club', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['club', 'players'])
            ->orderByDesc('is_active')
            ->latest()
            ->get();

        return view('lineups.index', [
            'lineups' => $lineups,
            'clubs' => $request->user()->clubs()->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return view('lineups.create', [
            'clubs' => $request->user()->clubs()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('lineups', 'name')->where(fn ($query) => $query->where('club_id', $request->input('club_id'))),
            ],
            'formation' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $club = $this->ownedClub($request, (int) $validated['club_id']);
        $isActive = (bool) ($validated['is_active'] ?? false);

        if ($isActive) {
            $club->lineups()->update(['is_active' => false]);
        }

        $lineup = $club->lineups()->create([
            'name' => $validated['name'],
            'formation' => $validated['formation'],
            'notes' => $validated['notes'] ?? null,
            'is_active' => $isActive || !$club->lineups()->where('is_active', true)->exists(),
        ]);

        return redirect()
            ->route('lineups.edit', $lineup)
            ->with('status', 'Aufstellung angelegt. Jetzt Spieler zuweisen.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Lineup $lineup, TeamStrengthCalculator $calculator): View
    {
        $this->ensureOwnership($request, $lineup);

        $lineup->load(['club', 'players']);
        $metrics = $calculator->calculate($lineup);

        return view('lineups.show', [
            'lineup' => $lineup,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Lineup $lineup): View
    {
        $this->ensureOwnership($request, $lineup);

        $lineup->load(['club', 'players']);
        $players = $lineup->club->players()->orderByDesc('overall')->get();

        return view('lineups.edit', [
            'lineup' => $lineup,
            'players' => $players,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lineup $lineup): RedirectResponse
    {
        $this->ensureOwnership($request, $lineup);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('lineups', 'name')
                    ->where(fn ($query) => $query->where('club_id', $lineup->club_id))
                    ->ignore($lineup->id),
            ],
            'formation' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'selected_players' => ['array'],
            'selected_players.*' => ['integer', 'exists:players,id'],
            'pitch_positions' => ['array'],
            'pitch_positions.*' => ['nullable', 'string', 'max:20'],
        ]);

        $selectedPlayers = collect($validated['selected_players'] ?? [])
            ->map(static fn ($value) => (int) $value)
            ->unique()
            ->values();

        if ($selectedPlayers->count() > 11) {
            return back()
                ->withInput()
                ->withErrors(['selected_players' => 'Du kannst maximal 11 Spieler aufstellen.']);
        }

        $clubPlayerIds = $lineup->club->players()->pluck('id');
        abort_if($selectedPlayers->diff($clubPlayerIds)->isNotEmpty(), 403);

        $pivotData = $selectedPlayers
            ->mapWithKeys(function (int $playerId, int $index) use ($request) {
                return [
                    $playerId => [
                        'pitch_position' => $request->input("pitch_positions.$playerId"),
                        'sort_order' => $index,
                        'is_bench' => false,
                        'bench_order' => null,
                    ],
                ];
            })
            ->all();

        $isActive = (bool) ($validated['is_active'] ?? false);
        if ($isActive) {
            $lineup->club->lineups()->update(['is_active' => false]);
        }

        $lineup->update([
            'name' => $validated['name'],
            'formation' => $validated['formation'],
            'notes' => $validated['notes'] ?? null,
            'is_active' => $isActive || $lineup->is_active,
        ]);

        $lineup->players()->sync($pivotData);

        return redirect()
            ->route('lineups.show', $lineup)
            ->with('status', 'Aufstellung aktualisiert.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Lineup $lineup): RedirectResponse
    {
        $this->ensureOwnership($request, $lineup);

        $club = $lineup->club;
        $wasActive = $lineup->is_active;

        $lineup->delete();

        if ($wasActive) {
            $club->lineups()->latest()->first()?->update(['is_active' => true]);
        }

        return redirect()
            ->route('lineups.index')
            ->with('status', 'Aufstellung wurde entfernt.');
    }

    public function activate(Request $request, Lineup $lineup): RedirectResponse
    {
        $this->ensureOwnership($request, $lineup);

        $lineup->club->lineups()->update(['is_active' => false]);
        $lineup->update(['is_active' => true]);

        return redirect()
            ->route('lineups.index')
            ->with('status', 'Aufstellung ist jetzt aktiv.');
    }

    private function ensureOwnership(Request $request, Lineup $lineup): void
    {
        abort_unless($lineup->club()->where('user_id', $request->user()->id)->exists(), 403);
    }

    private function ownedClub(Request $request, int $clubId): Club
    {
        $club = $request->user()->clubs()->whereKey($clubId)->first();
        abort_unless($club, 403);

        return $club;
    }
}
