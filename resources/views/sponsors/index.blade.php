<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                 <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Management</p>
                <h1 class="text-3xl font-bold text-white tracking-tight">Sponsoren</h1>
            </div>
            @if ($clubs->isNotEmpty())
                <form method="GET" action="{{ route('sponsors.index') }}">
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

        @if (!$activeClub)
            <div class="sim-card p-12 text-center border-dashed border-2 border-slate-700 bg-slate-900/40">
                <div class="flex flex-col items-center justify-center text-slate-500">
                    <svg class="w-16 h-16 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-lg font-bold text-white mb-2">Kein Verein ausgewählt</h3>
                    <p class="text-slate-400">Bitte wähle einen Verein, um die Sponsoren einzusehen.</p>
                </div>
            </div>
        @else

            <!-- Active Sponsor Hero -->
            <div class="sim-card p-0 overflow-hidden relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/80 to-transparent z-10"></div>
                <!-- Abstract Background Pattern -->
                <div class="absolute inset-0 opacity-20 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-cyan-500 via-slate-900 to-slate-900"></div>
                
                <div class="relative z-20 p-8 md:p-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                             <div class="w-8 h-8 rounded-full bg-cyan-500 flex items-center justify-center text-slate-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             </div>
                             <p class="text-xs font-bold uppercase tracking-widest text-cyan-400">Aktiver Hauptsponsor</p>
                        </div>
                        
                        @if ($activeContract)
                            <h2 class="text-4xl font-bold text-white tracking-tight mb-2">{{ $activeContract->sponsor->name }}</h2>
                            <p class="text-slate-300 text-lg max-w-xl">
                                Dieser Vertrag läuft bis zum <span class="text-white font-bold">{{ $activeContract->ends_on?->format('d.m.Y') }}</span>.
                            </p>
                        @else
                            <h2 class="text-3xl font-bold text-slate-400 tracking-tight mb-2">Kein aktiver Vertrag</h2>
                            <p class="text-slate-500 text-lg max-w-xl">
                                Wähle einen der verfügbaren Partner aus den Angeboten unten.
                            </p>
                        @endif
                    </div>

                    @if ($activeContract)
                        <div class="flex flex-col items-end gap-4 bg-slate-900/50 backdrop-blur-md p-4 rounded-xl border border-slate-700/50 shadow-xl">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 text-right mb-1">Einkommen</p>
                                <p class="text-3xl font-bold text-emerald-400 font-mono">{{ number_format((float) $activeContract->weekly_amount, 2, ',', '.') }} €<span class="text-sm text-slate-500 font-sans font-normal ml-1">/ Woche</span></p>
                            </div>
                            
                            <form method="POST" action="{{ route('sponsors.contracts.terminate', $activeContract) }}">
                                @csrf
                                <button type="submit" class="text-xs font-bold uppercase tracking-wider text-rose-400 hover:text-rose-300 transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Vertrag auflösen
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <!-- Available Offers -->
                <div class="xl:col-span-2 space-y-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                        Verfügbare Angebote
                    </h3>
                    
                    @if ($offers->isEmpty())
                        <div class="sim-card p-12 text-center border-dashed border-2 border-slate-700 bg-slate-900/40">
                             <p class="text-slate-400">Derzeit liegen keine Angebote vor.</p>
                        </div>
                    @else
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($offers as $offer)
                                <article class="sim-card p-5 group hover:border-cyan-500/30 transition-all duration-300 hover:-translate-y-1">
                                    <div class="flex justify-between items-start mb-4">
                                        <h4 class="font-bold text-white text-lg group-hover:text-cyan-400 transition-colors">{{ $offer->name }}</h4>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">
                                            {{ strtoupper($offer->tier) }}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-3 mb-6">
                                        <div class="flex justify-between items-center text-sm border-b border-slate-800 pb-2">
                                            <span class="text-slate-400">Basisbetrag</span>
                                            <span class="font-bold text-emerald-400 font-mono">{{ number_format((float) $offer->base_weekly_amount, 2, ',', '.') }} €</span>
                                        </div>
                                         <div class="flex justify-between items-center text-sm border-b border-slate-800 pb-2">
                                            <span class="text-slate-400">Reputation</span>
                                            <span class="font-bold text-white font-mono flex items-center gap-1">
                                                <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                {{ $offer->reputation_min }}
                                            </span>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('sponsors.sign', $offer) }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="club_id" value="{{ $activeClub->id }}">
                                        <div class="relative flex-1">
                                            <input type="number" class="sim-input w-full text-center font-bold !py-2" name="months" min="1" max="60" value="12" required placeholder="Laufzeit">
                                            <span class="absolute right-3 top-2.5 text-xs text-slate-500 font-bold pointer-events-none">Monate</span>
                                        </div>
                                        <button type="submit" class="sim-btn-primary flex-1 !py-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 border-none shadow-lg shadow-cyan-900/20" @disabled($activeContract !== null)>
                                            Wählen
                                        </button>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- History -->
                <div class="xl:col-span-1 space-y-4">
                     <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Vergangene Verträge
                    </h3>
                    
                    <div class="sim-card p-0 overflow-hidden">
                        <div class="overflow-y-auto max-h-[500px] scrollbar-thin scrollbar-track-slate-900 scrollbar-thumb-slate-700">
                             @if ($history->isEmpty())
                                <div class="p-8 text-center text-slate-500 text-sm">
                                    Noch keine Historie vorhanden.
                                </div>
                            @else
                                <table class="w-full text-left">
                                    <thead class="bg-slate-900/50 sticky top-0 backdrop-blur-sm">
                                        <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-700/50">
                                            <th class="px-4 py-3">Sponsor</th>
                                            <th class="px-4 py-3 text-right">Betrag</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700/30">
                                        @foreach ($history as $contract)
                                            <tr class="hover:bg-white/[0.02]">
                                                <td class="px-4 py-3">
                                                    <div class="text-sm font-bold text-slate-200">{{ $contract->sponsor->name }}</div>
                                                    <div class="text-[10px] text-slate-500">
                                                        {{ $contract->starts_on?->format('d.m.y') }} - {{ $contract->ends_on?->format('d.m.y') }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <div class="text-sm font-mono text-emerald-400">{{ number_format((float) $contract->weekly_amount, 0, ',', '.') }} €</div>
                                                    <div class="text-[10px] uppercase font-bold text-slate-500">{{ $contract->status }}</div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        @endif
    </div>
</x-app-layout>
