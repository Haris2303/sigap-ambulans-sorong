<?php

namespace Database\Seeders;

use App\Enums\JenisLayanan;
use App\Enums\TingkatDarurat;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PesananWorkflow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Pesanan contoh untuk dasbor dan laporan. Hanya environment local; semua lewat PesananWorkflow.
 */
class PesananDemoSeeder extends Seeder
{
    private PesananWorkflow $alur;

    private User $admin;

    private User $warga;

    private Carbon $waktu;

    public function run(): void
    {
        if (! app()->environment('local') || Pesanan::query()->exists()) {
            return;
        }

        $admin = User::where('email', 'admin'.AkunDemoSeeder::DOMAIN)->first();
        $warga = User::where('email', 'warga'.AkunDemoSeeder::DOMAIN)->first();
        if ($admin === null || $warga === null) {
            return;
        }

        $this->alur = new PesananWorkflow;
        $this->admin = $admin;
        $this->warga = $warga;

        try {
            $this->semua();
        } finally {
            Carbon::setTestNow();
        }
    }

    private function semua(): void
    {
        $tujuan = Faskes::where('nama', 'RSUD Selebesolu')->value('id');

        // Selesai
        $p = $this->mulai(6, 8, JenisLayanan::Darurat, TingkatDarurat::Gawat, 'Pasar Remu, Sorong');
        $this->selesai($p, 'selebesolu');
        $p = $this->mulai(5, 10, JenisLayanan::Persalinan, TingkatDarurat::Gawat, 'Kompleks Malanu, Sorong');
        $this->selesai($p, 'al');
        $p = $this->mulai(3, 9, JenisLayanan::Rujukan, TingkatDarurat::Biasa, 'Puskesmas Dum, Sorong', $tujuan);
        $this->selesai($p, 'aryoko');
        $p = $this->mulai(2, 14, JenisLayanan::Darurat, TingkatDarurat::Biasa, 'Jalan Basuki Rahmat Km 8');
        $this->selesai($p, 'klasaman');

        // Ditolak dan dibatalkan
        $p = $this->mulai(4, 11, JenisLayanan::Darurat, TingkatDarurat::Biasa, 'Jalan Ahmad Yani');
        $this->maju(5);
        $this->alur->tolak($p, $this->admin, 'Data lokasi tidak jelas, pemohon tidak dapat dihubungi.');
        $p = $this->mulai(4, 15, JenisLayanan::Rujukan, TingkatDarurat::Biasa, 'Kelurahan Klasaman', $tujuan);
        $this->maju(10);
        $this->alur->batalkanOlehWarga($p, $this->warga, 'Pasien dibawa keluarga.');
        $p = $this->mulai(2, 9, JenisLayanan::Darurat, TingkatDarurat::Gawat, 'Jalan Sultan Hasanuddin');
        $this->tugaskan($p, 'posmor');
        $this->maju(15);
        $this->alur->batalkanOlehAdmin($p, $this->admin, 'Pemohon membatalkan lewat telepon.');

        // Masih berjalan
        $p = $this->mulai(1, 16, JenisLayanan::Darurat, TingkatDarurat::Biasa, 'Jalan Pramuka');
        $this->maju(5);
        $this->alur->verifikasi($p, $this->admin);

        $p = $this->mulai(1, 18, JenisLayanan::Darurat, TingkatDarurat::Gawat, 'Pelabuhan Sorong');
        [, $sopir] = $this->tugaskan($p, 'selebesolu');
        $this->maju(2);
        $this->alur->terima($p, $sopir);
        $this->maju(3);
        $this->alur->mulaiPerjalanan($p, $sopir);

        $p = $this->mulai(0, 7, JenisLayanan::Persalinan, TingkatDarurat::Gawat, 'Kompleks Tanjung Kasuari');
        [, $sopir] = $this->tugaskan($p, 'al');
        $this->maju(2);
        $this->alur->terima($p, $sopir);

        $p = $this->mulai(0, 8, JenisLayanan::Rujukan, TingkatDarurat::Biasa, 'Puskesmas Remu', $tujuan);
        $this->tugaskan($p, 'aryoko');

        $this->mulai(0, 9, JenisLayanan::Darurat, TingkatDarurat::Gawat, 'Jalan Jenderal Sudirman');
        $this->mulai(0, 10, JenisLayanan::Darurat, TingkatDarurat::Biasa, 'Jalan Ahmad Yani Km 3');
    }

    private function mulai(int $hariLalu, int $jam, JenisLayanan $layanan, TingkatDarurat $tingkat, string $lokasi, ?int $tujuan = null): Pesanan
    {
        $this->waktu = now()->subDays($hariLalu)->setTime($jam, 0);
        Carbon::setTestNow($this->waktu);

        return $this->alur->buat($this->warga, [
            'nama_pasien' => 'Pasien Contoh '.($hariLalu + 1),
            'no_telepon_kontak' => '081100000000',
            'jenis_layanan' => $layanan,
            'tingkat_darurat' => $tingkat,
            'lokasi_jemput' => $lokasi,
            'faskes_tujuan_id' => $tujuan,
        ]);
    }

    private function maju(int $menit): void
    {
        $this->waktu = $this->waktu->copy()->addMinutes($menit);
        Carbon::setTestNow($this->waktu);
    }

    /** @return array{0: Ambulans, 1: User} */
    private function tugaskan(Pesanan $pesanan, string $slug): array
    {
        $this->maju(5);
        $this->alur->verifikasi($pesanan, $this->admin);

        $sopir = User::where('email', 'sopir.'.$slug.AkunDemoSeeder::DOMAIN)->firstOrFail();
        $sopir->update(['sedang_online' => true]);
        $ambulans = Ambulans::where('sopir_id', $sopir->id)->firstOrFail();

        $this->maju(5);
        $this->alur->tugaskan($pesanan, $this->admin, $ambulans, $sopir);

        return [$ambulans, $sopir];
    }

    private function selesai(Pesanan $pesanan, string $slug): void
    {
        [, $sopir] = $this->tugaskan($pesanan, $slug);
        $this->maju(2);
        $this->alur->terima($pesanan, $sopir);
        $this->maju(5);
        $this->alur->mulaiPerjalanan($pesanan, $sopir);
        $this->maju(40);
        $this->alur->selesaikan($pesanan, $sopir);
    }
}
