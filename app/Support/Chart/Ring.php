<?php

namespace App\Support\Chart;

/**
 * Cincin ketersediaan SVG sisi-server untuk x-ui.ring-tile (docs/RETEMA_B.md 7.3). Jari-jari 26, tebal 7, ruang 64x64.
 */
final class Ring
{
    private const RADIUS = 26;

    /** Tersedia di bawah 12% dari total dianggap menipis (isi berganti ke chart-coral). */
    public const AMBANG_MENIPIS = 0.12;

    public static function keliling(): float
    {
        return 2 * M_PI * self::RADIUS;
    }

    /** Persen tersedia/total, 0 bila total 0, dipotong ke rentang 0 sampai 1. */
    public static function persen(int $tersedia, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return max(0.0, min(1.0, $tersedia / $total));
    }

    /** Panjang ruas isi untuk stroke-dasharray; minimum 0,1 supaya cincin kosong tetap tergambar tipis. */
    public static function panjangIsi(int $tersedia, int $total): float
    {
        return max(self::persen($tersedia, $total) * self::keliling(), 0.1);
    }

    public static function menipis(int $tersedia, int $total): bool
    {
        return $total > 0 && self::persen($tersedia, $total) < self::AMBANG_MENIPIS;
    }
}
