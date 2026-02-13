<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchLiveMinuteSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'minute',
        'home_score',
        'away_score',
        'home_phase',
        'away_phase',
        'home_tactical_style',
        'away_tactical_style',
        'pending_plans',
        'executed_plans',
        'skipped_plans',
        'invalid_plans',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }
}

