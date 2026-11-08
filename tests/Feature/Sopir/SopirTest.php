<?php

namespace Tests\Feature\Sopir;

use App\Enums\PesananStatus;
use App\Enums\StatusAmbulans;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PesananWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

class SopirTest extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    private Faskes $faskes;

    private User $sopir;

    private Ambulans $ambulans;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
        $this->faskes = Faskes::factory()->create(['nama' => 'Faskes Sopir']);
        $this->sopir = User::factory()->sopir($this->faskes)->create(['name' => 'Pak Sopir']);
        $this->ambulans = Ambulans::factory()->create(['faskes_id' => $this->faskes->id, 'sopir_id' => $this->sopir->id]);
        $this->admin = $this->buatAdmin();
    }

    private function pesananDitugaskan(?User $sopir = null): Pesanan
    {
        $sopir ??= $this->sopir;
        $sopir->update(['sedang_online' => true]);
        $alur = new PesananWorkflow;
        $pesanan = $alur->buat($this->buatWarga(), [
            'nama_pasien' => 'Pasien Sopir', 'no_telepon_kontak' => '081200000000', 'jenis_layanan' => 'darurat',
            'tingkat_darurat' => 'gawat', 'lokasi_jemput' => 'Jalan Penugasan', 'lat_jemput' => -0.88, 'lng_jemput' => 131.25,
        ]);
        $alur->verifikasi($pesanan, $this->admin);

        return $alur->tugaskan($pesanan, $this->admin, $this->ambulans, $sopir);
    }

    // Dasbor

    public function test_dasbor_tanpa_penugasan_menurut_status_online(): void
    {
        $this->actingAs($this->sopir)->get(route('sopir.dashboard'))
            ->assertOk()->assertSee('Offline')->assertSee('Anda offline. Tekan Online untuk menerima penugasan.')
            ->assertSee('Belum ada penugasan yang selesai.');

        $this->sopir->update(['sedang_online' => true]);
        $this->get(route('sopir.dashboard'))->assertOk()->assertSee('Belum ada penugasan. Halaman ini diperbarui otomatis.');
    }

    public function test_penugasan_menampilkan_tombol_navigasi_ke_lokasi_jemput(): void
    {
        $this->pesananDitugaskan();

        $html = $this->actingAs($this->sopir)->get(route('sopir.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('href="geo:-0.88,131.25?q=-0.88,131.25(Lokasi%20jemput)"', $html);
        $this->assertStringContainsString('href="https://www.openstreetmap.org/?mlat=-0.88&amp;mlon=131.25#map=17/-0.88/131.25"', $html);
        $this->assertMatchesRegularExpression('/<a href="geo:[^"]*"[^>]*>[^<]*(<svg.*?<\/svg>)?\s*Buka navigasi\s*<\/a>/s', $html);
        $this->assertStringContainsString('target="_blank" rel="noopener"', $html);

        // Tombol berada tepat di bawah lokasi jemput, di bagian atas kartu (sebelum rincian dan peta).
        $this->assertGreaterThan(strpos($html, 'Jalan Penugasan'), strpos($html, 'Buka navigasi'));
        $this->assertLessThan(strpos($html, 'Layanan'), strpos($html, 'Buka navigasi'));
    }

    public function test_navigasi_memakai_alamat_teks_bila_pesanan_tanpa_koordinat(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $pesanan->update(['lat_jemput' => null, 'lng_jemput' => null, 'lokasi_jemput' => 'Jalan Basuki Rahmat Km 8']);

        $html = $this->actingAs($this->sopir)->get(route('sopir.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('href="geo:0,0?q=Jalan%20Basuki%20Rahmat%20Km%208"', $html);
        $this->assertStringContainsString('href="https://www.openstreetmap.org/search?query=Jalan%20Basuki%20Rahmat%20Km%208"', $html);
    }

    public function test_tanpa_penugasan_tidak_ada_tombol_navigasi(): void
    {
        $this->actingAs($this->sopir)->get(route('sopir.dashboard'))->assertOk()->assertDontSee('Buka navigasi');
    }

    public function test_templat_navigasi_hanya_memakai_layanan_openstreetmap_dan_skema_geo(): void
    {
        $templat = config('sigap.navigasi');

        $this->assertSame(['aplikasi', 'aplikasi_alamat', 'web', 'web_alamat'], array_keys($templat));
        foreach (['aplikasi', 'aplikasi_alamat'] as $kunci) {
            $this->assertStringStartsWith('geo:', $templat[$kunci]);
        }
        foreach (['web', 'web_alamat'] as $kunci) {
            $this->assertSame('www.openstreetmap.org', parse_url($templat[$kunci], PHP_URL_HOST), 'Layanan luar hanya OpenStreetMap (config/sigap.php).');
            $this->assertSame('https', parse_url($templat[$kunci], PHP_URL_SCHEME));
        }
    }

    public function test_aksi_utama_sopir_berada_di_bilah_lekat_dan_tolak_tersembunyi_di_pengungkap(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $this->actingAs($this->sopir);

        $html = $this->get(route('sopir.dashboard'))->assertOk()->getContent();

        $lipat = strpos($html, '<details');
        $bilah = strpos($html, 'data-bilah-aksi');
        $this->assertNotFalse($lipat);
        $this->assertNotFalse($bilah);
        $this->assertLessThan($bilah, $lipat, 'Pengungkap Tolak di atas, bilah aksi utama paling bawah kartu.');
        $this->assertMatchesRegularExpression('/<details\s+class="group[^"]*"\s*>\s*<summary[^>]*>\s*<span>Tolak penugasan<\/span>/', $html, 'Tolak tertutup secara bawaan.');
        $this->assertGreaterThan($lipat, strpos($html, 'Alasan menolak'));
        $this->assertLessThan($bilah, strpos($html, route('sopir.pesanan.tolak', $pesanan)));

        // Terima ada di dalam bilah, dan bilah itu anak terakhir kartu: tidak ada tombol aksi lain sesudahnya.
        $this->assertGreaterThan($bilah, strpos($html, route('sopir.pesanan.terima', $pesanan)));
        $this->assertLessThan(strpos($html, 'Penugasan terakhir'), strpos($html, route('sopir.pesanan.terima', $pesanan)));
        $this->assertStringContainsString('sticky bottom-0', $html);
    }

    public function test_pengungkap_tolak_terbuka_bila_alasan_gagal_divalidasi(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $this->actingAs($this->sopir);

        $this->post(route('sopir.pesanan.tolak', $pesanan), ['alasan' => ''])->assertSessionHasErrors('alasan');

        // Halaman sesudah redirect membawa galat isian; diberikan langsung karena klien tes tidak membawa cookie sesi antar permintaan.
        // Sesi berformat JSON menyimpan galat sebagai larik (dipulihkan menjadi ViewErrorBag saat sesi dimuat).
        $galat = ['default' => ['messages' => ['alasan' => ['Alasan wajib diisi.']], 'format' => ':message']];
        $html = $this->withSession(['errors' => $galat])->get(route('sopir.dashboard'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<details\s+open\s+class="group/', $html);
        $this->assertStringContainsString('id="f-alasan-galat"', $html, 'Galat isian tampil di dalam pengungkap yang terbuka.');
    }

    public function test_bilah_aksi_memuat_tombol_utama_untuk_setiap_status_aktif(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $this->actingAs($this->sopir);

        $this->post(route('sopir.pesanan.terima', $pesanan));
        $html = $this->get(route('sopir.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('data-bilah-aksi', $html);
        $this->assertStringNotContainsString('<details', $html);
        $this->assertGreaterThan(strpos($html, 'data-bilah-aksi'), strpos($html, 'Mulai perjalanan'));

        $this->post(route('sopir.pesanan.berangkat', $pesanan));
        $html = $this->get(route('sopir.dashboard'))->assertOk()->getContent();
        $this->assertGreaterThan(strpos($html, 'data-bilah-aksi'), strpos($html, route('sopir.pesanan.selesai', $pesanan)));
    }

    public function test_dasbor_tanpa_penugasan_tidak_punya_bilah_aksi(): void
    {
        $this->actingAs($this->sopir)->get(route('sopir.dashboard'))->assertOk()->assertDontSee('data-bilah-aksi', false);
    }

    public function test_dasbor_menampilkan_penugasan_dan_tombol_sesuai_status(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $this->actingAs($this->sopir);

        $this->get(route('sopir.dashboard'))->assertOk()
            ->assertSee($pesanan->kode)->assertSee('Pasien Sopir')->assertSee('Jalan Penugasan')->assertSee($this->ambulans->plat_nomor)
            ->assertSee('Terima')->assertSee('Alasan menolak')->assertDontSee('Mulai perjalanan')
            ->assertSee('data-penugasan="'.$pesanan->id.':ditugaskan"', false)
            ->assertSee('data-peta="pelacak"', false);

        $this->post(route('sopir.pesanan.terima', $pesanan));
        $this->get(route('sopir.dashboard'))->assertSee('Mulai perjalanan')->assertDontSee('Alasan menolak');

        $this->post(route('sopir.pesanan.berangkat', $pesanan));
        $this->get(route('sopir.dashboard'))->assertSee('Selesai')->assertDontSee('Mulai perjalanan');
    }

    public function test_dasbor_menampilkan_riwayat_selesai(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $this->actingAs($this->sopir);
        $this->post(route('sopir.pesanan.terima', $pesanan));
        $this->post(route('sopir.pesanan.berangkat', $pesanan));
        $this->post(route('sopir.pesanan.selesai', $pesanan));

        $this->get(route('sopir.dashboard'))->assertOk()->assertSee('Penugasan terakhir')->assertSee($pesanan->kode)
            ->assertSee('Belum ada penugasan. Halaman ini diperbarui otomatis.');
    }

    // Online dan lokasi

    public function test_online_dan_offline(): void
    {
        $this->actingAs($this->sopir)->post(route('sopir.online'), ['online' => '1'])
            ->assertRedirect(route('sopir.dashboard'))->assertSessionHas('sukses');
        $this->assertTrue($this->sopir->fresh()->sedang_online);

        $this->post(route('sopir.online'), ['online' => '0'])->assertRedirect(route('sopir.dashboard'));
        $this->assertFalse($this->sopir->fresh()->sedang_online);

        $this->post(route('sopir.online'), [])->assertSessionHasErrors('online');
    }

    public function test_tidak_bisa_offline_saat_memegang_pesanan(): void
    {
        $this->pesananDitugaskan();

        $this->actingAs($this->sopir)->post(route('sopir.online'), ['online' => '0'])->assertSessionHas('galat');
        $this->assertTrue($this->sopir->fresh()->sedang_online);

        $this->postJson(route('sopir.online'), ['online' => '0'])->assertStatus(422)->assertJson(['ok' => false]);
    }

    public function test_kirim_lokasi_menyimpan_posisi_dan_waktu(): void
    {
        $this->actingAs($this->sopir)->postJson(route('sopir.lokasi'), ['lat' => -0.8765, 'lng' => 131.2599])
            ->assertOk()->assertJsonPath('ok', true);

        $this->sopir->refresh();
        $this->assertEqualsWithDelta(-0.8765, (float) $this->sopir->lat, 0.00001);
        $this->assertEqualsWithDelta(131.2599, (float) $this->sopir->lng, 0.00001);
        $this->assertNotNull($this->sopir->lokasi_diperbarui_pada);
    }

    public function test_kirim_lokasi_menolak_koordinat_tidak_valid(): void
    {
        $this->actingAs($this->sopir)->postJson(route('sopir.lokasi'), ['lat' => 120, 'lng' => 'abc'])
            ->assertStatus(422)->assertJson(['ok' => false]);
        $this->postJson(route('sopir.lokasi'), [])->assertStatus(422);
        $this->assertNull($this->sopir->fresh()->lat);
    }

    // JSON penugasan

    public function test_penugasan_json_kosong_dan_berisi(): void
    {
        $this->actingAs($this->sopir)->getJson(route('sopir.penugasan'))
            ->assertOk()->assertJsonPath('ok', true)->assertJsonPath('data.penugasan', null)->assertJsonPath('data.sopir.online', false);

        $pesanan = $this->pesananDitugaskan();
        $this->sopir->update(['lat' => -0.87, 'lng' => 131.24]);

        $this->getJson(route('sopir.penugasan'))
            ->assertJsonPath('data.penugasan.id', $pesanan->id)
            ->assertJsonPath('data.penugasan.status', 'ditugaskan')
            ->assertJsonPath('data.penugasan.lokasi_jemput', 'Jalan Penugasan')
            ->assertJsonPath('data.penugasan.lat', -0.88)
            ->assertJsonPath('data.sopir.lat', -0.87)
            ->assertJsonPath('data.sopir.online', true);
    }

    public function test_penugasan_json_tidak_memuat_pesanan_sopir_lain(): void
    {
        $lain = User::factory()->sopir($this->faskes)->online()->create();
        $this->pesananDitugaskan();

        $this->actingAs($lain)->getJson(route('sopir.penugasan'))->assertJsonPath('data.penugasan', null);
    }

    // Aksi pesanan

    public function test_urutan_penuh_terima_berangkat_selesai(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $this->actingAs($this->sopir);

        $this->post(route('sopir.pesanan.terima', $pesanan))->assertRedirect(route('sopir.dashboard'))->assertSessionHas('sukses');
        $this->assertSame(PesananStatus::Diterima, $pesanan->fresh()->status);

        $this->post(route('sopir.pesanan.berangkat', $pesanan))->assertSessionHas('sukses');
        $this->assertSame(PesananStatus::DalamPerjalanan, $pesanan->fresh()->status);

        $this->post(route('sopir.pesanan.selesai', $pesanan))->assertSessionHas('sukses');
        $segar = $pesanan->fresh();
        $this->assertSame(PesananStatus::Selesai, $segar->status);
        $this->assertNotNull($segar->selesai_pada);
        $this->assertSame(StatusAmbulans::Tersedia, $this->ambulans->fresh()->status);
    }

    public function test_urutan_salah_ditolak_dengan_pesan_galat(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $this->actingAs($this->sopir);

        $this->post(route('sopir.pesanan.berangkat', $pesanan))->assertRedirect(route('sopir.dashboard'))->assertSessionHas('galat');
        $this->post(route('sopir.pesanan.selesai', $pesanan))->assertSessionHas('galat');
        $this->assertSame(PesananStatus::Ditugaskan, $pesanan->fresh()->status);

        $this->post(route('sopir.pesanan.terima', $pesanan));
        $this->post(route('sopir.pesanan.terima', $pesanan))->assertSessionHas('galat');
    }

    public function test_tolak_penugasan_wajib_alasan_dan_mengembalikan_ke_terverifikasi(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $this->actingAs($this->sopir);

        $this->post(route('sopir.pesanan.tolak', $pesanan), ['alasan' => ''])->assertSessionHasErrors('alasan');
        $this->assertSame(PesananStatus::Ditugaskan, $pesanan->fresh()->status);

        $this->post(route('sopir.pesanan.tolak', $pesanan), ['alasan' => 'Ban bocor'])->assertSessionHas('sukses');

        $segar = $pesanan->fresh();
        $this->assertSame(PesananStatus::Terverifikasi, $segar->status);
        $this->assertNull($segar->sopir_id);
        $this->assertNull($segar->ambulans_id);
        $this->assertSame(StatusAmbulans::Tersedia, $this->ambulans->fresh()->status);
    }

    public function test_sopir_tidak_bisa_menolak_setelah_menerima(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $this->actingAs($this->sopir)->post(route('sopir.pesanan.terima', $pesanan));

        $this->post(route('sopir.pesanan.tolak', $pesanan), ['alasan' => 'Berubah pikiran'])->assertSessionHas('galat');
        $this->assertSame(PesananStatus::Diterima, $pesanan->fresh()->status);
    }

    public function test_sopir_lain_tidak_bisa_mengubah_pesanan_bukan_miliknya(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $lain = User::factory()->sopir($this->faskes)->online()->create();

        $this->actingAs($lain);
        foreach (['terima', 'berangkat', 'selesai'] as $aksi) {
            $this->post(route("sopir.pesanan.$aksi", $pesanan))->assertForbidden();
        }
        $this->post(route('sopir.pesanan.tolak', $pesanan), ['alasan' => 'Bukan punya saya'])->assertForbidden();
        $this->postJson(route('sopir.pesanan.terima', $pesanan))->assertForbidden()->assertJson(['ok' => false]);

        $segar = $pesanan->fresh();
        $this->assertSame(PesananStatus::Ditugaskan, $segar->status);
        $this->assertSame($this->sopir->id, $segar->sopir_id);
    }

    public function test_aksi_json_mengikuti_konvensi(): void
    {
        $pesanan = $this->pesananDitugaskan();
        $this->actingAs($this->sopir);

        $this->postJson(route('sopir.pesanan.berangkat', $pesanan))->assertStatus(422)->assertJson(['ok' => false]);
        $this->postJson(route('sopir.pesanan.terima', $pesanan))->assertOk()->assertJsonPath('ok', true);
    }

    public function test_halaman_sopir_hanya_untuk_sopir(): void
    {
        $this->get(route('sopir.dashboard'))->assertRedirect(route('login'));
        $this->getJson(route('sopir.penugasan'))->assertUnauthorized()->assertJson(['ok' => false]);

        foreach ([$this->buatWarga(), $this->buatAdmin(), $this->buatPetugas($this->faskes)] as $user) {
            $this->actingAs($user)->get(route('sopir.dashboard'))->assertForbidden();
            $this->getJson(route('sopir.penugasan'))->assertForbidden()->assertJson(['ok' => false]);
            $this->postJson(route('sopir.lokasi'), ['lat' => 1, 'lng' => 1])->assertForbidden();
            $this->post(route('sopir.online'), ['online' => 1])->assertForbidden();
        }
    }
}
