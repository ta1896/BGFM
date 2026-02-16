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

                        <!-- Mode Switcher -->
                        <div class="flex p-1 bg-slate-950/50 rounded-xl mb-6 relative z-10">
                            <button type="button" onclick="switchLabMode('single')"
                                class="flex-1 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg text-white bg-slate-800 shadow-lg ring-1 ring-white/10 transition-all"
                                id="tab-single">
                                Single
                            </button>
                            <button type="button" onclick="switchLabMode('batch')"
                                class="flex-1 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-300 transition-all"
                                id="tab-batch">
                                Batch
                            </button>
                            <button type="button" onclick="switchLabMode('ab')"
                                class="flex-1 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg text-slate-500 hover:text-slate-300 transition-all"
                                id="tab-ab">
                                A/B
                            </button>
                        </div>

                        <!-- 1. Single Simulation Form -->
                        <form id="lab-simulate-form-single" class="space-y-6 mode-form block">
                            @csrf
                            <input type="hidden" name="mode" value="single">
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
                            <button type="submit" id="simulate-btn-single"
                                class="w-full py-4 bg-emerald-600 text-white font-black rounded-xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-500 hover:-translate-y-0.5 active:translate-y-0 transition-all text-xs uppercase tracking-widest">
                                Simulation starten
                            </button>
                        </form>

                        <!-- 2. Batch Simulation Form -->
                        <form id="lab-simulate-form-batch" class="space-y-6 mode-form hidden">
                            @csrf
                            <input type="hidden" name="mode" value="batch">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Team
                                    A</label>
                                <select name="home_club_id"
                                    class="w-full bg-slate-950/50 border-white/10 rounded-xl text-xs text-white p-3 focus:ring-indigo-500/50 transition">
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->id }}">{{ $club->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Team
                                    B</label>
                                <select name="away_club_id"
                                    class="w-full bg-slate-950/50 border-white/10 rounded-xl text-xs text-white p-3 focus:ring-indigo-500/50 transition">
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->id }}" @if($loop->index == 1) selected @endif>
                                            {{ $club->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Anzahl
                                    Iterationen</label>
                                <input type="number" name="iterations" value="50" min="10" max="250"
                                    class="w-full bg-slate-950/50 border-white/10 rounded-xl text-xs text-white p-3 focus:ring-indigo-500/50 transition text-center font-mono">
                            </div>
                            <button type="submit" id="simulate-btn-batch"
                                class="w-full py-4 bg-indigo-600 text-white font-black rounded-xl shadow-lg shadow-indigo-500/20 hover:bg-indigo-500 hover:-translate-y-0.5 active:translate-y-0 transition-all text-xs uppercase tracking-widest">
                                Batch Run Starten
                            </button>
                        </form>

                        <!-- 3. A/B Simulation Form -->
                        <form id="lab-simulate-form-ab" class="space-y-6 mode-form hidden">
                            @csrf
                            <input type="hidden" name="mode" value="ab">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Test-Paarung</label>
                                <div class="flex gap-2">
                                    <select name="home_club_id"
                                        class="w-1/2 bg-slate-950/50 border-white/10 rounded-xl text-xs text-white p-2 focus:ring-pink-500/50 transition truncate">
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}">{{ $club->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="away_club_id"
                                        class="w-1/2 bg-slate-950/50 border-white/10 rounded-xl text-xs text-white p-2 focus:ring-pink-500/50 transition truncate">
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" @if($loop->index == 1) selected @endif>
                                                {{ $club->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Variant A -->
                            <div class="p-3 bg-slate-900/50 rounded-xl border border-white/5">
                                <h4 class="text-[9px] font-black text-white uppercase tracking-wider mb-2">Variante A
                                    (Kontrolle)</h4>
                                <select name="config_a[aggression]"
                                    class="w-full bg-slate-950 border-white/10 rounded-lg text-xs text-slate-400 p-2 mb-2">
                                    <option value="normal" selected>Aggression: Normal</option>
                                    <option value="high">Aggression: Hoch</option>
                                    <option value="low">Aggression: Niedrig</option>
                                </select>
                            </div>

                            <!-- Variant B -->
                            <div class="p-3 bg-slate-900/50 rounded-xl border border-white/5">
                                <h4 class="text-[9px] font-black text-pink-400 uppercase tracking-wider mb-2">Variante B
                                    (Test)</h4>
                                <select name="config_b[aggression]"
                                    class="w-full bg-slate-950 border-white/10 rounded-lg text-xs text-pink-300 p-2 mb-2 focus:ring-pink-500">
                                    <option value="normal">Aggression: Normal</option>
                                    <option value="high" selected>Aggression: Hoch</option>
                                    <option value="low">Aggression: Niedrig</option>
                                </select>
                            </div>

                            <button type="submit" id="simulate-btn-ab"
                                class="w-full py-4 bg-pink-600 text-white font-black rounded-xl shadow-lg shadow-pink-500/20 hover:bg-pink-500 hover:-translate-y-0.5 active:translate-y-0 transition-all text-xs uppercase tracking-widest">
                                A/B Vergleich Starten
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
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    </div>
    </div>

    <script>
        // Mode Switcher Logic
        function switchLabMode(mode) {
            // Update Tabs
            document.querySelectorAll('[id^="tab-"]').forEach(el => {
                el.classList.remove('bg-slate-800', 'text-white', 'shadow-lg', 'ring-1', 'ring-white/10');
                el.classList.add('text-slate-500');
            });
            const activeTab = document.getElementById('tab-' + mode);
            activeTab.classList.remove('text-slate-500');
            activeTab.classList.add('bg-slate-800', 'text-white', 'shadow-lg', 'ring-1', 'ring-white/10');

            // Update Forms
            document.querySelectorAll('.mode-form').forEach(el => el.classList.add('hidden'));
            document.getElementById('lab-simulate-form-' + mode).classList.remove('hidden');

            // Reset View
            document.getElementById('lab-placeholder').classList.remove('hidden');
            document.getElementById('lab-results').classList.add('hidden');
        }

        // Attach Event Listeners to all forms
        document.querySelectorAll('.mode-form').forEach(form => {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const mode = this.querySelector('input[name="mode"]').value;
                const btn = document.getElementById('simulate-btn-' + mode);
                const originalText = btn.innerText;

                btn.disabled = true;
                btn.innerHTML = '<span class="flex items-center justify-center gap-2"><svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Simuliere...</span>';

                try {
                    const formData = new FormData(this);

                    // Determine Endpoint based on mode (or use same controller with mode param)
                    // For now we use the same endpoint and handle mode in controller
                    const response = await fetch('{{ route('admin.monitoring.lab.run') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        handleSimulationResult(mode, data.data);
                    } else {
                        alert('Fehler: ' + data.message);
                    }
                } catch (error) {
                    console.error(error);
                    alert('Ein Netzwerkfehler ist aufgetreten.');
                } finally {
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            });
        });

        function handleSimulationResult(mode, data) {
            const placeholder = document.getElementById('lab-placeholder');
            const results = document.getElementById('lab-results');

            if (mode === 'single') {
                renderSingleResult(data);
            } else if (mode === 'batch') {
                renderBatchResult(data);
            } else if (mode === 'ab') {
                renderABResult(data);
            } else if (mode === 'heatmap') {
                renderHeatmapResult(data);
            }

            placeholder.classList.add('hidden');
            results.classList.remove('hidden');

            // Smooth scroll
            window.scrollTo({
                top: results.offsetTop - 100,
                behavior: 'smooth'
            });
        }

        function renderSingleResult(sim) {
            // ... (The existing rendering logic, moved here) ...
            // Simplified for brevity in this replacement step, I will need to inject the full renderer back or refactor
            // Actually, to avoid breaking the existing logic, I will paste the previous renderer here

            // Inject content into existing DOM structure (re-using the Single Match UI)
            document.getElementById('res-home-name').textContent = sim.home_club.name;
            document.getElementById('res-away-name').textContent = sim.away_club.name;
            document.getElementById('res-score').textContent = `${sim.home_score}:${sim.away_score}`;
            document.getElementById('res-weather').textContent = sim.weather;
            document.getElementById('res-attendance').textContent = sim.attendance;

            // Update Metadata
            document.getElementById('res-metadata').innerHTML = `
                <div class="flex justify-between border-b border-white/5 pb-2 mb-2">
                    <span class="text-slate-500 uppercase tracking-tighter">Engine Performance</span>
                    <span class="text-white font-black">${sim.duration_ms}ms</span>
                </div>
                 <div class="flex justify-between border-b border-white/5 pb-2 mb-2">
                    <span class="text-slate-500 uppercase tracking-tighter">Event Integrity</span>
                    <span class="${sim.health.is_stable ? 'text-emerald-500' : 'text-amber-500'} font-black">
                        ${sim.health.is_stable ? 'PERFECT' : 'AUDIT REQUIRED'}
                    </span>
                </div>
                 <div class="mt-4 pt-4 border-t border-white/10">
                    <h5 class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-500 mb-3">Deep Simulation Audit</h5>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-[8px]">
                            <span class="text-slate-400">Score Validation</span>
                            <span class="${sim.health.audit.score_validated ? 'text-emerald-500' : 'text-red-500'} font-black">
                                ${sim.health.audit.score_validated ? 'PASSED' : 'FAILED'}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[8px]">
                            <span class="text-slate-400">Timeline Integrity</span>
                            <span class="${sim.health.audit.timeline_validated ? 'text-emerald-500' : 'text-red-500'} font-black">
                                ${sim.health.audit.timeline_validated ? 'SECURE' : 'ERROR'}
                            </span>
                        </div>
                         <div class="flex items-center justify-between text-[8px]">
                            <span class="text-slate-400">Squad Consistency</span>
                            <span class="${sim.health.audit.players_validated ? 'text-emerald-500' : 'text-amber-500'} font-black">
                                ${sim.health.audit.players_validated ? 'VERIFIED' : 'MISMATCH'}
                            </span>
                        </div>
                    </div>
                </div>
            `;

            const eventsContainer = document.getElementById('res-events');
            eventsContainer.innerHTML = '';

            sim.events.forEach((event, idx) => {
                const div = document.createElement('div');
                div.style.animationDelay = `${idx * 0.05}s`;

                const isBroken = !event.narrative || event.narrative.includes('[') || event.narrative.includes(']');

                div.className = `flex items-start gap-5 p-5 rounded-[1.5rem] border transition-all duration-300 group animate-in fade-in slide-in-from-bottom-2 
                    ${isBroken ? 'bg-red-500/5 border-red-500/20 hover:bg-red-500/10' : 'bg-slate-900/40 border-white/5 hover:border-emerald-500/30 hover:bg-slate-900/60'}`;

                let icon = '⚽';
                let colorClass = isBroken ? 'text-red-400' : 'text-emerald-400';
                let bgClass = isBroken ? 'bg-red-500/10' : 'bg-emerald-500/10';

                if (event.event_type === 'yellow_card') { icon = '🟨'; if (!isBroken) { colorClass = 'text-yellow-400'; bgClass = 'bg-yellow-500/10'; } }
                if (event.event_type === 'red_card') { icon = '🟥'; if (!isBroken) { colorClass = 'text-red-400'; bgClass = 'bg-red-500/10'; } }
                if (event.event_type === 'substitution') { icon = '🔄'; if (!isBroken) { colorClass = 'text-blue-400'; bgClass = 'bg-blue-500/10'; } }
                if (event.event_type === 'injury') { icon = '🚑'; if (!isBroken) { colorClass = 'text-red-400'; bgClass = 'bg-red-500/10'; } }
                if (event.event_type === 'foul') { icon = '🚨'; if (!isBroken) { colorClass = 'text-orange-400'; bgClass = 'bg-orange-500/10'; } }
                if (event.event_type === 'chance') { icon = '🔥'; if (!isBroken) { colorClass = 'text-amber-400'; bgClass = 'bg-amber-500/10'; } }
                if (event.event_type === 'corner') { icon = '🚩'; if (!isBroken) { colorClass = 'text-teal-400'; bgClass = 'bg-teal-500/10'; } }

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
                        <p class="text-[13px] ${isBroken ? 'text-red-400/90' : 'text-slate-200'} font-medium leading-[1.6] tracking-tight antialiased">
                            ${event.narrative || 'Spielereignis ohne Kommentar.'}
                        </p>
                    </div>
                `;
                eventsContainer.appendChild(div);
            });
        }

        function renderBatchResult(data) {
            const container = document.getElementById('res-events');
            container.innerHTML = `
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4">
                    <div class="text-center space-y-2">
                        <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Batch Simulation Report</div>
                        <h2 class="text-2xl font-black text-white">Stress Test Analysis</h2>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-800 rounded-full border border-white/5 text-[10px] text-slate-400">
                            <span>${data.iterations} Iterationen</span>
                            <span>•</span>
                            <span>${data.home_club.name} vs ${data.away_club.name}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Stat Cards -->
                        <div class="sim-card p-6 flex flex-col items-center justify-center bg-slate-900/50">
                            <span class="text-[9px] uppercase tracking-widest text-slate-500 mb-1">Heim-Siege</span>
                            <span class="text-3xl font-black text-emerald-400">${data.stats.home_win_rate}%</span>
                        </div>
                        <div class="sim-card p-6 flex flex-col items-center justify-center bg-slate-900/50">
                             <span class="text-[9px] uppercase tracking-widest text-slate-500 mb-1">Unentschieden</span>
                             <span class="text-3xl font-black text-slate-400">${data.stats.draw_rate}%</span>
                        </div>
                        <div class="sim-card p-6 flex flex-col items-center justify-center bg-slate-900/50">
                             <span class="text-[9px] uppercase tracking-widest text-slate-500 mb-1">Gast-Siege</span>
                             <span class="text-3xl font-black text-blue-400">${data.stats.away_win_rate}%</span>
                        </div>
                    </div>

                    <div class="sim-card p-6 bg-slate-900/50">
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-6">Tore pro Spiel (Durchschnitt)</h4>
                        <div id="chart-goals" class="h-64"></div>
                    </div>
                </div>
             `;

            // Render Charts
            const options = {
                chart: { type: 'bar', height: 250, toolbar: { show: false }, background: 'transparent' },
                series: [{ name: 'Tore', data: [data.stats.avg_home_goals, data.stats.avg_away_goals] }],
                xaxis: {
                    categories: [data.home_club.name, data.away_club.name],
                    labels: { style: { colors: '#94a3b8', fontSize: '10px', fontFamily: 'Inter' } },
                    axisBorder: { show: false }, axisTicks: { show: false }
                },
                yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10px', fontFamily: 'Inter' } } },
                grid: { borderColor: 'rgba(255,255,255,0.05)' },
                colors: ['#10b981', '#3b82f6'],
                plotOptions: { bar: { borderRadius: 4, columnWidth: '40%', distributed: true } },
                dataLabels: { enabled: true, style: { fontSize: '12px', fontFamily: 'Inter', fontWeight: 900 } },
                legend: { show: false },
                theme: { mode: 'dark' }
            };
            new ApexCharts(document.querySelector("#chart-goals"), options).render();
        }

        function renderABResult(data) {
            const container = document.getElementById('res-events');

            // Calculate diff colors
            const goalsDiffColor = data.diff.home_goals > 0 ? 'text-emerald-400' : (data.diff.home_goals < 0 ? 'text-red-400' : 'text-slate-400');
            const cardsDiffColor = data.diff.cards > 0 ? 'text-amber-400' : (data.diff.cards < 0 ? 'text-emerald-400' : 'text-slate-400');

            container.innerHTML = `
                <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4">
                    <div class="text-center space-y-2">
                         <div class="text-[10px] font-black uppercase tracking-[0.2em] text-pink-500">A/B Engine Comparison</div>
                        <h2 class="text-2xl font-black text-white">Variant Analysis</h2>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-800 rounded-full border border-white/5 text-[10px] text-slate-400">
                            <span>${data.iterations} Iterationen p. Var.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Variant A -->
                        <div class="sim-card p-6 border-t-4 border-slate-500 bg-slate-900/50">
                            <h3 class="text-sm font-black text-white mb-4 flex items-center justify-between">
                                <span>Variante A (Kontrolle)</span>
                                <span class="text-[9px] bg-slate-800 px-2 py-1 rounded text-slate-400 uppercase tracking-wider">Baseline</span>
                            </h3>
                             <dl class="space-y-2 text-xs">
                                <div class="flex justify-between py-2 border-b border-white/5">
                                    <dt class="text-slate-500">Tore (Heim)</dt>
                                    <dd class="font-mono text-white">${data.variant_a.stats.avg_home_goals}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-white/5">
                                    <dt class="text-slate-500">Auswärtssiege</dt>
                                    <dd class="font-mono text-white">${data.variant_a.stats.win_rate_away}%</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-white/5">
                                    <dt class="text-slate-500">Karten Ø</dt>
                                    <dd class="font-mono text-white">${data.variant_a.stats.avg_cards}</dd>
                                </div>
                            </dl>
                        </div>

                         <!-- Variant B -->
                        <div class="sim-card p-6 border-t-4 border-pink-500 bg-slate-900/50 relative overflow-hidden">
                             <div class="absolute top-0 right-0 p-3 opacity-5 text-4xl">🧪</div>
                            <h3 class="text-sm font-black text-white mb-4 flex items-center justify-between">
                                <span>Variante B (Test)</span>
                                <span class="text-[9px] bg-pink-500/10 text-pink-400 border border-pink-500/20 px-2 py-1 rounded uppercase tracking-wider">Aggression: High</span>
                            </h3>
                             <dl class="space-y-2 text-xs">
                                <div class="flex justify-between py-2 border-b border-white/5">
                                    <dt class="text-slate-500">Tore (Heim)</dt>
                                    <dd class="font-mono text-white">${data.variant_b.stats.avg_home_goals}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-white/5">
                                    <dt class="text-slate-500">Auswärtssiege</dt>
                                    <dd class="font-mono text-white">${data.variant_b.stats.win_rate_away}%</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-white/5">
                                    <dt class="text-slate-500">Karten Ø</dt>
                                    <dd class="font-mono text-white">${data.variant_b.stats.avg_cards}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Diff Summary -->
                    <div class="sim-card p-6 bg-slate-900/50 border border-white/5">
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-4">Veränderung (B vs A)</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-black/20 rounded-xl">
                                <div class="text-[9px] text-slate-500 uppercase tracking-wider mb-1">Tore Delta</div>
                                <div class="text-xl font-black ${goalsDiffColor}">${data.diff.home_goals > 0 ? '+' : ''}${data.diff.home_goals}</div>
                            </div>
                            <div class="text-center p-4 bg-black/20 rounded-xl">
                                <div class="text-[9px] text-slate-500 uppercase tracking-wider mb-1">Karten Delta</div>
                                <div class="text-xl font-black ${cardsDiffColor}">${data.diff.cards > 0 ? '+' : ''}${data.diff.cards}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sim-card p-6 bg-slate-900/50">
                        <div id="chart-ab" class="h-64"></div>
                    </div>
                </div>
             `;

            // Comparison Chart
            const options = {
                chart: { type: 'bar', height: 250, toolbar: { show: false }, background: 'transparent' },
                series: [
                    { name: 'Variante A', data: [data.variant_a.stats.avg_home_goals, data.variant_a.stats.avg_cards, data.variant_a.stats.avg_injuries] },
                    { name: 'Variante B', data: [data.variant_b.stats.avg_home_goals, data.variant_b.stats.avg_cards, data.variant_b.stats.avg_injuries] }
                ],
                xaxis: {
                    categories: ['Tore (Heim)', 'Karten', 'Verletzungen'],
                    labels: { style: { colors: '#94a3b8', fontSize: '10px', fontFamily: 'Inter' } },
                    axisBorder: { show: false }, axisTicks: { show: false }
                },
                yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10px', fontFamily: 'Inter' } } },
                grid: { borderColor: 'rgba(255,255,255,0.05)' },
                colors: ['#64748b', '#ec4899'],
                plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
                dataLabels: { enabled: false },
                legend: { show: true, labels: { colors: '#cbd5e1' }, position: 'top' },
                theme: { mode: 'dark' },
                stroke: { show: true, width: 2, colors: ['transparent'] }
            };
            new ApexCharts(document.querySelector("#chart-ab"), options).render();
        }
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