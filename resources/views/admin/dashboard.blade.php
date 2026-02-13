<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Admin Control Panel</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Systemverwaltung</h1>
            <p class="mt-2 text-sm text-slate-300">Adminbereich fuer globale Datenpflege.</p>
        </div>
    </x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-10">
        <article class="sim-card p-5">
            <p class="sim-section-title">User</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['users'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Admins</p>
            <p class="mt-2 text-3xl font-bold text-cyan-300">{{ $stats['admins'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Vereine</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['clubs'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">CPU-Teams</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['cpu_clubs'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Spieler</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['players'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Aufstellungen</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['lineups'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Offene Spiele</p>
            <p class="mt-2 text-3xl font-bold text-cyan-300">{{ $stats['scheduled_matches'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Sponsorvertraege</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['active_sponsors'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Stadionprojekte</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['active_stadium_projects'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Trainingslager</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $stats['active_training_camps'] }}</p>
        </article>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <article class="sim-card p-5">
            <div class="mb-4 flex items-center justify-between">
                <p class="sim-section-title">Schnellaktionen</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a class="sim-btn-muted" href="{{ route('admin.competitions.create') }}">Liga/Pokal erstellen</a>
                <a class="sim-btn-muted" href="{{ route('admin.competitions.index') }}">Ligen/Pokale</a>
                <a class="sim-btn-primary" href="{{ route('admin.clubs.create') }}">Verein erstellen</a>
                <a class="sim-btn-muted" href="{{ route('admin.players.create') }}">Spieler erstellen</a>
                <a class="sim-btn-muted" href="{{ route('admin.lineups.create') }}">Aufstellung erstellen</a>
                <a class="sim-btn-muted" href="{{ route('sponsors.index') }}">Sponsoren</a>
                <a class="sim-btn-muted" href="{{ route('stadium.index') }}">Stadion</a>
                <a class="sim-btn-muted" href="{{ route('training-camps.index') }}">Trainingslager</a>
            </div>

            <form method="POST" action="{{ route('admin.simulation.process-matchday') }}" class="mt-4 grid gap-2 sm:grid-cols-3">
                @csrf
                <select name="competition_season_id" class="sim-select sm:col-span-2">
                    <option value="">Alle aktiven Ligen</option>
                    @foreach ($activeCompetitionSeasons as $competitionSeason)
                    <option value="{{ $competitionSeason->id }}">
                            {{ $competitionSeason->competition?->name ?? 'Unbekannter Wettbewerb' }} - {{ $competitionSeason->season?->name ?? 'Unbekannte Saison' }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="sim-btn-primary sm:col-span-1">Spieltag laufen lassen</button>
            </form>
        </article>

        <article class="sim-card p-5">
            <div class="mb-4 flex items-center justify-between">
                <p class="sim-section-title">Simulationseinstellungen</p>
            </div>

            @php
                $selectedTypes = old('simulation.scheduler.default_types', data_get($simulationSettings, 'scheduler.default_types', ['friendly', 'league', 'cup']));
                $selectedTypes = is_array($selectedTypes) ? $selectedTypes : [];
                $observerEnabled = (bool) old('simulation.observers.match_finished.enabled', data_get($simulationSettings, 'observers.match_finished.enabled', true));
                $observerRebuildStats = (bool) old('simulation.observers.match_finished.rebuild_match_player_stats', data_get($simulationSettings, 'observers.match_finished.rebuild_match_player_stats', true));
                $observerAggregateStats = (bool) old('simulation.observers.match_finished.aggregate_player_competition_stats', data_get($simulationSettings, 'observers.match_finished.aggregate_player_competition_stats', true));
                $observerAvailability = (bool) old('simulation.observers.match_finished.apply_match_availability', data_get($simulationSettings, 'observers.match_finished.apply_match_availability', true));
                $observerCompetition = (bool) old('simulation.observers.match_finished.update_competition_after_match', data_get($simulationSettings, 'observers.match_finished.update_competition_after_match', true));
                $observerFinance = (bool) old('simulation.observers.match_finished.settle_match_finance', data_get($simulationSettings, 'observers.match_finished.settle_match_finance', true));
            @endphp

            <form method="POST" action="{{ route('admin.simulation.settings.update') }}" class="space-y-4">
                @csrf

                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="sim-label" for="scheduler_interval_minutes">Intervall (Minuten)</label>
                        <input id="scheduler_interval_minutes" type="number" min="1" max="60" name="simulation[scheduler][interval_minutes]" class="sim-input" value="{{ old('simulation.scheduler.interval_minutes', data_get($simulationSettings, 'scheduler.interval_minutes', 1)) }}">
                    </div>
                    <div>
                        <label class="sim-label" for="scheduler_default_limit">Max. Matches pro Lauf</label>
                        <input id="scheduler_default_limit" type="number" min="0" max="500" name="simulation[scheduler][default_limit]" class="sim-input" value="{{ old('simulation.scheduler.default_limit', data_get($simulationSettings, 'scheduler.default_limit', 0)) }}">
                    </div>
                    <div>
                        <label class="sim-label" for="scheduler_default_minutes_per_run">Minuten pro Lauf</label>
                        <input id="scheduler_default_minutes_per_run" type="number" min="1" max="90" name="simulation[scheduler][default_minutes_per_run]" class="sim-input" value="{{ old('simulation.scheduler.default_minutes_per_run', data_get($simulationSettings, 'scheduler.default_minutes_per_run', 5)) }}">
                    </div>
                    <div>
                        <label class="sim-label" for="scheduler_claim_stale_after_seconds">Claim Timeout (Sekunden)</label>
                        <input id="scheduler_claim_stale_after_seconds" type="number" min="30" max="3600" name="simulation[scheduler][claim_stale_after_seconds]" class="sim-input" value="{{ old('simulation.scheduler.claim_stale_after_seconds', data_get($simulationSettings, 'scheduler.claim_stale_after_seconds', 180)) }}">
                    </div>
                    <div>
                        <label class="sim-label" for="scheduler_runner_lock_seconds">Runner Lock TTL (Sekunden)</label>
                        <input id="scheduler_runner_lock_seconds" type="number" min="30" max="3600" name="simulation[scheduler][runner_lock_seconds]" class="sim-input" value="{{ old('simulation.scheduler.runner_lock_seconds', data_get($simulationSettings, 'scheduler.runner_lock_seconds', 120)) }}">
                    </div>
                </div>

                <div>
                    <p class="sim-label">Standard Match-Typen</p>
                    <div class="flex flex-wrap gap-3 text-sm text-slate-200">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="simulation[scheduler][default_types][]" value="friendly" @checked(in_array('friendly', $selectedTypes, true))>
                            <span>Friendly</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="simulation[scheduler][default_types][]" value="league" @checked(in_array('league', $selectedTypes, true))>
                            <span>Liga</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="simulation[scheduler][default_types][]" value="cup" @checked(in_array('cup', $selectedTypes, true))>
                            <span>Pokal</span>
                        </label>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="sim-label" for="position_fit_main">Positionsfaktor Main</label>
                        <input id="position_fit_main" step="0.01" type="number" min="0.50" max="1.20" name="simulation[position_fit][main]" class="sim-input" value="{{ old('simulation.position_fit.main', data_get($simulationSettings, 'position_fit.main', 1.00)) }}">
                    </div>
                    <div>
                        <label class="sim-label" for="position_fit_second">Positionsfaktor Second</label>
                        <input id="position_fit_second" step="0.01" type="number" min="0.50" max="1.20" name="simulation[position_fit][second]" class="sim-input" value="{{ old('simulation.position_fit.second', data_get($simulationSettings, 'position_fit.second', 0.92)) }}">
                    </div>
                    <div>
                        <label class="sim-label" for="position_fit_third">Positionsfaktor Third</label>
                        <input id="position_fit_third" step="0.01" type="number" min="0.50" max="1.20" name="simulation[position_fit][third]" class="sim-input" value="{{ old('simulation.position_fit.third', data_get($simulationSettings, 'position_fit.third', 0.84)) }}">
                    </div>
                    <div>
                        <label class="sim-label" for="position_fit_foreign">Positionsfaktor Fremd</label>
                        <input id="position_fit_foreign" step="0.01" type="number" min="0.30" max="1.20" name="simulation[position_fit][foreign]" class="sim-input" value="{{ old('simulation.position_fit.foreign', data_get($simulationSettings, 'position_fit.foreign', 0.76)) }}">
                    </div>
                    <div>
                        <label class="sim-label" for="position_fit_foreign_gk">Positionsfaktor Fremd-TW</label>
                        <input id="position_fit_foreign_gk" step="0.01" type="number" min="0.20" max="1.20" name="simulation[position_fit][foreign_gk]" class="sim-input" value="{{ old('simulation.position_fit.foreign_gk', data_get($simulationSettings, 'position_fit.foreign_gk', 0.55)) }}">
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    <div>
                        <label class="sim-label" for="planned_subs_max_per_club">Planned Subs max/Club</label>
                        <input id="planned_subs_max_per_club" type="number" min="1" max="5" name="simulation[live_changes][planned_substitutions][max_per_club]" class="sim-input" value="{{ old('simulation.live_changes.planned_substitutions.max_per_club', data_get($simulationSettings, 'live_changes.planned_substitutions.max_per_club', 5)) }}">
                    </div>
                    <div>
                        <label class="sim-label" for="planned_subs_min_minutes_ahead">Planned Subs Vorlauf</label>
                        <input id="planned_subs_min_minutes_ahead" type="number" min="1" max="30" name="simulation[live_changes][planned_substitutions][min_minutes_ahead]" class="sim-input" value="{{ old('simulation.live_changes.planned_substitutions.min_minutes_ahead', data_get($simulationSettings, 'live_changes.planned_substitutions.min_minutes_ahead', 2)) }}">
                    </div>
                    <div>
                        <label class="sim-label" for="planned_subs_min_interval_minutes">Planned Subs Intervall</label>
                        <input id="planned_subs_min_interval_minutes" type="number" min="1" max="30" name="simulation[live_changes][planned_substitutions][min_interval_minutes]" class="sim-input" value="{{ old('simulation.live_changes.planned_substitutions.min_interval_minutes', data_get($simulationSettings, 'live_changes.planned_substitutions.min_interval_minutes', 3)) }}">
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    <div>
                        <label class="sim-label" for="lineup_max_bench_players">Bankspieler (max)</label>
                        <input id="lineup_max_bench_players" type="number" min="1" max="10" name="simulation[lineup][max_bench_players]" class="sim-input" value="{{ old('simulation.lineup.max_bench_players', data_get($simulationSettings, 'lineup.max_bench_players', 5)) }}">
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="sim-label">Post-Match Observer</p>
                    <input type="hidden" name="simulation[observers][match_finished][enabled]" value="0">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                        <input type="checkbox" name="simulation[observers][match_finished][enabled]" value="1" @checked($observerEnabled)>
                        <span>Pipeline global aktiviert</span>
                    </label>
                    <div class="grid gap-2 md:grid-cols-2 text-sm text-slate-300">
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="simulation[observers][match_finished][rebuild_match_player_stats]" value="0">
                            <input type="checkbox" name="simulation[observers][match_finished][rebuild_match_player_stats]" value="1" @checked($observerRebuildStats)>
                            <span>Match-Player-Stats neu aufbauen</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="simulation[observers][match_finished][aggregate_player_competition_stats]" value="0">
                            <input type="checkbox" name="simulation[observers][match_finished][aggregate_player_competition_stats]" value="1" @checked($observerAggregateStats)>
                            <span>Player-Competition-Stats aggregieren</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="simulation[observers][match_finished][apply_match_availability]" value="0">
                            <input type="checkbox" name="simulation[observers][match_finished][apply_match_availability]" value="1" @checked($observerAvailability)>
                            <span>Verfuegbarkeit/Sperren anwenden</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="simulation[observers][match_finished][update_competition_after_match]" value="0">
                            <input type="checkbox" name="simulation[observers][match_finished][update_competition_after_match]" value="1" @checked($observerCompetition)>
                            <span>Wettbewerb/Folgerunden updaten</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="simulation[observers][match_finished][settle_match_finance]" value="0">
                            <input type="checkbox" name="simulation[observers][match_finished][settle_match_finance]" value="1" @checked($observerFinance)>
                            <span>Finanzabrechnung buchen</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs text-slate-400">Die Werte sind persistent und gelten sofort fuer Scheduler, Live-Ticker und Parameter.</p>
                    <button type="submit" class="sim-btn-primary">Simulation speichern</button>
                </div>
            </form>
        </article>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <article class="sim-card p-5">
            <div class="mb-3 flex items-center justify-between">
                <p class="sim-section-title">Letzte User</p>
            </div>
            <div class="space-y-2">
                @foreach ($latestUsers as $user)
                    <div class="sim-card-soft flex items-center justify-between px-3 py-2">
                        <div>
                            <p class="text-sm font-semibold text-white">{{ $user->name }}</p>
                            <p class="text-xs text-slate-400">{{ $user->email }}</p>
                        </div>
                        @if ($user->is_admin)
                            <span class="sim-pill">Admin</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </article>

        <article class="sim-card p-5">
            <div class="mb-3 flex items-center justify-between">
                <p class="sim-section-title">Letzte Vereine</p>
            </div>
            <div class="space-y-2">
                @foreach ($latestClubs as $club)
                    <div class="sim-card-soft flex items-center justify-between px-3 py-2">
                        <div>
                            <p class="text-sm font-semibold text-white">{{ $club->name }}</p>
                            <p class="text-xs text-slate-400">Owner: {{ $club->user?->name ?? 'Kein Owner' }}</p>
                        </div>
                        <a href="{{ route('admin.clubs.edit', $club) }}" class="text-sm text-cyan-300 hover:text-cyan-200">Bearbeiten</a>
                    </div>
                @endforeach
            </div>
        </article>
    </section>
</x-app-layout>
