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
                    <div id="lab-results" class="hidden space-y-8">
                        <!-- Score Card -->
                        <div class="sim-card overflow-hidden">
                            <div
                                class="p-8 bg-gradient-to-br from-slate-900 to-slate-950 flex items-center justify-between">
                                <div class="text-center flex-1">
                                    <h4 id="res-home-name"
                                        class="text-lg font-black text-white uppercase tracking-tight">Heim</h4>
                                </div>
                                <div class="px-10 py-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl">
                                    <span id="res-score" class="text-5xl font-black text-white tracking-tighter">0 :
                                        0</span>
                                </div>
                                <div class="text-center flex-1">
                                    <h4 id="res-away-name"
                                        class="text-lg font-black text-white uppercase tracking-tight">Gast</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Event List -->
                        <div class="sim-card p-6">
                            <h4
                                class="text-[10px] font-black border-b border-white/5 pb-4 mb-6 uppercase text-slate-500 tracking-widest text-center">
                                Ereignis-Protokoll</h4>
                            <div id="res-events" class="space-y-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                                <!-- Events will be injected here -->
                            </div>
                        </div>
                    </div>

                    <div id="lab-debug-info" class="grid grid-cols-1 md:grid-cols-2 gap-8 hidden">
                        <div class="sim-card p-6">
                            <h4
                                class="text-[10px] font-black border-b border-white/5 pb-4 mb-6 uppercase text-slate-500 tracking-widest">
                                Simulations-Metadaten</h4>
                            <div id="res-metadata" class="text-[10px] text-slate-400 font-mono space-y-1">
                                <!-- Metadata injected here -->
                            </div>
                        </div>
                        <div class="sim-card p-6">
                            <h4
                                class="text-[10px] font-black border-b border-white/5 pb-4 mb-6 uppercase text-slate-500 tracking-widest">
                                Wetter & Zuschaue</h4>
                            <div class="flex flex-col gap-4">
                                <div
                                    class="flex justify-between items-center bg-slate-900/50 p-3 rounded-xl border border-white/5">
                                    <span class="text-[10px] font-bold text-slate-500">WETTER</span>
                                    <span id="res-weather" class="text-xs font-black text-white">Sonnig</span>
                                </div>
                                <div
                                    class="flex justify-between items-center bg-slate-900/50 p-3 rounded-xl border border-white/5">
                                    <span class="text-[10px] font-bold text-slate-500">ZUSCHAUER</span>
                                    <span id="res-attendance" class="text-xs font-black text-white">0</span>
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
            const debug = document.getElementById('lab-debug-info');

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
                    document.getElementById('res-score').innerText = `${sim.home_score} : ${sim.away_score}`;
                    document.getElementById('res-attendance').innerText = sim.attendance.toLocaleString();
                    document.getElementById('res-weather').innerText = sim.weather;

                    // Update Metadata
                    document.getElementById('res-metadata').innerHTML = `
                        <div>SEED: ${sim.seed}</div>
                        <div>SPIELER HEIM: ${sim.home_players.length}</div>
                        <div>SPIELER GAST: ${sim.away_players.length}</div>
                        <div class="mt-2 text-emerald-500">SIMULATION ABGESCHLOSSEN</div>
                    `;

                    // Update Events
                    const eventsContainer = document.getElementById('res-events');
                    eventsContainer.innerHTML = '';

                    sim.events.forEach(event => {
                        const div = document.createElement('div');
                        div.className = 'flex items-center gap-4 bg-slate-900/50 p-4 rounded-2xl border border-white/5 hover:border-emerald-500/20 transition group';

                        let icon = '⚽';
                        if (event.event_type === 'yellow_card') icon = '🟨';
                        if (event.event_type === 'red_card') icon = '🟥';
                        if (event.event_type === 'substitution') icon = '🔄';
                        if (event.event_type === 'injury') icon = '🚑';
                        if (event.event_type === 'foul') icon = '🚨';
                        if (event.event_type === 'chance') icon = '🔥';
                        if (event.event_type === 'corner') icon = '🚩';

                        div.innerHTML = `
                            <div class="w-10 h-10 bg-slate-950 rounded-xl flex items-center justify-center text-sm border border-white/5 font-black text-emerald-500">
                                ${event.minute}'
                            </div>
                            <div class="flex-1">
                                <p class="text-[10px] font-black uppercase text-slate-500 mb-1 flex items-center gap-2">
                                    <span>${icon}</span>
                                    <span>${event.club_name || event.club_short_name || 'Club'}</span>
                                    <span class="ml-auto text-slate-600 font-mono">${event.score || ''}</span>
                                </p>
                                <p class="text-xs text-white font-medium leading-relaxed">${event.narrative || 'Kein Text verfügbar.'}</p>
                                <details class="mt-2">
                                    <summary class="text-[9px] text-slate-700 cursor-pointer hover:text-slate-500 uppercase tracking-widest font-bold">Debug Daten</summary>
                                    <pre class="mt-2 p-2 bg-black/40 rounded border border-white/5 text-[9px] text-slate-500 overflow-x-auto">${JSON.stringify(event, null, 2)}</pre>
                                </details>
                            </div>
                        `;
                        eventsContainer.appendChild(div);
                    });

                    // Show results
                    placeholder.classList.add('hidden');
                    results.classList.remove('hidden');
                    debug.classList.remove('hidden');

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
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.2);
            border-radius: 10px;
        }
    </style>
</x-app-layout>