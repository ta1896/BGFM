<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-100 leading-tight">
                {{ __('Match Engine Configuration') }}
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

            <form method="POST" action="{{ route('admin.match-engine.update') }}" class="space-y-8">
                @csrf
                
                <!-- General Physics -->
                <div class="sim-card p-6">
                    <div class="border-b border-slate-700/50 pb-4 mb-6">
                        <h3 class="text-lg font-medium text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Core Mechanics
                        </h3>
                        <p class="text-sm text-slate-400 mt-1">Grundlegende Parameter der Simulations-Physik.</p>
                    </div>

                    <div class="grid gap-8 md:grid-cols-2">
                        <!-- Duration -->
                        <div>
                            <x-input-label for="match_engine_duration" value="Match Dauer (Minuten)" />
                            <x-text-input id="match_engine_duration" class="block mt-1 w-full" type="number" name="settings[match_engine_duration]" :value="old('settings.match_engine_duration', $settings['match_engine.duration'])" required />
                            <p class="text-xs text-slate-500 mt-1">Dauer eines Spiels. Standard: 90.</p>
                        </div>

                        <!-- Home Advantage Slider -->
                        <div x-data="{ val: {{ old('settings.match_engine_home_advantage', $settings['match_engine.home_advantage']) }} }">
                            <div class="flex justify-between mb-2">
                                <x-input-label for="match_engine_home_advantage" value="Heimvorteil Multiplikator" />
                                <span class="text-sm font-mono text-cyan-300" x-text="parseFloat(val).toFixed(2) + 'x'"></span>
                            </div>
                            <input 
                                type="range" 
                                min="1.00" 
                                max="1.50" 
                                step="0.01" 
                                x-model="val"
                                class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-cyan-500 hover:accent-cyan-400"
                            >
                            <input type="hidden" name="settings[match_engine_home_advantage]" :value="val">
                            <p class="text-xs text-slate-500 mt-1">Erhöht die effektive Stärke des Heimteams. 1.10 = +10%.</p>
                        </div>
                    </div>
                </div>

                <!-- Probabilities Grid -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Events -->
                    <div class="sim-card p-6">
                         <div class="border-b border-slate-700/50 pb-4 mb-6">
                            <h3 class="text-lg font-medium text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                Event Probabilities
                            </h3>
                        </div>

                        <div class="space-y-6">
                            <!-- Chance Probability -->
                            <div x-data="{ val: {{ old('settings.match_engine_chance_probability', $settings['match_engine.chance_probability']) }} }">
                                <div class="flex justify-between mb-1">
                                    <x-input-label value="Chance pro Minute" />
                                    <span class="text-xs font-mono text-indigo-300" x-text="(val * 100).toFixed(1) + '%'"></span>
                                </div>
                                <input type="range" min="0.01" max="0.50" step="0.01" x-model="val" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-indigo-500">
                                <input type="hidden" name="settings[match_engine_chance_probability]" :value="val">
                                <p class="text-xs text-slate-500 mt-1">Wahrscheinlichkeit dass in einer Minute eine Torchance entsteht.</p>
                            </div>

                            <!-- Goal Conversion -->
                            <div x-data="{ val: {{ old('settings.match_engine_goal_conversion', $settings['match_engine.goal_conversion']) }} }">
                                <div class="flex justify-between mb-1">
                                    <x-input-label value="Tor-Konversion (Chancenverwertung)" />
                                    <span class="text-xs font-mono text-indigo-300" x-text="(val * 100).toFixed(1) + '%'"></span>
                                </div>
                                <input type="range" min="0.01" max="1.00" step="0.01" x-model="val" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-indigo-500">
                                <input type="hidden" name="settings[match_engine_goal_conversion]" :value="val">
                                <p class="text-xs text-slate-500 mt-1">Basis-Wert, wie viele Chancen zu Toren werden.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cards -->
                    <div class="sim-card p-6">
                         <div class="border-b border-slate-700/50 pb-4 mb-6">
                            <h3 class="text-lg font-medium text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-8a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2zM6.5 6l11 11M3 3l18 18"></path></svg>
                                Disciplinary
                            </h3>
                        </div>

                        <div class="space-y-6">
                            <!-- Yellow -->
                            <div x-data="{ val: {{ old('settings.match_engine_yellow_card_chance', $settings['match_engine.yellow_card_chance']) }} }">
                                <div class="flex justify-between mb-1">
                                    <x-input-label value="Gelbe Karte (Chance/Min)" />
                                    <span class="text-xs font-mono text-amber-300" x-text="(val * 100).toFixed(2) + '%'"></span>
                                </div>
                                <input type="range" min="0.001" max="0.2" step="0.001" x-model="val" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-amber-500">
                                <input type="hidden" name="settings[match_engine_yellow_card_chance]" :value="val">
                            </div>

                            <!-- Red -->
                             <div x-data="{ val: {{ old('settings.match_engine_red_card_chance', $settings['match_engine.red_card_chance']) }} }">
                                <div class="flex justify-between mb-1">
                                    <x-input-label value="Rote Karte (Chance/Min)" />
                                    <span class="text-xs font-mono text-rose-400" x-text="(val * 100).toFixed(3) + '%'"></span>
                                </div>
                                <input type="range" min="0.0001" max="0.1" step="0.0001" x-model="val" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-rose-500">
                                <input type="hidden" name="settings[match_engine_red_card_chance]" :value="val">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tacitcs -->
                <div class="sim-card p-6">
                    <div class="border-b border-slate-700/50 pb-4 mb-6">
                        <h3 class="text-lg font-medium text-white flex items-center gap-2">
                             <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                            Tactical Impact
                        </h3>
                        <p class="text-sm text-slate-400 mt-1">Auswirkungen von offensiven/defensiven Taktiken.</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                         <div x-data="{ val: {{ old('settings.match_engine_tactic_attack_bonus', $settings['match_engine.tactic_attack_bonus']) }} }">
                            <div class="flex justify-between mb-1">
                                <x-input-label value="Angriffs-Bonus" />
                                <span class="text-xs font-mono text-emerald-300" x-text="parseFloat(val).toFixed(2) + 'x'"></span>
                            </div>
                            <input type="range" min="1.0" max="2.0" step="0.01" x-model="val" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                            <input type="hidden" name="settings[match_engine_tactic_attack_bonus]" :value="val">
                            <p class="text-xs text-slate-500 mt-1">Bonus für 'Attacking' Einstellung.</p>
                        </div>

                         <div x-data="{ val: {{ old('settings.match_engine_tactic_defense_penalty', $settings['match_engine.tactic_defense_penalty']) }} }">
                            <div class="flex justify-between mb-1">
                                <x-input-label value="Defensiv-Malus (High Risk)" />
                                <span class="text-xs font-mono text-rose-300" x-text="parseFloat(val).toFixed(2) + 'x'"></span>
                            </div>
                            <input type="range" min="0.1" max="1.0" step="0.01" x-model="val" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-rose-500">
                            <input type="hidden" name="settings[match_engine_tactic_defense_penalty]" :value="val">
                            <p class="text-xs text-slate-500 mt-1">Abzug bei Verteidigung wenn 'All Out Attack'.</p>
                        </div>

                        <div x-data="{ val: {{ old('settings.match_engine_counter_attack_bonus', $settings['match_engine.counter_attack_bonus']) }} }">
                            <div class="flex justify-between mb-1">
                                <x-input-label value="Counter Attack Bonus" />
                                <span class="text-xs font-mono text-amber-300" x-text="parseFloat(val).toFixed(2) + 'x'"></span>
                            </div>
                            <input type="range" min="1.0" max="3.0" step="0.01" x-model="val" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-amber-500">
                            <input type="hidden" name="settings[match_engine_counter_attack_bonus]" :value="val">
                            <p class="text-xs text-slate-500 mt-1">Bonus für Konter-Taktiken.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-6">
                    <button type="submit" class="sim-btn-primary px-8 py-3 text-base shadow-lg shadow-cyan-500/20">
                        Physik-Engine updaten
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
