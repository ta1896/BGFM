<?php

namespace App\Jobs;

use App\Models\Player;
use App\Models\Club;
use App\Modules\DataCenter\Services\ScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SyncPlayerTransferHistoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $playerId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $player = Player::find($this->playerId);
        if (!$player || !$player->transfermarkt_url) {
            return;
        }

        $scraper = app(ScraperService::class);
        $historyData = $scraper->getPlayerTransferHistory($player->transfermarkt_url);

        if (empty($historyData)) {
            Log::info("SyncPlayerTransferHistoryJob: No data for Player {$player->id}");
            return;
        }

        foreach ($historyData as $data) {
            $leftClubId = isset($data['left_club_tm_id']) ? Club::where('transfermarkt_id', $data['left_club_tm_id'])->value('id') : null;
            if (!$leftClubId && !empty($data['left_club_name'])) {
                $leftClubId = Club::where('name', $data['left_club_name'])->value('id');
            }

            $joinedClubId = isset($data['joined_club_tm_id']) ? Club::where('transfermarkt_id', $data['joined_club_tm_id'])->value('id') : null;
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
