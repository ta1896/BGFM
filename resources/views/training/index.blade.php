<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Training</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Trainingsplan und Ausfuehrung</h1>
        </div>
    </x-slot>

    @php
        $selectedClubFilter = (int) ($filters['club'] ?? 0);
        $prefillClubId = (int) old('club_id', $selectedClubFilter ?: ($clubs->first()->id ?? 0));
        $prefillDate = old('session_date', $filters['date'] ?? now()->toDateString());
    @endphp

    <section class="sim-card p-4">
        <form method="GET" action="{{ route('training.index') }}" class="grid gap-3 md:grid-cols-5">
            <div>
                <label class="sim-label" for="trainingClubFilter">Verein</label>
                <select id="trainingClubFilter" name="club" class="sim-select">
                    <option value="">Alle Vereine</option>
                    @foreach ($clubs as $club)
                        <option value="{{ $club->id }}" @selected((int) ($filters['club'] ?? 0) === (int) $club->id)>
                            {{ $club->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sim-label" for="trainingDateFilter">Tag</label>
                <input id="trainingDateFilter" type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="sim-input">
            </div>
            <div>
                <label class="sim-label" for="trainingFromFilter">Von</label>
                <input id="trainingFromFilter" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="sim-input">
            </div>
            <div>
                <label class="sim-label" for="trainingToFilter">Bis</label>
                <input id="trainingToFilter" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="sim-input">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="sim-btn-primary">Filtern</button>
                <a href="{{ route('training.index') }}" class="sim-btn-muted">Reset</a>
            </div>
        </form>
        <div class="mt-3 flex flex-wrap gap-2">
            <a href="{{ route('training.index', array_filter(['club' => $filters['club'] ?? null, 'range' => 'today'])) }}" class="sim-btn-muted {{ ($filters['range'] ?? '') === 'today' ? '!border-cyan-300/60 !bg-cyan-500/15' : '' }}">Heute</a>
            <a href="{{ route('training.index', array_filter(['club' => $filters['club'] ?? null, 'range' => 'week'])) }}" class="sim-btn-muted {{ ($filters['range'] ?? '') === 'week' ? '!border-cyan-300/60 !bg-cyan-500/15' : '' }}">Diese Woche</a>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-3">
        <article class="sim-card p-5 xl:col-span-2">
            <h2 class="text-lg font-semibold text-white">Trainings-Sessions</h2>
            @if ($sessions->isEmpty())
                <p class="mt-4 text-sm text-slate-300">Noch keine Sessions angelegt.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($sessions as $session)
                        <div class="sim-card-soft p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="flex items-center gap-2 font-semibold text-white">
                                        <img class="sim-avatar sim-avatar-xs" src="{{ $session->club->logo_url }}" alt="{{ $session->club->name }}">
                                        <span>{{ $session->club->name }} | {{ ucfirst($session->type) }}</span>
                                    </p>
                                    <p class="text-sm text-slate-300">
                                        {{ $session->session_date?->format('d.m.Y') }} | Intensitaet {{ ucfirst($session->intensity) }}
                                    </p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span class="sim-pill">Moral {{ $session->morale_effect >= 0 ? '+' : '' }}{{ $session->morale_effect }}</span>
                                        <span class="sim-pill">Ausdauer {{ $session->stamina_effect >= 0 ? '+' : '' }}{{ $session->stamina_effect }}</span>
                                        <span class="sim-pill">Form {{ $session->form_effect >= 0 ? '+' : '' }}{{ $session->form_effect }}</span>
                                        <span class="sim-pill">{{ $session->is_applied ? 'Berechnet' : 'Ausstehend' }}</span>
                                    </div>
                                </div>
                                @if (!$session->is_applied)
                                    <form method="POST" action="{{ route('training.apply', $session) }}">
                                        @csrf
                                        <button type="submit" class="sim-btn-primary">Effekt anwenden</button>
                                    </form>
                                @endif
                            </div>
                            @if ($session->players->isNotEmpty())
                                <div class="mt-3 text-xs text-slate-400">
                                    Spieler: {{ $session->players->take(8)->pluck('full_name')->join(', ') }}{{ $session->players->count() > 8 ? ' ...' : '' }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $sessions->links() }}</div>
            @endif
        </article>

        <article class="sim-card p-5">
            <h2 class="text-lg font-semibold text-white">Neue Session</h2>
            <form method="POST" action="{{ route('training.store') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="sim-label" for="club_id">Verein</label>
                    <select id="club_id" name="club_id" class="sim-select" required>
                        <option value="">Auswaehlen</option>
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}" @selected($prefillClubId === (int) $club->id)>{{ $club->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sim-label" for="type">Typ</label>
                    <select id="type" name="type" class="sim-select" required>
                        <option value="fitness">Fitness</option>
                        <option value="tactics">Taktik</option>
                        <option value="technical">Technik</option>
                        <option value="recovery">Regeneration</option>
                        <option value="friendly">Testspiel</option>
                    </select>
                </div>
                <div>
                    <label class="sim-label" for="intensity">Intensitaet</label>
                    <select id="intensity" name="intensity" class="sim-select" required>
                        <option value="low">Niedrig</option>
                        <option value="medium" selected>Mittel</option>
                        <option value="high">Hoch</option>
                    </select>
                </div>
                <div>
                    <label class="sim-label" for="focus_position">Fokus Position</label>
                    <select id="focus_position" name="focus_position" class="sim-select">
                        <option value="">Alle</option>
                        <option value="GK">Torwart</option>
                        <option value="DEF">Abwehr</option>
                        <option value="MID">Mittelfeld</option>
                        <option value="FWD">Sturm</option>
                    </select>
                </div>
                <div>
                    <label class="sim-label" for="session_date">Datum</label>
                    <input id="session_date" name="session_date" type="date" value="{{ $prefillDate }}" class="sim-input" required>
                </div>
                <div>
                    <label class="sim-label" for="player_ids">Spieler</label>
                    <select id="player_ids" name="player_ids[]" class="sim-select min-h-32" multiple required>
                        @foreach ($clubs as $club)
                            <optgroup label="{{ $club->name }}" data-club-group="{{ $club->id }}">
                                @foreach ($club->players as $player)
                                    <option value="{{ $player->id }}" data-club-id="{{ $club->id }}">
                                        {{ $player->full_name }} ({{ $player->position }} | OVR {{ $player->overall }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sim-label" for="notes">Notizen</label>
                    <textarea id="notes" name="notes" class="sim-textarea" placeholder="Schwerpunkt der Einheit"></textarea>
                </div>
                <button type="submit" class="sim-btn-primary w-full">Session erstellen</button>
            </form>
        </article>
    </section>

    <script>
        (function () {
            const clubSelect = document.getElementById('club_id');
            const playerSelect = document.getElementById('player_ids');
            if (!clubSelect || !playerSelect) {
                return;
            }

            const playerOptions = Array.from(playerSelect.querySelectorAll('option[data-club-id]'));
            const playerGroups = Array.from(playerSelect.querySelectorAll('optgroup[data-club-group]'));

            function syncPlayerOptions() {
                const selectedClub = String(clubSelect.value || '');

                playerGroups.forEach(function (group) {
                    const showGroup = !selectedClub || String(group.dataset.clubGroup) === selectedClub;
                    group.hidden = !showGroup;
                });

                playerOptions.forEach(function (option) {
                    const showOption = !selectedClub || String(option.dataset.clubId) === selectedClub;
                    option.disabled = !showOption;
                    if (!showOption) {
                        option.selected = false;
                    }
                });
            }

            clubSelect.addEventListener('change', syncPlayerOptions);
            syncPlayerOptions();
        })();
    </script>
</x-app-layout>
