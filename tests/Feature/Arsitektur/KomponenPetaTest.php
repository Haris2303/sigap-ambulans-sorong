<?php

namespace Tests\Feature\Arsitektur;

use App\Models\Faskes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class KomponenPetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pemilih_memuat_isian_koordinat_tombol_lokasi_dan_pencarian(): void
    {
        $html = Blade::render('<x-peta.pemilih />');

        $this->assertStringContainsString('data-peta="pemilih"', $html);
        $this->assertStringContainsString('name="lat_jemput"', $html);
        $this->assertStringContainsString('name="lng_jemput"', $html);
        $this->assertStringContainsString('data-input-alamat="f-lokasi_jemput"', $html);
        $this->assertStringContainsString('Gunakan lokasi saya', $html);
        $this->assertStringContainsString('data-peta-cari', $html);
        $this->assertStringContainsString('data-ubin="'.config('sigap.peta.ubin_url').'"', $html);
        $this->assertStringContainsString('isolate', $html);
    }

    public function test_pemilih_menerima_nama_kolom_dan_nilai_awal(): void
    {
        $html = Blade::render('<x-peta.pemilih lat="lat" lng="lng" alamat="alamat" nilai-lat="-0.8800000" nilai-lng="131.2500000" />');

        $this->assertStringContainsString('name="lat" value="-0.8800000"', $html);
        $this->assertStringContainsString('name="lng" value="131.2500000"', $html);
        $this->assertStringContainsString('data-input-alamat="f-alamat"', $html);
    }

    public function test_pelacak_membawa_titik_jemput_url_posisi_dan_interval(): void
    {
        $html = Blade::render('<x-peta.pelacak lat="-0.87" lng="131.25" url-posisi="https://contoh.test/status" />');

        $this->assertStringContainsString('data-peta="pelacak"', $html);
        $this->assertStringContainsString('data-lat="-0.87"', $html);
        $this->assertStringContainsString('data-url-posisi="https://contoh.test/status"', $html);
        $this->assertStringContainsString('data-detik="'.config('sigap.polling.warga_detik').'"', $html);
    }

    public function test_peta_faskes_hanya_memuat_faskes_berkoordinat(): void
    {
        $ada = Faskes::factory()->koordinat()->create(['nama' => "RS Contoh's"]);
        $tanpa = Faskes::factory()->create(['nama' => 'Tanpa Koordinat']);

        $html = Blade::render('<x-peta.faskes :daftar="$d" />', ['d' => collect([$ada, $tanpa])]);

        $this->assertStringContainsString('data-peta="faskes"', $html);
        $this->assertStringContainsString('Contoh', $html);
        $this->assertStringNotContainsString('Tanpa Koordinat', $html);
    }

    public function test_peta_faskes_kosong_menampilkan_keadaan_kosong(): void
    {
        $html = Blade::render('<x-peta.faskes :daftar="$d" />', ['d' => collect([Faskes::factory()->create()])]);

        $this->assertStringContainsString('Belum ada Faskes dengan titik koordinat.', $html);
        $this->assertStringNotContainsString('data-peta="faskes"', $html);
    }
}
