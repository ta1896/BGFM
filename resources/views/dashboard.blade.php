<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Dashboard</p>
                <h1 class="mt-1 text-3xl font-bold leading-tight text-white sm:text-4xl">
                    Hallo,
                    <span class="bg-gradient-to-r from-cyan-300 via-fuchsia-300 to-rose-300 bg-clip-text text-transparent">
                        {{ auth()->user()->name }}
                    </span>
                </h1>
                <p class="mt-1 text-sm text-slate-300">Uebersicht ueber Verein, Spiele, Training und aktuelle Hinweise.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($activeClub)
                    <a href="{{ route('league.matches', array_filter(['club' => $activeClub->id, 'scope' => 'today', 'competition_season' => $selectedCompetitionSeasonId])) }}" class="sim-btn-muted">Spiele heute ({{ $todayMatchesCount }})</a>
                    <a href="{{ route('players.index', ['club' => $activeClub->id]) }}" class="sim-btn-muted">Kader</a>
                    <a href="{{ route('training.index', ['club' => $activeClub->id, 'range' => 'week']) }}" class="sim-btn-muted">Training</a>
                @else
                    <a href="{{ route('clubs.free') }}" class="sim-btn-primary">Freie Vereine</a>
                @endif

                <form method="GET" action="{{ route('dashboard') }}" class="ml-1 flex items-center gap-2">
                    @if ($activeClub)
                        <input type="hidden" name="club" value="{{ $activeClub->id }}">
                    @endif
                    <label for="variant" class="sim-label mb-0 hidden sm:block">Layout</label>
                    <select id="variant" name="variant" class="sim-select w-40" onchange="this.form.submit()">
                        @foreach ($dashboardVariants as $variantKey => $variantLabel)
                            <option value="{{ $variantKey }}" @selected($dashboardVariant === $variantKey)>
                                {{ $variantLabel }}
                            </option>
                        @endforeach
                    </select>
                </form>

                @if ($clubs->isNotEmpty())
                    <form method="GET" action="{{ route('dashboard') }}" class="ml-1 flex items-center gap-2">
                        @if (!empty($dashboardVariant))
                            <input type="hidden" name="variant" value="{{ $dashboardVariant }}">
                        @endif
                        <label for="club" class="sim-label mb-0 hidden sm:block">Verein</label>
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
        @if (auth()->user()->isAdmin())
            <section class="sim-card p-8 text-center">
                <h2 class="text-2xl font-bold text-white">Noch kein eigener Verein verknuepft</h2>
                <p class="mt-2 text-slate-300">Als Administrator steuerst du Datenverwaltung und Simulation im ACP.</p>
                <a href="{{ route('admin.dashboard') }}" class="sim-btn-primary mt-6">Zum ACP</a>
            </section>
        @else
            <section class="grid gap-4 lg:grid-cols-2">
                <article class="sim-card p-6">
                    <p class="sim-section-title">Managerstart</p>
                    <h2 class="mt-2 text-2xl font-bold text-white">Du hast noch keinen Verein</h2>
                    <p class="mt-3 text-sm text-slate-300">
                        Solange dir kein Verein zugewiesen ist, siehst du nur dieses Start-Dashboard und den Punkt "Freie Vereine".
                    </p>
                    <a href="{{ route('clubs.free') }}" class="sim-btn-primary mt-5">Freie Vereine ansehen</a>
                </article>

                <article class="sim-card p-6">
                    <p class="sim-section-title">Was danach frei wird</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-300">
                        <li>- Matchcenter und Spieltage</li>
                        <li>- Aufstellungen, Transfers, Leihen</li>
                        <li>- Sponsoren, Training, Stadion, Finanzen</li>
                    </ul>
                </article>
            </section>
        @endif
    @else
        @php
            $fanMood = max(0, min(100, (int) $activeClub->fan_mood));
            $isActiveClubHome = $nextMatch && (int) $nextMatch->home_club_id === (int) $activeClub->id;
            $homeReady = $nextMatch ? ($isActiveClubHome ? $activeClubReadyForNextMatch : $opponentReadyForNextMatch) : false;
            $awayReady = $nextMatch ? (!$isActiveClubHome ? $activeClubReadyForNextMatch : $opponentReadyForNextMatch) : false;
        @endphp

        @if ($dashboardVariant === 'compact')
            @include('dashboard.partials.variant-compact')
        @elseif ($dashboardVariant === 'classic')
            @include('dashboard.partials.variant-classic')
        @else

        <section class="sim-card p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Planung</p>
                    <h2 class="mt-1 text-2xl font-bold text-white">Woche im Ueberblick</h2>
                </div>
                <p class="text-xs text-slate-400">Zwischen auf Mobile - 7 Tage kompakt</p>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
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
                    <a href="{{ $dayUrl }}" class="rounded-2xl border p-3 transition hover:border-cyan-300/60 hover:bg-cyan-500/10 {{ $day['is_today'] ? 'border-fuchsia-400/55 bg-fuchsia-500/15' : 'border-slate-700/70 bg-slate-900/45' }}">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">{{ $day['label'] }}</p>
                        <p class="mt-1 text-xl font-bold text-white">{{ $day['date'] }}</p>
                        <p class="mt-3 text-xs text-slate-300">
                            {{ $day['training_count'] > 0 ? $day['training_count'].'x Training' : 'Kein Training' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-300">
                            {{ $day['match_count'] > 0 ? $day['match_count'].'x Spiel(e)' : 'Keine Spiele' }}
                        </p>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="sim-card overflow-hidden p-4 sm:p-5 lg:p-6">
            <div class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(0,0.95fr)]">
                <div class="space-y-4">
                    <div class="grid gap-4 lg:grid-cols-[minmax(0,1.9fr)_minmax(0,0.95fr)]">
                        <article class="sim-card-soft border-slate-700/60 bg-[linear-gradient(120deg,rgba(15,23,42,0.94),rgba(30,41,59,0.56))] p-4">
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Verein</p>
                            <div class="mt-3 flex items-center gap-3">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-slate-700/70 bg-slate-900/75">
                                    <img
                                        class="h-10 w-10 object-contain"
                                        src="{{ $activeClub->logo_url }}"
                                        alt="{{ $activeClub->name }}"
                                        width="40"
                                        height="40"
                                    >
                                </div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="truncate text-2xl font-bold text-white">{{ $activeClub->name }}</h2>
                                        @if ($clubRank)
                                            <span class="inline-flex rounded-full border border-fuchsia-400/35 bg-fuchsia-500/15 px-2 py-0.5 text-[11px] font-semibold text-fuchsia-200">Rang #{{ $clubRank }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        <span class="sim-pill">{{ $activeClub->league }}</span>
                                        <span class="sim-pill">Punkte: {{ $clubPoints ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <article class="sim-card-soft border-slate-700/60 bg-[linear-gradient(120deg,rgba(15,23,42,0.94),rgba(30,41,59,0.5))] p-4">
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Form (letzte Spiele)</p>
                            @if ($recentForm !== [])
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($recentForm as $result)
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-xs font-bold {{ $result === 'W' ? 'bg-emerald-500/20 text-emerald-200' : ($result === 'L' ? 'bg-rose-500/20 text-rose-200' : 'bg-amber-500/20 text-amber-200') }}">
                                            {{ $result }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-6 text-sm text-slate-400">Noch keine Ligaspiele.</p>
                            @endif
                        </article>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <article class="sim-card-soft border-slate-700/60 bg-[linear-gradient(120deg,rgba(15,23,42,0.94),rgba(30,41,59,0.5))] p-4">
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Finanzen</p>
                            <p class="mt-2 text-4xl font-bold text-emerald-200">{{ number_format((float) $activeClub->budget, 2, ',', '.') }} EUR</p>
                            <p class="mt-2 text-sm font-semibold text-amber-200">{{ number_format((int) ($activeClub->coins ?? 0), 0, ',', '.') }} Coins</p>
                            <p class="mt-1 text-xs text-slate-400">Vereinsbudget (Transfers, Gehaelter, Ausbau)</p>
                            <a href="{{ route('finances.index', ['club' => $activeClub->id]) }}" class="sim-btn-muted mt-4">Uebersicht oeffnen</a>
                        </article>

                        <article class="sim-card-soft border-slate-700/60 bg-[linear-gradient(120deg,rgba(15,23,42,0.94),rgba(30,41,59,0.5))] p-4">
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Vorstand</p>
                            <p class="mt-2 text-4xl font-bold text-slate-100">{{ (int) $activeClub->board_confidence }}</p>
                            <p class="mt-1 text-xs text-slate-400">Vertrauen des Vorstands in dein Management (0-100).</p>
                            <a href="{{ route('notifications.index') }}" class="sim-btn-muted mt-4">Hinweise</a>
                        </article>
                    </div>

                    <article class="sim-card-soft border-slate-700/60 bg-[linear-gradient(120deg,rgba(15,23,42,0.94),rgba(30,41,59,0.5))] p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Fanbeliebtheit</p>
                            <span class="inline-flex rounded-full border border-emerald-400/35 bg-emerald-500/15 px-2 py-0.5 text-[11px] font-semibold text-emerald-200">
                                {{ $fanMood >= 65 ? 'Beliebt' : ($fanMood >= 45 ? 'Stabil' : 'Kritisch') }}
                            </span>
                        </div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <p class="text-4xl font-bold text-slate-100">{{ $fanMood }}</p>
                            <p class="text-sm text-slate-400">/100</p>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">Beeinflusst Zuschauerinteresse, Stimmung im Umfeld und Sponsorpotenzial.</p>
                        <div class="mt-3 h-2 rounded-full bg-slate-700/70">
                            <div class="h-2 rounded-full bg-gradient-to-r from-cyan-400 to-emerald-400" style="width: {{ $fanMood }}%"></div>
                        </div>
                    </article>

                    <article class="sim-card-soft border-slate-700/60 bg-[linear-gradient(120deg,rgba(15,23,42,0.94),rgba(30,41,59,0.5))] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Naechstes Spiel</p>
                                @if ($nextMatch)
                                    <p class="mt-1 text-xl font-bold text-white">{{ $nextMatch->kickoff_at?->format('d.m.Y - H:i') }} Uhr</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $nextMatchTypeLabel }}</p>
                                @else
                                    <p class="mt-1 text-sm text-slate-300">Kein Spiel geplant.</p>
                                @endif
                            </div>
                            <a href="{{ $nextMatch ? route('matches.show', $nextMatch) : route('league.matches') }}" class="sim-btn-muted">
                                {{ $nextMatch ? 'Tippen fuer Match-Center' : 'Zum Spielplan' }}
                            </a>
                        </div>

                        @if ($nextMatch)
                            <div class="mt-4 rounded-2xl border border-slate-700/70 bg-slate-950/50 p-4">
                                <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-3">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <img class="sim-avatar sim-avatar-md h-10 w-10 shrink-0 object-contain" src="{{ $nextMatch->homeClub->logo_url }}" alt="{{ $nextMatch->homeClub->name }}" width="40" height="40">
                                        <div class="min-w-0">
                                            <p class="truncate text-base font-bold text-white">{{ $nextMatch->homeClub->name }}</p>
                                            <p class="text-xs text-slate-400">Heim</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex rounded-full border border-fuchsia-400/35 bg-fuchsia-500/15 px-2 py-0.5 text-[11px] font-semibold text-fuchsia-200">VS</span>
                                    <div class="flex min-w-0 items-center justify-end gap-2">
                                        <div class="min-w-0 text-right">
                                            <p class="truncate text-base font-bold text-white">{{ $nextMatch->awayClub->name }}</p>
                                            <p class="text-xs text-slate-400">Gast</p>
                                        </div>
                                        <img class="sim-avatar sim-avatar-md h-10 w-10 shrink-0 object-contain" src="{{ $nextMatch->awayClub->logo_url }}" alt="{{ $nextMatch->awayClub->name }}" width="40" height="40">
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2 text-xs">
                                    <span class="inline-flex items-center rounded-full border px-2 py-1 font-semibold {{ $homeReady ? 'border-emerald-400/35 bg-emerald-500/15 text-emerald-200' : 'border-rose-400/35 bg-rose-500/15 text-rose-200' }}">
                                        {{ $homeReady ? 'Heim aufgestellt' : 'Heim fehlt' }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full border px-2 py-1 font-semibold {{ $awayReady ? 'border-emerald-400/35 bg-emerald-500/15 text-emerald-200' : 'border-rose-400/35 bg-rose-500/15 text-rose-200' }}">
                                        {{ $awayReady ? 'Gast aufgestellt' : 'Gast fehlt' }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </article>
                </div>

                <article class="sim-card-soft border-slate-700/60 bg-[linear-gradient(140deg,rgba(30,41,59,0.62),rgba(73,23,76,0.32))] p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Assistent</p>
                            <h2 class="mt-1 text-2xl font-bold text-white">Manager-Assistent</h2>
                        </div>
                        <p class="text-xs text-slate-400">Diese Woche</p>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-600/60 bg-slate-900/45 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Training (diese Woche)</p>
                                <p class="mt-1 text-sm text-slate-200">
                                    {{ $trainingPlanComplete ? 'Trainingsplan ist gesetzt.' : 'Dein Trainingsplan ist noch nicht vollstaendig.' }}
                                </p>
                            </div>
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $trainingPlanComplete ? 'border-emerald-400/40 bg-emerald-500/15 text-emerald-200' : 'border-fuchsia-400/40 bg-fuchsia-500/15 text-fuchsia-200' }}">
                                {{ $trainingPlanComplete ? 'Bereit' : 'Ausstehend' }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-700/70 bg-slate-950/45 p-3">
                                <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Gruppe A</p>
                                <p class="mt-1 text-lg font-bold text-white">{{ $trainingGroupACount > 0 ? $trainingGroupACount.' Einheiten' : 'Plan fehlt' }}</p>
                                <p class="mt-1 text-xs text-slate-400">Fitness & Technik</p>
                            </div>
                            <div class="rounded-xl border border-slate-700/70 bg-slate-950/45 p-3">
                                <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Gruppe B</p>
                                <p class="mt-1 text-lg font-bold text-white">{{ $trainingGroupBCount > 0 ? $trainingGroupBCount.' Einheiten' : 'Plan fehlt' }}</p>
                                <p class="mt-1 text-xs text-slate-400">Taktik, Recovery & Friendly</p>
                            </div>
                        </div>

                            <a href="{{ route('training.index', ['club' => $activeClub->id, 'range' => 'week']) }}" class="sim-btn-muted mt-4">Training planen</a>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-700/70 bg-slate-900/35 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Inbox</p>
                            <span class="text-xs text-slate-300">{{ $unreadNotificationsCount }} ungelesen</span>
                        </div>
                        @if ($notifications->isEmpty())
                            <p class="mt-2 text-sm text-slate-300">Keine aktuellen Hinweise.</p>
                        @else
                            <div class="mt-3 space-y-2">
                                @foreach ($notifications->take(3) as $notification)
                                    <div class="rounded-lg border border-slate-700/70 bg-slate-950/45 px-3 py-2">
                                        <p class="text-sm font-semibold text-white">{{ $notification->title }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $notification->created_at->format('d.m.Y H:i') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <a href="{{ route('notifications.index') }}" class="sim-page-link mt-3">Alle Benachrichtigungen</a>
                    </div>

                    @if (!empty($assistantTasks))
                        <div class="mt-4 rounded-2xl border border-slate-700/70 bg-slate-900/35 p-4">
                            <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Empfohlene Schritte</p>
                            <div class="mt-3 space-y-2">
                                @foreach ($assistantTasks as $task)
                                    <div class="rounded-lg border px-3 py-2 {{ $task['kind'] === 'warning' ? 'border-fuchsia-400/35 bg-fuchsia-500/10' : 'border-cyan-400/30 bg-cyan-500/10' }}">
                                        <p class="text-sm font-semibold text-white">{{ $task['label'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-300">{{ $task['description'] }}</p>
                                        <a href="{{ $task['url'] }}" class="sim-page-link mt-2">{{ $task['cta'] }}</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </article>
            </div>
        </section>
        @endif
    @endif
</x-app-layout>
