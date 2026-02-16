<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title text-emerald-400">System Monitoring</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Match Lab (Sandbox)</h1>
                <p class="mt-2 text-sm text-slate-300">Testumgebung für die Match-Engine Logik.</p>
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
                    class="flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-xl shadow-lg shadow-emerald-500/20 font-bold transition text-sm">
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
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Config Side -->
                <div class="lg:col-span-1">
                    <div class="sim-card p-6">
                        <h3
                            class="text-sm font-black text-white uppercase tracking-widest mb-6 pb-2 border-b border-white/5">
                            Konfiguration</h3>
                        <form id="lab-simulate-form" class="space-y-6">
                            @csrf
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-500 uppercase mb-2 tracking-widest">Heimteam</label>
                                <select name="home_club_id"
                                    class="w-full bg-slate-900 border-slate-700 rounded-xl text-xs text-white p-2.5 focus:ring-emerald-500/50">
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->id }}">{{ $club->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-500 uppercase mb-2 tracking-widest">Gastteam</label>
                                <select name="away_club_id"
                                    class="w-full bg-slate-900 border-slate-700 rounded-xl text-xs text-white p-2.5 focus:ring-emerald-500/50">
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->id }}" @if($loop->index == 1) selected @endif>
                                            {{ $club->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" id="simulate-btn"
                                class="w-full py-4 bg-emerald-600 text-white font-black rounded-xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-500 transition text-xs uppercase tracking-widest">
                                Simulation starten
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Results Main -->
                <div class="lg:col-span-3 space-y-8">
                    <!-- Placeholder / Start State -->
                    <div id="lab-placeholder"
                        class="sim-card p-12 text-center border-2 border-dashed border-slate-800 bg-slate-900/40">
                        <div class="text-7xl mb-8 opacity-20 filter grayscale">🧪</div>
                        <h3 class="text-2xl font-black text-white mb-4 tracking-tight">Experimentelle Sandbox</h3>
                        <p class="text-slate-400 max-w-lg mx-auto leading-relaxed text-sm font-medium">
                            Hier können Simulationen durchgeführt werden, ohne Daten in die Datenbank zu schreiben.
                            Ideal zum Testen von Engine-Updates, Taktik-Einflüssen oder neuen Match-Events.
                        </p>
                    </div>

                    <!-- Results Area (Hidden by default) -->
                    <div id="lab-results" class="hidden space-y-6">
                        <!-- Score Card (Broadcast Style) -->
                        <div class="sim-card relative overflow-hidden bg-slate-900 shadow-2xl">
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/10 via-transparent to-blue-600/10 opacity-50"></div>
                            <div class="relative p-8 flex items-center justify-between gap-4">
                                <!-- Home Team -->
                                <div class="flex-1 flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 bg-slate-800 rounded-2xl border border-white/5 flex items-center justify-center text-3xl shadow-inner">🏠</div>
                                    <h4 id="res-home-name" class="text-base font-black text-white uppercase tracking-tight text-center break-words max-w-[150px]">Heim</h4>
                                </div>

                                <!-- Score Display -->
                                <div class="flex flex-col items-center gap-2 px-8">
                                    <div class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em] mb-1">Live Simulation</div>
                                    <div class="flex items-center gap-4">
                                        <div class="bg-slate-950/80 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/10 shadow-2xl">
                                            <span id="res-score" class="text-6xl font-black text-white tracking-tighter tabular-nums leading-none">0:0</span>
                                        </div>
                                    </div>
                                    <div id="res-match-status" class="mt-4 px-3 py-1 bg-emerald-500/10 text-emerald-500 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-500/20">
                                        Abgeschlossen
                                    </div>
                                </div>

                                <!-- Away Team -->
                                <div class="flex-1 flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 bg-slate-800 rounded-2xl border border-white/5 flex items-center justify-center text-3xl shadow-inner">🚌</div>
                                    <h4 id="res-away-name" class="text-base font-black text-white uppercase tracking-tight text-center break-words max-w-[150px]">Gast</h4>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Event List (Larger) -->
                            <div class="md:col-span-2 space-y-4">
                                <div class="sim-card p-6 h-full">
                                    <h4 class="text-[10px] font-black border-b border-white/5 pb-4 mb-6 uppercase text-slate-500 tracking-widest flex items-center gap-2">
                                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                        Ereignis-Protokoll
                                    </h4>
                                    <div id="res-events" class="space-y-3 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                                        <!-- Events will be injected here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar Info -->
                            <div class="space-y-6">
                                <div class="sim-card p-6">
                                    <h4 class="text-[10px] font-black border-b border-white/5 pb-4 mb-6 uppercase text-slate-500 tracking-widest">Wetter & Atmosphäre</h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center bg-slate-950/40 p-3 rounded-xl border border-white/5">
                                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Wetter</span>
                                            <span id="res-weather" class="text-xs font-black text-white capitalize">-</span>
                                        </div>
                                        <div class="flex justify-between items-center bg-slate-950/40 p-3 rounded-xl border border-white/5">
                                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Zuschauer</span>
                                            <span id="res-attendance" class="text-xs font-black text-white">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="sim-card p-6">
                                    <h4 class="text-[10px] font-black border-b border-white/5 pb-4 mb-6 uppercase text-slate-500 tracking-widest">Metadata</h4>
                                    <div id="res-metadata" class="text-[9px] text-slate-400 font-mono space-y-2 leading-relaxed">
                                        <!-- Metadata injected here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('lab-simulate-form').addEventListener('submit', async function (e) {
            e.preventDefault();

            const btn = document.getElementById('simulate-btn');
            const placeholder = document.getElementById('lab-placeholder');
            const results = document.getElementById('lab-results');

            btn.disabled = true;
            btn.innerHTML = '<span class="flex items-center justify-center gap-2"><svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Simuliere...</span>';

            try {
                const formData = new FormData(this);
                const response = await fetch('{{ route('admin.monitoring.lab.run') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    const sim = data.data;

                    // Update Score
                    document.getElementById('res-home-name').innerText = this.home_club_id.options[this.home_club_id.selectedIndex].text;
                    document.getElementById('res-away-name').innerText = this.away_club_id.options[this.away_club_id.selectedIndex].text;
                    document.getElementById('res-score').innerText = `${sim.home_score}:${sim.away_score}`;
                    document.getElementById('res-attendance').innerText = sim.attendance.toLocaleString();
                    document.getElementById('res-weather').innerText = sim.weather;

                    // Update Metadata
                    document.getElementById('res-metadata').innerHTML = `
                        <div class="flex justify-between border-b border-white/5 pb-1"><span>SEED:</span> <span class="text-white">${sim.seed}</span></div>
                        <div class="flex justify-between border-b border-white/5 pb-1"><span>HOME:</span> <span class="text-white">${sim.home_players.length} P</span></div>
                        <div class="flex justify-between border-b border-white/5 pb-1"><span>AWAY:</span> <span class="text-white">${sim.away_players.length} P</span></div>
                        <div class="mt-2 text-emerald-500 font-bold uppercase tracking-tighter">SUCCESS</div>
                    `;

                    // Update Events
                    const eventsContainer = document.getElementById('res-events');
                    eventsContainer.innerHTML = '';

                    sim.events.forEach(event => {
                        const div = document.createElement('div');
                        div.className = 'flex items-start gap-4 bg-slate-900/40 p-4 rounded-xl border border-white/5 hover:border-emerald-500/30 transition-all duration-300 group';

                        let icon = '⚽';
                        let colorClass = 'text-emerald-500';
                        if (event.event_type === 'yellow_card') { icon = '🟨'; colorClass = 'text-yellow-500'; }
                        if (event.event_type === 'red_card') { icon = '🟥'; colorClass = 'text-red-500'; }
                        if (event.event_type === 'substitution') { icon = '🔄'; colorClass = 'text-blue-400'; }
                        if (event.event_type === 'injury') { icon = '🚑'; colorClass = 'text-red-400'; }
                        if (event.event_type === 'foul') { icon = '🚨'; colorClass = 'text-orange-400'; }
                        if (event.event_type === 'chance') { icon = '🔥'; colorClass = 'text-amber-500'; }
                        if (event.event_type === 'corner') { icon = '🚩'; colorClass = 'text-teal-400'; }

                        div.innerHTML = `
                            <div class="w-8 h-8 shrink-0 bg-slate-950 rounded-lg flex items-center justify-center text-[10px] border border-white/10 font-black ${colorClass} shadow-lg shadow-black/50">
                                ${event.minute}'
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-1.3">
                                    <p class="text-[9px] font-black uppercase text-slate-400 flex items-center gap-1.5 truncate">
                                        <span class="scale-110">${icon}</span>
                                        <span class="truncate">${event.club_name || event.club_short_name || 'Club'}</span>
                                    </p>
                                    <span class="text-[9px] font-mono text-slate-600 bg-black/30 px-1.5 py-0.5 rounded border border-white/5">${event.score || ''}</span>
                                </div>
                                <p class="text-xs text-slate-200 font-medium leading-relaxed tracking-tight">${event.narrative || 'Kein Text verfügbar.'}</p>
                                <details class="mt-3 group/debug">
                                    <summary class="text-[8px] text-slate-600 cursor-pointer hover:text-slate-400 uppercase tracking-widest font-bold list-none flex items-center gap-1">
                                        <svg class="w-2 h-2 transition-transform group-open/debug:rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                                        Debug
                                    </summary>
                                    <pre class="mt-2 p-3 bg-black/60 rounded-lg border border-white/5 text-[9px] text-slate-500 overflow-x-auto font-mono ring-1 ring-white/5">${JSON.stringify(event, null, 2)}</pre>
                                </details>
                            </div>
                        `;
                        eventsContainer.appendChild(div);
                    });

                    // Show results
                    placeholder.classList.add('hidden');
                    results.classList.remove('hidden');

                } else {
                    alert('Fehler: ' + data.message);
                }
            } catch (error) {
                console.error(error);
                alert('Ein Netzwerkfehler ist aufgetreten.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Simulation starten';
            }
        });
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.15);
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.3);
        }

        .sim-card {
            @apply bg-slate-900/60 backdrop-blur-xl border border-white/5 rounded-3xl shadow-xl;
        }

        details summary::-webkit-details-marker {
            display: none;
        }
    </style>
</x-app-layout>