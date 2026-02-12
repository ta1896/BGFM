<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="sim-section-title">Spielerprofil</p>
                <h1 class="mt-1 text-2xl font-bold text-white">{{ $player->full_name }}</h1>
                <p class="mt-1 text-sm text-slate-300">{{ $player->club->name }} | {{ $player->position }}</p>
            </div>
            <a href="{{ route('players.edit', $player) }}" class="sim-btn-muted">Bearbeiten</a>
        </div>
    </x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="sim-card p-5">
            <p class="sim-section-title">Overall</p>
            <p class="mt-2 text-3xl font-bold text-cyan-300">{{ $player->overall }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Alter</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $player->age }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Marktwert</p>
            <p class="mt-2 text-xl font-bold text-white">{{ number_format((float) $player->market_value, 0, ',', '.') }} EUR</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Gehalt</p>
            <p class="mt-2 text-xl font-bold text-white">{{ number_format((float) $player->salary, 0, ',', '.') }} EUR</p>
        </article>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article class="sim-card p-5">
            <p class="sim-section-title">Tempo</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $player->pace }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Schuss</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $player->shooting }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Pass</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $player->passing }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Defensive</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $player->defending }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Physis</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $player->physical }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Form</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $player->stamina }} / {{ $player->morale }}</p>
        </article>
    </section>

    <form method="POST" action="{{ route('players.destroy', $player) }}" class="sim-card border-rose-400/30 bg-rose-500/10 p-5">
        @csrf
        @method('DELETE')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-rose-100/90">Spieler dauerhaft entfernen</p>
            <button class="sim-btn-danger" type="submit" onclick="return confirm('Spieler wirklich loeschen?')">Spieler loeschen</button>
        </div>
    </form>
</x-app-layout>
