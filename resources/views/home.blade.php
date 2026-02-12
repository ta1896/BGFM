<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'OpenWS Laravell') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="sim-shell">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <header class="sim-card-soft flex items-center justify-between px-4 py-3">
                <div>
                    <p class="text-lg font-bold text-white">OpenWS Laravell</p>
                </div>
                <div class="flex gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="sim-btn-primary">Zum Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="sim-btn-muted">Login</a>
                        <a href="{{ route('register') }}" class="sim-btn-primary">Registrieren</a>
                    @endauth
                </div>
            </header>

            <main class="mt-8 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <section class="sim-card p-6 sm:p-8">
                    <p class="sim-section-title">Fussball Manager</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight text-white sm:text-5xl">
                        Basis Architektur fuer deine eigene Fussball-Simulation
                    </h1>
                    <p class="mt-4 max-w-2xl text-slate-300">
                        Laravel + Docker + MySQL mit Login/Register, Vereinen, Spielern, Aufstellung und einer ersten Team-Berechnung.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <span class="sim-pill">Laravel</span>
                        <span class="sim-pill">Docker</span>
                        <span class="sim-pill">MySQL</span>
                        <span class="sim-pill">Responsive UI</span>
                    </div>
                    <div class="mt-8">
                        @auth
                            <a href="{{ route('dashboard') }}" class="sim-btn-primary">Simulation starten</a>
                        @else
                            <a href="{{ route('register') }}" class="sim-btn-primary">Jetzt Account erstellen</a>
                        @endauth
                    </div>
                </section>

                <section class="sim-card p-6">
                    <p class="sim-section-title">Grundmodule</p>
                    <div class="mt-4 space-y-3">
                        <article class="sim-card-soft p-4">
                            <h3 class="font-semibold text-white">Login / Register</h3>
                            <p class="mt-1 text-sm text-slate-300">Breeze-Auth, geschuetzte Routen und Profilverwaltung.</p>
                        </article>
                        <article class="sim-card-soft p-4">
                            <h3 class="font-semibold text-white">Vereine und Spieler</h3>
                            <p class="mt-1 text-sm text-slate-300">CRUD fuer Vereinsdaten, Budgets und Kader mit Attributen.</p>
                        </article>
                        <article class="sim-card-soft p-4">
                            <h3 class="font-semibold text-white">Aufstellung und Berechnung</h3>
                            <p class="mt-1 text-sm text-slate-300">11er-Aufstellung, aktive Formation und Teamstaerke-Score.</p>
                        </article>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
