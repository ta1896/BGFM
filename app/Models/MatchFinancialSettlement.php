<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchFinancialSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'home_income',
        'home_expense',
        'away_income',
        'away_expense',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'home_income' => 'decimal:2',
            'home_expense' => 'decimal:2',
            'away_income' => 'decimal:2',
            'away_expense' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }
}
