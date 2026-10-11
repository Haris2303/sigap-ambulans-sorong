Digantikan oleh docs/RETEMA_B.md

# Arah desain: dasbor Material (keputusan pemilik proyek)

Dokumen ini adalah keputusan pemilik proyek setelah melihat hasil M1 sampai M7. Pemilik menilai tampilan M3 baseline yang sekarang "terlalu simple" dan memilih gaya dasbor admin Material yang lebih kaya: kartu berbayang di atas latar bertint, chip ikon berwarna, grafik, tab, dan avatar. Pratinjau yang disetujui ada di `docs/referensi/`.

**Dokumen ini mengalahkan aturan Don't dan Layout lama di `DESIGN.md`, `docs/KIT_UI.md`, dan `agent/konsistensi.json`.** Ketika ada pertentangan, ubah ketiga berkas itu agar sesuai dokumen ini. Jangan menolak perubahan dengan alasan aturan lama.

Berkas referensi (baca semuanya sebelum mengerjakan langkah mana pun):

- `docs/referensi/dasbor-desktop.png` dan `dasbor-ponsel.png`: tangkapan layar pratinjau (lebar 1440 dan 390).
- `docs/referensi/dasbor-material.html`: sumber pratinjau. Nilai warna di dalamnya hex mentah, gaya inline. Ini referensi ukuran dan susunan, bukan kode yang disalin. Di aplikasi pakai komponen Blade dan token.

## Batasan (berlaku untuk semua langkah)

- Tanpa paket baru, baik Composer maupun npm, kecuali `chart.js` (keputusan pemilik proyek, langkah D3b): grafik dasbor memakai Chart.js, bukan SVG sisi server. Paket itu dimuat modular dan hanya di halaman yang punya grafik (5.13). Pembatasan ini tidak meluas ke paket lain.
- Jangan menampilkan isi `.env` atau kredensial. Jangan menjalankan perintah DB yang merusak (`migrate:fresh`, `db:wipe`, `migrate:rollback` pada DB nyata). Tes memakai SQLite memori.
- Warna di markup hanya lewat token. Tidak ada hex, tidak ada `bg-[#...]`, tidak ada gradien, tidak ada ilustrasi atau gambar dekoratif, tidak ada animasi masuk. JavaScript grafik juga tanpa hex: warna dibaca dari variabel CSS token di runtime (aturan `warna-js`).
- Label status, peran, dan jenis diambil dari Enum (`->label()`), dalam huruf awal kapital hanya di awal kalimat ("Dalam perjalanan", bukan "Dalam Perjalanan").
- Satu langkah per sesi. Setelah langkah selesai: jalankan gerbang (lihat bagian 9), commit di branch `material-design`, laporkan ringkas, lalu berhenti. Jangan mengerjakan langkah berikutnya sebelum diminta.
- Jangan mengubah alur bisnis, rute, atau skema database. Parameter kueri baru (`?tab=`, `?q=`) pada rute yang sudah ada diizinkan.

## 1. Apa yang berubah dari M1 sampai M7

| Hal | Sebelumnya | Sekarang |
|---|---|---|
| Latar halaman | `surface` #F6FAFE | `canvas` #EBF1F6 (token baru) |
| Panel | batas 1px outline-variant, tanpa bayangan, sudut 12 | tanpa batas, bayangan `card`, sudut 16 (`lg`), isi `surface-container-lowest` |
| Ringkasan angka | satu panel dengan sel terpisah garis tegak | kartu statistik terpisah: satu kartu utama berwarna primary dan tiga kartu putih, masing-masing dengan chip ikon |
| Chip ikon | dilarang ("ikon dekoratif di lingkaran berwarna") | diizinkan sebagai pembawa makna (lihat bagian 4) |
| Bilah atas desktop | tidak ada | kartu melayang: pencarian, lonceng pesanan menunggu, avatar |
| Laci navigasi | latar `surface-container-low`, item 56px, item aktif secondary-container | latar putih, item 48px, item aktif berlatar primary dengan bayangan `nav-active` |
| Lencana status | sudut 8px | pil (sudut penuh), tetap dengan titik 6px |
| Grafik | tidak ada | batang 7 hari dan donat status (SVG server-side) |
| Tab | tidak ada | tab primer M3 (garis bawah 3px) untuk memfilter daftar |
| Avatar | tidak ada | lingkaran 40px berinisial, `primary-container` |
| Bayangan | hanya level 1 sampai 3 untuk lapisan melayang | ditambah `card`, `card-raised`, `nav-active` |

Tidak berubah: Roboto, skala tipe M3, bobot hanya 400 dan 500, satu aksen biru (primary), lapisan keadaan, kontras, target sentuh 48px, dialog dan snackbar, formulir outlined, linimasa pesanan, tanpa mode gelap.

## 2. Token

### 2.1 Warna baru (tambahkan ke `colors` di DESIGN.md)

| Token | Nilai | Dipakai untuk |
|---|---|---|
| `canvas` | `#EBF1F6` | latar halaman semua peran. `app-background` memakai token ini |
| `chart-sky` | `#7FB9DA` | batang hari biasa dan segmen donat "dalam perjalanan" |

Tidak ada warna baru lain. Segmen donat "dibatalkan" memakai `outline` (#71787E), bukan abu-abu baru.

### 2.2 Pemetaan warna pratinjau ke token

Pratinjau memakai hex mentah. Di aplikasi pakai token berikut. Selisih kecil (satu atau dua tingkat terang) boleh diterima, token yang menang.

| Hex di pratinjau | Token | Catatan |
|---|---|---|
| #EBF1F6 | `canvas` | latar halaman |
| #FFFFFF | `surface-container-lowest` | kartu, laci, bilah atas |
| #F0F4F9 | `surface-container-low` | petak di dalam kartu, blok pengguna di laci, kepala tabel |
| #EEF2F6 | `surface-container` | kolom cari |
| #E3E8EE, #DDE3EA | `surface-container-high` | garis pemisah baris di dalam kartu, garis kisi grafik, tepi kanan laci |
| #C1C7CE | `outline-variant` | garis dasar grafik, batang bernilai nol |
| #216487 | `primary` | |
| #C6E7FF | `primary-container` | chip biru, avatar, teks sekunder di kartu utama |
| #004C6C | `on-primary-container` | |
| #FFDF99 | `warning-container` | chip kuning. Ikon: `on-warning-container` (#251A00), bukan #4D3A00 |
| #92F7BC | `success-container` | chip hijau. Ikon: `on-success-container` (#002111), bukan #00522F |
| #FFDAD6 / #93000A | `error-container` / `on-error-container` | chip merah, lencana bahaya |
| #D2E5F4 / #374955 | `secondary-container` / `on-secondary-container` | tombol tonal |
| #41484D, #5B6369 | `on-surface-variant` | teks sekunder, label grup menu |
| #181C20 | `on-surface` | |
| #BA1A1A | `error` | lencana hitungan, segmen "ditolak" |
| #006D43 | `success` | segmen "selesai" |
| #9AA1A7 | `outline` | segmen "dibatalkan" |
| #7FB9DA, #9CCBE6 | `chart-sky` | |
| #DFE3E7 | `surface-container-highest` | lencana netral |

### 2.3 Bayangan baru (tambahkan ke `elevation`, ikuti pola level-1 sampai level-3 dan cara `agent/tokens.mjs` membuat `shadow-level-*`)

Warna bayangan kartu adalah `on-surface` (24 28 32), bukan hitam murni. Jika pola `shadow-color` global tidak memungkinkan warna per tingkat, tambahkan kunci warna per tingkat yang baru.

| Token (kelas) | Lapisan 1 | Lapisan 2 | Dipakai untuk |
|---|---|---|---|
| `card` (`shadow-card`) | `0 1px 3px 0` @ 0,10 | `0 6px 16px 0` @ 0,06 | panel, kartu statistik putih, bilah atas |
| `card-raised` (`shadow-card-raised`) | `0 1px 3px 0` @ 0,12 | `0 8px 20px 0` @ 0,10 | kartu statistik utama (primary) |
| `nav-active` (`shadow-nav-active`) | `0 2px 6px 0` @ 0,22 | tidak ada | item menu aktif |

Level 1 sampai 3 tetap untuk lapisan yang melayang (menu tarik, dialog, snackbar) dan tidak diubah.

### 2.4 Gaya tipe baru (tambahkan ke `typography`)

Gaya M3 yang ada tetap. Lima gaya berikut menambah bobot 500 dan ukuran yang dipakai pratinjau. Semua angka memakai `tabular-nums`.

| Gaya | Ukuran / tinggi baris / bobot | Dipakai untuk |
|---|---|---|
| `page-title` | 28px / 36px / 500 | judul halaman ("Dasbor") |
| `card-title` | 20px / 28px / 500 | judul kartu ("Status pesanan") |
| `stat-hero` | 56px / 60px / 500 | angka di kartu statistik utama |
| `stat-l` | 32px / 36px / 500 | angka di kartu statistik putih |
| `stat-m` | 28px / 32px / 500 | angka di petak ketersediaan |

Pemetaan sisanya ke skala yang ada: subjudul halaman `body-l` (16/24) warna `on-surface-variant`; keterangan kartu `body-m` `on-surface-variant`; label kartu statistik `label-l`; keterangan kecil `body-s`; kepala tabel `label-l`; sel tabel `body-m`; lencana `label-m`; label grup menu `label-m`; teks item menu `label-l`.

### 2.5 Perubahan token komponen

| Komponen | Perubahan |
|---|---|
| `app-background` | `backgroundColor` jadi `canvas` |
| `top-app-bar` | `backgroundColor` `surface-container-lowest`, `rounded` `lg`, tinggi 64px; bayangan `card`; melayang sebagai kartu di desktop dan ponsel |
| `navigation-drawer` | `backgroundColor` `surface-container-lowest`; lebar tetap 280px (pratinjau 272px, abaikan selisih) |
| `nav-item` | tinggi 48px |
| `nav-item-active` | `backgroundColor` `primary`, `textColor` `on-primary`; bayangan `nav-active` |
| `nav-badge` | tinggi 20px, tipografi `label-m` (sebelumnya 16px dan `label-s`) |
| `panel` | `rounded` `lg`; bayangan `card`; tanpa batas |
| `badge-*` | `rounded` `full` |
| `button-on-primary` (baru) | latar `on-primary`, teks `primary`, pil, 40px. Hanya untuk di atas kartu primary |

Komponen baru yang perlu token: `stat-card`, `stat-card-featured`, `icon-chip`, `icon-chip-lg`, `tile`, `tab`, `tab-active`, `avatar`, `search-field`. Ukuran ada di bagian 5. Pastikan `designmd lint` bersih.

## 3. Aturan teks yang harus diubah di DESIGN.md

| Bagian | Ubah menjadi |
|---|---|
| Overview | Hapus klaim "satu hal menonjol hanya linimasa pesanan" dan "halaman lain sengaja tenang". Dasbor boleh memiliki satu kartu utama berwarna primary; halaman lain tetap tenang. Antarmuka tetap bukan situs pemasaran |
| Colors | `canvas` sebagai latar halaman, `surface` untuk dialog dan area formulir bila perlu. Primary juga untuk item menu aktif dan kartu statistik utama. Success, Warning, dan Error juga boleh sebagai pasangan container pada chip ikon (maksimum 52px) dan sebagai segmen grafik, tetap tidak untuk tombol atau area besar |
| Typography | Tambahkan lima gaya baru (2.4). Hapus kalimat "Display-s hanya untuk angka besar pada ringkasan". Tetap hanya bobot 400 dan 500 |
| Layout | Desktop (lg ke atas) punya bilah atas melayang di atas isi. Ringkasan angka = kartu statistik terpisah (bagian 5.2). Panel = kartu berbayang. Tabel di dalam kartu. Jarak antar kartu 20px (`gap-5`), padding halaman 28px (`px-7`) di desktop, 16px di ponsel |
| Elevation | Tambahkan tingkat `card`, `card-raised`, `nav-active` dan di mana dipakai. Hierarki kini dibentuk oleh latar `canvas` lawan kartu putih berbayang. Level 0 tidak lagi untuk semua panel |
| Shapes | Panel dan kartu 16px (`lg`). Chip ikon dan petak 12px (`md`). Lencana pil. Hapus "16px cadangan untuk wadah besar di ponsel" |
| Components | Perbarui Menu, Panel, Lencana. Tambah Kartu statistik, Chip ikon, Petak, Tab, Avatar, Bilah atas, Grafik (bagian 5) |
| Do's and Don'ts | Lihat daftar di bawah |

Do's and Don'ts: ubah tiga Don't dan satu Do berikut, lalu tambahkan satu Do:

- Ganti "Don't pakai emoji atau ikon dekoratif di lingkaran berwarna" menjadi: *Don't pakai emoji atau ikon dekoratif. Chip ikon hanya boleh membawa makna (status, kategori data) dengan pasangan container yang sesuai, dan selalu disertai teks.*
- Ganti "Don't susun halaman sebagai kisi kartu fitur ..., kartu di dalam kartu, atau deretan statistik berkartu terpisah" menjadi: *Don't susun halaman sebagai kisi kartu fitur yang bentuknya sama dan isinya promosi. Kartu statistik dan kartu data pada dasbor diizinkan. Kartu berbayang tidak berada di dalam kartu berbayang; petak tonal tanpa bayangan di dalam kartu diizinkan.*
- Ganti "Don't tambahkan warna aksen kedua, sudut di luar skala, atau gaya tipe di luar skala ..." menjadi: *Don't tambahkan warna aksen kedua, sudut di luar skala, atau gaya tipe di luar skala tanpa mengubah dokumen ini. Warna semantik (success, warning, error) hanya untuk makna status.*
- Ganti "Do bangun hierarki dengan permukaan tonal, garis tipis, dan jarak; bayangan hanya untuk lapisan yang melayang" menjadi: *Do bangun hierarki dengan latar `canvas`, kartu putih berbayang `card`, dan jarak; bayangan hanya dari token elevation, tidak ada bayangan di luar token.*
- Tambahkan Do: *Do beri setiap grafik label nilai yang terlihat dan keterangan teks (`aria-label`) sehingga tidak bergantung pada warna.*

Tetap berlaku: tanpa gradien, tanpa `backdrop-blur`, tanpa bayangan berwarna, tanpa hero dan ilustrasi, tanpa animasi masuk, satu tombol utama per layar.

## 4. Aturan konsistensi (`agent/konsistensi.json` dan `KonsistensiTest`)

Baca seluruh aturan dan kasusnya lebih dulu. Ubah hanya aturan yang bertentangan dengan bagian 3. Kemungkinan terkena: larangan kelas bayangan, larangan panel tanpa batas, bobot dan gaya tipe, larangan kartu bersarang. Untuk tiap perubahan:

- Izinkan `shadow-card`, `shadow-card-raised`, `shadow-nav-active`. Tetap larang `shadow-sm`, `shadow-md`, `shadow-lg`, `shadow-xl`, `shadow-2xl`, `shadow-[...]`, dan bayangan berwarna.
- Tetap larang `bg-gradient-*`, `backdrop-blur-*`, warna hex, `bg-[#...]`, `uppercase`, `tracking-*`, `italic`.
- Tambahkan kasus lolos dan gagal untuk setiap perubahan, supaya longgarnya aturan terbukti sempit.
- Jangan melonggarkan aturan yang tidak terkait.

## 5. Komponen

Semua komponen berada di `resources/views/components/ui/` (ikuti penamaan `x-ui.*` yang sudah ada) dan didokumentasikan di `docs/KIT_UI.md` dengan contoh pemakaian. Tiap komponen punya tes render (`$this->blade(...)`) yang memeriksa keluaran dan atribut aksesibilitas.

### 5.1 Panel (kartu)

`x-ui.panel` diubah menjadi kartu: `bg-surface-container-lowest rounded-lg shadow-card p-6`. Prop `flush` (tanpa padding, untuk tabel di dalam kartu). Tidak lagi punya batas. Seluruh halaman yang memakai panel otomatis mengikuti.

### 5.2 Kartu statistik: `x-ui.stat`

Props: `tone` (`primary`, `warning`, `success`, `error`), `value`, `label`, `hint` (opsional), `icon`, `featured` (bool), `href` (opsional).

- **Putih** (`featured` false): panel dengan padding 20px, baris: chip ikon 52px, lalu kolom: angka `stat-l`, label `label-l`, keterangan `body-s` `on-surface-variant`. Kartu tidak dapat diklik seluruhnya; tautan hanya bila `href` diberikan (pakai `<a>` pada label).
- **Utama** (`featured` true): latar `primary`, teks `on-primary`, bayangan `card-raised`, padding 20px 24px. Baris atas: chip ikon 44px berlatar `on-primary` 18% opasitas dan judul `title-m`. Tengah: angka `stat-hero` dan keterangan `body-m` berwarna `primary-container`. Bawah: `x-ui.button variant="on-primary"`.
- Pemetaan tone ke chip: primary = `primary-container` dengan `on-primary-container`; warning = `warning-container` dengan `on-warning-container`; success = `success-container` dengan `on-success-container`; error = `error-container` dengan `on-error-container`.

### 5.3 Chip ikon: `x-ui.icon-chip`

Props: `tone`, `icon`, `size` (`md` 40px dengan ikon 22px, `lg` 52px dengan ikon 26px). Sudut 12px (`rounded-md`). `aria-hidden` pada ikon; teks pendamping selalu ada. Tambahkan ikon Lucide `user-check` ke `resources/icons/` dan daftar di KIT_UI (belum ada). Semua ikon lain yang dipakai pratinjau sudah ada.

### 5.4 Petak: `x-ui.tile`

Dipakai di dalam kartu ketersediaan. Latar `surface-container-low`, sudut 12px, padding 16px, tanpa bayangan. Isi: chip ikon `md`, angka `stat-m`, label `body-m` `on-surface-variant`.

### 5.5 Tab: `x-ui.tabs` dan `x-ui.tab`

Tab primer M3: tinggi 44px, padding 16px, label `label-l`, tab aktif berwarna `primary` dengan garis bawah 3px `primary`, tab lain `on-surface-variant` dengan garis bawah transparan. Garis pemisah bawah `surface-container-high`. Tab adalah **tautan** (`<a>` dengan `aria-current="page"` pada yang aktif) ke URL yang sama dengan `?tab=...`; tanpa JavaScript. Baris tab boleh bergulir horizontal di ponsel (`overflow-x-auto`, item `shrink-0 whitespace-nowrap`). Lapisan keadaan hover, fokus, tekan seperti kontrol lain.

### 5.6 Avatar: `x-ui.avatar`

Lingkaran 40px, `primary-container` dengan `on-primary-container`, inisial dari dua kata pertama nama (maksimum dua huruf), `label-l`. `aria-hidden`; nama selalu ada di sebelahnya (atau `aria-label`).

### 5.7 Lencana status

Sudah ada. Ubah ke pil. Titik 6px tetap. Tinggi 24px, padding 10px, `label-m`.

### 5.8 Bilah atas

Kartu melayang `top-app-bar` di semua ukuran layar: (ponsel) tombol menu, pencarian, lonceng, avatar; (desktop) pencarian maksimal 460px, lonceng, avatar dan nama dua baris (nama, label peran). Kolom cari: pil 48px `surface-container`, ikon `search`, `<input type="search">` dengan `<label>` atau `aria-label`, lebar `min-w-0` agar tidak meluber di ponsel. Pencarian mengirim `GET` ke daftar pesanan dengan `?q=`. Lonceng = jumlah pesanan menunggu verifikasi (bukan notifikasi umum); `aria-label` "Pesanan menunggu verifikasi, {n}"; lencana `error` 18px bila n lebih dari 0; tautan ke daftar pesanan tab "Perlu tindakan".

### 5.9 Laci navigasi

Latar putih, lebar 280px, tepi kanan 1px `surface-container-high`. Kepala: tanda merek 40px (`primary`, sudut 12px, ikon `plus`) dan nama aplikasi `title-l`. Label grup (`label-m`, `on-surface-variant`) dan item 48px berbentuk pil (ikon 22px, teks `label-l`). Item aktif: `primary`, `on-primary`, `shadow-nav-active`. Lencana hitungan `error` di item Pesanan. Di bawah: blok pengguna (`surface-container-low`, sudut 16px, avatar, nama, peran) dengan tombol keluar yang adalah **form POST** ber-CSRF, bukan tautan. Laci tetap `sticky top-0 h-screen overflow-y-auto`; isi bergulir di dokumen sehingga tidak ada dua scrollbar vertikal. Di bawah `lg` laci menjadi laci modal berlatar scrim (mekanisme dari M-sebelumnya dipertahankan).

### 5.10 Tombol

Tambahkan varian `on-primary` (5.2). Varian lain tidak berubah.

### 5.11 Grafik batang: `x-ui.chart.bar`

Props: `labels` (mis. `['Sen','05/10', ...]` dua baris per hari), `values` (integer), `highlight` (indeks yang diberi warna `primary`; lainnya `chart-sky`), `title` (untuk `aria-label`), `unit?` (mis. "pesanan", dipakai di tooltip dan keterangan). Keluarannya: pembungkus `overflow-x-auto` berisi kotak `relative h-80 min-w-120` dengan `<canvas role="img" data-grafik="{JSON}">`, lalu tabel `sr-only`. Data lewat atribut JSON (`tipe`, `labels` berupa pasangan hari dan tanggal, `values`, `highlight`, `max`, `unit`), bukan skrip sebaris. Kanvas digambar Chart.js dari `resources/js/grafik-opsi.js`.

- Sumbu Y bilangan bulat mulai dari 0. `max` dihitung di Blade: bilangan genap terkecil yang ≥ max(2, nilai maksimum), jadi tepat tiga garis kisi (0, separuh, penuh) dengan langkah bulat. Kisi 1px `surface-container-high`, garis dasar (nilai 0) `outline-variant`, tanpa garis sumbu dan tanpa tanda sumbu. Label sumbu `body-s` `on-surface-variant`.
- Batang selebar 36px, sudut atas 8px (`rounded.sm`), sudut bawah 0; Chart.js sendiri mengecilkan radius bila batang lebih pendek dari 8px. Hari `highlight` berwarna `primary`, lainnya `chart-sky`. Nilai 0: batang rintisan setinggi 3px (radius 1,5px, `outline-variant`).
- Label nilai di atas batang (`label-l`, `on-surface`, 10px di atas ujung batang; untuk nilai 0, 11px di atas garis dasar) digambar plugin sebaris `labelNilai` di `grafik-opsi.js`, bukan paket tambahan. Plugin yang sama menggambar batang rintisan.
- Label hari dua baris (hari, tanggal) `body-s` `on-surface-variant`; hari ini (highlight) berbobot 500 dan berwarna `primary`.
- Tanpa judul dan legenda bawaan Chart.js. Tooltip bergaya Material: latar `inverse-surface`, teks `inverse-on-surface`, sudut 4px (`rounded.xs`), tanpa panah dan tanpa penanda warna, berbahasa Indonesia ("Sen 05/10" lalu "1 pesanan"); muncul bila kursor berada di kolom hari mana pun, termasuk hari bernilai 0.
- Tanpa animasi: `animation: false` pada render awal dan seterusnya, sehingga `prefers-reduced-motion` otomatis terpenuhi.
- Aksesibilitas: `role="img"` dengan `aria-label` yang menyebut semua nilai ("Grafik batang pesanan per hari: Sen 05/10 1 pesanan, ..."), ditambah tabel `sr-only` (caption = `title`, kolom Hari dan satuan) berisi data yang sama.

### 5.12 Grafik donat: `x-ui.chart.donut`

Props: `segments` (daftar `label`, `value`, `color`), `centerLabel`, `title`. Keluarannya: kotak `relative size-44` berisi `<canvas role="img" data-grafik="{JSON}">` dan lapisan HTML `aria-hidden` di tengah (total `stat-l`, di bawahnya `centerLabel` `body-s`), lalu legenda dan tabel `sr-only`. Data JSON: `tipe`, `segments` (`label`, `value`, `color`, `percent`), `unit`.

- Cincin dengan `radius: 81%` dan `cutout: 72%` (setara jari-jari 56 dan tebal 18 pada kanvas 160), mulai dari jam 12, tanpa garis tepi. Celah antar segmen 3px lewat opsi `spacing`; satu segmen penuh (100%) tanpa celah; segmen bernilai 0 tidak dikirim. Warna segmen = token bernama `color`, dibaca dari `--color-{color}`.
- Total 0: satu cincin `surface-container-highest`, angka 0, tanpa tooltip dan tanpa interaksi, tanpa legenda dan tabel.
- Legenda di bawah donat tetap berupa `<ul>` HTML: titik 10px berwarna segmen, label, nilai (tebal), persentase (dibulatkan; total boleh 99 atau 101). Hanya status dengan nilai lebih dari 0.
- Warna tetap per status Enum (bukan per urutan): selesai `success`; dibatalkan `outline`; ditolak `error`; menunggu verifikasi `warning`; terverifikasi `primary`; ditugaskan dan seterusnya sampai dalam perjalanan `chart-sky`. Jika Enum punya status lain, putuskan warnanya dengan prinsip yang sama dan catat di KIT_UI. Urutan segmen: nilai terbesar dulu, seri sama menurut urutan Enum; urutan, penyaringan, dan persentase dihitung di Blade.
- Tooltip, tanpa animasi, dan aksesibilitas sama dengan 5.11 ("Dibatalkan" lalu "2 pesanan, 20%"). `aria-label` menyebut semua segmen; tabel `sr-only` memuat status, jumlah, dan persentase.

### 5.13 Pemuatan Chart.js dan token di runtime

- Paket `chart.js` (npm, 4.x). `resources/js/grafik.js` mengimpor dan mendaftarkan hanya `BarController`, `DoughnutController`, `BarElement`, `ArcElement`, `CategoryScale`, `LinearScale`, `Tooltip`, dan `Legend` (legenda bawaan dimatikan), bukan `chart.js/auto`. Bentuk konfigurasi ada di `resources/js/grafik-opsi.js` sebagai fungsi murni.
- Bundel hanya dimuat di halaman yang punya grafik: `app.js` memanggil `import('./grafik')` hanya bila ada `canvas[data-grafik]`, sehingga Vite menghasilkan chunk tersendiri dan halaman lain tidak mengunduhnya.
- Warna, huruf, dan sudut dibaca dari variabel CSS di runtime (`--color-*`, `--text-*` beserta `--line-height` dan `--font-weight`, `--radius-*`, `--font-sans`); tidak ada hex di JavaScript. Tailwind 4 hanya mengeluarkan variabel tema yang dipakai kelas utilitas, karena itu `design-tokens.css` dihasilkan dengan `@theme static`. Kanvas digambar setelah Roboto dimuat (`document.fonts.load`), karena kanvas tidak menggambar ulang saat huruf selesai dimuat.
- Logika data tetap di PHP dan diuji (`KomponenGrafikTest`): `max` sumbu, urutan, penyaringan, dan persentase. Data harian dari `RingkasanDasbor` tetap diuji `RingkasanDasborTest`. `GrafikChartJsTest` menjaga impor modular, pemuatan dinamis, dan keberadaan setiap token yang dibaca JavaScript.
- Kelas geometri SVG (`App\Support\Chart\BarGeometry`, `DonutGeometry`, `Angka`) dan tes unitnya sudah dihapus; `App\Support\Chart\Warna` tinggal pemetaan nama token ke kelas titik legenda.

## 6. Dasbor Dinkes (halaman acuan)

Susunan dari atas ke bawah, selebar kolom isi:

1. Bilah atas (5.8).
2. Judul halaman: `page-title` "Dasbor", subjudul "Ringkasan pesanan dan ketersediaan Faskes. Angka diperbarui otomatis." di kiri; di kanan tombol tonal "Kelola ketersediaan" (tautan ke halaman ketersediaan).
3. Baris statistik (flex wrap, jarak 20px): kartu utama primary (lebar 1,5 bagian) dan tiga kartu putih (1 bagian).
   - Utama: "Menunggu verifikasi", angka = jumlah pesanan status menunggu verifikasi, keterangan "pesanan baru", tombol `on-primary` "Verifikasi pesanan" ke daftar pesanan tab "Perlu tindakan". Ikon `clock`.
   - "Terverifikasi", keterangan "menunggu penugasan", tone primary, ikon `user-check`.
   - "Sedang berjalan", keterangan "ditugaskan sampai di jalan", tone warning, ikon `ambulance`. Mencakup semua status aktif setelah terverifikasi sampai sebelum selesai.
   - "Selesai hari ini", keterangan "pesanan tuntas", tone success, ikon `circle-check`. Tanggal hari ini menurut zona waktu aplikasi (periksa `config('app.timezone')`; Sorong memakai WIT, `Asia/Jayapura`).
4. Baris grafik: kartu "Pesanan 7 hari terakhir" (subjudul rentang tanggal, mis. "5 sampai 11 Okt 2026"; 2 bagian lebar) dan kartu "Status pesanan" (subjudul "10 pesanan terbaru"; 1 bagian). Jumlah 10 adalah satu konstanta (`DASBOR_JUMLAH_PESANAN_TERBARU`). Batang menghitung pesanan yang dibuat per hari selama 7 hari terakhir termasuk hari ini; hari yang kosong tetap ditampilkan dengan nilai 0. Singkatan hari: Sen Sel Rab Kam Jum Sab Min.
5. Baris bawah: kartu "Ketersediaan" (subjudul "Semua Faskes, diperbarui otomatis"; tautan teks "Kelola"; 2×2 petak: ambulans, bed IGD, bed rawat inap, dokter; label "Ambulans tersedia", "Bed IGD tersedia", "Bed rawat inap tersedia", "Dokter tersedia"; ikon `ambulance`, `bed-double` (merah) , `bed-double` (kuning), `stethoscope` (hijau); sumber angka = data ketersediaan yang sudah ada) dan kartu "Pesanan terbaru" (2 bagian lebar, `flush`).
6. Kartu "Pesanan terbaru": judul, subjudul "Menampilkan {n} pesanan terbaru", tautan teks "Semua pesanan". Tab "Semua", "Perlu tindakan" (menunggu verifikasi dan terverifikasi), "Berjalan" (status aktif). Tabel empat kolom: Kode (tautan, `tabular-nums`, di bawahnya tanggal dan jam `body-s`), Pasien, Layanan (disertai lencana "Gawat" bila tingkat daruratnya gawat), Status (lencana). Tampil paling banyak 5 baris. Keadaan kosong per tab: "Belum ada pesanan di tab ini." Di bawah 640px tabel bergulir horizontal di dalam kartu, tab dan kepala tidak ikut bergulir.

Data dihitung di satu kelas layanan (mis. `App\Services\RingkasanDasbor`) yang mengembalikan array tegas. Batasi query: tidak lebih dari sepuluh query untuk seluruh dasbor, tanpa N+1 (uji dengan `DB::listen` yang menghitung query).

## 7. Responsif dan aksesibilitas

- Titik henti: `lg` (1024px) memisahkan laci tetap dan laci modal. Isi memakai flex wrap sehingga kartu turun satu kolom di ponsel.
- Tidak ada lebar halaman di atas viewport: uji tangkapan layar di 390px tanpa scroll horizontal pada halaman (hanya tabel dan grafik yang boleh bergulir di dalam kartunya).
- Semua kontrol: area sentuh 48px, cincin fokus 2px `primary` dengan jarak 2px, lapisan keadaan.
- Pasangan chip memenuhi kontras teks/ikon 4,5:1 (semua pasangan `container` dan `on-container` M3 sudah memenuhi); hitung dan catat bila Anda menambah pasangan.
- Grafik: nilai terlihat sebagai teks (label di atas batang, legenda donat), kanvas `role="img"` dengan keterangan lengkap, dan tabel `sr-only` berisi data yang sama; legenda berupa teks. `chart-sky` di atas putih kurang dari 3:1, karena itu nilai selalu tertulis, jangan mengandalkan warna.
- Hormati `prefers-reduced-motion`; tidak ada animasi masuk.

## 8. Menyapu peran dan halaman lain (D6)

- Dasbor Faskes: kartu statistik (jumlah tersedia per sumber daya milik Faskesnya) memakai `x-ui.stat` dan `x-ui.tile`. Dasbor sopir: tugas aktif dalam kartu besar, mobile-first, tanpa grafik. Halaman publik (buat pesanan, lacak): tanpa laci, panel baru, tanpa grafik.
- Daftar dan detail pesanan: tabel di dalam `x-ui.panel flush`, tab untuk filter status, lencana pil, linimasa tetap seperti sekarang.
- Formulir: `x-ui.panel`, tetap satu kolom lebar maksimum 36rem.
- Pencarian `?q=` di daftar pesanan: kode pesanan atau nama pasien, `LIKE` dengan parameter terikat, tes fitur.
- Enum: semua `->label()` huruf awal kapital hanya di awal kalimat.
- Satu bilah gulir vertikal per halaman (lihat 5.9).

## 9. Gerbang (jalankan di akhir setiap langkah)

1. Lint DESIGN.md (perintah yang sama dengan M1 sampai M7; lihat `package.json` atau `agent/`).
2. Pembuat token (`agent/tokens.mjs` atau skrip yang dipakai M2) dan pastikan `resources/css/` konsisten dengan DESIGN.md.
3. `php artisan test` (termasuk `KonsistensiTest`).
4. `npm run build`.
5. `php artisan route:list` tanpa galat.

Gagal di gerbang = langkah belum selesai. Laporkan nama tes yang gagal, jangan menurunkan standar tes.

## 10. Langkah

**D1. Aturan dan token.** Kerjakan bagian 2, 3, 4. Ubah `DESIGN.md`, `agent/konsistensi.json` (dan kasus tesnya), `agent/tokens.mjs` bila perlu, dan tambahkan bagian "Komponen dasbor (rencana)" ke `docs/KIT_UI.md` yang berisi daftar komponen bagian 5 dengan status "belum diimplementasikan". Belum ada perubahan pada Blade. Selesai bila gerbang lulus dan semua token baru (`canvas`, `chart-sky`, bayangan `card`, `card-raised`, `nav-active`, lima gaya tipe) ada sebagai variabel tema di CSS hasil pembuat token.

**D2. Komponen dasar.** Bagian 5.1 sampai 5.7 dan 5.10, ditambah ikon `user-check`. Tes render untuk tiap komponen. Perbarui KIT_UI dari "rencana" menjadi dokumentasi sebenarnya. Selesai bila halaman yang memakai panel dan lencana masih tampil benar dan semua tes lulus.

**D3. Grafik.** Bagian 5.11 sampai 5.13 dengan tes unit geometri dan tes render. Selesai bila contoh keluaran untuk data pratinjau (nilai 1,0,0,0,0,0,1 dan segmen 5,2,1,1,1) sama dengan pratinjau pada nilai geometri yang disebut di dokumen ini.

**D3b. Grafik Chart.js.** Keputusan pemilik proyek: grafik memakai Chart.js dan menggantikan 5.11 sampai 5.13 yang semula SVG sisi server. Pasang `chart.js` lewat npm, ganti `x-ui.chart.bar` dan `x-ui.chart.donut` dengan API Blade yang sama, hapus kelas geometri SVG dan tesnya, tambah tes render dan penjaga pemuatan. Selesai bila gerbang lulus dan dasbor tidak berubah dari sisi halaman.

**D4. Kerangka aplikasi.** Bagian 5.8 dan 5.9, latar `canvas`, jarak halaman. Terapkan ke layout semua peran. Selesai bila setiap halaman yang ada masih terbuka (`php artisan test` menyapu rute utama) dan tidak ada scroll horizontal di 390px.

**D5. Dasbor Dinkes.** Bagian 6, termasuk layanan data, tab, tes data (hitungan, zona waktu, tab), dan batas query. Selesai bila tampilan sama dengan tangkapan layar referensi pada data demo.

**D6. Peran lain dan sapuan halaman.** Bagian 8.

**D7. Penutupan.** Jalankan gerbang penuh, periksa kontras pasangan chip, perbarui `docs/KIT_UI.md` dan `docs/PROGRESS.md`, hapus kode dan token yang tidak terpakai (token `level-*` yang tidak dipakai boleh tetap), lalu tulis ringkasan perubahan. Setelah ini langkah penutup M8 yang lama boleh dikerjakan.
