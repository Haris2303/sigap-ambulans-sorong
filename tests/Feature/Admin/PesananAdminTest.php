<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisAmbulans;
use App\Enums\JenisLayanan;
use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPengguna;
use Tests\Concerns\MenyediakanRuteSementara;
use Tests\TestCase;

class PesananAdminTest extends TestCase
{
    use MembuatPengguna, MenyediakanRuteSementara, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sediakanRuteSementara();
        $this->admin = $this->buatAdmin();
    }

    private function pesanan(PesananStatus $status = PesananStatus::Menunggu, array $atribut = []): Pesanan
    {
        return Pesanan::factory()->berstatus($status)->create($atribut);
    }

    /** @return array{0: Faskes, 1: Ambulans, 2: User} */
    private function sumberDaya(string $namaFaskes = 'Faskes Satu'): array
    {
        $faskes = Faskes::factory()->create(['nama' => $namaFaskes]);
        $sopir = User::factory()->sopir($faskes)->online()->create(['name' => 'Sopir '.$namaFaskes]);
        $ambulans = Ambulans::factory()->create(['faskes_id' => $faskes->id, 'sopir_id' => $sopir->id]);

        return [$faskes, $ambulans, $sopir];
    }

    // T5.2 daftar

    public function test_daftar_mengurutkan_gawat_dulu_lalu_terlama_dan_status_final_terakhir(): void
    {
        $biasaBaru = $this->pesanan(atribut: ['created_at' => now()->subMinutes(5)]);
        $biasaLama = $this->pesanan(atribut: ['created_at' => now()->subHour()]);
        $gawat = $this->pesanan(atribut: ['tingkat_darurat' => 'gawat', 'created_at' => now()->subMinutes(2)]);
        $selesai = $this->pesanan(PesananStatus::Selesai, ['created_at' => now()->subDays(2)]);

        $this->actingAs($this->admin)->get(route('admin.pesanan.index'))->assertOk();

        $urutan = $this->actingAs($this->admin)->get(route('admin.pesanan.index'))->viewData('daftar')->pluck('id')->all();
        $this->assertSame([$gawat->id, $biasaLama->id, $biasaBaru->id, $selesai->id], $urutan);
    }

    public function test_filter_status_jenis_tanggal_dan_kata_kunci(): void
    {
        $a = $this->pesanan(atribut: ['nama_pasien' => 'Budi Kecil', 'jenis_layanan' => JenisLayanan::Persalinan]);
        $b = $this->pesanan(PesananStatus::Terverifikasi, ['nama_pasien' => 'Siti Besar', 'created_at' => now()->subDays(10)]);

        $ids = fn (array $q) => $this->actingAs($this->admin)->get(route('admin.pesanan.index', $q))->viewData('daftar')->pluck('id')->all();

        $this->assertSame([$b->id], $ids(['status' => 'terverifikasi']));
        $this->assertSame([$a->id], $ids(['jenis_layanan' => 'persalinan']));
        $this->assertSame([$a->id], $ids(['dari' => now()->subDays(1)->toDateString()]));
        $this->assertSame([$b->id], $ids(['sampai' => now()->subDays(5)->toDateString()]));
        $this->assertSame([$b->id], $ids(['q' => 'Besar']));
        $this->assertSame([$a->id], $ids(['q' => $a->kode]));
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $ids([]));
    }

    // D6: pencarian ?q= (kode pesanan atau nama pasien, LIKE dengan parameter terikat)

    public function test_pencarian_q_mencari_kode_dan_nama_pasien_tetapi_tidak_lokasi(): void
    {
        $a = $this->pesanan(atribut: ['nama_pasien' => 'Budi Santoso', 'lokasi_jemput' => 'Jalan Melati Nomor 5']);
        $b = $this->pesanan(atribut: ['nama_pasien' => 'Siti Aminah']);

        $ids = fn (string $q) => $this->actingAs($this->admin)->get(route('admin.pesanan.index', ['q' => $q]))->viewData('daftar')->pluck('id')->all();

        $this->assertSame([$a->id], $ids('santoso'));
        $this->assertSame([$a->id], $ids('Budi S'));
        $this->assertSame([$b->id], $ids($b->kode));
        $this->assertSame([$b->id], $ids(substr($b->kode, -4)), 'Sebagian kode juga cocok.');
        $this->assertSame([], $ids('Melati'), 'Lokasi jemput bukan bagian pencarian.');
        $this->assertSame([$a->id], $ids('  Budi  '), 'Spasi di tepi dipangkas.');
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $ids(''));
    }

    public function test_pencarian_tidak_menganggap_wildcard_dan_tanda_kutip_sebagai_perintah(): void
    {
        $biasa = $this->pesanan(atribut: ['nama_pasien' => 'Pasien Biasa']);
        $persen = $this->pesanan(atribut: ['nama_pasien' => 'Diskon 100% Lunas']);
        $garis = $this->pesanan(atribut: ['nama_pasien' => 'Nama_Dengan_Garis']);

        $ids = fn (string $q) => $this->actingAs($this->admin)->get(route('admin.pesanan.index', ['q' => $q]))->assertOk()->viewData('daftar')->pluck('id')->all();

        $this->assertSame([$persen->id], $ids('100%'), '% bukan wildcard.');
        $this->assertSame([$persen->id], $ids('%'), 'Hanya nama yang memuat tanda persen.');
        $this->assertSame([$garis->id], $ids('Nama_Dengan'), '_ bukan wildcard satu huruf.');
        $this->assertSame([], $ids('Nama1Dengan'));
        $this->assertSame([], $ids("' OR 1=1 --"), 'Tanda kutip diperlakukan sebagai teks.');
        $this->assertSame([], $ids('x\\'), 'Garis miring terbalik diperlakukan sebagai teks.');
        $this->assertNotNull($biasa->id);
    }

    public function test_pencarian_menolak_kata_kunci_terlalu_panjang(): void
    {
        $this->actingAs($this->admin)->get(route('admin.pesanan.index', ['q' => str_repeat('a', 101)]))->assertSessionHasErrors('q');
    }

    public function test_kolom_cari_di_bilah_atas_mengisi_ulang_kata_kunci(): void
    {
        $this->pesanan(atribut: ['nama_pasien' => 'Siti Aminah']);

        $html = $this->actingAs($this->admin)->get(route('admin.pesanan.index', ['q' => 'Siti']))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<input[^>]*type="search"[^>]*name="q"[^>]*value="Siti"/', $html);
        $this->assertStringContainsString('Siti Aminah', $html);
    }

    // D6: tab penyaring status (?tab=)

    public function test_tab_perlu_tindakan_dan_berjalan_menyaring_status(): void
    {
        $menunggu = $this->pesanan(PesananStatus::Menunggu);
        $verif = $this->pesanan(PesananStatus::Terverifikasi);
        $ditugaskan = $this->pesanan(PesananStatus::Ditugaskan);
        $perjalanan = $this->pesanan(PesananStatus::DalamPerjalanan);
        $selesai = $this->pesanan(PesananStatus::Selesai);
        $ditolak = $this->pesanan(PesananStatus::Ditolak);

        $ids = fn (array $q) => $this->actingAs($this->admin)->get(route('admin.pesanan.index', $q))->assertOk()->viewData('daftar')->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$menunggu->id, $verif->id], $ids(['tab' => 'perlu-tindakan']));
        $this->assertEqualsCanonicalizing([$ditugaskan->id, $perjalanan->id], $ids(['tab' => 'berjalan']));
        $this->assertCount(6, $ids(['tab' => 'semua']));
        $this->assertCount(6, $ids([]), 'Tanpa tab sama dengan Semua.');
        $this->assertCount(6, $ids(['tab' => 'ngawur']), 'Tab tidak dikenal kembali ke Semua.');
        $this->assertNotNull($selesai->id.$ditolak->id);
    }

    public function test_tab_digabung_dengan_pencarian_dan_filter_lain(): void
    {
        $cocok = $this->pesanan(PesananStatus::Menunggu, ['nama_pasien' => 'Budi Menunggu']);
        $this->pesanan(PesananStatus::Terverifikasi, ['nama_pasien' => 'Siti Verifikasi']);
        $this->pesanan(PesananStatus::Selesai, ['nama_pasien' => 'Budi Selesai']);

        $ids = fn (array $q) => $this->actingAs($this->admin)->get(route('admin.pesanan.index', $q))->viewData('daftar')->pluck('id')->all();

        $this->assertSame([$cocok->id], $ids(['tab' => 'perlu-tindakan', 'q' => 'Budi']));
        $this->assertSame([], $ids(['tab' => 'berjalan', 'q' => 'Budi']));
        $this->assertSame([$cocok->id], $ids(['tab' => 'perlu-tindakan', 'status' => 'menunggu']));
        $this->assertSame([], $ids(['tab' => 'perlu-tindakan', 'status' => 'selesai']));
    }

    public function test_daftar_menampilkan_tab_dan_tabel_di_dalam_kartu_tanpa_padding(): void
    {
        $this->pesanan(PesananStatus::Menunggu, ['nama_pasien' => 'Pasien Daftar']);

        $html = $this->actingAs($this->admin)->get(route('admin.pesanan.index', ['tab' => 'perlu-tindakan', 'q' => 'Pasien']))->assertOk()->getContent();

        // Tab berupa tautan yang mempertahankan kata kunci dan hanya mengganti tab; tab aktif bertanda halaman.
        $this->assertStringContainsString('aria-label="Filter status pesanan"', $html);
        foreach (['semua' => 'Semua', 'perlu-tindakan' => 'Perlu tindakan', 'berjalan' => 'Berjalan'] as $nilai => $label) {
            $this->assertMatchesRegularExpression('~<a href="[^"]*tab='.$nilai.'[^"]*q=Pasien[^"]*"[^>]*>\s*'.$label.'\s*</a>|<a href="[^"]*q=Pasien[^"]*tab='.$nilai.'[^"]*"[^>]*>\s*'.$label.'\s*</a>~', $html, $label);
        }
        $this->assertMatchesRegularExpression('~<a href="[^"]*tab=perlu-tindakan[^"]*"[^>]*aria-current="page"~', $html);

        // Tabel di dalam kartu tanpa padding: pembungkus kartu, bukan tabel berbayang sendiri.
        $kartu = strpos($html, 'overflow-hidden');
        $tab = strpos($html, 'aria-label="Filter status pesanan"');
        $tabel = strpos($html, '<table');
        $this->assertNotFalse($kartu);
        $this->assertLessThan($tab, $kartu, 'Tab berada di dalam kartu.');
        $this->assertLessThan($tabel, $tab, 'Tabel berada di bawah tab, di kartu yang sama.');
        $this->assertStringContainsString('<div class="overflow-x-auto">', $html, 'Tabel tanpa bingkai ganda di dalam kartu.');
        $this->assertStringContainsString('Pasien Daftar', $html);
        $this->assertStringContainsString('rounded-full px-2.5', $html);
    }

    public function test_tab_tanpa_hasil_menampilkan_keadaan_kosong_per_tab(): void
    {
        $this->pesanan(PesananStatus::Selesai);

        $this->actingAs($this->admin)->get(route('admin.pesanan.index', ['tab' => 'berjalan']))
            ->assertOk()->assertSee('Belum ada pesanan di tab ini.')->assertDontSee('Atur ulang');
    }

    public function test_daftar_kosong_dan_tidak_cocok(): void
    {
        $this->actingAs($this->admin)->get(route('admin.pesanan.index'))->assertSee('Belum ada pesanan.');
        $this->pesanan();
        $this->actingAs($this->admin)->get(route('admin.pesanan.index', ['q' => 'zzzz']))
            ->assertSee('Tidak ada pesanan yang cocok dengan filter.')->assertSee('Atur ulang');
    }

    public function test_daftar_berpaginasi(): void
    {
        Pesanan::factory()->count(23)->create();

        $this->actingAs($this->admin)->get(route('admin.pesanan.index'))->assertSee('Menampilkan 1–20 dari 23');
    }

    // T5.2 detail dan aksi

    public function test_detail_menampilkan_pemohon_lokasi_dan_aksi_sesuai_status(): void
    {
        $menunggu = $this->pesanan(atribut: ['nama_pasien' => 'Pasien Menunggu']);
        $this->actingAs($this->admin)->get(route('admin.pesanan.show', $menunggu))
            ->assertOk()->assertSee('Pasien Menunggu')->assertSee('Jalan Ahmad Yani')
            ->assertSee('Verifikasi')->assertSee('Tolak pesanan')
            ->assertDontSee('Tugaskan ambulans dan sopir')->assertDontSee('Batalkan pesanan');

        $terverifikasi = $this->pesanan(PesananStatus::Terverifikasi);
        $this->actingAs($this->admin)->get(route('admin.pesanan.show', $terverifikasi))
            ->assertOk()->assertSee('Tugaskan ambulans dan sopir')->assertSee('Batalkan pesanan')->assertDontSee('Tolak pesanan');

        $ditugaskan = $this->pesanan(PesananStatus::Ditugaskan);
        $this->actingAs($this->admin)->get(route('admin.pesanan.show', $ditugaskan))
            ->assertOk()->assertSee('Lepas penugasan')->assertSee('Batalkan pesanan');

        $diterima = $this->pesanan(PesananStatus::Diterima);
        $this->actingAs($this->admin)->get(route('admin.pesanan.show', $diterima))
            ->assertOk()->assertDontSee('Lepas penugasan')->assertSee('Batalkan pesanan');

        $selesai = $this->pesanan(PesananStatus::Selesai);
        $this->actingAs($this->admin)->get(route('admin.pesanan.show', $selesai))
            ->assertOk()->assertSee('Tidak ada aksi yang tersedia.')->assertDontSee('Batalkan pesanan');
    }

    public function test_verifikasi(): void
    {
        $pesanan = $this->pesanan();

        $this->actingAs($this->admin)->put(route('admin.pesanan.verifikasi', $pesanan))
            ->assertRedirect(route('admin.pesanan.show', $pesanan))->assertSessionHas('sukses', 'Pesanan berhasil diverifikasi.');

        $pesanan->refresh();
        $this->assertSame(PesananStatus::Terverifikasi, $pesanan->status);
        $this->assertSame($this->admin->id, $pesanan->diverifikasi_oleh_id);
        $this->assertSame(Role::Admin, $pesanan->log->last()->peran);
    }

    public function test_verifikasi_ulang_ditolak_dengan_pesan_galat(): void
    {
        $pesanan = $this->pesanan(PesananStatus::Terverifikasi);

        $this->actingAs($this->admin)->put(route('admin.pesanan.verifikasi', $pesanan))
            ->assertRedirect(route('admin.pesanan.show', $pesanan))->assertSessionHas('galat');
        $this->assertSame(PesananStatus::Terverifikasi, $pesanan->fresh()->status);
    }

    public function test_tolak_wajib_alasan(): void
    {
        $pesanan = $this->pesanan();

        $this->actingAs($this->admin)->put(route('admin.pesanan.tolak', $pesanan), ['alasan' => ''])
            ->assertSessionHasErrors('alasan');
        $this->assertSame(PesananStatus::Menunggu, $pesanan->fresh()->status);

        $this->put(route('admin.pesanan.tolak', $pesanan), ['alasan' => 'Lokasi tidak jelas'])
            ->assertSessionHas('sukses', 'Pesanan berhasil ditolak.');
        $this->assertSame('Lokasi tidak jelas', $pesanan->fresh()->alasan_penolakan);
        $this->assertSame(PesananStatus::Ditolak, $pesanan->fresh()->status);
    }

    public function test_batalkan_wajib_alasan_dan_tidak_untuk_menunggu(): void
    {
        $terverifikasi = $this->pesanan(PesananStatus::Terverifikasi);
        $this->actingAs($this->admin)->put(route('admin.pesanan.batalkan', $terverifikasi), [])->assertSessionHasErrors('alasan');
        $this->put(route('admin.pesanan.batalkan', $terverifikasi), ['alasan' => 'Pemohon menelepon'])
            ->assertSessionHas('sukses', 'Pesanan berhasil dibatalkan.');
        $this->assertSame(PesananStatus::Dibatalkan, $terverifikasi->fresh()->status);

        $menunggu = $this->pesanan();
        $this->put(route('admin.pesanan.batalkan', $menunggu), ['alasan' => 'x'])->assertSessionHas('galat');
        $this->assertSame(PesananStatus::Menunggu, $menunggu->fresh()->status);
    }

    public function test_aksi_pesanan_hanya_untuk_dinkes(): void
    {
        $pesanan = $this->pesanan();

        foreach ([$this->buatWarga(), $this->buatPetugas(), $this->buatSopir()] as $user) {
            $this->actingAs($user)->put(route('admin.pesanan.verifikasi', $pesanan))->assertForbidden();
            $this->actingAs($user)->get(route('admin.pesanan.show', $pesanan))->assertForbidden();
            $this->actingAs($user)->get(route('admin.pesanan.index'))->assertForbidden();
        }
        $this->assertSame(PesananStatus::Menunggu, $pesanan->fresh()->status);
    }

    // T5.3 tugaskan dan lepas

    public function test_tugaskan_berhasil(): void
    {
        [, $ambulans, $sopir] = $this->sumberDaya();
        $pesanan = $this->pesanan(PesananStatus::Terverifikasi);

        $this->actingAs($this->admin)->put(route('admin.pesanan.tugaskan', $pesanan), ['ambulans_id' => $ambulans->id, 'sopir_id' => $sopir->id])
            ->assertRedirect(route('admin.pesanan.show', $pesanan))->assertSessionHas('sukses', 'Ambulans dan sopir berhasil ditugaskan.');

        $pesanan->refresh();
        $this->assertSame(PesananStatus::Ditugaskan, $pesanan->status);
        $this->assertSame($ambulans->id, $pesanan->ambulans_id);
        $this->assertSame($sopir->id, $pesanan->sopir_id);
        $this->assertSame(StatusAmbulans::Bertugas, $ambulans->fresh()->status);
    }

    public function test_tugaskan_semua_penolakan_mengembalikan_galat_tanpa_mengubah_data(): void
    {
        [$faskes, $ambulans, $sopir] = $this->sumberDaya('Faskes A');
        [, , $sopirLain] = $this->sumberDaya('Faskes B');
        $servis = Ambulans::factory()->servis()->create(['faskes_id' => $faskes->id]);
        $sopirOffline = User::factory()->sopir($faskes)->create();
        $pesanan = $this->pesanan(PesananStatus::Terverifikasi);

        $kasus = [
            'ambulans servis' => [$servis->id, $sopir->id],
            'sopir offline' => [$ambulans->id, $sopirOffline->id],
            'beda Faskes' => [$ambulans->id, $sopirLain->id],
            'bukan sopir' => [$ambulans->id, $this->buatWarga()->id],
        ];

        foreach ($kasus as $nama => [$idAmbulans, $idSopir]) {
            $this->actingAs($this->admin)->put(route('admin.pesanan.tugaskan', $pesanan), ['ambulans_id' => $idAmbulans, 'sopir_id' => $idSopir])
                ->assertRedirect(route('admin.pesanan.show', $pesanan))->assertSessionHas('galat');

            $segar = $pesanan->fresh();
            $this->assertSame(PesananStatus::Terverifikasi, $segar->status, $nama);
            $this->assertNull($segar->ambulans_id, $nama);
            $this->assertSame(StatusAmbulans::Tersedia, $ambulans->fresh()->status, $nama);
        }
    }

    public function test_tugaskan_sopir_yang_sibuk_ditolak(): void
    {
        [$faskes, $ambulans, $sopir] = $this->sumberDaya();
        $ambulans2 = Ambulans::factory()->create(['faskes_id' => $faskes->id]);
        $this->pesanan(PesananStatus::Diterima, ['sopir_id' => $sopir->id, 'ambulans_id' => $ambulans->id]);
        $pesanan = $this->pesanan(PesananStatus::Terverifikasi);

        $this->actingAs($this->admin)->put(route('admin.pesanan.tugaskan', $pesanan), ['ambulans_id' => $ambulans2->id, 'sopir_id' => $sopir->id])
            ->assertSessionHas('galat');
        $this->assertSame(PesananStatus::Terverifikasi, $pesanan->fresh()->status);
    }

    public function test_tugaskan_validasi_isian_wajib(): void
    {
        $pesanan = $this->pesanan(PesananStatus::Terverifikasi);

        $this->actingAs($this->admin)->put(route('admin.pesanan.tugaskan', $pesanan), [])
            ->assertSessionHasErrors(['ambulans_id', 'sopir_id']);
    }

    public function test_pilihan_penugasan_hanya_sumber_daya_valid_dan_dikelompokkan_per_faskes(): void
    {
        [, $ambulansA, ] = $this->sumberDaya('Faskes A');
        $faskesB = Faskes::factory()->create(['nama' => 'Faskes B']);
        $darurat = Ambulans::factory()->darurat()->create(['faskes_id' => $faskesB->id, 'plat_nomor' => 'PB-DARURAT-1']);
        $servis = Ambulans::factory()->servis()->create(['faskes_id' => $faskesB->id, 'plat_nomor' => 'PB-SERVIS-1']);
        User::factory()->sopir($faskesB)->online()->create(['name' => 'Sopir B Online']);
        User::factory()->sopir($faskesB)->create(['name' => 'Sopir B Offline']);
        $pesanan = $this->pesanan(PesananStatus::Terverifikasi, ['jenis_layanan' => JenisLayanan::Darurat]);

        $opsi = $this->actingAs($this->admin)->get(route('admin.pesanan.show', $pesanan))->assertOk()->viewData('opsiPenugasan');

        $this->assertSame(['Faskes B', 'Faskes A'], array_keys($opsi['ambulans']), 'ambulans darurat disarankan lebih dulu');
        $this->assertStringContainsString('disarankan', $opsi['ambulans']['Faskes B'][$darurat->id]);
        $this->assertArrayHasKey($ambulansA->id, $opsi['ambulans']['Faskes A']);
        $this->assertArrayNotHasKey($servis->id, $opsi['ambulans']['Faskes B']);
        $this->assertSame(['Sopir B Online'], array_values($opsi['sopir']['Faskes B']));

        $html = $this->get(route('admin.pesanan.show', $pesanan))->getContent();
        $this->assertStringContainsString('<optgroup label="Faskes B">', $html);
    }

    public function test_saran_persalinan_mendahulukan_puskesmas(): void
    {
        $rs = Faskes::factory()->rs()->create(['nama' => 'RS Satu']);
        $pkm = Faskes::factory()->puskesmas()->create(['nama' => 'Puskesmas Satu']);
        Ambulans::factory()->create(['faskes_id' => $rs->id, 'jenis' => JenisAmbulans::Transport]);
        Ambulans::factory()->create(['faskes_id' => $pkm->id, 'jenis' => JenisAmbulans::Transport]);
        $pesanan = $this->pesanan(PesananStatus::Terverifikasi, ['jenis_layanan' => JenisLayanan::Persalinan]);

        $opsi = $this->actingAs($this->admin)->get(route('admin.pesanan.show', $pesanan))->viewData('opsiPenugasan');

        $this->assertSame(['Puskesmas Satu', 'RS Satu'], array_keys($opsi['ambulans']));
    }

    public function test_tanpa_ambulans_atau_sopir_menampilkan_peringatan_bukan_formulir(): void
    {
        $pesanan = $this->pesanan(PesananStatus::Terverifikasi);

        $this->actingAs($this->admin)->get(route('admin.pesanan.show', $pesanan))
            ->assertOk()->assertSee('Tidak ada ambulans yang tersedia.')->assertDontSee('Pilih ambulans');
    }

    public function test_lepas_penugasan_mengembalikan_ke_terverifikasi_dan_membebaskan_ambulans(): void
    {
        [, $ambulans, $sopir] = $this->sumberDaya();
        $pesanan = $this->pesanan(PesananStatus::Terverifikasi);
        $this->actingAs($this->admin)->put(route('admin.pesanan.tugaskan', $pesanan), ['ambulans_id' => $ambulans->id, 'sopir_id' => $sopir->id]);

        $this->put(route('admin.pesanan.lepas', $pesanan), ['alasan' => 'Sopir berhalangan'])
            ->assertSessionHas('sukses', 'Penugasan berhasil dilepas.');

        $pesanan->refresh();
        $this->assertSame(PesananStatus::Terverifikasi, $pesanan->status);
        $this->assertNull($pesanan->sopir_id);
        $this->assertSame(StatusAmbulans::Tersedia, $ambulans->fresh()->status);

        $this->put(route('admin.pesanan.lepas', $pesanan))->assertSessionHas('galat');
    }

    public function test_batalkan_pesanan_yang_sedang_berjalan_membebaskan_ambulans(): void
    {
        [, $ambulans, $sopir] = $this->sumberDaya();
        $pesanan = $this->pesanan(PesananStatus::Terverifikasi);
        $this->actingAs($this->admin)->put(route('admin.pesanan.tugaskan', $pesanan), ['ambulans_id' => $ambulans->id, 'sopir_id' => $sopir->id]);

        $this->put(route('admin.pesanan.batalkan', $pesanan), ['alasan' => 'Pasien dibawa keluarga'])
            ->assertSessionHas('sukses');

        $this->assertSame(PesananStatus::Dibatalkan, $pesanan->fresh()->status);
        $this->assertSame(StatusAmbulans::Tersedia, $ambulans->fresh()->status);
    }
}
