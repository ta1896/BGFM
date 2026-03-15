<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\ModuleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function index(ModuleManager $moduleManager): Response
    {
        return Inertia::render('Admin/Modules/Index', [
            'modules' => $moduleManager->adminRegistry(),
        ]);
    }

    public function update(Request $request, string $module, ModuleManager $moduleManager): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $moduleManager->setEnabled($module, (bool) $validated['enabled']);

        return back()->with('status', 'Modulstatus gespeichert. Ein Reload nutzt den neuen Zustand.');
    }
}
