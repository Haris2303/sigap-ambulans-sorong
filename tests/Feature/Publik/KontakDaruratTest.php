<?php

namespace Tests\Feature\Publik;

use App\Enums\PesananStatus;
use App\Models\Pesanan;
use App\Support\KontakDarurat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\MembuatPengguna;
use Tests\TestCase;

/** Kontak telepon darurat Dinkes (audit UX T8): jalur cadangan bila aplikasi tidak dapat dipakai. */
class KontakDaruratTest extends TestCase
{
    use MembuatPengguna, RefreshDatabase;

    private function atur(?string $nomor, ?string $nama = 'Dinkes'): void
    {
        config(['sigap.kontak_darurat' => ['nama' => $nama, 'nomor' => $nomor]]);
    }

    public function test_aplikasi_tidak_pernah_mengisi_nomor_sendiri(): void
    {
        $this->assertNull(config('sigap.kontak_darurat.nomor'), 'Nomor hanya dari .env (KONTAK_DARURAT_NOMOR), milik Dinkes.');
        $this->assertNull(KontakDarurat::data());

        $env = (string) file_get_contents(base_path('.env.example'));
        $this->assertMatchesRegularExpression('/^KONTAK_DARURAT_NOMOR=$/m', $env, 'Contoh .env membiarkan nomor kosong.');
        $this->assertMatchesRegularExpression('/^KONTAK_DARURAT_NAMA=Dinkes$/m', $env);
    }

    public function test_data_kosong_atau_bukan_nomor_dianggap_belum_diatur(): void
    {
        foreach ([null, '', '   ', '12', 'tidak ada', '--'] as $nomor) {
            $this->atur($nomor);
            $this->assertNull(KontakDarurat::data(), var_export($nomor, true));
        }
    }

    public function test_tautan_tel_hanya_berisi_angka_dan_plus_di_depan(): void
    {
        $this->atur('(0951) 123-456');
        $this->assertSame(['nama' => 'Dinkes', 'nomor' => '(0951) 123-456', 'tel' => '0951123456'], KontakDarurat::data());

        $this->atur('+62 951 123 456');
        $this->assertSame('+62951123456', KontakDarurat::data()['tel']);

        $this->atur('0951.123.456 ext 7');
        $this->assertSame('09511234567', KontakDarurat::data()['tel']);
    }

    public function test_nama_bawaan_dinkes_bila_kosong(): void
    {
        $this->atur('0951123456', '  ');
        $this->assertSame('Dinkes', KontakDarurat::data()['nama']);

        $this->atur('0951123456', 'Dinkes Kota Sorong');
        $this->assertSame('Dinkes Kota Sorong', KontakDarurat::data()['nama']);
    }

    public function test_komponen_tidak_menampilkan_apa_pun_tanpa_nomor(): void
    {
        $this->atur(null);

        $this->assertSame('', trim(Blade::render('<x-ui.kontak-darurat>Keadaan darurat?</x-ui.kontak-darurat>')));
        $this->assertSame('', trim(Blade::render('<x-ui.kontak-darurat varian="tautan">Keadaan darurat?</x-ui.kontak-darurat>')));
    }

    public function test_varian_tombol_berupa_tautan_tel_48px_dengan_nomor_terlihat_dan_kalimat_pengantar(): void
    {
        $this->atur('0951 123456');

        $html = Blade::render('<x-ui.kontak-darurat class="mt-6">Keadaan darurat?</x-ui.kontak-darurat>');

        $this->assertStringContainsString('<div class="mt-6">', $html);
        $this->assertStringContainsString('<p class="mb-2 text-body-m text-on-surface-variant">Keadaan darurat?</p>', $html);
        $this->assertMatchesRegularExpression('/<a href="tel:0951123456"[^>]*border border-outline text-primary[^>]*>\s*<svg[^>]*>.*?<\/svg>\s*Hubungi Dinkes di 0951 123456\s*<\/a>/s', $html);
        $this->assertStringContainsString('target-sentuh', $html);
        $this->assertStringContainsString('min-h-10', $html);
    }

    public function test_varian_tombol_tanpa_kalimat_pengantar_tidak_membuat_paragraf_kosong(): void
    {
        $this->atur('0951123456');

        $html = Blade::render('<x-ui.kontak-darurat />');

        $this->assertDoesNotMatchRegularExpression('/<p[ >]/', $html);
        $this->assertStringContainsString('href="tel:0951123456"', $html);
    }

    public function test_varian_tautan_berupa_kalimat_dengan_tautan_tel_yang_dapat_diketuk(): void
    {
        $this->atur('+62 951 123456', 'Dinkes Kota Sorong');

        $html = Blade::render('<x-ui.kontak-darurat varian="tautan" class="mb-6">Formulir tidak bisa dipakai?</x-ui.kontak-darurat>');

        $this->assertMatchesRegularExpression('/<p class="text-body-m text-on-surface-variant mb-6">\s*Formulir tidak bisa dipakai\?\s*<a href="tel:\+62951123456" class="lapisan-keadaan target-sentuh[^"]*">Hubungi Dinkes Kota Sorong di \+62 951 123456<\/a>\.\s*<\/p>/s', $html);
    }

    public function test_nama_dan_nomor_di_escape(): void
    {
        $this->atur('0951123456 <b>x</b>', '<script>alert(1)</script>');

        $html = Blade::render('<x-ui.kontak-darurat />');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<b>x</b>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    // ---------- halaman ----------

    public function test_beranda_publik_menampilkan_kontak_hanya_bila_nomor_diatur(): void
    {
        $this->get(route('beranda'))->assertOk()->assertDontSee('Keadaan darurat dan aplikasi ini tidak bisa dipakai?')->assertDontSee('tel:', false);

        $this->atur('0951123456');
        $this->get(route('beranda'))->assertOk()
            ->assertSee('Keadaan darurat dan aplikasi ini tidak bisa dipakai?')
            ->assertSee('href="tel:0951123456"', false)
            ->assertSee('Hubungi Dinkes di 0951123456');
    }

    public function test_halaman_galat_500_dan_419_menampilkan_kontak_sedangkan_403_dan_404_tidak(): void
    {
        $this->atur('0951123456');
        foreach ([403, 404, 419, 500] as $kode) {
            Route::get('/_uji/galat/'.$kode, fn () => abort($kode))->middleware('web');
        }

        foreach ([419, 500] as $kode) {
            $this->get('/_uji/galat/'.$kode)->assertStatus($kode)->assertSee('Keadaan darurat?')->assertSee('href="tel:0951123456"', false);
        }
        foreach ([403, 404] as $kode) {
            $this->get('/_uji/galat/'.$kode)->assertStatus($kode)->assertDontSee('tel:', false);
        }
    }

    public function test_formulir_pesanan_menampilkan_tautan_telepon_di_atas_formulir(): void
    {
        $warga = $this->buatWarga();
        $this->actingAs($warga)->get(route('warga.pesanan.buat'))->assertOk()->assertDontSee('tel:', false);

        $this->atur('0951123456');
        $html = $this->actingAs($warga)->get(route('warga.pesanan.buat'))->assertOk()->getContent();

        $this->assertStringContainsString('Formulir tidak bisa dipakai?', $html);
        $this->assertStringContainsString('href="tel:0951123456"', $html);
        $this->assertLessThan(strpos($html, '<form method="POST" action="'.route('warga.pesanan.tinjau').'"'), strpos($html, 'href="tel:0951123456"'), 'Tautan ada di atas formulir pesanan.');
    }

    public function test_halaman_pelacakan_menampilkan_tombol_hubungi_selama_pesanan_belum_final(): void
    {
        $warga = $this->buatWarga();
        $aktif = Pesanan::factory()->create(['user_id' => $warga->id]);
        $selesai = Pesanan::factory()->berstatus(PesananStatus::Selesai)->create(['user_id' => $warga->id]);

        $this->actingAs($warga)->get(route('warga.pesanan.show', $aktif))->assertOk()->assertDontSee('tel:0951', false);

        $this->atur('0951123456');
        $this->actingAs($warga)->get(route('warga.pesanan.show', $aktif))->assertOk()
            ->assertSee('href="tel:0951123456"', false)->assertSee('Hubungi Dinkes di 0951123456');
        $this->actingAs($warga)->get(route('warga.pesanan.show', $selesai))->assertOk()
            ->assertDontSee('href="tel:0951123456"', false);
    }
}
