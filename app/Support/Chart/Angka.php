<?php

namespace App\Support\Chart;

/** Format koordinat dan panjang SVG: paling banyak dua desimal, nol di belakang dibuang ("300", "71.14", "246.3"). */
final class Angka
{
    public static function format(float|int $nilai): string
    {
        $bulat = round((float) $nilai, 2);
        if ($bulat == 0.0) {
            return '0';
        }

        return rtrim(rtrim(number_format($bulat, 2, '.', ''), '0'), '.');
    }
}
