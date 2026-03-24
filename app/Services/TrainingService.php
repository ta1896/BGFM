<?php

namespace App\Services;

use App\Models\GameNotification;
use App\Models\Club;
use App\Models\Player;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Models\TrainingType;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\CarbonInterface;

class TrainingService
{
    private const SESSION_EFFECT_KEYS = [
        'morale_effect',
        'stamina_effect',
        'form_effect',
    ];

    public function __construct(
        private readonly PlayerLoadService $playerLoadService,
        private readonly PlayerMoraleService $playerMoraleService,
        private readonly SquadHierarchyService $squadHierarchyService,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createSession(Club $club, User $actor, array $payload): TrainingSession
    {
        $trainingType = TrainingType::query()
            ->whereKey((int) ($payload['training_type_id'] ?? 0))
            ->where('is_active', true)
            ->firstOrFail();

        $teamFocus = (string) ($payload['team_focus'] ?? $trainingType->team_focus);
        $unitFocus = (string) ($payload['unit_focus'] ?? ($trainingType->unit_focus ?: $trainingType->team_focus));
        $intensity = (string) ($payload['intensity'] ?? 'medium');
        $unitGroups = array_values(array_unique((array) ($payload['unit_groups'] ?? [])));
        $trainingGroupIds = collect((array) ($payload['training_group_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
        $groupPlayers = TrainingGroup::query()
            ->with('players:id')
            ->whereIn('id', $trainingGroupIds)
            ->where('club_id', $club->id)
            ->get()
            ->flatMap(fn (TrainingGroup $group) => $group->players->pluck('id'))
            ->map(fn ($id) => (int) $id);
        $playerIds = collect((array) ($payload['player_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->merge($groupPlayers)
            ->unique()
            ->values();
        $playerPlans = (array) ($payload['player_plans'] ?? []);
        $effectBlueprint = $this->normalizeEffectBlueprint((array) ($trainingType->effects ?? []));
        $sessionEffects = $this->sessionEffectsFromBlueprint($effectBlueprint, $intensity);

        $session = TrainingSession::create([
            'club_id' => $club->id,
            'created_by_user_id' => $actor->id,
            'training_type_id' => $trainingType->id,
            'training_type_name' => $trainingType->name,
            'type' => $trainingType->category,
            'team_focus' => $teamFocus,
            'unit_focus' => $unitFocus,
            'intensity' => $intensity,
            'focus_position' => $payload['focus_position'] ?? null,
            'unit_groups' => $unitGroups,
            'effect_blueprint' => $effectBlueprint,
            'session_date' => $payload['session_date'],
            'morale_effect' => $sessionEffects['morale_effect'],
            'stamina_effect' => $sessionEffects['stamina_effect'],
            'form_effect' => $sessionEffects['form_effect'],
            'notes' => $payload['notes'] ?? null,
        ]);

        $clubPlayers = $club->players()->get()->keyBy('id');
        $pivot = $playerIds
            ->mapWithKeys(function (int $playerId) use ($clubPlayers, $playerPlans, $session): array {
                /** @var Player|null $player */
                $player = $clubPlayers->get($playerId);
                if (!$player) {
                    return [];
                }

                $plan = (array) ($playerPlans[$playerId] ?? []);
                $focusGroup = $this->groupFromPosition((string) ($player->position_main ?: $player->position));
                $individualIntensity = (string) ($plan['intensity'] ?? $session->intensity);
                $playerAttributeDeltas = $this->attributeDeltasForPlayer($player, $session, $focusGroup, $individualIntensity);
                $staminaDelta = $this->staminaDeltaForPlayer($session, $focusGroup, $individualIntensity);
                $moraleDelta = $this->moraleDeltaForPlayer($player, $session, $individualIntensity);
                $overallDelta = $this->overallDeltaForPlayer($session, $focusGroup, $individualIntensity, $player);

                return [
                    $playerId => [
                        'role' => 'participant',
                        'focus_group' => $focusGroup,
                        'primary_focus' => $this->normalizeFocus((string) ($plan['primary_focus'] ?? $session->team_focus)),
                        'secondary_focus' => $this->normalizeFocus((string) ($plan['secondary_focus'] ?? $session->unit_focus)),
                        'individual_intensity' => $individualIntensity,
                        'stamina_delta' => $staminaDelta,
                        'morale_delta' => $moraleDelta,
                        'overall_delta' => $overallDelta,
                        'attribute_deltas' => $playerAttributeDeltas,
                    ],
                ];
            })
            ->all();

        $session->players()->sync($pivot);
        if ($trainingGroupIds->isNotEmpty()) {
            $session->trainingGroups()->sync($trainingGroupIds->all());
        }

        return $session;
    }

    public function applySession(TrainingSession $session): void
    {
        if ($session->is_applied) {
            return;
        }

        $session->loadMissing(['club', 'players', 'club.user']);

        DB::transaction(function () use ($session): void {
            foreach ($session->players as $player) {
                /** @var Player $player */
                $staminaDelta = (int) $player->pivot->stamina_delta;
                $moraleDelta = (int) $player->pivot->morale_delta;
                $overallDelta = (int) $player->pivot->overall_delta;
                $attributeDeltas = (array) ($player->pivot->attribute_deltas ?? []);

                $updates = [
                    'stamina' => max(1, min(100, $player->stamina + $staminaDelta)),
                    'morale' => max(1, min(100, $player->morale + $moraleDelta)),
                    'overall' => max(1, min(99, $player->overall + $overallDelta)),
                    'last_training_at' => now(),
                ];

                foreach ($attributeDeltas as $attribute => $delta) {
                    $maxValue = $attribute === 'potential' ? 99 : 99;
                    $updates[$attribute] = max(1, min($maxValue, (int) $player->{$attribute} + $delta));
                }

                $player->update($updates);

                $this->playerLoadService->applyTrainingLoad($player->fresh(), $session, $staminaDelta);
                $this->playerMoraleService->refresh($player->fresh()->loadMissing(['playtimePromises', 'injuries']));
            }

            $this->squadHierarchyService->refreshForClub($session->club);

            $session->update([
                'is_applied' => true,
                'applied_at' => now(),
            ]);

            if ($session->club->user_id) {
                GameNotification::create([
                    'user_id' => $session->club->user_id,
                    'club_id' => $session->club_id,
                    'type' => 'training_applied',
                    'title' => 'Training abgeschlossen',
                    'message' => 'Die Session vom '.$session->session_date?->format('d.m.Y').' wurde angewendet.',
                    'action_url' => '/training',
                ]);
            }
        });
    }

    /**
     * @return array{found:int,applied:int,already_applied:int}
     */
    public function applyScheduledSessionsForDate(CarbonInterface|string|null $date = null): array
    {
        $targetDate = $date instanceof CarbonInterface
            ? $date->toDateString()
            : (string) ($date ?: now()->toDateString());

        $sessions = TrainingSession::query()
            ->whereDate('session_date', $targetDate)
            ->orderBy('id')
            ->get();

        $summary = [
            'found' => $sessions->count(),
            'applied' => 0,
            'already_applied' => 0,
        ];

        foreach ($sessions as $session) {
            if ($session->is_applied) {
                $summary['already_applied']++;
                continue;
            }

            $this->applySession($session);
            $summary['applied']++;
        }

        return $summary;
    }

    private function groupFromPosition(string $position): string
    {
        $normalized = strtoupper(trim(preg_replace('/-(L|R)$/', '', $position)));

        return match ($normalized) {
            'TW', 'GK' => 'GK',
            'LV', 'RV', 'LWB', 'RWB', 'IV' => 'DEF',
            'DM', 'ZM', 'OM', 'ZOM', 'LM', 'RM', 'LAM', 'RAM' => 'MID',
            default => 'FWD',
        };
    }

    private function normalizeFocus(string $focus): string
    {
        return trim($focus) !== '' ? trim($focus) : 'build_up';
    }

    private function intensityFactor(string $intensity): float
    {
        return match ($intensity) {
            'low' => 0.85,
            'high' => 1.3,
            default => 1.0,
        };
    }

    private function groupFactor(TrainingSession $session, string $focusGroup): float
    {
        $unitGroups = (array) ($session->unit_groups ?? []);
        if ($unitGroups === []) {
            return 1.0;
        }

        return in_array($focusGroup, $unitGroups, true) ? 1.15 : 0.82;
    }

    private function playerDevelopmentFactor(Player $player): float
    {
        $growthRoom = max(0, (int) $player->potential - (int) $player->overall);
        $ageFactor = $player->age <= 21 ? 1.18 : ($player->age <= 25 ? 1.08 : ($player->age >= 31 ? 0.88 : 1.0));
        $fatigueFactor = max(0.72, 1 - (max(0, (int) $player->fatigue - 35) / 180));

        return (1 + min(0.24, $growthRoom / 120)) * $ageFactor * $fatigueFactor;
    }

    private function staminaDeltaForPlayer(TrainingSession $session, string $focusGroup, string $intensity): int
    {
        $base = (int) $session->stamina_effect;
        $groupFactor = $this->groupFactor($session, $focusGroup);
        $intensityFactor = $this->intensityFactor($intensity);

        return (int) round($base * $groupFactor * $intensityFactor);
    }

    private function moraleDeltaForPlayer(Player $player, TrainingSession $session, string $intensity): int
    {
        $base = (int) $session->morale_effect;
        $intensityPenalty = $intensity === 'high' && $player->happiness < 45 ? -1 : 0;

        return $base + $intensityPenalty;
    }

    private function overallDeltaForPlayer(TrainingSession $session, string $focusGroup, string $intensity, Player $player): int
    {
        $base = (int) $session->form_effect;
        $factor = $this->playerDevelopmentFactor($player) * $this->groupFactor($session, $focusGroup) * $this->intensityFactor($intensity);

        return (int) round($base * $factor);
    }

    /**
     * @return array<string, int>
     */
    private function attributeDeltasForPlayer(Player $player, TrainingSession $session, string $focusGroup, string $individualIntensity): array
    {
        $developmentFactor = $this->playerDevelopmentFactor($player) * $this->groupFactor($session, $focusGroup) * $this->intensityFactor($individualIntensity);

        $deltas = [];
        foreach ($this->playerEffectBlueprint($session) as $effect) {
            $attribute = (string) ($effect['attribute'] ?? '');
            $baseDelta = (int) ($effect['delta'] ?? 0);
            if ($attribute === '' || $baseDelta === 0) {
                continue;
            }

            $scaled = (int) round($baseDelta * $developmentFactor);
            if ($scaled === 0) {
                $scaled = $baseDelta > 0 ? 1 : -1;
            }

            $deltas[$attribute] = ($deltas[$attribute] ?? 0) + $scaled;
        }

        if ($player->age <= 22 && max(0, (int) $player->potential - (int) $player->overall) >= 8 && $developmentFactor >= 1.05) {
            $deltas['potential'] = 1;
        }

        return $deltas;
    }

    /**
     * @param array<int, mixed> $effects
     * @return array<int, array{attribute:string,delta:int}>
     */
    private function normalizeEffectBlueprint(array $effects): array
    {
        return collect($effects)
            ->map(function ($effect): ?array {
                $attribute = trim((string) ($effect['attribute'] ?? ''));
                $delta = (int) ($effect['delta'] ?? 0);

                if ($attribute === '' || $delta === 0) {
                    return null;
                }

                return [
                    'attribute' => $attribute,
                    'delta' => $delta,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param array<int, array{attribute:string,delta:int}> $effectBlueprint
     * @return array{morale_effect:int,stamina_effect:int,form_effect:int}
     */
    private function sessionEffectsFromBlueprint(array $effectBlueprint, string $intensity): array
    {
        $multiplier = $this->intensityMultiplier($intensity);
        $summary = [
            'morale_effect' => 0,
            'stamina_effect' => 0,
            'form_effect' => 0,
        ];

        foreach ($effectBlueprint as $effect) {
            if (!in_array($effect['attribute'], self::SESSION_EFFECT_KEYS, true)) {
                continue;
            }

            $summary[$effect['attribute']] += (int) round($effect['delta'] * $multiplier);
        }

        return $summary;
    }

    private function intensityMultiplier(string $intensity): float
    {
        return match ($intensity) {
            'low' => 1.0,
            'high' => 1.5,
            default => 1.25,
        };
    }

    /**
     * @return array<int, array{attribute:string,delta:int}>
     */
    private function playerEffectBlueprint(TrainingSession $session): array
    {
        return collect((array) ($session->effect_blueprint ?? []))
            ->filter(fn (array $effect): bool => !in_array((string) ($effect['attribute'] ?? ''), self::SESSION_EFFECT_KEYS, true))
            ->values()
            ->all();
    }
}
