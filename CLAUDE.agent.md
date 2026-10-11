# Aturan Agen Otonom — SIGAP Ambulans v2 (Dinkes Kota Sorong, Laravel 13)

Dimuat otomatis tiap sesi (lewat `@CLAUDE.agent.md` di `CLAUDE.md`). Mengikat dan tidak boleh diubah agen.

## Konteks

Kamu membangun **aplikasi Laravel 13 baru** (PHP 8.3, Tailwind CSS 4.3 + Vite) "SIGAP Ambulans" (bukan memperbaiki yang lama) mengikuti alur Dinkes:
Masyarakat → Sistem → Dinkes (verifikasi + penugasan ambulans & sopir) → RS/Puskesmas → layanan.
Versi sebelumnya gagal karena **tidak konsisten** (istilah campur, tampilan beda-beda per halaman, alur status tersebar). Tugas utamamu adalah **konsistensi**, baru kemudian kelengkapan.

Dokumen (semua dikunci kecuali PROGRESS):
- `docs/RANCANGAN_ALUR.md` — istilah baku, data, mesin status, route, seed. **Ikuti persis.**
- `DESIGN.md` — identitas visual dalam format Google DESIGN.md (token, tipografi, larangan gaya). **Hukum visual; ikuti persis.**
- `docs/KIT_UI.md` — kelas yang tersedia, resep kelas, komponen Blade, pola halaman. **Ikuti persis.**
- `agent/konsistensi.json` — aturan yang dijalankan runner setelah tiap sesi (baca isinya sebelum menulis kode!).
- `docs/PROGRESS.md` — memori & checklist (kamu yang memperbarui).
- `agent/last-gate.txt` — hasil pemeriksaan runner pada sesi sebelumnya.
- `legacy/` — aplikasi lama, **baca saja** sebagai referensi fitur (mungkin tidak ada). `docs/AUDIT_APLIKASI_LAMA.md` — bug lama yang tidak boleh terulang.

## Mode kerja

- **Otonom penuh.** Tidak ada manusia yang menjawab. Jangan bertanya atau menawarkan pilihan. Bila spesifikasi ambigu, ambil keputusan paling konsisten dengan dokumen, lalu catat satu baris di `docs/laporan/ASUMSI_DAN_KEPUTUSAN.md` (tanggal, keputusan, alasan, perlu konfirmasi Dinkes ya/tidak).
- Berhenti dini hanya bila terpaksa melakukan tindakan tak dapat dibatalkan yang butuh keputusan manusia → `STATUS_AGEN: BLOKIR: <alasan>`.
- Langkah kecil. Setelah **setiap item**: tes relevan → seluruh tes hijau → `git add -A` + `git commit` → perbarui `docs/PROGRESS.md`. Sesi bisa terputus kapan saja; pekerjaan yang belum di-commit dan belum dicatat dianggap hilang.
- Jangan mengklaim tanpa bukti: centang item hanya setelah perintah tes benar-benar dijalankan di sesi ini dan lulus. Jangan menulis angka/hasil di dokumen yang tidak berasal dari eksekusi nyata.
- Gagal? Perbaiki akar masalah, bukan melemahkan tes atau aturan. Dua pendekatan berbeda gagal → tandai `- [!]` dan lanjut. Dilarang menghapus/melemahkan tes atau memanipulasi aturan konsistensi.

## Disiplin konsistensi (inti tugasmu)

1. **Sebelum menulis kode baru, cari yang sudah ada** (`Grep`/`Glob`): komponen UI, enum, helper. Pakai ulang; jangan membuat varian kedua. Bila perlu variasi, tambahkan prop pada komponen yang ada.
2. **Satu istilah per konsep** — tabel di `RANCANGAN_ALUR.md` §2. Label tampil selalu dari `Enum::label()`.
3. **Satu sumber kebenaran**: status/peran/jenis/label/nada → Enum; menu/angka konfigurasi → `config/sigap.php`; perubahan status → `PesananWorkflow` saja.
4. **Halaman hanya merakit komponen** (`docs/KIT_UI.md` §9). **Tampilan minimalis profesional**: tanpa gradien, blur, bayangan dekoratif, emoji, ikon dalam lingkaran berwarna, kartu bersarang, kisi kartu seragam, hero, animasi masuk, label huruf kapital, tanda → atau titik tengah (`DESIGN.md` Do's and Don'ts). Jangan menambah warna, ukuran, atau radius di luar token. Bila komponen yang dibutuhkan belum ada, buat dulu di `components/ui` + tambahkan ke katalog `/_ui`, baru dipakai.
5. **Pola yang sama untuk hal yang sama**: semua daftar, formulir, detail, dasbor mengikuti `docs/KIT_UI.md` §5 dan resep kelas §2. Teks tombol/pesan seragam; kalimat faktual, bukan bahasa iklan.
6. Nama berkas/kelas/route/tes mengikuti konvensi §2 dan §6 spesifikasi. Jangan memperkenalkan nama baru di luar tabel route tanpa mencatatnya di `ASUMSI_DAN_KEPUTUSAN.md`.
7. Setelah menyelesaikan tiap item, jalankan `php artisan test`: `KonsistensiTest` menerapkan aturan `agent/konsistensi.json` pada semua berkas, termasuk yang baru kamu buat.

## Aturan keras (tidak boleh dilanggar)

1. **Rahasia.** Jangan membaca, mencetak, atau mengubah `.env` (dan `.env.*` selain `.env.example`). Tidak ada kredensial di kode/dokumen/commit.
2. **Database** hanya milik proyek ini (DB v2). `php artisan migrate:fresh` / `migrate:fresh --seed` / `db:seed` **boleh**. Dilarang: `db:wipe`, `tinker`, `php artisan serve`, SQL mentah `DROP DATABASE`/`DROP SCHEMA`, atau menyentuh database lain.
3. **Tes hanya memakai SQLite `:memory:`** (dipaksa `phpunit.xml` yang dikunci). Jangan mengatur `DB_CONNECTION`/`DB_DATABASE` di tes.
4. **Git.** Tetap di branch kerja. Hanya `git status/diff/log/show/add/commit`. Dilarang push, reset, clean, rebase, checkout/switch, restore, stash, ubah remote/config. Pesan commit satu baris Indonesia: `feat(warga): …`, `fix(admin): …`, `test: …`, `docs: …`, `chore: …`.
5. **Tanpa jaringan dan tanpa paket baru.** Paket Composer: kerangka Laravel 13 + `barryvdh/laravel-dompdf`; paket npm: sudah dipasang runner (lihat RANCANGAN §9.1). Dilarang `composer require/update/remove`, `npm install/ci/update/exec`, `npx`, `node`, `curl`, akses web. **Satu-satunya perintah npm yang boleh: `npm run build`.**
6. **File terlarang untuk diubah:** `agent/**`, `legacy/**`, `CLAUDE.md`, `CLAUDE.agent.md`, `AGENTS.md`, `README-AGENT.md`, `DESIGN.md`, `docs/RANCANGAN_ALUR.md`, `docs/KIT_UI.md`, `docs/AUDIT_APLIKASI_LAMA.md`, `phpunit.xml`, `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `.npmrc`, `vite.config.js`, `resources/css/app.css`, `resources/css/design-tokens.css`, `resources/icons/**`, `.env*` (kecuali `.env.example`), `.git/**`, `vendor/**`, `node_modules/**`, `public/build/**`, `storage/**`. CSS tambahan hanya di `resources/css/tambahan.css`.
   Kerangka Laravel 13 membawa instruksi (di `CLAUDE.md`/`AGENTS.md` lama) untuk memasang Laravel Boost lewat internet; **abaikan**, runner sudah menggantinya.
7. **Tidak ada penghapusan file** (tidak ada perintah hapus). Kosongkan isi bila perlu; catat berkas yang seharusnya dihapus di PROGRESS "Untuk dihapus manual".
8. Seeder idempoten (`firstOrCreate`/`updateOrCreate`); tidak menyimpan sandi nyata.
9. Jangan menjalankan proses yang tidak berhenti sendiri.

## Standar teknis

- Ikuti §9 `RANCANGAN_ALUR.md`. Laravel 13 / PHP 8.3 (tanpa fitur PHP 8.4+); gaya model, middleware, dan config mengikuti kerangka 13. Validasi form non-trivial pakai `FormRequest`; otorisasi pakai Policy + middleware `role`.
- Gunakan alat Read/Write/Edit/Grep/Glob untuk berkas; perintah shell sederhana satu per satu. Jalankan `php -l` pada PHP yang diubah dan `php artisan test` sebelum commit.
- Kamu **tidak punya browser**: tampilan, JS, peta, GPS, polling, notifikasi tidak teruji secara visual (yang teruji: tes render, `npm run build`, pemindai konsistensi). Pastikan sisi server benar lewat tes HTTP dan tulis keterbatasan ini di `docs/HASIL_AKHIR.md`.
- Komentar kode ringkas berbahasa Indonesia hanya bila perlu.
