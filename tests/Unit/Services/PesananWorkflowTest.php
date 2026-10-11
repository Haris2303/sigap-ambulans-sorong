<?php

namespace Tests\Unit\Services;

use App\Enums\JenisLayanan;
use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Exceptions\PenugasanTidakValidException;
use App\Exceptions\TransisiTidakValidException;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PesananWorkflow;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPengguna;
use Tests\TestCase;

class PesananWorkflowTest extends TestCase
{
    use MembuatPengguna, RefreshDatabase;

    private PesananWorkflow $alur;

    private User $warga;

    private User $admin;

    private Faskes $faskes;

    private Ambulans $ambulans;

    private User $sopir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->alur = new PesananWorkflow;
        $this->warga = $this->buatWarga();
        $this->admin = $this->buatAdmin();
        $this->faskes = Faskes::factory()->create();
        $this->sopir = User::factory()->sopir($this->faskes)->online()->create();
        $this->ambulans = Ambulans::factory()->create(['faskes_id' => $this->faskes->id, 'sopir_id' => $this->sopir->id]);
    }

    private function data(array $ganti = []): array
    {
        return array_merge([
            'nama_pasien' => 'Pasien Uji',
            'no_telepon_kontak' => '081234567890',
            'jenis_layanan' => JenisLayanan::Darurat,
            'tingkat_darurat' => 'biasa',
            'lokasi_jemput' => 'Jalan Basuki Rahmat',
        ], $ganti);
    }

    private function menunggu(): Pesanan
    {
        return $this->alur->buat($this->warga, $this->data());
    }

    private function terverifikasi(): Pesanan
    {
        return $this->alur->verifikasi($this->menunggu(), $this->admin);
    }

    private function ditugaskan(): Pesanan
    {
        return $this->alur->tugaskan($this->terverifikasi(), $this->admin, $this->ambulans, $this->sopir);
    }

    private function diterima(): Pesanan
    {
        return $this->alur->terima($this->ditugaskan(), $this->sopir);
    }

    private function dalamPerjalanan(): Pesanan
    {
        return $this->alur->mulaiPerjalanan($this->diterima(), $this->sopir);
    }

    public function test_buat_membuat_kode_status_menunggu_dan_log(): void
    {
        $pesanan = $this->menunggu();

        $this->assertSame(PesananStatus::Menunggu, $pesanan->status);
        $this->assertSame('SGP-'.str_pad((string) $pesanan->id, 6, '0', STR_PAD_LEFT), $pesanan->kode);
        $this->assertSame($this->warga->id, $pesanan->user_id);
        $log = $pesanan->log;
        $this->assertCount(1, $log);
        $this->assertNull($log[0]->dari_status);
        $this->assertSame(PesananStatus::Menunggu, $log[0]->ke_status);
        $this->assertSame(Role::Warga, $log[0]->peran);
    }

    public function test_buat_hanya_oleh_warga(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->alur->buat($this->admin, $this->data());
    }

    public function test_buat_rujukan_tanpa_faskes_tujuan_ditolak(): void
    {
        try {
            $this->alur->buat($this->warga, $this->data(['jenis_layanan' => JenisLayanan::Rujukan]));
            $this->fail('Seharusnya melempar exception.');
        } catch (TransisiTidakValidException) {
            $this->assertSame(0, Pesanan::count());
        }
    }

    public function test_verifikasi_mengisi_pelaku_dan_waktu(): void
    {
        $pesanan = $this->terverifikasi();

        $this->assertSame(PesananStatus::Terverifikasi, $pesanan->status);
        $this->assertSame($this->admin->id, $pesanan->diverifikasi_oleh_id);
        $this->assertNotNull($pesanan->diverifikasi_pada);
        $this->assertCount(2, $pesanan->log);
    }

    public function test_verifikasi_hanya_oleh_admin(): void
    {
        $pesanan = $this->menunggu();
        $this->expectException(AuthorizationException::class);
        $this->alur->verifikasi($pesanan, $this->warga);
    }

    public function test_tolak_wajib_alasan_dan_tersimpan(): void
    {
        $pesanan = $this->menunggu();

        try {
            $this->alur->tolak($pesanan, $this->admin, '   ');
            $this->fail('Seharusnya melempar exception.');
        } catch (TransisiTidakValidException) {
            $this->assertSame(PesananStatus::Menunggu, $pesanan->fresh()->status);
        }

        $pesanan = $this->alur->tolak($pesanan, $this->admin, 'Data tidak lengkap');
        $this->assertSame(PesananStatus::Ditolak, $pesanan->status);
        $this->assertSame('Data tidak lengkap', $pesanan->alasan_penolakan);
        $this->assertSame('Data tidak lengkap', $pesanan->log->last()->catatan);
    }

    public function test_tolak_hanya_dari_menunggu(): void
    {
        $pesanan = $this->terverifikasi();
        $this->expectException(TransisiTidakValidException::class);
        $this->alur->tolak($pesanan, $this->admin, 'Terlambat');
    }

    public function test_warga_membatalkan_dari_menunggu_dan_terverifikasi(): void
    {
        $a = $this->alur->batalkanOlehWarga($this->menunggu(), $this->warga, 'Salah pesan');
        $this->assertSame(PesananStatus::Dibatalkan, $a->status);
        $this->assertSame('Salah pesan', $a->alasan_pembatalan);

        $b = $this->alur->batalkanOlehWarga($this->terverifikasi(), $this->warga);
        $this->assertSame(PesananStatus::Dibatalkan, $b->status);
        $this->assertNull($b->alasan_pembatalan);
    }

    public function test_warga_tidak_bisa_membatalkan_setelah_ditugaskan(): void
    {
        $pesanan = $this->ditugaskan();

        try {
            $this->alur->batalkanOlehWarga($pesanan, $this->warga);
            $this->fail('Seharusnya melempar exception.');
        } catch (TransisiTidakValidException) {
            $this->assertSame(PesananStatus::Ditugaskan, $pesanan->fresh()->status);
        }
    }

    public function test_warga_lain_tidak_bisa_membatalkan(): void
    {
        $pesanan = $this->menunggu();
        $this->expectException(AuthorizationException::class);
        $this->alur->batalkanOlehWarga($pesanan, $this->buatWarga());
    }

    public function test_tugaskan_mengisi_sumber_daya_dan_ambulans_bertugas(): void
    {
        $pesanan = $this->ditugaskan();

        $this->assertSame(PesananStatus::Ditugaskan, $pesanan->status);
        $this->assertSame($this->ambulans->id, $pesanan->ambulans_id);
        $this->assertSame($this->sopir->id, $pesanan->sopir_id);
        $this->assertNotNull($pesanan->ditugaskan_pada);
        $this->assertSame(StatusAmbulans::Bertugas, $this->ambulans->fresh()->status);
    }

    public function test_tugaskan_hanya_dari_terverifikasi(): void
    {
        $pesanan = $this->menunggu();
        $this->expectException(TransisiTidakValidException::class);
        $this->alur->tugaskan($pesanan, $this->admin, $this->ambulans, $this->sopir);
    }

    public function test_tugaskan_ditolak_bila_ambulans_servis(): void
    {
        $this->ambulans->update(['status' => StatusAmbulans::Servis]);
        $pesanan = $this->terverifikasi();

        $this->assertPenugasanDitolak($pesanan, $this->ambulans, $this->sopir);
    }

    public function test_tugaskan_ditolak_bila_ambulans_sudah_bertugas(): void
    {
        $this->ditugaskan();
        $sopir2 = User::factory()->sopir($this->faskes)->online()->create();
        $lain = $this->alur->verifikasi($this->alur->buat($this->buatWarga(), $this->data()), $this->admin);

        $this->assertPenugasanDitolak($lain, $this->ambulans, $sopir2);
    }

    public function test_tugaskan_ditolak_bila_faskes_tidak_aktif(): void
    {
        $this->faskes->update(['aktif' => false]);
        $this->assertPenugasanDitolak($this->terverifikasi(), $this->ambulans, $this->sopir);
    }

    public function test_tugaskan_ditolak_bila_sopir_offline(): void
    {
        $this->sopir->update(['sedang_online' => false]);
        $this->assertPenugasanDitolak($this->terverifikasi(), $this->ambulans, $this->sopir);
    }

    public function test_tugaskan_ditolak_bila_sopir_beda_faskes(): void
    {
        $sopirLain = User::factory()->sopir()->online()->create();
        $this->assertPenugasanDitolak($this->terverifikasi(), $this->ambulans, $sopirLain);
    }

    public function test_tugaskan_ditolak_bila_pengguna_bukan_sopir(): void
    {
        $petugas = User::factory()->petugas($this->faskes)->online()->create();
        $this->assertPenugasanDitolak($this->terverifikasi(), $this->ambulans, $petugas);
    }

    public function test_tugaskan_ditolak_bila_sopir_nonaktif(): void
    {
        $this->sopir->update(['aktif' => false]);
        $this->assertPenugasanDitolak($this->terverifikasi(), $this->ambulans, $this->sopir);
    }

    public function test_tugaskan_ditolak_bila_sopir_masih_memegang_pesanan(): void
    {
        $this->ditugaskan();
        $ambulans2 = Ambulans::factory()->create(['faskes_id' => $this->faskes->id]);
        $lain = $this->alur->verifikasi($this->alur->buat($this->buatWarga(), $this->data()), $this->admin);

        $this->assertPenugasanDitolak($lain, $ambulans2, $this->sopir);
    }

    public function test_tugaskan_hanya_oleh_admin(): void
    {
        $pesanan = $this->terverifikasi();
        $this->expectException(AuthorizationException::class);
        $this->alur->tugaskan($pesanan, $this->warga, $this->ambulans, $this->sopir);
    }

    private function assertPenugasanDitolak(Pesanan $pesanan, Ambulans $ambulans, User $sopir): void
    {
        $statusAmbulans = $ambulans->fresh()->status;

        try {
            $this->alur->tugaskan($pesanan, $this->admin, $ambulans, $sopir);
            $this->fail('Seharusnya melempar PenugasanTidakValidException.');
        } catch (PenugasanTidakValidException) {
            $segar = $pesanan->fresh();
            $this->assertSame(PesananStatus::Terverifikasi, $segar->status);
            $this->assertNull($segar->ambulans_id);
            $this->assertNull($segar->sopir_id);
            $this->assertSame($statusAmbulans, $ambulans->fresh()->status);
        }
    }

    public function test_sopir_menerima_penugasan(): void
    {
        $pesanan = $this->diterima();

        $this->assertSame(PesananStatus::Diterima, $pesanan->status);
        $this->assertNotNull($pesanan->diterima_pada);
    }

    public function test_sopir_lain_tidak_bisa_menerima(): void
    {
        $pesanan = $this->ditugaskan();
        $sopirLain = User::factory()->sopir($this->faskes)->online()->create();

        $this->expectException(AuthorizationException::class);
        $this->alur->terima($pesanan, $sopirLain);
    }

    public function test_non_sopir_tidak_bisa_menerima(): void
    {
        $pesanan = $this->ditugaskan();
        $this->expectException(AuthorizationException::class);
        $this->alur->terima($pesanan, $this->admin);
    }

    public function test_sopir_menolak_mengembalikan_ke_terverifikasi_dan_membebaskan_sumber_daya(): void
    {
        $pesanan = $this->ditugaskan();
        $pesanan = $this->alur->tolakPenugasan($pesanan, $this->sopir, 'Ban bocor');

        $this->assertSame(PesananStatus::Terverifikasi, $pesanan->status);
        $this->assertNull($pesanan->ambulans_id);
        $this->assertNull($pesanan->sopir_id);
        $this->assertNull($pesanan->ditugaskan_pada);
        $this->assertSame(StatusAmbulans::Tersedia, $this->ambulans->fresh()->status);
        $this->assertStringContainsString('Ban bocor', $pesanan->log->last()->catatan);
        $this->assertSame(Role::Sopir, $pesanan->log->last()->peran);
    }

    public function test_sopir_menolak_wajib_alasan(): void
    {
        $pesanan = $this->ditugaskan();
        $this->expectException(TransisiTidakValidException::class);
        $this->alur->tolakPenugasan($pesanan, $this->sopir, '');
    }

    public function test_sopir_tidak_bisa_menolak_setelah_menerima(): void
    {
        $pesanan = $this->diterima();
        $this->expectException(TransisiTidakValidException::class);
        $this->alur->tolakPenugasan($pesanan, $this->sopir, 'Berubah pikiran');
    }

    public function test_setelah_ditolak_dapat_ditugaskan_ke_sopir_lain(): void
    {
        $pesanan = $this->alur->tolakPenugasan($this->ditugaskan(), $this->sopir, 'Sakit');
        $sopir2 = User::factory()->sopir($this->faskes)->online()->create();

        $pesanan = $this->alur->tugaskan($pesanan, $this->admin, $this->ambulans, $sopir2);

        $this->assertSame(PesananStatus::Ditugaskan, $pesanan->status);
        $this->assertSame($sopir2->id, $pesanan->sopir_id);
    }

    public function test_admin_melepas_penugasan(): void
    {
        $pesanan = $this->alur->lepaskanPenugasan($this->ditugaskan(), $this->admin);

        $this->assertSame(PesananStatus::Terverifikasi, $pesanan->status);
        $this->assertNull($pesanan->sopir_id);
        $this->assertSame(StatusAmbulans::Tersedia, $this->ambulans->fresh()->status);
        $this->assertSame(Role::Admin, $pesanan->log->last()->peran);
    }

    public function test_admin_tidak_bisa_melepas_setelah_diterima(): void
    {
        $pesanan = $this->diterima();
        $this->expectException(TransisiTidakValidException::class);
        $this->alur->lepaskanPenugasan($pesanan, $this->admin);
    }

    public function test_alur_penuh_sampai_selesai_dan_ambulans_kembali_tersedia(): void
    {
        $pesanan = $this->dalamPerjalanan();
        $this->assertNotNull($pesanan->berangkat_pada);
        $this->assertSame(StatusAmbulans::Bertugas, $this->ambulans->fresh()->status);

        $pesanan = $this->alur->selesaikan($pesanan, $this->sopir);

        $this->assertSame(PesananStatus::Selesai, $pesanan->status);
        $this->assertNotNull($pesanan->selesai_pada);
        $this->assertSame(StatusAmbulans::Tersedia, $this->ambulans->fresh()->status);
        $this->assertSame(
            [null, 'menunggu', 'terverifikasi', 'ditugaskan', 'diterima', 'dalam_perjalanan'],
            $pesanan->log->map(fn ($l) => $l->dari_status?->value)->all()
        );
        $this->assertCount(6, $pesanan->log);
        $this->assertSame(PesananStatus::Selesai, $pesanan->log->last()->ke_status);
    }

    public function test_mulai_perjalanan_dan_selesai_harus_berurutan(): void
    {
        $pesanan = $this->ditugaskan();

        try {
            $this->alur->mulaiPerjalanan($pesanan, $this->sopir);
            $this->fail('Seharusnya melempar exception.');
        } catch (TransisiTidakValidException) {
            $this->assertSame(PesananStatus::Ditugaskan, $pesanan->fresh()->status);
        }

        $this->expectException(TransisiTidakValidException::class);
        $this->alur->selesaikan($pesanan, $this->sopir);
    }

    public function test_admin_membatalkan_dengan_alasan_dan_membebaskan_ambulans(): void
    {
        foreach (['terverifikasi', 'ditugaskan', 'diterima', 'dalam_perjalanan'] as $awal) {
            $pesanan = match ($awal) {
                'terverifikasi' => $this->terverifikasi(),
                'ditugaskan' => $this->ditugaskan(),
                'diterima' => $this->diterima(),
                'dalam_perjalanan' => $this->dalamPerjalanan(),
            };

            $pesanan = $this->alur->batalkanOlehAdmin($pesanan, $this->admin, 'Dibatalkan Dinkes');

            $this->assertSame(PesananStatus::Dibatalkan, $pesanan->status, $awal);
            $this->assertSame('Dibatalkan Dinkes', $pesanan->alasan_pembatalan);
            $this->assertSame(StatusAmbulans::Tersedia, $this->ambulans->fresh()->status, $awal);
        }
    }

    public function test_admin_wajib_alasan_saat_membatalkan(): void
    {
        $pesanan = $this->terverifikasi();
        $this->expectException(TransisiTidakValidException::class);
        $this->alur->batalkanOlehAdmin($pesanan, $this->admin, '');
    }

    public function test_pesanan_final_tidak_bisa_diubah_lagi(): void
    {
        $selesai = $this->alur->selesaikan($this->dalamPerjalanan(), $this->sopir);
        $ditolak = $this->alur->tolak($this->menunggu(), $this->admin, 'Ditolak');
        $batal = $this->alur->batalkanOlehWarga($this->menunggu(), $this->warga);

        foreach ([$selesai, $ditolak, $batal] as $pesanan) {
            $status = $pesanan->status;
            foreach ([
                fn () => $this->alur->verifikasi($pesanan, $this->admin),
                fn () => $this->alur->tolak($pesanan, $this->admin, 'x'),
                fn () => $this->alur->batalkanOlehAdmin($pesanan, $this->admin, 'x'),
                fn () => $this->alur->batalkanOlehWarga($pesanan, $this->warga),
                fn () => $this->alur->lepaskanPenugasan($pesanan, $this->admin),
            ] as $aksi) {
                try {
                    $aksi();
                    $this->fail('Pesanan final seharusnya tidak dapat diubah.');
                } catch (TransisiTidakValidException) {
                    $this->assertSame($status, $pesanan->fresh()->status);
                }
            }
        }
    }

    public function test_admin_tidak_bisa_membatalkan_pesanan_menunggu(): void
    {
        $pesanan = $this->menunggu();
        $this->expectException(TransisiTidakValidException::class);
        $this->alur->batalkanOlehAdmin($pesanan, $this->admin, 'x');
    }

    public function test_setiap_transisi_menulis_satu_baris_log(): void
    {
        $pesanan = $this->dalamPerjalanan();
        $this->assertSame(5, $pesanan->log()->count());
        $this->alur->selesaikan($pesanan, $this->sopir);
        $this->assertSame(6, $pesanan->log()->count());
    }
}
