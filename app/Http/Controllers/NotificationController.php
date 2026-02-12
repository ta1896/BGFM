<?php

namespace App\Http\Controllers;

use App\Models\GameNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->gameNotifications()
            ->with('club')
            ->latest()
            ->paginate(20);

        return view('notifications.index', ['notifications' => $notifications]);
    }

    public function markSeen(Request $request, GameNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        if (!$notification->seen_at) {
            $notification->update(['seen_at' => now()]);
        }

        return back()->with('status', 'Benachrichtigung als gelesen markiert.');
    }

    public function markAllSeen(Request $request): RedirectResponse
    {
        $request->user()
            ->gameNotifications()
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        return back()->with('status', 'Alle Benachrichtigungen wurden als gelesen markiert.');
    }
}
