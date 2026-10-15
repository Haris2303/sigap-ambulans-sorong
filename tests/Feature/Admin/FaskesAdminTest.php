<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisFaskes;
use App\Enums\Kepemilikan;
use App\Models\Ambulans;
use App\Models\Faskes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

class FaskesAdminTest extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
    }

    private function admin()
    {
        return $this->actingAs($this->buatAdmin());
    }

    private function isian(array $ganti = []): array
    {
        return array_merge([
            'nama' => 'Puskesmas Baru Contoh',
            'jenis' => 'puskesmas',
            'kepemilikan' => 'pemerintah',
            'alamat' => 'Jalan Contoh 1',
            'telepon' => '0951-123456',
            'lat' => '-0.8700000',
            'lng' => '131.2500000',
            'aktif' => '1',
        ], $ganti);
    }

    public function test_daftar_faskes_menampilkan_jumlah_ambulans_koordinat_dan_status(): void
    {
        $f = Faskes::factory()->rs()->koordinat()->create(['nama' => 'RS Daftar']);
        Ambulans::factory()->count(2)->create(['faskes_id' => $f->id]);
        Faskes::factory()->nonaktif()->create(['nama' => 'Faskes Mati']);

        $this->admin()->get(route('admin.faskes.index'))
            ->assertOk()->assertSee('RS Daftar')->assertSee('Terisi')->assertSee('Belum diisi')
            ->assertSee('Nonaktif')->assertSee('Aktifkan')->assertSee('Nonaktifkan');
    }

    public function test_filter_jenis_dan_nama(): void
    {
        Faskes::factory()->rs()->create(['nama' => 'RS Alfa']);
        Faskes::factory()->puskesmas()->create(['nama' => 'Puskesmas Beta']);

        $this->admin()->get(route('admin.faskes.index', ['jenis' => 'rumah_sakit']))
            ->assertSee('RS Alfa')->assertDontSee('Puskesmas Beta');
        $this->get(route('admin.faskes.index', ['cari' => 'Beta']))
            ->assertSee('Puskesmas Beta')->assertDontSee('RS Alfa');
        $this->get(route('admin.faskes.index', ['cari' => 'tidak ada']))
            ->assertSee('Tidak ada Faskes yang cocok dengan filter.');
    }

    public function test_tambah_faskes(): void
    {
        $this->admin()->get(route('admin.faskes.create'))->assertOk()->assertSee('Titik koordinat')->assertSee('Gunakan lokasi saya');

        $this->post(route('admin.faskes.store'), $this->isian())
            ->assertRedirect(route('admin.faskes.index'))->assertSessionHas('sukses');

        $f = Faskes::where('nama', 'Puskesmas Baru Contoh')->firstOrFail();
        $this->assertSame(JenisFaskes::Puskesmas, $f->jenis);
        $this->assertSame(Kepemilikan::Pemerintah, $f->kepemilikan);
        $this->assertTrue($f->aktif);
        $this->assertEqualsWithDelta(-0.87, (float) $f->lat, 0.00001);
        $this->assertNull($f->ketersediaan_diperbarui_pada);
    }

    public function test_ubah_faskes_dan_nama_boleh_sama_dengan_dirinya_sendiri(): void
    {
        $f = Faskes::factory()->create(['nama' => 'Faskes Lama']);

        $this->admin()->get(route('admin.faskes.edit', $f))->assertOk()->assertSee('value="Faskes Lama"', false);

        $this->put(route('admin.faskes.update', $f), $this->isian(['nama' => 'Faskes Lama', 'alamat' => 'Jalan Baru']))
            ->assertRedirect(route('admin.faskes.index'));

        $this->assertSame('Jalan Baru', $f->fresh()->alamat);
    }

    public function test_validasi_faskes(): void
    {
        Faskes::factory()->create(['nama' => 'Sudah Ada']);

        $this->admin()->post(route('admin.faskes.store'), $this->isian(['nama' => 'Sudah Ada']))->assertSessionHasErrors('nama');
        $this->post(route('admin.faskes.store'), $this->isian(['jenis' => 'ngawur']))->assertSessionHasErrors('jenis');
        $this->post(route('admin.faskes.store'), $this->isian(['lat' => '95']))->assertSessionHasErrors('lat');
        $this->post(route('admin.faskes.store'), $this->isian(['lng' => '181']))->assertSessionHasErrors('lng');
        $this->post(route('admin.faskes.store'), $this->isian(['lat' => '-0.8', 'lng' => null]))->assertSessionHasErrors('lng');
        $this->post(route('admin.faskes.store'), $this->isian(['lat' => null, 'lng' => null, 'nama' => 'Tanpa Titik']))->assertSessionDoesntHaveErrors();
        $this->assertSame(2, Faskes::count());
    }

    public function test_aktif_membalik_status_dan_tidak_ada_penghapusan(): void
    {
        $f = Faskes::factory()->create();

        $this->admin()->put(route('admin.faskes.aktif', $f))->assertRedirect(route('admin.faskes.index'))->assertSessionHas('sukses');
        $this->assertFalse($f->fresh()->aktif);
        $this->put(route('admin.faskes.aktif', $f));
        $this->assertTrue($f->fresh()->aktif);

        $this->delete('/admin/faskes/'.$f->id)->assertStatus(405);
        $this->assertSame(1, Faskes::count());
    }

    public function test_hanya_dinkes_yang_boleh_mengelola_faskes(): void
    {
        $f = Faskes::factory()->create();

        foreach ([$this->buatWarga(), $this->buatPetugas($f), $this->buatSopir($f)] as $user) {
            $this->actingAs($user)->get(route('admin.faskes.index'))->assertForbidden();
            $this->post(route('admin.faskes.store'), $this->isian())->assertForbidden();
            $this->put(route('admin.faskes.aktif', $f))->assertForbidden();
            $this->put(route('admin.ketersediaan.update', $f), [])->assertForbidden();
        }
        $this->assertSame(1, Faskes::count());
        $this->assertTrue($f->fresh()->aktif);
    }

    // Ketersediaan

    private function ketersediaan(array $ganti = []): array
    {
        return array_merge([
            'bed_igd_total' => 10, 'bed_igd_tersedia' => 4,
            'bed_rawat_inap_total' => 30, 'bed_rawat_inap_tersedia' => 12,
            'dokter_umum_tersedia' => 3, 'dokter_spesialis_tersedia' => 2,
        ], $ganti);
    }

    public function test_halaman_ketersediaan_hanya_faskes_aktif_dengan_penanda_belum_diperbarui(): void
    {
        Faskes::factory()->create(['nama' => 'Faskes Aktif']);
        Faskes::factory()->nonaktif()->create(['nama' => 'Faskes Mati']);

        $this->admin()->get(route('admin.ketersediaan.index'))
            ->assertOk()->assertSee('Faskes Aktif')->assertDontSee('Faskes Mati')->assertSee('Belum diperbarui');
    }

    public function test_update_ketersediaan_mengisi_nilai_dan_stempel_waktu(): void
    {
        $f = Faskes::factory()->create();
        $this->assertNull($f->ketersediaan_diperbarui_pada);

        $this->admin()->put(route('admin.ketersediaan.update', $f), $this->ketersediaan())
            ->assertRedirect(route('admin.ketersediaan.index'))->assertSessionHas('sukses');

        $f->refresh();
        $this->assertSame(4, $f->bed_igd_tersedia);
        $this->assertSame(10, $f->bed_igd_total);
        $this->assertSame(12, $f->bed_rawat_inap_tersedia);
        $this->assertSame(2, $f->dokter_spesialis_tersedia);
        $this->assertNotNull($f->ketersediaan_diperbarui_pada);
        $this->assertTrue($f->ketersediaan_diperbarui_pada->diffInSeconds(now()) < 5);
    }

    public function test_ketersediaan_tersedia_tidak_boleh_melebihi_total(): void
    {
        $f = Faskes::factory()->create();

        $this->admin()->put(route('admin.ketersediaan.update', $f), $this->ketersediaan(['bed_igd_tersedia' => 11]))
            ->assertSessionHasErrors(['bed_igd_tersedia' => 'Bed IGD tersedia tidak boleh lebih dari total bed IGD.']);
        $this->put(route('admin.ketersediaan.update', $f), $this->ketersediaan(['bed_rawat_inap_tersedia' => 31]))
            ->assertSessionHasErrors('bed_rawat_inap_tersedia');
        $this->put(route('admin.ketersediaan.update', $f), $this->ketersediaan(['dokter_umum_tersedia' => -1]))
            ->assertSessionHasErrors('dokter_umum_tersedia');
        $this->put(route('admin.ketersediaan.update', $f), $this->ketersediaan(['dokter_umum_tersedia' => 'abc']))
            ->assertSessionHasErrors('dokter_umum_tersedia');

        $f->refresh();
        $this->assertSame(0, $f->bed_igd_total);
        $this->assertNull($f->ketersediaan_diperbarui_pada);
    }

    public function test_data_terbaru_tampil_di_halaman_info_faskes_publik(): void
    {
        $f = Faskes::factory()->create(['nama' => 'Faskes Publik']);
        $this->admin()->put(route('admin.ketersediaan.update', $f), $this->ketersediaan());

        auth()->logout();
        // Ketersediaan kini petak ring (docs/RETEMA_B.md 8.5 arah B, R8): aria-label cincin membawa angka, bukan lagi teks "4/10".
        $this->get(route('faskes.index'))->assertOk()->assertSee('Faskes Publik')
            ->assertSee('Bed IGD: 4 dari 10', false)->assertSee('Bed rawat inap: 12 dari 30', false)
            ->assertDontSee('Belum diperbarui');
    }
}
