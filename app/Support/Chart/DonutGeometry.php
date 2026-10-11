<?php

namespace App\Support\Chart;

use InvalidArgumentException;

/**
 * Geometri grafik donat (docs/DESAIN_DASBOR_MATERIAL.md bagian 5.12), viewBox 0 0 160 160, pusat (80, 80), jari-jari 56,
 * tebal garis 18. Tiap segmen berupa lingkaran dengan stroke-dasharray dan stroke-dashoffset; lingkaran diputar -90 derajat
 * oleh pemanggil agar mulai dari jam 12. Kelas murni tanpa ketergantungan kerangka.
 */
final class DonutGeometry
{
    public const PUSAT = 80;

    public const RADIUS = 56;

    public const TEBAL = 18;

    public const CELAH = 3.0;

    public static function keliling(): float
    {
        return 2 * M_PI * self::RADIUS;
    }

    /**
     * @param  array<int, array{label: string, value: int, color: string}>  $segments
     * @return array<string, mixed>
     */
    public static function buat(array $segments): array
    {
        $ada = [];
        foreach (array_values($segments) as $urutan => $segmen) {
            if (! is_array($segmen) || ! isset($segmen['label'], $segmen['color']) || ! array_key_exists('value', $segmen)) {
                throw new InvalidArgumentException('Segmen donat harus punya label, value, dan color.');
            }
            $nilai = (int) $segmen['value'];
            if ($nilai < 0) {
                throw new InvalidArgumentException('Nilai segmen donat tidak boleh negatif.');
            }
            if ($nilai > 0) {
                $ada[] = ['urutan' => $urutan, 'label' => (string) $segmen['label'], 'value' => $nilai, 'color' => (string) $segmen['color']];
            }
        }

        // Nilai terbesar dulu; seri sama mengikuti urutan masukan (pemanggil memberi urutan Enum).
        usort($ada, fn (array $a, array $b) => [$b['value'], $a['urutan']] <=> [$a['value'], $b['urutan']]);

        $total = array_sum(array_column($ada, 'value'));
        $keliling = self::keliling();

        $hasil = [];
        $terpakai = 0.0;
        foreach ($ada as $segmen) {
            $bagian = $segmen['value'] / $total * $keliling;
            // Celah 3px antar segmen; segmen kecil dikorbankan separuhnya agar tetap terlihat; satu segmen penuh tanpa celah.
            $celah = count($ada) === 1 ? 0.0 : min(self::CELAH, $bagian / 2);
            $panjang = $bagian - $celah;

            $hasil[] = [
                'label' => $segmen['label'],
                'value' => $segmen['value'],
                'color' => $segmen['color'],
                'percent' => (int) round($segmen['value'] / $total * 100),
                'dasharray' => Angka::format($panjang).' '.Angka::format($keliling - $panjang),
                'dashoffset' => Angka::format(-$terpakai),
            ];
            $terpakai += $bagian;
        }

        return [
            'viewBox' => '0 0 160 160',
            'cx' => Angka::format(self::PUSAT),
            'cy' => Angka::format(self::PUSAT),
            'r' => Angka::format(self::RADIUS),
            'strokeWidth' => Angka::format(self::TEBAL),
            'circumference' => Angka::format($keliling),
            'total' => $total,
            'kosong' => $total === 0,
            'segments' => $hasil,
        ];
    }
}
