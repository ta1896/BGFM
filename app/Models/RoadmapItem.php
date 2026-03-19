<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoadmapItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'title',
        'summary',
        'status',
        'category',
        'size_bucket',
        'tags',
        'priority',
        'effort',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RoadmapComment::class)->latest();
    }
}
