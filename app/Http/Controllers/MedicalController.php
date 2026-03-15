<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Player;
use App\Services\InjuryManagementService;
use App\Services\PlayerLoadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MedicalController extends Controller
{
    public function index(Request $request, InjuryManagementService $injuryManagementService, PlayerLoadService $playerLoadService): Response
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;

        if (!$activeClub) {
            $activeClub = $request->user()->isAdmin()
                ? Club::query()->where('is_cpu', false)->orderBy('name')->first()
                : $request->user()->clubs()->where('is_cpu', false)->orderBy('name')->first();
        }

        $board = [
            'injured' => [],
            'monitoring' => [],
            'return_candidates' => [],
            'summary' => [
                'injured_count' => 0,
                'rehab_count' => 0,
                'risk_count' => 0,
            ],
        ];

        if ($activeClub) {
            $activeClub->loadMissing(['players.injuries', 'players.recoveryLogs']);

            foreach ($activeClub->players->sortByDesc('overall') as $player) {
                $injury = $injuryManagementService->syncCurrentInjury($player->loadMissing('injuries'));
                $risk = $playerLoadService->injuryRisk($player);
                $entry = [
                    'id' => $player->id,
                    'name' => $player->full_name,
                    'position' => $player->display_position,
                    'photo_url' => $player->photo_url,
                    'overall' => (int) $player->overall,
                    'fatigue' => (int) $player->fatigue,
                    'sharpness' => (int) $player->sharpness,
                    'medical_status' => $player->medical_status,
                    'injury_risk' => $risk,
                    'injury' => $injury ? [
                        'type' => $injury->injury_type,
                        'severity' => $injury->severity,
                        'expected_return' => $injury->expected_return_at?->format('d.m.Y'),
                        'rehab_intensity' => $injury->rehab_intensity ?: 'medium',
                        'return_phase' => $injury->return_phase ?: 'recovery',
                        'availability_status' => $injury->availability_status ?: 'unavailable',
                        'setback_risk' => (int) $injury->setback_risk,
                        'cleared_at' => $injury->cleared_at?->format('d.m.Y H:i'),
                        'notes' => $injury->notes,
                    ] : null,
                ];

                if ($injury) {
                    $board['injured'][] = $entry;
                    $board['summary']['injured_count']++;
                } elseif (in_array($player->medical_status, ['monitoring', 'risk', 'rehab'], true) || $risk >= 60) {
                    $board['monitoring'][] = $entry;
                    $board['summary']['risk_count']++;
                }

                if (
                    $injury &&
                    $injury->expected_return_at &&
                    now()->diffInDays($injury->expected_return_at, false) <= 3
                ) {
                    $board['return_candidates'][] = $entry;
                    $board['summary']['rehab_count']++;
                }
            }
        }

        return Inertia::render('Medical/Index', [
            'club' => $activeClub ? [
                'id' => $activeClub->id,
                'name' => $activeClub->name,
                'logo_url' => $activeClub->logo_url,
            ] : null,
            'medicalBoard' => $board,
        ]);
    }

    public function updatePlan(Request $request, Player $player, InjuryManagementService $injuryManagementService): RedirectResponse
    {
        $this->ensureOwnership($request, $player);

        $validated = $request->validate([
            'rehab_intensity' => ['required', 'in:low,medium,high'],
            'return_phase' => ['required', 'in:recovery,individual,partial,full'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $injuryManagementService->updateRehabPlan($player, $validated);

        return back()->with('status', 'Medical-Plan wurde aktualisiert.');
    }

    public function updateClearance(Request $request, Player $player, InjuryManagementService $injuryManagementService): RedirectResponse
    {
        $this->ensureOwnership($request, $player);

        $validated = $request->validate([
            'availability_status' => ['required', 'in:unavailable,bench_only,limited,available'],
            'return_phase' => ['nullable', 'in:recovery,individual,partial,full'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $injuryManagementService->updateClearance($player, $validated);

        return back()->with('status', 'Matchday-Freigabe wurde aktualisiert.');
    }

    private function ensureOwnership(Request $request, Player $player): void
    {
        abort_unless(
            $request->user()->isAdmin() || $request->user()->clubs()->whereKey($player->club_id)->exists(),
            403
        );
    }
}
