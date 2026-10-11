<?php

namespace App\Support\Chart;

use InvalidArgumentException;

/**
 * Warna segmen grafik hanya dari token (DESIGN.md). Nama token menjadi kelas `bg-*` untuk titik legenda donat; kanvas membaca
 * warna yang sama dari variabel CSS `--color-*` di runtime. Kelas ditulis utuh supaya terbaca pemindai Tailwind; jangan dirangkai dari nama.
 */
final class Warna
{
    private const LATAR = [
        'primary' => 'bg-primary',
        'success' => 'bg-success',
        'error' => 'bg-error',
        'warning' => 'bg-warning',
        'outline' => 'bg-outline',
        'chart-sky' => 'bg-chart-sky',
    ];

    public static function latar(string $nama): string
    {
        return self::LATAR[$nama] ?? throw self::tidakDikenal($nama);
    }

    private static function tidakDikenal(string $nama): InvalidArgumentException
    {
        return new InvalidArgumentException('Warna grafik "'.$nama.'" tidak dikenal ('.implode(', ', array_keys(self::LATAR)).').');
    }
}
