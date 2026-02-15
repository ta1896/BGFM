<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">Vertraege</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Vertragsmanagement</h1>
            </div>
            @if ($clubs->isNotEmpty())
                <form method="GET" action="{{ route('contracts.index') }}">
                    <select class="sim-select" name="club" onchange="this.form.submit()">
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}" @selected($activeClub && $activeClub->id === $club->id)>
                                {{ $club->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </x-slot>

    @if (!$activeClub)
        <section class="sim-card p-8 text-center">
            <p class="text-slate-300">Kein Verein vorhanden.</p>
        </section>
    @else
        <section class="sim-card p-4">
            <p class="flex items-center gap-2 text-sm text-slate-300">
                <img class="sim-avatar sim-avatar-sm" src="{{ $activeClub->logo_url }}" alt="{{ $activeClub->name }}">
                <span>{{ $activeClub->name }}</span>
            </p>
        </section>
        <section class="sim-card overflow-x-auto">
            <table class="sim-table min-w-full">
                <thead>
                    <tr>
                        <th>Spieler</th>
                        <th>Pos</th>
                        <th>OVR</th>
                        <th>Aktuelles Gehalt</th>
                        <th>Vertragsende</th>
                        <th>Verlaengerung</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($players as $player)
                        <tr>
                            <td>
                                <span class="inline-flex items-center gap-2">
                                    <img class="sim-avatar sim-avatar-xs" src="{{ $player->photo_url }}"
                                        alt="{{ $player->full_name }}">
                                    <span>{{ $player->full_name }}</span>
                                </span>
                            </td>
                            <td>{{ $player->display_position }}</td>
                            <td>{{ $player->overall }}</td>
                            <td>{{ number_format((float) $player->salary, 2, ',', '.') }} EUR</td>
                            <td>{{ $player->contract_expires_on?->format('d.m.Y') ?: '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('contracts.renew', $player) }}"
                                    class="grid gap-2 sm:grid-cols-3">
                                    @csrf
                                    <input class="sim-input sm:col-span-1" type="number" step="0.01" min="0" name="salary"
                                        value="{{ (float) $player->salary }}" required>
                                    <input class="sim-input sm:col-span-1" type="number" min="1" max="84" name="months"
                                        value="24" required>
                                    <button class="sim-btn-primary sm:col-span-1" type="submit">Verlaengern</button>
                                    <input class="sim-input sm:col-span-3" type="number" step="0.01" min="0"
                                        name="release_clause" placeholder="Ausstiegsklausel (optional)">
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-300">Keine Spieler gefunden.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif
</x-app-layout>