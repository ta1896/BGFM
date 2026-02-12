@php
    $playersByPosition = $clubPlayers->groupBy('position');
    $selectedPlayerIds = collect($starterDraft)->filter()->values()->concat(collect($benchDraft)->filter())->unique()->all();
    $positionLabels = [
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
                    <h1 class="mt-1 text-2xl font-bold text-white">{{ $club->name }} vs {{ $opponentClub->name }}</h1>
                    <p class="mt-1 text-sm text-slate-300">
                        {{ $match->kickoff_at?->format('d.m.Y H:i') }} Uhr | {{ $match->type === 'friendly' ? 'Freundschaft' : 'Pflichtspiel' }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
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
                    <p class="sim-section-title mb-2">Auswechselbank</p>
                    <div class="grid gap-2 sm:grid-cols-5">
                        @for ($i = 0; $i < 5; $i++)
                            <select name="bench_slots[]" class="sim-select">
                                <option value="">Slot {{ $i + 1 }}</option>
                                @foreach ($clubPlayers as $player)
                                    <option value="{{ $player->id }}" @selected((int) ($benchDraft[$i] ?? 0) === $player->id)>
                                        {{ $player->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        @endfor
                    </div>
                </div>

                <div class="mt-6 sim-pitch">
                    <div class="sim-pitch-canvas">
                        @foreach ($slots as $slot)
                            <div class="sim-pitch-slot" style="left: {{ $slot['x'] }}%; top: {{ $slot['y'] }}%;">
                                <span class="sim-pitch-slot-label">{{ $slot['label'] }}</span>
                                <select name="starter_slots[{{ $slot['slot'] }}]" class="sim-pitch-select">
                                    <option value="">- Kein Spieler -</option>
                                    @foreach ($clubPlayers as $player)
                                        <option value="{{ $player->id }}" @selected((int) ($starterDraft[$slot['slot']] ?? 0) === $player->id)>
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
                    @foreach (['GK' => 'Torwart', 'DEF' => 'Abwehr', 'MID' => 'Mittelfeld', 'FWD' => 'Sturm'] as $code => $label)
                        <div>
                            <h3 class="text-sm font-semibold text-white">{{ $label }}</h3>
                            <div class="mt-2 space-y-2">
                                @forelse ($playersByPosition->get($code, collect()) as $player)
                                    <div class="sim-card-soft px-3 py-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-semibold text-white">{{ $player->full_name }}</p>
                                            <span class="sim-pill">OVR {{ $player->overall }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-400">
                                            <span class="sim-pill !px-2 !py-0.5 text-[10px]">{{ $positionLabels[$player->position] ?? $player->position }}</span>
                                            <span class="ml-1">{{ $player->age }} J.</span>
                                            <span class="ml-1">| {{ number_format((float) $player->market_value, 0, ',', '.') }} EUR</span>
                                            @if (in_array($player->id, $selectedPlayerIds, true))
                                                <span class="ml-1">| Aufgestellt</span>
                                            @endif
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
</x-app-layout>
