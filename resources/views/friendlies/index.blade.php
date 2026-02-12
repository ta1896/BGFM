<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">Spiele</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Freundschaftsspiele planen</h1>
            </div>
            @if ($clubs->isNotEmpty())
                <form method="GET" action="{{ route('friendlies.index') }}">
                    <select class="sim-select" name="club" onchange="this.form.submit()">
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}" @selected($activeClub && $activeClub->id === $club->id)>
                                {{ $club->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </x-slot>

    @if (!$activeClub)
        <section class="sim-card p-6 text-sm text-slate-300">
            Kein Verein verfuegbar.
        </section>
    @else
        <section class="grid gap-4 xl:grid-cols-3">
            <article class="sim-card p-5 xl:col-span-2">
                <h2 class="text-lg font-bold text-white">Neues Freundschaftsspiel</h2>
                <p class="mt-2 text-sm text-slate-300">
                    CPU-Teams nehmen automatisch an. Manager-Teams erhalten eine Anfrage.
                </p>
                <form method="POST" action="{{ route('friendlies.store') }}" class="mt-4 grid gap-3 md:grid-cols-2">
                    @csrf
                    <input type="hidden" name="club_id" value="{{ $activeClub->id }}">
                    <div>
                        <label class="sim-label" for="opponent_club_id">Gegner</label>
                        <select id="opponent_club_id" name="opponent_club_id" class="sim-select" required>
                            @foreach ($opponents as $opponent)
                                <option value="{{ $opponent->id }}">
                                    {{ $opponent->name }} ({{ $opponent->is_cpu ? 'CPU' : 'Manager' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sim-label" for="kickoff_at">Anstoss</label>
                        <input id="kickoff_at" name="kickoff_at" type="datetime-local" class="sim-input" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="sim-label" for="message">Hinweis</label>
                        <input id="message" name="message" type="text" class="sim-input" maxlength="255" placeholder="Optionaler Kommentar">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="sim-btn-primary">Anfrage senden</button>
                    </div>
                </form>
            </article>

            <article class="sim-card p-5">
                <h2 class="text-lg font-bold text-white">Kommende Friendlies</h2>
                <div class="mt-3 space-y-2">
                    @forelse ($friendlyMatches as $match)
                        <a href="{{ route('matches.show', $match) }}" class="sim-card-soft block px-3 py-2">
                            <p class="text-sm font-semibold text-white">
                                {{ $match->homeClub->name }} vs {{ $match->awayClub->name }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">{{ $match->kickoff_at?->format('d.m.Y H:i') }} Uhr</p>
                        </a>
                    @empty
                        <p class="text-sm text-slate-300">Noch keine Freundschaftsspiele.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <article class="sim-card p-5">
                <h2 class="text-lg font-bold text-white">Ausgehende Anfragen</h2>
                <div class="mt-3 space-y-2">
                    @forelse ($outgoingRequests as $request)
                        <div class="sim-card-soft px-3 py-3">
                            <p class="text-sm font-semibold text-white">{{ $request->challengedClub->name }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $request->kickoff_at?->format('d.m.Y H:i') }} Uhr | {{ strtoupper($request->status) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-300">Keine ausgehenden Anfragen.</p>
                    @endforelse
                </div>
            </article>

            <article class="sim-card p-5">
                <h2 class="text-lg font-bold text-white">Eingehende Anfragen</h2>
                <div class="mt-3 space-y-2">
                    @forelse ($incomingRequests as $request)
                        <div class="sim-card-soft px-3 py-3">
                            <p class="text-sm font-semibold text-white">{{ $request->challengerClub->name }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $request->kickoff_at?->format('d.m.Y H:i') }} Uhr | {{ strtoupper($request->status) }}</p>
                            @if ($request->status === 'pending')
                                <div class="mt-3 flex gap-2">
                                    <form method="POST" action="{{ route('friendlies.accept', $request) }}">
                                        @csrf
                                        <button type="submit" class="sim-btn-primary !px-3 !py-1.5 text-xs">Annehmen</button>
                                    </form>
                                    <form method="POST" action="{{ route('friendlies.reject', $request) }}">
                                        @csrf
                                        <button type="submit" class="sim-btn-danger !px-3 !py-1.5 text-xs">Ablehnen</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-300">Keine eingehenden Anfragen.</p>
                    @endforelse
                </div>
            </article>
        </section>
    @endif
</x-app-layout>
