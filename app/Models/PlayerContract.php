<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'club_id',
        'wage',
        'bonus_goal',
        'signed_on',
        'starts_on',
        'expires_on',
        'release_clause',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'wage' => 'decimal:2',
            'bonus_goal' => 'decimal:2',
            'release_clause' => 'decimal:2',
            'signed_on' => 'date',
            'starts_on' => 'date',
            'expires_on' => 'date',
            'is_active' => 'boolean',
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
