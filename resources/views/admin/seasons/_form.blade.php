<div class="space-y-4">
    <div>
        <label class="sim-label">Name der Saison (z.B. 2024/25)</label>
        <input type="text" name="name" class="sim-input" value="{{ old('name', $season->name ?? '') }}"
            placeholder="2025/26" required>
        @error('name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="sim-label">Startdatum</label>
            <input type="date" name="start_date" class="sim-input text-sm"
                value="{{ old('start_date', isset($season) ? $season->start_date->format('Y-m-d') : '') }}" required>
            @error('start_date') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="sim-label">Enddatum</label>
            <input type="date" name="end_date" class="sim-input text-sm"
                value="{{ old('end_date', isset($season) ? $season->end_date->format('Y-m-d') : '') }}" required>
            @error('end_date') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex items-center gap-3 bg-slate-800/40 p-3 rounded-lg border border-slate-700/50">
        <input type="hidden" name="is_current" value="0">
        <input type="checkbox" name="is_current" value="1" id="is_current" class="sim-checkbox"
            @checked(old('is_current', $season->is_current ?? false))>
        <label for="is_current" class="text-sm text-white font-bold cursor-pointer">Als aktuelle Hauptsaison
            festlegen</label>
    </div>
</div>