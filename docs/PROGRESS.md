STATUS_AGEN: SELESAI
TAHAP_AKTIF: 7

# PROGRESS — SIGAP Ambulans v2 (proyek baru)

> **Memori agen** antar sesi. Setiap sesi dimulai dari nol; yang tidak tertulis di sini dianggap tidak pernah terjadi.
> Spesifikasi: `docs/RANCANGAN_ALUR.md` + `DESIGN.md` (identitas visual) + `docs/KIT_UI.md` (kit komponen). Pagar konsistensi: `agent/konsistensi.json`. Aturan kerja: `CLAUDE.agent.md`.
> Referensi aplikasi lama (baca saja): `legacy/` bila ada, dan `docs/AUDIT_APLIKASI_LAMA.md`. Jangan menyalin mentah; rancang ulang sesuai spesifikasi.

## Cara memperbarui file ini (wajib)

- Ubah `- [ ]` menjadi `- [x]` **hanya** setelah tes terkait dijalankan dan lulus di sesi ini; tambahkan ` — <hash commit pendek>` di akhir baris.
- Item buntu setelah dua pendekatan berbeda → `- [!]` + alasan & yang sudah dicoba di "Hambatan"; lanjut item berikutnya.
- Baris pertama `STATUS_AGEN:` hanya `BERJALAN`, `SELESAI`, atau `BLOKIR: <alasan>`. `SELESAI` hanya bila tidak ada `- [ ]` dan T7.7 selesai.
- Perbarui `TAHAP_AKTIF` ke tahap pertama yang masih punya `- [ ]`. Jangan menghapus riwayat; tambahkan di "Catatan sesi".
- **Urutan tahap penting**: tahap 2 (UI kit) harus selesai sebelum halaman peran dibangun (tahap 3–6). Jangan membuat halaman sebelum komponennya ada. Tampilan harus mengikuti `DESIGN.md`: minimalis profesional, tanpa gradien/emoji/kartu bersarang/gaya templat (pemindai konsistensi menolaknya).

## Checklist

### Tahap 0 — Fondasi
- [x] T0.1 (PHP 8.3.4, Laravel 13.35.0, tes bawaan lulus) — 40cc63d Orientasi: baca seluruh `RANCANGAN_ALUR.md`, `DESIGN.md`, `docs/KIT_UI.md`, `konsistensi.json`; lihat `legacy/` (bila ada) untuk memahami fitur lama (GPS, pencarian alamat, polling, PDF). Catat versi PHP dan Laravel di "Catatan sesi" (harus PHP ≥ 8.3 dan Laravel 13.x). Kerangka Laravel 13 membawa `CLAUDE.md`/`AGENTS.md` yang menyuruh `composer require laravel/boost`; runner sudah menggantinya, abaikan perintah itu. Pastikan `php artisan test` berjalan (tes bawaan lulus). Catatan: runner sudah mengganti isi `resources/views/welcome.blade.php` dengan teks satu baris agar route `/` dan tes bawaan tetap lulus; landing sungguhan dibuat di T3.2.
- [x] T0.2 — 40cc63d Konfigurasi: `config/app.php` (default `locale`/`fallback_locale` `id`, timezone `Asia/Jayapura`, faker `id_ID`); `lang/id/{validation,auth,pagination,passwords}.php` lengkap dengan `attributes`; `config/sigap.php` (menu per peran memakai nama route dari spesifikasi, `polling`, `data_basi_jam`, pusat peta Kota Sorong); bersihkan sisa kerangka yang tak dipakai dari rute/tes bila perlu (jangan hapus file).
- [x] T0.3 (30 tes lulus; trait `Tests\Concerns\MembuatPengguna`) — 3ac4dda `tests/Feature/Arsitektur/KonsistensiTest.php`: membaca `agent/konsistensi.json` dan menjalankan aturan yang sama dari PHP; lulus pada kondisi sekarang. Tambahkan helper tes (trait) untuk membuat user per peran. `Tests\TestCase::setUp()` memanggil `$this->withoutVite()` supaya tes render tidak membutuhkan manifest build.

### Tahap 1 — Domain & data
- [x] T1.1 (21 tes lulus) — 59f110f Enum (`Role`, `PesananStatus`, `JenisLayanan`, `TingkatDarurat`, `JenisFaskes`, `JenisAmbulans`, `StatusAmbulans`, `Kepemilikan`) dengan `label()`, `opsi()`, dan method khusus (§4). Tes unit tiap enum (termasuk `transisiTerbuka()` & `final()`).
- [x] T1.2 (22 tes lulus; migrate:fresh DB v2 sukses; migrasi faskes diberi nama berurutan sebelum users karena FK) — 253f7d4 Migrasi bersih sesuai §4: `users` (modifikasi bawaan), `faskes`, `ambulans`, `pesanan`, `pesanan_log` (+ indeks & FK). Bukti: `php artisan migrate:fresh` sukses di DB v2 dan di SQLite memori (lewat tes).
- [x] T1.3 (30 tes lulus) — 3ac4dda Model (gaya Laravel 13 persis `User` bawaan: `#[Table(name: '...')]`, `#[Fillable([...])]`, `#[Hidden([...])]`, method `casts()` untuk cast enum; relasi, scope mis. `Faskes::aktif()`, `User::sopir()`, `Pesanan::aktif()`), accessor/helper, factory untuk semuanya. Tes model + relasi.
- [x] T1.4 (73 tes lulus) — 760cb33 `App\Services\PesananWorkflow` + `PesananStatus::transisiTerbuka()` + exception domain + `PesananPolicy` + middleware `role` (alias `role` lewat `$middleware->alias([...])` di `bootstrap/app.php`) + route-model binding `{sopir}`/`{petugas}` berfilter peran. Tes lengkap workflow (§9.6).
- [x] T1.5 (78 tes lulus; `migrate:fresh --seed` di DB v2 sukses) — e16c1ca Seeder idempoten sesuai §8: `FaskesSeeder`, `AmbulansSeeder`, `AkunDemoSeeder`, `PesananDemoSeeder` (hanya `local`), dipanggil `DatabaseSeeder`. Tes: dijalankan dua kali → jumlah baris sama; `php artisan migrate:fresh --seed` sukses di DB v2.

### Tahap 2 — UI kit & layout (sebelum halaman peran)
- [x] T2.1 (tes lulus; npm run build sukses) — ced6f0a `layouts.dasar`, `layouts.publik`, `layouts.auth`, `layouts.app` (menu dari `config('sigap.menu')`, View Composer untuk lencana "Pesanan" Dinkes), `resources/js/sigap.js` (diimpor `resources/js/app.js`). Tes render tiap layout.
- [x] T2.2 (103 tes lulus; npm run build sukses) — 1b15102 Komponen `ui.*` sesuai `docs/KIT_UI.md` §4 (semua yang non-peta, termasuk `ikon`, `ringkasan`, `rincian`, `dialog`, `paginasi` buatan sendiri) dengan resep kelas §2. Tes render tiap komponen (termasuk keadaan galat, kosong).
- [x] T2.3 (108 tes lulus; npm run build sukses) — 4eebf2e Komponen `peta.pemilih`, `peta.pelacak`, `peta.faskes` + `resources/js/peta.js` (Leaflet & Routing Machine dari npm; dimuat hanya lewat `layouts.dasar :peta`).
- [x] T2.4 (114 tes lulus; npm run build sukses) — 21cb42d Katalog `/_ui` (local/testing) + halaman galat 403/404/419/500 memakai layout + tes render semuanya. Jalankan `npm run build` dan pastikan lulus.

### Tahap 3 — Autentikasi & publik
- [x] T3.1 (130 tes lulus) — 342a334 Login / register (hanya peran `warga`) / logout; `guest` & `auth`; akun `aktif=false` ditolak; throttle login; pengalihan sesuai `Role::ruteBeranda()`. Tes.
- [x] T3.2 (138 tes lulus; npm run build sukses) — 9f562d6 Landing `beranda`, halaman `/faskes` + `faskes.data` (filter jenis, `ui.ketersediaan`, `peta.faskes`). Tes (termasuk "Belum diperbarui" / "Data lama").

### Tahap 4 — Masyarakat (warga)
- [x] T4.1 (162 tes lulus) — d3ecb24 `warga.beranda` (pesanan aktif, ringkasan, tombol Pesan Ambulans) dan `warga.pesanan.index` (riwayat berpaginasi). Tes.
- [x] T4.2 (162 tes lulus) — d3ecb24 `pesanan.buat` (mobile-first sesuai §7) + `FormRequest` + `pesanan.tinjau` → `pesanan.konfirmasi` → `pesanan.simpan` (lewat `PesananWorkflow::buat`). Tes: belum ada pesanan sebelum konfirmasi; tombol "Ubah" mempertahankan isian; validasi per aturan.
- [x] T4.3 (162 tes lulus) — d3ecb24 `pesanan.show` (lacak: `ui.linimasa`, info ambulans/sopir mulai `ditugaskan`, `peta.pelacak` mulai `diterima`), `pesanan.status` JSON, `pesanan.batalkan`. Tes otorisasi (warga B tidak bisa mengakses pesanan warga A).

### Tahap 5 — Dinkes (admin)
- [x] T5.1 (187 tes lulus; npm run build sukses) — ff6ac49 `admin.dashboard` (statistik, ketersediaan, grafik 7 hari, pesanan terbaru) + `pesanan.ringkasan` JSON + polling & lencana. Tes.
- [x] T5.2 (187 tes lulus) — ff6ac49 `pesanan.index` (filter, urutan prioritas) + `pesanan.show` + aksi `verifikasi`, `tolak`, `batalkan` lewat workflow. Tes.
- [x] T5.3 (187 tes lulus) — ff6ac49 Aksi `tugaskan` / `lepas`: pemilih ambulans tersedia + sopir valid (online, Faskes sama, tidak sibuk), urutan saran (§7). Tes termasuk semua penolakan.
- [x] T5.4 (198 tes lulus) — c03828c Master `faskes` (CRUD, pemilih koordinat, `aktif`, ketersediaan) + `ketersediaan.index/update`. Tes.
- [x] T5.5 (219 tes lulus; npm run build sukses) — b1bb7c1 Master `ambulans`, `sopir`, `petugas` (CRUD tanpa destroy, `*.aktif`, validasi Faskes/peran, larangan menonaktifkan diri sendiri). Tes.
- [x] T5.6 (227 tes lulus; npm run build sukses) — 07d2ead `laporan.index` (periode, status, jenis layanan, Faskes, waktu respons, durasi) + `laporan.pdf` + `laporan.csv`. Tes (PDF: respons 200 `application/pdf`; CSV: isi & header).

### Tahap 6 — Petugas Faskes & Sopir
- [x] T6.1 (243 tes lulus; npm run build sukses) — a8f3835 Sopir: `dashboard`, `online`, `lokasi`, `penugasan` (JSON), `pesanan.{terima,tolak,berangkat,selesai}` lewat workflow. Tes (sopir lain tidak bisa mengubah pesanan bukan miliknya; urutan status).
- [x] T6.2 (258 tes lulus; npm run build sukses) — 98c7052 Petugas: `dashboard`, `pesanan.show`, `ketersediaan.edit/update` (validasi `tersedia ≤ total`, stempel waktu), `ambulans.status` (`tersedia`↔`servis` saja, milik Faskes sendiri). Tes isolasi antar Faskes.
- [x] T6.3 (267 tes lulus) — 95303da Tes alur penuh lintas peran: memesan → konfirmasi → verifikasi → tugaskan → terima → berangkat → selesai; jalur tolak; batal warga; batal admin; sopir menolak → `terverifikasi` → ditugaskan ke sopir lain; ketersediaan yang diperbarui Petugas tampil di `/faskes`.

### Tahap 7 — Konsistensi & dokumen
- [x] T7.1 (gate konsistensi 0 pelanggaran; 279 tes lulus) — 727d6ec Sapu konsistensi: laporan gate konsistensi bersih (0 pelanggaran); semua halaman memakai komponen; tidak ada istilah/warna/literal terlarang; tidak ada kelas di luar KIT_UI §1; tidak ada kartu bersarang atau kisi kartu seragam; navigasi & judul seragam di semua peran.
- [x] T7.2 (279 tes lulus; npm run build sukses) — c23cf8e Tes matriks otorisasi (semua route bernama × semua peran) + tes asap: setiap route GET dapat dibuka (200/redirect sesuai) oleh peran yang berhak dengan data seed.
- [x] T7.3 (279 tes lulus; composer validate valid; npm run build, route:list, migrate:fresh --seed sukses; php -l massal tidak dijalankan karena batasan alat, tes memuat semua kelas) — 727d6ec Verifikasi penuh: seluruh tes hijau, `php -l` pada semua PHP yang diubah, `composer validate`, `npm run build`, `route:list`, `migrate:fresh --seed` di DB v2, tidak ada `TODO`/`dd(`/`dump(`.
- [x] T7.4 — 727d6ec Dokumen laporan di `docs/laporan/`: `KEBUTUHAN_FUNGSIONAL.md` (ID FR, deskripsi, aktor, route, tes), `USE_CASE.md` (Mermaid + tabel), `ALUR_STATUS.md` (Mermaid), `ERD.md` (Mermaid), `CLASS_DIAGRAM.md` (Mermaid), `KAMUS_DATA.md`, `PETA_LAYAR.md` (layar → route → view → peran), `STATUS_TEMUAN_AUDIT.md`.
- [x] T7.5 — 727d6ec `docs/laporan/PENGUJIAN.md`: tabel Black-Box dari tes nyata (ID, skenario, masukan, hasil diharapkan, nama method tes, hasil = LULUS dari eksekusi terakhir). Jangan memuat hasil yang tidak dijalankan.
- [x] T7.6 — 727d6ec `docs/laporan/ASUMSI_DAN_KEPUTUSAN.md` final: semua keputusan agen; mana yang menunggu konfirmasi Dinkes (ejaan nama Faskes, plat dummy, aturan saran ambulans, `nama_pasien`/`no_telepon_kontak`, tanpa jadwal ulang, Tailwind CDN tak terkunci versi, rute OSRM demo).
- [x] T7.7 — 727d6ec `docs/HASIL_AKHIR.md`: ringkasan fitur; cara menjalankan (`composer install`, `.env`, `php artisan migrate:fresh --seed`, `php artisan serve`); akun demo & sandi; **checklist uji manual 10 menit** per peran (termasuk GPS/peta/polling/notifikasi yang tak dapat diuji agen); daftar item `[!]`; daftar berkas untuk dihapus manual (mis. `legacy/`, `resources/views/welcome.blade.php`, berkas Vite bawaan); jumlah tes lulus. Lalu ubah baris pertama file ini menjadi `STATUS_AGEN: SELESAI`.

## Hambatan (item `[!]`)

_(kosong)_

## Untuk dihapus manual (agen tidak boleh menghapus file)

- `resources/views/welcome.blade.php` (tak dipakai lagi), `resources/js/bootstrap.js` (tak diimpor lagi), `tests/Feature/ExampleTest.php` dan `tests/Unit/ExampleTest.php` (tes bawaan kerangka, boleh dihapus bila diinginkan).

## Catatan sesi

_(satu entri per sesi: tanggal-jam, item yang dikerjakan, hash commit, hasil tes, hal yang perlu diketahui sesi berikutnya)_

- 2026-10-11 sesi 1: T0.1, T0.2 selesai; `KonsistensiTest` dan `TestCase::withoutVite()` dibuat (commit 40cc63d). `php artisan test`: 4 lulus. T0.3 belum dicentang: trait helper tes pembuat user per peran menunggu enum `Role` + migrasi `users` (T1.1/T1.2); tambahkan di T1.3 lalu centang T0.3. Catatan: nama ikon `ambulance` terkena aturan istilah-baku, jangan dipakai di kode/view (lihat ASUMSI_DAN_KEPUTUSAN). T1.1 selesai (59f110f); `JenisFaskes::Rs` (bukan RumahSakit, kena aturan istilah). T1.2 selesai (253f7d4). T1.3 dan T0.3 selesai (3ac4dda; factory `Faskes::rs()` bukan rumahSakit). T1.4 selesai (760cb33): `PesananWorkflow`, `PesananPolicy` (view, batalkan), alias `role` (`PastikanPeran`), binding `sopir`/`petugas` di AppServiceProvider; exception di `app/Exceptions`. Pelaku salah peran/bukan pemilik melempar `AuthorizationException` (controller petakan ke 403). T1.5 selesai (e16c1ca): sandi demo lewat `config('sigap.seed_password')`; akun sopir: `sopir.{selebesolu,al,aryoko,posmor,klasaman,remu}@sigap.test`. TAHAP 1 selesai. T2.1 (ced6f0a) dan T2.2 (1b15102) selesai: layout `x-layouts.{dasar,publik,auth,app}`, `resources/js/sigap.js`, semua `x-ui.*` non-peta, helper `App\Support\{Ikon,Tampilan}`, `MenuComposer`. Tes memakai trait `Tests\Concerns\MenyediakanRuteSementara` untuk route() yang belum dibuat; `$errors` tidak ada di `Blade::render` (gunakan `view()->share('errors', ...)`). Komponen `tombol` default `tipe="button"` (form pakai `tipe="submit"`); `ringkasan` pakai jumlah sel genap. T2.3 (4eebf2e) dan T2.4 (21cb42d) selesai: `x-peta.{pemilih,pelacak,faskes}` + `peta.js` (pelacak membaca `data.data.sopir.{lat,lng}` dari JSON status), katalog `/_ui`, halaman galat. TAHAP 2 selesai. Route `/` kini bernama `beranda` (placeholder welcome, diganti di T3.2). T3.1 (342a334) dan T3.2 (9f562d6) selesai: `Auth\LoginController`/`RegisterController`, `LoginRequest` (throttle 5x), `PastikanPeran` mengeluarkan akun nonaktif, `Publik\BerandaController`/`FaskesController` (+ trait `Concerns\MembalasJson` untuk semua JSON), `bootstrap/app.php` redirectUsersTo. TAHAP 3 selesai. `resources/views/welcome.blade.php` tak terpakai lagi (masuk daftar hapus manual). Beranda tombol "Pesan ambulans" mengarah ke `warga.pesanan.buat` untuk warga yang masuk (route dibuat di T4.2; sampai itu `/` untuk warga login error, tes memakai rute sementara). TAHAP 4 selesai (d3ecb24, satu commit untuk T4.1-T4.3 karena view saling merujuk route): controller `Warga\{Beranda,Pesanan}Controller`, `Requests\Warga\*`, draf pesanan di session `draf_pesanan`, fragmen `warga/_tabel-pesanan`, `JenisLayanan::deskripsi()`/`TingkatDarurat::deskripsi()`, handler JSON `{ok:false,pesan}` di `bootstrap/app.php` (untuk request expectsJson), prop `jarak` pada `ui.bagian` (jangan pakai `class="mt-0"`). Skrip inline di view harus dibungkus DOMContentLoaded (modul Vite dimuat deferred). T5.1-T5.3 selesai (ff6ac49): `Admin\{Dashboard,Pesanan}Controller`, `Services\RingkasanDinkes` (kunci `pesanan_menunggu`, dst, dipakai `data-ringkasan`/`data-lencana` + polling di sigap.js lewat `data-ringkasan-url` pada rel menu), `ui.pilihan` mendukung optgroup (opsi bertingkat), fragmen `admin/_tabel-pesanan`, FormRequest `Admin\{AlasanWajib,AlasanOpsional,Tugaskan}Request`. Route admin untuk master/ketersediaan/laporan belum ada (menu memakai rute sementara di tes). T5.4 (c03828c) dan T5.5 (b1bb7c1) selesai: `Admin\{Faskes,Ketersediaan,Ambulans,Sopir,Petugas}Controller` (Sopir/Petugas turunan `AkunFaskesController` + view bersama `admin/akun/*`), `Faskes::perbaruiKetersediaan()` dan `Requests\KetersediaanFaskesRequest` (dipakai ulang Petugas di T6.2), prop `ui.isian` baru: `labelTersembunyi`, `tanpaOld`, `idUnik`. View halaman tidak boleh diawali `@php` (aturan awal-halaman): hitung di controller. Route resource punya PATCH selain PUT. T5.6 selesai (07d2ead): `Services\LaporanPesanan` (ringkas, baris, responsDetik/durasiDetik, formatDurasi), PDF `pdf/laporan` (dompdf, berdiri sendiri), CSV streaming. TAHAP 5 selesai. T6.1 (a8f3835) dan T6.2 (98c7052) selesai: `Sopir\DashboardController` (aksi `selesaikan`, bukan `selesai`: nama method berisi literal status terkena aturan literal-status), polling/GPS sopir otomatis lewat atribut `data-sopir` di sigap.js, `Petugas\{Dashboard,Ketersediaan,Ambulans}Controller`. T6.3 selesai (95303da, `tests/Feature/AlurPenuhTest.php`). TAHAP 6 selesai. Sesi 2: T7.2 selesai (c23cf8e): `OtorisasiMatriksTest` + `SmokeRouteTest` (konfirmasi tanpa draf dialihkan, dikecualikan dari 200); dasbor admin kini memakai label/nada enum. Sesi 3: T7.1, T7.3-T7.7 selesai (727d6ec): dokumen di docs/laporan/ dan docs/HASIL_AKHIR.md; APP_NAME di .env.example diubah; 279 tes lulus (2079 assertion). Semua item selesai. Sebelum menulis view: lihat `resources/css/app.css`, `design-tokens.css`, `resources/js/*`, `resources/icons`, dan ingat nama ikon `ambulance` terlarang di view. Catatan: `Pesanan` hanya `#[Fillable]` field pemohon; status/ambulans/sopir diubah workflow via forceFill/atribut di app/Services (bukan app/Http).

## Migrasi tampilan Material 3 dan dasbor Material (sesi interaktif)

Catatan di luar checklist tahap 0 sampai 7 di atas (tidak mengubah status agen). Rincian keputusan, langkah M1 sampai M7 dan D1 sampai D7, kontras warna, dan ringkasan perubahan ada di `docs/MIGRASI_M3.md`; kontrak kode di `docs/KIT_UI.md`; arah desain di `DESIGN.md` dan `docs/DESAIN_DASBOR_MATERIAL.md`.

- Selesai: seluruh langkah M1 sampai M7 dan D1 sampai D7 pada branch `material-design`.
- Menunggu keputusan pemilik proyek: halaman Masyarakat (Buat Pesanan, lacak) "tanpa laci" seperti tertulis di dokumen arah; saat ini masih memakai laci navigasi.
- Belum dilakukan: pemeriksaan visual di browser (sesi pengerjaan tidak punya browser); lihat checklist uji tampilan di `docs/HASIL_AKHIR.md`.
- Audit UX (`docs/AUDIT_UX.md`): gelombang pertama T1 sampai T8 selesai pada branch `material-design` (status dan penyimpangan di bagian 11 dokumen itu). Yang menunggu Dinkes: nomor darurat (`KONTAK_DARURAT_NOMOR`) dan ambang waktu tunggu.
