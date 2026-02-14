<x-app-layout>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Taktiktafel</p>
                <h1 class="text-3xl font-bold text-white tracking-tight">Aufstellungen</h1>
            </div>
            <a href="{{ route('lineups.create') }}" class="sim-btn-primary py-2 px-4 shadow-lg shadow-cyan-500/20">
                <span class="flex items-center gap-2">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                     Neue Aufstellung
                </span>
            </a>
        </div>

        @if ($lineups->isEmpty())
             <div class="sim-card p-12 text-center border-dashed border-2 border-slate-700 bg-slate-900/40">
                <div class="flex flex-col items-center justify-center text-slate-500">
                    <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center mb-4 text-slate-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Keine Aufstellungen vorhanden</h3>
                    <p class="text-slate-400 max-w-sm mx-auto mb-6">Erstelle deine erste Aufstellung, um deine Mannschaft strategisch auf das nächste Spiel vorzubereiten.</p>
                    
                    @if ($clubs->isNotEmpty())
                        <a href="{{ route('lineups.create') }}" class="sim-btn-primary">Erste Aufstellung erstellen</a>
                    @else
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.clubs.create') }}" class="sim-btn-muted">Verein im ACP erstellen</a>
                        @else
                            <a href="{{ route('clubs.free') }}" class="sim-btn-muted">Freie Vereine anzeigen</a>
                        @endif
                    @endif
                </div>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($lineups as $lineup)
                    <article class="sim-card group relative overflow-hidden transition-all duration-300 hover:border-cyan-500/30 hover:shadow-lg hover:shadow-cyan-500/10">
                         <!-- Background Pattern -->
                        <div class="absolute inset-0 bg-gradient-to-br from-slate-800/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

                        <div class="p-6 relative z-10 flex flex-col h-full">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-800 p-2 border border-slate-700 flex items-center justify-center shrink-0">
                                         <img src="{{ $lineup->club->logo_url }}" alt="{{ $lineup->club->name }}" class="max-w-full max-h-full">
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-white group-hover:text-cyan-400 transition-colors line-clamp-1">{{ $lineup->name }}</h2>
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $lineup->club->name }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0 flex flex-col items-end gap-1">
                                    @if ($lineup->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            Aktiv
                                        </span>
                                    @else
                                         <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">
                                            Inaktiv
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mb-6">
                                <div class="bg-slate-900/50 rounded-lg p-3 border border-slate-700/50">
                                    <p class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1">Formation</p>
                                    <p class="text-white font-mono font-medium">{{ $lineup->formation }}</p>
                                </div>
                                <div class="bg-slate-900/50 rounded-lg p-3 border border-slate-700/50">
                                    <p class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1">Spieler</p>
                                    <p class="text-white font-mono font-medium">{{ $lineup->players->count() }} / 11</p>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-slate-700/50 flex items-center justify-between gap-2">
                                <a class="sim-btn-muted text-xs flex-1 justify-center" href="{{ route('lineups.show', $lineup) }}">Details</a>
                                <a class="sim-btn-muted text-xs flex-1 justify-center" href="{{ route('lineups.edit', $lineup) }}">Editieren</a>
                                @unless ($lineup->is_active)
                                    <form method="POST" action="{{ route('lineups.activate', $lineup) }}">
                                        @csrf
                                        <button type="submit" class="sim-btn-primary bg-transparent border-cyan-500/50 text-cyan-400 hover:bg-cyan-500/10 text-xs px-3 py-2 h-full rounded-lg transition-colors" title="Aktivieren">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
