<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Kader</p>
            <h1 class="mt-1 text-2xl font-bold text-white">{{ $player->full_name }} bearbeiten</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('players.update', $player) }}" class="sim-card p-6">
        @method('PUT')
        @include('players._form')
        <div class="mt-6 flex flex-wrap gap-2">
            <button type="submit" class="sim-btn-primary">Aenderungen speichern</button>
            <a href="{{ route('players.show', $player) }}" class="sim-btn-muted">Zurueck</a>
        </div>
    </form>
</x-app-layout>
