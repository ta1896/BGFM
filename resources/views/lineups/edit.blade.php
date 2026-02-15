@php
    $positionService = app(\App\Services\PlayerPositionService::class);
    $playersByPosition = $clubPlayers->groupBy(function ($player) use ($positionService) {
        return $positionService->groupFromPosition($player->position_main ?? $player->position) ?? 'MID';
    });
    $effectiveStarterDraft = old('starter_slots');
    $effectiveStarterDraft = is_array($effectiveStarterDraft) ? $effectiveStarterDraft : $starterDraft;
    $effectiveBenchDraft = old('bench_slots');
    $effectiveBenchDraft = is_array($effectiveBenchDraft) ? $effectiveBenchDraft : $benchDraft;
    $maxBenchPlayers = max(1, min(10, (int) ($maxBenchPlayers ?? 5)));
    $selectedPlayerIds = collect($effectiveStarterDraft)
        ->filter()
        ->map(static fn ($value) => (int) $value)
        ->values()
        ->concat(
            collect($effectiveBenchDraft)
                ->filter()
                ->map(static fn ($value) => (int) $value)
                ->values()
        )
        ->unique()
        ->all();
    $positionLabels = [
        'TW' => 'Torwart',
        'LV' => 'Linksverteidiger',
        'IV' => 'Innenverteidiger',
        'RV' => 'Rechtsverteidiger',
        'LWB' => 'Linker Wingback',
        'RWB' => 'Rechter Wingback',
        'LM' => 'Linkes Mittelfeld',
        'ZM' => 'Zentrales Mittelfeld',
        'RM' => 'Rechtes Mittelfeld',
        'DM' => 'Defensives Mittelfeld',
        'OM' => 'Offensives Mittelfeld',
        'LAM' => 'Linker Offensiver',
        'ZOM' => 'Zentrales Offensives Mittelfeld',
        'RAM' => 'Rechter Offensiver',
        'LS' => 'Linker Stuermer',
        'MS' => 'Mittelstuermer',
        'RS' => 'Rechter Stuermer',
        'LW' => 'Linker Fluegel',
        'RW' => 'Rechter Fluegel',
        'ST' => 'Stuermer',
    ];
    $groupLabels = [
        'GK' => 'Torwart',
        'DEF' => 'Abwehr',
        'MID' => 'Mittelfeld',
        'FWD' => 'Sturm',
    ];
@endphp

<x-app-layout>
    {{-- 
        Custom Header for Match Lineup 
        We use an inline header instead of <x-slot name="header"> to avoid the sticky behavior 
        and provide a custom, rich match presentation.
    --}}
    
    @php
        $match = $lineup->match;
        $homeClub = $match?->homeClub;
        $awayClub = $match?->awayClub;
        $weather = $match?->weather ?? 'clear';
        
        // Mock weather data for display based on basic weather string
        $weatherIcons = [
            'clear' => '<svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>',
            'cloudy' => '<svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z"></path></svg>',
            'rainy' => '<svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z"></path><path d="M5 18a1 1 0 001 1h1a1 1 0 100-2H6a1 1 0 00-1 1zm4 0a1 1 0 001 1h1a1 1 0 100-2h-1a1 1 0 00-1 1zm4 0a1 1 0 001 1h1a1 1 0 100-2h-1a1 1 0 00-1 1z"></path></svg>',
            'windy' => '<svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.59 4.59A2 2 0 1111 8H2m10.59 11.41A2 2 0 1014 16H2m15.73-8.27A2.5 2.5 0 1119.5 12H2"></path></svg>',
        ];

        $weatherLabels = [
            'clear' => 'Sonnig / Klar',
            'cloudy' => 'Bewoelkt',
            'rainy' => 'Regen',
            'windy' => 'Windig',
        ];

        $temp = match($match?->kickoff_at?->format('F')) {
            'December', 'January', 'February' => mt_rand(-2, 5),
            'March', 'November' => mt_rand(4, 10),
            'April', 'October' => mt_rand(8, 15),
            'May', 'September' => mt_rand(14, 20),
            'June', 'July', 'August' => mt_rand(20, 32),
            default => 12,
        };
        
        // Use seed to make it consistent per match view if possible, or just rand for now
        $wind = mt_rand(5, 35);
        $rain = $weather === 'rainy' ? mt_rand(2, 12) : 0;
        
        $matchDate = $match?->kickoff_at?->format('d.m.Y') ?? now()->format('d.m.Y');
        $matchTime = $match?->kickoff_at?->format('H:i') ?? '15:30';
        $matchType = $match?->type === 'friendly' ? 'Freundschaft' : 'Liga';
    @endphp

    {{-- Override sticky header style for this page --}}
    <style>
        /* Force inherit position to disable sticky behavior from layout */
        header.sticky {
            position: relative !important;
            top: auto !important;
            z-index: 10 !important;
            background: transparent !important;
            backdrop-filter: none !important;
            border-bottom: none !important;
        }
        /* Hide the default header content if it tries to render empty slot */
        header.sticky > div {
            display: none !important;
        }
    </style>

    <div class="mb-8 flex flex-col items-center">
        {{-- Breadcrumb / Meta --}}
        <div class="flex items-center justify-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">
            <span class="text-cyan-400">Aufstellung</span>
            <span>•</span>
            <span>{{ $matchDate }}</span>
            <span>•</span>
            <span>{{ $matchTime }}</span>
            <span>•</span>
            <span>{{ $matchType }}</span>
        </div>

        {{-- Match Card --}}
        <div class="flex flex-col md:flex-row items-center justify-center gap-8 mb-8 w-full">
            {{-- Home Team --}}
            <div class="flex items-center gap-6 text-right flex-1 justify-end">
                <div class="flex flex-col items-end">
                    <h1 class="text-3xl md:text-4xl font-black text-white leading-none tracking-tight">
                        {{ $homeClub?->name ?? 'Heim' }}
                    </h1>
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-1">Heim</span>
                </div>
                <div class="h-20 w-20 md:h-24 md:w-24 rounded-full bg-slate-800 border-2 border-slate-700 p-3 shadow-2xl shadow-black/30">
                    <img src="{{ $homeClub?->logo_url }}" class="w-full h-full object-contain drop-shadow-md" alt="{{ $homeClub?->name }}">
                </div>
            </div>

            {{-- VS Badge --}}
            <div class="flex flex-col items-center px-4">
                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-slate-700 to-slate-800 border border-slate-600 flex items-center justify-center shadow-lg transform -skew-x-12 relative z-10">
                     <span class="text-base font-black text-slate-200 transform skew-x-12">VS</span>
                </div>
            </div>

            {{-- Away Team --}}
            <div class="flex items-center gap-6 text-left flex-1 justify-start">
                <div class="h-20 w-20 md:h-24 md:w-24 rounded-full bg-slate-800 border-2 border-slate-700 p-3 shadow-2xl shadow-black/30">
                     <img src="{{ $awayClub?->logo_url }}" class="w-full h-full object-contain drop-shadow-md" alt="{{ $awayClub?->name }}">
                </div>
                <div class="flex flex-col items-start">
                    <h1 class="text-3xl md:text-4xl font-black text-white leading-none tracking-tight">
                        {{ $awayClub?->name ?? 'Gast' }}
                    </h1>
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-1">Auswaerts</span>
                </div>
            </div>
        </div>

        {{-- Weather Pill --}}
        <div class="inline-flex items-center gap-4 px-6 py-2.5 rounded-full border border-slate-700/50 bg-slate-900/60 backdrop-blur-md shadow-lg mb-6">
            {!! $weatherIcons[$weather] ?? $weatherIcons['clear'] !!}
            <div class="flex items-center gap-3 text-xs font-bold text-slate-300">
                <span>{{ $weatherLabels[$weather] ?? 'Unbekannt' }}</span>
                <span class="text-slate-600">•</span>
                <span>{{ $temp }}°C</span>
                <span class="text-slate-600">•</span>
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ $wind }} km/h</span>
                </div>
                <span class="text-slate-600">•</span>
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    <span>{{ $rain }} mm</span>
                </div>
            </div>
        </div>

        {{-- Match Selector --}}
        @if ($clubMatches->count() > 1)
            <div class="relative group" x-data="{ open: false }">
                <button 
                    @click="open = !open" 
                    @click.away="open = false"
                    type="button" 
                    class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-cyan-400 transition-colors"
                >
                    <span>Anderes Match waehlen</span>
                    <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="transform opacity-0 scale-95 -translate-y-2"
                    class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-72 rounded-xl bg-slate-900 border border-slate-700 shadow-xl overflow-hidden z-50 py-1"
                    style="display: none;"
                >
                    @foreach ($clubMatches as $clubMatch)
                        @php
                            $isCurrent = $clubMatch->id === $match->id;
                            $isHome = (int) $clubMatch->home_club_id === (int) $lineup->club_id;
                            $opponent = $isHome ? $clubMatch->awayClub : $clubMatch->homeClub;
                            // Update URL to use the new lineup match redirector
                            $url = route('lineups.match', ['match' => $clubMatch->id]);
                        @endphp
                        <a href="{{ $url }}" class="block px-4 py-3 hover:bg-slate-800 transition-colors {{ $isCurrent ? 'bg-slate-800/50' : '' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] uppercase font-bold text-slate-500">{{ $clubMatch->kickoff_at?->format('d.m.Y H:i') }}</span>
                                @if($isCurrent)
                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,0.6)]"></span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <img src="{{ $opponent?->logo_url }}" class="w-5 h-5 object-contain opacity-80">
                                <span class="text-xs font-bold text-slate-200">{{ $opponent?->name }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <form id="lineupEditForm" method="POST" action="{{ route('lineups.update', $lineup) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- COLUMN 1: TACTICS & SETTINGS (Left Sidebar) -->
            <aside class="lg:w-80 flex-shrink-0 space-y-4">
                <section class="sim-card p-5 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-cyan-500/10 blur-xl group-hover:bg-cyan-500/20 transition duration-700"></div>
                    <div class="relative z-10">
                        <p class="sim-section-title mb-3">Grundeinstellungen</p>
                        <div class="space-y-4">
                            <div>
                                <label class="sim-label" for="name">Name der Aufstellung</label>
                                <input id="name" name="name" type="text" class="sim-input" value="{{ old('name', $lineup->name) }}" required>
                                <x-input-error :messages="$errors->get('name')" class="mt-1" />
                            </div>
                            <div>
                                <label class="sim-label" for="formation">Formation</label>
                                <select id="formation" name="formation" class="sim-select">
                                    @foreach ($formations as $formationOption)
                                        <option value="{{ $formationOption }}" @selected(old('formation', $formation) === $formationOption)>{{ $formationOption }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('formation')" class="mt-1" />
                            </div>
                        </div>
                    </div>
                </section>

                <section class="sim-card p-5 relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 h-32 w-32 rounded-full bg-indigo-500/10 blur-xl group-hover:bg-indigo-500/20 transition duration-700"></div>
                    <div class="relative z-10">
                        <p class="sim-section-title mb-3">Taktik</p>
                        <div class="space-y-4">
                            <div>
                                <label class="sim-label" for="mentality">Mentalitaet</label>
                                <select id="mentality" name="mentality" class="sim-select">
                                    <option value="defensive" @selected(old('mentality', $mentality) === 'defensive')>Defensiv</option>
                                    <option value="counter" @selected(old('mentality', $mentality) === 'counter')>Konter</option>
                                    <option value="normal" @selected(old('mentality', $mentality) === 'normal')>Normal</option>
                                    <option value="offensive" @selected(old('mentality', $mentality) === 'offensive')>Offensiv</option>
                                    <option value="all_out" @selected(old('mentality', $mentality) === 'all_out')>Brechstange</option>
                                </select>
                            </div>
                            <div>
                                <label class="sim-label" for="aggression">Aggressivitaet</label>
                                <select id="aggression" name="aggression" class="sim-select">
                                    <option value="cautious" @selected(old('aggression', $aggression) === 'cautious')>Vorsichtig</option>
                                    <option value="normal" @selected(old('aggression', $aggression) === 'normal')>Normal</option>
                                    <option value="aggressive" @selected(old('aggression', $aggression) === 'aggressive')>Aggressiv</option>
                                </select>
                            </div>
                            <div>
                                <label class="sim-label" for="line_height">Abwehrlinie</label>
                                <select id="line_height" name="line_height" class="sim-select">
                                    <option value="deep" @selected(old('line_height', $line_height) === 'deep')>Tief</option>
                                    <option value="normal" @selected(old('line_height', $line_height) === 'normal')>Normal</option>
                                    <option value="high" @selected(old('line_height', $line_height) === 'high')>Hoch</option>
                                    <option value="very_high" @selected(old('line_height', $line_height) === 'very_high')>Sehr Hoch</option>
                                </select>
                            </div>
                            <div>
                                <label class="sim-label" for="attack_focus">Angriffsfokus</label>
                                <select id="attack_focus" name="attack_focus" class="sim-select">
                                    <option value="center" @selected(old('attack_focus', $attackFocus) === 'center')>Zentrum</option>
                                    <option value="left" @selected(old('attack_focus', $attackFocus) === 'left')>Linke Flanke</option>
                                    <option value="right" @selected(old('attack_focus', $attackFocus) === 'right')>Rechte Flanke</option>
                                    <option value="both_wings" @selected(old('attack_focus', $attackFocus) === 'both_wings')>Beide Flanken</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-3 pt-2">
                                <label class="flex items-center gap-3 cursor-pointer group/check">
                                    <div class="relative flex items-center">
                                        <input type="checkbox" name="offside_trap" value="1" @checked(old('offside_trap', $offside_trap)) class="peer h-4 w-4 rounded border-slate-600 bg-slate-800 text-cyan-500 focus:ring-cyan-500/50 focus:ring-offset-0 transition-all">
                                    </div>
                                    <span class="text-sm font-medium text-slate-400 group-hover/check:text-slate-300 transition-colors">Abseitsfalle</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group/check">
                                    <div class="relative flex items-center">
                                        <input type="checkbox" name="time_wasting" value="1" @checked(old('time_wasting', $time_wasting)) class="peer h-4 w-4 rounded border-slate-600 bg-slate-800 text-cyan-500 focus:ring-cyan-500/50 focus:ring-offset-0 transition-all">
                                    </div>
                                    <span class="text-sm font-medium text-slate-400 group-hover/check:text-slate-300 transition-colors">Zeitspiel</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="sim-card p-5 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-fuchsia-500/5 blur-xl group-hover:bg-fuchsia-500/10 transition duration-700"></div>
                    <div class="relative z-10">
                        <p class="sim-section-title mb-3">Status & Vorlagen</p>
                        <div class="space-y-4">
                            <label class="sim-switch flex justify-between items-center px-1">
                                <span class="text-sm font-medium text-slate-300">Aktiv setzen</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" class="sr-only" @checked(old('is_active', $lineup->is_active))>
                                    <span class="sim-switch-track" aria-hidden="true"></span>
                                </div>
                            </label>

                            <div class="pt-4 border-t border-slate-700/50">
                                <label class="sim-label" for="template_name">Als neue Vorlage speichern</label>
                                <div class="mt-2 flex gap-2">
                                    <input id="template_name" name="template_name" type="text" placeholder="Vorlagen-Name..." class="sim-input px-3 py-2 text-xs flex-1">
                                    <button type="submit" name="save_as_template" value="1" class="sim-btn-muted px-3 py-2 text-xs font-bold uppercase tracking-wider">Save</button>
                                </div>
                            </div>

                            @if($templates->isNotEmpty())
                            <div class="pt-2">
                                <label class="sim-label text-[10px]">Vorlage laden</label>
                                <select onchange="if(this.value) window.location.search = '?template_id=' + this.value" class="sim-select px-3 py-2 text-xs mt-1">
                                    <option value="">- Vorlage waehlen -</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->formation }})</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>
                </section>
            </aside>

            <main class="flex-1 space-y-6">
                <article class="sim-card p-5 relative overflow-hidden">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-500/50 to-indigo-500/50"></div>
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-800/10 to-transparent pointer-events-none"></div>
                    
                    <div class="relative z-10 flex flex-wrap items-center justify-between gap-4 mb-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="sim-pill bg-slate-900/60 border-slate-700/50">Gesamt: <span class="text-white ml-1 font-bold">{{ $metrics['overall'] }}</span></span>
                            <span class="sim-pill bg-slate-900/60 border-slate-700/50 text-cyan-400">A: <span class="text-slate-300 ml-1 font-bold">{{ $metrics['attack'] }}</span></span>
                            <span class="sim-pill bg-slate-900/60 border-slate-700/50 text-indigo-400">M: <span class="text-slate-300 ml-1 font-bold">{{ $metrics['midfield'] }}</span></span>
                            <span class="sim-pill bg-slate-900/60 border-slate-700/50 text-fuchsia-400">V: <span class="text-slate-300 ml-1 font-bold">{{ $metrics['defense'] }}</span></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" name="action" value="auto_pick" class="sim-btn-muted px-4 py-2 text-xs font-bold uppercase tracking-widest hover:border-cyan-500/40">
                                Auto-Fill
                            </button>
                            <button type="submit" name="action" value="save" class="sim-btn-primary px-8 py-2 text-xs uppercase tracking-widest shadow-lg shadow-cyan-500/20">
                                Speichern
                            </button>
                        </div>
                    </div>

                    <x-input-error :messages="$errors->get('starter_slots')" class="mt-3" />

                    <!-- THE PITCH -->
                    <div class="sim-pitch relative overflow-hidden rounded-2xl shadow-2xl border border-slate-800/40">
                        {{-- SVG pitch markings (FIFA-spec: 68m × 105m, ×10 scale) --}}
                        <svg class="absolute inset-0 w-full h-full z-[1] pointer-events-none" viewBox="0 0 680 1050" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                {{-- Clip top penalty arc to only show below penalty area --}}
                                <clipPath id="clipTopArc">
                                    <rect x="0" y="165" width="680" height="885" />
                                </clipPath>
                                {{-- Clip bottom penalty arc to only show above penalty area --}}
                                <clipPath id="clipBottomArc">
                                    <rect x="0" y="0" width="680" height="885" />
                                </clipPath>
                            </defs>

                            {{-- All markings use white with slight transparency --}}
                            <g stroke="rgba(255,255,255,0.55)" stroke-width="3" fill="none">

                                {{-- Outer boundary --}}
                                <rect x="1.5" y="1.5" width="677" height="1047" />

                                {{-- Halfway line --}}
                                <line x1="0" y1="525" x2="680" y2="525" />

                                {{-- Center circle --}}
                                <circle cx="340" cy="525" r="91.5" />

                                {{-- Top penalty area --}}
                                <rect x="138" y="0" width="404" height="165" />

                                {{-- Bottom penalty area --}}
                                <rect x="138" y="885" width="404" height="165" />

                                {{-- Top goal area (6-yard box) --}}
                                <rect x="248" y="0" width="184" height="55" />

                                {{-- Bottom goal area (6-yard box) --}}
                                <rect x="248" y="995" width="184" height="55" />

                                {{-- Top goal net --}}
                                <rect x="303" y="-25" width="74" height="26" stroke-width="3" />

                                {{-- Bottom goal net --}}
                                <rect x="303" y="1049" width="74" height="26" stroke-width="3" />

                                {{-- Top penalty arc (D-shape, clipped) --}}
                                <circle cx="340" cy="110" r="91.5" clip-path="url(#clipTopArc)" />

                                {{-- Bottom penalty arc (D-shape, clipped) --}}
                                <circle cx="340" cy="940" r="91.5" clip-path="url(#clipBottomArc)" />

                                {{-- Corner arcs --}}
                                <path d="M 10 0 A 10 10 0 0 0 0 10" />
                                <path d="M 670 0 A 10 10 0 0 1 680 10" />
                                <path d="M 0 1040 A 10 10 0 0 0 10 1050" />
                                <path d="M 680 1040 A 10 10 0 0 1 670 1050" />
                            </g>

                            {{-- Center dot --}}
                            <circle cx="340" cy="525" r="5" fill="rgba(255,255,255,0.55)" />

                            {{-- Penalty spots --}}
                            <circle cx="340" cy="110" r="4" fill="rgba(255,255,255,0.55)" />
                            <circle cx="340" cy="940" r="4" fill="rgba(255,255,255,0.55)" />
                        </svg>

                        <div class="sim-pitch-canvas relative w-full h-full">
                            @foreach ($slots as $slot)
                                @php
                                    $slotSelectId = 'starter_slot_'.\Illuminate\Support\Str::slug($slot['slot'], '_');
                                    $assignedPlayerId = $starterDraft[$slot['slot']] ?? null;
                                    $assignedPlayer = $assignedPlayerId ? $clubPlayers->firstWhere('id', $assignedPlayerId) : null;
                                @endphp
                                <div
                                    class="sim-pitch-slot !absolute group/slot cursor-pointer"
                                    data-slot-container
                                    data-select-id="{{ $slotSelectId }}"
                                    data-slot-group="{{ $slot['group'] }}"
                                    data-slot-role="{{ $slot['label'] }}"
                                    style="left: {{ $slot['x'] }}%; top: {{ $slot['y'] }}%; transform: translate(-50%, -50%);"
                                >
                                    <span class="sim-pitch-slot-label opacity-40 group-hover/slot:opacity-100 transition-opacity">{{ $slot['label'] }}</span>
                                    
                                    <div class="sim-slot-ring">
                                        <div class="sim-slot-player {{ $assignedPlayer ? '' : 'hidden' }} flex flex-col items-center" data-slot-player>
                                            <div class="sim-slot-jersey {{ $assignedPlayer && $assignedPlayer->pivot?->is_captain ? 'border-amber-400/80 shadow-[0_0_15px_-3px_rgba(251,191,36,0.4)]' : 'border-slate-500/50' }}">
                                                <span class="jersey-num font-black text-xs">{{ $assignedPlayer ? ($assignedPlayer->shirt_number ?? '??') : '' }}</span>
                                                @if($assignedPlayer && $assignedPlayer->pivot?->is_captain)
                                                    <div class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-amber-400 text-[8px] font-black text-black ring-2 ring-slate-900 shadow-sm" title="Captain">C</div>
                                                @endif
                                                <button type="button" class="sim-slot-remove absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500/80 text-[10px] text-white transition-transform hover:scale-110 opacity-0 group-hover/slot:opacity-100" data-slot-remove>×</button>
                                            </div>
                                            <div class="sim-slot-info">
                                                <span class="truncate block text-[9px] uppercase tracking-tighter">{{ $assignedPlayer ? $assignedPlayer->last_name : '' }}</span>
                                                @if($assignedPlayer)
                                                <div class="flex items-center justify-center gap-1 mt-0.5 opacity-80">
                                                    <span class="text-[8px] font-bold text-cyan-400">{{ $assignedPlayer->overall }}</span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Empty Slot Placeholder -->
                                        <div class="sim-slot-empty {{ $assignedPlayer ? 'hidden' : 'flex' }} flex-col items-center justify-center transition-all duration-300" data-slot-empty>
                                             <div class="h-10 w-10 rounded-full border-2 border-dashed border-slate-700/50 bg-slate-900/20 group-hover/slot:border-cyan-500/30 group-hover/slot:bg-cyan-500/5 transition-colors duration-300 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-slate-700 group-hover/slot:text-cyan-500/30 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                             </div>
                                        </div>
                                    </div>

                                    <select id="{{ $slotSelectId }}" name="starter_slots[{{ $slot['slot'] }}]" class="hidden" data-dnd-select data-starter-select>
                                        <option value="">- Leer -</option>
                                        @foreach ($clubPlayers as $p)
                                            <option value="{{ $p->id }}" @selected($assignedPlayerId === $p->id)>{{ $p->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- THE BENCH -->
                    <div class="mt-8">
                        <p class="sim-section-title mb-4 italic text-slate-500">Auswechselbank (max. {{ $maxBenchPlayers }})</p>
                        <div class="grid gap-3 grid-cols-2 sm:grid-cols-5 md:grid-cols-5 lg:grid-cols-5 2xl:grid-cols-5">
                            @for ($i = 0; $i < $maxBenchPlayers; $i++)
                                @php
                                    $benchSelectId = 'bench_slot_'.$i;
                                    $benchPlayerId = $benchDraft[$i] ?? null;
                                    $benchPlayer = $benchPlayerId ? $clubPlayers->firstWhere('id', $benchPlayerId) : null;
                                @endphp
                                <div
                                    class="sim-bench-slot relative group/bench flex flex-col items-center justify-center p-2 rounded-xl border border-dashed border-slate-700/40 bg-slate-900/20 min-h-[90px] transition-all duration-300 hover:border-cyan-500/30 hover:bg-cyan-500/5"
                                    data-slot-container
                                    data-select-id="{{ $benchSelectId }}"
                                    data-slot-group="BENCH"
                                    data-slot-role="BANK"
                                >
                                    <div class="sim-slot-player {{ $benchPlayer ? '' : 'hidden' }} flex flex-col items-center w-full" data-slot-player>
                                        <div class="w-8 h-8 rounded-full border border-slate-600 bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-300 mb-1 relative">
                                            {{ $benchPlayer ? ($benchPlayer->shirt_number ?? '??') : '' }}
                                            <button type="button" class="sim-slot-remove absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500/90 text-[10px] text-white opacity-0 group-hover/bench:opacity-100" data-slot-remove>×</button>
                                        </div>
                                        <span class="sim-slot-player-name truncate text-[9px] font-bold uppercase text-slate-400 w-full text-center" data-slot-player-name>
                                            {{ $benchPlayer ? $benchPlayer->last_name : '-' }}
                                        </span>
                                    </div>
                                    
                                    <div class="sim-slot-hint {{ $benchPlayer ? 'hidden' : 'flex' }} flex-col items-center opacity-20 group-hover/bench:opacity-40 transition-opacity" data-slot-hint>
                                        <span class="text-[10px] font-black">{{ $i + 1 }}</span>
                                    </div>

                                    <select id="{{ $benchSelectId }}" name="bench_slots[]" class="hidden" data-dnd-select data-bench-select>
                                        <option value="">Slot {{ $i + 1 }}</option>
                                        @foreach ($clubPlayers as $p)
                                            <option value="{{ $p->id }}" @selected($benchPlayerId === $p->id)>{{ $p->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- ROLES & PIECES AREA -->
                    <div class="mt-8 grid gap-6 sm:grid-cols-2">
                        <section class="sim-card-soft p-5 bg-slate-900/40 border-slate-800 relative overflow-hidden group">
                             <div class="absolute -right-4 -bottom-4 h-16 w-16 rounded-full bg-cyan-500/5 blur-lg group-hover:bg-cyan-500/10 transition"></div>
                             <p class="sim-label text-cyan-500/70 border-b border-slate-800 pb-2 mb-4 font-bold flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Verantwortlichkeiten
                             </p>
                             <div class="space-y-4">
                                <div class="flex items-center justify-between gap-4">
                                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-tighter">Kapitaen</label>
                                    <select name="captain_player_id" class="sim-select py-1.5 px-3 text-xs w-48 bg-slate-950/80 border-slate-700/50">
                                        <option value="">- Auto -</option>
                                        @foreach ($clubPlayers as $player)
                                            <option value="{{ $player->id }}" @selected((int) old('captain_player_id', $captainPlayerId) === $player->id)>
                                                {{ $player->full_name }} ({{ $player->overall }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-tighter">Elfmeter</label>
                                    <select name="penalty_taker_player_id" class="sim-select py-1.5 px-3 text-xs w-48 bg-slate-950/80 border-slate-700/50">
                                        <option value="">- Waehlen -</option>
                                        @foreach ($clubPlayers as $player)
                                            <option value="{{ $player->id }}" @selected((int) old('penalty_taker_player_id', $setPieces['penalty_taker_player_id']) === $player->id)>
                                                {{ $player->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                             </div>
                        </section>

                        <section class="sim-card-soft p-5 bg-slate-900/40 border-slate-800 relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 h-16 w-16 rounded-full bg-indigo-500/5 blur-lg group-hover:bg-indigo-500/10 transition"></div>
                            <p class="sim-label text-indigo-400/70 border-b border-slate-800 pb-2 mb-4 font-bold flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2L4 5V11C4 16.1 7.4 20.9 12 22C16.6 20.9 20 16.1 20 11V5L12 2Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                Standards
                            </p>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between gap-4">
                                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-tighter">FS Nah / Fern</label>
                                    <div class="flex gap-2 w-48">
                                        <select name="free_kick_near_player_id" class="sim-select py-1.5 !px-1.5 text-xs flex-1 bg-slate-950/80 border-slate-700/50" title="Freistoss Nah">
                                            <option value="">N</option>
                                            @foreach ($clubPlayers as $p)<option value="{{ $p->id }}" @selected((int)old('free_kick_near_player_id', $setPieces['free_kick_near_player_id'] ?? 0) === $p->id)>{{ $p->last_name }}</option>@endforeach
                                        </select>
                                        <select name="free_kick_far_player_id" class="sim-select py-1.5 !px-1.5 text-xs flex-1 bg-slate-950/80 border-slate-700/50" title="Freistoss Fern">
                                            <option value="">F</option>
                                            @foreach ($clubPlayers as $p)<option value="{{ $p->id }}" @selected((int)old('free_kick_far_player_id', $setPieces['free_kick_far_player_id'] ?? 0) === $p->id)>{{ $p->last_name }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-tighter">Ecke L / R</label>
                                    <div class="flex gap-2 w-48">
                                        <select name="corner_left_taker_player_id" class="sim-select py-1.5 !px-1.5 text-xs flex-1 bg-slate-950/80 border-slate-700/50" title="Ecke Links">
                                            <option value="">L</option>
                                            @foreach ($clubPlayers as $p)<option value="{{ $p->id }}" @selected((int)old('corner_left_taker_player_id', $setPieces['corner_left_taker_player_id'] ?? 0) === $p->id)>{{ $p->last_name }}</option>@endforeach
                                        </select>
                                        <select name="corner_right_taker_player_id" class="sim-select py-1.5 !px-1.5 text-xs flex-1 bg-slate-950/80 border-slate-700/50" title="Ecke Rechts">
                                            <option value="">R</option>
                                            @foreach ($clubPlayers as $p)<option value="{{ $p->id }}" @selected((int)old('corner_right_taker_player_id', $setPieces['corner_right_taker_player_id'] ?? 0) === $p->id)>{{ $p->last_name }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="mt-10 flex justify-end gap-4 border-t border-slate-800/80 pt-6">
                        <a href="{{ route('lineups.show', $lineup) }}" class="sim-btn-muted px-10 py-3 uppercase tracking-tighter text-xs font-black">Abbrechen</a>
                        <button type="submit" name="action" value="save" class="sim-btn-primary px-14 py-3 uppercase tracking-tighter text-xs font-black shadow-xl shadow-cyan-500/20">Speichern</button>
                    </div>
                </article>
            </main>

            <!-- COLUMN 3: PLAYER POOL (Right Sidebar) -->
            <aside class="lg:w-80 flex-shrink-0">
                <section class="sim-card p-5 h-full flex flex-col relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-cyan-500/5 blur-2xl group-hover:bg-cyan-500/10 transition duration-1000"></div>
                    
                    <div class="relative z-10 flex flex-col h-full">
                        <p class="sim-section-title mb-4">Spieler-Pool</p>
                        
                        <div class="mb-5">
                            <div class="relative group/search">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within/search:text-cyan-500/60 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </span>
                                <input type="text" placeholder="Spieler suchen..." x-model="searchTerm" class="sim-input pl-9 py-2 text-xs" @input="filterPlayers()">
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto space-y-6 pr-1 custom-scrollbar" style="max-height: calc(100vh - 300px);">
                            @foreach ($groupLabels as $code => $label)
                                <div class="player-group" data-group="{{ $code }}">
                                    <h3 class="flex items-center gap-2 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-3 sticky top-0 bg-slate-900/40 backdrop-blur-sm py-1 z-10 border-b border-white/5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500/40"></span>
                                        {{ $label }}
                                    </h3>
                                    <div class="grid gap-2">
                                        @forelse ($playersByPosition->get($code, collect()) as $player)
                                            @php
                                                $position = $player->position_main ?? $player->position;
                                                $isSelected = in_array($player->id, $selectedPlayerIds, true);
                                            @endphp
                                            <div
                                                class="sim-card-soft sim-player-card group/p p-2.5 cursor-grab active:cursor-grabbing transition-all border-slate-700/30 hover:border-cyan-500/30 {{ $isSelected ? 'opacity-50 ring-1 ring-cyan-500/20' : '' }}"
                                                draggable="true"
                                                data-player-id="{{ $player->id }}"
                                                data-player-name="{{ $player->full_name }}"
                                                data-player-last-name="{{ $player->last_name }}"
                                                data-player-number="{{ $player->shirt_number ?? '??' }}"
                                                data-player-overall="{{ $player->overall }}"
                                                data-position-main="{{ $player->position_main ?? $player->position }}"
                                                data-position-second="{{ $player->position_second ?? '' }}"
                                                data-position-third="{{ $player->position_third ?? '' }}"
                                                x-show="searchTerm === '' || '{{ strtolower($player->full_name) }}'.includes(searchTerm.toLowerCase())"
                                            >
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2.5 min-w-0">
                                                        <div class="w-7 h-7 rounded border border-slate-700 bg-slate-800/50 flex items-center justify-center shrink-0">
                                                            <span class="text-[10px] font-black text-cyan-400">{{ $player->overall }}</span>
                                                        </div>
                                                        <div class="truncate">
                                                            <p class="text-[11px] font-bold text-slate-200 truncate group-hover/p:text-white transition-colors">{{ $player->last_name }}</p>
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="text-[9px] font-black uppercase text-slate-500">{{ $position }}</span>
                                                                <span class="text-[9px] text-slate-600">#{{ $player->shirt_number ?? '??' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex flex-col gap-1 items-end">
                                                        <div class="flex gap-1 opacity-0 group-hover/p:opacity-100 transition-opacity">
                                                            <button type="button" class="h-5 w-5 flex items-center justify-center rounded bg-slate-800 hover:bg-cyan-500/20 hover:text-cyan-400 text-slate-400 border border-slate-700 transition-all font-bold" data-add-pitch title="Auf Feld setzen">+</button>
                                                            <button type="button" class="h-5 w-5 flex items-center justify-center rounded bg-slate-800 hover:bg-indigo-500/20 hover:text-indigo-400 text-slate-400 border border-slate-700 transition-all font-bold" data-add-bench title="Auf Bank setzen">B</button>
                                                        </div>
                                                        <div class="flex items-center gap-1.5 {{ $isSelected ? '' : 'hidden' }}" data-player-picked-wrapper>
                                                            <span class="text-[8px] font-black uppercase tracking-widest text-cyan-500/60" data-player-picked>Nominiert</span>
                                                            <button type="button" class="text-rose-500 hover:text-rose-400 transition-colors" data-remove-player title="Entfernen">
                                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-[10px] text-slate-600 italic">Keine Spieler in dieser Gruppe</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </form>

    <p class="mt-4 text-xs text-slate-400">
        Hinweis: Wie in OpenWS kannst du Spieler ziehen und auf Feld-/Bank-Slots ablegen oder per Buttons schnell zuweisen.
    </p>

    @php
        $pf = $positionFit ?? ['main' => 1.0, 'second' => 0.92, 'third' => 0.84, 'foreign' => 0.76, 'foreign_gk' => 0.55];
    @endphp
    <script>
        window.__positionFit = @json($pf);
    </script>
    <script>
        (function () {
            const lineupMatchSelect = document.getElementById('lineupMatchSelect');
            const openMatchLineupLink = document.getElementById('openMatchLineupLink');
            const formationSelect = document.getElementById('formation');

            if (lineupMatchSelect && openMatchLineupLink) {
                lineupMatchSelect.addEventListener('change', function () {
                    if (this.value) {
                        openMatchLineupLink.href = this.value;
                    }
                });
            }

            if (formationSelect) {
                formationSelect.addEventListener('change', function () {
                    const url = new URL(window.location.href);
                    url.searchParams.set('formation', this.value);
                    window.location.href = url.toString();
                });
            }

            const playerCards = Array.from(document.querySelectorAll('[data-player-id]'));
            const slotContainers = Array.from(document.querySelectorAll('[data-slot-container]'));
            const managedSelects = Array.from(document.querySelectorAll('[data-dnd-select]'));
            const starterSelects = managedSelects.filter(function (select) { return select.hasAttribute('data-starter-select'); });
            const benchSelects = managedSelects.filter(function (select) { return select.hasAttribute('data-bench-select'); });
            const playerCardMap = new Map(playerCards.map(function (card) { return [String(card.dataset.playerId), card]; }));

            function groupFromPosition(position) {
                const code = String(position || '').trim().toUpperCase();
                if (!code) {
                    return null;
                }

                if (code === 'TW' || code === 'GK') {
                    return 'GK';
                }
                if (['LV', 'IV', 'RV', 'LWB', 'RWB', 'DEF'].includes(code) || code.startsWith('IV')) {
                    return 'DEF';
                }
                if (['LM', 'ZM', 'RM', 'DM', 'OM', 'LAM', 'ZOM', 'RAM', 'MID'].includes(code) || code.startsWith('ZM') || code.startsWith('DM')) {
                    return 'MID';
                }

                return 'FWD';
            }

            function getSelectForContainer(container) {
                return document.getElementById(container.dataset.selectId);
            }

            function findSelectWithPlayer(playerId) {
                return managedSelects.find(function (select) {
                    return String(select.value) === String(playerId);
                }) || null;
            }

            function findPlayerCard(playerId) {
                return playerCardMap.get(String(playerId)) || null;
            }

            function fitsSlot(container, playerCard) {
                const slotGroup = container.dataset.slotGroup || '';
                if (!slotGroup || slotGroup === 'BENCH') {
                    return '';
                }

                const mainGroup = groupFromPosition(playerCard.dataset.positionMain);
                const secondGroup = groupFromPosition(playerCard.dataset.positionSecond);
                const thirdGroup = groupFromPosition(playerCard.dataset.positionThird);

                if (mainGroup && mainGroup === slotGroup) {
                    return 'primary';
                }
                if ((secondGroup && secondGroup === slotGroup) || (thirdGroup && thirdGroup === slotGroup)) {
                    return 'secondary';
                }

                return 'wrong';
            }

            /**
             * Returns a fit multiplier using the ACP-configured values from
             * window.__positionFit (set via config('simulation.position_fit.*')).
             */
            function fitFactor(playerCard, container) {
                var pf = window.__positionFit || {};
                var slotGroup = container.dataset.slotGroup || '';
                if (!slotGroup || slotGroup === 'BENCH') {
                    return pf.main || 1.0;
                }

                var mainGroup  = groupFromPosition(playerCard.dataset.positionMain);
                var secondGroup = groupFromPosition(playerCard.dataset.positionSecond);
                var thirdGroup = groupFromPosition(playerCard.dataset.positionThird);

                if (mainGroup && mainGroup === slotGroup) {
                    return pf.main || 1.0;
                }
                if (secondGroup && secondGroup === slotGroup) {
                    return pf.second || 0.92;
                }
                if (thirdGroup && thirdGroup === slotGroup) {
                    return pf.third || 0.84;
                }
                if (mainGroup === 'GK' || slotGroup === 'GK') {
                    return pf.foreign_gk || 0.55;
                }
                return pf.foreign || 0.76;
            }

            function syncSlotView(container) {
                const select = getSelectForContainer(container);
                if (!select) {
                    return;
                }

                const slotPlayer = container.querySelector('[data-slot-player]');
                const slotEmpty = container.querySelector('[data-slot-empty]');
                const slotName = container.querySelector('.sim-slot-info span');
                const slotOverall = container.querySelector('.sim-slot-info .text-cyan-400');
                const slotNum = container.querySelector('.jersey-num');
                const slotHint = container.querySelector('[data-slot-hint]');
                const slotRemove = container.querySelector('[data-slot-remove]');
                const selectedValue = String(select.value || '');

                container.classList.remove('sim-slot-state-primary', 'sim-slot-state-secondary', 'sim-slot-state-wrong');

                if (!selectedValue) {
                    if (slotPlayer) slotPlayer.classList.add('hidden');
                    if (slotEmpty) slotEmpty.classList.remove('hidden');
                    if (slotHint) slotHint.classList.remove('hidden');
                    if (slotRemove) slotRemove.classList.add('hidden');
                    return;
                }

                const playerCard = findPlayerCard(selectedValue);
                if (slotPlayer) slotPlayer.classList.remove('hidden');
                if (slotEmpty) slotEmpty.classList.add('hidden');
                if (slotHint) slotHint.classList.add('hidden');
                if (slotRemove) slotRemove.classList.remove('hidden');

                if (playerCard) {
                    if (slotName) slotName.textContent = playerCard.dataset.playerLastName || playerCard.dataset.playerName;
                    if (slotNum) slotNum.textContent = playerCard.dataset.playerNumber || '??';

                    // Compute effective OVR using the position fit factor
                    var baseOverall = parseFloat(playerCard.dataset.playerOverall) || 0;
                    var factor = fitFactor(playerCard, container);
                    var effectiveOvr = Math.round(baseOverall * factor);

                    if (slotOverall) {
                        if (factor < 1.0) {
                            // Show effective value + factor percentage
                            slotOverall.textContent = effectiveOvr + ' (' + Math.round(factor * 100) + '%)';
                        } else {
                            slotOverall.textContent = baseOverall || '??';
                        }
                    }

                    const fit = fitsSlot(container, playerCard);
                    if (fit) {
                        container.classList.add('sim-slot-state-' + fit);
                    }
                }
            }

            function syncPlayerCards() {
                playerCards.forEach(function (card) {
                    const playerId = card.dataset.playerId;
                    const selectedIn = findSelectWithPlayer(playerId);
                    const pickedMarker = card.querySelector('[data-player-picked]');
                    const addPitchBtn = card.querySelector('[data-add-pitch]');
                    const addBenchBtn = card.querySelector('[data-add-bench]');
                    const removeBtn = card.querySelector('[data-remove-player]');

                    const assigned = !!selectedIn;
                    card.classList.toggle('opacity-50', assigned);
                    card.classList.toggle('ring-1', assigned);
                    card.classList.toggle('ring-cyan-500/20', assigned);
                    
                    const wrapper = card.querySelector('[data-player-picked-wrapper]');
                    if (wrapper) {
                        wrapper.classList.toggle('hidden', !assigned);
                    }
                    if (addPitchBtn) {
                        addPitchBtn.classList.toggle('hidden', assigned);
                    }
                    if (addBenchBtn) {
                        addBenchBtn.classList.toggle('hidden', assigned);
                    }
                    if (removeBtn) {
                        removeBtn.classList.toggle('hidden', !assigned);
                    }
                });
            }

            function syncAll() {
                slotContainers.forEach(syncSlotView);
                syncPlayerCards();
            }

            function triggerChange(select) {
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function assignPlayerToSelect(select, playerId) {
                if (!select) {
                    return false;
                }

                const playerCard = findPlayerCard(playerId);
                if (!playerCard) {
                    return false;
                }

                const currentSelect = findSelectWithPlayer(playerId);
                const targetCurrent = select.value ? String(select.value) : '';

                if (currentSelect && currentSelect !== select) {
                    if (targetCurrent) {
                        currentSelect.value = targetCurrent;
                    } else {
                        currentSelect.value = '';
                    }
                    triggerChange(currentSelect);
                }

                select.value = String(playerId);
                triggerChange(select);
                return true;
            }

            function clearPlayerEverywhere(playerId) {
                const select = findSelectWithPlayer(playerId);
                if (!select) {
                    return;
                }
                select.value = '';
                triggerChange(select);
            }

            function clearContainer(container) {
                const select = getSelectForContainer(container);
                if (!select) {
                    return;
                }
                select.value = '';
                triggerChange(select);
            }

            function findFirstEmpty(selects) {
                return selects.find(function (select) { return !select.value; }) || null;
            }

            function findBestStarterSelect(playerCard) {
                const positionMain = String(playerCard.dataset.positionMain || '').toUpperCase();
                const positionSecond = String(playerCard.dataset.positionSecond || '').toUpperCase();
                const positionThird = String(playerCard.dataset.positionThird || '').toUpperCase();

                function byRole(role) {
                    return starterSelects.find(function (select) {
                        return !select.value && String(select.dataset.slotRole || '').toUpperCase() === role;
                    }) || null;
                }

                function byGroup(position) {
                    const group = groupFromPosition(position);
                    if (!group) {
                        return null;
                    }

                    return starterSelects.find(function (select) {
                        return !select.value && String(select.dataset.slotGroup || '') === group;
                    }) || null;
                }

                return byRole(positionMain)
                    || byRole(positionSecond)
                    || byRole(positionThird)
                    || byGroup(positionMain)
                    || byGroup(positionSecond)
                    || byGroup(positionThird)
                    || findFirstEmpty(starterSelects);
            }

            playerCards.forEach(function (card) {
                card.addEventListener('dragstart', function (event) {
                    event.dataTransfer.setData('text/plain', String(card.dataset.playerId));
                    event.dataTransfer.effectAllowed = 'move';
                    card.classList.add('sim-player-card-dragging');
                });

                card.addEventListener('dragend', function () {
                    card.classList.remove('sim-player-card-dragging');
                });

                card.addEventListener('dblclick', function () {
                    const target = findBestStarterSelect(card);
                    if (!target) {
                        return;
                    }

                    assignPlayerToSelect(target, card.dataset.playerId);
                });

                const addPitchBtn = card.querySelector('[data-add-pitch]');
                const addBenchBtn = card.querySelector('[data-add-bench]');
                const removeBtn = card.querySelector('[data-remove-player]');

                if (addPitchBtn) {
                    addPitchBtn.addEventListener('click', function () {
                        const target = findBestStarterSelect(card);
                        if (!target) {
                            return;
                        }
                        assignPlayerToSelect(target, card.dataset.playerId);
                    });
                }

                if (addBenchBtn) {
                    addBenchBtn.addEventListener('click', function () {
                        const target = findFirstEmpty(benchSelects);
                        if (!target) {
                            return;
                        }
                        assignPlayerToSelect(target, card.dataset.playerId);
                    });
                }

                if (removeBtn) {
                    removeBtn.addEventListener('click', function () {
                        clearPlayerEverywhere(card.dataset.playerId);
                    });
                }
            });

            slotContainers.forEach(function (container) {
                const select = getSelectForContainer(container);
                const removeBtn = container.querySelector('[data-slot-remove]');

                container.addEventListener('dragover', function (event) {
                    event.preventDefault();
                    container.classList.add('sim-slot-drop-hover');
                });

                container.addEventListener('dragleave', function () {
                    container.classList.remove('sim-slot-drop-hover');
                });

                container.addEventListener('drop', function (event) {
                    event.preventDefault();
                    container.classList.remove('sim-slot-drop-hover');
                    const playerId = event.dataTransfer.getData('text/plain');
                    if (!playerId || !playerCardMap.has(String(playerId))) {
                        return;
                    }
                    assignPlayerToSelect(select, playerId);
                });

                if (removeBtn) {
                    removeBtn.addEventListener('click', function () {
                        clearContainer(container);
                    });
                }
            });

            managedSelects.forEach(function (select) {
                const container = slotContainers.find(function (candidate) {
                    return candidate.dataset.selectId === select.id;
                });

                if (container) {
                    select.dataset.slotGroup = container.dataset.slotGroup || '';
                    select.dataset.slotRole = container.dataset.slotRole || '';
                }

                select.addEventListener('change', syncAll);
            });

            syncAll();
        })();
    </script>
</x-app-layout>
