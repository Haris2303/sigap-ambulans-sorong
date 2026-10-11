<?php

namespace Tests\Feature\Admin;

use App\Enums\PesananStatus;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
    }

    /** Kriteria penerimaan tampilan dasbor (M4): hierarki tipe, ringkasan, grafik, dan sel tabel. */
    public function test_dasbor_memakai_gaya_material_3(): void
    {
        Pesanan::factory()->gawat()->create();
        Pesanan::factory()->count(2)->create();

        $html = $this->actingAs($this->buatAdmin())->get(route('admin.dashboard'))->assertOk()->getContent();

        // Judul 24px dan keterangan 16px berbeda; judul blok 22px.
        $this->assertStringContainsString('<h1 class="text-headline-s">Dasbor</h1>', $html);
        $this->assertStringContainsString('text-body-l text-on-surface-variant', $html);
        $this->assertStringContainsString('<h2 class="text-title-l">Ketersediaan</h2>', $html);

        // Ringkasan: label di atas angka, angka lebih besar, sel dipisah garis outline-variant.
        $this->assertStringContainsString('text-display-s tabular-nums', $html);
        $this->assertStringContainsString('text-label-l text-on-surface-variant', $html);
        $this->assertStringContainsString('gap-px', $html);
        $this->assertStringContainsString('bg-outline-variant', $html);
        $this->assertStringContainsString('grid-cols-2', $html);
        $this->assertStringContainsString('lg:grid-cols-4', $html);

        // Grafik: label berlebar tetap tanpa pemecahan baris, nilai rata kanan.
        $this->assertStringContainsString('w-24 shrink-0 whitespace-nowrap', $html);
        $this->assertStringContainsString('w-10 shrink-0 text-right tabular-nums', $html);

        // Sel tabel berisi teks dan lencana sejajar vertikal; label status dengan huruf awal kalimat.
        $this->assertStringContainsString('inline-flex items-center gap-2', $html);
        $this->assertMatchesRegularExpression('/<span class="inline-flex items-center gap-2">[^<]+<span[^>]*bg-error-container/', $html);
        $this->assertStringContainsString('Menunggu verifikasi', $html);
        $this->assertStringNotContainsString('Menunggu Verifikasi', $html);

        // Daftar menu pada laci bergulir dengan bilah tipis.
        $this->assertStringContainsString('gulir-tipis', $html);
    }

    public function test_dasbor_menampilkan_angka_pesanan_dan_ketersediaan(): void
    {
        Pesanan::factory()->count(3)->create();
        Pesanan::factory()->berstatus(PesananStatus::Terverifikasi)->count(2)->create();
        Pesanan::factory()->berstatus(PesananStatus::Diterima)->create();
        Pesanan::factory()->berstatus(PesananStatus::Selesai)->create(['selesai_pada' => now()]);
        Pesanan::factory()->berstatus(PesananStatus::Selesai)->create(['selesai_pada' => now()->subDays(2)]);

        $faskes = Faskes::factory()->diperbarui(4, 9)->create();
        Faskes::factory()->diperbarui(1, 1)->nonaktif()->create();
        Ambulans::factory()->count(2)->create(['faskes_id' => $faskes->id]);
        Ambulans::factory()->servis()->create(['faskes_id' => $faskes->id]);

        $html = $this->actingAs($this->buatAdmin())->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/data-ringkasan="pesanan_menunggu".*?data-nilai>3</s', $html);
        $this->assertMatchesRegularExpression('/data-ringkasan="pesanan_terverifikasi".*?data-nilai>2</s', $html);
        $this->assertMatchesRegularExpression('/data-ringkasan="pesanan_berjalan".*?data-nilai>1</s', $html);
        $this->assertMatchesRegularExpression('/data-ringkasan="pesanan_selesai_hari_ini".*?data-nilai>1</s', $html);
        $this->assertStringContainsString('Ambulans tersedia', $html);
        $this->assertMatchesRegularExpression('/Bed IGD tersedia.*?data-nilai>4</s', $html);
        $this->assertMatchesRegularExpression('/Bed rawat inap tersedia.*?data-nilai>9</s', $html);
        $this->assertMatchesRegularExpression('/Dokter tersedia.*?data-nilai>3</s', $html);
        $this->assertStringContainsString('Pesanan 7 hari terakhir', $html);
        $this->assertStringContainsString('Pesanan terbaru', $html);
    }

    public function test_dasbor_kosong_tidak_error(): void
    {
        $this->actingAs($this->buatAdmin())->get(route('admin.dashboard'))
            ->assertOk()->assertSee('Belum ada pesanan.');
    }

    public function test_grafik_tujuh_hari_menghitung_pesanan_per_hari(): void
    {
        Pesanan::factory()->count(2)->create(['created_at' => now()]);
        Pesanan::factory()->create(['created_at' => now()->subDays(3)]);
        Pesanan::factory()->create(['created_at' => now()->subDays(10)]);

        $grafik = app(\App\Services\RingkasanDinkes::class)->pesananTujuhHari();

        $this->assertCount(7, $grafik);
        $this->assertSame(3, array_sum($grafik));
        $this->assertSame(2, array_values($grafik)[6]);
        $this->assertSame(1, array_values($grafik)[3]);
    }

    public function test_dasbor_memuat_url_polling_dan_lencana_menu(): void
    {
        Pesanan::factory()->count(2)->create();

        $this->actingAs($this->buatAdmin())->get(route('admin.dashboard'))
            ->assertSee('data-ringkasan-url="'.route('admin.pesanan.ringkasan').'"', false)
            ->assertSee('data-lencana="pesanan_menunggu"', false);
    }

    public function test_ringkasan_json_mengikuti_konvensi(): void
    {
        Pesanan::factory()->count(2)->create();
        Pesanan::factory()->berstatus(PesananStatus::Ditugaskan)->create();

        $this->actingAs($this->buatAdmin())->getJson(route('admin.pesanan.ringkasan'))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.pesanan_menunggu', 2)
            ->assertJsonPath('data.pesanan_berjalan', 1)
            ->assertJsonPath('data.pesanan_terverifikasi', 0);
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
