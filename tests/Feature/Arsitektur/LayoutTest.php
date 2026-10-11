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

    public function test_layout_publik_untuk_tamu_dan_pengguna_masuk(): void
    {
        $tamu = Blade::render('<x-layouts.publik judul="Beranda">isi</x-layouts.publik>');
        $this->assertStringContainsString('Masuk', $tamu);
        $this->assertStringContainsString('Info Faskes', $tamu);
        $this->assertStringContainsString('Dinas Kesehatan Kota Sorong', $tamu);
        $this->assertStringNotContainsString('Ke dasbor', $tamu);
        // Bilah atas publik: kartu melayang (token top-app-bar) di atas latar canvas.
        foreach (['h-16', 'bg-surface-container-lowest rounded-xl shadow-card', 'text-title-l', 'border-outline-variant', 'text-body-s text-on-surface-variant'] as $kelas) {
            $this->assertStringContainsString($kelas, $tamu);
        }
        $this->assertStringContainsString('px-4 lg:px-7', $tamu);
        // Tombol "Masuk" (nada sekunder): bg-surface-container dengan batas outline-variant (DESIGN.md 6.2).
        $this->assertStringContainsString('bg-surface-container text-on-surface border border-outline-variant', $tamu);

        $this->actingAs($this->buatAdmin());
        $masuk = Blade::render('<x-layouts.publik judul="Beranda">isi</x-layouts.publik>');
        $this->assertStringContainsString('Ke dasbor', $masuk);
    }

    public function test_layout_auth_kolom_tunggal_dengan_flash(): void
    {
        session()->flash('galat', 'Email atau kata sandi salah.');
        $html = Blade::render('<x-layouts.auth judul="Masuk">formulir</x-layouts.auth>');

        $this->assertStringContainsString('max-w-sm', $html);
        $this->assertStringContainsString('text-title-l', $html);
        $this->assertStringContainsString('Email atau kata sandi salah.', $html);
        $this->assertStringContainsString('formulir', $html);
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

    public function test_bilah_atas_adalah_kartu_melayang_dengan_pencarian_lonceng_dan_avatar(): void
    {
        $this->actingAs($this->buatAdmin(['name' => 'Admin Dinkes']));
        $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');

        // Kartu putih 64px berbayang card, sudut 24px (Tampilan::KARTU arah B), di atas latar canvas; melayang.
        $bilah = $this->tag($html, 'data-bilah-atas');
        foreach (['h-16', 'rounded-xl', 'bg-surface-container-lowest', 'shadow-card'] as $kelas) {
            $this->assertStringContainsString($kelas, $bilah);
        }
        $this->assertStringContainsString('sticky top-0', $html);
        $this->assertStringContainsString('bg-canvas', $html);

        // Tombol menu hanya di bawah lg.
        $menu = $this->tag($html, 'aria-label="Buka menu"');
        $this->assertStringContainsString('lg:hidden', $menu);
        $this->assertStringContainsString('data-menu-buka', $menu);

        // Pencarian: form GET ke daftar pesanan dengan ?q=, pil 48px surface-container, input type search berlabel.
        $form = $this->tag($html, 'role="search"');
        $this->assertStringContainsString('method="GET"', $form);
        $this->assertStringContainsString('action="'.route('admin.pesanan.index').'"', $form);
        foreach (['h-12', 'rounded-full', 'bg-surface-container', 'min-w-0', 'flex-1', 'lg:max-w-[460px]'] as $kelas) {
            $this->assertStringContainsString($kelas, $form);
        }
        $this->assertMatchesRegularExpression('/<input[^>]*type="search"[^>]*name="q"|<input[^>]*name="q"[^>]*type="search"/', $html);
        $this->assertMatchesRegularExpression('/<label[^>]*class="sr-only"[^>]*>Cari kode pesanan atau nama pasien<\/label>/', $html);

        // Avatar dan, di desktop, nama dengan peran dua baris.
        $this->assertStringContainsString('>AD<', $html);
        $this->assertMatchesRegularExpression('/<div class="hidden[^"]*lg:block[^"]*">\s*<p class="[^"]*text-label-l[^"]*">Admin Dinkes<\/p>\s*<p class="[^"]*text-body-s text-on-surface-variant[^"]*">Dinkes<\/p>/', $html);
    }

    public function test_lonceng_menghitung_pesanan_menunggu_verifikasi(): void
    {
        $this->actingAs($this->buatAdmin());

        // Tanpa pesanan menunggu: label menyebut 0 dan lencana tersembunyi.
        $kosong = Blade::render('<x-layouts.app judul="Dasbor">isi</x-layouts.app>');
        $lonceng = $this->tag($kosong, 'data-lonceng');
        $this->assertStringContainsString('aria-label="Pesanan menunggu verifikasi, 0"', $lonceng);
        $this->assertStringContainsString('href="'.route('admin.pesanan.index', ['tab' => 'perlu-tindakan']).'"', $lonceng);
        $this->assertStringContainsString('lapisan-keadaan', $lonceng);
        $this->assertStringContainsString('<svg', $kosong);

        // Dengan pesanan menunggu: label dan lencana error 18px menyebut jumlahnya.
        Pesanan::factory()->count(3)->create();
        $html = Blade::render('<x-layouts.app judul="Dasbor">isi</x-layouts.app>');
        $this->assertStringContainsString('aria-label="Pesanan menunggu verifikasi, 3"', $this->tag($html, 'data-lonceng'));
        $this->assertMatchesRegularExpression('/<span data-lencana="pesanan_menunggu"[^>]*h-4\.5 min-w-4\.5[^>]*bg-error[^>]*>3<\/span>/', $html);
        $this->assertSame(2, substr_count($html, 'data-lencana="pesanan_menunggu"'), 'Satu di menu Pesanan dan satu di lonceng.');
    }

    public function test_peran_selain_dinkes_tanpa_lonceng_dan_pencarian_tetapi_dengan_avatar(): void
    {
        foreach ([$this->buatWarga(['name' => 'Siti Aminah']), $this->buatPetugas(), $this->buatSopir()] as $pengguna) {
            $this->actingAs($pengguna);
            $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');

            $this->assertStringNotContainsString('data-lonceng', $html, $pengguna->role->value);
            $this->assertStringNotContainsString('role="search"', $html, $pengguna->role->value);
            $this->assertStringNotContainsString('Pesanan menunggu verifikasi', $html, $pengguna->role->value);
            $this->assertStringContainsString('aria-label="Buka menu"', $html);
            // Warna avatar dari hash nama (DESIGN.md 6.7), bukan selalu primary-container.
            $this->assertStringContainsString('rounded-full text-chip', $html, 'Avatar ada untuk '.$pengguna->role->value);
            $this->assertStringContainsString(\App\Support\Tampilan::warnaAvatar($pengguna->name), $html, 'Avatar ada untuk '.$pengguna->role->value);
        }
    }

    public function test_laci_navigasi_putih_dengan_kepala_label_grup_dan_item_pil_48px(): void
    {
        $this->actingAs($this->buatAdmin());
        $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');

        // Laci: putih 280px (lg), tepi kanan 1px surface-container-high, modal di ponsel dengan scrim; satu-satunya wadah gulir menu.
        $laci = $this->tag($html, 'data-menu-rel');
        foreach (['bg-surface-container-lowest', 'lg:w-drawer', 'lg:border-r', 'lg:border-surface-container-high', 'lg:sticky', 'lg:top-0', 'lg:h-screen',
            'overflow-y-auto', 'gulir-tipis', 'rounded-r-lg', 'shadow-level-1'] as $kelas) {
            $this->assertStringContainsString($kelas, $laci);
        }
        $this->assertStringNotContainsString('bg-surface-container-low ', $laci.' ');
        $this->assertStringContainsString('data-menu-scrim', $html);
        $this->assertStringContainsString('bg-scrim', $html);
        $this->assertStringContainsString('aria-label="Tutup menu"', $html);

        // Menu tidak punya bilah gulir sendiri (hanya satu bilah vertikal per halaman).
        $this->assertStringNotContainsString('overflow-y-auto', $this->tag($html, 'aria-label="Menu utama"'));

        // Kepala: tanda merek 40px (primary, sudut 12px, ikon plus) dan nama aplikasi title-l.
        $this->assertMatchesRegularExpression('/<span[^>]*size-10[^>]*rounded-md[^>]*bg-primary[^>]*>\s*<svg/', $html);
        $this->assertMatchesRegularExpression('/<a [^>]*class="[^"]*text-title-l[^"]*">SIGAP Ambulans<\/a>/', $html);

        // Label grup label-m on-surface-variant, urut sesuai pengelompokan.
        $this->assertMatchesRegularExpression('/<p class="[^"]*text-label-m text-on-surface-variant[^"]*">Operasional<\/p>/', $html);
        $this->assertMatchesRegularExpression('/<p class="[^"]*text-label-m text-on-surface-variant[^"]*">Data master<\/p>/', $html);
        $this->assertLessThan(strpos($html, '>Dasbor<'), strpos($html, '>Operasional<'));
        $this->assertLessThan(strpos($html, '>Faskes<'), strpos($html, '>Data master<'));
        $this->assertLessThan(strpos($html, '>Data master<'), strpos($html, '>Laporan<'));
        $this->assertSame(1, substr_count($html, '>Operasional<'));

        // Item 48px berbentuk pil dengan lapisan keadaan, ikon 22px, teks label-l.
        foreach (['lapisan-keadaan', 'min-h-12', 'rounded-full', 'px-4', 'text-label-l', 'width="22"'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }
        $this->assertStringNotContainsString('min-h-14', $html);
    }

    public function test_item_aktif_berlatar_primary_dengan_bayangan_nav_active(): void
    {
        Route::get('/_uji/dasbor', fn () => Blade::render('<x-layouts.app judul="Dasbor">isi</x-layouts.app>'))
            ->middleware('web')->name('admin.dashboard');
        Route::getRoutes()->refreshNameLookups();

        $html = $this->actingAs($this->buatAdmin())->get('/_uji/dasbor')->assertOk()->getContent();

        $aktif = $this->tag($html, 'aria-current="page"');
        $this->assertStringContainsString('bg-primary text-on-primary shadow-nav-active', $aktif);
        $this->assertStringNotContainsString('bg-secondary-container', $aktif);
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
    }

    public function test_blok_pengguna_di_dasar_laci_dengan_keluar_sebagai_form_post(): void
    {
        $this->actingAs($this->buatAdmin(['name' => 'Admin Dinkes']));
        $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');

        // Blok tonal sudut 16px berisi avatar, nama, dan peran.
        $this->assertMatchesRegularExpression('/<div class="[^"]*bg-surface-container-low[^"]*rounded-lg[^"]*">\s*<span aria-hidden="true"[^>]*>AD<\/span>/', $html);
        $this->assertStringContainsString('Admin Dinkes', $html);

        // Keluar: form POST ber-CSRF ke route logout dengan tombol ikon 20px; bukan tautan.
        $this->assertMatchesRegularExpression('/<form method="POST" action="'.preg_quote(route('logout'), '/').'"[^>]*>\s*<input type="hidden" name="_token"/', $html);
        $keluar = $this->tag($html, 'aria-label="Keluar"');
        $this->assertStringStartsWith('<button', $keluar);
        $this->assertStringContainsString('type="submit"', $keluar);
        $this->assertDoesNotMatchRegularExpression('/<a [^>]*href="[^"]*logout[^"]*"/', $html);
        $this->assertMatchesRegularExpression('/aria-label="Keluar"[^>]*>\s*<svg[^>]*width="20"/', $html);
    }

    public function test_isi_halaman_memakai_jarak_dasbor_dan_latar_canvas(): void
    {
        $this->actingAs($this->buatAdmin());
        $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');

        // Padding halaman 16px di ponsel dan 28px di desktop; kolom isi tidak boleh melebihi viewport (min-w-0).
        $this->assertMatchesRegularExpression('/<main id="isi" class="[^"]*min-w-0[^"]*px-4 lg:px-7[^"]*"/', $html);
        $this->assertStringContainsString('max-w-content', $html);
        $this->assertStringContainsString('min-w-0 flex-1', $html);

        // Latar halaman semua peran adalah canvas (app-background di DESIGN.md).
        $css = (string) file_get_contents(base_path('resources/css/app.css'));
        $this->assertStringContainsString('@apply bg-canvas', $css);
        $this->assertSame($css, (string) file_get_contents(base_path('agent/templates/app.css')), 'Templat app.css harus identik.');
    }

    public function test_lencana_pesanan_menunggu_hanya_untuk_dinkes(): void
    {
        Pesanan::factory()->count(3)->create();
        $this->actingAs($this->buatAdmin());

        $html = Blade::render('<x-layouts.app judul="Dasbor">isi</x-layouts.app>');

        $this->assertMatchesRegularExpression('/data-lencana="pesanan_menunggu"[^>]*>3</', $html);
        $this->assertMatchesRegularExpression('/<span data-lencana="pesanan_menunggu"[^>]*h-5 min-w-5[^>]*bg-error[^>]*text-label-m[^>]*>3</', $html);
        $this->assertDoesNotMatchRegularExpression('/data-lencana="pesanan_menunggu"[^>]*\shidden[\s>]/', $html);
    }

    public function test_lencana_menu_disembunyikan_saat_kosong(): void
    {
        $this->actingAs($this->buatAdmin());

        $html = Blade::render('<x-layouts.app judul="Dasbor">isi</x-layouts.app>');

        $this->assertMatchesRegularExpression('/data-lencana="pesanan_menunggu"[^>]*\shidden[\s>]/', $html);
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
