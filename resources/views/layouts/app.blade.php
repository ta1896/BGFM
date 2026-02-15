@php
    $user = auth()->user();
    $isAdmin = $user?->isAdmin() ?? false;
    $hasManagedClub = $isAdmin || $user?->clubs()->exists();
    
    // Default to empty collection
    $globalUserClubs = collect();

    if ($isAdmin) {
        $globalUserClubs = \App\Models\Club::where('is_cpu', false)->orderBy('name')->get();
    } elseif ($hasManagedClub) {
        $globalUserClubs = $user->clubs()->where('is_cpu', false)->orderBy('name')->get();
    }
        
    // Resolve active club from container (if set by middleware) or fallback
    $currentActiveClub = app()->has('activeClub') ? app('activeClub') : ($globalUserClubs->first() ?? null);

    // Grouped Menu Structure with IDs for state management if needed
    $menuGroups = [];

    if ($hasManagedClub) {
        $menuGroups['bg_buro'] = [
            'label' => 'Büro',
            'items' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'active' => 'dashboard', 'icon' => 'home'],
                ['route' => 'notifications.index', 'label' => 'Postfach', 'active' => 'notifications.*', 'icon' => 'inbox'],
                ['route' => 'finances.index', 'label' => 'Finanzen', 'active' => 'finances.*', 'icon' => 'banknotes'],
                ['route' => 'sponsors.index', 'label' => 'Sponsoren', 'active' => 'sponsors.*', 'icon' => 'briefcase'],
                ['route' => 'stadium.index', 'label' => 'Stadion', 'active' => 'stadium.*', 'icon' => 'building-office'],
            ]
        ];

        $menuGroups['bg_team'] = [
            'label' => 'Team',
            'items' => [
                ['route' => 'lineups.index', 'label' => 'Aufstellung', 'active' => 'lineups.*', 'icon' => 'user-group'],
                ['route' => 'players.index', 'label' => 'Kader', 'active' => 'players.*', 'icon' => 'users'],
                ['route' => 'training.index', 'label' => 'Training', 'active' => 'training.*', 'icon' => 'academic-cap'],
                ['route' => 'training-camps.index', 'label' => 'Trainingslager', 'active' => 'training-camps.*', 'icon' => 'tent'],
            ]
        ];

        $menuGroups['bg_wettbewerb'] = [
            'label' => 'Wettbewerb',
            'items' => [
                ['route' => 'league.matches', 'label' => 'Spiele', 'active' => 'league.matches', 'icon' => 'calendar'],
                ['route' => 'league.table', 'label' => 'Tabelle', 'active' => 'league.table', 'icon' => 'trophy'],
                ['route' => 'friendlies.index', 'label' => 'Freundschaft', 'active' => 'friendlies.*', 'icon' => 'hand-raised'],
                ['route' => 'team-of-the-day.index', 'label' => 'Team der Woche', 'active' => 'team-of-the-day.*', 'icon' => 'star'],
                ['route' => 'national-teams.index', 'label' => 'Nationalteams', 'active' => 'national-teams.*', 'icon' => 'globe-alt'],
            ]
        ];

         $menuGroups['bg_markt'] = [
            'label' => 'Markt',
            'items' => [
                ['route' => 'transfers.index', 'label' => 'Transfermarkt', 'active' => 'transfers.*', 'icon' => 'arrows-right-left'],
                ['route' => 'loans.index', 'label' => 'Leihmarkt', 'active' => 'loans.*', 'icon' => 'arrow-path'],
                ['route' => 'contracts.index', 'label' => 'Verträge', 'active' => 'contracts.*', 'icon' => 'document-text'],
                ['route' => 'clubs.index', 'label' => 'Vereins-Suche', 'active' => 'clubs.*', 'icon' => 'magnifying-glass'],
            ]
        ];
    } else {
        $menuGroups['bg_start'] = [
            'label' => 'Start',
            'items' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'active' => 'dashboard', 'icon' => 'home'],
                ['route' => 'clubs.free', 'label' => 'Verein wählen', 'active' => 'clubs.free', 'icon' => 'search'],
                ['route' => 'profile.edit', 'label' => 'Profil', 'active' => 'profile.*', 'icon' => 'user'],
            ]
        ];
    }

    if ($isAdmin) {
        $menuGroups['bg_admin'] = [
            'label' => 'Administration',
            'items' => [
                ['route' => 'admin.dashboard', 'label' => 'ACP Übersicht', 'active' => 'admin.dashboard', 'icon' => 'cog'],
                ['route' => 'admin.competitions.index', 'label' => 'Wettbewerbe', 'active' => 'admin.competitions.*', 'icon' => 'trophy'],
                ['route' => 'admin.seasons.index', 'label' => 'Saisons', 'active' => 'admin.seasons.*', 'icon' => 'calendar'],
                ['route' => 'admin.clubs.index', 'label' => 'Vereine', 'active' => 'admin.clubs.*', 'icon' => 'building-library'],
                ['route' => 'admin.players.index', 'label' => 'Spieler', 'active' => 'admin.players.*', 'icon' => 'identification'],
                ['route' => 'admin.ticker-templates.index', 'label' => 'Ticker Vorlagen', 'active' => 'admin.ticker-templates.*', 'icon' => 'chat-bubble-bottom-center-text'],
                ['route' => 'admin.match-engine.index', 'label' => 'Match Engine', 'active' => 'admin.match-engine.*', 'icon' => 'cpu-chip'],
            ]
        ];
    }

    // Flatten for active check and mobile menu
    $allMenuItems = collect($menuGroups)->pluck('items')->flatten(1);

    $activeMenu = $allMenuItems->first(
        fn (array $item): bool => request()->routeIs($item['active'])
    );
    $activeMenuLabel = $activeMenu['label'] ?? 'Dashboard';

    $headerActions = [];
    if ($hasManagedClub) {
        $headerActions = [
            ['route' => 'league.matches', 'params' => ['club' => $currentActiveClub?->id], 'label' => 'Spielplan', 'active' => 'league.matches', 'primary' => true],
          ['route' => 'lineups.index', 'label' => 'Aufstellung', 'active' => 'lineups.*', 'primary' => false],
        ];
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'OpenWS Laravell') }}</title>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="sim-shell font-sans text-slate-100 antialiased selection:bg-cyan-500/30 selection:text-cyan-100">
        <div class="min-h-screen lg:flex">
            <!-- Glassmorphism Sidebar -->
            <aside class="hidden w-72 flex-col border-r border-slate-950 bg-slate-900/60 backdrop-blur-xl lg:flex fixed inset-y-0 left-0 z-50">
                <!-- Branding -->
                <div class="flex h-20 items-center px-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-400 to-indigo-600 shadow-lg shadow-cyan-500/20 transition group-hover:scale-105 group-hover:shadow-cyan-500/40">
                            <span class="text-lg font-bold text-white">OW</span>
                        </div>
                        <div>
                            <p class="font-bold text-white leading-tight tracking-tight">OpenWS</p>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 group-hover:text-cyan-400 transition-colors">Laravell</p>
                        </div>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto px-4 py-4 scrollbar-thin scrollbar-track-transparent scrollbar-thumb-slate-700 space-y-2">
                    @foreach($menuGroups as $groupKey => $group)
                        @php
                            $isActiveGroup = collect($group['items'])->contains(fn($item) => request()->routeIs($item['active']));
                        @endphp
                        <div x-data="{ open: {{ $isActiveGroup ? 'true' : 'false' }} }" class="mb-2">
                            <button @click="open = !open" class="flex w-full items-center justify-between px-2 py-2 text-slate-400 hover:text-white transition group/btn rounded-md hover:bg-slate-800/50">
                                <span class="text-[10px] font-bold uppercase tracking-widest group-hover/btn:text-cyan-400 transition-colors">{{ $group['label'] }}</span>
                                <svg class="h-4 w-4 transition-transform duration-200" 
                                     :class="open ? 'rotate-180 text-cyan-400' : 'text-slate-600'" 
                                     fill="none" 
                                     viewBox="0 0 24 24" 
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 -translate-y-2"
                                 class="space-y-1 mt-1 pl-2 border-l border-slate-950 ml-2">
                                @foreach ($group['items'] as $item)
                                <a href="{{ route($item['route']) }}" 
                                   class="sim-nav-item {{ request()->routeIs($item['active']) ? 'sim-nav-item-active' : '' }}">
                                   <span class="flex items-center gap-3">
                                       @if(request()->routeIs($item['active']))
                                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,0.6)]"></span>
                                       @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-slate-500 transition-colors"></span>
                                       @endif
                                       {{ $item['label'] }}
                                   </span>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>

                <!-- User Profile Footer -->
                <div class="border-t border-slate-950 bg-slate-900/40 p-4">
                    
                    @if($hasManagedClub && $globalUserClubs->count() > 1)
                        <div x-data="{ open: false }" class="relative mb-3">
                            <button @click="open = !open" @click.away="open = false" 
                                class="flex w-full items-center gap-3 rounded-lg bg-slate-800 p-2 text-left hover:bg-slate-700 transition border border-slate-950">
                                @if($currentActiveClub)
                                    <div class="h-8 w-8 rounded-full overflow-hidden bg-slate-900 border border-slate-600">
                                        <img src="{{ $currentActiveClub->logo_url }}" class="h-full w-full object-contain">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs text-slate-400 uppercase tracking-wider font-bold">Aktiver Verein</p>
                                        <p class="truncate text-sm font-bold text-white">{{ $currentActiveClub->name }}</p>
                                    </div>
                                @else
                                    <div class="h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-slate-300">Verein wählen</span>
                                @endif
                                <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
                                 x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
                                 class="absolute bottom-full left-0 mb-2 w-full rounded-lg bg-slate-800 border border-slate-700 shadow-xl overflow-hidden z-50 max-h-64 overflow-y-auto custom-scrollbar">
                                <div class="p-1 space-y-0.5">
                                    @foreach($globalUserClubs as $c)
                                    <a href="{{ route('dashboard', ['club' => $c->id]) }}" 
                                       class="flex items-center gap-3 rounded-md px-2 py-2 text-sm transition group {{ $currentActiveClub?->id === $c->id ? 'bg-indigo-600/20 text-indigo-300' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                                        <div class="h-6 w-6 rounded-full overflow-hidden bg-slate-900 border border-slate-700 flex-shrink-0">
                                            <img src="{{ $c->logo_url }}" class="h-full w-full object-contain group-hover:scale-110 transition">
                                        </div>
                                        <span class="truncate flex-1">{{ $c->name }}</span>
                                        @if($currentActiveClub?->id === $c->id)
                                            <div class="w-1.5 h-1.5 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,0.6)]"></div>
                                        @endif
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center gap-3 rounded-lg p-2 transition hover:bg-slate-800/50">
                        <div class="h-9 w-9 overflow-hidden rounded-full border border-slate-600 bg-slate-800">
                             <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0f172a&color=cbd5e1" alt="{{ $user->name }}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                            <p class="truncate text-xs text-slate-400">{{ $isAdmin ? 'Administrator' : 'Manager' }}</p>
                        </div>
                        
                        <a href="{{ route('settings.index') }}" class="text-slate-400 hover:text-cyan-400 transition mr-2" title="Einstellungen">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-slate-400 hover:text-rose-400 transition" title="Logout">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex min-h-screen flex-1 flex-col lg:pl-72 transition-all">
                <!-- Top Header -->
                @if (isset($header))
                    <header class="sticky top-0 z-40 border-b border-slate-950 bg-slate-900/80 backdrop-blur-md">
                        <div class="px-6 py-4">
                            {{ $header }}
                        </div>
                    </header>
                @else
                    <header class="sticky top-0 z-40 border-b border-slate-950 bg-slate-900/80 backdrop-blur-md">
                        <div class="px-6 py-4 flex items-center justify-between">
                             <div class="flex items-center gap-4">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-cyan-500/80">Current View</p>
                                    <h1 class="text-xl font-bold text-white tracking-tight">{{ $activeMenuLabel }}</h1>
                                </div>
                                
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-3">
                                    @foreach($headerActions as $action)
                                    <a href="{{ route($action['route']) }}" class="{{ $action['primary'] ? 'sim-btn-primary py-2 px-4 shadow-sm' : 'sim-btn-muted py-2 px-4' }}">
                                        {{ $action['label'] }}
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </header>
                @endif

                <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8 max-w-[1600px] mx-auto w-full">
                     @if (session('status'))
                        <div class="mb-6 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400 shadow-lg shadow-emerald-500/5">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
