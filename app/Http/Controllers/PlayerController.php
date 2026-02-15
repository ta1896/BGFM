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
    public function index(Request $request): View
    {
        $clubId = (int) $request->query('club');

        $playerQuery = Player::query()
            ->whereHas('club', fn($query) => $query->where('user_id', $request->user()->id))
            ->with('club')
            ->orderByRaw("FIELD(position, 'TW', 'LV', 'IV', 'RV', 'DM', 'LM', 'ZM', 'RM', 'OM', 'LF', 'HS', 'MS', 'RF')")
            ->orderByDesc('overall');

        if ($clubId > 0) {
            $playerQuery->where('club_id', $clubId);
        }

        $players = $playerQuery->get();

        $squadStats = [
            'count' => $players->count(),
            'avg_age' => $players->isNotEmpty() ? round($players->avg('age'), 1) : 0,
            'avg_rating' => $players->isNotEmpty() ? round($players->avg('overall'), 1) : 0,
            'total_value' => $players->sum('market_value'),
            'avg_value' => $players->isNotEmpty() ? $players->avg('market_value') : 0,
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

        return view('players.index', [
            'groupedPlayers' => $groupedPlayers,
            'squadStats' => $squadStats,
            'clubs' => $request->user()->clubs()->orderBy('name')->get(),
            'activeClubId' => $clubId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return view('players.create', [
            'clubs' => $request->user()->clubs()->orderBy('name')->get(),
            'positions' => $this->positions(),
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
    public function show(Request $request, Player $player): View
    {
        // Removed ensureOwnership to allow public viewing

        $player->load([
            'club.stadium',
            'club.user',
            'seasonCompetitionStatistics.season',
        ]);

        $currentSeasonStats = $player->seasonCompetitionStatistics
            ->filter(fn($stat) => $stat->season_id === ($player->club->season_id ?? 0));

        // Use season stats for career history, sorted by season desc
        $careerStats = $player->seasonCompetitionStatistics
            ->sortByDesc(fn($stat) => $stat->season->start_date ?? '');

        // Fetch recent matches
        $recentMatches = \App\Models\MatchPlayerStat::query()
            ->where('player_id', $player->id)
            ->with(['match.homeClub', 'match.awayClub', 'match.competitionSeason.competition'])
            ->whereHas('match', fn($query) => $query->where('status', 'played'))
            ->orderByDesc(
                \App\Models\GameMatch::select('kickoff_at')
                    ->whereColumn('matches.id', 'match_player_stats.match_id')
            )
            ->take(10)
            ->get();

        return view('players.show', [
            'player' => $player,
            'currentSeasonStats' => $currentSeasonStats,
            'careerStats' => $careerStats,
            'recentMatches' => $recentMatches,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Player $player): View
    {
        $this->ensureOwnership($request, $player);

        return view('players.edit', [
            'player' => $player,
            'clubs' => $request->user()->clubs()->orderBy('name')->get(),
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
