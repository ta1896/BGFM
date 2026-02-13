<?php

namespace App\Services\Simulation\Observers;

use InvalidArgumentException;

class MatchFinishedObserverPipeline
{
    /**
     * @param array<int, MatchFinishedObserver> $observers
     */
    public function __construct(
        private readonly array $observers
    ) {
        foreach ($this->observers as $observer) {
            if (!$observer instanceof MatchFinishedObserver) {
                throw new InvalidArgumentException('All pipeline observers must implement MatchFinishedObserver.');
            }
        }
    }

    public function process(MatchFinishedContext $context): void
    {
        foreach ($this->observers as $observer) {
            $observer->handle($context);
        }
    }

    /**
     * @return array<int, class-string<MatchFinishedObserver>>
     */
    public function observerClassNames(): array
    {
        return array_map(
            static fn (MatchFinishedObserver $observer): string => $observer::class,
            $this->observers
        );
    }
}
