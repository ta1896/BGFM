<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">ACP Spieler</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Globale Spielerverwaltung</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="GET" action="{{ route('admin.players.index') }}">
                    <select name="club" class="sim-select" onchange="this.form.submit()">
                        <option value="">Alle Vereine</option>
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}" @selected($activeClubId === $club->id)>
                                {{ $club->name }} ({{ $club->user->name }})
                            </option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('admin.players.create') }}" class="sim-btn-primary">Spieler erstellen</a>
            </div>
        </div>
    </x-slot>

    <div class="sim-card overflow-x-auto">
        <table class="sim-table min-w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Verein</th>
                    <th>Owner</th>
                    <th>Pos.</th>
                    <th>Overall</th>
                    <th class="text-right">Aktion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($players as $player)
                    <tr>
                        <td>{{ $player->full_name }}</td>
                        <td>{{ $player->club->name }}</td>
                        <td>{{ $player->club->user->name }}</td>
                        <td>{{ $player->position }}</td>
                        <td>{{ $player->overall }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.players.edit', $player) }}" class="text-cyan-300 hover:text-cyan-200">Bearbeiten</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-5 text-center text-slate-300">Keine Spieler vorhanden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $players->links() }}
</x-app-layout>
