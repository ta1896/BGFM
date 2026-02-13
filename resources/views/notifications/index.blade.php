<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="sim-section-title">Benachrichtigungen</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Inbox</h1>
            </div>
            <form method="POST" action="{{ route('notifications.seen-all') }}">
                @csrf
                <button type="submit" class="sim-btn-muted">Alle als gelesen</button>
            </form>
        </div>
    </x-slot>

    <section class="sim-card p-5">
        @if ($notifications->isEmpty())
            <p class="text-sm text-slate-300">Keine Benachrichtigungen vorhanden.</p>
        @else
            <div class="space-y-3">
                @foreach ($notifications as $notification)
                    <article class="sim-card-soft p-4 {{ $notification->seen_at ? 'opacity-70' : '' }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-white">{{ $notification->title }}</p>
                                <p class="mt-1 text-sm text-slate-300">{{ $notification->message }}</p>
                                <p class="mt-2 text-xs text-slate-400">
                                    {{ $notification->created_at->format('d.m.Y H:i') }}
                                    @if ($notification->club)
                                        | 
                                        <span class="inline-flex items-center gap-1">
                                            <img class="sim-avatar sim-avatar-xs" src="{{ $notification->club->logo_url }}" alt="{{ $notification->club->name }}">
                                            {{ $notification->club->name }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($notification->action_url)
                                    <a href="{{ $notification->action_url }}" class="sim-btn-muted !px-3 !py-1.5 text-xs">Oeffnen</a>
                                @endif
                                @if (!$notification->seen_at)
                                    <form method="POST" action="{{ route('notifications.seen', $notification) }}">
                                        @csrf
                                        <button type="submit" class="sim-btn-primary !px-3 !py-1.5 text-xs">Gelesen</button>
                                    </form>
                                @else
                                    <span class="sim-pill">Gelesen</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-4">{{ $notifications->links() }}</div>
        @endif
    </section>
</x-app-layout>
