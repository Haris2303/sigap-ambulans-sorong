<?php

namespace Tests\Feature\Arsitektur;

use App\Enums\Role;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

/** Kerangka halaman arah B (docs/RETEMA_B.md bagian 5): bilah samping/laci (components.layouts.menu-app, dipakai dua kali
 *  oleh x-layouts.app), bilah atas ponsel, dan kepala halaman (x-ui.judul-halaman, pencarian dan lonceng desktop). */
class LayoutTest extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
    }

    public function test_layout_dasar_memuat_kerangka_dokumen_dan_dialog(): void
    {
        $html = Blade::render('<x-layouts.dasar judul="Uji">isi halaman</x-layouts.dasar>');

        $this->assertStringContainsString('<html lang="id">', $html);
        $this->assertStringContainsString('<title>Uji - SIGAP Ambulans</title>', $html);
        $this->assertStringContainsString('Lewati ke isi', $html);
        $this->assertStringContainsString('id="dialog-konfirmasi"', $html);
        $this->assertStringContainsString('isi halaman', $html);
        $this->assertStringContainsString('bg-primary', $html);
        $this->assertStringContainsString('focus:not-sr-only', $html);
        $this->assertStringContainsString('text-label-l', $html);
    }

    public function test_tautan_lompat_adalah_elemen_pertama_di_body(): void
    {
        // docs/RETEMA_B.md 5: "Lewati ke isi" di awal tiap layout (app, publik, auth) lewat x-layouts.dasar bersama.
        foreach ([
            '<x-layouts.app judul="Uji">isi</x-layouts.app>',
            '<x-layouts.auth judul="Masuk">isi</x-layouts.auth>',
            '<x-layouts.publik judul="Beranda">isi</x-layouts.publik>',
        ] as $markup) {
            if (str_contains($markup, 'x-layouts.app')) {
                $this->actingAs($this->buatAdmin());
            }
            $html = Blade::render($markup);
            $setelahBody = substr($html, strpos($html, '<body>') + strlen('<body>'));
            $this->assertMatchesRegularExpression('/^\s*<a href="#isi"/', $setelahBody, $markup);
        }
    }

    public function test_layout_publik_untuk_tamu_dan_pengguna_masuk(): void
    {
        $tamu = Blade::render('<x-layouts.publik judul="Beranda">isi</x-layouts.publik>');
        $this->assertStringContainsString('Masuk', $tamu);
        $this->assertStringContainsString('Info Faskes', $tamu);
        $this->assertStringContainsString('Dinas Kesehatan Kota Sorong', $tamu);
        $this->assertStringNotContainsString('Ke dasbor', $tamu);
        // Bilah atas publik (docs/RETEMA_B.md 5.3): kartu melayang 60px di atas latar surface.
        foreach (['h-15', 'bg-surface-container-lowest rounded-xl shadow-card', 'text-title-l', 'border-outline-variant', 'text-body-s text-on-surface-variant'] as $kelas) {
            $this->assertStringContainsString($kelas, $tamu);
        }
        $this->assertStringContainsString('px-4 lg:px-7', $tamu);
        // Tombol "Masuk" (nada sekunder): bg-surface-container dengan batas outline-variant (DESIGN.md 6.2).
        $this->assertStringContainsString('bg-surface-container text-on-surface border border-outline-variant', $tamu);

        $this->actingAs($this->buatAdmin());
        $masuk = Blade::render('<x-layouts.publik judul="Beranda">isi</x-layouts.publik>');
        $this->assertStringContainsString('Ke dasbor', $masuk);
    }

    public function test_layout_auth_satu_kartu_putih_dengan_tanda_merek_dan_flash(): void
    {
        session()->flash('galat', 'Email atau kata sandi salah.');
        $html = Blade::render('<x-layouts.auth judul="Masuk">formulir</x-layouts.auth>');

        $this->assertStringContainsString('max-w-md', $html);
        // Kartu putih arah B: rounded-2xl, bg-surface-container-lowest, shadow-card (bukan x-ui.panel biasa, radius dan padding berbeda).
        foreach (['rounded-2xl', 'bg-surface-container-lowest', 'shadow-card'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }
        $this->assertStringContainsString('text-brand', $html);
        $this->assertStringContainsString('Email atau kata sandi salah.', $html);
        $this->assertStringContainsString('formulir', $html);
        // Tanda merek (docs/RETEMA_B.md 5.3) di dalam kartu, sejajar "SIGAP", bukan sekadar tautan teks.
        $this->assertMatchesRegularExpression('/<span class="inline-flex size-10\.5 shrink-0 items-center justify-center rounded-md bg-primary text-on-primary">\s*<svg/', $html);
    }

    public function test_layout_app_menu_sesuai_peran(): void
    {
        $kasus = [
            [$this->buatWarga(), ['Beranda', 'Pesan ambulans', 'Riwayat pesanan'], ['Laporan']],
            [$this->buatAdmin(), ['Dasbor', 'Pesanan', 'Laporan', 'Petugas Faskes'], ['Pesan ambulans']],
            [$this->buatPetugas(), ['Dasbor', 'Ketersediaan'], ['Laporan']],
            [$this->buatSopir(), ['Penugasan'], ['Laporan', 'Ketersediaan']],
        ];

        foreach ($kasus as [$pengguna, $ada, $tidakAda]) {
            $this->actingAs($pengguna);
            $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');

            foreach ($ada as $label) {
                $this->assertStringContainsString('>'.$label.'<', $html, $pengguna->role->value.' harus punya menu '.$label);
            }
            foreach ($tidakAda as $label) {
                $this->assertStringNotContainsString('>'.$label.'<', $html, $pengguna->role->value.' tidak boleh punya menu '.$label);
            }
            $this->assertStringContainsString($pengguna->name, $html);
            $this->assertStringContainsString($pengguna->role->label(), $html);
            $this->assertStringContainsString('Keluar', $html);
            $this->assertStringContainsString('name="_token"', $html);
        }
    }

    /** Isi tag pembuka pertama yang memuat $penanda. */
    private function tag(string $html, string $penanda): string
    {
        $this->assertSame(1, preg_match('/<[a-z]+\b[^>]*'.preg_quote($penanda, '/').'[^>]*>/', $html, $cocok), 'Tag tidak ditemukan: '.$penanda);

        return $cocok[0];
    }

    /** Potongan HTML dari tag pembuka $mulai (mis. "<aside") sampai padanan tag penutup $akhir (mis. "</aside>") pertama setelahnya. */
    private function potong(string $html, string $mulai, string $akhir): string
    {
        $awal = strpos($html, $mulai);
        $this->assertNotFalse($awal, 'Tag awal tidak ditemukan: '.$mulai);
        $ujung = strpos($html, $akhir, $awal);
        $this->assertNotFalse($ujung, 'Tag akhir tidak ditemukan: '.$akhir);

        return substr($html, $awal, $ujung + strlen($akhir) - $awal);
    }

    // ---------- bilah atas ponsel dan laci (docs/RETEMA_B.md 5.2) ----------

    public function test_bilah_atas_ponsel_kartu_melayang_dengan_menu_lonceng_dan_avatar(): void
    {
        $this->actingAs($this->buatAdmin(['name' => 'Admin Dinkes']));
        $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');

        $bilah = $this->potong($html, '<div class="sticky top-4 z-20 mb-4', '</div>');
        foreach (['rounded-xl', 'bg-surface-container-lowest', 'shadow-card', 'lg:hidden'] as $kelas) {
            $this->assertStringContainsString($kelas, $bilah);
        }

        // Tombol menu 48px, hanya di bawah lg (bilah seluruhnya lg:hidden).
        $this->assertStringContainsString('aria-label="Buka menu"', $bilah);
        $this->assertStringContainsString('data-menu-buka', $bilah);
        $this->assertStringContainsString('size-12', $bilah);

        // Lonceng (Dinkes): titik error, bukan lencana angka; avatar di ujung.
        $this->assertStringContainsString('data-lonceng="pesanan_menunggu"', $bilah);
        $this->assertStringContainsString('aria-label="Pesanan menunggu verifikasi, 0"', $bilah);
        $this->assertStringContainsString('data-sembunyi-nol="pesanan_menunggu"', $bilah);
        $this->assertStringContainsString('>AD<', $bilah);

        // Tidak ada pencarian di bilah atas ponsel (docs/RETEMA_B.md 5.2 tidak menyebutnya; ada di kepala halaman lg ke atas).
        $this->assertStringNotContainsString('data-cari-buka', $bilah);
    }

    public function test_peran_selain_dinkes_tanpa_lonceng_tetapi_dengan_avatar(): void
    {
        foreach ([$this->buatWarga(['name' => 'Siti Aminah']), $this->buatPetugas(), $this->buatSopir()] as $pengguna) {
            $this->actingAs($pengguna);
            $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');

            $this->assertStringNotContainsString('data-lonceng', $html, $pengguna->role->value);
            $this->assertStringNotContainsString('Pesanan menunggu verifikasi', $html, $pengguna->role->value);
            $this->assertStringContainsString('aria-label="Buka menu"', $html);
            // Warna avatar dari hash nama (DESIGN.md 6.7), bukan selalu primary-container.
            $this->assertStringContainsString('rounded-full text-chip', $html, 'Avatar ada untuk '.$pengguna->role->value);
            $this->assertStringContainsString(\App\Support\Tampilan::warnaAvatar($pengguna->name), $html, 'Avatar ada untuk '.$pengguna->role->value);
        }
    }

    public function test_laci_adalah_dialog_316px_melayang_dengan_tombol_tutup(): void
    {
        $this->actingAs($this->buatAdmin());
        $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');

        // <dialog> native (docs/RETEMA_B.md 5.2): fokus terjebak, Esc, dan backdrop bawaan peramban (sigap.js hanya showModal()/close()).
        $laci = $this->potong($html, '<dialog data-laci', '</dialog>');
        $this->assertStringStartsWith('<dialog', $laci);
        foreach (['w-79', 'rounded-2xl', 'shadow-overlay', 'backdrop:bg-scrim/45', 'lg:hidden', 'bg-surface-container-lowest'] as $kelas) {
            $this->assertStringContainsString($kelas, $laci);
        }
        $this->assertStringContainsString('aria-label="Tutup menu"', $laci);
        $this->assertStringContainsString('data-menu-tutup', $laci);
    }

    public function test_bilah_samping_desktop_248px_sticky_dan_satu_bilah_gulir(): void
    {
        $this->actingAs($this->buatAdmin());
        $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');

        $aside = $this->potong($html, '<aside', '</aside>');
        foreach (['w-62', 'bg-surface-container-lowest', 'rounded-2xl', 'shadow-card', 'sticky top-4', 'h-[calc(100vh-2rem)]', 'lg:flex', 'hidden'] as $kelas) {
            $this->assertStringContainsString($kelas, $aside);
        }
        // Merek dan blok pengguna tetap (bukan area gulir); hanya daftar menu yang bergulir, supaya pada tinggi
        // layar pendek bilah gulir berpanah tidak muncul dan blok pengguna selalu terlihat (docs/RETEMA_B.md "DB" 1).
        $this->assertStringNotContainsString('overflow-y-auto', $this->potong($aside, '<aside', '<nav'));
        $this->assertStringContainsString('min-h-0 flex-1', $this->tag($aside, 'aria-label="Menu utama"'));
        $this->assertStringContainsString('overflow-y-auto gulir-tipis', $this->tag($aside, 'aria-label="Menu utama"'));
        // Bilah samping (tempat tetap) tidak punya tombol tutup, berbeda dari laci.
        $this->assertStringNotContainsString('data-menu-tutup', $aside);
    }

    // ---------- isi bersama bilah samping/laci (components/layouts/menu-app.blade.php) ----------

    public function test_kepala_merek_label_grup_dan_item_pil_48px(): void
    {
        $this->actingAs($this->buatAdmin());
        $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');
        $aside = $this->potong($html, '<aside', '</aside>');

        // Merek: tanda 42px (primary, sudut md, ikon plus) dan "SIGAP" gaya brand.
        $this->assertMatchesRegularExpression('/<span class="inline-flex size-10\.5 shrink-0 items-center justify-center rounded-md bg-primary text-on-primary">\s*<svg/', $aside);
        $this->assertMatchesRegularExpression('/<span class="min-w-0 truncate text-brand">SIGAP<\/span>/', $aside);

        // Label grup label-m on-surface-variant, urut sesuai pengelompokan.
        $this->assertMatchesRegularExpression('/<p class="[^"]*text-label-m text-on-surface-variant[^"]*">Operasional<\/p>/', $aside);
        $this->assertMatchesRegularExpression('/<p class="[^"]*text-label-m text-on-surface-variant[^"]*">Data master<\/p>/', $aside);
        $this->assertLessThan(strpos($aside, '>Dasbor<'), strpos($aside, '>Operasional<'));
        $this->assertLessThan(strpos($aside, '>Faskes<'), strpos($aside, '>Data master<'));
        $this->assertLessThan(strpos($aside, '>Data master<'), strpos($aside, '>Laporan<'));
        $this->assertSame(1, substr_count($aside, '>Operasional<'));

        // Item 48px, ikon 21px, teks label-l.
        foreach (['lapisan-keadaan', 'min-h-12', 'rounded-md', 'px-3.5', 'text-label-l', 'width="21"'] as $kelas) {
            $this->assertStringContainsString($kelas, $aside);
        }
    }

    public function test_item_aktif_berlatar_primary_container_tanpa_bayangan(): void
    {
        Route::get('/_uji/dasbor', fn () => Blade::render('<x-layouts.app judul="Dasbor">isi</x-layouts.app>'))
            ->middleware('web')->name('admin.dashboard');
        Route::getRoutes()->refreshNameLookups();

        $html = $this->actingAs($this->buatAdmin())->get('/_uji/dasbor')->assertOk()->getContent();

        // Item aktif tampil di aside DAN di laci (keduanya merender menu yang sama): bg-primary-container, tanpa bayangan
        // (docs/RETEMA_B.md 4.6: item menu aktif tanpa bayangan, berbeda dari kontrak lama shadow-nav-active).
        $this->assertSame(2, substr_count($html, 'aria-current="page"'));
        $aktif = $this->tag($html, 'aria-current="page"');
        $this->assertStringContainsString('bg-primary-container text-primary', $aktif);
        $this->assertStringNotContainsString('shadow-nav-active', $aktif);
        $this->assertStringNotContainsString('bg-primary text-on-primary', $aktif);
    }

    public function test_lencana_hitungan_pesanan_min_22px_dengan_teks_sr_only(): void
    {
        Pesanan::factory()->count(3)->create();
        $this->actingAs($this->buatAdmin());

        $html = Blade::render('<x-layouts.app judul="Dasbor">isi</x-layouts.app>');

        // Lencana error/on-error, rounded-full, min 22px, gaya chip (docs/RETEMA_B.md 5.1); teks sr-only " menunggu verifikasi" di sampingnya.
        $this->assertMatchesRegularExpression('/<span data-lencana="pesanan_menunggu"[^>]*h-5\.5 min-w-5\.5[^>]*bg-error[^>]*text-chip[^>]*>3<\/span><span class="sr-only"> menunggu verifikasi<\/span>/', $html);
        // Muncul dua kali (aside dan laci merender menu yang sama).
        $this->assertSame(2, substr_count($html, 'data-lencana="pesanan_menunggu"'));
        $this->assertDoesNotMatchRegularExpression('/data-lencana="pesanan_menunggu"[^>]*\shidden[\s>]/', $html);
    }

    public function test_lencana_menu_disembunyikan_saat_kosong(): void
    {
        $this->actingAs($this->buatAdmin());

        $html = Blade::render('<x-layouts.app judul="Dasbor">isi</x-layouts.app>');

        $this->assertMatchesRegularExpression('/data-lencana="pesanan_menunggu"[^>]*\shidden[\s>]/', $html);
    }

    public function test_blok_pengguna_tonal_di_dasar_dengan_keluar_sebagai_form_post(): void
    {
        $this->actingAs($this->buatAdmin(['name' => 'Admin Dinkes']));
        $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');
        $aside = $this->potong($html, '<aside', '</aside>');

        // Blok tonal sudut lg (bg-surface, berbeda dari latar kartu putih bilah samping) berisi avatar, nama, dan peran.
        $this->assertMatchesRegularExpression('/<div class="flex items-center gap-3 rounded-lg bg-surface p-3">\s*<span aria-hidden="true"[^>]*>AD<\/span>/', $aside);
        $this->assertStringContainsString('Admin Dinkes', $aside);

        // Keluar: form POST ber-CSRF ke route logout dengan tombol ikon 20px; bukan tautan.
        $this->assertMatchesRegularExpression('/<form method="POST" action="'.preg_quote(route('logout'), '/').'"[^>]*>\s*<input type="hidden" name="_token"/', $aside);
        $keluar = $this->tag($aside, 'aria-label="Keluar"');
        $this->assertStringStartsWith('<button', $keluar);
        $this->assertStringContainsString('type="submit"', $keluar);
        $this->assertDoesNotMatchRegularExpression('/<a [^>]*href="[^"]*logout[^"]*"/', $aside);
        $this->assertMatchesRegularExpression('/aria-label="Keluar"[^>]*>\s*<svg[^>]*width="20"/', $aside);
    }

    // ---------- kolom isi dan kepala halaman (x-ui.judul-halaman, docs/RETEMA_B.md 5.1) ----------

    public function test_kolom_isi_memakai_padding_dan_lebar_maksimum_arah_b(): void
    {
        $this->actingAs($this->buatAdmin());
        $html = Blade::render('<x-layouts.app judul="Uji">isi unik</x-layouts.app>');

        $this->assertMatchesRegularExpression('/<main id="isi" class="[^"]*min-w-0[^"]*lg:max-w-screen-2xl[^"]*lg:pb-6 lg:pr-2 lg:pt-1\.5[^"]*"/', $html);
        $this->assertStringContainsString('isi unik', $html);

        // Akar halaman: latar surface, padding 16px, jarak 20px antara bilah samping dan kolom isi (docs/RETEMA_B.md 5.1).
        $this->assertStringContainsString('bg-surface p-4', $html);
        $this->assertStringContainsString('lg:gap-5', $html);
    }

    public function test_kepala_halaman_judul_subjudul_dan_aksi(): void
    {
        $this->actingAs($this->buatWarga());
        $html = Blade::render('<x-ui.judul-halaman judul="Riwayat pesanan" deskripsi="Semua pesanan Anda." />');

        $header = $this->potong($html, '<header', '</header>');
        // Baris di lg ke atas; bertumpuk di bawah lg supaya judul tidak terjepit tombol aksi (docs/RETEMA_B.md 5.1, 5.2).
        $this->assertStringContainsString('flex flex-col items-start gap-3.5 lg:flex-row lg:items-center', $header);
        $this->assertStringContainsString('<h1 class="text-page-title max-lg:text-page-title-s">Riwayat pesanan</h1>', $html);
        $this->assertMatchesRegularExpression('/<p class="mt-1 text-body-m text-on-surface-variant">Semua pesanan Anda\.<\/p>/', $html);
    }

    public function test_pencarian_dan_lonceng_desktop_hanya_untuk_peran_yang_memakainya(): void
    {
        $this->actingAs($this->buatAdmin(['name' => 'Admin Dinkes']));
        $html = Blade::render('<x-ui.judul-halaman judul="Dasbor" />');

        // Tombol cari: dialog native "Cari pesanan", GET ke daftar pesanan dengan ?q=, hanya tampil di lg ke atas.
        $tombolCari = $this->tag($html, 'data-cari-buka');
        $this->assertStringContainsString('aria-label="Cari pesanan"', $tombolCari);
        foreach (['hidden', 'lg:inline-flex', 'size-12', 'bg-surface-container-lowest', 'rounded-lg', 'shadow-control'] as $kelas) {
            $this->assertStringContainsString($kelas, $tombolCari);
        }

        $dialog = $this->tag($html, 'id="dialog-cari-pesanan"');
        $this->assertStringStartsWith('<dialog', $dialog);
        $this->assertStringContainsString('role="search"', $html);
        $this->assertStringContainsString('method="GET"', $html);
        $this->assertStringContainsString('action="'.route('admin.pesanan.index').'"', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="search"[^>]*name="q"|<input[^>]*name="q"[^>]*type="search"/', $html);
        $this->assertMatchesRegularExpression('/<label[^>]*class="sr-only"[^>]*>Cari kode pesanan atau nama pasien<\/label>/', $html);

        // Lonceng: titik 10px tepi putih 2px, tersembunyi bila 0.
        $lonceng = $this->tag($html, 'data-lonceng');
        $this->assertStringContainsString('aria-label="Pesanan menunggu verifikasi, 0"', $lonceng);
        $this->assertStringContainsString('href="'.route('admin.pesanan.index', ['tab' => 'perlu-tindakan']).'"', $lonceng);
        $this->assertMatchesRegularExpression('/<span data-sembunyi-nol="pesanan_menunggu"[^>]*hidden[^>]*size-2\.5 rounded-full bg-error ring-2 ring-surface-container-lowest/', $html);

        // Peran lain (mis. Masyarakat) tidak punya pencarian/lonceng di kepala halaman.
        $this->actingAs($this->buatWarga());
        $tanpaPencarian = Blade::render('<x-ui.judul-halaman judul="Beranda" />');
        $this->assertStringNotContainsString('data-cari-buka', $tanpaPencarian);
        $this->assertStringNotContainsString('data-lonceng', $tanpaPencarian);
    }

    public function test_lonceng_desktop_menghitung_pesanan_menunggu_verifikasi(): void
    {
        $this->actingAs($this->buatAdmin());
        Pesanan::factory()->count(3)->create();

        $html = Blade::render('<x-ui.judul-halaman judul="Dasbor" />');

        $this->assertStringContainsString('aria-label="Pesanan menunggu verifikasi, 3"', $this->tag($html, 'data-lonceng'));
        $this->assertDoesNotMatchRegularExpression('/data-sembunyi-nol="pesanan_menunggu"[^>]*\shidden[\s>]/', $html);
    }

    public function test_layout_menerima_peta_dan_menampilkan_flash_di_atas_isi(): void
    {
        $this->actingAs($this->buatWarga());
        session()->flash('sukses', 'Pesanan berhasil dikirim.');

        $html = Blade::render('<x-layouts.app judul="Uji" :peta="true">isi unik</x-layouts.app>');

        $this->assertLessThan(strpos($html, 'isi unik'), strpos($html, 'Pesanan berhasil dikirim.'));
        $this->assertSame(Role::Warga, auth()->user()->role);
    }
}
