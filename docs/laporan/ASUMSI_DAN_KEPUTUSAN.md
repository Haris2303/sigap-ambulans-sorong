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
| 2026-10-11 | Rute JSON `warga.pesanan.status`, `admin.pesanan.ringkasan`, `sopir.penugasan` memakai envelope `{ok, data}` lewat trait `MembalasJson`; posisi sopir disembunyikan dari Masyarakat sebelum status `diterima`. | Konvensi JSON RANCANGAN §6 dan privasi sopir. | tidak |
| 2026-10-11 | Draf pesanan disimpan di session (`draf_pesanan`) antara `tinjau` dan `simpan`; pesanan baru dibuat saat `simpan`. | RANCANGAN §6 (tinjau → konfirmasi → simpan). | tidak |
| 2026-10-11 | Aksi sopir "selesai" diberi nama method `selesaikan` di controller (nama route tetap `sopir.pesanan.selesai`). | Aturan literal-status memindai nama method. | tidak |
| 2026-10-11 | Sopir tidak boleh offline atau dinonaktifkan saat memegang pesanan aktif. | Mencegah pesanan tanpa sopir. | tidak |
| 2026-10-11 | Pesanan demo (semua status, 7 hari) hanya dibuat di lingkungan `local`; sandi akun demo dari `SEED_PASSWORD`, bawaan `password` di `local`/`testing`, acak dan dicetak sekali di lingkungan lain. | Aturan keras: tidak menyimpan sandi nyata. | tidak |
| 2026-10-11 | Tidak ada penjadwalan ulang pesanan; pesanan yang ditolak sopir kembali ke `terverifikasi` untuk ditugaskan ulang. | RANCANGAN §5. | ya |
| 2026-10-11 | Ejaan nama Faskes dan plat nomor ambulans di seeder adalah data dummy dari wawancara. | Data resmi belum tersedia. | ya |
| 2026-10-11 | Urutan saran ambulans (darurat → `darurat`; rujukan/persalinan → `transport`; persalinan → Puskesmas dulu) hanya urutan, bukan larangan. | RANCANGAN §7. | ya |
| 2026-10-11 | `nama_pasien` dan `no_telepon_kontak` wajib pada pesanan; kontak diisi awal dari profil. | RANCANGAN §4 tanpa aturan wajib eksplisit. | ya |
| 2026-10-11 | Leaflet dan Routing Machine dipasang dari npm (versi terkunci); rute memakai server demo OSRM publik. Tailwind lewat build Vite, bukan CDN. | Tidak ada jaringan saat build agen; OSRM demo tidak untuk produksi. | ya (server rute produksi) |
| 2026-10-11 | `APP_NAME` di `.env.example` diubah menjadi "SIGAP Ambulans". | Temuan audit #20. | tidak |
