<x-app-layout>
    <x-slot name="header">
        <div class="sim-card p-5 sm:p-6">
            <p class="sim-section-title">Vereinswahl</p>
            <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Freie Vereine</h1>
            <p class="mt-2 text-sm text-slate-300">
                Uebernimm einen verfuegbaren Verein, um das volle Manager-Menue freizuschalten.
            </p>
        </div>
    </x-slot>

    @if ($hasOwnedClub)
        <section class="sim-card p-6">
            <p class="text-sm text-slate-300">Du verwaltest bereits einen Verein.</p>
            <a href="{{ route('dashboard') }}" class="sim-btn-muted mt-4">Zum Dashboard</a>
        </section>
    @elseif ($freeClubs->isEmpty())
        <section class="sim-card p-8 text-center">
            <h2 class="text-xl font-bold text-white">Aktuell keine freien Vereine</h2>
            <p class="mt-2 text-sm text-slate-300">
                Bitte warte, bis ein Verein freigegeben wurde, oder kontaktiere einen Administrator.
            </p>
        </section>
    @else
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($freeClubs as $club)
                <article class="sim-card p-5">
                    <p class="sim-section-title">{{ $club->league }}</p>
                    <div class="mt-2 flex items-center gap-3">
                        <img class="sim-avatar sim-avatar-md" src="{{ $club->logo_url }}" alt="{{ $club->name }}">
                        <h2 class="text-xl font-bold text-white">{{ $club->name }}</h2>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="sim-pill">{{ $club->country }}</span>
                        <span class="sim-pill">Reputation {{ $club->reputation }}</span>
                        <span class="sim-pill">{{ $club->players_count }} Spieler</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-300">
                        Budget {{ number_format((float) $club->budget, 2, ',', '.') }} EUR
                    </p>
                    <form method="POST" action="{{ route('clubs.claim', $club) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="sim-btn-primary w-full">Verein uebernehmen</button>
                    </form>
                </article>
            @endforeach
        </section>

        <div>
            {{ $freeClubs->links() }}
        </div>
    @endif
</x-app-layout>
