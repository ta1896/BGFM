<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$matchId = 543;
echo "Inspecting Match $matchId Live Actions:\n";
$actions = \App\Models\MatchLiveAction::where('match_id', $matchId)->get(['action_type', 'narrative']);
foreach ($actions as $a) {
    echo "Type: {$a->action_type} | Narrative: " . ($a->narrative ?: 'MISSING') . "\n";
}

echo "\nInspecting Match $matchId Events:\n";
$events = \App\Models\MatchEvent::where('match_id', $matchId)->get(['event_type', 'narrative']);
foreach ($events as $e) {
    echo "Type: {$e->event_type} | Narrative: " . ($e->narrative ?: 'MISSING') . "\n";
}
