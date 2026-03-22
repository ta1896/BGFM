<?php

namespace App\Constants;

class Nationality
{
    public static function getCode(?string $name): ?string
    {
        if (!$name) {
            return null;
        }

        $map = [
            'Germany' => 'de',
            'England' => 'gb',
            'France' => 'fr',
            'Spain' => 'es',
            'Italy' => 'it',
            'Portugal' => 'pt',
            'Brazil' => 'br',
            'Argentina' => 'ar',
            'Netherlands' => 'nl',
            'Belgium' => 'be',
            'Austria' => 'at',
            'Switzerland' => 'ch',
            'Turkey' => 'tr',
            'Poland' => 'pl',
            'Denmark' => 'dk',
            'Norway' => 'no',
            'Sweden' => 'se',
            'Croatia' => 'hr',
            'Serbia' => 'rs',
            'Scotland' => 'gb-sct',
            'Wales' => 'gb-wls',
            'Northern Ireland' => 'gb-nir',
            'Ireland' => 'ie',
            'USA' => 'us',
            'United States' => 'us',
            'Canada' => 'ca',
            'Mexico' => 'mx',
            'Japan' => 'jp',
            'South Korea' => 'kr',
            'Nigeria' => 'ng',
            'Senegal' => 'sn',
            'Morocco' => 'ma',
            'Ivory Coast' => 'ci',
            'Ghana' => 'gh',
            'Cameroon' => 'cm',
            'Algeria' => 'dz',
            'Egypt' => 'eg',
            'Uruguay' => 'uy',
            'Chile' => 'cl',
            'Colombia' => 'co',
            'Ukraine' => 'ua',
            'Russia' => 'ru',
            'Greece' => 'gr',
            'Romania' => 'ro',
            'Hungary' => 'hu',
            'Czech Republic' => 'cz',
            'Slovakia' => 'sk',
            'Slovenia' => 'si',
            'Bosnia-Herzegovina' => 'ba',
            'Montenegro' => 'me',
            'Albania' => 'al',
            'Kosovo' => 'xk',
            'Macedonia' => 'mk',
            'North Macedonia' => 'mk',
            'Israel' => 'il',
            'Australia' => 'au',
            'New Zealand' => 'nz',
        ];

        return $map[$name] ?? null;
    }
}
