<?php

namespace Database\Seeders;

use App\Enums\JenisFaskes;
use App\Enums\Kepemilikan;
use App\Models\Faskes;
use Illuminate\Database\Seeder;

class FaskesSeeder extends Seeder
{
    /**
     * Sumber: wawancara Dinkes. Kepemilikan hanya diisi bila jelas dari nama. Koordinat dikosongkan
     * (diisi Dinkes lewat UI). Dua nama perlu konfirmasi ejaan: Klinik Posmor dan Puskesmas samping Wilkot.
     *
     * @var array<int, array{0: string, 1: JenisFaskes, 2: ?Kepemilikan}>
     */
    public const DAFTAR = [
        ['RSUD Selebesolu', JenisFaskes::Rs, Kepemilikan::Pemerintah],
        ['RS AL', JenisFaskes::Rs, Kepemilikan::Militer],
        ['RS Aryoko', JenisFaskes::Rs, null],
        ['RS Maleo', JenisFaskes::Rs, null],
        ['RS Pertamina', JenisFaskes::Rs, Kepemilikan::Bumn],
        ['RS Mubara', JenisFaskes::Rs, null],
        ['RS Herlina', JenisFaskes::Rs, null],
        ['Klinik Posmor 3 Km 16', JenisFaskes::Klinik, null],
        ['Puskesmas Tanjung Kasuari', JenisFaskes::Puskesmas, Kepemilikan::Pemerintah],
        ['Puskesmas Sorong Barat', JenisFaskes::Puskesmas, Kepemilikan::Pemerintah],
        ['Puskesmas Dum', JenisFaskes::Puskesmas, Kepemilikan::Pemerintah],
        ['Puskesmas Malanu', JenisFaskes::Puskesmas, Kepemilikan::Pemerintah],
        ['Puskesmas Malawei', JenisFaskes::Puskesmas, Kepemilikan::Pemerintah],
        ['Puskesmas Remu', JenisFaskes::Puskesmas, Kepemilikan::Pemerintah],
        ['Puskesmas Klasaman', JenisFaskes::Puskesmas, Kepemilikan::Pemerintah],
        ['Puskesmas Sorong (samping Wilkot)', JenisFaskes::Puskesmas, Kepemilikan::Pemerintah],
        ['Puskesmas Sorong Kota', JenisFaskes::Puskesmas, Kepemilikan::Pemerintah],
        ['Puskesmas Malaimsimsa', JenisFaskes::Puskesmas, Kepemilikan::Pemerintah],
        ['Puskesmas Sorong Timur (Moyo)', JenisFaskes::Puskesmas, Kepemilikan::Pemerintah],
    ];

    public function run(): void
    {
        foreach (self::DAFTAR as [$nama, $jenis, $kepemilikan]) {
            // firstOrCreate: data yang sudah disunting Dinkes (koordinat, ketersediaan) tidak ditimpa.
            Faskes::firstOrCreate(
                ['nama' => $nama],
                ['jenis' => $jenis, 'kepemilikan' => $kepemilikan, 'aktif' => true],
            );
        }
    }
}
