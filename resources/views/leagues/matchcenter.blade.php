@php
    $statusLabel = $match->status === 'played' ? 'Beendet' : ucfirst($match->status);
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
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="sim-info-pill">🏟️ {{ $match->stadiumClub?->name ?? 'Stadion n/a' }}</span>
                <span class="sim-info-pill">👥 {{ $match->attendance ? number_format($match->attendance, 0, ',', '.') : '0' }} Zuschauer</span>
                <span class="sim-info-pill">🌧️ {{ $match->weather ?? 'Wetter n/a' }}</span>
                <span class="sim-info-pill">🌱 Rasen: Gut</span>
                <span class="sim-info-pill">🚪 Kabinen: Gut</span>
            </div>

            <div class="sim-card p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        @if ($match->homeClub->logo_path)
                            <img class="h-12 w-12 rounded-full border border-slate-700/80 bg-slate-900/60 object-cover"
                                 src="{{ Storage::url($match->homeClub->logo_path) }}"
                                 alt="">
                        @else
                            <span class="h-12 w-12 rounded-full border border-slate-700/80 bg-slate-900/60"></span>
                        @endif
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Heim</p>
                            <p class="text-lg font-semibold text-white">{{ $match->homeClub->name }}</p>
                        </div>
                    </div>

                    <div class="text-center">
                        <div class="text-4xl font-bold text-white">
                            {{ $match->home_score ?? '-' }} : {{ $match->away_score ?? '-' }}
                        </div>
                        <span class="sim-status-badge">● {{ $statusLabel }}</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Auswaerts</p>
                            <p class="text-lg font-semibold text-white">{{ $match->awayClub->name }}</p>
                        </div>
                        @if ($match->awayClub->logo_path)
                            <img class="h-12 w-12 rounded-full border border-slate-700/80 bg-slate-900/60 object-cover"
                                 src="{{ Storage::url($match->awayClub->logo_path) }}"
                                 alt="">
                        @else
                            <span class="h-12 w-12 rounded-full border border-slate-700/80 bg-slate-900/60"></span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('league.matches', ['competition_season' => $match->competition_season_id]) }}" class="sim-btn-muted">Zurueck</a>
                    @if ($match->status !== 'played')
                        @foreach ($manageableClubIds as $clubId)
                            <a href="{{ route('matches.lineup.edit', ['match' => $match->id, 'club' => $clubId]) }}" class="sim-btn-muted">
                                Aufstellung {{ $clubId === $match->home_club_id ? '(Heim)' : '(Auswaerts)' }}
                            </a>
                        @endforeach
                    @endif
                </div>
                @if ($canSimulate)
                    <form method="POST" action="{{ route('matches.simulate', $match) }}">
                        @csrf
                        <button type="submit" class="sim-btn-primary">Jetzt simulieren</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <section class="sim-card p-4">
        <div class="flex flex-wrap gap-2">
            @foreach (['Fan-Ticker', 'Spielfeld', 'Statistiken', 'Heatmap', 'Aufstellungen', 'Spielwerte'] as $tab)
                <button type="button" class="sim-tab {{ $loop->first ? 'sim-tab-active' : '' }}">{{ $tab }}</button>
            @endforeach
        </div>
    </section>

    <section class="sim-card p-5">
        <div class="flex items-center gap-2">
            <span class="sim-ticker-dot"></span>
            <p class="sim-section-title">Fan-Ticker</p>
        </div>

        @if ($match->events->isEmpty())
            <p class="mt-4 text-sm text-slate-300">Noch keine Match-Events vorhanden.</p>
        @else
            <div class="mt-4 space-y-2">
                @foreach ($match->events->sortByDesc(fn ($event) => ($event->minute * 60) + $event->second) as $event)
                    @php
                        $label = $eventLabels[$event->event_type] ?? strtoupper(str_replace('_', ' ', $event->event_type));
                        $timeLabel = str_pad((string) $event->minute, 2, '0', STR_PAD_LEFT)."'";
                        if ($event->second) {
                            $timeLabel .= '+';
                        }
                    @endphp
                    <div class="sim-ticker-row">
                        <div class="sim-ticker-dot"></div>
                        <div class="flex-1">
                            <p class="text-sm text-slate-100">
                                <span class="font-semibold text-cyan-200">{{ $label }}</span>
                                @if ($event->player)
                                    – {{ $event->player->full_name }}
                                @endif
                                @if ($event->assister)
                                    (Assist: {{ $event->assister->full_name }})
                                @endif
                                @if ($event->club)
                                    <span class="text-slate-400">({{ $event->club->short_name ?: $event->club->name }})</span>
                                @endif
                            </p>
                        </div>
                        <div class="sim-ticker-time">{{ $timeLabel }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="sim-card overflow-x-auto">
        <div class="border-b border-slate-800/80 px-4 py-3">
            <p class="sim-section-title">Spielerbewertungen</p>
        </div>
        @if ($match->playerStats->isEmpty())
            <p class="px-4 py-6 text-sm text-slate-300">Keine Spielerstatistiken verfuegbar.</p>
        @else
            <table class="sim-table min-w-full">
                <thead>
                    <tr>
                        <th>Verein</th>
                        <th>Spieler</th>
                        <th>Pos</th>
                        <th>Note</th>
                        <th>Tore</th>
                        <th>Assists</th>
                        <th>Min</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($match->playerStats->sortByDesc('rating') as $stat)
                        <tr>
                            <td>{{ $stat->club->short_name ?: $stat->club->name }}</td>
                            <td>{{ $stat->player->full_name }}</td>
                            <td>{{ $stat->position_code }}</td>
                            <td class="font-semibold">{{ number_format((float) $stat->rating, 2, ',', '.') }}</td>
                            <td>{{ $stat->goals }}</td>
                            <td>{{ $stat->assists }}</td>
                            <td>{{ $stat->minutes_played }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</x-app-layout>
