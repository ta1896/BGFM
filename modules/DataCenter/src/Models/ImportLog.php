<?php

namespace Modules\DataCenter\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'league_id',
        'season',
        'status',
        'message',
        'details',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'details' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
