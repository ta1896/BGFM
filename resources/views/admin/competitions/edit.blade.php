<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title">ACP Ligen & Pokale</p>
                <h1 class="mt-1 text-2xl font-bold text-white">{{ $competition->name }} bearbeiten</h1>
            </div>
            <a href="{{ route('admin.competitions.index') }}" class="sim-btn-muted">Liste</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.competitions.update', $competition) }}" class="sim-card p-6"
        enctype="multipart/form-data">
        @method('PUT')
        @include('admin.competitions._form')

        <div class="mt-6 flex flex-wrap gap-2">
            <button class="sim-btn-primary" type="submit">Speichern</button>
            <a class="sim-btn-muted" href="{{ route('admin.competitions.index') }}">Zurueck</a>
        </div>
    </form>

    <div class="sim-card mt-6 p-6">
        <h2 class="text-xl font-bold text-white mb-4">Saisons dieser Liga/Pokal</h2>

        <div class="space-y-4">
            @foreach($competition->competitionSeasons as $compSeason)
                <div class="bg-slate-800/40 p-4 rounded-lg border border-slate-700/50 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-cyan-400 text-lg">{{ $compSeason->season->name }}</p>
                        <p class="text-xs text-slate-400 uppercase tracking-tighter">Format: {{ $compSeason->format }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.competition-seasons.edit', $compSeason) }}"
                            class="sim-btn-primary text-xs py-1">Bearbeiten</a>
                        <form action="{{ route('admin.competition-seasons.generate-fixtures', $compSeason) }}"
                            method="POST">
                            @csrf
                            <button class="sim-btn-muted text-xs py-1">Spielplan gen. (DEBUG)</button>
                        </form>
                    </div>
                </div>
            @endforeach

            <form action="{{ route('admin.competitions.add-season', $competition) }}" method="POST"
                class="mt-4 p-4 border-2 border-dashed border-slate-800 rounded-lg bg-slate-900/50">
                @csrf
                <p class="text-sm font-bold text-white mb-3">Neue Saison zuordnen</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <select name="season_id" class="sim-select text-sm py-1" required>
                        <option value="">-- Saison wählen --</option>
                        @foreach($availableSeasons as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->start_date->format('Y') }})</option>
                        @endforeach
                    </select>
                    <input type="text" name="format" class="sim-input text-sm py-1"
                        placeholder="Format (z.B. league_18)" required>
                </div>
                <button type="submit" class="sim-btn-primary w-full mt-3 text-sm py-2">Saison hinzufügen</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.competitions.destroy', $competition) }}"
        class="sim-card mt-4 border-rose-400/30 bg-rose-500/10 p-5">
        @csrf
        @method('DELETE')
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-rose-100/90">Liga/Pokal loeschen.</p>
            <button type="submit" class="sim-btn-danger"
                onclick="return confirm('Liga/Pokal wirklich loeschen?')">Loeschen</button>
        </div>
    </form>
</x-app-layout>