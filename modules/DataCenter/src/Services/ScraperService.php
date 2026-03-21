<?php

namespace App\Modules\DataCenter\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScraperService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.scraper_service.url', 'http://scraper-service:8000');
    }

    /**
     * Get clubs for a competition and season.
     */
    public function getCompetitionClubs(string $competition, string $year): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/transfermarkt/clubs", [
                'league' => $competition, // Using the name/slug for ScraperFC
                'year' => $year
            ]);
            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error("Scraper Service Error (Get Clubs): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get player links for a competition and season.
     */
    public function scrapeLeague(string $year, string $league)
    {
        $response = Http::timeout(1200)->get("{$this->baseUrl}/transfermarkt/scrape-league", [
            'year' => $year,
            'league' => $league
        ]);

        return $response->successful() ? $response->json() : ['players' => []];
    }

    /**
     * Get data for a single player.
     */
    public function getPlayerData(string $url): ?array
    {
        try {
            $response = Http::timeout(60)->get("{$this->baseUrl}/transfermarkt/player-data", [
                'url' => $url
            ]);
            return $response->successful() ? $response->json()['player'] ?? null : null;
        } catch (\Exception $e) {
            Log::error("Scraper Service Error (Get Player Data): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get players for a competition and season (Batch).
     */
    public function getCompetitionPlayers(string $competition, string $year): array

    {
        try {
            $response = Http::timeout(600)->get("{$this->baseUrl}/transfermarkt/players", [
                'league' => $competition,
                'year' => $year
            ]);
            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error("Scraper Service Error (Get Players): " . $e->getMessage());
            return [];
        }
    }
}
