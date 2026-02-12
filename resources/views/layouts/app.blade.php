@php
    $menuItems = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'active' => 'dashboard'],
        ['route' => 'clubs.index', 'label' => 'Vereine', 'active' => 'clubs.*'],
        ['route' => 'players.index', 'label' => 'Spieler', 'active' => 'players.*'],
        ['route' => 'lineups.index', 'label' => 'Aufstellung', 'active' => 'lineups.*'],
        ['route' => 'league.matches', 'label' => 'Spiele', 'active' => 'league.matches'],
        ['route' => 'friendlies.index', 'label' => 'Freundschaft', 'active' => 'friendlies.*'],
        ['route' => 'league.table', 'label' => 'Tabelle', 'active' => 'league.table'],
        ['route' => 'transfers.index', 'label' => 'Transfermarkt', 'active' => 'transfers.*'],
        ['route' => 'loans.index', 'label' => 'Leihen', 'active' => 'loans.*'],
        ['route' => 'contracts.index', 'label' => 'Vertraege', 'active' => 'contracts.*'],
        ['route' => 'sponsors.index', 'label' => 'Sponsoren', 'active' => 'sponsors.*'],
        ['route' => 'stadium.index', 'label' => 'Stadion', 'active' => 'stadium.*'],
        ['route' => 'training-camps.index', 'label' => 'Trainingslager', 'active' => 'training-camps.*'],
        ['route' => 'training.index', 'label' => 'Training', 'active' => 'training.*'],
        ['route' => 'finances.index', 'label' => 'Finanzen', 'active' => 'finances.*'],
        ['route' => 'national-teams.index', 'label' => 'Nationalteams', 'active' => 'national-teams.*'],
        ['route' => 'team-of-the-day.index', 'label' => 'Team of the Day', 'active' => 'team-of-the-day.*'],
        ['route' => 'random-events.index', 'label' => 'Random Events', 'active' => 'random-events.*'],
        ['route' => 'notifications.index', 'label' => 'Benachrichtigungen', 'active' => 'notifications.*'],
        ['route' => 'profile.edit', 'label' => 'Profil', 'active' => 'profile.*'],
    ];

    if (auth()->user()?->isAdmin()) {
        $menuItems[] = ['route' => 'admin.dashboard', 'label' => 'ACP', 'active' => 'admin.*'];
    }
@endphp
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
        <div class="min-h-screen lg:flex">
            <aside class="hidden w-72 border-r border-slate-800/80 bg-slate-950/75 p-5 lg:flex lg:flex-col">
                <a href="{{ route('dashboard') }}" class="sim-card-soft flex items-center justify-between p-4">
                    <div>
                        <p class="text-lg font-bold leading-tight">OpenWS Laravell</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-cyan-400 to-indigo-500"></div>
                </a>

                <nav class="mt-6 space-y-2">
                    @foreach ($menuItems as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            class="sim-nav-item {{ request()->routeIs($item['active']) ? 'sim-nav-item-active' : '' }}"
                        >
                            <span>{{ $item['label'] }}</span>
                            <span class="text-xs text-slate-500">-></span>
                        </a>
                    @endforeach
                </nav>

                <div class="mt-auto space-y-3">
                    <div class="sim-card-soft p-4">
                        <p class="text-xs uppercase tracking-[0.15em] text-slate-400">Angemeldet als</p>
                        <p class="mt-2 text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="sim-btn-muted w-full" type="submit">Logout</button>
                    </form>
                </div>
            </aside>

            <div class="flex min-h-screen flex-1 flex-col">
                <header class="sticky top-0 z-10 border-b border-slate-800/80 bg-slate-950/70 backdrop-blur">
                    <div class="px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Dashboard</p>
                                <p class="text-xl font-bold text-white sm:text-2xl">Hallo, {{ auth()->user()->name }}</p>
                            </div>
                            <div class="hidden gap-2 sm:flex">
                                <a href="{{ route('lineups.index') }}" class="sim-btn-muted">Aufstellung</a>
                                <a href="{{ route('players.index') }}" class="sim-btn-muted">Kader</a>
                                <a href="{{ route('transfers.index') }}" class="sim-btn-muted">Transfers</a>
                                <a href="{{ route('loans.index') }}" class="sim-btn-muted">Leihen</a>
                                <a href="{{ route('stadium.index') }}" class="sim-btn-muted">Stadion</a>
                                @if (auth()->user()?->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="sim-btn-muted {{ request()->routeIs('admin.*') ? '!border-cyan-400/50 !text-cyan-100' : '' }}">ACP</a>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2 overflow-x-auto pb-1 lg:hidden">
                            @foreach ($menuItems as $item)
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="sim-btn-muted shrink-0 {{ request()->routeIs($item['active']) ? '!border-cyan-400/50 !text-cyan-100' : '' }}"
                                >
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </header>

                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-7xl space-y-6">
                        @if (session('status'))
                            <div class="sim-card border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                                {{ session('status') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="sim-card border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        @isset($header)
                            <div>{{ $header }}</div>
                        @endisset

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
