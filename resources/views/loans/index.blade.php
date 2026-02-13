<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Leihmarkt</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Leihangebote und aktive Leihen</h1>
            <p class="mt-1 text-sm text-slate-300">
                @if ($windowOpen)
                    Fenster offen: {{ $windowLabel ?? 'Transferfenster' }}
                @else
                    {{ $windowMessage }}
                @endif
            </p>
        </div>
    </x-slot>

    <section class="grid gap-4 xl:grid-cols-3">
        <article class="sim-card p-5 xl:col-span-2">
            <h2 class="text-lg font-semibold text-white">Offene Leihlistings</h2>
            @if ($listings->isEmpty())
                <p class="mt-3 text-sm text-slate-300">Keine offenen Leihangebote.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($listings as $listing)
                        <div class="sim-card-soft p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <img class="sim-avatar sim-avatar-sm" src="{{ $listing->player->photo_url }}" alt="{{ $listing->player->full_name }}">
                                        <p class="font-semibold text-white">{{ $listing->player->full_name }}</p>
                                    </div>
                                    <p class="mt-1 flex items-center gap-2 text-sm text-slate-300">
                                        <img class="sim-avatar sim-avatar-xs" src="{{ $listing->lenderClub->logo_url }}" alt="{{ $listing->lenderClub->name }}">
                                        <span>{{ $listing->lenderClub->name }} | OVR {{ $listing->player->overall }}</span>
                                    </p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span class="sim-pill">Min/Woche {{ number_format((float) $listing->min_weekly_fee, 0, ',', '.') }} EUR</span>
                                        <span class="sim-pill">Laufzeit {{ $listing->loan_months }} Monate</span>
                                        <span class="sim-pill">Bis {{ $listing->expires_at?->format('d.m.Y H:i') }}</span>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('loans.bids.store', $listing) }}" class="grid gap-2 sm:grid-cols-3">
                                    @csrf
                                    <select name="borrower_club_id" class="sim-select sm:col-span-1" required>
                                        <option value="">Mein Verein</option>
                                        @foreach ($myClubs as $club)
                                            <option value="{{ $club->id }}">{{ $club->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="weekly_fee" min="{{ (int) $listing->min_weekly_fee }}" step="0.01" class="sim-input sm:col-span-1" placeholder="Gebot/Woche" required>
                                    <button type="submit" class="sim-btn-primary sm:col-span-1" @disabled(!$windowOpen)>Bieten</button>
                                    <input type="text" name="message" maxlength="255" class="sim-input sm:col-span-3" placeholder="Nachricht (optional)">
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $listings->links() }}</div>
            @endif
        </article>

        <article class="sim-card p-5">
            <h2 class="text-lg font-semibold text-white">Spieler verleihen</h2>
            <form method="POST" action="{{ route('loans.listings.store') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="sim-label" for="player_id">Spieler</label>
                    <select id="player_id" name="player_id" class="sim-select" required>
                        <option value="">Auswaehlen</option>
                        @foreach ($myPlayers as $player)
                            <option value="{{ $player->id }}">{{ $player->full_name }} ({{ $player->club->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sim-label" for="min_weekly_fee">Min. Wochengebuehr</label>
                    <input id="min_weekly_fee" name="min_weekly_fee" type="number" step="0.01" min="0" class="sim-input" required>
                </div>
                <div>
                    <label class="sim-label" for="loan_months">Leihdauer (Monate)</label>
                    <input id="loan_months" name="loan_months" type="number" min="1" max="24" value="6" class="sim-input" required>
                </div>
                <div>
                    <label class="sim-label" for="buy_option_price">Kaufoption</label>
                    <input id="buy_option_price" name="buy_option_price" type="number" step="0.01" min="0" class="sim-input">
                </div>
                <div>
                    <label class="sim-label" for="duration_days">Listing-Laufzeit (Tage)</label>
                    <input id="duration_days" name="duration_days" type="number" min="1" max="21" value="7" class="sim-input" required>
                </div>
                <button class="sim-btn-primary w-full" type="submit" @disabled(!$windowOpen)>Leihlisting erstellen</button>
            </form>
        </article>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <article class="sim-card p-5">
            <h2 class="text-lg font-semibold text-white">Meine Leihlistings</h2>
            @if ($myListings->isEmpty())
                <p class="mt-3 text-sm text-slate-300">Noch keine eigenen Leihlistings.</p>
            @else
                <div class="mt-3 space-y-3">
                    @foreach ($myListings as $listing)
                        <div class="sim-card-soft p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="flex items-center gap-2 font-semibold text-white">
                                    <img class="sim-avatar sim-avatar-sm" src="{{ $listing->player->photo_url }}" alt="{{ $listing->player->full_name }}">
                                    <span>{{ $listing->player->full_name }}</span>
                                </p>
                                <span class="sim-pill">{{ $listing->status }}</span>
                            </div>
                            @if ($listing->status === 'open')
                                <form method="POST" action="{{ route('loans.listings.close', $listing) }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="sim-btn-danger !px-3 !py-1.5 text-xs">Schliessen</button>
                                </form>
                            @endif

                            @if ($listing->bids->isNotEmpty() && $listing->status === 'open')
                                <div class="mt-3 space-y-2">
                                    @foreach ($listing->bids as $bid)
                                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-700/70 px-3 py-2 text-sm">
                                            <div>
                                                <span class="inline-flex items-center gap-2 font-semibold text-white">
                                                    <img class="sim-avatar sim-avatar-xs" src="{{ $bid->borrowerClub->logo_url }}" alt="{{ $bid->borrowerClub->name }}">
                                                    {{ $bid->borrowerClub->name }}
                                                </span>
                                                <span class="text-slate-300"> {{ number_format((float) $bid->weekly_fee, 0, ',', '.') }} EUR/Woche</span>
                                            </div>
                                            @if ($bid->status === 'pending')
                                                <form method="POST" action="{{ route('loans.bids.accept', ['listing' => $listing, 'bid' => $bid]) }}">
                                                    @csrf
                                                    <button type="submit" class="sim-btn-primary !px-3 !py-1.5 text-xs" @disabled(!$windowOpen)>Akzeptieren</button>
                                                </form>
                                            @else
                                                <span class="sim-pill">{{ $bid->status }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="sim-card p-5">
            <h2 class="text-lg font-semibold text-white">Aktive Leihen</h2>
            @if ($activeLoans->isEmpty())
                <p class="mt-3 text-sm text-slate-300">Keine aktiven Leihen.</p>
            @else
                <div class="mt-3 space-y-2">
                    @foreach ($activeLoans as $loan)
                        <div class="sim-card-soft p-3">
                            <p class="flex items-center gap-2 font-semibold text-white">
                                <img class="sim-avatar sim-avatar-sm" src="{{ $loan->player->photo_url }}" alt="{{ $loan->player->full_name }}">
                                <span>{{ $loan->player->full_name }}</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-300">
                                <span class="inline-flex items-center gap-2">
                                    <img class="sim-avatar sim-avatar-xs" src="{{ $loan->lenderClub->logo_url }}" alt="{{ $loan->lenderClub->name }}">
                                    {{ $loan->lenderClub->name }}
                                </span>
                                -> 
                                <span class="inline-flex items-center gap-2">
                                    <img class="sim-avatar sim-avatar-xs" src="{{ $loan->borrowerClub->logo_url }}" alt="{{ $loan->borrowerClub->name }}">
                                    {{ $loan->borrowerClub->name }}
                                </span>
                                | bis {{ $loan->ends_on?->format('d.m.Y') }}
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="sim-pill">Kaufoption: {{ $loan->buy_option_price ? number_format((float) $loan->buy_option_price, 0, ',', '.') . ' EUR' : 'Keine' }}</span>
                                <span class="sim-pill">Status: {{ $loan->buy_option_state }}</span>
                            </div>
                            @if (in_array($loan->borrower_club_id, $myClubIds, true) && $loan->buy_option_state === 'pending' && $loan->buy_option_price)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('loans.option.exercise', $loan) }}">
                                        @csrf
                                        <button type="submit" class="sim-btn-primary !px-3 !py-1.5 text-xs" @disabled(!$windowOpen)>
                                            Kaufoption ziehen
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('loans.option.decline', $loan) }}">
                                        @csrf
                                        <button type="submit" class="sim-btn-muted !px-3 !py-1.5 text-xs">
                                            Kaufoption ablehnen
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
</x-app-layout>
