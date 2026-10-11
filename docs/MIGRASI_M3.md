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
- [x] M4 Komponen turunan: `judul-halaman`, `bagian`, `ringkasan`, `ringkasan-sel`, `rincian`, `rincian-baris`, `linimasa`, `paginasi`, `ketersediaan`, `kosong`, `lencana-status`, `waktu`, `grafik-batang`, `peta.*` (dan pencarian alamat di `peta.js`), katalog `/_ui`. Kriteria penerimaan dari tampilan `/admin` ditulis sebagai tes (`DashboardTest::test_dasbor_memakai_gaya_material_3`): hierarki judul dan keterangan, judul bagian, ringkasan, label grafik tanpa pecah baris, sel teks dan lencana sejajar (`inline-flex items-center gap-2`), label Enum berhuruf kapital awal kalimat (dijaga `EnumTest`), dan menu laci dengan bilah gulir tipis (`gulir-tipis`).
- [x] M5 Penyapuan halaman per peran (publik, auth, warga, admin, petugas, sopir, errors): 18 berkas disapu (nama kelas lama dan 9 `ukuran="sm"` dihapus; bilah tombol bawah di Buat Pesanan memakai `bg-surface-container shadow-level-1`). Semua kelas warna, teks, sudut, bayangan, dan bobot di seluruh view diperiksa terhadap token dan tidak ada yang tak dikenal. `PenyapuanM3Test` menjaga seluruh view (kecuali `pdf/`) sampai M6 mengaktifkan aturan umumnya.
- [x] M6 Aturan `token-lama` di `agent/konsistensi.json` (token dan kelas lama, `font-semibold`, `ukuran="sm"`; pada `resources/views`, `resources/js`, dan `app/Support`; pesannya memuat pemetaan ke nama baru) beserta kasus uji di `KonsistensiTest`. `PenyapuanM3Test`, trait `MemindaiNamaLama`, dan dua penjaga sementara di `KomponenUiTest` serta `LayoutTest` dihapus karena digantikan aturan ini. `KIT_UI` §10 dihapus. Catatan: `bg-surface` kini token M3 yang sah (latar halaman), jadi pemakaiannya sebagai "putih lama" tidak lagi bisa dideteksi otomatis; penyapuan M5 sudah menggantinya.
- [x] M7 Verifikasi penutup: `php artisan test` (298 lulus), `npm run build`, `designmd lint DESIGN.md` (0 galat, 0 peringatan), token sinkron; pemeriksaan bahwa semua kelas di view, JS, dan `Tampilan.php` ada di CSS hasil build; `docs/HASIL_AKHIR.md` diperbarui (bagian Tampilan Material 3, keterbatasan uji visual, checklist uji tampilan). **Belum dilakukan:** pemeriksaan visual di browser oleh pengguna.

## Kondisi sekarang

Setelah M5, tidak ada nama token lama di `resources/views` (kecuali `pdf/`, yang berdiri sendiri) dan komponen, layout, serta halaman semuanya Material 3. Yang tersisa adalah M6 (aturan pemindai umum) dan M7 (verifikasi penutup, termasuk pemeriksaan visual di browser oleh pengguna; sesi ini tidak punya browser).

## Lanjutan: dasbor Material (D1 sampai D7)

Setelah M1 sampai M7, pemilik proyek memilih gaya dasbor Material yang lebih kaya. Keputusannya ada di `docs/DESAIN_DASBOR_MATERIAL.md` dan mengalahkan aturan Don't dan Layout lama di `DESIGN.md`, `docs/KIT_UI.md`, dan `agent/konsistensi.json`. Satu langkah per sesi.

- [x] D1 Aturan dan token: `DESIGN.md` (warna `canvas` dan `chart-sky`, lima gaya tipe dasbor, bayangan `card`, `card-raised`, `nav-active`, token komponen baru dan yang berubah, prosa dan Do's and Don'ts), `agent/tokens.mjs` (bayangan dengan warna per tingkat dan lapisan ambient opsional), `agent/konsistensi.json` (`token-lama` tidak lagi melarang `canvas`; `warna-css` mengizinkan tiga token bayangan baru) beserta kasus uji, dan `docs/KIT_UI.md` bagian 10 (rencana komponen). Belum ada perubahan pada Blade dan `app.css`; latar `canvas` dipasang di D4.
- [x] D2 Komponen dasar: `panel` (kartu berbayang, prop `flush`), `stat` (putih dan utama), `icon-chip`, `tile`, `tabs` dan `tab`, `avatar`, lencana pil, tombol `on-primary`, ikon `user-check`, ukuran ikon 18/20/22/24/26, kelas bersama di `Tampilan` (`KARTU`, `kelasChip`, `inisial`). Tes render di `KomponenDasborTest`, `KomponenUiTest`, `TampilanTest`, dan katalog `/_ui`. Aturan `istilah-baku` diberi satu pengecualian sempit untuk nama ikon Lucide `ambulance`.
- [x] D3 Grafik: `x-ui.chart.bar` dan `x-ui.chart.donut` (SVG sisi server tanpa pustaka), `App\Support\Chart\BarGeometry`, `DonutGeometry`, `Angka`, `Warna`, `PesananStatus::warnaGrafik()`; uji unit geometri terhadap data pratinjau, tes render, dan bagian Grafik di katalog `/_ui`. `x-ui.grafik-batang` lama tetap sampai D5/D7.
- [x] D4 Kerangka aplikasi: `layouts.app` (bilah atas kartu melayang dengan pencarian, lonceng pesanan menunggu, dan avatar; laci putih dengan label grup, item pil 48px, item aktif `primary` + `shadow-nav-active`, blok pengguna dan keluar sebagai form POST; satu bilah gulir vertikal), `layouts.publik` (bilah atas kartu), latar `canvas` di `app.css`, padding halaman `px-4 lg:px-7`, komponen baru `x-ui.cari`, prop `ikon-ukuran` pada tombol, konfigurasi `sigap.bilah_atas` dan `grup` menu. Pencarian `?q=` belum menyaring daftar (D6); kolom cari hanya muncul untuk Dinkes sampai saat itu.
- [ ] D5 Dasbor Dinkes (`RingkasanDasbor`, tab, batas query).
- [ ] D6 Peran lain dan sapuan halaman.
- [ ] D7 Penutupan.
