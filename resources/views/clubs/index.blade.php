<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title">Vereine</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Deine Vereine</h1>
            </div>
            <a href="{{ route('clubs.create') }}" class="sim-btn-primary">Neuer Verein</a>
        </div>
    </x-slot>

    @if ($clubs->isEmpty())
        <div class="sim-card p-8 text-center">
            <p class="text-slate-300">Noch kein Verein vorhanden.</p>
            <a href="{{ route('clubs.create') }}" class="sim-btn-primary mt-4">Ersten Verein erstellen</a>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($clubs as $club)
                <article class="sim-card p-5">
                    <p class="sim-section-title">{{ $club->league }}</p>
                    <h2 class="mt-2 text-xl font-bold text-white">{{ $club->name }}</h2>
                    <p class="mt-1 text-sm text-slate-300">{{ $club->country }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="sim-pill">{{ $club->players_count }} Spieler</span>
                        <span class="sim-pill">{{ $club->lineups_count }} Aufstellungen</span>
                    </div>
                    <div class="mt-5 flex gap-2">
                        <a href="{{ route('clubs.show', $club) }}" class="sim-btn-muted">Details</a>
                        <a href="{{ route('clubs.edit', $club) }}" class="sim-btn-muted">Bearbeiten</a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
