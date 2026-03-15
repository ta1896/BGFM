<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerInjury extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'club_id',
        'injury_type',
        'body_area',
        'severity',
        'started_at',
        'expected_return_at',
        'actual_return_at',
        'status',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expected_return_at' => 'datetime',
            'actual_return_at' => 'datetime',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
