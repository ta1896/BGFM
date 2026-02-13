<section class="sim-card p-4 sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Dashboard</p>
            <h2 class="mt-1 text-2xl font-bold text-white">Klassische Uebersicht</h2>
        </div>
        <span class="inline-flex rounded-full border border-slate-600/80 bg-slate-900/50 px-2.5 py-1 text-xs text-slate-300">Layout: Klassisch</span>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
        <article class="sim-card-soft border-slate-700/70 bg-slate-900/45 p-4">
            <h3 class="text-lg font-semibold text-white">Vereinsdaten</h3>
            <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                <div class="rounded-lg border border-slate-700/70 bg-slate-950/35 px-3 py-2">
                    <dt class="text-xs uppercase tracking-[0.12em] text-slate-400">Verein</dt>
                    <dd class="mt-1 font-semibold text-white">{{ $activeClub->name }}</dd>
                </div>
                <div class="rounded-lg border border-slate-700/70 bg-slate-950/35 px-3 py-2">
                    <dt class="text-xs uppercase tracking-[0.12em] text-slate-400">Liga</dt>
                    <dd class="mt-1 font-semibold text-white">{{ $activeClub->league }}</dd>
                </div>
                <div class="rounded-lg border border-slate-700/70 bg-slate-950/35 px-3 py-2">
                    <dt class="text-xs uppercase tracking-[0.12em] text-slate-400">Rang</dt>
                    <dd class="mt-1 font-semibold text-white">{{ $clubRank ? '#'.$clubRank : '-' }}</dd>
                </div>
                <div class="rounded-lg border border-slate-700/70 bg-slate-950/35 px-3 py-2">
                    <dt class="text-xs uppercase tracking-[0.12em] text-slate-400">Punkte</dt>
                    <dd class="mt-1 font-semibold text-white">{{ $clubPoints ?? '-' }}</dd>
                </div>
                <div class="rounded-lg border border-slate-700/70 bg-slate-950/35 px-3 py-2">
                    <dt class="text-xs uppercase tracking-[0.12em] text-slate-400">Budget</dt>
                    <dd class="mt-1 font-semibold text-emerald-200">{{ number_format((float) $activeClub->budget, 2, ',', '.') }} EUR</dd>
                </div>
                <div class="rounded-lg border border-slate-700/70 bg-slate-950/35 px-3 py-2">
                    <dt class="text-xs uppercase tracking-[0.12em] text-slate-400">Fan-Mood</dt>
                    <dd class="mt-1 font-semibold text-white">{{ $fanMood }}/100</dd>
                </div>
            </dl>
        </article>

        <article class="sim-card-soft border-slate-700/70 bg-slate-900/45 p-4">
            <h3 class="text-lg font-semibold text-white">Naechstes Spiel</h3>
            @if ($nextMatch)
                <p class="mt-2 text-xl font-bold text-white">{{ $nextMatch->kickoff_at?->format('d.m.Y - H:i') }} Uhr</p>
                <p class="text-xs text-slate-400">{{ $nextMatchTypeLabel }}</p>
                <div class="mt-3 rounded-xl border border-slate-700/70 bg-slate-950/40 p-3">
                    <p class="text-sm font-semibold text-white">{{ $nextMatch->homeClub->name }} vs {{ $nextMatch->awayClub->name }}</p>
                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                        <span class="inline-flex rounded-full border px-2 py-1 {{ $homeReady ? 'border-emerald-400/35 bg-emerald-500/15 text-emerald-200' : 'border-rose-400/35 bg-rose-500/15 text-rose-200' }}">
                            {{ $homeReady ? 'Heim bereit' : 'Heim offen' }}
                        </span>
                        <span class="inline-flex rounded-full border px-2 py-1 {{ $awayReady ? 'border-emerald-400/35 bg-emerald-500/15 text-emerald-200' : 'border-rose-400/35 bg-rose-500/15 text-rose-200' }}">
                            {{ $awayReady ? 'Gast bereit' : 'Gast offen' }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('matches.show', $nextMatch) }}" class="sim-btn-muted mt-4">Matchcenter oeffnen</a>
            @else
                <p class="mt-3 text-sm text-slate-300">Kein Spiel geplant.</p>
                <a href="{{ route('league.matches', ['club' => $activeClub->id]) }}" class="sim-btn-muted mt-4">Zum Spielplan</a>
            @endif
        </article>
    </div>
</section>

<section class="sim-card p-4 sm:p-5">
    <h2 class="text-2xl font-bold text-white">Woche im Ueberblick</h2>
    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-700/70">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-900/70 text-slate-300">
                <tr>
                    <th class="px-3 py-2">Tag</th>
                    <th class="px-3 py-2">Datum</th>
                    <th class="px-3 py-2">Training</th>
                    <th class="px-3 py-2">Spiele</th>
                    <th class="px-3 py-2">Aktion</th>
                </tr>
            </thead>
            <tbody>
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
                    <tr class="{{ $day['is_today'] ? 'bg-fuchsia-500/10' : 'bg-slate-950/25' }}">
                        <td class="px-3 py-2 font-semibold text-white">{{ $day['label'] }}</td>
                        <td class="px-3 py-2 text-slate-300">{{ $day['date'] }}</td>
                        <td class="px-3 py-2 text-slate-300">{{ $day['training_count'] }}</td>
                        <td class="px-3 py-2 text-slate-300">{{ $day['match_count'] }}</td>
                        <td class="px-3 py-2">
                            <a href="{{ $dayUrl }}" class="sim-page-link">Oeffnen</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="grid gap-4 xl:grid-cols-2">
    <article class="sim-card p-4 sm:p-5">
        <h3 class="text-lg font-semibold text-white">Assistenz</h3>
        <p class="mt-1 text-xs text-slate-400">
            Training Gruppe A: {{ $trainingGroupACount }},
            Gruppe B: {{ $trainingGroupBCount }}
            ({{ $trainingPlanComplete ? 'vollstaendig' : 'offen' }})
        </p>

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
            <p class="mt-3 text-sm text-slate-300">Keine offenen Aufgaben.</p>
        @endif
    </article>

    <article class="sim-card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-white">Inbox</h3>
            <span class="text-xs text-slate-300">{{ $unreadNotificationsCount }} ungelesen</span>
        </div>

        @if ($notifications->isEmpty())
            <p class="mt-3 text-sm text-slate-300">Keine aktuellen Hinweise.</p>
        @else
            <div class="mt-3 space-y-2">
                @foreach ($notifications->take(5) as $notification)
                    <div class="rounded-lg border border-slate-700/70 bg-slate-900/40 px-3 py-2">
                        <p class="text-sm font-semibold text-white">{{ $notification->title }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $notification->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <a href="{{ route('notifications.index') }}" class="sim-btn-muted mt-4">Alle Hinweise</a>
    </article>
</section>
