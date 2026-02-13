<?php

namespace App\Services\Simulation\Observers;

interface MatchFinishedObserver
{
    public function handle(MatchFinishedContext $context): void;
}
