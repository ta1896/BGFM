<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubRecord extends Model
{
    use HasFactory;

    protected $table = 'club_records';

    protected $fillable = [
        'club_id',
        'record_key',
        'record_value',
        'reference_type',
        'reference_id',
        'achieved_at',
    ];

    protected $casts = [
        'achieved_at' => 'date',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
