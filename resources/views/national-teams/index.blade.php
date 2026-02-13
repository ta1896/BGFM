<x-app-layout>
    <x-slot name="header">
        <div class="sim-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="sim-section-title">Welle 3</p>
                    <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Nationalteams</h1>
                    <p class="mt-2 text-sm text-slate-300">
                        Bestenliste pro Land mit aktuellem Kader und Rollenverteilung.
                    </p>
                </div>
                @if ($teams->isNotEmpty())
                    <form method="GET" action="{{ route('national-teams.index') }}" class="flex items-center gap-2">
                        <label for="team" class="sim-label mb-0">Nationalteam</label>
                        <select id="team" name="team" class="sim-select w-64" onchange="this.form.submit()">
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected($activeTeam?->id === $team->id)>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    @if (!$activeTeam)
        <section class="sim-card p-6">
            <p class="text-sm text-slate-300">Noch keine Nationalteams vorhanden.</p>
        </section>
    @else
        <section class="grid gap-4 xl:grid-cols-3">
            <article class="sim-card p-5 xl:col-span-2">
                <p class="sim-section-title">Uebersicht</p>
                <h2 class="mt-3 text-2xl font-bold text-white">{{ $activeTeam->name }}</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="sim-pill">{{ $activeTeam->country->name }}</span>
                    <span class="sim-pill">Reputation {{ $activeTeam->reputation }}</span>
                    <span class="sim-pill">Stil {{ ucfirst($activeTeam->tactical_style) }}</span>
                </div>
                @if ($activeTeam->manager)
                    <p class="mt-4 text-sm text-slate-300">
                        Manager: {{ $activeTeam->manager->name }}
                    </p>
                @endif
            </article>

            <article class="sim-card p-5">
                <p class="sim-section-title">Kaderaktion</p>
                <p class="mt-3 text-sm text-slate-300">
                    Stellt die besten Spieler nach Gesamtstaerke neu zusammen.
                </p>
                @if (auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('national-teams.refresh', $activeTeam) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="sim-btn-primary w-full">Kader neu berechnen</button>
                    </form>
                @else
                    <p class="mt-4 text-xs text-slate-400">Nur Admin kann den Kader aktualisieren.</p>
                @endif
            </article>
        </section>

        <section class="sim-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-800/80 px-5 py-4">
                <h3 class="text-lg font-semibold text-white">Aktueller Nationalkader</h3>
                <span class="sim-pill">{{ $squad->count() }} Spieler</span>
            </div>
            <div class="overflow-x-auto">
                <table class="sim-table min-w-full">
                    <thead>
                        <tr>
                            <th>Rolle</th>
                            <th>Spieler</th>
                            <th>Verein</th>
                            <th>Position</th>
                            <th>Overall</th>
                            <th>Moral</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($squad as $callup)
                            <tr>
                                <td class="uppercase">{{ $callup->role }}</td>
                                <td class="font-semibold">
                                    @if ($callup->player)
                                        <span class="inline-flex items-center gap-2">
                                            <img class="sim-avatar sim-avatar-xs" src="{{ $callup->player->photo_url }}" alt="{{ $callup->player->full_name }}">
                                            {{ $callup->player->full_name }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($callup->player?->club)
                                        <span class="inline-flex items-center gap-2">
                                            <img class="sim-avatar sim-avatar-xs" src="{{ $callup->player->club->logo_url }}" alt="{{ $callup->player->club->name }}">
                                            {{ $callup->player->club->name }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $callup->player?->position ?? '-' }}</td>
                                <td>{{ $callup->player?->overall ?? '-' }}</td>
                                <td>{{ $callup->player?->morale ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-sm text-slate-300">
                                    Noch keine aktiven Berufungen vorhanden.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-app-layout>
