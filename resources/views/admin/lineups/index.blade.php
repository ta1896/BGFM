<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="sim-section-title">ACP Aufstellungen</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Globale Aufstellungsverwaltung</h1>
            </div>
            <a href="{{ route('admin.lineups.create') }}" class="sim-btn-primary">Aufstellung erstellen</a>
        </div>
    </x-slot>

    <div class="sim-card overflow-x-auto">
        <table class="sim-table min-w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Verein</th>
                    <th>Owner</th>
                    <th>Formation</th>
                    <th>Spieler</th>
                    <th>Status</th>
                    <th class="text-right">Aktion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lineups as $lineup)
                    <tr>
                        <td>{{ $lineup->name }}</td>
                        <td>{{ $lineup->club->name }}</td>
                        <td>{{ $lineup->club->user->name }}</td>
                        <td>{{ $lineup->formation }}</td>
                        <td>{{ $lineup->players->count() }}</td>
                        <td>
                            @if ($lineup->is_active)
                                <span class="sim-pill">Aktiv</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.lineups.show', $lineup) }}" class="text-cyan-300 hover:text-cyan-200">Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-5 text-center text-slate-300">Keine Aufstellungen vorhanden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $lineups->links() }}
</x-app-layout>
