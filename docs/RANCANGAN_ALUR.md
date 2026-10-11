# RANCANGAN — SIGAP Ambulans v2 (Dinkes Kota Sorong)

> **Sumber kebenaran tunggal.** File ini dikunci (agen tidak boleh mengubahnya). Pasangannya: `DESIGN.md` (identitas visual dan token, format Google DESIGN.md), `docs/KIT_UI.md` (kit komponen, resep kelas, pola halaman), dan `agent/konsistensi.json` (aturan konsistensi yang diperiksa otomatis).
> Asumsi dan penyimpangan dicatat di `docs/laporan/ASUMSI_DAN_KEPUTUSAN.md`, bukan di sini.
> Ini adalah **proyek Laravel baru** (bukan refaktor). Aplikasi lama hanya referensi fitur di `legacy/` (baca saja) dan daftar bugnya di `docs/AUDIT_APLIKASI_LAMA.md`.
> Tidak ada data lama yang perlu dipertahankan: migrasi ditulis bersih, satu migrasi `create` per tabel.

---

## 1. Tujuan dan prinsip

Sistem Informasi Layanan Ambulans Terpadu berbasis web untuk penanganan darurat di Kota Sorong, mengikuti alur resmi Dinas Kesehatan:

```
Masyarakat ──pesanan──▶ Sistem ──data pesanan──▶ Dinkes (Admin / Pusat Koordinasi)
                           ▲                            │ verifikasi → penugasan ambulans + sopir
                           │ status, notifikasi         ▼
                           └──────────────── Faskes (RS / Puskesmas / Klinik) ──▶ layanan sampai
```

Prinsip yang tidak boleh dilanggar (penyebab ketidakkonsistenan pada versi sebelumnya):

1. **Satu istilah untuk satu konsep** (bagian 2). Dilarang mencampur sinonim di kode, database, route, maupun tampilan.
2. **Satu sumber kebenaran**: status, peran, jenis, label, warna lencana, menu navigasi, angka konfigurasi masing-masing didefinisikan di satu tempat (Enum / `config/sigap.php`) dan hanya dirujuk dari tempat lain.
3. **Satu pintu perubahan status**: `App\Services\PesananWorkflow`. Controller tidak boleh mengubah `status`, `sopir_id`, `ambulans_id` pesanan.
4. **Satu kit UI**: setiap halaman dibangun dari komponen Blade yang sama (lihat `DESIGN.md` dan `docs/KIT_UI.md`). Tidak ada gaya khusus per halaman.
5. **Dinkes adalah satu-satunya pintu penugasan.** Masyarakat tidak memilih sopir; sopir tidak mengambil pesanan sendiri.

## 2. Istilah baku

| Konsep | Kode (PHP / DB / route / berkas) | Tampilan (UI, bahasa Indonesia) |
|---|---|---|
| Masyarakat pemohon | peran `warga` (`Role::Warga`) | **Masyarakat** |
| Dinas Kesehatan (admin/pusat koordinasi) | peran `admin` (`Role::Admin`) | **Dinkes** |
| Petugas RS/Puskesmas | peran `petugas_faskes` (`Role::PetugasFaskes`) | **Petugas Faskes** |
| Sopir ambulans | peran `sopir` (`Role::Sopir`), kolom `sopir_id` | **Sopir** |
| Fasilitas kesehatan (RS, Puskesmas, Klinik) | tabel `faskes`, model `Faskes`, kolom `faskes_id`, `faskes_tujuan_id` | **Faskes** (lengkap: "Fasilitas Kesehatan") |
| Ambulans | tabel `ambulans`, model `Ambulans`, kolom `ambulans_id` | **Ambulans** |
| Permintaan layanan | tabel `pesanan`, model `Pesanan`, log `PesananLog` | **Pesanan** |
| Jenis layanan | `jenis_layanan`: `darurat`, `rujukan`, `persalinan` | Darurat, Rujukan, Persalinan |
| Tingkat darurat | `tingkat_darurat`: `biasa`, `gawat` | Biasa, Gawat |
| Jenis ambulans | `ambulans.jenis`: `darurat`, `transport` | Darurat, Transport |
| Jenis faskes | `faskes.jenis`: `rumah_sakit`, `puskesmas`, `klinik` | Rumah Sakit, Puskesmas, Klinik |

Larangan istilah (diperiksa otomatis): `rs_id`, `rs_tujuan`, `rumah_sakit_id`, `pengemudi`, `supir`, `ambulan` (tanpa "s"), `ambulance`, `RumahSakit`. Label tampilan selalu diambil dari Enum (`->label()`), bukan diketik ulang.
Aturan penamaan lain: tabel/kolom/route/URI berbahasa Indonesia, huruf kecil, `snake_case` (kolom/route) dan `kebab-case` (URI); kolom waktu berakhiran `_pada`; kolom pelaku berakhiran `_oleh_id`; seluruh model menulis nama tabel eksplisit (`#[Table(name: '...')]`).

## 3. Aktor dan hak akses

| Aktor | Tugas |
|---|---|
| Masyarakat (`warga`) | Mendaftar, membuat pesanan (darurat/rujukan/persalinan), konfirmasi, memantau status & lokasi sopir, membatalkan sebelum penugasan, melihat riwayat, melihat info Faskes (publik) |
| Dinkes (`admin`) | Menerima & memverifikasi pesanan, menolak (alasan wajib), menugaskan ambulans + sopir dari Faskes, melepas/mengganti penugasan, membatalkan (alasan wajib), mengelola master Faskes/Ambulans/Sopir/Petugas, memantau ketersediaan, laporan & rekap (PDF/CSV) |
| Petugas Faskes (`petugas_faskes`) | Melihat pesanan yang ditugaskan ke Faskes-nya (detail lokasi & tujuan), memperbarui ketersediaan (bed IGD, bed rawat inap, dokter umum, dokter spesialis), status ambulans Faskes-nya (`tersedia` ↔ `servis`) |
| Sopir (`sopir`) | Online/offline, kirim lokasi GPS, menerima/menolak penugasan dari Dinkes, mulai perjalanan, selesai |
| Publik (tanpa login) | Landing, halaman `/faskes` (ketersediaan), login, register |

Isolasi data: Masyarakat hanya pesanan miliknya; Petugas hanya data Faskes-nya; Sopir hanya pesanan yang ditugaskan kepadanya; Admin semua. Diterapkan lewat middleware `role` **dan** `PesananPolicy` / pengecekan kepemilikan.

## 4. Data

Fresh migration (boleh `php artisan migrate:fresh` pada database v2). Satu berkas migrasi `create` per tabel; migrasi bawaan `users` dimodifikasi langsung (bukan ditambal). PHP backed enum untuk semua kolom bernilai terbatas; kolom DB bertipe `string` (bukan `enum` DB) dan di-cast di model. **Gaya model (Laravel 13)**: persis seperti `app/Models/User.php` bawaan kerangka: atribut Eloquent `#[Table(name: '...')]`, `#[Fillable([...])]`, `#[Hidden([...])]` dan method `protected function casts(): array`; dilarang properti `$table`, `$fillable`, `$hidden`, `$casts` (dipindai). Kerangka sudah punya migrasi `users` (+ `password_reset_tokens`, `sessions`), `cache`, `jobs`; migrasi `users` diubah langsung, dua lainnya dibiarkan.

**users**: `id`, `name`, `email` (unique), `email_verified_at` (null), `password`, `role` string(20) default `warga` (index), `aktif` bool default true (akun nonaktif tidak bisa login), `faskes_id` (nullable FK→faskes, null on delete), `no_telepon` string(20) null, `sedang_online` bool default false, `lat` / `lng` decimal(10,7) null (posisi sopir), `lokasi_diperbarui_pada` null, `remember_token`, timestamps.

**faskes**: `id`, `nama` (unique), `jenis` string(20), `kepemilikan` string(20) null (`pemerintah|swasta|militer|bumn`), `alamat` null, `telepon` null, `lat` / `lng` decimal(10,7) null, `aktif` bool default true, `bed_igd_total`, `bed_igd_tersedia`, `bed_rawat_inap_total`, `bed_rawat_inap_tersedia`, `dokter_umum_tersedia`, `dokter_spesialis_tersedia` (unsigned int default 0), `dokter_spesialis_keterangan` text null, `ketersediaan_diperbarui_pada` null, timestamps. Validasi: `*_tersedia ≤ *_total`; lat −90..90; lng −180..180.

**ambulans**: `id`, `faskes_id` (FK, restrict on delete), `sopir_id` (nullable FK→users, sopir tetap), `plat_nomor` (unique), `jenis` string(20), `merek_model` null, `status` string(20) default `tersedia` (`tersedia|bertugas|servis`), timestamps.

**pesanan**: `id`, `kode` string(20) unique (format `SGP-` + `id` 6 digit, dibuat dalam transaksi `buat`), `user_id` (pemohon, FK), `nama_pasien`, `no_telepon_kontak`, `jenis_layanan`, `tingkat_darurat`, `lokasi_jemput` string, `lat_jemput` / `lng_jemput` decimal(10,7) null, `faskes_tujuan_id` (nullable FK), `catatan` text null, `status` string(30) default `menunggu` (index), `ambulans_id` (nullable FK), `sopir_id` (nullable FK→users), `diverifikasi_oleh_id` (nullable FK→users), `diverifikasi_pada`, `ditugaskan_pada`, `diterima_pada`, `berangkat_pada`, `selesai_pada` (semua timestamp null), `alasan_penolakan` text null, `alasan_pembatalan` text null, timestamps.

**pesanan_log**: `id`, `pesanan_id` (FK cascade), `dari_status` null, `ke_status`, `oleh_id` (nullable FK→users, null on delete), `peran` string(20) null, `catatan` text null, `created_at`. Satu baris untuk **setiap** transisi; dipakai untuk linimasa.

**Enum** (`app/Enums`): `Role`, `PesananStatus`, `JenisLayanan`, `TingkatDarurat`, `JenisFaskes`, `JenisAmbulans`, `StatusAmbulans`, `Kepemilikan`. Semua punya `label()` dan statik `opsi()` (array nilai→label untuk `<x-ui.pilihan>`); `PesananStatus` juga `nada()` (nada lencana, lima saja: `netral|info|peringatan|sukses|bahaya`; pemetaan tetap: `menunggu`=peringatan; `terverifikasi`, `ditugaskan`, `diterima`, `dalam_perjalanan`=info; `selesai`=sukses; `ditolak`=bahaya; `dibatalkan`=netral), `final()`, `memegangSumberDaya()`, `transisiTerbuka()`; `Role` punya `ruteBeranda()` (nama route dashboard-nya, mis. `admin.dashboard`).

## 5. Mesin status pesanan

Status & label: `menunggu` "Menunggu Verifikasi", `terverifikasi` "Terverifikasi", `ditugaskan` "Ditugaskan", `diterima` "Diterima Sopir", `dalam_perjalanan` "Dalam Perjalanan", `selesai` "Selesai", `ditolak` "Ditolak", `dibatalkan` "Dibatalkan".
Final: `selesai`, `ditolak`, `dibatalkan`. Memegang sumber daya (ambulans+sopir): `ditugaskan`, `diterima`, `dalam_perjalanan`. Tidak ada penjadwalan ulang.

| # | Dari | Ke | Aktor | Syarat | Efek |
|---|---|---|---|---|---|
| 1 | — | `menunggu` | warga | form valid | buat `kode`; log |
| 2 | `menunggu` | `terverifikasi` | admin | — | `diverifikasi_oleh_id/pada` |
| 3 | `menunggu` | `ditolak` | admin | alasan wajib | `alasan_penolakan` |
| 4 | `menunggu`, `terverifikasi` | `dibatalkan` | warga pemilik | — | alasan opsional |
| 5 | `terverifikasi` | `ditugaskan` | admin | ambulans `tersedia` & faskes aktif; sopir ber-peran `sopir`, `sedang_online`, `faskes_id` **sama** dengan ambulans, tanpa pesanan aktif | isi `ambulans_id`, `sopir_id`, `ditugaskan_pada`; ambulans → `bertugas` |
| 6 | `ditugaskan` | `diterima` | sopir yang ditugaskan | — | `diterima_pada` |
| 7 | `ditugaskan` | `terverifikasi` | sopir (tolak, alasan wajib) atau admin (lepas) | — | ambulans → `tersedia`; kosongkan `ambulans_id`/`sopir_id`; alasan di log |
| 8 | `diterima` | `dalam_perjalanan` | sopir | — | `berangkat_pada` |
| 9 | `dalam_perjalanan` | `selesai` | sopir | — | `selesai_pada`; ambulans → `tersedia` |
| 10 | `terverifikasi`, `ditugaskan`, `diterima`, `dalam_perjalanan` | `dibatalkan` | admin | alasan wajib | ambulans → `tersedia` bila ada |

Transisi di luar tabel melempar `TransisiTidakValidException` tanpa mengubah data.

**`App\Services\PesananWorkflow`**: method `buat`, `verifikasi`, `tolak`, `batalkanOlehWarga`, `tugaskan`, `terima`, `tolakPenugasan`, `lepaskanPenugasan`, `mulaiPerjalanan`, `selesaikan`, `batalkanOlehAdmin`. Tiap method: `DB::transaction` → muat ulang `Pesanan` dengan `lockForUpdate()` (juga ambulans & sopir terkait) → validasi pelaku, kepemilikan, transisi, syarat → ubah data → tulis `PesananLog` → ubah `ambulans.status`. Exception domain: `TransisiTidakValidException`, `PenugasanTidakValidException`. Controller menangkapnya: web → flash `galat`; JSON → 422. Satu sopir ≤ 1 pesanan aktif; satu ambulans ≤ 1 pesanan aktif.

## 6. Route (tetap; nama = yang tertulis)

Semua route bernama. Middleware: publik tanpa; `guest` untuk `login`/`register`; `auth` + `role:<peran>` untuk grup peran. Route-model binding: `{pesanan}`; `{faskes}`; `{ambulans}`; `{sopir}` dan `{petugas}` (keduanya model `User`, di-bind eksplisit dengan filter peran yang benar sehingga akun peran lain menghasilkan 404). Rute JSON didefinisikan **sebelum** rute berparameter.

**Publik**: `beranda` GET `/` · `faskes.index` GET `/faskes` · `faskes.data` GET `/faskes/data` (JSON) · `login` GET `/login` · `login.proses` POST `/login` · `register` GET `/register` · `register.proses` POST `/register` · `logout` POST `/logout` (`auth`).

**Warga** (`/warga`, nama `warga.*`): `beranda` GET `/warga` · `pesanan.index` GET `/warga/pesanan` (riwayat) · `pesanan.buat` GET `/warga/pesanan/buat` · `pesanan.tinjau` POST `/warga/pesanan/tinjau` (validasi → simpan draf di session → redirect) · `pesanan.konfirmasi` GET `/warga/pesanan/konfirmasi` · `pesanan.simpan` POST `/warga/pesanan` · `pesanan.show` GET `/warga/pesanan/{pesanan}` (lacak) · `pesanan.status` GET `/warga/pesanan/{pesanan}/status` (JSON) · `pesanan.batalkan` PUT `/warga/pesanan/{pesanan}/batal`.

**Admin** (`/admin`, `admin.*`): `dashboard` GET `/admin` · `pesanan.index` GET `/admin/pesanan` · `pesanan.ringkasan` GET `/admin/pesanan/ringkasan` (JSON polling) · `pesanan.show` GET `/admin/pesanan/{pesanan}` · `pesanan.verifikasi` / `pesanan.tolak` / `pesanan.tugaskan` / `pesanan.lepas` / `pesanan.batalkan` PUT `/admin/pesanan/{pesanan}/<aksi>` · `faskes.*`, `ambulans.*`, `sopir.*`, `petugas.*` (resource: index, create, store, edit, update; **tanpa destroy**) · `faskes.aktif`, `sopir.aktif`, `petugas.aktif` PUT `/admin/<sumber>/{x}/aktif` (membalik kolom `aktif`; satu-satunya cara "menghapus"; ambulans tidak punya kolom `aktif`, statusnya `tersedia`/`servis` disunting lewat form edit, `bertugas` hanya oleh workflow) · `ketersediaan.index` GET `/admin/ketersediaan` · `ketersediaan.update` PUT `/admin/ketersediaan/{faskes}` · `laporan.index` GET `/admin/laporan` · `laporan.pdf` GET `/admin/laporan/pdf` · `laporan.csv` GET `/admin/laporan/csv`.

**Petugas** (`/petugas`, `petugas.*`): `dashboard` GET `/petugas` · `pesanan.show` GET `/petugas/pesanan/{pesanan}` · `ketersediaan.edit` GET `/petugas/ketersediaan` · `ketersediaan.update` PUT `/petugas/ketersediaan` · `ambulans.status` PUT `/petugas/ambulans/{ambulans}/status`.

**Sopir** (`/sopir`, `sopir.*`): `dashboard` GET `/sopir` · `online` POST `/sopir/online` · `lokasi` POST `/sopir/lokasi` · `penugasan` GET `/sopir/penugasan` (JSON polling) · `pesanan.terima` / `pesanan.tolak` / `pesanan.berangkat` / `pesanan.selesai` POST `/sopir/pesanan/{pesanan}/<aksi>`.

**Konvensi JSON** (semua endpoint JSON): sukses `{ "ok": true, "data": {...} }`; gagal `{ "ok": false, "pesan": "..." }` dengan kode HTTP yang benar (401/403/404/422). Satu trait/helper dipakai semua controller JSON.

## 7. Halaman utama (isi ringkas; rincian visual di `DESIGN.md` dan `docs/KIT_UI.md`)

- **Landing**: faktual dan singkat (bukan halaman pemasaran): H1, satu paragraf, tombol "Pesan ambulans" dan "Info Faskes", serta daftar bernomor empat langkah alur. Rincian di `docs/KIT_UI.md` §5.
- **Warga › Buat Pesanan** (mobile-first, satu layar, dibaca orang yang sedang panik): pilih jenis layanan (3 baris pilihan besar, `ui.opsi-baris`), tingkat darurat, tombol besar "Gunakan lokasi saya" + peta + cari alamat + kolom alamat teks, Faskes tujuan (opsional kecuali rujukan; tampilkan ringkasan ketersediaan Faskes terpilih), nama pasien, nomor kontak (prefill profil), catatan. → **Tinjau/Konfirmasi** (ringkasan + tombol "Ubah" dan "Kirim Pesanan") → **Lacak** (linimasa 8 status dari `PesananLog`, info ambulans & sopir mulai `ditugaskan`, peta + posisi sopir mulai `diterima`, polling).
- **Admin › Dashboard**: ringkasan angka "Menunggu verifikasi" (hidup, polling), ringkasan ketersediaan (ambulans tersedia, bed IGD & rawat inap tersedia, dokter), pesanan 7 hari (grafik batang CSS), pesanan terbaru.
- **Admin › Pesanan**: daftar (filter status/jenis layanan/tanggal/kata kunci; urut gawat dulu lalu terlama) → **Detail** (pemohon, lokasi + peta, Faskes tujuan, linimasa, aksi sesuai status). Menugaskan = pilih ambulans tersedia (kelompok per Faskes, disarankan dulu: `darurat` → ambulans `darurat`; `rujukan`/`persalinan` → `transport`; `persalinan` → Faskes `puskesmas` dulu — hanya urutan, bukan larangan) lalu sopir online dari Faskes yang sama.
- **Admin › Master**: Faskes (CRUD + pemilih koordinat peta + `aktif` + ketersediaan), Ambulans, Sopir, Petugas Faskes; **Ketersediaan** (tabel semua Faskes, bisa disunting); **Laporan** (periode, per status, per jenis layanan, per Faskes, rata-rata waktu respons menunggu→ditugaskan, durasi layanan; PDF (`barryvdh/laravel-dompdf` sudah terpasang oleh runner) dan CSV).
- **Petugas › Dashboard**: identitas Faskes, penugasan masuk/aktif, ambulans & sopir Faskes, form ketersediaan.
- **Sopir › Dashboard**: toggle online, kirim GPS, blok penugasan (Terima / Tolak+alasan → Mulai Perjalanan → Selesai), peta rute; popup + getar + Notification API saat penugasan baru.
- **Publik › `/faskes`**: tabel Faskes dengan filter jenis, ketersediaan bed IGD/rawat inap, dokter umum/spesialis, ambulans tersedia, "diperbarui …", penanda "Belum diperbarui" (null) atau "Data lama" (> `config('sigap.data_basi_jam')` jam), peta bila koordinat ada.

Polling (dengan helper JS bersama): admin ringkasan tiap `config('sigap.polling.admin_detik')` detik; sopir penugasan tiap `...sopir_detik`; warga status tiap `...warga_detik`.

## 8. Data awal (seeder idempoten: `firstOrCreate`/`updateOrCreate`, tanpa truncate)

Sumber: hasil wawancara Dinkes. Koordinat dikosongkan (Dinkes mengisi lewat UI — fitur "titik koordinat GPS").

**Faskes**: Rumah Sakit (7): RSUD Selebesolu, RS AL, RS Aryoko, RS Maleo, RS Pertamina, RS Mubara, RS Herlina. Klinik (1): Klinik Posmor 3 Km 16 *(ejaan perlu konfirmasi)*. Puskesmas (11): Tanjung Kasuari, Sorong Barat, Dum, Malanu, Malawei, Remu, Klasaman, Sorong (samping Wilkot) *(nama resmi perlu konfirmasi)*, Sorong Kota, Malaimsimsa, Sorong Timur (Moyo). Ketersediaan awal 0 dan `ketersediaan_diperbarui_pada` null.
**Ambulans**: darurat (4): RSUD Selebesolu, RS AL, RS Aryoko, Klinik Posmor 3 Km 16 — 1 unit `darurat` tiap Faskes. Transport (18): 7 RS + 11 Puskesmas — 1 unit `transport` tiap Faskes. Plat **dummy** `PB-DEMO-01`, `PB-DEMO-02`, … (wajib diganti data asli oleh Dinkes; dicatat di dokumen).
**Akun demo** (`@sigap.test`; sandi = `env('SEED_PASSWORD')`, bila kosong dan environment `local`/`testing` → `password`, selain itu acak dan dicetak sekali): `admin@`, `warga@`, `petugas.selebesolu@`, `petugas.klasaman@`, serta satu sopir (`sopir.<faskes>@`) untuk tiap Faskes ber-ambulans darurat + Puskesmas Klasaman + Puskesmas Remu, terhubung ke ambulansnya.
**Pesanan demo** (hanya environment `local`): ±10 pesanan lewat `PesananWorkflow` (bukan insert langsung) yang mencakup semua status dan tersebar 7 hari terakhir (pakai `Carbon::setTestNow`), supaya dashboard & laporan langsung terisi untuk dilihat.

## 9. Aturan kualitas

1. **Laravel 13 / PHP 8.3** (kerangka `laravel/laravel:^13.0`, PHPUnit 12). Ikuti idiom kerangka: middleware dan alias `role` di `bootstrap/app.php` (tidak ada `Kernel.php`), atribut Eloquent dan `casts()`, `PreventRequestForgery` (CSRF) dibiarkan aktif. Tanpa paket Composer baru (hanya kerangka + `barryvdh/laravel-dompdf`, dipasang runner). **Frontend**: Tailwind CSS 4.3 + Vite; paket npm dipasang runner (`tailwindcss`, `@tailwindcss/vite`, `vite`, `laravel-vite-plugin`, `@fontsource-variable/roboto`, `lucide-static`, `leaflet@1.9.4`, `leaflet-routing-machine@3.2.12`, `yaml`, `@google/design.md`); agen dilarang `npm install`/`npx`, hanya boleh `npm run build`. Tanpa CDN. Gaya visual: `DESIGN.md` (token) dan `docs/KIT_UI.md`.
2. Bahasa antarmuka, pesan validasi, flash: **Indonesia**. `config/app.php`: default `locale` dan `fallback_locale` = `id`, `faker_locale` = `id_ID`, `timezone` = `Asia/Jayapura` (runner juga mengisi `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_FAKER_LOCALE` di `.env`). Berkas `lang/id/*` lengkap (validation, auth, pagination, passwords + atribut).
3. Validasi form non-trivial memakai `FormRequest`; otorisasi memakai Policy + middleware `role`. Semua mutasi POST/PUT + CSRF (middleware bawaan `PreventRequestForgery`; token `@csrf` di tiap form).
4. Pesan flash hanya dua kunci session: `sukses` dan `galat`, ditampilkan lewat satu komponen. Tanggal/waktu ditampilkan lewat komponen `ui.waktu` (zona Asia/Jayapura, "d M Y H:i").
5. Controller per peran di namespace `App\Http\Controllers\{Publik,Auth,Warga,Admin,Petugas,Sopir}`; view di folder dengan nama yang sama di bawah `resources/views`. Fragmen view (di-`@include`) diawali garis bawah `_`.
6. Tes (PHPUnit, SQLite `:memory:`): `tests/Unit/{Enums,Models,Services}`, `tests/Feature/{Publik,Auth,Warga,Admin,Petugas,Sopir,Arsitektur}`. Nama method tes deskriptif berbahasa Indonesia. `PesananWorkflow` diuji penuh (semua transisi valid & tidak valid, sumber daya sudah dipakai, beda Faskes, ambulans servis, pesanan final, log, status ambulans).
7. `agent/konsistensi.json` adalah pagar yang dijalankan runner setelah tiap sesi; `tests/Feature/Arsitektur/KonsistensiTest.php` menjalankan aturan yang sama dari PHP. Pelanggaran = gate GAGAL.
8. Tanpa `dd()`, `dump()`, `TODO`. Tidak ada rahasia di kode. Seeder tidak menyimpan sandi nyata.
9. Kemampuan agen: **tidak ada browser**; tampilan hanya terverifikasi lewat tes render, `npm run build`, `designmd lint`, dan pemindai konsistensi. JavaScript, peta, GPS, polling, notifikasi browser tidak teruji otomatis; sisi server teruji lewat tes HTTP. Jelaskan di `HASIL_AKHIR.md`.

## 10. Definition of Done

- `php artisan test` hijau; `php artisan migrate:fresh --seed` sukses pada DB v2; `php artisan route:list` bersih.
- Gate konsistensi bersih (0 pelanggaran); `npm run build` sukses; `DESIGN.md` lolos `designmd lint` tanpa galat.
- Alur lengkap teruji otomatis lintas peran: Masyarakat memesan → konfirmasi → Dinkes verifikasi → Dinkes tugaskan (ambulans + sopir) → Sopir terima → berangkat → selesai; plus jalur: tolak, batal oleh warga, batal oleh admin, sopir menolak (kembali `terverifikasi`) lalu ditugaskan ke sopir lain, Petugas memperbarui ketersediaan yang tampil di `/faskes`.
- Matriks otorisasi: setiap route × setiap peran memberi hasil yang benar (lolos / 403 / redirect login).
- Semua bug pada `docs/AUDIT_APLIKASI_LAMA.md` (nomor #1–#24) tercatat di `docs/laporan/STATUS_TEMUAN_AUDIT.md` sebagai: tidak mungkin terjadi lagi (jelaskan mengapa) / ditangani / tidak relevan.
- Dokumen untuk revisi laporan Bab 3 tersedia di `docs/laporan/`, dan `docs/HASIL_AKHIR.md` berisi cara menjalankan, akun demo, checklist uji manual 10 menit, dan daftar yang tidak dapat diverifikasi agen.
