<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Lineup;
use App\Services\TeamStrengthCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LineupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $lineups = Lineup::query()
            ->with(['club.user', 'players'])
            ->orderByDesc('is_active')
            ->latest()
            ->paginate(20);

        return view('admin.lineups.index', [
            'lineups' => $lineups,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.lineups.create', [
            'clubs' => Club::with('user')->orderBy('name')->get(),
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

        $club = Club::findOrFail((int) $validated['club_id']);
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
            ->route('admin.lineups.edit', $lineup)
            ->with('status', 'Aufstellung im ACP erstellt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lineup $lineup, TeamStrengthCalculator $calculator): View
    {
        $lineup->load(['club.user', 'players']);

        return view('admin.lineups.show', [
            'lineup' => $lineup,
            'metrics' => $calculator->calculate($lineup),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lineup $lineup): View
    {
        $lineup->load(['club.user', 'players']);

        return view('admin.lineups.edit', [
            'lineup' => $lineup,
            'players' => $lineup->club->players()->orderByDesc('overall')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lineup $lineup): RedirectResponse
    {
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
        abort_if($selectedPlayers->diff($clubPlayerIds)->isNotEmpty(), 422);

        $pivotData = $selectedPlayers
            ->mapWithKeys(function (int $playerId, int $index) use ($request) {
                return [
                    $playerId => [
                        'pitch_position' => $request->input("pitch_positions.$playerId"),
                        'sort_order' => $index,
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
            ->route('admin.lineups.show', $lineup)
            ->with('status', 'Aufstellung aktualisiert.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lineup $lineup): RedirectResponse
    {
        $club = $lineup->club;
        $wasActive = $lineup->is_active;

        $lineup->delete();

        if ($wasActive) {
            $club->lineups()->latest()->first()?->update(['is_active' => true]);
        }

        return redirect()
            ->route('admin.lineups.index')
            ->with('status', 'Aufstellung geloescht.');
    }

    public function activate(Lineup $lineup): RedirectResponse
    {
        $lineup->club->lineups()->update(['is_active' => false]);
        $lineup->update(['is_active' => true]);

        return redirect()
            ->route('admin.lineups.index')
            ->with('status', 'Aufstellung ist jetzt aktiv.');
    }
}
