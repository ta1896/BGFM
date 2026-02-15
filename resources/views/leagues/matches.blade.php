<x-app-layout>
    @php
        $baseQuery = array_filter([
            'competition_season' => $activeCompetitionSeason?->id,
            'club' => $filters['club'] ?? null,
            'status' => $filters['status'] ?? null,
            'scope' => null,
            'day' => null,
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <div class="space-y-6">
         <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                 <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Wettbewerb</p>
                <h1 class="text-3xl font-bold text-white tracking-tight">Spielplan</h1>
                @if ($activeCompetitionSeason)
                    <div class="mt-2 flex items-center gap-3">
                        <div class="bg-slate-800 rounded px-2 py-1 border border-slate-700">
                             <img class="h-6 w-auto" src="{{ $activeCompetitionSeason->competition->logo_url }}" alt="{{ $activeCompetitionSeason->competition->name }}">
                        </div>
                        <span class="text-lg text-slate-300 font-medium">{{ $activeCompetitionSeason->competition->name }} <span class="text-slate-500 mx-1">|</span> {{ $activeCompetitionSeason->season->name }}</span>
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('league.matches') }}">
                    @foreach($filters as $key => $value)
                        @if($key !== 'competition_season' && !empty($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <div class="relative group">
                        <select name="competition_season" class="sim-input pl-4 pr-10 py-2 text-sm bg-slate-900/80 backdrop-blur-md border-slate-700 focus:border-cyan-500 rounded-lg appearance-none cursor-pointer min-w-[240px] shadow-lg shadow-black/20" onchange="this.form.submit()">
                            @foreach ($competitionSeasons as $cs)
                                <option value="{{ $cs->id }}" @selected($activeCompetitionSeason && $activeCompetitionSeason->id === $cs->id)>
                                    {{ $cs->competition->name }} - {{ $cs->season->name }}
                                </option>
                            @endforeach
                        </select>
                         <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400 group-hover:text-cyan-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </form>

                @if (auth()->user()->isAdmin() && $activeCompetitionSeason)
                    <form method="POST" action="{{ route('admin.competition-seasons.generate-fixtures', $activeCompetitionSeason) }}">
                        @csrf
                        <button class="sim-btn-muted py-2 px-4 flex items-center gap-2" type="submit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Generieren
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Filters Toolbar -->
        <div class="sim-card py-3 px-4 flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-slate-900/60 backdrop-blur-md">
             <!-- Quick Filters -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('league.matches', ['competition_season' => $activeCompetitionSeason?->id]) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide transition-all {{ !$hasActiveFilters ? 'bg-cyan-500 text-white shadow-lg shadow-cyan-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Alle</a>
                
                <div class="w-px h-6 bg-slate-700/50 mx-1"></div>

                <a href="{{ route('league.matches', array_merge($baseQuery, ['scope' => 'today', 'day' => null])) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide transition-all {{ ($filters['scope'] ?? '') === 'today' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Heute</a>
                <a href="{{ route('league.matches', array_merge($baseQuery, ['scope' => 'week', 'day' => null])) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide transition-all {{ ($filters['scope'] ?? '') === 'week' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Woche</a>
                
                 <div class="w-px h-6 bg-slate-700/50 mx-1"></div>

                <a href="{{ route('league.matches', array_merge($baseQuery, ['status' => 'live', 'scope' => null, 'day' => null])) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide transition-all {{ ($filters['status'] ?? '') === 'live' ? 'bg-red-500/10 text-red-400 border border-red-500/20 animate-pulse' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Live</a>
                <a href="{{ route('league.matches', array_merge($baseQuery, ['status' => 'scheduled', 'scope' => null, 'day' => null])) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide transition-all {{ ($filters['status'] ?? '') === 'scheduled' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Geplant</a>
                <a href="{{ route('league.matches', array_merge($baseQuery, ['status' => 'played', 'scope' => null, 'day' => null])) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide transition-all {{ ($filters['status'] ?? '') === 'played' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Beendet</a>
            </div>

            <!-- Detailed Filters Trigger -->
            <form method="GET" action="{{ route('league.matches') }}" class="flex flex-wrap items-center gap-3">
                 <input type="hidden" name="competition_season" value="{{ $activeCompetitionSeason?->id }}">
                 
                 <div class="flex items-center gap-2 bg-slate-900/50 rounded-lg p-1 border border-slate-700/50">
                     <select name="club" class="bg-transparent border-none text-xs text-white font-bold h-8 pl-3 pr-8 focus:ring-0 cursor-pointer" onchange="this.form.submit()">
                        <option value="">Alle Vereine</option>
                        @foreach ($clubFilterOptions as $clubOption)
                            <option value="{{ $clubOption->id }}" @selected((int) ($filters['club'] ?? 0) === (int) $clubOption->id)>
                                {{ $clubOption->name }}
                            </option>
                        @endforeach
                    </select>
                 </div>
            </form>
        </div>

        @if ($activeCompetitionSeason && $matchesByDay->isNotEmpty())
            <div class="space-y-8">
                @foreach ($matchesByDay as $matchday => $matches)
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                             <h2 class="text-xl font-bold text-white tracking-tight">Spieltag {{ $matchday }}</h2>
                             <div class="h-px flex-1 bg-gradient-to-r from-slate-700/50 to-transparent"></div>
                        </div>
                        
                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($matches as $match)
                                <article class="sim-card p-0 overflow-hidden group hover:border-cyan-500/30 transition-all duration-300">
                                    <div class="p-4 bg-slate-900/40 border-b border-slate-700/50 flex justify-between items-center text-xs">
                                        <span class="text-slate-400 font-medium flex items-center gap-1.5">
                                             <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                             {{ $match->kickoff_at?->format('d.m.Y H:i') }}
                                        </span>
                                        @if ($match->status === 'live')
                                             <span class="text-red-400 font-bold animate-pulse flex items-center gap-1">
                                                 <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> LIVE
                                             </span>
                                        @elseif($match->status === 'played')
                                             <span class="text-slate-500 font-bold uppercase tracking-wider">Beendet</span>
                                        @else
                                             <span class="text-slate-500 font-bold uppercase tracking-wider">Geplant</span>
                                        @endif
                                    </div>

                                    <div class="p-5">
                                        <div class="flex items-center justify-between gap-4">
                                            <!-- Home Team -->
                                            <div class="flex flex-col items-center gap-2 flex-1 text-center">
                                                <img src="{{ $match->homeClub->logo_url }}" alt="{{ $match->homeClub->name }}" class="sim-avatar sim-avatar-lg shrink-0">
                                                <span class="text-sm font-bold text-white leading-tight">{{ $match->homeClub->name }}</span>
                                            </div>

                                            <!-- Score / VS -->
                                            <div class="flex flex-col items-center shrink-0 w-20">
                                                @if ($match->status === 'played' || $match->status === 'live')
                                                    <div class="text-2xl font-bold text-white font-mono tracking-widest bg-slate-900/50 px-3 py-1 rounded-lg border border-slate-700/50">
                                                        {{ $match->home_score }} : {{ $match->away_score }}
                                                    </div>
                                                @else
                                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-800/50 px-2 py-1 rounded">VS</span>
                                                @endif
                                            </div>

                                            <!-- Away Team -->
                                            <div class="flex flex-col items-center gap-2 flex-1 text-center">
                                                <img src="{{ $match->awayClub->logo_url }}" alt="{{ $match->awayClub->name }}" class="sim-avatar sim-avatar-lg shrink-0">
                                                <span class="text-sm font-bold text-white leading-tight">{{ $match->awayClub->name }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-4 py-3 border-t border-slate-700/50 bg-slate-900/30 flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        @if (auth()->user()->isAdmin() || $ownedClubIds->contains($match->home_club_id) || $ownedClubIds->contains($match->away_club_id))
                                            @if ($match->status !== 'played')
                                                <a href="{{ route('matches.lineup.edit', ['match' => $match->id]) }}" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 hover:text-cyan-400 transition-colors py-1 px-2">Aufstellung</a>
                                                <div class="w-px h-3 bg-slate-700"></div>
                                            @endif
                                        @endif
                                        
                                        <a href="{{ route('matches.show', $match) }}" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 hover:text-cyan-400 transition-colors py-1 px-2">Matchcenter</a>
                                        
                                        @if (auth()->user()->isAdmin() && $match->status !== 'played')
                                             <div class="w-px h-3 bg-slate-700"></div>
                                             <form method="POST" action="{{ route('matches.simulate', $match) }}">
                                                @csrf
                                                <button type="submit" class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 hover:text-emerald-300 transition-colors py-1 px-2">Sim</button>
                                            </form>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="sim-card p-16 text-center border-dashed border-2 border-slate-700 bg-slate-900/40">
                <div class="flex flex-col items-center justify-center text-slate-500 mb-6">
                    <svg class="w-16 h-16 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <h3 class="text-lg font-bold text-white mb-2">Keine Spiele gefunden</h3>
                    <p class="text-slate-400 max-w-sm mx-auto">Für den gewählten Filterzeitraum sind keine Begegnungen angesetzt.</p>
                </div>

                @if (auth()->user()->isAdmin() && $activeCompetitionSeason)
                    <form method="POST" action="{{ route('admin.competition-seasons.generate-fixtures', $activeCompetitionSeason) }}">
                        @csrf
                        <button class="sim-btn-primary shadow-lg shadow-cyan-500/20" type="submit">Spielplan generieren</button>
                    </form>
                @else
                    <a href="{{ route('league.matches', ['competition_season' => $activeCompetitionSeason?->id]) }}" class="sim-btn-muted">Filter zurücksetzen</a>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
