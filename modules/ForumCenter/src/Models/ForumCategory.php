<?php

namespace App\Modules\ForumCenter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumCategory extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];

    public function forums(): HasMany
    {
        return $this->hasMany(Forum::class)->orderBy('sort_order');
    }
}
