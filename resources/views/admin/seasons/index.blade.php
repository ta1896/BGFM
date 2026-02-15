<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title">ACP Zeitraeume</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Saisons verwalten</h1>
            </div>
            <a href="{{ route('admin.seasons.create') }}" class="sim-btn-primary">Neue Saison</a>
        </div>
    </x-slot>

    <div class="sim-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/50 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Zeitraum</th>
                        <th class="px-6 py-4 text-center">Aktuell</th>
                        <th class="px-6 py-4 text-right">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($seasons as $season)
                        <tr class="hover:bg-slate-800/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="font-bold text-white">{{ $season->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300">
                                {{ $season->start_date->format('d.m.Y') }} - {{ $season->end_date->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($season->is_current)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 ring-1 ring-emerald-500/20">JA</span>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.seasons.edit', $season) }}"
                                        class="sim-btn-muted py-1 px-3 text-xs">Bearbeiten</a>
                                    <form action="{{ route('admin.seasons.destroy', $season) }}" method="POST"
                                        onsubmit="return confirm('Wirklich loeschen?')">
                                        @csrf @method('DELETE')
                                        <button class="sim-btn-danger py-1 px-3 text-xs">X</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-500 italic">Keine Saisons angelegt.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>