<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionSeason;
use App\Services\SeasonProgressionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    public function processMatchday(Request $request, SeasonProgressionService $progressionService): RedirectResponse
    {
        $validated = $request->validate([
            'competition_season_id' => ['nullable', 'integer', 'exists:competition_seasons,id'],
        ]);

        $competitionSeason = null;
        if (!empty($validated['competition_season_id'])) {
            $competitionSeason = CompetitionSeason::find((int) $validated['competition_season_id']);
        }

        $summary = $progressionService->processNextMatchday($competitionSeason);

        $status = sprintf(
            'Spieltag-Prozess: %d Spiele simuliert, %d Finanz-Abrechnung(en), %d Saison(en) finalisiert, %d Aufstieg(e), %d Abstieg(e), %d Stadionprojekt(e) fertig, %d Trainingslager aktiviert, %d Trainingslager abgeschlossen, %d Leihe(n) beendet.',
            $summary['matches_simulated'],
            $summary['match_settlements'],
            $summary['seasons_finalized'],
            $summary['promotions'],
            $summary['relegations'],
            $summary['stadium_projects_completed'],
            $summary['training_camps_activated'],
            $summary['training_camps_completed'],
            $summary['loans_completed']
        );

        return redirect()
            ->route('admin.dashboard')
            ->with('status', $status);
    }
}
