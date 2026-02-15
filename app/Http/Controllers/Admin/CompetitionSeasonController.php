<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionSeason;
use App\Models\Club;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompetitionSeasonController extends Controller
{
    public function edit(CompetitionSeason $competitionSeason): View
    {
        $competitionSeason->load(['competition', 'season']);
        $clubs = Club::orderBy('name')->get();

        return view('admin.competition_seasons.edit', compact('competitionSeason', 'clubs'));
    }

    public function update(Request $request, CompetitionSeason $competitionSeason): RedirectResponse
    {
        $validated = $request->validate([
            'league_winner_club_id' => 'nullable|exists:clubs,id',
            'national_cup_winner_club_id' => 'nullable|exists:clubs,id',
            'intl_cup_winner_club_id' => 'nullable|exists:clubs,id',
            'is_finished' => 'boolean',
        ]);

        $competitionSeason->update($validated);

        // If finished, record achievements and ranks
        if ($request->boolean('is_finished')) {
            $this->recordAchievements($competitionSeason);
            $this->assignFinalRanks($competitionSeason);
        }

        return redirect()
            ->route('admin.competitions.edit', $competitionSeason->competition_id)
            ->with('status', 'Wettbewerbs-Saison aktualisiert.');
    }

    private function recordAchievements(CompetitionSeason $compSeason): void
    {
        // League Winner
        if ($compSeason->league_winner_club_id) {
            $compSeason->achievements()->updateOrCreate(
                ['club_id' => $compSeason->league_winner_club_id, 'type' => 'league_winner'],
                [
                    'title' => 'Meister ' . $compSeason->competition->name . ' (' . $compSeason->season->name . ')',
                    'achieved_at' => now(),
                ]
            );
        }

        // National Cup
        if ($compSeason->national_cup_winner_club_id) {
            $compSeason->achievements()->updateOrCreate(
                ['club_id' => $compSeason->national_cup_winner_club_id, 'type' => 'cup_winner_national'],
                [
                    'title' => 'Nationaler Pokalsieger (' . $compSeason->season->name . ')',
                    'achieved_at' => now(),
                ]
            );
        }

        // Intl Cup
        if ($compSeason->intl_cup_winner_club_id) {
            $compSeason->achievements()->updateOrCreate(
                ['club_id' => $compSeason->intl_cup_winner_club_id, 'type' => 'cup_winner_intl'],
                [
                    'title' => 'Internationaler Pokalsieger (' . $compSeason->season->name . ')',
                    'achieved_at' => now(),
                ]
            );
        }
    }

    private function assignFinalRanks(CompetitionSeason $compSeason): void
    {
        if ($compSeason->competition->type !== 'league') {
            return;
        }

        $stats = $compSeason->seasonClubStatistics()
            ->orderByDesc('points')
            ->orderByDesc('goal_diff')
            ->orderByDesc('goals_for')
            ->get();

        foreach ($stats as $index => $stat) {
            $stat->update(['rank' => $index + 1]);

            // Auto-achievement for Rank 1 if not manually set
            if ($index === 0 && !$compSeason->league_winner_club_id) {
                $compSeason->achievements()->updateOrCreate(
                    ['club_id' => $stat->club_id, 'type' => 'league_winner'],
                    [
                        'title' => 'Meister ' . $compSeason->competition->name . ' (' . $compSeason->season->name . ')',
                        'achieved_at' => now(),
                    ]
                );
            }
        }
    }
}
