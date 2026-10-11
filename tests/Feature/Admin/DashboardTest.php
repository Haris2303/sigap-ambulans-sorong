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
