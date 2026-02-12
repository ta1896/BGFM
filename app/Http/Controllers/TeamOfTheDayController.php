<?php

namespace App\Http\Controllers;

use App\Models\CompetitionSeason;
use App\Models\TeamOfTheDay;
use App\Services\TeamOfTheDayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TeamOfTheDayController extends Controller
{
    public function index(Request $request): View
    {
        $teams = TeamOfTheDay::query()
            ->with(['competitionSeason.competition', 'competitionSeason.season'])
            ->withCount('players')
            ->orderByDesc('for_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $activeTeam = $teams->firstWhere('id', (int) $request->query('totd')) ?? $teams->first();

        $entries = collect();
        if ($activeTeam) {
            $entries = $activeTeam->players()
                ->with(['player.club'])
                ->orderBy('position_code')
                ->get();
        }

        return view('team-of-the-day.index', [
            'teams' => $teams,
            'activeTeam' => $activeTeam,
            'entries' => $entries,
            'competitionSeasons' => CompetitionSeason::query()
                ->with(['competition', 'season'])
                ->orderByDesc('id')
                ->limit(30)
                ->get(),
        ]);
    }

    public function generate(
        Request $request,
        TeamOfTheDayService $service
    ): RedirectResponse {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'for_date' => ['nullable', 'date'],
            'competition_season_id' => ['nullable', 'integer', 'exists:competition_seasons,id'],
            'matchday' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        if (!empty($validated['competition_season_id'])) {
            $competitionSeason = CompetitionSeason::query()
                ->with(['competition', 'season'])
                ->findOrFail((int) $validated['competition_season_id']);

            $matchday = !empty($validated['matchday'])
                ? (int) $validated['matchday']
                : (int) ($competitionSeason->matches()
                    ->where('status', 'played')
                    ->max('matchday') ?? 1);

            $team = $service->generateForCompetitionMatchday($competitionSeason, $matchday, $request->user());

            return redirect()
                ->route('team-of-the-day.index', ['totd' => $team->id])
                ->with('status', 'Team of the Day fuer Spieltag '.$matchday.' wurde neu berechnet.');
        }

        $forDate = !empty($validated['for_date'])
            ? Carbon::parse($validated['for_date'])
            : now();

        $team = $service->generateForDate($forDate, $request->user());

        return redirect()
            ->route('team-of-the-day.index', ['totd' => $team->id])
            ->with('status', 'Team of the Day wurde fuer '.$team->for_date->format('d.m.Y').' neu berechnet.');
    }
}
