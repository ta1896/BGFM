<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="sim-section-title">Aufstellung</p>
                <h1 class="mt-1 text-2xl font-bold text-white">{{ $lineup->name }} ({{ $lineup->formation }})</h1>
                <p class="mt-1 text-sm text-slate-300">{{ $lineup->club->name }}</p>
            </div>
            <a href="{{ route('lineups.edit', $lineup) }}" class="sim-btn-muted">Bearbeiten</a>
        </div>
    </x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <article class="sim-card p-5">
            <p class="sim-section-title">Gesamt</p>
            <p class="mt-2 text-3xl font-bold text-cyan-300">{{ $metrics['overall'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Angriff</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $metrics['attack'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Mittelfeld</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $metrics['midfield'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Verteidigung</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $metrics['defense'] }}</p>
        </article>
        <article class="sim-card p-5">
            <p class="sim-section-title">Chemie</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $metrics['chemistry'] }}%</p>
        </article>
    </section>

    <section class="sim-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-white">Aufgestellte Spieler</h2>
            @if ($lineup->is_active)
                <span class="sim-pill">Aktive Aufstellung</span>
            @endif
        </div>
        <div class="grid gap-3 md:grid-cols-2">
            @forelse ($lineup->players as $player)
                <div class="sim-card-soft flex items-center justify-between px-4 py-3">
                    <div>
                        <p class="font-semibold text-white">{{ $player->full_name }}</p>
                        <p class="text-xs text-slate-400">{{ $player->position }} @if($player->pivot->pitch_position) | {{ $player->pivot->pitch_position }} @endif</p>
                    </div>
                    <span class="sim-pill">OVR {{ $player->overall }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-300">Noch keine Spieler zugewiesen.</p>
            @endforelse
        </div>
    </section>

    <section class="sim-card border-rose-400/30 bg-rose-500/10 p-5">
        <form method="POST" action="{{ route('lineups.destroy', $lineup) }}" class="flex flex-wrap items-center justify-between gap-3">
            @csrf
            @method('DELETE')
            <p class="text-sm text-rose-100/90">Diese Aufstellung loeschen</p>
            <button class="sim-btn-danger" type="submit" onclick="return confirm('Aufstellung wirklich loeschen?')">Loeschen</button>
        </form>
    </section>
</x-app-layout>
