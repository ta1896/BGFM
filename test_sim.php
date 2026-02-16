<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\GameMatch;
use App\Services\LiveMatchTickerService;

$id = 546;
$match = GameMatch::find($id);
if (!$match) {
    echo "Match $id not found\n";
    exit(1);
}

// Reset match to scheduled and clear scores/events for a fresh run if needed
$match->update([
    'status' => 'scheduled',
    'home_score' => 0,
    'away_score' => 0,
    'live_minute' => 0,
    'live_paused' => false,
]);

DB::table('match_live_minute_snapshots')->where('match_id', $id)->delete();
DB::table('match_live_actions')->where('match_id', $id)->delete();
DB::table('match_events')->where('match_id', $id)->delete();
DB::table('match_live_player_states')->where('match_id', $id)->delete();
DB::table('match_live_team_states')->where('match_id', $id)->delete();

echo "Starting simulation for match " . $match->id . "\n";

try {
    $tickerService = app(LiveMatchTickerService::class);
    $tickerService->tick($match, 90);
    echo "Simulation finished\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . " LINE: " . $e->getLine() . "\n";
    if ($e instanceof \Illuminate\Database\QueryException) {
        echo "SQL: " . $e->getSql() . "\n";
    }
    echo $e->getTraceAsString() . "\n";
}
