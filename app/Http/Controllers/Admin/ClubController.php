<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use App\Services\ClubFinanceLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
    public function store(Request $request, ClubFinanceLedgerService $financeLedger): RedirectResponse
    {
        $validated = $this->validatePayload($request, null);
        $validated = $this->handleLogoUpload($request, $validated);
        $targetBudget = round((float) ($validated['budget'] ?? 0), 2);
        $targetCoins = (int) ($validated['coins'] ?? 0);
        unset($validated['budget'], $validated['coins']);

        $club = DB::transaction(function () use ($validated, $targetBudget, $targetCoins, $request, $financeLedger): Club {
            $clubPayload = $validated;
            $clubPayload['budget'] = 0;
            $clubPayload['coins'] = 0;

            /** @var Club $club */
            $club = Club::create($clubPayload);

            if ($targetBudget > 0) {
                $financeLedger->applyBudgetChange($club, $targetBudget, [
                    'user_id' => $request->user()->id,
                    'context_type' => 'admin_adjustment',
                    'reference_type' => 'clubs',
                    'reference_id' => $club->id,
                    'note' => 'Initiales Vereinsbudget (Admin)',
                ]);
            }

            if ($targetCoins > 0) {
                $financeLedger->applyCoinChange($club, $targetCoins, [
                    'user_id' => $request->user()->id,
                    'context_type' => 'admin_adjustment',
                    'reference_type' => 'clubs',
                    'reference_id' => $club->id,
                    'note' => 'Initiale Vereinscoins (Admin)',
                ]);
            }

            return $club;
        });

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
    public function update(Request $request, Club $club, ClubFinanceLedgerService $financeLedger): RedirectResponse
    {
        $validated = $this->validatePayload($request, $club);
        $validated = $this->handleLogoUpload($request, $validated, $club->logo_path);
        $targetBudget = round((float) ($validated['budget'] ?? $club->budget), 2);
        $targetCoins = (int) ($validated['coins'] ?? $club->coins);
        unset($validated['budget'], $validated['coins']);

        DB::transaction(function () use ($club, $validated, $targetBudget, $targetCoins, $request, $financeLedger): void {
            /** @var Club $lockedClub */
            $lockedClub = Club::query()
                ->whereKey($club->id)
                ->lockForUpdate()
                ->firstOrFail();

            $budgetDelta = round($targetBudget - (float) $lockedClub->budget, 2);
            $coinDelta = $targetCoins - (int) $lockedClub->coins;

            $lockedClub->update($validated);

            if ($budgetDelta !== 0.0) {
                $financeLedger->applyBudgetChange($lockedClub, $budgetDelta, [
                    'user_id' => $request->user()->id,
                    'context_type' => 'admin_adjustment',
                    'reference_type' => 'clubs',
                    'reference_id' => $club->id,
                    'note' => 'Budgetanpassung (Admin)',
                ]);
            }

            if ($coinDelta !== 0) {
                $financeLedger->applyCoinChange($lockedClub, $coinDelta, [
                    'user_id' => $request->user()->id,
                    'context_type' => 'admin_adjustment',
                    'reference_type' => 'clubs',
                    'reference_id' => $club->id,
                    'note' => 'Coin-Anpassung (Admin)',
                ]);
            }
        });

        return redirect()
            ->route('admin.clubs.edit', $club)
            ->with('status', 'Verein aktualisiert.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Club $club): RedirectResponse
    {
        if ($club->logo_path) {
            Storage::delete($club->logo_path);
        }

        $club->delete();

        return redirect()
            ->route('admin.clubs.index')
            ->with('status', 'Verein wurde geloescht.');
    }

    private function validatePayload(Request $request, ?Club $club): array
    {
        $captainRules = ['nullable'];
        $viceCaptainRules = ['nullable', 'different:captain_player_id'];

        if ($club) {
            $captainRules[] = Rule::exists('players', 'id')->where(fn ($query) => $query->where('club_id', $club->id));
            $viceCaptainRules[] = Rule::exists('players', 'id')->where(fn ($query) => $query->where('club_id', $club->id));
        } else {
            $captainRules[] = Rule::in([null, '']);
            $viceCaptainRules[] = Rule::in([null, '']);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:12'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'country' => ['required', 'string', 'max:80'],
            'league' => ['required', 'string', 'max:120'],
            'founded_year' => ['nullable', 'integer', 'min:1850', 'max:'.date('Y')],
            'budget' => ['required', 'numeric', 'min:0'],
            'coins' => ['nullable', 'integer', 'min:0'],
            'wage_budget' => ['required', 'numeric', 'min:0'],
            'reputation' => ['required', 'integer', 'min:1', 'max:99'],
            'fan_mood' => ['required', 'integer', 'min:1', 'max:100'],
            'season_objective' => ['nullable', 'in:avoid_relegation,mid_table,promotion,title,cup_run'],
            'captain_player_id' => $captainRules,
            'vice_captain_player_id' => $viceCaptainRules,
            'is_cpu' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_cpu'] = $request->boolean('is_cpu');

        return $validated;
    }

    private function handleLogoUpload(Request $request, array $validated, ?string $previousPath = null): array
    {
        if (!$request->hasFile('logo')) {
            unset($validated['logo']);

            return $validated;
        }

        $path = $request->file('logo')->store('public/club-logos');
        $validated['logo_path'] = $path;
        unset($validated['logo']);

        if ($previousPath) {
            Storage::delete($previousPath);
        }

        return $validated;
    }
}
