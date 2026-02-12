<x-app-layout>
    <x-slot name="header">
        <div class="sim-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="sim-section-title">Welle 3</p>
                    <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Random Events</h1>
                    <p class="mt-2 text-sm text-slate-300">
                        Dynamische Ereignisse fuer Budget, Stimmung und Spielerwerte.
                    </p>
                </div>
                @if ($clubs->isNotEmpty())
                    <form method="GET" action="{{ route('random-events.index') }}" class="flex items-center gap-2">
                        <label for="club" class="sim-label mb-0">Verein</label>
                        <select id="club" name="club" class="sim-select w-56" onchange="this.form.submit()">
                            @foreach ($clubs as $club)
                                <option value="{{ $club->id }}" @selected($activeClub?->id === $club->id)>
                                    {{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    @if (!$activeClub)
        <section class="sim-card p-6">
            <p class="text-sm text-slate-300">Kein Verein verfuegbar.</p>
        </section>
    @else
        <section class="grid gap-4 xl:grid-cols-3">
            <article class="sim-card p-5 xl:col-span-2">
                <p class="sim-section-title">Ereignisse ausloesen</p>
                <p class="mt-3 text-sm text-slate-300">
                    Erzeugt ein neues Ereignis auf Basis von Wahrscheinlichkeit und Vereinsreputation.
                </p>
                <form method="POST" action="{{ route('random-events.trigger') }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="club_id" value="{{ $activeClub->id }}" />
                    <button type="submit" class="sim-btn-primary">Neues Event erzeugen</button>
                </form>
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Aktive Vorlagen</p>
                <div class="mt-3 space-y-2">
                    @forelse ($templates as $template)
                        <div class="sim-card-soft px-3 py-2">
                            <p class="text-sm font-semibold text-white">{{ $template->name }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.12em] text-slate-400">
                                {{ $template->category }} | {{ $template->rarity }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-300">Keine Vorlagen fuer diesen Verein verfuegbar.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="sim-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-800/80 px-5 py-4">
                <h2 class="text-lg font-semibold text-white">Event-Historie</h2>
                <span class="sim-pill">{{ $occurrences->count() }} Eintraege</span>
            </div>
            <div class="overflow-x-auto">
                <table class="sim-table min-w-full">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Titel</th>
                            <th>Spieler</th>
                            <th>Status</th>
                            <th>Auswirkung</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($occurrences as $occurrence)
                            <tr>
                                <td>{{ $occurrence->happened_on->format('d.m.Y') }}</td>
                                <td>
                                    <p class="font-semibold text-white">{{ $occurrence->title }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $occurrence->message }}</p>
                                </td>
                                <td>{{ $occurrence->player?->full_name ?? '-' }}</td>
                                <td class="uppercase">{{ $occurrence->status }}</td>
                                <td>
                                    {{ number_format((int) data_get($occurrence->effect_payload, 'budget_delta', 0), 0, ',', '.') }} EUR
                                </td>
                                <td>
                                    @if ($occurrence->status === 'pending')
                                        <form method="POST" action="{{ route('random-events.apply', $occurrence) }}">
                                            @csrf
                                            <button type="submit" class="sim-btn-muted">Anwenden</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-sm text-slate-300">
                                    Noch keine Ereignisse vorhanden.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-app-layout>
