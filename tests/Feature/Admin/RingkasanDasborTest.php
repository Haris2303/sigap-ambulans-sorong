<?php

namespace Tests\Feature\Admin;

use App\Enums\PeriodeDasbor;
use App\Enums\PesananStatus;
use App\Enums\TabPesanan;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\PesananLog;
use App\Services\RingkasanDasbor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

/** Data dasbor Dinkes arah B (docs/RETEMA_B.md bagian 8.1): hitungan, periode, zona waktu, grafik, tab, dan batas query. */
class RingkasanDasborTest extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function data(TabPesanan $tab = TabPesanan::Semua, PeriodeDasbor $periode = PeriodeDasbor::Tujuh): array
    {
        return app(RingkasanDasbor::class)->ambil($tab, $periode);
    }

    private function pesanan(PesananStatus $status, string $dibuat, array $atribut = []): Pesanan
    {
        return Pesanan::factory()->berstatus($status)->create(['created_at' => $dibuat] + $atribut);
    }

    private function log(Pesanan $pesanan, PesananStatus $ke, string $pada): void
    {
        $langkah = PesananLog::create(['pesanan_id' => $pesanan->id, 'ke_status' => $ke]);
        $langkah->forceFill(['created_at' => $pada])->save();
    }

    // ---------- bentuk ----------

    public function test_hasil_berupa_array_tegas_dengan_kunci_tetap(): void
    {
        $data = $this->data();

        $this->assertSame(['periode', 'diperbarui', 'statistik', 'sparkline', 'ketersediaan', 'grafik', 'donat', 'terbaru'], array_keys($data));
        $this->assertSame(
            ['pesanan_menunggu', 'pesanan_menunggu_gawat', 'pesanan_menunggu_tertua_menit', 'pesanan_terverifikasi', 'pesanan_berjalan', 'pesanan_selesai', 'selesai_delta'],
            array_keys($data['statistik']),
        );
        $this->assertSame(['pesanan_terverifikasi', 'pesanan_berjalan', 'pesanan_selesai'], array_keys($data['sparkline']));
        $this->assertSame(['ambulans', 'bed_igd', 'bed_rawat_inap', 'dokter'], array_keys($data['ketersediaan']));
        foreach ($data['ketersediaan'] as $k) {
            $this->assertSame(['tersedia', 'total'], array_keys($k));
        }
        $this->assertSame(['labels', 'tick_step', 'tooltip_titles', 'masuk', 'pesanan_selesai'], array_keys($data['grafik']));
        $this->assertSame(['segments', 'total'], array_keys($data['donat']));
        $this->assertSame(['tab', 'baris', 'jumlah_hari_ini'], array_keys($data['terbaru']));
        $this->assertSame(PeriodeDasbor::Tujuh, $data['periode']);
        $this->assertMatchesRegularExpression('/^\d{2}\.\d{2}$/', $data['diperbarui']);
    }

    // ---------- statistik saat ini (tidak ikut periode) ----------

    public function test_statistik_menghitung_status_dan_berjalan_mencakup_semua_status_aktif(): void
    {
        $this->pesanan(PesananStatus::Menunggu, '2026-10-11 08:00:00');
        $this->pesanan(PesananStatus::Menunggu, '2026-10-11 08:10:00');
        $this->pesanan(PesananStatus::Terverifikasi, '2026-10-11 08:20:00');
        $this->pesanan(PesananStatus::Ditugaskan, '2026-10-11 08:30:00');
        $this->pesanan(PesananStatus::Diterima, '2026-10-11 08:40:00');
        $this->pesanan(PesananStatus::DalamPerjalanan, '2026-10-11 08:50:00');
        $this->pesanan(PesananStatus::Ditolak, '2026-10-11 09:00:00');
        $this->pesanan(PesananStatus::Dibatalkan, '2026-10-11 09:10:00');

        $statistik = $this->data()['statistik'];

        $this->assertSame(2, $statistik['pesanan_menunggu']);
        $this->assertSame(0, $statistik['pesanan_menunggu_gawat']);
        $this->assertSame(1, $statistik['pesanan_terverifikasi']);
        $this->assertSame(3, $statistik['pesanan_berjalan']);
    }

    public function test_menunggu_tertua_dalam_menit_dan_nol_bila_tidak_ada_yang_menunggu(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 10:00:00', 'Asia/Jayapura'));
        $this->pesanan(PesananStatus::Menunggu, '2026-10-11 09:30:00'); // 30 menit
        $this->pesanan(PesananStatus::Menunggu, '2026-10-11 09:45:00'); // 15 menit, bukan yang tertua
        $this->pesanan(PesananStatus::Terverifikasi, '2026-10-11 08:00:00'); // status lain, tidak dihitung

        $this->assertSame(30, $this->data()['statistik']['pesanan_menunggu_tertua_menit']);

        Pesanan::query()->delete();
        $this->assertSame(0, $this->data()['statistik']['pesanan_menunggu_tertua_menit']);
    }

    // ---------- selesai per periode dan delta ----------

    public function test_selesai_periode_hari_dan_delta_dibanding_kemarin(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 15:00:00', 'Asia/Jayapura'));
        $this->pesanan(PesananStatus::Selesai, '2026-10-11 08:00:00', ['selesai_pada' => '2026-10-11 09:00:00']);
        $this->pesanan(PesananStatus::Selesai, '2026-10-11 08:00:00', ['selesai_pada' => '2026-10-11 10:00:00']);
        // Kemarin pada jam yang sebanding (sebelum 15.00): dua pesanan selesai.
        $this->pesanan(PesananStatus::Selesai, '2026-10-10 08:00:00', ['selesai_pada' => '2026-10-10 09:00:00']);
        $this->pesanan(PesananStatus::Selesai, '2026-10-10 08:00:00', ['selesai_pada' => '2026-10-10 14:00:00']);
        // Kemarin setelah jam 15.00: tidak sebanding, tidak dihitung.
        $this->pesanan(PesananStatus::Selesai, '2026-10-10 08:00:00', ['selesai_pada' => '2026-10-10 16:00:00']);

        $statistik = $this->data(periode: PeriodeDasbor::Hari)['statistik'];

        $this->assertSame(2, $statistik['pesanan_selesai']);
        $this->assertSame(0, $statistik['selesai_delta']);
    }

    public function test_selesai_periode_7_hari_dan_delta_naik_turun(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 20:00:00', 'Asia/Jayapura'));
        // 7 hari terakhir (5 sampai 11 Okt): tiga selesai.
        $this->pesanan(PesananStatus::Selesai, '2026-10-05 08:00:00', ['selesai_pada' => '2026-10-05 09:00:00']);
        $this->pesanan(PesananStatus::Selesai, '2026-10-08 08:00:00', ['selesai_pada' => '2026-10-08 09:00:00']);
        $this->pesanan(PesananStatus::Selesai, '2026-10-11 08:00:00', ['selesai_pada' => '2026-10-11 09:00:00']);
        // 7 hari sebelumnya (28 Sep sampai 4 Okt): satu selesai, di luar rentang 7 hari sekarang.
        $this->pesanan(PesananStatus::Selesai, '2026-10-01 08:00:00', ['selesai_pada' => '2026-10-01 09:00:00']);

        $statistik = $this->data(periode: PeriodeDasbor::Tujuh)['statistik'];

        $this->assertSame(3, $statistik['pesanan_selesai']);
        $this->assertSame(2, $statistik['selesai_delta']); // 3 - 1
    }

    // ---------- sparkline (7 hari, tidak ikut periode) ----------

    public function test_sparkline_dari_pesanan_log_tujuh_hari_terakhir(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 20:00:00', 'Asia/Jayapura'));
        $p = Pesanan::factory()->create(['created_at' => '2026-10-05 08:00:00']);
        $this->log($p, PesananStatus::Terverifikasi, '2026-10-05 09:00:00');
        $this->log($p, PesananStatus::Ditugaskan, '2026-10-07 09:00:00');
        $this->log($p, PesananStatus::Selesai, '2026-10-11 09:00:00');
        // Di luar jendela 7 hari (sebelum 5 Okt): tidak dihitung.
        $this->log($p, PesananStatus::Terverifikasi, '2026-09-20 09:00:00');

        $sparkline = $this->data(periode: PeriodeDasbor::TigaPuluh)['sparkline'];

        $this->assertCount(7, $sparkline['pesanan_terverifikasi']);
        $this->assertSame([1, 0, 0, 0, 0, 0, 0], $sparkline['pesanan_terverifikasi']); // 5 Okt
        $this->assertSame([0, 0, 1, 0, 0, 0, 0], $sparkline['pesanan_berjalan']); // 7 Okt
        $this->assertSame([0, 0, 0, 0, 0, 0, 1], $sparkline['pesanan_selesai']); // 11 Okt
    }

    // ---------- ketersediaan (tersedia Faskes aktif, total semua Faskes) ----------

    public function test_ketersediaan_tersedia_hanya_faskes_aktif_dan_total_semua_faskes(): void
    {
        $aktif = Faskes::factory()->diperbarui(4, 9)->create();
        Faskes::factory()->diperbarui(1, 1)->nonaktif()->create(['bed_igd_total' => 2, 'bed_rawat_inap_total' => 3]);
        Ambulans::factory()->count(2)->create(['faskes_id' => $aktif->id]);
        Ambulans::factory()->servis()->create(['faskes_id' => $aktif->id]);

        $ketersediaan = $this->data()['ketersediaan'];

        $this->assertSame(['tersedia' => 2, 'total' => 3], $ketersediaan['ambulans']);
        $this->assertSame(4, $ketersediaan['bed_igd']['tersedia']);
        $this->assertSame($aktif->bed_igd_total + 2, $ketersediaan['bed_igd']['total']);
        $this->assertSame(9, $ketersediaan['bed_rawat_inap']['tersedia']);
        $this->assertSame($aktif->bed_rawat_inap_total + 3, $ketersediaan['bed_rawat_inap']['total']);
        $this->assertSame($aktif->dokter_umum_tersedia + $aktif->dokter_spesialis_tersedia, $ketersediaan['dokter']['tersedia']);
    }

    public function test_ketersediaan_nol_saat_tidak_ada_faskes(): void
    {
        $kosong = ['tersedia' => 0, 'total' => 0];
        $this->assertSame(
            ['ambulans' => $kosong, 'bed_igd' => $kosong, 'bed_rawat_inap' => $kosong, 'dokter' => $kosong],
            $this->data()['ketersediaan'],
        );
    }

    // ---------- grafik garis dan donat per periode ----------

    public function test_grafik_7_hari_label_hari_dan_tick_step_1(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 15:00:00', 'Asia/Jayapura'));
        $this->pesanan(PesananStatus::Menunggu, '2026-10-05 08:00:00');
        $this->pesanan(PesananStatus::Selesai, '2026-10-05 08:00:00', ['selesai_pada' => '2026-10-06 08:00:00']);

        $grafik = $this->data(periode: PeriodeDasbor::Tujuh)['grafik'];

        $this->assertSame(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'], $grafik['labels']);
        $this->assertSame(1, $grafik['tick_step']);
        $this->assertSame([2, 0, 0, 0, 0, 0, 0], $grafik['masuk']);
        $this->assertSame([0, 1, 0, 0, 0, 0, 0], $grafik['pesanan_selesai']);
        $this->assertStringContainsString('05/10', $grafik['tooltip_titles'][0]);
    }

    public function test_grafik_hari_ini_per_jam_dengan_tick_step_3(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 15:00:00', 'Asia/Jayapura'));
        $this->pesanan(PesananStatus::Menunggu, '2026-10-11 09:00:00');
        $this->pesanan(PesananStatus::Selesai, '2026-10-11 10:00:00', ['selesai_pada' => '2026-10-11 11:30:00']);
        // Kemarin, tidak ikut.
        $this->pesanan(PesananStatus::Menunggu, '2026-10-10 09:00:00');

        $grafik = $this->data(periode: PeriodeDasbor::Hari)['grafik'];

        $this->assertCount(24, $grafik['labels']);
        $this->assertSame(['00', '01', '02'], array_slice($grafik['labels'], 0, 3));
        $this->assertSame(3, $grafik['tick_step']);
        $this->assertSame(1, $grafik['masuk'][9]);
        $this->assertSame(1, $grafik['pesanan_selesai'][11]);
        // Dua pesanan dibuat hari ini (menunggu jam 9 dan selesai jam 10); kemarin tidak ikut.
        $this->assertSame(2, array_sum($grafik['masuk']));
    }

    public function test_grafik_30_hari_label_tanggal_dan_tick_step_5(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 15:00:00', 'Asia/Jayapura'));

        $grafik = $this->data(periode: PeriodeDasbor::TigaPuluh)['grafik'];

        $this->assertCount(30, $grafik['labels']);
        $this->assertSame('12/09', $grafik['labels'][0]);
        $this->assertSame('11/10', $grafik['labels'][29]);
        $this->assertSame(5, $grafik['tick_step']);
    }

    public function test_donat_menghitung_status_pesanan_dibuat_dalam_periode_urutan_enum(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 20:00:00', 'Asia/Jayapura'));
        // Di luar periode 7 hari (sebelum 5 Okt): tidak dihitung.
        $this->pesanan(PesananStatus::Dibatalkan, '2026-09-01 08:00:00');
        foreach ([PesananStatus::Selesai, PesananStatus::Selesai, PesananStatus::Selesai, PesananStatus::Selesai, PesananStatus::Selesai,
            PesananStatus::Dibatalkan, PesananStatus::Dibatalkan, PesananStatus::Ditolak, PesananStatus::Terverifikasi, PesananStatus::DalamPerjalanan] as $i => $status) {
            $this->pesanan($status, '2026-10-0'.($i % 5 + 5).' 10:00:00');
        }

        $donat = $this->data()['donat'];

        $this->assertSame(10, $donat['total']);
        // Nilai terbesar dulu; nilai seri (1) mengikuti urutan Enum: Terverifikasi, Dalam perjalanan, Ditolak.
        $this->assertSame(['Selesai', 'Dibatalkan', 'Terverifikasi', 'Dalam perjalanan', 'Ditolak'], array_column($donat['segments'], 'label'));
        $this->assertSame([5, 2, 1, 1, 1], array_column($donat['segments'], 'value'));
        $this->assertSame(['chart-mint', 'chart-slate', 'primary', 'tertiary', 'chart-coral'], array_column($donat['segments'], 'color'));
    }

    public function test_donat_kosong_tanpa_pesanan_dalam_periode(): void
    {
        $this->assertSame(['segments' => [], 'total' => 0], $this->data()['donat']);
    }

    // ---------- pesanan terbaru (hari ini, tidak ikut periode) ----------

    public function test_pesanan_terbaru_hanya_hari_ini_diurutkan_terbaru_dan_dibatasi_lima_baris(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 20:00:00', 'Asia/Jayapura'));
        for ($i = 1; $i <= 7; $i++) {
            $this->pesanan(PesananStatus::Menunggu, sprintf('2026-10-11 %02d:00:00', $i));
        }
        // Kemarin: tidak ikut jendela "hari ini".
        $this->pesanan(PesananStatus::Menunggu, '2026-10-10 10:00:00');

        $terbaru = $this->data()['terbaru'];

        $this->assertSame(TabPesanan::Semua, $terbaru['tab']);
        $this->assertSame(7, $terbaru['jumlah_hari_ini']);
        $this->assertCount(5, $terbaru['baris']);
        $this->assertSame(
            ['07', '06', '05', '04', '03'],
            $terbaru['baris']->map(fn (Pesanan $p) => $p->created_at->format('H'))->all(),
        );
    }

    public function test_tab_menyaring_dalam_jendela_hari_ini(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 20:00:00', 'Asia/Jayapura'));
        $this->pesanan(PesananStatus::Menunggu, '2026-10-11 01:00:00');
        $this->pesanan(PesananStatus::Terverifikasi, '2026-10-11 02:00:00');
        $this->pesanan(PesananStatus::Ditugaskan, '2026-10-11 03:00:00');
        $this->pesanan(PesananStatus::Selesai, '2026-10-11 04:00:00');

        $perlu = $this->data(TabPesanan::PerluTindakan)['terbaru'];
        $this->assertEqualsCanonicalizing([PesananStatus::Menunggu, PesananStatus::Terverifikasi], $perlu['baris']->pluck('status')->all());
        $this->assertSame(4, $perlu['jumlah_hari_ini']);

        $berjalan = $this->data(TabPesanan::Berjalan)['terbaru'];
        $this->assertSame([PesananStatus::Ditugaskan], $berjalan['baris']->pluck('status')->all());
    }

    public function test_tab_tanpa_pesanan_menghasilkan_baris_kosong(): void
    {
        $this->pesanan(PesananStatus::Selesai, now()->toDateTimeString());

        $berjalan = $this->data(TabPesanan::Berjalan)['terbaru'];

        $this->assertCount(0, $berjalan['baris']);
        $this->assertSame(1, $berjalan['jumlah_hari_ini']);
    }

    public function test_tanpa_pesanan_semua_angka_nol(): void
    {
        $data = $this->data();

        $this->assertSame(0, $data['statistik']['pesanan_menunggu']);
        $this->assertSame(0, $data['statistik']['pesanan_selesai']);
        $this->assertSame(0, $data['donat']['total']);
        $this->assertCount(0, $data['terbaru']['baris']);
        $this->assertSame(0, $data['terbaru']['jumlah_hari_ini']);
    }

    // ---------- batas query ----------

    private function hitungQuery(callable $aksi): int
    {
        $jumlah = 0;
        DB::listen(function () use (&$jumlah) {
            $jumlah++;
        });
        $aksi();

        return $jumlah;
    }

    public function test_seluruh_dasbor_memakai_paling_banyak_empat_belas_query_tanpa_n_plus_satu(): void
    {
        $faskes = Faskes::factory()->diperbarui(4, 9)->create();
        Ambulans::factory()->count(2)->create(['faskes_id' => $faskes->id]);
        $admin = $this->buatAdmin();

        $sedikit = $this->hitungQuery(fn () => $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk());

        Pesanan::factory()->count(3)->create();
        Pesanan::factory()->berstatus(PesananStatus::Selesai)->count(5)->create(['selesai_pada' => now()]);
        Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->count(10)->create();
        $banyak = $this->hitungQuery(fn () => $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'berjalan']))->assertOk());

        $this->assertLessThanOrEqual(14, $sedikit, 'Dasbor dengan sedikit data: '.$sedikit.' query.');
        $this->assertLessThanOrEqual(14, $banyak, 'Dasbor dengan banyak data: '.$banyak.' query.');
        $this->assertSame($sedikit, $banyak, 'Jumlah query tidak boleh bertambah mengikuti jumlah data (N+1).');
    }

    public function test_layanan_data_sendiri_memakai_paling_banyak_sepuluh_query(): void
    {
        Pesanan::factory()->count(12)->create();
        Faskes::factory()->diperbarui(3, 4)->create();

        $jumlah = $this->hitungQuery(fn () => $this->data(TabPesanan::PerluTindakan));

        $this->assertLessThanOrEqual(10, $jumlah);
    }
}
