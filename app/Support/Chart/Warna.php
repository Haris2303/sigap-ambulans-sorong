<?php

namespace App\Support\Chart;

use InvalidArgumentException;

/**
 * Warna grafik hanya dari token (DESIGN.md): nama token menjadi kelas `stroke-*` (segmen donat) dan `bg-*` (titik legenda).
 * Kelas ditulis utuh supaya terbaca pemindai Tailwind; jangan dirangkai dari nama.
 */
final class Warna
{
    private const STROKE = [
        'primary' => 'stroke-primary',
        'success' => 'stroke-success',
        'error' => 'stroke-error',
        'warning' => 'stroke-warning',
        'outline' => 'stroke-outline',
        'chart-sky' => 'stroke-chart-sky',
    ];

    private const LATAR = [
        'primary' => 'bg-primary',
        'success' => 'bg-success',
        'error' => 'bg-error',
        'warning' => 'bg-warning',
        'outline' => 'bg-outline',
        'chart-sky' => 'bg-chart-sky',
    ];

    public static function stroke(string $nama): string
    {
        return self::STROKE[$nama] ?? throw self::tidakDikenal($nama);
    }

    public static function latar(string $nama): string
    {
        return self::LATAR[$nama] ?? throw self::tidakDikenal($nama);
    }

    private static function tidakDikenal(string $nama): InvalidArgumentException
    {
        return new InvalidArgumentException('Warna grafik "'.$nama.'" tidak dikenal ('.implode(', ', array_keys(self::STROKE)).').');
    }
}
