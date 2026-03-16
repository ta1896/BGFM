<?php

namespace App\Http\Controllers;

use App\Modules\ModuleManager;
use App\Models\GameNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request, ModuleManager $modules): \Inertia\Response
    {
        $notifications = $request->user()
            ->gameNotifications()
            ->with('club:id,name,logo_path')
            ->latest()
            ->paginate(20)
            ->through(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'seen_at' => $n->seen_at,
                    'created_at_formatted' => $n->created_at->format('d.m.Y H:i'),
                    'action_url' => $n->action_url,
                    'club' => $n->club ? [
                        'id' => $n->club->id,
                        'name' => $n->club->name,
                        'logo_url' => $n->club->logo_url,
                    ] : null,
                ];
            })
            ->withQueryString();

        return \Inertia\Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'moduleNotificationThemes' => $modules->frontendRegistry()['notifications'] ?? [],
        ]);
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
