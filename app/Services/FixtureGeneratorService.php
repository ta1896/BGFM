<?php

namespace App\Services;

use App\Models\CompetitionSeason;
use App\Models\GameMatch;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FixtureGeneratorService
{
    public function generateRoundRobin(CompetitionSeason $competitionSeason): int
    {
        $clubIds = $competitionSeason->registrations()
            ->orderBy('club_id')
            ->pluck('club_id')
            ->values()
            ->all();

        if (count($clubIds) < 2) {
            return 0;
        }

        if (count($clubIds) % 2 !== 0) {
            $clubIds[] = null;
        }

        $half = count($clubIds) / 2;
        $rounds = count($clubIds) - 1;
        $fixtures = [];

        $rotation = $clubIds;
        for ($round = 1; $round <= $rounds; $round++) {
            for ($i = 0; $i < $half; $i++) {
                $home = $rotation[$i];
                $away = $rotation[count($rotation) - 1 - $i];

                if ($home && $away) {
                    $fixtures[] = [$round, $home, $away];
                }
            }

            $rotation = $this->rotate($rotation);
        }

        $secondLegFixtures = collect($fixtures)
            ->map(fn (array $fixture) => [$fixture[0] + $rounds, $fixture[2], $fixture[1]])
            ->all();

        $allFixtures = array_merge($fixtures, $secondLegFixtures);
        $kickoffBase = Carbon::parse($competitionSeason->season->start_date)->setTime(18, 30);

        GameMatch::where('competition_season_id', $competitionSeason->id)
            ->where('type', 'league')
            ->delete();

        foreach ($allFixtures as $fixture) {
            [$matchday, $homeClubId, $awayClubId] = $fixture;

            GameMatch::create([
                'competition_season_id' => $competitionSeason->id,
                'season_id' => $competitionSeason->season_id,
                'type' => 'league',
                'stage' => 'Regular Season',
                'round_number' => $matchday,
                'matchday' => $matchday,
                'kickoff_at' => (clone $kickoffBase)->addDays(($matchday - 1) * 7),
                'status' => 'scheduled',
                'home_club_id' => $homeClubId,
                'away_club_id' => $awayClubId,
                'stadium_club_id' => $homeClubId,
                'simulation_seed' => random_int(10000, 99999),
            ]);
        }

        return count($allFixtures);
    }

    /**
     * @param array<int, int|null> $teams
     * @return array<int, int|null>
     */
    private function rotate(array $teams): array
    {
        $fixed = array_shift($teams);
        $last = array_pop($teams);

        array_unshift($teams, $last);
        array_unshift($teams, $fixed);

        return $teams;
    }
}
