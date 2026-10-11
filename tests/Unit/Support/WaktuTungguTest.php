<?php

namespace Tests\Unit\Support;

use App\Support\WaktuTunggu;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WaktuTungguTest extends TestCase
{
    /** Tabel yang sama dipakai resources/js/waktu-tunggu.js (diperiksa dengan Node saat pengembangan). */
    public static function teksPerMenit(): array
    {
        return [
            'nol' => [0, 'Baru dibuat'],
            'satu menit' => [1, 'Menunggu 1 mnt'],
            'dua belas menit' => [12, 'Menunggu 12 mnt'],
            'lima puluh sembilan' => [59, 'Menunggu 59 mnt'],
            'tepat satu jam' => [60, 'Menunggu 1 j'],
            'satu jam lima menit' => [65, 'Menunggu 1 j 5 mnt'],
            'dua jam lima menit' => [125, 'Menunggu 2 j 5 mnt'],
            'dua puluh tiga jam lima puluh sembilan' => [1439, 'Menunggu 23 j 59 mnt'],
            'tepat satu hari' => [1440, 'Menunggu 1 hari'],
            'dua hari lebih' => [3000, 'Menunggu 2 hari'],
        ];
    }

    #[DataProvider('teksPerMenit')]
    public function test_teks_ringkas_per_menit(int $menit, string $diharapkan): void
    {
        $this->assertSame($diharapkan, WaktuTunggu::teks($menit));
    }

    public function test_nada_menurut_ambang_dengan_batas_inklusif(): void
    {
        $ambang = ['peringatan' => 5, 'bahaya' => 10];

        $this->assertSame('netral', WaktuTunggu::nada(0, $ambang));
        $this->assertSame('netral', WaktuTunggu::nada(4, $ambang));
        $this->assertSame('peringatan', WaktuTunggu::nada(5, $ambang));
        $this->assertSame('peringatan', WaktuTunggu::nada(9, $ambang));
        $this->assertSame('bahaya', WaktuTunggu::nada(10, $ambang));
        $this->assertSame('bahaya', WaktuTunggu::nada(5000, $ambang));
        $this->assertSame('bahaya', WaktuTunggu::nada(3, ['peringatan' => 1, 'bahaya' => 2]));
    }

    public function test_detik_dan_menit_dari_selisih_waktu(): void
    {
        $dibuat = Carbon::parse('2026-10-11 09:00:00', 'Asia/Jayapura');

        $this->assertSame(0, WaktuTunggu::detik($dibuat, $dibuat->copy()));
        $this->assertSame(59, WaktuTunggu::detik($dibuat, $dibuat->copy()->addSeconds(59)));
        $this->assertSame(0, WaktuTunggu::menit($dibuat, $dibuat->copy()->addSeconds(59)), 'Menit penuh, bukan pembulatan.');
        $this->assertSame(1, WaktuTunggu::menit($dibuat, $dibuat->copy()->addSeconds(60)));
        $this->assertSame(12, WaktuTunggu::menit($dibuat, $dibuat->copy()->addMinutes(12)->addSeconds(30)));
        $this->assertSame(1440, WaktuTunggu::menit($dibuat, $dibuat->copy()->addDay()));
    }

    public function test_waktu_di_masa_depan_tidak_pernah_negatif(): void
    {
        $dibuat = Carbon::parse('2026-10-11 09:00:00', 'Asia/Jayapura');

        $this->assertSame(0, WaktuTunggu::detik($dibuat, $dibuat->copy()->subMinutes(3)));
        $this->assertSame('Baru dibuat', WaktuTunggu::teks(WaktuTunggu::menit($dibuat, $dibuat->copy()->subHour())));
    }

    public function test_zona_waktu_berbeda_tidak_mengubah_selisih(): void
    {
        $dibuat = Carbon::parse('2026-10-11 09:00:00', 'Asia/Jayapura');
        $sekarang = Carbon::parse('2026-10-11 00:12:00', 'UTC');

        $this->assertSame(12, WaktuTunggu::menit($dibuat, $sekarang));
    }
}
