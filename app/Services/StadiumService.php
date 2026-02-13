<?php

namespace App\Services;

use App\Models\Club;
use App\Models\GameNotification;
use App\Models\Stadium;
use App\Models\StadiumProject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StadiumService
{
    public function __construct(private readonly ClubFinanceLedgerService $financeLedger)
    {
    }

    public function ensureForClub(Club $club): Stadium
    {
        return Stadium::firstOrCreate(
            ['club_id' => $club->id],
            [
                'name' => $club->name.' Arena',
                'capacity' => 18000,
                'covered_seats' => 9000,
                'vip_seats' => 900,
                'ticket_price' => 18,
                'maintenance_cost' => 25000,
                'facility_level' => 1,
                'pitch_quality' => 60,
                'fan_experience' => 60,
                'security_level' => 55,
                'environment_level' => 55,
            ]
        );
    }

    public function startProject(Club $club, User $actor, string $type): StadiumProject
    {
        $stadium = $this->ensureForClub($club);
        abort_if(
            $stadium->projects()->whereIn('status', ['planned', 'active'])->exists(),
            422,
            'Es laeuft bereits ein Stadionprojekt.'
        );

        [$cost, $durationDays, $from, $to] = $this->projectConfig($stadium, $type);
        abort_if((float) $club->budget < $cost, 422, 'Nicht genug Budget fuer dieses Stadionprojekt.');

        return DB::transaction(function () use ($club, $stadium, $actor, $type, $cost, $durationDays, $from, $to): StadiumProject {
            $project = StadiumProject::create([
                'stadium_id' => $stadium->id,
                'project_type' => $type,
                'level_from' => $from,
                'level_to' => $to,
                'cost' => $cost,
                'started_on' => now()->toDateString(),
                'completes_on' => now()->addDays($durationDays)->toDateString(),
                'status' => 'active',
            ]);

            $this->financeLedger->applyBudgetChange($club, -$cost, [
                'user_id' => $actor->id,
                'context_type' => 'stadium',
                'reference_type' => 'stadium_projects',
                'reference_id' => $project->id,
                'note' => 'Stadionprojekt: '.strtoupper($type),
            ]);

            return $project;
        });
    }

    public function completeDueProjects(): int
    {
        $projects = StadiumProject::query()
            ->with(['stadium.club'])
            ->where('status', 'active')
            ->whereDate('completes_on', '<=', now()->toDateString())
            ->get();

        foreach ($projects as $project) {
            DB::transaction(function () use ($project): void {
                $stadium = $project->stadium;
                if (!$stadium) {
                    return;
                }

                $this->applyProjectEffect($stadium, $project);
                $project->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                $club = $stadium->club;
                if ($club?->user_id) {
                    GameNotification::create([
                        'user_id' => $club->user_id,
                        'club_id' => $club->id,
                        'type' => 'stadium_project_done',
                        'title' => 'Stadionprojekt abgeschlossen',
                        'message' => strtoupper($project->project_type).' Upgrade ist abgeschlossen.',
                        'action_url' => '/stadium',
                    ]);
                }
            });
        }

        return $projects->count();
    }

    /**
     * @return array{0:float,1:int,2:int,3:int}
     */
    private function projectConfig(Stadium $stadium, string $type): array
    {
        return match ($type) {
            'capacity' => [
                160000 + ($stadium->capacity * 2.5),
                12,
                (int) $stadium->capacity,
                min(85000, (int) $stadium->capacity + 5000),
            ],
            'pitch' => [
                95000 + ($stadium->pitch_quality * 900),
                8,
                (int) $stadium->pitch_quality,
                min(99, (int) $stadium->pitch_quality + 6),
            ],
            'facility' => [
                110000 + ($stadium->facility_level * 65000),
                10,
                (int) $stadium->facility_level,
                min(10, (int) $stadium->facility_level + 1),
            ],
            'security' => [
                80000 + ($stadium->security_level * 800),
                9,
                (int) $stadium->security_level,
                min(99, (int) $stadium->security_level + 5),
            ],
            'environment' => [
                70000 + ($stadium->environment_level * 700),
                7,
                (int) $stadium->environment_level,
                min(99, (int) $stadium->environment_level + 5),
            ],
            'vip' => [
                65000 + ($stadium->vip_seats * 18),
                6,
                (int) $stadium->vip_seats,
                min((int) $stadium->capacity / 4, (int) $stadium->vip_seats + 250),
            ],
            default => throw new \InvalidArgumentException('Unbekannter Projekttyp.'),
        };
    }

    private function applyProjectEffect(Stadium $stadium, StadiumProject $project): void
    {
        $type = $project->project_type;

        if ($type === 'capacity') {
            $stadium->capacity = max($stadium->capacity, (int) ($project->level_to ?? $stadium->capacity));
            $stadium->covered_seats = min($stadium->capacity, $stadium->covered_seats + 3000);
        } elseif ($type === 'pitch') {
            $stadium->pitch_quality = (int) ($project->level_to ?? $stadium->pitch_quality);
        } elseif ($type === 'facility') {
            $stadium->facility_level = (int) ($project->level_to ?? $stadium->facility_level);
            $stadium->fan_experience = min(99, $stadium->fan_experience + 4);
        } elseif ($type === 'security') {
            $stadium->security_level = (int) ($project->level_to ?? $stadium->security_level);
        } elseif ($type === 'environment') {
            $stadium->environment_level = (int) ($project->level_to ?? $stadium->environment_level);
            $stadium->fan_experience = min(99, $stadium->fan_experience + 2);
        } elseif ($type === 'vip') {
            $stadium->vip_seats = (int) ($project->level_to ?? $stadium->vip_seats);
            $stadium->ticket_price = min(80, (float) $stadium->ticket_price + 1.5);
        }

        $stadium->maintenance_cost = round(
            (float) $stadium->maintenance_cost + (float) $project->cost * 0.008,
            2
        );

        $stadium->save();
    }
}
