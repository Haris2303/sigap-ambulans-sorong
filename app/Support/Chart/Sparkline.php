<?php

namespace App\Support\Chart;

/**
 * Sparkline SVG sisi-server untuk x-ui.stat (docs/RETEMA_B.md 7.2). Lebar 92, tinggi 38, garis dihaluskan Catmull-Rom.
 * Dekoratif (aria-hidden): angka dan label kartu sudah mewakili nilainya, sparkline tidak membawa informasi sendiri.
 */
final class Sparkline
{
    private const LEBAR = 92;

    private const TINGGI = 38;

    /** Nama token warna (mis. "primary", "chart-amber") dibaca lewat var(--color-...), tidak ada hex di markup. */
    public static function buat(array $nilai, string $warnaToken): string
    {
        $n = count($nilai);
        if ($n < 2) {
            return '';
        }

        $titik = self::titik(array_values($nilai));
        $d = self::jalur($titik);
        $warna = 'var(--color-'.$warnaToken.')';
        [$xAkhir, $yAkhir] = $titik[$n - 1];

        return '<svg width="'.self::LEBAR.'" height="'.self::TINGGI.'" viewBox="0 0 '.self::LEBAR.' '.self::TINGGI.'" aria-hidden="true">'
            .'<path d="'.$d.'" fill="none" stroke="'.$warna.'" stroke-width="2.5" stroke-linecap="round"/>'
            .'<circle cx="'.$xAkhir.'" cy="'.$yAkhir.'" r="3.5" fill="'.$warna.'"/>'
            .'</svg>';
    }

    /** @return array<int, array{0: string, 1: string}> titik [x, y] (string, 1 desimal) */
    private static function titik(array $nilai): array
    {
        $n = count($nilai);
        $maks = max(max($nilai), 1);

        $hasil = [];
        foreach ($nilai as $i => $v) {
            $x = $i * (self::LEBAR - 4) / ($n - 1) + 2;
            $y = self::TINGGI - 4 - ($v / $maks) * (self::TINGGI - 8);
            $hasil[] = [sprintf('%.1f', $x), sprintf('%.1f', $y)];
        }

        return $hasil;
    }

    /** Catmull-Rom ke Bezier kubik per ruas (docs/RETEMA_B.md 7.2). */
    private static function jalur(array $titik): string
    {
        $n = count($titik);
        $f = fn (string $s): float => (float) $s;

        $d = 'M '.$titik[0][0].' '.$titik[0][1];
        for ($i = 0; $i < $n - 1; $i++) {
            $p0 = $titik[max($i - 1, 0)];
            $p1 = $titik[$i];
            $p2 = $titik[$i + 1];
            $p3 = $titik[min($i + 2, $n - 1)];

            $c1x = $f($p1[0]) + ($f($p2[0]) - $f($p0[0])) / 6;
            $c1y = $f($p1[1]) + ($f($p2[1]) - $f($p0[1])) / 6;
            $c2x = $f($p2[0]) - ($f($p3[0]) - $f($p1[0])) / 6;
            $c2y = $f($p2[1]) - ($f($p3[1]) - $f($p1[1])) / 6;

            $d .= sprintf(' C %.1f %.1f %.1f %.1f %s %s', $c1x, $c1y, $c2x, $c2y, $p2[0], $p2[1]);
        }

        return $d;
    }
}
