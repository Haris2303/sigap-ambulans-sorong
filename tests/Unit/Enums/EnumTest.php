<?php

namespace Tests\Unit\Enums;

use App\Enums\JenisAmbulans;
use App\Enums\JenisFaskes;
use App\Enums\JenisLayanan;
use App\Enums\Kepemilikan;
use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Enums\TingkatDarurat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public static function semuaEnum(): array
    {
        return [
            [Role::class], [PesananStatus::class], [JenisLayanan::class], [TingkatDarurat::class],
            [JenisFaskes::class], [JenisAmbulans::class], [StatusAmbulans::class], [Kepemilikan::class],
        ];
    }

    #[DataProvider('semuaEnum')]
    public function test_opsi_memetakan_nilai_ke_label(string $enum): void
    {
        $opsi = $enum::opsi();

        $this->assertCount(count($enum::cases()), $opsi);
        foreach ($enum::cases() as $kasus) {
            $this->assertSame($kasus->label(), $opsi[$kasus->value]);
            $this->assertNotSame('', $kasus->label());
        }
    }

    public function test_label_peran_sesuai_istilah_baku(): void
    {
        $this->assertSame('Masyarakat', Role::Warga->label());
        $this->assertSame('Dinkes', Role::Admin->label());
        $this->assertSame('Petugas Faskes', Role::PetugasFaskes->label());
        $this->assertSame('Sopir', Role::Sopir->label());
    }

    public function test_rute_beranda_per_peran(): void
    {
        $this->assertSame('warga.beranda', Role::Warga->ruteBeranda());
        $this->assertSame('admin.dashboard', Role::Admin->ruteBeranda());
        $this->assertSame('petugas.dashboard', Role::PetugasFaskes->ruteBeranda());
        $this->assertSame('sopir.dashboard', Role::Sopir->ruteBeranda());
    }

    public function test_label_dan_nada_status_pesanan(): void
    {
        $this->assertSame('Menunggu Verifikasi', PesananStatus::Menunggu->label());
        $this->assertSame('Diterima Sopir', PesananStatus::Diterima->label());
        $this->assertSame('peringatan', PesananStatus::Menunggu->nada());
        $this->assertSame('info', PesananStatus::Terverifikasi->nada());
        $this->assertSame('info', PesananStatus::Ditugaskan->nada());
        $this->assertSame('info', PesananStatus::Diterima->nada());
        $this->assertSame('info', PesananStatus::DalamPerjalanan->nada());
        $this->assertSame('sukses', PesananStatus::Selesai->nada());
        $this->assertSame('bahaya', PesananStatus::Ditolak->nada());
        $this->assertSame('netral', PesananStatus::Dibatalkan->nada());
    }

    public function test_status_final_dan_pemegang_sumber_daya(): void
    {
        $final = array_filter(PesananStatus::cases(), fn ($s) => $s->final());
        $this->assertEqualsCanonicalizing(
            [PesananStatus::Selesai, PesananStatus::Ditolak, PesananStatus::Dibatalkan],
            array_values($final)
        );

        $pegang = array_filter(PesananStatus::cases(), fn ($s) => $s->memegangSumberDaya());
        $this->assertEqualsCanonicalizing(
            [PesananStatus::Ditugaskan, PesananStatus::Diterima, PesananStatus::DalamPerjalanan],
            array_values($pegang)
        );
    }

    public function test_transisi_terbuka_sesuai_tabel_mesin_status(): void
    {
        $this->assertEqualsCanonicalizing(
            [PesananStatus::Terverifikasi, PesananStatus::Ditolak, PesananStatus::Dibatalkan],
            PesananStatus::Menunggu->transisiTerbuka()
        );
        $this->assertEqualsCanonicalizing(
            [PesananStatus::Ditugaskan, PesananStatus::Dibatalkan],
            PesananStatus::Terverifikasi->transisiTerbuka()
        );
        $this->assertEqualsCanonicalizing(
            [PesananStatus::Diterima, PesananStatus::Terverifikasi, PesananStatus::Dibatalkan],
            PesananStatus::Ditugaskan->transisiTerbuka()
        );
        $this->assertEqualsCanonicalizing(
            [PesananStatus::DalamPerjalanan, PesananStatus::Dibatalkan],
            PesananStatus::Diterima->transisiTerbuka()
        );
        $this->assertEqualsCanonicalizing(
            [PesananStatus::Selesai, PesananStatus::Dibatalkan],
            PesananStatus::DalamPerjalanan->transisiTerbuka()
        );
    }

    public function test_status_final_tidak_punya_transisi(): void
    {
        foreach (PesananStatus::cases() as $status) {
            if ($status->final()) {
                $this->assertSame([], $status->transisiTerbuka());
            }
        }
        $this->assertFalse(PesananStatus::Selesai->bolehKe(PesananStatus::Menunggu));
        $this->assertTrue(PesananStatus::Menunggu->bolehKe(PesananStatus::Terverifikasi));
        $this->assertFalse(PesananStatus::Menunggu->bolehKe(PesananStatus::Ditugaskan));
    }

    public function test_status_aktif_tidak_memuat_status_final(): void
    {
        $this->assertCount(5, PesananStatus::aktif());
    }

    public function test_saran_jenis_ambulans_per_layanan(): void
    {
        $this->assertSame(JenisAmbulans::Darurat, JenisLayanan::Darurat->jenisAmbulansSaran());
        $this->assertSame(JenisAmbulans::Transport, JenisLayanan::Rujukan->jenisAmbulansSaran());
        $this->assertSame(JenisAmbulans::Transport, JenisLayanan::Persalinan->jenisAmbulansSaran());
        $this->assertTrue(JenisLayanan::Rujukan->wajibFaskesTujuan());
        $this->assertFalse(JenisLayanan::Darurat->wajibFaskesTujuan());
    }

    public function test_status_ambulans_manual_tidak_memuat_bertugas(): void
    {
        $this->assertSame(['tersedia', 'servis'], array_keys(StatusAmbulans::opsiManual()));
    }
}
