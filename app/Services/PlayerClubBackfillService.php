<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class PlayerClubBackfillService
{
    /**
     * @return array<string, mixed>
     */
    public function run(bool $dryRun = false, int $chunkSize = 500): array
    {
        $chunkSize = max(50, min(5000, $chunkSize));
        $startedAt = now();

        $report = [
            'dry_run' => $dryRun,
            'chunk_size' => $chunkSize,
            'started_at' => $startedAt->toDateTimeString(),
            'players_scanned' => 0,
            'players_updated' => 0,
            'players_position_main_filled' => 0,
            'players_profile_normalized' => 0,
            'players_context_suspensions_seeded' => 0,
            'players_legacy_suspension_synced' => 0,
            'players_status_repaired' => 0,
            'clubs_scanned' => 0,
            'clubs_updated' => 0,
            'clubs_slug_filled' => 0,
            'clubs_short_name_filled' => 0,
            'audit_before' => $this->audit(),
            'audit_after' => [],
        ];

        $this->backfillPlayers($report, $dryRun, $chunkSize);
        $this->backfillClubs($report, $dryRun, $chunkSize);

        $report['finished_at'] = now()->toDateTimeString();
        $report['audit_after'] = $dryRun ? $report['audit_before'] : $this->audit();

        return $report;
    }

    /**
     * @param array<string, mixed> $report
     */
    private function backfillPlayers(array &$report, bool $dryRun, int $chunkSize): void
    {
        DB::table('players')
            ->select([
                'id',
                'position',
                'position_main',
                'position_second',
                'position_third',
                'status',
                'injury_matches_remaining',
                'suspension_matches_remaining',
                'suspension_league_remaining',
                'suspension_cup_national_remaining',
                'suspension_cup_international_remaining',
                'suspension_friendly_remaining',
            ])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($rows) use (&$report, $dryRun): void {
                foreach ($rows as $row) {
                    $report['players_scanned']++;
                    $updates = $this->playerUpdates($row, $report);
                    if ($updates === []) {
                        continue;
                    }

                    $report['players_updated']++;
                    if ($dryRun) {
                        continue;
                    }

                    DB::table('players')
                        ->where('id', (int) $row->id)
                        ->update(array_merge($updates, ['updated_at' => now()]));
                }
            }, 'id');
    }

    /**
     * @param array<string, mixed> $report
     */
    private function backfillClubs(array &$report, bool $dryRun, int $chunkSize): void
    {
        $takenSlugs = [];
        DB::table('clubs')
            ->whereNotNull('slug')
            ->where('slug', '<>', '')
            ->pluck('slug')
            ->each(function ($slug) use (&$takenSlugs): void {
                $takenSlugs[strtolower((string) $slug)] = true;
            });

        DB::table('clubs')
            ->select(['id', 'name', 'slug', 'short_name'])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($rows) use (&$report, &$takenSlugs, $dryRun): void {
                foreach ($rows as $row) {
                    $report['clubs_scanned']++;
                    $updates = [];

                    $currentSlug = trim((string) ($row->slug ?? ''));
                    if ($currentSlug === '') {
                        $updates['slug'] = $this->nextUniqueSlug((string) $row->name, (int) $row->id, $takenSlugs);
                        $report['clubs_slug_filled']++;
                    }

                    $currentShort = trim((string) ($row->short_name ?? ''));
                    if ($currentShort === '') {
                        $updates['short_name'] = $this->buildShortName((string) $row->name, (int) $row->id);
                        $report['clubs_short_name_filled']++;
                    }

                    if ($updates === []) {
                        if ($currentSlug !== '') {
                            $takenSlugs[strtolower($currentSlug)] = true;
                        }
                        continue;
                    }

                    $report['clubs_updated']++;
                    if ($dryRun) {
                        continue;
                    }

                    DB::table('clubs')
                        ->where('id', (int) $row->id)
                        ->update(array_merge($updates, ['updated_at' => now()]));
                }
            }, 'id');
    }

    /**
     * @param array<string, mixed> $report
     * @return array<string, mixed>
     */
    private function playerUpdates(stdClass $row, array &$report): array
    {
        $updates = [];

        $position = $this->normalizePosition($row->position);
        $positionMain = $this->normalizePosition($row->position_main);
        $positionSecond = $this->normalizePosition($row->position_second);
        $positionThird = $this->normalizePosition($row->position_third);

        if ($positionMain === null) {
            $positionMain = $position ?? $positionSecond ?? $positionThird;
            if ($positionMain !== null) {
                $updates['position_main'] = $positionMain;
                $report['players_position_main_filled']++;
            }
        } elseif ((string) $row->position_main !== $positionMain) {
            $updates['position_main'] = $positionMain;
            $report['players_profile_normalized']++;
        }

        if ($positionSecond !== null && $positionSecond === $positionMain) {
            $positionSecond = null;
        }

        if ($positionThird !== null && ($positionThird === $positionMain || $positionThird === $positionSecond)) {
            $positionThird = null;
        }

        if ($positionSecond === null && $positionThird !== null) {
            $positionSecond = $positionThird;
            $positionThird = null;
        }

        $currentSecond = $this->normalizePosition($row->position_second);
        if ($currentSecond !== $positionSecond) {
            $updates['position_second'] = $positionSecond;
            $report['players_profile_normalized']++;
        }

        $currentThird = $this->normalizePosition($row->position_third);
        if ($currentThird !== $positionThird) {
            $updates['position_third'] = $positionThird;
            $report['players_profile_normalized']++;
        }

        $legacySuspension = max(0, (int) $row->suspension_matches_remaining);
        $suspensionLeague = max(0, (int) $row->suspension_league_remaining);
        $suspensionCupNational = max(0, (int) $row->suspension_cup_national_remaining);
        $suspensionCupInternational = max(0, (int) $row->suspension_cup_international_remaining);
        $suspensionFriendly = max(0, (int) $row->suspension_friendly_remaining);

        if (
            $legacySuspension > 0
            && $suspensionLeague === 0
            && $suspensionCupNational === 0
            && $suspensionCupInternational === 0
            && $suspensionFriendly === 0
        ) {
            $suspensionLeague = $legacySuspension;
            $updates['suspension_league_remaining'] = $suspensionLeague;
            $report['players_context_suspensions_seeded']++;
        }

        $expectedLegacySuspension = max(
            $suspensionLeague,
            $suspensionCupNational,
            $suspensionCupInternational,
            $suspensionFriendly
        );

        if ($legacySuspension !== $expectedLegacySuspension) {
            $updates['suspension_matches_remaining'] = $expectedLegacySuspension;
            $report['players_legacy_suspension_synced']++;
        }

        $injuryRemaining = max(0, (int) $row->injury_matches_remaining);
        $currentStatus = strtolower(trim((string) $row->status));
        $resolvedStatus = $this->resolvePlayerStatus($injuryRemaining, $expectedLegacySuspension, $currentStatus);
        if ($resolvedStatus !== $currentStatus) {
            $updates['status'] = $resolvedStatus;
            $report['players_status_repaired']++;
        }

        return $updates;
    }

    private function normalizePosition(mixed $position): ?string
    {
        $normalized = strtoupper(trim((string) ($position ?? '')));

        return $normalized === '' ? null : $normalized;
    }

    private function resolvePlayerStatus(int $injuryRemaining, int $legacySuspension, string $currentStatus): string
    {
        if ($injuryRemaining > 0) {
            return 'injured';
        }

        if ($legacySuspension > 0) {
            return 'suspended';
        }

        if (in_array($currentStatus, ['injured', 'suspended'], true)) {
            return 'active';
        }

        return $currentStatus !== '' ? $currentStatus : 'active';
    }

    /**
     * @param array<string, bool> $takenSlugs
     */
    private function nextUniqueSlug(string $name, int $clubId, array &$takenSlugs): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'club-'.$clubId;
        }

        $candidate = $base;
        $suffix = 2;
        while (isset($takenSlugs[strtolower($candidate)])) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        $takenSlugs[strtolower($candidate)] = true;

        return $candidate;
    }

    private function buildShortName(string $name, int $clubId): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $name) ?? '');
        if ($clean === '') {
            return 'CLUB'.$clubId;
        }

        $short = Str::upper(Str::substr($clean, 0, 12));

        return trim($short) !== '' ? trim($short) : 'CLUB'.$clubId;
    }

    /**
     * @return array<string, int>
     */
    private function audit(): array
    {
        $playersMissingMain = (int) DB::table('players')
            ->where(function ($query): void {
                $query->whereNull('position_main')
                    ->orWhere('position_main', '');
            })
            ->count();

        $playersDuplicateProfile = (int) DB::table('players')
            ->whereRaw("position_second IS NOT NULL AND position_second <> '' AND UPPER(TRIM(position_second)) = UPPER(TRIM(position_main))")
            ->orWhereRaw("position_third IS NOT NULL AND position_third <> '' AND UPPER(TRIM(position_third)) = UPPER(TRIM(position_main))")
            ->orWhereRaw("position_third IS NOT NULL AND position_third <> '' AND position_second IS NOT NULL AND position_second <> '' AND UPPER(TRIM(position_third)) = UPPER(TRIM(position_second))")
            ->count();

        $playersLegacyMismatch = (int) DB::table('players')
            ->whereRaw('COALESCE(suspension_matches_remaining, 0) <> GREATEST(COALESCE(suspension_league_remaining, 0), COALESCE(suspension_cup_national_remaining, 0), COALESCE(suspension_cup_international_remaining, 0), COALESCE(suspension_friendly_remaining, 0))')
            ->count();

        $clubsMissingSlug = (int) DB::table('clubs')
            ->where(function ($query): void {
                $query->whereNull('slug')
                    ->orWhere('slug', '');
            })
            ->count();

        $clubsMissingShortName = (int) DB::table('clubs')
            ->where(function ($query): void {
                $query->whereNull('short_name')
                    ->orWhere('short_name', '');
            })
            ->count();

        return [
            'players_missing_position_main' => $playersMissingMain,
            'players_duplicate_position_profile' => $playersDuplicateProfile,
            'players_legacy_context_suspension_mismatch' => $playersLegacyMismatch,
            'clubs_missing_slug' => $clubsMissingSlug,
            'clubs_missing_short_name' => $clubsMissingShortName,
        ];
    }
}

