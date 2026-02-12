<x-app-layout>
    <x-slot name="header">
        <div class="sim-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="sim-section-title">Uebersicht</p>
                    <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">
                        {{ $activeClub?->name ?? 'Noch kein Verein' }}
                    </h1>
                    <p class="mt-2 text-sm text-slate-300">
                        Verein, Kader, Aufstellung und Teamleistung im Blick.
                    </p>
                </div>
                @if ($clubs->isNotEmpty())
                    <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <label for="club" class="sim-label mb-0">Verein</label>
                        <select id="club" name="club" class="sim-select w-52" onchange="this.form.submit()">
                            @foreach ($clubs as $club)
                                <option value="{{ $club->id }}" @selected($activeClub && $activeClub->id === $club->id)>
                                    {{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    @if (!$activeClub)
        <section class="sim-card p-8 text-center">
            <h2 class="text-2xl font-bold text-white">Starte mit deinem ersten Verein</h2>
            <p class="mt-2 text-slate-300">Lege einen Verein an, damit Spieler und Aufstellung verfuegbar sind.</p>
            <a href="{{ route('clubs.create') }}" class="sim-btn-primary mt-6">Verein erstellen</a>
        </section>
    @else
        <section class="grid gap-4 lg:grid-cols-3">
            <article class="sim-card p-5 lg:col-span-2">
                <p class="sim-section-title">Verein</p>
                <h2 class="mt-3 text-2xl font-bold text-white">{{ $activeClub->name }}</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="sim-pill">{{ $activeClub->league }}</span>
                    <span class="sim-pill">{{ $activeClub->country }}</span>
                    <span class="sim-pill">Budget {{ number_format((float) $activeClub->budget, 2, ',', '.') }} EUR</span>
                </div>
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Aufstellung</p>
                <h3 class="mt-3 text-xl font-bold text-white">{{ $activeLineup?->name ?? 'Keine Aufstellung' }}</h3>
                <p class="mt-2 text-sm text-slate-300">
                    {{ $activeLineup ? 'Formation '.$activeLineup->formation : 'Bitte Aufstellung erstellen und Spieler zuordnen.' }}
                </p>
                <a href="{{ route('lineups.index') }}" class="sim-btn-muted mt-5">Aufstellungen verwalten</a>
            </article>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="sim-card p-5">
                <p class="sim-section-title">Gesamt</p>
                <p class="mt-2 text-3xl font-bold text-cyan-300">{{ $metrics['overall'] }}</p>
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Angriff</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $metrics['attack'] }}</p>
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Mittelfeld</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $metrics['midfield'] }}</p>
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Verteidigung</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $metrics['defense'] }}</p>
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Chemie</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $metrics['chemistry'] }}%</p>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <article class="sim-card p-5">
                <p class="sim-section-title">Sponsor</p>
                @if ($activeSponsorContract)
                    <p class="mt-2 text-xl font-bold text-white">{{ $activeSponsorContract->sponsor->name }}</p>
                    <p class="mt-1 text-sm text-slate-300">
                        {{ number_format((float) $activeSponsorContract->weekly_amount, 2, ',', '.') }} EUR / Woche
                    </p>
                @else
                    <p class="mt-2 text-sm text-slate-300">Kein Sponsorvertrag aktiv.</p>
                @endif
                <a href="{{ route('sponsors.index', ['club' => $activeClub->id]) }}" class="sim-btn-muted mt-4">Sponsoren verwalten</a>
            </article>

            <article class="sim-card p-5">
                <p class="sim-section-title">Stadion</p>
                @if ($stadium)
                    <p class="mt-2 text-xl font-bold text-white">{{ $stadium->name }}</p>
                    <p class="mt-1 text-sm text-slate-300">
                        {{ number_format($stadium->capacity, 0, ',', '.') }} Plaetze | Ticket {{ number_format((float) $stadium->ticket_price, 2, ',', '.') }} EUR
                    </p>
                @else
                    <p class="mt-2 text-sm text-slate-300">Noch kein Stadionprofil.</p>
                @endif
                <a href="{{ route('stadium.index', ['club' => $activeClub->id]) }}" class="sim-btn-muted mt-4">Stadion oeffnen</a>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            <article class="sim-card p-5">
                <p class="sim-section-title">Kader</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $activeClub->players_count }}</p>
                <p class="mt-2 text-sm text-slate-300">Spieler im Verein</p>
                <a class="sim-btn-muted mt-4" href="{{ route('players.index', ['club' => $activeClub->id]) }}">Spieler ansehen</a>
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Aufstellungen</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $activeClub->lineups_count }}</p>
                <p class="mt-2 text-sm text-slate-300">Gespeicherte Formationen</p>
                <a class="sim-btn-muted mt-4" href="{{ route('lineups.index') }}">Zur Aufstellung</a>
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Naechster Schritt</p>
                <p class="mt-2 text-sm text-slate-300">
                    Pflege Spielerdaten und aktiviere eine Aufstellung mit bis zu 11 Spielern.
                </p>
                <a class="sim-btn-primary mt-4" href="{{ route('players.create') }}">Neuen Spieler anlegen</a>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            <article class="sim-card p-5 xl:col-span-2">
                <p class="sim-section-title">Naechstes Spiel</p>
                @if ($nextMatch)
                    <h3 class="mt-3 text-xl font-bold text-white">
                        {{ $nextMatch->homeClub->name }} vs {{ $nextMatch->awayClub->name }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-300">
                        {{ $nextMatch->kickoff_at?->format('d.m.Y H:i') }} Uhr
                    </p>
                    <a href="{{ route('matches.show', $nextMatch) }}" class="sim-btn-muted mt-4">Zum Matchcenter</a>
                @else
                    <p class="mt-3 text-sm text-slate-300">Noch kein anstehendes Spiel.</p>
                    <a href="{{ route('league.matches') }}" class="sim-btn-muted mt-4">Spielplan anzeigen</a>
                @endif
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Inbox</p>
                @if ($notifications->isEmpty())
                    <p class="mt-3 text-sm text-slate-300">Keine neuen Hinweise.</p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach ($notifications as $notification)
                            <div class="sim-card-soft px-3 py-2">
                                <p class="text-sm font-semibold text-white">{{ $notification->title }}</p>
                                <p class="mt-1 text-xs text-slate-300">{{ $notification->created_at->format('d.m.Y H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
                <a href="{{ route('notifications.index') }}" class="sim-btn-muted mt-4">Alle ansehen</a>
            </article>
        </section>
    @endif
</x-app-layout>
