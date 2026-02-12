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
        <div class="space-y-4" id="match-live-root" data-match-id="{{ $match->id }}">
            <div class="flex flex-wrap items-center gap-2">
                <span class="sim-info-pill">Stadion {{ $match->stadiumClub?->name ?? 'n/a' }}</span>
                <span class="sim-info-pill">
                    Zuschauer <span id="live-attendance">{{ $match->attendance ? number_format($match->attendance, 0, ',', '.') : '0' }}</span>
                </span>
                <span class="sim-info-pill">Wetter {{ $match->weather ?? 'n/a' }}</span>
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
                        <div class="text-4xl font-bold text-white" id="live-score">
                            {{ $match->home_score ?? 0 }} : {{ $match->away_score ?? 0 }}
                        </div>
                        <span class="sim-status-badge" id="live-status">● {{ $statusLabel }}</span>
                        <p class="mt-2 text-sm text-slate-300">
                            Minute <span id="live-minute">{{ (int) $match->live_minute }}</span>'
                        </p>
                        <p class="mt-1 text-xs text-rose-300" id="live-error">{{ $match->live_error_message }}</p>
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

                @if ($canSimulate && $match->status !== 'played')
                    <p class="text-xs text-slate-300">
                        Live-Ticker wird automatisch per Cronjob fortgeschrieben.
                    </p>
                @endif
            </div>

            @if ($canSimulate && $match->status !== 'played')
                <section class="sim-card p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="sim-section-title">Live Eingriff: Taktik</p>
                        <button type="button" class="sim-btn-muted hidden" id="live-resume-btn">Simulation fortsetzen</button>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-3">
                        @foreach ($manageableClubIds as $clubId)
                            <div class="rounded border border-slate-700/80 p-3">
                                <p class="mb-2 text-sm text-slate-300">
                                    {{ $clubId === $match->home_club_id ? $match->homeClub->name : $match->awayClub->name }}
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach (['balanced' => 'Balanced', 'offensive' => 'Offensiv', 'defensive' => 'Defensiv', 'counter' => 'Konter'] as $style => $label)
                                        <button
                                            type="button"
                                            class="sim-btn-muted !px-2 !py-1 text-xs"
                                            data-live-action="style"
                                            data-club-id="{{ $clubId }}"
                                            data-style="{{ $style }}"
                                        >{{ $label }}</button>
                                    @endforeach
                                </div>

                                <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                    <select class="sim-input !py-1 text-xs" data-sub-out="{{ $clubId }}"></select>
                                    <select class="sim-input !py-1 text-xs" data-sub-in="{{ $clubId }}"></select>
                                    <select class="sim-input !py-1 text-xs" data-sub-slot="{{ $clubId }}"></select>
                                </div>
                                <div class="mt-2">
                                    <button
                                        type="button"
                                        class="sim-btn-primary !px-2 !py-1 text-xs"
                                        data-live-action="substitute"
                                        data-club-id="{{ $clubId }}"
                                    >
                                        Wechsel ausfuehren
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
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

        <div id="live-events-container">
            @if ($match->events->isEmpty())
                <p class="mt-4 text-sm text-slate-300" id="live-events-empty">Noch keine Match-Events vorhanden.</p>
            @else
                <div class="mt-4 space-y-2" id="live-events-list">
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
                                        - {{ $event->player->full_name }}
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
        </div>
    </section>

    <section class="sim-card p-5">
        <div class="flex items-center gap-2">
            <span class="sim-ticker-dot"></span>
            <p class="sim-section-title">Live Spielwerte</p>
        </div>
        <div class="mt-4" id="live-team-states-container">
            <p class="text-sm text-slate-300">Noch keine Team-Livewerte vorhanden.</p>
        </div>
    </section>

    <section class="sim-card p-5">
        <div class="flex items-center gap-2">
            <span class="sim-ticker-dot"></span>
            <p class="sim-section-title">Aktionskette</p>
        </div>
        <div class="mt-3 grid gap-2 sm:grid-cols-4">
            <select id="action-filter-type" class="sim-input !py-1 text-xs">
                <option value="">Alle Aktionstypen</option>
                <option value="possession">Ballbesitz</option>
                <option value="pass">Pass</option>
                <option value="tackle">Tackle</option>
                <option value="foul">Foul</option>
                <option value="set_piece">Standard</option>
                <option value="shot">Abschluss</option>
                <option value="penalty">Elfmeter</option>
                <option value="injury">Verletzung</option>
                <option value="substitution">Wechsel</option>
                <option value="phase">Phase</option>
                <option value="tactical_change">Taktikwechsel</option>
                <option value="penalty_shootout">Elfmeterschiessen</option>
            </select>
            <select id="action-filter-club" class="sim-input !py-1 text-xs">
                <option value="">Alle Vereine</option>
                <option value="{{ $match->home_club_id }}">{{ $match->homeClub->name }}</option>
                <option value="{{ $match->away_club_id }}">{{ $match->awayClub->name }}</option>
            </select>
            <input id="action-filter-query" class="sim-input !py-1 text-xs" type="text" placeholder="Spieler/Ergebnis filtern">
            <button id="action-filter-reset" type="button" class="sim-btn-muted !px-2 !py-1 text-xs">Filter zuruecksetzen</button>
        </div>
        <p class="mt-2 text-xs text-slate-400" id="actions-count-label">0 / 0 Eintraege</p>
        <div class="mt-4" id="live-actions-container">
            <p class="text-sm text-slate-300">Noch keine Live-Aktionen vorhanden.</p>
        </div>
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

    <script>
        (() => {
            const root = document.getElementById('match-live-root');
            if (!root) {
                return;
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const labels = @json($eventLabels);
            const canSimulate = @json($canSimulate);
            const homeClubId = Number(@json($match->home_club_id));
            const awayClubId = Number(@json($match->away_club_id));
            const clubNames = {
                [homeClubId]: @json($match->homeClub->name),
                [awayClubId]: @json($match->awayClub->name),
            };

            const routes = {
                state: "{{ route('matches.live.state', $match) }}",
                resume: "{{ route('matches.live.resume', $match) }}",
                style: "{{ route('matches.live.style', $match) }}",
                substitute: "{{ route('matches.live.substitute', $match) }}",
            };

            const scoreEl = document.getElementById('live-score');
            const statusEl = document.getElementById('live-status');
            const minuteEl = document.getElementById('live-minute');
            const errorEl = document.getElementById('live-error');
            const eventsContainer = document.getElementById('live-events-container');
            const teamStatesContainer = document.getElementById('live-team-states-container');
            const actionsContainer = document.getElementById('live-actions-container');
            const actionTypeFilterEl = document.getElementById('action-filter-type');
            const actionClubFilterEl = document.getElementById('action-filter-club');
            const actionQueryFilterEl = document.getElementById('action-filter-query');
            const actionFilterResetEl = document.getElementById('action-filter-reset');
            const actionsCountLabelEl = document.getElementById('actions-count-label');
            const resumeBtn = document.getElementById('live-resume-btn');
            let latestActions = [];

            const sendPost = async (url, payload = {}) => {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    return null;
                }

                return response.json();
            };

            const populateSelect = (select, options, selected) => {
                if (!select) {
                    return;
                }
                const previous = selected ?? select.value;
                select.innerHTML = '';
                options.forEach((entry) => {
                    const option = document.createElement('option');
                    option.value = String(entry.value);
                    option.textContent = entry.label;
                    if (String(entry.value) === String(previous)) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            };

            const renderEvents = (events) => {
                if (!events || events.length === 0) {
                    eventsContainer.innerHTML = '<p class="mt-4 text-sm text-slate-300" id="live-events-empty">Noch keine Match-Events vorhanden.</p>';
                    return;
                }

                const rows = events.map((event) => {
                    const label = labels[event.event_type] || event.event_type.toUpperCase().replaceAll('_', ' ');
                    const minute = String(event.minute).padStart(2, '0');
                    const time = `${minute}'${event.second ? '+' : ''}`;
                    const player = event.player_name ? ` - ${event.player_name}` : '';
                    const assist = event.assister_name ? ` (Assist: ${event.assister_name})` : '';
                    const club = event.club_short_name ? ` <span class="text-slate-400">(${event.club_short_name})</span>` : '';

                    return `<div class="sim-ticker-row">
                        <div class="sim-ticker-dot"></div>
                        <div class="flex-1">
                            <p class="text-sm text-slate-100">
                                <span class="font-semibold text-cyan-200">${label}</span>${player}${assist}${club}
                            </p>
                        </div>
                        <div class="sim-ticker-time">${time}</div>
                    </div>`;
                }).join('');

                eventsContainer.innerHTML = `<div class="mt-4 space-y-2" id="live-events-list">${rows}</div>`;
            };

            const renderTeamStates = (teamStates) => {
                if (!teamStates || Object.keys(teamStates).length === 0) {
                    teamStatesContainer.innerHTML = '<p class="text-sm text-slate-300">Noch keine Team-Livewerte vorhanden.</p>';
                    return;
                }

                const possessionTotal = Object.values(teamStates).reduce((carry, row) => carry + Number(row.possession_seconds || 0), 0);
                const orderedRows = [teamStates[String(homeClubId)], teamStates[String(awayClubId)]].filter(Boolean);

                const rows = orderedRows.map((row) => {
                    const possessionPct = possessionTotal > 0
                        ? Math.round((Number(row.possession_seconds || 0) / possessionTotal) * 100)
                        : 50;
                    const passAttempts = Number(row.pass_attempts || 0);
                    const passCompletions = Number(row.pass_completions || 0);
                    const passPct = passAttempts > 0 ? Math.round((passCompletions / passAttempts) * 100) : 0;
                    const shots = Number(row.shots || 0);
                    const shotsOnTarget = Number(row.shots_on_target || 0);
                    const tackles = Number(row.tackle_attempts || 0);
                    const tacklesWon = Number(row.tackle_won || 0);
                    const tacklePct = tackles > 0 ? Math.round((tacklesWon / tackles) * 100) : 0;

                    return `<div class="rounded border border-slate-700/80 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-100">${clubNames[Number(row.club_id)] || `Verein ${row.club_id}`}</p>
                            <span class="sim-info-pill">Stil ${row.tactical_style || 'balanced'}</span>
                        </div>
                        <div class="mt-3 grid gap-2 text-xs text-slate-300 sm:grid-cols-2 xl:grid-cols-4">
                            <div>Ballbesitz <span class="font-semibold text-slate-100">${possessionPct}%</span></div>
                            <div>Passe <span class="font-semibold text-slate-100">${passCompletions}/${passAttempts} (${passPct}%)</span></div>
                            <div>Torschuesse <span class="font-semibold text-slate-100">${shots} (${shotsOnTarget} aufs Tor)</span></div>
                            <div>xG <span class="font-semibold text-slate-100">${Number(row.expected_goals || 0).toFixed(2)}</span></div>
                            <div>Gefaehrliche Angriffe <span class="font-semibold text-slate-100">${Number(row.dangerous_attacks || 0)}</span></div>
                            <div>Tackles <span class="font-semibold text-slate-100">${tacklesWon}/${tackles} (${tacklePct}%)</span></div>
                            <div>Fouls / Ecken <span class="font-semibold text-slate-100">${Number(row.fouls_committed || 0)} / ${Number(row.corners_won || 0)}</span></div>
                            <div>Karten (G/R) <span class="font-semibold text-slate-100">${Number(row.yellow_cards || 0)} / ${Number(row.red_cards || 0)}</span></div>
                        </div>
                    </div>`;
                }).join('');

                teamStatesContainer.innerHTML = `<div class="grid gap-3">${rows}</div>`;
            };

            const applyActionFilters = (actions) => {
                const typeFilter = (actionTypeFilterEl?.value || '').trim().toLowerCase();
                const clubFilter = (actionClubFilterEl?.value || '').trim();
                const query = (actionQueryFilterEl?.value || '').trim().toLowerCase();

                return (actions || []).filter((action) => {
                    if (typeFilter !== '' && String(action.action_type || '').toLowerCase() !== typeFilter) {
                        return false;
                    }

                    if (clubFilter !== '' && String(action.club_id ?? '') !== clubFilter) {
                        return false;
                    }

                    if (query !== '') {
                        const haystack = [
                            action.player_name,
                            action.opponent_player_name,
                            action.club_short_name,
                            action.action_type,
                            action.outcome,
                        ].join(' ').toLowerCase();
                        if (!haystack.includes(query)) {
                            return false;
                        }
                    }

                    return true;
                });
            };

            const renderActions = (actions) => {
                latestActions = actions || [];
                const filtered = applyActionFilters(latestActions);

                if (actionsCountLabelEl) {
                    actionsCountLabelEl.textContent = `${filtered.length} / ${latestActions.length} Eintraege`;
                }

                if (!filtered || filtered.length === 0) {
                    actionsContainer.innerHTML = '<p class="text-sm text-slate-300">Noch keine Live-Aktionen vorhanden.</p>';
                    return;
                }

                const rows = filtered.map((action) => {
                    const minute = String(action.minute ?? 0).padStart(2, '0');
                    const second = String(action.second ?? 0).padStart(2, '0');
                    const actor = action.player_name ? ` - ${action.player_name}` : '';
                    const opponent = action.opponent_player_name ? ` vs ${action.opponent_player_name}` : '';
                    const club = action.club_short_name ? ` <span class="text-slate-400">(${action.club_short_name})</span>` : '';
                    const outcome = action.outcome ? ` <span class="text-cyan-300">${action.outcome}</span>` : '';
                    const type = (action.action_type || 'action').toString().replaceAll('_', ' ').toUpperCase();

                    return `<div class="sim-ticker-row">
                        <div class="sim-ticker-dot"></div>
                        <div class="flex-1">
                            <p class="text-sm text-slate-100">
                                <span class="font-semibold text-cyan-200">${type}</span>${outcome}${actor}${opponent}${club}
                            </p>
                        </div>
                        <div class="sim-ticker-time">${minute}:${second}</div>
                    </div>`;
                }).join('');

                actionsContainer.innerHTML = `<div class="space-y-2">${rows}</div>`;
            };

            const renderState = (state) => {
                if (!state) {
                    return;
                }

                scoreEl.textContent = `${state.home_score ?? 0} : ${state.away_score ?? 0}`;
                statusEl.textContent = `● ${state.status_label}`;
                minuteEl.textContent = String(state.live_minute ?? 0);
                errorEl.textContent = state.live_error_message || '';
                renderEvents(state.events || []);
                renderLineupControls(state.lineups || {});
                renderTeamStates(state.team_states || {});
                renderActions(state.actions || []);

                if (resumeBtn) {
                    const showResume = canSimulate && state.status === 'live' && state.live_paused && !!state.live_error_message;
                    resumeBtn.classList.toggle('hidden', !showResume);
                }
            };

            const renderLineupControls = (lineups) => {
                Object.entries(lineups).forEach(([clubId, lineup]) => {
                    const outSelect = document.querySelector(`[data-sub-out="${clubId}"]`);
                    const inSelect = document.querySelector(`[data-sub-in="${clubId}"]`);
                    const slotSelect = document.querySelector(`[data-sub-slot="${clubId}"]`);

                    const starters = (lineup.starters || []).map((starter) => ({
                        value: starter.id,
                        label: `${starter.name} (${starter.slot})`,
                    }));
                    const bench = (lineup.bench || []).map((player) => ({
                        value: player.id,
                        label: `${player.name} (${player.position})`,
                    }));
                    const slots = (lineup.starters || []).map((starter) => ({
                        value: starter.slot,
                        label: `${starter.slot} - Fit ${starter.fit_factor}`,
                    }));

                    populateSelect(outSelect, starters.length > 0 ? starters : [{value: '', label: 'Kein Starter'}]);
                    populateSelect(inSelect, bench.length > 0 ? bench : [{value: '', label: 'Keine Bankspieler'}]);
                    populateSelect(slotSelect, slots.length > 0 ? slots : [{value: '', label: 'Keine Slots'}]);
                });
            };

            const fetchState = async () => {
                const response = await fetch(routes.state, {headers: {'Accept': 'application/json'}});
                if (!response.ok) {
                    return;
                }
                renderState(await response.json());
            };

            document.querySelectorAll('[data-live-action="style"]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const clubId = Number(button.dataset.clubId);
                    const style = button.dataset.style;
                    renderState(await sendPost(routes.style, {club_id: clubId, tactical_style: style}));
                });
            });

            document.querySelectorAll('[data-live-action="substitute"]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const clubId = button.dataset.clubId;
                    const outSelect = document.querySelector(`[data-sub-out="${clubId}"]`);
                    const inSelect = document.querySelector(`[data-sub-in="${clubId}"]`);
                    const slotSelect = document.querySelector(`[data-sub-slot="${clubId}"]`);
                    const playerOutId = Number(outSelect?.value || 0);
                    const playerInId = Number(inSelect?.value || 0);
                    const targetSlot = String(slotSelect?.value || '');

                    if (!playerOutId || !playerInId) {
                        return;
                    }

                    renderState(await sendPost(routes.substitute, {
                        club_id: Number(clubId),
                        player_out_id: playerOutId,
                        player_in_id: playerInId,
                        target_slot: targetSlot,
                    }));
                });
            });

            if (resumeBtn) {
                resumeBtn.addEventListener('click', async () => {
                    renderState(await sendPost(routes.resume));
                });
            }

            if (actionTypeFilterEl) {
                actionTypeFilterEl.addEventListener('change', () => renderActions(latestActions));
            }
            if (actionClubFilterEl) {
                actionClubFilterEl.addEventListener('change', () => renderActions(latestActions));
            }
            if (actionQueryFilterEl) {
                actionQueryFilterEl.addEventListener('input', () => renderActions(latestActions));
            }
            if (actionFilterResetEl) {
                actionFilterResetEl.addEventListener('click', () => {
                    if (actionTypeFilterEl) {
                        actionTypeFilterEl.value = '';
                    }
                    if (actionClubFilterEl) {
                        actionClubFilterEl.value = '';
                    }
                    if (actionQueryFilterEl) {
                        actionQueryFilterEl.value = '';
                    }
                    renderActions(latestActions);
                });
            }

            fetchState();
            setInterval(fetchState, 5000);
        })();
    </script>
</x-app-layout>
