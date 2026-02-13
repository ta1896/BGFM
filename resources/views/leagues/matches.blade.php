<x-app-layout>
    @php
        $baseQuery = array_filter([
            'competition_season' => $activeCompetitionSeason?->id,
            'club' => $filters['club'] ?? null,
            'status' => $filters['status'] ?? null,
            'scope' => null,
            'day' => null,
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">Liga</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Spiele & Spieltage</h1>
                @if ($activeCompetitionSeason)
                    <div class="mt-1 flex items-center gap-2 text-sm text-slate-300">
                        <img class="sim-avatar sim-avatar-sm" src="{{ $activeCompetitionSeason->competition->logo_url }}" alt="{{ $activeCompetitionSeason->competition->name }}">
                        <span>{{ $activeCompetitionSeason->competition->name }} | {{ $activeCompetitionSeason->season->name }}</span>
                    </div>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="GET" action="{{ route('league.matches') }}">
                    @if (!empty($filters['club']))
                        <input type="hidden" name="club" value="{{ $filters['club'] }}">
                    @endif
                    @if (!empty($filters['status']))
                        <input type="hidden" name="status" value="{{ $filters['status'] }}">
                    @endif
                    @if (!empty($filters['scope']))
                        <input type="hidden" name="scope" value="{{ $filters['scope'] }}">
                    @endif
                    @if (!empty($filters['day']))
                        <input type="hidden" name="day" value="{{ $filters['day'] }}">
                    @endif
                    @if (!empty($filters['from']))
                        <input type="hidden" name="from" value="{{ $filters['from'] }}">
                    @endif
                    @if (!empty($filters['to']))
                        <input type="hidden" name="to" value="{{ $filters['to'] }}">
                    @endif
                    <select class="sim-select" name="competition_season" onchange="this.form.submit()">
                        @foreach ($competitionSeasons as $cs)
                            <option value="{{ $cs->id }}" @selected($activeCompetitionSeason && $activeCompetitionSeason->id === $cs->id)>
                                {{ $cs->competition->name }} - {{ $cs->season->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @if (auth()->user()->isAdmin() && $activeCompetitionSeason)
                    <form method="POST" action="{{ route('admin.competition-seasons.generate-fixtures', $activeCompetitionSeason) }}">
                        @csrf
                        <button class="sim-btn-muted" type="submit">Spielplan generieren</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <section class="sim-card p-4">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('league.matches', ['competition_season' => $activeCompetitionSeason?->id]) }}" class="sim-btn-muted {{ !$hasActiveFilters ? '!border-cyan-300/60 !bg-cyan-500/15' : '' }}">Alle</a>
            <a href="{{ route('league.matches', array_merge($baseQuery, ['scope' => 'today', 'day' => null])) }}" class="sim-btn-muted {{ ($filters['scope'] ?? '') === 'today' ? '!border-cyan-300/60 !bg-cyan-500/15' : '' }}">Heute</a>
            <a href="{{ route('league.matches', array_merge($baseQuery, ['scope' => 'week', 'day' => null])) }}" class="sim-btn-muted {{ ($filters['scope'] ?? '') === 'week' ? '!border-cyan-300/60 !bg-cyan-500/15' : '' }}">Diese Woche</a>
            <a href="{{ route('league.matches', array_merge($baseQuery, ['status' => 'live', 'scope' => null, 'day' => null])) }}" class="sim-btn-muted {{ ($filters['status'] ?? '') === 'live' ? '!border-cyan-300/60 !bg-cyan-500/15' : '' }}">Live</a>
            <a href="{{ route('league.matches', array_merge($baseQuery, ['status' => 'scheduled', 'scope' => null, 'day' => null])) }}" class="sim-btn-muted {{ ($filters['status'] ?? '') === 'scheduled' ? '!border-cyan-300/60 !bg-cyan-500/15' : '' }}">Geplant</a>
            <a href="{{ route('league.matches', array_merge($baseQuery, ['status' => 'played', 'scope' => null, 'day' => null])) }}" class="sim-btn-muted {{ ($filters['status'] ?? '') === 'played' ? '!border-cyan-300/60 !bg-cyan-500/15' : '' }}">Beendet</a>
        </div>

        <form method="GET" action="{{ route('league.matches') }}" class="mt-3 grid gap-3 md:grid-cols-5">
            <input type="hidden" name="competition_season" value="{{ $activeCompetitionSeason?->id }}">
            <div>
                <label class="sim-label" for="clubFilter">Verein</label>
                <select id="clubFilter" name="club" class="sim-select">
                    <option value="">Alle Vereine</option>
                    @foreach ($clubFilterOptions as $clubOption)
                        <option value="{{ $clubOption->id }}" @selected((int) ($filters['club'] ?? 0) === (int) $clubOption->id)>
                            {{ $clubOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sim-label" for="fromDate">Von</label>
                <input id="fromDate" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="sim-input">
            </div>
            <div>
                <label class="sim-label" for="toDate">Bis</label>
                <input id="toDate" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="sim-input">
            </div>
            <div>
                <label class="sim-label" for="statusFilter">Status</label>
                <select id="statusFilter" name="status" class="sim-select">
                    <option value="">Alle</option>
                    <option value="scheduled" @selected(($filters['status'] ?? '') === 'scheduled')>Geplant</option>
                    <option value="live" @selected(($filters['status'] ?? '') === 'live')>Live</option>
                    <option value="played" @selected(($filters['status'] ?? '') === 'played')>Beendet</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="sim-btn-primary">Filtern</button>
                @if ($hasActiveFilters)
                    <a href="{{ route('league.matches', ['competition_season' => $activeCompetitionSeason?->id]) }}" class="sim-btn-muted">Reset</a>
                @endif
            </div>
        </form>
    </section>

    @if ($activeCompetitionSeason && $matchesByDay->isNotEmpty())
        <div class="space-y-4">
            @foreach ($matchesByDay as $matchday => $matches)
                <section class="sim-card p-5">
                    <h2 class="text-lg font-bold text-white">Spieltag {{ $matchday }}</h2>
                    <div class="mt-4 space-y-2">
                        @foreach ($matches as $match)
                            <div class="sim-card-soft flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-sm text-slate-300">
                                    {{ $match->kickoff_at?->format('d.m.Y H:i') }} Uhr
                                </div>
                                <div class="flex items-center gap-2 text-sm font-semibold text-white">
                                    <img class="sim-avatar sim-avatar-xs" src="{{ $match->homeClub->logo_url }}" alt="{{ $match->homeClub->name }}">
                                    <span>{{ $match->homeClub->name }}</span>
                                    <span class="text-slate-400">vs</span>
                                    <img class="sim-avatar sim-avatar-xs" src="{{ $match->awayClub->logo_url }}" alt="{{ $match->awayClub->name }}">
                                    <span>{{ $match->awayClub->name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($match->status === 'played')
                                        <span class="sim-pill">{{ $match->home_score }} : {{ $match->away_score }}</span>
                                    @else
                                        <span class="sim-pill">{{ $match->status }}</span>
                                    @endif
                                    @if (auth()->user()->isAdmin() || $ownedClubIds->contains($match->home_club_id) || $ownedClubIds->contains($match->away_club_id))
                                        @if ($match->status !== 'played')
                                            <a href="{{ route('matches.lineup.edit', ['match' => $match->id]) }}" class="sim-btn-muted !px-3 !py-1.5 text-xs">Aufstellung</a>
                                        @endif
                                    @endif
                                    <a href="{{ route('matches.show', $match) }}" class="sim-btn-muted !px-3 !py-1.5 text-xs">Matchcenter</a>
                                    @if (auth()->user()->isAdmin() && $match->status !== 'played')
                                        <form method="POST" action="{{ route('matches.simulate', $match) }}">
                                            @csrf
                                            <button type="submit" class="sim-btn-primary !px-3 !py-1.5 text-xs">Simulieren</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="sim-card p-8 text-center">
            <p class="text-slate-300">Kein Spielplan vorhanden.</p>
            @if (auth()->user()->isAdmin() && $activeCompetitionSeason)
                <form method="POST" action="{{ route('admin.competition-seasons.generate-fixtures', $activeCompetitionSeason) }}" class="mt-4">
                    @csrf
                    <button class="sim-btn-primary" type="submit">Jetzt generieren</button>
                </form>
            @endif
        </div>
    @endif
</x-app-layout>
