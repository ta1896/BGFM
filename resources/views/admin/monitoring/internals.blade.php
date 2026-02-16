<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title text-orange-400">System Monitoring</p>
                <h1 class="mt-1 text-2xl font-bold text-white">System-Internals</h1>
                <p class="mt-2 text-sm text-slate-300">Konfiguration und Ressourcen-Management.</p>
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
                    class="flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 border border-slate-700 transition text-sm">
                    <span>⏳</span> Scheduler
                </a>
                <a href="{{ route('admin.monitoring.internals') }}"
                    class="flex items-center gap-2 px-6 py-3 bg-orange-600 text-white rounded-xl shadow-lg shadow-orange-500/20 font-bold transition text-sm">
                    <span>⚙️</span> Internals
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- System Info -->
                <div class="sim-card p-6">
                    <h3
                        class="text-sm font-black text-white uppercase tracking-widest mb-6 pb-2 border-b border-white/5">
                        Umgebungs-Informationen</h3>
                    <div class="space-y-4">
                        <div
                            class="flex justify-between items-center bg-slate-900/40 p-3 rounded-xl border border-slate-800">
                            <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">PHP Version</span>
                            <span
                                class="font-mono text-xs text-white bg-slate-800 px-3 py-1 rounded-lg">{{ $stats['php_version'] }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center bg-slate-900/40 p-3 rounded-xl border border-slate-800">
                            <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">Laravel</span>
                            <span
                                class="font-mono text-xs text-white bg-slate-800 px-3 py-1 rounded-lg">v{{ $stats['laravel_version'] }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center bg-slate-900/40 p-3 rounded-xl border border-slate-800">
                            <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">Cache Driver</span>
                            <span
                                class="font-mono text-[10px] text-cyan-400 bg-cyan-400/10 px-3 py-1 rounded-lg border border-cyan-400/20 uppercase font-black">{{ $stats['cache_driver'] }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center bg-slate-900/40 p-3 rounded-xl border border-slate-800">
                            <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">DB
                                Connection</span>
                            <span
                                class="font-mono text-[10px] text-emerald-400 bg-emerald-400/10 px-3 py-1 rounded-lg border border-emerald-400/20 uppercase font-black">{{ $stats['db_connection'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Storage & Logs -->
                <div class="sim-card p-6">
                    <h3
                        class="text-sm font-black text-white uppercase tracking-widest mb-6 pb-2 border-b border-white/5">
                        Speicher & Dateisystem</h3>
                    <div class="space-y-4">
                        <div
                            class="flex justify-between items-center bg-slate-900/40 p-3 rounded-xl border border-slate-800">
                            <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">App Storage</span>
                            <span
                                class="font-mono text-xs text-white">{{ number_format($stats['storage_size'] / 1024 / 1024, 2) }}
                                MB</span>
                        </div>
                        <div
                            class="flex justify-between items-center bg-slate-900/40 p-3 rounded-xl border border-slate-800">
                            <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">Log Datei
                                (larall.log)</span>
                            <span
                                class="font-mono text-xs text-indigo-400">{{ number_format($stats['log_size'] / 1024, 2) }}
                                KB</span>
                        </div>
                        <div class="pt-6 border-t border-white/5 flex gap-3">
                            <form action="{{ route('admin.monitoring.logs.clear') }}" method="POST"
                                onsubmit="return confirm('Sicher?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="px-4 py-2.5 bg-red-600/20 text-red-400 border border-red-500/30 rounded-xl hover:bg-red-500 text-[10px] font-black uppercase tracking-widest transition">Logs
                                    leeren</button>
                            </form>
                            <a href="{{ route('admin.monitoring.logs') }}"
                                class="px-4 py-2.5 bg-slate-800 text-slate-300 border border-slate-700 rounded-xl hover:bg-slate-700 text-[10px] font-black uppercase tracking-widest transition">Details</a>
                        </div>
                    </div>
                </div>

                <!-- Cache Management -->
                <div class="sim-card p-8 md:col-span-2 bg-gradient-to-br from-slate-900/50 to-orange-950/20">
                    <h3 class="text-lg font-black text-white mb-2 tracking-tight">System-Reset / Cache-Management</h3>
                    <p class="text-xs text-slate-500 mb-8 font-medium italic">Vorsicht: Das Leeren des Caches löscht
                        alle zwischengespeicherten Diagnostik-Berichte.</p>

                    <form action="{{ route('admin.monitoring.clear-cache') }}" method="POST"
                        onsubmit="return confirm('Wirklich den gesamten Cache leeren?')">
                        @csrf
                        <button type="submit"
                            class="w-full py-5 bg-orange-600 text-white font-black rounded-2xl hover:bg-orange-500 transition shadow-xl shadow-orange-500/20 uppercase tracking-widest text-sm">
                            System-Cache Flush (Global)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>