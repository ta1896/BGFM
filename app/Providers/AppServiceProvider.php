<?php

namespace App\Providers;

use App\Services\Simulation\Observers\ApplyMatchAvailabilityObserver;
use App\Services\Simulation\Observers\AggregatePlayerCompetitionStatsObserver;
use App\Services\Simulation\Observers\MatchFinishedObserverPipeline;
use App\Services\Simulation\Observers\RebuildMatchPlayerStatsObserver;
use App\Services\Simulation\Observers\SettleMatchFinanceObserver;
use App\Services\Simulation\Observers\UpdateCompetitionAfterMatchObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MatchFinishedObserverPipeline::class, function ($app): MatchFinishedObserverPipeline {
            return new MatchFinishedObserverPipeline([
                $app->make(RebuildMatchPlayerStatsObserver::class),
                $app->make(AggregatePlayerCompetitionStatsObserver::class),
                $app->make(ApplyMatchAvailabilityObserver::class),
                $app->make(UpdateCompetitionAfterMatchObserver::class),
                $app->make(SettleMatchFinanceObserver::class),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
