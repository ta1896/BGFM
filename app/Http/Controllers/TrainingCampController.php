<?php

namespace App\Http\Controllers;

use App\Models\TrainingCamp;
use App\Services\TrainingCampService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingCampController extends Controller
{
    public function index(Request $request): View
    {
        $clubs = $request->user()->clubs()->orderBy('name')->get();
        $clubIds = $clubs->pluck('id')->all();

        $camps = TrainingCamp::query()
            ->with('club')
            ->whereIn('club_id', $clubIds)
            ->orderByDesc('id')
            ->paginate(15);

        return view('training-camps.index', [
            'clubs' => $clubs,
            'camps' => $camps,
        ]);
    }

    public function store(Request $request, TrainingCampService $trainingCampService): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'name' => ['required', 'string', 'max:120'],
            'focus' => ['required', 'in:fitness,tactics,technical,team_building'],
            'intensity' => ['required', 'in:low,medium,high'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $club = $request->user()->clubs()->whereKey((int) $validated['club_id'])->first();
        abort_unless($club, 403);

        $trainingCampService->createCamp($club, $request->user(), $validated);

        return redirect()->route('training-camps.index')->with('status', 'Trainingslager erstellt.');
    }
}
