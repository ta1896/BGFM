<?php

namespace App\Services;

use App\Models\Club;
use App\Models\ClubFinancialTransaction;
use App\Models\GameNotification;
use App\Models\Player;
use App\Models\RandomEventOccurrence;
use App\Models\RandomEventTemplate;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RandomEventService
{
    /**
     * @return Collection<int, RandomEventTemplate>
     */
    public function availableTemplatesForClub(Club $club): Collection
    {
        return RandomEventTemplate::query()
            ->where('is_active', true)
            ->where(function ($query) use ($club): void {
                $query->whereNull('min_reputation')
                    ->orWhere('min_reputation', '<=', $club->reputation);
            })
            ->where(function ($query) use ($club): void {
                $query->whereNull('max_reputation')
                    ->orWhere('max_reputation', '>=', $club->reputation);
            })
            ->orderByDesc('probability_weight')
            ->orderBy('id')
            ->get();
    }

    public function triggerForClub(Club $club, ?User $actor = null): ?RandomEventOccurrence
    {
        $template = $this->weightedTemplate($club);
        if (!$template) {
            return null;
        }

        $player = $this->resolveTargetPlayer($club, $template);
        $budgetDelta = $this->randomBetween((int) $template->budget_delta_min, (int) $template->budget_delta_max);

        $payload = [
            'budget_delta' => $budgetDelta,
            'morale_delta' => (int) $template->morale_delta,
            'stamina_delta' => (int) $template->stamina_delta,
            'overall_delta' => (int) $template->overall_delta,
            'fan_mood_delta' => (int) $template->fan_mood_delta,
            'board_confidence_delta' => (int) $template->board_confidence_delta,
        ];

        $message = $template->description_template
            ? $this->renderMessage($template->description_template, $club, $player, $budgetDelta)
            : $this->defaultMessage($template->name, $club, $player, $budgetDelta);

        return RandomEventOccurrence::create([
            'template_id' => $template->id,
            'club_id' => $club->id,
            'player_id' => $player?->id,
            'triggered_by_user_id' => $actor?->id,
            'status' => 'pending',
            'title' => $template->name,
            'message' => $message,
            'happened_on' => now()->toDateString(),
            'effect_payload' => $payload,
        ]);
    }

    public function apply(RandomEventOccurrence $occurrence): RandomEventOccurrence
    {
        if ($occurrence->status === 'applied') {
            return $occurrence;
        }

        return DB::transaction(function () use ($occurrence): RandomEventOccurrence {
            $occurrence->loadMissing(['club.user', 'player', 'template']);
            $club = $occurrence->club;
            $payload = $occurrence->effect_payload ?? [];

            $budgetDelta = (int) ($payload['budget_delta'] ?? 0);
            $moraleDelta = (int) ($payload['morale_delta'] ?? 0);
            $staminaDelta = (int) ($payload['stamina_delta'] ?? 0);
            $overallDelta = (int) ($payload['overall_delta'] ?? 0);
            $fanMoodDelta = (int) ($payload['fan_mood_delta'] ?? 0);
            $boardDelta = (int) ($payload['board_confidence_delta'] ?? 0);

            $club->update([
                'budget' => (float) $club->budget + $budgetDelta,
                'fan_mood' => $this->clamp((int) $club->fan_mood + $fanMoodDelta, 0, 100),
                'board_confidence' => $this->clamp((int) $club->board_confidence + $boardDelta, 0, 100),
            ]);

            if ($occurrence->player) {
                $occurrence->player->update([
                    'morale' => $this->clamp((int) $occurrence->player->morale + $moraleDelta, 0, 100),
                    'stamina' => $this->clamp((int) $occurrence->player->stamina + $staminaDelta, 0, 100),
                    'overall' => $this->clamp((int) $occurrence->player->overall + $overallDelta, 1, 99),
                ]);
            }

            if ($budgetDelta !== 0) {
                ClubFinancialTransaction::create([
                    'club_id' => $club->id,
                    'user_id' => $occurrence->triggered_by_user_id,
                    'context_type' => 'other',
                    'direction' => $budgetDelta > 0 ? 'income' : 'expense',
                    'amount' => abs($budgetDelta),
                    'balance_after' => (float) $club->budget,
                    'reference_type' => 'random_event_occurrences',
                    'reference_id' => $occurrence->id,
                    'booked_at' => now(),
                    'note' => 'Random Event: '.$occurrence->title,
                ]);
            }

            if ($club->user_id) {
                GameNotification::create([
                    'user_id' => $club->user_id,
                    'club_id' => $club->id,
                    'type' => 'random_event',
                    'title' => $occurrence->title,
                    'message' => $occurrence->message,
                    'action_url' => '/random-events?club='.$club->id,
                ]);
            }

            $occurrence->update([
                'status' => 'applied',
                'applied_at' => now(),
            ]);

            return $occurrence->fresh(['club', 'player', 'template']);
        });
    }

    public function triggerAutomatedEvents(float $chancePerClub = 0.2): array
    {
        $generated = 0;
        $applied = 0;

        $clubs = Club::query()
            ->whereNotNull('user_id')
            ->with('players')
            ->get();

        foreach ($clubs as $club) {
            if (lcg_value() > $chancePerClub) {
                continue;
            }

            $event = $this->triggerForClub($club, null);
            if (!$event) {
                continue;
            }

            $generated++;
            $this->apply($event);
            $applied++;
        }

        return [
            'generated' => $generated,
            'applied' => $applied,
        ];
    }

    private function weightedTemplate(Club $club): ?RandomEventTemplate
    {
        $templates = $this->availableTemplatesForClub($club);
        if ($templates->isEmpty()) {
            return null;
        }

        $totalWeight = max(1, (int) $templates->sum('probability_weight'));
        $roll = random_int(1, $totalWeight);
        $running = 0;

        foreach ($templates as $template) {
            $running += max(1, (int) $template->probability_weight);
            if ($roll <= $running) {
                return $template;
            }
        }

        return $templates->last();
    }

    private function resolveTargetPlayer(Club $club, RandomEventTemplate $template): ?Player
    {
        if (!in_array($template->category, ['player', 'discipline', 'medical'], true)) {
            return null;
        }

        return $club->players()
            ->where('status', 'active')
            ->inRandomOrder()
            ->first();
    }

    private function renderMessage(string $template, Club $club, ?Player $player, int $budgetDelta): string
    {
        return strtr($template, [
            '{club}' => $club->name,
            '{player}' => $player?->full_name ?? 'Kader',
            '{amount}' => number_format(abs($budgetDelta), 0, ',', '.').' EUR',
            '{direction}' => $budgetDelta >= 0 ? 'erhoeht' : 'gesenkt',
        ]);
    }

    private function defaultMessage(string $name, Club $club, ?Player $player, int $budgetDelta): string
    {
        if ($budgetDelta > 0) {
            return $name.': '.$club->name.' erhaelt +'.number_format($budgetDelta, 0, ',', '.').' EUR.';
        }

        if ($budgetDelta < 0) {
            return $name.': '.$club->name.' verliert '.number_format(abs($budgetDelta), 0, ',', '.').' EUR.';
        }

        if ($player) {
            return $name.': '.$player->full_name.' ist direkt betroffen.';
        }

        return $name.': Ereignis wurde fuer '.$club->name.' ausgeloest.';
    }

    private function randomBetween(int $min, int $max): int
    {
        if ($max < $min) {
            return $min;
        }

        return random_int($min, $max);
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
