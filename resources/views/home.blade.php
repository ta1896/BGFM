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
                    <p class="text-xs uppercase tracking-[0.2em] text-cyan-300">Fussball Manager</p>
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

            <main class="mt-8 space-y-6">
                <section class="sim-card p-6 sm:p-8">
                    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                        <div>
                            <p class="sim-section-title">Dein Verein. Dein Spieltag. Deine Entscheidungen.</p>
                            <h1 class="mt-3 text-4xl font-bold leading-tight text-white sm:text-5xl">
                                Starte deinen eigenen Fussball-Manager mit Matchcenter, Taktik und Transfers.
                            </h1>
                            <p class="mt-4 max-w-2xl text-slate-300">
                                Stelle deine erste Elf auf, steuere den Kader, simuliere Spieltage und entwickle deinen Club Saison fuer Saison.
                            </p>
                            <div class="mt-6 flex flex-wrap gap-2">
                                <span class="sim-pill">Matchday Engine</span>
                                <span class="sim-pill">Aufstellung & Taktik</span>
                                <span class="sim-pill">Transfermarkt</span>
                                <span class="sim-pill">Finanzen</span>
                            </div>
                            <div class="mt-8 flex flex-wrap gap-2">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="sim-btn-primary">Zum Manager-Dashboard</a>
                                    <a href="{{ route('league.matches') }}" class="sim-btn-muted">Matchcenter oeffnen</a>
                                @else
                                    <a href="{{ route('register') }}" class="sim-btn-primary">Managerkonto erstellen</a>
                                    <a href="{{ route('login') }}" class="sim-btn-muted">Einloggen</a>
                                @endauth
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="sim-card-soft p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Naechstes Topspiel</p>
                                <div class="mt-3 grid grid-cols-[1fr_auto_1fr] items-center gap-2">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Heim</p>
                                        <p class="text-sm font-semibold text-white">Northbridge FC</p>
                                    </div>
                                    <p class="text-xl font-bold text-cyan-200">vs</p>
                                    <div class="text-right">
                                        <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Auswaerts</p>
                                        <p class="text-sm font-semibold text-white">Union Hafenstadt</p>
                                    </div>
                                </div>
                                <p class="mt-3 text-xs text-slate-300">Spieltag 12 | Samstag 18:30 | Matchcenter live</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="sim-card-soft p-3">
                                    <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Transferfenster</p>
                                    <p class="mt-1 text-sm font-semibold text-emerald-300">Geoeffnet</p>
                                </div>
                                <div class="sim-card-soft p-3">
                                    <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Liga-Takt</p>
                                    <p class="mt-1 text-sm font-semibold text-cyan-200">Spieltag-Modus</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-3">
                    <article class="sim-card p-5">
                        <p class="sim-section-title">Kadersteuerung</p>
                        <h3 class="mt-2 text-lg font-semibold text-white">11er-Aufstellung und Rollen</h3>
                        <p class="mt-2 text-sm text-slate-300">
                            Definiere Startelf, Bank, Kapitaen und Standardschuetzen fuer jedes Match.
                        </p>
                    </article>
                    <article class="sim-card p-5">
                        <p class="sim-section-title">Transferphase</p>
                        <h3 class="mt-2 text-lg font-semibold text-white">Kaufen, verkaufen, verleihen</h3>
                        <p class="mt-2 text-sm text-slate-300">
                            Steuere dein Budget mit Listings, Geboten, Leihen und Vertragsentscheidungen.
                        </p>
                    </article>
                    <article class="sim-card p-5">
                        <p class="sim-section-title">Saisonbetrieb</p>
                        <h3 class="mt-2 text-lg font-semibold text-white">Tabelle, Form und Finanzen</h3>
                        <p class="mt-2 text-sm text-slate-300">
                            Verfolge Spieltage, Einnahmen und Teamleistung ueber die komplette Saison.
                        </p>
                    </article>
                </section>
            </main>
        </div>
    </body>
</html>
