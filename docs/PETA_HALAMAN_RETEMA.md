# Peta halaman retema B

Dibuat sebelum R6 sesuai `docs/RETEMA_B.md` bagian 8. Daftar semua view di `resources/views`, rute dan perannya, komponen utama (yang dipakai sekarang, atau yang direncanakan bila halaman belum disentuh retema B), dan langkah mana yang mengerjakannya. Langkah memakai label P1 sampai P5 (pemetaan ke langkah R di `docs/RETEMA_B.md` bagian 11 ditulis di kolom terakhir).

Status "sudah" = sudah dibangun ulang arah B (R1 sampai R3, langkah "DB"). Status "lama" = markup lama (M3/D-era), belum disentuh retema B.

## Halaman (dipetakan satu per satu)

| View | Rute (nama) | Peran | Komponen utama | Status | Langkah | = R |
|---|---|---|---|---|---|---|
| `admin/dashboard.blade.php` | `admin.dashboard` | Dinkes | `x-ui.judul-halaman`, `x-ui.stat`, `x-ui.hero-stat`, `x-ui.chart`, `x-ui.ring-tile`, tabel "Pesanan terbaru" (markup sendiri, lihat catatan) | sudah | — (selesai di "DB") | DB |
| `warga/beranda.blade.php` | `warga.beranda` | Masyarakat | lama: `x-ui.judul-halaman`, `x-ui.stat`, `x-ui.bagian`, `warga/_tabel-pesanan`. **Tidak dirinci RETEMA_B.md bagian 8** (tidak ada bagian "Dasbor Masyarakat") — ini beranda/dasbor pribadi Masyarakat, paling dekat bentuknya dengan dasbor Faskes/sopir (8.6/8.7), dipetakan ke sana sebagai keputusan default | lama | **P4** (keputusan default, dicatat; bukan P1 karena bukan daftar/detail) | R8 |
| `admin/pesanan/index.blade.php` | `admin.pesanan.index` | Dinkes | lama: `x-ui.judul-halaman`, `x-ui.pilihan` (filter), `x-ui.tabel`, `x-ui.paginasi`. Target: `x-ui.tabs` (tab status), `x-ui.cari` di atas tabel, `x-ui.panel flush`, `x-ui.tabel` (mode baris ponsel baru), `x-ui.paginasi` | lama | **P1** | R6 |
| `admin/pesanan/show.blade.php` | `admin.pesanan.show` | Dinkes | lama: `x-ui.judul-halaman`, `x-ui.lencana-status`, `x-ui.rincian`/`rincian-baris`, `x-ui.pilihan` (tugaskan), `x-ui.area-teks` (tolak/batal/lepas), `x-ui.linimasa`, `x-ui.dialog` (konfirmasi via `data-konfirmasi`). Target: panel "Detail pesanan dan linimasa" sesuai B2, dialog per aksi dengan judul eksplisit | lama | **P1** | R6 |
| `admin/_tabel-pesanan.blade.php` | partial lama, dipakai `admin.pesanan.index` versi lama | Dinkes | `x-ui.tabel` kolom Kode terpisah (pola M3, bukan pola kartu "Pesanan terbaru" arah B) | lama, ditinggalkan R6 (lihat catatan), **tidak dihapus** | — | — |
| *(baru)* `petugas/pesanan/index.blade.php` | *(baru)* `petugas.pesanan.index` | Faskes | Target: sama pola dengan daftar Dinkes, difilter `PesananPolicy::view` (tujuan Faskes ini atau ambulans Faskes ini), tanpa kolom aksi admin | **belum ada** | **P1** | R6 |
| `petugas/pesanan/show.blade.php` | `petugas.pesanan.show` | Faskes | lama: `x-ui.judul-halaman`, `x-ui.rincian`, `x-ui.linimasa`. Tidak ada aksi (Faskes hanya melihat) | lama | **P1** | R6 |
| `warga/pesanan/index.blade.php` | `warga.pesanan.index` | Masyarakat | lama: `x-ui.judul-halaman`, `x-ui.tabel`, `x-ui.paginasi`. Ini halaman "Riwayat pesanan" di cakupan P1. Target: pola daftar yang sama (tanpa kolom aksi Dinkes), tab bila berguna, tanpa `?q=` wajib (opsional) | lama | **P1** | R6 |
| `warga/pesanan/show.blade.php` | `warga.pesanan.show` | Masyarakat | lama: `x-ui.judul-halaman`, `x-ui.rincian`, `x-ui.linimasa`, `x-ui.dialog` (batalkan), peta (`components/peta/pelacak`). Target: panel detail sama pola, dialog batal | lama | **P1** | R6 |
| `warga/_tabel-pesanan.blade.php` | partial (dipakai `warga.pesanan.index`?) | Masyarakat | periksa saat R6: kemungkinan disatukan ke `x-ui.tabel` seperti partial admin | lama | **P1** | R6 |
| `warga/pesanan/buat.blade.php` | `warga.pesanan.buat` | Masyarakat | formulir "Buat pesanan" (`x-ui.opsi-kisi`, `x-ui.segmented`, `x-ui.isian`, peta pemilih). Sesuai 8.5 (acuan B2 "layar ponsel") | lama | **P3** (bukan P1 — dikecualikan sesuai instruksi) | R8 |
| `warga/pesanan/konfirmasi.blade.php` | `warga.pesanan.konfirmasi` | Masyarakat | ringkasan sebelum kirim, bagian dari alur buat pesanan (8.5) | lama | **P3** | R8 |
| `admin/ambulans/index.blade.php` | `admin.ambulans.index` | Dinkes | lama: `x-ui.tabel`, status aktif/nonaktif | lama | **P2** | R7 |
| `admin/ambulans/form.blade.php` | `admin.ambulans.{create,edit}` | Dinkes | lama: `x-ui.isian`, `x-ui.pilihan` | lama | **P2** | R7 |
| `admin/faskes/index.blade.php` | `admin.faskes.index` | Dinkes | lama: `x-ui.tabel` | lama | **P2** | R7 |
| `admin/faskes/form.blade.php` | `admin.faskes.{create,edit}` | Dinkes | lama: `x-ui.isian` | lama | **P2** | R7 |
| `admin/akun/index.blade.php` | `admin.petugas.index`, `admin.sopir.index` (satu view dipakai dua resource via `AkunFaskesController`) | Dinkes | lama: `x-ui.tabel` | lama | **P2** | R7 |
| `admin/akun/form.blade.php` | `admin.petugas.{create,edit}`, `admin.sopir.{create,edit}` | Dinkes | lama: `x-ui.isian`, `x-ui.pilihan` | lama | **P2** | R7 |
| `admin/ketersediaan/index.blade.php` | `admin.ketersediaan.index` | Dinkes | lama: formulir angka per Faskes. Sesuai 8.3 "Ketersediaan" | lama | **P2** | R7 |
| `petugas/ketersediaan/edit.blade.php` | `petugas.ketersediaan.edit` | Faskes | lama: `x-ui.ring-tile` (ringkasan) + formulir angka + `x-ui.peringatan` (terakhir diperbarui). Sesuai 8.3 | lama | **P2** | R7 |
| `admin/laporan/index.blade.php` | `admin.laporan.index` | Dinkes | lama: filter periode, tabel, tombol unduh PDF/CSV. **Tidak dirinci di RETEMA_B.md bagian 8** — disamakan dengan pola data master (panel + tabel + filter), diputuskan masuk P2 sebagai default karena bentuknya sama (lihat laporan akhir) | lama | **P2** (keputusan default, dicatat) | R7 |
| `auth/login.blade.php` | `login` | Tamu | lama: kartu masuk. Sesuai 5.3 "Masuk" | lama | **P3** | R7 |
| `auth/register.blade.php` | `register` | Tamu | lama: kartu daftar. **Tidak dirinci di RETEMA_B.md** (hanya "Masuk" yang disebut) — disamakan dengan pola kartu masuk 5.3 (keputusan default) | lama | **P3** | R7 |
| `publik/beranda.blade.php` | `beranda` | Tamu/publik | lama: bilah atas publik, CTA pesan ambulans, direktori Faskes. **Tidak dirinci di bagian 8** — disamakan dengan layout publik 5.3 (keputusan default) | lama | **P3** | R8 |
| `publik/faskes.blade.php` | `faskes.index`, `faskes.data` (JSON) | Tamu/publik | lama: `components/peta/faskes` (peta direktori). **Tidak dirinci di bagian 8** — layout publik 5.3 (keputusan default) | lama | **P3** | R8 |
| `petugas/dashboard.blade.php` | `petugas.dashboard` | Faskes | lama: ringkasan pesanan aktif, `x-ui.ring-tile`. Sesuai 8.6 | lama | **P4** | R8 |
| `sopir/dashboard.blade.php` | `sopir.dashboard` | Sopir | lama: kartu tugas aktif, riwayat selesai. Sesuai 8.7 | lama | **P4** | R8 |
| `dev/katalog-ui.blade.php` | `ui.katalog` (hanya local/testing/shot) | Dev | katalog semua `x-ui.*`. Diperbarui berkelanjutan setiap komponen baru/berubah sejak R2 | sudah (sampai komponen DB), akan diperbarui lagi di R6 saat `x-ui.tabel` berubah | **lintas langkah** — P5 memastikan cakupannya penuh di akhir | R2→R9 |
| `errors/403.blade.php` | handler 403 | semua | lama: halaman galat generik | lama | **P5** (sapuan konsistensi penutup) | R9 |
| `errors/404.blade.php` | handler 404 | semua | lama | lama | **P5** | R9 |
| `errors/419.blade.php` | handler 419 | semua | lama | lama | **P5** | R9 |
| `errors/500.blade.php` | handler 500 | semua | lama | lama | **P5** | R9 |
| `welcome.blade.php` | *(tidak dirute, tidak dipakai)* | — | bawaan Laravel, tidak direferensikan controller mana pun | tidak dipakai | **P5** (kandidat dihapus, sama seperti pembersihan token/komponen tak terpakai lain) | R9 |
| `pdf/laporan.blade.php` | dirender `admin.laporan.pdf` lewat DomPDF, bukan halaman browser | Dinkes (unduhan) | gaya cetak sendiri (bukan Tailwind/x-ui) | lama | **di luar cakupan visual arah B** (PDF, bukan halaman web) | — |

## Lapisan (bukan halaman, infrastruktur — sudah selesai R1 sampai R3)

`components/layouts/app.blade.php`, `dasar.blade.php`, `auth.blade.php`, `publik.blade.php`, `menu-app.blade.php`: kerangka bilah samping/atas/laci, sudah arah B (R3). Semua halaman di atas memakainya; tidak dipetakan sebagai halaman tersendiri.

## Catatan keputusan (konvensi yang diambil tanpa menanyakan, sesuai CLAUDE.md)

- Register, Laporan, Beranda publik, dan direktori Faskes publik tidak dirinci RETEMA_B.md bagian 8. Dipetakan ke langkah yang bentuknya paling mirip (kartu masuk → P3 bersama Masuk; Laporan → P2 bersama data master; beranda dan direktori publik → P3 bersama layout publik). Spesifikasi detail tiap halaman itu baru ditulis saat langkahnya (P2/P3) dikerjakan.
- Kartu "Pesanan terbaru" di `admin/dashboard.blade.php` (langkah "DB") menulis markup tabel+daftar-ponsel sendiri di luar komponen, dan `docs/PROGRESS.md` mencatat itu sebagai utang: bila ada halaman lain yang butuh pola baris pesanan yang sama, satukan dulu, jangan menulis ulang. R6 memenuhi itu dengan membuat satu partial baru `resources/views/pesanan/_baris.blade.php` (bukan komponen `x-ui.*` karena isinya spesifik kolom Pesanan — pasien/layanan/status/jam, bukan primitif desain umum) dipakai bersama oleh dasbor Dinkes, daftar Pesanan Dinkes, daftar Pesanan Faskes (baru), dan riwayat Pesanan Masyarakat. `admin/_tabel-pesanan.blade.php` (pola lama, kolom Kode terpisah) dan `warga/_tabel-pesanan.blade.php` ditinggalkan tanpa dihapus karena `warga/beranda.blade.php` (P4, belum dikerjakan) masih memakainya.
- "Ganti ambulans" (disebut di cakupan R6) tidak punya rute/aksi tersendiri di kode saat ini — mekanisme yang ada adalah "Lepas penugasan" (`admin.pesanan.lepas`, mengembalikan status ke Terverifikasi) diikuti "Tugaskan" ulang (`admin.pesanan.tugaskan`). R6 tidak menambah rute atau aturan alur baru (sesuai batas "hanya lapisan tampilan"); "ganti ambulans" direalisasikan sebagai pemakaian dua aksi yang sudah ada itu dengan label dan dialog yang menjelaskan maksudnya ("Ganti ambulans dan sopir?" pada dialog Lepas), bukan aksi baru.
- Daftar pesanan Faskes (`petugas.pesanan.index`) belum ada rutenya. Menambah satu rute GET baru (indeks, baca saja, memakai `PesananPolicy` yang sudah ada untuk membatasi baris) bukan perubahan alur bisnis atau aturan peran — hanya menyediakan halaman daftar yang memang diminta bagian 8.2 ("Daftar: Dinkes, Faskes") dan belum punya tempat sebelumnya (Faskes sekarang hanya melihat pesanan lewat tabel di dasbor).
