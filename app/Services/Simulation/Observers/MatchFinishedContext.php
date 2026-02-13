<?php

namespace App\Services\Simulation\Observers;

use App\Models\GameMatch;
use Illuminate\Support\Collection;

class MatchFinishedContext
{
    public function __construct(
        public readonly GameMatch $match,
        public readonly Collection $homePlayers,
        public readonly Collection $awayPlayers
    ) {
    }
}
