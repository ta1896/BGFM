<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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
            ->whereHas('club', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with('club')
            ->orderByDesc('overall');

        if ($clubId > 0) {
            $playerQuery->where('club_id', $clubId);
        }

        return view('players.index', [
            'players' => $playerQuery->paginate(15)->withQueryString(),
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
            'position' => ['required', 'in:TW,LV,IV,RV,LWB,RWB,LM,ZM,RM,DM,OM,LAM,ZOM,RAM,LS,MS,RS,LW,RW,ST'],
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
        $this->ensureOwnership($request, $player);

        return view('players.show', ['player' => $player->load('club')]);
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

        $validated = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'position' => ['required', 'in:TW,LV,IV,RV,LWB,RWB,LM,ZM,RM,DM,OM,LAM,ZOM,RAM,LS,MS,RS,LW,RW,ST'],
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
        $player->update(array_merge($validated, ['club_id' => $club->id]));

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
            'LV' => 'Linksverteidiger',
            'IV' => 'Innenverteidiger',
            'RV' => 'Rechtsverteidiger',
            'LWB' => 'Linker Wingback',
            'RWB' => 'Rechter Wingback',
            'LM' => 'Linkes Mittelfeld',
            'ZM' => 'Zentrales Mittelfeld',
            'RM' => 'Rechtes Mittelfeld',
            'DM' => 'Defensives Mittelfeld',
            'OM' => 'Offensives Mittelfeld',
            'LAM' => 'Linker Offensiver',
            'ZOM' => 'Zentrales Offensives Mittelfeld',
            'RAM' => 'Rechter Offensiver',
            'LS' => 'Linker Stuermer',
            'MS' => 'Mittelstuermer',
            'RS' => 'Rechter Stuermer',
            'LW' => 'Linker Fluegel',
            'RW' => 'Rechter Fluegel',
            'ST' => 'Stuermer',
        ];
    }
}
