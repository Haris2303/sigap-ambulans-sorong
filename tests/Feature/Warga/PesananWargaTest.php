<?php

namespace Tests\Feature\Warga;

use App\Enums\JenisLayanan;
use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PesananWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPengguna;
use Tests\TestCase;

class PesananWargaTest extends TestCase
{
    use MembuatPengguna, RefreshDatabase;

    private User $warga;

    protected function setUp(): void
    {
        parent::setUp();
        $this->warga = $this->buatWarga(['no_telepon' => '081234567890']);
    }

    private function isian(array $ganti = []): array
    {
        return array_merge([
            'jenis_layanan' => 'darurat',
            'tingkat_darurat' => 'gawat',
            'lokasi_jemput' => 'Jalan Basuki Rahmat Km 8',
            'lat_jemput' => '-0.8800000',
            'lng_jemput' => '131.2500000',
            'nama_pasien' => 'Budi Santoso',
            'no_telepon_kontak' => '081200001111',
            'catatan' => 'Sesak napas',
        ], $ganti);
    }

    // T4.1

    public function test_beranda_warga_menampilkan_ringkasan_dan_pesanan_aktif(): void
    {
        Pesanan::factory()->create(['user_id' => $this->warga->id, 'nama_pasien' => 'Aktif Satu']);
        Pesanan::factory()->berstatus(PesananStatus::Selesai)->create(['user_id' => $this->warga->id]);
        Pesanan::factory()->create();

        $this->actingAs($this->warga)->get(route('warga.beranda'))
            ->assertOk()
            ->assertSee('Pesanan aktif')
            ->assertSee('Dari 2 pesanan')
            ->assertSee('Menunggu verifikasi')
            ->assertSee('Pesan ambulans');
    }

    public function test_beranda_tanpa_pesanan_menampilkan_keadaan_kosong(): void
    {
        $this->actingAs($this->warga)->get(route('warga.beranda'))
            ->assertOk()->assertSee('Belum ada pesanan aktif.');
    }

    public function test_riwayat_hanya_milik_sendiri_dan_berpaginasi(): void
    {
        Pesanan::factory()->count(25)->create(['user_id' => $this->warga->id]);
        $lain = Pesanan::factory()->create();

        $halaman1 = $this->actingAs($this->warga)->get(route('warga.pesanan.index'))->assertOk();
        $halaman1->assertSee('Menampilkan 1–20 dari 25')->assertDontSee($lain->kode);

        $this->actingAs($this->warga)->get(route('warga.pesanan.index', ['page' => 2]))
            ->assertOk()->assertSee('Menampilkan 21–25 dari 25');
    }

    public function test_riwayat_kosong(): void
    {
        $this->actingAs($this->warga)->get(route('warga.pesanan.index'))->assertOk()->assertSee('Belum ada pesanan.');
    }

    public function test_halaman_warga_menolak_tamu_dan_peran_lain(): void
    {
        $this->get(route('warga.beranda'))->assertRedirect(route('login'));
        $this->actingAs($this->buatAdmin())->get(route('warga.beranda'))->assertForbidden();
        $this->actingAs($this->buatSopir())->get(route('warga.pesanan.index'))->assertForbidden();
    }

    // T4.2

    public function test_form_buat_pesanan_memuat_semua_bagian_dan_prefill_kontak(): void
    {
        Faskes::factory()->rs()->create(['nama' => 'RS Tujuan']);

        $this->actingAs($this->warga)->get(route('warga.pesanan.buat'))
            ->assertOk()
            ->assertSee('Jenis layanan')
            ->assertSee('Darurat')->assertSee('Rujukan')->assertSee('Persalinan')
            ->assertSee('Tingkat darurat')
            ->assertSee('Gunakan lokasi saya')
            ->assertSee('RS Tujuan (Rumah sakit)')
            ->assertSee('value="081234567890"', false)
            ->assertSee('Tinjau pesanan');
    }

    public function test_tinjau_belum_membuat_pesanan(): void
    {
        $this->actingAs($this->warga)->post(route('warga.pesanan.tinjau'), $this->isian())
            ->assertRedirect(route('warga.pesanan.konfirmasi'));

        $this->assertSame(0, Pesanan::count());
    }

    public function test_konfirmasi_menampilkan_ringkasan_dan_tombol_ubah_dan_kirim(): void
    {
        $this->actingAs($this->warga)->post(route('warga.pesanan.tinjau'), $this->isian());

        $this->get(route('warga.pesanan.konfirmasi'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Jalan Basuki Rahmat Km 8')
            ->assertSee('Gawat')
            ->assertSee('Kirim pesanan')
            ->assertSee('Ubah');
    }

    public function test_tombol_ubah_mempertahankan_isian(): void
    {
        $this->actingAs($this->warga)->post(route('warga.pesanan.tinjau'), $this->isian(['nama_pasien' => 'Siti Pasien']));

        $this->get(route('warga.pesanan.buat'))
            ->assertOk()
            ->assertSee('value="Siti Pasien"', false)
            ->assertSee('value="Jalan Basuki Rahmat Km 8"', false)
            ->assertSee('value="-0.8800000"', false)
            ->assertSee('value="081200001111"', false);
    }

    public function test_konfirmasi_tanpa_draf_dialihkan_ke_form(): void
    {
        $this->actingAs($this->warga)->get(route('warga.pesanan.konfirmasi'))
            ->assertRedirect(route('warga.pesanan.buat'))->assertSessionHas('galat');

        $this->actingAs($this->warga)->post(route('warga.pesanan.simpan'))
            ->assertRedirect(route('warga.pesanan.buat'));

        $this->assertSame(0, Pesanan::count());
    }

    public function test_simpan_membuat_pesanan_lewat_workflow_dan_membersihkan_draf(): void
    {
        $this->actingAs($this->warga)->post(route('warga.pesanan.tinjau'), $this->isian());

        $respons = $this->post(route('warga.pesanan.simpan'));

        $pesanan = Pesanan::firstOrFail();
        $respons->assertRedirect(route('warga.pesanan.show', $pesanan))->assertSessionHas('sukses');
        $this->assertSame(PesananStatus::Menunggu, $pesanan->status);
        $this->assertSame($this->warga->id, $pesanan->user_id);
        $this->assertMatchesRegularExpression('/^SGP-\d{6}$/', $pesanan->kode);
        $this->assertSame('Budi Santoso', $pesanan->nama_pasien);
        $this->assertCount(1, $pesanan->log);
        $this->assertSame(Role::Warga, $pesanan->log->first()->peran);

        $this->get(route('warga.pesanan.konfirmasi'))->assertRedirect(route('warga.pesanan.buat'));
    }

    public function test_tombol_kirim_pesanan_berlabel_proses_agar_tidak_terkirim_dua_kali(): void
    {
        $this->actingAs($this->warga)->post(route('warga.pesanan.tinjau'), $this->isian());

        $this->get(route('warga.pesanan.konfirmasi'))->assertOk()
            ->assertSee('data-memuat="Mengirim pesanan..."', false);
    }

    public function test_pesanan_ganda_tidak_terjadi_saat_simpan_diulang(): void
    {
        $this->actingAs($this->warga)->post(route('warga.pesanan.tinjau'), $this->isian());
        $this->post(route('warga.pesanan.simpan'));

        // Ketukan kedua sesudah pesanan terkirim menuju pesanan itu, tanpa galat "Isi data pesanan terlebih dahulu.".
        $pesanan = Pesanan::firstOrFail();
        $this->post(route('warga.pesanan.simpan'))
            ->assertRedirect(route('warga.pesanan.show', $pesanan))
            ->assertSessionMissing('galat');

        $this->assertSame(1, Pesanan::count());
    }

    public function test_dua_permintaan_dengan_draf_yang_sama_hanya_membuat_satu_pesanan(): void
    {
        $this->actingAs($this->warga);
        $draf = $this->isian();

        // Dua permintaan yang sama-sama membaca draf (draf belum terhapus dari salinan sesi masing-masing).
        $this->withSession(['draf_pesanan' => $draf])->post(route('warga.pesanan.simpan'));
        $pertama = Pesanan::firstOrFail();
        $this->withSession(['draf_pesanan' => $draf])->post(route('warga.pesanan.simpan'))
            ->assertRedirect(route('warga.pesanan.show', $pertama))
            ->assertSessionHas('sukses');

        $this->assertSame(1, Pesanan::count());
    }

    public function test_pesanan_berbeda_atau_pesanan_lama_yang_final_tidak_dianggap_ganda(): void
    {
        $this->actingAs($this->warga);

        $this->withSession(['draf_pesanan' => $this->isian()])->post(route('warga.pesanan.simpan'));
        // Pasien lain pada lokasi yang sama: pesanan baru yang sah.
        $this->withSession(['draf_pesanan' => $this->isian(['nama_pasien' => 'Siti Aminah'])])->post(route('warga.pesanan.simpan'));
        $this->assertSame(2, Pesanan::count());

        // Pesanan identik yang sudah dibatalkan tidak dipakai ulang; memesan lagi membuat pesanan baru.
        Pesanan::query()->update(['status' => PesananStatus::Dibatalkan->value]);
        $this->withSession(['draf_pesanan' => $this->isian()])->post(route('warga.pesanan.simpan'));
        $this->assertSame(3, Pesanan::count());
        $this->assertSame(1, Pesanan::where('status', PesananStatus::Menunggu->value)->count());
    }

    public function test_pesanan_identik_di_luar_jendela_waktu_tetap_dibuat_baru(): void
    {
        $this->actingAs($this->warga);

        $this->withSession(['draf_pesanan' => $this->isian()])->post(route('warga.pesanan.simpan'));
        Pesanan::query()->update(['created_at' => now()->subMinutes(10)]);
        $this->withSession(['draf_pesanan' => $this->isian()])->post(route('warga.pesanan.simpan'));

        $this->assertSame(2, Pesanan::count());
    }

    public function test_validasi_form_pesanan(): void
    {
        $this->actingAs($this->warga)->post(route('warga.pesanan.tinjau'), [
            'jenis_layanan' => 'ngawur', 'tingkat_darurat' => '', 'lokasi_jemput' => '',
            'nama_pasien' => '', 'no_telepon_kontak' => 'abc', 'lat_jemput' => '999', 'lng_jemput' => '131.2',
        ])->assertSessionHasErrors(['jenis_layanan', 'tingkat_darurat', 'lokasi_jemput', 'nama_pasien', 'no_telepon_kontak', 'lat_jemput']);

        $this->assertSame(0, Pesanan::count());
    }

    public function test_rujukan_wajib_faskes_tujuan_dan_faskes_nonaktif_ditolak(): void
    {
        $nonaktif = Faskes::factory()->nonaktif()->create();
        $aktif = Faskes::factory()->create();

        $this->actingAs($this->warga)->post(route('warga.pesanan.tinjau'), $this->isian(['jenis_layanan' => 'rujukan']))
            ->assertSessionHasErrors('faskes_tujuan_id');
        $this->post(route('warga.pesanan.tinjau'), $this->isian(['jenis_layanan' => 'rujukan', 'faskes_tujuan_id' => $nonaktif->id]))
            ->assertSessionHasErrors('faskes_tujuan_id');
        $this->post(route('warga.pesanan.tinjau'), $this->isian(['jenis_layanan' => 'rujukan', 'faskes_tujuan_id' => $aktif->id]))
            ->assertSessionDoesntHaveErrors();
    }

    public function test_koordinat_boleh_kosong_tetapi_harus_berpasangan(): void
    {
        $this->actingAs($this->warga)->post(route('warga.pesanan.tinjau'), $this->isian(['lat_jemput' => null, 'lng_jemput' => null]))
            ->assertSessionDoesntHaveErrors();
        $this->post(route('warga.pesanan.tinjau'), $this->isian(['lat_jemput' => '-0.8', 'lng_jemput' => null]))
            ->assertSessionHasErrors('lng_jemput');
    }

    public function test_pengguna_lain_tidak_bisa_membuat_pesanan(): void
    {
        $this->actingAs($this->buatAdmin())->post(route('warga.pesanan.tinjau'), $this->isian())->assertForbidden();
    }

    // T4.3

    private function pesananKu(PesananStatus $status = PesananStatus::Menunggu): Pesanan
    {
        return Pesanan::factory()->berstatus($status)->create(['user_id' => $this->warga->id]);
    }

    public function test_halaman_lacak_menampilkan_rincian_dan_linimasa(): void
    {
        $alur = new PesananWorkflow;
        $pesanan = $alur->buat($this->warga, $this->isian(['jenis_layanan' => JenisLayanan::Darurat, 'tingkat_darurat' => 'biasa']));

        $this->actingAs($this->warga)->get(route('warga.pesanan.show', $pesanan))
            ->assertOk()
            ->assertSee($pesanan->kode)
            ->assertSee('Budi Santoso')
            ->assertSee('Riwayat status')
            ->assertSee('Menunggu verifikasi')
            ->assertSee('Batalkan pesanan')
            ->assertDontSee('Ambulans dan sopir');
    }

    public function test_info_ambulans_dan_sopir_muncul_mulai_ditugaskan_dan_peta_mulai_diterima(): void
    {
        $faskes = Faskes::factory()->create(['nama' => 'Faskes Asal']);
        $sopir = User::factory()->sopir($faskes)->online()->create(['name' => 'Pak Sopir', 'no_telepon' => '081377778888']);
        $ambulans = Ambulans::factory()->create(['faskes_id' => $faskes->id, 'plat_nomor' => 'PB-1234-XX']);
        $admin = $this->buatAdmin();
        $alur = new PesananWorkflow;

        $pesanan = $alur->buat($this->warga, $this->isian());
        $alur->verifikasi($pesanan, $admin);
        $alur->tugaskan($pesanan, $admin, $ambulans, $sopir);

        $this->actingAs($this->warga)->get(route('warga.pesanan.show', $pesanan))
            ->assertOk()
            ->assertSee('Ambulans dan sopir')->assertSee('PB-1234-XX')->assertSee('Pak Sopir')->assertSee('Faskes Asal')
            ->assertDontSee('Posisi ambulans')->assertDontSee('Batalkan pesanan');

        $alur->terima($pesanan, $sopir);
        $this->get(route('warga.pesanan.show', $pesanan))
            ->assertOk()->assertSee('Posisi ambulans')->assertSee('data-peta="pelacak"', false);
    }

    public function test_warga_lain_tidak_bisa_melihat_pesanan(): void
    {
        $pesanan = $this->pesananKu();
        $lain = $this->buatWarga();

        $this->actingAs($lain)->get(route('warga.pesanan.show', $pesanan))->assertForbidden();
        $this->actingAs($lain)->getJson(route('warga.pesanan.status', $pesanan))
            ->assertForbidden()->assertJson(['ok' => false]);
        $this->actingAs($lain)->put(route('warga.pesanan.batalkan', $pesanan))->assertForbidden();
        $this->assertSame(PesananStatus::Menunggu, $pesanan->fresh()->status);
    }

    public function test_status_json_mengikuti_konvensi_dan_menyembunyikan_lokasi_sebelum_diterima(): void
    {
        $faskes = Faskes::factory()->create();
        $sopir = User::factory()->sopir($faskes)->online()->create(['lat' => -0.87, 'lng' => 131.25, 'lokasi_diperbarui_pada' => now()]);
        $ambulans = Ambulans::factory()->create(['faskes_id' => $faskes->id]);
        $admin = $this->buatAdmin();
        $alur = new PesananWorkflow;

        $pesanan = $alur->buat($this->warga, $this->isian());
        $this->actingAs($this->warga)->getJson(route('warga.pesanan.status', $pesanan))
            ->assertOk()->assertJsonPath('ok', true)->assertJsonPath('data.status', 'menunggu')
            ->assertJsonPath('data.label', 'Menunggu verifikasi')->assertJsonPath('data.sopir', null)
            ->assertJsonPath('data.final', false);

        $alur->verifikasi($pesanan, $admin);
        $alur->tugaskan($pesanan, $admin, $ambulans, $sopir);
        $this->getJson(route('warga.pesanan.status', $pesanan))
            ->assertJsonPath('data.sopir.nama', $sopir->name)->assertJsonPath('data.sopir.lat', null);

        $alur->terima($pesanan, $sopir);
        $this->getJson(route('warga.pesanan.status', $pesanan))
            ->assertJsonPath('data.status', 'diterima')->assertJsonPath('data.sopir.lat', -0.87)
            ->assertJsonPath('data.ambulans.plat_nomor', $ambulans->plat_nomor);
    }

    public function test_status_json_tamu_mendapat_401_dan_pesanan_tidak_ada_404(): void
    {
        $pesanan = $this->pesananKu();

        $this->getJson(route('warga.pesanan.status', $pesanan))->assertUnauthorized()->assertJson(['ok' => false]);
        $this->actingAs($this->warga)->getJson('/warga/pesanan/99999/status')->assertNotFound()->assertJson(['ok' => false]);
    }

    public function test_warga_membatalkan_pesanan_menunggu_dan_terverifikasi(): void
    {
        foreach ([PesananStatus::Menunggu, PesananStatus::Terverifikasi] as $status) {
            $pesanan = $this->pesananKu($status);

            $this->actingAs($this->warga)->put(route('warga.pesanan.batalkan', $pesanan), ['alasan' => 'Salah pesan'])
                ->assertRedirect(route('warga.pesanan.show', $pesanan))->assertSessionHas('sukses');

            $this->assertSame(PesananStatus::Dibatalkan, $pesanan->fresh()->status);
            $this->assertSame('Salah pesan', $pesanan->fresh()->alasan_pembatalan);
        }
    }

    public function test_warga_tidak_bisa_membatalkan_setelah_ditugaskan(): void
    {
        $pesanan = $this->pesananKu(PesananStatus::Ditugaskan);

        $this->actingAs($this->warga)->put(route('warga.pesanan.batalkan', $pesanan))->assertForbidden();
        $this->assertSame(PesananStatus::Ditugaskan, $pesanan->fresh()->status);
    }

    public function test_pesanan_final_menampilkan_alasan_dan_tanpa_tombol_batal(): void
    {
        $pesanan = $this->pesananKu(PesananStatus::Ditolak);
        $pesanan->forceFill(['alasan_penolakan' => 'Data lokasi tidak jelas'])->save();

        $this->actingAs($this->warga)->get(route('warga.pesanan.show', $pesanan))
            ->assertOk()->assertSee('Data lokasi tidak jelas')->assertDontSee('Batalkan pesanan');
    }
}
