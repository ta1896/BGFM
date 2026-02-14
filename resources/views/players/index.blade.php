<x-app-layout>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Team Management</p>
                <h1 class="text-3xl font-bold text-white tracking-tight">Kaderübersicht</h1>
            </div>
            
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('players.index') }}" class="flex items-center">
                    <div class="relative group">
                        <select name="club" class="sim-input pl-4 pr-10 py-2 text-sm bg-slate-900/50 border-slate-700 focus:border-cyan-500 rounded-lg appearance-none cursor-pointer min-w-[200px]" onchange="this.form.submit()">
                            <option value="">Alle Vereine</option>
                            @foreach ($clubs as $c)
                                <option value="{{ $c->id }}" @selected($activeClubId == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400 group-hover:text-cyan-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </form>
                
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.players.create') }}" class="sim-btn-primary text-sm py-2 px-4 shadow-lg shadow-cyan-500/20">
                        <span class="flex items-center gap-2">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                             Spieler erstellen
                        </span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Players Table Card -->
        <div class="sim-card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-700/50 text-xs font-bold uppercase tracking-wider text-slate-400 bg-slate-900/40">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Verein</th>
                            <th class="px-6 py-4 text-center">Pos</th>
                            <th class="px-6 py-4 text-center">OVR</th>
                            <th class="px-6 py-4 text-center">Alter</th>
                            <th class="px-6 py-4 text-right">Marktwert</th>
                            <th class="px-6 py-4 text-right">Aktion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse ($players as $player)
                            <tr class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden shrink-0">
                                                @if($player->photo_url)
                                                    <img src="{{ $player->photo_url }}" alt="{{ $player->full_name }}" class="w-full h-full object-cover">
                                                @else
                                                     <span class="text-xs font-bold text-slate-500">{{ substr($player->first_name, 0, 1) }}{{ substr($player->last_name, 0, 1) }}</span>
                                                @endif
                                            </div>
                                            <!-- Country Flag could go here absolute bottom-0 right-0 -->
                                        </div>
                                        <div>
                                            <div class="font-bold text-white group-hover:text-cyan-400 transition-colors">{{ $player->full_name }}</div>
                                            <div class="text-xs text-slate-500">Trikot #{{ $player->shirt_number ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    @if($player->club)
                                    <div class="flex items-center gap-2 text-sm text-slate-300">
                                        <div class="w-6 h-6 rounded bg-slate-800 flex items-center justify-center p-0.5">
                                            <img src="{{ $player->club->logo_url }}" alt="{{ $player->club->name }}" class="max-w-full max-h-full">
                                        </div>
                                        <span>{{ $player->club->name }}</span>
                                    </div>
                                    @else
                                        <span class="text-sm text-slate-500 italic">Free Agent</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700 min-w-[2.5rem]">
                                        {{ $player->position }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <div class="inline-flex items-center justify-center w-9 h-9 rounded bg-slate-900 border border-slate-700 font-bold {{ $player->overall >= 80 ? 'text-emerald-400 border-emerald-500/30 shadow-[0_0_10px_rgba(52,211,153,0.2)]' : ($player->overall >= 70 ? 'text-cyan-400 border-cyan-500/30' : 'text-slate-400') }}">
                                        {{ $player->overall }}
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center text-slate-400 font-medium">
                                    {{ $player->age }}
                                </td>
                                <td class="px-6 py-3 text-right font-mono text-emerald-400">
                                    {{ number_format((float) $player->market_value, 0, ',', '.') }} €
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('players.show', $player) }}" class="sim-btn-muted text-xs py-1.5 px-3">
                                        Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-500">
                                        <svg class="w-12 h-12 mb-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <p class="text-lg font-medium text-white">Keine Spieler gefunden</p>
                                        <p class="text-sm">Versuche es mit einem anderen Suchfilter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($players->hasPages())
                <div class="px-6 py-4 border-t border-slate-700/50 bg-slate-900/30">
                    {{ $players->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
