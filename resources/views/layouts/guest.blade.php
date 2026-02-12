<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'OpenWS Laravell') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="sim-shell">
        <div class="mx-auto grid min-h-screen max-w-6xl items-center gap-6 px-4 py-8 lg:grid-cols-2 lg:gap-10">
            <section class="sim-card hidden p-8 lg:block">
                <p class="sim-section-title">Manager Konsole</p>
                <h1 class="mt-3 text-4xl font-bold text-white">OpenWS Laravell</h1>
                <p class="mt-4 text-slate-300">
                    Baue deinen Verein auf, verwalte den Kader, plane Aufstellungen und simuliere deine Teamleistung.
                </p>

                <div class="mt-8 space-y-3">
                    <div class="sim-card-soft p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Modul</p>
                        <p class="mt-1 text-base font-semibold text-white">Vereine und Spielerverwaltung</p>
                    </div>
                    <div class="sim-card-soft p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Modul</p>
                        <p class="mt-1 text-base font-semibold text-white">Aufstellung und Basis-Berechnung</p>
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
