<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title">{{ $competitionSeason->competition->name }}</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Saison {{ $competitionSeason->season->name }} bearbeiten
                </h1>
            </div>
            <a href="{{ route('admin.competitions.edit', $competitionSeason->competition_id) }}"
                class="sim-btn-muted">Zurueck</a>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <form action="{{ route('admin.competition-seasons.update', $competitionSeason) }}" method="POST"
            class="sim-card p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Winners Section -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-white border-b border-slate-800 pb-2 italic">Meister & Sieger</h3>

                    <div>
                        <label class="sim-label text-emerald-400">Liga Meister (Platz 1)</label>
                        <select name="league_winner_club_id" class="sim-select">
                            <option value="">-- Nicht festgelegt --</option>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}"
                                    @selected($competitionSeason->league_winner_club_id == $club->id)>{{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="sim-label text-amber-400">Nationaler Pokalsieger</label>
                        <select name="national_cup_winner_club_id" class="sim-select">
                            <option value="">-- Nicht festgelegt --</option>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}"
                                    @selected($competitionSeason->national_cup_winner_club_id == $club->id)>{{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="sim-label text-cyan-400">Internationaler Pokalsieger</label>
                        <select name="intl_cup_winner_club_id" class="sim-select">
                            <option value="">-- Nicht festgelegt --</option>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}"
                                    @selected($competitionSeason->intl_cup_winner_club_id == $club->id)>{{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-white border-b border-slate-800 pb-2 italic">Status</h3>

                    <div class="bg-slate-800/40 p-4 rounded-lg border border-slate-700/50">
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="is_finished" value="0">
                            <input type="checkbox" name="is_finished" value="1" id="is_finished" class="sim-checkbox"
                                @checked($competitionSeason->is_finished)>
                            <label for="is_finished" class="text-sm text-white font-bold cursor-pointer">Saison
                                abgeschlossen</label>
                        </div>
                        <p class="mt-2 text-[10px] text-slate-400 leading-tight">
                            WICHTIG: Wenn abgeschlossen, werden die oben gewaehlten Clubs als Gewinner registriert und
                            erhalten Trophaen.
                        </p>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex items-center justify-end gap-3 border-t border-slate-800">
                <a href="{{ route('admin.competitions.edit', $competitionSeason->competition_id) }}"
                    class="sim-btn-muted">Abbrechen</a>
                <button type="submit" class="sim-btn-primary">Speichern</button>
            </div>
        </form>
    </div>
</x-app-layout>