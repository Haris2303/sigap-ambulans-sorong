<?php

namespace App\Support\Chart;

use InvalidArgumentException;

/**
 * Geometri grafik batang (docs/DESAIN_DASBOR_MATERIAL.md bagian 5.11), viewBox 0 0 640 346.
 * Kelas murni tanpa ketergantungan kerangka; Blade hanya mencetak hasilnya. Semua koordinat dikembalikan sebagai string
 * berformat `Angka::format` (maksimum dua desimal).
 */
final class BarGeometry
{
    public const LEBAR = 640;

    public const TINGGI = 346;

    public const KIRI = 48;

    public const KANAN = 624;

    public const ATAS = 20;

    public const DASAR = 300;

    public const SUMBU_X = 38;

    public const LEBAR_BATANG = 36.0;

    public const RADIUS = 8.0;

    public const TINGGI_RINTISAN = 3.0;

    public const HARI_Y = 320;

    public const TANGGAL_Y = 336;

    /**
     * @param  array<int|string, int|float>  $values  nilai per slot (kunci diabaikan, urutan dipakai)
     * @param  int|null  $highlight  indeks slot yang diberi warna primary (di luar rentang diabaikan)
     * @return array<string, mixed>
     */
    public static function buat(array $values, ?int $highlight = null): array
    {
        $nilai = [];
        foreach (array_values($values) as $v) {
            $v = (int) $v;
            if ($v < 0) {
                throw new InvalidArgumentException('Nilai grafik batang tidak boleh negatif.');
            }
            $nilai[] = $v;
        }

        $maks = max(2, $nilai === [] ? 0 : max($nilai));
        $axisMax = $maks % 2 === 0 ? $maks : $maks + 1;
        $tinggiPlot = self::DASAR - self::ATAS;

        $grid = [];
        foreach ([[self::ATAS, $axisMax], [(self::ATAS + self::DASAR) / 2, intdiv($axisMax, 2)], [self::DASAR, 0]] as [$y, $label]) {
            $grid[] = [
                'y' => Angka::format($y),
                'label' => (string) $label,
                'labelY' => Angka::format($y + 4),
                'dasar' => $y === self::DASAR,
            ];
        }

        $jumlah = count($nilai);
        $slot = $jumlah > 0 ? (self::KANAN - self::KIRI) / $jumlah : 0.0;
        $lebar = min(self::LEBAR_BATANG, $slot * 0.6);

        $bars = [];
        foreach ($nilai as $i => $v) {
            $pusat = self::KIRI + ($i + 0.5) * $slot;
            $x = $pusat - $lebar / 2;
            $tinggi = $v / $axisMax * $tinggiPlot;
            $atas = self::DASAR - $tinggi;

            $batang = [
                'index' => $i,
                'value' => $v,
                'center' => Angka::format($pusat),
                'x' => Angka::format($x),
                'width' => Angka::format($lebar),
                'height' => Angka::format($tinggi),
                'top' => Angka::format($atas),
                'path' => null,
                'stub' => null,
                'valueY' => Angka::format($atas - 10),
                'highlight' => $highlight === $i,
            ];

            if ($v === 0) {
                // Batang rintisan agar hari kosong tetap terlihat; label nilai di atasnya.
                $batang['stub'] = [
                    'x' => $batang['x'],
                    'y' => Angka::format(self::DASAR - self::TINGGI_RINTISAN),
                    'width' => $batang['width'],
                    'height' => Angka::format(self::TINGGI_RINTISAN),
                    'rx' => Angka::format(self::TINGGI_RINTISAN / 2),
                ];
                $batang['valueY'] = Angka::format(self::DASAR - self::TINGGI_RINTISAN - 8);
            } else {
                $batang['path'] = self::jalur($x, $lebar, $tinggi);
            }

            $bars[] = $batang;
        }

        return [
            'viewBox' => '0 0 '.self::LEBAR.' '.self::TINGGI,
            'axisMax' => $axisMax,
            'grid' => $grid,
            'sumbuX' => Angka::format(self::SUMBU_X),
            'garisKiri' => Angka::format(self::KIRI),
            'garisKanan' => Angka::format(self::KANAN),
            'hariY' => Angka::format(self::HARI_Y),
            'tanggalY' => Angka::format(self::TANGGAL_Y),
            'bars' => $bars,
        ];
    }

    /** Batang bersudut atas membulat; radius mengecil bila batang lebih pendek dari radius (tidak ada koordinat terbalik). */
    private static function jalur(float $x, float $lebar, float $tinggi): string
    {
        $r = min(self::RADIUS, $tinggi, $lebar / 2);
        $f = Angka::format(...);

        return 'M'.$f($x).' '.$f(self::DASAR)
            .'V'.$f(self::DASAR - $tinggi + $r)
            .'a'.$f($r).' '.$f($r).' 0 0 1 '.$f($r).'-'.$f($r)
            .'h'.$f($lebar - 2 * $r)
            .'a'.$f($r).' '.$f($r).' 0 0 1 '.$f($r).' '.$f($r)
            .'v'.$f($tinggi - $r)
            .'Z';
    }
}
