<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title text-cyan-400">System Monitoring</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Debug & Diagnostic Center</h1>
                <p class="mt-2 text-sm text-slate-300">Uebersicht ueber Systemgesundheit und Fehlermeldungen.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="sim-btn-muted">Zurueck zum Dashboard</a>
                <form action="{{ route('admin.monitoring.logs.clear') }}" method="POST"
                    onsubmit="return confirm('Wirklich alle Logs loeschen?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="sim-btn-muted border-red-500/50 text-red-400 hover:bg-red-500/10">Logs
                        leeren</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Sub Navigation -->
            <div class="flex flex-wrap gap-4 mb-2">
                <a href="{{ route('admin.monitoring.index') }}"
                    class="flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-500/20 font-bold transition text-sm">
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
                    class="flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 border border-slate-700 transition text-sm">
                    <span>⚙️</span> Internals
                </a>
                <a href="{{ route('admin.monitoring.logs') }}"
                    class="flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 border border-slate-700 transition text-sm">
                    <span>📜</span> Logs
                </a>
            </div>

            @if (session('success'))
                <div
                    class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-sm font-bold animate-fade-in">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <!-- Health Cards -->
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <!-- Live Watchdog -->
                <article
                    class="sim-card p-5 border-l-4 {{ $liveStatus['stalled_matches'] > 0 ? 'border-l-red-500' : 'border-l-blue-500' }}">
                    <div class="flex items-center justify-between mb-2">
                        <p class="sim-section-title">Live Watchdog</p>
                        @if($liveStatus['active_matches'] > 0)
                            <span
                                class="flex h-2 w-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.8)] animate-pulse"></span>
                        @endif
                    </div>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-xl font-bold text-white">{{ $liveStatus['active_matches'] }} Aktiv</p>
                            <p class="text-xs text-slate-400 mt-1">{{ $liveStatus['stalled_matches'] }} Hängend</p>
                        </div>
                        @if($liveStatus['stalled_matches'] > 0)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500/20" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>

                    @if($liveStatus['stalled_matches'] > 0)
                        <div class="mt-3 space-y-1">
                            @foreach($liveStatus['matches']->where('is_stalled', true) as $match)
                                <div
                                    class="flex items-center justify-between bg-red-500/10 p-2 rounded border border-red-500/20">
                                    <span class="text-[10px] font-bold text-red-300">{{ $match['home'] }} vs
                                        {{ $match['away'] }} ({{ $match['minute'] }}')</span>
                                    <form action="{{ route('admin.monitoring.repair') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="type" value="match_stuck">
                                        <input type="hidden" name="id" value="{{ $match['id'] }}">
                                        <button type="submit"
                                            class="text-[10px] bg-red-500 hover:bg-red-600 text-white px-2 py-0.5 rounded transition-colors">
                                            FIX
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>

                <!-- DB Status -->
                <article
                    class="sim-card p-5 border-l-4 {{ $health['database'] ? 'border-l-emerald-500' : 'border-l-red-500' }}">
                    <div class="flex items-center justify-between mb-2">
                        <p class="sim-section-title">Datenbank</p>
                        @if($health['database'])
                            <span
                                class="flex h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                        @else
                            <span
                                class="flex h-2 w-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.8)]"></span>
                        @endif
                    </div>
                    <p class="text-xl font-bold text-white">{{ $health['database'] ? 'Bereit' : 'Verbindungsfehler' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">PDO Connection: ACTIVE</p>
                </article>

                <!-- Storage Status -->
                <article
                    class="sim-card p-5 border-l-4 {{ $health['storage']['percentage'] < 90 ? 'border-l-cyan-500' : 'border-l-orange-500' }}">
                    <p class="sim-section-title mb-2">Festplatte</p>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-xl font-bold text-white">{{ $health['storage']['free'] }} frei</p>
                            <p class="text-xs text-slate-400 mt-1">Von {{ $health['storage']['total'] }}</p>
                        </div>
                        <p class="text-2xl font-bold text-cyan-300">{{ $health['storage']['percentage'] }}%</p>
                    </div>
                    <div class="w-full bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                        <div class="bg-cyan-400 h-full rounded-full"
                            style="width: {{ $health['storage']['percentage'] }}%"></div>
                    </div>
                </article>

                <!-- Log Stats -->
                <article
                    class="sim-card p-5 border-l-4 {{ $logStats['errors'] > 0 ? 'border-l-red-500' : 'border-l-emerald-500' }}">
                    <p class="sim-section-title mb-2">Fehlerrate (24h)</p>
                    <div class="flex items-center justify-between">
                        <p
                            class="text-3xl font-bold {{ $logStats['errors'] > 0 ? 'text-red-400' : 'text-emerald-400' }}">
                            {{ $logStats['errors'] }}</p>
                        <div class="text-right text-xs text-slate-400">
                            <p>Gesamt: {{ $logStats['total'] }}</p>
                            <p>Warnungen: {{ $logStats['warnings'] }}</p>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-2 uppercase tracking-wider">Letzter Error:
                        {{ $logStats['latest_error'] ?? 'Keiner' }}</p>
                </article>

                <!-- System Info -->
                <article class="sim-card p-5 border-l-4 border-l-indigo-500">
                    <p class="sim-section-title mb-2">System Info</p>
                    <div class="space-y-1">
                        <p class="text-xs text-white">PHP: <span
                                class="text-indigo-300 font-mono">{{ $health['php_version'] }}</span></p>
                        <p class="text-xs text-white">Laravel: <span
                                class="text-indigo-300 font-mono">{{ $health['laravel_version'] }}</span></p>
                        <p class="text-xs text-white">Env: <span
                                class="text-indigo-300 font-mono uppercase">{{ $health['environment'] }}</span></p>
                        <p class="text-xs text-white">Debug: <span
                                class="font-mono {{ $health['debug_mode'] ? 'text-emerald-400' : 'text-red-400' }}">{{ $health['debug_mode'] ? 'ON' : 'OFF' }}</span>
                        </p>
                    </div>
                </article>
            </section>

            <!-- Performance & Financials -->
            <div class="grid gap-6 lg:grid-cols-2 mt-6">
                <!-- Performance Profiling -->
                <article class="sim-card p-5">
                    <h2 class="text-lg font-bold text-white mb-4">Simulation-Profiling</h2>
                    <div class="flex items-center justify-between mb-6">
                        <div class="text-center">
                            <p class="text-[10px] text-slate-500 uppercase font-bold">Ø Zeit / Spiel</p>
                            <p class="text-2xl font-bold text-cyan-400">
                                {{ $diagnostics['performance']['avg_time_ms'] }}ms</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[10px] text-slate-500 uppercase font-bold">Ø Aktionen / Spiel</p>
                            <p class="text-2xl font-bold text-indigo-400">
                                {{ $diagnostics['performance']['avg_actions'] }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @foreach($diagnostics['performance']['issues'] as $issue)
                            <div class="sim-card-soft p-3 border-l-2 border-l-orange-500">
                                <p class="text-xs font-bold text-white">{{ $issue['description'] }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">{{ $issue['reason'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>

                <!-- Financial Sanity -->
                <article class="sim-card p-5">
                    <h2 class="text-lg font-bold text-white mb-4">Finanz-Sanity (Geldwäsche)</h2>
                    @if(count($diagnostics['finances']['issues']) > 0)
                        <div class="space-y-3">
                            @foreach($diagnostics['finances']['issues'] as $issue)
                                <div class="sim-card-soft p-3 border-l-2 border-l-red-500">
                                    <p class="text-xs font-bold text-white">{{ $issue['description'] }}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">{{ $issue['reason'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-32 text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 opacity-20" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-xs">Keine finanziellen Anomalien gefunden.</p>
                        </div>
                    @endif
                </article>
            </div>

            <!-- Data Health Diagnostics -->
            <section class="mt-6">
                <article class="sim-card p-5">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-white">Daten-Integritaet & Diagnose</h2>
                            <p class="text-xs text-slate-400">Pruefung wichtiger Datenbankfelder auf Vollstaendigkeit
                                und Logik. <span class="text-cyan-400 ml-2">Stand:
                                    {{ $diagnostics['generated_at'] }}</span></p>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('admin.monitoring.index', ['refresh' => 1]) }}"
                                class="text-[10px] bg-slate-700 hover:bg-slate-600 text-slate-300 px-2 py-1 rounded transition-colors flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Neu Scannen
                            </a>
                            <div class="text-center">
                                <p class="text-xs text-slate-500 uppercase font-bold">Gesamt</p>
                                <p class="text-lg font-bold text-white">
                                    {{ $diagnostics['matches']['count'] + $diagnostics['events']['count'] + $diagnostics['clubs']['count'] + $diagnostics['inactivity']['count'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <!-- Match & Event Issues -->
                        <div class="space-y-3">
                            <h3
                                class="text-sm font-bold text-cyan-400 uppercase tracking-widest flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Spiele & Ereignisse
                            </h3>
                            @foreach(array_merge($diagnostics['matches']['issues'], $diagnostics['events']['issues']) as $issue)
                                <div
                                    class="sim-card-soft p-3 border-l-2 {{ $issue['severity'] === 'CRITICAL' ? 'border-l-red-500' : 'border-l-orange-500' }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-xs font-bold text-white">{{ $issue['description'] }}</p>
                                            <p class="text-[10px] text-slate-400 mt-1"><span
                                                    class="text-cyan-400 font-bold">Grund:</span> {{ $issue['reason'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if(count($diagnostics['matches']['issues']) + count($diagnostics['events']['issues']) == 0)
                                <p class="text-xs text-slate-500 italic">Keine Probleme bei Spielen oder Ereignissen
                                    gefunden.</p>
                            @endif
                        </div>

                        <!-- Structural & Inactivity Issues -->
                        <div class="space-y-3">
                            <h3
                                class="text-sm font-bold text-indigo-400 uppercase tracking-widest flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Vereine & Kader (Inaktivität)
                            </h3>
                            @foreach(array_merge($diagnostics['clubs']['issues'], $diagnostics['inactivity']['issues']) as $issue)
                                <div
                                    class="sim-card-soft p-3 border-l-2 {{ $issue['severity'] === 'CRITICAL' ? 'border-l-red-500' : 'border-l-orange-500' }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-xs font-bold text-white">@if(isset($issue['name']))
                                            {{ $issue['name'] }}: @endif {{ $issue['description'] }}</p>
                                            <p class="text-[10px] text-slate-400 mt-1"><span
                                                    class="text-cyan-400 font-bold">Grund:</span> {{ $issue['reason'] }}</p>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            @if(str_contains($issue['description'], 'no active lineup'))
                                                <form action="{{ route('admin.monitoring.repair') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="type" value="club_lineup">
                                                    <input type="hidden" name="id" value="{{ $issue['id'] }}">
                                                    <button type="submit"
                                                        class="text-[10px] bg-cyan-500/20 text-cyan-400 hover:bg-cyan-500/40 px-2 py-1 rounded transition-colors flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                        </svg>
                                                        Fix It
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            </section>

            <!-- Recent Logs -->
            <section class="mt-6">
                <article class="sim-card">
                    <div class="p-5 border-b border-white/5 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-white">Letzte Log-Eintraege</h2>
                            <p class="text-xs text-slate-400">Auszug aus der laravel.log Datei</p>
                        </div>
                        <a href="{{ route('admin.monitoring.logs') }}" class="sim-btn-muted text-xs">Vollstaendige
                            Logs</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-slate-800/30 text-[10px] uppercase font-bold text-slate-500 tracking-widest">
                                    <th class="px-5 py-3 border-b border-white/5">Zeitstempel</th>
                                    <th class="px-5 py-3 border-b border-white/5">Level</th>
                                    <th class="px-5 py-3 border-b border-white/5">Nachricht</th>
                                    <th class="px-5 py-3 border-b border-white/5">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse($recentLogs as $log)
                                    <tr class="hover:bg-white/5 transition-colors group">
                                        <td class="px-5 py-3 text-xs font-mono text-slate-400 whitespace-nowrap">
                                            {{ $log['timestamp'] }}</td>
                                        <td class="px-5 py-3 text-xs">
                                            <span
                                                class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tighter {{ $log['level'] === 'ERROR' || $log['level'] === 'CRITICAL' ? 'bg-red-500/20 text-red-400' : ($log['level'] === 'WARNING' ? 'bg-orange-500/20 text-orange-400' : 'bg-slate-700 text-slate-300') }}">
                                                {{ $log['level'] }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-xs text-slate-200">
                                            <div class="line-clamp-1 group-hover:line-clamp-none transition-all cursor-help max-w-xl"
                                                title="{{ $log['message'] }}">
                                                {{ $log['message'] }}
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <button
                                                class="text-cyan-400 hover:text-white transition-colors Opacity-0 group-hover:opacity-100"
                                                onclick="alert('{{ addslashes($log['message']) }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-10 text-center text-slate-500 italic">Keine Logs
                                            gefunden.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </div>
    </div>
    <div class="text-[8px] text-slate-800 text-right opacity-10 px-8 pb-4">v1.3-fix-structure</div>
</x-app-layout>