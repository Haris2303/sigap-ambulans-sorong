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
    }

    public function test_layout_publik_untuk_tamu_dan_pengguna_masuk(): void
    {
        $tamu = Blade::render('<x-layouts.publik judul="Beranda">isi</x-layouts.publik>');
        $this->assertStringContainsString('Masuk', $tamu);
        $this->assertStringContainsString('Info Faskes', $tamu);
        $this->assertStringContainsString('Dinas Kesehatan Kota Sorong', $tamu);
        $this->assertStringNotContainsString('Ke dasbor', $tamu);

        $this->actingAs($this->buatAdmin());
        $masuk = Blade::render('<x-layouts.publik judul="Beranda">isi</x-layouts.publik>');
        $this->assertStringContainsString('Ke dasbor', $masuk);
    }

    public function test_layout_auth_kolom_tunggal_dengan_flash(): void
    {
        session()->flash('galat', 'Email atau kata sandi salah.');
        $html = Blade::render('<x-layouts.auth judul="Masuk">formulir</x-layouts.auth>');

        $this->assertStringContainsString('max-w-sm', $html);
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

    public function test_menu_aktif_diberi_penanda_halaman_saat_ini(): void
    {
        Route::get('/_uji/warga', fn () => Blade::render('<x-layouts.app judul="Beranda">isi</x-layouts.app>'))
            ->middleware('web')->name('warga.beranda');
        Route::getRoutes()->refreshNameLookups();

        $this->actingAs($this->buatWarga())->get('/_uji/warga')
            ->assertOk()
            ->assertSee('aria-current="page"', false)
            ->assertSee('bg-primary-soft text-primary', false);
    }

    public function test_lencana_pesanan_menunggu_hanya_untuk_dinkes(): void
    {
        Pesanan::factory()->count(3)->create();
        $this->actingAs($this->buatAdmin());

        $html = Blade::render('<x-layouts.app judul="Dasbor">isi</x-layouts.app>');

        $this->assertMatchesRegularExpression('/data-lencana="pesanan_menunggu"[^>]*>3</', $html);
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
