<x-app-layout>
    <x-slot name="header">
        <div class="sim-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="sim-section-title">Welle 3</p>
                    <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Team of the Day</h1>
                    <p class="mt-2 text-sm text-slate-300">
                        Beste Einzelperformances pro Spieltag-Kontext oder Datum.
                    </p>
                </div>
                @if (auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('team-of-the-day.generate') }}" class="flex flex-wrap items-end gap-2">
                        @csrf
                        <div>
                            <label for="for_date" class="sim-label mb-1">Datum</label>
                            <input id="for_date" name="for_date" type="date" class="sim-input w-40" />
                        </div>
                        <div>
                            <label for="competition_season_id" class="sim-label mb-1">Liga/Saison</label>
                            <select id="competition_season_id" name="competition_season_id" class="sim-select w-56">
                                <option value="">- Kein Kontext -</option>
                                @foreach ($competitionSeasons as $competitionSeason)
                                    <option value="{{ $competitionSeason->id }}">
                                        {{ $competitionSeason->competition->short_name ?? $competitionSeason->competition->name }}
                                        | {{ $competitionSeason->season->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="matchday" class="sim-label mb-1">Spieltag</label>
                            <input id="matchday" name="matchday" type="number" min="1" max="99" class="sim-input w-28" />
                        </div>
                        <button type="submit" class="sim-btn-primary">Neu erzeugen</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <section class="grid gap-4 xl:grid-cols-3">
        <article class="sim-card p-5">
            <p class="sim-section-title">Historie</p>
            <div class="mt-4 space-y-2">
                @forelse ($teams as $team)
                    <a
                        href="{{ route('team-of-the-day.index', ['totd' => $team->id]) }}"
                        class="sim-card-soft block px-3 py-3 {{ $activeTeam?->id === $team->id ? 'border-cyan-400/40' : '' }}"
                    >
                        <p class="text-sm font-semibold text-white">{{ $team->for_date->format('d.m.Y') }}</p>
                        <p class="mt-1 text-xs text-slate-300">{{ $team->players_count }} Spieler | {{ $team->formation }} | {{ $team->generation_context }}</p>
                        @if ($team->competitionSeason)
                            <p class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                                <img class="sim-avatar sim-avatar-xs" src="{{ $team->competitionSeason->competition->logo_url }}" alt="{{ $team->competitionSeason->competition->name }}">
                                <span>{{ $team->competitionSeason->competition->short_name ?? $team->competitionSeason->competition->name }} ST {{ $team->matchday }}</span>
                            </p>
                        @endif
                    </a>
                @empty
                    <p class="text-sm text-slate-300">Noch keine Team-of-the-Day-Eintraege.</p>
                @endforelse
            </div>
        </article>

        <article class="sim-card xl:col-span-2">
            @if (!$activeTeam)
                <div class="p-5">
                    <p class="text-sm text-slate-300">Kein Datensatz ausgewaehlt.</p>
                </div>
            @else
                <div class="flex items-center justify-between border-b border-slate-800/80 px-5 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ $activeTeam->label }}</h2>
                        <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-400">
                            {{ $activeTeam->for_date->format('d.m.Y') }} | {{ $activeTeam->generation_context }}
                        </p>
                        @if ($activeTeam->competitionSeason)
                            <p class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                                <img class="sim-avatar sim-avatar-xs" src="{{ $activeTeam->competitionSeason->competition->logo_url }}" alt="{{ $activeTeam->competitionSeason->competition->name }}">
                                <span>
                                Kontext:
                                {{ $activeTeam->competitionSeason->competition->name }}
                                ({{ $activeTeam->competitionSeason->season->name }}) - Spieltag {{ $activeTeam->matchday }}
                                </span>
                            </p>
                        @endif
                    </div>
                    <span class="sim-pill">{{ $activeTeam->formation }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="sim-table min-w-full">
                        <thead>
                            <tr>
                                <th>Slot</th>
                                <th>Spieler</th>
                                <th>Verein</th>
                                <th>Position</th>
                                <th>Rating</th>
                                <th>G/A</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($entries as $entry)
                                <tr>
                                    <td>{{ $entry->position_code }}</td>
                                    <td class="font-semibold">
                                        @if ($entry->player)
                                            <span class="inline-flex items-center gap-2">
                                                <img class="sim-avatar sim-avatar-xs" src="{{ $entry->player->photo_url }}" alt="{{ $entry->player->full_name }}">
                                                {{ $entry->player->full_name }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($entry->club)
                                            <span class="inline-flex items-center gap-2">
                                                <img class="sim-avatar sim-avatar-xs" src="{{ $entry->club->logo_url }}" alt="{{ $entry->club->name }}">
                                                {{ $entry->club->name }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $entry->player?->position ?? '-' }}</td>
                                    <td>{{ $entry->rating ?? '-' }}</td>
                                    <td>
                                        {{ data_get($entry->stats_snapshot, 'goals', 0) }}/{{ data_get($entry->stats_snapshot, 'assists', 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-sm text-slate-300">
                                        Keine Spieler fuer dieses Datum gefunden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </article>
    </section>
</x-app-layout>
