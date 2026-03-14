<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Services\StadiumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StadiumController extends Controller
{
    public function index(Request $request, StadiumService $stadiumService): \Inertia\Response
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;
        $clubs = $request->user()->isAdmin() 
            ? \App\Models\Club::where('is_cpu', false)->orderBy('name')->get()
            : $request->user()->clubs()->orderBy('name')->get();

        if (!$activeClub && $clubs->isNotEmpty()) {
            $activeClub = $clubs->first();
        }

        $stadium = null;
        $projects = collect();
        if ($activeClub) {
            $stadium = $stadiumService->ensureForClub($activeClub);
            $projects = $stadium->projects()
                ->latest('id')
                ->limit(12)
                ->get()
                ->map(function ($p) {
                    $p->started_on_formatted = $p->started_on?->format('d.m.Y');
                    $p->completes_on_formatted = $p->completes_on?->format('d.m.Y');
                    return $p;
                });
        }

        return \Inertia\Inertia::render('Stadium/Index', [
            'clubs' => $clubs,
            'activeClub' => $activeClub,
            'stadium' => $stadium,
            'projects' => $projects,
            'projectTypes' => [
                'capacity' => 'Kapazitaet',
                'pitch' => 'Rasen',
                'facility' => 'Anlagen',
                'security' => 'Sicherheit',
                'environment' => 'Umfeld',
                'vip' => 'VIP',
            ],
        ]);
    }

    public function storeProject(Request $request, StadiumService $stadiumService): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'project_type' => ['required', 'in:capacity,pitch,facility,security,environment,vip'],
        ]);

        /** @var Club|null $club */
        $club = $request->user()->clubs()->whereKey((int) $validated['club_id'])->first();
        abort_unless($club, 403);

        $stadiumService->startProject($club, $request->user(), (string) $validated['project_type']);

        return redirect()
            ->route('stadium.index', ['club' => $club->id])
            ->with('status', 'Stadionprojekt gestartet.');
    }
}
