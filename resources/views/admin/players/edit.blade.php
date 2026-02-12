<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title">ACP Spieler</p>
                <h1 class="mt-1 text-2xl font-bold text-white">{{ $player->full_name }} bearbeiten</h1>
            </div>
            <a href="{{ route('admin.players.index') }}" class="sim-btn-muted">Liste</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.players.update', $player) }}" class="sim-card p-6" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.players._form')

        <div class="mt-6 flex flex-wrap gap-2">
            <button class="sim-btn-primary" type="submit">Speichern</button>
            <a class="sim-btn-muted" href="{{ route('admin.players.index') }}">Zurueck</a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.players.destroy', $player) }}" class="sim-card mt-4 border-rose-400/30 bg-rose-500/10 p-5">
        @csrf
        @method('DELETE')
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-rose-100/90">Spieler loeschen</p>
            <button type="submit" class="sim-btn-danger" onclick="return confirm('Spieler wirklich loeschen?')">Loeschen</button>
        </div>
    </form>
</x-app-layout>
