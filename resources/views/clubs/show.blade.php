<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <a href="{{ route('dashboard') }}" class="sim-page-link">← Zurueck zum Dashboard</a>

            <div class="sim-card p-5 sm:p-6">
                <div class="flex flex-wrap items-center gap-4">
                    @if ($club->logo_path)
                        <img class="h-16 w-16 rounded-full border border-slate-700/80 bg-slate-900/60 object-cover"
                             src="{{ Storage::url($club->logo_path) }}"
                             alt="">
                    @else
                        <span class="h-16 w-16 rounded-full border border-slate-700/80 bg-slate-900/60"></span>
                    @endif
                    <div>
                        <p class="text-2xl font-bold text-white">{{ $club->name }}</p>
                        <p class="text-sm text-slate-300">({{ $club->short_name ?? '---' }})</p>
                        <p class="mt-1 text-xs text-slate-400">
                            Manager: {{ $club->user?->name ?? '-' }}
                            @if ($club->stadium)
                                | Stadion: {{ $club->stadium->name }} ({{ number_format((float) $club->stadium->capacity) }} Plaetze)
                            @endif
                        </p>
                    </div>
                    <div class="ml-auto">
                        <a href="{{ route('clubs.edit', $club) }}" class="sim-btn-muted">Bearbeiten</a>
                    </div>
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
                            <p class="text-sm font-semibold text-white">
                                {{ $match->homeClub->name }} vs {{ $match->awayClub->name }}
                            </p>
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
