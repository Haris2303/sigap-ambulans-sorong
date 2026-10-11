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

## Keterbatasan

Agen tidak punya browser. Tampilan, JavaScript, peta, GPS, polling, getar, dan notifikasi peramban tidak teruji secara visual. Yang teruji: tes HTTP/render sisi server, `npm run build`, pemindai konsistensi.

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

`php artisan test` pada 2026-10-11: 279 lulus (2079 assertion). `npm run build` sukses; `composer validate` valid; `migrate:fresh --seed` sukses di DB v2.
