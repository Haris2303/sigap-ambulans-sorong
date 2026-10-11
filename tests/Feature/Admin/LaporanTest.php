<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisLayanan;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\LaporanPesanan;
use App\Services\PesananWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
        $this->admin = $this->buatAdmin();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Pesanan selesai: dibuat 09:00, ditugaskan 09:10, selesai 10:10 pada tanggal $hari. */
    private function pesananSelesai(string $hari, Faskes $faskes, string $pasien = 'Pasien Laporan'): Pesanan
    {
        $alur = new PesananWorkflow;
        $warga = $this->buatWarga();
        $sopir = User::factory()->sopir($faskes)->online()->create();
        $ambulans = Ambulans::factory()->create(['faskes_id' => $faskes->id, 'sopir_id' => $sopir->id]);

        Carbon::setTestNow(Carbon::parse($hari.' 09:00:00'));
        $pesanan = $alur->buat($warga, [
            'nama_pasien' => $pasien, 'no_telepon_kontak' => '081200000000', 'jenis_layanan' => JenisLayanan::Darurat,
            'tingkat_darurat' => 'biasa', 'lokasi_jemput' => 'Jalan Uji',
        ]);
        Carbon::setTestNow(Carbon::parse($hari.' 09:05:00'));
        $alur->verifikasi($pesanan, $this->admin);
        Carbon::setTestNow(Carbon::parse($hari.' 09:10:00'));
        $alur->tugaskan($pesanan, $this->admin, $ambulans, $sopir);
        Carbon::setTestNow(Carbon::parse($hari.' 09:12:00'));
        $alur->terima($pesanan, $sopir);
        Carbon::setTestNow(Carbon::parse($hari.' 09:20:00'));
        $alur->mulaiPerjalanan($pesanan, $sopir);
        Carbon::setTestNow(Carbon::parse($hari.' 10:10:00'));
        $alur->selesaikan($pesanan, $sopir);
        Carbon::setTestNow();

        return $pesanan->fresh();
    }

    public function test_laporan_menghitung_per_status_jenis_faskes_respons_dan_durasi(): void
    {
        $faskes = Faskes::factory()->create(['nama' => 'Faskes Laporan']);
        $hari = now()->subDays(2)->toDateString();
        $this->pesananSelesai($hari, $faskes);
        $this->pesananSelesai($hari, $faskes);
        Pesanan::factory()->create(['created_at' => now()->subDay()]);

        $data = LaporanPesanan::periodeAwal()->ringkas();

        $this->assertSame(3, $data['total']);
        $this->assertSame(2, $data['per_status']['Selesai']);
        $this->assertSame(1, $data['per_status']['Menunggu Verifikasi']);
        $this->assertSame(0, $data['per_status']['Ditolak']);
        $this->assertSame(3, $data['per_jenis']['Darurat']);
        $this->assertSame(['Faskes Laporan' => 2], $data['per_faskes']);
        $this->assertSame(2, $data['respons']['jumlah']);
        $this->assertSame(600, $data['respons']['rata']);
        $this->assertSame(3600, $data['durasi']['rata']);
        $this->assertSame(3600, $data['durasi']['terlama']);
    }

    public function test_format_durasi(): void
    {
        $this->assertSame('-', LaporanPesanan::formatDurasi(null));
        $this->assertSame('kurang dari 1 menit', LaporanPesanan::formatDurasi(20));
        $this->assertSame('10 menit', LaporanPesanan::formatDurasi(600));
        $this->assertSame('1 jam 5 menit', LaporanPesanan::formatDurasi(3900));
    }

    public function test_halaman_laporan_dan_filter_periode(): void
    {
        $faskes = Faskes::factory()->create(['nama' => 'Faskes Halaman']);
        $lama = now()->subDays(20)->toDateString();
        $this->pesananSelesai($lama, $faskes, 'Pasien Lama');
        $this->pesananSelesai(now()->subDay()->toDateString(), $faskes, 'Pasien Baru');

        $this->actingAs($this->admin)->get(route('admin.laporan.index'))
            ->assertOk()->assertSee('Pesanan per status')->assertSee('Faskes Halaman')->assertSee('10 menit')->assertSee('1 jam 0 menit');

        $respons = $this->get(route('admin.laporan.index', ['dari' => now()->subDays(3)->toDateString(), 'sampai' => now()->toDateString()]));
        $this->assertSame(1, $respons->viewData('data')['total']);
    }

    public function test_validasi_periode(): void
    {
        $this->actingAs($this->admin)->get(route('admin.laporan.index', ['dari' => '2026-10-10', 'sampai' => '2026-10-01']))
            ->assertSessionHasErrors('sampai');
        $this->get(route('admin.laporan.index', ['dari' => 'bukan-tanggal']))->assertSessionHasErrors('dari');
    }

    public function test_laporan_kosong_tidak_error(): void
    {
        $this->actingAs($this->admin)->get(route('admin.laporan.index'))
            ->assertOk()->assertSee('Belum ada penugasan pada periode ini.');
    }

    public function test_unduh_pdf(): void
    {
        $this->pesananSelesai(now()->subDay()->toDateString(), Faskes::factory()->create());

        $respons = $this->actingAs($this->admin)->get(route('admin.laporan.pdf'));

        $respons->assertOk();
        $this->assertStringContainsString('application/pdf', $respons->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $respons->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $respons->getContent());
    }

    public function test_unduh_csv_berisi_header_dan_baris_serta_aman_dari_rumus(): void
    {
        $faskes = Faskes::factory()->create(['nama' => 'Faskes Csv']);
        $this->pesananSelesai(now()->subDay()->toDateString(), $faskes, '=HYPERLINK("x")');

        $respons = $this->actingAs($this->admin)->get(route('admin.laporan.csv'));
        $isi = $respons->streamedContent();

        $respons->assertOk();
        $this->assertStringContainsString('text/csv', $respons->headers->get('Content-Type'));
        $this->assertStringStartsWith("\xEF\xBB\xBF", $isi);
        $baris = array_map('str_getcsv', array_filter(explode("\n", substr($isi, 3))));
        $this->assertSame('Kode', $baris[0][0]);
        $this->assertSame('Durasi layanan (menit)', $baris[0][12]);
        $this->assertCount(2, $baris);
        $this->assertMatchesRegularExpression('/^SGP-\d{6}$/', $baris[1][0]);
        $this->assertSame("'=HYPERLINK(\"x\")", $baris[1][2]);
        $this->assertSame('Selesai', $baris[1][5]);
        $this->assertSame('Faskes Csv', $baris[1][8]);
        $this->assertSame('10', $baris[1][11]);
        $this->assertSame('60', $baris[1][12]);
    }

    public function test_laporan_hanya_untuk_dinkes(): void
    {
        $this->get(route('admin.laporan.index'))->assertRedirect(route('login'));

        foreach ([$this->buatWarga(), $this->buatPetugas(), $this->buatSopir()] as $user) {
            $this->actingAs($user);
            $this->get(route('admin.laporan.index'))->assertForbidden();
            $this->get(route('admin.laporan.pdf'))->assertForbidden();
            $this->get(route('admin.laporan.csv'))->assertForbidden();
        }
    }
}
