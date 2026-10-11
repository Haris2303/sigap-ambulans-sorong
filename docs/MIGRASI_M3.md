# Migrasi tampilan ke Material Design 3

Catatan kemajuan untuk pergantian gaya dari "buku catatan dispatch" ke Material Design 3 baseline (bukan Expressive). Sumber kebenaran visual: `DESIGN.md`; kontrak kode: `docs/KIT_UI.md`. Daftar ini dipisah dari `docs/PROGRESS.md` agar status agen otonom tidak terganggu.

## Keputusan

- Font Roboto Variable (`@fontsource-variable/roboto`), lokal lewat npm.
- Benih `#0B5C80`, varian TonalSpot, spesifikasi M3 2021, skema terang. Skema dibekukan sebagai heksadesimal di `DESIGN.md`; paket penghasilnya (`@material/material-color-utilities` 0.4.0) dan skripnya tidak disimpan. Parameter lengkap ada di `DESIGN.md` bagian Colors.
- Warna kustom `success` (sumber `#2E7D32`) dan `warning` (sumber `#F9A825`) diharmonisasi ke benih. Hanya peran color, container, dan on-container yang dibuat.
- Mode gelap ditunda; seluruh warna memakai peran M3 sehingga skema gelap dapat ditambahkan nanti.
- Isian outlined dengan label tetap di atas kolom. Target sentuh minimal 48px (tombol 40px dengan area sentuh diperluas).
- Peran `secondary`, `tertiary`, `surface-dim`, `surface-bright`, `background`, `on-success`, `on-warning` tidak dibuat karena tidak dipakai.
- Lebar laci navigasi 280px (spesifikasi M3: 360px).

## Langkah

- [x] M1 Fondasi: `CLAUDE.md` mode interaktif; `DESIGN.md` M3; `agent/tokens.mjs`; `app.css` dan templatnya; `docs/KIT_UI.md`; `agent/konsistensi.json` beserta kasus uji; `tambahan.css` (`lapisan-keadaan`, `target-sentuh`); `TokenDesainTest`.
- [x] M2 Komponen primitif: `tombol` (nada baru `tonal` dan `terbalik`), `isian`, `pilihan`, `area-teks`, `centang`, `lencana`, `peringatan`, `flash`, `snackbar` (baru), `dialog`, `panel`, `tabel`, `opsi-baris`, `ikon` (hanya ukuran 18 dan 24). `KomponenUiTest` diubah lebih dulu, lalu komponennya; ditambah penjaga `test_komponen_m3_tidak_memuat_nama_token_lama` yang daftarnya bertambah tiap langkah. Sisa M2 yang sengaja ditunda ke M5: `ukuran="sm"` pada pemanggil tombol (kini diperlakukan sebagai `md`).
- [x] M3 Layout: `dasar`, `app` (laci navigasi tetap di lg+, laci modal dengan scrim dan bilah atas 64px di bawah lg), `publik`, `auth`. `LayoutTest` diubah lebih dulu; ditambah penjaga nama lama untuk keempat layout (trait `MemindaiNamaLama`, dipakai bersama `KomponenUiTest`). `sigap.js`: laci (tombol, scrim, Escape, fokus), bayangan bilah atas saat gulir, hitungan menu lewat atribut `hidden`.
- [ ] M4 Komponen turunan: `judul-halaman`, `bagian`, `ringkasan`, `ringkasan-sel`, `rincian`, `linimasa`, `paginasi`, `ketersediaan`, `kosong`, `waktu`, `grafik-batang`, `peta.*`, dan katalog `/_ui`.
- [ ] M5 Penyapuan halaman per peran (publik, auth, warga, admin, petugas, sopir, errors): hapus `ukuran="sm"` dan semua nama kelas lama (pemetaan di `docs/KIT_UI.md` §10).
- [ ] M6 Aturan pemindai nama lama di `agent/konsistensi.json` (token lama dan `font-semibold`) beserta kasus uji; hapus §10 dari `docs/KIT_UI.md`.
- [ ] M7 Verifikasi penutup: `php artisan test`, `npm run build`, `designmd lint DESIGN.md`; perbarui `docs/HASIL_AKHIR.md` (keterbatasan uji visual); pemeriksaan visual oleh pengguna di browser.

## Kondisi antara

Selama M2 sampai M5 belum selesai, komponen dan halaman lama masih memakai nama kelas token lama (`bg-canvas`, `border-line`, `text-h1`, dan seterusnya). Nama itu tidak lagi menghasilkan CSS, jadi tampilan aplikasi belum representatif sampai langkah-langkah itu selesai. Tes tetap hijau karena hanya memeriksa string kelas yang dikeluarkan komponen.

`KomponenUiTest` dan `LayoutTest` diubah bersama komponen yang diganti, bukan lebih awal. Yang masih menyatakan kelas lama dan harus diganti pada langkahnya:

- M4 (`KomponenUiTest`): `text-h1` (judul-halaman), `text-h2` dan `mt-8` (bagian), `text-display` (ringkasan), `<dt class="text-on-surface-muted">` (rincian), `font-semibold` (linimasa).
