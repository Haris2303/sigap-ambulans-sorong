<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisLayanan;
use App\Enums\PesananStatus;
use App\Enums\TabPesanan;
use App\Enums\TingkatDarurat;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Services\RingkasanDasbor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

/**
 * Data dasbor Dinkes (docs/DESAIN_DASBOR_MATERIAL.md bagian 6): hitungan, zona waktu, grafik, tab, dan batas query.
 */
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

    private function data(TabPesanan $tab = TabPesanan::Semua): array
    {
        return app(RingkasanDasbor::class)->ambil($tab);
    }

    private function pesanan(PesananStatus $status, string $dibuat, array $atribut = []): Pesanan
    {
        return Pesanan::factory()->berstatus($status)->create(['created_at' => $dibuat] + $atribut);
    }

    // ---------- konstanta dan bentuk ----------

    public function test_jumlah_pesanan_terbaru_adalah_satu_konstanta(): void
    {
        $this->assertSame(10, RingkasanDasbor::DASBOR_JUMLAH_PESANAN_TERBARU);
        $this->assertSame(5, RingkasanDasbor::DASBOR_BARIS_TABEL);
    }

    public function test_hasil_berupa_array_tegas_dengan_kunci_tetap(): void
    {
        $data = $this->data();

        $this->assertSame(['statistik', 'ketersediaan', 'batang', 'donat', 'terbaru'], array_keys($data));
        $this->assertSame(['pesanan_menunggu', 'pesanan_menunggu_gawat', 'pesanan_terverifikasi', 'pesanan_berjalan', 'pesanan_selesai_hari_ini'], array_keys($data['statistik']));
        $this->assertSame(['ambulans_tersedia', 'bed_igd_tersedia', 'bed_rawat_inap_tersedia', 'dokter_tersedia'], array_keys($data['ketersediaan']));
        $this->assertSame(['labels', 'values', 'highlight', 'rentang'], array_keys($data['batang']));
        $this->assertSame(['segments', 'total'], array_keys($data['donat']));
        $this->assertSame(['tab', 'baris', 'jumlah_tampil', 'jumlah_jendela'], array_keys($data['terbaru']));
        foreach ($data['statistik'] + $data['ketersediaan'] as $angka) {
            $this->assertIsInt($angka);
        }
    }

    // ---------- statistik ----------

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
        $this->assertSame(0, $statistik['pesanan_selesai_hari_ini']);
    }

    public function test_selesai_hari_ini_memakai_zona_waktu_aplikasi_asia_jayapura(): void
    {
        $this->assertSame('Asia/Jayapura', config('app.timezone'));
        // 11 Okt 2026 00:20 WIT: awal hari menurut zona aplikasi adalah 00:00 WIT, bukan 00:00 UTC.
        Carbon::setTestNow(Carbon::parse('2026-10-11 00:20:00', 'Asia/Jayapura'));

        $this->pesanan(PesananStatus::Selesai, '2026-10-10 20:00:00', ['selesai_pada' => '2026-10-10 23:50:00']);
        $this->pesanan(PesananStatus::Selesai, '2026-10-10 23:00:00', ['selesai_pada' => '2026-10-11 00:05:00']);
        $this->pesanan(PesananStatus::Selesai, '2026-10-10 23:30:00', ['selesai_pada' => '2026-10-11 00:15:00']);
        $this->pesanan(PesananStatus::Ditolak, '2026-10-10 23:30:00', ['selesai_pada' => '2026-10-11 00:10:00']);

        $this->assertSame(2, $this->data()['statistik']['pesanan_selesai_hari_ini']);

        // Satu menit sebelum tengah malam hari itu masih hari yang sama; sehari kemudian tidak lagi.
        Carbon::setTestNow(Carbon::parse('2026-10-11 23:59:00', 'Asia/Jayapura'));
        $this->assertSame(2, $this->data()['statistik']['pesanan_selesai_hari_ini']);

        Carbon::setTestNow(Carbon::parse('2026-10-12 00:01:00', 'Asia/Jayapura'));
        $this->assertSame(0, $this->data()['statistik']['pesanan_selesai_hari_ini']);
    }

    // ---------- ketersediaan ----------

    public function test_ketersediaan_menjumlahkan_faskes_aktif_dan_ambulans_tersedia(): void
    {
        $faskes = Faskes::factory()->diperbarui(4, 9)->create();
        Faskes::factory()->diperbarui(1, 1)->nonaktif()->create();
        Ambulans::factory()->count(2)->create(['faskes_id' => $faskes->id]);
        Ambulans::factory()->servis()->create(['faskes_id' => $faskes->id]);

        $ketersediaan = $this->data()['ketersediaan'];

        $this->assertSame(2, $ketersediaan['ambulans_tersedia']);
        $this->assertSame(4, $ketersediaan['bed_igd_tersedia']);
        $this->assertSame(9, $ketersediaan['bed_rawat_inap_tersedia']);
        $this->assertSame($faskes->dokter_umum_tersedia + $faskes->dokter_spesialis_tersedia, $ketersediaan['dokter_tersedia']);
    }

    public function test_ketersediaan_nol_saat_tidak_ada_faskes(): void
    {
        $this->assertSame(['ambulans_tersedia' => 0, 'bed_igd_tersedia' => 0, 'bed_rawat_inap_tersedia' => 0, 'dokter_tersedia' => 0], $this->data()['ketersediaan']);
    }

    // ---------- grafik batang ----------

    public function test_batang_tujuh_hari_termasuk_hari_ini_dengan_hari_kosong_bernilai_nol(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 15:00:00', 'Asia/Jayapura'));
        $this->pesanan(PesananStatus::Selesai, '2026-10-05 00:00:00');   // paling awal yang masuk (Sen)
        $this->pesanan(PesananStatus::Selesai, '2026-10-04 23:59:59');   // satu detik di luar rentang
        $this->pesanan(PesananStatus::Menunggu, '2026-10-11 14:00:00');
        $this->pesanan(PesananStatus::Menunggu, '2026-10-11 09:00:00');

        $batang = $this->data()['batang'];

        $this->assertSame([['Sen', '05/10'], ['Sel', '06/10'], ['Rab', '07/10'], ['Kam', '08/10'], ['Jum', '09/10'], ['Sab', '10/10'], ['Min', '11/10']], $batang['labels']);
        $this->assertSame([1, 0, 0, 0, 0, 0, 2], $batang['values']);
        $this->assertSame(6, $batang['highlight']);
    }

    public function test_singkatan_hari_tidak_bergantung_pada_bahasa_aplikasi(): void
    {
        app()->setLocale('en');
        Carbon::setTestNow(Carbon::parse('2026-10-11 10:00:00', 'Asia/Jayapura'));

        $hari = array_column($this->data()['batang']['labels'], 0);

        $this->assertSame(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'], $hari);
    }

    public function test_rentang_tanggal_di_bulan_yang_sama(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 10:00:00', 'Asia/Jayapura'));

        $this->assertSame('5 sampai 11 Okt 2026', $this->data()['batang']['rentang']);
    }

    public function test_rentang_tanggal_melewati_bulan_dan_tahun(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-04 10:00:00', 'Asia/Jayapura'));
        $this->assertSame('28 Sep sampai 4 Okt 2026', $this->data()['batang']['rentang']);

        Carbon::setTestNow(Carbon::parse('2027-01-04 10:00:00', 'Asia/Jayapura'));
        $this->assertSame('29 Des 2026 sampai 4 Jan 2027', $this->data()['batang']['rentang']);
    }

    // ---------- donat dan pesanan terbaru ----------

    public function test_donat_menghitung_status_dari_sepuluh_pesanan_terbaru_dalam_urutan_enum(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 20:00:00', 'Asia/Jayapura'));
        // Pesanan paling lama (di luar sepuluh terbaru) tidak dihitung.
        $this->pesanan(PesananStatus::Dibatalkan, '2026-09-01 08:00:00');
        foreach ([PesananStatus::Selesai, PesananStatus::Selesai, PesananStatus::Selesai, PesananStatus::Selesai, PesananStatus::Selesai,
            PesananStatus::Dibatalkan, PesananStatus::Dibatalkan, PesananStatus::Ditolak, PesananStatus::Terverifikasi, PesananStatus::DalamPerjalanan] as $i => $status) {
            $this->pesanan($status, '2026-10-0'.($i % 9 + 1).' 10:00:00');
        }

        $donat = $this->data()['donat'];

        $this->assertSame(10, $donat['total']);
        $this->assertSame(array_map(fn (PesananStatus $s) => $s->label(), PesananStatus::cases()), array_column($donat['segments'], 'label'));
        $this->assertSame([0, 1, 0, 0, 1, 5, 1, 2], array_column($donat['segments'], 'value'));
        $this->assertSame(
            ['warning', 'primary', 'chart-sky', 'chart-sky', 'chart-sky', 'success', 'error', 'outline'],
            array_column($donat['segments'], 'color'),
        );
    }

    public function test_pesanan_terbaru_diurutkan_dari_yang_terbaru_dan_dibatasi_lima_baris(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->pesanan(PesananStatus::Menunggu, sprintf('2026-10-%02d 10:00:00', $i));
        }

        $terbaru = $this->data()['terbaru'];

        $this->assertSame(TabPesanan::Semua, $terbaru['tab']);
        $this->assertCount(5, $terbaru['baris']);
        $this->assertSame(5, $terbaru['jumlah_tampil']);
        $this->assertSame(10, $terbaru['jumlah_jendela']);
        $this->assertSame(
            ['2026-10-12', '2026-10-11', '2026-10-10', '2026-10-09', '2026-10-08'],
            $terbaru['baris']->map(fn (Pesanan $p) => $p->created_at->toDateString())->all(),
        );
    }

    public function test_urutan_seri_waktu_sama_mengikuti_id_terbesar_dulu(): void
    {
        $pertama = $this->pesanan(PesananStatus::Menunggu, '2026-10-11 10:00:00');
        $kedua = $this->pesanan(PesananStatus::Menunggu, '2026-10-11 10:00:00');

        $this->assertSame([$kedua->id, $pertama->id], $this->data()['terbaru']['baris']->pluck('id')->all());
    }

    public function test_tab_menyaring_dalam_jendela_sepuluh_pesanan_terbaru(): void
    {
        $this->pesanan(PesananStatus::Menunggu, '2026-10-11 01:00:00');
        $this->pesanan(PesananStatus::Terverifikasi, '2026-10-11 02:00:00');
        $this->pesanan(PesananStatus::Ditugaskan, '2026-10-11 03:00:00');
        $this->pesanan(PesananStatus::DalamPerjalanan, '2026-10-11 04:00:00');
        $this->pesanan(PesananStatus::Selesai, '2026-10-11 05:00:00');
        $this->pesanan(PesananStatus::Ditolak, '2026-10-11 06:00:00');

        $perlu = $this->data(TabPesanan::PerluTindakan)['terbaru'];
        $this->assertSame(TabPesanan::PerluTindakan, $perlu['tab']);
        $this->assertEqualsCanonicalizing([PesananStatus::Menunggu, PesananStatus::Terverifikasi], $perlu['baris']->pluck('status')->all());
        $this->assertSame(2, $perlu['jumlah_tampil']);
        $this->assertSame(6, $perlu['jumlah_jendela']);

        $berjalan = $this->data(TabPesanan::Berjalan)['terbaru'];
        $this->assertEqualsCanonicalizing([PesananStatus::Ditugaskan, PesananStatus::DalamPerjalanan], $berjalan['baris']->pluck('status')->all());
        $this->assertSame(2, $berjalan['jumlah_tampil']);

        $semua = $this->data(TabPesanan::Semua)['terbaru'];
        $this->assertSame(5, $semua['jumlah_tampil']);
        $this->assertSame(6, $semua['jumlah_jendela']);
    }

    public function test_tab_tanpa_pesanan_menghasilkan_baris_kosong(): void
    {
        $this->pesanan(PesananStatus::Selesai, '2026-10-11 05:00:00');

        $berjalan = $this->data(TabPesanan::Berjalan)['terbaru'];

        $this->assertCount(0, $berjalan['baris']);
        $this->assertSame(0, $berjalan['jumlah_tampil']);
        $this->assertSame(1, $berjalan['jumlah_jendela']);
    }

    public function test_tanpa_pesanan_semua_angka_nol_dan_donat_kosong(): void
    {
        $data = $this->data();

        $this->assertSame([0, 0, 0, 0, 0], array_values($data['statistik']));
        $this->assertSame(0, $data['donat']['total']);
        $this->assertCount(7, $data['batang']['values']);
        $this->assertSame(array_fill(0, 7, 0), $data['batang']['values']);
        $this->assertCount(0, $data['terbaru']['baris']);
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

    public function test_seluruh_dasbor_memakai_paling_banyak_sepuluh_query_tanpa_n_plus_satu(): void
    {
        $faskes = Faskes::factory()->diperbarui(4, 9)->create();
        Ambulans::factory()->count(2)->create(['faskes_id' => $faskes->id]);
        $admin = $this->buatAdmin();

        $sedikit = $this->hitungQuery(fn () => $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk());

        Pesanan::factory()->count(3)->create(['tingkat_darurat' => TingkatDarurat::Gawat, 'jenis_layanan' => JenisLayanan::Rujukan]);
        Pesanan::factory()->berstatus(PesananStatus::Selesai)->count(5)->create(['selesai_pada' => now()]);
        Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->count(10)->create();
        $banyak = $this->hitungQuery(fn () => $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'berjalan']))->assertOk());

        $this->assertLessThanOrEqual(10, $sedikit, 'Dasbor dengan sedikit data: '.$sedikit.' query.');
        $this->assertLessThanOrEqual(10, $banyak, 'Dasbor dengan banyak data: '.$banyak.' query.');
        $this->assertSame($sedikit, $banyak, 'Jumlah query tidak boleh bertambah mengikuti jumlah data (N+1).');
    }

    public function test_layanan_data_sendiri_memakai_paling_banyak_enam_query(): void
    {
        Pesanan::factory()->count(12)->create();
        Faskes::factory()->diperbarui(3, 4)->create();

        $jumlah = $this->hitungQuery(fn () => $this->data(TabPesanan::PerluTindakan));

        $this->assertLessThanOrEqual(6, $jumlah);
    }
}
