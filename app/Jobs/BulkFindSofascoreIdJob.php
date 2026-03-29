<?php

namespace App\Jobs;

use App\Models\Player;
use App\Services\SofascoreLinkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\DataCenter\Models\ImportLog;

class BulkFindSofascoreIdJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    public function handle(SofascoreLinkService $linker): void
    {
        $players = Player::whereNull('sofascore_id')
            ->orWhere('sofascore_id', '')
            ->with('club')
            ->get();

        $total = $players->count();

        $log = ImportLog::create([
            'league_id' => 'bulk_find_sofascore_id',
            'season'    => date('Y/m/d H:i'),
            'status'    => 'running',
            'started_at' => now(),
            'details'   => [
                'total_players' => $total,
                'processed'     => 0,
                'linked'        => 0,
                'skipped'       => 0,
                'failed'        => 0,
                'current_player' => '',
            ],
        ]);

        Log::info("BulkFindSofascoreId: starting for {$total} players without sofascore_id.");

        $processed = 0;
        $linked    = 0;
        $skipped   = 0;
        $failed    = 0;

        foreach ($players as $player) {
            $processed++;

            if ($processed % 10 === 0 || $processed === $total) {
                $log->update([
                    'details' => [
                        'total_players'  => $total,
                        'processed'      => $processed,
                        'linked'         => $linked,
                        'skipped'        => $skipped,
                        'failed'         => $failed,
                        'current_player' => $player->full_name,
                    ],
                ]);
            }

            try {
                $result = $linker->linkPlayer($player);

                if ($result['linked']) {
                    $linked++;
                    Log::info("BulkFindSofascoreId: linked {$player->full_name} → {$result['id']}");
                } else {
                    $skipped++;
                    Log::debug("BulkFindSofascoreId: no match for {$player->full_name} ({$result['reason']})");
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::error("BulkFindSofascoreId: error on player {$player->id}: {$e->getMessage()}");
            }

            // Rate-limit: avoid hammering the Sofascore API
            sleep(1);
        }

        $log->update([
            'status'      => 'completed',
            'finished_at' => now(),
            'message'     => "Sofascore ID Finder abgeschlossen. {$linked} verknüpft, {$skipped} kein Treffer, {$failed} Fehler.",
            'details'     => [
                'total_players'  => $total,
                'processed'      => $processed,
                'linked'         => $linked,
                'skipped'        => $skipped,
                'failed'         => $failed,
                'current_player' => 'Done',
            ],
        ]);

        Log::info("BulkFindSofascoreId: done. linked={$linked}, skipped={$skipped}, failed={$failed}");
    }
}
