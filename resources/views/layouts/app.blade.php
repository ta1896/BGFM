@php
    $user = auth()->user();
    $isAdmin = $user?->isAdmin() ?? false;
    $hasManagedClub = $isAdmin || $user?->clubs()->exists();
    $uiTheme = (string) session('dashboard.variant', 'modern');
    if (!in_array($uiTheme, ['modern', 'compact', 'classic'], true)) {
        $uiTheme = 'modern';
    }

    if ($hasManagedClub) {
        $primaryMenuItems = [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'active' => 'dashboard'],
            ['route' => 'league.matches', 'label' => 'Spiele', 'active' => 'league.matches'],
            ['route' => 'lineups.index', 'label' => 'Aufstellung', 'active' => 'lineups.*'],
            ['route' => 'players.index', 'label' => 'Spieler', 'active' => 'players.*'],
            ['route' => 'finances.index', 'label' => 'Finanzen', 'active' => 'finances.*'],
        ];

        $secondaryMenuItems = [
            ['route' => 'clubs.index', 'label' => 'Vereine', 'active' => 'clubs.*'],
            ['route' => 'friendlies.index', 'label' => 'Freundschaft', 'active' => 'friendlies.*'],
            ['route' => 'league.table', 'label' => 'Tabelle', 'active' => 'league.table'],
            ['route' => 'transfers.index', 'label' => 'Transfermarkt', 'active' => 'transfers.*'],
            ['route' => 'loans.index', 'label' => 'Leihen', 'active' => 'loans.*'],
            ['route' => 'contracts.index', 'label' => 'Vertraege', 'active' => 'contracts.*'],
            ['route' => 'sponsors.index', 'label' => 'Sponsoren', 'active' => 'sponsors.*'],
            ['route' => 'stadium.index', 'label' => 'Stadion', 'active' => 'stadium.*'],
            ['route' => 'training-camps.index', 'label' => 'Trainingslager', 'active' => 'training-camps.*'],
            ['route' => 'training.index', 'label' => 'Training', 'active' => 'training.*'],
            ['route' => 'national-teams.index', 'label' => 'Nationalteams', 'active' => 'national-teams.*'],
            ['route' => 'team-of-the-day.index', 'label' => 'Team of the Day', 'active' => 'team-of-the-day.*'],
            ['route' => 'random-events.index', 'label' => 'Random Events', 'active' => 'random-events.*'],
            ['route' => 'notifications.index', 'label' => 'Benachrichtigungen', 'active' => 'notifications.*'],
            ['route' => 'profile.edit', 'label' => 'Profil', 'active' => 'profile.*'],
        ];

        $headerActions = [
            ['route' => 'league.matches', 'label' => 'Spielplan', 'active' => 'league.matches', 'primary' => true],
            ['route' => 'lineups.index', 'label' => 'Aufstellung', 'active' => 'lineups.*', 'primary' => false],
            ['route' => 'notifications.index', 'label' => 'Inbox', 'active' => 'notifications.*', 'primary' => false],
        ];
    } else {
        $primaryMenuItems = [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'active' => 'dashboard'],
            ['route' => 'clubs.free', 'label' => 'Freie Vereine', 'active' => 'clubs.free'],
        ];

        $secondaryMenuItems = [
            ['route' => 'profile.edit', 'label' => 'Profil', 'active' => 'profile.*'],
        ];

        $headerActions = [
            ['route' => 'clubs.free', 'label' => 'Freie Vereine', 'active' => 'clubs.free', 'primary' => true],
        ];
    }

    if ($isAdmin) {
        $primaryMenuItems[] = ['route' => 'admin.dashboard', 'label' => 'ACP', 'active' => 'admin.*'];
        $headerActions[] = ['route' => 'admin.dashboard', 'label' => 'ACP', 'active' => 'admin.*', 'primary' => false];
    }

    $menuItems = array_merge($primaryMenuItems, $secondaryMenuItems);
    $secondaryActive = collect($secondaryMenuItems)->contains(
        fn (array $item): bool => request()->routeIs($item['active'])
    );
    $activeMenu = collect($menuItems)->first(
        fn (array $item): bool => request()->routeIs($item['active'])
    );
    $activeMenuLabel = $activeMenu['label'] ?? 'Dashboard';
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
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&family=Merriweather:wght@400;700;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="sim-shell" data-layout-theme="{{ $uiTheme }}">
        <div class="min-h-screen lg:flex">
            <aside class="hidden w-72 border-r border-slate-800/80 bg-slate-950/75 p-5 lg:flex lg:flex-col">
                <a href="{{ route('dashboard') }}" class="sim-card-soft flex items-center justify-between p-4">
                    <div>
                        <p class="text-lg font-bold leading-tight">OpenWS Laravell</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-400">Manager Console</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-cyan-400 to-indigo-500"></div>
                </a>

                <nav class="mt-6">
                    <p class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Hauptmenue</p>
                    <div class="space-y-1.5">
                        @foreach ($primaryMenuItems as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                class="sim-nav-item {{ request()->routeIs($item['active']) ? 'sim-nav-item-active' : '' }}"
                            >
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </nav>

                @if ($secondaryMenuItems !== [])
                    <details class="mt-4" @if ($secondaryActive) open @endif>
                        <summary class="sim-nav-item cursor-pointer list-none">
                            <span>Mehr Bereiche</span>
                            <span class="text-xs text-slate-500">+</span>
                        </summary>
                        <div class="mt-2 space-y-1.5 pl-2">
                            @foreach ($secondaryMenuItems as $item)
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="sim-nav-item {{ request()->routeIs($item['active']) ? 'sim-nav-item-active' : '' }}"
                                >
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endif

                <div class="mt-auto space-y-3">
                    <div class="sim-card-soft p-4">
                        <p class="text-xs uppercase tracking-[0.15em] text-slate-400">Angemeldet als</p>
                        <p class="mt-2 text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $isAdmin ? 'Administrator' : 'Manager' }}</p>
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
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Navigation</p>
                                <p class="text-xl font-bold text-white sm:text-2xl">{{ $activeMenuLabel }}</p>
                            </div>
                            <div class="hidden items-center gap-2 sm:flex">
                                @foreach (array_slice($headerActions, 0, 3) as $action)
                                    <a
                                        href="{{ route($action['route']) }}"
                                        class="{{ $action['primary'] ? 'sim-btn-primary' : 'sim-btn-muted' }} {{ request()->routeIs($action['active']) ? '!border-cyan-400/50 !text-cyan-100' : '' }}"
                                    >
                                        {{ $action['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4 grid gap-2 lg:hidden">
                            <label for="mobile-nav" class="sim-label mb-0">Schnellnavigation</label>
                            <select
                                id="mobile-nav"
                                class="sim-select"
                                onchange="if (this.value) { window.location.href = this.value; }"
                            >
                                @foreach ($menuItems as $item)
                                    <option value="{{ route($item['route']) }}" @selected(request()->routeIs($item['active']))>
                                        {{ $item['label'] }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="mt-1 flex gap-2 overflow-x-auto pb-1">
                                @foreach (array_slice($headerActions, 0, 2) as $action)
                                    <a
                                        href="{{ route($action['route']) }}"
                                        class="sim-btn-muted shrink-0 {{ request()->routeIs($action['active']) ? '!border-cyan-400/50 !text-cyan-100' : '' }}"
                                    >
                                        {{ $action['label'] }}
                                    </a>
                                @endforeach
                                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                                    @csrf
                                    <button class="sim-btn-muted" type="submit">Logout</button>
                                </form>
                            </div>
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
