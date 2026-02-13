<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'name',
        'short_name',
        'logo_path',
        'type',
        'scope',
        'tier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function competitionSeasons(): HasMany
    {
        return $this->hasMany(CompetitionSeason::class);
    }

    public function getLogoUrlAttribute(): string
    {
        if (!$this->logo_path) {
            return asset('images/placeholders/competition.svg');
        }

        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }

        return Storage::url($this->logo_path);
    }
}
