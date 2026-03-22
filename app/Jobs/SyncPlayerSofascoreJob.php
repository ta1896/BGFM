<?php

namespace App\Jobs;

use App\Models\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncPlayerSofascoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Player $player)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->player->sofascore_id) {
            return;
        }

        try {
            $bioResponse = Http::timeout(15)->get("https://www.sofascore.com/api/v1/player/{$this->player->sofascore_id}");
            $attrResponse = Http::timeout(15)->get("https://www.sofascore.com/api/v1/player/{$this->player->sofascore_id}/attribute-overviews");
            
            if (!$bioResponse->successful() || !$attrResponse->successful()) {
                Log::warning("SyncPlayerSofascoreJob: API failed for Player {$this->player->id} (ID: {$this->player->sofascore_id})");
                return;
            }
            $bioData = $bioResponse->json();
            $attrData = $attrResponse->json();
            
            $playerInfo = $bioData['player'] ?? [];
            $overviews = $attrData['playerAttributeOverviews'] ?? [];

            if (empty($overviews) && empty($playerInfo)) {
                return;
            }

            $updateData = [];

            // Parse Bio Data
            if (!empty($playerInfo)) {
                if (isset($playerInfo['country']['name'])) {
                    $updateData['nationality'] = $playerInfo['country']['name'];
                }
                if (isset($playerInfo['height'])) {
                    $updateData['height'] = (int) $playerInfo['height'];
                }
                if (isset($playerInfo['shirtNumber'])) {
                    $updateData['shirt_number'] = (int) $playerInfo['shirtNumber'];
                }
                if (isset($playerInfo['preferredFoot'])) {
                    $footMap = [
                        'Left' => 'left',
                        'Right' => 'right',
                        'Both' => 'both',
                    ];
                    if (isset($footMap[$playerInfo['preferredFoot']])) {
                        $updateData['preferred_foot'] = $footMap[$playerInfo['preferredFoot']];
                    }
                }
                if (isset($playerInfo['dateOfBirthTimestamp'])) {
                    $updateData['birthday'] = Carbon::createFromTimestamp($playerInfo['dateOfBirthTimestamp'])->format('Y-m-d');
                }
            }

            // Parse Attribute Data
            if (!empty($overviews)) {
                $current = $overviews[0];

                $isGkOverview = isset($current['saves']) || isset($current['aerial']) || isset($current['ballDistribution']) || (isset($current['position']) && $current['position'] === 'G');

                if ($isGkOverview) {
                    // Goalkeeper Mapping
                    $updateData['attr_attacking'] = $current['aerial'] ?? $current['attacking'] ?? 50;
                    $updateData['attr_technical'] = $current['ballDistribution'] ?? $current['technical'] ?? 50;
                    $updateData['attr_tactical'] = $current['tactical'] ?? 50;
                    $updateData['attr_defending'] = $current['saves'] ?? $current['defending'] ?? 50;
                    $updateData['attr_creativity'] = $current['anticipation'] ?? $current['creativity'] ?? 50;
                } else {
                    // Outfield Player Mapping
                    $updateData['attr_attacking'] = $current['attacking'] ?? 50;
                    $updateData['attr_technical'] = $current['technical'] ?? 50;
                    $updateData['attr_tactical'] = $current['tactical'] ?? 50;
                    $updateData['attr_defending'] = $current['defending'] ?? 50;
                    $updateData['attr_creativity'] = $current['creativity'] ?? 50;
                }
            }

            if (!empty($updateData)) {
                $this->player->update($updateData);
            }
        } catch (\Exception $e) {
            Log::error("SyncPlayerSofascoreJob Error for Player {$this->player->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
