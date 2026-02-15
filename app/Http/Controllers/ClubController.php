<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Season;
use App\Services\ClubFinanceLedgerService;
use App\Services\StatisticsAggregationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $clubs = $request->user()
            ->clubs()
            ->withCount(['players', 'lineups'])
            ->latest()
            ->get();

        return view('clubs.index', ['clubs' => $clubs]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('clubs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ClubFinanceLedgerService $financeLedger): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:12'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'country' => ['required', 'string', 'max:80'],
            'league' => ['required', 'string', 'max:120'],
            'founded_year' => ['nullable', 'integer', 'min:1850', 'max:' . date('Y')],
            'budget' => ['required', 'numeric', 'min:0'],
            'coins' => ['nullable', 'integer', 'min:0'],
            'wage_budget' => ['required', 'numeric', 'min:0'],
            'reputation' => ['required', 'integer', 'min:1', 'max:99'],
            'fan_mood' => ['required', 'integer', 'min:1', 'max:100'],
            'season_objective' => ['nullable', 'in:avoid_relegation,mid_table,promotion,title,cup_run'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $validated = $this->handleLogoUpload($request, $validated);

        $targetBudget = round((float) ($validated['budget'] ?? 0), 2);
        $targetCoins = (int) ($validated['coins'] ?? 0);

        unset($validated['budget'], $validated['coins']);

        $club = DB::transaction(function () use ($request, $validated, $targetBudget, $targetCoins, $financeLedger): Club {
            $clubPayload = $validated;
            $clubPayload['budget'] = 0;
            $clubPayload['coins'] = 0;

            /** @var Club $club */
            $club = $request->user()->clubs()->create($clubPayload);

            if ($targetBudget > 0) {
                $financeLedger->applyBudgetChange($club, $targetBudget, [
                    'user_id' => $request->user()->id,
                    'context_type' => 'admin_adjustment',
                    'reference_type' => 'clubs',
                    'reference_id' => $club->id,
                    'note' => 'Initiales Vereinsbudget',
                ]);
            }

            if ($targetCoins > 0) {
                $financeLedger->applyCoinChange($club, $targetCoins, [
                    'user_id' => $request->user()->id,
                    'context_type' => 'admin_adjustment',
                    'reference_type' => 'clubs',
                    'reference_id' => $club->id,
                    'note' => 'Initiale Vereinscoins',
                ]);
            }

            return $club;
        });

        return redirect()
            ->route('clubs.show', $club)
            ->with('status', 'Verein erfolgreich angelegt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Club $club, StatisticsAggregationService $statisticsAggregationService): View
    {
        // Removed ensureOwnership to allow scouting other clubs

        $club->load([
            'players' => fn($query) => $query->orderByRaw("FIELD(position, 'TW', 'LV', 'IV', 'RV', 'DM', 'LM', 'ZM', 'RM', 'OM', 'LF', 'HS', 'MS', 'RF')")->orderByDesc('overall'),
            'user',
            'stadium',
            'captain',
            'viceCaptain',
            'achievements.competitionSeason.competition',
            'achievements.competitionSeason.season',
        ]);

        $seasonId = (int) $request->query('season_id');
        $seasons = Season::orderByDesc('id')->get();
        $activeSeason = $seasonId > 0
            ? $seasons->firstWhere('id', $seasonId)
            : $seasons->first();

        // --- Statistics for Overview Tab ---
        $overallStats = $statisticsAggregationService->clubSummaryForClub($club, null);
        $seasonStats = $statisticsAggregationService->clubSummaryForClub($club, $activeSeason?->id);
        $overallStatsByContext = $statisticsAggregationService->clubSummaryByContextForClub($club, null);
        $seasonStatsByContext = $statisticsAggregationService->clubSummaryByContextForClub($club, $activeSeason?->id);
        $seasonHistory = $statisticsAggregationService->clubSeasonHistoryForClub($club, 5);

        // --- Data for Squad Tab ---
        $players = $club->players;
        $squadStats = [
            'count' => $players->count(),
            'avg_age' => $players->isNotEmpty() ? round($players->avg('age'), 1) : 0,
            'avg_rating' => $players->isNotEmpty() ? round($players->avg('overall'), 1) : 0,
            'total_value' => $players->sum('market_value'),
            'avg_value' => $players->isNotEmpty() ? $players->avg('market_value') : 0,
            'injured_count' => $players->where('is_injured', true)->count(),
            'suspended_count' => $players->where('is_suspended', true)->count(),
        ];

        $groupedPlayers = $players->groupBy(fn($player) => match (true) {
            in_array($player->position, ['GK', 'TW']) => 'Torhüter',
            in_array($player->position, ['LB', 'CB', 'RB', 'LWB', 'RWB', 'LV', 'IV', 'RV']) => 'Abwehr',
            in_array($player->position, ['CDM', 'CM', 'CAM', 'LM', 'RM', 'DM', 'ZM', 'OM']) => 'Mittelfeld',
            default => 'Sturm',
        })->sortBy(fn($group, $key) => match ($key) {
                'Torhüter' => 1,
                'Abwehr' => 2,
                'Mittelfeld' => 3,
                'Sturm' => 4,
                default => 99,
            });

        // --- Data for Matches Tab ---
        $matches = GameMatch::query()
            ->where(function ($query) use ($club) {
                $query->where('home_club_id', $club->id)
                    ->orWhere('away_club_id', $club->id);
            })
            ->with(['homeClub', 'awayClub'])
            ->orderBy('kickoff_at')
            ->get();

        $latestMatches = $matches->where('status', 'played')->sortByDesc('played_at')->take(5);

        return view('clubs.show', [
            'club' => $club,
            'seasons' => $seasons,
            'activeSeason' => $activeSeason,
            'overallStats' => $overallStats,
            'seasonStats' => $seasonStats,
            'overallStatsByContext' => $overallStatsByContext,
            'seasonStatsByContext' => $seasonStatsByContext,
            'seasonHistory' => $seasonHistory,
            'latestMatches' => $latestMatches,
            'squadStats' => $squadStats,
            'groupedPlayers' => $groupedPlayers,
            'allMatches' => $matches,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Club $club): View
    {
        $this->ensureOwnership($request, $club);

        return view('clubs.edit', ['club' => $club]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Club $club, ClubFinanceLedgerService $financeLedger): RedirectResponse
    {
        $this->ensureOwnership($request, $club);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:12'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'country' => ['required', 'string', 'max:80'],
            'league' => ['required', 'string', 'max:120'],
            'founded_year' => ['nullable', 'integer', 'min:1850', 'max:' . date('Y')],
            'budget' => ['required', 'numeric', 'min:0'],
            'coins' => ['nullable', 'integer', 'min:0'],
            'wage_budget' => ['required', 'numeric', 'min:0'],
            'reputation' => ['required', 'integer', 'min:1', 'max:99'],
            'fan_mood' => ['required', 'integer', 'min:1', 'max:100'],
            'season_objective' => ['nullable', 'in:avoid_relegation,mid_table,promotion,title,cup_run'],
            'captain_player_id' => [
                'nullable',
                Rule::exists('players', 'id')->where(fn($query) => $query->where('club_id', $club->id)),
            ],
            'vice_captain_player_id' => [
                'nullable',
                Rule::exists('players', 'id')->where(fn($query) => $query->where('club_id', $club->id)),
                'different:captain_player_id',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
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
                    'note' => 'Manuelle Budgetanpassung',
                ]);
            }

            if ($coinDelta !== 0) {
                $financeLedger->applyCoinChange($lockedClub, $coinDelta, [
                    'user_id' => $request->user()->id,
                    'context_type' => 'admin_adjustment',
                    'reference_type' => 'clubs',
                    'reference_id' => $club->id,
                    'note' => 'Manuelle Coin-Anpassung',
                ]);
            }
        });

        return redirect()
            ->route('clubs.show', $club)
            ->with('status', 'Vereinsdaten wurden aktualisiert.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Club $club): RedirectResponse
    {
        $this->ensureOwnership($request, $club);

        if ($club->logo_path) {
            Storage::delete($club->logo_path);
        }

        $club->delete();

        return redirect()
            ->route('clubs.index')
            ->with('status', 'Verein wurde gelöscht.');
    }

    private function ensureOwnership(Request $request, Club $club): void
    {
        abort_unless($club->user_id === $request->user()->id, 403);
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
