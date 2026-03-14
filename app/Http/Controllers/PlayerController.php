<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Inertia\Response
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;
        $clubs = $request->user()->isAdmin() 
            ? \App\Models\Club::where('is_cpu', false)->orderBy('name')->get()
            : $request->user()->clubs()->orderBy('name')->get();

        if (!$activeClub && $clubs->isNotEmpty()) {
            $activeClub = $clubs->first();
        }

        $playerQuery = Player::query()
            ->with(['club'])
            ->orderByRaw("FIELD(position, 'TW', 'LV', 'IV', 'RV', 'DM', 'LM', 'ZM', 'RM', 'OM', 'LF', 'HS', 'MS', 'RF')")
            ->orderByDesc('overall');

        if ($activeClub) {
            $playerQuery->where('club_id', $activeClub->id);
        } elseif (!$request->user()->isAdmin()) {
            $playerQuery->whereHas('club', fn($query) => $query->where('user_id', $request->user()->id));
        }

        $players = $playerQuery->get()->map(function($p) {
            $p->append('photo_url');
            $p->market_value_formatted = number_format($p->market_value, 0, ',', '.') . ' €';
            $p->display_position = $p->position; // Or use a translation map
            return $p;
        });

        $squadStats = [
            'count' => $players->count(),
            'avg_age' => $players->isNotEmpty() ? round($players->avg('age'), 1) : 0,
            'avg_rating' => $players->isNotEmpty() ? round($players->avg('overall'), 1) : 0,
            'total_value' => $players->sum('market_value'),
            'total_value_formatted' => number_format($players->sum('market_value'), 0, ',', '.') . ' €',
            'avg_value' => $players->isNotEmpty() ? $players->avg('market_value') : 0,
            'avg_value_formatted' => number_format($players->isNotEmpty() ? $players->avg('market_value') : 0, 0, ',', '.') . ' €',
            'injured_count' => $players->where('is_injured', true)->count(),
            'suspended_count' => $players->where('is_suspended', true)->count(),
        ];

        $groupedPlayers = $players->groupBy(fn($player) => match (true) {
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
            });

        return \Inertia\Inertia::render('Players/Index', [
            'groupedPlayers' => $groupedPlayers,
            'squadStats' => $squadStats,
            'clubs' => $request->user()->clubs()->orderBy('name')->get(),
            'activeClubId' => $activeClub?->id,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): \Inertia\Response
    {
        return \Inertia\Inertia::render('Players/Form', [
            'clubs' => $request->user()->isAdmin()
                ? \App\Models\Club::where('is_cpu', false)->orderBy('name')->get()
                : $request->user()->clubs()->orderBy('name')->get(),
            'positions' => $this->positions(),
            'player' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'position' => ['required', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
            'age' => ['required', 'integer', 'min:15', 'max:45'],
            'overall' => ['required', 'integer', 'min:1', 'max:99'],
            'pace' => ['required', 'integer', 'min:1', 'max:99'],
            'shooting' => ['required', 'integer', 'min:1', 'max:99'],
            'passing' => ['required', 'integer', 'min:1', 'max:99'],
            'defending' => ['required', 'integer', 'min:1', 'max:99'],
            'physical' => ['required', 'integer', 'min:1', 'max:99'],
            'stamina' => ['required', 'integer', 'min:1', 'max:100'],
            'morale' => ['required', 'integer', 'min:1', 'max:100'],
            'market_value' => ['required', 'numeric', 'min:0'],
            'salary' => ['required', 'numeric', 'min:0'],
        ]);

        $club = $this->ownedClub($request, (int) $validated['club_id']);
        $validated = $this->handlePhotoUpload($request, $validated);

        $club->players()->create($validated);

        return redirect()
            ->route('players.index', ['club' => $club->id])
            ->with('status', 'Spieler wurde hinzugefuegt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Player $player): \Inertia\Response
    {
        $player->load([
            'club.stadium',
            'club.user',
            'seasonCompetitionStatistics.season',
        ]);

        $player->append('photo_url');
        
        // Add formatted value for easy display
        $player->market_value_formatted = number_format($player->market_value, 0, ',', '.') . ' €';

        $currentSeasonStats = $player->seasonCompetitionStatistics
            ->filter(fn($stat) => $stat->season_id === ($player->club->season_id ?? 0))
            ->values();

        // Use season stats for career history, sorted by season desc
        $careerStats = $player->seasonCompetitionStatistics
            ->sortByDesc(fn($stat) => $stat->season?->start_date ?? '')
            ->values();

        // Fetch recent matches
        $recentMatches = \App\Models\MatchPlayerStat::query()
            ->where('player_id', $player->id)
            ->with(['match.homeClub:id,name,short_name,logo_path', 'match.awayClub:id,name,short_name,logo_path', 'match.competitionSeason.competition'])
            ->whereHas('match', fn($query) => $query->where('status', 'played'))
            ->orderByDesc(
                \App\Models\GameMatch::select('kickoff_at')
                    ->whereColumn('matches.id', 'match_player_stats.match_id')
            )
            ->take(10)
            ->get()
            ->map(function($stat) {
                if ($stat->match) {
                    $stat->match->kickoff_date_formatted = $stat->match->kickoff_at?->format('d.m.y');
                }
                return $stat;
            });

        return \Inertia\Inertia::render('Players/Show', [
            'player' => $player,
            'currentSeasonStats' => $currentSeasonStats,
            'careerStats' => $careerStats,
            'recentMatches' => $recentMatches,
            'isOwner' => $player->club && $request->user()->id === $player->club->user_id,
            'positions' => array_keys($this->positions()),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Player $player): \Inertia\Response
    {
        $this->ensureOwnership($request, $player);

        return \Inertia\Inertia::render('Players/Form', [
            'player' => $player->append('photo_url'),
            'clubs' => $request->user()->isAdmin()
                ? \App\Models\Club::where('is_cpu', false)->orderBy('name')->get()
                : $request->user()->clubs()->orderBy('name')->get(),
            'positions' => $this->positions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Player $player): RedirectResponse
    {
        $this->ensureOwnership($request, $player);

        // Validation relaxed for "Customize" tab usage, but keeping strict for full edit if needed.
        // We check if it's a full update or just a customize update based on presence of fields.

        $rules = [
            'market_value' => ['nullable', 'numeric', 'min:0'],
            'position' => ['nullable', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
            'position_second' => ['nullable', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
            'position_third' => ['nullable', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'photo_url' => ['nullable', 'url', 'max:255'],
        ];

        // If it's a standard update (from ACP or edit form), we might expect other fields.
        // But for now, we merge valid data.

        $validated = $request->validate($rules);

        // Handle Photo
        // Priority: Upload > URL > Existing
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/player-photos');
            $validated['photo_path'] = $path;
            if ($player->photo_path) {
                Storage::delete($player->photo_path);
            }
        } elseif (!empty($validated['photo_url'])) {
            // If URL is provided and no file uploaded
            $validated['photo_path'] = $validated['photo_url'];
            // If previous was a file, strictly speaking we should delete it if we overwrite with URL, 
            // but maybe we keep it? Let's delete to be clean if it was a storage path.
            if ($player->photo_path && !str_starts_with($player->photo_path, 'http')) {
                Storage::delete($player->photo_path);
            }
        }

        // Filter nulls to avoid overwriting with null if partial update
        $dataToUpdate = array_filter($validated, fn($value) => !is_null($value));

        // Handle position mapping if coming from customize form
        if (isset($validated['position']))
            $dataToUpdate['position'] = $validated['position'];
        if (isset($validated['position_second']))
            $dataToUpdate['position_second'] = $validated['position_second'];
        if (isset($validated['position_third']))
            $dataToUpdate['position_third'] = $validated['position_third'];


        // Special case: standard update fields might need to be preserved if missing? 
        // The original update required EVERYTHING. 
        // If we want to support the "Customize" form AND the "Edit" form, we need to be careful.
        // The "Edit" form sends all fields. The "Customize" form sends specific fields.

        // If 'first_name' is missing, it's likely a partial update from "Customize".
        if (!$request->has('first_name')) {
            $player->update($dataToUpdate);
        } else {
            // Full update logic (legacy/ACP)
            $fullRules = [
                'club_id' => ['required', 'integer', 'exists:clubs,id'],
                'first_name' => ['required', 'string', 'max:80'],
                'last_name' => ['required', 'string', 'max:80'],
                'position' => ['required', 'in:TW,IV,LV,RV,ZM,DM,OM,LM,RM,LF,MS,HS,RF'],
                'age' => ['required', 'integer', 'min:15', 'max:45'],
                'overall' => ['required', 'integer', 'min:1', 'max:99'],
                'pace' => ['required', 'integer', 'min:1', 'max:99'],
                'shooting' => ['required', 'integer', 'min:1', 'max:99'],
                'passing' => ['required', 'integer', 'min:1', 'max:99'],
                'defending' => ['required', 'integer', 'min:1', 'max:99'],
                'physical' => ['required', 'integer', 'min:1', 'max:99'],
                'stamina' => ['required', 'integer', 'min:1', 'max:100'],
                'morale' => ['required', 'integer', 'min:1', 'max:100'],
                'market_value' => ['required', 'numeric', 'min:0'],
                'salary' => ['required', 'numeric', 'min:0'],
                'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            ];
            $fullValidated = $request->validate($fullRules);

            // Handle photo for full update
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/player-photos');
                $fullValidated['photo_path'] = $path;
                if ($player->photo_path)
                    Storage::delete($player->photo_path);
            }

            $club = $this->ownedClub($request, (int) $fullValidated['club_id']);
            $player->update(array_merge($fullValidated, ['club_id' => $club->id]));
        }

        return redirect()
            ->route('players.show', $player)
            ->with('status', 'Spieler wurde aktualisiert.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Player $player): RedirectResponse
    {
        $this->ensureOwnership($request, $player);

        $clubId = $player->club_id;

        if ($player->photo_path) {
            Storage::delete($player->photo_path);
        }

        $player->delete();

        return redirect()
            ->route('players.index', ['club' => $clubId])
            ->with('status', 'Spieler wurde geloescht.');
    }

    private function ensureOwnership(Request $request, Player $player): void
    {
        abort_unless($player->club()->where('user_id', $request->user()->id)->exists(), 403);
    }

    private function ownedClub(Request $request, int $clubId): Club
    {
        $club = $request->user()->clubs()->whereKey($clubId)->first();
        abort_unless($club, 403);

        return $club;
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
}
