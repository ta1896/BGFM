<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'OpenWS Laravell') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&family=Merriweather:wght@400;700;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $uiTheme = (string) session('dashboard.variant', 'modern');
        if (!in_array($uiTheme, ['modern', 'compact', 'classic'], true)) {
            $uiTheme = 'modern';
        }
    @endphp
    <body class="sim-shell" data-layout-theme="{{ $uiTheme }}">
        <div class="mx-auto grid min-h-screen max-w-6xl items-center gap-6 px-4 py-8 lg:grid-cols-2 lg:gap-10">
            <section class="sim-card hidden p-8 lg:block">
                <p class="sim-section-title">Matchday Hub</p>
                <h1 class="mt-3 text-4xl font-bold text-white">OpenWS Laravell</h1>
                <p class="mt-4 text-slate-300">Einloggen, Verein uebernehmen und direkt in den Spielbetrieb starten.</p>

                <div class="mt-6 sim-card-soft p-4">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Demo-Ansicht</p>
                    <div class="mt-2 grid grid-cols-[1fr_auto_1fr] items-center gap-2">
                        <div>
                            <p class="text-xs text-slate-400">Heim</p>
                            <p class="text-sm font-semibold text-white">OpenWS United</p>
                        </div>
                        <p class="text-lg font-bold text-cyan-200">2 : 1</p>
                        <div class="text-right">
                            <p class="text-xs text-slate-400">Auswaerts</p>
                            <p class="text-sm font-semibold text-white">FC Hafenblick</p>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-300">Spieltag 12 | 84. Minute | Live-Ticker bereit</p>
                </div>

                <div class="mt-6 space-y-3">
                    <div class="sim-card-soft p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Modul</p>
                        <p class="mt-1 text-base font-semibold text-white">Kader, Formation, Matchplan</p>
                    </div>
                    <div class="sim-card-soft p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Modul</p>
                        <p class="mt-1 text-base font-semibold text-white">Transfermarkt, Leihen, Vertraege</p>
                    </div>
                </div>
            </section>

            <section class="sim-card p-6 sm:p-8">
                <div class="mb-6 flex items-center justify-between">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-white">OpenWS Laravell</a>
                    <a href="{{ route('home') }}" class="text-sm text-cyan-300 hover:text-cyan-200">Startseite</a>
                </div>
                {{ $slot }}
            </section>
        </div>
    </body>
</html>
