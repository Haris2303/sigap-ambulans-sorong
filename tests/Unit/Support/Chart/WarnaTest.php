<?php

namespace Tests\Unit\Support\Chart;

use App\Enums\PesananStatus;
use App\Support\Chart\Warna;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WarnaTest extends TestCase
{
    public function test_kelas_isi_dan_latar_dari_token_yang_diizinkan(): void
    {
        $peta = [
            'primary' => ['stroke-primary', 'bg-primary'],
            'success' => ['stroke-success', 'bg-success'],
            'error' => ['stroke-error', 'bg-error'],
            'warning' => ['stroke-warning', 'bg-warning'],
            'outline' => ['stroke-outline', 'bg-outline'],
            'chart-sky' => ['stroke-chart-sky', 'bg-chart-sky'],
        ];

        foreach ($peta as $nama => [$stroke, $latar]) {
            $this->assertSame($stroke, Warna::stroke($nama));
            $this->assertSame($latar, Warna::latar($nama));
        }
    }

    public function test_warna_di_luar_daftar_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Warna::stroke('ungu');
    }

    public function test_warna_tetap_per_status_pesanan(): void
    {
        $diharapkan = [
            'selesai' => 'success',
            'dibatalkan' => 'outline',
            'ditolak' => 'error',
            'menunggu' => 'warning',
            'terverifikasi' => 'primary',
            'ditugaskan' => 'chart-sky',
            'diterima' => 'chart-sky',
            'dalam_perjalanan' => 'chart-sky',
        ];

        foreach (PesananStatus::cases() as $status) {
            $this->assertArrayHasKey($status->value, $diharapkan, 'Status baru perlu warna grafik: '.$status->value);
            $this->assertSame($diharapkan[$status->value], $status->warnaGrafik(), $status->value);
            $this->assertNotSame('', Warna::stroke($status->warnaGrafik()), 'Warna status harus dikenal Warna.');
        }
        $this->assertCount(count($diharapkan), PesananStatus::cases());
    }
}
