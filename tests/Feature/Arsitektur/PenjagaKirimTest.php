<?php

namespace Tests\Feature\Arsitektur;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Penjaga kirim formulir (audit UX T3): tombol kirim dinonaktifkan dan berlabel proses selama formulir dikirim.
 * Perilaku di peramban tidak teruji di sini; logikanya dijalankan dengan DOM tiruan di Node saat pengembangan,
 * dan yang dijaga di sini adalah pemasangannya serta markup yang dibutuhkan.
 */
class PenjagaKirimTest extends TestCase
{
    private function berkas(string $path): string
    {
        return (string) file_get_contents(base_path($path));
    }

    public function test_tombol_kirim_membawa_label_proses_hanya_bila_diminta(): void
    {
        $khusus = Blade::render('<x-ui.tombol tipe="submit" memuat="Mengirim pesanan...">Kirim pesanan</x-ui.tombol>');
        $this->assertStringContainsString('data-memuat="Mengirim pesanan..."', $khusus);

        $bawaan = Blade::render('<x-ui.tombol tipe="submit">Simpan</x-ui.tombol>');
        $this->assertStringNotContainsString('data-memuat', $bawaan, 'Tanpa prop memuat, JavaScript memakai "Memproses...".');

        $bukanKirim = Blade::render('<x-ui.tombol memuat="Mengirim...">Buka</x-ui.tombol>');
        $this->assertStringNotContainsString('data-memuat', $bukanKirim, 'Hanya tombol tipe submit.');

        $tautan = Blade::render('<x-ui.tombol href="https://contoh.test" memuat="Mengirim...">Buka</x-ui.tombol>');
        $this->assertStringNotContainsString('data-memuat', $tautan);
    }

    public function test_sigap_js_memasang_penjaga_kirim_untuk_semua_halaman(): void
    {
        $sigap = $this->berkas('resources/js/sigap.js');

        $this->assertStringContainsString("import { pasangPenjagaKirim } from './formulir';", $sigap);
        $this->assertMatchesRegularExpression('/^pasangPenjagaKirim\(\);$/m', $sigap);
    }

    public function test_penjaga_menonaktifkan_tombol_mencegah_kiriman_kedua_dan_memulihkan_dari_riwayat(): void
    {
        $js = $this->berkas('resources/js/formulir.js');

        foreach (["'submit'", 'tombol.disabled = true', "setAttribute('aria-busy', 'true')", 'e.preventDefault()', "'pageshow'", 'e.persisted', 'data-sibuk'] as $penanda) {
            $this->assertStringContainsString($penanda, $js, $penanda);
        }
        $this->assertStringContainsString("'Memproses...'", $js);
        // Tanpa spinner dan tanpa animasi (DESIGN.md, Motion).
        $this->assertDoesNotMatchRegularExpression('/animate-|\banimation\b|classList/', $js);
    }
}
