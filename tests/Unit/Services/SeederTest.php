<?php

namespace Tests\Unit\Services;

use App\Enums\JenisAmbulans;
use App\Enums\JenisFaskes;
use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use Database\Seeders\AkunDemoSeeder;
use Database\Seeders\AmbulansSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\FaskesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    private function hitung(): array
    {
        return [Faskes::count(), Ambulans::count(), User::count(), Pesanan::count()];
    }

    public function test_seeder_idempoten_dua_kali_jumlah_baris_sama(): void
    {
        $this->seed(DatabaseSeeder::class);
        $pertama = $this->hitung();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($pertama, $this->hitung());
    }

    public function test_data_faskes_dan_ambulans_sesuai_wawancara(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(7, Faskes::where('jenis', JenisFaskes::Rs->value)->count());
        $this->assertSame(1, Faskes::where('jenis', JenisFaskes::Klinik->value)->count());
        $this->assertSame(11, Faskes::where('jenis', JenisFaskes::Puskesmas->value)->count());
        $this->assertSame(4, Ambulans::where('jenis', JenisAmbulans::Darurat->value)->count());
        $this->assertSame(18, Ambulans::where('jenis', JenisAmbulans::Transport->value)->count());
        $this->assertSame(22, Ambulans::where('plat_nomor', 'like', 'PB-DEMO-%')->count());
        $this->assertSame(0, Faskes::whereNotNull('lat')->count());
        $this->assertSame(0, Faskes::whereNotNull('ketersediaan_diperbarui_pada')->count());
    }

    public function test_akun_demo_dan_sopir_terhubung_ke_ambulans(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(Role::Admin, User::where('email', 'admin@sigap.test')->first()->role);
        $this->assertSame(Role::Warga, User::where('email', 'warga@sigap.test')->first()->role);
        $this->assertSame(2, User::petugas()->count());
        $this->assertSame(6, User::sopir()->count());
        $this->assertTrue(Hash::check('password', User::where('email', 'admin@sigap.test')->first()->password));

        $sopir = User::where('email', 'sopir.selebesolu@sigap.test')->first();
        $this->assertSame('RSUD Selebesolu', $sopir->faskes->nama);
        $this->assertSame($sopir->faskes_id, $sopir->ambulansTetap->faskes_id);
    }

    public function test_pesanan_demo_tidak_dibuat_di_luar_local(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, Pesanan::count());
    }

    public function test_pesanan_demo_di_local_mencakup_semua_status_dan_tujuh_hari(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        $this->seed(DatabaseSeeder::class);

        $status = Pesanan::pluck('status')->map(fn ($s) => $s->value)->unique()->sort()->values()->all();
        $semua = collect(PesananStatus::cases())->map(fn ($s) => $s->value)->sort()->values()->all();
        $this->assertSame($semua, $status);

        $this->assertGreaterThanOrEqual(10, Pesanan::count());
        $this->assertGreaterThanOrEqual(5, Pesanan::pluck('created_at')->map(fn ($t) => $t->toDateString())->unique()->count());
        $this->assertSame(0, Ambulans::where('status', StatusAmbulans::Bertugas->value)->count() - Pesanan::aktif()->whereNotNull('ambulans_id')->count());
    }

    // ---------- DemoSeeder (docs/RETEMA_B.md "DB" langkah 6) ----------

    public function test_demo_seeder_menolak_di_luar_local(): void
    {
        $this->seed([FaskesSeeder::class, AmbulansSeeder::class, AkunDemoSeeder::class]);

        $this->seed(DemoSeeder::class);

        $this->assertSame(0, Pesanan::count());
    }

    public function test_demo_seeder_di_local_menambah_sekitar_70_pesanan_30_hari_dan_dua_menipis(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        $this->seed([FaskesSeeder::class, AmbulansSeeder::class, AkunDemoSeeder::class]);

        $this->seed(DemoSeeder::class);

        $jumlah = Pesanan::count();
        $this->assertGreaterThanOrEqual(50, $jumlah);
        $this->assertLessThanOrEqual(100, $jumlah);

        // Riwayat status (pesanan_log) terisi untuk setiap pesanan, bukan hanya kolom status.
        $this->assertGreaterThan($jumlah, \App\Models\PesananLog::count());

        // Rentang 30 hari: pesanan tersebar di banyak tanggal berbeda, bukan menumpuk satu hari.
        $this->assertGreaterThanOrEqual(15, Pesanan::pluck('created_at')->map(fn ($t) => $t->toDateString())->unique()->count());

        // Dua sumber daya menipis pada RSUD Selebesolu (docs/referensi-b/B1-dasbor-1440.png).
        $faskes = Faskes::where('nama', 'RSUD Selebesolu')->first();
        $this->assertLessThan(0.12, $faskes->bed_igd_tersedia / $faskes->bed_igd_total);
        $this->assertLessThan(0.12, $faskes->bed_rawat_inap_tersedia / $faskes->bed_rawat_inap_total);
        $this->assertNotNull($faskes->ketersediaan_diperbarui_pada);

        // Semua pesanan lewat PesananWorkflow: tidak ada status di luar Enum, dan hanya menambah (jalankan dua kali, bertambah).
        $this->seed(DemoSeeder::class);
        $this->assertGreaterThan($jumlah, Pesanan::count());
    }
}
