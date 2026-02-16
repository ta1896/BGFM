<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title text-cyan-400">System Monitoring</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Match-Analyse</h1>
                <p class="mt-2 text-sm text-slate-300">Detailansicht der Simulations-Parameter.</p>
            </div>
            <a href="{{ route('admin.monitoring.index') }}" class="sim-btn-muted">Zur Übersicht</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Sub Navigation -->
            <div class="flex flex-wrap gap-4 mb-2">
                <a href="{{ route('admin.monitoring.index') }}" class="flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 border border-slate-700 transition text-sm">
                    <span>🏠</span> Übersicht
                </a>
                <a href="{{ route('admin.monitoring.analysis') }}" class="flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-500/20 font-bold transition text-sm">
                    <span>🔍</span> Match-Analyse
                </a>
                <a href="{{ route('admin.monitoring.lab') }}" class="flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 border border-slate-700 transition text-sm">
                    <span>🧪</span> Match Lab
                </a>
                <a href="{{ route('admin.monitoring.scheduler') }}" class="flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 border border-slate-700 transition text-sm">
                    <span>⏳</span> Scheduler
                </a>
                <a href="{{ route('admin.monitoring.internals') }}" class="flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 border border-slate-700 transition text-sm">
                    <span>⚙️</span> Internals
                </a>
            </div>

            <div class="sim-card p-6">
                <form action="{{ route('admin.monitoring.analysis') }}" method="GET" class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 tracking-widest">Match ID suchen</label>
                        <input type="number" name="match_id" value="{{ request('match_id') }}" 
                            class="w-full bg-slate-900/50 border-slate-700 rounded-xl text-white focus:ring-cyan-500/50 focus:border-cyan-500" 
                            placeholder="z.B. 546">
                    </div>
                    <button type="submit" class="px-8 py-2.5 bg-cyan-600 text-white rounded-xl font-bold hover:bg-cyan-500 transition shadow-lg shadow-cyan-500/20">Analysieren</button>
                    @if($match)
                        <a href="{{ route('admin.monitoring.analysis') }}" class="px-4 py-2.5 bg-slate-800 text-slate-300 rounded-xl text-sm hover:bg-slate-700 transition border border-slate-700">Leeren</a>
                    @endif
                </form>
            </div>

            @if (session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-sm font-bold animate-fade-in shadow-lg">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if($match)
                <!-- Diagnostics Toolbar -->
                @if(count($matchDiagnostics) > 0)
                    <div class="sim-card p-6 border-l-4 border-l-red-500 bg-red-500/5">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="p-2 bg-red-500/20 rounded-lg text-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-white">Match-Probleme erkannt</h3>
                                <p class="text-xs text-slate-400 italic">Targeted diagnostics found {{ count($matchDiagnostics) }} issues with this entry.</p>
                            </div>
                        </div>
                        <div class="grid gap-3">
                            @foreach($matchDiagnostics as $diag)
                                <div class="flex items-center justify-between p-4 bg-slate-900/60 rounded-xl border border-white/5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-2 w-2 rounded-full {{ $diag['severity'] === 'CRITICAL' ? 'bg-red-500' : 'bg-orange-500' }}"></span>
                                        <p class="text-sm text-slate-200 font-medium">{{ $diag['description'] }}</p>
                                    </div>
                                    <form action="{{ route('admin.monitoring.repair') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="type" value="{{ $diag['action_type'] }}">
                                        <input type="hidden" name="id" value="{{ $match->id }}">
                                        <button type="submit" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-black uppercase tracking-widest rounded-lg border border-slate-700 transition">
                                            {{ $diag['action_label'] }}
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Basic Info -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="sim-card p-6 border-l-4 border-l-cyan-500">
                            <h3 class="text-lg font-bold text-white mb-6">Spielergebnis</h3>
                            <div class="flex justify-between items-center bg-slate-950/40 p-5 rounded-2xl mb-6 border border-slate-800">
                                <div class="text-center w-5/12">
                                    <div class="text-[10px] text-slate-500 uppercase font-black mb-2 tracking-widest">Heim</div>
                                    <div class="font-bold text-sm text-white truncate">{{ $match->homeClub->name }}</div>
                                </div>
                                <div class="text-3xl font-black text-white px-2">{{ $match->home_score }} : {{ $match->away_score }}</div>
                                <div class="text-center w-5/12">
                                    <div class="text-[10px] text-slate-500 uppercase font-black mb-2 tracking-widest">Gast</div>
                                    <div class="font-bold text-sm text-white truncate">{{ $match->awayClub->name }}</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
                                    <span class="text-slate-500 block mb-1 uppercase tracking-widest font-bold">Status</span> 
                                    <span class="font-black text-cyan-400 uppercase">{{ $match->status }}</span>
                                </div>
                                <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
                                    <span class="text-slate-500 block mb-1 uppercase tracking-widest font-bold">Minute</span> 
                                    <span class="font-black text-white">{{ $match->live_minute }}'</span>
                                </div>
                            </div>
                        </div>

                        <div class="sim-card p-6 text-center">
                             <form action="{{ route('matches.simulate', $match) }}" method="POST">
                                 @csrf
                                 <button type="submit" class="w-full py-4 bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 font-black rounded-xl hover:bg-indigo-600 hover:text-white transition uppercase tracking-widest text-xs">
                                    Match Re-Simulieren
                                 </button>
                             </form>
                             <p class="text-[8px] text-slate-600 mt-2 uppercase font-black">Vorsicht: Überschreibt alle aktuellen Daten</p>
                        </div>

                        <div class="sim-card p-6">
                            <h3 class="text-lg font-bold text-white mb-6">Simulation Insights</h3>
                            <div class="space-y-6">
                                <div>
                                    <div class="flex justify-between text-xs mb-2">
                                        <span class="text-slate-400 uppercase font-black tracking-tighter">Events</span>
                                        <span class="font-bold text-cyan-400">{{ $match->events->count() }}</span>
                                    </div>
                                    <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                                        <div class="bg-cyan-500 h-full shadow-[0_0_8px_rgba(6,182,212,0.5)]" style="width: {{ min(100, $match->events->count() * 4) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-2">
                                        <span class="text-slate-400 uppercase font-black tracking-tighter">Live Actions</span>
                                        <span class="font-bold text-indigo-400">{{ $match->liveActions->count() }}</span>
                                    </div>
                                    <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                                        <div class="bg-indigo-500 h-full shadow-[0_0_8px_rgba(99,102,241,0.5)]" style="width: {{ min(100, $match->liveActions->count() / 1.5) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline & Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="sim-card overflow-hidden">
                            <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-white">Event Timeline (Detail)</h3>
                                <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Expert Mode Active</div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="bg-slate-950/20 text-slate-500 uppercase text-[10px] tracking-widest">
                                            <th class="py-4 px-6">Min</th>
                                            <th class="py-4 px-6">Team</th>
                                            <th class="py-4 px-6">Typ</th>
                                            <th class="py-4 px-6 text-center">Outcome</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800">
                                        @forelse($match->liveActions->sortBy('minute') as $action)
                                            <tr class="hover:bg-white/5 transition-colors">
                                                <td class="py-4 px-6 font-mono text-slate-400">{{ $action->minute }}'</td>
                                                <td class="py-4 px-6">
                                                    @if($action->club_id == $match->home_club_id)
                                                        <span class="text-cyan-400 font-black text-[10px] tracking-widest uppercase">Heim</span>
                                                    @else
                                                        <span class="text-indigo-400 font-black text-[10px] tracking-widest uppercase">Gast</span>
                                                    @endif
                                                </td>
                                                <td class="py-4 px-6 text-slate-200 capitalize text-xs font-medium">{{ str_replace('_', ' ', $action->action_type) }}</td>
                                                <td class="py-4 px-6 text-center">
                                                    @if($action->outcome === 'success')
                                                        <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[9px] font-black uppercase">Erfolg</span>
                                                    @elseif($action->outcome === 'fail')
                                                        <span class="px-2 py-0.5 bg-red-500/10 text-red-400 border border-red-500/20 rounded-full text-[9px] font-black uppercase">Fehler</span>
                                                    @else
                                                        <span class="px-2 py-0.5 bg-slate-700/50 text-slate-400 rounded-full text-[9px] font-black uppercase group-hover:bg-slate-600 transition">{{ $action->outcome ?? '-' }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-20 text-center">
                                                    <div class="text-4xl mb-4 opacity-10">🚫</div>
                                                    <p class="text-slate-500 italic text-xs uppercase tracking-widest font-bold">Keine Live Actions für dieses Match in DB vorhanden.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="sim-card p-20 text-center border-2 border-dashed border-slate-800">
                    <div class="text-6xl mb-6 opacity-20 transform -rotate-12">🔍</div>
                    <p class="text-slate-400 font-medium max-w-sm mx-auto">Geben Sie oben eine Match ID ein, um das Simulationsprotokoll zu diagnostizieren und Fehler zu beheben.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
