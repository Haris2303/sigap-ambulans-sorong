<?php

namespace Tests\Feature\Publik;

use App\Enums\JenisFaskes;
use App\Models\Ambulans;
use App\Models\Faskes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

class BerandaFaskesTest extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara(['warga.pesanan.buat']);
    }

    public function test_beranda_tamu_menampilkan_judul_langkah_dan_tombol(): void
    {
        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Pesan ambulans Dinas Kesehatan Kota Sorong.')
            ->assertSee('Cara kerja')
            ->assertSee('<ol', false)
            ->assertSee('Dinkes memverifikasi pesanan.')
            ->assertSee('Info Faskes')
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_beranda_warga_menuju_form_pesanan_dan_peran_lain_ke_dasbor(): void
    {
        $this->actingAs($this->buatWarga())->get(route('beranda'))
            ->assertOk()->assertSee('href="'.route('warga.pesanan.buat').'"', false)->assertSee('Pesan ambulans');

        $this->actingAs($this->buatAdmin())->get(route('beranda'))
            ->assertOk()->assertSee('Ke dasbor');
    }

    public function test_halaman_faskes_publik_tanpa_login(): void
    {
        $segar = Faskes::factory()->rs()->diperbarui(5, 10)->create(['nama' => 'RS Segar', 'alamat' => 'Jalan Merdeka']);
        $baru = Faskes::factory()->create(['nama' => 'Puskesmas Baru']);
        $lama = Faskes::factory()->diperbarui()->create(['nama' => 'Puskesmas Lama', 'ketersediaan_diperbarui_pada' => now()->subHours(config('sigap.data_basi_jam') + 1)]);
        Faskes::factory()->nonaktif()->create(['nama' => 'Faskes Nonaktif']);

        $this->get(route('faskes.index'))
            ->assertOk()
            ->assertSee('RS Segar')
            ->assertSee('Jalan Merdeka')
            ->assertSee('5/7')
            ->assertSee('Puskesmas Baru')
            ->assertSee('Belum diperbarui')
            ->assertSee('Puskesmas Lama')
            ->assertSee('Data lama')
            ->assertDontSee('Faskes Nonaktif');

        $this->assertNotNull($segar->id.$baru->id.$lama->id);
    }

    public function test_filter_jenis_membatasi_daftar(): void
    {
        Faskes::factory()->rs()->create(['nama' => 'RS Satu']);
        Faskes::factory()->puskesmas()->create(['nama' => 'Puskesmas Satu']);

        $this->get(route('faskes.index', ['jenis' => JenisFaskes::Rs->value]))
            ->assertOk()->assertSee('RS Satu')->assertDontSee('Puskesmas Satu')->assertSee('Atur ulang');

        $this->get(route('faskes.index', ['jenis' => 'ngawur']))
            ->assertOk()->assertSee('RS Satu')->assertSee('Puskesmas Satu');
    }

    public function test_keadaan_kosong_saat_belum_ada_faskes(): void
    {
        $this->get(route('faskes.index'))->assertOk()->assertSee('Belum ada Faskes untuk jenis ini.');
    }

    public function test_peta_faskes_hanya_bila_ada_koordinat(): void
    {
        Faskes::factory()->create(['nama' => 'Tanpa Titik']);
        $this->get(route('faskes.index'))->assertSee('Belum ada Faskes dengan titik koordinat.');

        Faskes::factory()->koordinat()->create(['nama' => 'Dengan Titik']);
        $this->get(route('faskes.index'))->assertSee('data-peta="faskes"', false);
    }

    public function test_faskes_data_json_mengikuti_konvensi(): void
    {
        $faskes = Faskes::factory()->rs()->diperbarui(5, 10)->koordinat()->create(['nama' => 'RS Json']);
        Ambulans::factory()->count(2)->create(['faskes_id' => $faskes->id]);
        Ambulans::factory()->servis()->create(['faskes_id' => $faskes->id]);
        Faskes::factory()->create(['nama' => 'Belum Pernah']);
        Faskes::factory()->nonaktif()->create();

        $respons = $this->getJson(route('faskes.data'))->assertOk()->assertJsonPath('ok', true);

        $daftar = collect($respons->json('data.faskes'));
        $this->assertCount(2, $daftar);
        $rs = $daftar->firstWhere('nama', 'RS Json');
        $this->assertSame('rumah_sakit', $rs['jenis']);
        $this->assertSame('Rumah Sakit', $rs['jenis_label']);
        $this->assertSame(5, $rs['bed_igd_tersedia']);
        $this->assertSame(2, $rs['ambulans_tersedia']);
        $this->assertFalse($rs['belum_diperbarui']);
        $this->assertFalse($rs['data_lama']);
        $this->assertEqualsWithDelta(-0.8762, $rs['lat'], 0.0001);
        $this->assertTrue($daftar->firstWhere('nama', 'Belum Pernah')['belum_diperbarui']);
    }

    public function test_faskes_data_json_menerima_filter_jenis(): void
    {
        Faskes::factory()->rs()->create(['nama' => 'RS Dua']);
        Faskes::factory()->puskesmas()->create(['nama' => 'Puskesmas Dua']);

        $nama = collect($this->getJson(route('faskes.data', ['jenis' => 'puskesmas']))->json('data.faskes'))->pluck('nama')->all();

        $this->assertSame(['Puskesmas Dua'], $nama);
    }
}
