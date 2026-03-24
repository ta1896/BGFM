<?php

namespace App\Services;

use Illuminate\Support\Collection;

class MatchRandomService
{
    public function randomInt(int $min, int $max, bool $deterministic = false): int
    {
        if ($deterministic) {
            return mt_rand($min, $max);
        }

        return random_int($min, $max);
    }

    public function randomArrayKey(array $values, bool $deterministic = false): int|string
    {
        if ($values === []) {
            return 0;
        }

        if ($deterministic) {
            $keys = array_keys($values);
            $index = mt_rand(0, max(0, count($keys) - 1));

            return $keys[$index];
        }

        return array_rand($values);
    }

    public function randomCollectionItem(Collection $items, bool $deterministic = false): mixed
    {
        $fallback = $items->first();
        if ($fallback === null) {
            return null;
        }

        $values = $items->values();
        $index = $this->randomInt(0, max(0, $values->count() - 1), $deterministic);

        return $values->get($index) ?? $fallback;
    }
}
