@csrf

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    <div>
        <label class="sim-label" for="club_id">Verein</label>
        <select class="sim-select" id="club_id" name="club_id" required>
            @foreach ($clubs as $clubOption)
                <option value="{{ $clubOption->id }}" @selected(old('club_id', $player->club_id ?? '') == $clubOption->id)>
                    {{ $clubOption->name }} (Owner: {{ $clubOption->user?->name ?? 'CPU' }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('club_id')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="first_name">Vorname</label>
        <input class="sim-input" id="first_name" name="first_name" type="text"
            value="{{ old('first_name', $player->first_name ?? '') }}" required>
        <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="last_name">Nachname</label>
        <input class="sim-input" id="last_name" name="last_name" type="text"
            value="{{ old('last_name', $player->last_name ?? '') }}" required>
        <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="photo">Spielerfoto</label>
        <input class="sim-input" id="photo" name="photo" type="file" accept="image/*">
        <x-input-error :messages="$errors->get('photo')" class="mt-1" />
        @if (!empty($player?->photo_path))
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                <img class="h-8 w-8 rounded-full object-cover border border-slate-700" src="{{ $player->photo_url }}"
                    alt="Spielerfoto">
                <span>Aktuelles Foto</span>
            </div>
        @endif
    </div>
    <div>
        <label class="sim-label" for="position">Position</label>
        <select class="sim-select" id="position" name="position" required>
            @foreach ($positions as $positionKey => $positionLabel)
                <option value="{{ $positionKey }}" @selected(old('position', $player->position ?? 'ZM') === $positionKey)>
                    {{ $positionLabel }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('position')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="age">Alter</label>
        <input class="sim-input" id="age" name="age" type="number" min="15" max="45"
            value="{{ old('age', $player->age ?? 22) }}" required>
        <x-input-error :messages="$errors->get('age')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="overall">Overall</label>
        <input class="sim-input" id="overall" name="overall" type="number" min="1" max="99"
            value="{{ old('overall', $player->overall ?? 60) }}" required>
        <x-input-error :messages="$errors->get('overall')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="pace">Tempo</label>
        <input class="sim-input" id="pace" name="pace" type="number" min="1" max="99"
            value="{{ old('pace', $player->pace ?? 60) }}" required>
        <x-input-error :messages="$errors->get('pace')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="shooting">Schuss</label>
        <input class="sim-input" id="shooting" name="shooting" type="number" min="1" max="99"
            value="{{ old('shooting', $player->shooting ?? 60) }}" required>
        <x-input-error :messages="$errors->get('shooting')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="passing">Pass</label>
        <input class="sim-input" id="passing" name="passing" type="number" min="1" max="99"
            value="{{ old('passing', $player->passing ?? 60) }}" required>
        <x-input-error :messages="$errors->get('passing')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="defending">Defensive</label>
        <input class="sim-input" id="defending" name="defending" type="number" min="1" max="99"
            value="{{ old('defending', $player->defending ?? 60) }}" required>
        <x-input-error :messages="$errors->get('defending')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="physical">Physis</label>
        <input class="sim-input" id="physical" name="physical" type="number" min="1" max="99"
            value="{{ old('physical', $player->physical ?? 60) }}" required>
        <x-input-error :messages="$errors->get('physical')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="stamina">Ausdauer</label>
        <input class="sim-input" id="stamina" name="stamina" type="number" min="1" max="100"
            value="{{ old('stamina', $player->stamina ?? 80) }}" required>
        <x-input-error :messages="$errors->get('stamina')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="morale">Moral</label>
        <input class="sim-input" id="morale" name="morale" type="number" min="1" max="100"
            value="{{ old('morale', $player->morale ?? 60) }}" required>
        <x-input-error :messages="$errors->get('morale')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="market_value">Marktwert</label>
        <input class="sim-input" id="market_value" name="market_value" type="number" min="0" step="0.01"
            value="{{ old('market_value', $player->market_value ?? 1000000) }}" required>
        <x-input-error :messages="$errors->get('market_value')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="salary">Gehalt</label>
        <input class="sim-input" id="salary" name="salary" type="number" min="0" step="0.01"
            value="{{ old('salary', $player->salary ?? 15000) }}" required>
        <x-input-error :messages="$errors->get('salary')" class="mt-1" />
    </div>
</div>