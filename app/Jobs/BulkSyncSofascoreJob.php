<?php

namespace App\Jobs;

use App\Models\Player;
use App\Models\Club;
use App\Models\PlayerTransferHistory;
use App\Modules\DataCenter\Services\ScraperService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Modules\DataCenter\Models\ImportLog;

class BulkSyncSofascoreJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Sync mode: 'both' | 'sofascore' | 'transfermarkt'
     */
    public string $mode;

    /**
     * Create a new job instance.
     */
    public function __construct(string $mode = 'both')
    {
        $this->mode = in_array($mode, ['both', 'sofascore', 'transfermarkt']) ? $mode : 'both';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $scraper = app(ScraperService::class);
        $players = Player::whereNotNull('sofascore_id')
            ->orWhereNotNull('transfermarkt_url')
            ->get();
        $totalPlayers = $players->count();
        
        $log = ImportLog::where('league_id', 'bulk_sync_sofascore')
            ->where('status', 'running')
            ->latest()
            ->first();

        if (!$log) {
            $log = ImportLog::create([
                'league_id' => 'bulk_sync_sofascore',
                'season' => date('Y/m/d H:i'),
                'status' => 'running',
                'started_at' => now(),
                'details' => [
                    'total_players' => $totalPlayers,
                    'processed' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'success_transfers' => 0,
                    'failed_transfers' => 0,
                    'current_player' => ''
                ]
            ]);
        } else {
            // Update total players if reusing a log
            $log->update([
                'details' => array_merge($log->details, ['total_players' => $totalPlayers])
            ]);
        }

        Log::info("Starting Extended Bulk Sync (Sofascore & Transfers) for {$totalPlayers} players.");

        $successCount = 0;
        $failCount = 0;
        $successTransfers = 0;
        $failTransfers = 0;
        $processed = 0;

        foreach ($players as $player) {
            $processed++;
            $isPlayer28 = ($player->id == 28);
            
            try {
                if ($processed % 10 === 0 || $processed === $totalPlayers) {
                    $log->update([
                        'details' => array_merge($log->details, [
                            'processed' => $processed,
                            'success' => $successCount,
                            'failed' => $failCount,
                            'success_transfers' => $successTransfers,
                            'failed_transfers' => $failTransfers,
                            'current_player' => $player->full_name
                        ])
                    ]);
                }

                // --- 1. SOFASCORE SYNC ---
                if ($this->mode !== 'transfermarkt' && $player->sofascore_id) {
                    if ($isPlayer28) Log::info("BulkSync: Processing Sofascore for Player 28 ({$player->full_name})");
                    
                    $bioResponse = Http::timeout(15)->get("https://www.sofascore.com/api/v1/player/{$player->sofascore_id}");
                    $attrResponse = Http::timeout(15)->get("https://www.sofascore.com/api/v1/player/{$player->sofascore_id}/attribute-overviews");
                    
                    if (!$bioResponse->successful() || !$attrResponse->successful()) {
                        Log::warning("BulkSync: Failed fetching Sofascore data for Player {$player->id}. Bio: {$bioResponse->status()}, Attr: {$attrResponse->status()}");
                        $failCount++;
                    } else {
                        $bioData = $bioResponse->json();
                        $attrData = $attrResponse->json();
                        
                        $playerInfo = $bioData['player'] ?? [];
                        $overviews = $attrData['playerAttributeOverviews'] ?? [];

                        if (empty($overviews) && empty($playerInfo)) {
                            Log::info("BulkSync: No Sofascore data found for Player {$player->id}");
                            $failCount++;
                        } else {
                            $updateData = [];
                            if (!empty($playerInfo)) {
                                if (isset($playerInfo['country']['name'])) $updateData['nationality'] = $playerInfo['country']['name'];
                                if (isset($playerInfo['height'])) $updateData['height'] = (int) $playerInfo['height'];
                                if (isset($playerInfo['shirtNumber'])) $updateData['shirt_number'] = (int) $playerInfo['shirtNumber'];
                                if (isset($playerInfo['preferredFoot'])) {
                                    $footMap = ['Left' => 'left', 'Right' => 'right', 'Both' => 'both'];
                                    if (isset($footMap[$playerInfo['preferredFoot']])) $updateData['preferred_foot'] = $footMap[$playerInfo['preferredFoot']];
                                }
                                if (isset($playerInfo['dateOfBirthTimestamp'])) {
                                    $updateData['birthday'] = Carbon::createFromTimestamp($playerInfo['dateOfBirthTimestamp'])->format('Y-m-d');
                                }
                            }

                            if (!empty($overviews)) {
                                $current = $overviews[0];
                                if (isset($current['saves'])) {
                                    $updateData['attr_attacking'] = $current['aerial'] ?? 50;
                                    $updateData['attr_technical'] = $current['ballDistribution'] ?? 50;
                                    $updateData['attr_tactical'] = $current['tactical'] ?? 50;
                                    $updateData['attr_defending'] = $current['saves'] ?? 50;
                                    $updateData['attr_creativity'] = $current['anticipation'] ?? 50;
                                } else {
                                    $updateData['attr_attacking'] = $current['attacking'] ?? 50;
                                    $updateData['attr_technical'] = $current['technical'] ?? 50;
                                    $updateData['attr_tactical'] = $current['tactical'] ?? 50;
                                    $updateData['attr_defending'] = $current['defending'] ?? 50;
                                    $updateData['attr_creativity'] = $current['creativity'] ?? 50;
                                }
                            }

                            if (!empty($updateData)) {
                                if ($isPlayer28) Log::info("BulkSync: Updating Player 28 with: " . json_encode($updateData));
                                $player->update($updateData);
                            }
                            $successCount++;
                        }
                    }
                }

                // --- 2. TRANSFERMARKT SYNC ---
                $tmId = $player->transfermarkt_id;
                $tmUrl = $player->transfermarkt_url;

                if ($this->mode !== 'sofascore' && ($tmId || $tmUrl)) {
                    if ($isPlayer28) Log::info("BulkSync: Processing Transfers for Player 28 ({$player->full_name})");

                    $historyData = [];
                    if ($tmId) {
                        $historyData = $scraper->getPlayerTransferHistoryById($tmId);
                    }

                    if (empty($historyData) && $tmUrl) {
                        $historyData = $scraper->getPlayerTransferHistory($tmUrl);
                    }

                    if (empty($historyData)) {
                        Log::warning("BulkSync: No transfer data for Player {$player->id}", [
                            'player' => $player->full_name,
                            'tm_id' => $tmId,
                            'tm_url' => $tmUrl,
                        ]);
                        $failTransfers++;
                    } else {
                        foreach ($historyData as $data) {
                            $leftClubId = null;
                            $joinedClubId = null;

                            // 1. Try matching by TM ID
                            if (isset($data['left_club_tm_id'])) {
                                $leftClubId = Club::where('transfermarkt_id', $data['left_club_tm_id'])->value('id');
                            }
                            if (isset($data['joined_club_tm_id'])) {
                                $joinedClubId = Club::where('transfermarkt_id', $data['joined_club_tm_id'])->value('id');
                            }

                            // 2. Fallback: Match by Name
                            if (!$leftClubId && !empty($data['left_club_name'])) {
                                $leftClubId = Club::where('name', $data['left_club_name'])->value('id');
                            }
                            if (!$joinedClubId && !empty($data['joined_club_name'])) {
                                $joinedClubId = Club::where('name', $data['joined_club_name'])->value('id');
                            }

                            $player->transferHistories()->updateOrCreate(
                                [
                                    'season' => $data['season'],
                                    'transfer_date' => Carbon::parse($data['transfer_date'])->format('Y-m-d'),
                                    'left_club_name' => $data['left_club_name'] ?? 'Unbekannt',
                                    'joined_club_name' => $data['joined_club_name'] ?? 'Unbekannt',
                                ],
                                [
                                    'left_club_tm_id' => $data['left_club_tm_id'] ?? null,
                                    'left_club_id' => $leftClubId,
                                    'joined_club_tm_id' => $data['joined_club_tm_id'] ?? null,
                                    'joined_club_id' => $joinedClubId,
                                    'market_value' => $this->parseValue($data['market_value'] ?? null),
                                    'fee' => $data['fee'] ?? '?',
                                    'is_loan' => $data['is_loan'] ?? false,
                                ]
                            );
                        }
                        $successTransfers++;
                    }
                }

                // Prevent rate limiting & avoid timeouts
                sleep(1);
            } catch (\Exception $e) {
                Log::error("BulkSync Error on Player {$player->id}: " . $e->getMessage());
                $failCount++;
                sleep(1);
            }
        }

        $log->update([
            'status' => 'completed',
            'finished_at' => now(),
            'message' => "Bulk Sync abgeschlossen. Bio/Attr: {$successCount} OK, {$failCount} Fehl; Transfers: {$successTransfers} OK, {$failTransfers} Fehl.",
            'details' => [
                'total_players' => $totalPlayers,
                'processed' => $processed,
                'success' => $successCount,
                'failed' => $failCount,
                'success_transfers' => $successTransfers,
                'failed_transfers' => $failTransfers,
                'current_player' => 'Done'
            ]
        ]);

        Log::info("Bulk Sync finished. Success Bio/Attr: {$successCount}, Transfers: {$successTransfers}");
    }

    private function parseValue(?string $value): ?int
    {
        if (!$value || $value === '?' || $value === '-') return null;
        $value = str_replace(['.', ','], ['', '.'], $value);
        $factor = 1;
        if (Str::contains($value, 'Mio')) $factor = 1000000;
        elseif (Str::contains($value, 'Tsd')) $factor = 1000;
        $amount = (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        return (int) ($amount * $factor);
    }
}
