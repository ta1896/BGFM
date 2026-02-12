<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Kader</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Neuen Spieler anlegen</h1>
        </div>
    </x-slot>

    @if ($clubs->isEmpty())
        <div class="sim-card p-8 text-center">
            <p class="text-slate-300">Du brauchst zuerst einen Verein.</p>
            <a href="{{ route('clubs.create') }}" class="sim-btn-primary mt-4">Verein erstellen</a>
        </div>
    @else
        <form method="POST" action="{{ route('players.store') }}" class="sim-card p-6">
            @include('players._form')
            <div class="mt-6 flex flex-wrap gap-2">
                <button type="submit" class="sim-btn-primary">Spieler speichern</button>
                <a href="{{ route('players.index') }}" class="sim-btn-muted">Abbrechen</a>
            </div>
        </form>
    @endif
</x-app-layout>
