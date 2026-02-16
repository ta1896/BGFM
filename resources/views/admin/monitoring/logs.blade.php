<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title text-cyan-400">System Logs</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Vollstaendige Log-Uebersicht</h1>
                <p class="mt-2 text-sm text-slate-300">Anzeige der letzten 200 Eintraege aus der laravel.log Datei.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.monitoring.index') }}" class="sim-btn-muted">Zurueck zum Monitoring</a>
            </div>
        </div>
    </x-slot>

    <section class="mt-6">
        <article class="sim-card overflow-hidden">
            <div class="p-5 border-b border-white/5 bg-slate-800/20">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-500"></span>
                            <span class="text-xs text-white">Error</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                            <span class="text-xs text-white">Warning</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-slate-500"></span>
                            <span class="text-xs text-white">Info</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="h-[700px] overflow-y-auto bg-black/40 font-mono text-xs">
                @forelse($logs as $log)
                            <div class="px-5 py-4 border-b border-white/5 hover:bg-white/5 transition-colors">
                                <div class="flex items-start gap-4">
                                    <span class="text-slate-500 shrink-0 select-none">{{ $log['timestamp'] }}</span>
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tighter shrink-0
                                            {{ $log['level'] === 'ERROR' || $log['level'] === 'CRITICAL' ? 'bg-red-500/20 text-red-400 border border-red-500/20' :
                    ($log['level'] === 'WARNING' ? 'bg-orange-500/20 text-orange-400 border border-orange-500/20' : 'bg-slate-700/50 text-slate-300 border border-slate-700') }}">
                                        {{ $log['level'] }}
                                    </span>
                                    <div class="flex-1 overflow-hidden">
                                        <p class="text-slate-200 break-words leading-relaxed">{{ $log['message'] }}</p>
                                        @if($log['context'])
                                            <details class="mt-2 group">
                                                <summary
                                                    class="text-[10px] text-cyan-400 uppercase tracking-widest font-bold cursor-pointer hover:text-cyan-300 select-none list-none flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-3 w-3 transition-transform group-open:rotate-180" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                    Context anzeigen
                                                </summary>
                                                <div
                                                    class="mt-2 p-3 bg-black/60 rounded border border-white/5 text-[10px] text-slate-400 whitespace-pre-wrap leading-tight max-h-96 overflow-y-auto">
                                                    {{ $log['context'] }}
                                                </div>
                                            </details>
                                        @endif
                                    </div>
                                </div>
                            </div>
                @empty
                    <div class="p-20 text-center text-slate-500 italic">
                        Keine Log-Eintraege vorhanden.
                    </div>
                @endforelse
            </div>
        </article>
    </section>
</x-app-layout>