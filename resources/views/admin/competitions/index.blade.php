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
                                @if ($competition->logo_path)
                                    <img class="h-8 w-8 rounded-full border border-slate-700/70 bg-slate-900/60 object-cover"
                                         src="{{ Storage::url($competition->logo_path) }}"
                                         alt="">
                                @else
                                    <span class="h-8 w-8 rounded-full border border-slate-700/70 bg-slate-900/60"></span>
                                @endif
                                <span>{{ $competition->name }}</span>
                            </div>
                        </td>
                        <td>{{ $competition->type === 'cup' ? 'Pokal' : 'Liga' }}</td>
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
                            <a href="{{ route('admin.competitions.edit', $competition) }}" class="sim-btn-muted">Bearbeiten</a>
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
