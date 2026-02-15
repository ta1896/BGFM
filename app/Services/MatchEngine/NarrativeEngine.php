<?php

namespace App\Services\MatchEngine;

use App\Models\MatchTickerTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NarrativeEngine
{
    /**
     * Generate a dynamic narrative text for a given event.
     */
    public function generate(string $eventType, array $data, string $locale = 'de', array $usedIds = []): string
    {
        $template = $this->pickTemplate($eventType, $locale, $usedIds);

        if (!$template) {
            return $this->getFallbackText($eventType, $data);
        }

        return $this->replaceTokens($template->text, $data);
    }

    /**
     * Pick a random template for the given event type, avoiding already used ones.
     */
    public function pickTemplate(string $eventType, string $locale, array $usedIds = []): ?MatchTickerTemplate
    {
        // Cache name for all templates of this type/locale
        $cacheKey = "ticker_templates_{$eventType}_{$locale}";

        $templates = \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($eventType, $locale) {
            return MatchTickerTemplate::where('event_type', $eventType)
                ->where('locale', $locale)
                ->get();
        });

        if ($templates->isEmpty()) {
            return null;
        }

        // Filter out used IDs if possible
        $available = $templates->whereNotIn('id', $usedIds);

        // Fallback to all templates if everything was already used
        if ($available->isEmpty()) {
            $available = $templates;
        }

        return $available->random();
    }

    /**
     * Replace tokens like {player}, {club}, {score} with actual data.
     */
    public function replaceTokens(string $text, array $data): string
    {
        $replacements = [];

        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $replacements["{{$key}}"] = (string) $value;
            }
        }

        return strtr($text, $replacements);
    }

    /**
     * Provide a basic fallback text if no template is found in the database.
     */
    public function getFallbackText(string $eventType, array $data): string
    {
        $playerName = $data['player'] ?? 'Spieler';
        $clubName = $data['club'] ?? 'Verein';

        return match ($eventType) {
            'goal' => "TOR! {$playerName} trifft für {$clubName}!",
            'yellow_card' => "Gelbe Karte für {$playerName}.",
            'red_card' => "Platzverweis! Rote Karte für {$playerName}.",
            'substitution' => "Wechsel bei {$clubName}: {$playerName} kommt neu ins Spiel.",
            'foul' => "Foulspiel von {$playerName}.",
            'chance' => "Großchance für {$playerName}!",
            default => "Ereignis: {$eventType} durch {$playerName} ({$clubName}).",
        };
    }
}
