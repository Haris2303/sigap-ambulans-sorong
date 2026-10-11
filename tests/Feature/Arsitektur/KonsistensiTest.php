<?php

namespace Tests\Feature\Arsitektur;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Menjalankan aturan agent/konsistensi.json dari PHP (padanan pemindai runner).
 */
class KonsistensiTest extends TestCase
{
    public function test_semua_aturan_konsistensi_terpenuhi(): void
    {
        $aturan = $this->muatAturan();
        $this->assertNotEmpty($aturan, 'Tidak ada aturan konsistensi yang terbaca.');

        $pelanggaran = [];
        foreach ($aturan as $a) {
            foreach ($this->periksa($a) as $temuan) {
                $pelanggaran[] = '['.$a['id'].'] '.$temuan.' : '.$a['pesan'];
            }
        }

        $this->assertSame([], $pelanggaran, "Pelanggaran konsistensi:\n".implode("\n", $pelanggaran));
    }

    public function test_aturan_larang_benar_benar_mendeteksi_pelanggaran(): void
    {
        $istilah = collect($this->muatAturan())->firstWhere('id', 'istilah-baku');
        $this->assertNotNull($istilah);
        $this->assertSame(1, preg_match($this->regex($istilah), 'class Supir {}'));
        $this->assertSame(0, preg_match($this->regex($istilah), 'class Sopir {}'));

        // Nama ikon Lucide "ambulance" sebagai literal bertanda kutip diizinkan; ejaan lain tetap ditolak.
        foreach (['<x-ui.icon-chip icon="ambulance" />', "'ikon' => 'ambulance'", '<x-ui.tile icon="ambulance" label="Ambulans tersedia" />', 'class Ambulans {}'] as $boleh) {
            $this->assertSame(0, preg_match($this->regex($istilah), $boleh), $boleh.' harus diterima');
        }
        foreach (['<p>Ambulance darurat</p>', '<x-ui.icon-chip icon="ambulance-foo" />', 'icon="ambulan"', 'class Ambulan {}', 'ambulance_id', '"ambulance darurat"'] as $dilarang) {
            $this->assertSame(1, preg_match($this->regex($istilah), $dilarang), $dilarang.' harus ditolak');
        }

        $emoji = collect($this->muatAturan())->firstWhere('id', 'emoji');
        $this->assertSame(1, preg_match($this->regex($emoji), "ikon \u{1F600}"));
        $this->assertSame(0, preg_match($this->regex($emoji), 'teks biasa'));
    }

    public function test_gaya_dilarang_menolak_gaya_di_luar_token_dan_menerima_token_material_3(): void
    {
        $aturan = collect($this->muatAturan())->firstWhere('id', 'gaya-dilarang');
        $this->assertNotNull($aturan);

        // Bayangan Tailwind bawaan dan bayangan sembarang tetap dilarang meski token dasbor kini diizinkan.
        $dilarang = ['shadow-sm', 'shadow-md', 'shadow-lg', 'shadow-xl', 'shadow-2xl', 'shadow-inner', 'shadow-[0_4px_8px_red]',
            'rounded-2xl', 'rounded-t-3xl', 'uppercase', 'italic', 'font-bold', 'tracking-wide',
            'text-lg', 'text-2xl', 'backdrop-blur-sm', 'blur-md', 'drop-shadow-lg', 'bg-linear-to-r', 'bg-gradient-to-r',
            'from-primary', 'hover:scale-105', 'hover:-translate-y-1', 'animate-pulse', 'rounded-[10px]', 'shadow-[0_2px_4px_black]'];
        foreach ($dilarang as $kelas) {
            $this->assertSame(1, preg_match($this->regex($aturan), '<div class="flex '.$kelas.' gap-2">'), $kelas.' harus ditolak');
        }

        $diterima = ['shadow-level-1', 'shadow-level-3', 'shadow-none', 'rounded-xs', 'rounded-sm', 'rounded-md', 'rounded-lg',
            'rounded-xl', 'rounded-t-xl', 'rounded-full', 'text-display-s', 'text-headline-s', 'text-title-m', 'text-body-l',
            'text-label-l', 'font-medium', 'font-normal', 'bg-surface-container-high', 'border-outline-variant',
            'bg-secondary-container', 'text-on-secondary-container', 'hover:bg-primary', 'transition-colors', 'min-h-touch', 'w-drawer',
            // Token dasbor (docs/DESAIN_DASBOR_MATERIAL.md bagian 2): bayangan, latar, warna grafik, dan gaya tipe.
            'shadow-card', 'shadow-card-raised', 'shadow-nav-active', 'bg-canvas', 'bg-chart-sky', 'fill-chart-sky',
            'text-page-title', 'text-card-title', 'text-stat-hero', 'text-stat-l', 'text-stat-m', 'rounded-lg', 'rounded-full'];
        foreach ($diterima as $kelas) {
            $this->assertSame(0, preg_match($this->regex($aturan), '<div class="flex '.$kelas.' gap-2">'), $kelas.' harus diterima');
        }
    }

    public function test_token_lama_ditolak_dan_token_material_3_diterima(): void
    {
        $aturan = collect($this->muatAturan())->firstWhere('id', 'token-lama');
        $this->assertNotNull($aturan, 'Aturan token-lama belum ada di konsistensi.json.');
        $this->assertSame('larang', $aturan['tipe']);

        // bg-canvas bukan lagi nama lama: canvas adalah token latar halaman dasbor.
        $dilarang = ['divide-line', 'border-line', 'border-control-border', 'text-on-surface-muted', 'hover:bg-primary-soft',
            'hover:text-primary-hover', 'bg-danger', 'text-danger', 'border-danger', 'bg-danger-soft', 'bg-warning-soft',
            'bg-success-soft', 'bg-neutral-soft', 'text-h1', 'text-h2', 'text-h3', 'text-body', 'text-body-sm', 'text-label', 'text-caption',
            'text-display', 'font-semibold', 'shadow-overlay', 'w-rail', 'ukuran="sm"'];
        foreach ($dilarang as $kelas) {
            $this->assertSame(1, preg_match($this->regex($aturan), '<div class="flex '.$kelas.' gap-2">'), $kelas.' harus ditolak');
        }

        $diterima = ['bg-surface', 'bg-surface-container-lowest', 'bg-surface-container-high', 'text-body-l', 'text-body-m', 'text-body-s',
            'text-label-l', 'text-label-m', 'text-display-s', 'text-headline-s', 'text-title-l', 'text-title-m', 'font-medium', 'font-normal',
            'border-outline', 'border-outline-variant', 'border-error', 'bg-error', 'bg-error-container', 'text-on-error-container',
            'bg-warning-container', 'bg-success-container', 'text-success', 'text-warning', 'text-on-surface-variant',
            'hover:text-on-primary-container', 'bg-primary-container', 'bg-secondary-container', 'w-drawer', 'min-h-touch',
            'shadow-level-3', 'bg-linear-to-r', 'text-primary', 'ukuran="lg"', 'ukuran="md"',
            'bg-canvas', 'bg-chart-sky', 'shadow-card', 'text-page-title', 'text-stat-hero', 'text-stat-l', 'text-stat-m'];
        foreach ($diterima as $kelas) {
            $this->assertSame(0, preg_match($this->regex($aturan), '<div class="flex '.$kelas.' gap-2">'), $kelas.' harus diterima');
        }
    }

    public function test_komponen_lama_ditolak_di_halaman_dan_komponen_dasbor_diterima(): void
    {
        $aturan = collect($this->muatAturan())->firstWhere('id', 'komponen-lama');
        $this->assertNotNull($aturan, 'Aturan komponen-lama belum ada di konsistensi.json.');
        $this->assertContains('^resources/views/components/', $aturan['kecuali']);

        foreach (['<x-ui.ringkasan>', '<x-ui.ringkasan class="x">', '<x-ui.ringkasan-sel label="A" />', '<x-ui.grafik-batang :data="$g" />'] as $lama) {
            $this->assertSame(1, preg_match($this->regex($aturan), $lama), $lama.' harus ditolak');
        }
        foreach (['<x-ui.stat tone="primary" />', '<x-ui.chart.bar :labels="$l" />', '<x-ui.chart.donut />', '<x-ui.tile />', '<x-ui.ringkasanbaru />'] as $baru) {
            $this->assertSame(0, preg_match($this->regex($aturan), $baru), $baru.' harus diterima');
        }
    }

    public function test_warna_js_menolak_warna_mentah_dan_menerima_pembacaan_token(): void
    {
        $aturan = collect($this->muatAturan())->firstWhere('id', 'warna-js');
        $this->assertNotNull($aturan, 'Aturan warna-js belum ada di konsistensi.json.');
        $this->assertSame(['resources/js'], $aturan['folder']);

        foreach (["const warna = '#216487';", "ctx.fillStyle = '#fff';", 'ctx.fillStyle = "rgb(0 0 0)";', 'color: rgba(0, 0, 0, 0.5),', "const c = 'oklch(0.5 0.1 200)';", 'x = hsl(10 20% 30%);'] as $dilarang) {
            $this->assertSame(1, preg_match($this->regex($aturan), $dilarang), $dilarang.' harus ditolak');
        }
        foreach (["tema.warna('primary')", "getPropertyValue('--color-primary')", "querySelector('#peta')", 'label(nilai)', "kanvas.dataset.grafik"] as $boleh) {
            $this->assertSame(0, preg_match($this->regex($aturan), $boleh), $boleh.' harus diterima');
        }
    }

    public function test_warna_css_menerima_hanya_var_token_dan_menolak_nilai_mentah(): void
    {
        $aturan = collect($this->muatAturan())->firstWhere('id', 'warna-css');
        $this->assertNotNull($aturan);

        $dilarang = ['color: #fff;', 'color: #0b5c80;', 'color: rgb(0 0 0);', 'color: oklch(0.5 0.1 200);',
            'background: linear-gradient(red, blue);', 'box-shadow: 0 1px 2px black;', 'box-shadow: 0 0 4px var(--shadow-level-1);',
            'box-shadow: var(--shadow-level-4);', 'box-shadow: var(--shadow-card-tinggi);', 'box-shadow: 0 1px 3px var(--shadow-card);',
            'box-shadow: var(--shadow-card), 0 2px 4px black;', 'box-shadow: var(--shadow-nav);',
            'background: color-mix(in srgb, red 50%, white);',
            '@keyframes geser {', 'animation: geser 1s;'];
        foreach ($dilarang as $baris) {
            $this->assertSame(1, preg_match($this->regex($aturan), $baris), $baris.' harus ditolak');
        }

        $diterima = ['background-color: var(--color-surface);', 'box-shadow: none;', 'box-shadow: var(--shadow-level-2);',
            'box-shadow: var(--shadow-level-3) !important;', 'box-shadow: var(--shadow-card);', 'box-shadow: var(--shadow-card-raised);',
            'box-shadow: var(--shadow-nav-active);', 'box-shadow: var(--shadow-card) !important;',
            'background-color: var(--color-canvas);', 'opacity: var(--state-hover);',
            'background: color-mix(in oklab, var(--color-primary) 8%, transparent);', 'transition: opacity 150ms;'];
        foreach ($diterima as $baris) {
            $this->assertSame(0, preg_match($this->regex($aturan), $baris), $baris.' harus diterima');
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function muatAturan(): array
    {
        $json = json_decode((string) file_get_contents(base_path('agent/konsistensi.json')), true);

        return $json['aturan'] ?? [];
    }

    /** @param array<string, mixed> $aturan */
    private function regex(array $aturan): string
    {
        $pola = $aturan['pola_php'] ?? $aturan['pola'];

        return '~'.str_replace('~', '\~', $pola).'~u';
    }

    /**
     * @param  array<string, mixed>  $aturan
     * @return array<int, string>
     */
    private function periksa(array $aturan): array
    {
        $regex = $this->regex($aturan);
        $temuan = [];

        foreach ($this->berkas($aturan) as $path => $isi) {
            if ($aturan['tipe'] === 'awal') {
                if (! $this->awalCocok($isi, $regex)) {
                    $temuan[] = $path.':1';
                }

                continue;
            }

            foreach (preg_split('/\R/u', $isi) ?: [] as $no => $baris) {
                if (preg_match($regex, $baris) === 1) {
                    $temuan[] = $path.':'.($no + 1);
                }
            }
        }

        return $temuan;
    }

    private function awalCocok(string $isi, string $regex): bool
    {
        foreach (preg_split('/\R/u', $isi) ?: [] as $baris) {
            if (trim($baris) === '' || preg_match('/^\s*\{\{--.*--\}\}\s*$/', $baris) === 1) {
                continue;
            }

            return preg_match($regex, $baris) === 1;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $aturan
     * @return \Generator<string, string>
     */
    private function berkas(array $aturan): \Generator
    {
        $dasar = str_replace('\\', '/', base_path()).'/';
        $kecuali = $aturan['kecuali'] ?? [];

        foreach ($aturan['folder'] as $folder) {
            $akar = base_path($folder);
            if (! is_dir($akar)) {
                continue;
            }

            $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($akar, RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($iter as $file) {
                if (! $file->isFile()) {
                    continue;
                }
                $path = str_replace('\\', '/', $file->getPathname());
                $relatif = str_starts_with($path, $dasar) ? substr($path, strlen($dasar)) : $path;

                $cocokEkstensi = false;
                foreach ($aturan['ekstensi'] as $ext) {
                    if (str_ends_with($relatif, $ext)) {
                        $cocokEkstensi = true;
                    }
                }
                if (! $cocokEkstensi) {
                    continue;
                }

                foreach ($kecuali as $k) {
                    if (preg_match('~'.str_replace('~', '\~', $k).'~', $relatif) === 1) {
                        continue 2;
                    }
                }

                yield $relatif => (string) file_get_contents($path);
            }
        }
    }
}
