<section class="sim-card p-4 sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Steuerpult</p>
            <h2 class="mt-1 text-2xl font-bold text-white">Kompakt-Ansicht</h2>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('league.matches', array_filter(['club' => $activeClub->id, 'competition_season' => $selectedCompetitionSeasonId])) }}" class="sim-btn-muted">Spielplan</a>
            <a href="{{ route('training.index', ['club' => $activeClub->id, 'range' => 'week']) }}" class="sim-btn-muted">Training</a>
            <a href="{{ route('finances.index', ['club' => $activeClub->id]) }}" class="sim-btn-muted">Finanzen</a>
        </div>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="sim-card-soft border-slate-700/70 bg-slate-900/45 p-3">
            <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Budget</p>
            <p class="mt-1 text-2xl font-bold text-emerald-200">{{ number_format((float) $activeClub->budget, 2, ',', '.') }} EUR</p>
            <p class="mt-1 text-xs text-slate-400">{{ number_format((int) ($activeClub->coins ?? 0), 0, ',', '.') }} Coins</p>
        </article>
        <article class="sim-card-soft border-slate-700/70 bg-slate-900/45 p-3">
            <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Vorstand</p>
            <p class="mt-1 text-2xl font-bold text-white">{{ (int) $activeClub->board_confidence }}/100</p>
            <p class="mt-1 text-xs text-slate-400">Vertrauen</p>
        </article>
        <article class="sim-card-soft border-slate-700/70 bg-slate-900/45 p-3">
            <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Fan-Mood</p>
            <p class="mt-1 text-2xl font-bold text-white">{{ $fanMood }}/100</p>
            <p class="mt-1 text-xs text-slate-400">{{ $fanMood >= 65 ? 'Beliebt' : ($fanMood >= 45 ? 'Stabil' : 'Kritisch') }}</p>
        </article>
        <article class="sim-card-soft border-slate-700/70 bg-slate-900/45 p-3">
            <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Inbox</p>
            <p class="mt-1 text-2xl font-bold text-white">{{ $unreadNotificationsCount }}</p>
            <p class="mt-1 text-xs text-slate-400">Ungelesene Hinweise</p>
        </article>
    </div>
</section>

<section class="sim-card p-4 sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-2xl font-bold text-white">Woche im Ueberblick</h2>
        <p class="text-xs text-slate-400">7 Tage, kompakt</p>
    </div>

    <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
        @foreach ($weekDays as $day)
            @php
                $dayUrl = $day['match_count'] > 0
                    ? route('league.matches', array_filter([
                        'competition_season' => $selectedCompetitionSeasonId,
                        'club' => $activeClub->id,
                        'day' => $day['iso_date'],
                    ]))
                    : route('training.index', [
                        'club' => $activeClub->id,
                        'date' => $day['iso_date'],
                    ]);
            @endphp
            <a href="{{ $dayUrl }}" class="rounded-xl border p-3 transition hover:border-cyan-300/60 {{ $day['is_today'] ? 'border-fuchsia-400/55 bg-fuchsia-500/15' : 'border-slate-700/70 bg-slate-900/45' }}">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">{{ $day['label'] }}</p>
                <p class="text-lg font-bold text-white">{{ $day['date'] }}</p>
                <p class="mt-1 text-xs text-slate-300">T {{ $day['training_count'] }} | M {{ $day['match_count'] }}</p>
            </a>
        @endforeach
    </div>
</section>

<section class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
    <article class="sim-card p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Naechstes Spiel</p>
                @if ($nextMatch)
                    <p class="mt-1 text-2xl font-bold text-white">{{ $nextMatch->kickoff_at?->format('d.m.Y - H:i') }} Uhr</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $nextMatchTypeLabel }}</p>
                @else
                    <p class="mt-2 text-sm text-slate-300">Kein Spiel geplant.</p>
                @endif
            </div>
            <a href="{{ $nextMatch ? route('matches.show', $nextMatch) : route('league.matches') }}" class="sim-btn-muted">
                {{ $nextMatch ? 'Matchcenter' : 'Spielplan' }}
            </a>
        </div>

        @if ($nextMatch)
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-700/70 bg-slate-900/45 p-3">
                    <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Heimteam</p>
                    <p class="mt-1 text-base font-semibold text-white">{{ $nextMatch->homeClub->name }}</p>
                    <p class="mt-1 text-xs {{ $homeReady ? 'text-emerald-300' : 'text-rose-300' }}">
                        {{ $homeReady ? 'Aufstellung bereit' : 'Aufstellung fehlt' }}
                    </p>
                </div>
                <div class="rounded-xl border border-slate-700/70 bg-slate-900/45 p-3">
                    <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Auswaertsteam</p>
                    <p class="mt-1 text-base font-semibold text-white">{{ $nextMatch->awayClub->name }}</p>
                    <p class="mt-1 text-xs {{ $awayReady ? 'text-emerald-300' : 'text-rose-300' }}">
                        {{ $awayReady ? 'Aufstellung bereit' : 'Aufstellung fehlt' }}
                    </p>
                </div>
            </div>
        @endif

        <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-lg border border-slate-700/70 bg-slate-900/35 px-3 py-2">
                <p class="text-xs text-slate-400">ANG</p>
                <p class="text-base font-semibold text-white">{{ $metrics['attack'] }}</p>
            </div>
            <div class="rounded-lg border border-slate-700/70 bg-slate-900/35 px-3 py-2">
                <p class="text-xs text-slate-400">MIT</p>
                <p class="text-base font-semibold text-white">{{ $metrics['midfield'] }}</p>
            </div>
            <div class="rounded-lg border border-slate-700/70 bg-slate-900/35 px-3 py-2">
                <p class="text-xs text-slate-400">DEF</p>
                <p class="text-base font-semibold text-white">{{ $metrics['defense'] }}</p>
            </div>
            <div class="rounded-lg border border-slate-700/70 bg-slate-900/35 px-3 py-2">
                <p class="text-xs text-slate-400">CHE</p>
                <p class="text-base font-semibold text-white">{{ $metrics['chemistry'] }}</p>
            </div>
            <div class="rounded-lg border border-cyan-400/40 bg-cyan-500/10 px-3 py-2">
                <p class="text-xs text-cyan-200">OVR</p>
                <p class="text-base font-semibold text-white">{{ $metrics['overall'] }}</p>
            </div>
        </div>
    </article>

    <article class="sim-card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">To-dos</p>
            <a href="{{ route('notifications.index') }}" class="sim-page-link">Inbox</a>
        </div>

        @if (!empty($assistantTasks))
            <div class="mt-3 space-y-2">
                @foreach ($assistantTasks as $task)
                    <div class="rounded-lg border px-3 py-2 {{ $task['kind'] === 'warning' ? 'border-fuchsia-400/35 bg-fuchsia-500/10' : 'border-cyan-400/30 bg-cyan-500/10' }}">
                        <p class="text-sm font-semibold text-white">{{ $task['label'] }}</p>
                        <p class="mt-0.5 text-xs text-slate-300">{{ $task['description'] }}</p>
                        <a href="{{ $task['url'] }}" class="sim-page-link mt-2">{{ $task['cta'] }}</a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-3 text-sm text-slate-300">Keine offenen Assistenten-Aufgaben.</p>
        @endif
    </article>
</section>
