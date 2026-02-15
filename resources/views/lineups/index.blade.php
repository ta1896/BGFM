<x-app-layout>
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Matchcenter</p>
                <div class="flex items-center gap-4">
                    <h1 class="text-3xl font-bold text-white tracking-tight">Aufstellungen & Taktik</h1>
                    <!-- Global Switcher in Header -->
                </div>
            </div>
            <!-- Action to create a template (detached from match) -->
            <a href="{{ route('lineups.create') }}" class="sim-btn-muted py-2 px-4">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Neue Vorlage erstellen
                </span>
            </a>
        </div>

        <!-- 1. Upcoming Matches Section -->
        <div>
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Anstehende Spiele
            </h2>

            @if ($matches->isEmpty())
                <div class="sim-card p-8 text-center border-dashed border-2 border-slate-700 bg-slate-900/40">
                    <p class="text-slate-400">Keine anstehenden Spiele gefunden.</p>
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($matches as $match)
                        @php
                            $opponent = $match->home_club_id === $club->id ? $match->awayClub : $match->homeClub;
                            $userLineup = $match->lineups->first(); // Eager loaded and filtered by controller
                            $isHome = $match->home_club_id === $club->id;
                        @endphp

                        <article
                            class="sim-card group relative overflow-hidden transition-all duration-300 hover:border-cyan-500/30 hover:shadow-lg hover:shadow-cyan-500/10">
                            <!-- Background Pattern -->
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-slate-800/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                            </div>

                            <div class="p-5 flex flex-col h-full relative z-10">
                                <!-- Match Meta -->
                                <div
                                    class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">
                                    <span>{{ $match->kickoff_at->format('d.m.Y • H:i') }}</span>
                                    <span>{{ $match->match_type ?? 'Liga' }}</span>
                                </div>

                                <!-- Matchup -->
                                <div class="flex items-center justify-between gap-4 mb-6">
                                    <!-- User Club (Always Left mostly? Or stick to Home/Away logic? Stick to Home/Away for realism) -->
                                    <div class="flex flex-col items-center gap-2 flex-1">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-slate-800 p-2 border border-slate-700 flex items-center justify-center relative">
                                            <img src="{{ $match->homeClub->logo_url }}" class="w-full h-full object-contain">
                                            @if($isHome)
                                                <div class="absolute -top-1 -right-1 w-3 h-3 bg-cyan-500 rounded-full border-2 border-slate-800"
                                            title="Dein Team"></div> @endif
                                        </div>
                                        <span
                                            class="text-xs font-bold text-white text-center leading-tight truncate w-full">{{ $match->homeClub->short_name }}</span>
                                    </div>

                                    <div class="text-lg font-bold text-slate-600">VS</div>

                                    <div class="flex flex-col items-center gap-2 flex-1">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-slate-800 p-2 border border-slate-700 flex items-center justify-center relative">
                                            <img src="{{ $match->awayClub->logo_url }}" class="w-full h-full object-contain">
                                            @if(!$isHome)
                                                <div class="absolute -top-1 -right-1 w-3 h-3 bg-cyan-500 rounded-full border-2 border-slate-800"
                                            title="Dein Team"></div> @endif
                                        </div>
                                        <span
                                            class="text-xs font-bold text-white text-center leading-tight truncate w-full">{{ $match->awayClub->short_name }}</span>
                                    </div>
                                </div>

                                <!-- Status & Action -->
                                <div class="mt-auto pt-4 border-t border-slate-700/50 flex items-center justify-between gap-3">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold uppercase text-slate-500">Status</span>
                                        @if($userLineup)
                                            <span class="text-xs font-bold text-emerald-400 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                                Gesetzt
                                            </span>
                                        @else
                                            <span class="text-xs font-bold text-amber-500 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Ausstehend
                                            </span>
                                        @endif
                                    </div>

                                    <a href="{{ route('lineups.match', ['match' => $match->id]) }}"
                                       class="sim-btn-primary text-xs py-1.5 px-3">
                                        {{ $userLineup ? 'Bearbeiten' : 'Erstellen' }}
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 2. Templates Section -->
        <div class="pt-8 border-t border-slate-800">
            <h2 class="text-xl font-bold text-slate-400 mb-4 flex items-center gap-2 opacity-80">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                    </path>
                </svg>
                Gespeicherte Vorlagen
            </h2>

            @if ($templates->isEmpty())
                <div class="flex items-center gap-3 text-slate-500 text-sm">
                    <span>Keine Vorlagen vorhanden.</span>
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-4">
                    @foreach ($templates as $template)
                        <article
                            class="bg-slate-900 border border-slate-800 rounded-lg p-4 hover:border-slate-600 transition group">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="font-bold text-white truncate group-hover:text-cyan-400 transition-colors">
                                    {{ $template->name }}</h3>
                                <div class="bg-slate-800 text-[10px] font-mono px-1.5 py-0.5 rounded text-slate-400">
                                    {{ $template->formation }}</div>
                            </div>
                            <div class="text-xs text-slate-500 mb-4">
                                {{ $template->players->count() }} Spieler zugewiesen
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ route('lineups.edit', $template) }}"
                                    class="text-xs font-bold text-slate-400 hover:text-white transition">Bearbeiten</a>
                                <form method="POST" action="{{ route('lineups.destroy', $template) }}"
                                    onsubmit="return confirm('Vorlage wirklich löschen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-xs font-bold text-rose-500 hover:text-rose-400 transition">Löschen</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>