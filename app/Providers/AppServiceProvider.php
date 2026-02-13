<?php

namespace App\Providers;

use App\Services\SimulationSettingsService;
use App\Services\Simulation\Observers\ApplyMatchAvailabilityObserver;
use App\Services\Simulation\Observers\AggregatePlayerCompetitionStatsObserver;
use App\Services\Simulation\Observers\MatchFinishedObserverPipeline;
use App\Services\Simulation\Observers\RebuildMatchPlayerStatsObserver;
use App\Services\Simulation\Observers\SettleMatchFinanceObserver;
use App\Services\Simulation\Observers\UpdateCompetitionAfterMatchObserver;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MatchFinishedObserverPipeline::class, function ($app): MatchFinishedObserverPipeline {
            if (!(bool) config('simulation.observers.match_finished.enabled', true)) {
                return new MatchFinishedObserverPipeline([]);
            }

            $observers = [];

            if ((bool) config('simulation.observers.match_finished.rebuild_match_player_stats', true)) {
                $observers[] = $app->make(RebuildMatchPlayerStatsObserver::class);
            }
            if ((bool) config('simulation.observers.match_finished.aggregate_player_competition_stats', true)) {
                $observers[] = $app->make(AggregatePlayerCompetitionStatsObserver::class);
            }
            if ((bool) config('simulation.observers.match_finished.apply_match_availability', true)) {
                $observers[] = $app->make(ApplyMatchAvailabilityObserver::class);
            }
            if ((bool) config('simulation.observers.match_finished.update_competition_after_match', true)) {
                $observers[] = $app->make(UpdateCompetitionAfterMatchObserver::class);
            }
            if ((bool) config('simulation.observers.match_finished.settle_match_finance', true)) {
                $observers[] = $app->make(SettleMatchFinanceObserver::class);
            }

            return new MatchFinishedObserverPipeline($observers);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $this->app->make(SimulationSettingsService::class)->applyRuntimeOverrides();
        } catch (Throwable) {
            // Ignore early boot/migration phases where DB tables may not exist yet.
        }
    }
}
