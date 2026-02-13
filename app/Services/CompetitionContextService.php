<?php

namespace App\Services;

use App\Models\GameMatch;

class CompetitionContextService
{
    public const LEAGUE = 'league';

    public const CUP_NATIONAL = 'cup_national';

    public const CUP_INTERNATIONAL = 'cup_international';

    public const FRIENDLY = 'friendly';

    /**
     * @return array<int, string>
     */
    public function allContexts(): array
    {
        return [
            self::LEAGUE,
            self::CUP_NATIONAL,
            self::CUP_INTERNATIONAL,
            self::FRIENDLY,
        ];
    }

    public function forMatch(GameMatch $match): string
    {
        $stored = $this->normalize((string) ($match->competition_context ?? ''));
        if ($stored !== null) {
            return $stored;
        }

        $matchType = strtolower((string) $match->type);
        if ($matchType === 'league') {
            return self::LEAGUE;
        }

        if ($matchType === 'friendly') {
            return self::FRIENDLY;
        }

        if ($matchType !== 'cup') {
            return self::FRIENDLY;
        }

        $match->loadMissing('competitionSeason.competition');
        $competitionScope = $this->normalizeCompetitionScope((string) ($match->competitionSeason?->competition?->scope ?? ''));
        if ($competitionScope !== null) {
            return $competitionScope === 'international'
                ? self::CUP_INTERNATIONAL
                : self::CUP_NATIONAL;
        }

        $countryId = $match->competitionSeason?->competition?->country_id;

        return $countryId ? self::CUP_NATIONAL : self::CUP_INTERNATIONAL;
    }

    public function fromRawMatchData(string $matchType, ?int $competitionCountryId, ?string $competitionScope = null): string
    {
        $normalizedType = strtolower(trim($matchType));
        $normalizedScope = $this->normalizeCompetitionScope((string) ($competitionScope ?? ''));

        return match ($normalizedType) {
            'league' => self::LEAGUE,
            'friendly' => self::FRIENDLY,
            'cup' => $normalizedScope === 'international'
                ? self::CUP_INTERNATIONAL
                : ($normalizedScope === 'national'
                    ? self::CUP_NATIONAL
                    : ($competitionCountryId ? self::CUP_NATIONAL : self::CUP_INTERNATIONAL)),
            default => self::FRIENDLY,
        };
    }

    public function fromStoredOrRaw(
        ?string $storedContext,
        string $matchType,
        ?int $competitionCountryId,
        ?string $competitionScope = null
    ): string {
        $stored = $this->normalize((string) ($storedContext ?? ''));
        if ($stored !== null) {
            return $stored;
        }

        return $this->fromRawMatchData($matchType, $competitionCountryId, $competitionScope);
    }

    public function persistForMatch(GameMatch $match): string
    {
        $context = $this->forMatch($match);
        if ($this->normalize((string) ($match->competition_context ?? '')) === $context) {
            return $context;
        }

        $match->forceFill(['competition_context' => $context])->saveQuietly();

        return $context;
    }

    public function isLeague(GameMatch $match): bool
    {
        return $this->forMatch($match) === self::LEAGUE;
    }

    public function isCup(GameMatch $match): bool
    {
        return in_array($this->forMatch($match), [self::CUP_NATIONAL, self::CUP_INTERNATIONAL], true);
    }

    public function isNationalCup(GameMatch $match): bool
    {
        return $this->forMatch($match) === self::CUP_NATIONAL;
    }

    public function isInternationalCup(GameMatch $match): bool
    {
        return $this->forMatch($match) === self::CUP_INTERNATIONAL;
    }

    public function isFriendly(GameMatch $match): bool
    {
        return $this->forMatch($match) === self::FRIENDLY;
    }

    private function normalize(string $context): ?string
    {
        $value = strtolower(trim($context));

        return in_array($value, $this->allContexts(), true) ? $value : null;
    }

    private function normalizeCompetitionScope(string $scope): ?string
    {
        $value = strtolower(trim($scope));

        return in_array($value, ['national', 'international'], true) ? $value : null;
    }
}
