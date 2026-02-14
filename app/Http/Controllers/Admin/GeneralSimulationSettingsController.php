<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SimulationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeneralSimulationSettingsController extends Controller
{
    public function index(SimulationSettingsService $simulationSettings): View
    {
        return view('admin.simulation.settings', [
            'simulationSettings' => $simulationSettings->adminSettings(),
        ]);
    }

    public function update(Request $request, SimulationSettingsService $simulationSettings): RedirectResponse
    {
        $validated = $request->validate([
            'simulation.scheduler.interval_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'simulation.scheduler.default_limit' => ['required', 'integer', 'min:0', 'max:500'],
            'simulation.scheduler.default_minutes_per_run' => ['required', 'integer', 'min:1', 'max:90'],
            'simulation.scheduler.default_types' => ['required', 'array', 'min:1'],
            'simulation.scheduler.default_types.*' => ['string', 'in:friendly,league,cup'],
            'simulation.scheduler.claim_stale_after_seconds' => ['required', 'integer', 'min:30', 'max:3600'],
            'simulation.scheduler.runner_lock_seconds' => ['required', 'integer', 'min:30', 'max:3600'],
            'simulation.position_fit.main' => ['required', 'numeric', 'between:0.50,1.20'],
            'simulation.position_fit.second' => ['required', 'numeric', 'between:0.50,1.20'],
            'simulation.position_fit.third' => ['required', 'numeric', 'between:0.50,1.20'],
            'simulation.position_fit.foreign' => ['required', 'numeric', 'between:0.30,1.20'],
            'simulation.position_fit.foreign_gk' => ['required', 'numeric', 'between:0.20,1.20'],
            'simulation.live_changes.planned_substitutions.max_per_club' => ['required', 'integer', 'min:1', 'max:5'],
            'simulation.live_changes.planned_substitutions.min_minutes_ahead' => ['required', 'integer', 'min:1', 'max:30'],
            'simulation.live_changes.planned_substitutions.min_interval_minutes' => ['required', 'integer', 'min:1', 'max:30'],
            'simulation.lineup.max_bench_players' => ['required', 'integer', 'min:1', 'max:10'],
            'simulation.observers.match_finished.enabled' => ['required', 'boolean'],
            'simulation.observers.match_finished.rebuild_match_player_stats' => ['required', 'boolean'],
            'simulation.observers.match_finished.aggregate_player_competition_stats' => ['required', 'boolean'],
            'simulation.observers.match_finished.apply_match_availability' => ['required', 'boolean'],
            'simulation.observers.match_finished.update_competition_after_match' => ['required', 'boolean'],
            'simulation.observers.match_finished.settle_match_finance' => ['required', 'boolean'],
        ]);

        $simulationSettings->updateFromAdminPayload($validated['simulation']);

        return redirect()
            ->route('admin.simulation.settings.index')
            ->with('status', 'Simulationskonfiguration gespeichert und aktiviert.');
    }
}
