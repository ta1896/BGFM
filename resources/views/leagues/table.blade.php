@php
    $activeCompetitionId = $activeCompetitionSeason?->competition_id;
    $activeSeasonId = $activeCompetitionSeason?->season_id;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="sim-section-title">Liga</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Ligatabelle</h1>
                @if ($activeCompetitionSeason)
                    <div class="mt-1 flex items-center gap-2 text-sm text-slate-300">
                        <img class="sim-avatar sim-avatar-sm" src="{{ $activeCompetitionSeason->competition->logo_url }}" alt="{{ $activeCompetitionSeason->competition->name }}">
                        <span>{{ $activeCompetitionSeason->competition->name }} | {{ $activeCompetitionSeason->season->name }}</span>
                    </div>
                @endif
            </div>
            <form method="GET" action="{{ route('league.table') }}" class="flex flex-wrap gap-2">
                <select class="sim-select" name="competition_id" onchange="this.form.submit()">
                    @foreach ($competitions as $competition)
                        <option value="{{ $competition->id }}" @selected($competition->id === $activeCompetitionId)>
                            {{ $competition->name }}
                        </option>
                    @endforeach
                </select>
                <select class="sim-select" name="season_id" onchange="this.form.submit()">
                    @foreach ($seasons as $season)
                        <option value="{{ $season->id }}" @selected($season->id === $activeSeasonId)>
                            {{ $season->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <div class="sim-card p-4">
        <div class="flex flex-wrap gap-2">
            @foreach (['Gesamt', 'Hinrunde', 'Rueckrunde', 'Heim', 'Auswaerts', 'Form'] as $tab)
                <button type="button" class="sim-tab {{ $loop->first ? 'sim-tab-active' : '' }}">{{ $tab }}</button>
            @endforeach
            <div class="ml-auto flex flex-wrap gap-2">
                <span class="sim-chip !border-emerald-400/40 !text-emerald-200">Super-League</span>
                <span class="sim-chip !border-sky-400/40 !text-sky-200">SL-Quali</span>
                <span class="sim-chip !border-rose-400/40 !text-rose-200">Abstieg</span>
                <span class="sim-chip">Praemien</span>
                <span class="sim-chip">Manager</span>
            </div>
        </div>
    </div>

    @if ($activeCompetitionSeason && $table->isNotEmpty())
        <div class="sim-card overflow-x-auto">
            <table class="sim-table sim-table-compact min-w-full">
                <thead>
                    <tr>
                        <th class="w-16 text-center">#</th>
                        <th>Team</th>
                        <th class="text-center">Sp</th>
                        <th class="text-center">S</th>
                        <th class="text-center">U</th>
                        <th class="text-center">N</th>
                        <th class="text-center">Tore</th>
                        <th class="text-center">Diff</th>
                        <th class="text-center">Pkt</th>
                        <th class="text-center">Form</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($table as $index => $row)
                        @php
                            $isOwned = in_array($row->club_id, $ownedClubIds ?? [], true);
                            $form = strtoupper((string) $row->form_last5);
                            $formChars = str_split(str_pad($form, 5, '-'));
                        @endphp
                        <tr class="{{ $isOwned ? 'sim-table-highlight' : '' }}">
                            <td class="text-center">
                                <span class="sim-rank">{{ $index + 1 }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <img class="sim-avatar sim-avatar-sm" src="{{ $row->club->logo_url }}" alt="{{ $row->club->name }}">
                                    <span class="font-semibold">{{ $row->club->name }}</span>
                                </div>
                            </td>
                            <td class="text-center">{{ $row->matches_played }}</td>
                            <td class="text-center">{{ $row->wins }}</td>
                            <td class="text-center">{{ $row->draws }}</td>
                            <td class="text-center">{{ $row->losses }}</td>
                            <td class="text-center">{{ $row->goals_for }}:{{ $row->goals_against }}</td>
                            <td class="text-center">{{ $row->goal_diff }}</td>
                            <td class="text-center font-semibold text-cyan-200">{{ $row->points }}</td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @foreach ($formChars as $char)
                                        @php
                                            $dotClass = match ($char) {
                                                'W' => 'sim-form-dot sim-form-win',
                                                'D' => 'sim-form-dot sim-form-draw',
                                                'L' => 'sim-form-dot sim-form-loss',
                                                default => 'sim-form-dot',
                                            };
                                        @endphp
                                        <span class="{{ $dotClass }}"></span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="sim-card p-8 text-center">
            <p class="text-slate-300">Noch keine Tabellendaten vorhanden.</p>
        </div>
    @endif
</x-app-layout>
