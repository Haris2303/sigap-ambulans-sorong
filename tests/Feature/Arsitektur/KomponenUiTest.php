<?php

namespace Tests\Feature\Arsitektur;

use App\Enums\JenisFaskes;
use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\PesananLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $html = $this->render('<x-ui.ikon nama="house" :ukuran="16" class="text-primary" />');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('width="16"', $html);
        $this->assertStringContainsString('stroke-width="1.75"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('text-primary', $html);

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
            'arrow-left', 'hospital'];

        foreach ($daftar as $nama) {
            $this->assertStringContainsString('<svg', $this->render('<x-ui.ikon :nama="$n" />', ['n' => $nama]), $nama);
        }
    }

    public function test_tombol_semua_nada_ukuran_dan_bentuk(): void
    {
        $utama = $this->render('<x-ui.tombol tipe="submit">Simpan</x-ui.tombol>');
        $this->assertStringContainsString('<button type="submit"', $utama);
        $this->assertStringContainsString('bg-primary text-on-primary', $utama);
        $this->assertStringContainsString('min-h-touch', $utama);

        $this->assertStringContainsString('border-control-border', $this->render('<x-ui.tombol nada="sekunder">A</x-ui.tombol>'));
        $this->assertStringContainsString('bg-danger', $this->render('<x-ui.tombol nada="bahaya">A</x-ui.tombol>'));
        $this->assertStringContainsString('hover:bg-primary-soft', $this->render('<x-ui.tombol nada="hantu">A</x-ui.tombol>'));
        $this->assertStringContainsString('min-h-9', $this->render('<x-ui.tombol ukuran="sm">A</x-ui.tombol>'));
        $this->assertStringContainsString('min-h-12', $this->render('<x-ui.tombol ukuran="lg">A</x-ui.tombol>'));
        $this->assertStringContainsString('w-full', $this->render('<x-ui.tombol :blok="true">A</x-ui.tombol>'));

        $tautan = $this->render('<x-ui.tombol href="https://contoh.test/x" ikon="plus">Tambah</x-ui.tombol>');
        $this->assertStringContainsString('<a href="https://contoh.test/x"', $tautan);
        $this->assertStringContainsString('<svg', $tautan);

        $konfirmasi = $this->render('<x-ui.tombol nada="bahaya" tipe="submit" konfirmasi="Batalkan pesanan ini?">Batalkan</x-ui.tombol>');
        $this->assertStringContainsString('data-konfirmasi="Batalkan pesanan ini?"', $konfirmasi);
    }

    public function test_lencana_lima_nada_dan_lencana_status_dari_enum(): void
    {
        $peta = ['netral' => 'bg-neutral-soft', 'info' => 'bg-primary-soft', 'peringatan' => 'bg-warning-soft',
            'sukses' => 'bg-success-soft', 'bahaya' => 'bg-danger-soft'];
        foreach ($peta as $nada => $kelas) {
            $html = $this->render('<x-ui.lencana :nada="$n">Teks</x-ui.lencana>', ['n' => $nada]);
            $this->assertStringContainsString($kelas, $html);
            $this->assertStringContainsString('rounded-full bg-current', $html);
        }

        foreach (PesananStatus::cases() as $status) {
            $html = $this->render('<x-ui.lencana-status :status="$s" />', ['s' => $status]);
            $this->assertStringContainsString($status->label(), $html);
            $this->assertStringContainsString($peta[$status->nada()], $html);
        }
    }

    public function test_peringatan_dan_flash(): void
    {
        $this->assertStringContainsString('role="alert"', $this->render('<x-ui.peringatan nada="bahaya">Galat</x-ui.peringatan>'));
        $this->assertStringContainsString('role="status"', $this->render('<x-ui.peringatan nada="sukses">Ok</x-ui.peringatan>'));

        $this->assertSame('', trim($this->render('<x-ui.flash />')));

        session()->flash('sukses', 'Pesanan berhasil diverifikasi.');
        session()->flash('galat', 'Ambulans tidak tersedia.');
        $html = $this->render('<x-ui.flash />');
        $this->assertStringContainsString('Pesanan berhasil diverifikasi.', $html);
        $this->assertStringContainsString('Ambulans tidak tersedia.', $html);
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
        $this->assertStringContainsString('shadow-overlay', $html);
    }

    public function test_judul_halaman_bagian_panel_kosong(): void
    {
        $judul = $this->render('<x-ui.judul-halaman judul="Pesanan" deskripsi="Daftar pesanan."><x-slot:aksi>AKSI</x-slot:aksi></x-ui.judul-halaman>');
        $this->assertStringContainsString('<h1 class="text-h1">Pesanan</h1>', $judul);
        $this->assertStringContainsString('Daftar pesanan.', $judul);
        $this->assertStringContainsString('AKSI', $judul);

        $bagian = $this->render('<x-ui.bagian judul="Terbaru">ISI</x-ui.bagian>');
        $this->assertStringContainsString('<h2 class="text-h2">Terbaru</h2>', $bagian);
        $this->assertStringContainsString('mt-8', $bagian);

        $panel = $this->render('<x-ui.panel judul="Aksi">ISI<x-slot:kaki>KAKI</x-slot:kaki></x-ui.panel>');
        $this->assertStringContainsString('rounded-lg border border-line bg-surface', $panel);
        $this->assertStringContainsString('KAKI', $panel);

        $kosong = $this->render('<x-ui.kosong pesan="Belum ada pesanan."><x-slot:aksi>BUAT</x-slot:aksi></x-ui.kosong>');
        $this->assertStringContainsString('Belum ada pesanan.', $kosong);
        $this->assertStringContainsString('BUAT', $kosong);
    }

    public function test_tabel(): void
    {
        $html = $this->render('<x-ui.tabel><x-slot:kepala><th>Kode</th></x-slot:kepala><tr><td>SGP-000001</td></tr></x-ui.tabel>');

        $this->assertStringContainsString('overflow-x-auto', $html);
        $this->assertStringContainsString('<th>Kode</th>', $html);
        $this->assertStringContainsString('SGP-000001', $html);
        $this->assertStringContainsString('divide-line', $html);
    }

    public function test_isian_dengan_nilai_bantuan_dan_galat(): void
    {
        $biasa = $this->render('<x-ui.isian nama="nama_pasien" label="Nama pasien" nilai="Budi" bantuan="Sesuai KTP" :wajib="true" />');
        $this->assertStringContainsString('for="f-nama_pasien"', $biasa);
        $this->assertStringContainsString('value="Budi"', $biasa);
        $this->assertStringContainsString('Sesuai KTP', $biasa);
        $this->assertStringContainsString('required', $biasa);
        $this->assertStringContainsString('border-control-border', $biasa);

        $galat = new ViewErrorBag;
        $galat->put('default', new \Illuminate\Support\MessageBag(['nama_pasien' => ['Nama pasien wajib diisi.']]));
        view()->share('errors', $galat);
        $salah = $this->render('<x-ui.isian nama="nama_pasien" label="Nama pasien" />');
        $this->assertStringContainsString('Nama pasien wajib diisi.', $salah);
        $this->assertStringContainsString('border-danger', $salah);
        $this->assertStringContainsString('aria-invalid="true"', $salah);
        $this->assertStringNotContainsString('border-control-border', $salah);

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
        $this->assertStringContainsString('Rumah Sakit', $html);
        $this->assertMatchesRegularExpression('/<option value="puskesmas"\s+selected/', $html);
    }

    public function test_area_teks_centang_dan_opsi_baris(): void
    {
        $area = $this->render('<x-ui.area-teks nama="catatan" label="Catatan" nilai="Isi catatan" :baris="4" />');
        $this->assertStringContainsString('rows="4"', $area);
        $this->assertStringContainsString('>Isi catatan</textarea>', $area);

        $centang = $this->render('<x-ui.centang nama="aktif" label="Aktif" :nilai="true" />');
        $this->assertStringContainsString('type="hidden" name="aktif" value="0"', $centang);
        $this->assertStringContainsString('checked', $centang);

        $opsi = $this->render('<x-ui.opsi-baris nama="jenis_layanan" nilai="darurat" judul="Darurat" deskripsi="Kecelakaan" :terpilih="true" />');
        $this->assertStringContainsString('type="radio"', $opsi);
        $this->assertStringContainsString('border-primary bg-primary-soft', $opsi);
        $this->assertStringContainsString('Kecelakaan', $opsi);

        $tidak = $this->render('<x-ui.opsi-baris nama="jenis_layanan" nilai="rujukan" judul="Rujukan" />');
        $this->assertStringNotContainsString('checked', str_replace('has-[:checked]', '', $tidak));
    }

    public function test_ringkasan_rincian_dan_waktu(): void
    {
        $ringkasan = $this->render('<x-ui.ringkasan><x-ui.ringkasan-sel label="Menunggu verifikasi" :nilai="3" nada="peringatan" keterangan="Sekarang" /><x-ui.ringkasan-sel label="Selesai" :nilai="12" /></x-ui.ringkasan>');
        $this->assertStringContainsString('text-display', $ringkasan);
        $this->assertStringContainsString('text-warning', $ringkasan);
        $this->assertStringContainsString('Menunggu verifikasi', $ringkasan);
        $this->assertStringContainsString('gap-px', $ringkasan);

        $rincian = $this->render('<x-ui.rincian><x-ui.rincian-baris label="Pasien">Budi</x-ui.rincian-baris></x-ui.rincian>');
        $this->assertStringContainsString('<dl', $rincian);
        $this->assertStringContainsString('<dt class="text-on-surface-muted">Pasien</dt>', $rincian);

        $waktu = $this->render('<x-ui.waktu nilai="2026-10-11 09:30:00" />');
        $this->assertStringContainsString('11 Okt 2026 09:30', $waktu);
        $this->assertStringContainsString('tabular-nums', $waktu);
        $this->assertStringContainsString('>-<', $this->render('<x-ui.waktu :nilai="null" />'));
        $this->assertStringContainsString('yang lalu', $this->render('<x-ui.waktu :nilai="$t" :relatif="true" />', ['t' => now()->subMinutes(5)]));
    }

    public function test_grafik_batang(): void
    {
        $html = $this->render('<x-ui.grafik-batang :data="$d" />', ['d' => ['Sen' => 2, 'Sel' => 4, 'Rab' => 0]]);

        $this->assertStringContainsString('width: 50%', $html);
        $this->assertStringContainsString('width: 100%', $html);
        $this->assertStringContainsString('width: 0%', $html);

        $kosong = $this->render('<x-ui.grafik-batang :data="[]" />');
        $this->assertStringContainsString('<ul', $kosong);
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

        $this->assertStringContainsString('Menunggu Verifikasi', $html);
        $this->assertStringContainsString('Ditolak', $html);
        $this->assertStringContainsString('Siti', $html);
        $this->assertStringContainsString('(Dinkes)', $html);
        $this->assertStringContainsString('Data tidak lengkap', $html);
        $this->assertSame(1, substr_count($html, 'font-semibold'));
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
    }
}
