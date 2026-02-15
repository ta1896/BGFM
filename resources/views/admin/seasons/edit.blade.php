<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title">ACP Zeitraeume</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Saison {{ $season->name }} bearbeiten</h1>
            </div>
            <a href="{{ route('admin.seasons.index') }}" class="sim-btn-muted">Abbrechen</a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('admin.seasons.update', $season) }}" method="POST" class="sim-card p-6 space-y-4">
            @csrf
            @method('PUT')
            @include('admin.seasons._form')

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <a href="{{ route('admin.seasons.index') }}" class="sim-btn-muted">Abbrechen</a>
                <button type="submit" class="sim-btn-primary">Saison aktualisieren</button>
            </div>
        </form>
    </div>
</x-app-layout>