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

            <!-- Main Layout Wrapper -->
            <div class="flex flex-col lg:flex-row gap-8 items-start">

                <!-- Config Side (Sticky on large screens) -->
                <aside class="w-full lg:w-80 shrink-0 lg:sticky lg:top-8">
                    <div class="sim-card p-6">
                        <h3
                            class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-6 pb-2 border-b border-white/5">
                            Konfiguration
                        </h3>
                        <form id="lab-simulate-form" class="space-y-6">
                            @csrf
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Heimteam</label>
                                <select name="home_club_id"
                                    class="w-full bg-slate-950/50 border-white/10 rounded-xl text-xs text-white p-3 focus:ring-emerald-500/50 transition">
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->id }}">{{ $club->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Gastteam</label>
                                <select name="away_club_id"
                                    class="w-full bg-slate-950/50 border-white/10 rounded-xl text-xs text-white p-3 focus:ring-emerald-500/50 transition">
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->id }}" @if($loop->index == 1) selected @endif>
                                            {{ $club->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" id="simulate-btn"
                                class="w-full py-4 bg-emerald-600 text-white font-black rounded-xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-500 hover:-translate-y-0.5 active:translate-y-0 transition-all text-xs uppercase tracking-widest">
                                Simulation starten
                            </button>
                        </form>
                    </div>
                </aside>

                <!-- Results Main -->
                <div class="flex-1 min-w-0 w-full space-y-8">
                    <!-- Placeholder / Start State -->
                    <div id="lab-placeholder"
                        class="sim-card p-16 text-center border-2 border-dashed border-white/5 bg-slate-900/20">
                        <div class="text-7xl mb-8 opacity-20 filter grayscale">🧪</div>
                        <h3 class="text-3xl font-black text-white mb-4 tracking-tight">Experimentelle Sandbox</h3>
                        <p class="text-slate-400 max-w-xl mx-auto leading-relaxed text-sm font-medium">
                            Hier können Simulationen durchgeführt werden, ohne Daten in die Datenbank zu schreiben.
                            Ideal zum Testen von Engine-Updates, Taktik-Einflüssen oder neuen Match-Events.
                        </p>
                    </div>

                    <!-- Results Area (Hidden by default) -->
                    <div id="lab-results" class="hidden space-y-6 focus-within:outline-none" tabindex="-1">
                        <!-- Score Card (Broadcast Style) -->
                        <div class="sim-card relative overflow-hidden bg-slate-900 border-white/10 shadow-2xl">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-slate-900 to-blue-500/10">
                            </div>
                            <div
                                class="relative p-6 px-10 flex flex-col sm:flex-row items-center justify-between gap-8">
                                <!-- Home Team -->
                                <div class="flex flex-col items-center gap-4 text-center group">
                                    <div
                                        class="w-20 h-20 bg-slate-800 rounded-[2.5rem] border border-white/10 flex items-center justify-center text-4xl shadow-2xl group-hover:scale-105 transition-transform duration-500">
                                        🏠</div>
                                    <div class="space-y-1">
                                        <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                            Heimteam</div>
                                        <h4 id="res-home-name"
                                            class="text-lg font-black text-white uppercase tracking-tighter leading-none">
                                            Heim</h4>
                                    </div>
                                </div>

                                <!-- Score Display -->
                                <div class="flex flex-col items-center">
                                    <div class="mb-4 flex flex-col items-center">
                                        <div
                                            class="text-[9px] font-black text-emerald-400 uppercase tracking-[0.4em] mb-2 drop-shadow-[0_0_10px_rgba(16,185,129,0.5)]">
                                            Live Simulation</div>
                                        <div
                                            class="bg-slate-950 px-8 py-5 rounded-[2rem] border border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.5)] ring-1 ring-inset ring-white/5">
                                            <span id="res-score"
                                                class="text-7xl font-black text-white tracking-tighter tabular-nums leading-none drop-shadow-lg">0:0</span>
                                        </div>
                                    </div>
                                    <div id="res-match-status"
                                        class="px-4 py-1.5 bg-emerald-500/10 text-emerald-400 text-[10px] font-black uppercase tracking-[0.2em] rounded-full border border-emerald-500/20 backdrop-blur-md">
                                        Abgeschlossen
                                    </div>
                                </div>

                                <!-- Away Team -->
                                <div class="flex flex-col items-center gap-4 text-center group">
                                    <div
                                        class="w-20 h-20 bg-slate-800 rounded-[2.5rem] border border-white/10 flex items-center justify-center text-4xl shadow-2xl group-hover:scale-105 transition-transform duration-500">
                                        🚌</div>
                                    <div class="space-y-1">
                                        <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                            Gastteam</div>
                                        <h4 id="res-away-name"
                                            class="text-lg font-black text-white uppercase tracking-tighter leading-none">
                                            Gast</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
                            <!-- Event List (Larger) -->
                            <div class="xl:col-span-2 space-y-4 order-2 xl:order-1">
                                <div class="sim-card p-2">
                                    <div class="p-6 pb-2">
                                        <h4
                                            class="text-[10px] font-black border-b border-white/5 pb-4 mb-4 uppercase text-slate-500 tracking-[0.2em] flex items-center gap-3">
                                            <span class="relative flex h-2 w-2">
                                                <span
                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span
                                                    class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                            </span>
                                            Ereignis-Protokoll
                                        </h4>
                                    </div>
                                    <div id="res-events"
                                        class="space-y-2 p-4 max-h-[700px] overflow-y-auto custom-scrollbar">
                                        <!-- Events will be injected here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar Info -->
                            <div class="space-y-6 order-1 xl:order-2">
                                <div class="sim-card p-6 overflow-hidden relative group">
                                    <div
                                        class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                        ☁️</div>
                                    <h4
                                        class="text-[10px] font-black border-b border-white/5 pb-4 mb-6 uppercase text-slate-500 tracking-widest relative">
                                        Atmosphäre</h4>
                                    <div class="space-y-3 relative">
                                        <div
                                            class="flex justify-between items-center bg-slate-950/30 p-4 rounded-2xl border border-white/5 hover:bg-slate-950/50 transition">
                                            <span
                                                class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Wetter</span>
                                            <span id="res-weather"
                                                class="text-xs font-black text-white capitalize">-</span>
                                        </div>
                                        <div
                                            class="flex justify-between items-center bg-slate-950/30 p-4 rounded-2xl border border-white/5 hover:bg-slate-950/50 transition">
                                            <span
                                                class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Zuschauer</span>
                                            <span id="res-attendance"
                                                class="text-xs font-black text-white italic">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="sim-card p-6 relative group overflow-hidden">
                                    <div
                                        class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                        🧬</div>
                                    <h4
                                        class="text-[10px] font-black border-b border-white/5 pb-4 mb-6 uppercase text-slate-500 tracking-widest relative">
                                        Engine Metadata</h4>
                                    <div id="res-metadata"
                                        class="text-[9px] text-slate-400 font-mono space-y-2 leading-relaxed relative">
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
                        <div class="flex justify-between border-b border-white/5 pb-2 mb-2">
                            <span class="text-slate-500 uppercase tracking-tighter">Seed Code</span>
                            <span class="text-white font-black">${sim.seed}</span>
                        </div>
                        <div class="flex justify-between border-b border-white/5 pb-2 mb-2">
                            <span class="text-slate-500 uppercase tracking-tighter">Players Home</span>
                            <span class="text-white font-black">${sim.home_players.length}</span>
                        </div>
                        <div class="flex justify-between border-b border-white/5 pb-2 mb-2">
                            <span class="text-slate-500 uppercase tracking-tighter">Players Away</span>
                            <span class="text-white font-black">${sim.away_players.length}</span>
                        </div>
                        <div class="mt-4 pt-4 border-t border-emerald-500/20">
                            <div class="text-emerald-500 font-black uppercase tracking-widest text-[8px] animate-pulse">Simulation Engine v2.0 Live</div>
                        </div>
                    `;

                    // Update Events
                    const eventsContainer = document.getElementById('res-events');
                    eventsContainer.innerHTML = '';

                    sim.events.forEach((event, idx) => {
                        const div = document.createElement('div');
                        // Staggered animation effect
                        div.style.animationDelay = `${idx * 0.05}s`;
                        div.className = 'flex items-start gap-5 bg-slate-900/40 p-5 rounded-[1.5rem] border border-white/5 hover:border-emerald-500/30 hover:bg-slate-900/60 transition-all duration-300 group animate-in fade-in slide-in-from-bottom-2';

                        let icon = '⚽';
                        let colorClass = 'text-emerald-400';
                        let bgClass = 'bg-emerald-500/10';

                        if (event.event_type === 'yellow_card') { icon = '🟨'; colorClass = 'text-yellow-400'; bgClass = 'bg-yellow-500/10'; }
                        if (event.event_type === 'red_card') { icon = '🟥'; colorClass = 'text-red-400'; bgClass = 'bg-red-500/10'; }
                        if (event.event_type === 'substitution') { icon = '🔄'; colorClass = 'text-blue-400'; bgClass = 'bg-blue-500/10'; }
                        if (event.event_type === 'injury') { icon = '🚑'; colorClass = 'text-red-400'; bgClass = 'bg-red-500/10'; }
                        if (event.event_type === 'foul') { icon = '🚨'; colorClass = 'text-orange-400'; bgClass = 'bg-orange-500/10'; }
                        if (event.event_type === 'chance') { icon = '🔥'; colorClass = 'text-amber-400'; bgClass = 'bg-amber-500/10'; }
                        if (event.event_type === 'corner') { icon = '🚩'; colorClass = 'text-teal-400'; bgClass = 'bg-teal-500/10'; }

                        div.innerHTML = `
                            <div class="w-10 h-10 shrink-0 ${bgClass} rounded-xl flex items-center justify-center text-[11px] border border-white/10 font-black ${colorClass} shadow-lg ring-1 ring-inset ring-white/5">
                                ${event.minute}'
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="text-sm scale-110 drop-shadow">${icon}</span>
                                        <h5 class="text-[10px] font-black uppercase text-slate-300 truncate tracking-wider">
                                            ${event.club_name || event.club_short_name || 'Unbekannt'}
                                        </h5>
                                    </div>
                                    <span class="text-[10px] font-mono font-black text-slate-500 bg-black/40 px-2 py-0.5 rounded-lg border border-white/5">${event.score || ''}</span>
                                </div>
                                <p class="text-[13px] text-slate-200 font-medium leading-[1.6] tracking-tight antialiased">
                                    ${event.narrative || 'Spielereignis ohne Kommentar.'}
                                </p>
                                <details class="mt-4 group/debug">
                                    <summary class="text-[8px] text-slate-600 cursor-pointer hover:text-slate-400 uppercase tracking-[0.2em] font-black list-none flex items-center gap-2 transition-colors">
                                        <div class="w-1 h-3 bg-slate-800 rounded-full transition-colors group-hover/debug:bg-emerald-500/50"></div>
                                        <span>Raw Engine Data</span>
                                    </summary>
                                    <div class="mt-3 p-4 bg-black/80 rounded-[1.25rem] border border-white/10 text-[9px] text-emerald-500/70 overflow-x-auto font-mono leading-relaxed shadow-inner">
                                        ${JSON.stringify(event, null, 2)}
                                    </div>
                                </details>
                            </div>
                        `;
                        eventsContainer.appendChild(div);
                    });

                    // Show results with scroll and focus
                    placeholder.classList.add('hidden');
                    results.classList.remove('hidden');
                    results.focus();

                    // Smooth scroll to results
                    window.scrollTo({
                        top: results.offsetTop - 100,
                        behavior: 'smooth'
                    });

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
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.1);
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.3);
            background-clip: content-box;
        }

        .sim-card {
            @apply bg-slate-900/40 backdrop-blur-2xl border border-white/5 rounded-[2.5rem] shadow-2xl;
        }

        details summary::-webkit-details-marker {
            display: none;
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fade-in-up 0.5s ease-out forwards;
        }
    </style>
</x-app-layout>