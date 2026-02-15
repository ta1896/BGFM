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
    public function generate(string $eventType, array $data, string $locale = 'de'): string
    {
        $template = $this->pickTemplate($eventType, $locale);

        if (!$template) {
            return $this->getFallbackText($eventType, $data);
        }

        return $this->replaceTokens($template->text, $data);
    }

    /**
     * Pick a random template for the given event type.
     */
    private function pickTemplate(string $eventType, string $locale): ?MatchTickerTemplate
    {
        return MatchTickerTemplate::where('event_type', $eventType)
            ->where('locale', $locale)
            ->inRandomOrder()
            ->first();
    }

    /**
     * Replace tokens like {player}, {club}, {score} with actual data.
     */
    private function replaceTokens(string $text, array $data): string
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
    private function getFallbackText(string $eventType, array $data): string
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
