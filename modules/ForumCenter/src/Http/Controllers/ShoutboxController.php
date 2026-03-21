<?php

namespace App\Modules\ForumCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ForumCenter\Models\ShoutboxMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShoutboxController extends Controller
{
    public function index(): JsonResponse
    {
        $messages = ShoutboxMessage::with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(25)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($msg) => [
                'id' => $msg->id,
                'user_name' => $msg->user->name,
                'content' => $msg->content,
                'created_at' => $msg->created_at->diffForHumans(),
                'timestamp' => $msg->created_at->toDateTimeString(),
            ]);

        return response()->json($messages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:255'],
        ]);

        $message = ShoutboxMessage::create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        return response()->json([
            'id' => $message->id,
            'user_name' => $request->user()->name,
            'content' => $message->content,
            'created_at' => 'gerade eben',
            'timestamp' => $message->created_at->toDateTimeString(),
        ]);
    }
}
