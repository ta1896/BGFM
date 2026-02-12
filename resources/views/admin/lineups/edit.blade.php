@php
    $selectedIds = collect(old('selected_players', $lineup->players->pluck('id')->all()))
        ->map(static fn ($value) => (int) $value)
        ->all();

    $pitchOptions = ['GK', 'LB', 'LCB', 'CB', 'RCB', 'RB', 'LM', 'CM', 'RM', 'CAM', 'LW', 'RW', 'ST'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="sim-section-title">ACP Aufstellungen</p>
                <h1 class="mt-1 text-2xl font-bold text-white">{{ $lineup->name }} bearbeiten</h1>
                <p class="mt-1 text-sm text-slate-300">{{ $lineup->club->name }} | Owner: {{ $lineup->club->user->name }}</p>
            </div>
            <a class="sim-btn-muted" href="{{ route('admin.lineups.show', $lineup) }}">Zur Detailseite</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.lineups.update', $lineup) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <section class="sim-card p-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="sim-label" for="name">Name</label>
                    <input class="sim-input" id="name" name="name" type="text" value="{{ old('name', $lineup->name) }}" required>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <label class="sim-label" for="formation">Formation</label>
                    <input class="sim-input" id="formation" name="formation" type="text" value="{{ old('formation', $lineup->formation) }}" required>
                    <x-input-error :messages="$errors->get('formation')" class="mt-1" />
                </div>
            </div>
            <div class="mt-4">
                <label class="sim-label" for="notes">Notizen</label>
                <textarea class="sim-textarea" id="notes" name="notes">{{ old('notes', $lineup->notes) }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
            </div>
            <label class="mt-4 inline-flex items-center gap-2 text-sm text-slate-300">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-600 bg-slate-900 text-cyan-400 focus:ring-cyan-400" @checked(old('is_active', $lineup->is_active))>
                Als aktive Aufstellung setzen
            </label>
        </section>

        <section class="sim-card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-white">Spielerzuweisung (max. 11)</h2>
                <p class="text-sm text-slate-300">{{ count($selectedIds) }} ausgewaehlt</p>
            </div>
            <x-input-error :messages="$errors->get('selected_players')" class="mb-2" />
            <div class="grid gap-3 lg:grid-cols-2">
                @foreach ($players as $player)
                    @php
                        $isSelected = in_array($player->id, $selectedIds, true);
                        $pitchValue = old('pitch_positions.'.$player->id, $lineup->players->firstWhere('id', $player->id)?->pivot->pitch_position);
                    @endphp
                    <div class="sim-card-soft p-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label class="inline-flex items-center gap-2 text-sm text-white">
                                <input
                                    type="checkbox"
                                    name="selected_players[]"
                                    value="{{ $player->id }}"
                                    class="rounded border-slate-600 bg-slate-900 text-cyan-400 focus:ring-cyan-400"
                                    @checked($isSelected)
                                >
                                {{ $player->full_name }} ({{ $player->position }})
                            </label>
                            <span class="sim-pill">OVR {{ $player->overall }}</span>
                        </div>
                        <div class="mt-3">
                            <label class="sim-label" for="pitch_{{ $player->id }}">Feldposition</label>
                            <select id="pitch_{{ $player->id }}" name="pitch_positions[{{ $player->id }}]" class="sim-select">
                                <option value="">Automatisch</option>
                                @foreach ($pitchOptions as $pitchOption)
                                    <option value="{{ $pitchOption }}" @selected($pitchValue === $pitchOption)>{{ $pitchOption }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex flex-wrap gap-2">
            <button class="sim-btn-primary" type="submit">Speichern</button>
            <a href="{{ route('admin.lineups.index') }}" class="sim-btn-muted">Zurueck</a>
        </div>
    </form>
</x-app-layout>
