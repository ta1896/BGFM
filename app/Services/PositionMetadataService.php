<?php

namespace App\Services;

class PositionMetadataService
{
    /**
     * @return array<string, string>
     */
    public function aliases(): array
    {
        /** @var array<string, string> $aliases */
        $aliases = config('simulation.positions.aliases', []);

        return array_map(
            static fn(mixed $value): string => strtoupper(trim((string) $value)),
            $aliases
        );
    }

    /**
     * @return array<string, string>
     */
    public function groups(): array
    {
        /** @var array<string, string> $groups */
        $groups = config('simulation.positions.groups', []);

        return array_map(
            static fn(mixed $value): string => strtoupper(trim((string) $value)),
            $groups
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function slotAliasesMap(): array
    {
        /** @var array<string, array<int, mixed>> $map */
        $map = config('simulation.positions.slot_aliases', []);

        return collect($map)
            ->mapWithKeys(fn(array $aliases, string $code): array => [
                strtoupper(trim($code)) => array_values(array_unique(array_map(
                    fn(mixed $alias): string => $this->normalizeCode((string) $alias),
                    $aliases
                ))),
            ])
            ->all();
    }

    public function normalizeCode(?string $value): string
    {
        $normalized = strtoupper(trim((string) $value));
        if ($normalized === '') {
            return '';
        }

        $base = preg_replace('/-(L|R)$/', '', $normalized) ?: $normalized;

        return $this->aliases()[$base] ?? $base;
    }

    public function groupFromPosition(?string $position): ?string
    {
        $normalized = $this->normalizeCode($position);
        if ($normalized === '') {
            return null;
        }

        $groups = $this->groups();
        if (isset($groups[$normalized])) {
            return $groups[$normalized];
        }

        if (str_starts_with($normalized, 'IV')) {
            return 'DEF';
        }

        if (str_starts_with($normalized, 'DM') || str_starts_with($normalized, 'ZM')) {
            return 'MID';
        }

        if (str_starts_with($normalized, 'ST')) {
            return 'FWD';
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function slotAliases(string $slotCode, string $slotLabel): array
    {
        $slot = $this->normalizeCode($slotCode);
        $label = $this->normalizeCode($slotLabel);
        $slotAliases = $this->slotAliasesMap();

        return array_values(array_unique(array_filter(array_merge(
            [$slot, $label],
            $slotAliases[$slot] ?? [],
            $slotAliases[$label] ?? []
        ))));
    }

    /**
     * @return array<int, string>
     */
    public function compatibleGroups(string $group): array
    {
        /** @var array<string, array<int, string>> $map */
        $map = config('simulation.positions.group_fallbacks', []);

        return array_values(array_unique(array_map(
            static fn(mixed $value): string => strtoupper(trim((string) $value)),
            $map[strtoupper(trim($group))] ?? []
        )));
    }
}
