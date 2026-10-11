<?php

namespace Tests\Unit\Support\Chart;

use App\Support\Chart\Angka;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AngkaTest extends TestCase
{
    public static function kasus(): array
    {
        return [
            'bulat' => [300.0, '300'],
            'dua desimal' => [71.142857, '71.14'],
            'nol di belakang dibuang' => [246.3, '246.3'],
            'pembulatan ke atas' => [89.146, '89.15'],
            'negatif' => [-175.929, '-175.93'],
            'hampir nol negatif menjadi nol' => [-0.001, '0'],
            'nol' => [0.0, '0'],
            'bilangan bulat PHP' => [20, '20'],
            'keliling' => [2 * M_PI * 56, '351.86'],
        ];
    }

    #[DataProvider('kasus')]
    public function test_format_angka_koordinat(float|int $nilai, string $diharapkan): void
    {
        $this->assertSame($diharapkan, Angka::format($nilai));
    }
}
