<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StadiumProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'stadium_id',
        'project_type',
        'level_from',
        'level_to',
        'cost',
        'started_on',
        'completes_on',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'started_on' => 'date',
            'completes_on' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function stadium(): BelongsTo
    {
        return $this->belongsTo(Stadium::class);
    }
}
