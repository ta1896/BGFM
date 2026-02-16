<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Services\FormationPlannerService;
use App\Services\TeamStrengthCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LineupsController extends Controller
{
    // ────────────────────────────────────────────────────────
    //  INDEX
    // ────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        /** @var Club $club */
        $club = app()->has('activeClub') ? app('activeClub') : $this->resolveClub($request);

        // Fallback
        if (!$club) {
            $club = $this->resolveClub($request);
        }

        // 1. Fetch upcoming matches for the club
        $matches = GameMatch::query()
            ->where(function ($query) use ($club): void {
                $query->where('home_club_id', $club->id)
                    ->orWhere('away_club_id', $club->id);
            })
            ->whereIn('status', ['scheduled', 'live'])
            ->where('kickoff_at', '>=', now()->subHours(4))
            ->with([
                'homeClub',
                'awayClub',
                'lineups' => function ($q) use ($club) {
                    $q->where('club_id', $club->id);
                }
            ])
            ->orderBy('kickoff_at')
            ->get();

        // 2. Fetch templates
        $templates = $club->lineups()
            ->whereNull('match_id')
            ->where('is_template', true)
            ->with('players')
            ->orderBy('name')
            ->get();

        // 3. User clubs for the switcher
        $userClubs = $request->user()->isAdmin()
            ? Club::where('is_cpu', false)->orderBy('name')->get()
            : $request->user()->clubs()->where('is_cpu', false)->orderBy('name')->get();

        return view('lineups.index', [
            'club' => $club,
            'userClubs' => $userClubs,
            'matches' => $matches,
            'templates' => $templates,
        ]);
    }

    // ────────────────────────────────────────────────────────
    //  MATCH REDIRECT
    // ────────────────────────────────────────────────────────

    public function match(Request $request, GameMatch $match): RedirectResponse
    {
        /** @var Club $club */
        $club = app()->has('activeClub') ? app('activeClub') : $this->resolveClub($request);

        // Fallback
        if (!$club) {
            $club = $this->resolveClub($request);
        }

        // Verify match belongs to club
        if ($match->home_club_id !== $club->id && $match->away_club_id !== $club->id) {
            abort(403, 'Dieses Match gehoert nicht zu deinem Verein.');
        }

        // Find existing lineup
        $lineup = $club->lineups()->where('match_id', $match->id)->first();

        // Create if missing
        if (!$lineup) {
            $lineupName = 'Spieltag ' . $match->matchday . ' vs ' .
                ($match->home_club_id === $club->id ? $match->awayClub->name : $match->homeClub->name);

            $lineup = $club->lineups()->create([
                'match_id' => $match->id,
                'name' => substr($lineupName, 0, 120),
                'formation' => '4-4-2',
                'is_active' => true,
                'notes' => 'Automatisch erstellt fuer Match #' . $match->id,
            ]);
        }

        return redirect()->route('lineups.edit', $lineup);
    }


    // ────────────────────────────────────────────────────────
    //  CREATE
    // ────────────────────────────────────────────────────────

    public function create(Request $request): View
    {
        $club = $this->resolveClub($request);

        return view('lineups.create', [
            'clubs' => collect([$club]),
        ]);
    }

    // ────────────────────────────────────────────────────────
    //  STORE
    // ────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $club = $this->resolveClub($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('lineups', 'name')
                    ->where(fn($query) => $query->where('club_id', $club->id)),
            ],
            'formation' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

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
            ->with('status', 'Aufstellung erstellt.');
    }

    // ────────────────────────────────────────────────────────
    //  SHOW
    // ────────────────────────────────────────────────────────

    public function show(
        Request $request,
        Lineup $lineup,
        TeamStrengthCalculator $calculator
    ): \Illuminate\Http\RedirectResponse {
        return redirect()->route('lineups.edit', $lineup);
    }

    // ────────────────────────────────────────────────────────
    //  EDIT
    // ────────────────────────────────────────────────────────

    public function edit(
        Request $request,
        Lineup $lineup,
        FormationPlannerService $planner,
        TeamStrengthCalculator $calculator
    ): View {
        $this->authorizeLineup($request, $lineup);
        $lineup->load(['club.user', 'players', 'match.homeClub', 'match.awayClub']);

        $club = $lineup->club;
        $clubPlayers = $club->players()
            ->whereIn('status', ['active', 'transfer_listed'])
            ->orderByDesc('overall')
            ->orderByDesc('potential')
            ->get();

        $templates = $club->lineups()
            ->whereNull('match_id')
            ->where('is_template', true)
            ->with('players')
            ->orderBy('name')
            ->get();

        // Load template if requested via query param
        $templateId = max(0, (int) $request->query('template_id'));
        $sourceLineup = $lineup;
        if ($templateId > 0) {
            $template = $templates->firstWhere('id', $templateId);
            if ($template) {
                $sourceLineup = $template;
            }
        }

        $draft = $this->draftFromLineup($sourceLineup);

        $formation = (string) $request->query('formation', $draft['formation'] ?? '4-4-2');
        if (!in_array($formation, $planner->supportedFormations(), true)) {
            $formation = '4-4-2';
        }

        $slots = $planner->starterSlots($formation);
        $maxBenchPlayers = $this->maxBenchPlayers();
        $starters = $this->normalizeStarterDraft($slots, $draft['starter_slots'] ?? []);
        $bench = $this->normalizeBenchDraft($draft['bench_slots'] ?? [], $maxBenchPlayers);

        $metrics = [
            'overall' => 0,
            'attack' => 0,
            'midfield' => 0,
            'defense' => 0,
            'chemistry' => 0,
        ];
        if ($lineup->players->isNotEmpty()) {
            $metrics = $calculator->calculate($lineup);
        }

        $clubMatches = GameMatch::query()
            ->where(function ($query) use ($club): void {
                $query->where('home_club_id', $club->id)
                    ->orWhere('away_club_id', $club->id);
            })
            ->whereIn('status', ['scheduled', 'live'])
            ->with(['homeClub:id,name,short_name,logo_path', 'awayClub:id,name,short_name,logo_path'])
            ->orderBy('kickoff_at')
            ->get();

        return view('lineups.edit', [
            'lineup' => $lineup,
            'club' => $club,
            'clubPlayers' => $clubPlayers,
            'clubMatches' => $clubMatches,
            'templates' => $templates,
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
            'captainPlayerId' => $draft['captain_player_id'] ?? $lineup->players->firstWhere('pivot.is_captain', true)?->id,
            'setPieces' => [
                'penalty_taker_player_id' => $draft['penalty_taker_player_id'] ?? $lineup->penalty_taker_player_id,
                'free_kick_near_player_id' => $draft['free_kick_near_player_id'] ?? $lineup->free_kick_near_player_id,
                'free_kick_far_player_id' => $draft['free_kick_far_player_id'] ?? $lineup->free_kick_far_player_id,
                'corner_left_taker_player_id' => $draft['corner_left_taker_player_id'] ?? $lineup->corner_left_taker_player_id,
                'corner_right_taker_player_id' => $draft['corner_right_taker_player_id'] ?? $lineup->corner_right_taker_player_id,
            ],
            'metrics' => $metrics,
            'positionFit' => [
                'main' => (float) config('simulation.position_fit.main', 1.0),
                'second' => (float) config('simulation.position_fit.second', 0.92),
                'third' => (float) config('simulation.position_fit.third', 0.84),
                'foreign' => (float) config('simulation.position_fit.foreign', 0.76),
                'foreign_gk' => (float) config('simulation.position_fit.foreign_gk', 0.55),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────
    //  UPDATE
    // ────────────────────────────────────────────────────────

    public function update(
        Request $request,
        Lineup $lineup,
        FormationPlannerService $planner,
        TeamStrengthCalculator $calculator
    ): RedirectResponse {
        $this->authorizeLineup($request, $lineup);
        $club = $lineup->club;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('lineups', 'name')
                    ->where(fn($query) => $query->where('club_id', $club->id))
                    ->ignore($lineup->id),
            ],
            'formation' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'mentality' => ['nullable', 'string', 'in:defensive,counter,normal,offensive,all_out'],
            'aggression' => ['nullable', 'string', 'in:cautious,normal,aggressive'],
            'line_height' => ['nullable', 'string', 'in:deep,normal,high,very_high'],
            'attack_focus' => ['nullable', 'string', 'in:center,left,right,both_wings'],
            'offside_trap' => ['nullable'],
            'time_wasting' => ['nullable'],
            'captain_player_id' => ['nullable', 'integer'],
            'penalty_taker_player_id' => ['nullable', 'integer'],
            'free_kick_near_player_id' => ['nullable', 'integer'],
            'free_kick_far_player_id' => ['nullable', 'integer'],
            'corner_left_taker_player_id' => ['nullable', 'integer'],
            'corner_right_taker_player_id' => ['nullable', 'integer'],
            'starter_slots' => ['array'],
            'bench_slots' => ['array'],
            'action' => ['nullable', 'in:save,auto_pick,save_as_template'],
            'template_name' => ['nullable', 'string', 'max:120'],
            'save_as_template' => ['nullable'],
        ]);

        $action = $validated['action'] ?? 'save';

        // ── Auto-Fill ───────────────────────────────────────
        if ($action === 'auto_pick') {
            $formation = in_array($validated['formation'], $planner->supportedFormations(), true)
                ? $validated['formation']
                : '4-4-2';

            $selection = $planner->strongestByFormation(
                $club->players()->whereIn('status', ['active', 'transfer_listed'])->get(),
                $formation,
                $this->maxBenchPlayers()
            );

            $lineup->update([
                'name' => $validated['name'],
                'formation' => $formation,
            ]);

            $this->syncPlayersFromSelection($lineup, $planner->starterSlots($formation), $selection);

            return redirect()
                ->route('lineups.edit', $lineup)
                ->with('status', 'Staerkste Elf wurde eingesetzt.');
        }

        // ── Save as template ────────────────────────────────
        if (($validated['save_as_template'] ?? null) !== null) {
            $templateName = trim((string) ($validated['template_name'] ?? ''));
            if ($templateName === '') {
                return back()
                    ->withInput()
                    ->withErrors(['template_name' => 'Bitte einen Vorlagennamen eingeben.']);
            }

            $template = $club->lineups()
                ->whereNull('match_id')
                ->where('name', $templateName)
                ->first();

            $templateData = [
                'match_id' => null,
                'formation' => $validated['formation'],
                'mentality' => $validated['mentality'] ?? 'normal',
                'aggression' => $validated['aggression'] ?? 'normal',
                'line_height' => $validated['line_height'] ?? 'normal',
                'attack_focus' => $validated['attack_focus'] ?? 'center',
                'offside_trap' => isset($validated['offside_trap']),
                'time_wasting' => isset($validated['time_wasting']),
                'is_template' => true,
                'is_active' => false,
                'notes' => 'Aus Aufstellung gespeichert',
                'penalty_taker_player_id' => $validated['penalty_taker_player_id'] ?? null,
                'free_kick_near_player_id' => $validated['free_kick_near_player_id'] ?? null,
                'free_kick_far_player_id' => $validated['free_kick_far_player_id'] ?? null,
                'corner_left_taker_player_id' => $validated['corner_left_taker_player_id'] ?? null,
                'corner_right_taker_player_id' => $validated['corner_right_taker_player_id'] ?? null,
            ];

            if ($template) {
                $template->update($templateData);
            } else {
                $template = $club->lineups()->create(array_merge(['name' => $templateName], $templateData));
            }

            // Sync the same players to template
            $this->syncPlayersFromRequest($template, $planner, $validated, $request);

            return redirect()
                ->route('lineups.edit', $lineup)
                ->with('status', 'Vorlage "' . $templateName . '" gespeichert.');
        }

        // ── Normal save ─────────────────────────────────────
        $isActive = (bool) ($validated['is_active'] ?? false);
        if ($isActive) {
            $club->lineups()->where('id', '!=', $lineup->id)->update(['is_active' => false]);
        }

        $lineup->update([
            'name' => $validated['name'],
            'formation' => $validated['formation'],
            'notes' => $validated['notes'] ?? null,
            'is_active' => $isActive || $lineup->is_active,
            'mentality' => $validated['mentality'] ?? 'normal',
            'aggression' => $validated['aggression'] ?? 'normal',
            'line_height' => $validated['line_height'] ?? 'normal',
            'attack_focus' => $validated['attack_focus'] ?? 'center',
            'offside_trap' => isset($validated['offside_trap']),
            'time_wasting' => isset($validated['time_wasting']),
            'penalty_taker_player_id' => $validated['penalty_taker_player_id'] ?? null,
            'free_kick_near_player_id' => $validated['free_kick_near_player_id'] ?? null,
            'free_kick_far_player_id' => $validated['free_kick_far_player_id'] ?? null,
            'corner_left_taker_player_id' => $validated['corner_left_taker_player_id'] ?? null,
            'corner_right_taker_player_id' => $validated['corner_right_taker_player_id'] ?? null,
        ]);

        $this->syncPlayersFromRequest($lineup, $planner, $validated, $request);

        return redirect()
            ->route('lineups.edit', $lineup)
            ->with('status', 'Aufstellung aktualisiert.');
    }

    // ────────────────────────────────────────────────────────
    //  DESTROY
    // ────────────────────────────────────────────────────────

    public function destroy(Request $request, Lineup $lineup): RedirectResponse
    {
        $this->authorizeLineup($request, $lineup);

        $club = $lineup->club;
        $wasActive = $lineup->is_active;

        $lineup->delete();

        if ($wasActive) {
            $club->lineups()->whereNull('match_id')->latest()->first()?->update(['is_active' => true]);
        }

        return redirect()
            ->route('lineups.index')
            ->with('status', 'Aufstellung geloescht.');
    }

    // ────────────────────────────────────────────────────────
    //  ACTIVATE
    // ────────────────────────────────────────────────────────

    public function activate(Request $request, Lineup $lineup): RedirectResponse
    {
        $this->authorizeLineup($request, $lineup);

        $lineup->club->lineups()->update(['is_active' => false]);
        $lineup->update(['is_active' => true]);

        return redirect()
            ->route('lineups.index')
            ->with('status', 'Aufstellung ist jetzt aktiv.');
    }

    // ════════════════════════════════════════════════════════
    //  Helpers
    // ════════════════════════════════════════════════════════

    private function resolveClub(Request $request): Club
    {
        $user = $request->user();
        $startClubId = (int) $request->query('club');

        if ($user->isAdmin()) {
            if ($startClubId > 0) {
                $club = Club::find($startClubId);
                if ($club)
                    return $club;
            }
            $club = Club::first();
            abort_if(!$club, 404, 'Kein Verein vorhanden.');
            return $club;
        }

        if ($startClubId > 0) {
            $club = $user->clubs()->whereKey($startClubId)->first();
            if ($club)
                return $club;
        }

        $club = $user->clubs()->first();
        abort_if(!$club, 403, 'Du verwaltest keinen Verein.');

        return $club;
    }

    private function authorizeLineup(Request $request, Lineup $lineup): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        abort_if($lineup->club->user_id !== $user->id, 403);
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
                'attack_focus' => 'center',
                'offside_trap' => false,
                'time_wasting' => false,
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
            'mentality' => $lineup->mentality ?? 'normal',
            'aggression' => $lineup->aggression ?? 'normal',
            'line_height' => $lineup->line_height ?? 'normal',
            'attack_focus' => $lineup->attack_focus ?? 'center',
            'offside_trap' => $lineup->offside_trap ?? false,
            'time_wasting' => $lineup->time_wasting ?? false,
            'starter_slots' => $starterSlots,
            'bench_slots' => array_values($benchSlots),
            'captain_player_id' => $lineup->players->firstWhere('pivot.is_captain', true)?->id,
            'penalty_taker_player_id' => $lineup->penalty_taker_player_id,
            'free_kick_near_player_id' => $lineup->free_kick_near_player_id,
            'free_kick_far_player_id' => $lineup->free_kick_far_player_id,
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
            ->map(fn($value) => is_numeric($value) ? (int) $value : null)
            ->take($maxBenchPlayers)
            ->values()
            ->all();
    }

    private function maxBenchPlayers(): int
    {
        return max(1, min(10, (int) config('simulation.lineup.max_bench_players', 5)));
    }

    private function syncPlayersFromRequest(Lineup $lineup, FormationPlannerService $planner, array $validated, Request $request): void
    {
        $formation = in_array($validated['formation'], $planner->supportedFormations(), true)
            ? $validated['formation']
            : '4-4-2';

        $slots = $planner->starterSlots($formation);
        $maxBenchPlayers = $this->maxBenchPlayers();

        $starterInput = collect($request->input('starter_slots', []))
            ->map(fn($value) => is_numeric($value) ? (int) $value : null)
            ->all();

        $benchInput = collect($request->input('bench_slots', []))
            ->map(fn($value) => is_numeric($value) ? (int) $value : null)
            ->filter()
            ->values()
            ->take($maxBenchPlayers)
            ->all();

        $captainId = (int) ($validated['captain_player_id'] ?? 0);

        $pivot = [];
        foreach ($slots as $index => $slot) {
            $playerId = (int) ($starterInput[$slot['slot']] ?? 0);
            if ($playerId <= 0) {
                continue;
            }

            $pivot[$playerId] = [
                'pitch_position' => $slot['slot'],
                'sort_order' => $index + 1,
                'x_coord' => $slot['x'],
                'y_coord' => $slot['y'],
                'is_captain' => $captainId === $playerId,
                'is_set_piece_taker' => false,
                'is_bench' => false,
                'bench_order' => null,
            ];
        }

        foreach ($benchInput as $index => $playerId) {
            if (isset($pivot[$playerId])) {
                continue;
            }

            $order = $index + 1;
            $pivot[$playerId] = [
                'pitch_position' => 'BANK-' . $order,
                'sort_order' => 100 + $order,
                'x_coord' => null,
                'y_coord' => null,
                'is_captain' => false,
                'is_set_piece_taker' => false,
                'is_bench' => true,
                'bench_order' => $order,
            ];
        }

        $lineup->players()->sync($pivot);
    }

    private function syncPlayersFromSelection(Lineup $lineup, array $slots, array $selection): void
    {
        $pivot = [];

        $starters = $selection['starters'] ?? [];
        $bench = $selection['bench'] ?? [];
        $firstStarterId = null;

        foreach ($slots as $index => $slot) {
            $playerId = (int) ($starters[$slot['slot']] ?? 0);
            if ($playerId <= 0) {
                continue;
            }

            if ($firstStarterId === null) {
                $firstStarterId = $playerId;
            }

            $pivot[$playerId] = [
                'pitch_position' => $slot['slot'],
                'sort_order' => $index + 1,
                'x_coord' => $slot['x'],
                'y_coord' => $slot['y'],
                'is_captain' => $firstStarterId === $playerId,
                'is_set_piece_taker' => false,
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
                'pitch_position' => 'BANK-' . $order,
                'sort_order' => 100 + $order,
                'x_coord' => null,
                'y_coord' => null,
                'is_captain' => false,
                'is_set_piece_taker' => false,
                'is_bench' => true,
                'bench_order' => $order,
            ];
        }

        $lineup->players()->sync($pivot);
    }
}
