<?php

namespace Tests\Feature;

use App\Enums\PesananStatus;
use App\Enums\StatusAmbulans;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPengguna;
use Tests\TestCase;

/** Alur lintas peran lewat HTTP: Masyarakat, Dinkes, Sopir, dan Petugas Faskes (RANCANGAN bagian 10). */
class AlurPenuhTest extends TestCase
{
    use MembuatPengguna, RefreshDatabase;

    private Faskes $faskes;

    private User $warga;

    private User $admin;

    private User $sopir;

    private Ambulans $ambulans;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faskes = Faskes::factory()->rs()->create(['nama' => 'RS Alur']);
        $this->warga = $this->buatWarga(['no_telepon' => '081234567890']);
        $this->admin = $this->buatAdmin();
        $this->sopir = User::factory()->sopir($this->faskes)->online()->create(['name' => 'Sopir Alur']);
        $this->ambulans = Ambulans::factory()->darurat()->create(['faskes_id' => $this->faskes->id, 'sopir_id' => $this->sopir->id]);
    }

    private function pesanSebagaiWarga(): Pesanan
    {
        $isian = [
            'jenis_layanan' => 'darurat', 'tingkat_darurat' => 'gawat', 'lokasi_jemput' => 'Jalan Alur Uji',
            'lat_jemput' => '-0.8800000', 'lng_jemput' => '131.2500000', 'nama_pasien' => 'Pasien Alur',
            'no_telepon_kontak' => '081299990000', 'catatan' => 'Sesak napas',
        ];

        $this->actingAs($this->warga)->post(route('warga.pesanan.tinjau'), $isian)->assertRedirect(route('warga.pesanan.konfirmasi'));
        $this->assertSame(0, Pesanan::count(), 'Belum ada pesanan sebelum dikonfirmasi');
        $this->get(route('warga.pesanan.konfirmasi'))->assertOk()->assertSee('Pasien Alur');
        $this->post(route('warga.pesanan.simpan'))->assertSessionHas('sukses');

        return Pesanan::firstOrFail();
    }

    private function verifikasi(Pesanan $pesanan): void
    {
        $this->actingAs($this->admin)->put(route('admin.pesanan.verifikasi', $pesanan))->assertSessionHas('sukses');
    }

    private function tugaskan(Pesanan $pesanan, ?User $sopir = null, ?Ambulans $ambulans = null): void
    {
        $this->actingAs($this->admin)->put(route('admin.pesanan.tugaskan', $pesanan), [
            'ambulans_id' => ($ambulans ?? $this->ambulans)->id,
            'sopir_id' => ($sopir ?? $this->sopir)->id,
        ])->assertSessionHas('sukses');
    }

    public function test_alur_normal_dari_pesanan_sampai_selesai(): void
    {
        $pesanan = $this->pesanSebagaiWarga();
        $this->assertSame(PesananStatus::Menunggu, $pesanan->status);

        $this->verifikasi($pesanan);
        $this->assertSame(PesananStatus::Terverifikasi, $pesanan->fresh()->status);

        $this->tugaskan($pesanan);
        $this->assertSame(PesananStatus::Ditugaskan, $pesanan->fresh()->status);
        $this->assertSame(StatusAmbulans::Bertugas, $this->ambulans->fresh()->status);

        $this->actingAs($this->sopir)->getJson(route('sopir.penugasan'))->assertJsonPath('data.penugasan.id', $pesanan->id);
        $this->post(route('sopir.pesanan.terima', $pesanan))->assertSessionHas('sukses');

        $this->actingAs($this->warga)->get(route('warga.pesanan.show', $pesanan))
            ->assertOk()->assertSee('Sopir Alur')->assertSee('Posisi ambulans');

        $this->actingAs($this->sopir)->post(route('sopir.pesanan.berangkat', $pesanan))->assertSessionHas('sukses');
        $this->post(route('sopir.pesanan.selesai', $pesanan))->assertSessionHas('sukses');

        $akhir = $pesanan->fresh();
        $this->assertSame(PesananStatus::Selesai, $akhir->status);
        $this->assertSame(StatusAmbulans::Tersedia, $this->ambulans->fresh()->status);
        $this->assertSame(
            ['menunggu', 'terverifikasi', 'ditugaskan', 'diterima', 'dalam_perjalanan', 'selesai'],
            $akhir->log->map(fn ($l) => $l->ke_status->value)->all()
        );

        $this->actingAs($this->warga)->getJson(route('warga.pesanan.status', $pesanan))
            ->assertJsonPath('data.status', 'selesai')->assertJsonPath('data.final', true);
        $this->get(route('warga.pesanan.show', $pesanan))->assertSee('Selesai')->assertSee('Diterima Sopir');
    }

    public function test_jalur_ditolak_dinkes_tampil_dengan_alasan_ke_masyarakat(): void
    {
        $pesanan = $this->pesanSebagaiWarga();

        $this->actingAs($this->admin)->put(route('admin.pesanan.tolak', $pesanan), ['alasan' => 'Lokasi tidak dapat dijangkau'])->assertSessionHas('sukses');

        $this->actingAs($this->warga)->get(route('warga.pesanan.show', $pesanan))
            ->assertOk()->assertSee('Ditolak')->assertSee('Lokasi tidak dapat dijangkau')->assertDontSee('Batalkan pesanan');
        $this->assertSame(PesananStatus::Ditolak, $pesanan->fresh()->status);
    }

    public function test_jalur_dibatalkan_masyarakat_sebelum_dan_sesudah_verifikasi(): void
    {
        $pesanan = $this->pesanSebagaiWarga();
        $this->actingAs($this->warga)->put(route('warga.pesanan.batalkan', $pesanan), ['alasan' => 'Salah pesan'])->assertSessionHas('sukses');
        $this->assertSame(PesananStatus::Dibatalkan, $pesanan->fresh()->status);

        $this->actingAs($this->admin)->put(route('admin.pesanan.verifikasi', $pesanan))->assertSessionHas('galat');
    }

    public function test_masyarakat_tidak_bisa_membatalkan_setelah_ditugaskan(): void
    {
        $pesanan = $this->pesanSebagaiWarga();
        $this->verifikasi($pesanan);
        $this->tugaskan($pesanan);

        $this->actingAs($this->warga)->put(route('warga.pesanan.batalkan', $pesanan))->assertForbidden();
        $this->assertSame(PesananStatus::Ditugaskan, $pesanan->fresh()->status);
    }

    public function test_jalur_dibatalkan_dinkes_saat_dalam_perjalanan_membebaskan_ambulans(): void
    {
        $pesanan = $this->pesanSebagaiWarga();
        $this->verifikasi($pesanan);
        $this->tugaskan($pesanan);
        $this->actingAs($this->sopir)->post(route('sopir.pesanan.terima', $pesanan));
        $this->post(route('sopir.pesanan.berangkat', $pesanan));

        $this->actingAs($this->admin)->put(route('admin.pesanan.batalkan', $pesanan), ['alasan' => 'Pasien dibawa keluarga'])->assertSessionHas('sukses');

        $this->assertSame(PesananStatus::Dibatalkan, $pesanan->fresh()->status);
        $this->assertSame(StatusAmbulans::Tersedia, $this->ambulans->fresh()->status);
        $this->actingAs($this->sopir)->getJson(route('sopir.penugasan'))->assertJsonPath('data.penugasan', null);
    }

    public function test_sopir_menolak_lalu_ditugaskan_ke_sopir_lain(): void
    {
        $sopir2 = User::factory()->sopir($this->faskes)->online()->create(['name' => 'Sopir Pengganti']);
        $pesanan = $this->pesanSebagaiWarga();
        $this->verifikasi($pesanan);
        $this->tugaskan($pesanan);

        $this->actingAs($this->sopir)->post(route('sopir.pesanan.tolak', $pesanan), ['alasan' => 'Kendaraan bermasalah'])->assertSessionHas('sukses');

        $kembali = $pesanan->fresh();
        $this->assertSame(PesananStatus::Terverifikasi, $kembali->status);
        $this->assertNull($kembali->sopir_id);
        $this->assertSame(StatusAmbulans::Tersedia, $this->ambulans->fresh()->status);

        $this->tugaskan($pesanan, $sopir2);
        $this->assertSame($sopir2->id, $pesanan->fresh()->sopir_id);

        $this->actingAs($this->sopir)->post(route('sopir.pesanan.terima', $pesanan))->assertForbidden();
        $this->actingAs($sopir2)->post(route('sopir.pesanan.terima', $pesanan))->assertSessionHas('sukses');
        $this->post(route('sopir.pesanan.berangkat', $pesanan));
        $this->post(route('sopir.pesanan.selesai', $pesanan));

        $akhir = $pesanan->fresh();
        $this->assertSame(PesananStatus::Selesai, $akhir->status);
        $catatan = $akhir->log->map(fn ($l) => $l->catatan)->filter()->implode(' | ');
        $this->assertStringContainsString('Kendaraan bermasalah', $catatan);
    }

    public function test_dinkes_melepas_penugasan_lalu_menugaskan_ulang(): void
    {
        $pesanan = $this->pesanSebagaiWarga();
        $this->verifikasi($pesanan);
        $this->tugaskan($pesanan);

        $this->actingAs($this->admin)->put(route('admin.pesanan.lepas', $pesanan), ['alasan' => 'Salah pilih sopir'])->assertSessionHas('sukses');
        $this->assertSame(PesananStatus::Terverifikasi, $pesanan->fresh()->status);

        $this->tugaskan($pesanan);
        $this->assertSame(PesananStatus::Ditugaskan, $pesanan->fresh()->status);
    }

    public function test_petugas_memperbarui_ketersediaan_dan_tampil_di_info_faskes(): void
    {
        $petugas = $this->buatPetugas($this->faskes);

        $this->get(route('faskes.index'))->assertSee('RS Alur')->assertSee('Belum diperbarui');

        $this->actingAs($petugas)->put(route('petugas.ketersediaan.update'), [
            'bed_igd_total' => 12, 'bed_igd_tersedia' => 5, 'bed_rawat_inap_total' => 40, 'bed_rawat_inap_tersedia' => 18,
            'dokter_umum_tersedia' => 4, 'dokter_spesialis_tersedia' => 2,
        ])->assertSessionHas('sukses');

        auth()->logout();
        $this->get(route('faskes.index'))->assertSee('5/12')->assertSee('18/40')->assertDontSee('Belum diperbarui');

        $json = $this->getJson(route('faskes.data'))->json('data.faskes.0');
        $this->assertSame(5, $json['bed_igd_tersedia']);
        $this->assertSame(1, $json['ambulans_tersedia']);
    }

    public function test_dinkes_melihat_pesanan_baru_pada_ringkasan_dan_dasbor(): void
    {
        $this->pesanSebagaiWarga();

        $this->actingAs($this->admin)->getJson(route('admin.pesanan.ringkasan'))->assertJsonPath('data.pesanan_menunggu', 1);
        $this->get(route('admin.pesanan.index', ['status' => 'menunggu']))->assertSee('Pasien Alur')->assertSee('Gawat');
    }
}
