<?php

namespace Tests\Feature\Petugas;

use App\Enums\PesananStatus;
use App\Enums\StatusAmbulans;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

class PetugasTest extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    private Faskes $faskes;

    private Faskes $lain;

    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
        $this->faskes = Faskes::factory()->create(['nama' => 'Faskes Saya']);
        $this->lain = Faskes::factory()->create(['nama' => 'Faskes Lain']);
        $this->petugas = $this->buatPetugas($this->faskes, ['name' => 'Petugas Saya']);
    }

    private function ketersediaan(array $ganti = []): array
    {
        return array_merge([
            'bed_igd_total' => 8, 'bed_igd_tersedia' => 3,
            'bed_rawat_inap_total' => 20, 'bed_rawat_inap_tersedia' => 7,
            'dokter_umum_tersedia' => 2, 'dokter_spesialis_tersedia' => 1,
            'dokter_spesialis_keterangan' => 'Bedah umum',
        ], $ganti);
    }

    // Dasbor

    public function test_dasbor_menampilkan_identitas_ketersediaan_ambulans_dan_sopir(): void
    {
        Ambulans::factory()->create(['faskes_id' => $this->faskes->id, 'plat_nomor' => 'PB-SAYA-1']);
        Ambulans::factory()->create(['faskes_id' => $this->lain->id, 'plat_nomor' => 'PB-LAIN-1']);
        User::factory()->sopir($this->faskes)->online()->create(['name' => 'Sopir Saya']);
        User::factory()->sopir($this->lain)->create(['name' => 'Sopir Lain']);

        $this->actingAs($this->petugas)->get(route('petugas.dashboard'))
            ->assertOk()->assertSee('Faskes Saya')->assertSee('Belum diperbarui')
            ->assertSee('PB-SAYA-1')->assertDontSee('PB-LAIN-1')
            ->assertSee('Sopir Saya')->assertDontSee('Sopir Lain')
            ->assertSee('Perbarui ketersediaan');
    }

    public function test_dasbor_memuat_penugasan_asal_dan_tujuan_faskes_ini_saja(): void
    {
        $ambulansSaya = Ambulans::factory()->create(['faskes_id' => $this->faskes->id]);
        $ambulansLain = Ambulans::factory()->create(['faskes_id' => $this->lain->id]);
        $asal = Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create(['ambulans_id' => $ambulansSaya->id, 'nama_pasien' => 'Pasien Asal']);
        $tujuan = Pesanan::factory()->create(['faskes_tujuan_id' => $this->faskes->id, 'nama_pasien' => 'Pasien Tujuan']);
        $lain = Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create(['ambulans_id' => $ambulansLain->id, 'nama_pasien' => 'Pasien Lain']);
        $selesai = Pesanan::factory()->berstatus(PesananStatus::Selesai)->create(['ambulans_id' => $ambulansSaya->id, 'nama_pasien' => 'Pasien Selesai']);

        $this->actingAs($this->petugas)->get(route('petugas.dashboard'))
            ->assertOk()->assertSee('Pasien Asal')->assertSee('Pasien Tujuan')
            ->assertDontSee('Pasien Lain')->assertDontSee('Pasien Selesai')
            ->assertSee($asal->kode)->assertSee($tujuan->kode);
        $this->assertNotNull($lain->id.$selesai->id);
    }

    public function test_dasbor_menampilkan_lencana_gawat_di_baris_pesanan(): void
    {
        // Dasbor Faskes memakai pesanan/_baris (P4): lencana Gawat tampil di baris pesanan itu, bukan di baris lain.
        $ambulans = Ambulans::factory()->create(['faskes_id' => $this->faskes->id]);
        $gawat = Pesanan::factory()->gawat()->berstatus(PesananStatus::Ditugaskan)->create(['ambulans_id' => $ambulans->id]);
        $biasa = Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create(['ambulans_id' => $ambulans->id]);

        $html = $this->actingAs($this->petugas)->get(route('petugas.dashboard'))->assertOk()->getContent();

        $baris = fn (string $kode) => preg_match(
            '/<tr[^>]*>((?:(?!<\/tr>).)*?)'.preg_quote($kode, '/').'((?:(?!<\/tr>).)*?)<\/tr>/s',
            $html,
            $m
        ) ? $m[0] : null;

        $this->assertNotNull($baris($gawat->kode), 'Baris pesanan Gawat ditemukan.');
        $this->assertStringContainsString('Gawat', $baris($gawat->kode));

        $this->assertNotNull($baris($biasa->kode), 'Baris pesanan biasa ditemukan.');
        $this->assertStringNotContainsString('Gawat', $baris($biasa->kode));
    }

    public function test_dasbor_tanpa_pesanan_menampilkan_keadaan_kosong(): void
    {
        $this->actingAs($this->petugas)->get(route('petugas.dashboard'))
            ->assertSee('Belum ada pesanan aktif untuk Faskes ini.')
            ->assertSee('Faskes ini belum memiliki ambulans.')
            ->assertSee('Faskes ini belum memiliki sopir.');
    }

    // Detail pesanan

    public function test_detail_pesanan_hanya_untuk_faskes_terkait(): void
    {
        $ambulansSaya = Ambulans::factory()->create(['faskes_id' => $this->faskes->id]);
        $ambulansLain = Ambulans::factory()->create(['faskes_id' => $this->lain->id]);
        $milikku = Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create(['ambulans_id' => $ambulansSaya->id, 'nama_pasien' => 'Pasien Detail']);
        $tujuanku = Pesanan::factory()->create(['faskes_tujuan_id' => $this->faskes->id]);
        $bukanku = Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create(['ambulans_id' => $ambulansLain->id]);
        $tanpaFaskes = Pesanan::factory()->create();

        $this->actingAs($this->petugas);
        $this->get(route('petugas.pesanan.show', $milikku))->assertOk()->assertSee('Pasien Detail')->assertSee('Linimasa');
        $this->get(route('petugas.pesanan.show', $tujuanku))->assertOk();
        $this->get(route('petugas.pesanan.show', $bukanku))->assertForbidden();
        $this->get(route('petugas.pesanan.show', $tanpaFaskes))->assertForbidden();
    }

    // Daftar pesanan (docs/RETEMA_B.md 8.2, R6)

    public function test_daftar_pesanan_hanya_menampilkan_milik_faskes_sendiri(): void
    {
        $ambulansSaya = Ambulans::factory()->create(['faskes_id' => $this->faskes->id]);
        $ambulansLain = Ambulans::factory()->create(['faskes_id' => $this->lain->id]);
        $milikku = Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create(['ambulans_id' => $ambulansSaya->id, 'nama_pasien' => 'Pasien Daftar Faskes']);
        $tujuanku = Pesanan::factory()->create(['faskes_tujuan_id' => $this->faskes->id, 'nama_pasien' => 'Pasien Tujuan Faskes']);
        $bukanku = Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create(['ambulans_id' => $ambulansLain->id, 'nama_pasien' => 'Pasien Faskes Lain']);

        $html = $this->actingAs($this->petugas)->get(route('petugas.pesanan.index'))->assertOk()->getContent();

        $this->assertStringContainsString($milikku->kode, $html);
        $this->assertStringContainsString($tujuanku->kode, $html);
        $this->assertStringNotContainsString($bukanku->kode, $html);
    }

    public function test_daftar_pesanan_mendukung_tab_dan_pencarian(): void
    {
        $tujuanku = Pesanan::factory()->berstatus(PesananStatus::Selesai)->create(['faskes_tujuan_id' => $this->faskes->id, 'nama_pasien' => 'Pasien Selesai Faskes']);
        $menunggu = Pesanan::factory()->berstatus(PesananStatus::Menunggu)->create(['faskes_tujuan_id' => $this->faskes->id, 'nama_pasien' => 'Pasien Menunggu Faskes']);

        $this->actingAs($this->petugas);
        $html = $this->get(route('petugas.pesanan.index', ['tab' => 'perlu-tindakan']))->assertOk()->getContent();
        $this->assertStringContainsString($menunggu->kode, $html);
        $this->assertStringNotContainsString($tujuanku->kode, $html);

        $html = $this->get(route('petugas.pesanan.index', ['q' => 'Pasien Selesai Faskes']))->assertOk()->getContent();
        $this->assertStringContainsString($tujuanku->kode, $html);
        $this->assertStringNotContainsString($menunggu->kode, $html);
    }

    // Ketersediaan

    public function test_form_ketersediaan_terisi_data_faskes_sendiri(): void
    {
        $this->faskes->update(['bed_igd_total' => 9, 'bed_igd_tersedia' => 4]);

        $this->actingAs($this->petugas)->get(route('petugas.ketersediaan.edit'))
            ->assertOk()->assertSee('Bed IGD tersedia')->assertSee('value="9"', false)->assertSee('Belum pernah diperbarui.');
    }

    public function test_update_ketersediaan_mengisi_nilai_dan_stempel_waktu(): void
    {
        $this->actingAs($this->petugas)->put(route('petugas.ketersediaan.update'), $this->ketersediaan())
            ->assertRedirect(route('petugas.ketersediaan.edit'))->assertSessionHas('sukses');

        $f = $this->faskes->fresh();
        $this->assertSame(3, $f->bed_igd_tersedia);
        $this->assertSame(20, $f->bed_rawat_inap_total);
        $this->assertSame('Bedah umum', $f->dokter_spesialis_keterangan);
        $this->assertNotNull($f->ketersediaan_diperbarui_pada);
    }

    public function test_tersedia_tidak_boleh_melebihi_total(): void
    {
        $this->actingAs($this->petugas)->put(route('petugas.ketersediaan.update'), $this->ketersediaan(['bed_igd_tersedia' => 9]))
            ->assertSessionHasErrors('bed_igd_tersedia');
        $this->put(route('petugas.ketersediaan.update'), $this->ketersediaan(['bed_rawat_inap_tersedia' => 21]))
            ->assertSessionHasErrors('bed_rawat_inap_tersedia');

        $this->assertNull($this->faskes->fresh()->ketersediaan_diperbarui_pada);
    }

    public function test_petugas_tidak_bisa_mengubah_faskes_lain_lewat_isian(): void
    {
        $this->actingAs($this->petugas)->put(route('petugas.ketersediaan.update'), $this->ketersediaan() + ['faskes_id' => $this->lain->id]);

        $this->assertSame(3, $this->faskes->fresh()->bed_igd_tersedia);
        $this->assertSame(0, $this->lain->fresh()->bed_igd_tersedia);
        $this->assertNull($this->lain->fresh()->ketersediaan_diperbarui_pada);
    }

    public function test_ketersediaan_baru_tampil_di_info_faskes_publik(): void
    {
        $this->actingAs($this->petugas)->put(route('petugas.ketersediaan.update'), $this->ketersediaan());
        auth()->logout();

        // Ketersediaan kini petak ring (docs/RETEMA_B.md 8.5 arah B, R8): aria-label cincin membawa angka, bukan lagi teks "3/8".
        $this->get(route('faskes.index'))->assertSee('Faskes Saya')
            ->assertSee('Bed IGD: 3 dari 8', false)->assertSee('Bed rawat inap: 7 dari 20', false);
    }

    // Status ambulans

    public function test_tukar_tersedia_dan_servis_pada_ambulans_sendiri(): void
    {
        $a = Ambulans::factory()->create(['faskes_id' => $this->faskes->id]);

        $this->actingAs($this->petugas)->put(route('petugas.ambulans.status', $a), ['status' => 'servis'])
            ->assertRedirect(route('petugas.dashboard'))->assertSessionHas('sukses');
        $this->assertSame(StatusAmbulans::Servis, $a->fresh()->status);

        $this->put(route('petugas.ambulans.status', $a), ['status' => 'tersedia']);
        $this->assertSame(StatusAmbulans::Tersedia, $a->fresh()->status);
    }

    public function test_status_bertugas_tidak_bisa_diatur_petugas(): void
    {
        $a = Ambulans::factory()->create(['faskes_id' => $this->faskes->id]);

        $this->actingAs($this->petugas)->put(route('petugas.ambulans.status', $a), ['status' => 'bertugas'])
            ->assertSessionHasErrors('status');
        $this->assertSame(StatusAmbulans::Tersedia, $a->fresh()->status);
    }

    public function test_ambulans_yang_sedang_bertugas_tidak_bisa_diubah(): void
    {
        $a = Ambulans::factory()->bertugas()->create(['faskes_id' => $this->faskes->id]);

        $this->actingAs($this->petugas)->put(route('petugas.ambulans.status', $a), ['status' => 'servis'])
            ->assertSessionHas('galat');
        $this->assertSame(StatusAmbulans::Bertugas, $a->fresh()->status);
    }

    public function test_ambulans_faskes_lain_tidak_bisa_diubah(): void
    {
        $a = Ambulans::factory()->create(['faskes_id' => $this->lain->id]);

        $this->actingAs($this->petugas)->put(route('petugas.ambulans.status', $a), ['status' => 'servis'])->assertForbidden();
        $this->assertSame(StatusAmbulans::Tersedia, $a->fresh()->status);
    }

    // Akses

    public function test_halaman_petugas_hanya_untuk_petugas_faskes(): void
    {
        $a = Ambulans::factory()->create(['faskes_id' => $this->faskes->id]);

        $this->get(route('petugas.dashboard'))->assertRedirect(route('login'));

        foreach ([$this->buatWarga(), $this->buatAdmin(), $this->buatSopir($this->faskes)] as $user) {
            $this->actingAs($user);
            $this->get(route('petugas.dashboard'))->assertForbidden();
            $this->get(route('petugas.ketersediaan.edit'))->assertForbidden();
            $this->put(route('petugas.ketersediaan.update'), $this->ketersediaan())->assertForbidden();
            $this->put(route('petugas.ambulans.status', $a), ['status' => 'servis'])->assertForbidden();
        }
        $this->assertSame(StatusAmbulans::Tersedia, $a->fresh()->status);
    }

    public function test_petugas_tanpa_faskes_ditolak(): void
    {
        $tanpa = $this->buatPetugas(null, ['faskes_id' => null]);

        $this->actingAs($tanpa)->get(route('petugas.dashboard'))->assertForbidden();
        $this->get(route('petugas.ketersediaan.edit'))->assertForbidden();
    }
}
