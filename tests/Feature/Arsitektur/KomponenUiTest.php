<?php

namespace Tests\Feature\Arsitektur;

use App\Enums\JenisFaskes;
use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\PesananLog;
use App\Models\User;
use App\Support\Tampilan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\ViewException;
use Tests\TestCase;

class KomponenUiTest extends TestCase
{
    use RefreshDatabase;

    private function render(string $blade, array $data = []): string
    {
        return Blade::render($blade, $data);
    }

    public function test_ikon_merender_svg_dan_menolak_ikon_di_luar_daftar(): void
    {
        $html = $this->render('<x-ui.ikon nama="house" :ukuran="18" class="text-primary" />');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('width="18"', $html);
        $this->assertStringContainsString('stroke-width="1.75"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('text-primary', $html);
        $this->assertStringContainsString('width="24"', $this->render('<x-ui.ikon nama="house" />'));

        // Skala ikon arah B (docs/RETEMA_B.md 6.13): 16/17/18 dalam tombol, 20 tombol keluar, 21 menu/chip/tombol ikon, 22/24/26.
        foreach ([16, 17, 18, 20, 21, 22, 24, 26] as $ukuran) {
            $this->assertStringContainsString('width="'.$ukuran.'"', $this->render('<x-ui.ikon nama="house" :ukuran="$u" />', ['u' => $ukuran]));
        }

        foreach ([15, 19, 23, 28] as $salah) {
            try {
                $this->render('<x-ui.ikon nama="house" :ukuran="$u" />', ['u' => $salah]);
                $this->fail('Ukuran ikon '.$salah.' harus ditolak.');
            } catch (ViewException $e) {
                $this->assertStringContainsString('16, 17, 18, 20, 21, 22, 24, atau 26', $e->getMessage());
            }
        }

        try {
            $this->render('<x-ui.ikon nama="tidak-ada" />');
            $this->fail('Ikon di luar daftar harus ditolak.');
        } catch (ViewException $e) {
            $this->assertStringContainsString('Ikon "tidak-ada" tidak ada di daftar ikon', $e->getMessage());
        }
    }

    public function test_setiap_ikon_dalam_daftar_kit_dapat_dirender(): void
    {
        $daftar = ['house', 'layout-dashboard', 'clipboard-list', 'list-checks', 'users', 'user', 'building-2',
            'stethoscope', 'bed-double', 'file-text', 'download', 'settings', 'log-out', 'menu', 'x', 'plus',
            'pencil', 'search', 'check', 'circle-check', 'circle-x', 'ban', 'triangle-alert', 'info', 'clock',
            'phone', 'map-pin', 'navigation', 'crosshair', 'refresh-cw', 'bell', 'chevron-down', 'chevron-right',
            'arrow-left', 'hospital', 'user-check'];

        foreach ($daftar as $nama) {
            $this->assertStringContainsString('<svg', $this->render('<x-ui.ikon :nama="$n" />', ['n' => $nama]), $nama);
        }
    }

    public function test_tombol_semua_nada_ukuran_dan_bentuk(): void
    {
        $utama = $this->render('<x-ui.tombol tipe="submit">Simpan</x-ui.tombol>');
        $this->assertStringContainsString('<button type="submit"', $utama);
        $this->assertStringContainsString('bg-primary text-on-primary', $utama);
        $this->assertStringContainsString('disabled:bg-on-surface/12 disabled:text-on-surface/38', $utama);
        // Sudut md, tinggi 44px di lg ke atas dan 48px di bawahnya (DESIGN.md bagian 3), padding horizontal 20px.
        foreach (['lapisan-keadaan', 'target-sentuh', 'rounded-md', 'min-h-12', 'lg:min-h-11', 'px-5', 'text-label-l'] as $kelas) {
            $this->assertStringContainsString($kelas, $utama);
        }
        $this->assertStringNotContainsString('rounded-full', $utama);

        // tonal dan sekunder sama-sama menjadi varian secondary (DESIGN.md 6.2): tonal kontainer dengan batas outline-variant.
        foreach (['sekunder', 'tonal'] as $nada) {
            $this->assertStringContainsString('bg-surface-container text-on-surface border border-outline-variant', $this->render('<x-ui.tombol nada="'.$nada.'">A</x-ui.tombol>'));
        }
        $this->assertStringContainsString('bg-error-container text-on-error-container', $this->render('<x-ui.tombol nada="bahaya">A</x-ui.tombol>'));
        $this->assertStringContainsString('text-inverse-primary', $this->render('<x-ui.tombol nada="terbalik">A</x-ui.tombol>'));
        $this->assertStringContainsString('bg-surface-container-lowest text-secondary', $this->render('<x-ui.tombol nada="on-secondary">A</x-ui.tombol>'));

        // on-primary dipertahankan persis (dipakai kartu statistik utama primary sampai R4).
        $diAtasUtama = $this->render('<x-ui.tombol nada="on-primary">Verifikasi pesanan</x-ui.tombol>');
        $this->assertStringContainsString('bg-on-primary text-primary', $diAtasUtama);
        $this->assertStringContainsString('disabled:bg-on-surface/12 disabled:text-on-surface/38', $diAtasUtama);
        foreach (['lapisan-keadaan', 'target-sentuh', 'rounded-md', 'min-h-12'] as $kelas) {
            $this->assertStringContainsString($kelas, $diAtasUtama);
        }

        $hantu = $this->render('<x-ui.tombol nada="hantu">A</x-ui.tombol>');
        $this->assertStringContainsString('text-primary', $hantu);
        $this->assertStringContainsString('px-3', $hantu);
        $this->assertStringNotContainsString('bg-primary', $hantu);

        // ukuran="sm" baru (36/40px); "lg" lama kini setara bawaan (DESIGN.md bagian 3).
        $this->assertStringContainsString('min-h-10 lg:min-h-9', $this->render('<x-ui.tombol ukuran="sm">A</x-ui.tombol>'));
        $this->assertStringContainsString('min-h-12 lg:min-h-11', $this->render('<x-ui.tombol ukuran="lg">A</x-ui.tombol>'));
        $this->assertStringContainsString('w-full', $this->render('<x-ui.tombol :blok="true">A</x-ui.tombol>'));

        $tautan = $this->render('<x-ui.tombol href="https://contoh.test/x" ikon="plus">Tambah</x-ui.tombol>');
        $this->assertStringContainsString('<a href="https://contoh.test/x"', $tautan);
        $this->assertStringContainsString('<svg', $tautan);
        $this->assertStringContainsString('width="18"', $tautan);
        $this->assertStringContainsString('pl-4 pr-5', $tautan);

        $hanyaIkon = $this->render('<x-ui.tombol nada="hantu" ikon="menu" aria-label="Buka menu" />');
        $this->assertStringContainsString('aspect-square px-0', $hanyaIkon);
        $this->assertStringContainsString('width="24"', $hanyaIkon);
        $this->assertStringContainsString('aria-label="Buka menu"', $hanyaIkon);

        $konfirmasi = $this->render('<x-ui.tombol nada="bahaya" tipe="submit" konfirmasi="Batalkan pesanan ini?">Batalkan</x-ui.tombol>');
        $this->assertStringContainsString('data-konfirmasi="Batalkan pesanan ini?"', $konfirmasi);
    }

    public function test_waktu_tunggu_menurut_status_ambang_dan_nada(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-11 10:00:00', 'Asia/Jayapura'));

        $buat = fn (PesananStatus $status, string $menitLalu) => Pesanan::factory()->berstatus($status)->create(['created_at' => Carbon::now()->subMinutes((int) $menitLalu)->subSeconds(20)]);
        $html = fn (Pesanan $p) => $this->render('<x-ui.waktu-tunggu :pesanan="$p" class="mt-1" />', ['p' => $p->fresh()]);

        // 12 menit 20 detik: bahaya (ambang bawaan 5 dan 10), detik dikirim ke klien.
        $bahaya = $html($buat(PesananStatus::Menunggu, '12'));
        $this->assertStringContainsString('data-waktu-tunggu', $bahaya);
        $this->assertStringContainsString('data-tunggu-detik="740"', $bahaya);
        $this->assertStringContainsString('data-ambang-peringatan="5" data-ambang-bahaya="10"', $bahaya);
        $this->assertStringContainsString('data-nada="bahaya"', $bahaya);
        $this->assertMatchesRegularExpression('/<span data-teks>Menunggu 12 mnt<\/span>/', $bahaya);
        $this->assertStringContainsString('mt-1', $bahaya);
        $this->assertStringContainsString(Tampilan::WAKTU_TUNGGU, $bahaya);

        // Terverifikasi 7 menit: peringatan. 2 menit: netral. Baru dibuat: teks khusus.
        $this->assertStringContainsString('data-nada="peringatan"', $html($buat(PesananStatus::Terverifikasi, '7')));
        $this->assertStringContainsString('data-nada="netral"', $html($buat(PesananStatus::Menunggu, '2')));
        $this->assertStringContainsString('Baru dibuat', $this->render('<x-ui.waktu-tunggu :pesanan="$p" />', ['p' => Pesanan::factory()->create(['created_at' => Carbon::now()->subSeconds(10)])->fresh()]));

        // Status lain tidak menampilkan apa pun.
        foreach ([PesananStatus::Ditugaskan, PesananStatus::DalamPerjalanan, PesananStatus::Selesai, PesananStatus::Ditolak, PesananStatus::Dibatalkan] as $status) {
            $this->assertSame('', trim($html($buat($status, '30'))), $status->value);
        }

        // Ambang dari config.
        config(['sigap.waktu_tunggu' => ['peringatan_menit' => 1, 'bahaya_menit' => 3]]);
        $kustom = $html($buat(PesananStatus::Menunggu, '2'));
        $this->assertStringContainsString('data-nada="peringatan"', $kustom);
        $this->assertStringContainsString('data-ambang-peringatan="1" data-ambang-bahaya="3"', $kustom);

        Carbon::setTestNow();
    }

    public function test_lipat_adalah_pengungkap_bawaan_dengan_ikon_dan_target_48px(): void
    {
        $tertutup = $this->render('<x-ui.lipat judul="Tolak penugasan"><p>Isi</p></x-ui.lipat>');

        $this->assertMatchesRegularExpression('/<details\s+class="group rounded-md border border-outline-variant"\s*>/', $tertutup);
        $this->assertDoesNotMatchRegularExpression('/<details[^>]*\sopen[\s>]/', $tertutup);
        $this->assertMatchesRegularExpression('/<summary class="lapisan-keadaan flex min-h-12 cursor-pointer list-none[^"]*">\s*<span>Tolak penugasan<\/span>/', $tertutup);
        $this->assertStringContainsString('group-open:rotate-180', $tertutup);
        $this->assertStringContainsString('width="20"', $tertutup);
        $this->assertStringContainsString('<div class="border-t border-outline-variant p-4"><p>Isi</p></div>', $tertutup);

        $terbuka = $this->render('<x-ui.lipat judul="Tolak" :terbuka="true" class="mt-6">x</x-ui.lipat>');
        $this->assertMatchesRegularExpression('/<details\s+open\s+class="group[^"]* mt-6"/', $terbuka);
    }

    public function test_bilah_aksi_lekat_di_ponsel_dan_menyatu_di_lg(): void
    {
        $html = $this->render('<x-ui.bilah-aksi><button>Terima</button></x-ui.bilah-aksi>');

        $this->assertStringContainsString('data-bilah-aksi', $html);
        foreach (['sticky bottom-0', 'z-30', '-mx-6 -mb-6', 'rounded-b-lg', 'border-t border-outline-variant', 'bg-surface-container-lowest', 'shadow-level-1',
            'lg:static', 'lg:mx-0', 'lg:shadow-none', 'lg:bg-transparent'] as $kelas) {
            $this->assertStringContainsString($kelas, $html, $kelas);
        }
        $this->assertStringContainsString('<button>Terima</button>', $html);
    }

    public function test_snackbar_naik_di_atas_bilah_aksi_di_bawah_lg(): void
    {
        $css = (string) file_get_contents(base_path('resources/css/tambahan.css'));

        $this->assertMatchesRegularExpression('/@media \(max-width: 1023px\) \{\s*body:has\(\[data-bilah-aksi\]\) \[data-snackbar\] \{\s*bottom: 6\.5rem;/', $css);
    }

    public function test_lencana_nada_lama_dan_tone_arah_b_dengan_dot_dan_ukuran(): void
    {
        // Nada Indonesia lama dan tone arah B adalah sinonim persis (DESIGN.md 4.3, Tampilan::kelasLembut).
        $peta = [
            'netral' => 'bg-secondary-container text-on-secondary-container', 'neutral' => 'bg-secondary-container text-on-secondary-container',
            'info' => 'bg-primary-container text-on-primary-container', 'primary' => 'bg-primary-container text-on-primary-container',
            'peringatan' => 'bg-warning-container text-on-warning-container', 'warning' => 'bg-warning-container text-on-warning-container',
            'sukses' => 'bg-success-container text-on-success-container', 'success' => 'bg-success-container text-on-success-container',
            'bahaya' => 'bg-error-container text-on-error-container', 'error' => 'bg-error-container text-on-error-container',
            'tertiary' => 'bg-tertiary-container text-on-tertiary-container',
        ];
        foreach ($peta as $nada => $kelas) {
            $html = $this->render('<x-ui.lencana :nada="$n">Teks</x-ui.lencana>', ['n' => $nada]);
            $this->assertStringContainsString($kelas, $html, $nada);
            $this->assertStringContainsString('rounded-full bg-current', $html);
            $this->assertStringContainsString('rounded-full px-2.75', $html);
            $this->assertStringContainsString('min-h-6.5', $html, 'size bawaan md: 26px.');
            $this->assertStringContainsString('text-chip', $html);
        }

        // size="sm": 24px. dot=false: tanpa titik (chip keterangan/delta).
        $this->assertStringContainsString('min-h-6', $this->render('<x-ui.lencana size="sm">A</x-ui.lencana>'));
        $this->assertStringNotContainsString('min-h-6.5', $this->render('<x-ui.lencana size="sm">A</x-ui.lencana>'));
        $dengan = $this->render('<x-ui.lencana>A</x-ui.lencana>');
        $tanpa = $this->render('<x-ui.lencana :dot="false">A</x-ui.lencana>');
        $this->assertStringContainsString('size-1.5', $dengan);
        $this->assertStringNotContainsString('size-1.5', $tanpa);

        // Lencana status: PesananStatus::tone() sebagai sumber tunggal warna (bukan lagi nada()).
        foreach (PesananStatus::cases() as $status) {
            $html = $this->render('<x-ui.lencana-status :status="$s" />', ['s' => $status]);
            $this->assertStringContainsString($status->label(), $html);
            $this->assertStringContainsString($peta[$status->tone()], $html, $status->value);
        }
    }

    public function test_peringatan_dan_flash(): void
    {
        $this->assertStringContainsString('role="alert"', $this->render('<x-ui.peringatan nada="bahaya">Galat</x-ui.peringatan>'));
        $this->assertStringContainsString('role="status"', $this->render('<x-ui.peringatan nada="sukses">Ok</x-ui.peringatan>'));
        $banner = $this->render('<x-ui.peringatan nada="bahaya">Galat</x-ui.peringatan>');
        $this->assertStringContainsString('bg-error-container text-on-error-container', $banner);
        $this->assertStringContainsString('rounded-md', $banner);
        $this->assertStringContainsString('width="18"', $banner);
        $this->assertStringContainsString('text-label-l', $banner);

        $this->assertSame('', trim($this->render('<x-ui.flash />')));

        session()->flash('sukses', 'Pesanan berhasil diverifikasi.');
        session()->flash('galat', 'Ambulans tidak tersedia.');
        $html = $this->render('<x-ui.flash />');
        $this->assertStringContainsString('Pesanan berhasil diverifikasi.', $html);
        $this->assertStringContainsString('Ambulans tidak tersedia.', $html);

        // Pesan sukses menjadi snackbar; galat tetap pesan sebaris.
        $this->assertStringContainsString('data-snackbar', $html);
        $this->assertStringContainsString('bg-inverse-surface', $html);
        $this->assertStringContainsString('rounded-md', $html);
        $this->assertStringContainsString('shadow-overlay', $html);
        $this->assertStringContainsString('data-snackbar-tutup', $html);
        $this->assertStringContainsString('text-inverse-primary', $html);
        $this->assertStringContainsString('role="alert"', $html);
    }

    public function test_flash_menampilkan_ringkasan_galat_validasi(): void
    {
        $galat = new ViewErrorBag;
        $galat->put('default', new \Illuminate\Support\MessageBag(['nama' => ['Nama wajib diisi.']]));

        view()->share('errors', $galat);
        $html = $this->render('<x-ui.flash />');

        $this->assertStringContainsString('Nama wajib diisi.', $html);
        $this->assertStringContainsString('Periksa kembali isian berikut.', $html);
    }

    public function test_dialog_konfirmasi(): void
    {
        $html = $this->render('<x-ui.dialog judul="Tolak pesanan" pesan="Pesanan akan ditolak." aksi="Tolak" />');

        $this->assertStringContainsString('<dialog id="dialog-konfirmasi"', $html);
        $this->assertStringContainsString('Tolak pesanan', $html);
        $this->assertStringContainsString('data-dialog-batal', $html);
        $this->assertStringContainsString('data-dialog-aksi', $html);
        foreach (['shadow-overlay', 'rounded-2xl', 'bg-surface-container-lowest', 'text-title-l', 'backdrop:bg-scrim/45', 'max-w-md'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }
        $this->assertStringContainsString('bg-error-container text-on-error-container', $html);

        $netral = $this->render('<x-ui.dialog nada="utama" judul="Selesai" pesan="Pesanan selesai." aksi="Selesai" />');
        $this->assertStringNotContainsString('bg-error', $netral);
    }

    public function test_judul_halaman_bagian_panel_kosong(): void
    {
        $judul = $this->render('<x-ui.judul-halaman judul="Pesanan" deskripsi="Daftar pesanan."><x-slot:aksi>AKSI</x-slot:aksi></x-ui.judul-halaman>');
        $this->assertStringContainsString('<h1 class="text-page-title max-lg:text-page-title-s">Pesanan</h1>', $judul);
        $this->assertStringNotContainsString('text-headline-s', $judul);
        $this->assertStringContainsString('Daftar pesanan.', $judul);
        $this->assertStringContainsString('text-body-m text-on-surface-variant', $judul);
        $this->assertStringContainsString('AKSI', $judul);
        // Judul 24px dan keterangan 16px tidak boleh setara: kelas ukuran keduanya berbeda.
        $this->assertStringNotContainsString('<h1 class="text-body', $judul);

        $bagian = $this->render('<x-ui.bagian judul="Terbaru" deskripsi="Sepuluh pesanan terakhir.">ISI</x-ui.bagian>');
        $this->assertStringContainsString('<h2 class="text-title-l">Terbaru</h2>', $bagian);
        $this->assertStringContainsString('text-body-m text-on-surface-variant', $bagian);
        $this->assertStringContainsString('mt-8', $bagian);

        // Panel arah B (DESIGN.md 6.1): sudut 24px, padding 20px (24px di lg), judul title-l. Prop as: tag dapat diganti.
        $panel = $this->render('<x-ui.panel judul="Aksi">ISI<x-slot:kaki>KAKI</x-slot:kaki></x-ui.panel>');
        $this->assertStringContainsString('bg-surface-container-lowest rounded-xl shadow-card', $panel);
        $this->assertStringContainsString('p-5', $panel);
        $this->assertStringContainsString('lg:p-6', $panel);
        $this->assertStringNotContainsString('border border-outline-variant bg-surface-container-lowest', $panel);
        $this->assertStringContainsString('text-title-l', $panel);
        $this->assertStringContainsString('KAKI', $panel);

        $header = $this->render('<x-ui.panel>ISI<x-slot:header>JUDUL<span>AKSI</span></x-slot:header></x-ui.panel>');
        $this->assertStringContainsString('JUDUL', $header);
        $this->assertStringContainsString('<span>AKSI</span>', $header);

        $section = $this->render('<x-ui.panel as="section">ISI</x-ui.panel>');
        $this->assertStringStartsWith('<section ', trim($section));
        $this->assertStringEndsWith('</section>', trim($section));

        $flush = $this->render('<x-ui.panel :flush="true">TABEL</x-ui.panel>');
        $this->assertStringContainsString('bg-surface-container-lowest rounded-xl shadow-card', $flush);
        $this->assertStringContainsString('overflow-hidden', $flush);
        $this->assertStringNotContainsString('p-5', $flush);
        $this->assertStringNotContainsString('p-6', $flush);

        // Keadaan kosong arah B (DESIGN.md 6.11, x-ui.empty): batas putus-putus, pusat, ikon dan judul opsional.
        $kosong = $this->render('<x-ui.kosong pesan="Belum ada pesanan."><x-slot:aksi>BUAT</x-slot:aksi></x-ui.kosong>');
        $this->assertStringContainsString('Belum ada pesanan.', $kosong);
        $this->assertStringContainsString('BUAT', $kosong);
        foreach (['border-dashed', 'border-outline-variant', 'text-center', 'text-body-s text-on-surface-variant'] as $kelas) {
            $this->assertStringContainsString($kelas, $kosong);
        }
        $this->assertStringNotContainsString('shadow-card', $kosong);

        $lengkap = $this->render('<x-ui.kosong judul="Tidak ada pesanan" pesan="Pesanan baru muncul di sini." ikon="clipboard-list" />');
        $this->assertStringContainsString('Tidak ada pesanan', $lengkap);
        $this->assertStringContainsString('<svg', $lengkap);
        $this->assertStringContainsString('width="22"', $lengkap);
        $this->assertStringContainsString('text-label-l', $lengkap);
    }

    public function test_tabel(): void
    {
        $html = $this->render('<x-ui.tabel><x-slot:kepala><th>Kode</th></x-slot:kepala><tr><td>SGP-000001</td></tr></x-ui.tabel>');

        $this->assertStringContainsString('overflow-x-auto', $html);
        $this->assertStringContainsString('<th>Kode</th>', $html);
        $this->assertStringContainsString('SGP-000001', $html);
        // Baris bulat (border-spacing), tanpa garis pemisah (DESIGN.md 6.9); padding pada pembungkus (p-3).
        $this->assertStringNotContainsString('divide-outline-variant', $html);
        $this->assertStringContainsString('border-separate border-spacing-y-1', $html);
        $this->assertStringContainsString('bg-surface-container-lowest rounded-xl shadow-card', $html);
        $this->assertStringContainsString(' p-3', $html);
        $this->assertStringNotContainsString('rounded-md border', $html);
        $this->assertStringContainsString('bg-surface-container-low', $html);
        $this->assertStringContainsString('rounded-l-lg', $html);
        $this->assertStringContainsString('rounded-r-lg', $html);
        $this->assertStringNotContainsString('min-w-', $html);
    }

    public function test_tabel_tanpa_bingkai_di_dalam_kartu_dengan_lebar_minimum(): void
    {
        $html = $this->render('<x-ui.tabel :bingkai="false" lebar-min="min-w-136"><x-slot:kepala><th>Kode</th></x-slot:kepala><tr><td>SGP-000001</td></tr></x-ui.tabel>');

        // Hanya pembungkus gulir: tanpa batas, sudut, dan latar sendiri; baris tetap bulat tanpa garis pemisah.
        $this->assertStringContainsString('overflow-x-auto', $html);
        $this->assertStringNotContainsString('shadow-card', $html);
        $this->assertStringNotContainsString(' p-3', $html);
        $this->assertStringNotContainsString('bg-surface-container-lowest', $html);
        $this->assertStringContainsString('min-w-136', $html);
        $this->assertStringContainsString('rounded-l-lg', $html);
        $this->assertStringContainsString('rounded-r-lg', $html);
        $this->assertStringNotContainsString('divide-outline-variant', $html);
        $this->assertStringContainsString('bg-surface-container-low', $html);
    }

    public function test_isian_dengan_nilai_bantuan_dan_galat(): void
    {
        $biasa = $this->render('<x-ui.isian nama="nama_pasien" label="Nama pasien" nilai="Budi" bantuan="Sesuai KTP" :wajib="true" />');
        $this->assertStringContainsString('for="f-nama_pasien"', $biasa);
        $this->assertStringContainsString('value="Budi"', $biasa);
        $this->assertStringContainsString('Sesuai KTP', $biasa);
        $this->assertStringContainsString('required', $biasa);
        $this->assertStringContainsString('(wajib)', $biasa);
        $this->assertStringContainsString(Tampilan::kelasIsian(false), $biasa);
        // Tinggi 48px, sudut md, fokus batas primary + cincin (DESIGN.md 6.3).
        foreach (['border-outline', 'min-h-12', 'rounded-md', 'bg-surface-container-lowest', 'focus-visible:border-primary', 'focus-visible:ring-primary/20', 'text-label-l text-on-surface'] as $kelas) {
            $this->assertStringContainsString($kelas, $biasa);
        }
        $this->assertStringNotContainsString('min-h-14', $biasa);
        $this->assertStringNotContainsString('rounded-xs', $biasa);

        $tanpaWajib = $this->render('<x-ui.isian nama="catatan" label="Catatan" />');
        $this->assertStringNotContainsString('(wajib)', $tanpaWajib);

        $galat = new ViewErrorBag;
        $galat->put('default', new \Illuminate\Support\MessageBag(['nama_pasien' => ['Nama pasien wajib diisi.']]));
        view()->share('errors', $galat);
        $salah = $this->render('<x-ui.isian nama="nama_pasien" label="Nama pasien" />');
        $this->assertStringContainsString('Nama pasien wajib diisi.', $salah);
        $this->assertStringContainsString(Tampilan::kelasIsian(true), $salah);
        $this->assertStringContainsString('border-error', $salah);
        $this->assertStringContainsString('text-label-m text-error', $salah);
        $this->assertStringContainsString('aria-invalid="true"', $salah);
        $this->assertStringNotContainsString('border-outline', $salah);

        $sandi = $this->render('<x-ui.isian nama="password" label="Kata sandi" tipe="password" nilai="rahasia" />');
        $this->assertStringNotContainsString('rahasia', $sandi);
    }

    public function test_isian_memakai_old_input(): void
    {
        session()->flashInput(['nama_pasien' => 'Dari old']);
        $this->app['request']->setLaravelSession(session()->driver());

        $html = $this->render('<x-ui.isian nama="nama_pasien" label="Nama" nilai="Awal" />');

        $this->assertStringContainsString('value="Dari old"', $html);
    }

    public function test_pilihan_dari_enum_opsi_dengan_terpilih_dan_kosong(): void
    {
        $html = $this->render('<x-ui.pilihan nama="jenis" label="Jenis" :opsi="$o" :nilai="$n" kosong="Semua jenis" />', [
            'o' => JenisFaskes::opsi(),
            'n' => JenisFaskes::Puskesmas,
        ]);

        $this->assertStringContainsString('<option value="">Semua jenis</option>', $html);
        $this->assertStringContainsString('Rumah sakit', $html);
        $this->assertMatchesRegularExpression('/<option value="puskesmas"\s+selected/', $html);
        $this->assertStringContainsString(Tampilan::kelasIsian(false), $html);
        // Select appearance-none dengan ikon chevron-down 18px (DESIGN.md 6.3).
        $this->assertStringContainsString('appearance-none', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('width="18"', $html);
    }

    public function test_area_teks_centang_dan_opsi_baris(): void
    {
        $area = $this->render('<x-ui.area-teks nama="catatan" label="Catatan" nilai="Isi catatan" :baris="4" />');
        $this->assertStringContainsString('rows="4"', $area);
        $this->assertStringContainsString('>Isi catatan</textarea>', $area);
        $this->assertStringContainsString(Tampilan::kelasIsian(false), $area);
        $this->assertStringContainsString('min-h-21', $area, 'Minimal 84px (DESIGN.md 6.3).');

        $centang = $this->render('<x-ui.centang nama="aktif" label="Aktif" :nilai="true" />');
        $this->assertStringContainsString('type="hidden" name="aktif" value="0"', $centang);
        $this->assertStringContainsString('checked', $centang);
        foreach (['size-4.5', 'accent-primary', 'min-h-12', 'lapisan-keadaan'] as $kelas) {
            $this->assertStringContainsString($kelas, $centang);
        }

        // Kartu pilihan arah B (DESIGN.md 6.4): terpilih batas dan cincin dalam primary, latar selected.
        // Dengan deskripsi (baris, dipakai warga/pesanan/buat.blade.php sejak R8): petak ikon 40px (size-10).
        $opsi = $this->render('<x-ui.opsi-baris nama="jenis_layanan" nilai="darurat" judul="Darurat" deskripsi="Kecelakaan" ikon="ambulance" :terpilih="true" />');
        $this->assertStringContainsString('type="radio"', $opsi);
        // Gaya terpilih reaktif lewat has-[:checked]/group-has-[:checked] (bukan kelas kondisional PHP),
        // supaya klik radio langsung mengubah tampilan tanpa muat ulang halaman.
        $this->assertStringContainsString('has-[:checked]:border-primary has-[:checked]:bg-selected has-[:checked]:ring-1 has-[:checked]:ring-inset has-[:checked]:ring-primary', $opsi);
        $this->assertStringContainsString('group-has-[:checked]:bg-primary group-has-[:checked]:text-on-primary', $opsi, 'Petak ikon terpilih berlatar primary.');
        foreach (['rounded-md', 'min-h-14', 'text-label-l', 'size-10'] as $kelas) {
            $this->assertStringContainsString($kelas, $opsi);
        }
        $this->assertStringContainsString('Kecelakaan', $opsi);
        $this->assertStringNotContainsString('bg-secondary-container', $opsi);
        // Baris terpilih tanpa lapisan hover/tekan: hover tidak lagi menggelapkan latar bg-selected.
        $this->assertStringNotContainsString('lapisan-keadaan', $opsi);

        $takTerpilih = $this->render('<x-ui.opsi-baris nama="jenis_layanan" nilai="rujukan" judul="Rujukan" deskripsi="Pindah Faskes" ikon="building-2" />');
        $this->assertStringContainsString('lapisan-keadaan', $takTerpilih, 'Baris belum terpilih tetap punya lapisan hover.');

        // Tanpa deskripsi (kisi 3 kolom): petak ikon tetap 30px (size-7.5).
        $kisi = $this->render('<x-ui.opsi-baris nama="jenis_layanan_kisi" nilai="darurat" judul="Darurat" ikon="ambulance" :terpilih="true" />');
        $this->assertStringContainsString('size-7.5', $kisi);
        $this->assertStringNotContainsString('size-10', $kisi);

        $tanpaIkon = $this->render('<x-ui.opsi-baris nama="jenis_layanan" nilai="rujukan" judul="Rujukan" />');
        $this->assertStringNotContainsString('size-7.5', $tanpaIkon, 'Ikon opsional: tanpa petak bila tidak diberi.');
        $this->assertStringNotContainsString('size-10', $tanpaIkon);
        // "checked" muncul di kelas has-[:checked]:... (statis, dipakai di semua baris): yang dicek di sini
        // adalah atribut HTML checked pada <input>-nya sendiri, bukan potongan nama kelas.
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*\bchecked\b/', $tanpaIkon);
        $this->assertStringNotContainsString('border-primary bg-selected', $tanpaIkon);
    }

    public function test_rincian_dan_waktu(): void
    {
        $rincian = $this->render('<x-ui.rincian><x-ui.rincian-baris label="Pasien">Budi</x-ui.rincian-baris></x-ui.rincian>');
        $this->assertStringContainsString('<dl', $rincian);
        // Label label-m, nilai body-m (DESIGN.md 6.12).
        $this->assertStringContainsString('<dt class="text-label-m text-on-surface-variant">Pasien</dt>', $rincian);
        $this->assertStringContainsString('text-body-m', $rincian);
        foreach (['divide-outline-variant', 'bg-surface-container-lowest rounded-xl shadow-card', 'overflow-hidden', 'px-6'] as $kelas) {
            $this->assertStringContainsString($kelas, $rincian);
        }
        $this->assertStringNotContainsString('border-outline-variant bg', $rincian);

        // Di dalam kartu lain (mis. kartu penugasan sopir): tanpa gaya kartu sendiri agar kartu berbayang tidak bersarang.
        $polos = $this->render('<x-ui.rincian :bingkai="false"><x-ui.rincian-baris label="Pasien">Budi</x-ui.rincian-baris></x-ui.rincian>');
        $this->assertStringContainsString('divide-outline-variant', $polos);
        $this->assertStringNotContainsString('shadow-card', $polos);
        $this->assertStringNotContainsString('rounded-xl', $polos);
        $this->assertStringNotContainsString('bg-surface-container-lowest', $polos);
        $this->assertStringContainsString('<dt class="text-label-m text-on-surface-variant">Pasien</dt>', $polos);

        $waktu = $this->render('<x-ui.waktu nilai="2026-10-11 09:30:00" />');
        $this->assertStringContainsString('11 Okt 2026 09:30', $waktu);
        $this->assertStringContainsString('tabular-nums', $waktu);
        $this->assertStringContainsString('>-<', $this->render('<x-ui.waktu :nilai="null" />'));
        $this->assertStringContainsString('yang lalu', $this->render('<x-ui.waktu :nilai="$t" :relatif="true" />', ['t' => now()->subMinutes(5)]));

        // Format dapat diganti (dasbor memakai pemisah koma antara tanggal dan jam).
        $this->assertStringContainsString('>11 Okt 2026, 09:30<', $this->render('<x-ui.waktu nilai="2026-10-11 09:30:00" format="d M Y, H:i" />'));
    }

    public function test_paginasi_berbahasa_indonesia(): void
    {
        $halaman = new LengthAwarePaginator(array_fill(0, 20, 'x'), 134, 20, 2, ['path' => 'https://contoh.test/daftar']);
        $html = $this->render('<x-ui.paginasi :data="$p" />', ['p' => $halaman]);

        $this->assertStringContainsString('Menampilkan 21–40 dari 134', $html);
        $this->assertStringContainsString('Sebelumnya', $html);
        $this->assertStringContainsString('Berikutnya', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringNotContainsString('Previous', $html);
        $this->assertStringContainsString('bg-secondary-container text-on-secondary-container', $html);
        foreach (['rounded-full', 'min-h-10', 'lapisan-keadaan', 'text-label-l', 'text-body-m text-on-surface-variant'] as $kelas) {
            $this->assertStringContainsString($kelas, $html);
        }

        $kosong = $this->render('<x-ui.paginasi :data="$p" />', ['p' => new LengthAwarePaginator([], 0, 20, 1)]);
        $this->assertSame('', trim($kosong));
    }

    public function test_linimasa_menandai_langkah_terakhir_dan_kosong(): void
    {
        $this->assertStringContainsString('Belum ada riwayat.', $this->render('<x-ui.linimasa :log="collect()" />'));

        $warga = User::factory()->create(['name' => 'Siti']);
        $pesanan = Pesanan::factory()->create(['user_id' => $warga->id]);
        PesananLog::create(['pesanan_id' => $pesanan->id, 'ke_status' => PesananStatus::Menunggu, 'oleh_id' => $warga->id, 'peran' => Role::Warga]);
        PesananLog::create(['pesanan_id' => $pesanan->id, 'dari_status' => PesananStatus::Menunggu, 'ke_status' => PesananStatus::Ditolak, 'oleh_id' => $warga->id, 'peran' => Role::Admin, 'catatan' => 'Data tidak lengkap']);

        $html = $this->render('<x-ui.linimasa :log="$l" />', ['l' => $pesanan->log()->with('pelaku')->get()]);

        $this->assertStringContainsString('Menunggu verifikasi', $html);
        $this->assertStringNotContainsString('Menunggu Verifikasi', $html);
        $this->assertStringContainsString('Ditolak', $html);
        $this->assertStringContainsString('Siti', $html);
        $this->assertStringContainsString('(Dinkes)', $html);
        $this->assertStringContainsString('Data tidak lengkap', $html);
        // Langkah sekarang lebih tebal (label-l; token tidak punya body berbobot 700) dan titik terisi primary;
        // langkah lampau titik putih berbatas outline-variant. Titik tidak lagi mengikuti warna status (DESIGN.md 6.12).
        $this->assertSame(1, substr_count($html, 'text-label-l'));
        $this->assertStringContainsString('w-0.5 bg-outline-variant', $html);
        $this->assertStringContainsString('size-3 rounded-full bg-primary', $html);
        $this->assertStringContainsString('border-2 border-outline-variant bg-surface-container-lowest', $html);
        $this->assertStringNotContainsString('bg-error', $html);
        $this->assertStringContainsString('text-body-s text-on-surface-variant', $html);
    }

    public function test_ketersediaan_belum_diperbarui_data_lama_dan_segar(): void
    {
        $belum = Faskes::factory()->create();
        $this->assertStringContainsString('Belum diperbarui', $this->render('<x-ui.ketersediaan :faskes="$f" />', ['f' => $belum]));

        $lama = Faskes::factory()->diperbarui()->create(['ketersediaan_diperbarui_pada' => now()->subHours(config('sigap.data_basi_jam') + 2)]);
        $htmlLama = $this->render('<x-ui.ketersediaan :faskes="$f" />', ['f' => $lama]);
        $this->assertStringContainsString('Data lama', $htmlLama);
        $this->assertStringContainsString('Diperbarui', $htmlLama);

        $segar = Faskes::factory()->diperbarui(5, 10)->create();
        $htmlSegar = $this->render('<x-ui.ketersediaan :faskes="$f" />', ['f' => $segar]);
        $this->assertStringContainsString('5/7', $htmlSegar);
        $this->assertStringContainsString('10/15', $htmlSegar);
        $this->assertStringNotContainsString('Data lama', $htmlSegar);
        $this->assertStringNotContainsString('Belum diperbarui', $htmlSegar);
        $this->assertStringContainsString('text-body-m', $htmlSegar);
        $this->assertStringContainsString('<dt class="text-on-surface-variant">Bed IGD</dt>', $htmlSegar);
        $this->assertStringContainsString('text-body-s text-on-surface-variant', $htmlSegar);
    }

    /** Keterangan kesegaran data ketersediaan dipakai bersama oleh x-ui.ketersediaan dan dasbor Faskes. */
    public function test_pembaruan_menyebut_kesegaran_data_ketersediaan(): void
    {
        $belum = $this->render('<x-ui.pembaruan :faskes="$f" />', ['f' => Faskes::factory()->create()]);
        $this->assertStringContainsString('Belum diperbarui', $belum);
        $this->assertStringContainsString('bg-warning-container text-on-warning-container', $belum);
        $this->assertStringContainsString('text-body-s text-on-surface-variant', $belum);

        $lama = $this->render('<x-ui.pembaruan :faskes="$f" />', ['f' => Faskes::factory()->diperbarui()->create(['ketersediaan_diperbarui_pada' => now()->subHours(config('sigap.data_basi_jam') + 2)])]);
        $this->assertStringContainsString('Data lama', $lama);
        $this->assertStringContainsString('Diperbarui', $lama);

        $segar = $this->render('<x-ui.pembaruan :faskes="$f" />', ['f' => Faskes::factory()->diperbarui(5, 10)->create()]);
        $this->assertStringContainsString('Diperbarui', $segar);
        $this->assertStringContainsString('<time', $segar);
        $this->assertStringNotContainsString('Data lama', $segar);
        $this->assertStringNotContainsString('Belum diperbarui', $segar);

        // Komponen ketersediaan memakai keterangan yang sama.
        $this->assertStringContainsString('Belum diperbarui', $this->render('<x-ui.ketersediaan :faskes="$f" />', ['f' => Faskes::factory()->create()]));
    }

    public function test_ring_tile_cincin_ketersediaan(): void
    {
        $biasa = $this->render('<x-ui.ring-tile :tersedia="19" :total="24" label="Ambulans" />');
        $this->assertStringContainsString('role="img"', $biasa);
        $this->assertStringContainsString('aria-label="Ambulans: 19 dari 24"', $biasa);
        $this->assertStringContainsString('>19<', $biasa);
        $this->assertStringContainsString('Ambulans', $biasa);
        $this->assertStringContainsString('dari 24', $biasa);
        $this->assertStringContainsString('stroke="var(--color-primary)"', $biasa);
        $this->assertStringNotContainsString('menipis', $biasa);
        foreach (['bg-surface-container-low', 'rounded-lg', 'text-ring-value', 'text-label-l'] as $kelas) {
            $this->assertStringContainsString($kelas, $biasa);
        }

        // Tersedia < 12% dari total: isi chart-coral, teks "menipis" dengan label-m on-error-container (DESIGN.md 6.10).
        $menipis = $this->render('<x-ui.ring-tile :tersedia="1" :total="14" label="Bed IGD" />');
        $this->assertStringContainsString('aria-label="Bed IGD: 1 dari 14, menipis"', $menipis);
        $this->assertStringContainsString('stroke="var(--color-chart-coral)"', $menipis);
        $this->assertStringContainsString('dari 14, menipis', $menipis);
        $this->assertStringContainsString('text-label-m text-on-error-container', $menipis);

        // Total 0: cincin hampir kosong, "dari 0", tidak menipis (tidak ada penyebut untuk persen).
        $kosong = $this->render('<x-ui.ring-tile :tersedia="0" :total="0" label="Dokter" />');
        $this->assertStringContainsString('aria-label="Dokter: 0 dari 0"', $kosong);
        $this->assertStringContainsString('dari 0', $kosong);
        $this->assertStringNotContainsString('menipis', $kosong);
    }

    public function test_chart_garis_dan_donat(): void
    {
        $garis = $this->render(<<<'BLADE'
            <x-ui.chart type="line" title="Grafik pesanan" unit="pesanan" :labels="$l" :tick-step="1" :datasets="$d" />
            BLADE, [
            'l' => ['Sen', 'Sel', 'Rab'],
            'd' => [
                ['label' => 'Masuk', 'data' => [5, 8, 6], 'color' => 'primary', 'width' => 3.5, 'fill' => true],
                ['label' => 'Selesai', 'data' => [4, 6, 6], 'color' => 'chart-mint', 'width' => 3, 'fill' => false],
            ],
        ]);
        $this->assertStringContainsString('role="img"', $garis);
        $this->assertStringContainsString('&quot;tipe&quot;:&quot;garis&quot;', $garis);
        $this->assertStringContainsString('&quot;color&quot;:&quot;primary&quot;', $garis);
        $this->assertStringContainsString('h-58', $garis);
        $this->assertStringContainsString('lg:h-77.5', $garis);
        $this->assertStringContainsString('Masuk', $garis);
        $this->assertStringContainsString('<table class="sr-only">', $garis);
        $this->assertStringContainsString('Grafik pesanan', $garis);

        try {
            $this->render('<x-ui.chart type="line" title="X" :labels="$l" :datasets="$d" />', [
                'l' => ['Sen', 'Sel'],
                'd' => [['label' => 'Masuk', 'data' => [1, 2, 3], 'color' => 'primary']],
            ]);
            $this->fail('Jumlah data dan labels yang tidak sama harus ditolak.');
        } catch (\Illuminate\View\ViewException $e) {
            $this->assertStringContainsString('jumlah data dataset', $e->getMessage());
        }

        $donat = $this->render('<x-ui.chart type="doughnut" title="Status pesanan" unit="pesanan" center-label="pesanan" :segments="$s" />', [
            's' => [
                ['label' => 'Selesai', 'value' => 31, 'color' => 'chart-mint'],
                ['label' => 'Dibatalkan', 'value' => 5, 'color' => 'chart-slate'],
                ['label' => 'Ditolak', 'value' => 0, 'color' => 'chart-coral'],
            ],
        ]);
        $this->assertStringContainsString('&quot;tipe&quot;:&quot;donat&quot;', $donat);
        $this->assertStringContainsString('Selesai', $donat);
        $this->assertStringContainsString('86%', $donat); // 31/36
        $this->assertStringContainsString('var(--color-chart-mint)', $donat);
        $this->assertStringNotContainsString('Ditolak', $donat); // nilai 0 tidak tampil.
        $this->assertStringContainsString('>36<', $donat); // total di tengah.

        $kosong = $this->render('<x-ui.chart type="doughnut" title="Status pesanan" :segments="[]" />');
        $this->assertStringContainsString('&quot;segments&quot;:[]', $kosong);
        $this->assertStringContainsString('>0<', $kosong);
    }

    public function test_chart_batang(): void
    {
        $batang = $this->render(<<<'BLADE'
            <x-ui.chart type="bar" title="Waktu respons" unit="menit" :labels="$l" :datasets="$d" />
            BLADE, [
            'l' => ['Tercepat', 'Rata-rata', 'Terlama'],
            'd' => [
                ['label' => 'Respons', 'data' => [10, 12, 15], 'valueLabels' => ['10 menit', '12 menit', '15 menit'], 'color' => 'primary'],
            ],
        ]);
        $this->assertStringContainsString('role="img"', $batang);
        $this->assertStringContainsString('&quot;tipe&quot;:&quot;batang&quot;', $batang);
        $this->assertStringContainsString('&quot;color&quot;:&quot;primary&quot;', $batang);
        $this->assertStringContainsString('Tercepat 10 menit', $batang); // aria-label memakai valueLabels, bukan angka mentah.
        $this->assertStringContainsString('<table class="sr-only">', $batang);
        $this->assertStringContainsString('15 menit', $batang);

        // Tanpa valueLabels: tabel sr-only jatuh ke angka mentah + unit.
        $polos = $this->render(<<<'BLADE'
            <x-ui.chart type="bar" title="Penugasan per Faskes asal" unit="pesanan" :labels="$l" :datasets="$d" />
            BLADE, [
            'l' => ['RSUD Selebesolu', 'Puskesmas Klasaman'],
            'd' => [['label' => 'Pesanan', 'data' => [5, 3], 'color' => 'primary']],
        ]);
        $this->assertStringContainsString('5 pesanan', $polos);
        $this->assertStringContainsString('RSUD Selebesolu', $polos);

        try {
            $this->render('<x-ui.chart type="bar" title="X" :labels="$l" :datasets="$d" />', [
                'l' => ['Sen', 'Sel'],
                'd' => [['label' => 'Masuk', 'data' => [1, 2, 3], 'color' => 'primary']],
            ]);
            $this->fail('Jumlah data dan labels yang tidak sama harus ditolak.');
        } catch (\Illuminate\View\ViewException $e) {
            $this->assertStringContainsString('jumlah data dataset', $e->getMessage());
        }
    }
}
