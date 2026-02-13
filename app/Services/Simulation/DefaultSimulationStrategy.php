<?php

namespace App\Services\Simulation;

use Illuminate\Contracts\Config\Repository as ConfigRepository;

class DefaultSimulationStrategy
{
    public function __construct(
        private readonly ConfigRepository $config
    ) {
    }

    public function sequenceCount(): int
    {
        return $this->randomInt(
            $this->int('sequence.min_per_minute', 3),
            $this->int('sequence.max_per_minute', 5)
        );
    }

    public function homePossessionPercent(float $homeStrength, float $awayStrength): int
    {
        $value = $this->float('possession.base_percent', 50.0)
            + (($homeStrength - $awayStrength) / $this->float('possession.strength_divisor', 4.0))
            + $this->randomInt($this->int('possession.noise_min', -5), $this->int('possession.noise_max', 5));

        return (int) max(
            $this->int('possession.min_percent', 22),
            min($this->int('possession.max_percent', 78), round($value))
        );
    }

    public function homePossessionSeconds(int $homePossessionPercent): int
    {
        $seconds = (int) round(($homePossessionPercent / 100) * 60);

        return max(
            $this->int('possession.seconds_min', 15),
            min($this->int('possession.seconds_max', 45), $seconds)
        );
    }

    public function attackerClubId(int $homeClubId, int $awayClubId, int $homePossessionPercent): int
    {
        return $this->randomInt(1, 100) <= $homePossessionPercent ? $homeClubId : $awayClubId;
    }

    public function isPassSuccessful(float $passing, float $fitFactor): bool
    {
        $base = $this->float('formulas.pass.base', 0.70);
        $midpoint = $this->float('formulas.pass.midpoint', 60.0);
        $divisor = $this->float('formulas.pass.divisor', 180.0);
        $probability = $base + ((($passing * $fitFactor) - $midpoint) / $divisor);

        return $this->roll($this->clamp(
            $probability,
            $this->float('formulas.pass.min', 0.56),
            $this->float('formulas.pass.max', 0.93)
        ));
    }

    public function shouldAttemptTackle(): bool
    {
        return $this->roll($this->probability('tackle_attempt', 0.45));
    }

    public function isTackleWon(float $defending, float $carrierPace): bool
    {
        $base = $this->float('formulas.tackle_win.base', 0.50);
        $divisor = $this->float('formulas.tackle_win.divisor', 260.0);
        $probability = $base + (($defending - $carrierPace) / $divisor);

        return $this->roll($this->clamp(
            $probability,
            $this->float('formulas.tackle_win.min', 0.25),
            $this->float('formulas.tackle_win.max', 0.82)
        ));
    }

    public function shouldCommitFoulAfterTackleWin(): bool
    {
        return $this->roll($this->probability('foul_after_tackle_win', 0.18));
    }

    public function chanceXg(int $attackerClubId, int $homeClubId, float $homeStrength, float $awayStrength): float
    {
        $attackerStrength = $attackerClubId === $homeClubId ? $homeStrength : $awayStrength;
        $defenderStrength = $attackerClubId === $homeClubId ? $awayStrength : $homeStrength;

        $value = $this->float('chance.xg_base', 0.10)
            + (($attackerStrength - $defenderStrength) / $this->float('chance.xg_strength_divisor', 400.0))
            + ($this->randomInt($this->int('chance.xg_noise_min', 0), $this->int('chance.xg_noise_max', 12))
                / $this->float('chance.xg_noise_divisor', 100.0));

        return $this->clamp(
            $value,
            $this->float('chance.xg_min', 0.03),
            $this->float('chance.xg_max', 0.48)
        );
    }

    public function chanceQuality(float $xg): string
    {
        if ($xg >= $this->float('chance.big_chance_xg_threshold', 0.24)) {
            return 'big';
        }

        return $this->roll($this->probability('big_chance_roll', 0.38)) ? 'big' : 'normal';
    }

    public function shouldWinCornerAfterShot(): bool
    {
        return $this->roll($this->probability('corner_after_shot', 0.12));
    }

    public function isShotOnTarget(float $shooting, float $fitFactor): bool
    {
        $base = $this->float('formulas.shot_on_target.base', 0.34);
        $midpoint = $this->float('formulas.shot_on_target.midpoint', 58.0);
        $divisor = $this->float('formulas.shot_on_target.divisor', 220.0);
        $probability = $base + ((($shooting * $fitFactor) - $midpoint) / $divisor);

        return $this->roll($this->clamp(
            $probability,
            $this->float('formulas.shot_on_target.min', 0.22),
            $this->float('formulas.shot_on_target.max', 0.78)
        ));
    }

    public function isSave(
        float $goalkeeperOverall,
        float $goalkeeperFitFactor,
        float $shooterShooting,
        float $shooterFitFactor,
        float $xg
    ): bool {
        $base = $this->float('formulas.save.base', 0.55);
        $skillDivisor = $this->float('formulas.save.skill_divisor', 300.0);
        $xgDivisor = $this->float('formulas.save.xg_divisor', 2.5);
        $probability = $base
            + (((($goalkeeperOverall * $goalkeeperFitFactor) - ($shooterShooting * $shooterFitFactor)) / $skillDivisor))
            - ($xg / $xgDivisor);

        return $this->roll($this->clamp(
            $probability,
            $this->float('formulas.save.min', 0.18),
            $this->float('formulas.save.max', 0.86)
        ));
    }

    public function shouldCreateAssist(): bool
    {
        return $this->roll($this->probability('assist', 0.68));
    }

    public function isRedCardFromFoul(): bool
    {
        return $this->roll($this->probability('foul_red_card', 0.04));
    }

    public function isYellowCardFromFoul(bool $isRedCard): bool
    {
        if ($isRedCard) {
            return false;
        }

        return $this->roll($this->probability('foul_yellow_card', 0.30));
    }

    public function isPenaltyAwardedFromFoul(): bool
    {
        return $this->roll($this->probability('penalty_awarded_after_foul', 0.14));
    }

    public function isPenaltyScoredInPlay(float $takerShooting, float $goalkeeperOverall): bool
    {
        return $this->roll($this->penaltyScoreProbability($takerShooting, $goalkeeperOverall, 'formulas.penalty_in_play', 0.75));
    }

    public function shouldCreatePenaltySaveEventInPlay(): bool
    {
        return $this->roll($this->probability('penalty_save_event_in_play', 0.68));
    }

    public function shouldRandomInjuryOccur(): bool
    {
        return $this->roll($this->probability('random_injury_per_minute', 0.012));
    }

    public function isPenaltyScoredInShootout(float $takerShooting, float $goalkeeperOverall): bool
    {
        return $this->roll($this->penaltyScoreProbability($takerShooting, $goalkeeperOverall, 'formulas.penalty_shootout', 0.76));
    }

    public function shouldCreatePenaltySaveEventInShootout(): bool
    {
        return $this->roll($this->probability('penalty_save_event_shootout', 0.66));
    }

    public function shouldHomeWinShootoutCoinflip(): bool
    {
        return $this->roll($this->probability('shootout_coinflip_home_wins', 0.50));
    }

    private function penaltyScoreProbability(float $takerShooting, float $goalkeeperOverall, string $path, float $defaultBase): float
    {
        $base = $this->float($path.'.base', $defaultBase);
        $divisor = $this->float($path.'.divisor', 300.0);
        $probability = $base + (($takerShooting - $goalkeeperOverall) / $divisor);

        return $this->clamp(
            $probability,
            $this->float($path.'.min', 0.55),
            $this->float($path.'.max', 0.94)
        );
    }

    private function probability(string $key, float $default): float
    {
        return $this->clamp((float) $this->config->get('simulation.probabilities.'.$key, $default), 0.0, 1.0);
    }

    private function float(string $key, float $default): float
    {
        return (float) $this->config->get('simulation.'.$key, $default);
    }

    private function int(string $key, int $default): int
    {
        return (int) $this->config->get('simulation.'.$key, $default);
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    private function roll(float $probability): bool
    {
        return ($this->randomInt(1, 10000) / 10000) <= $probability;
    }

    private function randomInt(int $min, int $max): int
    {
        if ((bool) $this->config->get('simulation.deterministic.enabled', false)) {
            return mt_rand($min, $max);
        }

        return random_int($min, $max);
    }
}
