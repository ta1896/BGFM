<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">Finanzen</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Buchungen und Cashflow</h1>
            </div>
            @if ($clubs->isNotEmpty())
                <form method="GET" action="{{ route('finances.index') }}">
                    <select name="club" class="sim-select" onchange="this.form.submit()">
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
        <section class="sim-card p-5">
            <div class="mb-4 flex items-center justify-between">
                <p class="inline-flex items-center gap-2 text-lg font-semibold text-white">
                    <img class="sim-avatar sim-avatar-sm" src="{{ $activeClub->logo_url }}" alt="{{ $activeClub->name }}">
                    <span>{{ $activeClub->name }}</span>
                </p>
                <div class="flex items-center gap-2">
                    <span class="sim-pill">Budget {{ number_format((float) $activeClub->budget, 2, ',', '.') }} EUR</span>
                    <span class="sim-pill">Coins {{ number_format((int) $activeClub->coins, 0, ',', '.') }}</span>
                </div>
            </div>

            @if ($transactions->isEmpty())
                <p class="text-sm text-slate-300">Keine Finanzbuchungen vorhanden.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="sim-table min-w-full">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Typ</th>
                                <th>Asset</th>
                                <th>Richtung</th>
                                <th>Betrag</th>
                                <th>Saldo</th>
                                <th>Notiz</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                @php
                                    $assetType = $transaction->asset_type ?? 'budget';
                                    $isCoin = $assetType === 'coins';
                                @endphp
                                <tr>
                                    <td>{{ $transaction->booked_at?->format('d.m.Y H:i') }}</td>
                                    <td>{{ $transaction->context_type }}</td>
                                    <td>{{ $assetType }}</td>
                                    <td>
                                        <span class="{{ $transaction->direction === 'income' ? 'text-emerald-300' : 'text-rose-300' }}">
                                            {{ $transaction->direction }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $isCoin
                                            ? number_format((float) $transaction->amount, 0, ',', '.')
                                            : number_format((float) $transaction->amount, 2, ',', '.') }}
                                        {{ $isCoin ? 'Coins' : 'EUR' }}
                                    </td>
                                    <td>
                                        @if ($transaction->balance_after !== null)
                                            {{ $isCoin
                                                ? number_format((float) $transaction->balance_after, 0, ',', '.')
                                                : number_format((float) $transaction->balance_after, 2, ',', '.') }}
                                            {{ $isCoin ? 'Coins' : 'EUR' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $transaction->note ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $transactions->links() }}</div>
            @endif
        </section>
    @endif
</x-app-layout>
