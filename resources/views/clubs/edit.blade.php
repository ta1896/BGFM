<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Verein</p>
            <h1 class="mt-1 text-2xl font-bold text-white">{{ $club->name }} bearbeiten</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('clubs.update', $club) }}" class="sim-card p-6" enctype="multipart/form-data">
        @method('PUT')
        @include('clubs._form')

        <div class="mt-6 flex flex-wrap gap-2">
            <button class="sim-btn-primary" type="submit">Aenderungen speichern</button>
            <a href="{{ route('clubs.show', $club) }}" class="sim-btn-muted">Zurueck</a>
        </div>
    </form>
</x-app-layout>
