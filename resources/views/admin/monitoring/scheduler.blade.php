<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title text-indigo-400">System Monitoring</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Scheduler-Status</h1>
                <p class="mt-2 text-sm text-slate-300">Tracking der automatisierten Hintergrund-Tasks.</p>
            </div>
            <a href="{{ route('admin.monitoring.index') }}" class="sim-btn-muted">Zur Übersicht</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Sub Navigation -->
            <div class="flex flex-wrap gap-4 mb-2">
                <a href="{{ route('admin.monitoring.index') }}"
                    class="flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 border border-slate-700 transition text-sm">
                    <span>🏠</span> Übersicht
                </a>
                <a href="{{ route('admin.monitoring.analysis') }}"
                    class="flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 border border-slate-700 transition text-sm">
                    <span>🔍</span> Match-Analyse
                </a>
                <a href="{{ route('admin.monitoring.lab') }}"
                    class="flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 border border-slate-700 transition text-sm">
                    <span>🧪</span> Match Lab
                </a>
                <a href="{{ route('admin.monitoring.scheduler') }}"
                    class="flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-500/20 font-bold transition text-sm">
                    <span>⏳</span> Scheduler
                </a>
                <a href="{{ route('admin.monitoring.internals') }}"
                    class="flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 border border-slate-700 transition text-sm">
                    <span>⚙️</span> Internals
                </a>
            </div>

            <div class="sim-card overflow-hidden">
                <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Letzte 20 Simulation-Runs</h3>
                    <div class="text-[10px] text-slate-500 font-black uppercase tracking-widest">Live Refreshed:
                        {{ now()->format('H:i:s') }}</div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-slate-950/20 text-slate-500 uppercase text-[10px] tracking-widest font-black">
                                <th class="py-4 px-6">Run ID</th>
                                <th class="py-4 px-6">Startzeit</th>
                                <th class="py-4 px-6">Endzeit</th>
                                <th class="py-4 px-6 text-center">Matches</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6 text-right">Dauer</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($runs as $run)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="py-4 px-6 font-mono text-slate-500 text-xs">#{{ $run->id }}</td>
                                    <td class="py-4 px-6 text-slate-300 text-xs">{{ $run->started_at }}</td>
                                    <td class="py-4 px-6 text-slate-400 text-xs">{{ $run->finished_at ?? '-' }}</td>
                                    <td class="py-4 px-6 text-center">
                                        <span
                                            class="font-black text-cyan-400 text-sm">{{ $run->matches_processed ?? 0 }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($run->finished_at)
                                            <span
                                                class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[9px] font-black uppercase">Success</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full text-[9px] font-black uppercase animate-pulse">Running</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right font-mono text-indigo-400 text-xs font-bold">
                                        @if($run->finished_at)
                                            {{ \Carbon\Carbon::parse($run->started_at)->diffInSeconds($run->finished_at) }}s
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-500 italic font-medium">Noch keine
                                        Scheduler-Runs aufgezeichnet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Stats Helper -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="sim-card p-6 border-l-4 border-l-cyan-500">
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Total Processed
                    </div>
                    <div class="text-4xl font-black text-white tracking-tight">{{ $runs->sum('matches_processed') }}
                    </div>
                    <div class="text-[10px] mt-4 font-bold text-slate-600 uppercase tracking-tight italic">Gesamte
                        simulierte Spiele via Scheduler</div>
                </div>
                <div class="sim-card p-6 border-l-4 border-l-indigo-500">
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Avg. Duration
                    </div>
                    <div class="text-4xl font-black text-white tracking-tight">
                        {{ $runs->count() ? round($runs->avg(fn($r) => $r->finished_at ? \Carbon\Carbon::parse($r->started_at)->diffInSeconds($r->finished_at) : 0), 1) : 0 }}s
                    </div>
                    <div class="text-[10px] mt-4 font-bold text-slate-600 uppercase tracking-tight italic">
                        Durchschnittliche Simulationsdauer</div>
                </div>
                <div class="sim-card p-6 border-l-4 border-l-emerald-500">
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Success Rate</div>
                    <div class="text-4xl font-black text-white tracking-tight">
                        {{ $runs->count() ? round(($runs->whereNotNull('finished_at')->count() / $runs->count()) * 100) : 0 }}%
                    </div>
                    <div class="text-[10px] mt-4 font-bold text-slate-600 uppercase tracking-tight italic">Verhältnis
                        abgeschlossener Simulationen</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>