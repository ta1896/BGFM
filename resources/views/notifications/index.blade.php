<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                 <p class="text-xs font-bold uppercase tracking-widest text-cyan-400 mb-1">Office</p>
                <h1 class="text-3xl font-bold text-white tracking-tight">Posteingang</h1>
            </div>
            
            <form method="POST" action="{{ route('notifications.seen-all') }}">
                @csrf
                <button type="submit" class="sim-btn-muted flex items-center gap-2 group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Alle als gelesen markieren
                </button>
            </form>
        </div>

        <div class="sim-card p-0 overflow-hidden min-h-[500px] flex flex-col">
            @if ($notifications->isEmpty())
                <div class="flex-1 flex flex-col items-center justify-center p-12 text-center text-slate-500">
                     <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center mb-4 text-slate-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                     </div>
                    <h3 class="text-lg font-bold text-white mb-1">Keine neuen Nachrichten</h3>
                    <p class="text-slate-400">Du bist auf dem neuesten Stand.</p>
                </div>
            @else
                <div class="divide-y divide-slate-700/50">
                    @foreach ($notifications as $notification)
                        <article class="p-4 sm:p-5 flex gap-4 transition-colors hover:bg-slate-800/30 {{ $notification->seen_at ? 'bg-transparent opacity-60' : 'bg-slate-800/10' }}">
                            <!-- Icon/Status -->
                            <div class="shrink-0 pt-1">
                                @if(!$notification->seen_at)
                                    <div class="w-2 h-2 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,0.6)] mt-2"></div>
                                @else
                                    <div class="w-2 h-2 rounded-full bg-slate-600 mt-2"></div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-1 sm:gap-4 mb-1">
                                    <h3 class="text-sm font-bold text-white truncate {{ !$notification->seen_at ? 'text-white' : 'text-slate-400' }}">
                                        {{ $notification->title }}
                                    </h3>
                                    <span class="text-[10px] text-slate-500 whitespace-nowrap shrink-0">
                                        {{ $notification->created_at->format('d.m.Y H:i') }}
                                    </span>
                                </div>
                                
                                <p class="text-sm text-slate-300 leading-relaxed mb-3">
                                    {{ $notification->message }}
                                </p>

                                <div class="flex flex-wrap items-center gap-3">
                                    @if ($notification->club)
                                        <div class="flex items-center gap-1.5 p-1 pr-2 rounded bg-slate-800/50 border border-slate-700/50 text-[10px] font-bold text-slate-300 tracking-wide uppercase">
                                             <img class="w-4 h-4 object-contain" src="{{ $notification->club->logo_url }}" alt="{{ $notification->club->name }}">
                                             {{ $notification->club->name }}
                                        </div>
                                    @endif

                                    <div class="flex-1"></div>

                                    @if ($notification->action_url)
                                        <a href="{{ $notification->action_url }}" class="text-xs font-bold text-cyan-400 hover:text-cyan-300 flex items-center gap-1 transition-colors">
                                            Öffnen
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </a>
                                    @endif
                                    
                                    @if (!$notification->seen_at)
                                        <form method="POST" action="{{ route('notifications.seen', $notification) }}">
                                            @csrf
                                            <button type="submit" class="text-xs font-bold text-slate-500 hover:text-slate-300 transition-colors">
                                                Als gelesen markieren
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
            
            @if($notifications->hasPages())
                <div class="p-4 border-t border-slate-700/50 bg-slate-900/40">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
