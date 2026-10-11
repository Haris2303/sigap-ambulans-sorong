<?php

namespace Tests\Feature\Arsitektur;

use Tests\TestCase;

/**
 * Menjaga resources/css/design-tokens.css (dihasilkan agent/tokens.mjs) tetap sinkron dengan front matter DESIGN.md.
 * Bila gagal: jalankan `node agent/tokens.mjs`.
 */
class TokenDesainTest extends TestCase
{
    private string $css;

    /** @var array<string, string> */
    private array $warna;

    /** @var array<int, string> */
    private array $gayaTeks;

    /** @var array<string, string> */
    private array $sudut;

    protected function setUp(): void
    {
        parent::setUp();

        $dokumen = str_replace("\r\n", "\n", (string) file_get_contents(base_path('DESIGN.md')));
        $this->assertSame(1, preg_match('/^---\n(.*?)\n---\n/s', $dokumen, $cocok), 'Front matter DESIGN.md tidak ditemukan.');
        $frontMatter = $cocok[1];

        $this->warna = $this->petaBlok($frontMatter, 'colors');
        $this->sudut = $this->petaBlok($frontMatter, 'rounded');
        $this->gayaTeks = $this->namaTipografi($frontMatter);
        $this->css = (string) file_get_contents(base_path('resources/css/design-tokens.css'));
    }

    public function test_setiap_warna_di_design_md_ada_di_css_dan_sebaliknya(): void
    {
        $this->assertNotEmpty($this->warna);

        foreach ($this->warna as $nama => $nilai) {
            $this->assertStringContainsString('--color-'.$nama.': '.strtolower($nilai).';', $this->css, 'Warna '.$nama.' tidak sinkron.');
        }

        preg_match_all('/^\s*--color-([\w-]+): #/m', $this->css, $dalamCss);
        $this->assertEqualsCanonicalizing(array_keys($this->warna), $dalamCss[1], 'Daftar warna CSS berbeda dari DESIGN.md.');
    }

    public function test_skema_warna_material_3_lengkap_dan_tanpa_nama_lama(): void
    {
        foreach (['primary', 'on-primary', 'primary-container', 'on-primary-container', 'secondary-container',
            'on-secondary-container', 'error', 'on-error', 'error-container', 'on-error-container', 'surface',
            'surface-container-lowest', 'surface-container-low', 'surface-container', 'surface-container-high',
            'surface-container-highest', 'on-surface', 'on-surface-variant', 'outline', 'outline-variant', 'scrim'] as $peran) {
            $this->assertArrayHasKey($peran, $this->warna, 'Peran M3 hilang: '.$peran);
        }

        // canvas bukan nama lama lagi: token latar halaman dasbor (docs/DESAIN_DASBOR_MATERIAL.md).
        foreach (['line', 'control-border', 'primary-soft', 'primary-hover', 'danger', 'danger-soft', 'neutral-soft', 'on-surface-muted'] as $lama) {
            $this->assertArrayNotHasKey($lama, $this->warna, 'Nama token lama masih ada: '.$lama);
        }
    }

    public function test_pdf_laporan_hanya_memakai_warna_token_design_md(): void
    {
        $pdf = (string) file_get_contents(base_path('resources/views/pdf/laporan.blade.php'));
        preg_match_all('/#[0-9a-fA-F]{6}\b/', $pdf, $cocok);

        $this->assertNotEmpty($cocok[0], 'PDF berdiri sendiri (dompdf) dan memakai nilai heksadesimal statis.');
        $token = array_map('strtolower', array_values($this->warna));
        foreach (array_unique($cocok[0]) as $warna) {
            $this->assertContains(strtolower($warna), $token, 'Warna PDF di luar token DESIGN.md: '.$warna);
        }
    }

    public function test_warna_dasbor_canvas_dan_chart_sky(): void
    {
        $this->assertSame('#EBF1F6', $this->warna['canvas'] ?? null);
        $this->assertSame('#7FB9DA', $this->warna['chart-sky'] ?? null);
        $this->assertStringContainsString('--color-canvas: #ebf1f6;', $this->css);
        $this->assertStringContainsString('--color-chart-sky: #7fb9da;', $this->css);
        $this->assertCount(32, $this->warna, 'Skema 30 warna M3 ditambah canvas dan chart-sky; tidak ada warna baru lain.');
    }

    public function test_gaya_teks_dan_sudut_sinkron(): void
    {
        $this->assertCount(20, $this->gayaTeks, 'Skala tipe harus 15 gaya M3 ditambah 5 gaya dasbor.');
        foreach (['page-title', 'card-title', 'stat-hero', 'stat-l', 'stat-m'] as $gayaDasbor) {
            $this->assertContains($gayaDasbor, $this->gayaTeks);
            $this->assertStringContainsString('--text-'.$gayaDasbor.'--font-weight: 500;', $this->css);
        }
        $this->assertStringContainsString('--text-stat-hero: 56px;', $this->css);
        $this->assertStringContainsString('--text-stat-hero--line-height: 60px;', $this->css);
        $this->assertStringContainsString('--text-page-title: 28px;', $this->css);
        $this->assertStringContainsString('--text-card-title: 20px;', $this->css);
        $this->assertStringContainsString('--text-stat-l: 32px;', $this->css);
        $this->assertStringContainsString('--text-stat-m: 28px;', $this->css);
        foreach ($this->gayaTeks as $nama) {
            $this->assertStringContainsString('--text-'.$nama.': ', $this->css, 'Gaya teks '.$nama.' tidak sinkron.');
            $this->assertStringContainsString('--text-'.$nama.'--line-height: ', $this->css);
        }

        $this->assertSame(['xs' => '4px', 'sm' => '8px', 'md' => '12px', 'lg' => '16px', 'xl' => '28px', 'full' => '9999px'], $this->sudut);
        foreach ($this->sudut as $nama => $nilai) {
            $this->assertStringContainsString('--radius-'.$nama.': '.$nilai.';', $this->css);
        }
    }

    public function test_bayangan_dan_lapisan_keadaan_ada_di_css(): void
    {
        foreach ([1, 2, 3] as $tingkat) {
            $this->assertStringContainsString('--shadow-level-'.$tingkat.': ', $this->css);
        }
        $this->assertStringNotContainsString('--shadow-overlay', $this->css);

        // Bayangan dasbor: warna on-surface (24 28 32), bukan hitam; nav-active satu lapis.
        $this->assertStringContainsString('--shadow-card: 0px 1px 3px 0px rgb(24 28 32 / 0.1), 0px 6px 16px 0px rgb(24 28 32 / 0.06);', $this->css);
        $this->assertStringContainsString('--shadow-card-raised: 0px 1px 3px 0px rgb(24 28 32 / 0.12), 0px 8px 20px 0px rgb(24 28 32 / 0.1);', $this->css);
        $this->assertStringContainsString('--shadow-nav-active: 0px 2px 6px 0px rgb(24 28 32 / 0.22);', $this->css);
        $this->assertStringContainsString('--shadow-level-1: 0px 1px 2px 0px rgb(0 0 0 / 0.3)', $this->css, 'Level 1 tetap hitam.');

        foreach (['hover' => '0.08', 'focus' => '0.1', 'pressed' => '0.1', 'dragged' => '0.16'] as $keadaan => $opasitas) {
            $this->assertStringContainsString('--state-'.$keadaan.': '.$opasitas.';', $this->css);
        }
        $this->assertStringContainsString('--spacing-touch: 48px;', $this->css);
    }

    /** @return array<string, string> */
    private function petaBlok(string $frontMatter, string $blok): array
    {
        $this->assertSame(1, preg_match('/^'.$blok.':\n((?:[ ]{2}.*\n?)+)/m', $frontMatter, $cocok), 'Blok '.$blok.' tidak ditemukan.');

        $peta = [];
        foreach (explode("\n", rtrim($cocok[1])) as $baris) {
            if (preg_match('/^[ ]{2}([\w-]+): "?([^"]+)"?$/', $baris, $kolom) === 1) {
                $peta[$kolom[1]] = $kolom[2];
            }
        }

        return $peta;
    }

    /** @return array<int, string> */
    private function namaTipografi(string $frontMatter): array
    {
        $this->assertSame(1, preg_match('/^typography:\n((?:[ ]{2}.*\n?)+)/m', $frontMatter, $cocok), 'Blok typography tidak ditemukan.');
        preg_match_all('/^[ ]{2}([\w-]+):$/m', $cocok[1], $nama);

        return $nama[1];
    }
}
