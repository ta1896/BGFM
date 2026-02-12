<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso_code',
        'fifa_code',
    ];

    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class);
    }

    public function nationalTeam(): HasOne
    {
        return $this->hasOne(NationalTeam::class);
    }
}
