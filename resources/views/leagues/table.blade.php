@php
    $activeCompetitionId = $activeCompetitionSeason?->competition_id;
    $activeSeasonId = $activeCompetitionSeason?->season_id;
@endphp

<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                 <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Wettbewerb</p>
                <h1 class="text-3xl font-bold text-white tracking-tight">Tabelle</h1>
                @if ($activeCompetitionSeason)
                    <div class="mt-2 flex items-center gap-3">
                        <div class="bg-slate-800 rounded px-2 py-1 border border-slate-700">
                             <img class="h-6 w-auto" src="{{ $activeCompetitionSeason->competition->logo_url }}" alt="{{ $activeCompetitionSeason->competition->name }}">
                        </div>
                        <span class="text-lg text-slate-300 font-medium">{{ $activeCompetitionSeason->competition->name }} <span class="text-slate-500 mx-1">|</span> {{ $activeCompetitionSeason->season->name }}</span>
                    </div>
                @endif
            </div>

            <form method="GET" action="{{ route('league.table') }}" class="flex flex-wrap items-center gap-3">
                 <!-- Competition Select -->
                <div class="relative group">
                    <select name="competition_id" class="sim-input pl-4 pr-10 py-2 text-sm bg-slate-900/80 backdrop-blur-md border-slate-700 focus:border-cyan-500 rounded-lg appearance-none cursor-pointer min-w-[200px]" onchange="this.form.submit()">
                        @foreach ($competitions as $competition)
                            <option value="{{ $competition->id }}" @selected($competition->id === $activeCompetitionId)>
                                {{ $competition->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400 group-hover:text-cyan-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <!-- Season Select -->
                 <div class="relative group">
                    <select name="season_id" class="sim-input pl-4 pr-10 py-2 text-sm bg-slate-900/80 backdrop-blur-md border-slate-700 focus:border-cyan-500 rounded-lg appearance-none cursor-pointer min-w-[120px]" onchange="this.form.submit()">
                        @foreach ($seasons as $season)
                            <option value="{{ $season->id }}" @selected($season->id === $activeSeasonId)>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                     <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400 group-hover:text-cyan-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Controls & Legend -->
        <div class="sim-card py-3 px-4 flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-slate-900/60 backdrop-blur-md">
            <!-- Tabs -->
            <div class="flex flex-wrap gap-1 p-1 bg-slate-900/50 rounded-lg border border-slate-700/50">
                @foreach (['Gesamt', 'Hinrunde', 'Rueckrunde', 'Heim', 'Auswaerts', 'Form'] as $tab)
                    <button type="button" class="px-3 py-1.5 rounded-md text-xs font-bold uppercase tracking-wide transition-all {{ $loop->first ? 'bg-cyan-500 text-white shadow-lg shadow-cyan-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        {{ $tab }}
                    </button>
                @endforeach
            </div>

            <!-- Legend -->
            <div class="flex flex-wrap items-center gap-3 text-[10px] font-bold uppercase tracking-wider">
                <span class="flex items-center gap-1.5 px-2 py-1 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Super League
                </span>
                <span class="flex items-center gap-1.5 px-2 py-1 rounded bg-sky-500/10 text-sky-400 border border-sky-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span> SL-Quali
                </span>
                <span class="flex items-center gap-1.5 px-2 py-1 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Abstieg
                </span>
            </div>
        </div>

        @if ($activeCompetitionSeason && $table->isNotEmpty())
            <div class="sim-card p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-700/50 text-xs font-bold uppercase tracking-wider text-slate-400 bg-slate-900/40">
                                <th class="px-4 py-4 text-center w-16">#</th>
                                <th class="px-4 py-4">Team</th>
                                <th class="px-4 py-4 text-center">Sp</th>
                                <th class="px-4 py-4 text-center">S</th>
                                <th class="px-4 py-4 text-center">U</th>
                                <th class="px-4 py-4 text-center">N</th>
                                <th class="px-4 py-4 text-center">Tore</th>
                                <th class="px-4 py-4 text-center">Diff</th>
                                <th class="px-4 py-4 text-center">Pkt</th>
                                <th class="px-4 py-4 text-center">Form</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @foreach ($table as $index => $row)
                                @php
                                    $isOwned = in_array($row->club_id, $ownedClubIds ?? [], true);
                                    $form = strtoupper((string) $row->form_last5);
                                    $formChars = str_split(str_pad($form, 5, '-'));
                                    
                                    // Rank styling
                                    $rankClass = 'text-slate-400';
                                    $rankBg = '';
                                    if ($index < 4) { // CL spots approx
                                         $rankClass = 'text-emerald-400';
                                         $rankBg = 'bg-emerald-500/10 border-emerald-500/20';
                                    } elseif ($index > count($table) - 4) { // Relegation approx
                                         $rankClass = 'text-rose-400';
                                         $rankBg = 'bg-rose-500/10 border-rose-500/20';
                                    }
                                @endphp
                                <tr class="group hover:bg-white/[0.02] transition-colors {{ $isOwned ? 'bg-cyan-500/5 hover:bg-cyan-500/10' : '' }}">
                                    <td class="px-4 py-3 text-center">
                                        <div class="w-8 h-8 rounded mx-auto flex items-center justify-center font-bold text-sm border border-transparent {{ $rankBg }} {{ $rankClass }}">
                                            {{ $index + 1 }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded bg-slate-800 p-1 border border-slate-700 flex items-center justify-center shrink-0">
                                                <img src="{{ optional($row->club)->logo_url }}" alt="{{ optional($row->club)->name }}" class="max-w-full max-h-full">
                                            </div>
                                            <span class="font-bold text-white group-hover:text-cyan-400 transition-colors {{ $isOwned ? 'text-cyan-400' : '' }}">{{ optional($row->club)->name }}</span>
                                            @if($isOwned)
                                                <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">Du</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-300">{{ $row->matches_played }}</td>
                                    <td class="px-4 py-3 text-center text-slate-400">{{ $row->wins }}</td>
                                    <td class="px-4 py-3 text-center text-slate-400">{{ $row->draws }}</td>
                                    <td class="px-4 py-3 text-center text-slate-400">{{ $row->losses }}</td>
                                    <td class="px-4 py-3 text-center font-mono text-slate-300">{{ $row->goals_for }}:{{ $row->goals_against }}</td>
                                    <td class="px-4 py-3 text-center font-mono {{ $row->goal_diff > 0 ? 'text-emerald-400' : ($row->goal_diff < 0 ? 'text-rose-400' : 'text-slate-400') }}">
                                        {{ $row->goal_diff > 0 ? '+' : '' }}{{ $row->goal_diff }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-lg font-bold text-white">{{ $row->points }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            @foreach ($formChars as $char)
                                                @php
                                                    $dotClass = match ($char) {
                                                        'W' => 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]',
                                                        'D' => 'bg-slate-500',
                                                        'L' => 'bg-rose-500',
                                                        default => 'bg-slate-700',
                                                    };
                                                @endphp
                                                <div class="w-2 h-2 rounded-full {{ $dotClass }}" title="{{ $char }}"></div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="sim-card p-12 text-center border-dashed border-2 border-slate-700 bg-slate-900/40">
                <div class="flex flex-col items-center justify-center text-slate-500">
                    <svg class="w-12 h-12 mb-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="text-lg font-medium text-white">Keine Tabellendaten gefunden</p>
                    <p class="text-sm text-slate-400">Es wurden noch keine Spiele in diesem Wettbewerb absolviert.</p>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
