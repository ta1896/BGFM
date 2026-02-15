<x-app-layout>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Team Management</p>
                <h1 class="text-3xl font-bold text-white tracking-tight">Kaderübersicht</h1>
            </div>
            
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('players.index') }}" class="flex items-center">
                    <div class="relative group">
                        <select name="club" class="sim-input pl-4 pr-10 py-2 text-sm bg-slate-900/50 border-slate-950 focus:border-cyan-500 rounded-lg appearance-none cursor-pointer min-w-[200px]" onchange="this.form.submit()">
                            <option value="">Alle Vereine</option>
                            @foreach ($clubs as $c)
                                <option value="{{ $c->id }}" @selected($activeClubId == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400 group-hover:text-cyan-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </form>
                
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.players.create') }}" class="sim-btn-primary text-sm py-2 px-4 shadow-lg shadow-cyan-500/20">
                        <span class="flex items-center gap-2">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                             Spieler erstellen
                        </span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Squad Summary -->
        <div class="sim-card p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="sim-section-title flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Kader-Analyse
                </p>
                <span class="text-xs font-bold bg-slate-800 px-2 py-1 rounded text-slate-300 border border-slate-950">
                    Gesamt: {{ $squadStats['count'] }} Spieler
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                    <p class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Verfuegbarkeit</p>
                    <div class="flex items-center gap-3 mt-1">
                         <div class="flex items-center gap-1.5 text-amber-400 font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            {{ $squadStats['injured_count'] }} verletzt
                         </div>
                         <div class="h-4 w-px bg-slate-700"></div>
                         <div class="flex items-center gap-1.5 text-rose-400 font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            {{ $squadStats['suspended_count'] }} gesperrt
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Player List by Group -->
        <div class="space-y-6">
            @forelse ($groupedPlayers as $positionGroup => $groupPlayers)
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
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach ($groupPlayers as $player)
                                <div class="sim-card-soft p-3 hover:border-cyan-500/30 transition-all group relative overflow-hidden flex flex-col gap-3">
                                    <div class="flex items-center gap-3 relative z-10">
                                        <div class="relative shrink-0">
                                            @if($player->photo_url)
                                                <img src="{{ $player->photo_url }}" class="h-10 w-10 md:h-12 md:w-12 rounded-full object-cover ring-2 ring-slate-950 bg-slate-800" alt="{{ $player->full_name }}">
                                            @else
                                                <div class="h-10 w-10 md:h-12 md:w-12 rounded-full bg-slate-800 flex items-center justify-center border border-slate-950">
                                                    <span class="text-xs font-bold text-slate-500">{{ substr($player->first_name, 0, 1) }}{{ substr($player->last_name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('players.show', $player) }}" class="font-bold text-white truncate group-hover:text-cyan-400 transition-colors block text-sm">
                                                {{ $player->full_name }}
                                            </a>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-xs font-bold text-cyan-400 bg-cyan-950/30 px-1.5 py-0.5 rounded border border-cyan-500/20">{{ $player->display_position }}</span>
                                                @if($player->club)
                                                    <span class="text-[10px] text-slate-500 truncate">{{ $player->club->short_name ?: $player->club->name }}</span>
                                                @else
                                                    <span class="text-[10px] text-slate-500 italic">Vereinslos</span>
                                                @endif
                                                <span class="text-xs text-slate-400 ml-auto">{{ $player->age }}J</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-end justify-between relative z-10 pt-2 border-t border-slate-950/50">
                                        <div>
                                            <p class="text-[10px] font-bold uppercase text-slate-500 mb-1">Staerke</p>
                                            <div class="flex items-end gap-1">
                                                <span class="text-lg font-bold text-white leading-none {{ $player->overall >= 80 ? 'text-emerald-400' : ($player->overall >= 70 ? 'text-cyan-400' : 'text-white') }}">{{ $player->overall }}</span>
                                                <span class="text-[10px] text-slate-500 leading-none mb-0.5">OVR</span>
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
            @empty
                <div class="sim-card p-12 text-center">
                    <div class="flex flex-col items-center justify-center text-slate-500">
                        <svg class="w-12 h-12 mb-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <p class="text-lg font-medium text-white">Keine Spieler gefunden</p>
                        <p class="text-sm">In diesem Kader befinden sich aktuell keine Spieler.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
