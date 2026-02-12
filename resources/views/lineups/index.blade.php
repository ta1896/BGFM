<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="sim-section-title">Aufstellung</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Aufstellungen verwalten</h1>
            </div>
            <a href="{{ route('lineups.create') }}" class="sim-btn-primary">Neue Aufstellung</a>
        </div>
    </x-slot>

    @if ($lineups->isEmpty())
        <div class="sim-card p-8 text-center">
            <p class="text-slate-300">Keine Aufstellungen vorhanden.</p>
            @if ($clubs->isNotEmpty())
                <a href="{{ route('lineups.create') }}" class="sim-btn-primary mt-4">Erstellen</a>
            @else
                <a href="{{ route('clubs.create') }}" class="sim-btn-primary mt-4">Erst Verein erstellen</a>
            @endif
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($lineups as $lineup)
                <article class="sim-card p-5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="sim-section-title">{{ $lineup->club->name }}</p>
                            <h2 class="mt-1 text-xl font-bold text-white">{{ $lineup->name }}</h2>
                            <p class="mt-1 text-sm text-slate-300">{{ $lineup->formation }} | {{ $lineup->players->count() }} Spieler</p>
                        </div>
                        @if ($lineup->is_active)
                            <span class="sim-pill">Aktiv</span>
                        @endif
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <a class="sim-btn-muted" href="{{ route('lineups.show', $lineup) }}">Details</a>
                        <a class="sim-btn-muted" href="{{ route('lineups.edit', $lineup) }}">Bearbeiten</a>
                        @unless ($lineup->is_active)
                            <form method="POST" action="{{ route('lineups.activate', $lineup) }}">
                                @csrf
                                <button type="submit" class="sim-btn-muted">Aktivieren</button>
                            </form>
                        @endunless
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
