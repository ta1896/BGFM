<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeasonController extends Controller
{
    public function index(): \Inertia\Response
    {
        $seasons = Season::orderByDesc('start_date')->get();
        return \Inertia\Inertia::render('Admin/Seasons/Index', [
            'seasons' => $seasons,
        ]);
    }

    public function create(): \Inertia\Response
    {
        return \Inertia\Inertia::render('Admin/Seasons/Form', [
            'season' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        if ($request->boolean('is_current')) {
            Season::where('is_current', true)->update(['is_current' => false]);
        }

        Season::create($validated);

        return redirect()->route('admin.seasons.index')->with('status', 'Saison erstellt.');
    }

    public function edit(Season $season): \Inertia\Response
    {
        return \Inertia\Inertia::render('Admin/Seasons/Form', [
            'season' => $season,
        ]);
    }

    public function update(Request $request, Season $season): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        if ($request->boolean('is_current')) {
            Season::where('is_current', true)
                ->where('id', '!=', $season->id)
                ->update(['is_current' => false]);
        }

        $season->update($validated);

        return redirect()->route('admin.seasons.index')->with('status', 'Saison aktualisiert.');
    }

    public function destroy(Season $season): RedirectResponse
    {
        $season->delete();
        return redirect()->route('admin.seasons.index')->with('status', 'Saison geloescht.');
    }
}
