<?php

namespace App\Modules\ForumCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ForumCenter\Models\Forum;
use App\Modules\ForumCenter\Models\ForumCategory;
use App\Modules\ForumCenter\Models\ForumPost;
use App\Modules\ForumCenter\Models\ForumThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ForumController extends Controller
{
    public function index(): Response
    {
        $categories = ForumCategory::with(['forums.lastThread.user', 'forums.lastThread.lastPost.user'])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'forums' => $category->forums->map(function ($forum) {
                        return [
                            'id' => $forum->id,
                            'name' => $forum->name,
                            'slug' => $forum->slug,
                            'description' => $forum->description,
                            'icon' => $forum->icon,
                            'threads_count' => $forum->threads()->count(),
                            'posts_count' => ForumPost::whereIn('forum_thread_id', $forum->threads()->pluck('id'))->count(),
                            'last_thread' => $forum->lastThread ? [
                                'title' => $forum->lastThread->title,
                                'slug' => $forum->lastThread->slug,
                                'user_name' => $forum->lastThread->user->name,
                                'user_avatar' => null,
                                'created_at' => $forum->lastThread->last_post_at?->diffForHumans(),
                            ] : null,
                        ];
                    }),
                ];
            });

        return Inertia::render('Modules/ForumCenter/Forum/Index', [
            'categories' => $categories,
        ]);
    }

    public function showForum(Forum $forum): Response
    {
        $threads = $forum->threads()
            ->with(['user', 'lastPost.user'])
            ->paginate(20)
            ->through(fn ($thread) => [
                'id' => $thread->id,
                'title' => $thread->title,
                'slug' => $thread->slug,
                'is_pinned' => $thread->is_pinned,
                'is_locked' => $thread->is_locked,
                'views_count' => $thread->views_count,
                'posts_count' => $thread->posts()->count(),
                'user_name' => $thread->user->name,
                'user_avatar' => null,
                'last_post' => [
                    'user_name' => $thread->lastPost?->user->name,
                    'user_avatar' => null,
                    'created_at' => $thread->last_post_at?->diffForHumans(),
                ],
            ]);

        return Inertia::render('Modules/ForumCenter/Forum/Show', [
            'forum' => [
                'id' => $forum->id,
                'name' => $forum->name,
                'slug' => $forum->slug,
                'description' => $forum->description,
            ],
            'threads' => $threads,
        ]);
    }

    public function showThread(ForumThread $thread): Response
    {
        $thread->increment('views_count');

        $posts = $thread->posts()
            ->with(['user'])
            ->paginate(config('simulation.modules.forum_center.posts_per_page', 15))
            ->through(fn ($post) => [
                'id' => $post->id,
                'content' => $post->content,
                'images' => collect($post->images ?? [])->map(fn ($path) => asset('storage/' . $path))->all(),
                'created_at' => $post->created_at->format('d.m.Y H:i'),
                'user' => [
                    'name' => $post->user->name,
                    'avatar' => null,
                    'posts_count' => ForumPost::where('user_id', $post->user->id)->count(),
                    // Mocking some stats for consistency with screenshots
                    'tokens' => 125, 
                    'club_name' => $post->user->clubs()->first()?->name ?? 'Gast',
                ],
            ]);

        return Inertia::render('Modules/ForumCenter/Forum/Thread', [
            'thread' => [
                'id' => $thread->id,
                'title' => $thread->title,
                'slug' => $thread->slug,
                'is_locked' => $thread->is_locked,
                'forum' => [
                    'name' => $thread->forum->name,
                    'slug' => $thread->forum->slug,
                ],
            ],
            'posts' => $posts,
        ]);
    }

    public function createThread(Forum $forum): Response
    {
        return Inertia::render('Modules/ForumCenter/Forum/Create', [
            'forum' => [
                'id' => $forum->id,
                'name' => $forum->name,
                'slug' => $forum->slug,
            ],
        ]);
    }

    public function storeThread(Request $request, Forum $forum): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $thread = ForumThread::create([
            'forum_id' => $forum->id,
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . rand(100, 999),
            'last_post_at' => now(),
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('forum_posts', 'public');
            }
        }

        ForumPost::create([
            'forum_thread_id' => $thread->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'images' => $imagePaths,
        ]);

        return redirect()->route('forum.thread.show', $thread)->with('status', 'Thema wurde erstellt.');
    }

    public function storePost(Request $request, ForumThread $thread): RedirectResponse
    {
        abort_if($thread->is_locked, 403);

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'], // 5MB max
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('forum_posts', 'public');
            }
        }

        ForumPost::create([
            'forum_thread_id' => $thread->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'images' => $imagePaths,
        ]);

        $thread->update(['last_post_at' => now()]);

        return back()->with('status', 'Antwort wurde gespeichert.');
    }
}
