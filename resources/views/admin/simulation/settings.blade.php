<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-100 leading-tight">
                {{ __('General Simulation Settings') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                    {{ session('status') }}
                </div>
            @endif

            @php
                $selectedTypes = old('simulation.scheduler.default_types', data_get($simulationSettings, 'scheduler.default_types', ['friendly', 'league', 'cup']));
                $selectedTypes = is_array($selectedTypes) ? $selectedTypes : [];
                $observerEnabled = (bool) old('simulation.observers.match_finished.enabled', data_get($simulationSettings, 'observers.match_finished.enabled', true));
                $observerRebuildStats = (bool) old('simulation.observers.match_finished.rebuild_match_player_stats', data_get($simulationSettings, 'observers.match_finished.rebuild_match_player_stats', true));
                $observerAggregateStats = (bool) old('simulation.observers.match_finished.aggregate_player_competition_stats', data_get($simulationSettings, 'observers.match_finished.aggregate_player_competition_stats', true));
                $observerAvailability = (bool) old('simulation.observers.match_finished.apply_match_availability', data_get($simulationSettings, 'observers.match_finished.apply_match_availability', true));
                $observerCompetition = (bool) old('simulation.observers.match_finished.update_competition_after_match', data_get($simulationSettings, 'observers.match_finished.update_competition_after_match', true));
                $observerFinance = (bool) old('simulation.observers.match_finished.settle_match_finance', data_get($simulationSettings, 'observers.match_finished.settle_match_finance', true));
            @endphp

            <form method="POST" action="{{ route('admin.simulation.settings.update') }}" class="space-y-8">
                @csrf

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400">
                        <p class="font-semibold mb-2">Validierungsfehler:</p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Scheduler Section -->
                <div class="sim-card p-6">
                    <div class="border-b border-slate-700/50 pb-4 mb-6">
                        <h3 class="text-lg font-medium text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Scheduler Configuration
                        </h3>
                        <p class="text-sm text-slate-400 mt-1">Steuert die automatische Berechnung von Spielen im Hintergrund.</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <x-input-label for="scheduler_interval_minutes" value="Intervall (Minuten)" />
                            <x-text-input id="scheduler_interval_minutes" type="number" min="1" max="60" name="simulation[scheduler][interval_minutes]" class="block mt-1 w-full" :value="old('simulation.scheduler.interval_minutes', data_get($simulationSettings, 'scheduler.interval_minutes', 1))" />
                            <p class="text-xs text-slate-500 mt-1">Wie oft der Scheduler läuft.</p>
                        </div>
                        <div>
                            <x-input-label for="scheduler_default_limit" value="Max. Matches pro Lauf" />
                            <x-text-input id="scheduler_default_limit" type="number" min="0" max="500" name="simulation[scheduler][default_limit]" class="block mt-1 w-full" :value="old('simulation.scheduler.default_limit', data_get($simulationSettings, 'scheduler.default_limit', 0))" />
                            <p class="text-xs text-slate-500 mt-1">0 = Keine Begrenzung.</p>
                        </div>
                        <div>
                            <x-input-label for="scheduler_default_minutes_per_run" value="Simulierte Minuten / Lauf" />
                            <x-text-input id="scheduler_default_minutes_per_run" type="number" min="1" max="90" name="simulation[scheduler][default_minutes_per_run]" class="block mt-1 w-full" :value="old('simulation.scheduler.default_minutes_per_run', data_get($simulationSettings, 'scheduler.default_minutes_per_run', 5))" />
                            <p class="text-xs text-slate-500 mt-1">Spielminuten pro Echtzeit-Intervall.</p>
                        </div>
                    </div>

                    {{-- These fields are validated as required but not shown in UI — pass current values --}}
                    <input type="hidden" name="simulation[scheduler][claim_stale_after_seconds]" value="{{ data_get($simulationSettings, 'scheduler.claim_stale_after_seconds', 180) }}">
                    <input type="hidden" name="simulation[scheduler][runner_lock_seconds]" value="{{ data_get($simulationSettings, 'scheduler.runner_lock_seconds', 120) }}">

                    <div class="mt-6 pt-4 border-t border-slate-700/50">
                        <x-input-label value="Automatische Match-Typen" class="mb-3" />
                        <div class="flex flex-wrap gap-3">
                            @foreach(['friendly' => 'Freundschaftsspiele', 'league' => 'Ligaspiele', 'cup' => 'Pokalspiele'] as $key => $label)
                                <label class="cursor-pointer select-none rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2 hover:bg-slate-800 hover:border-slate-600 has-[:checked]:border-cyan-500/50 has-[:checked]:bg-cyan-500/10 has-[:checked]:text-cyan-100 transition">
                                    <input type="checkbox" name="simulation[scheduler][default_types][]" value="{{ $key }}" @checked(in_array($key, $selectedTypes, true)) class="hidden">
                                    <span class="text-sm font-medium">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Diese Match-Typen werden vom Scheduler automatisch erfasst.</p>
                    </div>
                </div>

                <!-- Position Fit Section -->
                <div class="sim-card p-6">
                    <div class="border-b border-slate-700/50 pb-4 mb-6">
                        <h3 class="text-lg font-medium text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Position Fit Multipliers
                        </h3>
                        <p class="text-sm text-slate-400 mt-1">Einfluss der Positionstreue auf die Spielstärke.</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach([
                            'main' => ['label' => 'Hauptposition', 'desc' => 'Perfekter Fit (1.0 = 100%)', 'min' => 0.5, 'max' => 1.2],
                            'second' => ['label' => 'Nebenposition', 'desc' => 'Leichter Abzug', 'min' => 0.5, 'max' => 1.2],
                            'third' => ['label' => 'Dritte Position', 'desc' => 'Spürbarer Abzug', 'min' => 0.5, 'max' => 1.2],
                            'foreign' => ['label' => 'Fremdposition', 'desc' => 'Starker Abzug', 'min' => 0.3, 'max' => 1.2],
                            'foreign_gk' => ['label' => 'Feldspieler im Tor', 'desc' => 'Extremer Abzug', 'min' => 0.2, 'max' => 1.2],
                        ] as $key => $conf)
                            <div x-data="{ val: {{ old('simulation.position_fit.'.$key, data_get($simulationSettings, 'position_fit.'.$key)) }} }">
                                <div class="flex justify-between mb-1">
                                    <x-input-label :for="'position_fit_'.$key" :value="$conf['label']" />
                                    <span class="text-xs font-mono text-cyan-300" x-text="val"></span>
                                </div>
                                <input 
                                    type="range" 
                                    min="{{ $conf['min'] }}" 
                                    max="{{ $conf['max'] }}" 
                                    step="0.01" 
                                    x-model="val"
                                    class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-cyan-500 hover:accent-cyan-400"
                                >
                                <input type="hidden" :name="'simulation[position_fit][{{ $key }}]'" :value="val">
                                <p class="text-xs text-slate-500 mt-1">{{ $conf['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Live Changes & Lineup -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="sim-card p-6">
                         <div class="border-b border-slate-700/50 pb-4 mb-6">
                            <h3 class="text-lg font-medium text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                Live Changes
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="planned_subs_max_per_club" value="Geplante Wechsel (Max/Club)" />
                                <x-text-input id="planned_subs_max_per_club" type="number" min="1" max="5" name="simulation[live_changes][planned_substitutions][max_per_club]" class="block mt-1 w-full" :value="old('simulation.live_changes.planned_substitutions.max_per_club', data_get($simulationSettings, 'live_changes.planned_substitutions.max_per_club', 5))" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="planned_subs_min_minutes_ahead" value="Vorlauf (Min)" />
                                    <x-text-input id="planned_subs_min_minutes_ahead" type="number" class="block mt-1 w-full" name="simulation[live_changes][planned_substitutions][min_minutes_ahead]" :value="old('simulation.live_changes.planned_substitutions.min_minutes_ahead', data_get($simulationSettings, 'live_changes.planned_substitutions.min_minutes_ahead', 2))" />
                                </div>
                                <div>
                                    <x-input-label for="planned_subs_min_interval_minutes" value="Intervall (Min)" />
                                    <x-text-input id="planned_subs_min_interval_minutes" type="number" class="block mt-1 w-full" name="simulation[live_changes][planned_substitutions][min_interval_minutes]" :value="old('simulation.live_changes.planned_substitutions.min_interval_minutes', data_get($simulationSettings, 'live_changes.planned_substitutions.min_interval_minutes', 3))" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sim-card p-6">
                        <div class="border-b border-slate-700/50 pb-4 mb-6">
                            <h3 class="text-lg font-medium text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Lineup Limits
                            </h3>
                        </div>
                        <div>
                            <x-input-label for="lineup_max_bench_players" value="Maximale Bankspieler" />
                            <x-text-input id="lineup_max_bench_players" type="number" min="1" max="10" name="simulation[lineup][max_bench_players]" class="block mt-1 w-full" :value="old('simulation.lineup.max_bench_players', data_get($simulationSettings, 'lineup.max_bench_players', 5))" />
                            <x-input-error :messages="$errors->get('simulation.lineup.max_bench_players')" class="mt-1" />
                            <p class="text-xs text-slate-500 mt-2">Definiert die Größe der Ersatzbank für alle Wettbewerbe (1–10).</p>
                        </div>
                    </div>
                </div>

                <!-- Observers (Pipeline) -->
                <div class="sim-card p-6">
                    <div class="border-b border-slate-700/50 pb-4 mb-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                Post-Match Pipeline (Observer)
                            </h3>
                            <p class="text-sm text-slate-400 mt-1">Aktionen, die nach jedem Spiel ausgeführt werden.</p>
                        </div>
                        <!-- Global Toggle -->
                        <label class="sim-switch">
                            <input type="hidden" name="simulation[observers][match_finished][enabled]" value="0">
                            <input type="checkbox" name="simulation[observers][match_finished][enabled]" value="1" @checked($observerEnabled)>
                            <div class="sim-switch-track"></div>
                            <span class="ml-2 text-sm font-semibold text-white">PIPELINE AKTIV</span>
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach([
                            'rebuild_match_player_stats' => ['Label' => 'Statistiken berechnen', 'Desc' => 'Goals, Assists, Cards aus Event-Log extrahieren', 'Val' => $observerRebuildStats],
                            'aggregate_player_competition_stats' => ['Label' => 'Wettbewerbs-Stats', 'Desc' => 'Summierte Saison-Stats aktualisieren', 'Val' => $observerAggregateStats],
                            'apply_match_availability' => ['Label' => 'Sperren/Fitness', 'Desc' => 'Gelbsperren und Erschöpfung anwenden', 'Val' => $observerAvailability],
                            'update_competition_after_match' => ['Label' => 'Tabellen & Runden', 'Desc' => 'Ligatabelle neu berechnen, Pokalrunden', 'Val' => $observerCompetition],
                            'settle_match_finance' => ['Label' => 'Finanzen buchen', 'Desc' => 'Prämien, Ticketeinnahmen auszahlen', 'Val' => $observerFinance],
                        ] as $key => $conf)
                            <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-700/40 bg-slate-800/20 hover:bg-slate-800/40 cursor-pointer transition">
                                <div class="pt-0.5">
                                    <label class="sim-switch">
                                        <input type="hidden" name="simulation[observers][match_finished][{{ $key }}]" value="0">
                                        <input type="checkbox" name="simulation[observers][match_finished][{{ $key }}]" value="1" @checked($conf['Val'])>
                                        <div class="sim-switch-track"></div>
                                    </label>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-slate-200">{{ $conf['Label'] }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5 leading-snug">{{ $conf['Desc'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end pt-6">
                    <button type="submit" class="sim-btn-primary px-8 py-3 text-base shadow-lg shadow-cyan-500/20">
                        Einstellungen speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
