<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <a href="{{ route('dashboard') }}" class="sim-page-link">← Zurueck zum Dashboard</a>

            <div class="sim-card p-5 sm:p-6">
                <div class="flex flex-wrap items-center gap-4">
                    <img class="sim-avatar sim-avatar-lg" src="{{ $club->logo_url }}" alt="{{ $club->name }}">
                    <div>
                        @php
                            $objectiveLabels = [
                                'avoid_relegation' => 'Klassenerhalt',
                                'mid_table' => 'Mittelfeld',
                                'promotion' => 'Aufstieg',
                                'title' => 'Meisterschaft',
                                'cup_run' => 'Pokalrunde',
                            ];
                        @endphp
                        <p class="text-2xl font-bold text-white">{{ $club->name }}</p>
                        <p class="text-sm text-slate-300">({{ $club->short_name ?? '---' }})</p>
                        <p class="mt-1 text-xs text-cyan-200">Saisonziel: {{ $objectiveLabels[$club->season_objective ?? 'mid_table'] ?? 'Mittelfeld' }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            Manager: {{ $club->user?->name ?? '-' }}
                            @if ($club->stadium)
                                | Stadion: {{ $club->stadium->name }} ({{ number_format((float) $club->stadium->capacity) }} Plaetze)
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            Kapitaen: {{ $club->captain?->full_name ?? '-' }}
                            | Vize: {{ $club->viceCaptain?->full_name ?? '-' }}
                        </p>
                    </div>
                    @if (auth()->user()->isAdmin())
                        <div class="ml-auto">
                            <a href="{{ route('admin.clubs.edit', $club) }}" class="sim-btn-muted">Im ACP bearbeiten</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="border-b border-slate-800/80">
                <div class="flex flex-wrap gap-6">
                    <a href="#" class="sim-tab-link sim-tab-link-active">Uebersicht</a>
                    <a href="#" class="sim-tab-link">Kader</a>
                    <a href="#" class="sim-tab-link">Spiele</a>
                    <a href="#" class="sim-tab-link">Erfolge</a>
                    <a href="#" class="sim-tab-link">Stadion</a>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $contextLabels = [
            'league' => 'Liga',
            'cup_national' => 'Pokal National',
            'cup_international' => 'Pokal International',
            'friendly' => 'Freundschaft',
        ];
    @endphp

    <section class="sim-card p-5">
        <p class="sim-section-title">Gesamtstatistik (Liga & Pokal)</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-white">{{ $overallStats['matches'] }}</p>
                <p class="text-xs text-slate-400">Spiele</p>
            </div>
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-emerald-300">{{ $overallStats['wins'] }}</p>
                <p class="text-xs text-slate-400">Siege</p>
            </div>
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-amber-300">{{ $overallStats['draws'] }}</p>
                <p class="text-xs text-slate-400">Remis</p>
            </div>
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-rose-300">{{ $overallStats['losses'] }}</p>
                <p class="text-xs text-slate-400">Niederlagen</p>
            </div>
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-white">{{ $overallStats['goals_for'] }}</p>
                <p class="text-xs text-slate-400">Tore</p>
            </div>
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-white">{{ $overallStats['goals_against'] }}</p>
                <p class="text-xs text-slate-400">Gegentore</p>
            </div>
        </div>
        <p class="mt-3 text-center text-xs text-slate-400">Punkte gesamt: {{ $overallStats['points'] }}</p>
    </section>

    <section class="sim-card p-5">
        <p class="sim-section-title">Gesamtwerte nach Wettbewerb</p>
        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($overallStatsByContext as $context => $stats)
                <div class="sim-card-soft p-4">
                    <p class="text-sm font-semibold text-cyan-100">{{ $contextLabels[$context] ?? $context }}</p>
                    <p class="mt-2 text-xs text-slate-300">
                        {{ $stats['matches'] }} Spiele | {{ $stats['wins'] }}-{{ $stats['draws'] }}-{{ $stats['losses'] }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">Tore {{ $stats['goals_for'] }}:{{ $stats['goals_against'] }} | Punkte {{ $stats['points'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="sim-card p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="sim-section-title">Saison {{ $activeSeason?->name ?? '-' }} - Leistungsuebersicht</p>
            <form method="GET" action="{{ route('clubs.show', $club) }}">
                <select name="season_id" class="sim-select" onchange="this.form.submit()">
                    @foreach ($seasons as $season)
                        <option value="{{ $season->id }}" @selected($activeSeason && $activeSeason->id === $season->id)>
                            {{ $season->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-white">{{ $seasonStats['matches'] }}</p>
                <p class="text-xs text-slate-400">Spiele</p>
            </div>
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-emerald-300">{{ $seasonStats['wins'] }}</p>
                <p class="text-xs text-slate-400">Siege</p>
            </div>
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-amber-300">{{ $seasonStats['draws'] }}</p>
                <p class="text-xs text-slate-400">Remis</p>
            </div>
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-rose-300">{{ $seasonStats['losses'] }}</p>
                <p class="text-xs text-slate-400">Niederlagen</p>
            </div>
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-white">{{ $seasonStats['goals_for'] }}</p>
                <p class="text-xs text-slate-400">Tore</p>
            </div>
            <div class="sim-stat-card">
                <p class="text-2xl font-bold text-white">{{ $seasonStats['goals_against'] }}</p>
                <p class="text-xs text-slate-400">Gegentore</p>
            </div>
        </div>
        <p class="mt-3 text-center text-xs text-slate-400">Punkte: {{ $seasonStats['points'] }}</p>
    </section>

    <section class="sim-card p-5">
        <p class="sim-section-title">Saison {{ $activeSeason?->name ?? '-' }} nach Wettbewerb</p>
        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($seasonStatsByContext as $context => $stats)
                <div class="sim-card-soft p-4">
                    <p class="text-sm font-semibold text-cyan-100">{{ $contextLabels[$context] ?? $context }}</p>
                    <p class="mt-2 text-xs text-slate-300">
                        {{ $stats['matches'] }} Spiele | {{ $stats['wins'] }}-{{ $stats['draws'] }}-{{ $stats['losses'] }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">Tore {{ $stats['goals_for'] }}:{{ $stats['goals_against'] }} | Punkte {{ $stats['points'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="sim-card p-5">
        <p class="sim-section-title">Historie der letzten Saisons</p>
        <div class="mt-4 overflow-x-auto">
            <table class="sim-table min-w-full">
                <thead>
                    <tr>
                        <th>Saison</th>
                        <th>Spiele</th>
                        <th>S</th>
                        <th>U</th>
                        <th>N</th>
                        <th>Tore</th>
                        <th>Punkte</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($seasonHistory as $historyRow)
                        <tr>
                            <td>{{ $historyRow['season_name'] }}</td>
                            <td>{{ $historyRow['matches'] }}</td>
                            <td>{{ $historyRow['wins'] }}</td>
                            <td>{{ $historyRow['draws'] }}</td>
                            <td>{{ $historyRow['losses'] }}</td>
                            <td>{{ $historyRow['goals_for'] }}:{{ $historyRow['goals_against'] }}</td>
                            <td>{{ $historyRow['points'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-sm text-slate-300">Keine Saisonhistorie vorhanden.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="sim-card p-5">
        <p class="sim-section-title">Letzte Spiele</p>
        <div class="mt-4 space-y-2">
            @forelse ($latestMatches as $match)
                @php
                    $isHome = $match->home_club_id === $club->id;
                    $opponent = $isHome ? $match->awayClub : $match->homeClub;
                    $gf = (int) ($isHome ? $match->home_score : $match->away_score);
                    $ga = (int) ($isHome ? $match->away_score : $match->home_score);
                    $result = $gf > $ga ? 'W' : ($gf === $ga ? 'D' : 'L');
                    $resultClass = match ($result) {
                        'W' => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
                        'D' => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
                        default => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
                    };
                @endphp
                <div class="sim-card-soft flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg border {{ $resultClass }}">{{ $result }}</span>
                        <div>
                            <div class="flex items-center gap-2 text-sm font-semibold text-white">
                                <img class="sim-avatar sim-avatar-xs" src="{{ $match->homeClub->logo_url }}" alt="{{ $match->homeClub->name }}">
                                <span>{{ $match->homeClub->name }}</span>
                                <span class="text-slate-400">vs</span>
                                <img class="sim-avatar sim-avatar-xs" src="{{ $match->awayClub->logo_url }}" alt="{{ $match->awayClub->name }}">
                                <span>{{ $match->awayClub->name }}</span>
                            </div>
                            <p class="text-xs text-slate-400">
                                {{ $match->type === 'friendly' ? 'Freundschaft' : 'Pflichtspiel' }}
                                · {{ $match->played_at?->format('d.m.Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="text-sm font-semibold text-white">
                        {{ $match->home_score }} : {{ $match->away_score }}
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-300">Noch keine Spiele gespielt.</p>
            @endforelse
        </div>
    </section>
</x-app-layout>
