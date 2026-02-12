<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:12'],
            'country' => ['required', 'string', 'max:80'],
            'league' => ['required', 'string', 'max:120'],
            'founded_year' => ['nullable', 'integer', 'min:1850', 'max:'.date('Y')],
            'budget' => ['required', 'numeric', 'min:0'],
            'wage_budget' => ['required', 'numeric', 'min:0'],
            'reputation' => ['required', 'integer', 'min:1', 'max:99'],
            'fan_mood' => ['required', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $club = $request->user()->clubs()->create($validated);

        return redirect()
            ->route('clubs.show', $club)
            ->with('status', 'Verein erfolgreich angelegt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Club $club): View
    {
        $this->ensureOwnership($request, $club);

        $club->load([
            'players' => fn ($query) => $query->orderByDesc('overall')->limit(12),
            'lineups' => fn ($query) => $query->latest()->limit(5),
            'user',
            'stadium',
        ]);

        $seasonId = (int) $request->query('season_id');
        $seasons = Season::orderByDesc('id')->get();
        $activeSeason = $seasonId > 0
            ? $seasons->firstWhere('id', $seasonId)
            : $seasons->first();

        $overallStats = $this->calculateStatsForSeason($club, null);
        $seasonStats = $this->calculateStatsForSeason($club, $activeSeason?->id);

        $latestMatches = GameMatch::query()
            ->where('status', 'played')
            ->where(function ($query) use ($club) {
                $query->where('home_club_id', $club->id)
                    ->orWhere('away_club_id', $club->id);
            })
            ->with(['homeClub', 'awayClub'])
            ->orderByDesc('played_at')
            ->limit(5)
            ->get();

        return view('clubs.show', [
            'club' => $club,
            'seasons' => $seasons,
            'activeSeason' => $activeSeason,
            'overallStats' => $overallStats,
            'seasonStats' => $seasonStats,
            'latestMatches' => $latestMatches,
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
    public function update(Request $request, Club $club): RedirectResponse
    {
        $this->ensureOwnership($request, $club);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:12'],
            'country' => ['required', 'string', 'max:80'],
            'league' => ['required', 'string', 'max:120'],
            'founded_year' => ['nullable', 'integer', 'min:1850', 'max:'.date('Y')],
            'budget' => ['required', 'numeric', 'min:0'],
            'wage_budget' => ['required', 'numeric', 'min:0'],
            'reputation' => ['required', 'integer', 'min:1', 'max:99'],
            'fan_mood' => ['required', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $club->update($validated);

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

        $club->delete();

        return redirect()
            ->route('clubs.index')
            ->with('status', 'Verein wurde gelöscht.');
    }

    private function ensureOwnership(Request $request, Club $club): void
    {
        abort_unless($club->user_id === $request->user()->id, 403);
    }

    private function calculateStatsForSeason(Club $club, ?int $seasonId): array
    {
        $matches = GameMatch::query()
            ->where('status', 'played')
            ->when($seasonId, fn ($query) => $query->where('season_id', $seasonId))
            ->where(function ($query) use ($club) {
                $query->where('home_club_id', $club->id)
                    ->orWhere('away_club_id', $club->id);
            })
            ->get();

        $wins = 0;
        $draws = 0;
        $losses = 0;
        $goalsFor = 0;
        $goalsAgainst = 0;

        foreach ($matches as $match) {
            $isHome = $match->home_club_id === $club->id;
            $gf = (int) ($isHome ? $match->home_score : $match->away_score);
            $ga = (int) ($isHome ? $match->away_score : $match->home_score);

            $goalsFor += $gf;
            $goalsAgainst += $ga;

            if ($gf > $ga) {
                $wins++;
            } elseif ($gf === $ga) {
                $draws++;
            } else {
                $losses++;
            }
        }

        $points = ($wins * 3) + $draws;

        return [
            'matches' => $matches->count(),
            'wins' => $wins,
            'draws' => $draws,
            'losses' => $losses,
            'goals_for' => $goalsFor,
            'goals_against' => $goalsAgainst,
            'points' => $points,
        ];
    }
}
