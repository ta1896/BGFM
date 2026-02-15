<x-app-layout>
    @php
        $baseQuery = array_filter([
            'competition_season' => $activeCompetitionSeason?->id,
            'club' => $filters['club'] ?? null,
            'status' => $filters['status'] ?? null,
            'type' => $filters['type'] ?? null,
            'scope' => null,
            'day' => null,
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <div class="space-y-6">
         <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="space-y-1">
                 <p class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-400 opacity-80">Universal Match Center</p>
                <h1 class="text-4xl font-extrabold text-white tracking-tight">Spielplan</h1>
                @if ($activeCompetitionSeason)
                    <div class="mt-3 flex items-center gap-3 bg-slate-800/30 rounded-xl px-4 py-2 border border-transparent backdrop-blur-sm w-fit shadow-sm">
                        <img class="h-6 w-auto opacity-90" src="{{ $activeCompetitionSeason->competition->logo_url }}" alt="{{ $activeCompetitionSeason->competition->name }}">
                        <span class="text-slate-300 font-semibold">{{ $activeCompetitionSeason->competition->name }}</span>
                        <span class="text-slate-600 font-medium px-2">/</span>
                        <span class="text-slate-500 font-medium">{{ $activeCompetitionSeason->season->name }}</span>
                    </div>
                @else
                    <div class="mt-3 flex items-center gap-3 bg-cyan-500/10 rounded-xl px-4 py-2 border border-transparent backdrop-blur-sm w-fit">
                        <span class="flex h-2 w-2 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,0.6)]"></span>
                        <span class="text-cyan-100 font-semibold tracking-wide">Gesamtübersicht</span>
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <form method="GET" action="{{ route('league.matches') }}" class="relative group">
                    @foreach($filters as $key => $value)
                        @if($key !== 'competition_season' && !empty($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <select name="competition_season" class="sim-input pl-4 pr-10 py-2.5 text-sm bg-slate-900/80 border-transparent focus:border-cyan-500/30 focus:ring-4 focus:ring-cyan-500/5 rounded-xl appearance-none cursor-pointer min-w-[280px] shadow-2xl" onchange="this.form.submit()">
                        <option value="">Alle Wettbewerbe</option>
                        @foreach ($competitionSeasons as $cs)
                            <option value="{{ $cs->id }}" @selected($activeCompetitionSeason && $activeCompetitionSeason->id === $cs->id)>
                                {{ $cs->competition->name }} ({{ $cs->season->name }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500 group-hover:text-cyan-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </form>

                @if (auth()->user()->isAdmin() && $activeCompetitionSeason)
                    <form method="POST" action="{{ route('admin.competition-seasons.generate-fixtures', $activeCompetitionSeason) }}">
                        @csrf
                        <button class="flex items-center gap-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 px-5 py-2.5 text-sm font-bold text-slate-200 border border-transparent transition-all active:scale-95" type="submit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Plan erstellen
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Filters Grid -->
        <div class="grid gap-4 items-start">
            <div class="sim-card p-2 bg-slate-900/60 backdrop-blur-xl border-transparent">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-2">
                    <!-- Global Scopes & Status -->
                    <div class="flex flex-wrap items-center gap-1.5 p-1 bg-slate-950/40 rounded-xl border border-transparent">
                        <a href="{{ route('league.matches', array_merge($baseQuery, ['scope' => null, 'status' => null, 'day' => null])) }}" 
                           class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition-all {{ !($filters['scope'] ?? null) && !($filters['status'] ?? null) ? 'bg-cyan-600 text-white shadow-lg shadow-cyan-900/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">Alle</a>
                        
                        <div class="w-px h-5 bg-transparent mx-1"></div>

                        <a href="{{ route('league.matches', array_merge($baseQuery, ['scope' => 'today', 'status' => null, 'day' => null])) }}" 
                           class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition-all {{ ($filters['scope'] ?? '') === 'today' ? 'bg-slate-800 text-cyan-400' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Heute</a>
                        
                        <a href="{{ route('league.matches', array_merge($baseQuery, ['scope' => 'upcoming', 'status' => null, 'day' => null])) }}" 
                           class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition-all {{ ($filters['scope'] ?? '') === 'upcoming' ? 'bg-slate-800 text-cyan-400' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Demnächst</a>

                        <div class="w-px h-5 bg-transparent mx-1"></div>

                        <a href="{{ route('league.matches', array_merge($baseQuery, ['status' => 'live', 'scope' => null, 'day' => null])) }}" 
                           class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition-all {{ ($filters['status'] ?? '') === 'live' ? 'bg-red-500/10 text-red-400' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Live</a>
                        
                        <a href="{{ route('league.matches', array_merge($baseQuery, ['status' => 'played', 'scope' => null, 'day' => null])) }}" 
                           class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition-all {{ ($filters['status'] ?? '') === 'played' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Beendet</a>
                    </div>

                    <!-- Type Filter -->
                    <div class="flex items-center gap-1.5 p-1 bg-slate-950/40 rounded-xl border border-transparent">
                        <a href="{{ route('league.matches', array_merge($baseQuery, ['type' => null])) }}" 
                           class="px-4 py-2 rounded-lg text-[11px] font-bold uppercase tracking-wide transition-all {{ !($filters['type'] ?? null) ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-300' }}">Alle Typen</a>
                        
                        <a href="{{ route('league.matches', array_merge($baseQuery, ['type' => 'league'])) }}" 
                           class="px-4 py-2 rounded-lg text-[11px] font-bold uppercase tracking-wide transition-all {{ ($filters['type'] ?? '') === 'league' ? 'bg-cyan-500/10 text-cyan-400' : 'text-slate-500 hover:text-slate-300' }}">Liga</a>
                        
                        <a href="{{ route('league.matches', array_merge($baseQuery, ['type' => 'cup'])) }}" 
                           class="px-4 py-2 rounded-lg text-[11px] font-bold uppercase tracking-wide transition-all {{ ($filters['type'] ?? '') === 'cup' ? 'bg-amber-500/10 text-amber-400' : 'text-slate-500 hover:text-slate-300' }}">Pokal</a>
                        
                        <a href="{{ route('league.matches', array_merge($baseQuery, ['type' => 'friendly'])) }}" 
                           class="px-4 py-2 rounded-lg text-[11px] font-bold uppercase tracking-wide transition-all {{ ($filters['type'] ?? '') === 'friendly' ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-500 hover:text-slate-300' }}">Testspiel</a>
                    </div>

                    <!-- Club Picker -->
                    <form method="GET" action="{{ route('league.matches') }}" class="min-w-[200px]">
                        <input type="hidden" name="competition_season" value="{{ $activeCompetitionSeason?->id }}">
                        @if($filters['type']) <input type="hidden" name="type" value="{{ $filters['type'] }}"> @endif
                        @if($filters['status']) <input type="hidden" name="status" value="{{ $filters['status'] }}"> @endif
                        @if($filters['scope']) <input type="hidden" name="scope" value="{{ $filters['scope'] }}"> @endif
                        
                        <div class="relative group">
                            <select name="club" class="w-full bg-slate-900/40 border-transparent text-[11px] text-slate-400 font-bold h-10 pl-3 pr-10 rounded-xl focus:ring-0 focus:border-cyan-500/30 cursor-pointer appearance-none transition-all hover:bg-slate-800/40" onchange="this.form.submit()">
                                <option value="" class="bg-slate-900 text-white">Filter: Alle Vereine</option>
                                @foreach ($clubFilterOptions as $clubOption)
                                    <option value="{{ $clubOption->id }}" class="bg-slate-900 text-white" @selected((int) ($filters['club'] ?? 0) === (int) $clubOption->id)>
                                        {{ $clubOption->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-700/50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Matches Listing -->
        @if ($matchesByDay->isNotEmpty())
            <div class="space-y-12 pb-20">
                @foreach ($matchesByDay as $groupKey => $matches)
                    <div class="space-y-5">
                        <div class="flex items-center gap-6">
                             <div class="flex flex-col">
                                @if($groupType === 'matchday')
                                    <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-cyan-500/80 mb-1">Runde / Spieltag</span>
                                    <h2 class="text-2xl font-black text-white tracking-tight">Spieltag {{ $groupKey }}</h2>
                                @else
                                    <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-cyan-500/80 mb-1">{{ \Carbon\Carbon::parse($groupKey)->isoFormat('dddd') }}</span>
                                    <h2 class="text-2xl font-black text-white tracking-tight">{{ \Carbon\Carbon::parse($groupKey)->format('d. F Y') }}</h2>
                                @endif
                             </div>
                             <div class="h-px flex-1 bg-transparent"></div>
                        </div>
                        
                        <div class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
                            @foreach ($matches as $match)
                                <article class="sim-card relative !bg-slate-900/20 border-transparent hover:border-cyan-500/10 transition-all duration-500 group overflow-hidden flex flex-col shadow-lg hover:shadow-cyan-900/5">
                                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>

                                    <!-- Card Header -->
                                    <div class="px-5 py-3 border-b border-transparent flex justify-between items-center relative z-10">
                                        <div class="flex items-center gap-2.5">
                                            @if ($match->competitionSeason?->competition)
                                                <div class="p-1 px-1.5 bg-slate-800/40 rounded border border-transparent shadow-sm">
                                                    <img class="h-4 w-auto grayscale group-hover:grayscale-0 transition-all duration-500 opacity-60 group-hover:opacity-100" src="{{ $match->competitionSeason->competition->logo_url }}" title="{{ $match->competitionSeason->competition->name }}">
                                                </div>
                                            @else
                                                <div class="p-1 px-1.5 bg-emerald-500/10 rounded border border-emerald-500/20 shadow-sm">
                                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                </div>
                                            @endif
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-0.5">
                                                    {{ $match->kickoff_at?->format('H:i') }} Uhr
                                                </span>
                                                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-tighter truncate max-w-[120px]">
                                                    {{ $match->competitionSeason?->competition->short_name ?: ($match->type === 'friendly' ? 'Freundschaft' : 'Wettbewerb') }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-2">
                                            @if ($match->status === 'live')
                                                 <span class="text-[10px] text-red-100 font-extrabold flex items-center gap-1.5 bg-red-600 px-2.5 py-1 rounded-full shadow-lg shadow-red-500/20">
                                                     <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> LIVE
                                                 </span>
                                            @elseif($match->status === 'played')
                                                 <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest bg-slate-800/40 px-2.5 py-1 rounded-md border border-transparent shadow-sm">Endstand</span>
                                             @else
                                                 <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest px-2 py-1 border border-transparent rounded bg-slate-900/50 group-hover:border-transparent transition">Geplant</span>
                                             @endif
                                        </div>
                                    </div>

                                    <!-- Main Match Area -->
                                    <div class="p-6 relative z-10 flex-1 flex flex-col justify-center">
                                        <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-6">
                                            <!-- Home Team -->
                                            <div class="flex flex-col items-center gap-3 text-center group/team">
                                                <div class="relative h-14 w-14 md:h-16 md:w-16 rounded-2xl border border-transparent bg-slate-950/40 p-2.5 shadow-xl transition-all duration-500 group-hover:border-cyan-500/30 group-hover:shadow-cyan-500/10 group-hover:-translate-y-1">
                                                    <img src="{{ $match->homeClub->logo_url }}" alt="{{ $match->homeClub->name }}" class="h-full w-full object-contain filter drop-shadow-md">
                                                </div>
                                                <span class="text-[11px] font-bold text-slate-300 group-hover:text-white transition-colors uppercase tracking-tight line-clamp-1 h-4">{{ $match->homeClub->name }}</span>
                                            </div>

                                            <!-- Score/VS -->
                                            <div class="flex flex-col items-center gap-2">
                                                @if ($match->status === 'played' || $match->status === 'live')
                                                    <div class="text-3xl font-black text-white font-mono tracking-[-0.1em] tabular-nums flex items-center gap-2">
                                                        <span class="inline-block min-w-[32px] text-center">{{ $match->home_score }}</span>
                                                        <span class="text-slate-600/60">:</span>
                                                        <span class="inline-block min-w-[32px] text-center">{{ $match->away_score }}</span>
                                                    </div>
                                                @else
                                                    <div class="w-10 h-10 rounded-full border border-transparent flex items-center justify-center bg-slate-900/20 shadow-inner">
                                                        <span class="text-[10px] font-black text-slate-700/60 uppercase italic">vs</span>
                                                    </div>
                                                @endif
                                                
                                                @if ($match->stadiumClub)
                                                    <div class="flex items-center gap-1 opacity-40 group-hover:opacity-100 transition duration-500">
                                                        <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                        <span class="text-[8px] font-bold text-slate-500 uppercase tracking-tighter truncate max-w-[70px]">{{ $match->stadiumClub->name }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Away Team -->
                                            <div class="flex flex-col items-center gap-3 text-center group/team">
                                                <div class="relative h-14 w-14 md:h-16 md:w-16 rounded-2xl border border-transparent bg-slate-950/20 p-2.5 shadow-xl transition-all duration-500 group-hover:border-cyan-500/20 group-hover:shadow-cyan-500/5 group-hover:-translate-y-1">
                                                    <img src="{{ $match->awayClub->logo_url }}" alt="{{ $match->awayClub->name }}" class="h-full w-full object-contain filter drop-shadow-md opacity-90 group-hover:opacity-100">
                                                </div>
                                                <span class="text-[11px] font-bold text-slate-400 group-hover:text-slate-200 transition-colors uppercase tracking-tight line-clamp-1 h-4">{{ $match->awayClub->name }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer Actions -->
                                    <div class="p-3 bg-slate-950/30 border-t border-transparent group-hover:bg-slate-900/50 transition-colors">
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="{{ route('matches.show', $match) }}" class="flex items-center justify-center py-2.5 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-white hover:bg-slate-700/40 rounded-lg transition-all border border-transparent">Matchcenter</a>
                                            
                                            @if (auth()->user()->isAdmin())
                                                @if ($match->status === 'played')
                                                    <form method="POST" action="{{ route('matches.simulate', $match) }}" class="contents">
                                                        @csrf
                                                        <button type="submit" class="flex items-center justify-center py-2.5 text-[10px] font-bold uppercase tracking-widest text-orange-400 bg-orange-500/5 hover:bg-orange-500/15 rounded-lg transition-all border border-transparent">Re-Sim</button>
                                                    </form>
                                                @elseif($match->status !== 'played')
                                                     <form method="POST" action="{{ route('matches.simulate', $match) }}" class="contents">
                                                        @csrf
                                                        <button type="submit" class="flex items-center justify-center py-2.5 text-[10px] font-bold uppercase tracking-widest text-emerald-400 bg-emerald-500/5 hover:bg-emerald-500/15 rounded-lg transition-all border border-transparent">Simulieren</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="sim-card p-24 text-center border-dashed border-2 border-transparent bg-slate-900/20 backdrop-blur-md rounded-[2rem]">
                <div class="flex flex-col items-center justify-center text-slate-500">
                    <div class="w-24 h-24 rounded-full bg-slate-900/50 flex items-center justify-center mb-6 border border-transparent shadow-inner">
                        <svg class="w-10 h-10 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-black text-white mb-3 tracking-tight">Keine Spiele im System</h3>
                    <p class="text-slate-500 max-w-sm mx-auto leading-relaxed">Derzeit sind keine Spiele geplant, die deinen Filtereinstellungen entsprechen. Bitte wähle einen anderen Zeitraum oder Wettbewerb.</p>
                </div>

                <div class="mt-10 flex justify-center gap-4">
                    @if (auth()->user()->isAdmin() && $activeCompetitionSeason)
                        <form method="POST" action="{{ route('admin.competition-seasons.generate-fixtures', $activeCompetitionSeason) }}">
                            @csrf
                            <button class="sim-btn-primary px-8 py-3 rounded-2xl shadow-xl shadow-cyan-500/20" type="submit">Spielplan generieren</button>
                        </form>
                    @endif
                    <a href="{{ route('league.matches') }}" class="sim-btn-muted px-8 py-3 rounded-2xl border border-transparent">Filter zurücksetzen</a>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
