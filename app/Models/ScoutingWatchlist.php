<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoutingWatchlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'player_id',
        'created_by_user_id',
        'priority',
        'status',
        'focus',
        'progress',
        'reports_requested',
        'last_scouted_at',
        'next_report_due_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'integer',
            'reports_requested' => 'integer',
            'last_scouted_at' => 'datetime',
            'next_report_due_at' => 'datetime',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ScoutingReport::class, 'watchlist_id');
    }
}
