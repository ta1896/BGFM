<?php
$content = <<<'EOT'
<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Services\FormationPlannerService;
use App\Services\TeamStrengthCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamLineupsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $lineups = Lineup::query()
            ->whereHas('club', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['club', 'players'])
            ->orderByDesc('is_active')
            ->latest()
            ->get();

        if ($lineups->isNotEmpty() && !$request->boolean('manage')) {
            $preferred = $lineups->firstWhere('is_active', true) ?? $lineups->first();

            return redirect()->route('lineups.edit', $preferred);
        }

        return view('lineups.index', [
            'lineups' => $lineups,
            'clubs' => $request->user()->clubs()->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return view('lineups.create', [
            'clubs' => $request->user()->clubs()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('lineups', 'name')->where(fn ($query) => $query->where('club_id', $request->input('club_id'))),
            ],
            'formation' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $club = $this->ownedClub($request, (int) $validated['club_id']);
        $isActive = (bool) ($validated['is_active'] ?? false);

        if ($isActive) {
            $club->lineups()->update(['is_active' => false]);
        }

        $lineup = $club->lineups()->create([
            'name' => $validated['name'],
            'formation' => $validated['formation'],
            'notes' => $validated['notes'] ?? null,
            'is_active' => $isActive || !$club->lineups()->where('is_active', true)->exists(),
        ]);

        return redirect()
            ->route('lineups.edit', $lineup)
            ->with('status', 'Aufstellung angelegt. Jetzt Spieler zuweisen.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Lineup $lineup, TeamStrengthCalculator $calculator): View
    {
        $this->ensureOwnership($request, $lineup);

        $lineup->load(['club', 'players']);
        $metrics = $calculator->calculate($lineup);

        return view('lineups.show', [
            'lineup' => $lineup,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(
        Request $request,
        Lineup $lineup,
        FormationPlannerService $planner,
        TeamStrengthCalculator $calculator
    ): View
    {
        $this->ensureOwnership($request, $lineup);

        $lineup->load(['club', 'players']);
        $clubPlayers = $lineup->club->players()
            ->orderByDesc('overall')
            ->orderByDesc('potential')
            ->get();

        $formation = (string) $request->query('formation', $lineup->formation ?: '4-4-2');
        if (!in_array($formation, $planner->supportedFormations(), true)) {
            $formation = '4-4-2';
        }

        $slots = $planner->starterSlots($formation);
        $draft = $this->draftFromLineup($lineup);
        $maxBenchPlayers = $this->maxBenchPlayers();

        // Priority: auto_pick > query params (drag/drop/remove) > DB draft
        if ($request->query('action') === 'auto_pick') {
            $auto = $planner->strongestByFormation($clubPlayers, $formation, $maxBenchPlayers);
            $starters = $this->normalizeStarterDraft($slots, $auto['starters']);
            $bench = $this->normalizeBenchDraft(
                collect($auto['bench'])->mapWithKeys(fn ($id, $i) => [$i => $id])->all(),
                $maxBenchPlayers
            );
        } elseif ($request->query('starter_slots')) {
            $queryStarters = [];
            foreach ($request->query('starter_slots', []) as $slot => $id) {
                $queryStarters[$slot] = $id ? (int) $id : null;
            }
            $queryBench = [];
            foreach ($request->query('bench_slots', []) as $slot => $id) {
                $queryBench[$slot] = $id ? (int) $id : null;
            }
            $starters = $this->normalizeStarterDraft($slots, $queryStarters);
            $bench = $this->normalizeBenchDraft($queryBench, $maxBenchPlayers);
        } else {
            $starters = $this->normalizeStarterDraft($slots, $draft['starter_slots'] ?? []);
            $bench = $this->normalizeBenchDraft($draft['bench_slots'] ?? [], $maxBenchPlayers);
        }
        
        $metrics = $calculator->calculate($lineup);
        $clubMatches = GameMatch::query()
            ->where(function ($query) use ($lineup): void {
                $query->where('home_club_id', $lineup->club_id)
                    ->orWhere('away_club_id', $lineup->club_id);
            })
            ->whereIn('status', ['scheduled', 'live'])
            ->with(['homeClub:id,name,short_name', 'awayClub:id,name,short_name'])
            ->orderBy('kickoff_at')
            ->get();

        $templates = Lineup::where('club_id', $lineup->club_id)
            ->where('id', '!=', $lineup->id)
            ->orderBy('name')
            ->get(['id', 'name', 'formation']);

        // Load template if requested (does NOT save)
        $templateId = $request->query('template_id');
        if ($templateId) {
            $template = Lineup::with('players')->where('club_id', $lineup->club_id)->find($templateId);
            if ($template) {
                $templateDraft = $this->draftFromLineup($template);
                $formation = in_array($templateDraft['formation'], $planner->supportedFormations(), true)
                    ? $templateDraft['formation'] : $formation;
                $slots = $planner->starterSlots($formation);
                $starters = $this->normalizeStarterDraft($slots, $templateDraft['starter_slots'] ?? []);
                $bench = $this->normalizeBenchDraft($templateDraft['bench_slots'] ?? [], $maxBenchPlayers);
            }
        }

        return view('lineups.edit', [
            'lineup' => $lineup,
            'clubMatches' => $clubMatches,
            'clubPlayers' => $clubPlayers,
            'formation' => $formation,
            'formations' => $planner->supportedFormations(),
            'slots' => $slots,
            'starterDraft' => $starters,
            'benchDraft' => $bench,
            'maxBenchPlayers' => $maxBenchPlayers,
            'mentality' => $draft['mentality'] ?? ($lineup->mentality ?? 'normal'),
            'aggression' => $draft['aggression'] ?? ($lineup->aggression ?? 'normal'),
            'line_height' => $draft['line_height'] ?? ($lineup->line_height ?? 'normal'),
            'attackFocus' => $draft['attack_focus'] ?? ($lineup->attack_focus ?? 'center'),
            'offside_trap' => $draft['offside_trap'] ?? ($lineup->offside_trap ?? false),
            'time_wasting' => $draft['time_wasting'] ?? ($lineup->time_wasting ?? false),
            'captainPlayerId' => $draft['captain_player_id']
                ?? $lineup->players->firstWhere('pivot.is_captain', true)?->id,
            'setPieces' => [
                'penalty_taker_player_id' => $draft['penalty_taker_player_id'] ?? $lineup->penalty_taker_player_id,
                'free_kick_taker_player_id' => $draft['free_kick_taker_player_id'] ?? $lineup->free_kick_taker_player_id,
                'corner_left_taker_player_id' => $draft['corner_left_taker_player_id'] ?? $lineup->corner_left_taker_player_id,
                'corner_right_taker_player_id' => $draft['corner_right_taker_player_id'] ?? $lineup->corner_right_taker_player_id,
            ],
            'metrics' => $metrics,
            'templates' => $templates,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lineup $lineup, FormationPlannerService $planner): RedirectResponse
    {
        $this->ensureOwnership($request, $lineup);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('lineups', 'name')
                    ->where(fn ($query) => $query->where('club_id', $lineup->club_id))
                    ->ignore($lineup->id),
            ],
            'formation' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'mentality' => ['nullable', 'in:defensive,counter,normal,offensive,all_out'],
            'aggression' => ['nullable', 'in:cautious,normal,aggressive'],
            'line_height' => ['nullable', 'in:deep,normal,high,very_high'],
            'attack_focus' => ['nullable', 'in:left,center,right,both_wings'],
            'offside_trap' => ['sometimes', 'boolean'],
            'time_wasting' => ['sometimes', 'boolean'],
            'captain_player_id' => ['nullable', 'integer'],
            'penalty_taker_player_id' => ['nullable', 'integer'],
            'free_kick_taker_player_id' => ['nullable', 'integer'],
            'corner_left_taker_player_id' => ['nullable', 'integer'],
            'corner_right_taker_player_id' => ['nullable', 'integer'],
            'starter_slots' => ['array'],
            'bench_slots' => ['array'],
            'action' => ['nullable', 'in:save,auto_pick'],
            'selected_players' => ['array'],
            'selected_players.*' => ['integer', 'exists:players,id'],
            'pitch_positions' => ['array'],
            'pitch_positions.*' => ['nullable', 'string', 'max:20'],
        ]);

        $formation = in_array($validated['formation'], $planner->supportedFormations(), true)
            ? $validated['formation']
            : '4-4-2';
        $slots = $planner->starterSlots($formation);
        $maxBenchPlayers = $this->maxBenchPlayers();

        $selection = $this->resolveSelection(
            $lineup->club,
            $slots,
            $request,
            $validated['action'] ?? null,
            $planner,
            $formation,
            $maxBenchPlayers
        );
        if ($selection['error']) {
            return back()
                ->withInput()
                ->withErrors(['starter_slots' => $selection['error']]);
        }

        $captainId = $this->resolveCaptainId($selection['starterIds'], (int) ($validated['captain_player_id'] ?? 0));
        $setPieceIds = $this->resolveSetPieceIds($selection['allIds'], $validated);
        $pivotData = $this->buildPivotSync($slots, $selection['starters'], $selection['bench'], $captainId, $setPieceIds);

        $isActive = $request->boolean('is_active');

        if ($isActive) {
            $lineup->club->lineups()->update(['is_active' => false]);
        }

        $lineup->update([
            'name' => $validated['name'],
            'formation' => $formation,
            'mentality' => $validated['mentality'] ?? 'normal',
            'aggression' => $validated['aggression'] ?? 'normal',
            'line_height' => $validated['line_height'] ?? 'normal',
            'attack_focus' => $validated['attack_focus'] ?? 'center',
            'offside_trap' => $request->boolean('offside_trap'),
            'time_wasting' => $request->boolean('time_wasting'),
            'penalty_taker_player_id' => $setPieceIds['penalty_taker_player_id'],
            'free_kick_taker_player_id' => $setPieceIds['free_kick_taker_player_id'],
            'corner_left_taker_player_id' => $setPieceIds['corner_left_taker_player_id'],
            'corner_right_taker_player_id' => $setPieceIds['corner_right_taker_player_id'],
            'notes' => $validated['notes'] ?? null,
            'is_active' => $isActive || $lineup->is_active,
        ]);

        $lineup->players()->sync($pivotData);

        // Save as template: create a new lineup copy
        if ($request->input('save_as_template') && $request->input('template_name')) {
            $newTemplate = Lineup::create([
                'club_id' => $lineup->club_id,
                'name' => $request->input('template_name'),
                'formation' => $formation,
                'mentality' => $validated['mentality'] ?? 'normal',
                'aggression' => $validated['aggression'] ?? 'normal',
                'line_height' => $validated['line_height'] ?? 'normal',
                'offside_trap' => $request->boolean('offside_trap'),
                'time_wasting' => $request->boolean('time_wasting'),
                'attack_focus' => $validated['attack_focus'] ?? 'center',
                'penalty_taker_player_id' => $setPieceIds['penalty_taker_player_id'],
                'free_kick_taker_player_id' => $setPieceIds['free_kick_taker_player_id'],
                'corner_left_taker_player_id' => $setPieceIds['corner_left_taker_player_id'],
                'corner_right_taker_player_id' => $setPieceIds['corner_right_taker_player_id'],
                'is_active' => false,
            ]);
            $newTemplate->players()->sync($pivotData);

            return redirect()
                ->route('lineups.edit', $lineup)
                ->with('status', 'Vorlage "' . $request->input('template_name') . '" gespeichert.');
        }

        return redirect()
            ->route('lineups.show', $lineup)
            ->with('status', 'Aufstellung aktualisiert.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Lineup $lineup): RedirectResponse
    {
        $this->ensureOwnership($request, $lineup);

        $club = $lineup->club;
        $wasActive = $lineup->is_active;

        $lineup->delete();

        if ($wasActive) {
            $club->lineups()->latest()->first()?->update(['is_active' => true]);
        }

        return redirect()
            ->route('lineups.index')
            ->with('status', 'Aufstellung wurde entfernt.');
    }

    public function activate(Request $request, Lineup $lineup): RedirectResponse
    {
        $this->ensureOwnership($request, $lineup);

        $lineup->club->lineups()->update(['is_active' => false]);
        $lineup->update(['is_active' => true]);

        return redirect()
            ->route('lineups.index')
            ->with('status', 'Aufstellung ist jetzt aktiv.');
    }

    /**
     * @param array<int, array{slot:string,label:string,group:string,x:int,y:int}> $slots
     * @param array<string, mixed> $validated
     * @return array{
     *   starters: array<string, int|null>,
     *   bench: array<int, int>,
     *   starterIds: array<int, int>,
     *   allIds: array<int, int>,
     *   error: string|null
     * }
     */
    private function resolveSelection(
        Club $club,
        array $slots,
        Request $request,
        ?string $action,
        FormationPlannerService $planner,
        string $formation,
        int $maxBenchPlayers
    ): array {
        if ($action === 'auto_pick') {
            $autoPick = $planner->strongestByFormation(
                $club->players()->whereIn('status', ['active', 'transfer_listed'])->get(),
                $formation,
                $maxBenchPlayers
            );

            $starterInput = collect($autoPick['starters'])
                ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
                ->all();

            $benchInput = collect($autoPick['bench'])
                ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
                ->filter()
                ->values()
                ->all();
        } else {
            $starterInput = collect($request->input('starter_slots', []))
                ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
                ->all();

            $benchInput = collect($request->input('bench_slots', []))
                ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
                ->filter()
                ->values()
                ->all();
        }

        $starters = [];
        foreach ($slots as $index => $slot) {
            $value = $starterInput[$slot['slot']] ?? null;
            if ($value === null && empty($starterInput)) {
                $legacyId = $request->input("selected_players.$index");
                $value = is_numeric($legacyId) ? (int) $legacyId : null;
            }
            $starters[$slot['slot']] = $value;
        }

        $starterIds = collect($starters)->filter()->values();
        $allIds = $starterIds->concat($benchInput)->filter()->values();

        if ($starterIds->count() > 11) {
            return $this->selectionError('Es sind maximal 11 Startplaetze erlaubt.');
        }
        if ($benchInput && count($benchInput) > $maxBenchPlayers) {
            return $this->selectionError('Es sind maximal '.$maxBenchPlayers.' Bankplaetze erlaubt.');
        }
        if ($allIds->count() !== $allIds->unique()->count()) {
            return $this->selectionError('Ein Spieler darf nur einmal aufgestellt sein.');
        }

        $clubPlayerIds = $club->players()->pluck('id');
        if ($allIds->diff($clubPlayerIds)->isNotEmpty()) {
            return $this->selectionError('Es wurden ungueltige Spieler ausgewaehlt.');
        }

        return [
            'starters' => $starters,
            'bench' => array_values(array_slice(array_unique($benchInput), 0, $maxBenchPlayers)),
            'starterIds' => $starterIds->all(),
            'allIds' => $allIds->all(),
            'error' => null,
        ];
    }

    /**
     * @return array{
     *   starters: array<string, int|null>,
     *   bench: array<int, int>,
     *   starterIds: array<int, int>,
     *   allIds: array<int, int>,
     *   error: string|null
     * }
     */
    private function selectionError(string $message): array
    {
        return [
            'starters' => [],
            'bench' => [],
            'starterIds' => [],
            'allIds' => [],
            'error' => $message,
        ];
    }

    /**
     * @param array<int, int> $starterIds
     */
    private function resolveCaptainId(array $starterIds, int $captainId): ?int
    {
        if (in_array($captainId, $starterIds, true)) {
            return $captainId;
        }

        return $starterIds[0] ?? null;
    }

    /**
     * @param array<int, int> $allowedPlayerIds
     * @param array<string, mixed> $validated
     * @return array{
     *   penalty_taker_player_id:int|null,
     *   free_kick_taker_player_id:int|null,
     *   corner_left_taker_player_id:int|null,
     *   corner_right_taker_player_id:int|null
     * }
     */
    private function resolveSetPieceIds(array $allowedPlayerIds, array $validated): array
    {
        $normalize = function (string $key) use ($allowedPlayerIds, $validated): ?int {
            $value = (int) ($validated[$key] ?? 0);

            return in_array($value, $allowedPlayerIds, true) ? $value : null;
        };

        return [
            'penalty_taker_player_id' => $normalize('penalty_taker_player_id'),
            'free_kick_taker_player_id' => $normalize('free_kick_taker_player_id'),
            'corner_left_taker_player_id' => $normalize('corner_left_taker_player_id'),
            'corner_right_taker_player_id' => $normalize('corner_right_taker_player_id'),
        ];
    }

    /**
     * @param array<int, array{slot:string,label:string,group:string,x:int,y:int}> $slots
     * @param array<string, int|null> $starters
     * @param array<int, int> $bench
     * @param array<string, int|null> $setPieceIds
     * @return array<int, array<string, int|float|string|bool|null>>
     */
    private function buildPivotSync(
        array $slots,
        array $starters,
        array $bench,
        ?int $captainId,
        array $setPieceIds
    ): array {
        $setPiecePlayerIds = collect($setPieceIds)->filter()->values()->all();
        $pivot = [];

        foreach ($slots as $index => $slot) {
            $playerId = (int) ($starters[$slot['slot']] ?? 0);
            if ($playerId <= 0) {
                continue;
            }

            $pivot[$playerId] = [
                'pitch_position' => $slot['slot'],
                'sort_order' => $index + 1,
                'x_coord' => $slot['x'],
                'y_coord' => $slot['y'],
                'is_captain' => $captainId === $playerId,
                'is_set_piece_taker' => in_array($playerId, $setPiecePlayerIds, true),
                'is_bench' => false,
                'bench_order' => null,
            ];
        }

        foreach ($bench as $index => $playerId) {
            if (isset($pivot[$playerId])) {
                continue;
            }

            $order = $index + 1;
            $pivot[$playerId] = [
                'pitch_position' => 'BANK-'.$order,
                'sort_order' => 100 + $order,
                'x_coord' => null,
                'y_coord' => null,
                'is_captain' => false,
                'is_set_piece_taker' => in_array($playerId, $setPiecePlayerIds, true),
                'is_bench' => true,
                'bench_order' => $order,
            ];
        }

        return $pivot;
    }

    /**
     * @return array<string, mixed>
     */
    private function draftFromLineup(?Lineup $lineup): array
    {
        if (!$lineup) {
            return [
                'formation' => '4-4-2',
                'mentality' => 'normal',
                'aggression' => 'normal',
                'line_height' => 'normal',
                'offside_trap' => false,
                'time_wasting' => false,
                'attack_focus' => 'center',
                'starter_slots' => [],
                'bench_slots' => [],
            ];
        }

        $starterSlots = [];
        $benchSlots = [];
        foreach ($lineup->players as $player) {
            if ($player->pivot->is_bench) {
                $benchSlots[] = $player->id;
            } else {
                $slot = (string) ($player->pivot->pitch_position ?: '');
                if ($slot !== '') {
                    $starterSlots[$slot] = $player->id;
                }
            }
        }

        return [
            'formation' => $lineup->formation ?: '4-4-2',
            'mentality' => $lineup->mentality ?: 'normal',
            'aggression' => $lineup->aggression ?: 'normal',
            'line_height' => $lineup->line_height ?: 'normal',
            'offside_trap' => (bool) $lineup->offside_trap,
            'time_wasting' => (bool) $lineup->time_wasting,
            'attack_focus' => $lineup->attack_focus ?: 'center',
            'starter_slots' => $starterSlots,
            'bench_slots' => array_values($benchSlots),
            'captain_player_id' => $lineup->players->firstWhere('pivot.is_captain', true)?->id,
            'penalty_taker_player_id' => $lineup->penalty_taker_player_id,
            'free_kick_taker_player_id' => $lineup->free_kick_taker_player_id,
            'corner_left_taker_player_id' => $lineup->corner_left_taker_player_id,
            'corner_right_taker_player_id' => $lineup->corner_right_taker_player_id,
        ];
    }

    /**
     * @param array<int, array{slot:string,label:string,group:string,x:int,y:int}> $slots
     * @param mixed $draft
     * @return array<string, int|null>
     */
    private function normalizeStarterDraft(array $slots, mixed $draft): array
    {
        $normalized = [];
        $source = is_array($draft) ? $draft : [];
        foreach ($slots as $slot) {
            $value = $source[$slot['slot']] ?? null;
            $normalized[$slot['slot']] = is_numeric($value) ? (int) $value : null;
        }

        return $normalized;
    }

    /**
     * @param mixed $draft
     * @return array<int, int|null>
     */
    private function normalizeBenchDraft(mixed $draft, int $maxBenchPlayers): array
    {
        $source = is_array($draft) ? $draft : [];

        return collect($source)
            ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
            ->take($maxBenchPlayers)
            ->values()
            ->all();
    }

    private function maxBenchPlayers(): int
    {
        return max(1, min(10, (int) config('simulation.lineup.max_bench_players', 5)));
    }

    private function ensureOwnership(Request $request, Lineup $lineup): void
    {
        abort_unless($lineup->club()->where('user_id', $request->user()->id)->exists(), 403);
    }

    private function ownedClub(Request $request, int $clubId): Club
    {
        $club = $request->user()->clubs()->whereKey($clubId)->first();
        abort_unless($club, 403);

        return $club;
    }
}
EOT;

file_put_contents('app/Http/Controllers/TeamLineupsController.php', $content);
echo "Written: " . filesize('app/Http/Controllers/TeamLineupsController.php') . " bytes";
