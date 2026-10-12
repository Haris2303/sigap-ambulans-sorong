<?php

namespace Tests\Feature\Arsitektur;

use Tests\TestCase;

/**
 * Menjaga pemasangan Chart.js arah B (docs/RETEMA_B.md bagian 7.1): impor modular, bundel terpisah, tanpa warna
 * mentah di skrip (kecuali suffix alfa gradien dua digit heksa), dan token yang dibaca lewat getComputedStyle benar-
 * benar ada di design-tokens.css. GrafikChartJsTest menjaga bundel lama (grafik.js/grafik-opsi.js) secara terpisah.
 */
class GrafikRetemaTest extends TestCase
{
    private function berkas(string $path): string
    {
        return (string) file_get_contents(base_path($path));
    }

    public function test_hanya_bagian_chart_js_yang_dipakai_yang_diimpor_dan_didaftarkan(): void
    {
        $js = $this->berkas('resources/js/charts.js');

        $this->assertSame(1, preg_match("/import \{([^}]+)\} from 'chart\.js';/", $js, $impor), 'Impor harus bernama dari chart.js, bukan chart.js/auto.');
        $nama = array_map('trim', explode(',', trim($impor[1], " \n,")));
        sort($nama);
        $this->assertSame(
            ['ArcElement', 'CategoryScale', 'Chart', 'DoughnutController', 'Filler', 'LineController', 'LineElement', 'LinearScale', 'PointElement', 'Tooltip'],
            $nama,
        );
        $this->assertStringNotContainsString('chart.js/auto', $js);

        $this->assertSame(1, preg_match('/Chart\.register\(([^)]+)\);/', $js, $daftar));
        $didaftarkan = array_map('trim', explode(',', $daftar[1]));
        sort($didaftarkan);
        $this->assertSame(['ArcElement', 'CategoryScale', 'DoughnutController', 'Filler', 'LineController', 'LineElement', 'LinearScale', 'PointElement', 'Tooltip'], $didaftarkan);
    }

    public function test_bundel_charts_hanya_dimuat_di_halaman_yang_punya_kanvas_data_chart(): void
    {
        $app = $this->berkas('resources/js/app.js');

        $this->assertMatchesRegularExpression("/if \(document\.querySelector\('canvas\[data-chart\]'\)\) \{\s*import\('\.\/charts'\);\s*\}/", $app);
        $this->assertDoesNotMatchRegularExpression('/^import .*charts/m', $app, 'Impor statis akan memasukkan Chart.js ke setiap halaman.');
        $this->assertStringNotContainsString('charts', $this->berkas('vite.config.js'), 'Bundel charts adalah chunk dinamis, bukan entri.');
    }

    public function test_javascript_charts_tanpa_warna_mentah_dan_menghormati_prefers_reduced_motion(): void
    {
        $js = $this->berkas('resources/js/charts.js');

        // Satu-satunya "warna" bukan token yang diizinkan adalah suffix alfa heksa 2 digit pada gradien ('33'/'00').
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $js);
        $this->assertDoesNotMatchRegularExpression('/\b(?:rgb|rgba|hsl|hsla|oklch)\(/', $js);

        $this->assertStringContainsString('prefers-reduced-motion: reduce', $js);
        $this->assertStringContainsString("easing: 'easeOutQuart'", $js);
        $this->assertStringContainsString('duration: 700', $js);
    }

    public function test_token_yang_dibaca_javascript_ada_di_css_hasil_pembuat_token(): void
    {
        $css = $this->berkas('resources/css/design-tokens.css');
        $js = $this->berkas('resources/js/charts.js');

        preg_match_all("/tema\.warna\('([\w-]+)'\)/", $js, $w);
        $warna = array_unique($w[1]);
        $this->assertContains('on-surface-variant', $warna);
        $this->assertContains('inverse-surface', $warna);
        $this->assertContains('surface-container-highest', $warna);
        foreach ($warna as $nama) {
            $this->assertStringContainsString('--color-'.$nama.': #', $css, 'Token warna tidak ada: '.$nama);
        }

        preg_match_all("/tema\.teks\('([\w-]+)'/", $js, $t);
        $this->assertNotEmpty($t[1]);
        foreach (array_unique($t[1]) as $nama) {
            $this->assertStringContainsString('--text-'.$nama.': ', $css, 'Gaya teks tidak ada: '.$nama);
        }
    }
}
