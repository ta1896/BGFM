<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Training</p>
                <h1 class="text-3xl font-bold text-white tracking-tight">Trainingszentrum</h1>
            </div>
            
             <!-- Filter Controls -->
             <div class="sim-card py-2 px-4 flex items-center gap-3 bg-slate-900/80 backdrop-blur-md border hover:border-slate-600 transition-colors">
                 <form method="GET" action="{{ route('training.index') }}" class="flex flex-wrap items-center gap-4">
                     <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500 block mb-0.5">Verein</label>
                        <select name="club" class="bg-transparent text-sm text-white font-bold border-none p-0 focus:ring-0 cursor-pointer" onchange="this.form.submit()">
                            <option value="">Alle Vereine</option>
                            @foreach ($clubs as $club)
                                <option value="{{ $club->id }}" @selected((int) ($filters['club'] ?? 0) === (int) $club->id)>
                                    {{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                     <div class="h-8 w-px bg-slate-700/50"></div>
                     <div>
                         <label class="text-[10px] font-bold uppercase text-slate-500 block mb-0.5">Zeitraum</label>
                         <div class="flex items-center gap-2">
                             <a href="{{ route('training.index', array_merge($filters, ['range' => 'today'])) }}" 
                                class="text-xs font-bold px-2 py-1 rounded {{ ($filters['range'] ?? '') === 'today' ? 'bg-cyan-500/10 text-cyan-400' : 'text-slate-400 hover:text-white' }}">
                                Heute
                             </a>
                             <a href="{{ route('training.index', array_merge($filters, ['range' => 'week'])) }}" 
                                class="text-xs font-bold px-2 py-1 rounded {{ ($filters['range'] ?? '') === 'week' ? 'bg-cyan-500/10 text-cyan-400' : 'text-slate-400 hover:text-white' }}">
                                Woche
                             </a>
                         </div>
                     </div>
                 </form>
             </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
             <!-- Training Sessions List -->
            <div class="xl:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                     <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Geplante Einheiten
                     </h2>
                </div>

                @if ($sessions->isEmpty())
                    <div class="sim-card p-12 text-center border-dashed border-2 border-slate-700 bg-slate-900/40">
                         <p class="text-slate-400">Keine Trainingseinheiten für den gewählten Zeitraum gefunden.</p>
                         <p class="text-xs text-slate-500 mt-2">Plane  neue Einheiten über das Formular.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($sessions as $session)
                            <div class="sim-card p-5 group hover:border-cyan-500/30 transition-all duration-300">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="flex items-start gap-4">
                                         <img src="{{ $session->club->logo_url }}" alt="{{ $session->club->name }}" class="sim-avatar sim-avatar-lg shrink-0">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="font-bold text-white text-lg">{{ ucfirst($session->type) }}</h3>
                                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $session->club->name }}</span>
                                            </div>
                                            
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-400">
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    {{ $session->session_date?->format('d.m.Y') }}
                                                </span>
                                                <span class="flex items-center gap-1.5">
                                                     <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                     {{ ucfirst($session->intensity) }}
                                                </span>
                                            </div>

                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @if($session->morale_effect != 0)
                                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded bg-slate-800 text-slate-300 border border-slate-700">
                                                        Moral {{ $session->morale_effect > 0 ? '+' : '' }}{{ $session->morale_effect }}
                                                    </span>
                                                @endif
                                                @if($session->stamina_effect != 0)
                                                     <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded bg-slate-800 text-slate-300 border border-slate-700">
                                                        Ausdauer {{ $session->stamina_effect > 0 ? '+' : '' }}{{ $session->stamina_effect }}
                                                    </span>
                                                @endif
                                                @if($session->form_effect != 0)
                                                     <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded bg-slate-800 text-slate-300 border border-slate-700">
                                                        Form {{ $session->form_effect > 0 ? '+' : '' }}{{ $session->form_effect }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if ($session->players->isNotEmpty())
                                                <div class="mt-3 text-xs text-slate-500">
                                                    <span class="font-bold text-slate-400">Teilnehmer:</span>
                                                    {{ $session->players->take(5)->pluck('full_name')->join(', ') }}
                                                    @if($session->players->count() > 5)
                                                        <span class="text-slate-600">+{{ $session->players->count() - 5 }} weitere</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex flex-col items-end gap-2">
                                         @if ($session->is_applied)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Absolviert
                                            </span>
                                         @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                                Ausstehend
                                            </span>
                                            
                                            <form method="POST" action="{{ route('training.apply', $session) }}">
                                                @csrf
                                                <button type="submit" class="sim-btn-primary text-xs py-1.5 px-3">
                                                    Durchführen
                                                </button>
                                            </form>
                                         @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                     <div class="mt-4">
                        {{ $sessions->links() }}
                    </div>
                @endif
            </div>

            <!-- Create Session Form -->
            <div class="xl:col-span-1">
                 <div class="sim-card p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Einheit planen
                    </h2>

                    <form method="POST" action="{{ route('training.store') }}" class="space-y-5">
                        @csrf
                        
                        <!-- Club Select -->
                        <div>
                            <label class="sim-label" for="club_id">Verein</label>
                            <select id="club_id" name="club_id" class="sim-select w-full" required>
                                <option value="">Auswählen</option>
                                @foreach ($clubs as $club)
                                    <option value="{{ $club->id }}" @selected($prefillClubId === (int) $club->id)>{{ $club->name }}</option>
                                @endforeach
                            </select>
                        </div>

                         <div class="grid grid-cols-2 gap-4">
                             <!-- Date -->
                            <div>
                                <label class="sim-label" for="session_date">Datum</label>
                                <input id="session_date" name="session_date" type="date" value="{{ $prefillDate }}" class="sim-input w-full" required>
                            </div>
                            
                            <!-- Type -->
                             <div>
                                <label class="sim-label" for="type">Typ</label>
                                <select id="type" name="type" class="sim-select w-full" required>
                                    <option value="fitness">Fitness</option>
                                    <option value="tactics">Taktik</option>
                                    <option value="technical">Technik</option>
                                    <option value="recovery">Regeneration</option>
                                    <option value="friendly">Testspiel</option>
                                </select>
                            </div>
                         </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Intensity -->
                            <div>
                                <label class="sim-label" for="intensity">Intensität</label>
                                <select id="intensity" name="intensity" class="sim-select w-full" required>
                                    <option value="low">Niedrig</option>
                                    <option value="medium" selected>Mittel</option>
                                    <option value="high">Hoch</option>
                                </select>
                            </div>

                             <!-- Focus -->
                            <div>
                                <label class="sim-label" for="focus_position">Fokus</label>
                                <select id="focus_position" name="focus_position" class="sim-select w-full">
                                    <option value="">Alle</option>
                                    <option value="GK">Torwart</option>
                                    <option value="DEF">Abwehr</option>
                                    <option value="MID">Mittelfeld</option>
                                    <option value="FWD">Sturm</option>
                                </select>
                            </div>
                        </div>

                        <!-- Players -->
                        <div>
                            <label class="sim-label" for="player_ids">Teilnehmer</label>
                            <div class="relative">
                                <select id="player_ids" name="player_ids[]" class="sim-select w-full min-h-[120px]" multiple required>
                                    @foreach ($clubs as $club)
                                        <optgroup label="{{ $club->name }}" data-club-group="{{ $club->id }}">
                                            @foreach ($club->players as $player)
                                                <option value="{{ $player->id }}" data-club-id="{{ $club->id }}">
                                                    {{ $player->full_name }} ({{ $player->display_position }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-slate-500 mt-1 italic">STRG + Klick für Mehrfachauswahl</p>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="sim-label" for="notes">Notizen</label>
                            <textarea id="notes" name="notes" class="sim-textarea w-full h-20" placeholder="Optionale Anmerkungen..."></textarea>
                        </div>

                        <button type="submit" class="sim-btn-primary w-full py-3 shadow-lg shadow-cyan-500/20">
                            Session erstellen
                        </button>
                    </form>
                 </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Club Switching Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const clubSelect = document.getElementById('club_id');
            const playerSelect = document.getElementById('player_ids');
            
            if (!clubSelect || !playerSelect) return;

            const playerOptions = Array.from(playerSelect.querySelectorAll('option[data-club-id]'));
            const playerGroups = Array.from(playerSelect.querySelectorAll('optgroup[data-club-group]'));

            function syncPlayerOptions() {
                const selectedClub = String(clubSelect.value || '');
                
                playerGroups.forEach(group => {
                    const showGroup = !selectedClub || String(group.dataset.clubGroup) === selectedClub;
                    group.hidden = !showGroup;
                });

                playerOptions.forEach(option => {
                    const showOption = !selectedClub || String(option.dataset.clubId) === selectedClub;
                    option.disabled = !showOption;
                    if (!showOption) option.selected = false;
                });
            }

            clubSelect.addEventListener('change', syncPlayerOptions);
            syncPlayerOptions(); // Init
        });
    </script>
</x-app-layout>
