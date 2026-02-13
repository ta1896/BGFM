@csrf

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="sim-label" for="country_id">Land</label>
        <select class="sim-select" id="country_id" name="country_id">
            <option value="">-</option>
            @foreach ($countries as $country)
                <option value="{{ $country->id }}" @selected(old('country_id', $competition?->country_id ?? '') == $country->id)>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('country_id')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="name">Name</label>
        <input class="sim-input" id="name" name="name" type="text" value="{{ old('name', $competition?->name ?? '') }}" required>
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="short_name">Kurzname</label>
        <input class="sim-input" id="short_name" name="short_name" type="text" value="{{ old('short_name', $competition?->short_name ?? '') }}">
        <x-input-error :messages="$errors->get('short_name')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="type">Typ</label>
        <select class="sim-select" id="type" name="type" required>
            <option value="league" @selected(old('type', $competition?->type ?? 'league') === 'league')>Liga</option>
            <option value="cup" @selected(old('type', $competition?->type ?? '') === 'cup')>Pokal</option>
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="scope">Wettbewerbsebene</label>
        <select class="sim-select" id="scope" name="scope">
            <option value="">Automatisch (nach Land)</option>
            <option value="national" @selected(old('scope', $competition?->scope ?? '') === 'national')>National</option>
            <option value="international" @selected(old('scope', $competition?->scope ?? '') === 'international')>International</option>
        </select>
        <x-input-error :messages="$errors->get('scope')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="tier">Stufe</label>
        <input class="sim-input" id="tier" name="tier" type="number" min="1" max="10" value="{{ old('tier', $competition?->tier ?? '') }}">
        <x-input-error :messages="$errors->get('tier')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="logo">Logo</label>
        <input class="sim-input" id="logo" name="logo" type="file" accept="image/*">
        <x-input-error :messages="$errors->get('logo')" class="mt-1" />
        @if (!empty($competition?->logo_path))
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                <img class="sim-avatar sim-avatar-md"
                     src="{{ $competition->logo_url }}"
                     alt="Logo">
                <span>Aktuelles Logo</span>
            </div>
        @endif
    </div>
</div>

<div class="mt-4">
    <label class="inline-flex items-center gap-2 rounded-xl border border-slate-700/70 px-3 py-2 text-sm text-slate-200">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $competition?->is_active ?? true))>
        <span>Aktiv</span>
    </label>
    <x-input-error :messages="$errors->get('is_active')" class="mt-1" />
</div>
