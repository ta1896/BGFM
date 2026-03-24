<?php

namespace App\Services;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use Illuminate\Support\Collection;

class MatchLineupService
{
    public function __construct(private readonly FormationPlannerService $formationPlannerService)
    {
    }

    public function resolvePreferredLineup(Club $club, GameMatch $match): ?Lineup
    {
        return $club->lineups()
            ->with('players')
            ->where('match_id', $match->id)
            ->first()
            ?? $club->lineups()
                ->with('players')
                ->where('is_active', true)
                ->first();
    }

    public function ensureMatchLineup(GameMatch $match, Club $club): Lineup
    {
        /** @var Lineup|null $lineup */
        $lineup = $club->lineups()
            ->with('players')
            ->where('match_id', $match->id)
            ->first();

        if ($lineup && $lineup->players->isNotEmpty()) {
            return $lineup;
        }

        $source = $club->lineups()
            ->with('players')
            ->whereNull('match_id')
            ->where('is_active', true)
            ->first()
            ?? $club->lineups()
                ->with('players')
                ->whereNull('match_id')
                ->where('is_template', true)
                ->orderBy('id')
                ->first();

        $defaultFormation = $this->formationPlannerService->defaultFormation();

        $lineup = $club->lineups()->updateOrCreate(
            ['match_id' => $match->id],
            [
                'name' => 'Live Match ' . $match->id,
                'formation' => $source?->formation ?: $defaultFormation,
                'mentality' => $source?->mentality ?: 'normal',
                'aggression' => $source?->aggression ?: 'normal',
                'line_height' => $source?->line_height ?: 'normal',
                'offside_trap' => (bool) ($source?->offside_trap ?? false),
                'time_wasting' => (bool) ($source?->time_wasting ?? false),
                'attack_focus' => $source?->attack_focus ?: 'center',
                'penalty_taker_player_id' => $source?->penalty_taker_player_id,
                'free_kick_near_player_id' => $source?->free_kick_near_player_id,
                'free_kick_far_player_id' => $source?->free_kick_far_player_id,
                'corner_left_taker_player_id' => $source?->corner_left_taker_player_id,
                'corner_right_taker_player_id' => $source?->corner_right_taker_player_id,
                'is_active' => true,
                'is_template' => false,
            ]
        );

        if ($source && $source->players->isNotEmpty()) {
            $lineup->players()->sync($this->sourcePivot($source));

            return $lineup->load('players');
        }

        $selection = $this->formationPlannerService->strongestByFormation(
            $club->players()
                ->whereIn('status', ['active', 'transfer_listed', 'suspended'])
                ->orderByDesc('overall')
                ->get(),
            $defaultFormation,
            $this->maxBenchPlayers()
        );

        $pivot = $this->generatedPivot($selection, $defaultFormation);
        if ($pivot !== []) {
            $lineup->players()->sync($pivot);
        }

        return $lineup->load('players');
    }

    public function resolveStarters(Club $club, GameMatch $match, bool $ensureMatchLineup = false): Collection
    {
        $lineup = $ensureMatchLineup
            ? $this->ensureMatchLineup($match, $club)
            : $this->resolvePreferredLineup($club, $match);

        if ($lineup && $lineup->players->isNotEmpty()) {
            $starters = $lineup->players
                ->filter(function (Player $player): bool {
                    $slot = strtoupper((string) $player->pivot->pitch_position);

                    return !(bool) $player->pivot->is_bench && !str_starts_with($slot, 'OUT-');
                })
                ->sortBy(fn (Player $player) => (int) $player->pivot->sort_order)
                ->take(11)
                ->values();

            if ($starters->count() < 11) {
                $fallback = $this->fallbackPlayers($club, $starters->pluck('id'), 11 - $starters->count());
                $starters = $starters->concat($fallback)->take(11)->values();
            }

            if ($starters->isNotEmpty()) {
                return $starters;
            }
        }

        return $this->fallbackPlayers($club, collect(), 11);
    }

    public function resolveParticipants(Club $club, GameMatch $match, bool $ensureMatchLineup = false): Collection
    {
        $lineup = $ensureMatchLineup
            ? $this->ensureMatchLineup($match, $club)
            : $this->resolvePreferredLineup($club, $match);

        if ($lineup && $lineup->players->isNotEmpty()) {
            return $lineup->players->values();
        }

        return $this->resolveStarters($club, $match, $ensureMatchLineup);
    }

    public function lineupStyle(Club $club, GameMatch $match): string
    {
        $lineup = $this->resolvePreferredLineup($club, $match);

        return match ((string) ($lineup?->mentality ?? 'normal')) {
            'offensive' => 'offensive',
            'defensive' => 'defensive',
            'counter' => 'counter',
            default => 'balanced',
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sourcePivot(Lineup $source): array
    {
        return $source->players->mapWithKeys(function (Player $player): array {
            return [
                $player->id => [
                    'pitch_position' => $player->pivot->pitch_position,
                    'sort_order' => $player->pivot->sort_order,
                    'x_coord' => $player->pivot->x_coord,
                    'y_coord' => $player->pivot->y_coord,
                    'is_captain' => (bool) $player->pivot->is_captain,
                    'is_set_piece_taker' => (bool) $player->pivot->is_set_piece_taker,
                    'is_bench' => (bool) $player->pivot->is_bench,
                    'bench_order' => $player->pivot->bench_order,
                ],
            ];
        })->all();
    }

    /**
     * @param array{starters: array<string, int|null>, bench: array<int, int|null>} $selection
     * @return array<int, array<string, mixed>>
     */
    private function generatedPivot(array $selection, string $formation): array
    {
        $slots = collect($this->formationPlannerService->starterSlots($formation))->keyBy('slot');
        $pivot = [];
        $starterOrder = 1;

        foreach ($selection['starters'] as $slot => $playerId) {
            if (!$playerId) {
                continue;
            }

            $slotInfo = $slots->get($slot);
            $pivot[$playerId] = [
                'pitch_position' => $slot,
                'sort_order' => $starterOrder,
                'x_coord' => $slotInfo['x'] ?? null,
                'y_coord' => $slotInfo['y'] ?? null,
                'is_captain' => $starterOrder === 1,
                'is_set_piece_taker' => false,
                'is_bench' => false,
                'bench_order' => null,
            ];
            $starterOrder++;
        }

        foreach ($selection['bench'] as $index => $playerId) {
            if (!$playerId || isset($pivot[$playerId])) {
                continue;
            }

            $benchOrder = $index + 1;
            $pivot[$playerId] = [
                'pitch_position' => 'BANK-' . $benchOrder,
                'sort_order' => 100 + $benchOrder,
                'x_coord' => null,
                'y_coord' => null,
                'is_captain' => false,
                'is_set_piece_taker' => false,
                'is_bench' => true,
                'bench_order' => $benchOrder,
            ];
        }

        return $pivot;
    }

    private function fallbackPlayers(Club $club, Collection $excludeIds, int $limit): Collection
    {
        return $club->players()
            ->when($excludeIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $excludeIds->all()))
            ->orderByDesc('overall')
            ->limit($limit)
            ->get();
    }

    private function maxBenchPlayers(): int
    {
        return max(1, min(10, (int) config('simulation.lineup.max_bench_players', 5)));
    }
}
