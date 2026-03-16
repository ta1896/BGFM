<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoutingScout extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'created_by_user_id',
        'name',
        'level',
        'specialty',
        'region',
        'status',
        'workload',
        'active_watchlist_id',
        'available_at',
    ];

    protected function casts(): array
    {
        return [
            'workload' => 'integer',
            'available_at' => 'datetime',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function activeWatchlist(): BelongsTo
    {
        return $this->belongsTo(ScoutingWatchlist::class, 'active_watchlist_id');
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(ScoutingWatchlist::class, 'scout_id');
    }
}
