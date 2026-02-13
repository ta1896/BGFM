<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Aufstellung</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Neue Aufstellung</h1>
        </div>
    </x-slot>

    @if ($clubs->isEmpty())
        <div class="sim-card p-8 text-center">
            <p class="text-slate-300">Lege zuerst einen Verein an.</p>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.clubs.create') }}" class="sim-btn-primary mt-4">Verein im ACP erstellen</a>
            @else
                <a href="{{ route('clubs.free') }}" class="sim-btn-primary mt-4">Freie Vereine anzeigen</a>
            @endif
        </div>
    @else
        <form method="POST" action="{{ route('lineups.store') }}" class="sim-card p-6">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="sim-label" for="club_id">Verein</label>
                    <select class="sim-select" id="club_id" name="club_id" required>
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}" @selected(old('club_id') == $club->id)>{{ $club->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('club_id')" class="mt-1" />
                </div>
                <div>
                    <label class="sim-label" for="formation">Formation</label>
                    <input class="sim-input" id="formation" name="formation" type="text" value="{{ old('formation', '4-3-3') }}" required>
                    <x-input-error :messages="$errors->get('formation')" class="mt-1" />
                </div>
            </div>
            <div class="mt-4">
                <label class="sim-label" for="name">Name</label>
                <input class="sim-input" id="name" name="name" type="text" value="{{ old('name') }}" required>
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>
            <div class="mt-4">
                <label class="sim-label" for="notes">Notizen</label>
                <textarea class="sim-textarea" id="notes" name="notes">{{ old('notes') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
            </div>
            <label class="mt-4 inline-flex items-center gap-2 text-sm text-slate-300">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-600 bg-slate-900 text-cyan-400 focus:ring-cyan-400" @checked(old('is_active', true))>
                Als aktive Aufstellung setzen
            </label>

            <div class="mt-6 flex flex-wrap gap-2">
                <button type="submit" class="sim-btn-primary">Aufstellung anlegen</button>
                <a href="{{ route('lineups.index') }}" class="sim-btn-muted">Abbrechen</a>
            </div>
        </form>
    @endif
</x-app-layout>
