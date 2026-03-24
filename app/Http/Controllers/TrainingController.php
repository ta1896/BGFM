<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Models\TrainingType;
use App\Services\InjuryManagementService;
use App\Services\PlayerLoadService;
use App\Services\PlayerMoraleService;
use App\Services\SquadHierarchyService;
use App\Services\TrainingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TrainingController extends Controller
{
    public function index(
        Request $request,
        SquadHierarchyService $squadHierarchyService,
        PlayerMoraleService $playerMoraleService,
        PlayerLoadService $playerLoadService,
        InjuryManagementService $injuryManagementService,
    ): \Inertia\Response
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;

        if (!$activeClub) {
            $activeClub = $request->user()->isAdmin()
                ? Club::query()->where('is_cpu', false)->orderBy('name')->first()
                : $request->user()->clubs()->where('is_cpu', false)->orderBy('name')->first();
        }

        if ($activeClub) {
            $squadHierarchyService->refreshForClub($activeClub);
            $activeClub->loadMissing(['players']);
            $activeClub->players->each(function ($player) use ($playerMoraleService, $injuryManagementService): void {
                $injuryManagementService->syncCurrentInjury($player);
                $playerMoraleService->refresh($player->loadMissing(['playtimePromises', 'injuries']));
            });
        }

        $normalizeDate = static function (?string $value): ?string {
            if (!$value) return null;
            try {
                return Carbon::createFromFormat('Y-m-d', $value)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        };

        $rangeFilter = (string) $request->query('range', '');
        if (!in_array($rangeFilter, ['today', 'week'], true)) $rangeFilter = '';

        $selectedDate = $normalizeDate($request->query('date') ?? $request->query('day'));
        $dateFrom = $normalizeDate($request->query('from'));
        $dateTo = $normalizeDate($request->query('to'));

        if ($rangeFilter === 'today') {
            $selectedDate = now()->toDateString();
            $dateFrom = $selectedDate;
            $dateTo = $selectedDate;
        } elseif ($rangeFilter === 'week') {
            $dateFrom = now()->startOfWeek(Carbon::MONDAY)->toDateString();
            $dateTo = now()->startOfWeek(Carbon::MONDAY)->addDays(6)->toDateString();
        } elseif ($selectedDate) {
            $dateFrom = $selectedDate;
            $dateTo = $selectedDate;
        }

        $sessions = TrainingSession::query()
            ->with(['players:id,first_name,last_name', 'trainingGroups:id,name,color', 'trainingType:id,name,tone,icon'])
            ->when($activeClub, fn($query) => $query->where('club_id', $activeClub->id))
            ->when($dateFrom, fn($query) => $query->whereDate('session_date', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->whereDate('session_date', '<=', $dateTo))
            ->orderByDesc('session_date')
            ->latest('id')
            ->paginate(12)
            ->through(fn (TrainingSession $session) => $this->mapSessionPayload($session))
            ->withQueryString();

        $weekStart = Carbon::parse($prefillDate = now()->toDateString())->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);

        $weekSessions = TrainingSession::query()
            ->with(['players:id,first_name,last_name', 'trainingGroups:id,name,color', 'trainingType:id,name,tone,icon'])
            ->when($activeClub, fn($query) => $query->where('club_id', $activeClub->id))
            ->whereBetween('session_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('session_date')
            ->orderBy('id')
            ->get();

        $sessionsByDay = $weekSessions->groupBy(fn (TrainingSession $session) => $session->session_date?->toDateString());
        $weekDays = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $sessionsByDay): array {
            $date = $weekStart->copy()->addDays($offset);
            $dateKey = $date->toDateString();

            return [
                'date' => $dateKey,
                'label' => $date->translatedFormat('D d.m.'),
                'is_today' => $date->isSameDay(now()),
                'sessions' => ($sessionsByDay->get($dateKey) ?? collect())
                    ->map(fn (TrainingSession $session) => $this->mapSessionPayload($session))
                    ->values()
                    ->all(),
            ];
        })->values()->all();

        $trainingGroups = $activeClub
            ? $activeClub->trainingGroups()
                ->with(['players:id,first_name,last_name,position,position_main,overall'])
                ->orderBy('name')
                ->get()
                ->map(fn (TrainingGroup $group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'color' => $group->color,
                    'notes' => $group->notes,
                    'player_ids' => $group->players->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                    'players' => $group->players->map(fn ($player) => [
                        'id' => $player->id,
                        'name' => $player->full_name,
                        'position' => $player->position_main ?: $player->position,
                        'overall' => (int) $player->overall,
                    ])->values()->all(),
                ])
                ->values()
                ->all()
            : [];

        $trainingTypes = TrainingType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (TrainingType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
                'description' => $type->description,
                'category' => $type->category,
                'team_focus' => $type->team_focus,
                'unit_focus' => $type->unit_focus,
                'default_intensity' => $type->default_intensity,
                'tone' => $type->tone,
                'icon' => $type->icon,
                'effects' => collect((array) $type->effects)
                    ->map(fn (array $effect) => [
                        'attribute' => (string) ($effect['attribute'] ?? ''),
                        'delta' => (int) ($effect['delta'] ?? 0),
                    ])
                    ->filter(fn (array $effect) => $effect['attribute'] !== '' && $effect['delta'] !== 0)
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return \Inertia\Inertia::render('Training/Index', [
            'sessions' => $sessions,
            'weekDays' => $weekDays,
            'club' => $activeClub ? [
                'id' => $activeClub->id,
                'name' => $activeClub->name,
                'players' => $activeClub->players->map(fn ($player) => [
                    'id' => $player->id,
                    'name' => $player->full_name,
                    'age' => (int) $player->age,
                    'position' => $player->position_main ?: $player->position,
                    'position_group' => $this->groupFromPosition((string) ($player->position_main ?: $player->position)),
                    'overall' => (int) $player->overall,
                    'potential' => (int) $player->potential,
                    'fatigue' => (int) $player->fatigue,
                    'sharpness' => (int) $player->sharpness,
                    'happiness' => (int) $player->happiness,
                    'stamina' => (int) $player->stamina,
                    'morale' => (int) $player->morale,
                    'injury_risk' => $playerLoadService->injuryRisk($player),
                    'medical_status' => $player->medical_status,
                    'attr_attacking' => (int) $player->attr_attacking,
                    'technical' => (int) $player->technical,
                    'attr_technical' => (int) $player->attr_technical,
                    'attr_tactical' => (int) $player->attr_tactical,
                    'attr_defending' => (int) $player->attr_defending,
                    'attr_creativity' => (int) $player->attr_creativity,
                    'pace' => (int) $player->pace,
                    'shooting' => (int) $player->shooting,
                    'passing' => (int) $player->passing,
                    'defending' => (int) $player->defending,
                    'physical' => (int) $player->physical,
                ])->values()->all(),
                'medical_summary' => [
                    'risk_count' => $activeClub->players->filter(fn ($player) => $playerLoadService->injuryRisk($player) >= 60)->count(),
                    'rehab_count' => $activeClub->players->where('medical_status', 'rehab')->count(),
                    'monitoring_count' => $activeClub->players->whereIn('medical_status', ['monitoring', 'risk'])->count(),
                ],
                'load_rows' => $activeClub->players
                    ->sortByDesc('overall')
                    ->map(fn ($player) => [
                        'id' => $player->id,
                        'name' => $player->full_name,
                        'position' => $player->position_main ?: $player->position,
                        'fatigue' => (int) $player->fatigue,
                        'sharpness' => (int) $player->sharpness,
                        'happiness' => (int) $player->happiness,
                        'medical_status' => $player->medical_status,
                        'injury_risk' => $playerLoadService->injuryRisk($player),
                    ])
                    ->values()
                    ->all(),
            ] : null,
            'trainingGroups' => $trainingGroups,
            'trainingTypes' => $trainingTypes,
            'prefillDate' => now()->toDateString(),
        ]);
    }

    public function store(Request $request, TrainingService $trainingService): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'training_type_id' => ['required', 'integer', 'exists:training_types,id'],
            'team_focus' => ['required', 'string', 'max:32'],
            'unit_focus' => ['nullable', 'string', 'max:32'],
            'intensity' => ['required', 'in:low,medium,high'],
            'focus_position' => ['nullable', 'in:GK,DEF,MID,FWD'],
            'unit_groups' => ['nullable', 'array'],
            'unit_groups.*' => ['string', 'in:GK,DEF,MID,FWD'],
            'training_group_ids' => ['required', 'array', 'min:1'],
            'training_group_ids.*' => ['integer', 'exists:training_groups,id'],
            'session_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'player_ids' => ['nullable', 'array'],
            'player_ids.*' => ['integer', 'exists:players,id'],
            'player_plans' => ['nullable', 'array'],
            'player_plans.*.primary_focus' => ['nullable', 'in:pressing,defending,build_up,passing,technical,tactical,creativity,pace,finishing_focus,goalkeeping,recovery'],
            'player_plans.*.secondary_focus' => ['nullable', 'in:pressing,defending,build_up,passing,technical,tactical,creativity,pace,finishing_focus,goalkeeping,recovery'],
            'player_plans.*.intensity' => ['nullable', 'in:low,medium,high'],
        ]);

        $club = $request->user()->clubs()->with('players')->whereKey((int) $validated['club_id'])->first();
        abort_unless($club, 403);

        $groupIds = collect($validated['training_group_ids'])->map(static fn ($id) => (int) $id)->unique()->values();
        $groups = $club->trainingGroups()->with('players:id')->whereIn('id', $groupIds)->get();
        abort_if($groups->count() !== $groupIds->count(), 403);

        $sessionDate = Carbon::parse((string) $validated['session_date'])->toDateString();
        $existingCount = TrainingSession::query()
            ->where('club_id', $club->id)
            ->whereDate('session_date', $sessionDate)
            ->count();
        abort_if($existingCount >= 3, 422, 'Pro Tag koennen maximal drei Trainingseinheiten geplant werden.');

        $alreadyPlannedGroupIds = TrainingSession::query()
            ->where('club_id', $club->id)
            ->whereDate('session_date', $sessionDate)
            ->with('trainingGroups:id')
            ->get()
            ->flatMap(fn (TrainingSession $session) => $session->trainingGroups->pluck('id'))
            ->map(fn ($id) => (int) $id)
            ->unique();
        abort_if($groupIds->intersect($alreadyPlannedGroupIds)->isNotEmpty(), 422, 'Mindestens eine Trainingsgruppe ist an diesem Tag bereits verplant.');

        $playerIds = collect($validated['player_ids'] ?? [])->map(static fn($id) => (int) $id)->unique()->values();
        $groupPlayerIds = $groups
            ->flatMap(fn (TrainingGroup $group) => $group->players->pluck('id'))
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values();
        $playerIds = $playerIds->merge($groupPlayerIds)->unique()->values();
        $clubPlayerIds = $club->players->pluck('id');
        abort_if($playerIds->diff($clubPlayerIds)->isNotEmpty(), 403);

        $validated['player_ids'] = $playerIds->all();
        $validated['training_group_ids'] = $groupIds->all();
        $validated['unit_groups'] = $groups->pluck('name')->values()->all();
        abort_unless(TrainingType::query()->whereKey((int) $validated['training_type_id'])->where('is_active', true)->exists(), 422);
        $trainingService->createSession($club, $request->user(), $validated);

        return back()->with('status', 'Trainingseinheit wurde erstellt.');
    }

    public function apply(Request $request, TrainingSession $session, TrainingService $trainingService): RedirectResponse
    {
        abort_unless($request->user()->clubs()->whereKey($session->club_id)->exists(), 403);
        abort_if(!$session->session_date?->isSameDay(now()), 403, 'Training kann nur am geplanten Tag manuell ausgeloest werden.');

        $trainingService->applySession($session);

        return back()->with('status', 'Trainingseffekte wurden angewendet.');
    }

    public function applyToday(Request $request, TrainingService $trainingService): RedirectResponse
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;

        if (!$activeClub) {
            $activeClub = $request->user()->isAdmin()
                ? Club::query()->where('is_cpu', false)->orderBy('name')->first()
                : $request->user()->clubs()->where('is_cpu', false)->orderBy('name')->first();
        }

        abort_unless($activeClub, 403);
        abort_unless($request->user()->isAdmin() || $request->user()->clubs()->whereKey($activeClub->id)->exists(), 403);

        $sessions = TrainingSession::query()
            ->where('club_id', $activeClub->id)
            ->whereDate('session_date', now()->toDateString())
            ->where('is_applied', false)
            ->orderBy('id')
            ->get();

        abort_if($sessions->isEmpty(), 422, 'Heute sind keine offenen Trainingseinheiten vorhanden.');

        foreach ($sessions as $session) {
            $trainingService->applySession($session);
        }

        return back()->with('status', $sessions->count() . ' heutige Trainingseinheit(en) wurden angewendet.');
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'name' => ['required', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:24'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'player_ids' => ['nullable', 'array'],
            'player_ids.*' => ['integer', 'exists:players,id'],
        ]);

        $club = $request->user()->clubs()->with('players')->whereKey((int) $validated['club_id'])->first();
        abort_unless($club, 403);

        $playerIds = collect($validated['player_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        abort_if($playerIds->diff($club->players->pluck('id'))->isNotEmpty(), 403);

        $group = $club->trainingGroups()->create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?: 'cyan',
            'notes' => $validated['notes'] ?? null,
        ]);
        $group->players()->sync($playerIds->all());

        return back()->with('status', 'Trainingsgruppe wurde erstellt.');
    }

    public function updateGroup(Request $request, TrainingGroup $group): RedirectResponse
    {
        abort_unless($request->user()->clubs()->whereKey($group->club_id)->exists(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:24'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'player_ids' => ['nullable', 'array'],
            'player_ids.*' => ['integer', 'exists:players,id'],
        ]);

        $playerIds = collect($validated['player_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $clubPlayerIds = $group->club->players()->pluck('players.id');
        abort_if($playerIds->diff($clubPlayerIds)->isNotEmpty(), 403);

        $group->update([
            'name' => $validated['name'],
            'color' => $validated['color'] ?: $group->color,
            'notes' => $validated['notes'] ?? null,
        ]);
        $group->players()->sync($playerIds->all());

        return back()->with('status', 'Trainingsgruppe wurde aktualisiert.');
    }

    public function destroyGroup(Request $request, TrainingGroup $group): RedirectResponse
    {
        abort_unless($request->user()->clubs()->whereKey($group->club_id)->exists(), 403);

        $group->delete();

        return back()->with('status', 'Trainingsgruppe wurde geloescht.');
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

    private function mapSessionPayload(TrainingSession $session): array
    {
        $impact = $this->summarizeSessionImpact($session);

        return [
            'id' => $session->id,
            'session_date' => $session->session_date?->toDateString(),
            'type' => $session->type,
            'training_type' => $session->trainingType ? [
                'id' => $session->trainingType->id,
                'name' => $session->trainingType->name,
                'tone' => $session->trainingType->tone,
                'icon' => $session->trainingType->icon,
            ] : [
                'id' => $session->training_type_id,
                'name' => $session->training_type_name ?: $session->team_focus,
                'tone' => 'cyan',
                'icon' => 'GraduationCap',
            ],
            'team_focus' => $session->team_focus,
            'unit_focus' => $session->unit_focus,
            'intensity' => $session->intensity,
            'unit_groups' => $session->unit_groups ?? [],
            'training_groups' => $session->trainingGroups->map(fn ($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'color' => $group->color,
            ])->values()->all(),
            'applied_at' => $session->applied_at,
            'player_count' => $session->players->count(),
            'can_apply_manually' => !$session->is_applied && $session->session_date?->isSameDay(now()),
            'impact' => $impact,
        ];
    }

    private function summarizeSessionImpact(TrainingSession $session): array
    {
        $players = $session->players;
        $count = max(1, $players->count());

        $playerImpacts = $players
            ->map(function ($player): array {
                $total = (int) $player->pivot->stamina_delta + (int) $player->pivot->morale_delta + (int) $player->pivot->overall_delta;

                return [
                    'id' => $player->id,
                    'name' => $player->full_name,
                    'stamina_delta' => (int) $player->pivot->stamina_delta,
                    'morale_delta' => (int) $player->pivot->morale_delta,
                    'overall_delta' => (int) $player->pivot->overall_delta,
                    'total_delta' => $total,
                ];
            })
            ->sortByDesc('total_delta')
            ->values();

        return [
            'stamina_total' => (int) $players->sum(fn ($player) => (int) $player->pivot->stamina_delta),
            'morale_total' => (int) $players->sum(fn ($player) => (int) $player->pivot->morale_delta),
            'overall_total' => (int) $players->sum(fn ($player) => (int) $player->pivot->overall_delta),
            'stamina_avg' => round($players->sum(fn ($player) => (int) $player->pivot->stamina_delta) / $count, 1),
            'morale_avg' => round($players->sum(fn ($player) => (int) $player->pivot->morale_delta) / $count, 1),
            'overall_avg' => round($players->sum(fn ($player) => (int) $player->pivot->overall_delta) / $count, 1),
            'top_players' => $playerImpacts->take(4)->all(),
        ];
    }
}
