<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                 <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Management</p>
                <h1 class="text-3xl font-bold text-white tracking-tight">Finanzen</h1>
            </div>
            @if ($clubs->isNotEmpty())
                <form method="GET" action="{{ route('finances.index') }}">
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
                    <p class="text-slate-400">Bitte wähle einen Verein, um die Finanzen einzusehen.</p>
                </div>
            </div>
        @else
            <!-- Overview Cards -->
            <div class="grid gap-6 md:grid-cols-2">
                 <div class="sim-card p-6 relative overflow-hidden group">
                     <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-24 h-24 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                     </div>
                     <p class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-1">Aktuelles Budget</p>
                     <p class="text-3xl font-bold text-white font-mono tracking-tight">{{ number_format((float) $activeClub->budget, 2, ',', '.') }} €</p>
                     <div class="mt-4 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-400">
                         <span class="flex items-center gap-1 bg-emerald-500/10 px-2 py-1 rounded border border-emerald-500/20">
                             <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                             Verfügbar
                         </span>
                     </div>
                 </div>

                 <div class="sim-card p-6 relative overflow-hidden group">
                     <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-24 h-24 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                     </div>
                     <p class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-1">Club Coins</p>
                     <p class="text-3xl font-bold text-white font-mono tracking-tight">{{ number_format((int) $activeClub->coins, 0, ',', '.') }}</p>
                     <div class="mt-4 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-400">
                         <span class="flex items-center gap-1 bg-amber-500/10 px-2 py-1 rounded border border-amber-500/20">
                             <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             Premium Währung
                         </span>
                     </div>
                 </div>
            </div>

            <div class="sim-card p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700/50 bg-slate-900/40 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">Transaktionshistorie</h2>
                    <div class="text-xs text-slate-400">Die letzten Buchungen</div>
                </div>
                
                @if ($transactions->isEmpty())
                    <div class="p-12 text-center text-slate-500">
                        Keine Transaktionen gefunden.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-700/50 text-xs font-bold uppercase tracking-wider text-slate-400 bg-slate-900/40">
                                    <th class="px-6 py-4">Datum</th>
                                    <th class="px-6 py-4">Typ</th>
                                    <th class="px-6 py-4">Kategorie</th>
                                    <th class="px-6 py-4 text-right">Betrag</th>
                                    <th class="px-6 py-4 text-right">Saldo</th>
                                    <th class="px-6 py-4">Notiz</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                @foreach ($transactions as $transaction)
                                    @php
                                        $assetType = $transaction->asset_type ?? 'budget';
                                        $isCoin = $assetType === 'coins';
                                        $isIncome = $transaction->direction === 'income';
                                    @endphp
                                    <tr class="group hover:bg-white/[0.02] transition-colors">
                                        <td class="px-6 py-3 text-sm text-slate-300 font-mono">
                                            {{ $transaction->booked_at?->format('d.m.Y H:i') }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $isIncome ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                                {{ $transaction->context_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-slate-400 capitalize">
                                            {{ $assetType }}
                                        </td>
                                        <td class="px-6 py-3 text-right font-mono font-medium {{ $isIncome ? 'text-emerald-400' : 'text-rose-400' }}">
                                            {{ $isIncome ? '+' : '-' }} {{ $isCoin ? number_format((float) $transaction->amount, 0, ',', '.') : number_format((float) $transaction->amount, 2, ',', '.') }}
                                            <span class="text-[10px] text-slate-500 ml-1">{{ $isCoin ? 'C' : '€' }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-right font-mono text-slate-300">
                                            @if ($transaction->balance_after !== null)
                                                {{ $isCoin ? number_format((float) $transaction->balance_after, 0, ',', '.') : number_format((float) $transaction->balance_after, 2, ',', '.') }}
                                                <span class="text-[10px] text-slate-500 ml-1">{{ $isCoin ? 'C' : '€' }}</span>
                                            @else
                                                <span class="text-slate-600">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-sm text-slate-400 italic">
                                            {{ $transaction->note ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($transactions->hasPages())
                        <div class="px-6 py-4 border-t border-slate-700/50 bg-slate-900/30">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
