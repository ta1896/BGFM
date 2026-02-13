<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Trainingslager</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Planung und Verlauf</h1>
        </div>
    </x-slot>

    <section class="grid gap-4 xl:grid-cols-3">
        <article class="sim-card p-5 xl:col-span-2">
            <h2 class="text-lg font-semibold text-white">Aktive & geplante Lager</h2>
            @if ($camps->isEmpty())
                <p class="mt-3 text-sm text-slate-300">Noch keine Trainingslager vorhanden.</p>
            @else
                <div class="mt-3 space-y-3">
                    @foreach ($camps as $camp)
                        <div class="sim-card-soft p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-white">{{ $camp->name }}</p>
                                    <p class="mt-1 flex items-center gap-2 text-sm text-slate-300">
                                        <img class="sim-avatar sim-avatar-xs" src="{{ $camp->club->logo_url }}" alt="{{ $camp->club->name }}">
                                        <span>{{ $camp->club->name }} | {{ ucfirst($camp->focus) }} | {{ ucfirst($camp->intensity) }}</span>
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $camp->starts_on?->format('d.m.Y') }} - {{ $camp->ends_on?->format('d.m.Y') }}
                                    </p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="sim-pill">Kosten {{ number_format((float) $camp->cost, 0, ',', '.') }} EUR</span>
                                    <span class="sim-pill">{{ $camp->status }}</span>
                                </div>
                            </div>
                            <div class="mt-2 text-xs text-slate-300">
                                Effekte: AUS {{ $camp->stamina_effect >= 0 ? '+' : '' }}{{ $camp->stamina_effect }},
                                MOR {{ $camp->morale_effect >= 0 ? '+' : '' }}{{ $camp->morale_effect }},
                                OVR {{ $camp->overall_effect >= 0 ? '+' : '' }}{{ $camp->overall_effect }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $camps->links() }}</div>
            @endif
        </article>

        <article class="sim-card p-5">
            <h2 class="text-lg font-semibold text-white">Neues Trainingslager</h2>
            <form method="POST" action="{{ route('training-camps.store') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="sim-label" for="club_id">Verein</label>
                    <select id="club_id" name="club_id" class="sim-select" required>
                        <option value="">Auswaehlen</option>
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}">{{ $club->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sim-label" for="name">Name</label>
                    <input id="name" name="name" class="sim-input" type="text" maxlength="120" required>
                </div>
                <div>
                    <label class="sim-label" for="focus">Fokus</label>
                    <select id="focus" name="focus" class="sim-select" required>
                        <option value="fitness">Fitness</option>
                        <option value="tactics">Taktik</option>
                        <option value="technical">Technik</option>
                        <option value="team_building">Team Building</option>
                    </select>
                </div>
                <div>
                    <label class="sim-label" for="intensity">Intensitaet</label>
                    <select id="intensity" name="intensity" class="sim-select" required>
                        <option value="low">Niedrig</option>
                        <option value="medium" selected>Mittel</option>
                        <option value="high">Hoch</option>
                    </select>
                </div>
                <div class="grid gap-2 sm:grid-cols-2">
                    <div>
                        <label class="sim-label" for="starts_on">Start</label>
                        <input id="starts_on" name="starts_on" class="sim-input" type="date" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div>
                        <label class="sim-label" for="ends_on">Ende</label>
                        <input id="ends_on" name="ends_on" class="sim-input" type="date" value="{{ now()->addDays(5)->toDateString() }}" required>
                    </div>
                </div>
                <div>
                    <label class="sim-label" for="notes">Notiz</label>
                    <textarea id="notes" name="notes" class="sim-textarea"></textarea>
                </div>
                <button class="sim-btn-primary w-full" type="submit">Trainingslager anlegen</button>
            </form>
        </article>
    </section>
</x-app-layout>
