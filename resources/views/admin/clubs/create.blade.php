<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">ACP Vereine</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Verein erstellen</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.clubs.store') }}" class="sim-card p-6" enctype="multipart/form-data">
        @include('admin.clubs._form')

        <div class="mt-6 flex flex-wrap gap-2">
            <button class="sim-btn-primary" type="submit">Erstellen</button>
            <a class="sim-btn-muted" href="{{ route('admin.clubs.index') }}">Zurueck</a>
        </div>
    </form>
</x-app-layout>
