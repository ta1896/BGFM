<?php

namespace App\Modules\ForumCenter\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\ForumCenter\Models\Forum;
use App\Modules\ForumCenter\Models\ForumCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ForumController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Modules/ForumCenter/Admin/Index', [
            'categories' => ForumCategory::with('forums')->orderBy('sort_order')->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        ForumCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sort_order' => ForumCategory::count() + 1,
        ]);

        return back()->with('status', 'Kategorie erstellt.');
    }

    public function storeForum(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'forum_category_id' => ['required', 'exists:forum_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Forum::create([
            'forum_category_id' => $validated['forum_category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'sort_order' => Forum::where('forum_category_id', $validated['forum_category_id'])->count() + 1,
        ]);

        return back()->with('status', 'Forum erstellt.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'exists:forum_categories,id'],
            'categories.*.sort_order' => ['required', 'integer'],
            'categories.*.forums' => ['nullable', 'array'],
            'categories.*.forums.*.id' => ['required', 'exists:forums,id'],
            'categories.*.forums.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($validated['categories'] as $catData) {
            ForumCategory::where('id', $catData['id'])->update(['sort_order' => $catData['sort_order']]);
            if (isset($catData['forums'])) {
                foreach ($catData['forums'] as $forumData) {
                    Forum::where('id', $forumData['id'])->update([
                        'sort_order' => $forumData['sort_order'],
                        'forum_category_id' => $catData['id']
                    ]);
                }
            }
        }

        return back()->with('status', 'Struktur wurde aktualisiert.');
    }
}
