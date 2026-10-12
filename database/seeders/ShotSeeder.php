<?php

namespace Database\Seeders;

use App\Enums\JenisAmbulans;
use App\Enums\JenisFaskes;
use App\Enums\JenisLayanan;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Enums\TingkatDarurat;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\Pesanan;
use App\Models\User;
use App\Services\PesananWorkflow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Data demo khusus mode shot (agent/shot.mjs): menirukan papan docs/referensi-b/B1-dasbor.html sedekat mungkin
 * pada database/shot.sqlite. Hanya berjalan di environment shot, hanya pada DB kosong. Seluruh pesanan dibuat
 * lewat PesananWorkflow (CLAUDE.md: perubahan status hanya lewat PesananWorkflow).
 *
 * Batasan skema dan selisih terhadap papan: lihat docs/PROGRESS.md bagian "Mode shot".
 */
class ShotSeeder extends Seeder
{
    public const DOMAIN = '@sigap.test';

    private PesananWorkflow $alur;

    private User $admin;

    private User $sopir;

    private Ambulans $ambulansSopir;

    private Faskes $faskes;

    private Carbon $waktu;

    public function run(): void
    {
        if (! app()->environment('shot')) {
            $this->command?->error('ShotSeeder hanya untuk environment shot (APP_ENV=shot).');

            return;
        }
        if (Pesanan::query()->exists()) {
            $this->command?->warn('ShotSeeder dilewati: tabel pesanan sudah berisi data.');

            return;
        }

        $this->alur = new PesananWorkflow;
        $this->akun();
        $this->ambulansTambahan();

        try {
            $this->pesanan();
        } finally {
            Carbon::setTestNow();
        }
    }

    private function akun(): void
    {
        $this->faskes = Faskes::create([
            'nama' => 'RSUD Selebesolu',
            'jenis' => JenisFaskes::Rs,
            'kepemilikan' => null,
            'alamat' => 'Jalan Basuki Rahmat, Sorong',
            'telepon' => '0951-321000',
            'lat' => -0.8762,
            'lng' => 131.2558,
            'aktif' => true,
            'bed_igd_total' => 14,
            'bed_igd_tersedia' => 1,
            'bed_rawat_inap_total' => 40,
            'bed_rawat_inap_tersedia' => 1,
            'dokter_umum_tersedia' => 4,
            'dokter_spesialis_tersedia' => 2,
            'ketersediaan_diperbarui_pada' => Carbon::parse('2026-10-11 20:40:00', config('app.timezone')),
        ]);

        $this->admin = User::create([
            'name' => 'Admin Dinkes',
            'email' => 'admin'.self::DOMAIN,
            'password' => 'password',
            'role' => Role::Admin,
            'aktif' => true,
            'no_telepon' => '081100000001',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Petugas RSUD Selebesolu',
            'email' => 'petugas'.self::DOMAIN,
            'password' => 'password',
            'role' => Role::PetugasFaskes,
            'aktif' => true,
            'faskes_id' => $this->faskes->id,
            'no_telepon' => '081100000002',
            'email_verified_at' => now(),
        ]);

        $this->sopir = User::create([
            'name' => 'Sopir RSUD Selebesolu',
            'email' => 'sopir'.self::DOMAIN,
            'password' => 'password',
            'role' => Role::Sopir,
            'aktif' => true,
            'faskes_id' => $this->faskes->id,
            'sedang_online' => true,
            'no_telepon' => '081100000003',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Warga Demo',
            'email' => 'warga'.self::DOMAIN,
            'password' => 'password',
            'role' => Role::Warga,
            'aktif' => true,
            'no_telepon' => '081100000004',
            'email_verified_at' => now(),
        ]);

        $this->ambulansSopir = Ambulans::create([
            'faskes_id' => $this->faskes->id,
            'sopir_id' => $this->sopir->id,
            'plat_nomor' => 'PB-1000-SG',
            'jenis' => JenisAmbulans::Darurat,
            'merek_model' => 'Toyota Hiace',
            'status' => StatusAmbulans::Tersedia,
        ]);
    }

    /**
     * 23 ambulans tambahan tanpa sopir tetap, hanya untuk angka ketersediaan (B1: 19 dari 24).
     * 19 tersedia + 3 servis di sini, ditambah ambulans sopir yang berakhir "bertugas" pada SGP-000038 = 19 tersedia dari 24.
     */
    private function ambulansTambahan(): void
    {
        for ($i = 1; $i <= 23; $i++) {
            Ambulans::create([
                'faskes_id' => $this->faskes->id,
                'plat_nomor' => 'PB-'.str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT).'-SG',
                'jenis' => $i % 3 === 0 ? JenisAmbulans::Transport : JenisAmbulans::Darurat,
                'merek_model' => 'Toyota Hiace',
                'status' => $i <= 19 ? StatusAmbulans::Tersedia : StatusAmbulans::Servis,
            ]);
        }
    }

    /**
     * Tujuan: SGP-000037 sampai SGP-000041 persis seperti B1 (37 Selesai sampai 41 Menunggu, 19.54 sampai 21.12).
     * Agar kodenya pas, ada tepat 36 pesanan sebelumnya (Senin 5 Okt sampai Sabtu 10 Okt). Target papan untuk 7 hari
     * (masuk 5,8,6,9,7,4,6 dan status 31/5/3/3/2/1) diskalakan ke 41 pesanan karena satu sopir hanya bisa memegang
     * satu pesanan aktif sekaligus (CLAUDE.md: status hanya lewat PesananWorkflow, tidak ada jalan pintas).
     * Selisih riil dicatat di docs/PROGRESS.md.
     */
    private function pesanan(): void
    {
        // [tanggal, [daftar hasil per pesanan hari itu]]
        $hari = [
            ['2026-10-05', ['selesai', 'selesai', 'selesai', 'ditolak', 'dibatalkan']],
            ['2026-10-06', ['selesai', 'selesai', 'selesai', 'selesai', 'selesai', 'selesai', 'dibatalkan']],
            ['2026-10-07', ['selesai', 'selesai', 'selesai', 'selesai', 'selesai', 'ditolak']],
            ['2026-10-08', ['selesai', 'selesai', 'selesai', 'selesai', 'selesai', 'selesai', 'selesai', 'dibatalkan']],
            ['2026-10-09', ['selesai', 'selesai', 'selesai', 'selesai', 'ditolak', 'dibatalkan']],
            ['2026-10-10', ['selesai', 'selesai', 'selesai', 'dibatalkan']],
        ];

        $nama = [
            'Yustina Rumbrar', 'Bastian Kalalo', 'Ruth Sawen', 'Decky Mofu', 'Agustina Yenu', 'Marthen Kabes',
            'Yuliana Idje', 'Robert Sroyer', 'Feronika Kambu', 'Hengky Baransano', 'Dorkas Rumadas', 'Simon Sawaki',
            'Melkior Imbiri', 'Theresia Mayor', 'Oktovianus Krey', 'Agnes Rahawarin', 'Yunus Yabansabra', 'Priska Hae',
            'Charles Sedik', 'Martina Wospakrik', 'Yulius Ongge', 'Fransiska Kafiar', 'Daniel Batfor', 'Wilhelmina Saflembolo',
            'Petrus Yoku', 'Herlina Rejauw', 'Agustinus Howay', 'Elisabeth Mote', 'Yonas Fatie', 'Sarah Baransano',
            'Markus Yarangga', 'Veronika Rejauw', 'Nikolas Sraun', 'Katrina Mambor', 'Yohanes Imbir', 'Christin Kalami',
        ];
        $layanan = [JenisLayanan::Darurat, JenisLayanan::Rujukan, JenisLayanan::Persalinan];
        $lokasi = [
            'Pasar Remu, Sorong', 'Kompleks Malanu, Sorong', 'Puskesmas Dum, Sorong', 'Jalan Basuki Rahmat Km 8',
            'Kelurahan Klasaman', 'Jalan Sultan Hasanuddin', 'Jalan Pramuka', 'Pelabuhan Sorong',
            'Kompleks Tanjung Kasuari', 'Jalan Jenderal Sudirman', 'Jalan Ahmad Yani Km 3', 'Kampung Baru, Sorong',
        ];

        $urut = 0;
        foreach ($hari as [$tanggal, $hasil]) {
            // Sebar sepanjang hari kerja: mulai 07.00, jarak kira-kira genap.
            $jamAwal = 7;
            $langkah = (int) floor((21 - $jamAwal) * 60 / max(count($hasil), 1));

            foreach ($hasil as $i => $jenisHasil) {
                $jam = $jamAwal + intdiv($i * $langkah, 60);
                $menit = ($i * $langkah) % 60;
                $this->waktu = Carbon::parse($tanggal, config('app.timezone'))->setTime($jam, $menit);
                Carbon::setTestNow($this->waktu);

                $layananTerpilih = $layanan[$urut % count($layanan)];
                $tingkat = $urut % 4 === 0 ? TingkatDarurat::Gawat : TingkatDarurat::Biasa;
                $tujuan = $layananTerpilih === JenisLayanan::Rujukan ? $this->faskes->id : null;

                $pesanan = $this->alur->buat($this->wargaFiller(), [
                    'nama_pasien' => $nama[$urut % count($nama)],
                    'no_telepon_kontak' => '0811000'.str_pad((string) ($urut + 1), 4, '0', STR_PAD_LEFT),
                    'jenis_layanan' => $layananTerpilih,
                    'tingkat_darurat' => $tingkat,
                    'lokasi_jemput' => $lokasi[$urut % count($lokasi)],
                    'faskes_tujuan_id' => $tujuan,
                ]);

                match ($jenisHasil) {
                    'selesai' => $this->selesaikanPenuh($pesanan),
                    'ditolak' => $this->tolakSingkat($pesanan),
                    'dibatalkan' => $this->batalkanSetelahVerifikasi($pesanan),
                };

                $urut++;
            }
        }

        $this->persisTerbaru();
    }

    private ?User $warga = null;

    private function wargaFiller(): User
    {
        return $this->warga ??= User::where('email', 'warga'.self::DOMAIN)->firstOrFail();
    }

    private function maju(int $menit): void
    {
        $this->waktu = $this->waktu->copy()->addMinutes($menit);
        Carbon::setTestNow($this->waktu);
    }

    private function selesaikanPenuh(Pesanan $pesanan): void
    {
        $this->maju(4);
        $this->alur->verifikasi($pesanan, $this->admin);
        $this->maju(4);
        $this->alur->tugaskan($pesanan, $this->admin, $this->ambulansSopir, $this->sopir);
        $this->maju(3);
        $this->alur->terima($pesanan, $this->sopir);
        $this->maju(5);
        $this->alur->mulaiPerjalanan($pesanan, $this->sopir);
        $this->maju(25);
        $this->alur->selesaikan($pesanan, $this->sopir);
    }

    private function tolakSingkat(Pesanan $pesanan): void
    {
        $this->maju(6);
        $this->alur->tolak($pesanan, $this->admin, 'Data lokasi tidak jelas, pemohon tidak dapat dihubungi.');
    }

    private function batalkanSetelahVerifikasi(Pesanan $pesanan): void
    {
        $this->maju(4);
        $this->alur->verifikasi($pesanan, $this->admin);
        $this->maju(8);
        $this->alur->batalkanOlehAdmin($pesanan, $this->admin, 'Pemohon membatalkan lewat telepon.');
    }

    /** SGP-000037 sampai SGP-000041, persis seperti B1-dasbor.html (nama, layanan, gawat, status, jam). */
    private function persisTerbaru(): void
    {
        $this->waktu = Carbon::parse('2026-10-11 19:54:00', config('app.timezone'));
        Carbon::setTestNow($this->waktu);
        $p37 = $this->alur->buat($this->wargaFiller(), [
            'nama_pasien' => 'Frans Ukru',
            'no_telepon_kontak' => '081100000037',
            'jenis_layanan' => JenisLayanan::Rujukan,
            'tingkat_darurat' => TingkatDarurat::Biasa,
            'lokasi_jemput' => 'Puskesmas Remu, Sorong',
            'faskes_tujuan_id' => $this->faskes->id,
        ]);
        $this->selesaikanPenuhPada($p37, '2026-10-11 19:54:00');

        $this->waktu = Carbon::parse('2026-10-11 20:31:00', config('app.timezone'));
        Carbon::setTestNow($this->waktu);
        $p38 = $this->alur->buat($this->wargaFiller(), [
            'nama_pasien' => 'Siti Aisyah',
            'no_telepon_kontak' => '081100000038',
            'jenis_layanan' => JenisLayanan::Darurat,
            'tingkat_darurat' => TingkatDarurat::Gawat,
            'lokasi_jemput' => 'Jalan Ahmad Yani, Sorong',
        ]);
        $this->maju(2);
        $this->alur->verifikasi($p38, $this->admin);
        $this->maju(2);
        $this->alur->tugaskan($p38, $this->admin, $this->ambulansSopir, $this->sopir);
        $this->maju(2);
        $this->alur->terima($p38, $this->sopir);
        $this->maju(2);
        $this->alur->mulaiPerjalanan($p38, $this->sopir);
        // Tetap "dalam perjalanan": ini pesanan terakhir yang memegang sopir dan ambulans satu-satunya.

        $this->waktu = Carbon::parse('2026-10-11 20:48:00', config('app.timezone'));
        Carbon::setTestNow($this->waktu);
        $p39 = $this->alur->buat($this->wargaFiller(), [
            'nama_pasien' => 'Abdul Rahman',
            'no_telepon_kontak' => '081100000039',
            'jenis_layanan' => JenisLayanan::Rujukan,
            'tingkat_darurat' => TingkatDarurat::Biasa,
            'lokasi_jemput' => 'Kelurahan Klasaman',
            'faskes_tujuan_id' => $this->faskes->id,
        ]);
        $this->maju(1);
        $this->alur->verifikasi($p39, $this->admin);

        $this->waktu = Carbon::parse('2026-10-11 21:05:00', config('app.timezone'));
        Carbon::setTestNow($this->waktu);
        $this->alur->buat($this->wargaFiller(), [
            'nama_pasien' => 'Yosep Mayor',
            'no_telepon_kontak' => '081100000040',
            'jenis_layanan' => JenisLayanan::Persalinan,
            'tingkat_darurat' => TingkatDarurat::Biasa,
            'lokasi_jemput' => 'Kompleks Tanjung Kasuari',
        ]);

        $this->waktu = Carbon::parse('2026-10-11 21:12:00', config('app.timezone'));
        Carbon::setTestNow($this->waktu);
        $this->alur->buat($this->wargaFiller(), [
            'nama_pasien' => 'Maria Kambu',
            'no_telepon_kontak' => '081100000041',
            'jenis_layanan' => JenisLayanan::Darurat,
            'tingkat_darurat' => TingkatDarurat::Gawat,
            'lokasi_jemput' => 'Jalan Basuki Rahmat Km 8',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-10-11 21:26:00', config('app.timezone')));
    }

    private function selesaikanPenuhPada(Pesanan $pesanan, string $dibuatPada): void
    {
        $this->waktu = Carbon::parse($dibuatPada, config('app.timezone'));
        $this->maju(3);
        $this->alur->verifikasi($pesanan, $this->admin);
        $this->maju(3);
        $this->alur->tugaskan($pesanan, $this->admin, $this->ambulansSopir, $this->sopir);
        $this->maju(3);
        $this->alur->terima($pesanan, $this->sopir);
        $this->maju(3);
        $this->alur->mulaiPerjalanan($pesanan, $this->sopir);
        $this->maju(15);
        $this->alur->selesaikan($pesanan, $this->sopir);
    }
}
