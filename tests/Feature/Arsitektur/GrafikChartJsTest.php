<?php

namespace Tests\Feature\Arsitektur;

use App\Support\Chart\Warna;
use ReflectionClass;
use Tests\TestCase;

/**
 * Menjaga pemasangan Chart.js (docs/DESAIN_DASBOR_MATERIAL.md bagian 5.11 sampai 5.13): impor modular, bundel terpisah,
 * dan setiap token yang dibaca JavaScript di runtime benar-benar ada di design-tokens.css. Tanpa browser, kesalahan ketik
 * nama token hanya akan terlihat saat grafik gagal digambar, jadi dijaga di sini.
 */
class GrafikChartJsTest extends TestCase
{
    private function berkas(string $path): string
    {
        return (string) file_get_contents(base_path($path));
    }

    private function css(): string
    {
        return $this->berkas('resources/css/design-tokens.css');
    }

    public function test_chart_js_terpasang_sebagai_paket_npm(): void
    {
        $paket = json_decode($this->berkas('package.json'), true, 512, JSON_THROW_ON_ERROR);
        $semua = ($paket['dependencies'] ?? []) + ($paket['devDependencies'] ?? []);

        $this->assertArrayHasKey('chart.js', $semua);
        $this->assertMatchesRegularExpression('/^\^?4\./', $semua['chart.js']);
    }

    public function test_hanya_bagian_chart_js_yang_dipakai_yang_diimpor_dan_didaftarkan(): void
    {
        $js = $this->berkas('resources/js/grafik.js');

        $this->assertSame(1, preg_match("/import \{([^}]+)\} from 'chart\.js';/", $js, $impor), 'Impor harus bernama dari chart.js, bukan chart.js/auto.');
        $nama = array_map('trim', explode(',', trim($impor[1], " \n,")));
        sort($nama);
        $this->assertSame(['ArcElement', 'BarController', 'BarElement', 'CategoryScale', 'Chart', 'DoughnutController', 'Legend', 'LinearScale', 'Tooltip'], $nama);

        $this->assertStringNotContainsString('chart.js/auto', $js);
        $this->assertSame(1, preg_match('/Chart\.register\(([^)]+)\);/', $js, $daftar));
        $didaftarkan = array_map('trim', explode(',', $daftar[1]));
        sort($didaftarkan);
        $this->assertSame(['ArcElement', 'BarController', 'BarElement', 'CategoryScale', 'DoughnutController', 'Legend', 'LinearScale', 'Tooltip'], $didaftarkan);

        // Judul bawaan Chart.js tidak dipakai.
        $this->assertStringNotContainsString('Title', $js);
    }

    public function test_bundel_grafik_hanya_dimuat_di_halaman_yang_punya_kanvas_grafik(): void
    {
        $app = $this->berkas('resources/js/app.js');

        $this->assertMatchesRegularExpression("/if \(document\.querySelector\('canvas\[data-grafik\]'\)\) \{\s*import\('\.\/grafik'\);\s*\}/", $app);
        $this->assertDoesNotMatchRegularExpression('/^import .*grafik/m', $app, 'Impor statis akan memasukkan Chart.js ke setiap halaman.');
        $this->assertStringNotContainsString('grafik', $this->berkas('vite.config.js'), 'Bundel grafik adalah chunk dinamis, bukan entri.');
    }

    public function test_javascript_grafik_tanpa_warna_mentah_dan_tanpa_animasi_masuk(): void
    {
        foreach (['resources/js/grafik.js', 'resources/js/grafik-opsi.js'] as $path) {
            $js = $this->berkas($path);
            $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $js, $path);
            $this->assertDoesNotMatchRegularExpression('/\b(?:rgb|rgba|hsl|hsla|oklch)\(/', $js, $path);
        }

        $opsi = $this->berkas('resources/js/grafik-opsi.js');
        $this->assertSame(2, substr_count($opsi, 'animation: false'), 'Batang dan donat tanpa animasi masuk.');
        $this->assertStringContainsString("legend: { display: false }", $opsi);
    }

    public function test_token_yang_dibaca_javascript_ada_di_css_hasil_pembuat_token(): void
    {
        $css = $this->css();
        $opsi = $this->berkas('resources/js/grafik-opsi.js');

        $this->assertStringStartsWith('/* DIHASILKAN', $css);
        $this->assertStringContainsString('@theme static {', $css, 'Tanpa "static", Tailwind 4 hanya mengeluarkan variabel tema yang dipakai kelas utilitas.');

        preg_match_all("/tema\.warna\('([\w-]+)'\)|warna\('([\w-]+)'\)|'(outline-variant|surface-container-high)'/", $opsi, $w);
        $warna = array_unique(array_filter(array_merge($w[1], $w[2], $w[3])));
        $this->assertContains('primary', $warna);
        $this->assertContains('chart-sky', $warna);
        $this->assertContains('inverse-surface', $warna);
        foreach ($warna as $nama) {
            $this->assertStringContainsString('--color-'.$nama.': #', $css, 'Token warna tidak ada: '.$nama);
        }

        preg_match_all("/tema\.teks\('([\w-]+)'/", $opsi, $t);
        $this->assertNotEmpty($t[1]);
        foreach (array_unique($t[1]) as $nama) {
            $this->assertStringContainsString('--text-'.$nama.': ', $css, 'Gaya teks tidak ada: '.$nama);
            $this->assertStringContainsString('--text-'.$nama.'--line-height: ', $css);
            $this->assertStringContainsString('--text-'.$nama.'--font-weight: ', $css);
        }

        preg_match_all("/tema\.sudut\('([\w-]+)'\)/", $opsi, $r);
        $this->assertNotEmpty($r[1]);
        foreach (array_unique($r[1]) as $nama) {
            $this->assertStringContainsString('--radius-'.$nama.': ', $css, 'Sudut tidak ada: '.$nama);
        }

        $this->assertStringContainsString('--font-sans: ', $css);
    }

    public function test_warna_segmen_donat_yang_diizinkan_semuanya_token(): void
    {
        $diizinkan = array_keys((new ReflectionClass(Warna::class))->getConstant('LATAR'));

        $this->assertNotEmpty($diizinkan);
        foreach ($diizinkan as $nama) {
            $this->assertStringContainsString('--color-'.$nama.': #', $this->css(), 'Token warna tidak ada: '.$nama);
        }
    }
}
