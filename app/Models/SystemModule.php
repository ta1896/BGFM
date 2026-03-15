<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    protected $fillable = [
        'key',
        'name',
        'version',
        'description',
        'enabled',
        'module_path',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'config' => 'array',
        ];
    }
}
