<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title">ACP Vereine</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Globale Vereinsverwaltung</h1>
            </div>
            <a href="{{ route('admin.clubs.create') }}" class="sim-btn-primary">Verein erstellen</a>
        </div>
    </x-slot>

    <div class="sim-card overflow-x-auto">
        <table class="sim-table min-w-full">
            <thead>
                <tr>
                    <th>Verein</th>
                    <th>Owner</th>
                    <th>Liga</th>
                    <th>Spieler</th>
                    <th>Aufstellungen</th>
                    <th class="text-right">Aktion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clubs as $club)
                    <tr>
                        <td>
                            <span>{{ $club->name }}</span>
                            @if ($club->is_cpu)
                                <span class="sim-pill ml-2">CPU</span>
                            @endif
                        </td>
                        <td>{{ $club->user->name }}</td>
                        <td>{{ $club->league }}</td>
                        <td>{{ $club->players_count }}</td>
                        <td>{{ $club->lineups_count }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.clubs.edit', $club) }}" class="text-cyan-300 hover:text-cyan-200">Bearbeiten</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-5 text-center text-slate-300">Keine Vereine vorhanden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $clubs->links() }}
</x-app-layout>
