<?php

namespace Database\Seeders;

use App\Enums\JenisLayanan;
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
 * Dasbor Dinkes arah B ("DB" langkah 6, docs/RETEMA_B.md): sekitar 70 pesanan selama 30 hari dengan sebaran status
 * wajar, supaya dasbor pada DB lokal tampak ramai seperti B1-dasbor-1440.png. Hanya menambah baris (tidak pernah
 * menghapus), hanya environment local, dan seluruh pesanan lewat PesananWorkflow (CLAUDE.md). Dua sumber daya
 * (bed IGD dan bed rawat inap satu Faskes) diperbarui menjadi menipis; ini satu-satunya pembaruan baris yang ada
 * (bukan baris baru), karena ketersediaan memang data operasional yang berubah, bukan data historis.
 *
 * Jalankan manual: php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    /** Jumlah hari ke belakang (termasuk hari ini) yang diisi pesanan. */
    private const HARI = 30;

    private PesananWorkflow $alur;

    private User $admin;

    private User $warga;

    /** @var array<int, array{0: User, 1: Ambulans}> pasangan sopir dan ambulans tetapnya */
    private array $sopirAmbulans;

    private Carbon $waktu;

    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->error('DemoSeeder hanya untuk environment local.');

            return;
        }

        $admin = User::where('email', 'admin'.AkunDemoSeeder::DOMAIN)->first();
        $warga = User::where('email', 'warga'.AkunDemoSeeder::DOMAIN)->first();
        if ($admin === null || $warga === null) {
            $this->command?->error('Akun demo belum ada. Jalankan dulu: php artisan db:seed (FaskesSeeder, AmbulansSeeder, AkunDemoSeeder).');

            return;
        }

        $this->admin = $admin;
        $this->warga = $warga;
        $this->alur = new PesananWorkflow;
        $this->sopirAmbulans = $this->pasanganSopirAmbulans();
        if ($this->sopirAmbulans === []) {
            $this->command?->error('Tidak ada sopir dengan ambulans tetap. Jalankan dulu AkunDemoSeeder.');

            return;
        }

        try {
            $this->pesananRiwayat();
            $this->pesananLive();
        } finally {
            Carbon::setTestNow();
        }

        $this->ketersediaanMenipis();

        $this->command?->info('DemoSeeder selesai: pesanan ditambahkan dan dua sumber daya ketersediaan dibuat menipis.');
    }

    /** @return array<int, array{0: User, 1: Ambulans}> */
    private function pasanganSopirAmbulans(): array
    {
        $pasangan = [];
        foreach (array_keys(AkunDemoSeeder::SOPIR) as $slug) {
            $sopir = User::where('email', 'sopir.'.$slug.AkunDemoSeeder::DOMAIN)->first();
            $ambulans = $sopir === null ? null : Ambulans::where('sopir_id', $sopir->id)->first();
            if ($sopir !== null && $ambulans !== null) {
                if (! $sopir->sedang_online) {
                    $sopir->update(['sedang_online' => true]);
                }
                $pasangan[] = [$sopir, $ambulans];
            }
        }

        return $pasangan;
    }

    /**
     * Sampai $maks pasangan sopir-ambulans yang ambulansnya benar-benar berstatus Tersedia saat ini (dibaca ulang
     * dari DB, bukan indeks tetap), supaya DemoSeeder aman dipanggil berkali-kali tanpa membentur pesanan "sedang
     * berjalan" yang ditinggalkan oleh panggilan sebelumnya.
     *
     * @return array<int, array{0: User, 1: Ambulans}>
     */
    private function pasanganTersedia(int $maks): array
    {
        $hasil = [];
        foreach ($this->sopirAmbulans as [$sopir, $ambulans]) {
            $segar = Ambulans::find($ambulans->id);
            if ($segar !== null && $segar->status === StatusAmbulans::Tersedia) {
                $hasil[] = [$sopir, $segar];
            }
            if (count($hasil) >= $maks) {
                break;
            }
        }

        return $hasil;
    }

    /**
     * ~60 pesanan dari 29 hari lalu sampai kemarin, semuanya berstatus akhir (selesai, ditolak, dibatalkan) —
     * pesanan lama yang masih menunggu atau berjalan tidak realistis.
     */
    private function pesananRiwayat(): void
    {
        // Bobot kasar: sebagian besar selesai, sebagian ditolak atau dibatalkan (docs/referensi-b/B1: ~69% selesai).
        $hasil = ['selesai', 'selesai', 'selesai', 'selesai', 'selesai', 'selesai', 'selesai', 'ditolak', 'dibatalkan'];

        $urut = 0;
        for ($hariLalu = self::HARI - 1; $hariLalu >= 1; $hariLalu--) {
            $jumlahHariIni = 1 + ($this->acak($hariLalu, 'jumlah') % 3); // 1 sampai 3 pesanan sehari.
            for ($i = 0; $i < $jumlahHariIni; $i++) {
                $jam = 7 + ($this->acak($hariLalu * 10 + $i, 'jam') % 14); // 07.00 sampai 20.59.
                $menit = $this->acak($hariLalu * 10 + $i, 'menit') % 60;
                $this->waktu = now()->subDays($hariLalu)->setTime($jam, $menit);
                Carbon::setTestNow($this->waktu);

                $pesanan = $this->buatPesanan($urut);
                $jenisHasil = $hasil[$this->acak($urut, 'hasil') % count($hasil)];
                match ($jenisHasil) {
                    'selesai' => $this->selesaikanPenuh($pesanan),
                    'ditolak' => $this->tolak($pesanan),
                    'dibatalkan' => $this->batalkan($pesanan),
                };

                $urut++;
            }
        }
    }

    /**
     * ~10 pesanan hari ini: beberapa menunggu verifikasi, satu terverifikasi, beberapa sedang berjalan, beberapa
     * selesai hari ini — supaya kartu statistik "saat ini" dan tabel "Pesanan terbaru" terisi.
     */
    private function pesananLive(): void
    {
        $urut = 1000;
        $jamAwal = max(7, (int) now()->format('G') - 6);
        $jamAkhir = (int) now()->format('G');

        // Dua selesai hari ini.
        foreach ([0, 1] as $i) {
            $this->waktu = now()->startOfDay()->setTime($jamAwal + $i, 15);
            Carbon::setTestNow($this->waktu);
            $this->selesaikanPenuh($this->buatPesanan($urut++));
        }

        // Sampai dua sedang berjalan (ditugaskan dan dalam perjalanan), hanya memakai pasangan yang benar-benar
        // tersedia saat ini (periksa ulang, bukan indeks tetap) supaya seeder aman dijalankan berulang kali.
        $tersedia = $this->pasanganTersedia(2);

        if (isset($tersedia[0])) {
            $this->waktu = now()->subMinutes(50);
            Carbon::setTestNow($this->waktu);
            $p = $this->buatPesanan($urut++);
            [$sopir, $ambulans] = $tersedia[0];
            $this->maju(4);
            $this->alur->verifikasi($p, $this->admin);
            $this->maju(4);
            $this->alur->tugaskan($p, $this->admin, $ambulans, $sopir);
            $this->maju(3);
            $this->alur->terima($p, $sopir);
        }

        if (isset($tersedia[1])) {
            $this->waktu = now()->subMinutes(35);
            Carbon::setTestNow($this->waktu);
            $p = $this->buatPesanan($urut++, TingkatDarurat::Gawat);
            [$sopir, $ambulans] = $tersedia[1];
            $this->maju(3);
            $this->alur->verifikasi($p, $this->admin);
            $this->maju(3);
            $this->alur->tugaskan($p, $this->admin, $ambulans, $sopir);
            $this->maju(3);
            $this->alur->terima($p, $sopir);
            $this->maju(4);
            $this->alur->mulaiPerjalanan($p, $sopir);
        }

        // Satu terverifikasi, menunggu penugasan.
        $this->waktu = now()->subMinutes(18);
        Carbon::setTestNow($this->waktu);
        $p = $this->buatPesanan($urut++);
        $this->maju(5);
        $this->alur->verifikasi($p, $this->admin);

        // Dua sampai tiga masih menunggu verifikasi, termasuk satu yang cukup lama menunggu.
        $this->waktu = now()->subMinutes(28);
        Carbon::setTestNow($this->waktu);
        $this->buatPesanan($urut++, TingkatDarurat::Gawat);

        $this->waktu = now()->subMinutes(9);
        Carbon::setTestNow($this->waktu);
        $this->buatPesanan($urut++);
    }

    private function buatPesanan(int $urut, ?TingkatDarurat $tingkatTetap = null): Pesanan
    {
        $nama = self::NAMA[$urut % count(self::NAMA)];
        $layanan = self::LAYANAN[$urut % count(self::LAYANAN)];
        $lokasi = self::LOKASI[$urut % count(self::LOKASI)];
        $tingkat = $tingkatTetap ?? ($this->acak($urut, 'gawat') % 5 === 0 ? TingkatDarurat::Gawat : TingkatDarurat::Biasa);
        $tujuan = $layanan->wajibFaskesTujuan() ? Faskes::query()->where('aktif', true)->inRandomOrder()->value('id') : null;

        return $this->alur->buat($this->warga, [
            'nama_pasien' => $nama,
            'no_telepon_kontak' => '0811'.str_pad((string) (100000 + $urut), 7, '0', STR_PAD_LEFT),
            'jenis_layanan' => $layanan,
            'tingkat_darurat' => $tingkat,
            'lokasi_jemput' => $lokasi,
            'faskes_tujuan_id' => $tujuan,
        ]);
    }

    private function selesaikanPenuh(Pesanan $pesanan): void
    {
        // Pasangan yang benar-benar tersedia saat ini, dibaca ulang dari DB (bukan array_rand atas daftar tetap):
        // satu atau dua ambulans bisa sedang ditinggal "sedang berjalan" oleh pemanggilan DemoSeeder sebelumnya.
        $tersedia = $this->pasanganTersedia(count($this->sopirAmbulans));
        if ($tersedia === []) {
            $this->tolak($pesanan);

            return;
        }
        [$sopir, $ambulans] = $tersedia[array_rand($tersedia)];
        $this->maju(5);
        $this->alur->verifikasi($pesanan, $this->admin);
        $this->maju(5);
        $this->alur->tugaskan($pesanan, $this->admin, $ambulans, $sopir);
        $this->maju(3);
        $this->alur->terima($pesanan, $sopir);
        $this->maju(5);
        $this->alur->mulaiPerjalanan($pesanan, $sopir);
        $this->maju(20 + ($this->acak($pesanan->id, 'durasi') % 30));
        $this->alur->selesaikan($pesanan, $sopir);
    }

    private function tolak(Pesanan $pesanan): void
    {
        $this->maju(6);
        $this->alur->tolak($pesanan, $this->admin, 'Data lokasi tidak jelas, pemohon tidak dapat dihubungi.');
    }

    private function batalkan(Pesanan $pesanan): void
    {
        $this->maju(4);
        $this->alur->verifikasi($pesanan, $this->admin);
        $this->maju(8);
        $this->alur->batalkanOlehAdmin($pesanan, $this->admin, 'Pemohon membatalkan lewat telepon.');
    }

    private function maju(int $menit): void
    {
        $this->waktu = $this->waktu->copy()->addMinutes($menit);
        Carbon::setTestNow($this->waktu);
    }

    /** Angka semu deterministik 0 sampai 99 dari $n dan $garam, supaya sebaran stabil antar baris tanpa rand() asli. */
    private function acak(int $n, string $garam): int
    {
        return crc32($garam.'-'.$n) % 100;
    }

    /**
     * Dua sumber daya menipis (docs/RETEMA_B.md "DB" langkah 6): bed IGD dan bed rawat inap RSUD Selebesolu,
     * meniru docs/referensi-b/B1-dasbor-1440.png ("dari 14, menipis" dan "dari 40, menipis"). Pembaruan baris yang
     * sudah ada (bukan baris baru): ketersediaan adalah data operasional yang Faskes perbarui, bukan riwayat.
     */
    private function ketersediaanMenipis(): void
    {
        Faskes::where('nama', 'RSUD Selebesolu')->update([
            'bed_igd_total' => 14,
            'bed_igd_tersedia' => 1,
            'bed_rawat_inap_total' => 40,
            'bed_rawat_inap_tersedia' => 1,
            'dokter_umum_tersedia' => 4,
            'dokter_spesialis_tersedia' => 2,
            'ketersediaan_diperbarui_pada' => now(),
        ]);
    }

    /** @var array<int, string> */
    private const NAMA = [
        'Yustina Rumbrar', 'Bastian Kalalo', 'Ruth Sawen', 'Decky Mofu', 'Agustina Yenu', 'Marthen Kabes',
        'Yuliana Idje', 'Robert Sroyer', 'Feronika Kambu', 'Hengky Baransano', 'Dorkas Rumadas', 'Simon Sawaki',
        'Melkior Imbiri', 'Theresia Mayor', 'Oktovianus Krey', 'Agnes Rahawarin', 'Yunus Yabansabra', 'Priska Hae',
        'Charles Sedik', 'Martina Wospakrik', 'Yulius Ongge', 'Fransiska Kafiar', 'Daniel Batfor', 'Wilhelmina Saflembolo',
        'Petrus Yoku', 'Herlina Rejauw', 'Agustinus Howay', 'Elisabeth Mote', 'Yonas Fatie', 'Sarah Baransano',
        'Markus Yarangga', 'Veronika Rejauw', 'Nikolas Sraun', 'Katrina Mambor', 'Yohanes Imbir', 'Christin Kalami',
    ];

    /** @var array<int, JenisLayanan> */
    private const LAYANAN = [JenisLayanan::Darurat, JenisLayanan::Darurat, JenisLayanan::Rujukan, JenisLayanan::Persalinan];

    /** @var array<int, string> */
    private const LOKASI = [
        'Pasar Remu, Sorong', 'Kompleks Malanu, Sorong', 'Puskesmas Dum, Sorong', 'Jalan Basuki Rahmat Km 8',
        'Kelurahan Klasaman', 'Jalan Sultan Hasanuddin', 'Jalan Pramuka', 'Pelabuhan Sorong',
        'Kompleks Tanjung Kasuari', 'Jalan Jenderal Sudirman', 'Jalan Ahmad Yani Km 3', 'Kampung Baru, Sorong',
    ];
}
