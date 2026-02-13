<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title">Vereine</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Deine Vereine</h1>
            </div>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.clubs.create') }}" class="sim-btn-primary">Verein im ACP anlegen</a>
            @endif
        </div>
    </x-slot>

    @if ($clubs->isEmpty())
        <div class="sim-card p-8 text-center">
            <p class="text-slate-300">Noch kein Verein vorhanden.</p>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.clubs.create') }}" class="sim-btn-primary mt-4">Verein im ACP anlegen</a>
            @else
                <a href="{{ route('clubs.free') }}" class="sim-btn-primary mt-4">Freie Vereine anzeigen</a>
            @endif
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($clubs as $club)
                <article class="sim-card p-5">
                    <p class="sim-section-title">{{ $club->league }}</p>
                    <div class="mt-2 flex items-center gap-3">
                        <img class="sim-avatar sim-avatar-md" src="{{ $club->logo_url }}" alt="{{ $club->name }}">
                        <h2 class="text-xl font-bold text-white">{{ $club->name }}</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-300">{{ $club->country }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="sim-pill">{{ $club->players_count }} Spieler</span>
                        <span class="sim-pill">{{ $club->lineups_count }} Aufstellungen</span>
                    </div>
                    <div class="mt-5 flex gap-2">
                        <a href="{{ route('clubs.show', $club) }}" class="sim-btn-muted">Details</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.clubs.edit', $club) }}" class="sim-btn-muted">Im ACP bearbeiten</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
