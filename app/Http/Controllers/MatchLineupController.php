<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Services\FormationPlannerService;
use App\Services\TeamStrengthCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchLineupController extends Controller
{
    public function edit(
        Request $request,
        GameMatch $match,
        FormationPlannerService $planner,
        TeamStrengthCalculator $calculator
    ): View {
        $match = $this->resolveRouteMatch($request, $match);
        $club = $this->resolveClubForMatch($request, $match);
        abort_if($match->status === 'played', 422, 'Fuer gespielte Partien kann die Aufstellung nicht mehr geaendert werden.');

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

        $matchLineup = $club->lineups()
            ->where('match_id', $match->id)
            ->with('players')
            ->first();

        $sourceLineup = $matchLineup
            ?? $club->lineups()->whereNull('match_id')->where('is_active', true)->with('players')->first()
            ?? $templates->first();

        $draft = session()->get($this->draftKey($request, $match, $club));
        if (!is_array($draft)) {
            $draft = $this->draftFromLineup($sourceLineup);
        }

        $formation = $draft['formation'] ?? '4-4-2';
        if (!in_array($formation, $planner->supportedFormations(), true)) {
            $formation = '4-4-2';
        }

        $slots = $planner->starterSlots($formation);
        $starters = $this->normalizeStarterDraft($slots, $draft['starter_slots'] ?? []);
        $bench = $this->normalizeBenchDraft($draft['bench_slots'] ?? []);

        $metrics = [
            'overall' => 0,
            'attack' => 0,
            'midfield' => 0,
            'defense' => 0,
            'chemistry' => 0,
        ];
        if ($matchLineup && $matchLineup->players->isNotEmpty()) {
            $metrics = $calculator->calculate($matchLineup);
        }

        return view('leagues.lineup', [
            'match' => $match->loadMissing(['homeClub', 'awayClub']),
            'club' => $club,
            'opponentClub' => $match->home_club_id === $club->id ? $match->awayClub : $match->homeClub,
            'clubPlayers' => $clubPlayers,
            'templates' => $templates,
            'currentLineup' => $matchLineup,
            'formation' => $formation,
            'formations' => $planner->supportedFormations(),
            'slots' => $slots,
            'starterDraft' => $starters,
            'benchDraft' => $bench,
            'tacticalStyle' => $draft['tactical_style'] ?? ($sourceLineup?->tactical_style ?? 'balanced'),
            'attackFocus' => $draft['attack_focus'] ?? ($sourceLineup?->attack_focus ?? 'center'),
            'captainPlayerId' => $draft['captain_player_id'] ?? $sourceLineup?->players->firstWhere('pivot.is_captain', true)?->id,
            'setPieces' => [
                'penalty_taker_player_id' => $draft['penalty_taker_player_id'] ?? $sourceLineup?->penalty_taker_player_id,
                'free_kick_taker_player_id' => $draft['free_kick_taker_player_id'] ?? $sourceLineup?->free_kick_taker_player_id,
                'corner_left_taker_player_id' => $draft['corner_left_taker_player_id'] ?? $sourceLineup?->corner_left_taker_player_id,
                'corner_right_taker_player_id' => $draft['corner_right_taker_player_id'] ?? $sourceLineup?->corner_right_taker_player_id,
            ],
            'metrics' => $metrics,
        ]);
    }

    public function update(
        Request $request,
        GameMatch $match,
        FormationPlannerService $planner
    ): RedirectResponse {
        $match = $this->resolveRouteMatch($request, $match);
        $club = $this->resolveClubForMatch($request, $match);
        abort_if($match->status === 'played', 422, 'Fuer gespielte Partien kann die Aufstellung nicht mehr geaendert werden.');

        $validated = $request->validate([
            'formation' => ['required', 'string'],
            'tactical_style' => ['required', 'in:balanced,offensive,defensive,counter'],
            'attack_focus' => ['required', 'in:left,center,right'],
            'captain_player_id' => ['nullable', 'integer'],
            'penalty_taker_player_id' => ['nullable', 'integer'],
            'free_kick_taker_player_id' => ['nullable', 'integer'],
            'corner_left_taker_player_id' => ['nullable', 'integer'],
            'corner_right_taker_player_id' => ['nullable', 'integer'],
            'starter_slots' => ['array'],
            'bench_slots' => ['array'],
            'action' => ['nullable', 'in:save_match,save_template'],
            'template_name' => ['nullable', 'string', 'max:120'],
        ]);

        $formation = in_array($validated['formation'], $planner->supportedFormations(), true)
            ? $validated['formation']
            : '4-4-2';
        $slots = $planner->starterSlots($formation);

        $selection = $this->resolveSelection($club, $slots, $request);
        $captainId = $this->resolveCaptainId($selection['starterIds'], (int) ($validated['captain_player_id'] ?? 0));

        $setPieceIds = $this->resolveSetPieceIds($selection['allIds'], $validated);
        $pivot = $this->buildPivotSync($slots, $selection['starters'], $selection['bench'], $captainId, $setPieceIds);

        $lineup = Lineup::query()->updateOrCreate(
            [
                'club_id' => $club->id,
                'match_id' => $match->id,
            ],
            [
                'name' => 'Matchplan '.$club->short_name.' #'.$match->id,
                'formation' => $formation,
                'tactical_style' => $validated['tactical_style'],
                'attack_focus' => $validated['attack_focus'],
                'penalty_taker_player_id' => $setPieceIds['penalty_taker_player_id'],
                'free_kick_taker_player_id' => $setPieceIds['free_kick_taker_player_id'],
                'corner_left_taker_player_id' => $setPieceIds['corner_left_taker_player_id'],
                'corner_right_taker_player_id' => $setPieceIds['corner_right_taker_player_id'],
                'is_template' => false,
                'is_active' => false,
                'notes' => null,
            ]
        );
        $lineup->players()->sync($pivot);

        $action = $validated['action'] ?? 'save_match';
        if ($action === 'save_template') {
            $templateName = trim((string) ($validated['template_name'] ?? ''));
            if ($templateName === '') {
                return back()
                    ->withInput()
                    ->withErrors(['template_name' => 'Bitte einen Vorlagennamen eingeben.']);
            }

            $template = $club->lineups()->where('name', $templateName)->first();
            if (!$template) {
                $template = $club->lineups()->create([
                    'name' => $templateName,
                    'match_id' => null,
                    'formation' => $formation,
                    'tactical_style' => $validated['tactical_style'],
                    'attack_focus' => $validated['attack_focus'],
                    'penalty_taker_player_id' => $setPieceIds['penalty_taker_player_id'],
                    'free_kick_taker_player_id' => $setPieceIds['free_kick_taker_player_id'],
                    'corner_left_taker_player_id' => $setPieceIds['corner_left_taker_player_id'],
                    'corner_right_taker_player_id' => $setPieceIds['corner_right_taker_player_id'],
                    'is_template' => true,
                    'is_active' => false,
                    'notes' => 'Aus Match-Aufstellung gespeichert',
                ]);
            } else {
                $template->update([
                    'match_id' => null,
                    'formation' => $formation,
                    'tactical_style' => $validated['tactical_style'],
                    'attack_focus' => $validated['attack_focus'],
                    'penalty_taker_player_id' => $setPieceIds['penalty_taker_player_id'],
                    'free_kick_taker_player_id' => $setPieceIds['free_kick_taker_player_id'],
                    'corner_left_taker_player_id' => $setPieceIds['corner_left_taker_player_id'],
                    'corner_right_taker_player_id' => $setPieceIds['corner_right_taker_player_id'],
                    'is_template' => true,
                    'is_active' => false,
                    'notes' => 'Aus Match-Aufstellung gespeichert',
                ]);
            }
            $template->players()->sync($pivot);
        }

        session()->forget($this->draftKey($request, $match, $club));

        return redirect()
            ->route('matches.lineup.edit', ['match' => $match->id, 'club' => $club->id])
            ->with('status', $action === 'save_template'
                ? 'Aufstellung gespeichert und als Vorlage abgelegt.'
                : 'Match-Aufstellung gespeichert.');
    }

    public function loadTemplate(
        Request $request,
        GameMatch $match,
        FormationPlannerService $planner
    ): RedirectResponse {
        $match = $this->resolveRouteMatch($request, $match);
        $club = $this->resolveClubForMatch($request, $match);
        $templateId = (int) $request->validate([
            'template_id' => ['required', 'integer'],
        ])['template_id'];

        $template = $club->lineups()
            ->whereNull('match_id')
            ->where('is_template', true)
            ->with('players')
            ->findOrFail($templateId);

        $draft = $this->draftFromLineup($template);
        if (!in_array($draft['formation'], $planner->supportedFormations(), true)) {
            $draft['formation'] = '4-4-2';
        }

        session()->put($this->draftKey($request, $match, $club), $draft);

        return redirect()
            ->route('matches.lineup.edit', ['match' => $match->id, 'club' => $club->id])
            ->with('status', 'Vorlage wurde in den Matchplan geladen.');
    }

    public function autoPick(
        Request $request,
        GameMatch $match,
        FormationPlannerService $planner
    ): RedirectResponse {
        $match = $this->resolveRouteMatch($request, $match);
        $club = $this->resolveClubForMatch($request, $match);
        $validated = $request->validate([
            'formation' => ['required', 'string'],
            'tactical_style' => ['required', 'in:balanced,offensive,defensive,counter'],
            'attack_focus' => ['required', 'in:left,center,right'],
        ]);

        $formation = in_array($validated['formation'], $planner->supportedFormations(), true)
            ? $validated['formation']
            : '4-4-2';
        $selection = $planner->strongestByFormation(
            $club->players()->whereIn('status', ['active', 'transfer_listed'])->get(),
            $formation
        );

        $draft = [
            'formation' => $formation,
            'tactical_style' => $validated['tactical_style'],
            'attack_focus' => $validated['attack_focus'],
            'starter_slots' => $selection['starters'],
            'bench_slots' => $selection['bench'],
            'captain_player_id' => collect($selection['starters'])->filter()->first(),
        ];

        session()->put($this->draftKey($request, $match, $club), $draft);

        return redirect()
            ->route('matches.lineup.edit', ['match' => $match->id, 'club' => $club->id])
            ->with('status', 'Staerkste Elf wurde vorgeschlagen.');
    }

    public function destroyTemplate(Request $request, GameMatch $match, Lineup $template): RedirectResponse
    {
        $match = $this->resolveRouteMatch($request, $match);
        $club = $this->resolveClubForMatch($request, $match);
        abort_unless(
            $template->club_id === $club->id && $template->is_template && !$template->match_id,
            404
        );

        $template->delete();

        return redirect()
            ->route('matches.lineup.edit', ['match' => $match->id, 'club' => $club->id])
            ->with('status', 'Vorlage wurde geloescht.');
    }

    private function resolveClubForMatch(Request $request, GameMatch $match): Club
    {
        $match->loadMissing(['homeClub', 'awayClub']);
        $clubId = (int) ($request->query('club') ?? $request->input('club_id') ?? 0);

        if ($request->user()->isAdmin()) {
            $candidate = collect([$match->homeClub, $match->awayClub])->firstWhere('id', $clubId)
                ?? $match->homeClub;

            return $candidate;
        }

        $eligibleIds = Club::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('id', [$match->home_club_id, $match->away_club_id])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        abort_if($eligibleIds === [], 403);

        $selectedId = in_array($clubId, $eligibleIds, true) ? $clubId : $eligibleIds[0];

        return Club::query()->findOrFail($selectedId);
    }

    /**
     * @param array<int, array{slot:string,label:string,group:string,x:int,y:int}> $slots
     * @return array{
     *   starters: array<string, int|null>,
     *   bench: array<int, int>,
     *   starterIds: array<int, int>,
     *   allIds: array<int, int>
     * }
     */
    private function resolveSelection(Club $club, array $slots, Request $request): array
    {
        $starterInput = collect($request->input('starter_slots', []))
            ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
            ->all();

        $benchInput = collect($request->input('bench_slots', []))
            ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
            ->filter()
            ->values()
            ->all();

        $starters = [];
        foreach ($slots as $slot) {
            $starters[$slot['slot']] = $starterInput[$slot['slot']] ?? null;
        }

        $starterIds = collect($starters)->filter()->values();
        $allIds = $starterIds->concat($benchInput)->filter()->values();

        abort_if($starterIds->count() > 11, 422, 'Es sind maximal 11 Startplaetze erlaubt.');
        abort_if($benchInput && count($benchInput) > 5, 422, 'Es sind maximal 5 Bankplaetze erlaubt.');
        abort_if($allIds->count() !== $allIds->unique()->count(), 422, 'Ein Spieler darf nur einmal aufgestellt sein.');

        $clubPlayerIds = $club->players()->pluck('id');
        abort_if($allIds->diff($clubPlayerIds)->isNotEmpty(), 422, 'Es wurden ungueltige Spieler ausgewaehlt.');

        return [
            'starters' => $starters,
            'bench' => array_values(array_slice(array_unique($benchInput), 0, 5)),
            'starterIds' => $starterIds->all(),
            'allIds' => $allIds->all(),
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
                'tactical_style' => 'balanced',
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
            'tactical_style' => $lineup->tactical_style ?: 'balanced',
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
    private function normalizeBenchDraft(mixed $draft): array
    {
        $source = is_array($draft) ? $draft : [];

        return collect($source)
            ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
            ->take(5)
            ->values()
            ->all();
    }

    private function draftKey(Request $request, GameMatch $match, Club $club): string
    {
        return 'match_lineup_draft_'.$request->user()->id.'_'.$match->id.'_'.$club->id;
    }

    private function resolveRouteMatch(Request $request, GameMatch $match): GameMatch
    {
        if ($match->exists) {
            return $match;
        }

        $routeMatch = $request->route('match');
        if ($routeMatch instanceof GameMatch) {
            return $routeMatch;
        }

        if (is_numeric($routeMatch)) {
            return GameMatch::query()->findOrFail((int) $routeMatch);
        }

        abort(404, 'Partie konnte nicht geladen werden.');
    }
}
