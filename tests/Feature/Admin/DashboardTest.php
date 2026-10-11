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

/** Dasbor Dinkes (docs/DESAIN_DASBOR_MATERIAL.md bagian 6). Hitungan, grafik, dan batas query ada di RingkasanDasborTest. */
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
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->create(['created_at' => '2026-10-10 09:00:00', 'nama_pasien' => 'Pasien Dua']);
        Pesanan::factory()->berstatus(PesananStatus::Terverifikasi)->create(['created_at' => '2026-10-09 09:00:00', 'nama_pasien' => 'Pasien Tiga']);
        Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create(['created_at' => '2026-10-08 09:00:00', 'nama_pasien' => 'Pasien Empat']);
        Pesanan::factory()->berstatus(PesananStatus::Diterima)->create(['created_at' => '2026-10-07 09:00:00', 'nama_pasien' => 'Pasien Lima']);
        Pesanan::factory()->berstatus(PesananStatus::DalamPerjalanan)->create(['created_at' => '2026-10-06 09:00:00', 'nama_pasien' => 'Pasien Enam', 'tingkat_darurat' => TingkatDarurat::Gawat]);
        Pesanan::factory()->berstatus(PesananStatus::Selesai)->create(['created_at' => '2026-10-11 08:00:00', 'selesai_pada' => '2026-10-11 10:00:00', 'nama_pasien' => 'Pasien Tujuh']);

        $faskes = Faskes::factory()->diperbarui(4, 9)->create();
        Faskes::factory()->diperbarui(1, 1)->nonaktif()->create();
        Ambulans::factory()->count(2)->create(['faskes_id' => $faskes->id]);
        Ambulans::factory()->servis()->create(['faskes_id' => $faskes->id]);
    }

    private function dasbor(array $query = []): string
    {
        return $this->actingAs($this->buatAdmin(['name' => 'Admin Dinkes']))->get(route('admin.dashboard', $query))->assertOk()->getContent();
    }

    // ---------- judul dan kartu statistik ----------

    public function test_judul_halaman_subjudul_dan_aksi_tonal(): void
    {
        $html = $this->dasbor();

        $this->assertStringContainsString('<h1 class="text-page-title">Dasbor</h1>', $html);
        $this->assertStringContainsString('Ringkasan pesanan dan ketersediaan Faskes. Angka diperbarui otomatis.', $html);
        $this->assertStringContainsString('text-body-l text-on-surface-variant', $html);
        $this->assertMatchesRegularExpression('/<a href="'.preg_quote(route('admin.ketersediaan.index'), '/').'"[^>]*bg-secondary-container text-on-secondary-container[^>]*>\s*Kelola ketersediaan\s*<\/a>/', $html);
    }

    public function test_empat_kartu_statistik_dengan_angka_label_keterangan_dan_ikon(): void
    {
        $this->dataDemo();
        $html = $this->dasbor();

        // Kartu utama primary: menunggu verifikasi, dengan tombol on-primary ke daftar pesanan tab Perlu tindakan.
        $utama = $this->bagian($html, 'data-ringkasan="pesanan_menunggu"', 'data-ringkasan="pesanan_terverifikasi"');
        foreach (['bg-primary', 'text-on-primary', 'shadow-card-raised', 'text-stat-hero', 'Menunggu verifikasi', 'pesanan baru', 'bg-on-primary text-primary', 'Verifikasi pesanan'] as $teks) {
            $this->assertStringContainsString($teks, $utama);
        }
        $this->assertMatchesRegularExpression('/data-nilai>2</', $utama);
        $this->assertStringContainsString('href="'.route('admin.pesanan.index', ['tab' => 'perlu-tindakan']).'"', $utama);

        // Tiga kartu putih dengan nada dan keterangan sesuai dokumen.
        $kartu = [
            ['pesanan_terverifikasi', 'pesanan_berjalan', 'Terverifikasi', 'menunggu penugasan', 'bg-primary-container text-on-primary-container', '1'],
            ['pesanan_berjalan', 'pesanan_selesai_hari_ini', 'Sedang berjalan', 'ditugaskan sampai di jalan', 'bg-warning-container text-on-warning-container', '3'],
            ['pesanan_selesai_hari_ini', 'Pesanan 7 hari terakhir', 'Selesai hari ini', 'pesanan tuntas', 'bg-success-container text-on-success-container', '1'],
        ];
        foreach ($kartu as [$kunci, $berikut, $label, $hint, $chip, $angka]) {
            $isi = $this->bagian($html, 'data-ringkasan="'.$kunci.'"', str_contains($berikut, ' ') ? $berikut : 'data-ringkasan="'.$berikut.'"');
            foreach (['shadow-card', 'text-stat-l tabular-nums', $label, $hint, $chip, 'size-13'] as $teks) {
                $this->assertStringContainsString($teks, $isi, $kunci.': '.$teks);
            }
            $this->assertMatchesRegularExpression('/data-nilai>'.$angka.'</', $isi, $kunci);
        }
    }

    public function test_kartu_utama_menonjolkan_jumlah_pesanan_gawat_yang_menunggu(): void
    {
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->gawat()->count(2)->create();
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->create();
        // Gawat pada status lain tidak dihitung: yang diminta adalah pesanan Gawat yang menunggu verifikasi.
        Pesanan::factory()->berstatus(PesananStatus::Terverifikasi)->gawat()->create();

        $utama = $this->bagian($this->dasbor(), 'data-ringkasan="pesanan_menunggu"', 'data-ringkasan="pesanan_terverifikasi"');

        $this->assertMatchesRegularExpression('/data-nilai>3</', $utama, 'Tiga pesanan menunggu.');
        $this->assertMatchesRegularExpression(
            '/<span[^>]*data-sembunyi-nol="pesanan_menunggu_gawat"[^>]*>\s*<span class="size-1\.5[^>]*><\/span>\s*<span data-angka="pesanan_menunggu_gawat" class="tabular-nums">2<\/span> Gawat\s*<\/span>/',
            $utama,
        );
        $this->assertStringContainsString('bg-error-container text-on-error-container', $utama);
        $this->assertDoesNotMatchRegularExpression('/data-sembunyi-nol="pesanan_menunggu_gawat"[^>]*\shidden/', $utama);
        // Tidak menimpa angka kartu lewat data-nilai (polling mengisi data-nilai di dalam data-ringkasan).
        $this->assertSame(1, preg_match_all('/data-nilai>/', $utama));
    }

    public function test_lencana_gawat_disembunyikan_bila_tidak_ada_pesanan_gawat_menunggu(): void
    {
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->count(2)->create();

        $utama = $this->bagian($this->dasbor(), 'data-ringkasan="pesanan_menunggu"', 'data-ringkasan="pesanan_terverifikasi"');

        $this->assertMatchesRegularExpression('/data-sembunyi-nol="pesanan_menunggu_gawat"[^>]*\shidden/', $utama);
        $this->assertMatchesRegularExpression('/data-angka="pesanan_menunggu_gawat"[^>]*>0</', $utama);
    }

    public function test_polling_json_memuat_jumlah_gawat_menunggu(): void
    {
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->gawat()->create();
        Pesanan::factory()->berstatus(PesananStatus::Menunggu)->create();

        $this->actingAs($this->buatAdmin())->getJson(route('admin.pesanan.ringkasan'))
            ->assertOk()
            ->assertJsonPath('data.pesanan_menunggu', 2)
            ->assertJsonPath('data.pesanan_menunggu_gawat', 1);
    }

    public function test_selesai_hari_ini_tidak_menghitung_hari_lain(): void
    {
        $this->dataDemo();
        Pesanan::factory()->berstatus(PesananStatus::Selesai)->create(['selesai_pada' => '2026-10-10 23:59:00']);

        $html = $this->dasbor();

        $this->assertMatchesRegularExpression('/data-ringkasan="pesanan_selesai_hari_ini".*?data-nilai>1</s', $html);
    }

    // ---------- grafik ----------

    public function test_kartu_grafik_batang_dan_donat_dengan_subjudul(): void
    {
        $this->dataDemo();
        $html = $this->dasbor();

        $this->assertStringContainsString('<h2 class="text-card-title">Pesanan 7 hari terakhir</h2>', $html);
        $this->assertStringContainsString('5 sampai 11 Okt 2026', $html);
        // Grafik batang berupa kanvas Chart.js: hari ini (indeks 6) disorot dan datanya dikirim lewat atribut JSON.
        $this->assertStringContainsString('&quot;tipe&quot;:&quot;batang&quot;', $html);
        $this->assertStringContainsString('&quot;highlight&quot;:6', $html);
        $this->assertStringContainsString('&quot;unit&quot;:&quot;pesanan&quot;', $html);
        $this->assertStringContainsString('aria-label="Grafik batang pesanan per hari: Sen 05/10 0 pesanan', $html);
        $this->assertStringContainsString('Min 11/10 2 pesanan', $html);
        $this->assertStringNotContainsString('<svg viewBox', $html);

        $this->assertStringContainsString('<h2 class="text-card-title">Status pesanan</h2>', $html);
        $this->assertStringContainsString('7 pesanan terbaru', $html);
        $this->assertStringContainsString('&quot;tipe&quot;:&quot;donat&quot;', $html);
        $this->assertStringContainsString('Diagram cincin status 7 pesanan terbaru: ', $html);
        // Legenda hanya status bernilai lebih dari nol, dengan warna tetap per status.
        foreach (['Menunggu verifikasi', 'Terverifikasi', 'Ditugaskan', 'Diterima sopir', 'Dalam perjalanan', 'Selesai'] as $label) {
            $this->assertMatchesRegularExpression('/<span class="flex-1">'.preg_quote($label, '/').'<\/span>/', $html, $label);
        }
        $this->assertDoesNotMatchRegularExpression('/<span class="flex-1">(Ditolak|Dibatalkan)<\/span>/', $html);
        $this->assertStringContainsString('bg-warning', $html);
    }

    // ---------- ketersediaan ----------

    public function test_kartu_ketersediaan_dengan_empat_petak_dan_tautan_kelola(): void
    {
        $this->dataDemo();
        $html = $this->dasbor();

        $this->assertStringContainsString('<h2 class="text-card-title">Ketersediaan</h2>', $html);
        $this->assertStringContainsString('Semua Faskes, diperbarui otomatis', $html);
        $this->assertMatchesRegularExpression('/<a href="'.preg_quote(route('admin.ketersediaan.index'), '/').'"[^>]*text-primary[^>]*>Kelola<\/a>/', $html);

        $petak = [
            ['ambulans_tersedia', 'Ambulans tersedia', 'bg-primary-container text-on-primary-container', '2'],
            ['bed_igd_tersedia', 'Bed IGD tersedia', 'bg-error-container text-on-error-container', '4'],
            ['bed_rawat_inap_tersedia', 'Bed rawat inap tersedia', 'bg-warning-container text-on-warning-container', '9'],
            ['dokter_tersedia', 'Dokter tersedia', 'bg-success-container text-on-success-container', null],
        ];
        foreach ($petak as [$kunci, $label, $chip, $angka]) {
            $this->assertMatchesRegularExpression('/<div(?=[^>]*data-ringkasan="'.$kunci.'")(?=[^>]*bg-surface-container-low)[^>]*>/', $html, $kunci);
            $this->assertStringContainsString($label, $html);
            $this->assertStringContainsString($chip, $html);
            if ($angka !== null) {
                $this->assertMatchesRegularExpression('/data-ringkasan="'.$kunci.'".*?data-nilai>'.$angka.'</s', $html, $kunci);
            }
        }
        $this->assertStringContainsString('text-stat-m tabular-nums', $html);
        $this->assertStringContainsString('sm:grid-cols-2', $html);
    }

    // ---------- pesanan terbaru ----------

    public function test_kartu_pesanan_terbaru_dengan_tab_tabel_dan_tautan_semua(): void
    {
        $this->dataDemo();
        $html = $this->dasbor();

        $this->assertStringContainsString('<h2 class="text-card-title">Pesanan terbaru</h2>', $html);
        $this->assertStringContainsString('Menampilkan 5 dari 7 pesanan terbaru', $html);
        $this->assertMatchesRegularExpression('/<a href="'.preg_quote(route('admin.pesanan.index'), '/').'"[^>]*>Semua pesanan<\/a>/', $html);
        $this->assertStringContainsString('id="pesanan-terbaru"', $html);

        // Tab: tautan ke dasbor yang sama dengan ?tab=; "Semua" aktif secara bawaan.
        $this->assertMatchesRegularExpression('/aria-label="Filter pesanan terbaru"/', $html);
        foreach (['semua' => 'Semua', 'perlu-tindakan' => 'Perlu tindakan', 'berjalan' => 'Berjalan'] as $nilai => $label) {
            $this->assertMatchesRegularExpression('~<a href="'.preg_quote(route('admin.dashboard', ['tab' => $nilai]), '~').'#pesanan-terbaru"[^>]*>\s*'.$label.'\s*</a>~', $html, $label);
        }
        $this->assertSame(1, substr_count($html, 'aria-current="page"') - 1, 'Satu tab aktif (selain item menu).');
        $this->assertMatchesRegularExpression('~<a href="[^"]*tab=semua#pesanan-terbaru"[^>]*aria-current="page"~', $html);
    }

    public function test_tabel_pesanan_terbaru_empat_kolom_dengan_kode_waktu_dan_lencana(): void
    {
        $this->dataDemo();
        $html = $this->dasbor();

        foreach (['Kode', 'Pasien', 'Layanan', 'Status'] as $kolom) {
            $this->assertMatchesRegularExpression('/<th[^>]*>'.$kolom.'<\/th>/', $html, $kolom);
        }
        $terbaru = Pesanan::latest()->orderByDesc('id')->first();

        // Kode: tautan bertanda tabular di atas tanggal dan jam (body-s) berkoma; tampil paling banyak 5 baris.
        $this->assertMatchesRegularExpression('~<a href="'.preg_quote(route('admin.pesanan.show', $terbaru), '~').'"[^>]*tabular-nums[^>]*>'.preg_quote($terbaru->kode, '~').'</a>~', $html);
        $this->assertStringContainsString('11 Okt 2026, 09:00', $html);
        $this->assertMatchesRegularExpression('/<time[^>]*text-body-s text-on-surface-variant[^>]*>/', $html);
        $this->assertSame(5, preg_match_all('/<tr>\s*<td/', $html));

        // Layanan dengan lencana Gawat sejajar; status berupa lencana pil dari Enum.
        $this->assertStringContainsString('Pasien Satu', $html);
        $this->assertStringContainsString('rounded-full px-2.5', $html);
        $this->assertStringContainsString('Menunggu verifikasi', $html);

        // Tabel bergulir di dalam kartu (bukan kepala dan tab) dengan lebar minimum; tanpa bingkai ganda.
        $this->assertStringContainsString('min-w-136', $html);
        $this->assertStringContainsString('overflow-x-auto', $html);
    }

    public function test_lencana_gawat_sejajar_dengan_teks_layanan(): void
    {
        $this->dataDemo();
        $html = $this->dasbor(['tab' => 'berjalan']);

        $this->assertMatchesRegularExpression('/<span class="inline-flex items-center gap-2">Darurat\s*<span[^>]*bg-error-container[^>]*>/', $html);
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
        $this->assertStringContainsString('Menampilkan 3 dari 7 pesanan terbaru', $html);
        $this->assertMatchesRegularExpression('~<a href="[^"]*tab=perlu-tindakan#pesanan-terbaru"[^>]*aria-current="page"~', $html);
        $this->assertDoesNotMatchRegularExpression('~<a href="[^"]*tab=semua#pesanan-terbaru"[^>]*aria-current="page"~', $html);
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

    public function test_tab_tanpa_pesanan_menampilkan_keadaan_kosong(): void
    {
        $this->dataDemo();
        Pesanan::query()->whereIn('status', [PesananStatus::Ditugaskan->value, PesananStatus::Diterima->value, PesananStatus::DalamPerjalanan->value])->delete();

        $html = $this->dasbor(['tab' => 'berjalan']);

        $this->assertStringContainsString('Belum ada pesanan di tab ini.', $html);
        $this->assertDoesNotMatchRegularExpression('/<th[^>]*>Kode<\/th>/', $html);
    }

    public function test_tab_tidak_dikenal_kembali_ke_semua(): void
    {
        $this->dataDemo();
        $html = $this->dasbor(['tab' => 'ngawur']);

        $this->assertMatchesRegularExpression('~<a href="[^"]*tab=semua#pesanan-terbaru"[^>]*aria-current="page"~', $html);
        $this->assertStringContainsString('Menampilkan 5 dari 7 pesanan terbaru', $html);
    }

    public function test_dasbor_kosong_tidak_error_dan_menampilkan_keadaan_kosong(): void
    {
        $html = $this->dasbor();

        $this->assertStringContainsString('Belum ada pesanan di tab ini.', $html);
        $this->assertStringContainsString('0 pesanan terbaru', $html);
        $this->assertStringContainsString('tidak ada data', $html);
        $this->assertMatchesRegularExpression('/data-ringkasan="pesanan_menunggu".*?data-nilai>0</s', $html);
    }

    // ---------- polling, JSON, dan otorisasi ----------

    public function test_dasbor_memuat_url_polling_dan_lencana_menu(): void
    {
        Pesanan::factory()->count(2)->create();

        $this->actingAs($this->buatAdmin())->get(route('admin.dashboard'))
            ->assertSee('data-ringkasan-url="'.route('admin.pesanan.ringkasan').'"', false)
            ->assertSee('data-lencana="pesanan_menunggu"', false);
    }

    public function test_ringkasan_json_mengikuti_konvensi_dan_memuat_ketersediaan(): void
    {
        Pesanan::factory()->count(2)->create();
        Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create();
        Faskes::factory()->diperbarui(4, 9)->create();

        $this->actingAs($this->buatAdmin())->getJson(route('admin.pesanan.ringkasan'))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.pesanan_menunggu', 2)
            ->assertJsonPath('data.pesanan_berjalan', 1)
            ->assertJsonPath('data.pesanan_terverifikasi', 0)
            ->assertJsonPath('data.bed_igd_tersedia', 4)
            ->assertJsonPath('data.bed_rawat_inap_tersedia', 9)
            ->assertJsonStructure(['data' => ['ambulans_tersedia', 'dokter_tersedia', 'pesanan_selesai_hari_ini']]);
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

    /** Potongan HTML dari penanda awal sampai sebelum penanda akhir; penanda atribut data-* diperluas ke awal tag `<div` pemiliknya. */
    private function bagian(string $html, string $awal, string $akhir): string
    {
        $i = strpos($html, $awal);
        $this->assertNotFalse($i, 'Penanda awal tidak ada: '.$awal);
        $j = strpos($html, $akhir, $i + 1);
        $this->assertNotFalse($j, 'Penanda akhir tidak ada: '.$akhir);

        $awalTag = fn (int $posisi) => (int) strrpos(substr($html, 0, $posisi), '<div');
        $mulai = str_starts_with($awal, 'data-') ? $awalTag($i) : $i;
        $selesai = str_starts_with($akhir, 'data-') ? $awalTag($j) : $j;

        return substr($html, $mulai, $selesai - $mulai);
    }
}
