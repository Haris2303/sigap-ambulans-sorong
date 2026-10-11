# Asumsi dan keputusan

| Tanggal | Keputusan | Alasan | Perlu konfirmasi Dinkes |
|---|---|---|---|
| 2026-10-11 | Ikon Lucide `ambulance` tidak dipakai di kode/view; menu Ambulans memakai ikon `navigation`, "Pesan ambulans" memakai `plus`. | Pemindai `istilah-baku` menolak "ambulance" (juga sebagai nama ikon). | tidak |
| 2026-10-11 | Komentar pembuka `resources/css/tambahan.css` ditulis ulang tanpa kata `rgb()`/gradien. | Aturan `warna-css` memindai komentar juga. | tidak |
| 2026-10-11 | `config/sigap.php` menambah kunci `per_halaman` (20) dan `peta` (pusat Kota Sorong), serta kunci `cocok` dan `lencana` pada item menu. | Satu sumber untuk angka paginasi, pusat peta, dan penanda menu aktif. | tidak |
| 2026-10-11 | Pesan `auth.nonaktif` ditambahkan di `lang/id/auth.php`. | Pesan untuk akun `aktif=false`. | tidak |
