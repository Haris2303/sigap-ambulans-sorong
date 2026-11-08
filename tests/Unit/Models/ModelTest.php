<?php

namespace Tests\Unit\Models;

use App\Enums\JenisFaskes;
use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\PesananLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPengguna;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use MembuatPengguna, RefreshDatabase;

    public function test_user_di_cast_ke_enum_peran(): void
    {
        $admin = $this->buatAdmin();

        $this->assertSame(Role::Admin, $admin->fresh()->role);
        $this->assertTrue($admin->punyaPeran(Role::Admin, Role::Sopir));
        $this->assertFalse($admin->punyaPeran(Role::Warga));
    }

    public function test_scope_sopir_petugas_dan_aktif(): void
    {
        $faskes = Faskes::factory()->create();
        $this->buatSopir($faskes);
        $this->buatSopir($faskes, ['aktif' => false]);
        $this->buatPetugas($faskes);
        $this->buatWarga();

        $this->assertSame(2, User::sopir()->count());
        $this->assertSame(1, User::sopir()->aktif()->count());
        $this->assertSame(1, User::petugas()->count());
    }

    public function test_relasi_faskes_ambulans_dan_sopir(): void
    {
        $faskes = Faskes::factory()->rs()->create();
        $sopir = $this->buatSopir($faskes);
        $ambulans = Ambulans::factory()->darurat()->create(['faskes_id' => $faskes->id, 'sopir_id' => $sopir->id]);

        $this->assertSame(JenisFaskes::Rs, $faskes->fresh()->jenis);
        $this->assertTrue($faskes->ambulans->contains($ambulans));
        $this->assertTrue($ambulans->faskes->is($faskes));
        $this->assertTrue($ambulans->sopirTetap->is($sopir));
        $this->assertTrue($sopir->ambulansTetap->is($ambulans));
        $this->assertTrue($sopir->faskes->is($faskes));
        $this->assertSame(StatusAmbulans::Tersedia, $ambulans->fresh()->status);
    }

    public function test_scope_faskes_aktif_dan_ambulans_tersedia(): void
    {
        $aktif = Faskes::factory()->create();
        Faskes::factory()->nonaktif()->create();
        Ambulans::factory()->create(['faskes_id' => $aktif->id]);
        Ambulans::factory()->servis()->create(['faskes_id' => $aktif->id]);
        Ambulans::factory()->bertugas()->create(['faskes_id' => $aktif->id]);

        $this->assertSame(1, Faskes::aktif()->count());
        $this->assertSame(1, Ambulans::tersedia()->count());
    }

    public function test_status_ketersediaan_faskes(): void
    {
        $baru = Faskes::factory()->create();
        $segar = Faskes::factory()->diperbarui()->create();
        $lama = Faskes::factory()->create(['ketersediaan_diperbarui_pada' => now()->subHours(config('sigap.data_basi_jam') + 1)]);

        $this->assertTrue($baru->belumDiperbarui());
        $this->assertFalse($baru->dataLama());
        $this->assertFalse($segar->belumDiperbarui());
        $this->assertFalse($segar->dataLama());
        $this->assertTrue($lama->dataLama());
    }

    public function test_pesanan_relasi_cast_dan_kode(): void
    {
        $warga = $this->buatWarga();
        $pesanan = Pesanan::factory()->create(['user_id' => $warga->id]);
        PesananLog::create(['pesanan_id' => $pesanan->id, 'ke_status' => PesananStatus::Menunggu, 'oleh_id' => $warga->id, 'peran' => Role::Warga]);

        $pesanan = $pesanan->fresh();
        $this->assertSame(PesananStatus::Menunggu, $pesanan->status);
        $this->assertMatchesRegularExpression('/^SGP-\d{6}$/', $pesanan->kode);
        $this->assertTrue($pesanan->pemohon->is($warga));
        $this->assertCount(1, $pesanan->log);
        $this->assertSame(Role::Warga, $pesanan->log->first()->peran);
        $this->assertNotNull($pesanan->log->first()->created_at);
    }

    public function test_scope_aktif_dan_prioritas_pesanan(): void
    {
        $biasaLama = Pesanan::factory()->create(['created_at' => now()->subHour()]);
        $gawatBaru = Pesanan::factory()->gawat()->create(['created_at' => now()]);
        $biasaBaru = Pesanan::factory()->create(['created_at' => now()]);
        Pesanan::factory()->berstatus(PesananStatus::Selesai)->create();
        Pesanan::factory()->berstatus(PesananStatus::Ditolak)->create();

        $this->assertSame(3, Pesanan::aktif()->count());
        $this->assertSame(
            [$gawatBaru->id, $biasaLama->id, $biasaBaru->id],
            Pesanan::aktif()->prioritas()->pluck('id')->all()
        );
    }

    public function test_sopir_bertugas_bila_punya_pesanan_aktif(): void
    {
        $sopir = $this->buatSopir();
        $this->assertFalse($sopir->sedangBertugas());

        Pesanan::factory()->berstatus(PesananStatus::Selesai)->create(['sopir_id' => $sopir->id]);
        $this->assertFalse($sopir->sedangBertugas());

        Pesanan::factory()->berstatus(PesananStatus::Diterima)->create(['sopir_id' => $sopir->id]);
        $this->assertTrue($sopir->sedangBertugas());
    }
}
