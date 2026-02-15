<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title">ACP Ligen & Pokale</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Ligen & Pokale</h1>
            </div>
            <a href="{{ route('admin.competitions.create') }}" class="sim-btn-primary">Neu</a>
        </div>
    </x-slot>

    <div class="sim-card overflow-x-auto">
        <table class="sim-table min-w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Typ</th>
                    <th>Ebene</th>
                    <th>Stufe</th>
                    <th>Land</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($competitions as $competition)
                    <tr>
                        <td class="font-semibold">
                            <div class="flex items-center gap-2">
                                <img class="h-5 w-5 rounded-full object-cover ring-1 ring-slate-800"
                                    src="{{ $competition->logo_url }}" alt="{{ $competition->name }}">
                                <span>{{ $competition->name }}</span>
                            </div>
                        </td>
                        <td>{{ $competition->type === 'cup' ? 'Pokal' : 'Liga' }}</td>
                        <td>
                            @if ($competition->type === 'cup')
                                {{ $competition->scope === 'international' ? 'International' : 'National' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $competition->tier ?? '-' }}</td>
                        <td>{{ $competition->country?->name ?? '-' }}</td>
                        <td>
                            @if ($competition->is_active)
                                <span class="sim-pill">Aktiv</span>
                            @else
                                <span class="sim-pill !border-slate-500/50 !bg-slate-700/30 !text-slate-200">Inaktiv</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.competitions.edit', $competition) }}"
                                class="sim-btn-muted">Bearbeiten</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $competitions->links() }}
    </div>
</x-app-layout>