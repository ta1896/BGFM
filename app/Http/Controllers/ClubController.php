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
use Inertia\Inertia;
use Inertia\Response;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Club::query()->withCount(['players', 'lineups']);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        } else {
            // Admins see all clubs, but maybe prioritize their default one or non-cpu ones
            $query->orderByRaw("FIELD(id, ?) DESC", [$user->default_club_id])
                  ->orderByDesc('id');
        }

        $clubs = $query->latest()->get();

        return Inertia::render('Clubs/Index', ['clubs' => $clubs]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Clubs/Form', [
            'club' => null,
            'rolePlayers' => [],
        ]);
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
    public function show(Request $request, Club $club, StatisticsAggregationService $statisticsAggregationService): Response
    {
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

        // --- Statistics ---
        $overallStats = $statisticsAggregationService->clubSummaryForClub($club, null);
        $seasonStats = $statisticsAggregationService->clubSummaryForClub($club, $activeSeason?->id);
        
        $players = $club->players->map(function($p) {
            $p->append('photo_url');
            return $p;
        });

        $trophyCabinet = $club->achievements
            ->sortByDesc(function ($achievement) {
                return $achievement->competitionSeason?->season?->id
                    ?? $achievement->achieved_at?->timestamp
                    ?? 0;
            })
            ->values()
            ->map(function ($achievement) {
                $competition = $achievement->competitionSeason?->competition;
                $season = $achievement->competitionSeason?->season;

                $type = match ($achievement->type) {
                    'league_winner' => 'league',
                    'cup_winner_intl' => 'international_cup',
                    default => 'national_cup',
                };

                $categoryLabel = match ($achievement->type) {
                    'league_winner' => 'Ligatitel',
                    'cup_winner_intl' => 'Internationaler Pokal',
                    default => 'Nationaler Pokal',
                };

                return [
                    'id' => $achievement->id,
                    'type' => $type,
                    'category_label' => $categoryLabel,
                    'title' => $achievement->title,
                    'competition_name' => $competition?->name ?? 'Wettbewerb',
                    'competition_short_name' => $competition?->short_name ?: $competition?->name ?: 'Wettbewerb',
                    'competition_logo_url' => $competition?->logo_url,
                    'season_name' => $season?->name ?? 'Unbekannte Saison',
                    'achieved_at' => $achievement->achieved_at?->format('d.m.Y'),
                    'scope' => $competition?->scope,
                    'competition_type' => $competition?->type,
                ];
            });

        return Inertia::render('Clubs/Show', [
            'club' => $club,
            'seasons' => $seasons,
            'activeSeason' => $activeSeason,
            'overallStats' => $overallStats,
            'seasonStats' => $seasonStats,
            'players' => $players,
            'trophyCabinet' => [
                'total' => $trophyCabinet->count(),
                'by_type' => [
                    'league' => $trophyCabinet->where('type', 'league')->count(),
                    'national_cup' => $trophyCabinet->where('type', 'national_cup')->count(),
                    'international_cup' => $trophyCabinet->where('type', 'international_cup')->count(),
                ],
                'items' => $trophyCabinet,
            ],
            'isOwner' => $club->user_id === $request->user()->id,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Club $club): Response
    {
        $this->ensureOwnership($request, $club);

        $rolePlayers = $club->players()
            ->orderByDesc('overall')
            ->limit(40)
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'full_name' => $p->full_name,
                    'position' => $p->position_main ?: $p->position,
                    'overall' => $p->overall,
                ];
            });

        return Inertia::render('Clubs/Form', [
            'club' => $club,
            'rolePlayers' => $rolePlayers,
        ]);
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

        $path = $request->file('logo')->store('club-logos', 'public');
        $validated['logo_path'] = $path;
        unset($validated['logo']);

        if ($previousPath) {
            $this->deleteLogoFile($previousPath);
        }

        return $validated;
    }

    private function deleteLogoFile(string $path): void
    {
        $normalizedPath = ltrim(preg_replace('#^public/#', '', $path), '/');

        Storage::disk('public')->delete($normalizedPath);
        Storage::disk('local')->delete($path);
    }
}
