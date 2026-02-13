<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\MatchLivePlayerState;
use App\Models\Player;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatchPlayerStatsService
{
    public function __construct(
        private readonly PlayerPositionService $positionService
    ) {
    }

    public function rebuild(GameMatch $match, Collection $homePlayers, Collection $awayPlayers): void
    {
        $match->playerStats()->delete();

        $goalEvents = $match->events()->whereIn('event_type', ['goal', 'penalty_scored'])->get();
        $yellowEvents = $match->events()->where('event_type', 'yellow_card')->get();
        $redEvents = $match->events()->where('event_type', 'red_card')->get();
        $homeSubstitutions = $this->substitutionMinutes($match, (int) $match->home_club_id);
        $awaySubstitutions = $this->substitutionMinutes($match, (int) $match->away_club_id);
        $stateByPlayerId = MatchLivePlayerState::query()
            ->where('match_id', $match->id)
            ->get()
            ->keyBy('player_id');

        $build = function (
            Collection $players,
            int $clubId,
            array $substitutions
        ) use ($match, $goalEvents, $yellowEvents, $redEvents, $stateByPlayerId): array {
            return $players->values()->map(function (Player $player) use (
                $match,
                $clubId,
                $goalEvents,
                $yellowEvents,
                $redEvents,
                $substitutions,
                $stateByPlayerId
            ) {
                /** @var MatchLivePlayerState|null $state */
                $state = $stateByPlayerId->get($player->id);
                $goals = $state ? (int) $state->goals : $goalEvents->where('player_id', $player->id)->count();
                $assists = $state ? (int) $state->assists : $goalEvents->where('assister_player_id', $player->id)->count();
                $yellow = $state ? (int) $state->yellow_cards : $yellowEvents->where('player_id', $player->id)->count();
                $red = $state ? (int) $state->red_cards : $redEvents->where('player_id', $player->id)->count();
                $fit = $state ? (float) $state->fit_factor : $this->playerFit($player);

                $role = 'bench';
                if (isset($substitutions['out'][$player->id])) {
                    $role = 'sub_off';
                } elseif (isset($substitutions['in'][$player->id])) {
                    $role = 'sub_on';
                } else {
                    $slot = strtoupper((string) ($state?->slot ?: ($player->pivot?->pitch_position ?? '')));
                    if (!(bool) ($player->pivot?->is_bench ?? false) && !str_starts_with($slot, 'OUT-')) {
                        $role = 'starter';
                    }
                }

                $minutesPlayed = $state ? max(0, min((int) $match->live_minute, (int) $state->minutes_played)) : (int) $match->live_minute;
                $baseRating = 5.8
                    + (($player->overall * $fit) / 50)
                    + ($goals * 0.7)
                    + ($assists * 0.4)
                    - ($yellow * 0.25)
                    - ($red * 0.9)
                    - ((1 - $fit) * 1.6)
                    + (($this->randomInt(0, 30) - 15) / 100);

                $shots = $state ? (int) $state->shots : max(0, $goals + $this->randomInt(0, 4));
                $passesCompleted = $state ? (int) $state->pass_completions : $this->randomInt(12, 74);
                $passAttempts = $state ? (int) $state->pass_attempts : ($passesCompleted + $this->randomInt(2, 19));
                $tacklesWon = $state ? (int) $state->tackle_won : $this->randomInt(0, 8);
                $tackleAttempts = $state ? (int) $state->tackle_attempts : ($tacklesWon + $this->randomInt(0, 5));
                $saves = $state ? (int) $state->saves : ($this->isGoalkeeper($player) ? $this->randomInt(1, 8) : 0);

                return [
                    'match_id' => $match->id,
                    'club_id' => $clubId,
                    'player_id' => $player->id,
                    'lineup_role' => $role,
                    'position_code' => $this->positionCodeForStat($player, $state?->slot),
                    'rating' => max(3.5, min(10.0, round($baseRating, 2))),
                    'minutes_played' => $minutesPlayed,
                    'goals' => $goals,
                    'assists' => $assists,
                    'yellow_cards' => $yellow,
                    'red_cards' => $red,
                    'shots' => $shots,
                    'passes_completed' => $passesCompleted,
                    'passes_failed' => max(0, $passAttempts - $passesCompleted),
                    'tackles_won' => $tacklesWon,
                    'tackles_lost' => max(0, $tackleAttempts - $tacklesWon),
                    'saves' => $this->isGoalkeeper($player) ? $saves : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();
        };

        DB::table('match_player_stats')->insert(array_merge(
            $build($homePlayers, (int) $match->home_club_id, $homeSubstitutions),
            $build($awayPlayers, (int) $match->away_club_id, $awaySubstitutions)
        ));
    }

    private function substitutionMinutes(GameMatch $match, int $clubId): array
    {
        $in = [];
        $out = [];
        $subEvents = $match->events()
            ->where('event_type', 'substitution')
            ->where('club_id', $clubId)
            ->get();

        foreach ($subEvents as $event) {
            $playerInId = (int) ($event->metadata['player_in_id'] ?? 0);
            $playerOutId = (int) ($event->metadata['player_out_id'] ?? 0);
            if ($playerInId > 0 && !isset($in[$playerInId])) {
                $in[$playerInId] = (int) $event->minute;
            }
            if ($playerOutId > 0 && !isset($out[$playerOutId])) {
                $out[$playerOutId] = (int) $event->minute;
            }
        }

        return ['in' => $in, 'out' => $out];
    }

    private function positionCodeForStat(Player $player, ?string $slot = null): string
    {
        $resolvedSlot = strtoupper(trim((string) ($slot ?: ($player->pivot?->pitch_position ?? ''))));
        if ($resolvedSlot !== '') {
            if (str_starts_with($resolvedSlot, 'BANK-')) {
                return 'SUB';
            }
            if (str_starts_with($resolvedSlot, 'OUT-')) {
                $position = strtoupper((string) $player->position);

                return strlen($position) <= 4 ? $position : substr($position, 0, 4);
            }
            if (strlen($resolvedSlot) <= 4) {
                return $resolvedSlot;
            }
        }

        $position = strtoupper((string) $player->position);

        return strlen($position) <= 4 ? $position : substr($position, 0, 4);
    }

    private function playerFit(Player $player): float
    {
        return $this->positionService->fitFactorWithProfile(
            (string) ($player->position_main ?: $player->position),
            (string) $player->position_second,
            (string) $player->position_third,
            $player->pivot?->pitch_position
        );
    }

    private function isGoalkeeper(Player $player): bool
    {
        return $this->positionService->groupFromPosition((string) ($player->position_main ?: $player->position)) === 'GK';
    }

    private function randomInt(int $min, int $max): int
    {
        if ((bool) config('simulation.deterministic.enabled', false)) {
            return mt_rand($min, $max);
        }

        return random_int($min, $max);
    }
}
