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

    public function test_katalog_ui_memuat_pengungkap_bilah_aksi_dan_navigasi(): void
    {
        $this->get('/_ui')
            ->assertOk()
            ->assertSee('Pengungkap dan bilah aksi')
            ->assertSee('data-bilah-aksi', false)
            ->assertSee('<details', false)
            ->assertSee('Buka navigasi')
            ->assertSee('geo:-0.8762,131.2558', false)
            ->assertSee('geo:0,0?q=Jalan%20Basuki%20Rahmat%20Km%208', false);
    }

    public function test_katalog_ui_memuat_komponen_dasar_dasbor(): void
    {
        $this->get('/_ui')
            ->assertOk()
            ->assertSee('Kartu statistik')
            ->assertSee('shadow-card-raised', false)
            ->assertSee('text-stat-hero', false)
            ->assertSee('bg-on-primary text-primary', false)
            ->assertSee('size-13', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('>AD<', false)
            ->assertSee('Bed IGD tersedia');
    }

    public function test_katalog_ui_memuat_grafik_dengan_data_pratinjau(): void
    {
        $this->get('/_ui')
            ->assertOk()
            ->assertSee('Pesanan 7 hari terakhir')
            ->assertSee('data-grafik=', false)
            ->assertSee('&quot;tipe&quot;:&quot;batang&quot;', false)
            ->assertSee('&quot;tipe&quot;:&quot;donat&quot;', false)
            ->assertSee('Diagram cincin status 10 pesanan terbaru: 5 selesai, 2 dibatalkan, 1 ditolak, 1 terverifikasi, 1 dalam perjalanan', false)
            ->assertSee('Dalam perjalanan')
            ->assertSee('bg-chart-sky', false)
            ->assertSee('tidak ada data', false);
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
