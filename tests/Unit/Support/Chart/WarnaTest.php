<?php

namespace Tests\Unit\Support\Chart;

use App\Enums\PesananStatus;
use App\Support\Chart\Warna;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WarnaTest extends TestCase
{
    public function test_kelas_latar_dari_token_yang_diizinkan(): void
    {
        $peta = [
            'primary' => 'bg-primary',
            'success' => 'bg-success',
            'error' => 'bg-error',
            'warning' => 'bg-warning',
            'outline' => 'bg-outline',
            'chart-sky' => 'bg-chart-sky',
        ];

        foreach ($peta as $nama => $latar) {
            $this->assertSame($latar, Warna::latar($nama));
        }
    }

    public function test_warna_di_luar_daftar_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Warna::latar('ungu');
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
            $this->assertNotSame('', Warna::latar($status->warnaGrafik()), 'Warna status harus dikenal Warna.');
        }
        $this->assertCount(count($diharapkan), PesananStatus::cases());
    }
}
