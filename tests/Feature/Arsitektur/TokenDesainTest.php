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

    public function test_skema_warna_arah_b_lengkap_dan_tanpa_nama_lama(): void
    {
        // Peran M3 dan peran baru arah B (docs/RETEMA_B.md 4.1).
        foreach (['primary', 'on-primary', 'primary-container', 'on-primary-container', 'secondary', 'on-secondary',
            'secondary-container', 'on-secondary-container', 'tertiary', 'on-tertiary', 'tertiary-container',
            'on-tertiary-container', 'success', 'on-success', 'success-container', 'on-success-container', 'warning',
            'on-warning', 'warning-container', 'on-warning-container', 'error', 'on-error', 'error-container',
            'on-error-container', 'surface', 'surface-container-lowest', 'surface-container-low', 'surface-container',
            'surface-container-high', 'surface-container-highest', 'on-surface', 'on-surface-variant', 'outline',
            'outline-variant', 'inverse-surface', 'inverse-on-surface', 'inverse-primary', 'selected', 'scrim',
            'chart-mint', 'chart-coral', 'chart-amber', 'chart-slate'] as $peran) {
            $this->assertArrayHasKey($peran, $this->warna, 'Peran warna hilang: '.$peran);
        }

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

    public function test_warna_arah_b_dan_token_lama(): void
    {
        // canvas (dari D1) bernilai sama dengan surface dan boleh dihapus di R9; chart-sky dipertahankan sampai grafik lama dihapus (R9).
        $this->assertSame($this->warna['surface'] ?? null, $this->warna['canvas'] ?? null, 'canvas harus sama dengan surface.');
        $this->assertSame('#F3F5FB', $this->warna['surface'] ?? null);
        $this->assertSame('#7FB9DA', $this->warna['chart-sky'] ?? null);
        $this->assertStringContainsString('--color-canvas: #f3f5fb;', $this->css);
        $this->assertStringContainsString('--color-chart-sky: #7fb9da;', $this->css);

        // Opasitas scrim diberikan di kelas (bg-scrim/45), bukan di token.
        $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $this->warna['scrim'] ?? '', 'scrim tanpa alfa.');

        $this->assertCount(45, $this->warna, '44 warna arah B (docs/RETEMA_B.md 4.1) ditambah chart-sky lama; tidak ada warna lain.');
    }

    public function test_gaya_teks_dan_sudut_sinkron(): void
    {
        $this->assertCount(25, $this->gayaTeks, 'Skala tipe harus 15 gaya arah B ditambah 10 gaya lama.');

        // Tabel 4.4: ukuran, tinggi baris, bobot, jarak huruf.
        $arahB = [
            'stat-hero' => ['56px', '62px', '800', '-0.03em'], 'stat-l' => ['38px', '44px', '800', '-0.03em'],
            'page-title' => ['30px', '38px', '800', '-0.025em'], 'page-title-s' => ['26px', '34px', '800', '-0.025em'],
            'title-xl' => ['22px', '28px', '800', '-0.02em'], 'title-l' => ['18px', '26px', '800', '-0.01em'],
            'title-m' => ['16px', '24px', '700', '0em'], 'brand' => ['19px', '24px', '800', '-0.02em'],
            'ring-value' => ['16px', '20px', '800', '0em'], 'body-l' => ['16px', '24px', '400', '0em'],
            'body-m' => ['14px', '20px', '400', '0em'], 'body-s' => ['13px', '18px', '400', '0em'],
            'label-l' => ['14px', '20px', '600', '0em'], 'label-m' => ['12px', '16px', '600', '0em'],
            'chip' => ['12px', '16px', '700', '0em'],
        ];
        foreach ($arahB as $nama => [$ukuran, $tinggi, $bobot, $jarak]) {
            $this->assertContains($nama, $this->gayaTeks);
            $this->assertStringContainsString('--text-'.$nama.': '.$ukuran.';', $this->css);
            $this->assertStringContainsString('--text-'.$nama.'--line-height: '.$tinggi.';', $this->css);
            $this->assertStringContainsString('--text-'.$nama.'--font-weight: '.$bobot.';', $this->css);
            $this->assertStringContainsString('--text-'.$nama.'--letter-spacing: '.$jarak.';', $this->css);
        }
        foreach (['display-l', 'display-m', 'display-s', 'headline-l', 'headline-m', 'headline-s', 'title-s', 'label-s', 'card-title', 'stat-m'] as $lama) {
            $this->assertContains($lama, $this->gayaTeks, 'Gaya lama dipertahankan sampai R9: '.$lama);
        }

        // Satu kelas text-<gaya> memuat keempat nilai, untuk setiap gaya.
        foreach ($this->gayaTeks as $nama) {
            foreach (['', '--line-height', '--font-weight', '--letter-spacing'] as $bagian) {
                $this->assertStringContainsString('--text-'.$nama.$bagian.': ', $this->css, 'Gaya teks '.$nama.' tidak lengkap: '.$bagian);
            }
        }

        // Tabel 4.5 ditambah xs lama.
        $this->assertSame(['sm' => '10px', 'md' => '14px', 'lg' => '18px', 'xl' => '24px', '2xl' => '28px', 'full' => '9999px', 'xs' => '4px'], $this->sudut);
        foreach ($this->sudut as $nama => $nilai) {
            $this->assertStringContainsString('--radius-'.$nama.': '.$nilai.';', $this->css);
        }
    }

    public function test_font_dasar_plus_jakarta_sans_dari_paket_npm(): void
    {
        $this->assertStringContainsString('--font-sans: "Plus Jakarta Sans Variable", "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;', $this->css);

        $paket = json_decode((string) file_get_contents(base_path('package.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('@fontsource-variable/plus-jakarta-sans', ($paket['dependencies'] ?? []) + ($paket['devDependencies'] ?? []));
        $this->assertStringContainsString("@import '@fontsource-variable/plus-jakarta-sans/wght.css';", (string) file_get_contents(base_path('resources/css/app.css')));
    }

    public function test_bayangan_dan_lapisan_keadaan_ada_di_css(): void
    {
        // Tabel 4.6: tinta biru tua rgb(30 41 100), bukan hitam.
        $this->assertStringContainsString('--shadow-card: 0px 10px 30px -12px rgb(30 41 100 / 0.14), 0px 1px 2px 0px rgb(30 41 100 / 0.04);', $this->css);
        $this->assertStringContainsString('--shadow-control: 0px 4px 14px -8px rgb(30 41 100 / 0.2);', $this->css);
        $this->assertStringContainsString('--shadow-chip: 0px 2px 8px -2px rgb(30 41 100 / 0.2);', $this->css);
        $this->assertStringContainsString('--shadow-overlay: 0px 24px 60px -20px rgb(30 41 100 / 0.35);', $this->css);
        $this->assertStringNotContainsString('rgb(0 0 0', $this->css, 'Tidak ada bayangan hitam.');

        // Token lama dipertahankan sampai R9 supaya tidak ada gaya yang hilang diam-diam.
        foreach ([1, 2, 3] as $tingkat) {
            $this->assertStringContainsString('--shadow-level-'.$tingkat.': ', $this->css);
        }
        $this->assertStringContainsString('--shadow-level-1: 0px 1px 2px 0px rgb(30 41 100 / 0.3)', $this->css);
        $this->assertStringContainsString('--shadow-card-raised: 0px 1px 3px 0px rgb(27 36 55 / 0.12), 0px 8px 20px 0px rgb(27 36 55 / 0.1);', $this->css);
        $this->assertStringContainsString('--shadow-nav-active: 0px 2px 6px 0px rgb(27 36 55 / 0.22);', $this->css);

        foreach (['hover' => '0.08', 'focus' => '0.1', 'pressed' => '0.1', 'dragged' => '0.16'] as $keadaan => $opasitas) {
            $this->assertStringContainsString('--state-'.$keadaan.': '.$opasitas.';', $this->css);
        }
        $this->assertStringContainsString('--spacing-touch: 48px;', $this->css);
        $this->assertStringContainsString('--motion-duration-medium: 200ms;', $this->css, 'Buka dan tutup 200ms (docs/RETEMA_B.md 4.7).');
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
