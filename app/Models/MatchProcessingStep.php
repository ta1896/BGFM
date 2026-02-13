<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchProcessingStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'step',
        'processed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }
}
