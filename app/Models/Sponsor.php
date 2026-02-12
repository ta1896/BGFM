<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sponsor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tier',
        'reputation_min',
        'base_weekly_amount',
        'signing_bonus_min',
        'signing_bonus_max',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_weekly_amount' => 'decimal:2',
            'signing_bonus_min' => 'decimal:2',
            'signing_bonus_max' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(SponsorContract::class);
    }
}
