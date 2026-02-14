<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="relative z-10">
                <p class="sim-section-title mb-2">Command Center</p>
                <h1 class="text-4xl font-bold leading-tight text-white sm:text-5xl">
                    Welcome back, <br>
                    <span class="bg-gradient-to-r from-cyan-300 via-indigo-300 to-fuchsia-300 bg-clip-text text-transparent">
                        {{ auth()->user()->name }}
                    </span>
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if ($activeClub)
                    <div class="flex items-center gap-2 rounded-xl border border-slate-700/50 bg-slate-900/60 p-1.5 backdrop-blur-sm">
                         <a href="{{ route('league.matches', array_filter(['club' => $activeClub->id, 'scope' => 'today', 'competition_season' => $selectedCompetitionSeasonId])) }}" 
                           class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
                            <span>Today's Games</span>
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-800 text-xs text-white">{{ $todayMatchesCount }}</span>
                        </a>
                        <div class="h-4 w-px bg-slate-700"></div>
                        <a href="{{ route('players.index', ['club' => $activeClub->id]) }}" 
                           class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
                            Squad
                        </a>
                        <a href="{{ route('training.index', ['club' => $activeClub->id, 'range' => 'week']) }}" 
                           class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
                            Training
                        </a>
                    </div>
                @else
                    <a href="{{ route('clubs.free') }}" class="sim-btn-primary shadow-lg shadow-cyan-500/20">
                        <span class="mr-2">Find a Club</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    @if (!$activeClub)
        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            @if (auth()->user()->isAdmin())
                <section class="sim-card p-8 relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl group-hover:bg-cyan-500/20 transition duration-700"></div>
                    <div class="relative z-10">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 shadow-lg">
                            <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-white mb-2">Admin Control Panel</h2>
                        <p class="text-slate-400 mb-6 max-w-md">Manage the game universe, simulations, and user data from the centralized control panel.</p>
                        <a href="{{ route('admin.dashboard') }}" class="sim-btn-primary">
                            Access ACP
                        </a>
                    </div>
                </section>
            @endif

            <section class="sim-card p-8 group relative overflow-hidden">
                 <div class="absolute -right-10 -bottom-10 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl group-hover:bg-indigo-500/20 transition duration-700"></div>
                 <div class="relative z-10">
                    <p class="sim-section-title text-indigo-400">Career Mode</p>
                    <h2 class="text-2xl font-bold text-white mb-3">Begin Your Journey</h2>
                    <p class="text-slate-400 mb-6">Select a club from the available free agents list and start building your legacy.</p>
                    <a href="{{ route('clubs.free') }}" class="sim-btn-primary bg-indigo-600">
                        View Available Clubs
                    </a>
                </div>
            </section>
        </div>
    @else
        <!-- Active Club Dashboard -->
        @php
            $fanMood = max(0, min(100, (int) $activeClub->fan_mood));
            $isActiveClubHome = $nextMatch && (int) $nextMatch->home_club_id === (int) $activeClub->id;
            $homeReady = $nextMatch ? ($isActiveClubHome ? $activeClubReadyForNextMatch : $opponentReadyForNextMatch) : false;
            $awayReady = $nextMatch ? (!$isActiveClubHome ? $activeClubReadyForNextMatch : $opponentReadyForNextMatch) : false;
        @endphp

        <!-- Weekly Timeline -->
        <section class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="sim-section-title mb-0">Weekly Overview</h3>
                 <div class="flex gap-2">
                     <span class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span> Match
                     </span>
                     <span class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Training
                     </span>
                 </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                @foreach ($weekDays as $day)
                    @php
                        $isToday = $day['is_today'];
                        $hasMatch = $day['match_count'] > 0;
                        $hasTraining = $day['training_count'] > 0;
                        
                        $dayUrl = $hasMatch
                            ? route('league.matches', array_filter(['competition_season' => $selectedCompetitionSeasonId, 'club' => $activeClub->id, 'day' => $day['iso_date']]))
                            : route('training.index', ['club' => $activeClub->id, 'date' => $day['iso_date']]);
                            
                        $borderColor = $isToday ? 'border-cyan-500/50' : ($hasMatch ? 'border-indigo-500/30' : 'border-slate-700/50');
                        $bgColor = $isToday ? 'bg-cyan-500/5' : ($hasMatch ? 'bg-indigo-500/5' : 'bg-slate-800/20');
                    @endphp
                    <a href="{{ $dayUrl }}" class="group relative flex flex-col justify-between rounded-xl border {{ $borderColor }} {{ $bgColor }} p-3 transition hover:border-cyan-400/50 hover:bg-slate-800/60 hover:shadow-lg hover:shadow-cyan-900/10 hover:-translate-y-0.5">
                        @if ($isToday)
                            <div class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,0.6)]"></div>
                        @endif
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest {{ $isToday ? 'text-cyan-400' : 'text-slate-500' }}">{{ $day['label'] }}</p>
                            <p class="mt-1 text-lg font-bold text-white">{{ $day['date'] }}</p>
                        </div>
                        
                        <div class="mt-4 flex flex-col gap-1.5">
                            @if ($hasMatch)
                                <div class="flex items-center gap-2 rounded bg-slate-900/50 px-2 py-1">
                                    <div class="h-1.5 w-1.5 rounded-full bg-cyan-400"></div>
                                    <span class="text-[10px] font-bold uppercase text-slate-300">{{ $day['match_count'] }} Game{{ $day['match_count'] > 1 ? 's' : '' }}</span>
                                </div>
                            @endif
                             @if ($hasTraining)
                                <div class="flex items-center gap-2 rounded bg-slate-900/50 px-2 py-1">
                                    <div class="h-1.5 w-1.5 rounded-full bg-indigo-500"></div>
                                    <span class="text-[10px] font-bold uppercase text-slate-300">{{ $day['training_count'] }} Session{{ $day['training_count'] > 1 ? 's' : '' }}</span>
                                </div>
                            @endif
                            @if (!$hasMatch && !$hasTraining)
                                <span class="text-[10px] font-medium text-slate-600 px-1">Rest Day</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Dynamic Grid Layout -->
        <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_20rem] xl:grid-cols-[1fr_22rem]">
            
            <div class="space-y-6">
                <!-- Club & Form Card -->
                <div class="sim-card p-6 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-800/20 to-transparent pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col md:flex-row gap-6 md:items-center justify-between">
                         <div class="flex items-center gap-5">
                            <div class="relative h-20 w-20 shrink-0 rounded-2xl border border-slate-600 bg-slate-900 p-2 shadow-xl shadow-black/40">
                                <img class="h-full w-full object-contain" src="{{ $activeClub->logo_url }}" alt="{{ $activeClub->name }}">
                            </div>
                            <div>
                                <h2 class="text-3xl font-bold text-white tracking-tight">{{ $activeClub->name }}</h2>
                                <div class="mt-2 flex flex-wrap gap-2">
                                     <span class="inline-flex items-center rounded-md border border-slate-600/50 bg-slate-800/50 px-2 py-1 text-xs font-semibold text-slate-300">
                                        {{ $activeClub->league }}
                                     </span>
                                     @if ($clubRank)
                                        <span class="inline-flex items-center rounded-md border border-fuchsia-500/30 bg-fuchsia-500/10 px-2 py-1 text-xs font-semibold text-fuchsia-300 shadow-[0_0_10px_-3px_rgba(232,121,249,0.3)]">
                                            Rank #{{ $clubRank }}
                                        </span>
                                     @endif
                                      <span class="inline-flex items-center rounded-md border border-slate-600/50 bg-slate-800/50 px-2 py-1 text-xs font-semibold text-slate-300">
                                        {{ $clubPoints ?? '-' }} Pts
                                     </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 min-w-[140px]">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Recent Form</p>
                            <div class="flex gap-1.5">
                                @forelse ($recentForm as $result)
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-bold shadow-sm backdrop-blur-sm
                                        {{ $result === 'W' ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400' : 
                                           ($result === 'L' ? 'border-rose-500/30 bg-rose-500/10 text-rose-400' : 
                                            'border-slate-500/30 bg-slate-500/10 text-slate-400') }}">
                                        {{ $result }}
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-500 italic">No games yet</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Next Match Card -->
                    <div class="sim-card flex flex-col h-full">
                        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center">
                            <h3 class="sim-section-title mb-0">Next Match</h3>
                            @if ($nextMatch)
                            <span class="text-xs font-medium text-cyan-400">{{ $nextMatch->kickoff_at?->format('d.m. H:i') }}</span>
                            @endif
                        </div>
                        
                        <div class="p-6 flex-1 flex flex-col justify-center">
                            @if ($nextMatch)
                                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-4">
                                     <div class="text-center group">
                                         <div class="mx-auto mb-2 h-14 w-14 rounded-full border border-slate-700 bg-slate-900 p-2 transition group-hover:border-cyan-500/50 group-hover:shadow-[0_0_15px_-4px_rgba(34,211,238,0.3)]">
                                            <img class="h-full w-full object-contain" src="{{ $nextMatch->homeClub->logo_url }}" alt="{{ $nextMatch->homeClub->name }}">
                                         </div>
                                         <p class="truncate text-sm font-bold text-white">{{ $nextMatch->homeClub->name }}</p>
                                         <p class="text-[10px] uppercase font-bold text-slate-500">Home</p>
                                     </div>

                                     <div class="text-center">
                                         <span class="text-2xl font-black text-slate-700">VS</span>
                                     </div>

                                     <div class="text-center group">
                                         <div class="mx-auto mb-2 h-14 w-14 rounded-full border border-slate-700 bg-slate-900 p-2 transition group-hover:border-rose-500/50 group-hover:shadow-[0_0_15px_-4px_rgba(244,63,94,0.3)]">
                                            <img class="h-full w-full object-contain" src="{{ $nextMatch->awayClub->logo_url }}" alt="{{ $nextMatch->awayClub->name }}">
                                         </div>
                                         <p class="truncate text-sm font-bold text-white">{{ $nextMatch->awayClub->name }}</p>
                                         <p class="text-[10px] uppercase font-bold text-slate-500">Away</p>
                                     </div>
                                </div>
                                
                                <div class="mt-6 flex justify-center gap-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wide border {{ $homeReady ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400' : 'border-rose-500/30 bg-rose-500/10 text-rose-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $homeReady ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                        Home Lineup
                                    </span>
                                     <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wide border {{ $awayReady ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400' : 'border-rose-500/30 bg-rose-500/10 text-rose-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $awayReady ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                        Away Lineup
                                    </span>
                                </div>

                                <div class="mt-6">
                                     <a href="{{ route('matches.show', $nextMatch) }}" class="sim-btn-primary w-full shadow-lg shadow-cyan-500/20">
                                        Go to Match Center
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <div class="mx-auto h-12 w-12 rounded-full bg-slate-800/50 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <p class="text-slate-400">No upcoming matches scheduled.</p>
                                    <a href="{{ route('league.matches') }}" class="mt-4 text-sm font-semibold text-cyan-400 hover:text-cyan-300">View Schedule &rarr;</a>
                                </div>
                            @endif
                        </div>
                    </div>
                
                    <!-- Finance & Board -->
                    <div class="grid gap-6 rows-2">
                         <div class="sim-card p-6 bg-gradient-to-br from-slate-900 to-slate-900 border-none relative overflow-hidden">
                            <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-emerald-500 to-cyan-500"></div>
                            <h3 class="sim-section-title text-slate-500">Finances</h3>
                            <div class="mt-2 text-3xl font-bold text-white tracking-tight leading-none">
                                {{ number_format((float) $activeClub->budget, 0, ',', '.') }}<span class="text-lg text-slate-500 ml-1">€</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.6)]"></span>
                                Available Budget
                            </p>
                         </div>

                         <div class="sim-card p-6 relative overflow-hidden">
                            <h3 class="sim-section-title text-slate-500">Board Confidence</h3>
                            <div class="mt-2 flex items-end gap-3">
                                <span class="text-4xl font-bold text-white leading-none">{{ (int) $activeClub->board_confidence }}</span>
                                <span class="text-sm font-semibold text-slate-400 mb-1.5">/ 100</span>
                            </div>
                            <div class="mt-3 h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-rose-500 via-amber-500 to-emerald-500" style="width: {{ $activeClub->board_confidence }}%"></div>
                            </div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar Area -->
            <div class="space-y-6">
                <!-- Notifications -->
                 <div class="sim-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="sim-section-title mb-0">Inbox</h3>
                        @if($unreadNotificationsCount > 0)
                        <span class="inline-flex items-center justify-center rounded-full bg-rose-500/20 px-2 py-0.5 text-[10px] font-bold text-rose-300 ring-1 ring-inset ring-rose-500/30">
                            {{ $unreadNotificationsCount }} New
                        </span>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @forelse ($notifications->take(3) as $notification)
                            <div class="group relative rounded-lg border border-slate-700/40 bg-slate-900/30 p-3 hover:border-slate-600/60 hover:bg-slate-800/40 transition">
                                <p class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors line-clamp-2 pr-4">
                                    {{ $notification->title }}
                                </p>
                                <p class="mt-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wide">
                                    {{ $notification->created_at->diffForHumans(null, true, true) }}
                                </p>
                                @if(!$notification->read_at)
                                <div class="absolute top-3 right-3 h-1.5 w-1.5 rounded-full bg-cyan-400 shadow-[0_0_6px_rgba(34,211,238,0.8)]"></div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-6 text-sm text-slate-500">
                                No new notifications.
                            </div>
                        @endforelse
                    </div>

                    <a href="{{ route('notifications.index') }}" class="sim-btn-muted w-full mt-4 text-xs uppercase tracking-wide">
                        View All Messages
                    </a>
                </div>

                <!-- Assistant / Tasks -->
                 @if (!empty($assistantTasks))
                <div class="sim-card p-5 border-l-4 border-l-fuchsia-500">
                    <h3 class="sim-section-title text-fuchsia-400 mb-4">Assistant Suggestions</h3>
                    <div class="space-y-3">
                         @foreach ($assistantTasks as $task)
                            <div class="relative rounded-lg bg-slate-900/40 p-3 hover:bg-slate-800/40 transition">
                                <p class="text-sm font-semibold text-white">{{ $task['label'] }}</p>
                                <p class="mt-1 text-xs text-slate-400 leading-relaxed">{{ $task['description'] }}</p>
                                <a href="{{ $task['url'] }}" class="mt-2.5 inline-flex text-[11px] font-bold uppercase tracking-wider text-cyan-400 hover:text-cyan-300">
                                    {{ $task['cta'] }} &rarr;
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    @endif
</x-app-layout>
