<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\MatchLivePlayerState;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MatchLiveLineupService
{
    private const ALLOWED_INSTRUCTIONS = [
        'stay_back',
        'tight_marking',
        'join_attack',
        'playmaker',
        'box_to_box',
        'safe_passing',
        'shoot_on_sight',
        'run_behind',
        'target_man',
        'dribble_more',
    ];

    public function __construct(
        private readonly MatchLineupService $matchLineupService,
        private readonly FormationPlannerService $formationPlanner,
    ) {
    }

    public function sync(GameMatch $match, int $clubId, array $payload): Lineup
    {
        return DB::transaction(function () use ($match, $clubId, $payload): Lineup {
            $club = $clubId === (int) $match->home_club_id ? $match->homeClub : $match->awayClub;
            $lineup = $this->matchLineupService->ensureMatchLineup($match, $club);

            $formation = $this->formationPlanner->normalizeFormation((string) ($payload['formation'] ?? $lineup->formation));
            $slots = collect($this->formationPlanner->starterSlots($formation))
                ->keyBy(fn (array $slot): string => (string) $slot['slot']);

            $clubPlayerIds = $club->players()->pluck('id')->map(fn ($id) => (int) $id)->all();
            $allowedPlayerIds = array_flip($clubPlayerIds);
            $maxBenchPlayers = max(1, min(10, (int) config('simulation.lineup.max_bench_players', 5)));

            $starterSlots = $this->normalizeStarterSlots((array) ($payload['starter_slots'] ?? []), $slots->keys()->all(), $allowedPlayerIds);
            $benchSlots = $this->normalizeBenchSlots((array) ($payload['bench_slots'] ?? []), $maxBenchPlayers, $allowedPlayerIds);
            $playerInstructions = $this->normalizePlayerInstructions((array) ($payload['player_instructions'] ?? []), $allowedPlayerIds);

            if ($match->status === 'live') {
                $this->guardLiveStarterSet($match, $clubId, array_values($starterSlots));
            }

            $allSelectedPlayerIds = array_merge(
                array_values(array_filter($starterSlots)),
                array_values(array_filter($benchSlots))
            );

            if (count($allSelectedPlayerIds) !== count(array_unique($allSelectedPlayerIds))) {
                throw ValidationException::withMessages([
                    'starter_slots' => 'Ein Spieler darf nur einmal in der Live-Aufstellung vorkommen.',
                ]);
            }

            $captainId = $this->normalizeOptionalPlayerId($payload['captain_player_id'] ?? null, $allowedPlayerIds);
            $setPieces = [
                'penalty_taker_player_id' => $this->normalizeOptionalPlayerId($payload['penalty_taker_player_id'] ?? null, $allowedPlayerIds),
                'free_kick_near_player_id' => $this->normalizeOptionalPlayerId($payload['free_kick_near_player_id'] ?? null, $allowedPlayerIds),
                'free_kick_far_player_id' => $this->normalizeOptionalPlayerId($payload['free_kick_far_player_id'] ?? null, $allowedPlayerIds),
                'corner_left_taker_player_id' => $this->normalizeOptionalPlayerId($payload['corner_left_taker_player_id'] ?? null, $allowedPlayerIds),
                'corner_right_taker_player_id' => $this->normalizeOptionalPlayerId($payload['corner_right_taker_player_id'] ?? null, $allowedPlayerIds),
            ];

            $lineup->update([
                'formation' => $formation,
                'mentality' => (string) ($payload['mentality'] ?? $lineup->mentality ?? 'normal'),
                'aggression' => (string) ($payload['aggression'] ?? $lineup->aggression ?? 'normal'),
                'line_height' => (string) ($payload['line_height'] ?? $lineup->line_height ?? 'normal'),
                'attack_focus' => (string) ($payload['attack_focus'] ?? $lineup->attack_focus ?? 'center'),
                'offside_trap' => (bool) ($payload['offside_trap'] ?? false),
                'time_wasting' => (bool) ($payload['time_wasting'] ?? false),
                'pressing_intensity' => (string) ($payload['pressing_intensity'] ?? $lineup->pressing_intensity ?? 'normal'),
                'line_of_engagement' => (string) ($payload['line_of_engagement'] ?? $lineup->line_of_engagement ?? 'normal'),
                'pressing_trap' => (string) ($payload['pressing_trap'] ?? $lineup->pressing_trap ?? 'none'),
                'cross_engagement' => (string) ($payload['cross_engagement'] ?? $lineup->cross_engagement ?? 'none'),
                'corner_marking_strategy' => (string) ($payload['corner_marking_strategy'] ?? $lineup->corner_marking_strategy ?? 'zonal'),
                'free_kick_marking_strategy' => (string) ($payload['free_kick_marking_strategy'] ?? $lineup->free_kick_marking_strategy ?? 'zonal'),
                ...$setPieces,
            ]);

            $pivot = [];
            foreach ($slots as $index => $slot) {
                $slotKey = (string) $slot['slot'];
                $playerId = (int) ($starterSlots[$slotKey] ?? 0);
                if ($playerId <= 0) {
                    continue;
                }

                $instructions = $playerInstructions[$playerId] ?? [];
                $pivot[$playerId] = [
                    'pitch_position' => $slotKey,
                    'sort_order' => count($pivot) + 1,
                    'x_coord' => (int) ($slot['x'] ?? 0),
                    'y_coord' => (int) ($slot['y'] ?? 0),
                    'is_captain' => $captainId === $playerId,
                    'is_set_piece_taker' => in_array($playerId, array_filter($setPieces), true),
                    'is_bench' => false,
                    'bench_order' => null,
                    'instructions' => !empty($instructions) ? json_encode($instructions) : null,
                ];
            }

            foreach ($benchSlots as $index => $playerId) {
                $playerId = (int) $playerId;
                if ($playerId <= 0 || isset($pivot[$playerId])) {
                    continue;
                }

                $instructions = $playerInstructions[$playerId] ?? [];
                $order = $index + 1;
                $pivot[$playerId] = [
                    'pitch_position' => 'BANK-' . $order,
                    'sort_order' => 100 + $order,
                    'x_coord' => null,
                    'y_coord' => null,
                    'is_captain' => false,
                    'is_set_piece_taker' => in_array($playerId, array_filter($setPieces), true),
                    'is_bench' => true,
                    'bench_order' => $order,
                    'instructions' => !empty($instructions) ? json_encode($instructions) : null,
                ];
            }

            $lineup->players()->sync($pivot);
            $lineup->load(['players:id,first_name,last_name,position,position_main,position_second,position_third,overall,photo_path']);

            if ($match->status === 'live') {
                $this->syncLivePlayerStates($match, $clubId, $starterSlots, $benchSlots);
            }

            return $lineup;
        });
    }

    /**
     * @param array<string, int> $allowedPlayerIds
     * @return array<string, int|null>
     */
    private function normalizeStarterSlots(array $starterSlots, array $allowedSlotKeys, array $allowedPlayerIds): array
    {
        $normalized = [];

        foreach ($allowedSlotKeys as $slotKey) {
            $playerId = $this->normalizeOptionalPlayerId($starterSlots[$slotKey] ?? null, $allowedPlayerIds);
            $normalized[$slotKey] = $playerId;
        }

        return $normalized;
    }

    /**
     * @param array<string, int> $allowedPlayerIds
     * @return array<int, int>
     */
    private function normalizeBenchSlots(array $benchSlots, int $maxBenchPlayers, array $allowedPlayerIds): array
    {
        return collect($benchSlots)
            ->map(fn ($playerId) => $this->normalizeOptionalPlayerId($playerId, $allowedPlayerIds))
            ->filter(fn ($playerId) => $playerId !== null)
            ->take($maxBenchPlayers)
            ->values()
            ->all();
    }

    /**
     * @param array<string, int> $allowedPlayerIds
     * @return array<int, array<int, string>>
     */
    private function normalizePlayerInstructions(array $playerInstructions, array $allowedPlayerIds): array
    {
        $normalized = [];

        foreach ($playerInstructions as $playerId => $instructions) {
            $resolvedPlayerId = $this->normalizeOptionalPlayerId($playerId, $allowedPlayerIds);
            if ($resolvedPlayerId === null) {
                continue;
            }

            $normalized[$resolvedPlayerId] = collect((array) $instructions)
                ->map(fn ($instruction) => (string) $instruction)
                ->filter(fn (string $instruction) => in_array($instruction, self::ALLOWED_INSTRUCTIONS, true))
                ->values()
                ->all();
        }

        return $normalized;
    }

    /**
     * @param array<string, int> $allowedPlayerIds
     */
    private function normalizeOptionalPlayerId(mixed $value, array $allowedPlayerIds): ?int
    {
        $playerId = (int) $value;
        if ($playerId <= 0) {
            return null;
        }

        if (!isset($allowedPlayerIds[$playerId])) {
            throw ValidationException::withMessages([
                'club_id' => 'Ein ausgewaehlter Spieler gehoert nicht zu diesem Verein.',
            ]);
        }

        return $playerId;
    }

    private function guardLiveStarterSet(GameMatch $match, int $clubId, array $incomingStarterIds): void
    {
        $currentStarterIds = MatchLivePlayerState::query()
            ->where('match_id', $match->id)
            ->where('club_id', $clubId)
            ->where('is_on_pitch', true)
            ->pluck('player_id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $incoming = collect($incomingStarterIds)
            ->filter(fn ($id) => (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        if ($incoming !== $currentStarterIds) {
            throw ValidationException::withMessages([
                'starter_slots' => 'Spielerwechsel bitte ueber den Live-Wechsel ausfuehren. Die Live-Aufstellung darf nur vorhandene Feldspieler neu anordnen.',
            ]);
        }
    }

    /**
     * @param array<string, int|null> $starterSlots
     * @param array<int, int> $benchSlots
     */
    private function syncLivePlayerStates(GameMatch $match, int $clubId, array $starterSlots, array $benchSlots): void
    {
        $starterMap = collect($starterSlots)
            ->filter(fn ($playerId) => (int) $playerId > 0)
            ->mapWithKeys(fn ($playerId, $slotKey) => [(int) $playerId => (string) $slotKey])
            ->all();

        $benchIds = collect($benchSlots)
            ->map(fn ($playerId) => (int) $playerId)
            ->filter(fn ($playerId) => $playerId > 0)
            ->values()
            ->all();

        MatchLivePlayerState::query()
            ->where('match_id', $match->id)
            ->where('club_id', $clubId)
            ->get()
            ->each(function (MatchLivePlayerState $state) use ($starterMap, $benchIds): void {
                $playerId = (int) $state->player_id;

                if (isset($starterMap[$playerId])) {
                    $state->update([
                        'slot' => $starterMap[$playerId],
                        'is_on_pitch' => true,
                    ]);

                    return;
                }

                if (in_array($playerId, $benchIds, true) && !$state->is_sent_off && !$state->is_injured) {
                    $state->update([
                        'slot' => null,
                        'is_on_pitch' => false,
                    ]);
                }
            });
    }
}
