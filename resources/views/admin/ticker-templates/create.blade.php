<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">ACP Liveticker</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Neue Vorlage erstellen</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.ticker-templates.index') }}" class="sim-btn-muted">
                    Zurück zur Übersicht
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto py-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
            <form action="{{ route('admin.ticker-templates.store') }}" method="POST">
                @include('admin.ticker-templates._form')
            </form>
        </div>
    </div>
</x-app-layout>