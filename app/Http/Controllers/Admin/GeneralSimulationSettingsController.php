<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGeneralSimulationSettingsRequest;
use App\Modules\ModuleManager;
use App\Services\SimulationSettingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GeneralSimulationSettingsController extends Controller
{
    public function index(SimulationSettingsService $simulationSettings, ModuleManager $modules): Response
    {
        return Inertia::render('Admin/SimulationSettings/Index', [
            'simulationSettings' => $simulationSettings->adminSettings(),
            'moduleSettingsSections' => $modules->frontendRegistry()['settings_sections'] ?? [],
        ]);
    }

    public function update(UpdateGeneralSimulationSettingsRequest $request, SimulationSettingsService $simulationSettings): RedirectResponse
    {
        $validated = $request->validated();
        $moduleFieldDefinitions = $request->moduleFieldDefinitions();

        $simulationSettings->updateFromAdminPayload($validated['simulation']);
        $simulationSettings->persistModuleFieldValues(
            ['simulation' => $validated['simulation']],
            $moduleFieldDefinitions
        );

        return redirect()
            ->route('admin.simulation.settings.index')
            ->with('status', 'Simulationskonfiguration gespeichert und aktiviert.');
    }
}
