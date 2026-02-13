@php
    $positionService = app(\App\Services\PlayerPositionService::class);
    $playersByPosition = $clubPlayers->groupBy(function ($player) use ($positionService) {
        return $positionService->groupFromPosition($player->position_main ?? $player->position) ?? 'MID';
    });
    $effectiveStarterDraft = old('starter_slots');
    $effectiveStarterDraft = is_array($effectiveStarterDraft) ? $effectiveStarterDraft : $starterDraft;
    $effectiveBenchDraft = old('bench_slots');
    $effectiveBenchDraft = is_array($effectiveBenchDraft) ? $effectiveBenchDraft : $benchDraft;
    $maxBenchPlayers = max(1, min(10, (int) ($maxBenchPlayers ?? 5)));
    $selectedPlayerIds = collect($effectiveStarterDraft)
        ->filter()
        ->map(static fn ($value) => (int) $value)
        ->values()
        ->concat(
            collect($effectiveBenchDraft)
                ->filter()
                ->map(static fn ($value) => (int) $value)
                ->values()
        )
        ->unique()
        ->all();
    $positionLabels = [
        'TW' => 'Torwart',
        'LV' => 'Linksverteidiger',
        'IV' => 'Innenverteidiger',
        'RV' => 'Rechtsverteidiger',
        'LWB' => 'Linker Wingback',
        'RWB' => 'Rechter Wingback',
        'LM' => 'Linkes Mittelfeld',
        'ZM' => 'Zentrales Mittelfeld',
        'RM' => 'Rechtes Mittelfeld',
        'DM' => 'Defensives Mittelfeld',
        'OM' => 'Offensives Mittelfeld',
        'LAM' => 'Linker Offensiver',
        'ZOM' => 'Zentrales Offensives Mittelfeld',
        'RAM' => 'Rechter Offensiver',
        'LS' => 'Linker Stuermer',
        'MS' => 'Mittelstuermer',
        'RS' => 'Rechter Stuermer',
        'LW' => 'Linker Fluegel',
        'RW' => 'Rechter Fluegel',
        'ST' => 'Stuermer',
    ];
    $groupLabels = [
        'GK' => 'Torwart',
        'DEF' => 'Abwehr',
        'MID' => 'Mittelfeld',
        'FWD' => 'Sturm',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="sim-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="sim-section-title">Aufstellung</p>
                    <h1 class="mt-1 text-2xl font-bold text-white">{{ old('name', $lineup->name) }}</h1>
                    <p class="mt-1 flex items-center gap-2 text-sm text-slate-300">
                        <img class="sim-avatar sim-avatar-xs" src="{{ $lineup->club->logo_url }}" alt="{{ $lineup->club->name }}">
                        <span>{{ $lineup->club->name }} | Vorlage bearbeiten</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($clubMatches->isNotEmpty())
                        @php
                            $firstMatch = $clubMatches->first();
                            $firstMatchUrl = route('matches.lineup.edit', [
                                'match' => $firstMatch->id,
                                'club' => $lineup->club_id,
                                'lineup' => $lineup->id,
                            ]);
                        @endphp
                        <div class="flex items-end gap-2">
                            <div>
                                <label class="sim-label mb-1" for="lineupMatchSelect">Match</label>
                                <select id="lineupMatchSelect" class="sim-select w-72">
                                    @foreach ($clubMatches as $clubMatch)
                                        @php
                                            $isHome = (int) $clubMatch->home_club_id === (int) $lineup->club_id;
                                            $opponent = $isHome ? $clubMatch->awayClub : $clubMatch->homeClub;
                                            $statusLabel = $clubMatch->status === 'live' ? 'LIVE' : strtoupper($clubMatch->status);
                                        @endphp
                                        <option
                                            value="{{ route('matches.lineup.edit', ['match' => $clubMatch->id, 'club' => $lineup->club_id, 'lineup' => $lineup->id]) }}"
                                        >
                                            {{ $clubMatch->kickoff_at?->format('d.m H:i') }} | vs {{ $opponent?->name ?? 'Unbekannt' }} | {{ $statusLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <a id="openMatchLineupLink" href="{{ $firstMatchUrl }}" class="sim-btn-primary">Fuer Match aufstellen</a>
                        </div>
                    @endif
                    <a href="{{ route('lineups.show', $lineup) }}" class="sim-btn-muted">Details</a>
                    <a href="{{ route('lineups.index', ['manage' => 1]) }}" class="sim-btn-muted">Alle Aufstellungen</a>
                </div>
            </div>
        </div>
    </x-slot>

    <form id="lineupEditForm" method="POST" action="{{ route('lineups.update', $lineup) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <section class="sim-card p-5">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="sim-label" for="name">Name</label>
                    <input id="name" name="name" type="text" class="sim-input" value="{{ old('name', $lineup->name) }}" required>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <label class="sim-label" for="formation">Formation</label>
                    <select id="formation" name="formation" class="sim-select">
                        @foreach ($formations as $formationOption)
                            <option value="{{ $formationOption }}" @selected(old('formation', $formation) === $formationOption)>{{ $formationOption }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('formation')" class="mt-1" />
                </div>
                <div>
                    <label class="sim-label" for="tactical_style">Spielstil</label>
                    <select id="tactical_style" name="tactical_style" class="sim-select">
                        @foreach (['balanced' => 'Ausgewogen', 'offensive' => 'Offensiv', 'defensive' => 'Defensiv', 'counter' => 'Konter'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('tactical_style', $tacticalStyle) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sim-label" for="attack_focus">Fokus</label>
                    <select id="attack_focus" name="attack_focus" class="sim-select">
                        @foreach (['left' => 'Linke Seite', 'center' => 'Zentrum', 'right' => 'Rechte Seite'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('attack_focus', $attackFocus) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <label class="sim-label" for="notes">Notizen</label>
                <textarea id="notes" name="notes" class="sim-textarea">{{ old('notes', $lineup->notes) }}</textarea>
            </div>
            <div class="mt-3 rounded-xl border border-slate-700/70 bg-slate-950/45 px-3 py-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-white">Aktive Standardaufstellung</p>
                        <p class="text-xs text-slate-400">Wird fuer kommende Spiele als Standard geladen.</p>
                    </div>
                    <label class="sim-switch">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="sr-only"
                            @checked(old('is_active', $lineup->is_active))
                        >
                        <span class="sim-switch-track" aria-hidden="true"></span>
                        <span class="sim-switch-label">Aktiv</span>
                    </label>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[2fr_1fr]">
            <article class="sim-card p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="sim-pill">Gesamtstaerke: {{ $metrics['overall'] }}</span>
                        <span class="sim-pill">Angriff: {{ $metrics['attack'] }}</span>
                        <span class="sim-pill">Mittelfeld: {{ $metrics['midfield'] }}</span>
                        <span class="sim-pill">Verteidigung: {{ $metrics['defense'] }}</span>
                    </div>
                    <button type="submit" name="action" value="auto_pick" class="sim-btn-muted">Staerkste Elf waehlen</button>
                </div>

                <x-input-error :messages="$errors->get('starter_slots')" class="mt-3" />

                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="sim-label">Elfmeter-Schuetze</label>
                        <select name="penalty_taker_player_id" class="sim-select">
                            <option value="">Kein Spieler</option>
                            @foreach ($clubPlayers as $player)
                                <option value="{{ $player->id }}" @selected((int) old('penalty_taker_player_id', $setPieces['penalty_taker_player_id']) === $player->id)>
                                    {{ $player->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sim-label">Freistoss-Schuetze</label>
                        <select name="free_kick_taker_player_id" class="sim-select">
                            <option value="">Kein Spieler</option>
                            @foreach ($clubPlayers as $player)
                                <option value="{{ $player->id }}" @selected((int) old('free_kick_taker_player_id', $setPieces['free_kick_taker_player_id']) === $player->id)>
                                    {{ $player->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sim-label">Ecke links</label>
                        <select name="corner_left_taker_player_id" class="sim-select">
                            <option value="">Kein Spieler</option>
                            @foreach ($clubPlayers as $player)
                                <option value="{{ $player->id }}" @selected((int) old('corner_left_taker_player_id', $setPieces['corner_left_taker_player_id']) === $player->id)>
                                    {{ $player->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sim-label">Ecke rechts</label>
                        <select name="corner_right_taker_player_id" class="sim-select">
                            <option value="">Kein Spieler</option>
                            @foreach ($clubPlayers as $player)
                                <option value="{{ $player->id }}" @selected((int) old('corner_right_taker_player_id', $setPieces['corner_right_taker_player_id']) === $player->id)>
                                    {{ $player->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 max-w-sm">
                    <label class="sim-label">Kapitaen</label>
                    <select name="captain_player_id" class="sim-select">
                        <option value="">Automatisch</option>
                        @foreach ($clubPlayers as $player)
                            <option value="{{ $player->id }}" @selected((int) old('captain_player_id', $captainPlayerId) === $player->id)>
                                {{ $player->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-6">
                    <p class="sim-section-title mb-2">Auswechselbank (max. {{ $maxBenchPlayers }})</p>
                    <div class="grid gap-2 sm:grid-cols-5">
                        @for ($i = 0; $i < $maxBenchPlayers; $i++)
                            @php
                                $benchSelectId = 'bench_slot_'.$i;
                            @endphp
                            <div
                                class="sim-bench-slot"
                                data-slot-container
                                data-select-id="{{ $benchSelectId }}"
                                data-slot-group="BENCH"
                                data-slot-role="BANK"
                            >
                                <div class="sim-bench-slot-title">Slot {{ $i + 1 }}</div>
                                <div class="sim-slot-player hidden" data-slot-player>
                                    <span class="sim-slot-player-name" data-slot-player-name>-</span>
                                    <button type="button" class="sim-slot-remove hidden" data-slot-remove title="Spieler entfernen">x</button>
                                </div>
                                <div class="sim-slot-hint" data-slot-hint>Spieler hierher ziehen</div>
                                <select
                                    id="{{ $benchSelectId }}"
                                    name="bench_slots[]"
                                    class="sim-select hidden"
                                    data-dnd-select
                                    data-bench-select
                                >
                                    <option value="">Slot {{ $i + 1 }}</option>
                                    @foreach ($clubPlayers as $player)
                                        <option value="{{ $player->id }}" @selected((int) ($effectiveBenchDraft[$i] ?? 0) === $player->id)>
                                            {{ $player->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="mt-6 sim-pitch">
                    <div class="sim-pitch-canvas">
                        @foreach ($slots as $slot)
                            @php
                                $slotSelectId = 'starter_slot_'.\Illuminate\Support\Str::slug($slot['slot'], '_');
                            @endphp
                            <div
                                class="sim-pitch-slot"
                                data-slot-container
                                data-select-id="{{ $slotSelectId }}"
                                data-slot-group="{{ $slot['group'] }}"
                                data-slot-role="{{ $slot['label'] }}"
                                style="left: {{ $slot['x'] }}%; top: {{ $slot['y'] }}%;"
                            >
                                <span class="sim-pitch-slot-label">{{ $slot['label'] }}</span>
                                <div class="sim-slot-player hidden" data-slot-player>
                                    <span class="sim-slot-player-name" data-slot-player-name>-</span>
                                    <button type="button" class="sim-slot-remove hidden" data-slot-remove title="Spieler entfernen">x</button>
                                </div>
                                <div class="sim-slot-hint" data-slot-hint>Spieler hierhin ziehen</div>
                                <select
                                    id="{{ $slotSelectId }}"
                                    name="starter_slots[{{ $slot['slot'] }}]"
                                    class="sim-pitch-select hidden"
                                    data-dnd-select
                                    data-starter-select
                                >
                                    <option value="">- Kein Spieler -</option>
                                    @foreach ($clubPlayers as $player)
                                        <option value="{{ $player->id }}" @selected((int) ($effectiveStarterDraft[$slot['slot']] ?? 0) === $player->id)>
                                            {{ $player->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    <button type="submit" name="action" value="save" class="sim-btn-primary">Aufstellung speichern</button>
                    <a href="{{ route('lineups.show', $lineup) }}" class="sim-btn-muted">Abbrechen</a>
                </div>
            </article>

            <aside class="sim-card p-5">
                <p class="sim-section-title">Spieler-Pool</p>
                <div class="mt-3 space-y-4">
                    @foreach ($groupLabels as $code => $label)
                        <div>
                            <h3 class="text-sm font-semibold text-white">{{ $label }}</h3>
                            <div class="mt-2 space-y-2">
                                @forelse ($playersByPosition->get($code, collect()) as $player)
                                    @php
                                        $position = $player->position_main ?? $player->position;
                                        $isSelected = in_array($player->id, $selectedPlayerIds, true);
                                    @endphp
                                    <div
                                        class="sim-card-soft sim-player-card px-3 py-2"
                                        draggable="true"
                                        data-player-id="{{ $player->id }}"
                                        data-player-name="{{ $player->full_name }}"
                                        data-position-main="{{ $player->position_main ?? $player->position }}"
                                        data-position-second="{{ $player->position_second ?? '' }}"
                                        data-position-third="{{ $player->position_third ?? '' }}"
                                    >
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <img class="sim-avatar sim-avatar-xs" src="{{ $player->photo_url }}" alt="{{ $player->full_name }}">
                                                <p class="text-sm font-semibold text-white">{{ $player->full_name }}</p>
                                            </div>
                                            <span class="sim-pill">OVR {{ $player->overall }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-400">
                                            <span class="sim-pill !px-2 !py-0.5 text-[10px]">{{ $positionLabels[$position] ?? $position }}</span>
                                            <span class="ml-1">{{ $player->age }} J.</span>
                                            <span class="ml-1">| {{ number_format((float) $player->market_value, 0, ',', '.') }} EUR</span>
                                            <span class="ml-1 {{ $isSelected ? '' : 'hidden' }}" data-player-picked>| Aufgestellt</span>
                                        </p>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <button type="button" class="sim-card-action" data-add-pitch>+ Feld</button>
                                            <button type="button" class="sim-card-action" data-add-bench>Bank</button>
                                            <button type="button" class="sim-card-action hidden" data-remove-player>Entfernen</button>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-500">Keine Spieler</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </aside>
        </section>
    </form>

    <p class="mt-4 text-xs text-slate-400">
        Hinweis: Wie in OpenWS kannst du Spieler ziehen und auf Feld-/Bank-Slots ablegen oder per Buttons schnell zuweisen.
    </p>

    <script>
        (function () {
            const lineupMatchSelect = document.getElementById('lineupMatchSelect');
            const openMatchLineupLink = document.getElementById('openMatchLineupLink');
            const formationSelect = document.getElementById('formation');

            if (lineupMatchSelect && openMatchLineupLink) {
                lineupMatchSelect.addEventListener('change', function () {
                    if (this.value) {
                        openMatchLineupLink.href = this.value;
                    }
                });
            }

            if (formationSelect) {
                formationSelect.addEventListener('change', function () {
                    const url = new URL(window.location.href);
                    url.searchParams.set('formation', this.value);
                    window.location.href = url.toString();
                });
            }

            const playerCards = Array.from(document.querySelectorAll('[data-player-id]'));
            const slotContainers = Array.from(document.querySelectorAll('[data-slot-container]'));
            const managedSelects = Array.from(document.querySelectorAll('[data-dnd-select]'));
            const starterSelects = managedSelects.filter(function (select) { return select.hasAttribute('data-starter-select'); });
            const benchSelects = managedSelects.filter(function (select) { return select.hasAttribute('data-bench-select'); });
            const playerCardMap = new Map(playerCards.map(function (card) { return [String(card.dataset.playerId), card]; }));

            function groupFromPosition(position) {
                const code = String(position || '').trim().toUpperCase();
                if (!code) {
                    return null;
                }

                if (code === 'TW' || code === 'GK') {
                    return 'GK';
                }
                if (['LV', 'IV', 'RV', 'LWB', 'RWB', 'DEF'].includes(code) || code.startsWith('IV')) {
                    return 'DEF';
                }
                if (['LM', 'ZM', 'RM', 'DM', 'OM', 'LAM', 'ZOM', 'RAM', 'MID'].includes(code) || code.startsWith('ZM') || code.startsWith('DM')) {
                    return 'MID';
                }

                return 'FWD';
            }

            function getSelectForContainer(container) {
                return document.getElementById(container.dataset.selectId);
            }

            function findSelectWithPlayer(playerId) {
                return managedSelects.find(function (select) {
                    return String(select.value) === String(playerId);
                }) || null;
            }

            function findPlayerCard(playerId) {
                return playerCardMap.get(String(playerId)) || null;
            }

            function fitsSlot(container, playerCard) {
                const slotGroup = container.dataset.slotGroup || '';
                if (!slotGroup || slotGroup === 'BENCH') {
                    return '';
                }

                const mainGroup = groupFromPosition(playerCard.dataset.positionMain);
                const secondGroup = groupFromPosition(playerCard.dataset.positionSecond);
                const thirdGroup = groupFromPosition(playerCard.dataset.positionThird);

                if (mainGroup && mainGroup === slotGroup) {
                    return 'primary';
                }
                if ((secondGroup && secondGroup === slotGroup) || (thirdGroup && thirdGroup === slotGroup)) {
                    return 'secondary';
                }

                return 'wrong';
            }

            function syncSlotView(container) {
                const select = getSelectForContainer(container);
                if (!select) {
                    return;
                }

                const slotPlayer = container.querySelector('[data-slot-player]');
                const slotPlayerName = container.querySelector('[data-slot-player-name]');
                const slotHint = container.querySelector('[data-slot-hint]');
                const slotRemove = container.querySelector('[data-slot-remove]');
                const selectedValue = String(select.value || '');

                container.classList.remove('sim-slot-state-primary', 'sim-slot-state-secondary', 'sim-slot-state-wrong');

                if (!selectedValue) {
                    if (slotPlayer) {
                        slotPlayer.classList.add('hidden');
                    }
                    if (slotHint) {
                        slotHint.classList.remove('hidden');
                    }
                    if (slotRemove) {
                        slotRemove.classList.add('hidden');
                    }

                    return;
                }

                const option = select.options[select.selectedIndex];
                const playerCard = findPlayerCard(selectedValue);
                if (slotPlayerName) {
                    slotPlayerName.textContent = option ? option.text : 'Spieler gesetzt';
                }
                if (slotPlayer) {
                    slotPlayer.classList.remove('hidden');
                }
                if (slotHint) {
                    slotHint.classList.add('hidden');
                }
                if (slotRemove) {
                    slotRemove.classList.remove('hidden');
                }

                if (playerCard) {
                    const fit = fitsSlot(container, playerCard);
                    if (fit) {
                        container.classList.add('sim-slot-state-' + fit);
                    }
                }
            }

            function syncPlayerCards() {
                playerCards.forEach(function (card) {
                    const playerId = card.dataset.playerId;
                    const selectedIn = findSelectWithPlayer(playerId);
                    const pickedMarker = card.querySelector('[data-player-picked]');
                    const addPitchBtn = card.querySelector('[data-add-pitch]');
                    const addBenchBtn = card.querySelector('[data-add-bench]');
                    const removeBtn = card.querySelector('[data-remove-player]');

                    const assigned = !!selectedIn;
                    card.classList.toggle('sim-player-card-active', assigned);
                    if (pickedMarker) {
                        pickedMarker.classList.toggle('hidden', !assigned);
                    }
                    if (addPitchBtn) {
                        addPitchBtn.classList.toggle('hidden', assigned);
                    }
                    if (addBenchBtn) {
                        addBenchBtn.classList.toggle('hidden', assigned);
                    }
                    if (removeBtn) {
                        removeBtn.classList.toggle('hidden', !assigned);
                    }
                });
            }

            function syncAll() {
                slotContainers.forEach(syncSlotView);
                syncPlayerCards();
            }

            function triggerChange(select) {
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function assignPlayerToSelect(select, playerId) {
                if (!select) {
                    return false;
                }

                const playerCard = findPlayerCard(playerId);
                if (!playerCard) {
                    return false;
                }

                const currentSelect = findSelectWithPlayer(playerId);
                const targetCurrent = select.value ? String(select.value) : '';

                if (currentSelect && currentSelect !== select) {
                    if (targetCurrent) {
                        currentSelect.value = targetCurrent;
                    } else {
                        currentSelect.value = '';
                    }
                    triggerChange(currentSelect);
                }

                select.value = String(playerId);
                triggerChange(select);
                return true;
            }

            function clearPlayerEverywhere(playerId) {
                const select = findSelectWithPlayer(playerId);
                if (!select) {
                    return;
                }
                select.value = '';
                triggerChange(select);
            }

            function clearContainer(container) {
                const select = getSelectForContainer(container);
                if (!select) {
                    return;
                }
                select.value = '';
                triggerChange(select);
            }

            function findFirstEmpty(selects) {
                return selects.find(function (select) { return !select.value; }) || null;
            }

            function findBestStarterSelect(playerCard) {
                const positionMain = String(playerCard.dataset.positionMain || '').toUpperCase();
                const positionSecond = String(playerCard.dataset.positionSecond || '').toUpperCase();
                const positionThird = String(playerCard.dataset.positionThird || '').toUpperCase();

                function byRole(role) {
                    return starterSelects.find(function (select) {
                        return !select.value && String(select.dataset.slotRole || '').toUpperCase() === role;
                    }) || null;
                }

                function byGroup(position) {
                    const group = groupFromPosition(position);
                    if (!group) {
                        return null;
                    }

                    return starterSelects.find(function (select) {
                        return !select.value && String(select.dataset.slotGroup || '') === group;
                    }) || null;
                }

                return byRole(positionMain)
                    || byRole(positionSecond)
                    || byRole(positionThird)
                    || byGroup(positionMain)
                    || byGroup(positionSecond)
                    || byGroup(positionThird)
                    || findFirstEmpty(starterSelects);
            }

            playerCards.forEach(function (card) {
                card.addEventListener('dragstart', function (event) {
                    event.dataTransfer.setData('text/plain', String(card.dataset.playerId));
                    event.dataTransfer.effectAllowed = 'move';
                    card.classList.add('sim-player-card-dragging');
                });

                card.addEventListener('dragend', function () {
                    card.classList.remove('sim-player-card-dragging');
                });

                card.addEventListener('dblclick', function () {
                    const target = findBestStarterSelect(card);
                    if (!target) {
                        return;
                    }

                    assignPlayerToSelect(target, card.dataset.playerId);
                });

                const addPitchBtn = card.querySelector('[data-add-pitch]');
                const addBenchBtn = card.querySelector('[data-add-bench]');
                const removeBtn = card.querySelector('[data-remove-player]');

                if (addPitchBtn) {
                    addPitchBtn.addEventListener('click', function () {
                        const target = findBestStarterSelect(card);
                        if (!target) {
                            return;
                        }
                        assignPlayerToSelect(target, card.dataset.playerId);
                    });
                }

                if (addBenchBtn) {
                    addBenchBtn.addEventListener('click', function () {
                        const target = findFirstEmpty(benchSelects);
                        if (!target) {
                            return;
                        }
                        assignPlayerToSelect(target, card.dataset.playerId);
                    });
                }

                if (removeBtn) {
                    removeBtn.addEventListener('click', function () {
                        clearPlayerEverywhere(card.dataset.playerId);
                    });
                }
            });

            slotContainers.forEach(function (container) {
                const select = getSelectForContainer(container);
                const removeBtn = container.querySelector('[data-slot-remove]');

                container.addEventListener('dragover', function (event) {
                    event.preventDefault();
                    container.classList.add('sim-slot-drop-hover');
                });

                container.addEventListener('dragleave', function () {
                    container.classList.remove('sim-slot-drop-hover');
                });

                container.addEventListener('drop', function (event) {
                    event.preventDefault();
                    container.classList.remove('sim-slot-drop-hover');
                    const playerId = event.dataTransfer.getData('text/plain');
                    if (!playerId || !playerCardMap.has(String(playerId))) {
                        return;
                    }
                    assignPlayerToSelect(select, playerId);
                });

                if (removeBtn) {
                    removeBtn.addEventListener('click', function () {
                        clearContainer(container);
                    });
                }
            });

            managedSelects.forEach(function (select) {
                const container = slotContainers.find(function (candidate) {
                    return candidate.dataset.selectId === select.id;
                });

                if (container) {
                    select.dataset.slotGroup = container.dataset.slotGroup || '';
                    select.dataset.slotRole = container.dataset.slotRole || '';
                }

                select.addEventListener('change', syncAll);
            });

            syncAll();
        })();
    </script>
</x-app-layout>
