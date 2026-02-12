<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingCamp extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'created_by_user_id',
        'name',
        'focus',
        'intensity',
        'starts_on',
        'ends_on',
        'cost',
        'stamina_effect',
        'morale_effect',
        'overall_effect',
        'status',
        'applied_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'cost' => 'decimal:2',
            'applied_at' => 'datetime',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
