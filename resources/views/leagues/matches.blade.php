<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">Liga</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Spiele & Spieltage</h1>
                @if ($activeCompetitionSeason)
                    <p class="mt-1 text-sm text-slate-300">
                        {{ $activeCompetitionSeason->competition->name }} | {{ $activeCompetitionSeason->season->name }}
                    </p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="GET" action="{{ route('league.matches') }}">
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
                                <div class="text-sm font-semibold text-white">
                                    {{ $match->homeClub->name }} vs {{ $match->awayClub->name }}
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
