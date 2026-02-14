<x-app-layout>
    @section('title', 'Aufstellung: ' . $match->homeClub->name . ' vs ' . $match->awayClub->name)

    <div class="space-y-6" id="lineup-editor" x-data="{ 
        showAnalysis: false,
        activeTab: 'all',
        searchTerm: ''
    }">
        
        <form method="POST" action="{{ route('matches.lineup.update', $match) }}" id="lineup-form">
            @csrf

            <!-- 1. Top Bar: Templates -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 rounded-xl bg-slate-900 p-4 border border-slate-700/50">
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <span class="text-sm text-slate-400 font-medium">Vorlagen:</span>
                    <select name="template_id_load" class="rounded-md bg-slate-800 border-slate-600 text-sm text-white focus:border-cyan-500 focus:ring-cyan-500 py-1.5">
                        <option value="">— Vorlage wählen —</option>
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" formaction="{{ route('matches.lineup.load-template', $match) }}" class="px-3 py-1.5 rounded bg-slate-700 text-sm font-bold text-slate-200 hover:bg-slate-600 transition">Laden</button>
                    <!-- Delete template functionality would require JS to get selected ID or separate form, simplifying visuals for now -->
                    <button type="button" class="px-3 py-1.5 rounded bg-rose-600 text-sm font-bold text-white hover:bg-rose-500 transition">Löschen</button>
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <input type="text" name="template_name" placeholder="Neuer Vorlagenname" class="rounded-md bg-slate-800 border-slate-600 text-sm text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-cyan-500 py-1.5">
                    <button type="submit" name="action" value="save_template" class="px-4 py-1.5 rounded bg-sky-500 text-sm font-bold text-white hover:bg-sky-400 transition shadow-[0_0_10px_rgba(14,165,233,0.3)]">
                        Als Vorlage speichern
                    </button>
                </div>
            </div>

            <!-- 2. Match Header Card -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-xl mt-4">
                <div class="p-6">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-4 uppercase tracking-wider">
                        <span>Aufstellung</span>
                        <span>•</span>
                        <span>{{ $match->kickoff_at->format('d.m.Y • H:i') }}</span>
                        <span>•</span>
                        <span>{{ $match->match_type ?? 'Ligaspiel' }}</span>
                    </div>

                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <!-- Matchup -->
                        <div class="flex items-center gap-8">
                            <!-- Home -->
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 overflow-hidden rounded-full bg-slate-800 border border-slate-600 flex items-center justify-center font-bold text-slate-500">
                                     {{ substr($match->homeClub->name, 0, 1) }}
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-white leading-tight">{{ $match->homeClub->name }}</h2>
                                    <span class="text-xs font-bold uppercase text-slate-500 tracking-wider">Heim</span>
                                </div>
                            </div>

                            <div class="text-slate-600 font-bold text-lg">VS</div>

                            <!-- Away -->
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 overflow-hidden rounded-full bg-slate-800 border border-slate-600 flex items-center justify-center font-bold text-slate-500">
                                     {{ substr($match->awayClub->name, 0, 1) }}
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-white leading-tight">{{ $match->awayClub->name }}</h2>
                                    <span class="text-xs font-bold uppercase text-slate-500 tracking-wider">Auswärts</span>
                                </div>
                            </div>
                        </div>

                        <!-- Auto Pick Button -->
                        <div>
                             <button type="submit" name="action" value="auto_pick" class="flex items-center gap-2 rounded-full bg-sky-600/20 border border-sky-500/30 px-4 py-2 text-sm font-bold text-sky-400 hover:bg-sky-600/30 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                Stärkste Elf wählen
                            </button>
                        </div>
                    </div>

                    <!-- Weather (Mocked for visual accuracy) -->
                    <div class="mt-4 flex items-center gap-2 rounded bg-slate-800/50 px-3 py-1.5 w-fit border border-slate-700/50">
                        <span class="text-amber-400">☀</span>
                        <span class="text-xs font-medium text-slate-300">Sonnig / Klar • 2°C • 29 km/h • 0 mm</span>
                    </div>
                </div>
            </div>

            <!-- 3. Stats & Main Save -->
            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center gap-4">
                    <div class="rounded-lg bg-slate-800 border border-slate-700 px-4 py-2 flex items-center gap-2">
                        <div class="h-2 w-0.5 bg-cyan-500"></div>
                        <span class="text-sm font-bold text-slate-300">Gesamtstärke:</span>
                        <span class="text-lg font-bold text-white">{{ round($metrics['overall'] ?? 0) }}</span>
                    </div>
                    <button type="button" @click="showAnalysis = !showAnalysis" class="rounded-lg bg-slate-800 border border-slate-700 px-4 py-2 text-sm font-bold text-slate-300 hover:text-white transition flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        Stärke-Analyse
                    </button>
                </div>

                <button type="submit" name="action" value="save_match" class="rounded-lg bg-emerald-600 px-8 py-3 text-base font-bold text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-500 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Aufstellung speichern
                </button>
            </div>

            <!-- 4. Set Pieces & Tactics Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- Set Pieces -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-slate-900 border border-slate-700 rounded-lg p-2">
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Elfmeter-Schütze</label>
                        <select name="penalty_taker_player_id" class="w-full bg-transparent border-none p-0 text-xs font-bold text-white focus:ring-0">
                            <option value="">— Kein Spieler —</option>
                            @foreach($clubPlayers as $p)
                                <option value="{{ $p->id }}" @selected($setPieces['penalty_taker_player_id'] == $p->id)>{{ $p->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bg-slate-900 border border-slate-700 rounded-lg p-2">
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Freistoß-Schütze</label>
                        <select name="free_kick_taker_player_id" class="w-full bg-transparent border-none p-0 text-xs font-bold text-white focus:ring-0">
                            <option value="">— Kein Spieler —</option>
                            @foreach($clubPlayers as $p)
                                <option value="{{ $p->id }}" @selected($setPieces['free_kick_taker_player_id'] == $p->id)>{{ $p->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bg-slate-900 border border-slate-700 rounded-lg p-2">
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Ecke links</label>
                        <select name="corner_left_taker_player_id" class="w-full bg-transparent border-none p-0 text-xs font-bold text-white focus:ring-0">
                            <option value="">— Kein Spieler —</option>
                            @foreach($clubPlayers as $p)
                                <option value="{{ $p->id }}" @selected($setPieces['corner_left_taker_player_id'] == $p->id)>{{ $p->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bg-slate-900 border border-slate-700 rounded-lg p-2">
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Ecke rechts</label>
                        <select name="corner_right_taker_player_id" class="w-full bg-transparent border-none p-0 text-xs font-bold text-white focus:ring-0">
                            <option value="">— Kein Spieler —</option>
                            @foreach($clubPlayers as $p)
                                <option value="{{ $p->id }}" @selected($setPieces['corner_right_taker_player_id'] == $p->id)>{{ $p->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tactics -->
                <div class="flex items-center gap-4">
                     <div class="flex-1">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Formation</label>
                        <select name="formation" onchange="this.form.submit()" class="w-full rounded bg-slate-800 border-slate-600 text-sm text-white focus:border-cyan-500 focus:ring-cyan-500">
                             @foreach($formations as $fmt)
                                <option value="{{ $fmt }}" @selected($formation == $fmt)>{{ $fmt }}</option>
                            @endforeach
                        </select>
                     </div>
                     <div class="flex-1">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Spielstil</label>
                        <select name="tactical_style" class="w-full rounded bg-slate-800 border-slate-600 text-sm text-white focus:border-cyan-500 focus:ring-cyan-500">
                            <option value="balanced" @selected($tacticalStyle == 'balanced')>Ausgewogen</option>
                            <option value="offensive" @selected($tacticalStyle == 'offensive')>Offensiv</option>
                            <option value="defensive" @selected($tacticalStyle == 'defensive')>Defensiv</option>
                            <option value="counter" @selected($tacticalStyle == 'counter')>Konter</option>
                        </select>
                     </div>
                     <div class="flex-1">
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Fokus</label>
                        <select name="attack_focus" class="w-full rounded bg-slate-800 border-slate-600 text-sm text-white focus:border-cyan-500 focus:ring-cyan-500">
                            <option value="center" @selected($attackFocus == 'center')>Zentrum</option>
                            <option value="left" @selected($attackFocus == 'left')>Links</option>
                            <option value="right" @selected($attackFocus == 'right')>Rechts</option>
                        </select>
                     </div>
                     <div class="mt-5">
                         <div class="h-9 w-9 rounded-full bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-500 cursor-help" title="Hilfe zur Taktik">
                             ?
                         </div>
                     </div>
                </div>
            </div>

            <!-- 5. Bench Row (Horizontal) -->
            <div class="mt-8">
                <h3 class="text-sm font-bold text-sky-400 mb-3">Auswechselbank</h3>
                <div class="grid grid-cols-5 gap-4">
                     @for ($i = 0; $i < 5; $i++) <!-- Fixed 5 slots based on screenshot -->
                        @php
                            $slotKey = $i;
                            $assignedPlayerId = $benchDraft[$i] ?? null;
                            $player = $assignedPlayerId ? $clubPlayers->firstWhere('id', $assignedPlayerId) : null;
                        @endphp
                        <div class="relative h-20 rounded-lg border border-slate-700 bg-slate-900 flex items-center justify-center p-2 group transition hover:border-slate-500"
                             data-slot-type="bench"
                             data-slot-key="{{ $slotKey }}"
                             ondrop="handleDrop(event)"
                             ondragover="handleDragOver(event)">
                            
                            <span class="absolute top-1 left-2 text-[10px] font-bold text-slate-600">Slot {{ $i + 1 }}</span>
                            <input type="hidden" name="bench_slots[]" id="input-bench-{{ $slotKey }}" value="{{ $player?->id }}">

                            @if($player)
                                <div class="flex flex-col items-center cursor-grab active:cursor-grabbing w-full"
                                     draggable="true"
                                     data-player-id="{{ $player->id }}"
                                     data-origin-type="bench"
                                     data-origin-key="{{ $slotKey }}"
                                     ondragstart="handleDragStart(event)"
                                     onclick="removePlayer('bench', '{{ $slotKey }}')">
                                    <div class="text-sm font-bold text-white">{{ $player->last_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $player->position }} • {{ $player->overall }}</div>
                                </div>
                            @else
                                <span class="text-slate-700 text-xs">Leer</span>
                            @endif
                        </div>
                    @endfor
                </div>
            </div>

            <!-- 6. Main Content: Pitch vs Player Pool -->
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-[1fr_350px] gap-6">
                
                <!-- Pitch -->
                <div class="relative rounded-2xl border border-slate-800 bg-[#0f1218] overflow-hidden" style="height: 700px;">
                    <!-- Simple field lines -->
                    <div class="absolute inset-8 border border-slate-700/30 rounded"></div>
                    <div class="absolute top-1/2 left-8 right-8 h-px bg-slate-700/30"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 rounded-full border border-slate-700/30"></div>

                    <!-- Slots -->
                    @foreach ($slots as $slot)
                        @php
                            $slotKey = $slot['slot'];
                            $assignedPlayerId = $starterDraft[$slotKey] ?? null;
                            $player = $assignedPlayerId ? $clubPlayers->firstWhere('id', $assignedPlayerId) : null;
                        @endphp

                        <div class="absolute -translate-x-1/2 -translate-y-1/2 flex flex-col items-center"
                             style="left: {{ $slot['x'] }}%; top: {{ $slot['y'] }}%;"
                             data-slot-type="starter"
                             data-slot-key="{{ $slotKey }}"
                             ondrop="handleDrop(event)"
                             ondragover="handleDragOver(event)">
                            
                            <input type="hidden" name="starter_slots[{{ $slotKey }}]" id="input-starter-{{ $slotKey }}" value="{{ $player?->id }}">

                            @if($player)
                                <!-- Assigned Player -->
                                <div class="flex flex-col items-center group cursor-pointer"
                                     draggable="true"
                                     data-player-id="{{ $player->id }}"
                                     data-origin-type="starter"
                                     data-origin-key="{{ $slotKey }}"
                                     ondragstart="handleDragStart(event)"
                                     onclick="removePlayer('starter', '{{ $slotKey }}')">
                                    
                                    <!-- Jersey/Avatar Circle -->
                                    <div class="h-10 w-10 rounded-full bg-slate-800 border border-slate-600 flex items-center justify-center text-xs font-bold text-white shadow-lg mb-1 group-hover:scale-110 transition-transform">
                                        {{ $player->number }}
                                    </div>
                                    <!-- Label -->
                                    <div class="bg-slate-900/80 px-2 py-0.5 rounded text-[10px] text-white font-bold border border-slate-700 whitespace-nowrap">
                                        {{ $player->last_name }}
                                        <span class="text-emerald-400 ml-1">{{ $player->overall }}</span>
                                    </div>
                                </div>
                            @else
                                <!-- Empty Slot -->
                                <div class="h-10 w-10 rounded-full border border-dashed border-slate-600 flex items-center justify-center text-slate-600 mb-1">
                                    <span class="text-xs">+</span>
                                </div>
                                <span class="bg-slate-900/50 px-1.5 py-0.5 rounded text-[10px] text-slate-500 font-bold uppercase">{{ $slot['label'] }}</span>
                            @endif

                        </div>
                    @endforeach
                </div>

                <!-- Player Pool Sidebar -->
                <div class="rounded-xl border border-slate-800 bg-[#0b0f15] flex flex-col h-[700px]">
                    <div class="p-4 border-b border-slate-800">
                        <h3 class="font-bold text-sky-400 text-sm mb-2">Spieler-Pool</h3>
                         <div class="relative">
                            <input type="text" x-model="searchTerm" placeholder="Suchen..." class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-1.5 text-xs text-white focus:border-sky-500">
                        </div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-2 space-y-4 custom-scrollbar">
                        @php
                            // Group players by simple groups
                            $groups = [
                                'Torwart' => ['TW'],
                                'Abwehr' => ['LV', 'IV', 'RV', 'LWB', 'RWB'],
                                'Mittelfeld' => ['DM', 'LM', 'ZM', 'RM', 'OM', 'ZOM', 'LAM', 'RAM'],
                                'Sturm' => ['LF', 'MS', 'ST', 'RF', 'LW', 'RW']
                            ];
                        @endphp

                        @foreach($groups as $label => $positions)
                            <div>
                                <h4 class="px-2 text-xs font-bold text-slate-500 uppercase mb-2">{{ $label }}</h4>
                                <div class="space-y-2">
                                    @foreach($clubPlayers->whereIn('position', $positions) as $player)
                                        @php
                                            $isAssigned = in_array($player->id, $starterDraft) || in_array($player->id, $benchDraft);
                                            // Calculate generic color for progress bar based on overall
                                            $barColor = $player->overall >= 80 ? 'bg-emerald-500' : ($player->overall >= 70 ? 'bg-amber-400' : 'bg-slate-500');
                                            $width = min(100, max(0, $player->overall)); // approximate
                                        @endphp
                                        
                                        <div class="relative bg-slate-900 rounded-lg p-2 border border-slate-800 flex items-center gap-3 hover:border-slate-600 transition group cursor-grab active:cursor-grabbing {{ $isAssigned ? 'opacity-30 pointer-events-none grayscale' : '' }}"
                                             x-show="searchTerm === '' || '{{ strtolower($player->last_name) }}'.includes(searchTerm.toLowerCase())"
                                             draggable="{{ $isAssigned ? 'false' : 'true' }}"
                                             data-player-id="{{ $player->id }}"
                                             data-origin-type="pool"
                                             ondragstart="handleDragStart(event)">
                                            
                                            <!-- Avatar -->
                                            <div class="h-10 w-10 rounded-full bg-slate-800 border border-slate-700 overflow-hidden flex-shrink-0">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($player->last_name) }}&background=1e293b&color=94a3b8" class="w-full h-full object-cover">
                                            </div>

                                            <!-- Info -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <div class="text-sm font-bold text-white leading-tight truncate">{{ $player->last_name }}</div>
                                                        <div class="text-[10px] text-slate-400">{{ $player->position }}</div>
                                                    </div>
                                                    <div class="text-sm font-bold text-slate-200">{{ $player->overall }}</div>
                                                </div>
                                                
                                                <!-- Bar + Value/Age row -->
                                                <div class="mt-1">
                                                    <div class="h-1 w-full bg-slate-800 rounded-full overflow-hidden">
                                                        <div class="h-full {{ $barColor }}" style="width: {{ $width }}%"></div>
                                                    </div>
                                                    <div class="flex justify-between mt-1 text-[9px] text-slate-500">
                                                        <span>{{ $player->age }} J.</span>
                                                        <span>€ {{ number_format($player->market_value / 1000000, 1, ',', '.') }} Mio</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Simple Drag and Drop Logic matching the previous robust implementation but mapped to new IDs
        let draggedPlayerId = null;
        let originType = null;
        let originKey = null;

        window.handleDragStart = function(e) {
            const card = e.target.closest('[draggable]');
            if (!card) return;

            draggedPlayerId = card.dataset.playerId;
            originType = card.dataset.originType;
            originKey = card.dataset.originKey;

            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', draggedPlayerId);
        }

        window.handleDragOver = function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        }

        window.handleDrop = function(e) {
            e.preventDefault();
            const targetEl = e.target.closest('[data-slot-type]');
            
            if (targetEl && draggedPlayerId) {
                const toType = targetEl.dataset.slotType;
                const toKey = targetEl.dataset.slotKey;
                
                // Get inputs
                const targetInput = document.getElementById(`input-${toType}-${toKey}`);
                if (!targetInput) return;

                // Handle origin
                if (originType !== 'pool' && originType) {
                    const sourceInput = document.getElementById(`input-${originType}-${originKey}`);
                    // Swap logic
                    const incomingValue = draggedPlayerId;
                    const existingValue = targetInput.value;

                    targetInput.value = incomingValue;
                    if (sourceInput) sourceInput.value = existingValue;
                } else {
                    // Pool to Slot
                    targetInput.value = draggedPlayerId;
                }

                // Submit to persist
                document.getElementById('lineup-form').submit();
            }
        }

        window.removePlayer = function(type, key) {
            const input = document.getElementById(`input-${type}-${key}`);
            if (input) {
                input.value = '';
                document.getElementById('lineup-form').submit();
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #0f172a; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155; 
            border-radius: 2px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #475569; 
        }
    </style>
    @endpush
</x-app-layout>
