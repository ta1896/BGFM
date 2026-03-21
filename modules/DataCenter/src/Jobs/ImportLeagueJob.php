<?php

namespace App\Modules\DataCenter\Jobs;

use App\Models\Club;
use App\Models\Player;
use App\Models\Country;
use App\Models\Season;
use App\Models\Competition;
use App\Models\CompetitionSeason;
use App\Models\SeasonClubRegistration;
use App\Models\SeasonClubStatistic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Modules\DataCenter\Services\ScraperService;
use Modules\DataCenter\Models\ImportLog;


class ImportLeagueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $leagueId,
        protected ?string $season = '24/25',
        protected ?int $logId = null
    ) {
    }

    public function handle()
    {
        $log = null;
        if ($this->logId) {
            $log = ImportLog::find($this->logId);
        }

        if ($log) {
            $log->update([
                'status' => 'running',
                'started_at' => now(),
                'season' => $this->season ?? $log->season, // Ensure season is synced
            ]);
        } else {
            $log = ImportLog::create([
                'league_id' => $this->leagueId,
                'season' => $this->season ?? '23/24',
                'status' => 'running',
                'started_at' => now(),
            ]);
        }

        try {
            $scraper = app(ScraperService::class);
            
            // Map League ID to ScraperFC Name
            $leagueMap = [
                'L1' => 'Germany Bundesliga',
                'L2' => 'Germany 2.Bundesliga',
                'GB1' => 'England Premier League',
                'GB2' => 'England EFL Championship',
                'ES1' => 'Spain La Liga',
                'ES2' => 'Spain La Liga 2',
                'IT1' => 'Italy Serie A',
                'IT2' => 'Italy Serie B',
                'FR1' => 'France Ligue 1',
                'FR2' => 'France Ligue 2',
                'NL1' => 'Netherlands Eredivisie',
                'PO1' => 'Portugal Primeira Liga',
                'TR1' => 'Turkiye Super Lig',
            ];

            $mappedLeague = $leagueMap[$this->leagueId] ?? $this->leagueId;
            $mappedSeason = $this->season ?? '24/25';

            Log::info("Starting Bulk Import for: {$mappedLeague} ({$mappedSeason}) - Sending ID: {$this->leagueId}");

            // CRITICAL: We MUST pass the ID (e.g. L1, GB1) to the scraper and not the human name
            $result = $scraper->scrapeLeague($mappedSeason, $this->leagueId);
            $playersData = $result['players'] ?? [];
            $totalPlayers = count($playersData);

            if ($totalPlayers === 0) {
                throw new \Exception("Keine Spieler-Daten von Transfermarkt erhalten für {$mappedLeague} ({$mappedSeason}).");
            }

            if ($log) {
                $log->update([
                    'message' => "Verarbeite {$totalPlayers} Spieler...",
                    'details' => ['total_players' => $totalPlayers, 'mapped_league' => $mappedLeague]
                ]);
            }

            $clubCount = 0;
            $playerCount = 0;
            $clubsProcessed = [];

            // Group players by club
            $playersByClub = [];
            foreach ($playersData as $playerData) {
                $clubName = $playerData['Club'] ?? $playerData['Team'] ?? 'Unknown Club';
                $playersByClub[$clubName][] = $playerData;
            }

            $totalClubs = count($playersByClub);
            $clubIndex = 0;
            $playerCount = 0;

            // --- step 1: Auto-Create Structure ---
            $leagueConfig = [
                'L1' => ['country' => 'Germany', 'iso' => 'DE', 'tier' => 1, 'name' => 'Bundesliga'],
                'L2' => ['country' => 'Germany', 'iso' => 'DE', 'tier' => 2, 'name' => '2. Bundesliga'],
                'GB1' => ['country' => 'England', 'iso' => 'GB', 'tier' => 1, 'name' => 'Premier League'],
                'ES1' => ['country' => 'Spain', 'iso' => 'ES', 'tier' => 1, 'name' => 'La Liga'],
                'IT1' => ['country' => 'Italy', 'iso' => 'IT', 'tier' => 1, 'name' => 'Serie A'],
                'FR1' => ['country' => 'France', 'iso' => 'FR', 'tier' => 1, 'name' => 'Ligue 1'],
            ];

            $conf = $leagueConfig[$this->leagueId] ?? [
                'country' => 'Unknown', 'iso' => 'XX', 'tier' => 1, 'name' => $mappedLeague
            ];

            // 1.1 Country
            $country = Country::firstOrCreate(
                ['name' => $conf['country']],
                ['iso_code' => $conf['iso']]
            );

            // 1.2 Season
            $yearPart = explode('/', $mappedSeason)[0];
            $fullYearStart = "20" . $yearPart;
            $fullYearEnd = "20" . (intval($yearPart) + 1);
            $seasonName = "{$fullYearStart}/{$fullYearEnd}";

            $season = Season::firstOrCreate(
                ['name' => $seasonName],
                [
                    'start_date' => "{$fullYearStart}-07-01",
                    'end_date' => "{$fullYearEnd}-06-30",
                    'is_current' => $mappedSeason === '24/25',
                ]
            );

            // 1.3 Competition
            $competition = Competition::firstOrCreate(
                ['country_id' => $country->id, 'name' => $conf['name'], 'type' => 'league'],
                ['tier' => $conf['tier'], 'is_active' => true]
            );

            // 1.4 CompetitionSeason
            $format = 'league_' . $totalClubs;
            $competitionSeason = CompetitionSeason::firstOrCreate(
                ['competition_id' => $competition->id, 'season_id' => $season->id],
                [
                    'format' => $format,
                    'points_win' => 3, 'points_draw' => 1, 'points_loss' => 0,
                    'is_finished' => false,
                ]
            );

            foreach ($playersByClub as $clubName => $clubPlayers) {
                $clubIndex++;
                if ($log) {
                    $log->update([
                        'message' => "[$clubIndex/$totalClubs] Verarbeite Kader von $clubName...",
                        'details' => array_merge($log->details ?? [], [
                            'current_club' => $clubName,
                            'club_index' => $clubIndex,
                            'total_clubs' => $totalClubs,
                            'players_in_club' => count($clubPlayers),
                            'processed_players' => $playerCount
                        ])
                    ]);
                }

                // Create/Update Club
                $club = Club::updateOrCreate(
                    ['name' => $clubName],
                    [
                        'slug' => Str::slug($clubName),
                        'is_cpu' => true,
                        'league' => $this->leagueId,
                        'league_id' => $competition->id,
                    ]
                );

                // Register Club to Season
                SeasonClubRegistration::firstOrCreate([
                    'competition_season_id' => $competitionSeason->id,
                    'club_id' => $club->id,
                ]);

                // Initialize Stats
                SeasonClubStatistic::updateOrCreate(
                    [
                        'competition_season_id' => $competitionSeason->id,
                        'club_id' => $club->id,
                    ],
                    [
                        'matches_played' => 0,
                        'wins' => 0, 'draws' => 0, 'losses' => 0,
                        'goals_for' => 0, 'goals_against' => 0, 'goal_diff' => 0,
                        'points' => 0,
                    ]
                );

                foreach ($clubPlayers as $playerData) {
                    try {
                        $this->importPlayer($club, $playerData);
                        $playerCount++;
                    } catch (\Exception $e) {
                        Log::error("Fehler beim Import von " . ($playerData['Player'] ?? 'unbekannt') . ": " . $e->getMessage());
                    }
                }
            }

            if ($log) {
                $log->update([
                    'status' => 'completed',
                    'message' => "Import erfolgreich abgeschlossen. {$totalClubs} Vereine und {$playerCount} Spieler verarbeitet.",
                    'finished_at' => now(),
                ]);
            }

            Log::info("Bulk Import Finished: {$this->leagueId}. Processed {$playerCount} players.");
        } catch (\Exception $e) {
            Log::error("Bulk Import failed for {$this->leagueId}: " . $e->getMessage());
            if ($log) {
                $log->update([
                    'status' => 'failed',
                    'message' => Str::limit($e->getMessage(), 500),
                    'finished_at' => now(),
                ]);
            }
            throw $e;
        }
    }

    protected function importPlayer(Club $club, array $data): void
    {
        // Bulk Scraper keys: Player, Club, Position, Age, Market Value, Nationality, URL
        $fullName = $data['Player'] ?? $data['Name'] ?? 'Unknown Player';
        $parts = explode(' ', $fullName, 2);
        $firstName = $parts[0];
        $lastName = $parts[1] ?? '';

        $val = $data['Market Value'] ?? $data['Value'] ?? 0;
        Log::info("Importing Player: {$fullName} - Received Value: " . json_encode($val));

        Player::updateOrCreate(
            [
                'club_id' => $club->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ],
            [
                'position' => $this->mapPosition($data['Position'] ?? null),
                'age' => (int) ($data['Age'] ?? 25),
                'market_value' => (float) ($data['Market Value'] ?? $data['Value'] ?? 0.0),
                'status' => 'active',
                'overall' => rand(65, 80),
                'potential' => rand(75, 90),
                'transfermarkt_id' => $this->extractIdFromUrl($data['URL'] ?? null),
                'transfermarkt_url' => $data['URL'] ?? null,
                'pace' => rand(60, 90),
                'shooting' => rand(50, 85),
                'passing' => rand(50, 85),
                'defending' => rand(40, 85),
                'physical' => rand(60, 90),
                'stamina' => rand(70, 100),
            ]
        );
    }

    protected function extractIdFromUrl(?string $url): ?string
    {
        if (!$url) return null;
        if (preg_match('/spieler\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function mapPosition(?string $pos): string
    {
        if (!$pos) return 'ZM';
        
        $map = [
            // English
            'Goalkeeper' => 'TW', 'GK' => 'TW',
            'Centre-Back' => 'IV', 'CB' => 'IV',
            'Left-Back' => 'LV', 'LB' => 'LV', 'LWB' => 'LV',
            'Right-Back' => 'RV', 'RB' => 'RV', 'RWB' => 'RV',
            'Defensive Midfield' => 'DM', 'CDM' => 'DM',
            'Central Midfield' => 'ZM', 'CM' => 'ZM',
            'Attacking Midfield' => 'OM', 'CAM' => 'OM', 'ZOM' => 'ZOM',
            'Left Midfield' => 'LM', 'Right Midfield' => 'RM',
            'Left Winger' => 'LW', 'Right Winger' => 'RW',
            'Centre-Forward' => 'ST', 'ST' => 'ST', 'CF' => 'ST',
            'Second Striker' => 'MS', 'SS' => 'MS',
            // German fallback
            'Torwart' => 'TW', 'Innenverteidiger' => 'IV', 'Linker Verteidiger' => 'LV', 'Rechter Verteidiger' => 'RV',
            'Defensives Mittelfeld' => 'DM', 'Zentrales Mittelfeld' => 'ZM', 'Offensives Mittelfeld' => 'OM',
            'Linksaußen' => 'LW', 'Rechtsaußen' => 'RW', 'Mittelstürmer' => 'ST', 'Hängende Spitze' => 'MS',
        ];

        return $map[$pos] ?? 'ZM';
    }

    protected function parseValue(mixed $val): float
    {
        if (!$val) return 0.0;
        if (is_numeric($val)) return (float) $val;
        
        // Final fallback for string values
        $clean = str_replace(['€', 'm', 'k', ' ', ','], ['', 'M', 'K', '', '.'], (string)$val);
        $floatVal = (float) $clean;
        
        if (str_contains(strtoupper($clean), 'M')) return $floatVal; // Already normalized
        if (str_contains(strtoupper($clean), 'K')) return $floatVal / 1000.0;

        return $floatVal;
    }
}

