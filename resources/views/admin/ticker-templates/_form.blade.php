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

    <div>
        <label class="block text-sm font-medium text-slate-400 mb-2">Text Vorlage</label>
        <textarea name="text" rows="4"
            class="w-full bg-slate-900 border border-slate-800 rounded-lg py-2 px-4 text-slate-300 focus:ring-indigo-500 focus:border-indigo-500"
            required
            placeholder="Verwende {player}, {opponent}, {club}, {score} als Platzhalter">{{ old('text', $tickerTemplate->text ?? '') }}</textarea>
        <p class="mt-2 text-xs text-slate-500">Verfügbare Platzhalter: {player}, {opponent}, {club}, {score}</p>
    </div>

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