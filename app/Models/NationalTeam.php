<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NationalTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'name',
        'short_name',
        'manager_user_id',
        'reputation',
        'tactical_style',
        'notes',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function callups(): HasMany
    {
        return $this->hasMany(NationalTeamCallup::class);
    }

    public function activeCallups(): HasMany
    {
        return $this->hasMany(NationalTeamCallup::class)
            ->where('status', 'active');
    }
}
