# Audit UX dan visual SIGAP Ambulans

Tanggal 2026-10-11, branch `material-design`, setelah commit `c279435` (D3b). Dokumen ini hanya audit: tidak ada berkas aplikasi yang diubah.

## 1. Ringkasan

Fondasinya sehat: token dan komponen konsisten, label dan pesan seragam, galat sebaris jelas, data lama ditandai, aksi merusak meminta konfirmasi, dan dasbor Dinkes sudah mengikuti referensi. Masalahnya bukan gaya, melainkan kerja di lapangan. Antarmuka masih terasa seperti aplikasi data di desktop, padahal Masyarakat dan sopir hampir pasti memakai ponsel, dan keduanya dalam keadaan terburu-buru.

Pola yang berulang:

1. **Informasi dan tombol terpenting berada paling bawah di ponsel.** Tombol Verifikasi, tombol Terima milik sopir, dan status pesanan bagi Masyarakat semuanya di dasar halaman (T2, T4, T27). Lencana Gawat bahkan hilang dari daftar Dinkes di ponsel (T1).
2. **Aplikasi diam saat sesuatu terjadi.** Tidak ada status memuat (T3), tidak ada bunyi atau pemberitahuan pesanan baru (T23), halaman memuat ulang sendiri tanpa tanda (T24), dan GPS sopir bisa mati tanpa ada yang tahu (T25).
3. **Keputusan yang paling berharga belum dibantu.** Memilih ambulans tidak menampilkan jarak (T22), waktu tunggu tidak terlihat (T7), kapasitas Faskes tidak bertanda Penuh (T16).
4. **Jalur darurat hanya satu.** Tidak ada nomor telepon cadangan (T8), pendaftaran wajib (T30), dan seluruh peta bergantung pada layanan publik gratis (T29).

Delapan temuan pertama (T1 sampai T8) berdampak tinggi dan hanya berusaha kecil; semuanya dapat selesai dalam sekitar dua hari kerja. Dua temuan berikutnya yang paling menentukan mutu layanan adalah T22 (penugasan dengan jarak) dan T23 (pemberitahuan pesanan baru). Tiga usulan ciri khas ada di bagian 6.

Tidak satu pun usulan memerlukan token baru (warna, tipografi, sudut). Perubahan pada `DESIGN.md` hanya berupa tambahan entri komponen dan keadaan (bagian 7).

## 2. Cakupan, metode, dan keterbatasan

- **Dibaca:** `DESIGN.md`, `docs/DESAIN_DASBOR_MATERIAL.md`, `docs/KIT_UI.md`, `docs/referensi/` (dua tangkapan layar dan sumber HTML), seluruh 74 berkas view dan komponen Blade, `resources/js/sigap.js`, `peta.js`, `grafik-opsi.js`, `config/sigap.php`, `routes/web.php`, controller penugasan Dinkes, controller pesanan Masyarakat, dan keluaran `npm run build`.
- **Tidak dilakukan:** tidak ada browser di sesi ini, jadi tidak ada pengujian visual atau pada perangkat, dan tidak ada uji pengguna. Satu-satunya bukti visual adalah dua tangkapan referensi (dasbor Dinkes 1440px dan 390px).
- **Dasar tiap temuan:** *Kode* berarti terbukti dari sumber (sertakan berkas dan baris). *Referensi* berarti terlihat di tangkapan layar referensi. *Dugaan* berarti kesimpulan dari kode yang perlu dicoba di perangkat sebelum dijadikan keputusan.
- **Hitungan ketukan** dihitung dari kode, bukan diukur.
- **Dampak:** Tinggi (memengaruhi kecepatan atau keselamatan layanan, atau banyak pengguna di setiap pemakaian), Sedang (menambah gesekan atau risiko salah), Rendah (kerapian dan kenyamanan).
- **Usaha:** Kecil (sampai sekitar setengah hari), Sedang (satu sampai tiga hari), Besar (lebih dari tiga hari atau menunggu keputusan di luar kode).
- **Urutan temuan:** rasio dampak terhadap usaha (Tinggi 3, Sedang 2, Rendah 1, dibagi Kecil 1, Sedang 2, Besar 3), lalu dampak yang lebih tinggi lebih dulu.

## 3. Penilaian per alur utama

Nilai: **Baik**, **Cukup**, **Lemah**. Angka dalam kurung mengacu ke temuan di bagian 5.

### 3.1 Buat pesanan (Masyarakat)

Hitungan: sudah masuk, sekitar 5 sampai 6 ketukan dan satu isian wajib (nama pasien) dalam tiga kali muat halaman (formulir, konfirmasi, detail). Tamu baru: sekitar 9 ketukan dan 6 isian teks, karena harus mendaftar lebih dulu.

| Aspek | Nilai | Catatan |
|---|---|---|
| Langkah dan klik | Cukup | Lokasi tidak otomatis (ketuk "Gunakan lokasi saya" lalu izin). Lima kartu setara, termasuk Faskes tujuan yang jarang perlu (T28, T30). |
| Umpan balik | Cukup | Snackbar sesudah kirim bagus ("Pesanan berhasil dikirim. Dinkes akan memverifikasi pesanan Anda."). Tombol Kirim tanpa status memuat dan tanpa pelindung klik ganda (T3). |
| Kosong, galat, data lama | Cukup | Pesan GPS dan pencarian alamat gagal jelas dan memberi jalan keluar (isi manual). Galat tampil dua kali (T10). Sesi habis membuang isian (T13). |
| Ponsel dan target sentuh | Cukup | Isian 56px dan tombol kirim lekat di dasar sudah benar. Halaman panjang, peta setinggi 256px di dalam gulir halaman (T28). |
| Keterbacaan dan hierarki | Cukup | Judul bagian jelas, tetapi lima kartu tampak sama penting (T28). |
| Bahasa | Cukup | Deskripsi pilihan ("Kecelakaan, sesak napas...") sangat baik. "Darurat" muncul dua kali (T20). |
| Keyboard dan fokus | Cukup | Label terhubung, fieldset dan legend benar, Enter pada pencarian peta tidak mengirim formulir. Legend "Faskes tujuan" sama dengan labelnya (T28). |
| Kecepatan terasa | Cukup | Peta 113 kB gzip dimuat di halaman paling kritis dan bergantung pada tiga layanan luar (T34, T29). |

### 3.2 Verifikasi (Dinkes)

Hitungan: dari dasbor 3 ketukan (Verifikasi pesanan, kode pesanan, Verifikasi) dalam empat kali muat halaman, ditambah gulir panjang di ponsel.

| Aspek | Nilai | Catatan |
|---|---|---|
| Langkah dan klik | Cukup | Jalur ke satu pesanan pendek, tetapi tidak ada langkah berikutnya sesudahnya (T17). |
| Umpan balik | Cukup | Pesan hasil sudah konsisten ("Pesanan berhasil diverifikasi."). Pesanan baru tidak diumumkan (T23), tanpa status memuat (T3). |
| Kosong, galat, data lama | Cukup | Keadaan kosong tidak menyebut langkah berikutnya (T37). Pembaruan terputus tidak terlihat (T23). |
| Ponsel dan target sentuh | Lemah | Gawat tidak tampak di daftar (T1), tombol Verifikasi di dasar halaman (T2), hanya teks kode yang bisa diketuk (T12). |
| Keterbacaan dan hierarki | Cukup | Urutan prioritas dari server sudah tepat. Waktu tunggu tidak terlihat (T7) dan bahan keputusan tersebar (T18). |
| Bahasa | Baik | Nama aksi sama di tombol dan pesan, alasan penolakan wajib. |
| Keyboard dan fokus | Cukup | Tab dan tautan benar. Laci ponsel tidak terkunci (T11). |
| Kecepatan terasa | Cukup | Dasbor 7 query dan polling 10 detik sudah baik. Tabel dan grafik tidak ikut diperbarui (T31). |

### 3.3 Tugaskan ambulans dan sopir (Dinkes)

Hitungan: sesudah verifikasi 5 ketukan (ambulans: buka dan pilih; sopir: buka dan pilih; Tugaskan). Dari daftar 7 ketukan dan empat kali muat halaman.

| Aspek | Nilai | Catatan |
|---|---|---|
| Langkah dan klik | Lemah | Dua pilihan terpisah yang saling terkait; pasangan keliru baru ketahuan setelah kirim (T22). |
| Umpan balik | Cukup | Pesan sukses jelas. Galat Faskes berbeda hanya muncul sesudah submit (T22). |
| Kosong, galat, data lama | Baik | Peringatan "Tidak ada ambulans yang tersedia." dan "Tidak ada sopir online yang bebas." sudah ada, tetapi tanpa langkah berikutnya (T37). |
| Ponsel dan target sentuh | Cukup | Pilihan bawaan 56px nyaman, tetapi panelnya jauh di bawah (T2). |
| Keterbacaan dan hierarki | Lemah | Pilihan dikelompokkan menurut nama Faskes, tanpa jarak ke lokasi jemput (T22). |
| Bahasa | Baik | Bantuan "Dikelompokkan per Faskes. Yang disarankan tampil lebih dulu." jelas. |
| Keyboard dan fokus | Baik | Elemen asli, label terhubung. |
| Kecepatan terasa | Cukup | Satu kali kirim, tetapi keputusan lambat karena harus membandingkan sendiri (T22). |

### 3.4 Perbarui ketersediaan (Petugas Faskes dan Dinkes)

Hitungan: Petugas 3 ketukan ditambah mengetik angka (menu, ketuk isian, Simpan) dalam dua kali muat halaman. Dinkes per baris, dengan enam isian angka dalam satu baris tabel.

| Aspek | Nilai | Catatan |
|---|---|---|
| Langkah dan klik | Cukup | Jumlah ketukan wajar, tetapi mengubah satu angka berarti menghapus dan mengetik ulang (T26). |
| Umpan balik | Cukup | Snackbar menyebut nama Faskes. Sesudah Simpan halaman Dinkes kembali ke atas (T21). |
| Kosong, galat, data lama | Baik | Lencana "Data lama" dan "Belum diperbarui" sudah ada dan jelas. Isian tidak punya batas maksimum menurut total (T26). |
| Ponsel dan target sentuh | Lemah | Tabel tujuh kolom dengan isian lebar tetap di ponsel (T32). Formulir Petugas sudah satu kolom di ponsel. |
| Keterbacaan dan hierarki | Cukup | Label tersedia dan total jelas. "1/3" di tempat lain tanpa satuan (T16). |
| Bahasa | Baik | "Bed IGD tersedia" dan "Total bed IGD" tidak ambigu. |
| Keyboard dan fokus | Baik | Label tersembunyi per isian dan atribut `form` pada tombol Simpan sudah rapi. |
| Kecepatan terasa | Cukup | Satu halaman, satu simpan. |

### 3.5 Tugas sopir

Hitungan: Online (1) lalu dua izin sistem (lokasi, notifikasi), menunggu, Terima (1), Mulai perjalanan (1), Selesai (1) dan Selesai di dialog (1). Lima ketukan aplikasi, empat kali muat halaman, tanpa tombol navigasi.

| Aspek | Nilai | Catatan |
|---|---|---|
| Langkah dan klik | Cukup | Jumlah ketukan sudah minimal, tetapi tombolnya jauh di bawah (T4). |
| Umpan balik | Lemah | Penugasan baru memuat ulang halaman tanpa tanda dan bunyi (T24). GPS gagal tidak terlihat (T25). Tanpa status memuat (T3). |
| Kosong, galat, data lama | Baik | "Anda offline. Tekan Online untuk menerima penugasan." dan "Belum ada penugasan. Halaman ini diperbarui otomatis." sudah menyebut langkah berikutnya. |
| Ponsel dan target sentuh | Cukup | Tombol 48px selebar layar sudah tepat. Telepon pasien hanya teks bergaris bawah (T19), tidak ada navigasi (T5). |
| Keterbacaan dan hierarki | Cukup | Lokasi jemput sudah berhuruf besar dan Gawat bertanda. Tindakan tersembunyi di dasar (T4). |
| Bahasa | Cukup | Lencana "Online" dan tombol "Offline" membingungkan (T9). "Terima" dan "Tolak" tanpa objek. |
| Keyboard dan fokus | Cukup | Dialog bawaan benar. Reload otomatis dapat menghapus alasan yang sedang diketik (T24). |
| Kecepatan terasa | Cukup | Polling 8 detik wajar, tetapi setiap perubahan memuat ulang penuh. |

## 4. Penilaian per peran

| Peran | Kekuatan | Kelemahan utama | Temuan yang paling berarti |
|---|---|---|---|
| Masyarakat | Formulir jelas dan berdeskripsi, tombol kirim lekat, pesan sukses faktual, pembatalan berkonfirmasi. | Tanpa nomor darurat, lima kartu setara, status dan ETA tidak menonjol, halaman memuat ulang sendiri, harus mendaftar dulu. | T8, T3, T28, T27, T24, T30 |
| Operator Dinkes | Dasbor sesuai referensi, daftar terurut prioritas dari server, tab dan pencarian, kesegaran data terlihat. | Gawat dan tombol aksi tersembunyi di ponsel, waktu tunggu tidak tampak, penugasan tanpa jarak, tidak diberi tahu pesanan baru. | T1, T2, T6, T7, T22, T23 |
| Petugas Faskes | Dasbor ringkas, lencana kesegaran data, alur satu halaman. | Mengetik angka setiap kali, dasbor tanpa pembaruan otomatis (hanya Dinkes yang memakai polling), telepon berupa teks biasa. | T26, T19, T16, T23 |
| Sopir | Kartu tunggal mobile-first, tombol 48px selebar layar, keadaan kosong yang menyebut langkah berikutnya, konfirmasi Selesai. | Tombol tindakan di dasar, tanpa navigasi, reload otomatis, GPS tanpa indikator, tombol Online ambigu. | T4, T5, T9, T24, T25 |

## 5. Temuan

Rangkuman, diurutkan menurut rasio dampak terhadap usaha. Rincian tiap temuan ada di bawah tabel.

| No | Temuan | Dampak | Usaha | Ubah DESIGN.md |
|---|---|---|---|---|
| T1 | Gawat tidak terlihat di daftar pesanan Dinkes di ponsel | Tinggi | Kecil | Tidak |
| T2 | Panel aksi pesanan Dinkes di dasar halaman ponsel | Tinggi | Kecil | Tidak |
| T3 | Tidak ada status memuat dan pelindung klik ganda | Tinggi | Kecil | Ya (Interaction states) |
| T4 | Tombol tindakan sopir di dasar kartu | Tinggi | Kecil | Tidak |
| T5 | Tugas sopir tanpa tombol navigasi | Tinggi | Kecil | Tidak |
| T6 | Dasbor Dinkes tidak menonjolkan pesanan Gawat | Tinggi | Kecil | Tidak |
| T7 | Waktu tunggu pesanan tidak terlihat | Tinggi | Kecil | Tidak (Ya bila jadi komponen, lihat C1) |
| T8 | Tidak ada nomor telepon darurat Dinkes | Tinggi | Kecil | Tidak |
| T9 | Tombol Online sopir menampilkan lawan keadaan | Sedang | Kecil | Tidak |
| T10 | Ringkasan galat tidak terhubung ke isian | Sedang | Kecil | Tidak |
| T11 | Laci modal di ponsel tidak terkunci | Sedang | Kecil | Tidak |
| T12 | Baris tabel hanya bisa diketuk di teks kodenya | Sedang | Kecil | Tidak |
| T13 | Halaman Sesi berakhir salah arah | Sedang | Kecil | Tidak |
| T14 | Grafik batang di ponsel menyembunyikan hari ini | Sedang | Kecil | Tidak |
| T15 | Dasbor ponsel menaruh pesanan terbaru paling bawah | Sedang | Kecil | Tidak |
| T16 | Kapasitas Faskes tanpa tanda Penuh atau Menipis | Sedang | Kecil | Tidak |
| T17 | Tidak ada langkah berikutnya sesudah Verifikasi | Sedang | Kecil | Tidak |
| T18 | Kepala detail pesanan Dinkes tidak merangkum bahan keputusan | Sedang | Kecil | Tidak |
| T19 | Nomor telepon tidak seragam sebagai tombol | Sedang | Kecil | Tidak |
| T20 | "Darurat" muncul dua kali di Buat Pesanan | Sedang | Kecil | Tidak (istilah baku: ya) |
| T21 | Ketersediaan Dinkes kembali ke atas sesudah Simpan | Sedang | Kecil | Tidak |
| T22 | Penugasan tanpa jarak dan pasangan ambulans-sopir | Tinggi | Sedang | Tidak |
| T23 | Dinkes tidak diberi tahu pesanan baru dan pembaruan terputus | Tinggi | Sedang | Ya (Interaction states) |
| T24 | Halaman memuat ulang sendiri saat status berubah | Tinggi | Sedang | Tidak |
| T25 | Sopir tidak tahu GPS-nya terkirim atau tidak | Tinggi | Sedang | Tidak |
| T26 | Petugas harus mengetik angka ketersediaan | Tinggi | Sedang | Ya (Stepper numerik) |
| T27 | Pelacakan Masyarakat tanpa status dan ETA di atas | Tinggi | Sedang | Tidak (Ya bila jalur status, C2) |
| T28 | Formulir Buat Pesanan terlalu banyak bagian | Tinggi | Sedang | Tidak |
| T29 | Layanan peta publik gratis sebagai titik gagal | Tinggi | Besar | Tidak |
| T30 | Pendaftaran wajib dan tombol Pesan ambulans bagi tamu | Tinggi | Besar | Tidak |
| T31 | Angka dasbor hidup, tabel dan grafik basi | Sedang | Sedang | Tidak |
| T32 | Tabel ketersediaan Dinkes di ponsel | Sedang | Sedang | Tergantung T26 |
| T33 | Aksi merusak sebagai panel penuh dengan kolom alasan | Sedang | Sedang | Ya (Dialog berisi isian) |
| T34 | Bundel peta berat di halaman Buat Pesanan | Sedang | Sedang | Tidak |
| T35 | Beranda Masyarakat tidak berpusat pada pesanan aktif | Sedang | Sedang | Tidak |
| T36 | Tombol filter berlabel berbeda | Rendah | Kecil | Tidak |
| T37 | Keadaan kosong tanpa langkah berikutnya | Rendah | Kecil | Tidak |
| T38 | Kolom cari di bilah atas terpotong di ponsel | Rendah | Kecil | Tidak |
| T39 | Snackbar tanpa batas waktu menutupi isi | Rendah | Kecil | Ya (Snackbar) |
| T40 | Panel filter pesanan Dinkes selalu terbuka | Rendah | Kecil | Tidak |
| T41 | Tiga kartu statistik putih satu kolom di ponsel | Rendah | Kecil | Tidak |
| T42 | Info Faskes: filter butuh tombol, tanpa rute | Rendah | Kecil | Tidak |
| T43 | Waktu tanpa zona | Rendah | Kecil | Tidak |
| T44 | Perpindahan halaman terasa lambat | Rendah | Kecil | Ya (Motion) |
| T45 | Tidak ada lupa kata sandi, ganti kata sandi, atau profil | Sedang | Besar | Ya (Menu avatar) |

### T1. Gawat tidak terlihat di daftar pesanan Dinkes di ponsel

- **Halaman:** `admin/pesanan/index` (`admin/_tabel-pesanan.blade.php:6` dan `:14`). **Dasar:** kode.
- **Masalah:** Lencana "Gawat" berada di kolom Layanan, dan kolom itu disembunyikan di bawah 768px (`hidden md:table-cell`). Daftar sudah mengurutkan pesanan Gawat paling atas, tetapi operator di ponsel tidak melihat alasannya. Tabel di dasbor (`admin/dashboard.blade.php:91`) menampilkannya karena kolom itu tidak disembunyikan.
- **Usulan:** Pindahkan lencana Gawat ke sel Kode (di bawah kode, bersama usia pesanan dari T7) supaya selalu terlihat. Kolom Layanan boleh tetap tersembunyi di ponsel. Pakai pola yang sama pada dasbor Petugas.
- **Dampak:** Tinggi. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T2. Panel aksi pesanan Dinkes di dasar halaman ponsel

- **Halaman:** `admin/pesanan/show.blade.php:9-10` dan `:69`; pola yang sama pada `warga/pesanan/show` (Batalkan). **Dasar:** kode.
- **Masalah:** Tata letak `lg:grid-cols-3` menaruh kolom aksi (Verifikasi, Tolak, Tugaskan, Lepas, Batalkan) setelah lima bagian rincian, peta, Faskes, dan linimasa. Di ponsel operator menggulir beberapa layar sebelum menemukan tombol Verifikasi.
- **Usulan:** Di bawah `lg`, tampilkan panel aksi langsung setelah ringkasan (T18) dengan `order-first`, atau pakai bilah aksi lekat di dasar layar berisi satu tombol utama. Polanya sudah dipakai di Buat Pesanan (`sticky bottom-0`); catat sebagai pola di `docs/KIT_UI.md`.
- **Dampak:** Tinggi. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T3. Tidak ada status memuat dan pelindung klik ganda

- **Halaman:** semua formulir; paling penting Kirim pesanan (`warga/pesanan/konfirmasi.blade.php:16-19`) dan Tugaskan. **Dasar:** kode, dan dugaan untuk duplikasi.
- **Masalah:** `sigap.js` tidak menangani `submit`. Tombol punya gaya `disabled`, tetapi tidak pernah dinonaktifkan atau diberi `aria-busy`, padahal `DESIGN.md` (Do's) menyebut keadaan "memuat" sebagai keadaan nyata. Di jaringan lambat pengguna mengetuk lagi. Pada `Warga\PesananController::simpan` draf dihapus setelah sukses, sehingga ketukan kedua yang datang sesudahnya menampilkan galat menyesatkan "Isi data pesanan terlebih dahulu." padahal pesanan sudah terkirim. Dua permintaan yang tiba hampir bersamaan dapat membaca draf yang sama dan membuat dua pesanan (dugaan, belum diuji).
- **Usulan:** Satu pendengar `submit` global yang menonaktifkan tombol, mengganti label menjadi kata kerja proses ("Mengirim..."), dan mengatur `aria-busy`. Tanpa spinner berputar, agar sesuai larangan animasi berulang di `DESIGN.md`. Tambahan (usaha sedang): jadikan `simpan` idempoten dengan kunci per draf, dan bila draf sudah hilang tetapi Masyarakat baru saja membuat pesanan, arahkan ke pesanan itu.
- **Dampak:** Tinggi. **Usaha:** Kecil (bagian JavaScript). **DESIGN.md:** Ya, tambah keadaan "memuat" pada Interaction states.

### T4. Tombol tindakan sopir di dasar kartu

- **Halaman:** `sopir/dashboard.blade.php:65-88`. **Dasar:** kode.
- **Masalah:** Urutannya status, lokasi, lima baris rincian, peta, baru Tindakan. Terima, Mulai perjalanan, dan Selesai adalah satu-satunya hal yang dilakukan sopir, sering dengan satu tangan. Di bawah "Terima" ada kolom alasan "Tolak" yang terbuka dan dapat tersentuh keliru.
- **Usulan:** Di bawah `lg`, pakai bilah tindakan lekat di dasar layar dengan satu tombol utama 48px selebar layar. "Tolak" menjadi tombol teks yang membuka dialog berisi alasan (T33). Taruh lokasi jemput, tombol navigasi (T5), dan telepon (T19) di bagian atas kartu.
- **Dampak:** Tinggi. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T5. Tugas sopir tanpa tombol navigasi

- **Halaman:** `sopir/dashboard.blade.php:44-63`. **Dasar:** kode; risiko latar belakang adalah dugaan.
- **Masalah:** Sopir melihat alamat teks dan peta kecil dengan garis rute dari posisinya, tanpa petunjuk belok. Untuk menyetir ia harus menyalin alamat ke aplikasi peta.
- **Usulan:** Tombol sekunder 48px "Buka navigasi" yang membuka URI `geo:lat,lng` (cadangan: tautan peta web); koordinat sudah ada (`lat_jemput`, `lng_jemput`). Bila titik tidak ada, pakai alamat teks. **Peringatan:** bila aplikasi peta lain dibuka, halaman SIGAP berpindah ke latar belakang dan pengiriman GPS serta polling dapat berhenti, jadi pasangkan dengan T25 (Wake Lock dan pesan). Tautan keluar juga melampaui catatan di `config/sigap.php:22` bahwa layanan luar hanya OSM, Nominatim, dan OSRM; perlu persetujuan pemilik.
- **Dampak:** Tinggi. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T6. Dasbor Dinkes tidak menonjolkan pesanan Gawat

- **Halaman:** `admin/dashboard.blade.php:11-16`, `App\Services\RingkasanDasbor`. **Dasar:** kode dan referensi.
- **Masalah:** Kartu utama berbunyi "2 pesanan baru" tanpa membedakan Gawat. Lencana Gawat baru terlihat di tabel Pesanan terbaru, paling bawah.
- **Usulan:** Tambahkan jumlah Gawat pada keterangan kartu utama ("pesanan baru, 1 gawat") dan buat tombol "Verifikasi pesanan" menuju pesanan Gawat tertua. Angkanya ikut polling lewat kunci baru di `RingkasanDinkes`.
- **Dampak:** Tinggi. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T7. Waktu tunggu pesanan tidak terlihat

- **Halaman:** daftar pesanan, dasbor, detail pesanan Dinkes (`admin/_tabel-pesanan.blade.php:8` dan `:16`). **Dasar:** kode.
- **Masalah:** Ukuran kinerja yang dilaporkan Laporan (waktu respons) tidak tampak saat bekerja. "Dibuat 11 Okt 2026, 16:14" memaksa operator menghitung sendiri, dan kolom itu tidak ada di ponsel.
- **Usulan:** Tampilkan usia pesanan ("12 mnt") di sel Kode atau Status untuk status Menunggu dan Terverifikasi, dengan nada naik bertahap (netral, peringatan, bahaya) menurut ambang di `config/sigap.php`. Versi lengkapnya adalah ciri khas C1.
- **Dampak:** Tinggi. **Usaha:** Kecil. **DESIGN.md:** Tidak untuk versi teks; Ya bila dijadikan komponen (C1).

### T8. Tidak ada nomor telepon darurat Dinkes

- **Halaman:** `publik/beranda`, `errors/*`, `warga/pesanan/buat`, `warga/pesanan/show`. **Dasar:** kode.
- **Masalah:** Aplikasi adalah satu-satunya jalur memesan. Bila internet atau GPS gagal, atau muncul galat 500, tidak ada jalur cadangan. Halaman 500 menyuruh "hubungi Dinkes" tanpa nomor.
- **Usulan:** Tambah `config('sigap.kontak_darurat')` (nama dan nomor) dan tampilkan sebagai tombol `tel:` di beranda publik, halaman galat, kepala Buat Pesanan, dan halaman pelacakan ("Hubungi Dinkes").
- **Dampak:** Tinggi. **Usaha:** Kecil. **DESIGN.md:** Tidak. **Perlu konfirmasi Dinkes:** nomor dan jam layanan.

### T9. Tombol Online sopir menampilkan lawan keadaan

- **Halaman:** `sopir/dashboard.blade.php:12` dan `:18`. **Dasar:** kode.
- **Masalah:** Lencana "Online" berdampingan dengan tombol "Offline". Pada keadaan Offline, tombol "Online" berwarna filled dan terbaca seperti status.
- **Usulan:** Label tindakan eksplisit: "Mulai online" dan "Berhenti online". Alternatif jangka panjang: komponen Switch M3 (Ya pada `DESIGN.md`).
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak untuk perubahan label.

### T10. Ringkasan galat tidak terhubung ke isian

- **Halaman:** `ui/flash.blade.php:10-19`, `ui/isian.blade.php`. **Dasar:** kode.
- **Masalah:** Tiap galat tampil dua kali (ringkasan di atas dan di bawah isian). Ringkasan tidak mendapat fokus, dan butirnya bukan tautan ke isian. Pada formulir panjang seperti Buat Pesanan, pengguna papan ketik dan pembaca layar tidak diarahkan ke masalah.
- **Usulan:** Beri ringkasan `tabindex="-1"` dan pindahkan fokus ke sana saat ada galat; jadikan tiap butir tautan `#f-{nama}`. Pertahankan galat sebaris.
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T11. Laci modal di ponsel tidak terkunci

- **Halaman:** `sigap.js:186-196`, `layouts/app.blade.php:16-18` dan `:69`. **Dasar:** kode.
- **Masalah:** Laci hanya berganti kelas. Tombol menu tanpa `aria-expanded`, laci tanpa peran dialog, fokus papan ketik dapat menembus scrim ke halaman di belakang, dan gulir latar tidak dikunci. Escape dan pengembalian fokus sudah benar.
- **Usulan:** `aria-expanded` pada tombol, `inert` pada isi dan bilah atas selama laci terbuka, kunci gulir badan halaman.
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T12. Baris tabel hanya bisa diketuk di teks kodenya

- **Halaman:** `ui/tabel.blade.php:8`, `admin/_tabel-pesanan.blade.php:12`, `warga/_tabel-pesanan.blade.php:11`, `admin/dashboard.blade.php:87`, serta daftar Faskes, Ambulans, dan akun. **Dasar:** kode.
- **Masalah:** Seluruh baris menyala saat disorot (`tr:hover`), tetapi hanya tautan teks yang bisa diklik. Tinggi target tautan sekitar 20px, jauh di bawah 48px yang diwajibkan `DESIGN.md`.
- **Usulan:** Rentangkan tautan kode ke seluruh baris (`after:absolute after:inset-0` dengan `tr` relatif) sehingga satu baris menjadi satu target; fokus papan ketik tetap di tautan. Sel yang punya tombol sendiri (Nonaktifkan) diberi `relative z-10`.
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T13. Halaman Sesi berakhir salah arah

- **Halaman:** `errors/419.blade.php:2-3`; `SESSION_LIFETIME=120` di `.env.example`. **Dasar:** kode; bagian Dinkes adalah dugaan.
- **Masalah:** Teksnya menyuruh "Muat ulang halaman, lalu ulangi tindakan Anda", tetapi satu-satunya tombol adalah "Ke beranda". Masyarakat yang lama mengisi formulir kehilangan isiannya. Dinkes yang meninggalkan tab terbuka lebih dari dua jam bertemu halaman ini saat mengetuk aksi, karena polling berhenti ketika tab tersembunyi sehingga sesi tidak diperpanjang.
- **Usulan:** Ganti tombol dengan "Kembali dan coba lagi" (`url()->previous()`) dan "Masuk lagi". Pada penangan 419, kembalikan ke halaman sebelumnya dengan isian (`withInput`). Polling mengenali 401 atau 419 dan menampilkan pesan sebaris (T23).
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T14. Grafik batang di ponsel menyembunyikan hari ini

- **Halaman:** `admin/dashboard.blade.php:27-32`, `ui/chart/bar` (`min-w-120`). **Dasar:** referensi dan kode.
- **Masalah:** Tangkapan `dasbor-ponsel.png` hanya memperlihatkan Sen sampai Kam. Lebar dalam kartu di 390px sekitar 310px, sedangkan grafik minimal 480px sehingga bergulir; batang "hari ini" (paling kanan) tidak terlihat, dan tidak ada petunjuk bahwa grafik bisa digulir.
- **Usulan:** Turunkan lebar minimum agar tujuh batang muat di sekitar 300px (tebal batang fleksibel), atau gulirkan wadah ke kanan saat dimuat (`scrollLeft = scrollWidth`).
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T15. Dasbor ponsel menaruh pesanan terbaru paling bawah

- **Halaman:** `admin/dashboard.blade.php`. **Dasar:** referensi.
- **Masalah:** Halaman ponsel setinggi 3.105px dan "Pesanan terbaru" baru mulai di sekitar 2.500px, sesudah empat kartu statistik, dua grafik, dan kartu ketersediaan. Pekerjaan inti operator (antrean) jadi paling jauh.
- **Usulan:** Di bawah `md`, urutkan: kartu utama, Pesanan terbaru, kartu statistik lain, grafik, ketersediaan (kelas `order-*`). Tampilan desktop tidak berubah.
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T16. Kapasitas Faskes tanpa tanda Penuh atau Menipis

- **Halaman:** `ui/ketersediaan.blade.php:5-7`, dasbor, `publik/faskes`, `warga/pesanan/buat`. **Dasar:** kode.
- **Masalah:** "1/3" tanpa satuan, dan "0/3" tampil setenang angka lain. Masyarakat memilih Faskes tujuan, dan Dinkes menugaskan, tanpa tahu IGD penuh.
- **Usulan:** Tulis "1 dari 3 tersedia" dan tambahkan lencana Penuh (nada bahaya, saat tersedia 0) dan Menipis (nada peringatan, ambang di `config/sigap.php`) memakai nada yang sudah ada.
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T17. Tidak ada langkah berikutnya sesudah Verifikasi

- **Halaman:** `admin/pesanan/show`, `Admin\PesananController::jalankan`. **Dasar:** kode.
- **Masalah:** Setelah verifikasi halaman dimuat ulang dari atas, panel Tugaskan harus dicari lagi (di ponsel di dasar, T2). Sesudah menugaskan tidak ada jalan ke pesanan berikutnya, sehingga operator kembali ke daftar (dua kali muat halaman tambahan).
- **Usulan:** Redirect verifikasi ke `show#tugaskan` dan beri fokus ke panel. Pada panel Aksi tampilkan tombol "Pesanan berikutnya yang menunggu" (Menunggu tertua, Gawat lebih dulu).
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T18. Kepala detail pesanan Dinkes tidak merangkum bahan keputusan

- **Halaman:** `admin/pesanan/show.blade.php:11-51`. **Dasar:** kode.
- **Masalah:** Bagian pertama adalah "Pemohon dan pasien" sebanyak sembilan baris. Hal yang menentukan keputusan (Gawat, jenis layanan, lokasi, usia pesanan, kontak, kapasitas Faskes tujuan) tersebar di empat bagian.
- **Usulan:** Kartu ringkasan di atas: lencana Gawat dan jenis layanan, lokasi jemput satu baris, tombol telepon kontak (T19), usia pesanan (T7), dan Faskes tujuan beserta kapasitasnya (T16). Rincian lengkap tetap di bawah.
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T19. Nomor telepon tidak seragam sebagai tombol

- **Halaman:** `petugas/pesanan/show.blade.php:14` (teks), `publik/faskes.blade.php:33` (teks), `admin/pesanan/show.blade.php:16`, `warga/pesanan/show.blade.php:38`, `sopir/dashboard.blade.php:51` (tautan bergaris bawah). **Dasar:** kode.
- **Masalah:** Sebagian nomor berupa teks biasa, sebagian tautan teks setinggi sekitar 20px. Menelepon pasien atau sopir adalah aksi biasa bagi Dinkes, sopir, dan Masyarakat.
- **Usulan:** Satu komponen `x-ui.telepon`: tombol teks atau tonal 48px dengan ikon `phone` yang menampilkan nomor, dipakai di setiap tempat nomor muncul. Tambahkan ke `docs/KIT_UI.md` dan katalog `/_ui`; ikon `phone` perlu disalin ke `resources/icons/` dan daftar ikon.
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T20. "Darurat" muncul dua kali di Buat Pesanan

- **Halaman:** `warga/pesanan/buat.blade.php:9-31`, `JenisLayanan::Darurat`, `TingkatDarurat` (Biasa, Gawat). **Dasar:** kode.
- **Masalah:** Pemesan memilih jenis layanan "Darurat" lalu "Tingkat darurat: Biasa", dan kombinasi itu bertentangan. Nilai bawaannya memang Darurat dan Biasa. "Gawat" adalah penilaian klinis yang sulit dibuat orang awam.
- **Usulan:** Ganti judul bagian kedua menjadi "Kondisi pasien" dengan pilihan "Stabil" dan "Gawat" (deskripsi sudah ada). Label berasal dari Enum, jadi ubah di Enum dan `docs/RANCANGAN_ALUR.md` bagian 2.
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak. **Perlu konfirmasi Dinkes:** istilah baku.

### T21. Ketersediaan Dinkes kembali ke atas sesudah Simpan

- **Halaman:** `admin/ketersediaan/index.blade.php:46-50`, `Admin\KetersediaanController:24`. **Dasar:** kode.
- **Masalah:** Redirect ke `index` tanpa jangkar. Dengan banyak Faskes, baris yang baru disimpan tidak terlihat; hanya snackbar yang menyebut namanya.
- **Usulan:** Redirect ke `index#faskes-{id}` dan beri baris itu latar `secondary-container` sampai pemuatan berikutnya.
- **Dampak:** Sedang. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T22. Penugasan tanpa jarak dan pasangan ambulans-sopir

- **Halaman:** `admin/pesanan/show.blade.php:109-110`, `Admin\PesananController::opsiPenugasan` (`:141-177`). **Dasar:** kode.
- **Masalah:** Ambulans diurutkan menurut jenis yang disarankan, lalu nama Faskes (abjad), bukan jarak ke lokasi jemput, padahal koordinat Faskes dan titik jemput ada. Sopir dipilih terpisah dan hanya divalidasi di server ("Sopir harus dari Faskes yang sama"), jadi pasangan keliru baru ketahuan setelah kirim. Sopir tetap pada ambulans (`sopirTetap`) tidak dipakai untuk mengisi otomatis. Dari daftar sampai ditugaskan butuh tujuh ketukan.
- **Usulan:** (a) Hitung jarak garis lurus (haversine) dari Faskes ke titik jemput, urutkan menurut jarak dan tampilkan "3,2 km"; tanpa titik jemput, urutan sekarang tetap berlaku. (b) Setelah ambulans dipilih, sisakan sopir dari Faskes yang sama dan isi sopir tetap bila sedang online. (c) Tombol "Tugaskan yang disarankan" (ambulans terdekat bertipe sesuai ditambah sopir online dari Faskes itu) yang menurunkan penugasan menjadi dua ketukan.
- **Dampak:** Tinggi. **Usaha:** Sedang. **DESIGN.md:** Tidak.

### T23. Dinkes tidak diberi tahu pesanan baru dan pembaruan terputus

- **Halaman:** `sigap.js:80-100` (hanya mengisi angka, lencana, dan label), `:20-22` (galat jaringan diabaikan), `:48-61` (notifikasi hanya dipakai sopir), `layouts/app.blade.php:8` (polling hanya untuk Dinkes). **Dasar:** kode.
- **Masalah:** Pesanan baru hanya mengubah lencana lonceng secara diam-diam, jadi operator harus menatap layar. Putus jaringan atau sesi habis tidak terlihat, sementara dasbor tetap menyatakan "diperbarui otomatis". Dasbor Petugas Faskes tidak punya polling sama sekali.
- **Usulan:** Saat jumlah Menunggu naik: bunyi singkat (aset audio kecil, diizinkan setelah gestur pertama), notifikasi peramban lewat `Sigap.notifikasi` yang sudah ada, dan baris status di bawah judul ("Diperbarui 10.42.05") yang berubah menjadi "Terputus sejak 10.40" bernada peringatan setelah dua kegagalan, atau tautan "Masuk lagi" bila 401 atau 419. Beri Petugas polling untuk penugasan masuk.
- **Dampak:** Tinggi. **Usaha:** Sedang. **DESIGN.md:** Ya, tambah keadaan terhubung dan terputus pada Interaction states.

### T24. Halaman memuat ulang sendiri saat status berubah

- **Halaman:** `sigap.js:114-118` (sopir, setelah 1,5 detik), `warga/pesanan/show.blade.php:73-79` (segera). **Dasar:** kode.
- **Masalah:** Tidak ada pemberitahuan selain notifikasi peramban yang bergantung izin. Gulir kembali ke atas, dan teks yang sedang diketik (alasan menolak) hilang. Masyarakat yang sedang membaca linimasa tiba-tiba terlempar. Permintaan izin notifikasi di dalam callback polling (`sigap.js:56-59`) tanpa gestur umumnya diabaikan peramban.
- **Usulan:** Perbarui di tempat: ganti elemen status dengan data baru, umumkan lewat `aria-live="polite"` dan snackbar ("Status pesanan berubah: Dalam perjalanan"). Untuk sopir: bunyi dan getar sekali pada penugasan baru, dan jangan muat ulang bila ada kolom terisi.
- **Dampak:** Tinggi. **Usaha:** Sedang. **DESIGN.md:** Tidak.

### T25. Sopir tidak tahu GPS-nya terkirim atau tidak

- **Halaman:** `sigap.js:121-123` (`lokasiSaya` tanpa penanganan galat), `sopir/dashboard.blade.php:2`. **Dasar:** kode; perilaku latar belakang adalah dugaan.
- **Masalah:** Teks halaman berjanji "Posisi GPS dikirim selama Online", tetapi izin ditolak, GPS mati, atau layar terkunci tidak terlihat. Masyarakat melihat posisi basi pada peta pelacakan.
- **Usulan:** Baris status di kartu Status Anda: "GPS aktif, terkirim 10.42" atau "GPS tidak aktif. Izinkan lokasi" (lencana sukses atau peringatan). Pakai Screen Wake Lock selama Online dan sedang bertugas supaya layar tidak mati, dan beri tahu bahwa pelacakan berhenti bila halaman berada di latar belakang (terkait T5).
- **Dampak:** Tinggi. **Usaha:** Sedang. **DESIGN.md:** Tidak.

### T26. Petugas harus mengetik angka ketersediaan

- **Halaman:** `petugas/ketersediaan/edit.blade.php:10-15`, `petugas/dashboard.blade.php`. **Dasar:** kode.
- **Masalah:** Tugas harian paling sering adalah bed berkurang atau bertambah satu. Petugas harus membuka halaman, mengetuk isian, menghapus angka, mengetik, dan menyimpan. Isian hanya punya `min=0`, tanpa batas maksimum menurut total. Menyatakan "data masih benar" tetap berarti membuka formulir dan menekan Simpan, padahal lencana "Data lama" muncul setelah 24 jam walau tidak ada yang berubah.
- **Usulan:** Komponen stepper numerik (tombol minus dan plus 48px di kiri dan kanan angka, `max` mengikuti total). Tambahkan tombol "Data sudah benar" pada dasbor yang memperbarui cap waktu tanpa membuka formulir. Pakai stepper yang sama di T32.
- **Dampak:** Tinggi. **Usaha:** Sedang. **DESIGN.md:** Ya, tambah komponen Stepper numerik.

### T27. Pelacakan Masyarakat tanpa status dan ETA di atas

- **Halaman:** `warga/pesanan/show.blade.php:8-68`. **Dasar:** kode.
- **Masalah:** Urutannya judul dan lencana, rincian delapan baris, sopir, peta, riwayat; tombol batal paling bawah. Masyarakat yang menunggu ingin tahu "sudah dekat belum" dan "bagaimana menghubungi". `peta.js` sudah menghitung rute OSRM, tetapi durasinya tidak ditampilkan.
- **Usulan:** Kartu status di atas: kalimat status besar ("Ambulans dalam perjalanan"), perkiraan tiba dari durasi rute, nama sopir dan plat, tombol telepon sopir (T19) dan Hubungi Dinkes (T8). Rincian di bawahnya. Pasang jalur status (C2).
- **Dampak:** Tinggi. **Usaha:** Sedang. **DESIGN.md:** Tidak (Ya bila memakai jalur status, C2).

### T28. Formulir Buat Pesanan terlalu banyak bagian

- **Halaman:** `warga/pesanan/buat.blade.php:7-70` dan `:72-74`. **Dasar:** kode; perilaku peta di ponsel adalah dugaan.
- **Masalah:** Lima kartu setara (jenis, tingkat, lokasi, Faskes tujuan, pasien). Faskes tujuan tampil selalu meski hanya wajib untuk Rujukan, dan judulnya ganda (legend dan label). Lokasi tidak otomatis. Peta setinggi 256px berada di dalam gulir halaman, jadi geser satu jari pada peta menggeser peta, bukan halaman.
- **Usulan:** Susun ulang: lokasi, kontak dan nama pasien, lalu pilihan layanan dan kondisi dalam satu kartu. Sembunyikan Faskes tujuan kecuali Rujukan (aturan server tetap). Minta GPS otomatis saat halaman dibuka bila izin sudah diberikan, tanpa memicu dialog izin sebelum ada ketukan. Hapus legend ganda. Di ponsel, peta digeser dua jari atau punya tombol "Perbesar peta".
- **Dampak:** Tinggi. **Usaha:** Sedang. **DESIGN.md:** Tidak.

### T29. Layanan peta publik gratis sebagai titik gagal

- **Halaman:** `config/sigap.php:22-25` (ubin OSM, Nominatim, `router.project-osrm.org`). **Dasar:** kode.
- **Masalah:** Server OSRM demo dan Nominatim publik punya batas pemakaian dan tidak menjamin layanan. Pengisian alamat otomatis, pencarian alamat, dan rute pelacakan bergantung padanya. Alamat sudah punya cadangan isi manual (bagus), tetapi rute dan ETA (T27) tidak punya.
- **Usulan:** Putuskan penyedia yang dikelola Dinkes sebelum rilis (OSRM dan Nominatim sendiri, atau penyedia berbayar dengan SLA), dan tampilkan "Rute tidak tersedia" alih-alih diam.
- **Dampak:** Tinggi. **Usaha:** Besar. **DESIGN.md:** Tidak. **Perlu keputusan:** hosting dan anggaran.

### T30. Pendaftaran wajib dan tombol Pesan ambulans bagi tamu

- **Halaman:** `publik/beranda.blade.php:6`, `Publik\BerandaController:19-22`, `auth/login`, `auth/register`. **Dasar:** kode.
- **Masalah:** Tamu yang mengetuk "Pesan ambulans" tiba di halaman Masuk; tautan Daftar kecil di bawah formulir. Dari beranda sampai pesanan terkirim, tamu baru melewati sekitar 9 ketukan dan 6 isian teks (lima untuk daftar, satu nama pasien).
- **Usulan:** Bertahap. (a) Ganti label menjadi "Masuk untuk memesan" dan tambahkan tombol kedua "Daftar". (b) Persingkat pendaftaran (nama, telepon, kata sandi; email opsional) bila Dinkes setuju. (c) Kaji pemesanan tanpa akun dengan verifikasi nomor telepon; ini mengubah alur bisnis dan tidak dibahas di sini.
- **Dampak:** Tinggi. **Usaha:** Besar (a saja: Kecil). **DESIGN.md:** Tidak. **Perlu konfirmasi Dinkes.**

### T31. Angka dasbor hidup, tabel dan grafik basi

- **Halaman:** `admin/dashboard.blade.php`, `sigap.js:80-100`. **Dasar:** kode.
- **Masalah:** Polling hanya mengisi elemen `data-ringkasan`. Donat, batang, dan tabel Pesanan terbaru tetap seperti saat dimuat, jadi kartu "3 menunggu" dapat bertentangan dengan tabel yang menampilkan dua.
- **Usulan:** Bila `pesanan_menunggu` atau `pesanan_berjalan` berbeda dari nilai awal, tampilkan pita sebaris "Ada pembaruan. Muat ulang." (tombol teks) pada kartu Pesanan terbaru; atau ganti fragmen tabel lewat fetch.
- **Dampak:** Sedang. **Usaha:** Sedang. **DESIGN.md:** Tidak.

### T32. Tabel ketersediaan Dinkes di ponsel

- **Halaman:** `admin/ketersediaan/index.blade.php`. **Dasar:** kode.
- **Masalah:** Tujuh kolom, enam isian angka selebar tetap, dan Simpan per baris. Di ponsel tabel bergulir ke samping dan label isian hanya `sr-only`, jadi angka tampak tanpa label bila header tergeser.
- **Usulan:** Di bawah `md`, satu kartu per Faskes: nama, lencana kesegaran, enam stepper berlabel (T26), dan Simpan.
- **Dampak:** Sedang. **Usaha:** Sedang. **DESIGN.md:** Tergantung T26.

### T33. Aksi merusak sebagai panel penuh dengan kolom alasan

- **Halaman:** `admin/pesanan/show.blade.php:85-137`, `sopir/dashboard.blade.php:72-76`, `warga/pesanan/show.blade.php:57-65`. **Dasar:** kode.
- **Masalah:** Verifikasi (aksi utama) bersebelahan dengan panel "Tolak pesanan" lengkap dengan kolom alasan dan tombol merah; Lepas penugasan dan Batalkan pesanan juga terbuka. Beban visual, risiko salah ketuk, dan tambahan gulir di ponsel.
- **Usulan:** Satu tombol teks atau outlined ("Tolak...") yang membuka dialog berisi kolom alasan wajib dan konfirmasi. Dialog yang ada sudah menangani konfirmasi; tambahkan slot isian. Berlaku untuk Tolak, Lepas penugasan, dan Batalkan (Dinkes), Tolak (sopir), serta Batalkan (Masyarakat).
- **Dampak:** Sedang. **Usaha:** Sedang. **DESIGN.md:** Ya, dialog boleh memuat isian.

### T34. Bundel peta berat di halaman Buat Pesanan

- **Halaman:** `warga/pesanan/buat` (`:peta="true"`). **Dasar:** keluaran `npm run build`: `peta-*.js` 702 kB (113 kB gzip).
- **Masalah:** Satu bundel memuat Leaflet dan Routing Machine untuk semua jenis peta, padahal Buat Pesanan hanya butuh pemilih titik. Itu halaman paling kritis, sering dibuka di jaringan seluler.
- **Usulan:** Pisahkan pemilih (hanya Leaflet) dari pelacak (Leaflet dan routing) lewat `import()` terpisah, dan pastikan formulir sudah dapat dipakai sebelum peta selesai dimuat. Chart.js sudah dimuat terpisah.
- **Dampak:** Sedang. **Usaha:** Sedang. **DESIGN.md:** Tidak.

### T35. Beranda Masyarakat tidak berpusat pada pesanan aktif

- **Halaman:** `warga/beranda.blade.php:8-26`. **Dasar:** kode.
- **Masalah:** Dua kartu statistik ("Pesanan aktif 0", "Pesanan selesai 3") menempati bagian paling atas, sementara tombol Pesan ambulans kecil di kanan atas (di ponsel di bawah deskripsi). Masyarakat hampir selalu datang untuk dua hal: memesan, atau melihat pesanan yang sedang berjalan.
- **Usulan:** Bila ada pesanan aktif tampilkan kartu status (jalur status C2, ETA, tombol Lihat). Bila tidak, tampilkan satu tombol besar "Pesan ambulans" (ukuran lg) dan tautan Riwayat. Hapus kartu statistik untuk peran ini.
- **Dampak:** Sedang. **Usaha:** Sedang. **DESIGN.md:** Tidak.

### T36. Tombol filter berlabel berbeda

- **Halaman:** "Terapkan" (`admin/pesanan/index:16`), "Cari" (Faskes, Ambulans, akun, `publik/faskes`), "Tampilkan" (laporan). **Dasar:** kode.
- **Masalah:** `DESIGN.md` (Writing) meminta nama aksi sama di seluruh alur.
- **Usulan:** "Terapkan" untuk semua filter, "Cari" hanya untuk pencarian teks.
- **Dampak:** Rendah. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T37. Keadaan kosong tanpa langkah berikutnya

- **Halaman:** "Belum ada pesanan." dan "Belum ada pesanan di tab ini." (`admin/pesanan/index`), "Belum ada pesanan aktif untuk Faskes ini." (`petugas/dashboard`), "Belum ada Faskes aktif." (`admin/ketersediaan`), "Belum ada penugasan yang selesai." (sopir), serta peringatan "Tidak ada ambulans yang tersedia." (penugasan). **Dasar:** kode.
- **Masalah:** `DESIGN.md` (Writing) meminta keadaan kosong menyebut langkah berikutnya. Halaman Masyarakat dan sopir sudah melakukannya; yang lain belum.
- **Usulan:** Tambahkan satu kalimat atau tautan, misalnya pada penugasan kosong: "Periksa status ambulans di menu Ambulans."
- **Dampak:** Rendah. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T38. Kolom cari di bilah atas terpotong di ponsel

- **Halaman:** `layouts/app.blade.php:72`. **Dasar:** referensi (tangkapan ponsel menampilkan "Cari ko").
- **Masalah:** Placeholder "Cari kode pesanan atau nama pasien" terlalu panjang untuk lebar ponsel.
- **Usulan:** Placeholder "Cari pesanan"; label `sr-only` tetap lengkap.
- **Dampak:** Rendah. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T39. Snackbar tanpa batas waktu menutupi isi

- **Halaman:** `ui/snackbar.blade.php:2-3`, `ui/flash.blade.php:22-24`. **Dasar:** kode.
- **Masalah:** Snackbar tetap tampil sampai ditutup dan menutupi sekitar 60px dasar layar di ponsel, termasuk bilah aksi lekat (T2, T4). `role="status"` pada elemen yang sudah ada saat halaman dimuat sering tidak dibacakan.
- **Usulan:** Hilang otomatis setelah sekitar 8 detik (jeda saat disorot atau difokus), tombol Tutup tetap ada. Beri ruang di dasar isi, dan render ke wilayah `aria-live` yang sudah ada sebelum pesan masuk.
- **Dampak:** Rendah. **Usaha:** Kecil. **DESIGN.md:** Ya, Components (Snackbar) dan baris Snackbar di `docs/KIT_UI.md`.

### T40. Panel filter pesanan Dinkes selalu terbuka

- **Halaman:** `admin/pesanan/index.blade.php:5-22`. **Dasar:** kode.
- **Masalah:** Empat isian dan tombol mendorong data ke bawah di ponsel, padahal tab dan kolom cari sudah menyaring.
- **Usulan:** Bungkus dalam disclosure "Filter", tertutup bila tidak ada filter aktif.
- **Dampak:** Rendah. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T41. Tiga kartu statistik putih satu kolom di ponsel

- **Halaman:** `admin/dashboard.blade.php:17-22` (`flex-[1_1_14rem]`). **Dasar:** referensi.
- **Masalah:** Di 390px tiap kartu satu baris, sekitar 250px untuk tiga angka kecil.
- **Usulan:** Dua kolom kompak di ponsel; kartu utama tetap penuh.
- **Dampak:** Rendah. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T42. Info Faskes: filter butuh tombol, tanpa rute

- **Halaman:** `publik/faskes.blade.php:4-14`, `:33`, `:42-44`. **Dasar:** kode.
- **Masalah:** Satu pilihan jenis tetapi masih memerlukan tombol "Cari"; telepon berupa teks; peta terpisah di dasar halaman tanpa tautan per Faskes.
- **Usulan:** Kirim otomatis saat pilihan berubah (tombol tetap sebagai cadangan tanpa JavaScript), pakai komponen telepon (T19), dan beri tiap Faskes tautan "Lihat di peta".
- **Dampak:** Rendah. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T43. Waktu tanpa zona

- **Halaman:** `ui/waktu.blade.php`. **Dasar:** kode.
- **Masalah:** "11 Okt 2026, 16:14" tanpa zona; laporan PDF dan CSV dapat dibaca di luar Papua.
- **Usulan:** Tambahkan "WIT" pada format di detail pesanan dan laporan.
- **Dampak:** Rendah. **Usaha:** Kecil. **DESIGN.md:** Tidak.

### T44. Perpindahan halaman terasa lambat

- **Halaman:** semua. **Dasar:** kode; dampaknya dugaan.
- **Masalah:** Setiap tautan dan aksi memuat halaman penuh tanpa petunjuk selama berpindah (selain T3).
- **Usulan:** Pra-ambil tautan saat disentuh atau disorot (skrip kecil tanpa paket baru), dan pertimbangkan transisi dokumen silang CSS (`@view-transition`, hanya Chromium, tanpa gerak berulang).
- **Dampak:** Rendah. **Usaha:** Kecil. **DESIGN.md:** Ya, bagian Motion melarang animasi masuk; transisi dokumen perlu dibolehkan secara eksplisit atau ditolak.

### T45. Tidak ada lupa kata sandi, ganti kata sandi, atau profil

- **Halaman:** `routes/web.php:28-35`, avatar di `layouts/app.blade.php:53` dan `:86`. **Dasar:** kode.
- **Masalah:** Masyarakat yang lupa kata sandi tidak punya jalan; akun Dinkes, Petugas, dan sopir bergantung pada Dinkes mengatur ulang (formulir akun sudah mendukung kata sandi baru). Avatar dan nama tidak membuka apa pun.
- **Usulan:** Menu avatar (Profil, Ganti kata sandi, Keluar) dan alur lupa kata sandi lewat email untuk Masyarakat.
- **Dampak:** Sedang. **Usaha:** Besar. **DESIGN.md:** Ya, tambah entri menu avatar pada Components.

## 6. Tiga usulan ciri khas

Syaratnya: satu elemen penanda yang membuat SIGAP dikenali, bukan dekorasi, dan tetap di dalam sistem Material. Ketiganya memakai token yang sudah ada (tidak ada warna, ukuran, atau sudut baru), tidak ada gerak, dan membawa makna, bukan hiasan. Urutan menurut manfaat bagi operasi.

### C1. Jam tunggu (usia pesanan yang berubah nada)

- **Apa:** Komponen kecil `x-ui.waktu-tunggu`, berbentuk lencana pil dengan teks usia ("12 mnt"), untuk pesanan berstatus Menunggu dan Terverifikasi. Nada: netral, lalu peringatan, lalu bahaya menurut ambang di `config/sigap.php` (nilai contoh: di bawah 5 menit, 5 sampai 10 menit, di atas 10 menit; angka sebenarnya ditetapkan Dinkes). Muncul di kartu utama dasbor ("terlama 12 mnt"), setiap baris daftar, kepala detail pesanan, dan padanannya di Laporan (waktu respons).
- **Mengapa khas:** Satu-satunya ukuran yang membedakan layanan ambulans dari aplikasi pesanan biasa adalah waktu. Aplikasi lain menampilkan tanggal; SIGAP menampilkan seberapa lama seseorang menunggu. Itu mengubah dasbor dari daftar data menjadi alat antrean, dan sejalan dengan nama "sigap". Elemen ini membawa keputusan, jadi bukan dekorasi.
- **Dalam Material:** Memakai lencana dan nada yang sudah ada, `label-m`, angka tabular. Warna semantik tetap hanya untuk makna status; ambang waktu adalah status.
- **Perubahan:** `DESIGN.md` Components tambah satu entri (Jam tunggu), tanpa token baru. Teks diperbarui di klien tiap menit; hanya angka yang berganti, tanpa animasi. **Usaha:** Sedang. **Risiko:** ambang harus disepakati Dinkes agar warna tidak memicu kepanikan palsu.

### C2. Jalur status (jejak pesanan dengan penanda ambulans)

- **Apa:** Jalur horizontal lima titik (Dikirim, Diverifikasi, Ditugaskan, Dalam perjalanan, Selesai) dengan garis 2px. Bagian yang sudah dilalui berwarna `primary`, sisanya `outline-variant`, dan titik saat ini berupa chip kecil `primary-container` berisi ikon `ambulance`. Di ponsel jalurnya vertikal ringkas. Pesanan yang ditolak atau dibatalkan berhenti di titik terakhir dengan penanda `error`. Tampil di detail pesanan Masyarakat, beranda Masyarakat (pesanan aktif), dan versi ringkas di detail Dinkes dan Petugas.
- **Mengapa khas:** Masyarakat yang menunggu hanya ingin tahu "di mana pesanan saya". Jalur itu menjawabnya dalam sekali lihat, tanpa membaca linimasa, dan menjadi motif yang dapat dikenali dan diulang di laporan PDF. Ikon ambulans hanya muncul pada titik saat ini sehingga membawa makna, bukan hiasan, dan sesuai aturan chip ikon `DESIGN.md`.
- **Dalam Material:** Mirip *stepper* M3, dibuat dari token yang ada (`primary`, `outline-variant`, `primary-container`, `error`). Linimasa rinci tetap ada sebagai riwayat.
- **Perubahan:** `DESIGN.md` Components tambah satu entri (Jalur status), tanpa token baru. **Usaha:** Sedang. Mendukung T27 dan T35.

### C3. Tanda merek SIGAP yang bukan tanda tambah

- **Apa:** Satu logomark datar berukuran 40px (sudut 12px, `primary`, glif `on-primary`) untuk menggantikan ikon `plus` generik pada kepala laci (`layouts/app.blade.php:21-23`), halaman Masuk dan Daftar (`layouts/auth` saat ini hanya menampilkan teks), kepala PDF laporan, dan favicon. Rancangan glifnya satu garis denyut sederhana pada kotak; konsep akhir ditentukan Dinkes.
- **Mengapa khas:** Saat ini tanda mereknya adalah ikon `plus` Lucide yang sama dengan tombol "Tambah Faskes", "Tambah Ambulans", dan menu "Pesan ambulans" (`ikon="plus"`), jadi mereknya bentrok dengan makna aksi dan tampak seperti tombol. Merek yang berbeda adalah syarat paling murah agar aplikasi dikenali di tab peramban, layar masuk, dan dokumen cetak. Ini identitas, bukan dekorasi.
- **Dalam Material:** Sudah dimungkinkan: entri Menu di `DESIGN.md` sudah memuat "tanda merek 40px (Primary, sudut 12px)". Yang berubah hanya isinya (berkas SVG di `resources/icons/` dan tampilannya di komponen), bukan ukuran atau warnanya.
- **Perubahan:** `DESIGN.md` Components (Menu) menyebut tanda merek khusus; tanpa token baru. **Usaha:** Kecil, tetapi menunggu bentuk logo. **Risiko:** hak cipta dan persetujuan Dinkes atas logo.

Yang sengaja tidak diusulkan karena bertentangan dengan `DESIGN.md`: gradien, ilustrasi atau foto, hero, animasi masuk dan efek angkat, serta warna aksen kedua.

## 7. Perubahan `DESIGN.md` yang dibutuhkan

Tidak ada token baru (warna, tipografi, sudut, bayangan). Yang perlu ditambah hanya perilaku dan komponen:

| Bagian | Perubahan | Temuan |
|---|---|---|
| Interaction states | Keadaan "memuat": tombol berganti label proses dan `aria-busy`, tanpa spinner berputar. | T3 |
| Interaction states | Keadaan "terhubung atau terputus" untuk data yang diperbarui otomatis. | T23 |
| Components | Stepper numerik (minus, angka, plus; `max` menurut total). | T26, T32 |
| Components | Dialog yang dapat memuat isian (alasan wajib). | T33 |
| Components | Snackbar hilang otomatis dengan jeda saat disorot atau difokus. | T39 |
| Components | Menu avatar (Profil, Ganti kata sandi, Keluar). | T45 |
| Components | Jam tunggu; Jalur status; tanda merek khusus pada entri Menu. | C1, C2, C3 |
| Motion | Putuskan boleh atau tidaknya transisi dokumen silang. | T44 |
| Opsional | Switch M3 bila tombol Online sopir dijadikan sakelar. | T9 |

Pola yang cukup dicatat di `docs/KIT_UI.md` tanpa mengubah `DESIGN.md`: bilah aksi lekat di dasar layar pada ponsel (T2, T4), tautan baris penuh pada tabel (T12), dan komponen telepon (T19).

## 8. Pertanyaan untuk pemilik dan Dinkes

1. Nomor telepon darurat dan jam layanannya (T8).
2. Apakah label "Tingkat darurat: Biasa dan Gawat" boleh menjadi "Kondisi pasien: Stabil dan Gawat" (T20, `RANCANGAN_ALUR.md` bagian 2).
3. Ambang waktu tunggu untuk perubahan nada (T7, C1).
4. Apakah pendaftaran dapat disederhanakan atau pemesanan tanpa akun dipertimbangkan (T30).
5. Penyedia layanan peta, rute, dan alamat untuk produksi (T29), dan apakah tautan navigasi ke aplikasi peta eksternal diizinkan (T5).
6. Bentuk logo dan persetujuan atasnya (C3).

## 9. Yang sudah baik dan perlu dipertahankan

- Tautan "Lewati ke isi", cincin fokus global, label terhubung pada semua isian, dan galat sebaris yang selalu berteks.
- Lencana status selalu berteks (tidak mengandalkan warna), dan keadaan "Data lama" serta "Belum diperbarui" yang konsisten.
- Dialog konfirmasi bersama untuk aksi merusak, dengan teks yang menyebut akibatnya.
- Daftar pesanan Dinkes terurut prioritas dari server (Gawat dan yang paling lama menunggu lebih dulu), dengan tab dan pencarian.
- Pesan sukses faktual yang selaras dengan nama tombol; pesan galat peta menyebut jalan keluar (isi alamat manual).
- Tombol kirim lekat di dasar pada Buat Pesanan; tombol sopir 48px selebar layar.
- Polling berhenti saat tab tersembunyi; Chart.js dimuat terpisah dan hanya di halaman yang punya grafik.
- Satu tombol filled per layar pada hampir semua halaman.

## 10. Saran urutan kerja

1. **Gelombang pertama (sekitar dua hari):** T1, T2, T3, T4, T5, T6, T7, T8 (semua dampak tinggi, usaha kecil), ditambah T9, T12, T14, dan T15 yang berada di berkas yang sama.
2. **Gelombang kedua:** T22, T23, T24, T25 (penugasan, pemberitahuan, pembaruan di tempat, GPS), lalu T26 dan T28. Putuskan C1 dan C3 pada gelombang ini; keduanya murah dan terlihat.
3. **Gelombang ketiga:** T27 dan C2 (pelacakan dan jalur status), T33, T34, serta keputusan hosting peta (T29) dan pendaftaran (T30) yang bergantung pada Dinkes.

Batas audit ini: temuan yang bertanda Dugaan perlu dicoba di perangkat nyata (ponsel Android kelas menengah, jaringan seluler biasa) sebelum menjadi keputusan, dan belum ada uji dengan pengguna sebenarnya.
