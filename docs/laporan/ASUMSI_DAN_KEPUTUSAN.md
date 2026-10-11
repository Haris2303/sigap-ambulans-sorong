# Asumsi dan keputusan

| Tanggal | Keputusan | Alasan | Perlu konfirmasi Dinkes |
|---|---|---|---|
| 2026-10-11 | Ikon Lucide `ambulance` tidak dipakai di kode/view; menu Ambulans memakai ikon `navigation`, "Pesan ambulans" memakai `plus`. | Pemindai `istilah-baku` menolak "ambulance" (juga sebagai nama ikon). | tidak |
| 2026-10-11 | Komentar pembuka `resources/css/tambahan.css` ditulis ulang tanpa kata `rgb()`/gradien. | Aturan `warna-css` memindai komentar juga. | tidak |
| 2026-10-11 | `config/sigap.php` menambah kunci `per_halaman` (20) dan `peta` (pusat Kota Sorong), serta kunci `cocok` dan `lencana` pada item menu. | Satu sumber untuk angka paginasi, pusat peta, dan penanda menu aktif. | tidak |
| 2026-10-11 | Pesan `auth.nonaktif` ditambahkan di `lang/id/auth.php`. | Pesan untuk akun `aktif=false`. | tidak |
| 2026-10-11 | Kasus enum jenis faskes rumah sakit bernama `JenisFaskes::Rs` (nilai `rumah_sakit`). | Aturan `istilah-baku` menolak "rumahsakit" tanpa membedakan huruf besar kecil. | tidak |
| 2026-10-11 | Route `ui.katalog` (GET `/_ui`, hanya local/testing) dan `KatalogUiController` ditambahkan; halaman galat 403/404/419/500 memakai `x-layouts.auth` (tanpa query autentikasi agar aman saat galat 500). Prop `galat` ditambahkan pada `ui.isian`, `ui.pilihan`, `ui.area-teks` untuk menampilkan keadaan galat di katalog. Konfigurasi URL ubin/Nominatim/OSRM ada di `config/sigap.php` (peta). | KIT_UI bagian 8 mewajibkan katalog; tidak ada route di tabel RANCANGAN untuknya. | tidak |
| 2026-10-11 | Laporan: waktu respons = pesanan dibuat sampai ditugaskan; durasi layanan = ditugaskan sampai selesai; periode dihitung dari tanggal pesanan dibuat (bawaan 30 hari terakhir); rekap per Faskes memakai Faskes asal ambulans. CSV memakai BOM UTF-8 dan awalan tanda petik pada sel berawalan = + - @. | RANCANGAN bagian 7 tidak merinci definisi ukuran. | ya (definisi respons dan durasi) |
