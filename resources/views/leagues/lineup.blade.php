@php
    $positionService = app(\App\Services\PlayerPositionService::class);
    $playersByPosition = $clubPlayers->groupBy(fn ($player) => $positionService->groupFromPosition($player->position_main ?? $player->position) ?? 'MID');
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
                    <div class="mt-1 flex items-center gap-2 text-2xl font-bold text-white">
                        <img class="sim-avatar sim-avatar-sm" src="{{ $club->logo_url }}" alt="{{ $club->name }}">
                        <span>{{ $club->name }}</span>
                        <span class="text-slate-400">vs</span>
                        <img class="sim-avatar sim-avatar-sm" src="{{ $opponentClub->logo_url }}" alt="{{ $opponentClub->name }}">
                        <span>{{ $opponentClub->name }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-300">
                        {{ $match->kickoff_at?->format('d.m.Y H:i') }} Uhr | {{ $match->type === 'friendly' ? 'Freundschaft' : 'Pflichtspiel' }}
                    </p>
                </div>
                <div class="flex flex-wrap items-end gap-2">
                    <div>
                        <label class="sim-label mb-1">Match waehlen</label>
                        <select id="matchSwitch" class="sim-select w-72">
                            @foreach ($clubMatches as $clubMatch)
                                @php
                                    $isHome = (int) $clubMatch->home_club_id === (int) $club->id;
                                    $opponent = $isHome ? $clubMatch->awayClub : $clubMatch->homeClub;
                                    $statusLabel = $clubMatch->status === 'live' ? 'LIVE' : strtoupper($clubMatch->status);
                                @endphp
                                <option
                                    value="{{ route('matches.lineup.edit', ['match' => $clubMatch->id, 'club' => $club->id]) }}"
                                    @selected((int) $clubMatch->id === (int) $match->id)
                                >
                                    {{ $clubMatch->kickoff_at?->format('d.m H:i') }} | vs {{ $opponent?->name ?? 'Unbekannt' }} | {{ $statusLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <a href="{{ route('matches.show', $match) }}" class="sim-btn-muted">Zum Matchcenter</a>
                    <a href="{{ route('league.matches') }}" class="sim-btn-muted">Spiele</a>
                </div>
            </div>
        </div>
    </x-slot>

    <section class="sim-card p-5">
        <div class="grid gap-3 lg:grid-cols-[1fr_auto]">
            <div class="flex flex-wrap items-end gap-2">
                <form method="POST" action="{{ route('matches.lineup.load-template', ['match' => $match->id, 'club' => $club->id]) }}" class="flex items-end gap-2">
                    @csrf
                    <input type="hidden" name="club_id" value="{{ $club->id }}">
                    <div>
                        <label class="sim-label mb-1">Vorlagen</label>
                        <select name="template_id" class="sim-select w-56" required>
                            <option value="">Vorlage waehlen</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }} ({{ $template->formation }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="sim-btn-muted">Laden</button>
                </form>
            </div>
            <div class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="sim-label mb-1" for="template_name">Neuer Vorlagenname</label>
                    <input id="template_name" name="template_name" type="text" class="sim-input w-56" placeholder="z. B. 4-4-2 Heimspiel" form="matchLineupForm">
                </div>
                <button type="submit" name="action" value="save_template" class="sim-btn-primary" form="matchLineupForm">Als Vorlage speichern</button>
            </div>
        </div>

        @if ($templates->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($templates as $template)
                    <form method="POST" action="{{ route('matches.lineup.template.destroy', ['match' => $match->id, 'template' => $template->id, 'club' => $club->id]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="sim-btn-danger !px-3 !py-1.5 text-xs" onclick="return confirm('Vorlage wirklich loeschen?')">
                            {{ $template->name }} loeschen
                        </button>
                    </form>
                @endforeach
            </div>
        @endif
    </section>

    <form id="matchLineupForm" method="POST" action="{{ route('matches.lineup.update', ['match' => $match->id, 'club' => $club->id]) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="club_id" value="{{ $club->id }}">

        <section class="grid gap-4 xl:grid-cols-[2fr_1fr]">
            <article class="sim-card p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="sim-pill">Gesamtstaerke: {{ $metrics['overall'] }}</span>
                        <span class="sim-pill">Formation: {{ $formation }}</span>
                    </div>
                    <button
                        type="submit"
                        formaction="{{ route('matches.lineup.auto-pick', ['match' => $match->id, 'club' => $club->id]) }}"
                        formmethod="POST"
                        class="sim-btn-muted"
                    >
                        Staerkste Elf waehlen
                    </button>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <div>
                        <label class="sim-label" for="formation">Formation</label>
                        <select id="formation" name="formation" class="sim-select">
                            @foreach ($formations as $formationOption)
                                <option value="{{ $formationOption }}" @selected($formationOption === $formation)>{{ $formationOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sim-label" for="tactical_style">Spielstil</label>
                        <select id="tactical_style" name="tactical_style" class="sim-select">
                            @foreach (['balanced' => 'Ausgewogen', 'offensive' => 'Offensiv', 'defensive' => 'Defensiv', 'counter' => 'Konter'] as $value => $label)
                                <option value="{{ $value }}" @selected($value === $tacticalStyle)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sim-label" for="attack_focus">Fokus</label>
                        <select id="attack_focus" name="attack_focus" class="sim-select">
                            @foreach (['left' => 'Linke Seite', 'center' => 'Zentrum', 'right' => 'Rechte Seite'] as $value => $label)
                                <option value="{{ $value }}" @selected($value === $attackFocus)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="sim-label">Elfmeter-Schuetze</label>
                        <select name="penalty_taker_player_id" class="sim-select">
                            <option value="">Kein Spieler</option>
                            @foreach ($clubPlayers as $player)
                                <option value="{{ $player->id }}" @selected((int) $setPieces['penalty_taker_player_id'] === $player->id)>
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
                                <option value="{{ $player->id }}" @selected((int) $setPieces['free_kick_taker_player_id'] === $player->id)>
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
                                <option value="{{ $player->id }}" @selected((int) $setPieces['corner_left_taker_player_id'] === $player->id)>
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
                                <option value="{{ $player->id }}" @selected((int) $setPieces['corner_right_taker_player_id'] === $player->id)>
                                    {{ $player->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="sim-label">Kapitän</label>
                    <select name="captain_player_id" class="sim-select max-w-sm">
                        <option value="">Automatisch</option>
                        @foreach ($clubPlayers as $player)
                            <option value="{{ $player->id }}" @selected((int) $captainPlayerId === $player->id)>
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
                            <div class="space-y-1">
                                <div class="sim-dropzone" data-drop-select="{{ $benchSelectId }}" data-empty-label="Slot {{ $i + 1 }}: - Kein Spieler -">
                                    <span class="sim-dropzone-label" data-drop-label>Slot {{ $i + 1 }}: - Kein Spieler -</span>
                                    <button type="button" class="sim-dropzone-clear hidden" data-drop-clear title="Slot leeren">x</button>
                                </div>
                                <select id="{{ $benchSelectId }}" name="bench_slots[]" class="sim-select" data-dnd-select>
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
                            <div class="sim-pitch-slot" style="left: {{ $slot['x'] }}%; top: {{ $slot['y'] }}%;">
                                <span class="sim-pitch-slot-label">{{ $slot['label'] }}</span>
                                <div class="sim-dropzone mb-1" data-drop-select="{{ $slotSelectId }}" data-empty-label="- Kein Spieler -">
                                    <span class="sim-dropzone-label" data-drop-label>- Kein Spieler -</span>
                                    <button type="button" class="sim-dropzone-clear hidden" data-drop-clear title="Slot leeren">x</button>
                                </div>
                                <select id="{{ $slotSelectId }}" name="starter_slots[{{ $slot['slot'] }}]" class="sim-pitch-select" data-dnd-select>
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
                    <button type="submit" name="action" value="save_match" class="sim-btn-primary">Aufstellung speichern</button>
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

    <script>
        (function () {
            const matchSwitch = document.getElementById('matchSwitch');
            const formationSelect = document.getElementById('formation');

            if (matchSwitch) {
                matchSwitch.addEventListener('change', function () {
                    if (this.value) {
                        window.location.href = this.value;
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
            const dropzones = Array.from(document.querySelectorAll('[data-drop-select]'));
            const managedSelects = Array.from(document.querySelectorAll('[data-dnd-select]'));
            const playerMap = new Map(playerCards.map(function (card) {
                return [String(card.dataset.playerId), card];
            }));

            function selectedPlayerIds() {
                return managedSelects
                    .map(function (select) { return select.value; })
                    .filter(function (value) { return value !== ''; });
            }

            function syncPoolHighlights() {
                const selected = new Set(selectedPlayerIds());
                playerCards.forEach(function (card) {
                    const pickedMarker = card.querySelector('[data-player-picked]');
                    const isPicked = selected.has(String(card.dataset.playerId));
                    card.classList.toggle('sim-player-card-active', isPicked);
                    if (pickedMarker) {
                        pickedMarker.classList.toggle('hidden', !isPicked);
                    }
                });
            }

            function syncDropzone(dropzone) {
                const selectId = dropzone.dataset.dropSelect;
                const select = document.getElementById(selectId);
                const label = dropzone.querySelector('[data-drop-label]');
                const clearBtn = dropzone.querySelector('[data-drop-clear]');
                if (!select || !label) {
                    return;
                }

                if (select.value) {
                    const option = select.options[select.selectedIndex];
                    label.textContent = option ? option.text : 'Spieler gesetzt';
                    dropzone.classList.add('sim-dropzone-filled');
                    if (clearBtn) {
                        clearBtn.classList.remove('hidden');
                    }
                } else {
                    label.textContent = dropzone.dataset.emptyLabel || '- Kein Spieler -';
                    dropzone.classList.remove('sim-dropzone-filled');
                    if (clearBtn) {
                        clearBtn.classList.add('hidden');
                    }
                }
            }

            function syncAllDropzones() {
                dropzones.forEach(syncDropzone);
                syncPoolHighlights();
            }

            function assignPlayerToSelect(select, playerId) {
                if (!select) {
                    return;
                }

                managedSelects.forEach(function (current) {
                    if (current !== select && current.value === String(playerId)) {
                        current.value = '';
                        syncDropzone(document.querySelector('[data-drop-select="' + current.id + '"]'));
                    }
                });

                select.value = String(playerId);
                select.dispatchEvent(new Event('change', { bubbles: true }));
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
            });

            dropzones.forEach(function (dropzone) {
                const select = document.getElementById(dropzone.dataset.dropSelect);
                const clearBtn = dropzone.querySelector('[data-drop-clear]');

                dropzone.addEventListener('dragover', function (event) {
                    event.preventDefault();
                    dropzone.classList.add('sim-dropzone-over');
                });

                dropzone.addEventListener('dragleave', function () {
                    dropzone.classList.remove('sim-dropzone-over');
                });

                dropzone.addEventListener('drop', function (event) {
                    event.preventDefault();
                    dropzone.classList.remove('sim-dropzone-over');
                    const playerId = event.dataTransfer.getData('text/plain');
                    if (!playerId || !playerMap.has(String(playerId))) {
                        return;
                    }

                    assignPlayerToSelect(select, playerId);
                    syncAllDropzones();
                });

                if (clearBtn) {
                    clearBtn.addEventListener('click', function () {
                        if (!select) {
                            return;
                        }

                        select.value = '';
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        syncAllDropzones();
                    });
                }
            });

            managedSelects.forEach(function (select) {
                select.addEventListener('change', syncAllDropzones);
            });

            syncAllDropzones();
        })();
    </script>
</x-app-layout>
