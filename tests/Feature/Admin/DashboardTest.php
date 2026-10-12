<?php

namespace Tests\Feature\Admin;

use App\Enums\PesananStatus;
use App\Enums\TingkatDarurat;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

/** Dasbor Dinkes arah B (docs/RETEMA_B.md bagian 8.1). Hitungan, grafik, dan batas query ada di RingkasanDasborTest. */
class DashboardTest extends TestCase
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

    /** Data demo kecil seperti pratinjau: dua menunggu, satu terverifikasi, tiga berjalan, satu selesai hari ini. */
    private function dataDemo(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 18:00:00', 'Asia/Jayapura'));

        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->create(['created_at' => '2026-10-11 09:00:00', 'nama_pasien' => 'Pasien Satu']);
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->create(['created_at' => '2026-10-11 10:00:00', 'nama_pasien' => 'Pasien Dua']);
        Pesanan::factory()->berstatus(PesananStatus::Terverifikasi)->create(['created_at' => '2026-10-11 11:00:00', 'nama_pasien' => 'Pasien Tiga']);
        Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create(['created_at' => '2026-10-11 12:00:00', 'nama_pasien' => 'Pasien Empat']);
        Pesanan::factory()->berstatus(PesananStatus::Diterima)->create(['created_at' => '2026-10-11 13:00:00', 'nama_pasien' => 'Pasien Lima']);
        Pesanan::factory()->berstatus(PesananStatus::DalamPerjalanan)->create(['created_at' => '2026-10-11 14:00:00', 'nama_pasien' => 'Pasien Enam', 'tingkat_darurat' => TingkatDarurat::Gawat]);
        Pesanan::factory()->berstatus(PesananStatus::Selesai)->create(['created_at' => '2026-10-11 08:00:00', 'selesai_pada' => '2026-10-11 15:00:00', 'nama_pasien' => 'Pasien Tujuh']);

        $faskes = Faskes::factory()->diperbarui(4, 9)->create();
        Faskes::factory()->diperbarui(1, 1)->nonaktif()->create();
        Ambulans::factory()->count(2)->create(['faskes_id' => $faskes->id]);
        Ambulans::factory()->servis()->create(['faskes_id' => $faskes->id]);
    }

    private function dasbor(array $query = []): string
    {
        return $this->actingAs($this->buatAdmin(['name' => 'Admin Dinkes']))->get(route('admin.dashboard', $query))->assertOk()->getContent();
    }

    // ---------- judul dan filter periode ----------

    public function test_judul_halaman_sapaan_dan_subjudul(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 18:00:00', 'Asia/Jayapura'));
        $html = $this->dasbor();

        $this->assertStringContainsString('<h1 class="text-page-title max-lg:text-page-title-s">Selamat malam, Admin</h1>', $html);
        $this->assertStringContainsString('Minggu, 11 Oktober 2026. Tidak ada pesanan yang menunggu Anda.', $html);
    }

    public function test_filter_periode_floating_tabs_default_7_hari_dan_tautan_mempertahankan_tab(): void
    {
        $html = $this->dasbor(['tab' => 'berjalan']);

        $this->assertStringContainsString('aria-label="Filter periode"', $html);
        $this->assertStringContainsString('shadow-control', $html);
        foreach (['Hari ini', '7 hari', '30 hari'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
        // Bawaan 7 hari aktif, dan tautan periode mempertahankan tab yang sedang dipilih.
        $urlTujuhHari = str_replace('&', '&amp;', route('admin.dashboard', ['tab' => 'berjalan', 'periode' => '7']));
        $this->assertMatchesRegularExpression(
            '~<a href="'.preg_quote($urlTujuhHari, '~').'"[^>]*aria-current="page"[^>]*>\s*7 hari\s*</a>~',
            $html,
        );
        $this->assertStringNotContainsString('Kelola ketersediaan', $html);
    }

    public function test_periode_tidak_dikenal_jatuh_ke_7_hari(): void
    {
        $html = $this->dasbor(['periode' => 'ngawur']);

        $this->assertMatchesRegularExpression('~periode=7"[^>]*aria-current="page"~', $html);
    }

    public function test_filter_periode_hari_ini_mengubah_label_kartu_selesai_dan_grafik(): void
    {
        $this->dataDemo();
        $html = $this->dasbor(['periode' => 'hari']);

        $this->assertStringContainsString('Selesai, hari ini', $html);
        $this->assertStringContainsString('Pesanan hari ini', $html);
        $this->assertMatchesRegularExpression('~periode=hari"[^>]*aria-current="page"~', $html);
    }

    // ---------- baris statistik ----------

    public function test_kartu_navy_menunggu_verifikasi_dengan_waktu_tunggu_dan_aksi(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 09:30:00', 'Asia/Jayapura'));
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->create(['created_at' => '2026-10-11 09:00:00']);

        $html = $this->dasbor();

        foreach (['bg-secondary', 'text-stat-hero', 'Menunggu verifikasi', 'pesanan, terlama 30 menit', 'Verifikasi sekarang'] as $teks) {
            $this->assertStringContainsString($teks, $html);
        }
        $this->assertStringContainsString('bg-surface-container-lowest text-secondary', $html);
        $this->assertMatchesRegularExpression('/data-nilai[^>]*>1</', $html);
        $this->assertStringContainsString('href="'.route('admin.pesanan.index', ['tab' => 'perlu-tindakan']).'"', $html);
    }

    public function test_kartu_navy_tanpa_pesanan_menunggu(): void
    {
        $html = $this->dasbor();

        $this->assertStringContainsString('tidak ada pesanan menunggu', $html);
    }

    public function test_tiga_kartu_putih_dengan_sparkline_dan_chip_keterangan(): void
    {
        $this->dataDemo();
        $html = $this->dasbor();

        foreach (['Terverifikasi', 'menunggu penugasan', 'Sedang berjalan', 'ditugaskan sampai di jalan', 'Selesai, 7 hari'] as $teks) {
            $this->assertStringContainsString($teks, $html);
        }
        $this->assertStringContainsString('<svg width="92" height="38"', $html);
        $this->assertMatchesRegularExpression('/data-ringkasan="pesanan_terverifikasi"[\s\S]*?data-nilai[^>]*>1</', $html);
        $this->assertMatchesRegularExpression('/data-ringkasan="pesanan_berjalan"[\s\S]*?data-nilai[^>]*>3</', $html);
        // Delta "+1 dari 7 hari sebelumnya" (satu pesanan selesai hari ini, nol di periode sebelumnya).
        $this->assertStringContainsString('+1 dari 7 hari sebelumnya', $html);
    }

    public function test_kartu_selesai_hari_ini_terhubung_polling_hanya_saat_periode_hari(): void
    {
        $this->dataDemo();

        $tujuh = $this->dasbor();
        $this->assertStringNotContainsString('data-ringkasan="pesanan_selesai_hari_ini"', $tujuh);

        $hari = $this->dasbor(['periode' => 'hari']);
        $this->assertStringContainsString('data-ringkasan="pesanan_selesai_hari_ini"', $hari);
    }

    public function test_kartu_utama_menonjolkan_jumlah_pesanan_gawat_yang_menunggu(): void
    {
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->gawat()->count(2)->create();
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->create();

        $html = $this->dasbor();

        $this->assertMatchesRegularExpression('/data-nilai[^>]*>3</', $html, 'Tiga pesanan menunggu.');
        $this->assertStringContainsString('2</span> Gawat', $html);
        $this->assertStringContainsString('bg-error-container text-on-error-container', $html);
    }

    public function test_lencana_gawat_disembunyikan_bila_tidak_ada_pesanan_gawat_menunggu(): void
    {
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->count(2)->create();

        $html = $this->dasbor();

        $this->assertMatchesRegularExpression('/data-sembunyi-nol="pesanan_menunggu_gawat"[^>]*\shidden/', $html);
    }

    public function test_polling_json_memuat_jumlah_gawat_dan_total_ketersediaan(): void
    {
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->gawat()->create();
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->create();
        Faskes::factory()->diperbarui(4, 9)->create();

        $this->actingAs($this->buatAdmin())->getJson(route('admin.pesanan.ringkasan'))
            ->assertOk()
            ->assertJsonPath('data.pesanan_menunggu', 2)
            ->assertJsonPath('data.pesanan_menunggu_gawat', 1)
            ->assertJsonPath('data.ambulans_total', 0)
            ->assertJsonPath('data.bed_igd_total', 6);
    }

    // ---------- grafik ----------

    public function test_kartu_grafik_garis_dan_donat_dengan_judul_periode(): void
    {
        $this->dataDemo();
        $html = $this->dasbor();

        $this->assertStringContainsString('Pesanan 7 hari', $html);
        $this->assertStringContainsString('Masuk dan selesai per hari', $html);
        $this->assertStringContainsString('Status pesanan', $html);
        $this->assertStringContainsString('&quot;tipe&quot;:&quot;garis&quot;', $html);
        $this->assertStringContainsString('&quot;tipe&quot;:&quot;donat&quot;', $html);
        $this->assertStringContainsString('Diagram cincin status pesanan 7 hari', $html);
        // Legenda HTML di luar Chart.js (bukan plugin legend).
        $this->assertStringContainsString('>Masuk<', $html);
        $this->assertStringContainsString('>Selesai<', $html);
    }

    // ---------- ketersediaan ----------

    public function test_kartu_ketersediaan_empat_sumber_daya_tersedia_dan_total(): void
    {
        $this->dataDemo();
        $html = $this->dasbor();

        $this->assertStringContainsString('Ketersediaan', $html);
        $this->assertStringContainsString('Tersedia dari total, semua Faskes', $html);
        foreach (['Ambulans', 'Bed IGD', 'Bed rawat inap', 'Dokter'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
    }

    // ---------- pesanan terbaru ----------

    public function test_kartu_pesanan_terbaru_menampilkan_jumlah_hari_ini(): void
    {
        $this->dataDemo();
        $html = $this->dasbor();

        $this->assertStringContainsString('Pesanan terbaru', $html);
        $this->assertStringContainsString('7 pesanan terakhir hari ini', $html);
        // Tujuh dibuat hari ini; tabel menampilkan lima yang paling baru (Tiga sampai Tujuh).
        foreach (['Pasien Tiga', 'Pasien Empat', 'Pasien Lima', 'Pasien Enam', 'Pasien Tujuh'] as $pasien) {
            $this->assertStringContainsString($pasien, $html);
        }
        $this->assertStringNotContainsString('Pasien Satu', $html);
    }

    public function test_tab_perlu_tindakan_hanya_menunggu_dan_terverifikasi(): void
    {
        $this->dataDemo();
        $html = $this->dasbor(['tab' => 'perlu-tindakan']);

        foreach (['Pasien Satu', 'Pasien Dua', 'Pasien Tiga'] as $pasien) {
            $this->assertStringContainsString($pasien, $html);
        }
        foreach (['Pasien Empat', 'Pasien Lima', 'Pasien Enam', 'Pasien Tujuh'] as $pasien) {
            $this->assertStringNotContainsString($pasien, $html);
        }
    }

    public function test_tab_berjalan_hanya_status_aktif_setelah_terverifikasi(): void
    {
        $this->dataDemo();
        $html = $this->dasbor(['tab' => 'berjalan']);

        foreach (['Pasien Empat', 'Pasien Lima', 'Pasien Enam'] as $pasien) {
            $this->assertStringContainsString($pasien, $html);
        }
        foreach (['Pasien Satu', 'Pasien Dua', 'Pasien Tiga', 'Pasien Tujuh'] as $pasien) {
            $this->assertStringNotContainsString($pasien, $html);
        }
    }

    public function test_dasbor_kosong_tidak_error(): void
    {
        $html = $this->dasbor();

        $this->assertStringContainsString('0 pesanan terakhir hari ini', $html);
        $this->assertMatchesRegularExpression('/data-ringkasan="pesanan_menunggu"[\s\S]*?data-nilai[^>]*>0</', $html);
    }

    // ---------- polling, JSON, dan otorisasi ----------

    public function test_dasbor_memuat_url_polling_dan_lencana_menu(): void
    {
        Pesanan::factory()->count(2)->create();

        $this->actingAs($this->buatAdmin())->get(route('admin.dashboard'))
            ->assertSee('data-ringkasan-url="'.route('admin.pesanan.ringkasan').'"', false)
            ->assertSee('data-lencana="pesanan_menunggu"', false);
    }

    public function test_dasbor_dan_ringkasan_hanya_untuk_dinkes(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->getJson(route('admin.pesanan.ringkasan'))->assertUnauthorized()->assertJson(['ok' => false]);

        foreach ([$this->buatWarga(), $this->buatPetugas(), $this->buatSopir()] as $user) {
            $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
            $this->actingAs($user)->getJson(route('admin.pesanan.ringkasan'))->assertForbidden()->assertJson(['ok' => false]);
        }
    }
}
