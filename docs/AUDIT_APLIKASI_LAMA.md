# AUDIT APLIKASI — SIGAP Ambulans (Dinkes Kota Sorong)

Audit baca-saja. Tidak ada kode diubah, tidak ada migrate/seed/install dijalankan. Satu-satunya perintah artisan yang dipakai: `route:list` (baca-saja). Nilai `.env` tidak dibaca; hanya nama variabel dari `.env.example`.

## 1. Stack

| Item | Temuan | Bukti |
|---|---|---|
| PHP | `^8.1` (CLI lokal 8.3.4) | `composer.json` |
| Framework | Laravel `^10.0` | `composer.json` |
| Database | MySQL (`DB_CONNECTION=mysql`; nama DB/user hanya variabel `DB_*`) | `.env.example` |
| Paket penting | `barryvdh/laravel-dompdf` (PDF laporan), `laravel/sanctum` (terpasang, hanya dipakai route bawaan `/api/user`), `guzzlehttp/guzzle`, `laravel/tinker` | `composer.json` |
| Frontend | Tidak ada build step dipakai: Tailwind via CDN, Tabler Icons via CDN, Leaflet 1.9.4 + Leaflet Routing Machine (`@latest`) via CDN, Nominatim OSM (geocoding). `package.json` hanya vite/axios bawaan | `resources/views/**`, `package.json` |
| Role/permission | Tidak ada paket (spatie dll); role = kolom enum + middleware buatan sendiri | lihat bagian 2 |
| Notifikasi | Tidak ada paket/Notification class/email/WA/FCM. Hanya polling JS + Web Notification API browser + `navigator.vibrate` di dashboard sopir | `pengemudi/dashboard.blade.php` |
| Realtime | Tidak ada websocket/Pusher/Echo. `BROADCAST_DRIVER=log`, `QUEUE_CONNECTION=sync`. Realtime = polling `setInterval` 5 detik | `.env.example`, view |
| Test | Hanya `ExampleTest` bawaan (Feature + Unit); tidak ada test aplikasi | `tests/` |

## 2. Peran dan autentikasi

| Aspek | Temuan | Bukti |
|---|---|---|
| Penyimpanan role | Kolom `users.role` enum(`warga`,`pengemudi`,`admin`), default `warga` | `database/migrations/2014_10_12_000000_create_users_table.php` |
| Istilah vs laporan | Laporan: Pengguna/Admin/Sopir → aplikasi: `warga`/`admin`/`pengemudi`. Tidak ada role petugas RS/Puskesmas | — |
| Middleware | `auth` + `App\Http\Middleware\RoleMiddleware:<role>` dipanggil lewat class (bukan alias di `Kernel.php`) | `routes/web.php`, `app/Http/Middleware/RoleMiddleware.php` |
| Perilaku salah role | Redirect ke dashboard role sendiri + flash `error` | `RoleMiddleware.php` |
| Role default registrasi | Selalu `warga` (di-hardcode, bukan dari input) | `AuthController@register` |
| Pembuatan admin/sopir | Admin dibuat via seeder; sopir dibuat admin via menu Kelola Sopir | `DatabaseSeeder.php`, `AdminController@sopirStore` |
| Login | Email + password, `session()->regenerate()`, redirect per role | `AuthController@login` |
| Guest middleware | Route `/login` dan `/register` tidak memakai `guest`; user yang sudah login masih bisa membuka halaman login | `routes/web.php` |

## 3. Routes (`php artisan route:list` berhasil; 40 route)

| Method | URI | Handler | Middleware / role |
|---|---|---|---|
| GET | `/` | closure → view `welcome` | publik |
| GET | `/login` | closure → view `login` | publik |
| POST | `/login` | AuthController@login | publik |
| POST | `/logout` | AuthController@logout | publik (tanpa auth) |
| GET/POST | `/register` | AuthController@showRegister / @register | publik |
| GET | `/warga/beranda` | WargaController@beranda | auth + warga |
| GET/POST | `/warga/pesan` | WargaController@pesan / @simpanPesanan | auth + warga |
| GET | `/warga/lacak/{id}` | WargaController@lacak | auth + warga |
| GET | `/warga/pesanan/{id}/status` | closure (JSON) | auth + warga |
| GET | `/warga/pesanan/{id}/lokasi-sopir` | closure (JSON) | auth + warga |
| PUT | `/warga/pesanan/{id}/batalkan` | closure | auth + warga |
| GET | `/warga/riwayat` | WargaController@riwayat | auth + warga |
| GET | `/pengemudi/dashboard` | PengemudiController@dashboard | auth + pengemudi |
| POST | `/pengemudi/toggle-online` | @toggleOnline | auth + pengemudi |
| GET | `/pengemudi/cek-pesanan` | @cekPesananBaru | auth + pengemudi |
| POST | `/pengemudi/pesanan/{id}/terima` | @terima | auth + pengemudi |
| POST | `/pengemudi/pesanan/{id}/tolak` | @tolak | auth + pengemudi |
| POST | `/pengemudi/pesanan/{id}/status` | @updateStatus | auth + pengemudi |
| POST | `/pengemudi/update-lokasi` | @updateLokasi | auth + pengemudi |
| GET | `/admin/dashboard` | AdminController@dashboard | auth + admin |
| GET | `/admin/pesanan` | @pesanan | auth + admin |
| PUT | `/admin/pesanan/{id}/tugaskan` | @tugaskanSopir | auth + admin |
| PUT | `/admin/pesanan/{id}/tolak` | @tolakPesanan | auth + admin |
| GET/POST/PUT/DELETE | `/admin/master/rumah-sakit[/{id}]` | @rumahSakit, Store, Update, Destroy | auth + admin |
| GET/POST/PUT/DELETE | `/admin/master/sopir[/{id}]` | @sopir, Store, Update, Destroy | auth + admin |
| GET/POST/PUT/DELETE | `/admin/master/ambulan[/{id}]` | @ambulan, Store, Update, Destroy | auth + admin |
| GET | `/admin/laporan`, `/admin/laporan/pdf` | @laporan, @laporanPdf | auth + admin |
| GET | `/api/user` | closure (bawaan Laravel) | auth:sanctum |

**Route putus:** tidak ada. Semua controller/method ada; seluruh `route('...')` di view cocok dengan nama route; semua view yang dipanggil ada. Catatan: tidak ada route admin tanpa middleware.

## 4. Database (7 migration aplikasi + 4 bawaan)

**users**: id, name, email (unique), email_verified_at, password, `role` enum(warga,pengemudi,admin) default warga, `rumah_sakit_id` FK→rumah_sakit (nullable, set null), no_telepon, `is_online` bool default false, `lat_pengemudi`/`lng_pengemudi` decimal(10,7) nullable, remember_token, timestamps.

**pesanan**: id, `user_id` FK→users (cascade), `pengemudi_id` FK→users (nullable, set null), lokasi_jemput (string), `lat_jemput`/`lng_jemput` decimal(10,7) nullable, `rs_tujuan` (string, nama RS), `rs_id` FK→rumah_sakit (nullable, set null), `tingkat_darurat` enum(biasa,gawat) default biasa, catatan (text), **`status` enum(menunggu, diterima, dalam_perjalanan, selesai, dibatalkan) default menunggu**, timestamps.

**rumah_sakit**: id, nama, alamat, telepon, `jenis` string default `umum` (nilai di form: umum/swasta/militer/puskesmas/klinik), lat, lng (decimal 10,7 nullable), `aktif` bool default true, timestamps.

**ambulan**: id, `rumah_sakit_id` FK (cascade), `pengemudi_id` FK→users (nullable), `plat_nomor` unique, `jenis` string default `transport` (bebas, bukan enum), merk_model, `status` enum(tersedia, bertugas, servis), timestamps.

**Bawaan**: password_reset_tokens, failed_jobs, personal_access_tokens, sessions (migrasi `sessions` ada walau `SESSION_DRIVER=file` di `.env.example`).

**Seeder** (`DatabaseSeeder.php`): hanya tabel `users`, 3 baris (1 warga, 1 pengemudi, 1 admin). Tidak ada seed RS, ambulan, atau pesanan. Password seeder di-hardcode di source (lemah, jangan dipakai di produksi).

**Tidak ada kolom/tabel** untuk: ambulan di pesanan (`ambulan_id`), jenis layanan, bed IGD/rawat inap, dokter, kapasitas, riwayat perubahan status, notifikasi, kolom waktu terima/selesai.

## 5. Model dan relasi

| Model | Relasi / catatan |
|---|---|
| `User` | `rumahSakit()` belongsTo; `pesananAktif()` hasMany Pesanan (status diterima/dalam_perjalanan). `$fillable` memuat `role` |
| `Pesanan` | `warga()`, `pengemudi()` belongsTo User; `rumahSakit()` belongsTo via `rs_id`; `statusLabel()`; `namaRS()` (fallback slug lama `rsud_sele_be_solu` dst.); `sopirOnlineDiRS()` (tidak dipakai di controller) |
| `RumahSakit` | `pengemudi()` hasMany User; `ambulan()` hasMany; `pesanan()` hasMany **memakai `rs_tujuan`→`nama`**, bukan `rs_id` |
| `Ambulan` | `rumahSakit()`, `pengemudi()` belongsTo; `statusLabel()`. **Tidak ada relasi ke Pesanan** |

## 6. View per role

| View | Fungsi | Status |
|---|---|---|
| `welcome` | Landing page | Lengkap |
| `login`, `register` | Form login / registrasi warga | Lengkap |
| `warga/beranda` | Dashboard: pesanan aktif, 5 terbaru, total | Lengkap |
| `warga/pesan` | Form pesan: peta Leaflet, GPS, cari alamat, pilih RS, tingkat darurat, catatan | Lengkap |
| `warga/lacak` | Status bertahap, peta, posisi sopir (polling 5 dtk), rute | Lengkap |
| `warga/riwayat` | Daftar pesanan paginasi | Lengkap |
| `pengemudi/dashboard` | Toggle online, GPS kirim lokasi, popup pesanan baru (terima/tolak), update status, peta rute | Lengkap |
| `layouts/admin` | Layout + sidebar admin + badge pesanan menunggu | Lengkap |
| `admin/dashboard` | Statistik, grafik 7 hari, pesanan terbaru | Lengkap |
| `admin/pesanan` | Daftar + filter, modal tugaskan sopir, tombol tolak | Ada bug (bagian 10) |
| `admin/master/rumah-sakit`, `sopir`, `ambulan` | CRUD master | Lengkap; RS tanpa kontrol `aktif` |
| `admin/laporan`, `laporan-pdf` | Rekap bulanan + unduh PDF | Lengkap |
| `errors/404,419,500` | Halaman error kustom | Lengkap |

Tidak ada penanda TODO/FIXME/dummy/lorem di `resources/views`. File `public/qr.html` adalah halaman QR ke aplikasi (di luar Laravel view). `app/Http/Controllers/AuthController.php` berisi blok kode lama yang dikomentari di luar class.

## 7. Alur pesanan

1. **Warga** mengisi `/warga/pesan`: `lokasi_jemput` (wajib), `lat_jemput`/`lng_jemput` (opsional, dari GPS atau klik peta/cari alamat Nominatim), `rs_id` (wajib, RS tujuan), `tingkat_darurat` (biasa/gawat), `catatan`. Tidak ada pilihan jenis layanan atau jenis ambulans.
2. Disimpan dengan status `menunggu`. Sistem menghitung sopir online di RS tujuan hanya untuk teks flash.
3. **Penugasan, dua jalur paralel:**
   - **Sopir** online (RS sama dengan RS tujuan) mendapat tawaran lewat polling `/pengemudi/cek-pesanan` tiap 5 dtk (gawat didahulukan, lalu FIFO) → **Terima** (`menunggu→diterima`, `pengemudi_id` diisi) atau Tolak (hanya menutup popup; server tidak mencatat).
   - **Admin** lewat modal Tugaskan → `diterima` + `pengemudi_id`; atau Tolak → `dibatalkan`.
4. **Sopir** mengubah `diterima→dalam_perjalanan→selesai` (`updateStatus`, validasi `in:dalam_perjalanan,selesai`).
5. **Warga** dapat membatalkan hanya saat `menunggu` (`dibatalkan`).

| Status | Diubah oleh |
|---|---|
| menunggu | sistem (saat warga memesan) |
| diterima | sopir (terima) atau admin (tugaskan) |
| dalam_perjalanan | sopir |
| selesai | sopir |
| dibatalkan | warga (saat menunggu) atau admin (tolak) |

- **Ambulan:** tidak pernah ditugaskan ke pesanan; hanya sopir. `ambulan.status` tidak berubah otomatis.
- **Lokasi:** jemput = GPS browser (`getCurrentPosition`/`watchPosition`) atau klik peta Leaflet, reverse geocode Nominatim. Sopir mengirim `lat/lng` ke `users.lat_pengemudi/lng_pengemudi` lewat `watchPosition` → POST `/pengemudi/update-lokasi`; warga membaca via polling 5 dtk dan melihat marker + rute (Leaflet Routing Machine).
- **Notifikasi:** tidak ada notifikasi server-side. Sopir: polling + Web Notification + getar + popup. Admin: tidak ada notifikasi pesanan baru, hanya angka badge yang dihitung saat halaman dimuat (tanpa auto-refresh). Warga: polling status halaman lacak.

## 8. Checklist fitur

### Pengguna (warga)
| Fitur | Status | Bukti | Catatan |
|---|---|---|---|
| Registrasi | Ada | `AuthController@register`, `register.blade.php` | Validasi lengkap, auto-login |
| Login | Ada | `AuthController@login` | |
| Logout | Ada | `AuthController@logout` | Route POST, tanpa `auth` |
| Form pesan ambulans | Ada | `warga/pesan.blade.php`, `WargaController@simpanPesanan` | Tanpa jenis layanan |
| Deteksi lokasi otomatis | Ada | `pesan.blade.php` (`navigator.geolocation`) | |
| Pilih lokasi manual di peta | Ada | `pesan.blade.php` (Leaflet klik + cari alamat) | |
| Konfirmasi pesanan | Sebagian | `pesan.blade.php`, flash di `lacak` | Tidak ada halaman/langkah konfirmasi terpisah sebelum kirim |
| Lihat status pesanan | Ada | `warga/lacak.blade.php`, route `/status` | |
| Riwayat | Ada | `warga/riwayat.blade.php` | |
| Lihat posisi ambulans di peta | Ada | `lacak.blade.php`, `/lokasi-sopir` | Posisi sopir, bukan data ambulan |

### Admin
| Fitur | Status | Bukti | Catatan |
|---|---|---|---|
| Dashboard | Ada | `admin/dashboard.blade.php` | |
| Verifikasi pesanan | Sebagian | `admin/pesanan.blade.php` | Tidak ada status/langkah "terverifikasi"; tidak ada halaman detail (tombol Detail hanya link ke daftar) |
| Tolak / jadwal ulang | Sebagian | `AdminController@tolakPesanan` | Tolak ada (langsung `dibatalkan`, tanpa alasan). Jadwal ulang: belum ada |
| Tugaskan ambulan & sopir | Sebagian | `AdminController@tugaskanSopir` | Sopir saja; ambulan tidak; modal salah mendeteksi sopir sibuk (bagian 10) |
| Kelola data sopir | Ada | `admin/master/sopir.blade.php` | |
| Kelola data ambulan | Ada | `admin/master/ambulan.blade.php` | |
| Laporan/rekap + ekspor | Ada | `admin/laporan*.blade.php`, `laporanPdf` | Ekspor PDF; tanpa Excel |
| Notifikasi pesanan baru | Sebagian | `layouts/admin.blade.php` | Hanya badge saat load, tanpa polling/suara |

### Sopir
| Fitur | Status | Bukti | Catatan |
|---|---|---|---|
| Notifikasi tugas | Ada | `pengemudi/dashboard.blade.php` | Polling 5 dtk + Notification API |
| Terima/tolak tugas | Sebagian | `PengemudiController@terima/@tolak` | Tolak hanya UI; server tidak mencatat |
| Aktifkan GPS & kirim lokasi berkala | Ada | `dashboard.blade.php`, `@updateLokasi` | |
| Update status perjalanan | Ada | `@updateStatus` | |
| Selesaikan tugas | Ada | `@updateStatus` (`selesai`) | |

### Data Dinkes
| Fitur | Status | Catatan |
|---|---|---|
| Jenis layanan (darurat/rujukan/persalinan) | Belum ada | Hanya `tingkat_darurat` biasa/gawat |
| Jenis ambulans (darurat vs transport) | Sebagian | `ambulan.jenis` string bebas (default `transport`), tidak dipakai di alur pesanan |
| Master faskes (RS/Puskesmas/Klinik) + koordinat | Sebagian | `rumah_sakit` + `jenis` (puskesmas/klinik ada di form) + `lat/lng`; belum ada data awal |
| Role petugas RS/Puskesmas | Belum ada | Hanya 3 role |
| Dinkes meneruskan permintaan ke RS/Puskesmas | Belum ada | Pesanan langsung memilih RS oleh warga; sopir RS tujuan menerima otomatis |
| Ketersediaan ambulans per faskes | Sebagian | `ambulan.status` + dashboard (`ambulan_tersedia` total); tidak per faskes dan tidak terlihat warga |
| Info bed IGD / rawat inap | Belum ada | Tidak ada kolom/tabel |
| Ketersediaan dokter umum/spesialis | Belum ada | Tidak ada kolom/tabel |

## 9. Pemetaan layar laporan → aplikasi

| Layar Bab 3 | Route | View | Catatan |
|---|---|---|---|
| Landing Page Sistem | `/` | `welcome` | |
| Registrasi Pengguna | `/register` | `register` | |
| Login Pengguna | `/login` | `login` | Login dipakai bersama semua role |
| Dashboard Pesanan | `/warga/beranda` | `warga/beranda` | |
| Lokasi Peta | `/warga/pesan` | `warga/pesan` | Peta menyatu dengan form pesan |
| Konfirmasi Pesanan | — | — | Tidak ada layar tersendiri; paling dekat: `/warga/lacak/{id}` (flash sukses) |
| Status Pesanan | `/warga/lacak/{id}` | `warga/lacak` | |
| Riwayat Pesanan | `/warga/riwayat` | `warga/riwayat` | |
| Dashboard Admin | `/admin/dashboard` | `admin/dashboard` | |
| Detail Verifikasi | — | — | Tidak ada; verifikasi lewat tabel `/admin/pesanan` |
| Tugaskan Supir | `/admin/pesanan` (modal) | `admin/pesanan` | Berupa modal, bukan halaman |
| Kelola Data Admin | `/admin/master/{rumah-sakit,sopir,ambulan}` | `admin/master/*` | Tiga halaman |
| Laporan Data Admin | `/admin/laporan` (+ `/pdf`) | `admin/laporan`, `laporan-pdf` | |
| Notifikasi Supir | `/pengemudi/dashboard` | `pengemudi/dashboard` | Popup + Notification API dalam dashboard |

## 10. Masalah ditemukan (paling serius dulu)

**Bug / keamanan**
1. **Modal Tugaskan salah mendeteksi sopir sibuk.** `admin/pesanan.blade.php:190,208,223` memakai `$s->sedang_bertugas` yang tidak ada di model/DB/controller (bernilai null). Akibatnya sopir yang sedang bertugas tetap bisa dipilih dan peringatan "tidak ada sopir tersedia" tidak pernah muncul.
2. **`tugaskanSopir` tanpa validasi.** `AdminController.php:72-78`: `pengemudi_id` hanya `exists:users` (bisa id admin/warga), tidak cek role, online, atau sedang bertugas; tidak cek status pesanan masih `menunggu` (pesanan `selesai`/`dibatalkan`/sedang ditangani bisa ditimpa); tidak membuat ambulan `bertugas`. `tolakPesanan` (`:83-89`) juga tidak cek status, sehingga pesanan `selesai` bisa dibatalkan.
3. **`sopirUpdate`/`sopirDestroy` memakai `User::findOrFail($id)` tanpa filter role** (`:172`, `:190`). Admin bisa mengubah/menghapus akun admin atau warga lewat id; `sopirDestroy` bisa menghapus akunnya sendiri.
4. **`terima` rawan balapan.** `PengemudiController.php:~125` memakai `lockForUpdate()` di luar transaksi, sehingga kunci tidak efektif; dua sopir tetap bisa menerima pesanan yang sama.
5. **`updateStatus` tanpa pengecekan urutan.** Sopir bisa memanggil `selesai` langsung dari `diterima`, atau mengubah status pesanan yang sudah `selesai`/`dibatalkan` (filter hanya `pengemudi_id`).
6. **Tolak sopir tidak dicatat server** (`PengemudiController@tolak`): pesanan yang sama ditawarkan lagi ke sopir yang menolak pada polling berikutnya (popup muncul lagi).
7. **`rumahSakitUpdate` tidak memvalidasi `lat/lng`** dan UI edit tidak punya kontrol `aktif` (kolom `aktif` tidak bisa diubah dari antarmuka); `rumahSakitStore` tidak memvalidasi `jenis` terhadap daftar nilai.
8. **`ambulanUpdate`** tidak memvalidasi `rumah_sakit_id`/`pengemudi_id` (diterima lewat `only()`).
9. **Hapus RS = cascade ke ambulan** (`ambulan.rumah_sakit_id` cascade) dan `set null` ke pesanan/users; tidak ada peringatan data historis.
10. **Seeder berisi password hardcode** dan akun admin default; pastikan tidak dipakai di produksi.

**Inkonsistensi**
11. `RumahSakit::pesanan()` relasi lewat `rs_tujuan`↔`nama` (bukan `rs_id`); akan salah jika nama RS diubah. `pesanan.rs_tujuan` duplikat dengan `rs_id`; `Pesanan::namaRS()` masih menyimpan slug RS lama (`rsud_sele_be_solu` dst.).
12. Ambulan tidak terhubung ke pesanan; `ambulan.status` dan penugasan ambulan terpisah dari alur pesanan; `ambulan.pengemudi_id` tidak dipakai saat menugaskan.
13. Dua jalur penugasan (sopir ambil sendiri vs admin tugaskan) tanpa langkah verifikasi admin; sopir RS tujuan bisa menerima langsung tanpa Dinkes. Ini berbeda dengan alur resmi (Dinkes memverifikasi dan menugaskan).
14. Sopir tanpa `rumah_sakit_id` melihat **semua** pesanan menunggu (fallback di `cekPesananBaru`).
15. Status `ditolak`/`terverifikasi`/`ditugaskan` tidak ada di enum; tolak admin dan batal warga sama-sama `dibatalkan`, tanpa alasan atau pembeda.
16. `RoleMiddleware` dipanggil lewat class di route, tidak didaftarkan di `Kernel.php` (tetap jalan, tapi tidak konsisten dengan alias bawaan). `/logout` tanpa `auth`; `/login`, `/register` tanpa `guest`.
17. Layout admin: `$menunggu` dihitung dengan query di dalam view (`layouts/admin.blade.php:105`), bukan dari controller/composer.
18. Library CDN (`leaflet-routing-machine@latest`, Tailwind CDN): butuh internet, versi tidak terkunci. Rute Leaflet Routing Machine memakai server demo OSRM publik (tidak untuk produksi).
19. Kode mati: blok komentar `AuthController` di luar class; `Pesanan::sopirOnlineDiRS()` tidak dipanggil; Sanctum terpasang tapi tidak dipakai.
20. `.env.example`: `APP_NAME=Laravel`, `SESSION_DRIVER=file` meski migration `sessions` ada.

**Belum selesai (sesuai kebutuhan laporan/Dinkes)**
21. Tidak ada jenis layanan (darurat/rujukan/persalinan), role petugas RS/Puskesmas, penerusan Dinkes→RS/Puskesmas, info bed IGD/rawat inap, dan ketersediaan dokter.
22. Tidak ada notifikasi pesanan baru untuk admin yang bersifat real-time; tidak ada notifikasi ke warga selain polling halaman lacak.
23. Tidak ada tes otomatis aplikasi; tidak ada seed data RS/Puskesmas/ambulan.
24. Tidak ada halaman detail pesanan/verifikasi dan tidak ada layar konfirmasi pesanan sebagaimana mockup Bab 3.
