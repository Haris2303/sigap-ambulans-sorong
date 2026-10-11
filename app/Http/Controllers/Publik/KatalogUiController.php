<?php

namespace App\Http\Controllers\Publik;

use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\PesananLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

/** Katalog gaya /_ui (hanya local dan testing): semua komponen dengan data contoh, tanpa menyentuh database. */
class KatalogUiController extends Controller
{
    public function __invoke(): View
    {
        // Pemeriksa saat permintaan, bukan hanya saat route didaftarkan (routes/web.php): dijaga KatalogUiTest
        // lewat $this->app->detectEnvironment(), sama seperti PesananDemoSeeder.
        abort_unless(app()->environment('local', 'testing'), 404);

        $pelaku = new User(['name' => 'Siti Aminah']);

        $log = collect([
            [null, PesananStatus::Menunggu, Role::Warga, null, '2026-10-11 09:00:00'],
            [PesananStatus::Menunggu, PesananStatus::Terverifikasi, Role::Admin, null, '2026-10-11 09:03:00'],
            [PesananStatus::Terverifikasi, PesananStatus::Ditugaskan, Role::Admin, 'PB-DEMO-01 dengan Sopir Contoh', '2026-10-11 09:06:00'],
            [PesananStatus::Ditugaskan, PesananStatus::Diterima, Role::Sopir, null, '2026-10-11 09:07:00'],
        ])->map(function (array $baris) use ($pelaku) {
            [$dari, $ke, $peran, $catatan, $waktu] = $baris;
            $langkah = new PesananLog(['dari_status' => $dari, 'ke_status' => $ke, 'peran' => $peran, 'catatan' => $catatan]);
            $langkah->forceFill(['created_at' => $waktu]);
            $langkah->setRelation('pelaku', $pelaku);

            return $langkah;
        });

        $dasar = [
            'jenis' => 'puskesmas',
            'bed_igd_total' => 6, 'bed_igd_tersedia' => 4,
            'bed_rawat_inap_total' => 20, 'bed_rawat_inap_tersedia' => 11,
            'dokter_umum_tersedia' => 2, 'dokter_spesialis_tersedia' => 1,
        ];
        $faskesSegar = new Faskes(['nama' => 'Puskesmas Contoh Segar', 'ketersediaan_diperbarui_pada' => now()->subMinutes(12)] + $dasar);
        $faskesLama = new Faskes(['nama' => 'Puskesmas Contoh Lama', 'ketersediaan_diperbarui_pada' => now()->subHours(config('sigap.data_basi_jam') + 6)] + $dasar);
        $faskesBaru = new Faskes(['nama' => 'Puskesmas Contoh Baru', 'jenis' => 'puskesmas']);
        foreach ([$faskesSegar, $faskesLama, $faskesBaru] as $f) {
            $f->setAttribute('ambulans_tersedia_count', 1);
        }

        // Katalog hanya untuk local dan testing: nomor contoh yang jelas palsu agar komponen kontak-darurat tampil (aplikasi asli tidak pernah mengisi nomor sendiri).
        config(['sigap.kontak_darurat' => ['nama' => 'Dinkes', 'nomor' => '0000 0000 0000']]);

        // Waktu tunggu contoh: netral, peringatan, dan bahaya menurut ambang config('sigap.waktu_tunggu'), serta satu pesanan yang sudah ditugaskan (tidak tampil).
        $waktuTunggu = collect([[PesananStatus::Menunggu, 2], [PesananStatus::Terverifikasi, 7], [PesananStatus::Menunggu, 14], [PesananStatus::Ditugaskan, 30]])
            ->map(fn (array $c) => (new Pesanan)->forceFill(['status' => $c[0], 'created_at' => now()->subMinutes($c[1])->subSeconds(20)]));

        return view('dev.katalog-ui', [
            'waktuTunggu' => $waktuTunggu,
            'log' => $log,
            'faskes' => [$faskesSegar, $faskesLama, $faskesBaru],
            'halaman' => new LengthAwarePaginator(array_fill(0, 20, 'x'), 134, 20, 2, ['path' => route('ui.katalog')]),
            'grafik' => ['Sen' => 3, 'Sel' => 7, 'Rab' => 5, 'Kam' => 0, 'Jum' => 9, 'Sab' => 4, 'Min' => 2],
            // Data pratinjau grafik (docs/referensi): batang 1,0,0,0,0,0,1 dan donat 5,2,1,1,1 dengan warna tetap per status.
            'labelBatang' => ['Sen 05/10', 'Sel 06/10', 'Rab 07/10', 'Kam 08/10', 'Jum 09/10', 'Sab 10/10', 'Min 11/10'],
            'nilaiBatang' => [1, 0, 0, 0, 0, 0, 1],
            'segmenStatus' => collect([
                [PesananStatus::Selesai, 5], [PesananStatus::Dibatalkan, 2], [PesananStatus::Ditolak, 1],
                [PesananStatus::Terverifikasi, 1], [PesananStatus::DalamPerjalanan, 1],
            ])->map(fn (array $s) => ['label' => $s[0]->label(), 'value' => $s[1], 'color' => $s[0]->warnaGrafik()])->all(),
            'statusPesanan' => PesananStatus::cases(),
        ]);
    }
}
