<?php

namespace Tests\Feature\Arsitektur;

use Illuminate\View\ViewException;
use Tests\TestCase;

/**
 * Tes render komponen dasar dasbor Material (docs/DESAIN_DASBOR_MATERIAL.md bagian 5.2 sampai 5.6):
 * x-ui.icon-chip, x-ui.stat, x-ui.tile, x-ui.tabs, x-ui.tab, x-ui.avatar.
 * Panel, lencana pil, tombol on-primary, dan ikon ada di KomponenUiTest.
 */
class KomponenDasborTest extends TestCase
{
    private function html(string $blade, array $data = []): string
    {
        return (string) $this->blade($blade, $data);
    }

    // ---------- icon-chip ----------

    public function test_chip_ikon_menurut_nada_dengan_pasangan_container(): void
    {
        $peta = [
            'primary' => 'bg-primary-container text-on-primary-container',
            'warning' => 'bg-warning-container text-on-warning-container',
            'success' => 'bg-success-container text-on-success-container',
            'error' => 'bg-error-container text-on-error-container',
        ];

        foreach ($peta as $tone => $kelas) {
            $html = $this->html('<x-ui.icon-chip :tone="$t" icon="clock" />', ['t' => $tone]);

            $this->assertStringContainsString($kelas, $html, $tone);
            $this->assertStringContainsString('rounded-md', $html);
            $this->assertStringContainsString('shrink-0', $html);
            $this->assertStringContainsString('<svg', $html);
            $this->assertStringContainsString('aria-hidden="true"', $html);
        }
    }

    public function test_chip_ikon_ukuran_md_dan_lg(): void
    {
        $md = $this->html('<x-ui.icon-chip tone="primary" icon="user-check" />');
        $this->assertStringContainsString('size-10', $md);
        $this->assertStringContainsString('width="22"', $md);
        $this->assertStringContainsString('height="22"', $md);

        $lg = $this->html('<x-ui.icon-chip tone="success" icon="circle-check" size="lg" />');
        $this->assertStringContainsString('size-13', $lg);
        $this->assertStringContainsString('width="26"', $lg);
        $this->assertStringNotContainsString('size-10', $lg);
    }

    public function test_chip_ikon_menolak_nada_dan_ukuran_yang_tidak_dikenal(): void
    {
        foreach (['<x-ui.icon-chip tone="ungu" icon="clock" />', '<x-ui.icon-chip tone="primary" icon="clock" size="xl" />'] as $blade) {
            try {
                $this->html($blade);
                $this->fail('Harus ditolak: '.$blade);
            } catch (ViewException $e) {
                $this->assertStringContainsString('icon-chip', $e->getMessage());
            }
        }
    }

    // ---------- stat ----------

    public function test_stat_putih_menyusun_chip_angka_label_dan_keterangan(): void
    {
        $html = $this->html('<x-ui.stat tone="success" icon="circle-check" :value="12" label="Selesai hari ini" hint="pesanan tuntas" />');

        // Kartu putih berbayang dengan padding 20px (sudut 24px sejak Tampilan::KARTU arah B, R1/R2).
        foreach (['bg-surface-container-lowest', 'rounded-xl', 'shadow-card', 'p-5'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }
        $this->assertDoesNotMatchRegularExpression('/shadow-card-raised/', $html);

        // Chip ikon 52px dengan pasangan container nada.
        $this->assertStringContainsString('size-13', $html);
        $this->assertStringContainsString('bg-success-container text-on-success-container', $html);

        // Urutan: angka, label, keterangan.
        $this->assertStringContainsString('text-stat-l tabular-nums', $html);
        $this->assertStringContainsString('data-nilai', $html);
        $this->assertStringContainsString('text-label-l', $html);
        $this->assertStringContainsString('text-body-s text-on-surface-variant', $html);
        $angka = strpos($html, '>12<');
        $label = strpos($html, 'Selesai hari ini');
        $hint = strpos($html, 'pesanan tuntas');
        $this->assertNotFalse($angka);
        $this->assertLessThan($label, $angka);
        $this->assertLessThan($hint, $label);

        // Tanpa href: tidak ada tautan.
        $this->assertStringNotContainsString('<a ', $html);
    }

    public function test_stat_putih_tanpa_keterangan_dan_dengan_tautan_pada_label(): void
    {
        $tanpa = $this->html('<x-ui.stat tone="primary" icon="user-check" :value="1" label="Terverifikasi" />');
        $this->assertStringNotContainsString('text-body-s text-on-surface-variant', $tanpa);

        $html = $this->html('<x-ui.stat tone="primary" icon="user-check" :value="1" label="Terverifikasi" href="https://contoh.test/pesanan" />');
        $this->assertMatchesRegularExpression('~<a href="https://contoh.test/pesanan"[^>]*>\s*Terverifikasi\s*</a>~', $html);
        $this->assertSame(1, substr_count($html, '<a '), 'Hanya label yang menjadi tautan, bukan seluruh kartu.');
    }

    public function test_stat_utama_berlatar_primary_dengan_angka_besar_dan_aksi(): void
    {
        $html = $this->html(<<<'BLADE'
            <x-ui.stat :featured="true" icon="clock" :value="2" label="Menunggu verifikasi" hint="pesanan baru">
                <x-slot:aksi><x-ui.tombol nada="on-primary" href="https://contoh.test/perlu-tindakan">Verifikasi pesanan</x-ui.tombol></x-slot:aksi>
            </x-ui.stat>
            BLADE);

        foreach (['bg-primary', 'text-on-primary', 'rounded-lg', 'shadow-card-raised', 'px-6', 'py-5'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }
        $this->assertDoesNotMatchRegularExpression('/shadow-card(?!-raised)/', $html);
        $this->assertStringNotContainsString('bg-surface-container-lowest', $html);

        // Chip 44px berlatar on-primary 18%, judul title-m, angka stat-hero, keterangan primary-container.
        $this->assertStringContainsString('size-11', $html);
        $this->assertStringContainsString('bg-on-primary/18', $html);
        $this->assertStringContainsString('text-title-m', $html);
        $this->assertStringContainsString('text-stat-hero tabular-nums', $html);
        $this->assertStringContainsString('text-body-m text-primary-container', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);

        // Aksi di bawah: tombol on-primary.
        $this->assertStringContainsString('bg-on-primary text-primary', $html);
        $this->assertStringContainsString('Verifikasi pesanan', $html);
        $this->assertLessThan(strpos($html, 'Verifikasi pesanan'), strpos($html, '>2<'));
    }

    public function test_stat_meneruskan_atribut_ke_akar_dan_nilai_dapat_diperbarui(): void
    {
        $html = $this->html('<x-ui.stat data-ringkasan="pesanan_menunggu" tone="warning" icon="ambulance" :value="3" label="Sedang berjalan" />');

        $this->assertMatchesRegularExpression('/<div[^>]*data-ringkasan="pesanan_menunggu"/', $html);
        $this->assertMatchesRegularExpression('/data-nilai[^>]*>3</', $html);
        $this->assertStringContainsString('bg-warning-container text-on-warning-container', $html);
    }

    // ---------- tile ----------

    public function test_petak_tonal_tanpa_bayangan_dengan_chip_angka_dan_label(): void
    {
        $html = $this->html('<x-ui.tile tone="error" icon="bed-double" :value="1" label="Bed IGD tersedia" />');

        foreach (['bg-surface-container-low', 'rounded-md', 'p-4'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }
        $this->assertStringNotContainsString('shadow-', $html);
        $this->assertStringContainsString('size-10', $html);
        $this->assertStringContainsString('bg-error-container text-on-error-container', $html);
        $this->assertStringContainsString('text-stat-m tabular-nums', $html);
        $this->assertStringContainsString('data-nilai', $html);
        $this->assertStringContainsString('text-body-m text-on-surface-variant', $html);
        $this->assertLessThan(strpos($html, 'Bed IGD tersedia'), strpos($html, '>1<'));
    }

    // ---------- tabs ----------

    public function test_tab_variant_track_trek_abu_aktif_putih_dengan_shadow_chip(): void
    {
        $html = $this->html(<<<'BLADE'
            <x-ui.tabs label="Filter pesanan">
                <x-ui.tab href="https://contoh.test/p?tab=semua" :aktif="true">Semua</x-ui.tab>
                <x-ui.tab href="https://contoh.test/p?tab=perlu-tindakan">Perlu tindakan</x-ui.tab>
            </x-ui.tabs>
            BLADE);

        // Wadah: navigasi berlabel, bergulir horizontal, trek surface-container (DESIGN.md 6.5).
        $this->assertStringContainsString('<nav', $html);
        $this->assertStringContainsString('aria-label="Filter pesanan"', $html);
        foreach (['overflow-x-auto', 'rounded-md', 'bg-surface-container', 'p-1'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }

        // Aktif: putih dengan shadow-chip; tanda halaman hanya pada satu tab.
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertMatchesRegularExpression('~<a href="https://contoh.test/p\?tab=semua"[^>]*aria-current="page"[^>]*>~', $html);
        $this->assertMatchesRegularExpression('~<a href="https://contoh.test/p\?tab=semua"[^>]*class="[^"]*bg-surface-container-lowest text-on-surface shadow-chip~', $html);

        // Tidak aktif: on-surface-variant, tanpa latar.
        $this->assertMatchesRegularExpression('~<a href="https://contoh.test/p\?tab=perlu-tindakan"[^>]*class="[^"]*text-on-surface-variant~', $html);
        $this->assertStringContainsString('Perlu tindakan', $html);
    }

    public function test_tab_variant_floating_trek_melayang_aktif_primary(): void
    {
        $html = $this->html(<<<'BLADE'
            <x-ui.tabs label="Filter periode" variant="floating">
                <x-ui.tab href="https://contoh.test/p?periode=7" variant="floating" :aktif="true">7 hari</x-ui.tab>
                <x-ui.tab href="https://contoh.test/p?periode=30" variant="floating">30 hari</x-ui.tab>
            </x-ui.tabs>
            BLADE);

        foreach (['rounded-lg', 'bg-surface-container-lowest', 'shadow-control'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }
        $this->assertMatchesRegularExpression('~<a href="https://contoh.test/p\?periode=7"[^>]*class="[^"]*bg-primary text-on-primary~', $html);
        $this->assertStringContainsString('min-h-10', $html);
        $this->assertStringContainsString('lg:min-h-9.5', $html);
    }

    public function test_tab_memakai_ukuran_dan_lapisan_keadaan_kontrol_lain(): void
    {
        $html = $this->html('<x-ui.tab href="https://contoh.test/x">Berjalan</x-ui.tab>');

        foreach (['min-h-9', 'rounded-sm', 'px-4', 'text-label-m', 'shrink-0', 'whitespace-nowrap', 'lapisan-keadaan', 'target-sentuh'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }
        $this->assertStringNotContainsString('aria-current', $html);
        $this->assertStringNotContainsString('border-b-3', $html, 'Tab arah B tidak lagi bergaris bawah.');
        $this->assertStringNotContainsString('<button', $html, 'Tab adalah tautan, bukan tombol.');
    }

    // ---------- segmented ----------

    public function test_segmented_adalah_radiogroup_dan_segmen_tampilan_sama_dengan_tab_track(): void
    {
        $html = $this->html(<<<'BLADE'
            <x-ui.segmented label="Tingkat darurat">
                <x-ui.segmen nama="tingkat_darurat" nilai="biasa" :terpilih="true">Biasa</x-ui.segmen>
                <x-ui.segmen nama="tingkat_darurat" nilai="gawat">Gawat</x-ui.segmen>
            </x-ui.segmented>
            BLADE);

        $this->assertStringContainsString('role="radiogroup"', $html);
        $this->assertStringContainsString('aria-label="Tingkat darurat"', $html);
        foreach (['rounded-md', 'bg-surface-container', 'p-1'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }
        $this->assertStringContainsString('type="radio"', $html);
        $this->assertMatchesRegularExpression('/value="biasa" checked[^>]*class="sr-only"/', $html);
        $this->assertStringContainsString('bg-surface-container-lowest text-on-surface shadow-chip', $html);
        $this->assertStringContainsString('min-h-9', $html);
    }

    // ---------- avatar ----------

    public function test_avatar_berinisial_dua_kata_pertama_dan_ukuran(): void
    {
        // "Admin Dinkes" -> crc32 % 5 == 0 == primary (DESIGN.md 6.7).
        $html = $this->html('<x-ui.avatar nama="Admin Dinkes" />');
        foreach (['size-10', 'rounded-full', 'bg-primary-container', 'text-on-primary-container', 'text-chip', 'shrink-0'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }
        $this->assertStringContainsString('>AD<', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);

        $this->assertStringContainsString('>SN<', $this->html('<x-ui.avatar nama="Siti Nur Aini" />'));
        $this->assertStringContainsString('>B<', $this->html('<x-ui.avatar nama="budi" />'));
        $this->assertStringContainsString('>?<', $this->html('<x-ui.avatar nama="" />'));

        $kecil = $this->html('<x-ui.avatar nama="Admin Dinkes" size="sm" />');
        $this->assertStringContainsString('size-9', $kecil);
        $this->assertStringNotContainsString('size-10', $kecil);
    }

    public function test_avatar_warna_dari_hash_nama_lima_pasangan(): void
    {
        $pasangan = [
            'bg-primary-container text-on-primary-container', 'bg-error-container text-on-error-container',
            'bg-success-container text-on-success-container', 'bg-tertiary-container text-on-tertiary-container',
            'bg-warning-container text-on-warning-container',
        ];
        foreach (['Admin Dinkes', 'Siti Aminah', 'budi', 'Maria Kambu', 'Yosep Mayor', ''] as $nama) {
            $html = $this->html('<x-ui.avatar :nama="$n" />', ['n' => $nama]);
            $cocok = false;
            foreach ($pasangan as $kelas) {
                if (str_contains($html, $kelas)) {
                    $cocok = true;
                }
            }
            $this->assertTrue($cocok, 'Avatar "'.$nama.'" harus memakai salah satu dari lima pasangan.');
        }
    }

    // ---------- kolom cari ----------

    public function test_kolom_cari_adalah_form_get_berbentuk_pil_dengan_input_berlabel(): void
    {
        $html = $this->html('<x-ui.cari aksi="https://contoh.test/pesanan" label="Cari kode pesanan atau nama pasien" placeholder="Cari kode pesanan atau nama pasien" />');

        $this->assertMatchesRegularExpression('~<form method="GET" action="https://contoh.test/pesanan" role="search"~', $html);
        foreach (['h-12', 'rounded-full', 'bg-surface-container', 'min-w-0', 'flex-1', 'has-[:focus-visible]:outline-primary'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }
        $this->assertStringContainsString('<svg', $html);
        $this->assertMatchesRegularExpression('/<label for="([^"]+)" class="sr-only">Cari kode pesanan atau nama pasien<\/label>\s*<input id="\1"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="search"[^>]*name="q"/', $html);
        $this->assertStringContainsString('placeholder="Cari kode pesanan atau nama pasien"', $html);
        $this->assertStringContainsString('text-body-l', $html);
        $this->assertStringContainsString('outline-hidden', $html);
    }

    public function test_kolom_cari_mempertahankan_dan_menyaring_nilai_dan_menerima_nama_lain(): void
    {
        $html = $this->html('<x-ui.cari aksi="https://contoh.test/x" label="Cari" nama="kata" nilai="Budi <b>" />');

        $this->assertStringContainsString('name="kata"', $html);
        $this->assertStringContainsString('value="Budi &lt;b&gt;"', $html);
        $this->assertStringNotContainsString('<b>', $html);

        $tanpa = $this->html('<x-ui.cari aksi="https://contoh.test/x" label="Cari" />');
        $this->assertStringNotContainsString(' value=', $tanpa);
    }

    // ---------- tombol ikon dengan ukuran ikon ----------

    public function test_tombol_ikon_dapat_memakai_ukuran_ikon_baku_lain(): void
    {
        $html = $this->html('<x-ui.tombol nada="hantu" tipe="submit" ikon="log-out" ikon-ukuran="20" aria-label="Keluar" />');

        $this->assertStringContainsString('aspect-square px-0', $html);
        $this->assertStringContainsString('width="20"', $html);
        $this->assertStringContainsString('aria-label="Keluar"', $html);

        $this->expectException(ViewException::class);
        $this->html('<x-ui.tombol nada="hantu" ikon="log-out" ikon-ukuran="21" aria-label="Keluar" />');
    }
}
