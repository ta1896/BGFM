<?php

namespace App\Modules\AwardsCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CompetitionSeason;
use App\Models\SeasonAward;
use App\Services\SeasonAwardsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AwardsCenterController extends Controller
{
    public function index(Request $request, SeasonAwardsService $seasonAwardsService): Response
    {
        $competitionSeasons = CompetitionSeason::query()
            ->with(['competition', 'season'])
            ->orderByDesc('id')
            ->get();

        $selectedSeasonId = (int) $request->query('competition_season_id', $competitionSeasons->first()?->id);
        $selectedSeason = $competitionSeasons->firstWhere('id', $selectedSeasonId) ?? $competitionSeasons->first();

        $currentAwards = collect();
        if ($selectedSeason) {
            $currentAwards = $seasonAwardsService->generateForCompetitionSeason($selectedSeason);
        }

        $history = SeasonAward::query()
            ->with(['competitionSeason.competition', 'competitionSeason.season', 'player.club', 'club', 'user'])
            ->orderByDesc('competition_season_id')
            ->get()
            ->groupBy('competition_season_id')
            ->map(function ($awards) {
                $season = $awards->first()?->competitionSeason;

                return [
                    'competition_season_id' => $season?->id,
                    'season_label' => trim(($season?->competition?->name ?? '').' - '.($season?->season?->name ?? '')),
                    'awards' => $awards->map(fn (SeasonAward $award) => $this->mapAward($award))->values()->all(),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Modules/AwardsCenter/Index', [
            'competitionSeasons' => $competitionSeasons->map(fn ($item) => [
                'id' => $item->id,
                'label' => trim(($item->competition?->name ?? '').' - '.($item->season?->name ?? '')),
            ])->values()->all(),
            'activeCompetitionSeasonId' => $selectedSeason?->id,
            'currentAwards' => $currentAwards->map(fn (SeasonAward $award) => $this->mapAward($award))->values()->all(),
            'history' => $history,
        ]);
    }

    private function mapAward(SeasonAward $award): array
    {
        return [
            'id' => $award->id,
            'award_key' => $award->award_key,
            'label' => $award->label,
            'value_label' => $award->value_label,
            'summary' => $award->summary,
            'player' => $award->player ? [
                'id' => $award->player->id,
                'name' => $award->player->full_name,
                'photo_url' => $award->player->photo_url,
            ] : null,
            'club' => $award->club ? [
                'id' => $award->club->id,
                'name' => $award->club->name,
                'logo_url' => $award->club->logo_url,
            ] : null,
            'user' => $award->user ? [
                'id' => $award->user->id,
                'name' => $award->user->name,
            ] : null,
        ];
    }
}
