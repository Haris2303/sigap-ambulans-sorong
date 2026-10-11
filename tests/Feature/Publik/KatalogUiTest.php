<?php

namespace Tests\Feature\Publik;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

class KatalogUiTest extends TestCase
{
    use MenyediakanRuteSementara, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
    }

    public function test_katalog_ui_menampilkan_semua_komponen(): void
    {
        $this->get('/_ui')
            ->assertOk()
            ->assertSee('Katalog gaya')
            ->assertSee('Menunggu verifikasi')
            ->assertSee('Belum diperbarui')
            ->assertSee('Data lama')
            ->assertSee('Belum ada pesanan.')
            ->assertSee('Nomor kontak wajib diisi.')
            ->assertSee('Menampilkan 21–40 dari 134')
            ->assertSee('Gunakan lokasi saya')
            ->assertSee('Belum ada Faskes dengan titik koordinat.')
            ->assertSee('data-konfirmasi', false);
    }

    #[DataProvider('kodeGalat')]
    public function test_halaman_galat_memakai_layout(int $kode, string $judul): void
    {
        Route::get('/_uji/galat/'.$kode, fn () => abort($kode))->middleware('web');

        $this->get('/_uji/galat/'.$kode)
            ->assertStatus($kode)
            ->assertSee($judul)
            ->assertSee('SIGAP Ambulans')
            ->assertSee('Ke beranda');
    }

    public static function kodeGalat(): array
    {
        return [
            [403, 'Akses ditolak'],
            [404, 'Halaman tidak ditemukan'],
            [419, 'Sesi berakhir'],
            [500, 'Terjadi kesalahan'],
        ];
    }

    public function test_route_tidak_dikenal_menghasilkan_404_berlayout(): void
    {
        $this->get('/tidak-ada-halaman-ini')->assertNotFound()->assertSee('Halaman tidak ditemukan');
    }
}
