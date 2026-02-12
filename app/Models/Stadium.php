<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stadium extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'name',
        'capacity',
        'covered_seats',
        'vip_seats',
        'ticket_price',
        'maintenance_cost',
        'facility_level',
        'pitch_quality',
        'fan_experience',
        'security_level',
        'environment_level',
        'last_maintenance_at',
    ];

    protected function casts(): array
    {
        return [
            'ticket_price' => 'decimal:2',
            'maintenance_cost' => 'decimal:2',
            'last_maintenance_at' => 'datetime',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(StadiumProject::class);
    }
}
