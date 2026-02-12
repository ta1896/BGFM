<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">Sponsoren</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Sponsorvertraege</h1>
            </div>
            @if ($clubs->isNotEmpty())
                <form method="GET" action="{{ route('sponsors.index') }}">
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
        <section class="sim-card p-8 text-center">
            <p class="text-slate-300">Kein Verein vorhanden.</p>
        </section>
    @else
        <section class="grid gap-4 xl:grid-cols-3">
            <article class="sim-card p-5">
                <p class="sim-section-title">Aktiver Sponsor</p>
                @if ($activeContract)
                    <p class="mt-2 text-xl font-bold text-white">{{ $activeContract->sponsor->name }}</p>
                    <p class="mt-2 text-sm text-slate-300">
                        {{ number_format((float) $activeContract->weekly_amount, 2, ',', '.') }} EUR / Woche
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        Laufzeit bis {{ $activeContract->ends_on?->format('d.m.Y') }}
                    </p>
                    <form method="POST" action="{{ route('sponsors.contracts.terminate', $activeContract) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="sim-btn-danger">Vertrag beenden</button>
                    </form>
                @else
                    <p class="mt-2 text-sm text-slate-300">Kein aktiver Sponsorvertrag.</p>
                @endif
            </article>

            <article class="sim-card p-5 xl:col-span-2">
                <p class="sim-section-title">Verfuegbare Sponsoren</p>
                @if ($offers->isEmpty())
                    <p class="mt-2 text-sm text-slate-300">Keine Sponsoren verfuegbar.</p>
                @else
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        @foreach ($offers as $offer)
                            <div class="sim-card-soft p-4">
                                <p class="font-semibold text-white">{{ $offer->name }}</p>
                                <p class="mt-1 text-sm text-slate-300">
                                    {{ strtoupper($offer->tier) }} | min. Reputation {{ $offer->reputation_min }}
                                </p>
                                <p class="mt-2 text-sm text-cyan-200">
                                    Basis {{ number_format((float) $offer->base_weekly_amount, 2, ',', '.') }} EUR/Woche
                                </p>
                                <form method="POST" action="{{ route('sponsors.sign', $offer) }}" class="mt-3 flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="club_id" value="{{ $activeClub->id }}">
                                    <input type="number" class="sim-input w-24" name="months" min="1" max="60" value="12" required>
                                    <button type="submit" class="sim-btn-primary" @disabled($activeContract !== null)>
                                        Unterzeichnen
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        </section>

        <section class="sim-card overflow-x-auto">
            <table class="sim-table min-w-full">
                <thead>
                    <tr>
                        <th>Sponsor</th>
                        <th>Status</th>
                        <th>Woche</th>
                        <th>Bonus</th>
                        <th>Start</th>
                        <th>Ende</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($history as $contract)
                        <tr>
                            <td>{{ $contract->sponsor->name }}</td>
                            <td>{{ $contract->status }}</td>
                            <td>{{ number_format((float) $contract->weekly_amount, 2, ',', '.') }} EUR</td>
                            <td>{{ number_format((float) $contract->signing_bonus, 2, ',', '.') }} EUR</td>
                            <td>{{ $contract->starts_on?->format('d.m.Y') }}</td>
                            <td>{{ $contract->ends_on?->format('d.m.Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-300">Noch keine Sponsorhistorie.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif
</x-app-layout>
