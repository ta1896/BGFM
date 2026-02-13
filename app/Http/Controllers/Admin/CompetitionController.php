<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function index(): View
    {
        $competitions = Competition::query()
            ->with('country')
            ->orderBy('type')
            ->orderBy('tier')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.competitions.index', [
            'competitions' => $competitions,
        ]);
    }

    public function create(): View
    {
        return view('admin.competitions.create', [
            'countries' => Country::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $validated = $this->handleLogoUpload($request, $validated);
        $competition = Competition::create($validated);

        return redirect()
            ->route('admin.competitions.edit', $competition)
            ->with('status', 'Liga/Pokal im ACP erstellt.');
    }

    public function show(Competition $competition): RedirectResponse
    {
        return redirect()->route('admin.competitions.edit', $competition);
    }

    public function edit(Competition $competition): View
    {
        return view('admin.competitions.edit', [
            'competition' => $competition,
            'countries' => Country::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $validated = $this->handleLogoUpload($request, $validated, $competition->logo_path);
        $competition->update($validated);

        return redirect()
            ->route('admin.competitions.edit', $competition)
            ->with('status', 'Liga/Pokal aktualisiert.');
    }

    public function destroy(Competition $competition): RedirectResponse
    {
        if ($competition->logo_path) {
            Storage::delete($competition->logo_path);
        }

        $competition->delete();

        return redirect()
            ->route('admin.competitions.index')
            ->with('status', 'Liga/Pokal wurde geloescht.');
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:16'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'type' => ['required', 'in:league,cup'],
            'scope' => ['nullable', 'in:national,international'],
            'tier' => ['nullable', 'integer', 'min:1', 'max:10'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (($validated['type'] ?? 'league') === 'cup') {
            $scope = (string) ($validated['scope'] ?? '');
            if ($scope === '') {
                $scope = !empty($validated['country_id']) ? 'national' : 'international';
            }

            $validated['scope'] = $scope;
            if ($scope === 'international') {
                $validated['country_id'] = null;
            }
        } else {
            $validated['scope'] = null;
        }

        return $validated;
    }

    private function handleLogoUpload(Request $request, array $validated, ?string $previousPath = null): array
    {
        if (!$request->hasFile('logo')) {
            unset($validated['logo']);
            $validated['is_active'] = $request->boolean('is_active');
            return $validated;
        }

        $path = $request->file('logo')->store('public/competition-logos');
        $validated['logo_path'] = $path;
        unset($validated['logo']);
        $validated['is_active'] = $request->boolean('is_active');

        if ($previousPath) {
            Storage::delete($previousPath);
        }

        return $validated;
    }
}
