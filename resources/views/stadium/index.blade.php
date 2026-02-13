<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="sim-section-title">Stadion</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Stadion & Umfeld</h1>
            </div>
            @if ($clubs->isNotEmpty())
                <form method="GET" action="{{ route('stadium.index') }}">
                    <select class="sim-select" name="club" onchange="this.form.submit()">
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}" @selected($activeClub && $activeClub->id === $club->id)>
                                {{ $club->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </x-slot>

    @if (!$activeClub || !$stadium)
        <section class="sim-card p-8 text-center">
            <p class="text-slate-300">Kein Stadion verfuegbar.</p>
        </section>
    @else
        <section class="sim-card p-4">
            <p class="flex items-center gap-2 text-sm text-slate-300">
                <img class="sim-avatar sim-avatar-sm" src="{{ $activeClub->logo_url }}" alt="{{ $activeClub->name }}">
                <span>{{ $activeClub->name }} | {{ $stadium->name }}</span>
            </p>
        </section>
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="sim-card p-5">
                <p class="sim-section-title">Kapazitaet</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ number_format($stadium->capacity, 0, ',', '.') }}</p>
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Ticketpreis</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ number_format((float) $stadium->ticket_price, 2, ',', '.') }} EUR</p>
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Rasen</p>
                <p class="mt-2 text-3xl font-bold text-cyan-300">{{ $stadium->pitch_quality }}</p>
            </article>
            <article class="sim-card p-5">
                <p class="sim-section-title">Fan-Erlebnis</p>
                <p class="mt-2 text-3xl font-bold text-cyan-300">{{ $stadium->fan_experience }}</p>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            <article class="sim-card p-5 xl:col-span-2">
                <p class="sim-section-title">Infrastrukturwerte</p>
                <div class="mt-3 grid gap-2 md:grid-cols-2">
                    <div class="sim-card-soft p-3 text-sm">
                        <p class="font-semibold text-white">Anlagen-Level</p>
                        <p class="text-slate-300">{{ $stadium->facility_level }}</p>
                    </div>
                    <div class="sim-card-soft p-3 text-sm">
                        <p class="font-semibold text-white">Sicherheit</p>
                        <p class="text-slate-300">{{ $stadium->security_level }}</p>
                    </div>
                    <div class="sim-card-soft p-3 text-sm">
                        <p class="font-semibold text-white">Umfeld</p>
                        <p class="text-slate-300">{{ $stadium->environment_level }}</p>
                    </div>
                    <div class="sim-card-soft p-3 text-sm">
                        <p class="font-semibold text-white">Wartungskosten/Monat</p>
                        <p class="text-slate-300">{{ number_format((float) $stadium->maintenance_cost, 2, ',', '.') }} EUR</p>
                    </div>
                </div>
            </article>

            <article class="sim-card p-5">
                <p class="sim-section-title">Projekt starten</p>
                <form method="POST" action="{{ route('stadium.projects.store') }}" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="club_id" value="{{ $activeClub->id }}">
                    <select class="sim-select" name="project_type" required>
                        @foreach ($projectTypes as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="sim-btn-primary w-full" type="submit">Projekt starten</button>
                </form>
            </article>
        </section>

        <section class="sim-card overflow-x-auto">
            <table class="sim-table min-w-full">
                <thead>
                    <tr>
                        <th>Typ</th>
                        <th>Von</th>
                        <th>Zu</th>
                        <th>Kosten</th>
                        <th>Start</th>
                        <th>Fertig</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr>
                            <td>{{ $project->project_type }}</td>
                            <td>{{ $project->level_from ?? '-' }}</td>
                            <td>{{ $project->level_to ?? '-' }}</td>
                            <td>{{ number_format((float) $project->cost, 2, ',', '.') }} EUR</td>
                            <td>{{ $project->started_on?->format('d.m.Y') }}</td>
                            <td>{{ $project->completes_on?->format('d.m.Y') }}</td>
                            <td>{{ $project->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-300">Keine Projekte vorhanden.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif
</x-app-layout>
