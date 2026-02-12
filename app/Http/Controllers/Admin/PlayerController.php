<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $query = Player::query()
            ->with(['club.user'])
            ->orderByDesc('overall');

        if ($clubId > 0) {
            $query->where('club_id', $clubId);
        }

        return view('admin.players.index', [
            'players' => $query->paginate(20)->withQueryString(),
            'clubs' => Club::with('user')->orderBy('name')->get(),
            'activeClubId' => $clubId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.players.create', [
            'clubs' => Club::with('user')->orderBy('name')->get(),
            'positions' => $this->positions(),
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
    public function edit(Player $player): View
    {
        return view('admin.players.edit', [
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
        $player->delete();

        return redirect()
            ->route('admin.players.index')
            ->with('status', 'Spieler wurde geloescht.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
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
    }

    private function handlePhotoUpload(Request $request, array $validated, ?string $previousPath = null): array
    {
        if (!$request->hasFile('photo')) {
            return $validated;
        }

        $path = $request->file('photo')->store('public/player-photos');
        $validated['photo_path'] = $path;

        if ($previousPath) {
            Storage::delete($previousPath);
        }

        return $validated;
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
