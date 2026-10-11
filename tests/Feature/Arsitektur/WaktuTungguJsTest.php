<?php

namespace Tests\Feature\Arsitektur;

use App\Support\WaktuTunggu;
use Tests\TestCase;

/**
 * Menjaga pemasangan resources/js/waktu-tunggu.js (audit UX T7) dan kesamaan aturannya dengan App\Support\WaktuTunggu.
 * Perilaku di peramban tidak teruji di sini; logikanya dijalankan dengan DOM tiruan di Node saat pengembangan.
 */
class WaktuTungguJsTest extends TestCase
{
    private function berkas(string $path): string
    {
        return (string) file_get_contents(base_path($path));
    }

    public function test_sigap_js_memasang_penghitung_waktu_tunggu(): void
    {
        $sigap = $this->berkas('resources/js/sigap.js');

        $this->assertStringContainsString("import { pasangWaktuTunggu } from './waktu-tunggu';", $sigap);
        $this->assertMatchesRegularExpression('/^pasangWaktuTunggu\(\);$/m', $sigap);
    }

    public function test_aturan_teks_di_javascript_sama_dengan_php(): void
    {
        $js = $this->berkas('resources/js/waktu-tunggu.js');

        foreach (['Baru dibuat', 'Menunggu ${menit} mnt', 'Menunggu ${Math.floor(menit / 60)} j', ' ${sisa} mnt', 'Menunggu ${Math.floor(menit / 1440)} hari', 'menit < 60', 'menit < 1440'] as $potongan) {
            $this->assertStringContainsString($potongan, $js, $potongan);
        }
        $this->assertSame('Baru dibuat', WaktuTunggu::teks(0));
        $this->assertSame('Menunggu 1 j 5 mnt', WaktuTunggu::teks(65));

        // Nada: ambang inklusif, bahaya dicek lebih dulu.
        $this->assertMatchesRegularExpression('/menit >= bahaya.*return \'bahaya\'.*menit >= peringatan \? \'peringatan\' : \'netral\'/s', $js);
    }

    public function test_javascript_memakai_detik_dari_server_bukan_jam_absolut_perangkat(): void
    {
        $js = $this->berkas('resources/js/waktu-tunggu.js');

        $this->assertStringContainsString('dataset.tungguDetik', $js);
        $this->assertStringNotContainsString('Date.parse', $js, 'Jam absolut perangkat bisa salah; hanya selisih sejak dimuat yang dipakai.');
        $this->assertStringContainsString("'visibilitychange'", $js);
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b|animat/', $js);
    }
}
