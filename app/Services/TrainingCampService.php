<?php

namespace App\Services;

use App\Models\Club;
use App\Models\GameNotification;
use App\Models\TrainingCamp;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TrainingCampService
{
    public function __construct(private readonly ClubFinanceLedgerService $financeLedger)
    {
    }

    public function createCamp(Club $club, User $actor, array $payload): TrainingCamp
    {
        $startsOn = \Carbon\Carbon::parse($payload['starts_on']);
        $endsOn = \Carbon\Carbon::parse($payload['ends_on']);
        abort_if($endsOn->lt($startsOn), 422, 'Enddatum muss nach dem Startdatum liegen.');

        $days = max(1, $startsOn->diffInDays($endsOn) + 1);
        $cost = $this->cost((string) $payload['focus'], (string) $payload['intensity'], $days);
        abort_if((float) $club->budget < $cost, 422, 'Nicht genug Budget fuer Trainingslager.');

        [$stamina, $morale, $overall] = $this->effects((string) $payload['focus'], (string) $payload['intensity'], $days);

        return DB::transaction(function () use (
            $club,
            $actor,
            $payload,
            $startsOn,
            $endsOn,
            $cost,
            $stamina,
            $morale,
            $overall
        ): TrainingCamp {
            $camp = TrainingCamp::create([
                'club_id' => $club->id,
                'created_by_user_id' => $actor->id,
                'name' => (string) $payload['name'],
                'focus' => (string) $payload['focus'],
                'intensity' => (string) $payload['intensity'],
                'starts_on' => $startsOn->toDateString(),
                'ends_on' => $endsOn->toDateString(),
                'cost' => $cost,
                'stamina_effect' => $stamina,
                'morale_effect' => $morale,
                'overall_effect' => $overall,
                'status' => $startsOn->isFuture() ? 'planned' : 'active',
                'notes' => $payload['notes'] ?? null,
            ]);

            $this->financeLedger->applyBudgetChange($club, -$cost, [
                'user_id' => $actor->id,
                'context_type' => 'training',
                'reference_type' => 'training_camps',
                'reference_id' => $camp->id,
                'note' => 'Trainingslager: '.$camp->name,
            ]);

            return $camp;
        });
    }

    /**
     * @return array{activated:int,completed:int}
     */
    public function progressDueCamps(): array
    {
        $activated = TrainingCamp::query()
            ->where('status', 'planned')
            ->whereDate('starts_on', '<=', now()->toDateString())
            ->update(['status' => 'active']);

        $camps = TrainingCamp::query()
            ->with('club.players')
            ->where('status', 'active')
            ->whereDate('ends_on', '<=', now()->toDateString())
            ->get();

        foreach ($camps as $camp) {
            DB::transaction(function () use ($camp): void {
                $players = $camp->club?->players ?? collect();

                foreach ($players as $player) {
                    $player->update([
                        'stamina' => max(1, min(100, $player->stamina + $camp->stamina_effect + random_int(-1, 1))),
                        'morale' => max(1, min(100, $player->morale + $camp->morale_effect + random_int(-1, 1))),
                        'overall' => max(1, min(99, $player->overall + $camp->overall_effect)),
                        'last_training_at' => now(),
                    ]);
                }

                $camp->update([
                    'status' => 'completed',
                    'applied_at' => now(),
                ]);

                if ($camp->club?->user_id) {
                    GameNotification::create([
                        'user_id' => $camp->club->user_id,
                        'club_id' => $camp->club->id,
                        'type' => 'training_camp_completed',
                        'title' => 'Trainingslager abgeschlossen',
                        'message' => $camp->name.' wurde erfolgreich abgeschlossen.',
                        'action_url' => '/training-camps',
                    ]);
                }
            });
        }

        return [
            'activated' => $activated,
            'completed' => $camps->count(),
        ];
    }

    private function cost(string $focus, string $intensity, int $days): float
    {
        $focusFactor = match ($focus) {
            'fitness' => 1.0,
            'tactics' => 1.1,
            'technical' => 1.15,
            default => 0.95,
        };

        $intensityFactor = match ($intensity) {
            'low' => 1.0,
            'medium' => 1.4,
            'high' => 1.9,
        };

        return round(9000 * $days * $focusFactor * $intensityFactor, 2);
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private function effects(string $focus, string $intensity, int $days): array
    {
        $intensityBoost = match ($intensity) {
            'low' => 1,
            'medium' => 2,
            'high' => 3,
        };

        $daysFactor = max(1, min(4, (int) floor($days / 3)));

        return match ($focus) {
            'fitness' => [2 * $intensityBoost, 1, 0],
            'tactics' => [1, 1 * $daysFactor, 1],
            'technical' => [0, 1, 1 * $daysFactor],
            default => [1, 2 * $daysFactor, 0],
        };
    }
}
