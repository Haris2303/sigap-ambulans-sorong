<?php

namespace Database\Seeders;

use App\Enums\JenisAmbulans;
use App\Enums\JenisFaskes;
use App\Models\Ambulans;
use App\Models\Faskes;
use Illuminate\Database\Seeder;

class AmbulansSeeder extends Seeder
{
    /** Faskes yang punya satu unit ambulans darurat (wawancara Dinkes). */
    public const FASKES_DARURAT = [
        'RSUD Selebesolu',
        'RS AL',
        'RS Aryoko',
        'Klinik Posmor 3 Km 16',
    ];

    /**
     * Plat nomor DUMMY (PB-DEMO-nn) dan wajib diganti Dinkes dengan data asli.
     * Urutan: 4 unit darurat, lalu satu unit transport untuk tiap RS dan Puskesmas.
     */
    public function run(): void
    {
        $nomor = 0;
        $buat = function (string $namaFaskes, JenisAmbulans $jenis) use (&$nomor): void {
            $nomor++;
            $faskes = Faskes::where('nama', $namaFaskes)->first();
            if ($faskes === null) {
                return;
            }

            Ambulans::firstOrCreate(
                ['plat_nomor' => sprintf('PB-DEMO-%02d', $nomor)],
                ['faskes_id' => $faskes->id, 'jenis' => $jenis],
            );
        };

        foreach (self::FASKES_DARURAT as $nama) {
            $buat($nama, JenisAmbulans::Darurat);
        }

        foreach (FaskesSeeder::DAFTAR as [$nama, $jenis]) {
            if ($jenis === JenisFaskes::Rs || $jenis === JenisFaskes::Puskesmas) {
                $buat($nama, JenisAmbulans::Transport);
            }
        }
    }
}
