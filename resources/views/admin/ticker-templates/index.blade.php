<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">ACP Liveticker</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Ticker Vorlagen Verwaltung</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.ticker-templates.create') }}" class="sim-btn-primary">
                    Neue Vorlage
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
            <div class="p-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-xl">
            <div class="p-6 bg-slate-900/50">
                <form action="{{ route('admin.ticker-templates.index') }}" method="GET" id="filter-form">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Ereignisse filtern
                        (Mehrfachauswahl möglich)</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($eventTypes as $value => $label)
                                            @php
                                                $selected = in_array($value, (array) request('event_types', []));
                                            @endphp
                                            <label class="cursor-pointer group">
                                                <input type="checkbox" name="event_types[]" value="{{ $value }}" class="hidden"
                                                    onclick="this.form.submit()" {{ $selected ? 'checked' : '' }}>
                                                <span
                                                    class="flex items-center gap-2 px-4 py-2 rounded-lg border transition-all 
                                                        {{ $selected
                            ? 'bg-emerald-600 text-white border-emerald-500 shadow-[0_0_20px_rgba(16,185,129,0.4)]'
                            : 'bg-slate-900 border-slate-800 text-slate-400 group-hover:border-slate-600' }}">
                                                    @if($selected)
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                                                d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    @endif
                                                    {{ $label }}
                                                </span>
                                            </label>
                        @endforeach

                        @if(request('event_types'))
                            <a href="{{ route('admin.ticker-templates.index') }}"
                                class="px-4 py-2 rounded-lg border border-rose-500/30 bg-rose-500/10 text-rose-400 text-sm hover:bg-rose-500/20 transition-all">
                                Filter zurücksetzen
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-900/50 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Ereignis</th>
                        <th class="px-6 py-4 font-semibold">Text</th>
                        <th class="px-6 py-4 font-semibold text-right">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y-0">
                    @forelse($templates as $template)
                        <tr class="hover:bg-slate-800/30 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider 
                                                            @if($template->event_type == 'goal') bg-emerald-500/10 text-emerald-400
                                                            @elseif($template->event_type == 'yellow_card') bg-amber-500/10 text-amber-400
                                                            @elseif($template->event_type == 'red_card') bg-rose-500/10 text-rose-400
                                                            @else bg-slate-800 text-slate-400
                                                            @endif">
                                    {{ \App\Models\MatchTickerTemplate::EVENT_TYPES[$template->event_type] ?? $template->event_type }}
                                </span>
                                <div class="text-[9px] text-slate-500 mt-1 uppercase font-mono tracking-tighter">
                                    {{ $template->priority }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-300 text-sm">
                                "{{ $template->text }}"
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div
                                    class="flex justify-end gap-3 translate-x-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 transition-all">
                                    <a href="{{ route('admin.ticker-templates.edit', $template) }}"
                                        class="text-indigo-400 hover:text-indigo-300">
                                        Bearbeiten
                                    </a>
                                    <form action="{{ route('admin.ticker-templates.destroy', $template) }}" method="POST"
                                        onsubmit="return confirm('Wirklich löschen?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-rose-500 hover:text-rose-400 underline">Löschen</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3"
                                class="px-6 py-12 text-center text-slate-500 italic uppercase tracking-widest text-xs">Keine
                                Vorlagen gefunden.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($templates->hasPages())
                <div class="p-4 bg-slate-900 border-t border-slate-800">
                    {{ $templates->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>