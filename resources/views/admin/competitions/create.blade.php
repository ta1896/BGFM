<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">ACP Ligen & Pokale</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Liga/Pokal erstellen</h1>
        </div>
    </x-slot>

    @php($competition = $competition ?? null)

    <form method="POST" action="{{ route('admin.competitions.store') }}" class="sim-card p-6" enctype="multipart/form-data">
        @include('admin.competitions._form')

        <div class="mt-6 flex flex-wrap gap-2">
            <button class="sim-btn-primary" type="submit">Erstellen</button>
            <a class="sim-btn-muted" href="{{ route('admin.competitions.index') }}">Zurueck</a>
        </div>
    </form>
</x-app-layout>
