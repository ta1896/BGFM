<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Verein</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Neuen Verein anlegen</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('clubs.store') }}" class="sim-card p-6" enctype="multipart/form-data">
        @include('clubs._form')

        <div class="mt-6 flex flex-wrap gap-2">
            <button class="sim-btn-primary" type="submit">Verein speichern</button>
            <a href="{{ route('clubs.index') }}" class="sim-btn-muted">Abbrechen</a>
        </div>
    </form>
</x-app-layout>
