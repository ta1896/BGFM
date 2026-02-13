<?php

namespace App\Http\Controllers;

use App\Models\TrainingSession;
use App\Services\TrainingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(Request $request): View
    {
        $clubs = $request->user()->clubs()->with('players')->orderBy('name')->get();
        $clubIds = $clubs->pluck('id');

        $selectedClubId = (int) $request->query('club');
        if ($selectedClubId > 0 && !$clubIds->contains($selectedClubId)) {
            $selectedClubId = 0;
        }

        $normalizeDate = static function (?string $value): ?string {
            if (!$value) {
                return null;
            }

            try {
                return Carbon::createFromFormat('Y-m-d', $value)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        };

        $rangeFilter = (string) $request->query('range', '');
        if (!in_array($rangeFilter, ['today', 'week'], true)) {
            $rangeFilter = '';
        }

        $selectedDate = $normalizeDate($request->query('date') ?? $request->query('day'));
        $dateFrom = $normalizeDate($request->query('from'));
        $dateTo = $normalizeDate($request->query('to'));

        if ($rangeFilter === 'today') {
            $selectedDate = now()->toDateString();
            $dateFrom = $selectedDate;
            $dateTo = $selectedDate;
        } elseif ($rangeFilter === 'week') {
            $selectedDate = null;
            $dateFrom = now()->startOfWeek(Carbon::MONDAY)->toDateString();
            $dateTo = now()->startOfWeek(Carbon::MONDAY)->addDays(6)->toDateString();
        } elseif ($selectedDate) {
            $dateFrom = $selectedDate;
            $dateTo = $selectedDate;
        }

        $sessions = TrainingSession::query()
            ->with(['club', 'players'])
            ->whereIn('club_id', $clubIds)
            ->when($selectedClubId > 0, fn ($query) => $query->where('club_id', $selectedClubId))
            ->when($dateFrom, fn ($query) => $query->whereDate('session_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('session_date', '<=', $dateTo))
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->paginate(12);
        $sessions->appends($request->query());

        return view('training.index', [
            'clubs' => $clubs,
            'sessions' => $sessions,
            'filters' => [
                'club' => $selectedClubId > 0 ? $selectedClubId : null,
                'range' => $rangeFilter,
                'date' => $selectedDate,
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'type' => ['required', 'in:fitness,tactics,technical,recovery,friendly'],
            'intensity' => ['required', 'in:low,medium,high'],
            'focus_position' => ['nullable', 'in:GK,DEF,MID,FWD'],
            'session_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'player_ids' => ['required', 'array', 'min:1'],
            'player_ids.*' => ['integer', 'exists:players,id'],
        ]);

        $club = $request->user()->clubs()->with('players')->whereKey((int) $validated['club_id'])->first();
        abort_unless($club, 403);

        $playerIds = collect($validated['player_ids'])->map(static fn ($id) => (int) $id)->unique()->values();
        $clubPlayerIds = $club->players->pluck('id');
        abort_if($playerIds->diff($clubPlayerIds)->isNotEmpty(), 403);

        [$moraleEffect, $staminaEffect, $formEffect] = $this->effectPreset($validated['type'], $validated['intensity']);

        $session = TrainingSession::create([
            'club_id' => $club->id,
            'created_by_user_id' => $request->user()->id,
            'type' => $validated['type'],
            'intensity' => $validated['intensity'],
            'focus_position' => $validated['focus_position'] ?? null,
            'session_date' => $validated['session_date'],
            'morale_effect' => $moraleEffect,
            'stamina_effect' => $staminaEffect,
            'form_effect' => $formEffect,
            'notes' => $validated['notes'] ?? null,
        ]);

        $pivot = $playerIds->mapWithKeys(function (int $playerId) use ($moraleEffect, $staminaEffect, $formEffect) {
            return [
                $playerId => [
                    'role' => 'participant',
                    'stamina_delta' => $staminaEffect,
                    'morale_delta' => $moraleEffect,
                    'overall_delta' => $formEffect,
                ],
            ];
        })->all();

        $session->players()->sync($pivot);

        return back()->with('status', 'Trainingseinheit wurde erstellt.');
    }

    public function apply(Request $request, TrainingSession $session, TrainingService $trainingService): RedirectResponse
    {
        abort_unless($request->user()->clubs()->whereKey($session->club_id)->exists(), 403);

        $trainingService->applySession($session);

        return back()->with('status', 'Trainingseffekte wurden angewendet.');
    }

    private function effectPreset(string $type, string $intensity): array
    {
        $base = match ($type) {
            'fitness' => [1, -2, 0],
            'tactics' => [1, -1, 1],
            'technical' => [0, -1, 1],
            'recovery' => [2, 2, 0],
            default => [1, -1, 0],
        };

        $multiplier = match ($intensity) {
            'low' => 1,
            'medium' => 2,
            'high' => 3,
        };

        return [
            $base[0] * $multiplier,
            $base[1] * $multiplier,
            $base[2] * $multiplier,
        ];
    }
}
