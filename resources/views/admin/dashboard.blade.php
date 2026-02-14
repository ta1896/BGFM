<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Admin Control Panel</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Systemverwaltung</h1>
            <p class="mt-2 text-sm text-slate-300">Adminbereich fuer globale Datenpflege.</p>
        </div>
    </x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-10">
        <article class="sim-card p-5">
            <p class="sim-section-title">User</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['users'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Admins</p>
            <p class="mt-2 text-3xl font-bold text-cyan-300">{{ $stats['admins'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Vereine</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['clubs'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">CPU-Teams</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['cpu_clubs'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Spieler</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['players'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Aufstellungen</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['lineups'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Offene Spiele</p>
            <p class="mt-2 text-3xl font-bold text-cyan-300">{{ $stats['scheduled_matches'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Sponsorvertraege</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['active_sponsors'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Stadionprojekte</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['active_stadium_projects'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Trainingslager</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['active_training_camps'] }}</p>
        </article>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <article class="sim-card p-5">
            <div class="mb-4 flex items-center justify-between">
                <p class="sim-section-title">Schnellaktionen</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a class="sim-btn-muted" href="{{ route('admin.competitions.create') }}">Liga/Pokal erstellen</a>
                <a class="sim-btn-muted" href="{{ route('admin.competitions.index') }}">Ligen/Pokale</a>
                <a class="sim-btn-primary" href="{{ route('admin.clubs.create') }}">Verein erstellen</a>
                <a class="sim-btn-muted" href="{{ route('admin.players.create') }}">Spieler erstellen</a>
                <a class="sim-btn-muted" href="{{ route('admin.lineups.create') }}">Aufstellung erstellen</a>
                <a class="sim-btn-muted" href="{{ route('sponsors.index') }}">Sponsoren</a>
                <a class="sim-btn-muted" href="{{ route('stadium.index') }}">Stadion</a>
                <a class="sim-btn-muted" href="{{ route('training-camps.index') }}">Trainingslager</a>
                <a class="sim-btn-primary" href="{{ route('admin.match-engine.index') }}">Match Engine</a>
            </div>

            <form method="POST" action="{{ route('admin.simulation.process-matchday') }}" class="mt-4 grid gap-2 sm:grid-cols-3">
                @csrf
                <select name="competition_season_id" class="sim-select sm:col-span-2">
                    <option value="">Alle aktiven Ligen</option>
                    @foreach ($activeCompetitionSeasons as $competitionSeason)
                    <option value="{{ $competitionSeason->id }}">
                            {{ $competitionSeason->competition?->name ?? 'Unbekannter Wettbewerb' }} - {{ $competitionSeason->season?->name ?? 'Unbekannte Saison' }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="sim-btn-primary sm:col-span-1">Spieltag laufen lassen</button>
            </form>
        </article>

        <article class="sim-card p-5">
            <div class="mb-4 flex items-center justify-between">
                <p class="sim-section-title">Simulationseinstellungen</p>
            </div>
            
            <p class="text-sm text-slate-300 mb-4">
                Die allgemeinen Simulationseinstellungen (Scheduler, Observer, etc.) wurden auf eine eigene Seite verschoben.
            </p>

            <a href="{{ route('admin.simulation.settings.index') }}" class="sim-btn-primary w-full text-center block">
                Einstellungen verwalten
            </a>
        </article>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <article class="sim-card p-5">
            <div class="mb-3 flex items-center justify-between">
                <p class="sim-section-title">Letzte User</p>
            </div>
            <div class="space-y-2">
                @foreach ($latestUsers as $user)
                    <div class="sim-card-soft flex items-center justify-between px-3 py-2">
                        <div>
                            <p class="text-sm font-semibold text-white">{{ $user->name }}</p>
                            <p class="text-xs text-slate-400">{{ $user->email }}</p>
                        </div>
                        @if ($user->is_admin)
                            <span class="sim-pill">Admin</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </article>

        <article class="sim-card p-5">
            <div class="mb-3 flex items-center justify-between">
                <p class="sim-section-title">Letzte Vereine</p>
            </div>
            <div class="space-y-2">
                @foreach ($latestClubs as $club)
                    <div class="sim-card-soft flex items-center justify-between px-3 py-2">
                        <div>
                            <p class="text-sm font-semibold text-white">{{ $club->name }}</p>
                            <p class="text-xs text-slate-400">Owner: {{ $club->user?->name ?? 'Kein Owner' }}</p>
                        </div>
                        <a href="{{ route('admin.clubs.edit', $club) }}" class="text-sm text-cyan-300 hover:text-cyan-200">Bearbeiten</a>
                    </div>
                @endforeach
            </div>
        </article>
    </section>
</x-app-layout>
