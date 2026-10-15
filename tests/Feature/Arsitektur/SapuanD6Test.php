<?php

namespace Tests\Feature\Arsitektur;

use App\Enums\PesananStatus;
use App\Enums\StatusAmbulans;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

/** Sapuan peran lain dan halaman (docs/DESAIN_DASBOR_MATERIAL.md bagian 8): kartu statistik, kartu penugasan, panel, dan tabel dalam kartu. */
class SapuanD6Test extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
    }

    public function test_dasbor_faskes_memakai_kartu_statistik_dan_petak_status_ambulans(): void
    {
        $faskes = Faskes::factory()->diperbarui(4, 9)->create(['nama' => 'Faskes Saya']);
        $petugas = $this->buatPetugas($faskes);
        Ambulans::factory()->count(2)->create(['faskes_id' => $faskes->id]);
        Ambulans::factory()->servis()->create(['faskes_id' => $faskes->id]);

        $html = $this->actingAs($petugas)->get(route('petugas.dashboard'))->assertOk()->getContent();

        // Baris statistik ala dasbor Dinkes: kartu navy hero (Pesanan aktif) + tiga kartu putih, angka yang sudah
        // ada di halaman (tanpa query baru).
        foreach (['Pesanan aktif', 'Ambulans tersedia', 'Sopir online', 'Dokter tersedia', 'dari 3 ambulans'] as $teks) {
            $this->assertStringContainsString($teks, $html);
        }
        $this->assertStringContainsString('text-stat-hero', $html, 'Kartu navy hero.');
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'text-stat-l tabular-nums'), 'Tiga kartu putih.');
        foreach (['bg-primary-container text-on-primary-container', 'bg-warning-container text-on-warning-container',
            'bg-success-container text-on-success-container'] as $chip) {
            $this->assertStringContainsString($chip, $html);
        }

        // Ketersediaan Faskes: ring-tile Ambulans/Bed IGD/Bed rawat inap (jumlah tersedia dari total), dan
        // keterangan kesegaran data + petak jumlah ambulans per status.
        foreach (['Ambulans', 'Bed IGD', 'Bed rawat inap', 'Dokter umum', 'Dokter spesialis'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
        $this->assertStringContainsString('dari '.$faskes->bed_igd_total, $html);
        $this->assertStringContainsString('Diperbarui', $html);
        // 2 (petak dokter umum/spesialis di kartu Ketersediaan) + 3 (petak jumlah ambulans per status di bawah).
        $this->assertSame(5, substr_count($html, 'text-stat-m tabular-nums'));
        foreach ([StatusAmbulans::Tersedia, StatusAmbulans::Bertugas, StatusAmbulans::Servis] as $status) {
            $this->assertMatchesRegularExpression('/text-body-m text-on-surface-variant">'.preg_quote($status->label(), '/').'</', $html);
        }
        $this->assertStringContainsString('bg-surface-container-low', $html);
    }

    public function test_tabel_di_halaman_daftar_adalah_kartu_berbayang_tanpa_batas(): void
    {
        $faskes = Faskes::factory()->create();
        Ambulans::factory()->create(['faskes_id' => $faskes->id]);

        $html = $this->actingAs($this->buatPetugas($faskes))->get(route('petugas.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('bg-surface-container-lowest rounded-xl shadow-card', $html);
        $this->assertStringNotContainsString('rounded-md border border-outline-variant', $html);
    }

    public function test_dasbor_sopir_menampilkan_penugasan_dalam_satu_kartu_besar(): void
    {
        $faskes = Faskes::factory()->create();
        $sopir = User::factory()->sopir($faskes)->online()->create();
        $ambulans = Ambulans::factory()->create(['faskes_id' => $faskes->id, 'sopir_id' => $sopir->id]);
        $pesanan = Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create([
            'ambulans_id' => $ambulans->id, 'sopir_id' => $sopir->id, 'lokasi_jemput' => 'Jalan Penugasan 7', 'nama_pasien' => 'Pasien Kartu',
            'no_telepon_kontak' => '081234567890',
        ]);

        $html = $this->actingAs($sopir)->get(route('sopir.dashboard'))->assertOk()->getContent();

        // Kartu besar: kode, status, lokasi jemput besar, rincian tanpa kartu bersarang, kontak dapat ditelepon.
        $mulai = strpos($html, 'data-penugasan-aktif');
        $kartu = substr($html, $mulai, strpos($html, 'Penugasan terakhir') - $mulai);
        $this->assertMatchesRegularExpression('/<div[^>]*shadow-card[^>]*data-penugasan-aktif[^>]*>|<div[^>]*data-penugasan-aktif[^>]*shadow-card[^>]*>/', $html);
        $this->assertStringContainsString($pesanan->kode, $kartu);
        $this->assertMatchesRegularExpression('/<p class="text-title-m">Jalan Penugasan 7<\/p>/', $kartu);
        $this->assertStringContainsString('href="tel:081234567890"', $kartu);
        $this->assertStringContainsString('Pasien Kartu', $kartu);
        $this->assertSame(0, substr_count($kartu, 'shadow-card'), 'Rincian di dalam kartu tidak membawa bayangan sendiri.');

        // Tindakan besar satu kolom (mobile-first): tombol penuh selebar kartu dan tanpa grafik.
        $this->assertStringContainsString('min-h-12', $kartu);
        $this->assertStringContainsString('w-full', $kartu);
        $this->assertStringNotContainsString('<svg viewBox="0 0 640 346"', $html);
        $this->assertStringNotContainsString('viewBox="0 0 160 160"', $html);
    }

    public function test_beranda_masyarakat_memakai_kartu_statistik(): void
    {
        $warga = $this->buatWarga();
        Pesanan::factory()->create(['user_id' => $warga->id]);
        Pesanan::factory()->berstatus(PesananStatus::Selesai)->create(['user_id' => $warga->id]);

        $html = $this->actingAs($warga)->get(route('warga.beranda'))->assertOk()->getContent();

        foreach (['Pesanan aktif', 'Pesanan selesai', 'Dari 2 pesanan', 'text-stat-l tabular-nums', 'bg-success-container text-on-success-container'] as $teks) {
            $this->assertStringContainsString($teks, $html);
        }
        $this->assertStringNotContainsString('gap-px', $html, 'Tidak lagi satu panel bersel garis.');
    }

    public function test_laporan_memakai_kartu_statistik_dan_tabel_kartu(): void
    {
        $html = $this->actingAs($this->buatAdmin())->get(route('admin.laporan.index'))->assertOk()->getContent();

        foreach (['Total pesanan', 'Selesai', 'Rata-rata waktu respons', 'Rata-rata durasi layanan'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
        $this->assertSame(4, substr_count($html, 'text-stat-l tabular-nums'));
        $this->assertStringNotContainsString('gap-px', $html);
        $this->assertStringContainsString('bg-surface-container-lowest rounded-xl shadow-card', $html);
    }

    public function test_buat_pesanan_satu_panel_berisi_semua_bagian_formulir(): void
    {
        // R8 (docs/RETEMA_B.md 8.5): satu x-ui.panel membungkus seluruh formulir, bukan satu kartu per bagian lagi.
        $html = $this->actingAs($this->buatWarga())->get(route('warga.pesanan.buat'))->assertOk()->getContent();

        $this->assertSame(5, substr_count($html, '<fieldset'));
        $this->assertSame(1, substr_count($html, 'bg-surface-container-lowest rounded-xl shadow-card p-'));
        $this->assertStringContainsString('Tinjau pesanan', $html);
        // P4b: formulir dua kolom di lg ke atas (lebih dari 4 isian), lebar maksimum naik ke 72rem.
        $this->assertStringContainsString('max-w-5xl', $html);
    }

    public function test_landing_memuat_langkah_cara_kerja_di_dalam_kartu(): void
    {
        $html = $this->get(route('beranda'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/shadow-card[^>]*>\s*<ol class="list-decimal/', $html);
        $this->assertStringContainsString('Masyarakat memantau perjalanan ambulans sampai tiba.', $html);
    }

    public function test_halaman_tidak_memakai_ringkasan_lama_dan_grafik_batang_lama(): void
    {
        $semua = '';
        foreach (['admin', 'petugas', 'sopir', 'warga', 'publik', 'auth', 'dev'] as $folder) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views/'.$folder), \FilesystemIterator::SKIP_DOTS)) as $berkas) {
                $semua .= (string) file_get_contents($berkas->getPathname());
            }
        }

        $this->assertStringNotContainsString('<x-ui.ringkasan', $semua);
        $this->assertStringNotContainsString('<x-ui.grafik-batang', $semua);
    }
}
