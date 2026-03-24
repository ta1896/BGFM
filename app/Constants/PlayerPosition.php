<?php

namespace App\Constants;

class PlayerPosition
{
    public const TW = 'TW'; // Torwart
    public const IV = 'IV'; // Innenverteidiger
    public const RV = 'RV'; // Rechter Verteidiger
    public const LV = 'LV'; // Linker Verteidiger
    public const DM = 'DM'; // Defensives Mittelfeld
    public const ZM = 'ZM'; // Zentrales Mittelfeld
    public const OM = 'OM'; // Offensives Mittelfeld
    public const RM = 'RM'; // Rechtes Mittelfeld
    public const LM = 'LM'; // Linkes Mittelfeld
    public const RF = 'RF'; // Rechter Flügel
    public const LF = 'LF'; // Linker Flügel
    public const HS = 'HS'; // Hängende Spitze
    public const MS = 'MS'; // Mittelstürmer

    public static function labels(): array
    {
        return [
            self::TW => 'Torhüter',
            self::IV => 'Innenverteidiger',
            self::RV => 'Rechter Verteidiger',
            self::LV => 'Linker Verteidiger',
            self::DM => 'Defensives Mittelfeld',
            self::ZM => 'Zentrales Mittelfeld',
            self::OM => 'Offensives Mittelfeld',
            self::RM => 'Rechtes Mittelfeld',
            self::LM => 'Linkes Mittelfeld',
            self::RF => 'Rechter Flügel',
            self::LF => 'Linker Flügel',
            self::HS => 'Hängende Spitze',
            self::MS => 'Mittelstürmer',
        ];
    }

    public static function all(): array
    {
        return [
            self::TW, self::IV, self::RV, self::LV,
            self::DM, self::ZM, self::OM, self::RM, self::LM,
            self::RF, self::LF, self::HS, self::MS
        ];
    }

    public static function aliases(): array
    {
        return config('simulation.positions.aliases', [
            'GK' => self::TW,
            'LB' => self::LV,
            'CB' => self::IV,
            'RB' => self::RV,
            'LWB' => self::LV,
            'RWB' => self::RV,
            'CDM' => self::DM,
            'CM' => self::ZM,
            'CAM' => self::OM,
            'LAM' => self::OM,
            'ZOM' => self::OM,
            'RAM' => self::OM,
            'LW' => self::LF,
            'RW' => self::RF,
            'ST' => self::MS,
            'CF' => self::HS,
            'LS' => self::MS,
            'RS' => self::MS,
        ]);
    }

    public static function map(string $pos): string
    {
        return self::aliases()[strtoupper($pos)] ?? strtoupper($pos);
    }
}
