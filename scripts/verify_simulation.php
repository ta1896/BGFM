<?php

use App\Models\GameMatch;
use App\Services\MatchSimulationService;
use Illuminate\Support\Facades\DB;

// Ensure we have a match to simulate
$match = GameMatch::where('status', 'scheduled')->first();

if (!$match) {
    echo "No scheduled match found. Creating a test match...\n";
    // Setup test data if needed, but for now assume DB has data or we fail gracefully
    exit(1);
}

echo "Simulating Match ID: {$match->id}...\n";
echo "Home: {$match->homeClub->name} vs Away: {$match->awayClub->name}\n";

/** @var MatchSimulationService $service */
$service = app(MatchSimulationService::class);

$start = microtime(true);
$simulatedMatch = $service->simulate($match);
$duration = microtime(true) - $start;

echo "\nSimulation completed in " . round($duration * 1000, 2) . "ms\n";
echo "Score: {$simulatedMatch->home_score} - {$simulatedMatch->away_score}\n";

$events = $simulatedMatch->events()->orderBy('minute')->get();
echo "\nEvents:\n";
foreach ($events as $event) {
    $playerName = $event->player->lastname ?? $event->player->firstname ?? 'Unknown';
    echo "{$event->minute}': {$event->event_type} by {$playerName} ({$event->club->name})\n";
}

echo "\nStats Check:\n";
$stats = $simulatedMatch->playerStats()->take(5)->get();
foreach ($stats as $stat) {
    $playerName = $stat->player->lastname ?? 'Unknown';
    echo "{$playerName}: Rating {$stat->rating}, Shots {$stat->shots}\n";
}
