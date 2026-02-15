<div class="space-y-6">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-2">Ereignis-Typ</label>
            <select name="event_type"
                class="w-full bg-slate-900 border border-slate-800 rounded-lg py-2 px-4 text-slate-300 focus:ring-indigo-500"
                required>
                @foreach($eventTypes as $value => $label)
                    <option value="{{ $value }}" {{ old('event_type', $tickerTemplate->event_type ?? '') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-400 mb-2">Priorität</label>
            <select name="priority"
                class="w-full bg-slate-900 border border-slate-800 rounded-lg py-2 px-4 text-slate-300 focus:ring-indigo-500">
                <option value="low" {{ (old('priority', $tickerTemplate->priority ?? 'normal') == 'low') ? 'selected' : '' }}>Niedrig</option>
                <option value="normal" {{ (old('priority', $tickerTemplate->priority ?? 'normal') == 'normal') ? 'selected' : '' }}>Normal</option>
                <option value="high" {{ (old('priority', $tickerTemplate->priority ?? 'normal') == 'high') ? 'selected' : '' }}>Hoch</option>
            </select>
        </div>
    </div>

    <div class="col-span-1 md:col-span-2">
        <label class="block text-sm font-medium text-slate-400 mb-2">Text Vorlage</label>
        <textarea name="text" id="ticker-text" rows="4"
            class="w-full bg-slate-900 border border-slate-800 rounded-lg py-3 px-4 text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-inner transition-all"
            required
            placeholder="Verwende {player}, {opponent}, {club}, {score} als Platzhalter">{{ old('text', $tickerTemplate->text ?? '') }}</textarea>

        @error('text')
            <p class="text-rose-500 text-xs mt-2">{{ $message }}</p>
        @enderror

        <div class="mt-4 p-5 bg-slate-950/80 border border-slate-800/50 rounded-xl backdrop-blur-sm">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                    Live-Vorschau
                </h4>
                <span class="text-[9px] text-slate-600 font-mono">Demo Match Simulation</span>
            </div>
            <div id="preview-box" class="text-sm text-slate-400 leading-relaxed min-h-[1.5rem]">
                <span class="opacity-50 italic">Gib einen Text ein, um die Live-Vorschau zu sehen...</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textarea = document.getElementById('ticker-text');
            const preview = document.getElementById('preview-box');

            const demoData = {
                'player': '<span class="text-indigo-400 font-bold">Max Mustermann</span>',
                'opponent': '<span class="text-rose-400 font-bold">Torben Torjäger</span>',
                'club': '<span class="text-slate-200 font-black border-b border-slate-700">FC BEISPIEL</span>',
                'score': '<span class="bg-slate-800 px-2 py-0.5 rounded text-white font-mono font-bold mx-1 shadow-lg shadow-black/20 text-xs">2:1</span>'
            };

            function updatePreview() {
                let content = textarea.value;
                if (!content.trim()) {
                    preview.innerHTML = '<span class="opacity-50 italic">Gib einen Text ein, um die Live-Vorschau zu sehen...</span>';
                    return;
                }

                // Escaping HTML
                content = content.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

                for (const [key, val] of Object.entries(demoData)) {
                    const regex = new RegExp('\\{' + key + '\\}', 'g');
                    content = content.replace(regex, val);
                }

                preview.innerHTML = content;
            }

            textarea.addEventListener('input', updatePreview);
            updatePreview();
        });
    </script>

    <input type="hidden" name="locale" value="de">

    <div class="flex justify-end gap-4">
        <a href="{{ route('admin.ticker-templates.index') }}"
            class="px-4 py-2 border border-slate-700 text-slate-300 rounded hover:bg-slate-800 transition">
            Abbrechen
        </a>
        <button type="submit"
            class="px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition font-medium">
            Speichern
        </button>
    </div>
</div>