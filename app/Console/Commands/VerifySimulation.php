<?php

namespace App\Console\Commands;

use App\Models\GameMatch;
use App\Models\Club;
use App\Models\Player;
use App\Services\MatchSimulationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifySimulation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:simulation {match_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs a simulation for a specific match or creates a test match if none exists.';

    /**
     * Execute the console command.
     */
    public function handle(MatchSimulationService $service)
    {
        $matchId = $this->argument('match_id');

        if ($matchId) {
            $match = GameMatch::find($matchId);
        }
        else {
            $match = GameMatch::where('status', 'scheduled')->first();
        }

        if (!$match) {
            $this->info("No scheduled match found. creating test data...");
            // Create dummy clubs and players if needed
            // For now, let's assume we can fail if no data
            $this->error("No match found!");
            return 1;
        }

        $this->info("Simulating Match ID: {$match->id}");
        $this->info("Home: {$match->homeClub->name} vs Away: {$match->awayClub->name}");

        $start = microtime(true);
        try {
            $simulatedMatch = $service->simulate($match);
        }
        catch (\Throwable $e) {
            $this->error("Simulation failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
        $duration = microtime(true) - $start;

        $this->info("\nSimulation completed in " . round($duration * 1000, 2) . "ms");
        $this->info("Score: {$simulatedMatch->home_score} - {$simulatedMatch->away_score}");

        $events = $simulatedMatch->events()->orderBy('minute')->get();
        $this->info("\nEvents:");
        foreach ($events as $event) {
            $playerName = $event->player->lastname ?? $event->player->firstname ?? 'Unknown';
            $this->line("{$event->minute}': {$event->event_type} by {$playerName} ({$event->club->name})");
        }

        return 0;
    }
}
