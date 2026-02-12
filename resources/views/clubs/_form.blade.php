@csrf

<div class="grid gap-4 md:grid-cols-2">
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
        <label class="sim-label" for="reputation">Reputation (1-99)</label>
        <input class="sim-input" id="reputation" name="reputation" type="number" min="1" max="99" value="{{ old('reputation', $club->reputation ?? 50) }}" required>
        <x-input-error :messages="$errors->get('reputation')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="fan_mood">Fan-Stimmung (1-100)</label>
        <input class="sim-input" id="fan_mood" name="fan_mood" type="number" min="1" max="100" value="{{ old('fan_mood', $club->fan_mood ?? 50) }}" required>
        <x-input-error :messages="$errors->get('fan_mood')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="budget">Transferbudget (EUR)</label>
        <input class="sim-input" id="budget" name="budget" type="number" min="0" step="0.01" value="{{ old('budget', $club->budget ?? 500000) }}" required>
        <x-input-error :messages="$errors->get('budget')" class="mt-1" />
    </div>
    <div>
        <label class="sim-label" for="wage_budget">Gehaltsbudget (EUR)</label>
        <input class="sim-input" id="wage_budget" name="wage_budget" type="number" min="0" step="0.01" value="{{ old('wage_budget', $club->wage_budget ?? 250000) }}" required>
        <x-input-error :messages="$errors->get('wage_budget')" class="mt-1" />
    </div>
</div>

<div class="mt-4">
    <label class="sim-label" for="notes">Notizen</label>
    <textarea class="sim-textarea" id="notes" name="notes">{{ old('notes', $club->notes ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('notes')" class="mt-1" />
</div>
