<?php

namespace App\Modules\ForumCenter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Forum extends Model
{
    protected $table = 'forum_forums';
    protected $fillable = ['forum_category_id', 'name', 'slug', 'description', 'icon', 'sort_order', 'is_locked'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(ForumThread::class)->orderByDesc('is_pinned')->orderByDesc('last_post_at');
    }

    public function lastThread(): HasOne
    {
        return $this->hasOne(ForumThread::class)->latest('last_post_at');
    }
}
