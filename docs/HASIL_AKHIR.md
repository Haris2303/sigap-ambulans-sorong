# Hasil akhir — SIGAP Ambulans v2

## Ringkasan fitur

Alur: Masyarakat → sistem → Dinkes (verifikasi, penugasan ambulans dan sopir) → Faskes → layanan.

- Publik: landing, info Faskes (ketersediaan bed, dokter, ambulans, peta), masuk, daftar.
- Masyarakat: buat pesanan (darurat, rujukan, persalinan), konfirmasi, lacak (linimasa, ambulans, sopir, posisi), batal sebelum penugasan, riwayat.
- Dinkes: dasbor, daftar dan detail pesanan, verifikasi, tolak, tugaskan, lepas, batalkan, master Faskes/Ambulans/Sopir/Petugas, ketersediaan, laporan PDF dan CSV.
- Petugas Faskes: pesanan Faskes sendiri, ketersediaan, status ambulans (`tersedia` ↔ `servis`).
- Sopir: online/offline, kirim lokasi, terima/tolak, berangkat, selesai.

Dokumen: `docs/laporan/` (kebutuhan, use case, alur status, ERD, class diagram, kamus data, peta layar, status temuan audit, pengujian, asumsi).

## Cara menjalankan

```
composer install
cp .env.example .env        # lalu atur DB_* ; php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Pesanan demo (semua status, 7 hari) hanya dibuat bila `APP_ENV=local`.

## Akun demo

Domain `@sigap.test`. Sandi: nilai `SEED_PASSWORD` bila diisi; bila tidak, `password` pada `local`/`testing`, dan acak (dicetak sekali oleh seeder) di lingkungan lain.

| Peran | Email |
|---|---|
| Dinkes | `admin@sigap.test` |
| Masyarakat | `warga@sigap.test` |
| Petugas Faskes | `petugas.selebesolu@sigap.test`, `petugas.klasaman@sigap.test` |
| Sopir | `sopir.selebesolu@`, `sopir.al@`, `sopir.aryoko@`, `sopir.posmor@`, `sopir.klasaman@`, `sopir.remu@` `sigap.test` |

## Tampilan Material 3

Antarmuka memakai Material Design 3 baseline (bukan Expressive), terang saja, dengan gaya dasbor Material pilihan pemilik proyek (`docs/DESAIN_DASBOR_MATERIAL.md`): kartu putih berbayang di atas latar `canvas`, kartu statistik dan chip ikon pembawa makna, grafik batang dan donat digambar Chart.js (dimuat hanya di halaman yang punya grafik), tab, avatar, bilah atas kartu melayang, dan laci putih. Dasbor Dinkes (`/admin`) menjadi halaman acuan. Sumber kebenarannya `DESIGN.md`: skema tonal dari benih `#0B5C80`, 30 warna, skala tipe M3 (Roboto, lokal lewat npm), bentuk 4/8/12/16/28/penuh, elevasi level 0 sampai 3, dan lapisan keadaan. Kontrak kode ada di `docs/KIT_UI.md`; keputusan dan langkah migrasi di `docs/MIGRASI_M3.md`.

- Mengubah tema: ubah front matter `DESIGN.md`, jalankan `node agent/tokens.mjs` (menghasilkan `resources/css/design-tokens.css`), lalu `npm run build`.
- Pemeriksaan dokumen desain: `node node_modules/@google/design.md/dist/index.js lint DESIGN.md` (0 galat, 0 peringatan).
- Penjaga otomatis: `agent/konsistensi.json` (termasuk aturan `token-lama`, `komponen-lama`, dan `warna-js`), `KonsistensiTest`, `TokenDesainTest` (css sinkron dengan DESIGN.md), `KomponenUiTest`, `LayoutTest`.
- Mode gelap ditunda; seluruh warna memakai peran M3 sehingga dapat ditambahkan nanti.

## Keterbatasan

Sesi pengerjaan tidak punya browser. Tampilan, JavaScript, peta, GPS, polling, getar, dan notifikasi peramban tidak teruji secara visual. Yang teruji: tes HTTP/render sisi server, `npm run build`, pemindai konsistensi, dan pemeriksaan bahwa setiap kelas Tailwind yang tertulis di view, JS, dan `Tampilan.php` ada sebagai selector di CSS hasil build.

Khusus tampilan Material 3, yang belum pernah dilihat dan perlu diperiksa manual di browser (daftar langkah ada pada checklist di bawah):
- Tata letak responsif: laci tetap di lg ke atas, bilah atas dan laci modal di bawah lg, titik henti `md` 768px dan `lg` 1024px.
- Perilaku laci modal (scrim, Escape, fokus), snackbar, dan bayangan bilah atas saat gulir (JavaScript di `resources/js/sigap.js`).
- Lapisan keadaan (hover 8%, fokus 10%, tekan 10%) dan area sentuh 48px (CSS `lapisan-keadaan` dan `target-sentuh` di `tambahan.css`).
- Pemuatan font Roboto dan kerapian angka tabular.
- Kontras: `designmd lint` hanya memeriksa pasangan warna pada token komponen di `DESIGN.md`; warna yang dirakit di markup tidak diperiksa otomatis.

## Checklist uji tampilan Material 3 (±10 menit, sekali untuk semua peran)

Jalankan `npm run build` lalu buka aplikasi di browser desktop dan ponsel (atau mode perangkat di devtools).
- Lebar 1280px: laci navigasi tetap di kiri (lebar 280px), item aktif berbentuk pil `secondary-container`, tanpa bilah atas. Menu panjang bergulir dengan bilah tipis.
- Lebar 390px: bilah atas 64px berisi tombol menu dan judul halaman; tombol menu membuka laci modal dengan scrim; laci menutup lewat tombol Tutup, klik scrim, dan Escape.
- Dasbor Dinkes (`/admin`): judul 28px dengan subjudul 16px dan tombol tonal di kanan; empat kartu statistik (utama biru, tiga putih dengan chip ikon) turun satu kolom di ponsel; grafik batang bergulir horizontal di ponsel dengan label hari dan tanggal dua baris; donat dengan legenda; empat petak ketersediaan; tab Semua, Perlu tindakan, Berjalan memuat ulang halaman ke kartu Pesanan terbaru; tabel bergulir di dalam kartunya (kepala kartu dan tab tidak ikut bergulir); lencana "Gawat" sejajar vertikal dengan teks layanan.
- Tombol berbentuk pil setinggi 40px; hover, fokus papan ketik (Tab), dan tekan menampilkan lapisan tipis; tombol nonaktif tampak redup dan tidak bereaksi.
- Isian: batas Outline, label di atas kolom, fokus menebalkan batas jadi biru 2px, galat menampilkan batas dan teks merah.
- Aksi dengan konfirmasi (mis. Tolak): dialog berlatar scrim dengan sudut 28px; tombol aksi merusak berwarna merah.
- Setelah aksi berhasil: snackbar hitam kebiruan di dasar layar, tombol Tutup menghilangkannya.
- Buat Pesanan di ponsel: tombol "Tinjau pesanan" tetap terlihat di dasar layar dengan bayangan tipis.
- Semua target sentuh terasa cukup besar (minimal 48px) dan teks tidak lebih kecil dari 12px.

## Checklist uji manual (±10 menit per peran)

Masyarakat
- Daftar, masuk, buka Buat Pesanan di ponsel; tombol "Gunakan lokasi saya" mengisi koordinat dan penanda peta.
- Cari alamat; penanda berpindah. Pilih rujukan, ringkasan ketersediaan Faskes tampil.
- Tinjau → Ubah mempertahankan isian → Kirim Pesanan; halaman lacak terbuka.
- Setelah Dinkes bertindak, linimasa berubah tanpa memuat ulang halaman (polling).
- Setelah sopir menerima, posisi sopir muncul di peta dan bergerak.

Dinkes
- Angka "Menunggu verifikasi" dan lencana menu bertambah sendiri saat ada pesanan baru.
- Verifikasi, tugaskan ambulans dan sopir (saran urutan sesuai jenis layanan), lepas, batalkan dengan alasan.
- Peta lokasi jemput di detail pesanan tampil benar.
- Master: tambah Faskes dengan pemilih koordinat; nonaktifkan; ubah ketersediaan.
- Laporan: filter periode, unduh PDF dan CSV, buka di aplikasi lain.

Petugas Faskes
- Penugasan ke Faskes tampil; ubah ketersediaan dan lihat hasilnya di `/faskes`.
- Tukar status ambulans `tersedia` ↔ `servis`.

Sopir
- Aktifkan online; izin GPS diminta dan lokasi terkirim berkala.
- Penugasan baru memunculkan popup, getar, dan notifikasi peramban.
- Terima → Mulai Perjalanan → Selesai; rute di peta tampil (server OSRM demo).
- Tolak dengan alasan; penugasan kembali ke Dinkes.

## Item `[!]`

Tidak ada.

## Keputusan yang menunggu konfirmasi Dinkes

Lihat `docs/laporan/ASUMSI_DAN_KEPUTUSAN.md` (kolom "Perlu konfirmasi Dinkes").

## Berkas untuk dihapus manual

- `legacy/` (aplikasi lama, bila ada).
- `resources/views/welcome.blade.php` (tak dipakai).
- `resources/js/bootstrap.js` (tak diimpor).
- `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php` (tes bawaan kerangka).

## Jumlah tes

`php artisan test` pada 2026-10-11 (setelah migrasi Material 3): 440 lulus (3456 assertion), termasuk langkah D1 sampai D7 dasbor Material. `npm run build` sukses; `designmd lint DESIGN.md` 0 galat 0 peringatan; `node agent/tokens.mjs` tidak mengubah `design-tokens.css`. Sebelum migrasi (tahap 7 awal): 279 lulus, `composer validate` valid, `migrate:fresh --seed` sukses di DB v2; skema basis data tidak diubah oleh migrasi tampilan.
