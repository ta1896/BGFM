<?php

namespace App\Modules\ScoutingCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Player;
use App\Models\ScoutingDiscovery;
use App\Models\ScoutingReport;
use App\Models\ScoutingWatchlist;
use App\Services\ScoutingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScoutingCenterController extends Controller
{
    public function index(Request $request, ScoutingService $scoutingService): Response
    {
        $scoutSlots = max(1, min(6, (int) config('simulation.modules.scouting_center.scout_slots', 3)));
        $targetLimit = max(8, min(60, (int) config('simulation.modules.scouting_center.target_limit', 24)));
        $discoveryLimit = max(4, min(30, (int) config('simulation.modules.scouting_center.discovery_limit', 12)));
        $defaultMarket = (string) config('simulation.modules.scouting_center.default_market', 'domestic');
        $defaultDiscoveryLevel = (string) config('simulation.modules.scouting_center.default_discovery_level', 'experienced');
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;

        if (!$activeClub) {
            $activeClub = $request->user()->isAdmin()
                ? Club::query()->where('is_cpu', false)->orderBy('name')->first()
                : $request->user()->clubs()->where('is_cpu', false)->orderBy('name')->first();
        }

        $search = trim((string) $request->query('search', ''));
        $market = (string) $request->query('market', $defaultMarket);
        $position = (string) $request->query('position', 'all');
        $ageBand = (string) $request->query('age_band', 'all');
        $valueBand = (string) $request->query('value_band', 'all');
        $discoveryLevel = (string) $request->query('discovery_level', $defaultDiscoveryLevel);

        $playerQuery = Player::query()
            ->with(['club', 'injuries', 'recoveryLogs'])
            ->when($activeClub, fn ($builder) => $builder->where('club_id', '!=', $activeClub->id))
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($inner) use ($search): void {
                    $inner
                        ->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%');
                });
            });

        $this->applyMarketScope($playerQuery, $activeClub, $market);
        $this->applyPositionScope($playerQuery, $position);
        $this->applyAgeScope($playerQuery, $ageBand);
        $this->applyValueScope($playerQuery, $valueBand);

        $query = $playerQuery
            ->orderByDesc($discoveryLevel === 'elite' ? 'potential' : 'overall')
            ->orderBy('age')
            ->limit($targetLimit)
            ->get();

        $discoveries = collect();
        $scanState = null;
        if ($activeClub) {
            $scanState = $scoutingService->discoveryScanState($activeClub, [
                'market' => $market,
                'position' => $position,
                'age_band' => $ageBand,
                'value_band' => $valueBand,
                'discovery_level' => $discoveryLevel,
            ]);
            $discoveries = ScoutingDiscovery::query()
                ->where('club_id', $activeClub->id)
                ->where('market', $market)
                ->where('position_group', $position)
                ->where('age_band', $ageBand)
                ->where('value_band', $valueBand)
                ->where('discovery_level', $discoveryLevel)
                ->with('player.club')
                ->latest('scanned_at')
                ->limit($discoveryLimit)
                ->get();
        }

        $marketCounts = [
            'domestic' => $this->marketCount($activeClub, 'domestic'),
            'continental' => $this->marketCount($activeClub, 'continental'),
            'global' => $this->marketCount($activeClub, 'global'),
        ];

        $watchlist = collect();
        $scouts = collect();
        if ($activeClub) {
            $scouts = $scoutingService->availableScouts($activeClub, $request->user()?->id);
            $watchlist = ScoutingWatchlist::query()
                ->where('club_id', $activeClub->id)
                ->with(['player.club', 'reports' => fn ($query) => $query->latest('id')->limit(3), 'scout'])
                ->latest('updated_at')
                ->get();
        }

        return Inertia::render('Modules/ScoutingCenter/Index', [
            'club' => $activeClub ? [
                'id' => $activeClub->id,
                'name' => $activeClub->name,
                'logo_url' => $activeClub->logo_url,
                'budget' => (float) $activeClub->budget,
            ] : null,
            'scoutOptions' => [
                'levels' => ['junior', 'experienced', 'elite'],
                'regions' => ['domestic', 'continental', 'global'],
                'types' => ['live', 'video', 'data'],
                'focuses' => ['general', 'tactical', 'medical', 'personality'],
                'markets' => ['domestic', 'continental', 'global'],
                'positions' => ['all', 'GK', 'DEF', 'MID', 'ATT'],
                'ageBands' => ['all', 'u21', '21_25', '26_30', '31_plus'],
                'valueBands' => ['all', 'budget', 'mid', 'premium', 'elite'],
                'slot_limit' => $scoutSlots,
            ],
            'filters' => [
                'search' => $search,
                'market' => $market,
                'position' => $position,
                'age_band' => $ageBand,
                'value_band' => $valueBand,
                'discovery_level' => $discoveryLevel,
            ],
            'marketCounts' => $marketCounts,
            'moduleSettings' => [
                'scout_slots' => $scoutSlots,
                'default_market' => $defaultMarket,
                'default_discovery_level' => $defaultDiscoveryLevel,
                'target_limit' => $targetLimit,
                'discovery_limit' => $discoveryLimit,
                'discovery_note_prefix' => (string) config('simulation.modules.scouting_center.discovery_note_prefix', ''),
                'scan_cooldown_minutes' => max(10, min(480, (int) config('simulation.modules.scouting_center.scan_cooldown_minutes', 45))),
                'rotation_window_minutes' => max(30, min(1440, (int) config('simulation.modules.scouting_center.rotation_window_minutes', 180))),
            ],
            'scanState' => $scanState ? [
                'cooldown_active' => (bool) $scanState['cooldown_active'],
                'minutes_remaining' => (int) $scanState['minutes_remaining'],
                'last_scan_at' => $scanState['last_scan_at']?->format('d.m.Y H:i'),
                'next_scan_at' => $scanState['next_scan_at']?->format('d.m.Y H:i'),
                'cooldown_minutes' => (int) $scanState['cooldown_minutes'],
                'rotation_window_minutes' => (int) $scanState['rotation_window_minutes'],
            ] : null,
            'scoutStaff' => $scouts->map(fn ($scout) => [
                'id' => $scout->id,
                'name' => $scout->name,
                'level' => $scout->level,
                'specialty' => $scout->specialty,
                'region' => $scout->region,
                'status' => $scout->status,
                'workload' => (int) $scout->workload,
                'available_at' => $scout->available_at?->format('d.m.Y H:i'),
                'active_watchlist_id' => $scout->active_watchlist_id,
            ])->values()->all(),
            'discoveries' => $discoveries->map(fn (ScoutingDiscovery $entry) => [
                'id' => $entry->id,
                'fit_score' => (int) $entry->fit_score,
                'market_band' => $entry->market_band,
                'region_tag' => $entry->region_tag,
                'discovery_note' => $entry->discovery_note,
                'scanned_at' => $entry->scanned_at?->format('d.m.Y H:i'),
                'player' => [
                    'id' => $entry->player?->id,
                    'name' => $entry->player?->full_name,
                    'photo_url' => $entry->player?->photo_url,
                    'position' => $entry->player?->display_position,
                    'age' => (int) ($entry->player?->age ?? 0),
                    'club_name' => $entry->player?->club?->name,
                    'country' => $entry->player?->club?->country,
                    'potential_hint' => $entry->player && $entry->player->potential >= 80 ? 'Elite-Profil' : ($entry->player && $entry->player->potential >= 72 ? 'Spannend' : 'Breite'),
                ],
            ])->values()->all(),
            'targets' => $query->map(fn (Player $player) => [
                'id' => $player->id,
                'name' => $player->full_name,
                'photo_url' => $player->photo_url,
                'position' => $player->display_position,
                'age' => (int) $player->age,
                'club_name' => $player->club?->name,
                'country' => $player->club?->country,
                'market_band' => $this->marketValueBand((float) $player->market_value),
                'potential_hint' => $player->potential >= 80 ? 'Elite-Profil' : ($player->potential >= 72 ? 'Spannend' : 'Breite'),
                'fit_score' => $this->fitScore($player, $activeClub, $position),
                'region_tag' => $this->regionTag($activeClub, $player),
                'discovery_note' => $this->discoveryNote($player, $discoveryLevel),
            ])->values()->all(),
            'watchlist' => $watchlist->map(function (ScoutingWatchlist $entry) use ($activeClub, $scoutingService) {
                $report = $entry->reports->first();

                return [
                    'id' => $entry->id,
                    'priority' => $entry->priority,
                    'status' => $entry->status,
                    'focus' => $entry->focus,
                    'scout_level' => $entry->scout_level,
                    'scout_region' => $entry->scout_region,
                    'scout_type' => $entry->scout_type,
                    'scout_id' => $entry->scout_id,
                    'progress' => (int) $entry->progress,
                    'reports_requested' => (int) $entry->reports_requested,
                    'mission_days_left' => (int) $entry->mission_days_left,
                    'last_mission_cost' => (float) $entry->last_mission_cost,
                    'last_scouted_at' => $entry->last_scouted_at?->format('d.m.Y H:i'),
                    'next_report_due_at' => $entry->next_report_due_at?->format('d.m.Y'),
                    'notes' => $entry->notes,
                    'player' => [
                        'id' => $entry->player?->id,
                        'name' => $entry->player?->full_name,
                        'photo_url' => $entry->player?->photo_url,
                        'position' => $entry->player?->display_position,
                        'club_name' => $entry->player?->club?->name,
                        'country' => $entry->player?->club?->country,
                    ],
                    'scout' => $entry->scout ? [
                        'id' => $entry->scout->id,
                        'name' => $entry->scout->name,
                        'level' => $entry->scout->level,
                        'specialty' => $entry->scout->specialty,
                        'region' => $entry->scout->region,
                        'status' => $entry->scout->status,
                        'workload' => (int) $entry->scout->workload,
                        'available_at' => $entry->scout->available_at?->format('d.m.Y H:i'),
                    ] : null,
                    'mission_preview' => $entry->player ? $scoutingService->previewMission(
                        $activeClub,
                        $entry->player,
                        $entry->priority,
                        $entry->focus,
                        $entry->scout_level,
                        $entry->scout_region,
                        $entry->scout_type,
                        $entry->scout,
                    ) : null,
                    'latest_report' => $report ? [
                        'created_at' => $report->created_at?->format('d.m.Y H:i'),
                        'confidence' => (int) $report->confidence,
                        'overall_band' => $report->overall_min.'-'.$report->overall_max,
                        'potential_band' => $report->potential_min.'-'.$report->potential_max,
                        'injury_risk_band' => $report->injury_risk_band,
                        'personality_band' => $report->personality_band,
                        'summary' => $report->summary,
                    ] : null,
                    'report_history' => $entry->reports->map(fn (ScoutingReport $history) => [
                        'id' => $history->id,
                        'confidence' => (int) $history->confidence,
                        'created_at' => $history->created_at?->format('d.m.Y H:i'),
                    ])->values()->all(),
                ];
            })->values()->all(),
        ]);
    }

    public function discoverTargets(Request $request, ScoutingService $scoutingService): RedirectResponse
    {
        $club = $this->resolveManagedClub($request);
        abort_unless($club, 422);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'market' => ['required', 'in:domestic,continental,global'],
            'position' => ['required', 'in:all,GK,DEF,MID,ATT'],
            'age_band' => ['required', 'in:all,u21,21_25,26_30,31_plus'],
            'value_band' => ['required', 'in:all,budget,mid,premium,elite'],
            'discovery_level' => ['required', 'in:junior,experienced,elite'],
        ]);

        $result = $scoutingService->discoverTargets($club, $validated, $request->user()->id);

        return back()->with('status', sprintf('%d neue Scout-Leads gefunden. Kosten: %s EUR.', $result['count'], number_format((float) $result['cost'], 0, ',', '.')));
    }

    public function storeWatchlist(Request $request, Player $player, ScoutingService $scoutingService): RedirectResponse
    {
        $club = $this->resolveManagedClub($request);
        abort_if(!$club || $player->club_id === $club->id, 422);

        $validated = $request->validate([
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:watching,priority,negotiating'],
            'focus' => ['nullable', 'in:general,tactical,medical,personality'],
            'scout_level' => ['nullable', 'in:junior,experienced,elite'],
            'scout_region' => ['nullable', 'in:domestic,continental,global'],
            'scout_type' => ['nullable', 'in:live,video,data'],
            'scout_id' => ['nullable', 'integer', 'exists:scouting_scouts,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $scoutingService->upsertWatchlist($player, $club->id, $request->user()->id, $validated);

        return back()->with('status', 'Spieler wurde auf die Watchlist gesetzt.');
    }

    public function updateWatchlist(Request $request, ScoutingWatchlist $watchlist, ScoutingService $scoutingService): RedirectResponse
    {
        $club = $this->resolveManagedClub($request);
        abort_unless($club && $watchlist->club_id === $club->id, 403);

        $validated = $request->validate([
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:watching,priority,negotiating'],
            'focus' => ['nullable', 'in:general,tactical,medical,personality'],
            'scout_level' => ['nullable', 'in:junior,experienced,elite'],
            'scout_region' => ['nullable', 'in:domestic,continental,global'],
            'scout_type' => ['nullable', 'in:live,video,data'],
            'scout_id' => ['nullable', 'integer', 'exists:scouting_scouts,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $watchlist->update($validated);

        if (array_key_exists('scout_id', $validated)) {
            $scoutingService->assignScoutToWatchlist($watchlist->fresh(['club', 'player.club', 'scout']), $validated['scout_id']);
        }

        return back()->with('status', 'Watchlist-Eintrag aktualisiert.');
    }

    public function destroyWatchlist(Request $request, ScoutingWatchlist $watchlist): RedirectResponse
    {
        $club = $this->resolveManagedClub($request);
        abort_unless($club && $watchlist->club_id === $club->id, 403);
        $watchlist->delete();

        return back()->with('status', 'Watchlist-Eintrag entfernt.');
    }

    public function generateReport(Request $request, Player $player, ScoutingService $scoutingService): RedirectResponse
    {
        $club = $this->resolveManagedClub($request);
        abort_if(!$club || $player->club_id === $club->id, 422);

        $watchlist = ScoutingWatchlist::query()
            ->where('club_id', $club->id)
            ->where('player_id', $player->id)
            ->first();

        $scoutingService->generateReport($player, $club->id, $watchlist?->id, $request->user()->id);

        return back()->with('status', 'Scout-Report wurde aktualisiert.');
    }

    public function advanceWatchlist(Request $request, ScoutingWatchlist $watchlist, ScoutingService $scoutingService): RedirectResponse
    {
        $club = $this->resolveManagedClub($request);
        abort_unless($club && $watchlist->club_id === $club->id, 403);

        $report = $scoutingService->advanceWatchlist($watchlist->loadMissing('player'), $request->user()->id);

        return back()->with('status', $report ? 'Scout-Mission abgeschlossen, neuer Report eingegangen.' : 'Scout-Mission wurde vorangetrieben.');
    }

    private function resolveManagedClub(Request $request): ?Club
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;

        if ($activeClub) {
            return $activeClub;
        }

        return $request->user()->isAdmin()
            ? Club::query()->where('is_cpu', false)->orderBy('name')->first()
            : $request->user()->clubs()->where('is_cpu', false)->orderBy('name')->first();
    }

    private function applyMarketScope($builder, ?Club $activeClub, string $market): void
    {
        if (!$activeClub?->country || $market === 'global') {
            return;
        }

        if ($market === 'domestic') {
            $builder->whereHas('club', fn ($clubQuery) => $clubQuery->where('country', $activeClub->country));

            return;
        }

        if ($market === 'continental') {
            $regionalPool = $this->regionalPoolForCountry($activeClub->country);
            $builder->whereHas('club', function ($clubQuery) use ($activeClub, $regionalPool): void {
                $clubQuery
                    ->whereIn('country', $regionalPool)
                    ->where('country', '!=', $activeClub->country);
            });
        }
    }

    private function applyPositionScope($builder, string $position): void
    {
        $map = [
            'GK' => ['GK'],
            'DEF' => ['LB', 'LWB', 'CB', 'RB', 'RWB', 'SW'],
            'MID' => ['CDM', 'CM', 'CAM', 'LM', 'RM'],
            'ATT' => ['LW', 'RW', 'CF', 'ST', 'LS', 'RS'],
        ];

        if (!isset($map[$position])) {
            return;
        }

        $builder->whereIn('position', $map[$position]);
    }

    private function applyAgeScope($builder, string $ageBand): void
    {
        match ($ageBand) {
            'u21' => $builder->where('age', '<=', 20),
            '21_25' => $builder->whereBetween('age', [21, 25]),
            '26_30' => $builder->whereBetween('age', [26, 30]),
            '31_plus' => $builder->where('age', '>=', 31),
            default => null,
        };
    }

    private function applyValueScope($builder, string $valueBand): void
    {
        match ($valueBand) {
            'budget' => $builder->where('market_value', '<=', 2500000),
            'mid' => $builder->whereBetween('market_value', [2500001, 12000000]),
            'premium' => $builder->whereBetween('market_value', [12000001, 30000000]),
            'elite' => $builder->where('market_value', '>', 30000000),
            default => null,
        };
    }

    private function marketCount(?Club $activeClub, string $market): int
    {
        $builder = Player::query();
        if ($activeClub) {
            $builder->where('club_id', '!=', $activeClub->id);
        }

        $this->applyMarketScope($builder, $activeClub, $market);

        return (int) $builder->count();
    }

    private function marketValueBand(float $value): string
    {
        return match (true) {
            $value <= 2500000 => 'Budgetfenster',
            $value <= 12000000 => 'Mittleres Regal',
            $value <= 30000000 => 'Premium-Ziel',
            default => 'Elite-Markt',
        };
    }

    private function fitScore(Player $player, ?Club $activeClub, string $position): int
    {
        $base = (int) round(($player->overall * 0.45) + ($player->potential * 0.35) + max(0, 30 - $player->age));

        if ($activeClub && $player->club?->country === $activeClub->country) {
            $base += 6;
        }

        if ($position !== 'all' && $this->positionMatchesGroup($player->position, $position)) {
            $base += 8;
        }

        return min(99, max(45, $base));
    }

    private function regionTag(?Club $activeClub, Player $player): string
    {
        if (!$activeClub?->country || !$player->club?->country) {
            return 'unbekannt';
        }

        if ($player->club->country === $activeClub->country) {
            return 'inland';
        }

        return in_array($player->club->country, $this->regionalPoolForCountry($activeClub->country), true)
            ? 'kontinental'
            : 'global';
    }

    private function discoveryNote(Player $player, string $discoveryLevel): string
    {
        $baseNote = match ($discoveryLevel) {
            'elite' => $player->potential >= 80 ? 'Scout sieht klares Top-Potenzial.' : 'Scout erkennt belastbares Profil.',
            'junior' => $player->age <= 22 ? 'Rohes Talent, mehr Beobachtung noetig.' : 'Erster Eindruck, noch unsicher.',
            default => $player->overall >= 72 ? 'Soforthilfe moeglich, Details folgen.' : 'Entwicklungsspieler mit offenen Fragen.',
        };

        $prefix = trim((string) config('simulation.modules.scouting_center.discovery_note_prefix', ''));

        return $prefix !== '' ? $prefix.' '.$baseNote : $baseNote;
    }

    private function regionalPoolForCountry(string $country): array
    {
        $pools = [
            'Deutschland' => ['Deutschland', 'Oesterreich', 'Schweiz', 'Niederlande', 'Belgien', 'Daenemark'],
            'Oesterreich' => ['Deutschland', 'Oesterreich', 'Schweiz', 'Tschechien', 'Ungarn'],
            'Schweiz' => ['Deutschland', 'Oesterreich', 'Schweiz', 'Frankreich', 'Italien'],
            'England' => ['England', 'Schottland', 'Wales', 'Irland', 'Niederlande', 'Belgien'],
            'Spanien' => ['Spanien', 'Portugal', 'Frankreich', 'Italien'],
            'Italien' => ['Italien', 'Schweiz', 'Frankreich', 'Spanien', 'Oesterreich'],
            'Frankreich' => ['Frankreich', 'Belgien', 'Niederlande', 'Schweiz', 'Spanien', 'Italien'],
        ];

        return $pools[$country] ?? [$country];
    }

    private function positionMatchesGroup(?string $position, string $group): bool
    {
        $groups = [
            'GK' => ['GK'],
            'DEF' => ['LB', 'LWB', 'CB', 'RB', 'RWB', 'SW'],
            'MID' => ['CDM', 'CM', 'CAM', 'LM', 'RM'],
            'ATT' => ['LW', 'RW', 'CF', 'ST', 'LS', 'RS'],
        ];

        return in_array((string) $position, $groups[$group] ?? [], true);
    }
}
