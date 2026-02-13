<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">Transfermarkt</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Angebote, Gebote und Abschluesse</h1>
                <p class="mt-1 text-sm text-slate-300">
                    @if ($windowOpen)
                        Fenster offen: {{ $windowLabel ?? 'Transferfenster' }}
                    @else
                        {{ $windowMessage }}
                    @endif
                </p>
            </div>
        </div>
    </x-slot>

    <section class="grid gap-4 xl:grid-cols-3">
        <article class="sim-card p-5 xl:col-span-2">
            <h2 class="text-lg font-semibold text-white">Offene Listings</h2>
            @if ($listings->isEmpty())
                <p class="mt-4 text-sm text-slate-300">Aktuell sind keine offenen Angebote vorhanden.</p>
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
                                        <img class="sim-avatar sim-avatar-xs" src="{{ $listing->player->club->logo_url }}" alt="{{ $listing->player->club->name }}">
                                        <span>{{ $listing->player->club->name }} | OVR {{ $listing->player->overall }}</span>
                                    </p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span class="sim-pill">Min {{ number_format((float) $listing->min_price, 0, ',', '.') }} EUR</span>
                                        @if ($listing->buy_now_price)
                                            <span class="sim-pill">Sofort {{ number_format((float) $listing->buy_now_price, 0, ',', '.') }} EUR</span>
                                        @endif
                                        <span class="sim-pill">Bis {{ $listing->expires_at?->format('d.m.Y H:i') }}</span>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('transfers.bids.store', $listing) }}" class="grid gap-2 sm:grid-cols-3">
                                    @csrf
                                    <select name="bidder_club_id" class="sim-select sm:col-span-1" required>
                                        <option value="">Mein Verein</option>
                                        @foreach ($myClubs as $club)
                                            <option value="{{ $club->id }}">{{ $club->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="amount" min="{{ (int) $listing->min_price }}" step="0.01" class="sim-input sm:col-span-1" placeholder="Gebot in EUR" required>
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
            <h2 class="text-lg font-semibold text-white">Spieler listen</h2>
            <form method="POST" action="{{ route('transfers.listings.store') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="sim-label" for="player_id">Spieler</label>
                    <select id="player_id" name="player_id" class="sim-select" required>
                        <option value="">Auswaehlen</option>
                        @foreach ($myPlayers as $player)
                            <option value="{{ $player->id }}">
                                {{ $player->full_name }} ({{ $player->club->name }} | OVR {{ $player->overall }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sim-label" for="min_price">Mindestpreis (EUR)</label>
                    <input id="min_price" name="min_price" type="number" min="1" step="0.01" class="sim-input" required>
                </div>
                <div>
                    <label class="sim-label" for="buy_now_price">Sofortkauf (EUR)</label>
                    <input id="buy_now_price" name="buy_now_price" type="number" min="1" step="0.01" class="sim-input">
                </div>
                <div>
                    <label class="sim-label" for="duration_days">Laufzeit (Tage)</label>
                    <input id="duration_days" name="duration_days" type="number" min="1" max="21" value="7" class="sim-input" required>
                </div>
                <button type="submit" class="sim-btn-primary w-full" @disabled(!$windowOpen)>Listing erstellen</button>
            </form>
        </article>
    </section>

    <section class="sim-card p-5">
        <h2 class="text-lg font-semibold text-white">Meine Listings</h2>
        @if ($myListings->isEmpty())
            <p class="mt-4 text-sm text-slate-300">Du hast noch keine eigenen Listings.</p>
        @else
            <div class="mt-4 space-y-3">
                @foreach ($myListings as $listing)
                        <div class="sim-card-soft p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <img class="sim-avatar sim-avatar-sm" src="{{ $listing->player->photo_url }}" alt="{{ $listing->player->full_name }}">
                                        <p class="font-semibold text-white">{{ $listing->player->full_name }}</p>
                                    </div>
                                    <p class="text-sm text-slate-300">Status: {{ $listing->status }} | Gebote: {{ $listing->bids->count() }}</p>
                                </div>
                            @if ($listing->status === 'open')
                                <form method="POST" action="{{ route('transfers.listings.close', $listing) }}">
                                    @csrf
                                    <button type="submit" class="sim-btn-danger">Schliessen</button>
                                </form>
                            @endif
                        </div>
                        @if ($listing->bids->isNotEmpty() && $listing->status === 'open')
                            <div class="mt-3 space-y-2">
                                @foreach ($listing->bids as $bid)
                                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-700/70 px-3 py-2 text-sm">
                                        <div>
                                            <span class="inline-flex items-center gap-2 font-semibold text-white">
                                                <img class="sim-avatar sim-avatar-xs" src="{{ $bid->bidderClub->logo_url }}" alt="{{ $bid->bidderClub->name }}">
                                                {{ $bid->bidderClub->name }}
                                            </span>
                                            <span class="text-slate-300">bietet {{ number_format((float) $bid->amount, 0, ',', '.') }} EUR</span>
                                        </div>
                                        @if ($bid->status === 'pending')
                                            <form method="POST" action="{{ route('transfers.bids.accept', ['listing' => $listing, 'bid' => $bid]) }}">
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
    </section>
</x-app-layout>
