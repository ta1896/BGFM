<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">Kader</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Spielerverwaltung</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="GET" action="{{ route('players.index') }}">
                    <select name="club" class="sim-select" onchange="this.form.submit()">
                        <option value="">Alle Vereine</option>
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}" @selected($activeClubId === $club->id)>{{ $club->name }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('players.create') }}" class="sim-btn-primary">Neuer Spieler</a>
            </div>
        </div>
    </x-slot>

    <div class="sim-card overflow-x-auto">
        <table class="sim-table min-w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Verein</th>
                    <th>Pos.</th>
                    <th>Overall</th>
                    <th>Alter</th>
                    <th>Wert</th>
                    <th class="text-right">Aktion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($players as $player)
                    <tr>
                        <td>{{ $player->full_name }}</td>
                        <td>{{ $player->club->name }}</td>
                        <td>{{ $player->position }}</td>
                        <td>{{ $player->overall }}</td>
                        <td>{{ $player->age }}</td>
                        <td>{{ number_format((float) $player->market_value, 0, ',', '.') }} EUR</td>
                        <td class="text-right">
                            <a href="{{ route('players.show', $player) }}" class="text-cyan-300 hover:text-cyan-200">Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-5 text-center text-slate-300">Keine Spieler gefunden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $players->links() }}
    </div>
</x-app-layout>
