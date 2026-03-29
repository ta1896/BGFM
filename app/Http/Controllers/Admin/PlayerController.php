<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Player;
use App\Jobs\BulkSyncSofascoreJob;
use App\Jobs\SyncPlayerSofascoreJob;
use App\Jobs\SyncPlayerTransferHistoryJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\DataCenter\Models\ImportLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PlayerController extends Controller
{
    /**
     * Dispatch the bulk sync job for all players.
     */
    public function bulkSyncSofascore(): RedirectResponse
    {
        BulkSyncSofascoreJob::dispatch();

        return redirect()->back()->with('status', 'Bulk-Sync für Sofascore Spielerdaten wurde im Hintergrund gestartet!');
    }

    /**
     * Clear the bulk sync logs.
     */
    public function clearBulkSyncLogs(): RedirectResponse
    {
        ImportLog::where('league_id', 'bulk_sync_sofascore')->delete();

        return redirect()->back()->with('status', 'Bulk-Sync Journal wurde geleert.');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Inertia\Response
    {
        $clubId = (int) $request->query('club');

        $query = Player::query()
            ->with(['club.user'])
            ->orderByRaw("FIELD(position, 'TW', 'LV', 'IV', 'RV', 'DM', 'LM', 'ZM', 'RM', 'OM', 'LF', 'HS', 'MS', 'RF')")
            ->orderByDesc('overall');

        if ($clubId > 0) {
            $query->where('club_id', $clubId);
            $players = $query->get(); // Show all for specific club squad view
        } else {
            $players = $query->paginate(20)->withQueryString();
        }

        // Stats calculation
        $statsPlayers = ($players instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $players->getCollection() : $players;

        $squadStats = [
            'count' => $statsPlayers->count(),
            'avg_age' => $statsPlayers->isNotEmpty() ? round($statsPlayers->avg('age'), 1) : 0,
            'avg_rating' => $statsPlayers->isNotEmpty() ? round($statsPlayers->avg('overall'), 1) : 0,
            'total_value' => (float) $statsPlayers->sum('market_value'),
            'avg_value' => $statsPlayers->isNotEmpty() ? (float) $statsPlayers->avg('market_value') : 0,
            'injured_count' => $statsPlayers->where('is_injured', true)->count(),
            'suspended_count' => $statsPlayers->where('is_suspended', true)->count(),
        ];

        // Group only if club selected
        $groupedPlayers = null;
        if ($clubId > 0) {
            $groupedPlayers = $statsPlayers->groupBy(fn($player) => match (true) {
                in_array($player->position, ['GK', 'TW']) => 'Torhüter',
                in_array($player->position, ['LB', 'CB', 'RB', 'LWB', 'RWB', 'LV', 'IV', 'RV']) => 'Abwehr',
                in_array($player->position, ['CDM', 'CM', 'CAM', 'LM', 'RM', 'DM', 'ZM', 'OM']) => 'Mittelfeld',
                default => 'Sturm',
            })->sortBy(fn($group, $key) => match ($key) {
                    'Torhüter' => 1,
                    'Abwehr' => 2,
                    'Mittelfeld' => 3,
                    'Sturm' => 4,
                    default => 99,
                })->map(fn($group) => $group->values()); // Reset keys for JSON
        }

        return \Inertia\Inertia::render('Admin/Players/Index', [
            'players' => $players,
            'groupedPlayers' => $groupedPlayers,
            'squadStats' => $squadStats,
            'clubs' => Club::with('user')->orderBy('name')->get(),
            'activeClubId' => $clubId,
            'bulkSyncLogs' => ImportLog::where('league_id', 'bulk_sync_sofascore')
                ->orderByDesc('created_at')
                ->limit(15)
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Inertia\Response
    {
        return \Inertia\Inertia::render('Admin/Players/Form', [
            'clubs' => Club::with('user')->orderBy('name')->get(),
            'positions' => $this->positions(),
            'player' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $validated = $this->handlePhotoUpload($request, $validated);
        $player = Player::create($validated);

        return redirect()
            ->route('admin.players.edit', $player)
            ->with('status', 'Spieler im ACP erstellt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Player $player): RedirectResponse
    {
        return redirect()->route('admin.players.edit', $player);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Player $player): \Inertia\Response
    {
        return \Inertia\Inertia::render('Admin/Players/Form', [
            'player' => $player,
            'clubs' => Club::with('user')->orderBy('name')->get(),
            'positions' => $this->positions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Player $player): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $validated = $this->handlePhotoUpload($request, $validated, $player->photo_path);
        $player->update($validated);

        return redirect()
            ->route('admin.players.edit', $player)
            ->with('status', 'Spieler aktualisiert.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Player $player): RedirectResponse
    {
        if ($player->photo_path) {
            Storage::delete($player->photo_path);
        }

        $player->delete();

        return redirect()
            ->route('admin.players.index')
            ->with('status', 'Spieler wurde geloescht.');
    }

    public function syncTransferHistory(Player $player): RedirectResponse
    {
        if (!$player->transfermarkt_id && !$player->transfermarkt_url) {
            return back()->with('error', 'Keine Transfermarkt-ID oder URL beim Spieler hinterlegt.');
        }

        SyncPlayerTransferHistoryJob::dispatch($player->id);

        return back()->with('status', 'Transferhistorie wurde zur Synchronisation eingeplant.');
    }

    public function syncSofascore(Player $player): RedirectResponse
    {
        if (!$player->sofascore_id) {
            return back()->with('error', 'Keine Sofascore-ID beim Spieler hinterlegt.');
        }

        SyncPlayerSofascoreJob::dispatch($player);

        return back()->with('status', 'Sofascore-Daten wurden zur Synchronisation eingeplant.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'position' => ['required', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
            'position_second' => ['nullable', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
            'position_third' => ['nullable', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
            'age' => ['required', 'integer', 'min:15', 'max:45'],
            'overall' => ['required', 'integer', 'min:1', 'max:99'],
            'technical' => ['nullable', 'integer', 'min:0', 'max:100'],
            'potential' => ['sometimes', 'integer', 'min:1', 'max:99'],
            'market_value' => ['required', 'numeric', 'min:0'],
            'salary' => ['required', 'numeric', 'min:0'],
            'is_imported' => ['sometimes', 'boolean'],
            'transfermarkt_id' => ['nullable', 'string', 'max:50'],
            'sofascore_id' => ['nullable', 'string', 'max:50'],
            'birthday' => ['nullable', 'date'],
            'attr_attacking' => ['nullable', 'integer', 'min:0', 'max:100'],
            'attr_technical' => ['nullable', 'integer', 'min:0', 'max:100'],
            'attr_tactical' => ['nullable', 'integer', 'min:0', 'max:100'],
            'attr_defending' => ['nullable', 'integer', 'min:0', 'max:100'],
            'attr_creativity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'attr_market' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
    }

    private function handlePhotoUpload(Request $request, array $validated, ?string $previousPath = null): array
    {
        if (!$request->hasFile('photo')) {
            unset($validated['photo']);

            return $validated;
        }

        $path = $request->file('photo')->store('public/player-photos');
        $validated['photo_path'] = $path;
        unset($validated['photo']);

        if ($previousPath) {
            Storage::delete($previousPath);
        }

        return $validated;
    }

    private function positions(): array
    {
        return [
            'TW' => 'Torwart',
            'IV' => 'Innenverteidiger',
            'LV' => 'Linksverteidiger',
            'RV' => 'Rechtsverteidiger',
            'ZM' => 'Zentrales Mittelfeld',
            'DM' => 'Defensives Mittelfeld',
            'OM' => 'Offensives Mittelfeld',
            'LM' => 'Linkes Mittelfeld',
            'RM' => 'Rechtes Mittelfeld',
            'LF' => 'Linker Fluegel',
            'MS' => 'Mittelstuermer',
            'HS' => 'Haengende Spitze',
            'RF' => 'Rechter Fluegel',
        ];
    }
}
