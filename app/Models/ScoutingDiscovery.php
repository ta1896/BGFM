<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoutingDiscovery extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'player_id',
        'created_by_user_id',
        'market',
        'position_group',
        'age_band',
        'value_band',
        'discovery_level',
        'fit_score',
        'market_band',
        'region_tag',
        'discovery_note',
        'scanned_at',
    ];

    protected $casts = [
        'fit_score' => 'integer',
        'scanned_at' => 'datetime',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
