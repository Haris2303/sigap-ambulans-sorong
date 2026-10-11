STATUS_AGEN: BERJALAN
TAHAP_AKTIF: 0

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
- [ ] T1.4 `App\Services\PesananWorkflow` + `PesananStatus::transisiTerbuka()` + exception domain + `PesananPolicy` + middleware `role` (alias `role` lewat `$middleware->alias([...])` di `bootstrap/app.php`) + route-model binding `{sopir}`/`{petugas}` berfilter peran. Tes lengkap workflow (§9.6).
- [ ] T1.5 Seeder idempoten sesuai §8: `FaskesSeeder`, `AmbulansSeeder`, `AkunDemoSeeder`, `PesananDemoSeeder` (hanya `local`), dipanggil `DatabaseSeeder`. Tes: dijalankan dua kali → jumlah baris sama; `php artisan migrate:fresh --seed` sukses di DB v2.

### Tahap 2 — UI kit & layout (sebelum halaman peran)
- [ ] T2.1 `layouts.dasar`, `layouts.publik`, `layouts.auth`, `layouts.app` (menu dari `config('sigap.menu')`, View Composer untuk lencana "Pesanan" Dinkes), `resources/js/sigap.js` (diimpor `resources/js/app.js`). Tes render tiap layout.
- [ ] T2.2 Komponen `ui.*` sesuai `docs/KIT_UI.md` §4 (semua yang non-peta, termasuk `ikon`, `ringkasan`, `rincian`, `dialog`, `paginasi` buatan sendiri) dengan resep kelas §2. Tes render tiap komponen (termasuk keadaan galat, kosong).
- [ ] T2.3 Komponen `peta.pemilih`, `peta.pelacak`, `peta.faskes` + `resources/js/peta.js` (Leaflet & Routing Machine dari npm; dimuat hanya lewat `layouts.dasar :peta`).
- [ ] T2.4 Katalog `/_ui` (local/testing) + halaman galat 403/404/419/500 memakai layout + tes render semuanya. Jalankan `npm run build` dan pastikan lulus.

### Tahap 3 — Autentikasi & publik
- [ ] T3.1 Login / register (hanya peran `warga`) / logout; `guest` & `auth`; akun `aktif=false` ditolak; throttle login; pengalihan sesuai `Role::ruteBeranda()`. Tes.
- [ ] T3.2 Landing `beranda`, halaman `/faskes` + `faskes.data` (filter jenis, `ui.ketersediaan`, `peta.faskes`). Tes (termasuk "Belum diperbarui" / "Data lama").

### Tahap 4 — Masyarakat (warga)
- [ ] T4.1 `warga.beranda` (pesanan aktif, ringkasan, tombol Pesan Ambulans) dan `warga.pesanan.index` (riwayat berpaginasi). Tes.
- [ ] T4.2 `pesanan.buat` (mobile-first sesuai §7) + `FormRequest` + `pesanan.tinjau` → `pesanan.konfirmasi` → `pesanan.simpan` (lewat `PesananWorkflow::buat`). Tes: belum ada pesanan sebelum konfirmasi; tombol "Ubah" mempertahankan isian; validasi per aturan.
- [ ] T4.3 `pesanan.show` (lacak: `ui.linimasa`, info ambulans/sopir mulai `ditugaskan`, `peta.pelacak` mulai `diterima`), `pesanan.status` JSON, `pesanan.batalkan`. Tes otorisasi (warga B tidak bisa mengakses pesanan warga A).

### Tahap 5 — Dinkes (admin)
- [ ] T5.1 `admin.dashboard` (statistik, ketersediaan, grafik 7 hari, pesanan terbaru) + `pesanan.ringkasan` JSON + polling & lencana. Tes.
- [ ] T5.2 `pesanan.index` (filter, urutan prioritas) + `pesanan.show` + aksi `verifikasi`, `tolak`, `batalkan` lewat workflow. Tes.
- [ ] T5.3 Aksi `tugaskan` / `lepas`: pemilih ambulans tersedia + sopir valid (online, Faskes sama, tidak sibuk), urutan saran (§7). Tes termasuk semua penolakan.
- [ ] T5.4 Master `faskes` (CRUD, pemilih koordinat, `aktif`, ketersediaan) + `ketersediaan.index/update`. Tes.
- [ ] T5.5 Master `ambulans`, `sopir`, `petugas` (CRUD tanpa destroy, `*.aktif`, validasi Faskes/peran, larangan menonaktifkan diri sendiri). Tes.
- [ ] T5.6 `laporan.index` (periode, status, jenis layanan, Faskes, waktu respons, durasi) + `laporan.pdf` + `laporan.csv`. Tes (PDF: respons 200 `application/pdf`; CSV: isi & header).

### Tahap 6 — Petugas Faskes & Sopir
- [ ] T6.1 Sopir: `dashboard`, `online`, `lokasi`, `penugasan` (JSON), `pesanan.{terima,tolak,berangkat,selesai}` lewat workflow. Tes (sopir lain tidak bisa mengubah pesanan bukan miliknya; urutan status).
- [ ] T6.2 Petugas: `dashboard`, `pesanan.show`, `ketersediaan.edit/update` (validasi `tersedia ≤ total`, stempel waktu), `ambulans.status` (`tersedia`↔`servis` saja, milik Faskes sendiri). Tes isolasi antar Faskes.
- [ ] T6.3 Tes alur penuh lintas peran: memesan → konfirmasi → verifikasi → tugaskan → terima → berangkat → selesai; jalur tolak; batal warga; batal admin; sopir menolak → `terverifikasi` → ditugaskan ke sopir lain; ketersediaan yang diperbarui Petugas tampil di `/faskes`.

### Tahap 7 — Konsistensi & dokumen
- [ ] T7.1 Sapu konsistensi: laporan gate konsistensi bersih (0 pelanggaran); semua halaman memakai komponen; tidak ada istilah/warna/literal terlarang; tidak ada kelas di luar KIT_UI §1; tidak ada kartu bersarang atau kisi kartu seragam; navigasi & judul seragam di semua peran.
- [ ] T7.2 Tes matriks otorisasi (semua route bernama × semua peran) + tes asap: setiap route GET dapat dibuka (200/redirect sesuai) oleh peran yang berhak dengan data seed.
- [ ] T7.3 Verifikasi penuh: seluruh tes hijau, `php -l` pada semua PHP yang diubah, `composer validate`, `npm run build`, `route:list`, `migrate:fresh --seed` di DB v2, tidak ada `TODO`/`dd(`/`dump(`.
- [ ] T7.4 Dokumen laporan di `docs/laporan/`: `KEBUTUHAN_FUNGSIONAL.md` (ID FR, deskripsi, aktor, route, tes), `USE_CASE.md` (Mermaid + tabel), `ALUR_STATUS.md` (Mermaid), `ERD.md` (Mermaid), `CLASS_DIAGRAM.md` (Mermaid), `KAMUS_DATA.md`, `PETA_LAYAR.md` (layar → route → view → peran), `STATUS_TEMUAN_AUDIT.md`.
- [ ] T7.5 `docs/laporan/PENGUJIAN.md`: tabel Black-Box dari tes nyata (ID, skenario, masukan, hasil diharapkan, nama method tes, hasil = LULUS dari eksekusi terakhir). Jangan memuat hasil yang tidak dijalankan.
- [ ] T7.6 `docs/laporan/ASUMSI_DAN_KEPUTUSAN.md` final: semua keputusan agen; mana yang menunggu konfirmasi Dinkes (ejaan nama Faskes, plat dummy, aturan saran ambulans, `nama_pasien`/`no_telepon_kontak`, tanpa jadwal ulang, Tailwind CDN tak terkunci versi, rute OSRM demo).
- [ ] T7.7 `docs/HASIL_AKHIR.md`: ringkasan fitur; cara menjalankan (`composer install`, `.env`, `php artisan migrate:fresh --seed`, `php artisan serve`); akun demo & sandi; **checklist uji manual 10 menit** per peran (termasuk GPS/peta/polling/notifikasi yang tak dapat diuji agen); daftar item `[!]`; daftar berkas untuk dihapus manual (mis. `legacy/`, `resources/views/welcome.blade.php`, berkas Vite bawaan); jumlah tes lulus. Lalu ubah baris pertama file ini menjadi `STATUS_AGEN: SELESAI`.

## Hambatan (item `[!]`)

_(kosong)_

## Untuk dihapus manual (agen tidak boleh menghapus file)

_(kosong)_

## Catatan sesi

_(satu entri per sesi: tanggal-jam, item yang dikerjakan, hash commit, hasil tes, hal yang perlu diketahui sesi berikutnya)_

- 2026-10-11 sesi 1: T0.1, T0.2 selesai; `KonsistensiTest` dan `TestCase::withoutVite()` dibuat (commit 40cc63d). `php artisan test`: 4 lulus. T0.3 belum dicentang: trait helper tes pembuat user per peran menunggu enum `Role` + migrasi `users` (T1.1/T1.2); tambahkan di T1.3 lalu centang T0.3. Catatan: nama ikon `ambulance` terkena aturan istilah-baku, jangan dipakai di kode/view (lihat ASUMSI_DAN_KEPUTUSAN). T1.1 selesai (59f110f); `JenisFaskes::Rs` (bukan RumahSakit, kena aturan istilah). T1.2 selesai (253f7d4). T1.3 dan T0.3 selesai (3ac4dda; factory `Faskes::rs()` bukan rumahSakit). Lanjut dari T1.4 (PesananWorkflow, exception, policy, middleware role, binding sopir/petugas). Catatan: `Pesanan` hanya `#[Fillable]` field pemohon; status/ambulans/sopir diubah workflow via forceFill/atribut di app/Services (bukan app/Http).
