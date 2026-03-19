<?php

namespace App\Modules\ForumCenter\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ForumThread extends Model
{
    protected $fillable = ['forum_id', 'user_id', 'title', 'slug', 'is_pinned', 'is_locked', 'views_count', 'last_post_at'];

    protected $casts = [
        'last_post_at' => 'datetime',
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class)->orderBy('created_at');
    }

    public function firstPost(): HasOne
    {
        return $this->hasOne(ForumPost::class)->oldestOfMany();
    }

    public function lastPost(): HasOne
    {
        return $this->hasOne(ForumPost::class)->latestOfMany();
    }
}
