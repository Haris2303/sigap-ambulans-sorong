<?php

namespace Tests\Feature\Arsitektur;

use App\Enums\Role;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MemindaiNamaLama;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use MembuatPengguna, MemindaiNamaLama, MenyediakanRuteSementara, RefreshDatabase;

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
        foreach (['h-16', 'bg-surface-container', 'text-title-l', 'border-outline-variant', 'text-body-s text-on-surface-variant'] as $kelas) {
            $this->assertStringContainsString($kelas, $tamu);
        }
        $this->assertStringContainsString('border border-outline text-primary', $tamu);

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

    public function test_layout_app_laci_navigasi_dan_bilah_atas_material_3(): void
    {
        $this->actingAs($this->buatAdmin());
        $html = Blade::render('<x-layouts.app judul="Uji">isi</x-layouts.app>');

        // Bilah atas di bawah lg: 64px, tombol menu, judul halaman.
        foreach (['data-bilah-atas', 'h-16', 'bg-surface-container', 'lg:hidden', 'aria-label="Buka menu"', 'text-title-l'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }

        // Laci: modal di ponsel, tetap selebar token drawer di lg ke atas, dengan scrim.
        foreach (['data-menu-rel', 'bg-surface-container-low', 'lg:w-drawer', 'rounded-r-lg', 'shadow-level-1', 'aria-label="Tutup menu"',
            'data-menu-scrim', 'bg-scrim'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }

        // Item menu: pil 56px dengan lapisan keadaan dan ikon 24px.
        foreach (['lapisan-keadaan', 'min-h-14', 'rounded-full', 'px-4', 'text-label-l', 'width="24"'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }

        // Rincian pengguna di dasar laci.
        $this->assertStringContainsString('border-outline-variant', $html);
        $this->assertStringContainsString('text-body-s text-on-surface-variant', $html);
    }

    public function test_menu_aktif_diberi_penanda_halaman_saat_ini(): void
    {
        Route::get('/_uji/warga', fn () => Blade::render('<x-layouts.app judul="Beranda">isi</x-layouts.app>'))
            ->middleware('web')->name('warga.beranda');
        Route::getRoutes()->refreshNameLookups();

        $this->actingAs($this->buatWarga())->get('/_uji/warga')
            ->assertOk()
            ->assertSee('aria-current="page"', false)
            ->assertSee('bg-secondary-container text-on-secondary-container', false);
    }

    public function test_lencana_pesanan_menunggu_hanya_untuk_dinkes(): void
    {
        Pesanan::factory()->count(3)->create();
        $this->actingAs($this->buatAdmin());

        $html = Blade::render('<x-layouts.app judul="Dasbor">isi</x-layouts.app>');

        $this->assertMatchesRegularExpression('/data-lencana="pesanan_menunggu"[^>]*>3</', $html);
        $this->assertMatchesRegularExpression('/<span data-lencana="pesanan_menunggu"[^>]*bg-error[^>]*text-label-s[^>]*>3</', $html);
        $this->assertDoesNotMatchRegularExpression('/data-lencana="pesanan_menunggu"[^>]*\shidden[\s>]/', $html);
    }

    public function test_lencana_menu_disembunyikan_saat_kosong(): void
    {
        $this->actingAs($this->buatAdmin());

        $html = Blade::render('<x-layouts.app judul="Dasbor">isi</x-layouts.app>');

        $this->assertMatchesRegularExpression('/data-lencana="pesanan_menunggu"[^>]*\shidden[\s>]/', $html);
    }

    public function test_layout_m3_tidak_memuat_nama_token_lama(): void
    {
        $temuan = $this->cariNamaLama(array_map(
            fn (string $nama) => 'resources/views/components/layouts/'.$nama.'.blade.php',
            ['dasar', 'app', 'publik', 'auth'],
        ));

        $this->assertSame([], $temuan, 'Nama lama masih ada: '.implode('; ', $temuan));
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
