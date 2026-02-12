<?php

namespace App\Services;

use Carbon\CarbonInterface;

class TransferWindowService
{
    public function isOpen(?CarbonInterface $date = null): bool
    {
        if (!config('transfer.window_enforced', true)) {
            return true;
        }

        $date = $date ?: now();
        $monthDay = $date->format('m-d');

        foreach (config('transfer.windows', []) as $window) {
            $start = (string) ($window['start'] ?? '');
            $end = (string) ($window['end'] ?? '');
            if ($start === '' || $end === '') {
                continue;
            }

            if ($monthDay >= $start && $monthDay <= $end) {
                return true;
            }
        }

        return false;
    }

    public function currentWindowLabel(?CarbonInterface $date = null): ?string
    {
        if (!config('transfer.window_enforced', true)) {
            return 'Transfermarkt offen (Regel deaktiviert)';
        }

        $date = $date ?: now();
        $monthDay = $date->format('m-d');

        foreach (config('transfer.windows', []) as $window) {
            $start = (string) ($window['start'] ?? '');
            $end = (string) ($window['end'] ?? '');
            if ($start === '' || $end === '') {
                continue;
            }

            if ($monthDay >= $start && $monthDay <= $end) {
                return (string) ($window['label'] ?? 'Transferfenster');
            }
        }

        return null;
    }

    public function closedMessage(): string
    {
        $windows = collect(config('transfer.windows', []))
            ->map(function (array $window) {
                $label = (string) ($window['label'] ?? 'Fenster');
                $start = (string) ($window['start'] ?? '');
                $end = (string) ($window['end'] ?? '');

                return $label.' ('.$start.' bis '.$end.')';
            })
            ->filter()
            ->values()
            ->join(', ');

        return $windows !== ''
            ? 'Transferfenster geschlossen. Erlaubt: '.$windows
            : 'Transferfenster geschlossen.';
    }
}
