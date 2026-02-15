<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">ACP Spieler</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Globale Spielerverwaltung</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="GET" action="{{ route('admin.players.index') }}">
                    <select name="club" class="sim-select" onchange="this.form.submit()">
                        <option value="">Alle Vereine (Tabellenansicht)</option>
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}" @selected($activeClubId === $club->id)>
                                {{ $club->name }} (Owner: {{ $club->user?->name ?? 'CPU' }})
                            </option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('admin.players.create') }}" class="sim-btn-primary">Spieler erstellen</a>
            </div>
        </div>
    </x-slot>

    <!-- Squad Summary -->
    <div class="sim-card p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <p class="sim-section-title flex items-center gap-2">
                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
                Kader-Analyse @if($activeClubId) (Gefiltert) @endif
            </p>
            <span class="text-xs font-bold bg-slate-800 px-2 py-1 rounded text-slate-300 border border-slate-700">
                {{ $squadStats['count'] }} Spieler @if($players instanceof \Illuminate\Pagination\LengthAwarePaginator)
                (Seite {{ $players->currentPage() }}) @endif
            </span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="sim-card-soft p-4 border-l-4 border-l-cyan-500">
                <p class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Ø Staerke / Alter</p>
                <p class="text-xl font-bold text-white mt-1">
                    {{ $squadStats['avg_rating'] }} <span class="text-slate-500 text-sm font-normal">/ 99</span>
                </p>
                <p class="text-xs text-slate-400 mt-0.5">Alter {{ $squadStats['avg_age'] }} Jahre</p>
            </div>
            <div class="sim-card-soft p-4 border-l-4 border-l-emerald-500">
                <p class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Gesamtmarktwert</p>
                <p class="text-xl font-bold text-emerald-300 mt-1">
                    {{ number_format($squadStats['total_value'], 0, ',', '.') }} €
                </p>
                <p class="text-xs text-slate-400 mt-0.5">Ø {{ number_format($squadStats['avg_value'], 0, ',', '.') }} €
                </p>
            </div>
            <div class="sim-card-soft p-4 border-l-4 border-l-amber-500">
                <p class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Verfuegbarkeit</p>
                <div class="flex items-center gap-3 mt-1">
                    <div class="flex items-center gap-1.5 text-amber-400 font-bold">
                        {{ $squadStats['injured_count'] }} verletzt
                    </div>
                    <div class="h-4 w-px bg-slate-700"></div>
                    <div class="flex items-center gap-1.5 text-rose-400 font-bold">
                        {{ $squadStats['suspended_count'] }} gesperrt
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($groupedPlayers)
        <!-- Grouped Squad View (Active Club) -->
        <div class="space-y-6">
            @foreach ($groupedPlayers as $positionGroup => $groupPlayers)
                <div class="sim-card p-5" x-data="{ expanded: true }">
                    <button @click="expanded = !expanded"
                        class="w-full flex items-center justify-between gap-2 mb-4 group transition-all duration-200">
                        <div class="sim-section-title flex items-center gap-2 text-cyan-400 mb-0">
                            {{ $positionGroup }} <span
                                class="text-xs text-slate-500 font-normal">({{ $groupPlayers->count() }})</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-500 transform transition-transform duration-200"
                            :class="{ 'rotate-180': !expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        </svg>
                    </button>

                    <div x-show="expanded" x-transition.opacity.duration.200ms>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($groupPlayers as $player)
                                <div
                                    class="sim-card-soft p-3 hover:border-cyan-500/30 transition-all group relative overflow-hidden flex flex-col gap-3">
                                    <div class="flex items-center gap-3 relative z-10">
                                        <div class="shrink-0">
                                            <img src="{{ $player->photo_url }}"
                                                class="h-8 w-8 rounded-full object-cover border border-slate-700"
                                                alt="{{ $player->full_name }}">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('admin.players.edit', $player) }}"
                                                class="font-bold text-white truncate hover:text-cyan-400 transition-colors block text-sm">
                                                {{ $player->full_name }}
                                            </a>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span
                                                    class="text-[10px] font-bold text-cyan-400 bg-cyan-950/30 px-1.5 py-0.5 rounded border border-cyan-500/20">{{ $player->display_position }}</span>
                                                <span class="text-[10px] text-slate-400 ml-auto">{{ $player->age }}J</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-end justify-between relative z-10 pt-2 border-t border-slate-700/50">
                                        <div>
                                            <p class="text-[10px] font-bold uppercase text-slate-500 mb-1">Staerke</p>
                                            <span class="text-lg font-bold text-white leading-none">{{ $player->overall }}</span>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] font-bold uppercase text-slate-500 mb-1">Edit</p>
                                            <a href="{{ route('admin.players.edit', $player) }}"
                                                class="text-xs text-cyan-400 font-bold hover:underline">Details</a>
                                        </div>
                                    </div>

                                    <div
                                        class="absolute -bottom-2 -right-2 text-[40px] font-black text-white/[0.03] select-none pointer-events-none">
                                        {{ $player->display_position }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Standard Table View (All Clubs or Paginated) -->
        <div class="sim-card overflow-x-auto">
            <table class="sim-table min-w-full">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Verein</th>
                        <th>Owner</th>
                        <th class="text-center">Pos.</th>
                        <th class="text-center">OVR</th>
                        <th class="text-center">Alter</th>
                        <th class="text-right">Marktwert</th>
                        <th class="text-right">Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($players as $player)
                        <tr class="hover:bg-white/[0.02]">
                            <td>
                                <span class="inline-flex items-center gap-2">
                                    <img class="h-5 w-5 rounded-full object-cover ring-1 ring-slate-800" src="{{ $player->photo_url }}"
                                        alt="{{ $player->full_name }}">
                                    <span>{{ $player->full_name }}</span>
                                </span>
                            </td>
                            <td>
                                @if($player->club)
                                    <span class="inline-flex items-center gap-2">
                                        <img class="h-5 w-5 rounded-full object-cover ring-1 ring-slate-800" src="{{ $player->club->logo_url }}"
                                            alt="{{ $player->club->name }}">
                                        <span>{{ $player->club->name }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-500 italic">Vereinslos</span>
                                @endif
                            </td>
                            <td>{{ $player->club?->user?->name ?? '-' }}</td>
                            <td class="text-center">
                                <span
                                    class="bg-slate-800 px-1.5 py-0.5 rounded text-[10px] font-bold text-cyan-400">{{ $player->position }}</span>
                            </td>
                            <td class="text-center font-bold">{{ $player->overall }}</td>
                            <td class="text-center text-slate-400">{{ $player->age }}</td>
                            <td class="text-right font-mono text-emerald-400">
                                {{ number_format($player->market_value, 0, ',', '.') }} €</td>
                            <td class="text-right">
                                <a href="{{ route('admin.players.edit', $player) }}"
                                    class="text-cyan-300 hover:text-cyan-200">Bearbeiten</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400 italic">Keine Spieler vorhanden.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($players instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">
                {{ $players->links() }}
            </div>
        @endif
    @endif
</x-app-layout>