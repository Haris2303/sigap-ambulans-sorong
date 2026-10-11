# Peta layar

Layar → route → view → peran. JSON dan aksi tanpa view ditandai "—".

| Layar | Route | View | Peran |
|---|---|---|---|
| Landing | `beranda` | `publik/beranda` | Publik |
| Info Faskes | `faskes.index` | `publik/faskes` | Publik |
| Data Faskes (JSON) | `faskes.data` | — | Publik |
| Masuk | `login` | `auth/login` | Tamu |
| Daftar | `register` | `auth/register` | Tamu |
| Beranda Masyarakat | `warga.beranda` | `warga/beranda` | Masyarakat |
| Riwayat pesanan | `warga.pesanan.index` | `warga/pesanan/index` | Masyarakat |
| Buat pesanan | `warga.pesanan.buat` | `warga/pesanan/buat` | Masyarakat |
| Konfirmasi pesanan | `warga.pesanan.konfirmasi` | `warga/pesanan/konfirmasi` | Masyarakat |
| Lacak pesanan | `warga.pesanan.show` | `warga/pesanan/show` | Masyarakat |
| Status pesanan (JSON) | `warga.pesanan.status` | — | Masyarakat |
| Dasbor Dinkes | `admin.dashboard` | `admin/dashboard` | Dinkes |
| Daftar pesanan | `admin.pesanan.index` | `admin/pesanan/index` | Dinkes |
| Detail pesanan dan aksi | `admin.pesanan.show` | `admin/pesanan/show` | Dinkes |
| Ringkasan (JSON) | `admin.pesanan.ringkasan` | — | Dinkes |
| Master Faskes | `admin.faskes.index`, `.create`, `.edit` | `admin/faskes/index`, `admin/faskes/form` | Dinkes |
| Master Ambulans | `admin.ambulans.index`, `.create`, `.edit` | `admin/ambulans/index`, `admin/ambulans/form` | Dinkes |
| Master Sopir | `admin.sopir.index`, `.create`, `.edit` | `admin/akun/index`, `admin/akun/form` | Dinkes |
| Master Petugas Faskes | `admin.petugas.index`, `.create`, `.edit` | `admin/akun/index`, `admin/akun/form` | Dinkes |
| Ketersediaan | `admin.ketersediaan.index` | `admin/ketersediaan/index` | Dinkes |
| Laporan | `admin.laporan.index` | `admin/laporan/index` | Dinkes |
| Laporan PDF / CSV | `admin.laporan.pdf`, `admin.laporan.csv` | `pdf/laporan` / — | Dinkes |
| Dasbor Petugas Faskes | `petugas.dashboard` | `petugas/dashboard` | Petugas Faskes |
| Detail pesanan Faskes | `petugas.pesanan.show` | `petugas/pesanan/show` | Petugas Faskes |
| Ketersediaan Faskes | `petugas.ketersediaan.edit` | `petugas/ketersediaan/edit` | Petugas Faskes |
| Dasbor Sopir | `sopir.dashboard` | `sopir/dashboard` | Sopir |
| Penugasan (JSON) | `sopir.penugasan` | — | Sopir |
| Katalog UI (hanya local/testing) | `ui.katalog` | `dev/katalog-ui` | Pengembang |

Catatan layar setelah migrasi tampilan Material 3:

- `admin.dashboard` menerima `?tab=semua|perlu-tindakan|berjalan` (tab kartu Pesanan terbaru). `admin.pesanan.index` menerima `?tab=` yang sama, `?q=` (kode atau nama pasien), `status`, `jenis_layanan`, `dari`, dan `sampai`.
- Semua layar setelah masuk memakai `layouts/app` (laci navigasi dan bilah atas); layar publik memakai `layouts/publik`; Masuk dan Daftar memakai `layouts/auth`.
- `admin.pesanan.ringkasan` (JSON) kini juga memuat angka ketersediaan untuk pembaruan otomatis kartu dasbor.
- Katalog UI (`/_ui`) memuat semua komponen termasuk kartu statistik, chip ikon, tab, avatar, dan grafik.
