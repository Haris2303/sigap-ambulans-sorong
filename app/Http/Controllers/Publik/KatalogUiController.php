<?php

namespace App\Http\Controllers\Publik;

use App\Enums\PesananStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Faskes;
use App\Models\PesananLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

/** Katalog gaya /_ui (hanya local dan testing): semua komponen dengan data contoh, tanpa menyentuh database. */
class KatalogUiController extends Controller
{
    public function __invoke(): View
    {
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

        return view('dev.katalog-ui', [
            'log' => $log,
            'faskes' => [$faskesSegar, $faskesLama, $faskesBaru],
            'halaman' => new LengthAwarePaginator(array_fill(0, 20, 'x'), 134, 20, 2, ['path' => route('ui.katalog')]),
            'grafik' => ['Sen' => 3, 'Sel' => 7, 'Rab' => 5, 'Kam' => 0, 'Jum' => 9, 'Sab' => 4, 'Min' => 2],
            'statusPesanan' => PesananStatus::cases(),
        ]);
    }
}
