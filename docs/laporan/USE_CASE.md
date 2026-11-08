# Use case

```mermaid
flowchart LR
    Publik([Publik])
    Warga([Masyarakat])
    Admin([Dinkes])
    Petugas([Petugas Faskes])
    Sopir([Sopir])

    Publik --- UC1(Melihat info Faskes)
    Publik --- UC2(Mendaftar)
    Publik --- UC3(Masuk)

    Warga --- UC4(Membuat dan mengonfirmasi pesanan)
    Warga --- UC5(Melacak pesanan)
    Warga --- UC6(Membatalkan pesanan)
    Warga --- UC7(Melihat riwayat)

    Admin --- UC8(Memverifikasi atau menolak pesanan)
    Admin --- UC9(Menugaskan, melepas, membatalkan)
    Admin --- UC10(Mengelola master data)
    Admin --- UC11(Memantau ketersediaan)
    Admin --- UC12(Laporan PDF dan CSV)

    Petugas --- UC13(Melihat pesanan Faskes)
    Petugas --- UC14(Memperbarui ketersediaan)
    Petugas --- UC15(Mengubah status ambulans)

    Sopir --- UC16(Online dan kirim lokasi)
    Sopir --- UC17(Terima atau tolak penugasan)
    Sopir --- UC18(Berangkat dan selesai)
```

| Use case | Aktor | Prasyarat | Hasil | Kebutuhan |
|---|---|---|---|---|
| Membuat dan mengonfirmasi pesanan | Masyarakat | Masuk sebagai Masyarakat | Pesanan `menunggu` dengan kode `SGP-…` | FR-06, FR-07 |
| Melacak pesanan | Masyarakat | Pemilik pesanan | Linimasa dan posisi sopir | FR-08 |
| Membatalkan pesanan | Masyarakat | Status `menunggu`/`terverifikasi` | `dibatalkan` | FR-09 |
| Memverifikasi atau menolak | Dinkes | Status `menunggu` | `terverifikasi` / `ditolak` | FR-11, FR-12 |
| Menugaskan | Dinkes | Status `terverifikasi`, ambulans tersedia, sopir online dari Faskes sama | `ditugaskan` | FR-13 |
| Melepas atau membatalkan | Dinkes | Status memegang sumber daya | `terverifikasi` / `dibatalkan` | FR-14 |
| Mengelola master | Dinkes | — | Data Faskes/Ambulans/Sopir/Petugas | FR-15 |
| Memperbarui ketersediaan | Petugas Faskes | Terhubung ke Faskes | Angka dan stempel waktu baru | FR-19 |
| Menerima atau menolak penugasan | Sopir | Status `ditugaskan` kepadanya | `diterima` / `terverifikasi` | FR-23 |
| Berangkat dan selesai | Sopir | Status `diterima` / `dalam_perjalanan` | `dalam_perjalanan` / `selesai` | FR-23 |
