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
        'mood',
        'commentator_style',
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

    public const MOODS = [
        'neutral' => 'Neutral (Standard)',
        'excited' => 'Aufgeregt / Emotional',
        'aggressive' => 'Aggressiv / Derby',
        'humorous' => 'Humorvoll / Locker',
        'frustrated' => 'Frustriert (Rückstand)',
        'crunch_time' => 'Crunch Time (Spannung)',
    ];

    public const STYLES = [
        'sachlich' => 'Sachlich & Informativ',
        'fanatiker' => 'Fanatisch & Subjektiv',
        'taktik' => 'Analytisch & Taktisch',
        'poetisch' => 'Blumig & Poetisch',
    ];
}
