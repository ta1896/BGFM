<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchTickerTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'text',
        'priority',
        'locale',
    ];

    public const EVENT_TYPES = [
        'goal' => 'Tor',
        'chance' => 'Großchance',
        'foul' => 'Foulspiel',
        'yellow_card' => 'Gelbe Karte',
        'red_card' => 'Rote Karte',
        'injury' => 'Verletzung',
        'substitution' => 'Auswechslung',
        'phase' => 'Spielphase (Start/Ende)',
    ];
}
