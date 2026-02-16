@php
    $statusLabel = match ($match->status) {
        'played' => 'Beendet',
        'live' => $match->live_paused ? 'Pausiert' : 'Live',
        default => ucfirst($match->status),
    };
    $eventLabels = [
        'goal' => 'Tor',
        'assist' => 'Assist',
        'yellow_card' => 'Gelbe Karte',
        'red_card' => 'Rote Karte',
        'substitution' => 'Wechsel',
        'injury' => 'Verletzung',
        'chance' => 'Chance',
        'corner' => 'Ecke',
        'foul' => 'Foul',
        'offside' => 'Abseits',
        'penalty' => 'Elfmeter',
        'save' => 'Parade',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="sim-heading">
                {{ __('Match Center') }}
            </h2>
        </div>
    </x-slot>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 space-y-0" id="match-live-root">

        @if ($match->status === 'scheduled')
            {{-- PRE-MATCH VIEW --}}
            <div class="bg-slate-800 rounded-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-slate-900/50 p-6 text-center border-b border-slate-950">
                    <div class="flex justify-between items-center mb-4 px-4">
                        <div class="flex items-center gap-4">
                            <img src="{{ $match->homeClub->logo_url }}" class="w-16 h-16 object-contain">
                            <span class="text-2xl font-bold text-slate-100">{{ $match->homeClub->name }}</span>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-slate-100">{{ $match->kickoff_at->format('d.m.Y - H:i') }}
                            </div>
                            <div class="text-sm text-slate-400 mt-1">Noch nicht gespielt</div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-2xl font-bold text-slate-100">{{ $match->awayClub->name }}</span>
                            <img src="{{ $match->awayClub->logo_url }}" class="w-16 h-16 object-contain">
                        </div>
                    </div>

                    <!-- Info Bar -->
                    <div
                        class="flex justify-center gap-6 text-sm text-slate-300 bg-slate-800/80 py-2 rounded-full inline-flex px-8 border border-slate-950 mx-auto">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            {{ $match->stadiumClub?->stadium?->name ?? 'Unbekanntes Stadion' }}
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            {{ ucfirst($match->weather ?? 'Klar') }}
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Schiedsrichter: Unbekannt
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            {{ ucfirst($match->type) }}
                        </span>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="px-6 py-4">
                    <div class="flex gap-4 border-b border-slate-950 pb-2 mb-6">
                        <button class="px-4 py-2 text-cyan-400 border-b-2 border-cyan-400 font-semibold">Vorschau</button>
                    </div>

                    <div class="bg-slate-900/40 rounded-lg p-6 border border-slate-950">
                        <h3 class="text-xl font-bold text-center text-blue-200 mb-2">🔮 Spielvorschau</h3>
                        <p class="text-center text-slate-400 text-sm mb-6">Alle Statistiken, Managerinfos & Formkurven vor
                            dem großen Duell</p>

                        <div class="flex justify-center gap-2 mb-8">
                            <button
                                class="px-4 py-1.5 bg-cyan-600/20 text-cyan-300 border border-cyan-500/30 rounded text-sm hover:bg-cyan-600/30 transition">⚔️
                                Head-to-Head</button>
                            <button
                                class="px-4 py-1.5 bg-slate-700/30 text-slate-400 border border-slate-600/30 rounded text-sm hover:bg-slate-700/50 transition">🧠
                                Manager</button>
                            <button
                                class="px-4 py-1.5 bg-slate-700/30 text-slate-400 border border-slate-600/30 rounded text-sm hover:bg-slate-700/50 transition">🧩
                                Aufstellungen</button>
                            <button
                                class="px-4 py-1.5 bg-slate-700/30 text-slate-400 border border-slate-600/30 rounded text-sm hover:bg-slate-700/50 transition">📈
                                Formkurve</button>
                            <button
                                class="px-4 py-1.5 bg-slate-700/30 text-slate-400 border border-slate-600/30 rounded text-sm hover:bg-slate-700/50 transition">🆚
                                Direkte Duelle</button>
                            <button
                                class="px-4 py-1.5 bg-slate-700/30 text-slate-400 border border-slate-600/30 rounded text-sm hover:bg-slate-700/50 transition">🤖
                                KI-Prognose</button>
                        </div>

                        <div class="bg-slate-900/60 rounded-xl border border-slate-950 p-6 max-w-4xl mx-auto">
                            <h4 class="text-center text-lg font-semibold text-slate-300 mb-6">⚔️ Teamvergleich</h4>

                            <div class="flex items-center justify-between mb-8 px-12">
                                <div class="text-center">
                                    <img src="{{ $match->homeClub->logo_url }}"
                                        class="w-20 h-20 mx-auto mb-2 object-contain">
                                    <div class="font-bold text-slate-100">{{ $match->homeClub->name }}</div>
                                    <div class="text-xs text-slate-500">#- in der Liga</div>
                                </div>
                                <div class="text-slate-500 font-bold text-xl">vs</div>
                                <div class="text-center">
                                    <img src="{{ $match->awayClub->logo_url }}"
                                        class="w-20 h-20 mx-auto mb-2 object-contain">
                                    <div class="font-bold text-slate-100">{{ $match->awayClub->name }}</div>
                                    <div class="text-xs text-slate-500">#- in der Liga</div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Compare Row: Market Value -->
                                <div
                                    class="grid grid-cols-[1fr_auto_1fr] gap-4 items-center py-3 border-b border-slate-950">
                                    <div class="text-right font-mono text-cyan-400">
                                        {{ number_format($comparison['home']['market_value'] / 1000000, 1, ',', '.') }} M €
                                    </div>
                                    <div
                                        class="text-xs text-orange-400 uppercase tracking-wider font-semibold px-4 flex items-center gap-2">
                                        💰 Marktwert
                                    </div>
                                    <div class="text-left font-mono text-cyan-400">
                                        {{ number_format($comparison['away']['market_value'] / 1000000, 1, ',', '.') }} M €
                                    </div>
                                </div>
                                <!-- Compare Row: Strength -->
                                <div
                                    class="grid grid-cols-[1fr_auto_1fr] gap-4 items-center py-3 border-b border-slate-950">
                                    <div class="text-right font-mono text-slate-200">
                                        {{ number_format($comparison['home']['strength'], 1) }}
                                    </div>
                                    <div
                                        class="text-xs text-green-400 uppercase tracking-wider font-semibold px-4 flex items-center gap-2">
                                        📊 Teamstärke
                                    </div>
                                    <div class="text-left font-mono text-slate-200">
                                        {{ number_format($comparison['away']['strength'], 1) }}
                                    </div>
                                </div>
                                <!-- Compare Row: Age -->
                                <div
                                    class="grid grid-cols-[1fr_auto_1fr] gap-4 items-center py-3 border-b border-slate-950">
                                    <div class="text-right font-mono text-slate-200">
                                        {{ number_format($comparison['home']['avg_age'], 1) }} J.
                                    </div>
                                    <div
                                        class="text-xs text-orange-300 uppercase tracking-wider font-semibold px-4 flex items-center gap-2">
                                        🎂 Ø-Alter
                                    </div>
                                    <div class="text-left font-mono text-slate-200">
                                        {{ number_format($comparison['away']['avg_age'], 1) }} J.
                                    </div>
                                </div>
                                <!-- Compare Row: Rank -->
                                <div class="grid grid-cols-[1fr_auto_1fr] gap-4 items-center py-3">
                                    <div class="text-right font-mono text-slate-200">{{ $comparison['home']['rank'] }}</div>
                                    <div
                                        class="text-xs text-blue-300 uppercase tracking-wider font-semibold px-4 flex items-center gap-2">
                                        📈 Tabellenplatz
                                    </div>
                                    <div class="text-left font-mono text-slate-200">{{ $comparison['away']['rank'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($canSimulate)
                        <div class="mt-6 flex justify-center gap-4">
                            <form action="{{ route('matches.live.start', $match) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="bg-green-600 hover:bg-green-500 text-white font-bold py-3 px-8 rounded shadow-lg transition transform hover:scale-105">
                                    Live-Simulation starten
                                </button>
                            </form>
                            <form action="{{ route('matches.simulate', $match) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="bg-slate-700 hover:bg-slate-600 text-white font-bold py-3 px-8 rounded shadow-lg transition">
                                    Schnell-Simulation
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

        @else
            {{-- LIVE / FINISHED VIEW --}}

            <!-- Live Header -->
            <div class="bg-slate-800/90 rounded-t-lg backdrop-blur-sm" style="overflow: visible;">
                <!-- Top Info Bar -->
                <div
                    class="bg-slate-900/80 px-4 py-2 flex flex-wrap justify-between items-center text-xs text-slate-400 border-b border-slate-950">
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-500"></span>
                            {{ $match->stadiumClub?->stadium?->name ?? 'Stadion' }}</span>
                        <span class="flex items-center gap-1"><span class="w-4 text-center">👥</span>
                            {{ number_format($match->attendance, 0, ',', '.') }} Zuschauer</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1"><span class="w-4 text-center">☁️</span>
                            {{ ucfirst($match->weather) }}</span>
                        <span class="flex items-center gap-1"><span class="w-4 text-center">🌱</span> Hybridrasen <span
                                class="bg-yellow-600/20 text-yellow-500 px-1 rounded">Gut</span></span>
                        <span class="flex items-center gap-1"><span class="w-4 text-center">🏠</span> Kabinen <span
                                class="bg-yellow-600/20 text-yellow-500 px-1 rounded">Gut</span></span>
                        <span class="flex items-center gap-1"><span class="w-4 text-center">⚖️</span> Alejandro Muñiz
                            Ruiz</span>
                    </div>
                </div>

                <!-- Scoreboard -->
                <div class="py-8 px-6 bg-gradient-to-b from-slate-800 via-slate-850 to-slate-900 relative">
                    {{-- Subtle gradient accent behind score --}}
                    <div class="absolute inset-0 pointer-events-none"
                        style="background: radial-gradient(ellipse at center 40%, rgba(6,182,212,0.06) 0%, transparent 60%)">
                    </div>
                    <div class="flex items-center justify-between max-w-4xl mx-auto relative z-10">
                        <!-- Home -->
                        <div class="flex items-center gap-5 w-1/3">
                            <img src="{{ $match->homeClub->logo_url }}"
                                class="w-20 h-20 object-contain filter drop-shadow-[0_4px_12px_rgba(0,0,0,0.4)]">
                            <div class="text-left">
                                <span
                                    class="block text-[10px] text-cyan-500/60 uppercase tracking-[0.2em] font-bold">Heim</span>
                                <span
                                    class="block text-2xl font-bold text-white leading-tight tracking-tight">{{ $match->homeClub->name }}</span>
                            </div>
                        </div>

                        <!-- Score -->
                        <div class="text-center w-1/3">
                            <div class="text-7xl font-black text-white tracking-tighter sim-score-glow" id="live-score">
                                {{ $match->home_score ?? '-' }} : {{ $match->away_score ?? '-' }}
                            </div>
                            <div class="mt-3 inline-flex items-center gap-2 px-4 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                                                                                {{ $match->status === 'played' ? 'bg-slate-700/60 text-slate-300' : 'bg-green-500/10 text-green-400 border border-green-500/20' }}"
                                id="live-status">
                                @if ($match->status !== 'played')
                                    <span class="sim-live-dot"></span>
                                @endif
                                {{ $match->status === 'played' ? 'Beendet' : 'Live ' . $match->live_minute . "'" }}
                            </div>
                            @if ($match->status === 'live' && $canSimulate && $match->live_paused)
                                <div class="mt-3 flex flex-col items-center gap-2">
                                    <button id="live-resume-btn"
                                        class="text-xs bg-green-600 hover:bg-green-500 text-white px-4 py-1.5 rounded-lg font-bold animate-pulse shadow-lg shadow-green-500/25">
                                        ▶ Fortsetzen
                                    </button>
                                    <button id="live-simulate-remainder-btn"
                                        class="text-[10px] text-slate-400 hover:text-white underline decoration-slate-600 hover:decoration-white transition-colors">
                                        Restliches Spiel simulieren
                                    </button>
                                </div>
                            @endif
                            <div id="live-error" class="text-xs text-red-400 mt-1 h-4"></div>
                        </div>

                        <!-- Away -->
                        <div class="flex items-center gap-5 w-1/3 justify-end">
                            <div class="text-right">
                                <span
                                    class="block text-[10px] text-indigo-400/60 uppercase tracking-[0.2em] font-bold">Auswärts</span>
                                <span
                                    class="block text-2xl font-bold text-white leading-tight tracking-tight">{{ $match->awayClub->name }}</span>
                            </div>
                            <img src="{{ $match->awayClub->logo_url }}"
                                class="w-20 h-20 object-contain filter drop-shadow-[0_4px_12px_rgba(0,0,0,0.4)]">
                        </div>
                    </div>
                </div>

                <!-- Strength Bar -->
                <div class="bg-slate-900/80 px-6 py-3 border-t border-slate-800/50">
                    <div
                        class="flex justify-between text-[10px] text-slate-500 mb-1.5 uppercase tracking-[0.15em] font-bold">
                        <span class="text-cyan-400/70">Heim 904</span>
                        <span class="text-slate-600">Teamstärken</span>
                        <span class="text-indigo-400/70">809 Auswärts</span>
                    </div>
                    <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden flex">
                        <div class="bg-gradient-to-r from-cyan-500 to-cyan-400 h-full transition-all duration-1000"
                            style="width: 52.8%"></div>
                        <div
                            class="bg-gradient-to-r from-indigo-500 to-indigo-400 h-full flex-1 transition-all duration-1000">
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="px-6 py-4 bg-slate-800/50 border-t border-slate-800/50" style="overflow: visible;">
                    <div class="flex justify-between text-[10px] text-slate-500 mb-2">
                        <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                            <span class="uppercase tracking-[0.15em] font-bold text-cyan-500/50">Heim</span></span>
                        <span class="uppercase tracking-[0.15em] font-bold text-slate-600">Spielverlauf</span>
                        <span class="flex items-center gap-1.5"><span
                                class="uppercase tracking-[0.15em] font-bold text-indigo-400/50">Auswärts</span> <span
                                class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span></span>
                    </div>
                    <div class="sim-timeline-bar">
                        <div class="w-full h-px bg-slate-700/60"></div>
                        <!-- Timeline Events (injected by JS) -->
                        <div id="timeline-events-container" class="absolute inset-0"></div>
                    </div>
                    <div class="flex justify-between text-[9px] text-slate-600 mt-1.5 px-4 font-mono">
                        <span>0'</span><span>15'</span><span>30'</span><span>45'</span><span>60'</span><span>75'</span><span>90'</span>
                    </div>
                </div>

                <!-- Tab Navigation & Actions -->
                <div class="bg-slate-900/60 px-6 py-3 flex items-center justify-between border-t border-slate-800/50">
                    <div class="flex gap-1 bg-slate-900/40 p-1 rounded-xl" id="match-tabs">
                        <button onclick="switchTab('ticker')" class="sim-tab sim-tab-active tab-btn" data-tab="ticker">📡
                            Fan-Ticker</button>
                        <button onclick="switchTab('pitch')" class="sim-tab sim-tab-inactive tab-btn" data-tab="pitch">⚽
                            Spielfeld</button>
                        <button onclick="switchTab('stats')" class="sim-tab sim-tab-inactive tab-btn" data-tab="stats">📊
                            Statistiken</button>
                        <button onclick="switchTab('heatmap')" class="sim-tab sim-tab-inactive tab-btn"
                            data-tab="heatmap">🔥 Heatmap</button>
                        <button onclick="switchTab('lineups')" class="sim-tab sim-tab-inactive tab-btn"
                            data-tab="lineups">👥 Aufstellungen</button>
                        <button onclick="switchTab('ratings')" class="sim-tab sim-tab-inactive tab-btn" data-tab="ratings">⭐
                            Spielerwerte</button>
                    </div>

                    <div class="flex gap-2">
                        <button id="btn-toggle-sound"
                            class="px-3 py-1.5 rounded text-xs bg-slate-800 text-slate-400 border border-slate-950 hover:bg-slate-700">🔇
                            Sound aus</button>
                        @if ($match->status === 'live')
                            @foreach ($manageableClubIds as $clubId)
                                <div class="relative group">
                                    <button
                                        class="px-3 py-1.5 rounded text-xs bg-indigo-600 text-white hover:bg-indigo-500 font-semibold shadow-lg shadow-indigo-500/20">
                                        Manager: {{ $clubId === $match->home_club_id ? 'Heim' : 'Gast' }}
                                    </button>
                                    <!-- Popover Menu -->
                                    <div
                                        class="absolute bottom-full right-0 mb-2 w-48 bg-slate-800 border border-slate-950 rounded-lg shadow-xl p-2 hidden group-hover:block z-50">
                                        <div class="text-[10px] text-slate-500 uppercase font-bold mb-2">Interaktionen</div>
                                        <!-- Shouts -->
                                        <div class="grid grid-cols-2 gap-1 mb-2">
                                            <button class="p-1 bg-slate-700 hover:bg-slate-600 text-[10px] text-slate-200 rounded"
                                                data-live-action="shout" data-club-id="{{ $clubId }}" data-shout="demand_more">📢
                                                Mehr fordern</button>
                                            <button class="p-1 bg-slate-700 hover:bg-slate-600 text-[10px] text-slate-200 rounded"
                                                data-live-action="shout" data-club-id="{{ $clubId }}" data-shout="concentrate">🧠
                                                Konz.</button>
                                            <button class="p-1 bg-slate-700 hover:bg-slate-600 text-[10px] text-slate-200 rounded"
                                                data-live-action="shout" data-club-id="{{ $clubId }}" data-shout="encourage">👏
                                                Ermutigen</button>
                                            <button class="p-1 bg-slate-700 hover:bg-slate-600 text-[10px] text-slate-200 rounded"
                                                data-live-action="shout" data-club-id="{{ $clubId }}" data-shout="calm_down">🧘
                                                Beruhigen</button>
                                        </div>
                                        <div class="border-t border-slate-950 pt-2">
                                            <button
                                                onclick="alert('Substitution UI Placeholder - Use existing sub panel if needed')"
                                                class="w-full text-left px-2 py-1 text-xs text-slate-300 hover:bg-slate-700 rounded">🔄
                                                Spielerwechsel</button>
                                            <button onclick="alert('Tactics UI Placeholder')"
                                                class="w-full text-left px-2 py-1 text-xs text-slate-300 hover:bg-slate-700 rounded">📋
                                                Taktik</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tab Content Area -->
            <div class="bg-slate-800/60 rounded-b-lg overflow-hidden min-h-[500px] p-6 backdrop-blur-sm">

                <!-- 1. FAN TICKER TAB -->
                <div id="tab-content-ticker" class="tab-content">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        <h3 class="font-bold text-slate-100">Fan-Ticker</h3>
                    </div>
                    <div class="space-y-0 relative border-l-2 border-slate-950 ml-3 pl-6 py-2" id="live-events-list">
                        <!-- Empty State -->
                        <div class="text-sm text-slate-500 italic">Noch keine Ereignisse...</div>
                    </div>
                </div>

                <!-- 2. SPIELFELD (PITCH) TAB -->
                <div id="tab-content-pitch" class="tab-content hidden h-full">
                    <div
                        class="relative w-full max-w-4xl mx-auto aspect-[105/68] bg-emerald-800 rounded border border-emerald-900 overflow-hidden shadow-inner bg-cover bg-center">

                        <!-- CSS Pitch Lines fallback/overlay -->
                        <div class="absolute inset-0 pointer-events-none opacity-40">
                            <!-- Outer border -->
                            <div class="absolute inset-4 border-2 border-white/80"></div>
                            <!-- Half way line -->
                            <div class="absolute inset-y-4 left-1/2 w-0.5 bg-white/80 -translate-x-1/2"></div>
                            <!-- Center circle -->
                            <div
                                class="absolute top-1/2 left-1/2 w-32 h-32 border-2 border-white/80 rounded-full -translate-x-1/2 -translate-y-1/2">
                            </div>
                            <!-- Center spot -->
                            <div
                                class="absolute top-1/2 left-1/2 w-2 h-2 bg-white/80 rounded-full -translate-x-1/2 -translate-y-1/2">
                            </div>
                            <!-- Boxes -->
                            <div class="absolute inset-y-[20%] left-4 w-[16%] border-2 border-white/80 border-l-0"></div>
                            <div class="absolute inset-y-[20%] right-4 w-[16%] border-2 border-white/80 border-r-0"></div>
                            <div class="absolute inset-y-[35%] left-4 w-[6%] border-2 border-white/80 border-l-0"></div>
                            <div class="absolute inset-y-[35%] right-4 w-[6%] border-2 border-white/80 border-r-0"></div>
                        </div>

                        <div id="pitch-players-overlay" class="absolute inset-0">
                            <!-- Players injected by JS -->
                        </div>
                        <div id="action-map-overlay" class="absolute inset-0">
                            <!-- Action dots -->
                        </div>
                        <div id="pitch-ball"
                            class="absolute w-4 h-4 bg-white rounded-full border-2 border-slate-900 shadow-lg transform -translate-x-1/2 -translate-y-1/2 z-30 transition-all duration-1000 hidden">
                            <div class="absolute inset-0 bg-white rounded-full animate-ping opacity-25"></div>
                        </div>
                        <div class="absolute bottom-4 left-4 bg-black/50 p-2 rounded text-[10px] text-white">
                            Live Match View
                        </div>
                    </div>
                </div>

                <!-- 3. STATISTIKEN TAB -->
                <div id="tab-content-stats" class="tab-content hidden">
                    <!-- Momentum Graph -->
                    <div class="bg-slate-900 rounded p-4 mb-6 border border-slate-950">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-bold text-slate-200 text-sm">Momentum-Trend</h4>
                            <span class="text-xs text-slate-500">Komplettes Spiel</span>
                        </div>
                        <div class="h-32 w-full relative" id="momentum-chart-container">
                            <div class="absolute inset-0 flex items-center justify-center text-xs text-slate-600">Lade
                                Grafik...</div>
                            <div id="momentum-chart" class="flex h-full w-full items-end gap-[1px] overflow-hidden"></div>
                        </div>
                    </div>

                    <!-- Stat Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="stats-grid">
                        <!-- Filled by JS -->
                    </div>
                </div>

                <!-- 4. HEATMAP TAB -->
                <div id="tab-content-heatmap" class="tab-content hidden">
                    <div class="flex items-center justify-center h-64 text-slate-500">
                        Heatmap wird geladen... (Mockup)
                    </div>
                </div>

                <!-- 5. AUFSTELLUNGEN TAB -->
                <div id="tab-content-lineups" class="tab-content hidden">
                    <div class="bg-slate-900/60 rounded-xl border border-slate-950 overflow-hidden shadow-2xl">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-900/80">
                            <div>
                                <h3 class="text-lg font-black text-white uppercase tracking-tight">Live-Aufstellungen</h3>
                                <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">
                                    Heim links • Gast rechts • <span id="lineup-formations-label"
                                        class="text-cyan-400">Loading...</span>
                                </div>
                            </div>
                            <div class="flex gap-1 bg-slate-950 p-1 rounded-lg border border-slate-800">
                                <button
                                    class="px-3 py-1 text-[10px] font-black uppercase tracking-tighter bg-cyan-600 text-white rounded shadow-sm">Spielfeld</button>
                                <button
                                    class="px-3 py-1 text-[10px] font-black uppercase tracking-tighter text-slate-500 hover:text-slate-300">Liste</button>
                            </div>
                        </div>

                        <!-- Visual Pitch for Lineups -->
                        <div class="relative w-full aspect-[105/68] bg-emerald-950/20 overflow-hidden shadow-inner">
                            <!-- CSS Pitch Lines for Lineups -->
                            <div class="absolute inset-0 pointer-events-none opacity-20">
                                <div class="absolute inset-4 border border-white/40"></div>
                                <div class="absolute inset-y-4 left-1/2 w-px bg-white/40 -translate-x-1/2"></div>
                                <div
                                    class="absolute top-1/2 left-1/2 w-48 h-48 border border-white/40 rounded-full -translate-x-1/2 -translate-y-1/2">
                                </div>
                                <div class="absolute inset-y-[20%] left-4 w-[16%] border border-white/40 border-l-0"></div>
                                <div class="absolute inset-y-[20%] right-4 w-[16%] border border-white/40 border-r-0"></div>
                            </div>

                            <!-- Lineups Container -->
                            <div id="visual-lineups-overlay" class="absolute inset-0">
                                <!-- JS will inject premium player cards here -->
                            </div>
                        </div>
                    </div>

                    <!-- Bank & Ereignisse Section -->
                    <div class="mt-8">
                        <h3 class="text-sm font-black text-white uppercase tracking-[0.2em] mb-4 pl-1">Bank & Ereignisse
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="bank-ereignisse-container">
                            <!-- Team Columns (Home/Away) injected by JS -->
                        </div>
                    </div>
                </div>

                <!-- 6. SPIELERWERTE TAB -->
                <div id="tab-content-ratings" class="tab-content hidden">
                    <div id="ratings-table-container">
                        <!-- Table injected by JS -->
                    </div>
                </div>

            </div>

        @endif
    </div>

    <!-- Replacement JS Logic -->
    <script>
        // Simple Tab Switcher
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            const targetTab = document.getElementById('tab-content-' + tabId);
            if (targetTab) {
                targetTab.classList.remove('hidden');
                // Re-trigger fade animation
                targetTab.style.animation = 'none';
                targetTab.offsetHeight;
                targetTab.style.animation = '';
            }

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('sim-tab-active');
                btn.classList.add('sim-tab-inactive');
            });
            const activeBtn = document.querySelector(`[data-tab="${tabId}"]`);
            if (activeBtn) {
                activeBtn.classList.add('sim-tab-active');
                activeBtn.classList.remove('sim-tab-inactive');
            }
        }
    </script>

    @if ($match->status !== 'scheduled')
        <!-- Interaction Modals -->
        <div id="modal-substitution" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4">
            <div class="bg-slate-800 rounded-lg shadow-2xl border border-slate-950 w-full max-w-md overflow-hidden">
                <div class="bg-slate-900 px-4 py-3 border-b border-slate-950 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-100">🔄 Spielerwechsel</h3>
                    <button onclick="closeModal('modal-substitution')"
                        class="text-slate-400 hover:text-white">&times;</button>
                </div>
                <div class="p-4 space-y-4">
                    <input type="hidden" id="sub-club-id">
                    <div>
                        <label class="block text-xs text-slate-400 uppercase font-semibold mb-1">Spieler Aus (Feld)</label>
                        <select id="sub-player-out"
                            class="w-full bg-slate-900 border border-slate-950 rounded text-sm text-slate-200 p-2"></select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 uppercase font-semibold mb-1">Spieler Ein (Bank)</label>
                        <select id="sub-player-in"
                            class="w-full bg-slate-900 border border-slate-950 rounded text-sm text-slate-200 p-2"></select>
                    </div>
                    <button id="btn-confirm-sub"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 rounded shadow-lg">
                        Wechsel durchführen
                    </button>
                </div>
            </div>
        </div>

        <div id="modal-tactics" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4">
            <div class="bg-slate-800 rounded-lg shadow-2xl border border-slate-950 w-full max-w-md overflow-hidden">
                <div class="bg-slate-900 px-4 py-3 border-b border-slate-950 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-100">📋 Taktik & Ausrichtung</h3>
                    <button onclick="closeModal('modal-tactics')" class="text-slate-400 hover:text-white">&times;</button>
                </div>
                <div class="p-4 space-y-4">
                    <input type="hidden" id="tac-club-id">
                    <div>
                        <label class="block text-xs text-slate-400 uppercase font-semibold mb-1">Taktische
                            Ausrichtung</label>
                        <div class="grid grid-cols-2 gap-2" id="tac-style-grid">
                            <!-- Filled by JS -->
                        </div>
                    </div>
                    <div id="tac-feedback" class="text-xs text-center text-green-400 h-4"></div>
                </div>
            </div>
        </div>

        <script>
            function closeModal(id) {
                document.getElementById(id).classList.add('hidden');
            }

            (() => {
                const root = document.getElementById('match-live-root');
                if (!root) return;

                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const homeClubId = Number(@json($match->home_club_id));
                const awayClubId = Number(@json($match->away_club_id));
                const homeClubLogo = "{{ $match->homeClub->logo_url }}";
                const awayClubLogo = "{{ $match->awayClub->logo_url }}";

                const routes = {
                    state: "{{ route('matches.live.state', $match) }}",
                    resume: "{{ route('matches.live.resume', $match) }}",
                    shout: "{{ route('matches.live.shout', $match) }}",
                    substitute: "{{ route('matches.live.substitute', $match) }}",
                    style: "{{ route('matches.live.style', $match) }}",
                    simulate: "{{ route('matches.simulate', $match) }}"
                };

                const SoundEngine = {
                    sounds: {
                        whistle: new Audio('https://inv.tux.pizza/vi/whistle-referee/audio.mp3'),
                        goal: new Audio('https://actions.google.com/sounds/v1/crowds/battle_crowd_celebrate_stutter.ogg'),
                    },
                    enabled: false,
                    btn: document.getElementById('btn-toggle-sound'),
                    init() {
                        if (this.btn) {
                            this.updateBtn();
                            this.btn.addEventListener('click', () => {
                                this.enabled = !this.enabled;
                                this.updateBtn();
                                if (this.enabled) this.sounds.whistle.play().catch(() => { });
                            });
                        }
                    },
                    updateBtn() {
                        if (this.btn) {
                            this.btn.innerHTML = this.enabled ? '🔊 <span class="text-[10px] ml-1">AN</span>' : '🔇 <span class="text-[10px] ml-1">AUS</span>';
                            this.btn.classList.toggle('text-indigo-400', this.enabled);
                            this.btn.classList.toggle('text-slate-500', !this.enabled);
                        }
                    },
                    play(type) {
                        if (this.enabled && this.sounds[type]) {
                            this.sounds[type].play().catch(() => { });
                        }
                    }
                };

                let latestState = null;
                let lastStateHash = "";
                let processedEventIds = new Set();

                const scoreEl = document.getElementById('live-score');
                const statusEl = document.getElementById('live-status');
                const timelineContainer = document.getElementById('timeline-events-container');
                const eventsList = document.getElementById('live-events-list');
                const statsGrid = document.getElementById('stats-grid');
                const visualLineupsOverlay = document.getElementById('visual-lineups-overlay');
                const actionMapOverlay = document.getElementById('action-map-overlay');
                const momentumChart = document.getElementById('momentum-chart');

                const fetchState = async () => {
                    try {
                        const res = await fetch(routes.state);
                        const state = await res.json();
                        renderState(state);
                    } catch (e) {
                        console.error("Fetch State Error:", e);
                    }
                };

                const sendPost = async (url, payload = {}) => {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    fetchState();
                    return res;
                };

                const renderState = (state) => {
                    latestState = state;
                    if (scoreEl) scoreEl.textContent = `${state.home_score ?? '-'} : ${state.away_score ?? '-'}`;
                    if (statusEl) statusEl.textContent = state.status === 'played' ? 'Beendet' : `Live ${state.live_minute}'`;

                    let actionsSource = state.actions || [];
                    if (actionsSource.length === 0 && state.events) {
                        actionsSource = state.events.map(e => ({
                            id: e.id,
                            minute: e.minute,
                            action_type: e.event_type,
                            club_id: e.club_id,
                            player_name: e.player_name,
                            narrative: e.narrative
                        }));
                    }

                    const currentHash = JSON.stringify({
                        score: `${state.home_score}-${state.away_score}`,
                        status: state.status,
                        minute: state.live_minute,
                        actionsCount: actionsSource.length,
                        lastAction: actionsSource.length > 0 ? actionsSource[actionsSource.length - 1].id : null
                    });

                    if (currentHash === lastStateHash) return;
                    lastStateHash = currentHash;

                    renderTimeline(actionsSource);
                    updateTicker(actionsSource);
                    renderStats(state.team_states);
                    renderVisualLineups(state);
                    renderActionMap(state.actions);
                    renderHeatmap(state.actions);
                    renderMomentum(state.actions);
                    renderRatings(state.player_states, state.final_stats);

                    const resumeBtn = document.getElementById('live-resume-btn');
                    if (resumeBtn) {
                        resumeBtn.parentElement.classList.toggle('hidden', !(state.live_paused && state.can_simulate));
                    }
                };

                const renderTimeline = (actions) => {
                    if (!timelineContainer) return;
                    timelineContainer.innerHTML = '';
                    const relevantTypes = ['goal', 'red_card', 'yellow_card', 'substitution'];
                    const relevant = actions.filter(a => relevantTypes.includes(a.action_type));

                    const eventConfig = {
                        goal: { icon: '⚽', color: 'bg-slate-900 border-2 border-green-500', label: 'Tor', accent: '#4ade80', headerBg: 'rgba(22,163,74,0.3)' },
                        yellow_card: { icon: '🟨', color: 'bg-slate-900 border-2 border-yellow-500', label: 'Gelbe Karte', accent: '#facc15', headerBg: 'rgba(202,138,4,0.3)' },
                        red_card: { icon: '🟥', color: 'bg-slate-900 border-2 border-red-500', label: 'Rote Karte', accent: '#f87171', headerBg: 'rgba(220,38,38,0.3)' },
                        substitution: { icon: '🔄', color: 'bg-slate-900 border-2 border-indigo-500', label: 'Wechsel', accent: '#818cf8', headerBg: 'rgba(79,70,229,0.3)' },
                        chance: { icon: '🎯', color: 'bg-emerald-600', label: 'Großchance', accent: '#34d399', headerBg: 'rgba(16,185,129,0.3)' },
                        save: { icon: '🧤', color: 'bg-green-600', label: 'Parade', accent: '#4ade80', headerBg: 'rgba(22,163,74,0.3)' },
                        shot: { icon: '💥', color: 'bg-slate-700', label: 'Schuss', accent: '#94a3b8', headerBg: 'rgba(100,116,139,0.3)' },
                        corner: { icon: '🚩', color: 'bg-teal-600', label: 'Eckball', accent: '#2dd4bf', headerBg: 'rgba(45,212,191,0.3)' },
                        free_kick: { icon: '🎯', color: 'bg-amber-600', label: 'Freistoß', accent: '#fbbf24', headerBg: 'rgba(251,191,36,0.3)' },
                        offside: { icon: '🚫', color: 'bg-indigo-500', label: 'Abseits', accent: '#818cf8', headerBg: 'rgba(129,140,248,0.3)' },
                        injury: { icon: '🚑', color: 'bg-rose-500', label: 'Verletzung', accent: '#fb7185', headerBg: 'rgba(251,113,133,0.3)' },
                        clearance: { icon: '🛡️', color: 'bg-slate-600', label: 'Klärung', accent: '#cbd5e1', headerBg: 'rgba(148,163,184,0.3)' },
                    };

                    relevant.forEach(a => {
                        const el = document.createElement('div');
                        const leftPct = (a.minute / 95) * 100;
                        el.className = 'absolute top-1/2 transform -translate-y-1/2 -translate-x-1/2 flex flex-col items-center group cursor-pointer';
                        el.style.left = `${Math.min(100, Math.max(0, leftPct))}%`;

                        const cfg = eventConfig[a.action_type] || eventConfig.goal;
                        const isHome = Number(a.club_id) === homeClubId;
                        const teamName = isHome ? '{{ $match->homeClub->short_name ?? $match->homeClub->name }}' : '{{ $match->awayClub->short_name ?? $match->awayClub->name }}';
                        const teamLogo = isHome ? homeClubLogo : awayClubLogo;

                        let narrativeLine = a.narrative ? `<div class="text-[10px] text-slate-400 mt-1 italic leading-snug">"${a.narrative}"</div>` : '';

                        let tooltipStyle = 'min-width: 220px; left: 50%; transform: translateX(-50%);';
                        if (leftPct < 15) tooltipStyle = 'min-width: 220px; left: 0;';
                        else if (leftPct > 85) tooltipStyle = 'min-width: 220px; right: 0;';

                        el.innerHTML = `
                                                                    <div class="w-6 h-6 rounded-full ${cfg.color} flex items-center justify-center text-[10px] shadow z-10 hover:scale-125 transition text-white">
                                                                        ${cfg.icon}
                                                                    </div>
                                                                    <div class="absolute bottom-full mb-2 opacity-0 group-hover:opacity-100 pointer-events-none z-50 transition-all duration-200 group-hover:translate-y-0 translate-y-1" style="${tooltipStyle}">
                                                                        <div class="bg-slate-900 rounded-lg overflow-hidden border border-slate-700/50 shadow-xl shadow-black/40">
                                                                            <div class="px-3 py-1.5 text-[10px] font-bold flex items-center justify-between gap-3" style="background: ${cfg.headerBg}; border-bottom: 1px solid ${cfg.accent}30;">
                                                                                <span style="color: ${cfg.accent}" class="uppercase tracking-widest">${cfg.icon} ${cfg.label}</span>
                                                                                <span class="text-slate-500 font-mono">${a.minute}'</span>
                                                                            </div>
                                                                            <div class="p-3 flex items-start gap-2.5">
                                                                                <img src="${teamLogo}" class="w-7 h-7 rounded-full bg-slate-800 p-0.5 object-contain shrink-0 border border-slate-700/50">
                                                                                <div class="flex-1 min-w-0">
                                                                                    ${a.player_name ? `<div class="text-xs font-bold text-white truncate">${a.player_name}</div>` : ''}
                                                                                    <div class="text-[10px] text-slate-500 font-medium">${teamName}</div>
                                                                                    ${narrativeLine}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                `;
                        timelineContainer.appendChild(el);
                    });
                };

                const renderEventCard = (a, isHome, mins, logoUrl) => {
                    const type = a.action_type;
                    const narrative = a.narrative || '';
                    const initials = a.player_name ? a.player_name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : '??';
                    const sideColor = isHome ? 'cyan-500' : 'indigo-500';
                    const sideLabel = isHome ? 'HEIM' : 'GAST';

                    // Extract score if available
                    let scoreDisplay = null;
                    const scoreMatch = narrative.match(/(\d+)\s*:\s*(\d+)/);
                    if (scoreMatch) scoreDisplay = `${scoreMatch[1]}:${scoreMatch[2]}`;

                    const isGoal = type === 'goal' || (type === 'shot' && a.outcome === 'goal');

                    // --- 1. PREMIUM HEADER LAYOUT (GOAL, YELLOW_CARD, RED_CARD) ---
                    if (isGoal || ['yellow_card', 'red_card'].includes(type)) {
                        let headerText = '';
                        let headerBg = '';
                        let icon = '';

                        if (isGoal) {
                            headerText = `TOR FÜR ${sideLabel}`;
                            headerBg = 'bg-cyan-950/80 border-cyan-500/30';
                            icon = '⚽';
                        } else if (type === 'yellow_card') {
                            headerText = `GELBE KARTE ${sideLabel}`;
                            headerBg = 'bg-yellow-900/40 border-yellow-500/30';
                            icon = '🟨';
                        } else if (type === 'red_card') {
                            headerText = `ROTE KARTE ${sideLabel}`;
                            headerBg = 'bg-red-900/40 border-red-500/30';
                            icon = '🟥';
                        }

                        return `
                                                    <div class="w-full mb-8 animate-fade-in-up">
                                                        <div class="relative max-w-2xl mx-auto">
                                                            <!-- Time Bubble (Top Left - matching latest image) -->
                                                            <div class="absolute -top-3 left-4 z-20">
                                                                <span class="px-2 py-0.5 bg-slate-900 border border-slate-700 rounded-full text-[10px] font-black text-white shadow-xl">${mins}'</span>
                                                            </div>

                                                            <!-- Premium Card -->
                                                            <div class="bg-slate-900/90 rounded-xl border border-slate-800 overflow-hidden shadow-2xl backdrop-blur-md">
                                                                <!-- Colored Header Strip -->
                                                                <div class="${headerBg} border-b py-2 text-center">
                                                                    <span class="text-xs font-black text-white uppercase tracking-[0.2em] flex items-center justify-center gap-2">
                                                                        <span>${icon}</span> ${headerText}
                                                                    </span>
                                                                </div>

                                                                <!-- Body -->
                                                        <div class="p-6">
                                                            <div class="flex items-start gap-6">
                                                                <!-- Left: Visual/Shield -->
                                                                <div class="relative shrink-0 pt-1">
                                                                    <div class="w-14 h-14 rounded-full bg-slate-800 border border-slate-700/50 flex items-center justify-center text-xl shadow-inner">
                                                                        <span class="${sideColor === 'cyan-500' ? 'text-cyan-400' : 'text-indigo-400'}">🛡️</span>
                                                                    </div>
                                                                    <img src="${logoUrl}" class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full border-2 border-slate-900 shadow-lg bg-slate-800 p-0.5">
                                                                </div>

                                                                <!-- Right: Info Section -->
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="flex justify-between items-start mb-2">
                                                                        <div class="flex flex-col">
                                                                            <span class="text-xl font-black text-white leading-tight uppercase tracking-tight">${a.player_name || 'Unbekannt'}</span>
                                                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">${a.club_short_name || sideLabel}</span>
                                                                        </div>
                                                                        ${isGoal && scoreDisplay ? `
                                                                            <div class="text-3xl font-black text-white tracking-tighter drop-shadow-lg font-mono">${scoreDisplay}</div>
                                                                        ` : ''}
                                                                    </div>

                                                                    ${isGoal ? `
                                                                        <div class="mb-4 flex flex-wrap items-center gap-3">
                                                                            <span class="text-[10px] px-2 py-0.5 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-black uppercase tracking-widest rounded-sm">${a.metadata?.goal_type || 'TOR'}</span>
                                                                            ${a.assister_name ? `
                                                                                <div class="flex items-center gap-1.5 opacity-80">
                                                                                    <span class="text-[10px] text-slate-500 font-bold uppercase">Assistent</span>
                                                                                    <span class="text-[11px] text-slate-300 font-black">${a.assister_name}</span>
                                                                                </div>
                                                                            ` : ''}
                                                                        </div>
                                                                    ` : ''}

                                                                    <div class="text-sm text-slate-400 italic leading-relaxed py-3 px-4 bg-slate-800/50 rounded-lg border-l-2 border-slate-700">
                                                                        "${narrative.replace(/"/g, '')}"
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>`;
                    }

                    // --- 2. SUBSTITUTION LAYOUT ---
                    if (type === 'substitution') {
                        let playerIn = a.player_name;
                        let playerOut = '???';
                        const outMatch = narrative.match(/für\s+([^.]+)/);
                        if (outMatch) playerOut = outMatch[1].trim();

                        return `
                                                     <div class="w-full mb-6 animate-fade-in-up">
                                                        <div class="relative max-w-xl mx-auto">
                                                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-20">
                                                                <span class="px-2 py-0.5 bg-slate-800 border border-slate-700 rounded text-[10px] font-mono text-slate-400">${mins}'</span>
                                                            </div>
                                                            <div class="w-full bg-slate-900 border border-slate-700 rounded-lg overflow-hidden flex flex-col shadow-lg">
                                                                <div class="bg-indigo-900/20 py-1.5 text-center border-b border-slate-700">
                                                                    <span class="text-[10px] uppercase tracking-widest font-black text-slate-400">Spielerwechsel ${sideLabel}</span>
                                                                </div>
                                                                <div class="flex items-stretch divide-x divide-slate-700/50">
                                                                    <div class="flex-1 p-4 flex items-center gap-3">
                                                                        <div class="w-8 h-8 rounded-full bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-xs animate-pulse">⬆️</div>
                                                                        <div class="min-w-0">
                                                                            <div class="text-[10px] text-emerald-500 font-black uppercase leading-none mb-1">Ein</div>
                                                                            <div class="text-sm font-bold text-white truncate">${playerIn}</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-1 p-4 flex items-center justify-end gap-3 text-right">
                                                                        <div class="min-w-0">
                                                                            <div class="text-[10px] text-red-500 font-black uppercase leading-none mb-1">Aus</div>
                                                                            <div class="text-sm font-bold text-slate-400 truncate">${playerOut}</div>
                                                                        </div>
                                                                        <div class="w-8 h-8 rounded-full bg-red-500/10 border border-red-500/30 flex items-center justify-center text-xs">⬇️</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                     </div>`;
                    }

                    // --- 3. MINIMAL LAYOUT (with Team Indicator) ---
                    let icon = '⚽';
                    if (type === 'chance') icon = '🎯';
                    else if (type === 'save') icon = '🧤';
                    else if (type === 'shot') icon = '💥';
                    else if (type === 'corner') icon = '🚩';
                    else if (type === 'foul') icon = '⚠️';
                    else if (type === 'offside') icon = '🚫';
                    else if (type === 'injury') icon = '🚑';
                    else if (type === 'midfield_possession') icon = '👟';
                    else if (type === 'turnover') icon = '❌';
                    else if (type === 'throw_in') icon = '👐';
                    else if (type === 'clearance') icon = '🛡️';
                    else if (type === 'free_kick') icon = '🦶';

                    return `
                                                <div class="group flex items-start gap-4 w-full mb-3 px-3 py-2.5 hover:bg-slate-700/30 rounded-lg transition-all border-l-4 border-${sideColor}/30 hover:border-${sideColor} animate-fade-in-up">
                                                    <div class="font-mono text-xs text-slate-500 pt-0.5 min-w-[32px] text-right">${mins}'</div>
                                                    <div class="text-lg pt-0.5 transition-all group-hover:drop-shadow-[0_0_5px_rgba(255,255,255,0.6)] scale-100 group-hover:scale-110">${icon}</div>
                                                    <div class="flex-1 text-sm text-slate-300 leading-relaxed group-hover:text-slate-100 transition-colors">
                                                        <span class="font-black text-[10px] uppercase tracking-wider text-${sideColor} mr-2">${sideLabel}</span>
                                                        ${narrative.replace(/"/g, '')}
                                                    </div>
                                                </div>`;
                };
                const updateTicker = (actions) => {
                    if (!eventsList) return;
                    const html = actions.slice().map(a => {
                        // META EVENTS (Kickoff, Halftime, etc) - V4 MILESTONE REDESIGN
                        if (['kickoff', 'half_time', 'full_time'].includes(a.action_type)) {
                            let label = 'INFORMATION', icon = 'ℹ️', color = 'text-slate-300', headerBg = 'bg-slate-800/80 border-slate-700/50';
                            if (a.action_type === 'kickoff') { label = 'ANPFIFF'; icon = '📢'; color = 'text-emerald-400'; headerBg = 'bg-emerald-950/40 border-emerald-500/30'; }
                            else if (a.action_type === 'half_time') { label = 'HALBZEIT'; icon = '☕'; color = 'text-amber-400'; headerBg = 'bg-amber-950/40 border-amber-500/30'; }
                            else if (a.action_type === 'full_time') { label = 'SPIELENDE'; icon = '🏁'; color = 'text-red-400'; headerBg = 'bg-red-950/40 border-red-500/30'; }

                            let text = (a.narrative && !a.narrative.startsWith('Ereignis:')) ? a.narrative : (a.action_type === 'kickoff' ? "Das Spiel beginnt." : (a.action_type === 'half_time' ? "Halbzeit." : "Spielende."));

                            return `
                                                                <div class="w-full my-8 flex flex-col items-center justify-center animate-fade-in-up">
                                                                    <div class="relative w-full max-w-md">
                                                                        <!-- Milestone Card -->
                                                                        <div class="bg-slate-900/90 rounded-xl border border-slate-800 overflow-hidden shadow-2xl backdrop-blur-md">
                                                                            <!-- Label Header Strip -->
                                                                            <div class="${headerBg} border-b py-2 text-center">
                                                                                <span class="text-[10px] font-black uppercase tracking-[0.3em] ${color} flex items-center justify-center gap-2">
                                                                                    <span>${icon}</span> ${label}
                                                                                </span>
                                                                            </div>

                                                                            <!-- Milestone Body -->
                                                                            <div class="p-5 text-center px-10">
                                                                                <div class="text-sm text-slate-300 font-bold leading-relaxed">
                                                                                    "${text.replace(/"/g, '')}"
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Connection Lines -->
                                                                        <div class="absolute -left-10 right-full top-1/2 -translate-y-1/2 h-px bg-gradient-to-r from-transparent via-slate-700 to-slate-800/50 hidden md:block"></div>
                                                                        <div class="absolute -right-10 left-full top-1/2 -translate-y-1/2 h-px bg-gradient-to-l from-transparent via-slate-700 to-slate-800/50 hidden md:block"></div>
                                                                    </div>
                                                                </div>`;
                        }

                        if (a.action_type === 'goal' && !processedEventIds.has(a.id)) SoundEngine.play('goal');
                        processedEventIds.add(a.id);

                        const isHome = Number(a.club_id) === homeClubId;
                        const logoUrl = isHome ? homeClubLogo : awayClubLogo;
                        const mins = String(a.minute).padStart(2, '0');

                        return renderEventCard(a, isHome, mins, logoUrl);
                    }).join('');
                    eventsList.innerHTML = html || '<div class="text-sm text-slate-500 italic text-center py-8">Noch keine Ereignisse...</div>';
                }; const renderStats = (teamStates) => {
                    if (!statsGrid) return;
                    const home = teamStates[String(homeClubId)] || {}, away = teamStates[String(awayClubId)] || {};
                    const row = (lbl, h, a, s = '') => {
                        const hN = Number(h), aN = Number(a);
                        return `
                                                                    <div class="bg-slate-800 p-3 rounded border border-slate-950">
                                                                        <div class="text-xs text-slate-500 uppercase tracking-widest mb-2 text-center">${lbl}</div>
                                                                        <div class="flex justify-between items-end font-mono">
                                                                            <span class="text-lg font-bold ${hN > aN ? 'text-green-400' : 'text-slate-300'}">${h}${s}</span>
                                                                            <span class="text-lg font-bold ${aN > hN ? 'text-green-400' : 'text-slate-300'}">${a}${s}</span>
                                                                        </div>
                                                                        <div class="mt-1 h-1 bg-slate-700 rounded-full flex overflow-hidden">
                                                                            <div class="bg-cyan-500 h-full" style="width: ${(hN / ((hN + aN) || 1)) * 100}%"></div>
                                                                            <div class="bg-indigo-500 h-full flex-1"></div>
                                                                        </div>
                                                                    </div>`;
                    };
                    statsGrid.innerHTML = [
                        row('Ballbesitz', Math.round((home.possession_seconds / (home.possession_seconds + away.possession_seconds || 1)) * 100), Math.round((away.possession_seconds / (home.possession_seconds + away.possession_seconds || 1)) * 100), '%'),
                        row('xG', (home.expected_goals || 0).toFixed(2), (away.expected_goals || 0).toFixed(2)),
                        row('Schüsse', home.shots || 0, away.shots || 0),
                        row('Pässe', home.pass_completions || 0, away.pass_completions || 0),
                        row('Fouls', home.fouls_committed || 0, away.fouls_committed || 0),
                        row('Ecken', home.corners_won || 0, away.corners_won || 0),
                    ].join('');
                };

                const renderVisualLineups = (state) => {
                    const lineups = state.lineups;
                    const pitchOverlay = document.getElementById('visual-lineups-overlay');
                    const secondaryPitchOverlay = document.getElementById('pitch-players-overlay');
                    const bankContainer = document.getElementById('bank-ereignisse-container');
                    const formationLabel = document.getElementById('lineup-formations-label');

                    if (formationLabel && lineups[String(homeClubId)] && lineups[String(awayClubId)]) {
                        formationLabel.textContent = `${lineups[String(homeClubId)].formation} vs ${lineups[String(awayClubId)].formation}`;
                    }

                    const renderStarters = (container, isLineupTab) => {
                        if (!container) return;
                        container.innerHTML = '';
                        
                        // Tab-specific slots for strict separation or dynamic view
                        const dynamicSlots = { 
                            // Spielfeld tab: Traditional tactical layout
                            'TW': [6, 50], 'GK': [6, 50],
                            'LV': [22, 10], 'RV': [22, 90], 'IV': [18, 50], 'IV-L': [18, 30], 'IV-R': [18, 70],
                            'DM': [30, 50], 'DM-L': [32, 28], 'DM-R': [32, 72],
                            'LM': [42, 10], 'RM': [42, 90], 'ZM': [40, 50], 'ZM-L': [38, 32], 'ZM-R': [38, 68],
                            'ZOM': [48, 50], 'LAM': [46, 25], 'RAM': [46, 75],
                            'ST': [56, 50], 'ST-L': [52, 35], 'ST-R': [52, 65],
                            'LW': [54, 15], 'RW': [54, 85]
                        };

                        const strictSlots = { 
                            // Aufstellung tab: Strict separation (x stays < 50 for Home, > 50 for Away)
                            'TW': [8, 50], 'GK': [8, 50],
                            'LV': [22, 15], 'RV': [22, 85], 'IV': [20, 50], 'IV-L': [20, 35], 'IV-R': [20, 65],
                            'DM': [30, 50], 'DM-L': [32, 30], 'DM-R': [32, 70],
                            'LM': [42, 15], 'RM': [42, 85], 'ZM': [40, 50], 'ZM-L': [38, 35], 'ZM-R': [38, 65],
                            'ZOM': [46, 50], 'LAM': [46, 25], 'RAM': [46, 75],
                            'ST': [48, 50], 'ST-L': [48, 35], 'ST-R': [48, 65],
                            'LW': [48, 15], 'RW': [48, 85]
                        };

                        const activeSlots = isLineupTab ? strictSlots : dynamicSlots;

                        // Local event count map
                        const playerEvents = {};
                        (state.events || []).forEach(e => {
                            if (!e.player_id) return;
                            if (!playerEvents[e.player_id]) playerEvents[e.player_id] = { goals: 0, yellow: 0, red: 0 };
                            if (e.type === 'goal') playerEvents[e.player_id].goals++;
                            if (e.type === 'yellow_card') playerEvents[e.player_id].yellow++;
                            if (e.type === 'red_card') playerEvents[e.player_id].red++;
                        });

                        [homeClubId, awayClubId].forEach((cid, idx) => {
                            const l = lineups[String(cid)];
                            if (!l) return;
                            l.starters.forEach(p => {
                                let slotKey = p.slot.toUpperCase();
                                let coords = activeSlots[slotKey] || activeSlots[slotKey.split('-')[0]] || [25, 50];
                                let x = coords[0], y = coords[1];
                                
                                // Perspective swap: x = 100 - x for Away Team
                                if (idx === 1) { 
                                    x = 100 - x; 
                                }

                                const pEl = document.createElement('div');
                                pEl.className = `absolute transform -translate-x-1/2 -translate-y-1/2 flex flex-col items-center group z-20`;
                                pEl.style.left = `${x}%`;
                                pEl.style.top = `${y}%`;

                                if (isLineupTab) {
                                    const evs = playerEvents[p.id] || { goals: 0, yellow: 0, red: 0 };
                                    let badges = '';
                                    if (evs.goals > 0) badges += `<div class="flex items-center gap-0.5 bg-black/80 rounded-full pl-0.5 pr-1 py-0.5 border border-white/20 shadow-lg"><img src="/images/icons/ball.svg" class="w-2 h-2 invert opacity-80"><span class="text-[7px] text-white font-black">${evs.goals}</span></div>`;
                                    if (evs.yellow > 0) badges += `<div class="w-2 h-3 bg-yellow-400 rounded-sm border border-black/20 shadow-lg"></div>`;
                                    if (evs.red > 0) badges += `<div class="w-2 h-3 bg-red-600 rounded-sm border border-black/20 shadow-lg"></div>`;

                                    pEl.innerHTML = `
                                            <div class="relative group-hover:z-50">
                                                <div class="w-12 h-12 md:w-16 md:h-16 rounded-full border-2 ${idx === 0 ? 'border-cyan-500 shadow-[0_0_15px_rgba(6,182,212,0.4)]' : 'border-indigo-500 shadow-[0_0_15px_rgba(79,70,229,0.4)]'} bg-slate-800 overflow-hidden transition-all group-hover:scale-125 group-hover:border-white">
                                                    <img src="${p.photo_url}" class="w-full h-full object-cover">
                                                </div>
                                                <!-- Event Badges (Goals/Cards) -->
                                                ${badges ? `<div class="absolute -right-2 top-0 flex flex-col gap-1 z-20">${badges}</div>` : ''}

                                                <!-- Position Bubble -->
                                                <div class="absolute -bottom-1 -right-1 bg-slate-900 border border-slate-700 rounded-sm px-1 text-[7px] font-black text-white uppercase z-10">${p.slot}</div>
                                            </div>
                                            <!-- Name Card -->
                                            <div class="mt-1.5 px-3 py-1 bg-slate-900/95 border border-slate-700/50 rounded shadow-lg backdrop-blur-sm min-w-[70px] text-center transition-all group-hover:bg-slate-800 group-hover:border-slate-500">
                                                <span class="text-[9px] font-black text-white whitespace-nowrap tracking-wide leading-none uppercase drop-shadow-sm">${p.name.split(' ').pop()}</span>
                                            </div>
                                        `;
                                } else {
                                    // ... existing dot style ...
                                    pEl.setAttribute('data-player-id', p.id);
                                    pEl.className += ` w-7 h-7 ${idx === 0 ? 'bg-cyan-600' : 'bg-indigo-600'} border-2 border-slate-700/50 rounded-full flex items-center justify-center shadow-lg transition hover:scale-125 hover:z-50 cursor-pointer`;
                                    pEl.innerHTML = `
                                            <span class="text-white font-black text-[9px] pointer-events-none">${p.name.split(' ').pop().substring(0, 3).toUpperCase()}</span>
                                            <div class="absolute bottom-full mb-2 opacity-0 group-hover:opacity-100 transition pointer-events-none bg-slate-900 border border-slate-700 text-white text-[10px] px-2 py-1 rounded shadow-xl whitespace-nowrap z-50">
                                                <div class="font-bold">${p.name}</div>
                                                <div class="text-[8px] text-slate-400 capitalize">${p.slot} | ${p.position}</div>
                                            </div>
                                        `;
                                }
                                container.appendChild(pEl);
                            });
                        });
                    };

                    renderStarters(pitchOverlay, true);
                    renderStarters(secondaryPitchOverlay, false);

                    if (bankContainer) {
                        bankContainer.innerHTML = '';
                        [homeClubId, awayClubId].forEach((cid, idx) => {
                            const l = lineups[String(cid)];
                            const teamName = idx === 0 ? "{{ $match->homeClub->name }}" : "{{ $match->awayClub->name }}";
                            if (!l) return;

                            const col = document.createElement('div');
                            col.className = 'bg-slate-900/40 border border-slate-950 rounded-xl p-5';

                            const renderPlayerRow = (p) => `
                                    <div class="flex items-center gap-3 p-2 bg-slate-800/40 border border-slate-800 rounded-lg mb-2">
                                        <img src="${p.photo_url}" class="w-10 h-10 rounded-lg object-cover border border-slate-700">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-bold text-white truncate">${p.name}</div>
                                            <div class="text-[10px] text-slate-500 font-bold uppercase">${p.position}</div>
                                        </div>
                                    </div>
                                `;

                            const benchHtml = l.bench.length > 0 ? `<div class="grid grid-cols-2 gap-2">${l.bench.map(renderPlayerRow).join('')}</div>` : '<div class="text-[10px] text-slate-600 italic">Keine Bankspieler</div>';

                            // Find substituted players from 'removed' list who were actually substituted (not red cards)
                            const subsOut = (l.removed || []).filter(p => !p.is_sent_off);
                            const subsHtml = subsOut.length > 0 ? `<div class="space-y-1">${subsOut.map(p => `<div class="text-[11px] text-slate-400">${p.name}</div>`).join('')}</div>` : '<div class="text-[11px] text-slate-600 italic">Niemand ausgewechselt</div>';

                            // Find red carded players
                            const sentOff = (l.removed || []).filter(p => p.is_sent_off);
                            const sentOffHtml = sentOff.length > 0 ? `<div class="space-y-1">${sentOff.map(p => `<div class="text-[11px] text-red-400 font-bold">${p.name}</div>`).join('')}</div>` : '<div class="text-[11px] text-slate-600 italic">Keine Platzverweise</div>';

                            col.innerHTML = `
                                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
                                        <h4 class="font-black text-white text-xs uppercase tracking-wider">${teamName}</h4>
                                        <div class="flex gap-2">
                                            <span class="px-1.5 py-0.5 bg-slate-950 text-[8px] text-slate-500 rounded border border-slate-800">BANK: ${l.bench.length}</span>
                                            <span class="px-1.5 py-0.5 bg-slate-950 text-[8px] text-slate-500 rounded border border-slate-800">AUS: ${subsOut.length}</span>
                                            <span class="px-1.5 py-0.5 bg-slate-950 text-[8px] text-slate-500 rounded border border-slate-800">ROT: ${sentOff.length}</span>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <div class="text-[10px] text-slate-500 font-black uppercase mb-2">Bank</div>
                                            ${benchHtml}
                                        </div>
                                        <div class="pt-2 border-t border-slate-800/50">
                                            <div class="text-[10px] text-slate-500 font-black uppercase mb-1">Ausgewechselt</div>
                                            ${subsHtml}
                                        </div>
                                        <div class="pt-2 border-t border-slate-800/50">
                                            <div class="text-[10px] text-slate-500 font-black uppercase mb-1">Platzverweis</div>
                                            ${sentOffHtml}
                                        </div>
                                    </div>
                                `;
                            bankContainer.appendChild(col);
                        });
                    }
                };

                const renderActionMap = (actions) => {
                    if (!actionMapOverlay) return;
                    actionMapOverlay.innerHTML = '';

                    const ball = document.getElementById('pitch-ball');
                    const latest = actions[0];

                    if (latest && ball) {
                        const bx = parseFloat(latest.x_coord), by = parseFloat(latest.y_coord);
                        if (!isNaN(bx) && !isNaN(by)) {
                            ball.classList.remove('hidden');
                            ball.style.left = `${bx}%`;
                            ball.style.top = `${by}%`;

                            // Highlight involved player if possible
                            document.querySelectorAll('#pitch-players-overlay [data-player-id]').forEach(p => {
                                p.classList.remove('ring-4', 'ring-white', 'scale-125', 'z-40');
                            });
                            if (latest.player_id) {
                                const activePlayer = document.querySelector(`#pitch-players-overlay [data-player-id="${latest.player_id}"]`);
                                if (activePlayer) activePlayer.classList.add('ring-4', 'ring-white', 'scale-125', 'z-40');
                            }
                        }
                    }

                    actions.slice(0, 20).forEach((a, i) => {
                        const x = parseFloat(a.x_coord), y = parseFloat(a.y_coord);
                        if (!isNaN(x) && !isNaN(y)) {
                            const el = document.createElement('div');
                            const opacity = Math.max(0.1, 1 - (i / 20));
                            el.className = `absolute w-2 h-2 rounded-full ${Number(a.club_id) === homeClubId ? 'bg-cyan-400' : 'bg-indigo-500'} shadow-sm transform -translate-x-1/2 -translate-y-1/2 transition-opacity`;
                            el.style.left = `${x}%`;
                            el.style.top = `${y}%`;
                            el.style.opacity = opacity;
                            actionMapOverlay.appendChild(el);
                        }
                    });
                };

                const renderHeatmap = (actions) => {
                    const cont = document.getElementById('tab-content-heatmap');
                    if (!cont) return;
                    const rows = 7, cols = 10, grid = Array.from({ length: rows }, () => Array(cols).fill(0));
                    let max = 1;
                    actions.forEach(a => {
                        const x = parseFloat(a.x_coord), y = parseFloat(a.y_coord);
                        if (!isNaN(x) && !isNaN(y)) {
                            const c = Math.min(cols - 1, Math.max(0, Math.floor((x / 100) * cols)));
                            const r = Math.min(rows - 1, Math.max(0, Math.floor((y / 100) * rows)));
                            grid[r][c]++; if (grid[r][c] > max) max = grid[r][c];
                        }
                    });
                    let html = `<div class="relative w-full max-w-4xl mx-auto aspect-[105/68] bg-slate-800 rounded border border-slate-950 overflow-hidden bg-[url('https://raw.githubusercontent.com/mladenilic/soccer-pitch-bg/master/pitch.svg')] bg-cover grid grid-cols-10 grid-rows-7">`;
                    grid.forEach(row => row.forEach(val => { html += `<div class="bg-red-500 transition-opacity duration-1000" style="opacity: ${val > 0 ? (val / max) * 0.6 + 0.1 : 0}"></div>`; }));
                    cont.innerHTML = html + '</div>';
                };

                const renderMomentum = (actions) => {
                    if (!momentumChart) return;
                    const relevant = actions.filter(a => !isNaN(parseFloat(a.momentum_value))).slice(-30);
                    momentumChart.innerHTML = relevant.map(a => `<div class="w-1.5 mx-[1.5px] rounded-t ${parseFloat(a.momentum_value) > 0 ? 'bg-cyan-500' : 'bg-indigo-500'} opacity-80" style="height: ${Math.min(100, Math.abs(parseFloat(a.momentum_value)) * 2)}%;"></div>`).join('');
                };

                const renderRatings = (playerStates, finalStats) => {
                    const cont = document.getElementById('ratings-table-container');
                    if (!cont) return;
                    const data = (playerStates && playerStates.length > 0) ? playerStates : (finalStats || []);
                    if (data.length === 0) { cont.innerHTML = '<div class="text-slate-500 text-center py-4">Keine Werte</div>'; return; }

                    const getR = (s) => {
                        if (s.rating) return Number(s.rating);
                        let r = 6.0; r += (s.goals || 0) * 1.0; r += (s.assists || 0) * 0.5; r -= (s.yellow_cards || 0) * 0.5;
                        return Math.max(1, Math.min(10, r));
                    };
                    const html = data.map(s => {
                        const r = getR(s);
                        const c = r >= 8 ? 'text-green-400' : (r >= 7 ? 'text-emerald-400' : (r >= 6 ? 'text-slate-300' : 'text-orange-400'));
                        return `<div class="flex items-center justify-between p-2 border-b border-slate-800/50"><div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-slate-700 flex items-center justify-center text-[8px] font-bold text-slate-500 shadow-inner">${s.player_name ? s.player_name.substring(0, 1) : 'P'}</div><span class="text-xs font-medium text-slate-300">${s.player_name || 'Spieler'}</span></div><div class="font-mono font-bold ${c}">${r.toFixed(1)}</div></div>`;
                    }).join('');
                    cont.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 gap-4">${html}</div>`;
                };

                window.openSubstitutions = (clubId) => {
                    document.getElementById('sub-club-id').value = clubId;
                    const selOut = document.getElementById('sub-player-out'), selIn = document.getElementById('sub-player-in');
                    selOut.innerHTML = ''; selIn.innerHTML = '';
                    const l = latestState?.lineups[String(clubId)];
                    if (!l) return;
                    l.starters.forEach(p => selOut.innerHTML += `<option value="${p.id}">${p.name} (${p.slot})</option>`);
                    l.bench.forEach(p => selIn.innerHTML += `<option value="${p.id}">${p.name} (${p.position})</option>`);
                    document.getElementById('modal-substitution').classList.remove('hidden');
                };

                window.openTactics = (clubId) => {
                    document.getElementById('tac-club-id').value = clubId;
                    const grid = document.getElementById('tac-style-grid');
                    grid.innerHTML = ['balanced', 'offensive', 'defensive', 'counter'].map(style => `<button onclick="submitTactic('${style}')" class="p-2 bg-slate-700 hover:bg-indigo-600 rounded text-slate-200 text-sm uppercase font-semibold transition">${style}</button>`).join('');
                    document.getElementById('modal-tactics').classList.remove('hidden');
                };

                window.submitTactic = async (style) => {
                    const id = document.getElementById('tac-club-id').value;
                    await sendPost(routes.style, { club_id: id, style });
                    document.getElementById('tac-feedback').textContent = 'Taktik geändert!';
                    setTimeout(() => closeModal('modal-tactics'), 1000);
                };

                document.getElementById('btn-confirm-sub')?.addEventListener('click', async () => {
                    const cid = document.getElementById('sub-club-id').value, pOut = document.getElementById('sub-player-out').value, pIn = document.getElementById('sub-player-in').value;
                    await sendPost(routes.substitute, { club_id: cid, player_out_id: pOut, player_in_id: pIn });
                    closeModal('modal-substitution');
                });

                const fixButtons = () => {
                    document.querySelectorAll('button').forEach(b => {
                        if (b.textContent.includes('Spielerwechsel') || b.textContent.includes('Taktik')) {
                            b.onclick = null;
                            b.addEventListener('click', () => {
                                const btn = b.closest('.absolute')?.querySelector('[data-club-id]');
                                if (btn) b.textContent.includes('Wechsel') ? openSubstitutions(btn.dataset.clubId) : openTactics(btn.dataset.clubId);
                            });
                        }
                    });
                };

                document.querySelectorAll('[data-live-action="shout"]').forEach(btn => btn.addEventListener('click', () => sendPost(routes.shout, { club_id: btn.dataset.clubId, shout: btn.dataset.shout })));
                document.getElementById('live-resume-btn')?.addEventListener('click', () => sendPost(routes.resume));
                document.getElementById('live-simulate-remainder-btn')?.addEventListener('click', () => sendPost(routes.simulate));

                SoundEngine.init();
                fixButtons();
                fetchState();
                setInterval(fetchState, 10000);
            })();
        </script>
    @endif
</x-app-layout>