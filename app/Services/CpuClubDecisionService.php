<?php

namespace App\Services;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Player;
use Illuminate\Support\Collection;

class CpuClubDecisionService
{
    public function __construct(private readonly PlayerPositionService $positionService)
    {
    }

    public function prepareForMatch(GameMatch $match): void
    {
        $match->loadMissing([
            'homeClub.players',
            'awayClub.players',
        ]);

        $this->prepareCpuLineup($match, $match->homeClub, $match->awayClub);
        $this->prepareCpuLineup($match, $match->awayClub, $match->homeClub);
    }

    private function prepareCpuLineup(GameMatch $match, Club $club, Club $opponent): void
    {
        if (!$club->is_cpu) {
            return;
        }

        $players = $club->players
            ->whereIn('status', ['active', 'transfer_listed'])
            ->values();

        if ($players->isEmpty()) {
            return;
        }

        $starters = $this->pickStarters($players, 11);
        if ($starters->isEmpty()) {
            return;
        }

        $formation = $this->formation($starters);
        $style = $this->style($starters, $opponent->players);

        $lineupName = 'CPU Matchday ' . ($match->matchday ?? '-') . ' (Match ' . $match->id . ')';
        \Illuminate\Support\Facades\Log::info("CpuClubDecision: Creating lineup for Match {$match->id}, Club {$club->id} ({$club->name}), Name: {$lineupName}");

        $lineup = $club->lineups()->updateOrCreate(
            ['match_id' => $match->id],
            [
                'name' => $lineupName,
                'formation' => $formation,
                'tactical_style' => $style,
                'is_active' => true,
                'is_template' => false,
            ]
        );

        $club->lineups()
            ->where('id', '!=', $lineup->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $captainId = $starters->sortByDesc('overall')->first()?->id;
        $setPieceId = $starters
            ->sortByDesc(fn(Player $player) => $player->passing + $player->shooting)
            ->first()?->id;

        $pivot = $starters->values()->mapWithKeys(function (Player $player, int $index) use ($captainId, $setPieceId) {
            return [
                $player->id => [
                    'sort_order' => $index,
                    'pitch_position' => $player->position,
                    'is_captain' => $player->id === $captainId,
                    'is_set_piece_taker' => $player->id === $setPieceId,
                    'is_bench' => false,
                    'bench_order' => null,
                ],
            ];
        })->all();

        $lineup->players()->sync($pivot);
    }

    private function pickStarters(Collection $players, int $limit): Collection
    {
        $goalkeepers = $players
            ->filter(fn(Player $player) => $this->positionService->groupFromPosition($player->position) === 'GK')
            ->sortByDesc('overall')
            ->values();
        $others = $players
            ->reject(fn(Player $player) => $this->positionService->groupFromPosition($player->position) === 'GK')
            ->sortByDesc(function (Player $player) {
                return ($player->overall * 2) + $player->stamina + $player->morale;
            })
            ->values();

        $starters = collect();
        if ($goalkeepers->isNotEmpty()) {
            $starters->push($goalkeepers->first());
        }

        $remaining = max(0, $limit - $starters->count());
        $starters = $starters->concat($others->take($remaining))->values();

        if ($starters->count() < $limit) {
            $filler = $players
                ->whereNotIn('id', $starters->pluck('id'))
                ->sortByDesc('overall')
                ->take($limit - $starters->count());
            $starters = $starters->concat($filler)->values();
        }

        return $starters->take($limit)->values();
    }

    private function formation(Collection $players): string
    {
        $def = $players->filter(fn(Player $player) => $this->positionService->groupFromPosition($player->position) === 'DEF')->count();
        $mid = $players->filter(fn(Player $player) => $this->positionService->groupFromPosition($player->position) === 'MID')->count();
        $fwd = $players->filter(fn(Player $player) => $this->positionService->groupFromPosition($player->position) === 'FWD')->count();

        if ($def >= 4 && $mid >= 4) {
            return '4-4-2';
        }

        if ($def >= 3 && $mid >= 5) {
            return '3-5-2';
        }

        if ($fwd >= 3) {
            return '4-3-3';
        }

        return '4-2-3-1';
    }

    private function style(Collection $ownPlayers, Collection $opponentPlayers): string
    {
        $own = (float) $ownPlayers->avg('overall');
        $opp = (float) $opponentPlayers->avg('overall');
        $diff = $own - $opp;

        if ($diff >= 4) {
            return 'offensive';
        }

        if ($diff <= -4) {
            return 'defensive';
        }

        if ($diff <= -1.5) {
            return 'counter';
        }

        return 'balanced';
    }
}
