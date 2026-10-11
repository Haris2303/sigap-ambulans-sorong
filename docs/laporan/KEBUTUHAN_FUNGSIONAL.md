# Kebutuhan fungsional

Kelas tes ada di `tests/`. Singkatan: PW = `Feature\Warga\PesananWargaTest`, PA = `Feature\Admin\PesananAdminTest`, WF = `Unit\Services\PesananWorkflowTest`, AP = `Feature\AlurPenuhTest`.

| ID | Deskripsi | Aktor | Route | Tes |
|---|---|---|---|---|
| FR-01 | Masuk dengan email dan sandi; akun nonaktif ditolak; percobaan dibatasi 5 kali | Semua | `login`, `login.proses` | `AutentikasiTest` |
| FR-02 | Mendaftar sebagai Masyarakat (peran tidak dapat disisipkan) | Publik | `register`, `register.proses` | `AutentikasiTest` |
| FR-03 | Keluar dari sesi | Semua yang masuk | `logout` | `AutentikasiTest` |
| FR-04 | Melihat info Faskes: ketersediaan bed, dokter, ambulans, peta, penanda "Belum diperbarui" / "Data lama" | Publik | `faskes.index`, `faskes.data` | `Publik\BerandaFaskesTest` |
| FR-05 | Landing dengan alur empat langkah | Publik | `beranda` | `Publik\BerandaFaskesTest` |
| FR-06 | Membuat pesanan (jenis layanan, tingkat darurat, lokasi jemput, Faskes tujuan, data pasien) | Masyarakat | `warga.pesanan.buat`, `warga.pesanan.tinjau` | PW |
| FR-07 | Konfirmasi sebelum kirim; pesanan baru dibuat saat konfirmasi | Masyarakat | `warga.pesanan.konfirmasi`, `warga.pesanan.simpan` | PW |
| FR-08 | Melacak pesanan: linimasa, info ambulans dan sopir, posisi sopir | Masyarakat | `warga.pesanan.show`, `warga.pesanan.status` | PW |
| FR-09 | Membatalkan pesanan sebelum penugasan | Masyarakat | `warga.pesanan.batalkan` | PW, WF, AP |
| FR-10 | Melihat riwayat pesanan sendiri | Masyarakat | `warga.beranda`, `warga.pesanan.index` | PW |
| FR-11 | Memantau dan memverifikasi pesanan masuk (daftar berurutan prioritas, filter, tab status, pencarian kode atau nama pasien) | Dinkes | `admin.dashboard`, `admin.pesanan.index`, `admin.pesanan.show`, `admin.pesanan.ringkasan`, `admin.pesanan.verifikasi` | PA, `Admin\DashboardTest` |
| FR-12 | Menolak pesanan dengan alasan wajib | Dinkes | `admin.pesanan.tolak` | PA, WF |
| FR-13 | Menugaskan ambulans dan sopir (validasi Faskes sama, sopir online, tidak sibuk) | Dinkes | `admin.pesanan.tugaskan` | PA, WF |
| FR-14 | Melepas penugasan dan membatalkan pesanan berjalan (alasan wajib) | Dinkes | `admin.pesanan.lepas`, `admin.pesanan.batalkan` | PA, WF, AP |
| FR-15 | Mengelola master Faskes, Ambulans, Sopir, Petugas Faskes (tanpa hapus; nonaktif lewat `aktif`) | Dinkes | `admin.faskes.*`, `admin.ambulans.*`, `admin.sopir.*`, `admin.petugas.*` | `Admin\FaskesAdminTest`, `Admin\MasterAdminTest` |
| FR-16 | Memantau dan menyunting ketersediaan seluruh Faskes | Dinkes | `admin.ketersediaan.index`, `admin.ketersediaan.update` | `Admin\FaskesAdminTest` |
| FR-17 | Laporan periode, per status/jenis layanan/Faskes, waktu respons, durasi; unduh PDF dan CSV | Dinkes | `admin.laporan.index`, `admin.laporan.pdf`, `admin.laporan.csv` | `Admin\LaporanTest` |
| FR-18 | Melihat pesanan yang menyangkut Faskes sendiri | Petugas Faskes | `petugas.dashboard`, `petugas.pesanan.show` | `Petugas\PetugasTest` |
| FR-19 | Memperbarui ketersediaan Faskes sendiri (tersedia ≤ total, stempel waktu) | Petugas Faskes | `petugas.ketersediaan.edit`, `petugas.ketersediaan.update` | `Petugas\PetugasTest` |
| FR-20 | Mengubah status ambulans Faskes sendiri (`tersedia` ↔ `servis`) | Petugas Faskes | `petugas.ambulans.status` | `Petugas\PetugasTest` |
| FR-21 | Online/offline dan mengirim lokasi GPS | Sopir | `sopir.online`, `sopir.lokasi` | `Sopir\SopirTest` |
| FR-22 | Melihat penugasan (polling JSON) | Sopir | `sopir.dashboard`, `sopir.penugasan` | `Sopir\SopirTest` |
| FR-23 | Menerima, menolak (alasan wajib), berangkat, menyelesaikan | Sopir | `sopir.pesanan.terima`, `.tolak`, `.berangkat`, `.selesai` | `Sopir\SopirTest`, WF, AP |
| FR-24 | Isolasi data per peran (middleware `role` dan `PesananPolicy`) | Semua | semua route terlindungi | `Arsitektur\OtorisasiMatriksTest`, `Auth\PeranDanPolicyTest` |
| FR-25 | Setiap transisi status tercatat di `pesanan_log` | Sistem | — | WF `test_setiap_transisi_menulis_satu_baris_log` |
| FR-26 | Dasbor Dinkes: kartu statistik (menunggu, terverifikasi, berjalan, selesai hari ini menurut zona waktu Asia/Jayapura), grafik batang 7 hari, donat status, ketersediaan, pesanan terbaru dengan tab | Dinkes | `admin.dashboard`, `admin.pesanan.ringkasan` | `Admin\RingkasanDasborTest`, `Admin\DashboardTest`, `Arsitektur\KomponenGrafikTest`, `Arsitektur\GrafikChartJsTest` |
| FR-27 | Dasbor Faskes menampilkan jumlah tersedia per sumber daya; dasbor sopir menampilkan penugasan aktif dalam satu kartu | Petugas Faskes, Sopir | `petugas.dashboard`, `sopir.dashboard` | `Arsitektur\SapuanD6Test`, `Petugas\PetugasTest`, `Sopir\SopirTest` |

## Kebutuhan nonfungsional

| ID | Deskripsi | Bukti |
|---|---|---|
| NFR-01 | Konsistensi istilah, kelas, dan komponen dipindai otomatis | `Arsitektur\KonsistensiTest` |
| NFR-02 | Semua halaman GET dapat dibuka oleh peran yang berhak | `Arsitektur\SmokeRouteTest` |
| NFR-03 | Seeder idempoten | `Unit\Services\SeederTest` |
| NFR-04 | Token tampilan dan CSS sinkron dengan `DESIGN.md`; dokumen desain lulus linter tanpa galat dan peringatan | `Arsitektur\TokenDesainTest`, `node node_modules/@google/design.md/dist/index.js lint DESIGN.md` |
| NFR-05 | Dasbor Dinkes memakai paling banyak 10 query dan tidak bertambah menurut jumlah data | `Admin\RingkasanDasborTest::test_seluruh_dasbor_memakai_paling_banyak_sepuluh_query_tanpa_n_plus_satu` |
| NFR-06 | Kontras pasangan warna memenuhi WCAG AA (kecuali `chart-sky` yang selalu disertai nilai tertulis) | Perhitungan di `docs/MIGRASI_M3.md` |
