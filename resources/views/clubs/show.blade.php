<x-app-layout>
    <div x-data="{ activeTab: 'overview' }" class="space-y-6">
        <!-- Header & Navigation -->
        <div class="space-y-3">
            <a href="{{ route('dashboard') }}" class="sim-page-link">← Zurueck zum Dashboard</a>

            <div class="sim-card p-5 sm:p-6">
                <div class="flex flex-wrap items-center gap-4">
                    <img class="h-10 w-10 md:h-12 md:w-12 rounded-full object-cover ring-2 bg-slate-900 ring-slate-700/50 p-1 shadow-lg" src="{{ $club->logo_url }}" alt="{{ $club->name }}">
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
                            <span class="text-indigo-400 font-bold">Manager:</span> {{ $club->user?->name ?? 'CPU' }}
                            @if ($club->stadium)
                                | <span class="text-indigo-400 font-bold">Stadion:</span> {{ $club->stadium->name }} ({{ number_format((float) $club->stadium->capacity) }} Plaetze)
                            @endif
                        </p>
                    </div>
                    @if (auth()->user()->isAdmin() || auth()->id() === $club->user_id)
                        <div class="ml-auto">
                            <a href="{{ route('admin.clubs.edit', $club) }}" class="sim-btn-muted">Bearbeiten</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="border-b border-slate-800/80">
                <div class="flex flex-wrap gap-6">
                    <button @click="activeTab = 'overview'" :class="{ 'sim-tab-link-active': activeTab === 'overview' }" class="sim-tab-link">Ueberblick</button>
                    <button @click="activeTab = 'squad'" :class="{ 'sim-tab-link-active': activeTab === 'squad' }" class="sim-tab-link">Kader</button>
                    <button @click="activeTab = 'matches'" :class="{ 'sim-tab-link-active': activeTab === 'matches' }" class="sim-tab-link">Spiele</button>
                    <button @click="activeTab = 'achievements'" :class="{ 'sim-tab-link-active': activeTab === 'achievements' }" class="sim-tab-link">Erfolge</button>
                    <button @click="activeTab = 'stadium'" :class="{ 'sim-tab-link-active': activeTab === 'stadium' }" class="sim-tab-link">Stadion</button>
                </div>
            </div>
        </div>

        @php
            $contextLabels = [
                'league' => 'Liga',
                'cup_national' => 'Pokal National',
                'cup_international' => 'Pokal International',
                'friendly' => 'Freundschaft',
            ];
        @endphp

        <!-- OVERVIEW TAB -->
        <div x-show="activeTab === 'overview'" x-transition.opacity class="space-y-6">
            <section class="sim-card p-5">
                <p class="sim-section-title flex items-center gap-2">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Gesamtstatistik (Liga & Pokal)
                </p>
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
                    <p class="sim-section-title flex items-center gap-2">
                         <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Saison {{ $activeSeason?->name ?? '-' }} – Leistungsuebersicht
                    </p>
                    <form method="GET" action="{{ route('clubs.show', $club) }}">
                        <select name="season_id" class="sim-select text-xs py-1" onchange="this.form.submit()">
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
                <p class="sim-section-title flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Letzte Spiele
                </p>
                <div class="mt-4 space-y-2">
                    @forelse ($latestMatches as $match)
                        @php
                            $isHome = $match->home_club_id === $club->id;
                            $gf = (int) ($isHome ? $match->home_score : $match->away_score);
                            $ga = (int) ($isHome ? $match->away_score : $match->home_score);
                            $result = $gf > $ga ? 'W' : ($gf === $ga ? 'D' : 'L');
                            $resultClass = match ($result) {
                                'W' => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
                                'D' => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
                                'L' => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
                            };
                        @endphp
                        <div class="sim-card-soft flex flex-wrap items-center justify-between gap-3 px-4 py-3 hover:bg-slate-800/80 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg border font-bold {{ $resultClass }}">{{ $result }}</span>
                                <div>
                                    <div class="flex items-center gap-2 text-sm font-semibold text-white">
                                        <img class="h-6 w-6 rounded-full object-cover ring-1 bg-slate-800 ring-slate-700/50" src="{{ $match->homeClub->logo_url }}" alt="{{ $match->homeClub->name }}">
                                        <span class="{{ $match->home_club_id === $club->id ? 'font-bold text-white' : 'text-slate-300' }}">{{ $match->homeClub->name }}</span>
                                        <span class="text-xs text-slate-500">vs</span>
                                        <img class="h-6 w-6 rounded-full object-cover ring-1 bg-slate-800 ring-slate-700/50" src="{{ $match->awayClub->logo_url }}" alt="{{ $match->awayClub->name }}">
                                        <span class="{{ $match->away_club_id === $club->id ? 'font-bold text-white' : 'text-slate-300' }}">{{ $match->awayClub->name }}</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $contextLabels[$match->type] ?? ucfirst($match->type) }}
                                        · {{ $match->played_at?->format('d.m.Y') }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-sm font-bold text-white tracking-widest bg-slate-900 px-2 py-1 rounded border border-slate-700">
                                {{ $match->home_score }} : {{ $match->away_score }}
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 italic text-center py-4">Noch keine Spiele in dieser Saison.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- SQUAD TAB -->
        <div x-show="activeTab === 'squad'" style="display: none;" x-transition.opacity class="space-y-6">
            <!-- Squad Summary -->
            <div class="sim-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <p class="sim-section-title flex items-center gap-2">
                        <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Mannschaftskader
                    </p>
                    <span class="text-xs font-bold bg-slate-800 px-2 py-1 rounded text-slate-300 border border-slate-700">
                        Kadergroeße: {{ $squadStats['count'] }}
                    </span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="sim-card-soft p-4 border-l-4 border-l-cyan-500">
                        <p class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Ø Staerke / Alter</p>
                        <p class="text-xl font-bold text-white mt-1">
                            {{ $squadStats['avg_rating'] }} <span class="text-slate-500 text-sm font-normal">/ 99</span>
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">Alter {{ $squadStats['avg_age'] }} Jahre</p>
                    </div>
                    <div class="sim-card-soft p-4 border-l-4 border-l-emerald-500">
                        <p class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Gesamtmarktwert</p>
                        <p class="text-xl font-bold text-emerald-300 mt-1">
                            {{ number_format($squadStats['total_value'], 0, ',', '.') }} €
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">Ø {{ number_format($squadStats['avg_value'], 0, ',', '.') }} €</p>
                    </div>
                    <div class="sim-card-soft p-4 border-l-4 border-l-amber-500">
                        <p class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Verletzte / Gesperrt</p>
                        <div class="flex items-center gap-3 mt-1">
                             <div class="flex items-center gap-1.5 text-amber-400 font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                {{ $squadStats['injured_count'] }}
                             </div>
                             <div class="h-4 w-px bg-slate-700"></div>
                             <div class="flex items-center gap-1.5 text-rose-400 font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                {{ $squadStats['suspended_count'] }}
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Player List by Group -->
            @foreach ($groupedPlayers as $positionGroup => $groupPlayers)
                <div class="sim-card p-5" x-data="{ expanded: true }">
                    <button @click="expanded = !expanded" class="w-full flex items-center justify-between gap-2 mb-4 group transition-all duration-200">
                        <div class="sim-section-title flex items-center gap-2 text-cyan-400 mb-0">
                             @if($positionGroup === 'Torhüter')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                             @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                             @endif
                            {{ $positionGroup }} <span class="text-xs text-slate-500 font-normal">({{ $groupPlayers->count() }})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase font-bold text-slate-600 group-hover:text-slate-400 transition-colors" x-text="expanded ? 'Einklappen' : 'Ausklappen'"></span>
                            <svg class="w-4 h-4 text-slate-500 transform transition-transform duration-200" :class="{ 'rotate-180': !expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                        </div>
                    </button>

                    <div x-show="expanded" x-transition.opacity.duration.200ms>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($groupPlayers as $player)
                            <div class="sim-card-soft p-3 hover:border-cyan-500/30 transition-all group relative overflow-hidden flex flex-col gap-3">
                                <div class="flex items-center gap-3 relative z-10">
                                    <div class="relative shrink-0">
                                         <img src="{{ $player->photo_url }}" class="h-10 w-10 md:h-12 md:w-12 rounded-full object-cover ring-2 ring-slate-800 bg-slate-800" alt="{{ $player->full_name }}">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('players.show', $player) }}" class="font-bold text-white truncate group-hover:text-cyan-400 transition-colors block text-sm">
                                            {{ $player->first_name }} {{ $player->last_name }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs font-bold text-cyan-400 bg-cyan-950/30 px-1.5 py-0.5 rounded border border-cyan-500/20">{{ $player->display_position }}</span>
                                            @if($player->position_second)
                                                <span class="text-[10px] text-slate-500">{{ \App\Models\Player::mapPosition($player->position_second) }}</span>
                                            @endif
                                            @if($player->position_third)
                                                <span class="text-[10px] text-slate-500">{{ \App\Models\Player::mapPosition($player->position_third) }}</span>
                                            @endif
                                            <span class="text-xs text-slate-400 ml-auto">{{ $player->age }}J</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-end justify-between relative z-10 pt-2 border-t border-slate-700/50">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase text-slate-500 mb-1">Staerke</p>
                                        <div class="flex items-end gap-1">
                                            <span class="text-lg font-bold text-white leading-none">{{ $player->overall }}</span>
                                            <span class="text-[10px] text-slate-500 leading-none mb-0.5">/ {{ $player->potential }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold uppercase text-slate-500 mb-1">Marktwert</p>
                                        <p class="text-xs font-bold text-emerald-400 leading-none">{{ number_format($player->market_value, 0, ',', '.') }} €</p>
                                    </div>
                                </div>
                                
                                <!-- Background Position Text -->
                                <div class="absolute -bottom-2 -right-2 text-[50px] font-black text-white/[0.03] select-none pointer-events-none">
                                    {{ $player->display_position }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- MATCHES TAB -->
        <div x-show="activeTab === 'matches'" style="display: none;" x-transition.opacity class="space-y-6">
            <section class="sim-card p-5">
                <p class="sim-section-title mb-4">Spielplan & Ergebnisse</p>
                <div class="space-y-2">
                    @forelse ($allMatches as $match)
                        @php
                            $isHome = $match->home_club_id === $club->id;
                            $hasResult = $match->status === 'played';
                            $gf = $hasResult ? (int) ($isHome ? $match->home_score : $match->away_score) : 0;
                            $ga = $hasResult ? (int) ($isHome ? $match->away_score : $match->home_score) : 0;
                            $result = $gf > $ga ? 'W' : ($gf === $ga ? 'D' : 'L');
                            $resultClass = $hasResult ? match ($result) {
                                'W' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                'D' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                'L' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                            } : 'bg-slate-800 text-slate-400 border-slate-700';
                        @endphp
                         <div class="sim-card-soft flex flex-wrap items-center justify-between gap-4 px-4 py-3 hover:bg-slate-800/80 transition-colors">
                             <div class="flex items-center gap-4 min-w-[200px]">
                                 @if ($hasResult)
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded font-bold border {{ $resultClass }}">
                                        {{ $result }}
                                    </span>
                                 @else
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded font-bold bg-slate-800 text-slate-500 border border-slate-700">
                                        -
                                    </span>
                                 @endif
                                 <div>
                                      <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $match->competitionSeason?->competition->name ?? 'Freundschaft' }}</p>
                                      <p class="text-xs text-slate-400">{{ $match->kickoff_at?->format('d.m.Y H:i') }} Uhr</p>
                                 </div>
                             </div>

                             <div class="flex-1 grid grid-cols-[1fr_auto_1fr] items-center gap-4 text-center">
                                 <!-- Home -->
                                 <div class="flex items-center justify-end gap-2 text-right">
                                     <span class="font-bold text-white {{ $match->home_club_id === $club->id ? 'text-cyan-400' : '' }}">{{ $match->homeClub->name }}</span>
                                     <img class="h-6 w-6 rounded-full object-cover ring-1 bg-slate-800 ring-slate-700/50" src="{{ $match->homeClub->logo_url }}" alt="">
                                 </div>
                                 
                                 <!-- Score -->
                                 <div class="w-16 py-1 bg-slate-900 rounded border border-slate-700 font-mono font-bold text-white tracking-widest">
                                     @if ($match->status === 'played')
                                        {{ $match->home_score }} : {{ $match->away_score }}
                                     @else
                                        - : -
                                     @endif
                                 </div>

                                 <!-- Away -->
                                 <div class="flex items-center justify-start gap-2 text-left">
                                     <img class="h-6 w-6 rounded-full object-cover ring-1 bg-slate-800 ring-slate-700/50" src="{{ $match->awayClub->logo_url }}" alt="">
                                     <span class="font-bold text-white {{ $match->away_club_id === $club->id ? 'text-cyan-400' : '' }}">{{ $match->awayClub->name }}</span>
                                 </div>
                             </div>

                             <div class="w-full sm:w-auto text-center sm:text-right">
                                 <a href="{{ route('matches.show', $match) }}" class="sim-btn-xs">Zum Spiel</a>
                             </div>
                         </div>
                    @empty
                         <div class="sim-card-soft p-8 text-center text-slate-400 italic">
                            Keine Spiele gefunden.
                         </div>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- ACHIEVEMENTS TAB -->
        <div x-show="activeTab === 'achievements'" style="display: none;" x-transition.opacity class="space-y-6">
             <div class="sim-card p-12 text-center border-dashed border-2 border-slate-700 bg-slate-900/40">
                <svg class="w-16 h-16 mx-auto text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg> 
                <p class="text-xl font-bold text-white mb-2">Vereinserfolge</p>
                <p class="text-slate-400">Hier werden bald die Trophäen und Erfolge des Vereins angezeigt.</p>
             </div>
        </div>

        <!-- STADIUM TAB -->
        <div x-show="activeTab === 'stadium'" style="display: none;" x-transition.opacity class="space-y-6">
            @if ($club->stadium)
                <div class="sim-card p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-32 bg-cyan-500/5 blur-[100px] rounded-full pointer-events-none"></div>
                    
                    <div class="flex items-start justify-between relative z-10">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Heimspielstaette</p>
                            <h2 class="text-3xl font-bold text-white">{{ $club->stadium->name }}</h2>
                        </div>
                         <div class="bg-slate-800 px-4 py-2 rounded-lg border border-slate-700 text-center">
                            <p class="text-2xl font-bold text-white">{{ number_format($club->stadium->capacity, 0, ',', '.') }}</p>
                            <p class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Plaetze</p>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Stadium stats placeholders -->
                         <div class="sim-card-soft p-4">
                            <p class="text-[10px] font-bold uppercase text-slate-500">Zustand</p>
                            <p class="text-lg font-bold text-white mt-1">Exzellent</p>
                            <div class="w-full h-1 bg-slate-700 rounded-full mt-2 overflow-hidden">
                                <div class="w-[95%] h-full bg-emerald-500"></div>
                            </div>
                         </div>
                         <div class="sim-card-soft p-4">
                            <p class="text-[10px] font-bold uppercase text-slate-500">Auslastung Ø</p>
                            <p class="text-lg font-bold text-white mt-1">92%</p> 
                         </div>
                    </div>
                </div>
            @else
                <div class="sim-card p-12 text-center border-dashed border-2 border-slate-700">
                    <p class="text-slate-400">Dieser Verein besitzt noch kein eigenes Stadion.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
