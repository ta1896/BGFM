<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                 <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Infrastruktur</p>
                <h1 class="text-3xl font-bold text-white tracking-tight">Stadion & Umfeld</h1>
            </div>
            @if ($clubs->isNotEmpty())
                <form method="GET" action="{{ route('stadium.index') }}">
                    <div class="relative group">
                        <select name="club" class="sim-input pl-4 pr-10 py-2 text-sm bg-slate-900/80 backdrop-blur-md border-slate-700 focus:border-cyan-500 rounded-lg appearance-none cursor-pointer min-w-[200px]" onchange="this.form.submit()">
                            @foreach ($clubs as $club)
                                <option value="{{ $club->id }}" @selected($activeClub && $activeClub->id === $club->id)>
                                    {{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                         <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400 group-hover:text-cyan-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </form>
            @endif
        </div>

        @if (!$activeClub || !$stadium)
            <div class="sim-card p-12 text-center border-dashed border-2 border-slate-700 bg-slate-900/40">
                <div class="flex flex-col items-center justify-center text-slate-500">
                     <svg class="w-16 h-16 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <h3 class="text-lg font-bold text-white mb-2">Kein Stadion gefunden</h3>
                    <p class="text-slate-400">Für diesen Verein sind keine Stadiondaten hinterlegt.</p>
                </div>
            </div>
        @else

            <!-- Stadium Hero & Stats -->
            <div class="grid gap-6 xl:grid-cols-3">
                 <!-- Main Info -->
                <div class="xl:col-span-2 sim-card p-0 overflow-hidden relative group">
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 z-0"></div>
                     <!-- Decorative pattern -->
                    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-cyan-900 via-transparent to-transparent z-0"></div>
                    
                    <div class="relative z-10 p-6 sm:p-8 flex flex-col justify-between h-full">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-8 h-8 rounded bg-slate-800 p-1 border border-slate-700 flex items-center justify-center shrink-0">
                                         <img src="{{ $activeClub->logo_url }}" alt="{{ $activeClub->name }}" class="max-w-full max-h-full">
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Heimstätte</span>
                                </div>
                                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-2">{{ $stadium->name }}</h2>
                            </div>
                            <!-- Live Status Dot if match day? (Optional future feature) -->
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8">
                             <div class="bg-slate-900/50 backdrop-blur-sm rounded-xl p-4 border border-slate-700/50">
                                 <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Kapazität</p>
                                 <p class="text-xl sm:text-2xl font-bold text-white flex items-baseline gap-1">
                                     {{ number_format($stadium->capacity, 0, ',', '.') }}
                                     <span class="text-xs font-normal text-slate-500">Plätze</span>
                                 </p>
                             </div>
                             <div class="bg-slate-900/50 backdrop-blur-sm rounded-xl p-4 border border-slate-700/50">
                                 <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Ticketpreis</p>
                                 <p class="text-xl sm:text-2xl font-bold text-emerald-400 flex items-baseline gap-1">
                                     {{ number_format((float) $stadium->ticket_price, 2, ',', '.') }}
                                     <span class="text-xs font-normal text-slate-500">€</span>
                                 </p>
                             </div>
                             <div class="bg-slate-900/50 backdrop-blur-sm rounded-xl p-4 border border-slate-700/50">
                                 <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Rasenqualität</p>
                                 <div class="flex items-center gap-2">
                                     <div class="flex-1 h-2 bg-slate-700 rounded-full overflow-hidden">
                                         <div class="h-full bg-gradient-to-r from-emerald-600 to-emerald-400" style="width: {{ min(100, $stadium->pitch_quality * 10) }}%"></div>
                                     </div>
                                     <span class="text-sm font-bold text-emerald-400">{{ $stadium->pitch_quality }}/10</span>
                                 </div>
                             </div>
                             <div class="bg-slate-900/50 backdrop-blur-sm rounded-xl p-4 border border-slate-700/50">
                                 <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Fan-Erlebnis</p>
                                  <div class="flex items-center gap-2">
                                     <div class="flex-1 h-2 bg-slate-700 rounded-full overflow-hidden">
                                         <div class="h-full bg-gradient-to-r from-amber-600 to-amber-400" style="width: {{ min(100, $stadium->fan_experience * 10) }}%"></div>
                                     </div>
                                     <span class="text-sm font-bold text-amber-400">{{ $stadium->fan_experience }}/10</span>
                                 </div>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Start Project Card -->
                <div class="sim-card p-6 flex flex-col justify-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Ausbau & Projekte
                    </h3>
                    <p class="text-sm text-slate-400 mb-6">Starte ein neues Bauprojekt, um dein Stadion oder das Umfeld zu verbessern.</p>
                    
                    <form method="POST" action="{{ route('stadium.projects.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="club_id" value="{{ $activeClub->id }}">
                        
                        <div>
                            <label class="sim-label">Projekt wählen</label>
                            <select class="sim-select w-full" name="project_type" required>
                                @foreach ($projectTypes as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <button class="sim-btn-primary w-full py-3 shadow-lg shadow-cyan-500/20" type="submit">
                            Projekt beauftragen
                        </button>
                    </form>
                </div>
            </div>

            <!-- Infrastructure & Projects Grid -->
            <div class="grid gap-6 xl:grid-cols-3">
                 <!-- Infrastructure Details -->
                <div class="sim-card p-6 h-full">
                     <h3 class="text-lg font-bold text-white mb-4">Infrastruktur</h3>
                     <div class="space-y-4">
                         <div class="flex items-center justify-between p-3 rounded-lg bg-slate-900/50 border border-slate-700/50">
                             <div>
                                 <p class="text-sm font-semibold text-white">Trainingsanlagen</p>
                                 <p class="text-xs text-slate-400">Level {{ $stadium->facility_level }}</p>
                             </div>
                             <div class="w-12 h-12 rounded-full border-4 border-slate-700 flex items-center justify-center text-sm font-bold text-cyan-400">
                                 {{ $stadium->facility_level }}
                             </div>
                         </div>
                         <div class="flex items-center justify-between p-3 rounded-lg bg-slate-900/50 border border-slate-700/50">
                             <div>
                                 <p class="text-sm font-semibold text-white">Sicherheit</p>
                                 <p class="text-xs text-slate-400">Level {{ $stadium->security_level }}</p>
                             </div>
                             <div class="w-12 h-12 rounded-full border-4 border-slate-700 flex items-center justify-center text-sm font-bold text-cyan-400">
                                 {{ $stadium->security_level }}
                             </div>
                         </div>
                         <div class="flex items-center justify-between p-3 rounded-lg bg-slate-900/50 border border-slate-700/50">
                             <div>
                                 <p class="text-sm font-semibold text-white">Umfeld & Parkplätze</p>
                                 <p class="text-xs text-slate-400">Level {{ $stadium->environment_level }}</p>
                             </div>
                             <div class="w-12 h-12 rounded-full border-4 border-slate-700 flex items-center justify-center text-sm font-bold text-cyan-400">
                                 {{ $stadium->environment_level }}
                             </div>
                         </div>
                         
                         <div class="mt-6 pt-4 border-t border-slate-700/50">
                             <div class="flex justify-between items-center text-sm">
                                 <span class="text-slate-400">Wartungskosten</span>
                                 <span class="font-mono font-bold text-rose-400">{{ number_format((float) $stadium->maintenance_cost, 2, ',', '.') }} €<span class="text-[10px] text-slate-500 font-sans ml-1">/ Monat</span></span>
                             </div>
                         </div>
                     </div>
                </div>

                <!-- Active Projects List -->
                <div class="xl:col-span-2 sim-card p-0 overflow-hidden flex flex-col">
                    <div class="px-6 py-4 border-b border-slate-700/50 bg-slate-900/40 flex items-center justify-between">
                         <h3 class="text-lg font-bold text-white">Laufende Projekte</h3>
                         <div class="text-xs text-slate-400">Bauhistorie & Status</div>
                    </div>
                    
                    <div class="flex-1 overflow-x-auto">
                        @if ($projects->isEmpty())
                             <div class="p-12 text-center text-slate-500">
                                 Keine Bauprojekte vorhanden.
                             </div>
                        @else
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-900/20 border-b border-slate-700/50">
                                        <th class="px-6 py-3">Projekt</th>
                                        <th class="px-6 py-3 text-center">Level</th>
                                        <th class="px-6 py-3 text-right">Kosten</th>
                                        <th class="px-6 py-3 text-right">Zeitraum</th>
                                        <th class="px-6 py-3 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700/50">
                                    @foreach ($projects as $project)
                                        <tr class="hover:bg-white/[0.02] transition-colors">
                                            <td class="px-6 py-4">
                                                <span class="font-bold text-slate-200 block">{{ ucfirst($project->project_type) }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="text-slate-400">{{ $project->level_from ?? 0 }}</span>
                                                <span class="text-slate-600 mx-1">→</span>
                                                <span class="text-cyan-400 font-bold">{{ $project->level_to ?? 1 }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-right font-mono text-slate-300">
                                                {{ number_format((float) $project->cost, 0, ',', '.') }} €
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="text-xs text-slate-300">{{ $project->started_on?->format('d.m.Y') }}</div>
                                                <div class="text-[10px] text-slate-500">bis {{ $project->completes_on?->format('d.m.Y') }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                @if($project->status === 'completed')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                        Fertig
                                                    </span>
                                                @elseif($project->status === 'in_progress')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20 animate-pulse">
                                                        Im Bau
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">
                                                        {{ $project->status }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

        @endif
    </div>
</x-app-layout>
