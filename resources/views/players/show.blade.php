<x-app-layout>
    <div x-data="{ activeTab: 'overview' }" class="space-y-6">
        <!-- Header -->
        <div class="space-y-3">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : ($player->club_id ? route('clubs.show', $player->club_id) : route('dashboard')) }}"
                class="sim-page-link">← Zurueck</a>

            <div class="sim-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div class="flex items-center gap-6">
                        <img class="h-14 w-14 md:h-16 md:w-16 rounded-full object-cover ring-4 bg-slate-900 ring-slate-700/50 p-2 shadow-lg" src="{{ $player->photo_url }}"
                            alt="{{ $player->full_name }}">
                        <div>
                            <h1 class="text-3xl font-bold text-white">{{ $player->full_name }}</h1>
                            <p class="text-slate-400 font-medium">
                                {{ $player->age }} Jahre • {{ $player->display_position }}
                            </p>
                            <div class="flex items-center gap-3 mt-3">
                                @if($player->club)
                                    <img class="h-6 w-6 rounded-full object-cover ring-1 bg-slate-800 ring-slate-700/50" src="{{ $player->club->logo_url }}"
                                        alt="{{ $player->club->name }}">
                                    <a href="{{ route('clubs.show', $player->club_id) }}"
                                        class="text-cyan-400 hover:underline font-bold">
                                        {{ $player->club->name }}
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">Vereinslos</span>
                                @endif
                            </div>
                            <!-- External Link Icon (TM Style) -->
                            <div
                                class="mt-2 text-xs font-bold text-slate-500 bg-slate-800 px-2 py-0.5 rounded inline-block">
                                tm</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-8 text-right">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Staerke</p>
                            <div class="relative inline-flex items-center justify-center mt-1">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4"
                                        fill="transparent" class="text-slate-800" />
                                    <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4"
                                        fill="transparent" class="text-emerald-500" stroke-dasharray="175.9"
                                        stroke-dashoffset="{{ 175.9 - (175.9 * $player->overall / 99) }}" />
                                </svg>
                                <span class="absolute text-xl font-bold text-white">{{ $player->overall }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">von {{ $player->potential }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Marktwert</p>
                            <p class="text-2xl font-bold text-white mt-1">
                                {{ number_format($player->market_value, 0, ',', '.') }} €
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-b border-slate-800/80">
                <div class="flex flex-wrap gap-6">
                    <button @click="activeTab = 'overview'" :class="{ 'sim-tab-link-active': activeTab === 'overview' }"
                        class="sim-tab-link">Ueberblick</button>
                    <button @click="activeTab = 'career'" :class="{ 'sim-tab-link-active': activeTab === 'career' }"
                        class="sim-tab-link">Karriere</button>
                    <button @click="activeTab = 'matches'" :class="{ 'sim-tab-link-active': activeTab === 'matches' }"
                        class="sim-tab-link">Spiele</button>
                    <button @click="activeTab = 'history'" :class="{ 'sim-tab-link-active': activeTab === 'history' }"
                        class="sim-tab-link">Werdegang</button>
                    @if ($player->club && auth()->id() === $player->club->user_id)
                        <button @click="activeTab = 'customize'"
                            :class="{ 'sim-tab-link-active': activeTab === 'customize' }"
                            class="sim-tab-link">Anpassen</button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" x-transition.opacity class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Condition -->
                <section class="sim-card p-6">
                    <p class="sim-section-title">Zustand</p>
                    <div class="flex items-center justify-center gap-12 mt-6">
                        <div class="text-center">
                            <div class="relative inline-flex items-center justify-center mb-2">
                                <svg class="w-14 h-14 transform -rotate-90">
                                    <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="3"
                                        fill="transparent" class="text-slate-800" />
                                    <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="3"
                                        fill="transparent" class="text-emerald-500" stroke-dasharray="150"
                                        stroke-dashoffset="{{ 150 - (150 * $player->morale / 100) }}" />
                                </svg>
                                <span class="absolute text-sm font-bold text-white">{{ $player->morale }}</span>
                            </div>
                            <p class="text-xs text-slate-400">Zufriedenheit</p>
                        </div>
                        <div class="text-center">
                            <div class="relative inline-flex items-center justify-center mb-2">
                                <svg class="w-14 h-14 transform -rotate-90">
                                    <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="3"
                                        fill="transparent" class="text-slate-800" />
                                    <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="3"
                                        fill="transparent" class="text-emerald-500" stroke-dasharray="150"
                                        stroke-dashoffset="{{ 150 - (150 * $player->stamina / 100) }}" />
                                </svg>
                                <span class="absolute text-sm font-bold text-white">{{ $player->stamina }}</span>
                            </div>
                            <p class="text-xs text-slate-400">Fitness</p>
                        </div>
                        <div class="text-center">
                            <div class="relative inline-flex items-center justify-center mb-2">
                                <svg class="w-14 h-14 transform -rotate-90">
                                    <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="3"
                                        fill="transparent" class="text-slate-800" />
                                    <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="3"
                                        fill="transparent" class="text-emerald-500" stroke-dasharray="150"
                                        stroke-dashoffset="0" />
                                </svg>
                                <span class="absolute text-sm font-bold text-white">100</span>
                            </div>
                            <p class="text-xs text-slate-400">Form</p>
                        </div>
                    </div>
                </section>

                <!-- Performance Values -->
                <section class="sim-card p-6">
                    <p class="sim-section-title">Leistungswerte</p>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="sim-card-soft p-3 text-center">
                            <p class="text-[10px] text-slate-500 uppercase font-bold">Marktwert</p>
                            <p class="text-white font-bold">{{ number_format($player->market_value, 0, ',', '.') }} €
                            </p>
                        </div>
                        <div class="sim-card-soft p-3 text-center">
                            <p class="text-[10px] text-slate-500 uppercase font-bold">Staerke</p>
                            <p class="text-white font-bold">{{ $player->overall }}/{{ $player->potential }}</p>
                        </div>
                        <div class="col-span-2 sim-card-soft p-3 text-center">
                            <p class="text-[10px] text-slate-500 uppercase font-bold">Nebenpositionen</p>
                            <p class="text-white font-bold">
                                {{ implode(', ', array_filter([$player->position_second, $player->position_third])) ?: '-' }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Charts Placeholders -->
            <div class="grid gap-6 md:grid-cols-2">
                <section class="sim-card p-6 min-h-[250px]">
                    <p class="sim-section-title flex items-center gap-2">
                        <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        Marktwert-Verlauf
                    </p>
                    <div class="flex items-center justify-center h-full text-slate-500 text-sm italic">
                        <span class="bg-slate-800 px-3 py-1 rounded border border-slate-700">Marktwert (€) diagram
                            placeholder</span>
                    </div>
                </section>
                <section class="sim-card p-6 min-h-[250px]">
                    <p class="sim-section-title flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        Staerken-Verlauf
                    </p>
                    <div class="flex items-center justify-center h-full text-slate-500 text-sm italic">
                        <span class="bg-slate-800 px-3 py-1 rounded border border-slate-700">Staerke diagram
                            placeholder</span>
                    </div>
                </section>
            </div>
        </div>

        <!-- Career Tab -->
        <div x-show="activeTab === 'career'" style="display: none;" x-transition.opacity class="space-y-6">
            <section class="sim-card p-5">
                <p class="sim-section-title">Karriere-Statistiken</p>
                <!-- Summary Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
                    @php
                        $totalMatches = $careerStats->sum('appearances');
                        $totalGoals = $careerStats->sum('goals');
                        $totalAssists = $careerStats->sum('assists');
                        $totalCards = $careerStats->sum('red_cards') + $careerStats->sum('yellow_cards'); // Approximation
                    @endphp
                    <div class="sim-card-soft p-3 text-center">
                        <p class="text-xl font-bold text-white">{{ $totalMatches }}</p>
                        <p class="text-[10px] text-slate-500 uppercase">Spiele</p>
                    </div>
                    <div class="sim-card-soft p-3 text-center">
                        <p class="text-xl font-bold text-white">{{ $totalGoals }}</p>
                        <p class="text-[10px] text-slate-500 uppercase">Tore</p>
                    </div>
                    <div class="sim-card-soft p-3 text-center">
                        <p class="text-xl font-bold text-white">{{ $totalAssists }}</p>
                        <p class="text-[10px] text-slate-500 uppercase">Vorlagen</p>
                    </div>
                    <div class="sim-card-soft p-3 text-center">
                        <p class="text-xl font-bold text-amber-400">{{ $careerStats->sum('yellow_cards') }} <span
                                class="text-rose-400">/ {{ $careerStats->sum('red_cards') }}</span></p>
                        <p class="text-[10px] text-slate-500 uppercase">Karten</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="sim-table w-full text-left">
                        <thead>
                            <tr>
                                <th>Saison</th>
                                <th>Wettbewerb</th>
                                <th>Verein</th>
                                <th class="text-center">Spiele</th>
                                <th class="text-center">Tore</th>
                                <th class="text-center">Vorl.</th>
                                <th class="text-center">Gelb</th>
                                <th class="text-center">Rot</th>
                                <th class="text-center">Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($careerStats as $stat)
                                <tr>
                                    <td class="font-bold text-white">{{ $stat->season->name ?? '-' }}
                                    </td>
                                    <td>
                                        @php
                                            $contextNames = [
                                                'league' => 'Liga',
                                                'cup_national' => 'Pokal (Nat.)',
                                                'cup_international' => 'International',
                                                'friendly' => 'Testspiel',
                                            ];
                                        @endphp
                                        {{ $contextNames[$stat->competition_context] ?? ucfirst($stat->competition_context) }}
                                    </td>
                                    <td>-</td>
                                    <td class="text-center">{{ $stat->appearances }}</td>
                                    <td class="text-center">{{ $stat->goals }}</td>
                                    <td class="text-center">{{ $stat->assists }}</td>
                                    <td class="text-center">{{ $stat->yellow_cards }}</td>
                                    <td class="text-center">{{ $stat->red_cards }}</td>
                                    <td
                                        class="text-center font-bold {{ $stat->average_rating >= 7.0 ? 'text-emerald-400' : 'text-slate-300' }}">
                                        {{ $stat->average_rating > 0 ? number_format($stat->average_rating, 1) : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-8 text-slate-500 italic">Keine Karrieredaten
                                        vorhanden.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Matches Tab -->
        <div x-show="activeTab === 'matches'" style="display: none;" x-transition.opacity class="space-y-6">
            <section class="sim-card p-5">
                <p class="sim-section-title">Letzte Spiele</p>
                <div class="overflow-x-auto">
                    <table class="sim-table w-full text-left">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Wettbewerb</th>
                                <th>Begegnung</th>
                                <th class="text-center">Erg.</th>
                                <th class="text-center">Min</th>
                                <th class="text-center">Note</th>
                                <th class="text-center">Tore/Vorl.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentMatches as $stat)
                                <tr>
                                    <td>{{ $stat->match?->kickoff_at?->format('d.m.y') ?? '-' }}</td>
                                    <td>
                                        <span class="sim-pill text-[10px] border-slate-600 bg-slate-700/50">
                                            {{ $stat->match?->competitionSeason?->competition?->code ?? 'FR' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2 text-xs">
                                            <img class="w-4 h-4" src="{{ $stat->match?->homeClub?->logo_url }}">
                                            <span
                                                class="{{ $stat->match?->home_club_id === $player->club_id ? 'font-bold text-white' : 'text-slate-400' }}">{{ $stat->match?->homeClub?->short_name ?? '?' }}</span>
                                            <span>vs</span>
                                            <img class="w-4 h-4" src="{{ $stat->match?->awayClub?->logo_url }}">
                                            <span
                                                class="{{ $stat->match?->away_club_id === $player->club_id ? 'font-bold text-white' : 'text-slate-400' }}">{{ $stat->match?->awayClub?->short_name ?? '?' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center font-bold text-white">
                                        {{ $stat->match?->home_score ?? '-' }}:{{ $stat->match?->away_score ?? '-' }}
                                    </td>
                                    <td class="text-center text-xs text-slate-400">{{ $stat->minutes_played }}'</td>
                                    <td
                                        class="text-center font-bold {{ $stat->rating >= 7.0 ? 'text-emerald-400' : 'text-slate-300' }}">
                                        {{ number_format($stat->rating, 1) }}
                                    </td>
                                    <td class="text-center text-xs">
                                        @if($stat->goals > 0) <span
                                        class="text-emerald-400 font-bold">{{ $stat->goals }}G</span> @endif
                                        @if($stat->assists > 0) <span
                                        class="text-cyan-400 font-bold">{{ $stat->assists }}A</span> @endif
                                        @if($stat->goals == 0 && $stat->assists == 0) - @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-slate-500 italic">Keine Spiele in letzter
                                        Zeit.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- History Tab -->
        <div x-show="activeTab === 'history'" style="display: none;" x-transition.opacity class="space-y-6">
            <div class="sim-card p-12 text-center border-dashed border-2 border-slate-700 bg-slate-900/40">
                <p class="text-xl font-bold text-white mb-2">Werdegang</p>
                <p class="text-slate-400">Hier wird bald die Transferhistorie angezeigt.</p>
            </div>
        </div>

        <!-- Customize Tab (Owner Only) -->
        @if ($player->club && auth()->id() === $player->club->user_id)
            <div x-show="activeTab === 'customize'" style="display: none;" x-transition.opacity class="space-y-6">
                <section class="sim-card p-6">
                    <p class="sim-section-title mb-4 border-b border-slate-800 pb-2">Anpassung beantragen</p>

                    <form method="POST" action="{{ route('players.update', $player) }}" enctype="multipart/form-data"
                        class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <div class="space-y-4">
                            <p class="sim-label">Spielerbild</p>
                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Option A: Bild hochladen</label>
                                    <input type="file" name="photo"
                                        class="sim-input file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-700 file:text-white hover:file:bg-slate-600">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Option B: Spielerbild-Link
                                        (Sortitoutsi)</label>
                                    <input type="url" name="photo_url" placeholder="https://..." class="sim-input">
                                </div>
                            </div>

                            <div class="text-xs text-slate-500 bg-slate-800/50 p-3 rounded border border-slate-700/50">
                                <p class="font-bold text-slate-400 mb-1">So findest du den korrekten Bild-Link:</p>
                                <ol class="list-decimal list-inside space-y-1 ml-1">
                                    <li>Öffne Sortitoutsi.</li>
                                    <li>Gib den Spielernamen dort ein.</li>
                                    <li>Rechtsklick auf das Spielerbild → „Bildadresse kopieren“.</li>
                                    <li>Adresse in das Feld „Spielerbild-Link“ eintragen.</li>
                                </ol>
                                <p class="mt-2 text-[10px] italic opacity-75">Hinweis: Wenn du Upload und Link angibst, wird
                                    der Upload bevorzugt.</p>
                            </div>
                        </div>

                        <div>
                            <label class="sim-label">Marktwert (€)</label>
                            <input type="number" name="market_value"
                                value="{{ old('market_value', $player->market_value) }}" class="sim-input font-mono">
                        </div>

                        <div class="grid gap-6 md:grid-cols-3">
                            @php
                                $positions = ['TW', 'IV', 'LV', 'RV', 'ZM', 'DM', 'OM', 'LM', 'RM', 'LF', 'MS', 'HS', 'RF'];
                            @endphp
                            <div>
                                <label class="sim-label">Position 1</label>
                                <select name="position" class="sim-select">
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos }}" @selected($player->position === $pos)>{{ $pos }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="sim-label">Position 2</label>
                                <select name="position_second" class="sim-select">
                                    <option value="">-</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos }}" @selected($player->position_second === $pos)>{{ $pos }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="sim-label">Position 3</label>
                                <select name="position_third" class="sim-select">
                                    <option value="">-</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos }}" @selected($player->position_third === $pos)>{{ $pos }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-800">
                            <button type="submit" class="sim-btn-primary w-full sm:w-auto">
                                Antrag einreichen
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        @endif
    </div>
</x-app-layout>