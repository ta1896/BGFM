<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Training</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Trainingsplan und Ausfuehrung</h1>
        </div>
    </x-slot>

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
                                    <p class="font-semibold text-white">{{ $session->club->name }} | {{ ucfirst($session->type) }}</p>
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
                            <option value="{{ $club->id }}">{{ $club->name }}</option>
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
                    <input id="session_date" name="session_date" type="date" value="{{ now()->toDateString() }}" class="sim-input" required>
                </div>
                <div>
                    <label class="sim-label" for="player_ids">Spieler</label>
                    <select id="player_ids" name="player_ids[]" class="sim-select min-h-32" multiple required>
                        @foreach ($clubs as $club)
                            <optgroup label="{{ $club->name }}">
                                @foreach ($club->players as $player)
                                    <option value="{{ $player->id }}">
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
</x-app-layout>
