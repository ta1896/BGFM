<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $clubs = Club::query()
            ->with('user')
            ->withCount(['players', 'lineups'])
            ->latest()
            ->paginate(20);

        return view('admin.clubs.index', ['clubs' => $clubs]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.clubs.create', [
            'users' => User::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $validated = $this->handleLogoUpload($request, $validated);
        $club = Club::create($validated);

        return redirect()
            ->route('admin.clubs.edit', $club)
            ->with('status', 'Verein im ACP erstellt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Club $club): RedirectResponse
    {
        return redirect()->route('admin.clubs.edit', $club);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Club $club): View
    {
        return view('admin.clubs.edit', [
            'club' => $club,
            'users' => User::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Club $club): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $validated = $this->handleLogoUpload($request, $validated, $club->logo_path);
        $club->update($validated);

        return redirect()
            ->route('admin.clubs.edit', $club)
            ->with('status', 'Verein aktualisiert.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Club $club): RedirectResponse
    {
        $club->delete();

        return redirect()
            ->route('admin.clubs.index')
            ->with('status', 'Verein wurde geloescht.');
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:12'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'country' => ['required', 'string', 'max:80'],
            'league' => ['required', 'string', 'max:120'],
            'founded_year' => ['nullable', 'integer', 'min:1850', 'max:'.date('Y')],
            'budget' => ['required', 'numeric', 'min:0'],
            'wage_budget' => ['required', 'numeric', 'min:0'],
            'reputation' => ['required', 'integer', 'min:1', 'max:99'],
            'fan_mood' => ['required', 'integer', 'min:1', 'max:100'],
            'is_cpu' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_cpu'] = $request->boolean('is_cpu');

        return $validated;
    }

    private function handleLogoUpload(Request $request, array $validated, ?string $previousPath = null): array
    {
        if (!$request->hasFile('logo')) {
            return $validated;
        }

        $path = $request->file('logo')->store('public/club-logos');
        $validated['logo_path'] = $path;

        if ($previousPath) {
            Storage::delete($previousPath);
        }

        return $validated;
    }
}
