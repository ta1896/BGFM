@csrf
@php
    $rolePlayers = isset($club) && $club->exists
        ? $club->players()->orderByDesc('overall')->limit(40)->get()
        : collect();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="sim-label" for="user_id">Owner User</label>
        <select class="sim-select" id="user_id" name="user_id" required>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $club->user_id ?? '') == $user->id)>
                    {{ $user->name }} ({{ $user->email }}) @if($user->is_admin)[ADMIN]@endif
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('user_id')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="name">Vereinsname</label>
        <input class="sim-input" id="name" name="name" type="text" value="{{ old('name', $club->name ?? '') }}" required>
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="short_name">Kurzname</label>
        <input class="sim-input" id="short_name" name="short_name" type="text" value="{{ old('short_name', $club->short_name ?? '') }}">
        <x-input-error :messages="$errors->get('short_name')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="logo">Vereinslogo</label>
        <input class="sim-input" id="logo" name="logo" type="file" accept="image/*">
        <x-input-error :messages="$errors->get('logo')" class="mt-1" />
        @if (!empty($club?->logo_path))
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                <img class="sim-avatar sim-avatar-md"
                     src="{{ $club->logo_url }}"
                     alt="Club Logo">
                <span>Aktuelles Logo</span>
            </div>
        @endif
    </div>
    <div>
        <label class="sim-label" for="country">Land</label>
        <input class="sim-input" id="country" name="country" type="text" value="{{ old('country', $club->country ?? 'Deutschland') }}" required>
        <x-input-error :messages="$errors->get('country')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="league">Liga</label>
        <input class="sim-input" id="league" name="league" type="text" value="{{ old('league', $club->league ?? 'Amateurliga') }}" required>
        <x-input-error :messages="$errors->get('league')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="founded_year">Gruendungsjahr</label>
        <input class="sim-input" id="founded_year" name="founded_year" type="number" value="{{ old('founded_year', $club->founded_year ?? '') }}">
        <x-input-error :messages="$errors->get('founded_year')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="reputation">Reputation</label>
        <input class="sim-input" id="reputation" name="reputation" type="number" min="1" max="99" value="{{ old('reputation', $club->reputation ?? 50) }}" required>
        <x-input-error :messages="$errors->get('reputation')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="fan_mood">Fan Stimmung</label>
        <input class="sim-input" id="fan_mood" name="fan_mood" type="number" min="1" max="100" value="{{ old('fan_mood', $club->fan_mood ?? 50) }}" required>
        <x-input-error :messages="$errors->get('fan_mood')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="season_objective">Saisonziel</label>
        <select class="sim-select" id="season_objective" name="season_objective">
            <option value="avoid_relegation" @selected(old('season_objective', $club->season_objective ?? 'mid_table') === 'avoid_relegation')>Klassenerhalt</option>
            <option value="mid_table" @selected(old('season_objective', $club->season_objective ?? 'mid_table') === 'mid_table')>Mittelfeld</option>
            <option value="promotion" @selected(old('season_objective', $club->season_objective ?? 'mid_table') === 'promotion')>Aufstieg</option>
            <option value="title" @selected(old('season_objective', $club->season_objective ?? 'mid_table') === 'title')>Meisterschaft</option>
            <option value="cup_run" @selected(old('season_objective', $club->season_objective ?? 'mid_table') === 'cup_run')>Pokalrunde</option>
        </select>
        <x-input-error :messages="$errors->get('season_objective')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="budget">Budget</label>
        <input class="sim-input" id="budget" name="budget" type="number" min="0" step="0.01" value="{{ old('budget', $club->budget ?? 500000) }}" required>
        <x-input-error :messages="$errors->get('budget')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="coins">Coins</label>
        <input class="sim-input" id="coins" name="coins" type="number" min="0" step="1" value="{{ old('coins', $club->coins ?? 0) }}">
        <x-input-error :messages="$errors->get('coins')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="wage_budget">Gehaltsbudget</label>
        <input class="sim-input" id="wage_budget" name="wage_budget" type="number" min="0" step="0.01" value="{{ old('wage_budget', $club->wage_budget ?? 250000) }}" required>
        <x-input-error :messages="$errors->get('wage_budget')" class="mt-1" />
    </div>
</div>

<div class="mt-4">
    <label class="inline-flex items-center gap-2 rounded-xl border border-slate-700/70 px-3 py-2 text-sm text-slate-200">
        <input type="checkbox" name="is_cpu" value="1" @checked(old('is_cpu', $club->is_cpu ?? false))>
        <span>CPU-Team (wird automatisch vom Spielsystem gesteuert)</span>
    </label>
    <x-input-error :messages="$errors->get('is_cpu')" class="mt-1" />
</div>

@if ($rolePlayers->isNotEmpty())
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label class="sim-label" for="captain_player_id">Vereinskapitaen</label>
            <select class="sim-select" id="captain_player_id" name="captain_player_id">
                <option value="">-- Kein Spieler --</option>
                @foreach ($rolePlayers as $player)
                    <option value="{{ $player->id }}" @selected((string) old('captain_player_id', $club->captain_player_id ?? '') === (string) $player->id)>
                        {{ $player->full_name }} ({{ $player->position_main ?: $player->position }} | OVR {{ $player->overall }})
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('captain_player_id')" class="mt-1" />
        </div>
        <div>
            <label class="sim-label" for="vice_captain_player_id">Vizekapitaen</label>
            <select class="sim-select" id="vice_captain_player_id" name="vice_captain_player_id">
                <option value="">-- Kein Spieler --</option>
                @foreach ($rolePlayers as $player)
                    <option value="{{ $player->id }}" @selected((string) old('vice_captain_player_id', $club->vice_captain_player_id ?? '') === (string) $player->id)>
                        {{ $player->full_name }} ({{ $player->position_main ?: $player->position }} | OVR {{ $player->overall }})
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('vice_captain_player_id')" class="mt-1" />
        </div>
    </div>
@endif

<div class="mt-4">
    <label class="sim-label" for="notes">Notizen</label>
    <textarea class="sim-textarea" id="notes" name="notes">{{ old('notes', $club->notes ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('notes')" class="mt-1" />
</div>
