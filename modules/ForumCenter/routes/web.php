<?php

use App\Modules\ForumCenter\Http\Controllers\ForumController;
use App\Modules\ForumCenter\Http\Controllers\Admin\ForumController as AdminForumController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Frontend Routes
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/forum/f/{forum:slug}', [ForumController::class, 'showForum'])->name('forum.show');
    Route::get('/forum/f/{forum:slug}/create', [ForumController::class, 'createThread'])->name('forum.thread.create');
    Route::post('/forum/f/{forum:slug}/store', [ForumController::class, 'storeThread'])->name('forum.thread.store');
    
    Route::get('/forum/t/{thread:slug}', [ForumController::class, 'showThread'])->name('forum.thread.show');
    Route::post('/forum/t/{thread:slug}/reply', [ForumController::class, 'storePost'])->name('forum.post.store');

    // Shoutbox
    Route::get('/forum/shoutbox', [\App\Modules\ForumCenter\Http\Controllers\ShoutboxController::class, 'index'])->name('forum.shoutbox.index');
    Route::post('/forum/shoutbox', [\App\Modules\ForumCenter\Http\Controllers\ShoutboxController::class, 'store'])->name('forum.shoutbox.store');
    
    // Admin Routes
    Route::middleware(['admin'])
        ->prefix('acp/forum')
        ->name('admin.forum.')
        ->group(function () {
            Route::get('/categories', [AdminForumController::class, 'index'])->name('categories.index');
            Route::post('/categories', [AdminForumController::class, 'storeCategory'])->name('categories.store');
            Route::post('/forums', [AdminForumController::class, 'storeForum'])->name('forums.store');
            Route::post('/reorder', [AdminForumController::class, 'reorder'])->name('reorder');
        });
});
