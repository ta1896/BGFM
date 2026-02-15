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
            <h2 class="font-semibold text-xl text-slate-100 leading-tight">
                {{ __('Match Center') }}
            </h2>
        </div>
    </x-slot>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 space-y-6" id="match-live-root">

        @if($match->status === 'scheduled')
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

                        <div class="bg-slate-900/60 rounded-xl border border-slate-800 p-6 max-w-4xl mx-auto">
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
                                    class="grid grid-cols-[1fr_auto_1fr] gap-4 items-center py-3 border-b border-slate-800">
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
                                    class="grid grid-cols-[1fr_auto_1fr] gap-4 items-center py-3 border-b border-slate-800">
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
                                    class="grid grid-cols-[1fr_auto_1fr] gap-4 items-center py-3 border-b border-slate-800">
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

                    @if($canSimulate)
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
            <div class="bg-slate-800 rounded-lg overflow-hidden mb-6">
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
                <div class="p-6 bg-gradient-to-b from-slate-800 to-slate-900 relative">
                    <div class="flex items-center justify-between max-w-4xl mx-auto">
                        <!-- Home -->
                        <div class="flex items-center gap-4 w-1/3">
                            <img src="{{ $match->homeClub->logo_url }}"
                                class="w-16 h-16 object-contain filter drop-shadow-lg">
                            <div class="text-left">
                                <span class="block text-xs text-slate-400 uppercase tracking-widest">Heim</span>
                                <span
                                    class="block text-xl font-bold text-slate-100 leading-tight">{{ $match->homeClub->name }}</span>
                            </div>
                        </div>

                        <!-- Score -->
                        <div class="text-center w-1/3">
                            <div class="text-5xl font-black text-slate-100 tracking-tighter" id="live-score">
                                {{ $match->home_score }} : {{ $match->away_score }}
                            </div>
                            <div class="mt-2 inline-block px-3 py-0.5 bg-slate-700 rounded text-xs font-semibold text-slate-300 uppercase tracking-wider"
                                id="live-status">
                                {{ $match->status === 'played' ? 'Beendet' : 'Live ' . $match->live_minute . "'" }}
                            </div>
                            @if($match->status === 'live' && $canSimulate && $match->live_paused)
                                <div class="mt-2">
                                    <button id="live-resume-btn"
                                        class="text-xs bg-green-600 hover:bg-green-500 text-white px-2 py-1 rounded animate-pulse">
                                        Fortsetzen
                                    </button>
                                </div>
                            @endif
                            <div id="live-error" class="text-xs text-red-400 mt-1 h-4"></div>
                        </div>

                        <!-- Away -->
                        <div class="flex items-center gap-4 w-1/3 justify-end">
                            <div class="text-right">
                                <span class="block text-xs text-slate-400 uppercase tracking-widest">Auswärts</span>
                                <span
                                    class="block text-xl font-bold text-slate-100 leading-tight">{{ $match->awayClub->name }}</span>
                            </div>
                            <img src="{{ $match->awayClub->logo_url }}"
                                class="w-16 h-16 object-contain filter drop-shadow-lg">
                        </div>
                    </div>
                </div>

                <!-- Strength Bar -->
                <div class="bg-slate-900 px-6 py-4 border-t border-slate-950">
                    <div class="flex justify-between text-xs text-slate-400 mb-2 uppercase tracking-wider font-semibold">
                        <span>Heim 904</span>
                        <span>Teamstärken • Live</span>
                        <span>809 Auswärts</span>
                    </div>
                    <div class="h-2 bg-slate-700 rounded-full overflow-hidden flex relative">
                        <!-- Simplified visual strength bar (50/50 split for now, dynamic later) -->
                        <div class="w-1/2 bg-slate-600 h-full"></div>
                        <div class="w-1/2 bg-slate-700 h-full"></div>
                        <!-- Marker -->
                        <div
                            class="absolute top-0 bottom-0 w-8 bg-green-500 left-1/2 -ml-4 rounded shadow-[0_0_10px_rgba(34,197,94,0.5)]">
                        </div>
                    </div>
                    <div class="text-center text-[10px] text-slate-500 mt-1">Heimteam mit Vorteil</div>
                </div>

                <!-- Timeline -->
                <div class="px-6 py-8 bg-slate-800 border-t border-slate-950">
                    <div class="flex justify-between text-xs text-slate-400 mb-1">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                            Heim</span>
                        <span class="uppercase tracking-widest font-semibold">Spielverlauf</span>
                        <span class="flex items-center gap-1">Auswärts <span
                                class="w-2 h-2 rounded-full bg-indigo-500"></span></span>
                    </div>
                    <div
                        class="relative h-12 mt-4 bg-slate-700/30 rounded-full border border-slate-950 flex items-center px-4">
                        <div class="w-full h-px bg-slate-600"></div>
                        <!-- Timeline Events (injected by JS) -->
                        <div id="timeline-events-container" class="absolute inset-0"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-slate-500 mt-1 px-4 font-mono">
                        <span>0'</span><span>15'</span><span>30'</span><span>45'</span><span>60'</span><span>75'</span><span>90'</span>
                    </div>
                </div>

                <!-- Tab Navigation & Actions -->
                <div class="bg-slate-900 px-6 py-3 flex items-center justify-between border-t border-slate-950">
                    <div class="flex gap-1" id="match-tabs">
                        <button onclick="switchTab('ticker')"
                            class="px-4 py-1.5 rounded text-sm font-medium bg-slate-700 text-slate-100  tab-btn"
                            data-tab="ticker">Fan-Ticker</button>
                        <button onclick="switchTab('pitch')"
                            class="px-4 py-1.5 rounded text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition tab-btn"
                            data-tab="pitch">Spielfeld</button>
                        <button onclick="switchTab('stats')"
                            class="px-4 py-1.5 rounded text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition tab-btn"
                            data-tab="stats">Statistiken</button>
                        <button onclick="switchTab('heatmap')"
                            class="px-4 py-1.5 rounded text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition tab-btn"
                            data-tab="heatmap">Heatmap</button>
                        <button onclick="switchTab('lineups')"
                            class="px-4 py-1.5 rounded text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition tab-btn"
                            data-tab="lineups">Aufstellungen</button>
                        <button onclick="switchTab('ratings')"
                            class="px-4 py-1.5 rounded text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition tab-btn"
                            data-tab="ratings">Spielerwerte</button>
                    </div>

                    <div class="flex gap-2">
                        <button id="btn-toggle-sound"
                            class="px-3 py-1.5 rounded text-xs bg-slate-800 text-slate-400 border border-slate-950 hover:bg-slate-700">🔇
                            Sound aus</button>
                        @if($match->status === 'live')
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
            <div class="bg-slate-800 rounded-lg overflow-hidden min-h-[500px] p-6">

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
                        class="relative w-full max-w-4xl mx-auto aspect-[105/68] bg-emerald-800 rounded border border-emerald-900 overflow-hidden shadow-inner bg-[url('https://raw.githubusercontent.com/mladenilic/soccer-pitch-bg/master/pitch.svg')] bg-cover bg-center">
                        <div id="action-map-overlay" class="absolute inset-0">
                            <!-- Action dots -->
                        </div>
                        <div class="absolute bottom-4 left-4 bg-black/50 p-2 rounded text-[10px] text-white">
                            Live Action Map
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
                    <!-- Visual Pitch for Lineups -->
                    <div
                        class="relative w-full max-w-5xl mx-auto aspect-[105/68] bg-emerald-900 rounded border border-emerald-900 overflow-hidden shadow-2xl bg-[url('https://raw.githubusercontent.com/mladenilic/soccer-pitch-bg/master/pitch.svg')] bg-cover bg-center">
                        <!-- Lineups Container -->
                        <div id="visual-lineups-overlay" class="absolute inset-0">
                            <!-- JS will inject players here -->
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
            document.getElementById('tab-content-' + tabId).classList.remove('hidden');

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-slate-700', 'text-slate-100', '');
                btn.classList.add('text-slate-400', 'hover:bg-slate-800');
            });
            const activeBtn = document.querySelector(`[data-tab="${tabId}"]`);
            if (activeBtn) {
                activeBtn.classList.add('bg-slate-700', 'text-slate-100', '');
                activeBtn.classList.remove('text-slate-400', 'hover:bg-slate-800');
            }
        }
    </script>

    @if($match->status !== 'scheduled')
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
                    style: "{{ route('matches.live.style', $match) }}"
                };

                // Audio Engine (Re-integrated)
                const SoundEngine = {
                    sounds: {
                        whistle: new Audio('https://inv.tux.pizza/vi/whistle-referee/audio.mp3'),
                        goal_home: new Audio('https://actions.google.com/sounds/v1/crowds/battle_crowd_celebrate_stutter.ogg'),
                        goal_away: new Audio('https://actions.google.com/sounds/v1/crowds/crowd_gasp.ogg'),
                        chance: new Audio('https://actions.google.com/sounds/v1/crowds/crowd_gasp.ogg'),
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
                        if (!this.btn) return;
                        this.btn.textContent = this.enabled ? '🔊 Sound an' : '🔇 Sound aus';
                        this.btn.classList.toggle('text-green-400', this.enabled);
                    },
                    play(type, isHome) {
                        if (!this.enabled) return;
                        let audio = null;
                        if (type === 'goal') audio = isHome ? this.sounds.goal_home : this.sounds.goal_away;
                        else if (type === 'chance') audio = this.sounds.chance;
                        else if (type === 'whistle') audio = this.sounds.whistle;

                        if (audio) { audio.currentTime = 0; audio.play().catch(() => { }); }
                    }
                };

                // Elements
                const scoreEl = document.getElementById('live-score');
                const statusEl = document.getElementById('live-status');
                const eventsList = document.getElementById('live-events-list');
                const statsGrid = document.getElementById('stats-grid');
                const visualLineupsOverlay = document.getElementById('visual-lineups-overlay');
                const actionMapOverlay = document.getElementById('action-map-overlay');
                const momentumChart = document.getElementById('momentum-chart');
                const timelineContainer = document.getElementById('timeline-events-container');

                let processedEventIds = new Set();
                let latestState = null;

                const fetchState = async () => {
                    const res = await fetch(routes.state, { headers: { 'Accept': 'application/json' } });
                    if (res.ok) renderState(await res.json());
                };

                const sendPost = async (url, payload = {}) => {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    // Refresh immediately
                    fetchState();
                    return res;
                };

                const renderState = (state) => {
                    latestState = state;
                    scoreEl.textContent = `${state.home_score} : ${state.away_score}`;
                    statusEl.textContent = state.status === 'played' ? 'Beendet' : `Live ${state.live_minute}'`;

                    // Fallback for Legacy/Instant-Sim Matches (No Actions, Only Events)
                    let actionsSource = state.actions;
                    if (!actionsSource || actionsSource.length === 0) {
                        actionsSource = (state.events || []).map(e => ({
                            id: e.id,
                            minute: e.minute,
                            second: e.second,
                            action_type: e.event_type, // 'goal', 'yellow_card', 'red_card', 'substitution'
                            club_id: e.club_id,
                            player_id: e.player_id,
                            player_name: e.player_name,
                            club_short_name: e.club_short_name,
                            narrative: null,
                            outcome: e.event_type === 'goal' ? 'scored' : null
                        }));
                    }

                    // Timeline
                    renderTimeline(actionsSource);
                    // Events (Ticker)
                    updateTicker(actionsSource);
                    // Stats
                    renderStats(state.team_states);
                    // Lineups
                    renderVisualLineups(state.lineups);
                    // Action Map (Only works with real actions, otherwise empty)
                    renderActionMap(state.actions);
                    // Heatmap (Only works with real actions)
                    renderHeatmap(state.actions);
                    // Momentum
                    renderMomentum(state.actions);
                    // Ratings
                    renderRatings(state.player_states, state.final_stats);

                    // Resume Button
                    const resumeBtn = document.getElementById('live-resume-btn');
                    if (resumeBtn) {
                        resumeBtn.parentElement.classList.toggle('hidden', !(state.live_paused && state.can_simulate));
                    }
                };

                const renderTimeline = (actions) => {
                    if (!timelineContainer) return;
                    timelineContainer.innerHTML = '';
                    // Use actions for timeline to ensure we have goals/cards
                    const relevant = actions.filter(a => ['goal', 'red_card', 'yellow_card', 'substitution'].includes(a.action_type));

                    relevant.forEach(a => {
                        const el = document.createElement('div');
                        const leftPct = (a.minute / 95) * 100; // Cap at 95 minutes for visual?
                        el.className = 'absolute top-1/2 transform -translate-y-1/2 -translate-x-1/2 flex flex-col items-center group cursor-pointer';
                        el.style.left = `${Math.min(100, Math.max(0, leftPct))}%`;

                        let icon = '⚽';
                        let color = 'bg-slate-900 border-2 border-slate-500';
                        if (a.action_type === 'goal') { icon = '⚽'; color = 'bg-slate-900 border-2 border-green-500'; }
                        if (a.action_type === 'yellow_card') { icon = '🟨'; color = 'bg-yellow-500 border-none'; }
                        if (a.action_type === 'red_card') { icon = '🟥'; color = 'bg-red-600 border-none'; }
                        if (a.action_type === 'substitution') { icon = '🔄'; color = 'bg-indigo-600 border-none'; }

                        el.innerHTML = `
                                                                                                                                                <div class="w-6 h-6 rounded-full ${color} flex items-center justify-center text-[10px] shadow z-10 hover:scale-125 transition text-white">
                                                                                                                                                   ${icon}
                                                                                                                                                </div>
                                                                                                                                                <div class="absolute bottom-full mb-1 bg-black/80 px-2 py-1 rounded text-[10px] text-white whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none z-20">
                                                                                                                                                    ${a.minute}' ${a.player_name || ''}
                                                                                                                                                </div>
                                                                                                                                            `;
                        timelineContainer.appendChild(el);
                    });
                };

                const updateTicker = (actions) => {
                    if (!actions) return;

                    // Filter for relevant ticker items (exclude internal or empty narrative ones if any)
                    // We want to show: goals, chances, cards, fouls, substitutions, shots, injuries
                    // Sorted descending (newest first)
                    // Actions from controller are already sorted desc.

                    // We need to deduplicate actions if we fetch frequently? 
                    // processedEventIds checks `id`.

                    const html = actions.map(a => {
                        // Mark as processed for sound
                        if ((a.action_type === 'goal') && !processedEventIds.has(a.id)) {
                            SoundEngine.play('goal', Number(a.club_id) === homeClubId);
                        }
                        processedEventIds.add(a.id);

                        const isHome = Number(a.club_id) === homeClubId;
                        const logoUrl = isHome ? homeClubLogo : awayClubLogo;
                        // Time
                        const minutes = String(a.minute).padStart(2, '0');

                        // Icon & Color for Special Events
                        let specialType = null;
                        if (a.action_type === 'goal') specialType = 'goal';
                        else if (a.action_type === 'yellow_card') specialType = 'yellow';
                        else if (a.action_type === 'red_card') specialType = 'red';
                        else if (a.action_type === 'substitution') specialType = 'sub';

                        // Narrative Text
                        // Fallback if narrative is empty (it shouldn't be for these types)
                        let text = a.narrative;

                        if (!text) {
                            if (a.action_type === 'goal') {
                                text = `Toooor für ${a.club_short_name}! ${a.player_name || 'Ein Spieler'} trifft ins Netz.`;
                            } else if (a.action_type === 'yellow_card') {
                                text = `Gelbe Karte für ${a.player_name || 'den Spieler'}.`;
                            } else if (a.action_type === 'red_card') {
                                text = `Platzverweis! Rote Karte für ${a.player_name || 'den Spieler'}.`;
                            } else if (a.action_type === 'kickoff') {
                                text = `Anstoß! Der Ball rollt.`;
                            } else if (a.action_type === 'half_time') {
                                text = `Halbzeitpause.`;
                            } else if (a.action_type === 'full_time') {
                                text = `Abpfiff. Das Spiel ist beendet.`;
                            } else if (a.action_type === 'free_kick') {
                                text = `Freistoß für ${a.club_short_name}.`;
                            } else if (a.action_type === 'substitution') {
                                text = `Wechsel bei ${a.club_short_name}.`;
                            } else if (a.action_type === 'chance') {
                                text = `Großchance für ${a.club_short_name}!`;
                            } else if (a.action_type === 'foul') {
                                text = `Foulspiel von ${a.player_name || a.club_short_name}.`;
                            } else if (a.action_type === 'corner') {
                                text = `Eckball für ${a.club_short_name}.`;
                            } else if (a.action_type === 'offside') {
                                text = `Abseitsstellung von ${a.player_name || a.club_short_name}.`;
                            } else if (a.action_type === 'save') {
                                text = `Parade! Der Torhüter rettet.`;
                            } else if (a.action_type === 'shot') {
                                text = `Schuss von ${a.player_name}, aber kein Problem für den Keeper.`;
                            } else if (a.action_type === 'midfield_possession') {
                                text = `Ballbesitz ${a.club_short_name} im Mittelfeld.`;
                            } else if (a.action_type === 'turnover') {
                                text = `Ballverlust durch ${a.player_name || a.club_short_name}.`;
                            } else if (a.action_type === 'throw_in') {
                                text = `Einwurf für ${a.club_short_name}.`;
                            } else if (a.action_type === 'clearance') {
                                text = `Gute Klärungsaktion von ${a.player_name || a.club_short_name}.`;
                            } else {
                                text = `Ereignis: ${String(a.action_type).replace(/_/g, ' ').toUpperCase()}`;
                            }
                        }

                        // --- SPECIAL DESIGN FOR GOALS / CARDS ---
                        if (specialType === 'goal' || specialType === 'red' || specialType === 'yellow') {
                            let headerClass = 'bg-slate-900 border-b border-slate-950';
                            let icon = '';
                            let title = `${minutes}. Minute`;
                            let titleClass = 'text-white';
                            let playerFaceHtml = '';
                            let cardBorder = 'border-slate-950';

                            if (specialType === 'goal') {
                                headerClass = 'bg-gradient-to-r from-sky-950/40 to-slate-900 border-b border-sky-500/10';
                                cardBorder = 'border-sky-900/30';
                                icon = '⚽';
                                title = `TOOOOR in der ${minutes}. Minute`;
                                titleClass = 'text-sky-400';
                                if (a.player_name) {
                                    playerFaceHtml = `
                                                                                                            <div class="w-8 h-8 rounded-full bg-slate-950 border border-sky-500/10 flex items-center justify-center overflow-hidden shrink-0">
                                                                                                                <span class="text-[10px] font-bold text-sky-400 font-mono">${a.player_name.charAt(0)}</span>
                                                                                                            </div>
                                                                                                        `;
                                }
                            } else if (specialType === 'yellow') {
                                headerClass = 'bg-gradient-to-r from-yellow-950/40 to-slate-900 border-b border-yellow-500/10';
                                cardBorder = 'border-yellow-900/30';
                                icon = '🟨';
                                title = `Gelbe Karte (${minutes}. Min)`;
                                titleClass = 'text-yellow-400';
                                if (a.player_name) {
                                    playerFaceHtml = `
                                                                                                            <div class="w-8 h-8 rounded-full bg-slate-950 border border-yellow-500/10 flex items-center justify-center overflow-hidden shrink-0">
                                                                                                                <span class="text-[10px] font-bold text-yellow-400 font-mono">${a.player_name.charAt(0)}</span>
                                                                                                            </div>
                                                                                                        `;
                                }
                            } else if (specialType === 'red') {
                                headerClass = 'bg-gradient-to-r from-red-950/40 to-slate-900 border-b border-red-500/10';
                                cardBorder = 'border-red-900/30';
                                icon = '🟥';
                                title = `PLATZVERWEIS (${minutes}. Min)`;
                                titleClass = 'text-red-400';
                                if (a.player_name) {
                                    playerFaceHtml = `
                                                                                                            <div class="w-8 h-8 rounded-full bg-slate-950 border border-red-500/10 flex items-center justify-center overflow-hidden shrink-0">
                                                                                                                <span class="text-[10px] font-bold text-red-400 font-mono">${a.player_name.charAt(0)}</span>
                                                                                                            </div>
                                                                                                        `;
                                }
                            }

                            return `
                                                                                                <div class="flex ${isHome ? 'flex-row' : 'flex-row-reverse'} items-start gap-2 mb-4 w-full animate-fade-in-up">
                                                                                                    <div class="relative shrink-0">
                                                                                                        <img src="${logoUrl}" class="w-7 h-7 object-contain drop-shadow mt-1 shrink-0 bg-slate-900 rounded-full p-1 border border-slate-950">
                                                                                                        ${specialType === 'goal' ? '<div class="absolute -top-1 -right-1 text-[8px]">⚽</div>' : ''}
                                                                                                    </div>
                                                                                                    <div class="flex flex-col w-full max-w-lg">
                                                                                                        <div class="rounded-lg overflow-hidden w-full bg-slate-900 relative border ${cardBorder}">
                                                                                                            <!-- Header -->
                                                                                                            <div class="${headerClass} px-3 py-1 text-[10px] font-bold flex justify-between items-center">
                                                                                                                <span class="flex items-center gap-1.5 uppercase tracking-widest ${titleClass}">${icon} ${title}</span>
                                                                                                                ${a.player_name ? `<span class="opacity-30 text-[9px] uppercase tracking-tighter bg-black/30 px-1 py-0.5 rounded text-white font-mono">#${a.player_id % 99}</span>` : ''}
                                                                                                            </div>
                                                                                                            <!-- Content -->
                                                                                                            <div class="p-2.5 flex gap-2.5 items-center bg-slate-900/40 relative">
                                                                                                                ${playerFaceHtml}
                                                                                                                <div class="flex-1 min-w-0">
                                                                                                                    ${a.player_name ? `<div class="font-bold text-sm leading-tight text-slate-100 truncate">${a.player_name}</div>` : ''}
                                                                                                                    <div class="text-[9px] font-bold text-slate-600 uppercase tracking-[0.2em]">${a.outcome === 'scored' ? 'TOOOOR!' : String(a.action_type).replace(/_/g, ' ').toUpperCase()}</div>
                                                                                                                </div>

                                                                                                                 <!-- Narrative Text -->
                                                                                                                <div class="text-xs text-slate-500 font-medium leading-relaxed pl-3 border-l border-slate-950 ml-2 italic">
                                                                                                                    "${text}"
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            `;
                        }

                        // --- STANDARD CHAT BUBBLE (Deep Dark Mode) ---
                        return `
                                                                                            <div class="flex ${isHome ? 'flex-row' : 'flex-row-reverse'} items-start gap-2 mb-2 w-full group">
                                                                                                <!-- Team Logo -->
                                                                                                <img src="${logoUrl}" class="w-7 h-7 object-contain drop-shadow mt-0.5 shrink-0 bg-slate-900 rounded-full p-1 border border-slate-950 opacity-30 group-hover:opacity-100 transition-opacity">

                                                                                                <!-- Bubble -->
                                                                                                <div class="flex flex-col max-w-lg ${isHome ? 'items-start' : 'items-end'}">
                                                                                                    <div class="bg-slate-950/40 text-slate-500 px-3 py-1.5 rounded  relative text-xs leading-relaxed border border-slate-950 group-hover:border-slate-950 transition-colors">
                                                                                                        <!-- Minute Badge -->
                                                                                                        <div class="text-[8px] font-bold text-slate-700 mb-0.5 flex items-center gap-1 uppercase tracking-widest">
                                                                                                            ${minutes}. MIN
                                                                                                        </div>

                                                                                                        <div class="font-medium">
                                                                                                            ${text}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        `;
                    }).join('');

                    if (html) {
                        eventsList.innerHTML = html;
                    } else {
                        eventsList.innerHTML = '<div class="text-sm text-slate-500 italic text-center py-8">Noch keine Ereignisse...</div>';
                    }
                };

                const renderStats = (teamStates) => {
                    if (!statsGrid) return;
                    const home = teamStates[String(homeClubId)] || {};
                    const away = teamStates[String(awayClubId)] || {};

                    const statRow = (label, hVal, aVal, suffix = '') => `
                                                                                                <div class="bg-slate-800 p-3 rounded border border-slate-950" >
                                                                                                                                                <div class="text-xs text-slate-500 uppercase tracking-widest mb-2 text-center">${label}</div>
                                                                                                                                                <div class="flex justify-between items-end font-mono">
                                                                                                                                                    <span class="text-lg font-bold ${hVal > aVal ? 'text-green-400' : 'text-slate-300'}">${hVal}${suffix}</span>
                                                                                                                                                    <span class="text-lg font-bold ${aVal > hVal ? 'text-green-400' : 'text-slate-300'}">${aVal}${suffix}</span>
                                                                                                                                                </div>
                                                                                                                                                <div class="mt-1 h-1 bg-slate-700 rounded-full flex overflow-hidden">
                                                                                                                                                     <div class="bg-cyan-500 h-full" style="width: ${(hVal / ((hVal + aVal) || 1)) * 100}%"></div>
                                                                                                                                                     <div class="bg-indigo-500 h-full flex-1"></div>
                                                                                                                                                </div>
                                                                                                                                            </div >
                                                                                        `;

                    statsGrid.innerHTML = [
                        statRow('Ballbesitz', Math.round((Number(home.possession_seconds || 0) / (Number(home.possession_seconds || 0) + Number(away.possession_seconds || 0) || 1)) * 100), Math.round((Number(away.possession_seconds || 0) / (Number(home.possession_seconds || 0) + Number(away.possession_seconds || 0) || 1)) * 100), '%'),
                        statRow('xG (Erwartete Tore)', Number(home.expected_goals || 0).toFixed(2), Number(away.expected_goals || 0).toFixed(2)),
                        statRow('Schüsse', home.shots || 0, away.shots || 0),
                        statRow('Pässe', home.pass_completions || 0, away.pass_completions || 0),
                        statRow('Fouls', home.fouls_committed || 0, away.fouls_committed || 0),
                        statRow('Ecken', home.corners_won || 0, away.corners_won || 0),
                        statRow('Gelbe Karten', home.yellow_cards || 0, away.yellow_cards || 0),
                        statRow('Rote Karten', home.red_cards || 0, away.red_cards || 0),
                    ].join('');
                };

                const renderVisualLineups = (lineups) => {
                    if (!visualLineupsOverlay) return;

                    const slotMap = {
                        'GK': [50, 95], 'TW': [50, 95],
                        'LB': [15, 80], 'LV': [15, 80], 'RB': [85, 80], 'RV': [85, 80],
                        'CB': [35, 85], 'IV': [35, 85], 'LCB': [35, 85], 'RCB': [65, 85],
                        'CDM': [50, 70], 'DM': [50, 70], 'LCM': [35, 60], 'RCM': [65, 60],
                        'LM': [15, 45], 'RM': [85, 45], 'CAM': [50, 45], 'OM': [50, 45],
                        'ST': [50, 15], 'MS': [50, 15], 'LS': [35, 15], 'RS': [65, 15],
                        'LW': [15, 20], 'LF': [15, 20], 'RW': [85, 20], 'RF': [85, 20]
                    };

                    let html = '';
                    [homeClubId, awayClubId].forEach((cid, idx) => {
                        const l = lineups[String(cid)];
                        if (!l) return;
                        const isHome = idx === 0;

                        l.starters.forEach(p => {
                            let [x, y] = slotMap[p.slot] || [50, 50];
                            if (!isHome) {
                                x = 100 - x;
                                y = 100 - y;
                            }
                            const color = isHome ? 'bg-cyan-600' : 'bg-indigo-600';
                            html += `<div class="absolute w-10 h-10 ${color} border-2 border-white rounded-full flex flex-col items-center justify-center shadow-lg transform -translate-x-1/2 -translate-y-1/2 hover:scale-110 transition cursor-pointer group"
                                                                                    style = "left: ${x}%; top: ${y}%;">
                                                                                                                                                    <span class="text-white font-bold text-xs">${String(p.name).substring(0, 1)}</span>
                                                                                                                                                    <div class="absolute bottom-full mb-1 flex flex-col items-center opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                                                                                                                                        <div class="bg-black/80 text-white text-[10px] px-2 py-1 rounded whitespace-nowrap">${p.name}</div>
                                                                                                                                                        <div class="text-[9px] text-yellow-300 font-mono">${p.slot}</div>
                                                                                                                                                    </div>
                                                                                                                                                </div>`;
                        });
                    });
                    visualLineupsOverlay.innerHTML = html;
                };

                const renderActionMap = (actions) => {
                    if (!actionMapOverlay) return;
                    actionMapOverlay.innerHTML = '';
                    const significant = actions.filter(a => a.x_coord).slice(0, 8);

                    significant.forEach(a => {
                        const el = document.createElement('div');
                        const isHome = Number(a.club_id) === homeClubId;
                        const color = isHome ? 'bg-cyan-400' : 'bg-indigo-500';
                        el.className = `absolute w-2 h-2 rounded-full ${color} shadow-sm transform -translate-x-1/2 -translate-y-1/2`;
                        el.style.left = `${Math.min(100, Math.max(0, a.x_coord))}% `;
                        el.style.top = `${Math.min(100, Math.max(0, a.y_coord))}% `;
                        actionMapOverlay.appendChild(el);
                    });
                };

                // Heatmap Logic
                const renderHeatmap = (actions) => {
                    const container = document.getElementById('tab-content-heatmap');
                    if (!container) return;
                    // Only render if tab is visible to save perf? Or structure it

                    // 10x7 Grid
                    const cols = 10;
                    const rows = 7;
                    const grid = Array(rows).fill().map(() => Array(cols).fill(0));

                    // Count
                    let max = 1;
                    actions.forEach(a => {
                        if (a.x_coord !== null && a.y_coord !== null) {
                            const c = Math.min(cols - 1, Math.floor((a.x_coord / 100) * cols));
                            const r = Math.min(rows - 1, Math.floor((a.y_coord / 100) * rows));
                            grid[r][c]++;
                            if (grid[r][c] > max) max = grid[r][c];
                        }
                    });

                    // Generate HTML
                    let html = `<div class="relative w-full max-w-4xl mx-auto aspect-[105/68] bg-slate-800 rounded border border-slate-950 overflow-hidden shadow-inner bg-[url('https://raw.githubusercontent.com/mladenilic/soccer-pitch-bg/master/pitch.svg')] bg-cover bg-center grid grid-cols-10 grid-rows-7">`;

                    for (let r = 0; r < rows; r++) {
                        for (let c = 0; c < cols; c++) {
                            const val = grid[r][c];
                            const alpha = val > 0 ? Math.min(0.8, (val / max) * 0.8 + 0.1) : 0;
                            // Use a hot color gradient? Red.
                            html += `<div class="w-full h-full bg-red-500 transition-all duration-1000" style="opacity: ${alpha}"></div>`;
                        }
                    }
                    html += `</div>`;

                    container.innerHTML = `
                                                                                        <div class="mb-4 text-center text-xs text-slate-400 uppercase tracking-widest">Live Ballaktionen Heatmap</div>
                                                                                            ${html}
                                                                                    `;
                };

                const renderMomentum = (actions) => {
                    if (!momentumChart) return;
                    const relevant = actions.filter(a => a.momentum_value).slice(-25);
                    momentumChart.innerHTML = relevant.map(a => {
                        const val = Number(a.momentum_value);
                        const h = Math.min(100, Math.abs(val) * 2);
                        const color = val > 0 ? 'bg-cyan-500' : 'bg-indigo-500';
                        return `<div class="w-1.5 mx-[1px] rounded-t ${color} opacity-80" style="height: ${h}%;"></div>`;
                    }).join('');
                };

                const renderRatings = (playerStates, finalStats) => {
                    const container = document.getElementById('ratings-table-container');
                    if (!container) return;

                    let data = playerStates;
                    if ((!data || data.length === 0) && finalStats && finalStats.length > 0) {
                        data = finalStats;
                    }

                    if (!data || data.length === 0) {
                        container.innerHTML = '<div class="text-slate-500 text-center py-4">Keine Spielerwerte verfügbar</div>';
                        return;
                    }

                    // Live Rating Calculation
                    // Base 6.0
                    // Goal +1.0, Assist +0.5, ShotOT +0.2, PassComp/Att > 0.8 +0.3 ...
                    const calculateRating = (s) => {
                        if (s.rating) return Number(s.rating);

                        let r = 6.0;
                        r += (s.goals || 0) * 1.0;
                        r += (s.assists || 0) * 0.5;
                        r += (s.shots_on_target || 0) * 0.2;
                        r += (s.tackle_won || 0) * 0.1;
                        // Avoid div by zero
                        if (s.pass_attempts > 5 && (s.pass_completions / s.pass_attempts) > 0.85) r += 0.3;

                        r -= (s.fouls_committed || 0) * 0.2;
                        r -= (s.yellow_cards || 0) * 0.5;
                        if (s.red_cards) r -= 2.0;

                        return Math.max(1, Math.min(10, r));
                    };

                    const withRatings = playerStates.map(s => ({ ...s, rating: calculateRating(s) }));
                    const sorted = withRatings.sort((a, b) => b.rating - a.rating);

                    let html = `<table class="w-full text-sm text-left text-slate-300">
                                                                                                                                            <thead class="text-xs text-slate-400 uppercase bg-slate-700/50">
                                                                                                                                                <tr>
                                                                                                                                                    <th class="px-4 py-2">Spieler</th>
                                                                                                                                                    <th class="px-4 py-2 text-center">Note</th>
                                                                                                                                                    <th class="px-4 py-2 text-center">Tore</th>
                                                                                                                                                    <th class="px-4 py-2 text-center">Assists</th>
                                                                                                                                                </tr>
                                                                                                                                            </thead>
                                                                                                                                            <tbody class="divide-y divide-slate-700">`;

                    sorted.forEach(s => {
                        let ratingColor = 'text-slate-300';
                        if (s.rating >= 8) ratingColor = 'text-green-400 font-bold';
                        else if (s.rating >= 7) ratingColor = 'text-green-300';
                        else if (s.rating < 5) ratingColor = 'text-red-400';

                        html += `<tr class="bg-slate-800 border-b border-slate-950 hover:bg-slate-700/50">
                                                                                                                                                <td class="px-4 py-2 font-medium text-slate-200">
                                                                                                                                                    ${s.player_name} <span class="text-xs text-slate-500">(${Number(s.club_id) === homeClubId ? 'Heim' : 'Gast'})</span>
                                                                                                                                                </td>
                                                                                                                                                <td class="px-4 py-2 text-center ${ratingColor}">${s.rating.toFixed(1)}</td>
                                                                                                                                                <td class="px-4 py-2 text-center text-slate-400">${s.goals || '-'}</td>
                                                                                                                                                <td class="px-4 py-2 text-center text-slate-400">${s.assists || '-'}</td>
                                                                                                                                            </tr>`;
                    });
                    html += `</tbody></table > `;
                    container.innerHTML = html;
                };

                // UI Interactions
                window.openSubstitutions = (clubId) => {
                    const l = latestState?.lineups?.[String(clubId)];
                    if (!l) return alert('Keine Daten verfügbar');

                    document.getElementById('sub-club-id').value = clubId;

                    // Populate selects
                    const selOut = document.getElementById('sub-player-out');
                    const selIn = document.getElementById('sub-player-in');
                    selOut.innerHTML = '';
                    selIn.innerHTML = '';

                    l.starters.forEach(p => {
                        selOut.innerHTML += `< option value = "${p.id}" > ${p.name} (${p.position})</option > `;
                    });
                    l.bench.forEach(p => {
                        selIn.innerHTML += `< option value = "${p.id}" > ${p.name} (${p.position})</option > `;
                    });

                    document.getElementById('modal-substitution').classList.remove('hidden');
                };

                window.openTactics = (clubId) => {
                    document.getElementById('tac-club-id').value = clubId;
                    const grid = document.getElementById('tac-style-grid');
                    grid.innerHTML = '';

                    ['balanced', 'offensive', 'defensive', 'counter'].forEach(style => {
                        grid.innerHTML += `< button onclick = "submitTactic('${style}')" class="p-2 bg-slate-700 hover:bg-indigo-600 rounded text-slate-200 text-sm uppercase font-semibold transition" > ${style}</button > `;
                    });

                    document.getElementById('modal-tactics').classList.remove('hidden');
                };

                window.submitTactic = async (style) => {
                    const clubId = document.getElementById('tac-club-id').value;
                    await sendPost(routes.style, { club_id: clubId, style: style });
                    document.getElementById('tac-feedback').textContent = 'Taktik geändert!';
                    setTimeout(() => closeModal('modal-tactics'), 1000);
                };

                document.getElementById('btn-confirm-sub').addEventListener('click', async () => {
                    const clubId = document.getElementById('sub-club-id').value;
                    const pOut = document.getElementById('sub-player-out').value;
                    const pIn = document.getElementById('sub-player-in').value;

                    await sendPost(routes.substitute, {
                        club_id: clubId,
                        player_out_id: pOut,
                        player_in_id: pIn
                    });
                    closeModal('modal-substitution');
                });

                // Helper to wire buttons (called after body load? No, buttons exist in static HTML mostly, or we bind dynamically)
                // We used onclick="alert" before. Now we need to change those or bind listeners.
                // Since I can't easily change the HTML part in this tool call (too large), I will bind via JS delegate.
                document.addEventListener('click', (e) => {
                    if (e.target.dataset.action === 'open-sub') {
                        openSubstitutions(e.target.dataset.clubId);
                    }
                    if (e.target.dataset.action === 'open-tac') {
                        openTactics(e.target.dataset.clubId);
                    }
                });

                // ALSO: I need to update the buttons in the "Popover Menu" to use these data attributes.
                // But I haven't replaced the HTML part of the buttons above (lines 381, 384).
                // I will try to target them by their text content or onclick attribute if possible, OR
                // BETTER: Just replace the onclick attribute via JS on init.

                const fixButtons = () => {
                    document.querySelectorAll('button').forEach(b => {
                        if (b.textContent.includes('Spielerwechsel')) {
                            b.onclick = null;
                            b.addEventListener('click', (e) => {
                                // Find closest clubId context?
                                // The button is inside a loop, I need to know the clubId.
                                // The parent div or previous buttons have `data - club - id`.
                                // The popover structure: div.absolute > ... > button
                                // The parent "Manager" button has no ID.
                                // But the "Shout" buttons in the SAME popover have `data - club - id`.
                                const wrapper = b.closest('.absolute');
                                if (wrapper) {
                                    const shoutBtn = wrapper.querySelector('[data-club-id]');
                                    if (shoutBtn) openSubstitutions(shoutBtn.dataset.clubId);
                                }
                            });
                        }
                        if (b.textContent.includes('Taktik')) {
                            b.onclick = null;
                            b.addEventListener('click', (e) => {
                                const wrapper = b.closest('.absolute');
                                if (wrapper) {
                                    const shoutBtn = wrapper.querySelector('[data-club-id]');
                                    if (shoutBtn) openTactics(shoutBtn.dataset.clubId);
                                }
                            });
                        }
                    });
                };


                // Shouts bindings
                document.querySelectorAll('[data-live-action="shout"]').forEach(btn => {
                    btn.addEventListener('click', () => sendPost(routes.shout, { club_id: btn.dataset.clubId, shout: btn.dataset.shout }));
                });

                // Resume
                const resumeBtn = document.getElementById('live-resume-btn');
                if (resumeBtn) resumeBtn.addEventListener('click', () => sendPost(routes.resume));

                // Init
                SoundEngine.init();
                fixButtons(); // Fix the placeholder alerts
                fetchState();
                setInterval(fetchState, 5000);
            })();
        </script>
    @endif
</x-app-layout>