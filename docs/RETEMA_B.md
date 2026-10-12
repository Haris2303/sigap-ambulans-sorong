# Retema total: arah B "Terang lembut"

Keputusan pemilik proyek: seluruh tampilan aplikasi SIGAP Ambulans diganti ke arah B ("Terang lembut"). Ini bukan perubahan dasbor saja. Token, kerangka halaman, komponen, dan semua halaman semua peran ikut berubah. Alasannya: tampilan M3 baseline dan dasbor Material (D1 sampai D7) dinilai terlalu datar dan kurang modern.

**Dokumen ini mengalahkan** `docs/DESAIN_DASBOR_MATERIAL.md` (D1 sampai D7), `DESIGN.md` (bagian prosa), `docs/KIT_UI.md`, dan `agent/konsistensi.json` bila ada pertentangan. Ubah berkas-berkas itu agar sesuai dokumen ini. Jangan menolak perubahan dengan alasan aturan lama. Di bagian atas `docs/DESAIN_DASBOR_MATERIAL.md` tambahkan satu baris: "Digantikan oleh docs/RETEMA_B.md".

Berkas referensi (baca semuanya sebelum mengerjakan langkah mana pun), semuanya di `docs/referensi-b/`:

- `B1-dasbor-1440.png`: dasbor Dinkes desktop (lebar 1440). Acuan susunan dan ukuran.
- `B2-komponen-1440.png`: komponen (tombol, isian, kartu pilihan, tab, lencana, daftar, keadaan kosong), layar ponsel "Buat pesanan", detail pesanan dengan linimasa, dialog, snackbar, catatan.
- `B3-ponsel-390.png`: dasbor di ponsel (lebar 390) dan menu terbuka.
- `B1-dasbor.html`, `B2-komponen.html`, `B3-ponsel.html`: sumbernya. Nilai warna di dalamnya hex mentah dan gaya sebaris. Ini referensi ukuran, bukan kode untuk disalin. Di aplikasi pakai komponen Blade dan token. Selisih 1 sampai 2 piksel dari papan boleh, token yang menang.

Papan menampilkan data demo yang lebih ramai daripada isi database sekarang (lihat langkah R5 untuk seeder demo).

## 1. Batasan (berlaku untuk semua langkah)

- Jangan menampilkan isi `.env` atau kredensial. Jangan menjalankan perintah DB yang merusak (`migrate:fresh`, `db:wipe`, `migrate:rollback`) pada DB nyata. Tes memakai SQLite memori.
- Jangan mengubah alur bisnis, aturan peran, rute yang sudah ada, atau skema database. Yang boleh: parameter kueri baru (`?tab=`, `?q=`, `?periode=`), kelas layanan baru, komponen Blade baru, seeder demo (R5), dan satu rute pratinjau komponen khusus lokal (R2).
- Warna di markup hanya lewat token. Tidak ada hex, tidak ada `bg-[#...]`, tidak ada `bg-gradient-*`, tidak ada `backdrop-blur`, tidak ada `uppercase`, `italic`, atau `tracking-*`. Pengecualian tunggal gradien: isi area grafik garis di Chart.js (bagian 7).
- Label status, peran, dan jenis diambil dari Enum (`->label()`), huruf kapital hanya di awal kalimat ("Dalam perjalanan", bukan "Dalam Perjalanan").
- Satu langkah per sesi. Setelah langkah selesai: jalankan gerbang (bagian 10), commit di branch `retema-b`, laporkan ringkas (apa yang berubah, hasil gerbang, apa yang perlu dilihat pengguna), lalu berhenti. Jangan mengerjakan langkah berikutnya sebelum diminta.
- Paket npm baru yang diizinkan: `@fontsource-variable/plus-jakarta-sans` dan `chart.js`. Selain itu tidak ada paket baru.
- Teks antarmuka dalam bahasa Indonesia, kalimat biasa (huruf kapital hanya di awal kalimat dan nama diri), tombol berupa kata kerja ("Verifikasi sekarang", "Kirim pesanan").

## 2. Arah B dalam satu paragraf

Terang dan ramah. Latar `surface` yang sangat muda kebiruan, kartu putih bersudut besar dengan bayangan lembut, bilah samping putih yang melayang, huruf Plus Jakarta Sans dengan judul tebal (800) dan angka besar. Warna dipakai untuk satu tugas: membawa makna. Biru `primary` untuk aksi dan item aktif, kartu navy (`secondary`) untuk satu hal yang paling butuh tindakan, dan keluarga warna status (kuning, biru, ungu, hijau, merah, abu) untuk chip, lencana, dan grafik. Tidak ada gradien dekoratif, ilustrasi, emoji, atau warna tanpa arti.

Pagar supaya hasilnya tidak menjadi "kisi kartu generik":

- Satu kartu navy per halaman paling banyak, hanya untuk hal yang menunggu tindakan (dasbor: pesanan menunggu verifikasi; sopir: tugas aktif).
- Warna chip dan grafik hanya dari tabel status (4.3) dan tabel tone (4.1). Warna avatar tidak berarti apa-apa tetapi selalu disertai nama.
- Data yang ditampilkan nyata dan spesifik (kode pesanan, nama, menit menunggu), bukan angka hiasan.
- Halaman data master dan formulir tenang: kartu putih, tipografi, dan jarak yang rapi. Warna kaya hanya di dasbor dan di tempat status berada.

## 3. Koreksi terhadap papan (aplikasi mengikuti dokumen ini)

Papan sudah diperbaiki, tetapi bila ada selisih, ini yang menang:

| Hal | Aturan |
|---|---|
| Teks sekunder | Satu warna: `on-surface-variant` (#5F6B80). Tidak ada abu-abu lain untuk teks, label sumbu, kepala tabel, atau label grup menu |
| Lencana hitungan dan titik lonceng | `error` (#CC3A24) dengan teks `on-error`. Bukan koral terang (kontras putih di atas koral hanya 2,8:1) |
| Batas isian dan kartu pilihan | `outline` (#7F8AA0), kontras 3:1 ke atas. Garis `outline-variant` hanya untuk pemisah dekoratif |
| Warna status | Satu tabel (4.3) untuk chip, lencana, dan segmen donat. Tidak boleh berbeda antara tabel dan grafik |
| Garis grafik utama | Kontras minimal 3:1 terhadap putih (`primary`, `chart-mint`). Segmen donat, sparkline, dan cincin boleh lebih terang karena nilainya selalu tertulis |
| Tinggi kontrol | Tombol 44px di desktop dan 48px di bawah `lg`; kolom isian 48px; item menu 48px; tombol ikon 48px |
| Jam | Format Indonesia dengan titik: "21.12". Sufiks "WIT" hanya di halaman detail |
| Filter periode | Berlaku untuk grafik, donat, dan kartu "Selesai". Tiga kartu pertama adalah hitungan saat ini (bagian 8.1) |
| "Diperbarui otomatis" | Jangan menulis klaim ini kecuali dasbor benar-benar menyegarkan sendiri. Tampilkan "Diperbarui 21.14" dari waktu server |

## 4. Lapisan 1: Token

### 4.1 Warna

Nama peran M3 yang sudah ada dipertahankan (komponen lama tetap jalan). Nilai diganti, dan peran baru ditambahkan. `canvas` (dari D1) diberi nilai sama dengan `surface` dan boleh dihapus di R9 setelah tidak ada pemakaian.

```yaml
colors:
  primary: "#1368CE"
  on-primary: "#FFFFFF"
  primary-container: "#E4EEFF"
  on-primary-container: "#0F55A8"
  secondary: "#102A56"            # navy: kartu utama, teks tombol di atasnya
  on-secondary: "#FFFFFF"
  secondary-container: "#EEF1F7"  # abu netral: lencana netral, chip netral
  on-secondary-container: "#475066"
  tertiary: "#7A5AF8"             # ungu: status aktif di jalan
  on-tertiary: "#FFFFFF"
  tertiary-container: "#EDE8FF"
  on-tertiary-container: "#5B3FD0"
  success: "#0B7A53"
  on-success: "#FFFFFF"
  success-container: "#DDF6EA"
  on-success-container: "#0B7A53"
  warning: "#A86300"
  on-warning: "#FFFFFF"
  warning-container: "#FFF1D6"
  on-warning-container: "#8A5200"
  error: "#CC3A24"
  on-error: "#FFFFFF"
  error-container: "#FFE9E4"
  on-error-container: "#B8341F"
  surface: "#F3F5FB"                       # latar halaman
  canvas: "#F3F5FB"                        # sama dengan surface (sementara)
  surface-container-lowest: "#FFFFFF"      # kartu, bilah samping, dialog
  surface-container-low: "#F7F8FD"         # hover baris, petak, kartu cincin
  surface-container: "#F3F5FB"             # trek tab, blok pengguna, pil legenda
  surface-container-high: "#EEF1F7"
  surface-container-highest: "#E7EAF3"     # garis dalam kartu, trek cincin
  on-surface: "#1B2437"
  on-surface-variant: "#5F6B80"
  outline: "#7F8AA0"                       # batas isian
  outline-variant: "#D0D6E4"               # pemisah dekoratif
  inverse-surface: "#1B2437"               # tooltip, snackbar
  inverse-on-surface: "#FFFFFF"
  inverse-primary: "#8DB8FF"               # aksi di snackbar
  selected: "#EEF4FF"                      # latar kartu pilihan terpilih
  scrim: "#1B2437"                         # dengan opasitas 45% di bawah dialog dan laci
  chart-mint: "#0FA582"
  chart-coral: "#FF6B57"
  chart-amber: "#F5A524"
  chart-slate: "#A3ADC2"
```

Aturan pemakaian:

- Pasangan container: chip, lencana, dan avatar memakai `*-container` dengan `on-*-container`. Pasangan solid (`primary`, `error`, `secondary`) dengan `on-*` hanya untuk tombol utama, lencana hitungan, dan kartu navy.
- Seri grafik: biru = `primary`, ungu = `tertiary`, sisanya `chart-*`. Tidak ada warna grafik lain.
- Dekorasi lingkaran di kartu navy memakai `primary` dengan opasitas 25% dan 15% (`bg-primary/25`, `bg-primary/15`), bukan token baru. Teks kecil di kartu navy memakai `primary-container`. Chip ikon di kartu navy memakai `on-secondary` 15%.
- Variabel CSS `--color-chart-*` harus ada di `:root` walau tidak dipakai kelas utilitas (Tailwind 4 tidak mengeluarkan variabel tema yang tak terpakai). Pakai `@theme static` untuk token `chart-*` atau cara setara, dan buktikan dengan memeriksa `public/build` bahwa variabelnya ada.

### 4.2 Pemetaan hex papan ke token

| Hex di papan | Token |
|---|---|
| #F3F5FB | `surface` |
| #FFFFFF | `surface-container-lowest` |
| #F7F8FD | `surface-container-low` |
| #EEF1F7 | `surface-container-high` (lencana netral: `secondary-container`) |
| #E7EAF3 | `surface-container-highest` |
| #D0D6E4 | `outline-variant` |
| #7F8AA0 | `outline` |
| #1B2437 | `on-surface` (tooltip dan snackbar: `inverse-surface`) |
| #5F6B80 | `on-surface-variant` |
| #1368CE | `primary` |
| #E4EEFF | `primary-container` |
| #0F55A8 | `on-primary-container` |
| #EEF4FF | `selected` |
| #102A56 | `secondary` |
| #BBD0F2 | `primary-container` |
| #DDF6EA, #0B7A53 | `success-container`, `on-success-container` |
| #FFF1D6, #8A5200 | `warning-container`, `on-warning-container` |
| #A86300 (ikon chip kuning) | `warning` |
| #FFE9E4, #B8341F | `error-container`, `on-error-container` |
| #CC3A24 | `error` |
| #EDE8FF, #5B3FD0 | `tertiary-container`, `on-tertiary-container` |
| #7A5AF8 | `tertiary` |
| #0FA582, #FF6B57, #F5A524, #A3ADC2 | `chart-mint`, `chart-coral`, `chart-amber`, `chart-slate` |
| #8DB8FF | `inverse-primary` |

### 4.3 Satu tabel status (sumber tunggal)

Tambahkan metode `tone()` pada Enum status (mengembalikan string tone). Semua lencana, chip, dan grafik membaca dari sini.

| Status | Tone | Pasangan chip | Warna grafik |
|---|---|---|---|
| Menunggu verifikasi | `warning` | `warning-container` / `on-warning-container` | `chart-amber` |
| Terverifikasi | `primary` | `primary-container` / `on-primary-container` | `primary` |
| Semua status aktif setelah terverifikasi sampai dalam perjalanan (ditugaskan, menuju lokasi, dan seterusnya, sesuai Enum) | `tertiary` | `tertiary-container` / `on-tertiary-container` | `tertiary` |
| Selesai | `success` | `success-container` / `on-success-container` | `chart-mint` |
| Ditolak | `error` | `error-container` / `on-error-container` | `chart-coral` |
| Dibatalkan | `neutral` | `secondary-container` / `on-secondary-container` | `chart-slate` |

Lencana "Gawat" memakai tone `error`. Status aktif yang berbeda memakai tone dan warna grafik yang sama, tetapi legendanya tetap menulis tiap status. Jika Enum punya status lain, putuskan dengan prinsip yang sama dan catat di KIT_UI.

Warna avatar tidak membawa makna: `crc32(nama) % 5` memilih salah satu pasangan `primary`, `error`, `success`, `tertiary`, `warning` (container dan on-container). Avatar `aria-hidden`; nama selalu terlihat di sebelahnya.

### 4.4 Tipografi

Font: Plus Jakarta Sans, dipasang lewat `@fontsource-variable/plus-jakarta-sans` (self-hosted, tanpa CDN). Hapus impor Roboto di R9 setelah tidak ada pemakaian. Bobot yang dipakai: 400, 600, 700, 800. Semua angka memakai `tabular-nums`.

| Gaya (`typography`) | Ukuran / tinggi baris / bobot / jarak huruf | Dipakai untuk |
|---|---|---|
| `stat-hero` | 56 / 62 / 800 / -0.03em | angka di kartu navy |
| `stat-l` | 38 / 44 / 800 / -0.03em | angka di kartu statistik putih |
| `page-title` | 30 / 38 / 800 / -0.025em | judul halaman, sapaan |
| `page-title-s` | 26 / 34 / 800 / -0.025em | judul halaman di bawah `lg` |
| `title-xl` | 22 / 28 / 800 / -0.02em | kode pesanan di halaman detail |
| `title-l` | 18 / 26 / 800 / -0.01em | judul kartu, judul dialog |
| `title-m` | 16 / 24 / 700 / 0 | label di kartu navy, judul bilah publik |
| `brand` | 19 / 24 / 800 / -0.02em | nama aplikasi di menu |
| `ring-value` | 16 / 20 / 800 / 0 | angka di dalam cincin ketersediaan |
| `body-l` | 16 / 24 / 400 / 0 | |
| `body-m` | 14 / 20 / 400 / 0 | teks isi |
| `body-s` | 13 / 18 / 400 / 0 | keterangan, subjudul kartu |
| `label-l` | 14 / 20 / 600 / 0 | label tombol, label kolom isian, teks menu, label kartu statistik |
| `label-m` | 12 / 16 / 600 / 0 | label grup menu, kepala tabel, bantuan isian |
| `chip` | 12 / 16 / 700 / 0 | lencana, chip delta, lencana hitungan |

Papan memakai bobot 700 di beberapa label yang di sini 600; token yang menang. Tidak ada teks di bawah 12px. Pastikan `agent/tokens.mjs` mengeluarkan `--text-<nama>--line-height`, `--font-weight`, dan `--letter-spacing` (Tailwind 4) sehingga satu kelas `text-title-l` memuat ukuran, tinggi baris, bobot, dan jarak huruf. Bila `tokens.mjs` belum mendukung `letterSpacing`, tambahkan.

### 4.5 Bentuk (`rounded`)

| Token | Nilai | Dipakai untuk |
|---|---|---|
| `sm` | 10px | segmen di dalam trek tab, ikon kartu pilihan |
| `md` | 14px | tombol, isian, item menu, chip ikon, kartu pilihan, snackbar, catatan, keadaan kosong |
| `lg` | 18px | kontrol melayang (filter periode, tombol ikon), baris tabel, petak dan kartu cincin, blok pengguna |
| `xl` | 24px | kartu dan panel |
| `2xl` | 28px | bilah samping, dialog, laci ponsel |
| `full` | 9999px | lencana, chip delta, pil legenda, avatar, tombol hitungan |

Papan memakai 9, 11, 12, 13, 16, 22 di beberapa tempat; semuanya dilebur ke skala ini. Setelah nilai diganti, telusuri semua `rounded-*` di Blade.

### 4.6 Elevasi (`elevation`)

Warna bayangan adalah tinta biru tua `rgb(30 41 100)`, bukan hitam.

| Token (kelas) | Nilai | Dipakai untuk |
|---|---|---|
| `card` (`shadow-card`) | `0 10px 30px -12px rgb(30 41 100 / .14), 0 1px 2px rgb(30 41 100 / .04)` | kartu, panel, bilah samping, kartu navy |
| `control` (`shadow-control`) | `0 4px 14px -8px rgb(30 41 100 / .20)` | kontrol melayang: filter periode, tombol ikon, bilah atas ponsel |
| `chip` (`shadow-chip`) | `0 2px 8px -2px rgb(30 41 100 / .20)` | segmen aktif di trek tab dan segmented |
| `overlay` (`shadow-overlay`) | `0 24px 60px -20px rgb(30 41 100 / .35)` | dialog, menu tarik, laci ponsel, snackbar |

Di R1 pertahankan token lama (`level-*`, `card-raised`, `nav-active`) bersama yang baru supaya tidak ada gaya yang hilang diam-diam. Pemakaian dialihkan di R2 sampai R8 (menu tarik, dialog, snackbar menjadi `overlay`; kartu statistik utama menjadi `card`; item menu aktif tanpa bayangan). Penghapusan token lama di R9.

### 4.7 Gerak

- Interaksi: transisi 150ms pada `background-color`, `color`, `border-color`, `box-shadow`. Lapisan keadaan (hover, tekan, fokus) tetap seperti sekarang, hanya warnanya mengikuti token baru. Baris yang dapat diklik berubah ke `surface-container-low` saat hover.
- Buka dan tutup: dialog memudar dan membesar dari 0,96 ke 1 dalam 200ms; laci ponsel bergeser dari kiri 200ms; scrim memudar 200ms.
- Grafik menggambar masuk sekali, 700ms, `easeOutQuart`, tidak diulang saat data tidak berubah.
- Tidak ada animasi masuk untuk bagian halaman, tidak ada hitung-naik angka, tidak ada gerak yang bukan jawaban atas aksi pengguna (kecuali gambar-masuk grafik).
- `prefers-reduced-motion: reduce`: semua transisi dan animasi dimatikan.

### 4.8 Pasangan kontras wajib dan tesnya

Tambahkan `agent/kontras.json` (daftar pasangan di bawah) dan sebuah tes (`KontrasTokenTest`) yang membaca front matter `DESIGN.md`, menghitung rasio kontras WCAG, dan gagal bila ada pasangan di bawah batas. Daftar ini sudah diverifikasi pada nilai 4.1; tes menjaga supaya perubahan token berikutnya tidak merusaknya.

Teks (minimal 4,5:1): `on-surface` pada `surface`, `surface-container-lowest`, `selected`; `on-surface-variant` pada `surface`, `surface-container-lowest`, `surface-container-low`, `surface-container-high`, `primary-container`; `on-primary` pada `primary`; `primary` pada `surface`, `surface-container-lowest`, `primary-container`, `selected`; `on-primary-container` pada `primary-container`; `on-secondary` pada `secondary`; `primary-container` pada `secondary`; `secondary` pada `surface-container-lowest`; `on-secondary-container` pada `secondary-container`; `on-tertiary` pada `tertiary`; `on-tertiary-container` pada `tertiary-container`; `on-success` pada `success`; `on-success-container` pada `success-container`; `on-warning` pada `warning`; `on-warning-container` pada `warning-container`; `on-error` pada `error`; `on-error-container` pada `error-container`; `error` pada `surface` dan `surface-container-lowest`; `inverse-on-surface` dan `inverse-primary` pada `inverse-surface`.

Bukan teks (minimal 3:1): `outline` pada `surface-container-lowest` dan `surface`; `primary`, `tertiary`, `chart-mint` pada `surface-container-lowest`; `warning` pada `warning-container`; `success` pada `success-container`.

Nilai terendah yang lolos dan perlu dijaga: `on-tertiary` pada `tertiary` (4,52), `primary` pada `primary-container` (4,61), `error` pada `surface` (4,59), `on-success-container` pada `success-container` (4,70).

### 4.9 Teks DESIGN.md yang diganti

| Bagian | Ubah menjadi |
|---|---|
| Overview | Ganti dengan paragraf bagian 2 (versi ringkas). Hapus klaim "satu hal menonjol hanya linimasa" dan "halaman lain sengaja tenang". Antarmuka tetap bukan situs pemasaran |
| Colors | Ganti isi dengan bagian 4.1: `surface` untuk latar halaman, kartu putih, `primary` untuk aksi dan item aktif, `secondary` navy untuk satu kartu utama per halaman, keluarga warna status, `chart-*` untuk grafik. Tambahkan: warna tidak pernah satu-satunya pembawa informasi |
| Typography | Ganti dengan tabel 4.4. Hapus "hanya bobot 400 dan 500", "Display-s hanya untuk angka besar pada ringkasan", dan larangan jarak huruf (jarak huruf kini dari token, bukan dari kelas) |
| Layout | Desktop: bilah samping putih melayang (248px) di kiri, tanpa bilah atas; kepala halaman berisi sapaan, filter periode, pencarian, lonceng. Jarak antar kartu 20px, padding halaman 16px, padding kartu 24px (20px di ponsel). Konten dibatasi `max-w-screen-2xl`. Di bawah `lg` ada bilah atas melayang dan laci modal |
| Elevation | Ganti dengan tabel 4.6. Hierarki dibentuk oleh latar `surface` lawan kartu putih berbayang |
| Shapes | Ganti dengan tabel 4.5 |
| Components | Perbarui sesuai bagian 6, tambahkan komponen baru (kartu statistik, chip ikon, sparkline, cincin, tab pil, segmented, avatar, grafik) |
| Motion | Ganti dengan 4.7 |
| Do's and Don'ts | Ganti seluruhnya dengan daftar di bawah |

Do's and Don'ts baru:

- Do beri warna hanya bila membawa makna (status, kategori data, satu aksi utama). Dekorasi tanpa makna dilarang. Dua pengecualian: warna avatar (dipilih dari hash nama, selalu disertai nama) dan dua lingkaran di kartu navy.
- Do bangun hierarki dengan latar `surface`, kartu putih berbayang `card`, dan jarak 20px. Bayangan hanya dari token `elevation`.
- Do beri setiap angka dan grafik teks yang terlihat (nilai, legenda) serta keterangan `aria-label` dan tabel `sr-only`, sehingga informasi tidak bergantung pada warna.
- Do pakai satu tombol utama (`primary`) per layar dan per dialog.
- Do pakai kartu navy (`secondary`) paling banyak satu per halaman, untuk hal yang menunggu tindakan.
- Don't pakai emoji, ilustrasi, atau ikon dekoratif. Ikon di chip hanya membawa makna dan selalu disertai teks.
- Don't pakai gradien dekoratif. Satu-satunya gradien: isi area grafik garis di Chart.js.
- Don't pakai warna di luar token, hex, `bg-[...]`, bayangan di luar token, `backdrop-blur`, `uppercase`, `italic`, atau `tracking-*` di markup.
- Don't susun halaman sebagai kisi kartu identik berisi promosi. Kartu berbayang tidak berada di dalam kartu berbayang; petak tonal tanpa bayangan di dalam kartu boleh.
- Don't pakai animasi masuk pada bagian halaman. Gerak hanya jawaban atas aksi pengguna dan gambar-masuk grafik.
- Don't pakai teks di bawah 12px, atau abu-abu lain untuk teks sekunder selain `on-surface-variant`.
- Don't pakai batas isian dengan kontras di bawah 3:1.
- Tetap berlaku: tanpa mode gelap, tanpa hero pemasaran.

### 4.10 Aturan konsistensi (`agent/konsistensi.json` dan `KonsistensiTest`)

Baca seluruh aturan dan kasusnya lebih dulu. Ubah hanya yang bertentangan dengan 4.9:

- Izinkan `shadow-card`, `shadow-control`, `shadow-chip`, `shadow-overlay`. Tetap larang `shadow-sm`, `shadow-md`, `shadow-lg`, `shadow-xl`, `shadow-2xl`, `shadow-[...]`, dan bayangan berwarna (`shadow-blue-500/50` dan sejenisnya).
- Izinkan `rounded-sm|md|lg|xl|2xl|full` sesuai skala baru; larang `rounded-[...]`.
- Izinkan bobot dari token tipografi (kelas `text-<gaya>`); larang `font-thin` sampai `font-black` dan `font-<angka>` langsung di markup.
- Izinkan kartu putih berbayang sebagai panel; tetap larang kartu berbayang di dalam kartu berbayang.
- Tetap larang `bg-gradient-*`, `backdrop-blur-*`, hex, `bg-[#...]`, `uppercase`, `italic`, `tracking-*`, dan kelas warna bawaan Tailwind (`text-gray-*`, `bg-slate-*`, dan sebagainya).
- Tambahkan aturan: kelas `text-*` ukuran mentah (`text-sm`, `text-xs`) dilarang; pakai gaya token.
- Tambahkan kasus lolos dan gagal untuk setiap perubahan, supaya longgarnya aturan terbukti sempit. Jangan melonggarkan aturan yang tidak terkait.

## 5. Lapisan 2: Kerangka halaman

### 5.1 Desktop (`lg` ke atas)

Susunan: akar halaman `bg-surface p-4`, flex dengan jarak 20px: bilah samping 248px dan kolom isi.

- **Bilah samping** (`<aside>`/`<nav aria-label="Menu utama">`): `bg-surface-container-lowest rounded-2xl shadow-card`, padding 22px 16px, `sticky top-4` dengan tinggi `calc(100vh - 2rem)` dan `overflow-y-auto` (satu bilah gulir vertikal per halaman).
  - Merek: tanda 42px (`primary`, `rounded-md`, ikon `plus` 24px, garis 2,25) dan "SIGAP" gaya `brand`.
  - Label grup ("Operasional", "Data master"): `label-m` `on-surface-variant`, padding 14px 12px 6px.
  - Item menu: tinggi 48px, padding 0 14px, jarak 12px, `rounded-md`, ikon 21px, teks `label-l` `on-surface-variant`. Aktif: `bg-primary-container text-primary` dengan `aria-current="page"`. Hover: `bg-surface-container-low`.
  - Lencana hitungan di item Pesanan (jumlah menunggu verifikasi): `error`/`on-error`, `rounded-full`, min 22px, gaya `chip`; teks `sr-only` " menunggu verifikasi".
  - Blok pengguna di dasar (`mt-auto`): `bg-surface rounded-lg` padding 12px, avatar 40px, nama (`label-l`), peran (`body-s`), dan tombol keluar yang adalah **form POST** ber-CSRF (tombol ikon `logout`, `aria-label="Keluar"`), bukan tautan.
- **Kolom isi**: `min-w-0`, `max-w-screen-2xl`, jarak vertikal 20px, padding `pt-1.5 pr-2 pb-6`.
- **Kepala halaman** (`<header>`), flex, jarak 14px, rata tengah vertikal:
  - Kiri (flex-1): `<h1>` gaya `page-title`; di bawahnya `body-m` `on-surface-variant` (mis. tanggal dan satu kalimat konteks).
  - Kanan: segmen filter periode (bila halaman memakainya), tombol ikon pencarian, tombol ikon lonceng.
  - Dasbor: judul = sapaan "Selamat {pagi|siang|sore|malam}, {nama depan}" menurut jam `Asia/Jayapura` (00.00 sampai 10.59 pagi, 11.00 sampai 14.59 siang, 15.00 sampai 17.59 sore, 18.00 sampai 23.59 malam). Subjudul: "Minggu, 11 Oktober 2026. Ada 2 pesanan yang menunggu Anda." (bila 0: "Tidak ada pesanan yang menunggu Anda."). Tanggal lewat `translatedFormat('l, j F Y')` dengan locale `id`.
  - Halaman lain: judul = nama halaman, subjudul = satu kalimat fungsi halaman.
- **Pencarian**: tombol ikon `search` 48px (`bg-surface-container-lowest rounded-lg shadow-control`, `aria-label="Cari pesanan"`) yang membuka `<dialog>` native "Cari pesanan" berisi satu kolom `q` dan tombol "Cari"; `GET` ke daftar pesanan dengan `?q=`. Gunakan JavaScript secukupnya (`showModal`, tutup dengan Esc dan klik di luar); fokus kembali ke tombol saat ditutup. Pencarian mencocokkan kode pesanan atau nama pasien (`LIKE` dengan parameter terikat, tes fitur). Di halaman Pesanan, kolom cari juga selalu tampil di atas daftar (tinggi 48px, `rounded-md`, ikon `search`), karena pencarian adalah fungsi utama halaman itu.
- **Lonceng**: tautan ke daftar pesanan tab "Perlu tindakan". `aria-label="Pesanan menunggu verifikasi, {n}"`. Titik `error` 10px dengan tepi putih 2px hanya bila n lebih dari 0.

### 5.2 Ponsel dan tablet (di bawah `lg`)

Lihat `B3-ponsel-390.png`.

- Padding halaman 16px, jarak antar bagian 16px.
- **Bilah atas** melayang (`bg-surface-container-lowest rounded-xl shadow-card`, padding 8px, jarak 10px): tombol menu 48px (`bg-surface rounded-lg`), tanda merek 36px dan "SIGAP", lonceng 48px, avatar 40px. Sticky di atas (`sticky top-4 z-20`).
- **Laci menu**: `<dialog>` atau elemen dengan `role="dialog" aria-modal="true"`, lebar 316px, melayang 12px dari tepi (kiri, atas, bawah), `rounded-2xl shadow-overlay`, isi sama dengan bilah samping desktop. Scrim `bg-scrim/45`. Tutup dengan Esc, klik scrim, dan tombol menu. Fokus terjebak di laci selama terbuka, kembali ke tombol menu saat ditutup. Mekanisme yang sudah ada dipertahankan bila sudah memenuhi ini.
- Sapaan `page-title-s`. Filter periode selebar penuh, tiga segmen sama lebar.
- Tidak ada scroll horizontal pada halaman di 390px. Hanya tabel dan grafik yang boleh bergulir di dalam kartunya (tabel pesanan berubah menjadi daftar baris, lihat 6.9).

### 5.3 Layout publik dan masuk

- **Publik** (buat pesanan, lacak): tanpa bilah samping. Kolom `max-w-md` di tengah di layar lebar, latar `surface`. Bilah atas 60px (`bg-surface-container-lowest`, tombol kembali, judul `title-m`). Aksi utama form ada di bilah bawah lekat (`sticky bottom-0`, putih, padding 12px 16px 16px, garis atas `outline-variant`) berisi satu tombol penuh lebar. Lihat layar ponsel di `B2-komponen-1440.png`.
- **Masuk**: kartu `max-w-sm` di tengah, tanda merek 48px, judul "Masuk" gaya `page-title-s`, dua isian, tombol utama penuh lebar. Galat kredensial dalam `x-ui.note tone="error"` di atas isian.

## 6. Lapisan 3: Komponen

Semua di `resources/views/components/ui/` (penamaan `x-ui.*` yang sudah ada), didokumentasikan di `docs/KIT_UI.md` dengan contoh pemakaian, dan masing-masing punya tes render (`$this->blade(...)`) yang memeriksa keluaran, token (bukan hex), dan atribut aksesibilitas. Cocokkan nama dengan repo; bila komponen D1 sampai D7 sudah ada, ubah, jangan menduplikasi.

### 6.1 `x-ui.panel` (kartu)

`bg-surface-container-lowest rounded-xl shadow-card p-5 lg:p-6`, tanpa batas. Prop `flush` (tanpa padding, untuk tabel), `as`. Kepala kartu opsional lewat slot `header` (judul `title-l`, subjudul `body-s` `on-surface-variant`, slot aksi di kanan, jarak bawah 12px).

### 6.2 `x-ui.button`

Radius `md`, label `label-l`, ikon opsional 16 sampai 18px di kiri. Tinggi `h-12 lg:h-11` (default) dan `h-10 lg:h-9` (`size="sm"`). Padding horizontal 20px (12px untuk `ghost`).

| Varian | Tampilan |
|---|---|
| `primary` | `bg-primary text-on-primary` |
| `secondary` | `bg-surface-container text-on-surface border border-outline-variant` |
| `ghost` | tanpa latar, `text-primary` |
| `danger` | `bg-error-container text-on-error-container` (tonal) |
| `on-secondary` | `bg-surface-container-lowest text-secondary`, hanya di atas kartu navy |

Fokus: cincin 2px `primary` dengan jarak 2px. Nonaktif: opasitas 40%, tidak ada bayangan. Tombol yang memicu permintaan lama memakai `aria-busy` dan menonaktifkan klik ganda.

### 6.3 Isian: `x-ui.field`, `x-ui.select`, `x-ui.textarea`

Label di atas isian (`label-l`, bukan label mengambang), tinggi isian 48px, `rounded-md`, `border border-outline bg-surface-container-lowest`, padding 0 14px, teks `body-m`. Fokus: batas `primary` dan cincin 3px `primary` 18% opasitas (`ring-primary/20`). Galat: batas `error`, pesan `label-m` `text-error` di bawah, `aria-invalid="true"` dan `aria-describedby`. Bantuan: `label-m` `on-surface-variant`. Textarea minimal 84px. Select: `appearance-none` dengan ikon `chevron-down` 18px di kanan. Tanda wajib ditulis dalam label ("(wajib)") bukan bintang berwarna.

### 6.4 Kartu pilihan: `x-ui.choice-group` dan `x-ui.choice`

Grup radio tampil sebagai kisi (3 kolom, jarak 10px). Tiap pilihan adalah `<label>` berisi `<input type="radio" class="peer sr-only">`, petak ikon 30px (`rounded-sm bg-surface-container text-on-surface-variant`), dan teks `label-l`. Batas `outline`, `rounded-md`, padding 12px 10px. Terpilih: batas `primary` 1px ditambah cincin dalam 1px `primary`, latar `selected`, petak ikon berlatar `primary` dengan ikon `on-primary`. Fokus keyboard: cincin fokus pada `peer-focus-visible`. Dipakai untuk jenis layanan.

### 6.5 Tab dan segmented

Tiga tampilan dari satu pola:

- `x-ui.tabs` (varian `track`, default): trek `bg-surface-container rounded-md p-1 gap-0.5`, item tinggi 36px `rounded-sm` teks `label-m` `on-surface-variant`; aktif `bg-surface-container-lowest text-on-surface shadow-chip`. Item adalah **tautan** (`<a>` dengan `aria-current="page"` pada yang aktif) ke URL yang sama dengan `?tab=...`, tanpa JavaScript. Di ponsel baris tab boleh bergulir horizontal (`overflow-x-auto`, item `shrink-0 whitespace-nowrap`).
- `x-ui.tabs variant="floating"` (filter periode): trek `bg-surface-container-lowest rounded-lg p-1 shadow-control`, item tinggi 38px (40px di ponsel) `rounded-md` `label-l`; aktif `bg-primary text-on-primary`. Tautan ke `?periode=hari|7|30`.
- `x-ui.segmented`: radio dalam form (mis. "Biasa" / "Gawat"), tampilan sama dengan varian `track`, tinggi 36px, setiap segmen `<label>` dengan `<input type="radio" class="peer sr-only">`.

### 6.6 Lencana dan chip

- `x-ui.badge` (status): tinggi 26px (24px untuk `size="sm"`), padding 0 11px, `rounded-full`, gaya `chip`, titik 6px berwarna `currentColor` di kiri (prop `dot`, default aktif untuk status). Props `tone` (`primary`, `warning`, `success`, `error`, `tertiary`, `neutral`). Pasangan sesuai 4.3. Teks selalu ada.
- Chip delta/keterangan (`x-ui.badge` tanpa `dot`, `size="sm"`): min tinggi 24px, boleh membungkus ke dua baris di ponsel (`py-0.5`).
- `x-ui.icon-chip`: `tone`, `icon`, `size` (`md` 40px dengan ikon 21px, `lg` 44px dengan ikon 22px), `rounded-md`, warna latar dan ikon menurut tabel di bawah. `aria-hidden`; teks pendamping selalu ada.

| Tone chip ikon | Latar | Ikon |
|---|---|---|
| `primary` | `primary-container` | `primary` |
| `warning` | `warning-container` | `warning` |
| `success` | `success-container` | `on-success-container` |
| `error` | `error-container` | `on-error-container` |
| `tertiary` | `tertiary-container` | `on-tertiary-container` |
| `neutral` | `secondary-container` | `on-secondary-container` |

### 6.7 `x-ui.avatar`

Lingkaran, ukuran `md` 40px dan `sm` 36px, teks gaya `chip`, inisial dari dua kata pertama nama (maksimum dua huruf), warna menurut 4.3 (hash nama). `aria-hidden`; nama selalu terlihat di sebelahnya.

### 6.8 `x-ui.stat` (kartu statistik) dan `x-ui.hero-stat`

`x-ui.stat` (kartu putih, padding 24px dan 16px di ponsel): baris atas: `icon-chip lg` di kiri dan sparkline 92×38 di kanan (disembunyikan di bawah `sm`); angka `stat-l` dengan jarak atas 14px; label `label-l`; chip keterangan. Props: `tone`, `icon`, `value`, `label`, `hint` (teks chip), `hint-tone`, `series` (array angka untuk sparkline, opsional), `href` (opsional; tautan pada label, kartu tidak dapat diklik seluruhnya).

`x-ui.hero-stat` (kartu navy): `bg-secondary text-on-secondary rounded-xl shadow-card`, padding 24px, `overflow-hidden relative`, dua lingkaran dekoratif (`aria-hidden`, `bg-primary/25` 170px di kanan atas dengan offset -50px dan `bg-primary/15` 140px di kanan bawah dengan offset -70px, di bawah konten). Baris atas: `icon-chip` (latar `on-secondary` 15%, ikon `on-secondary`) dan judul `title-m`. Angka `stat-hero`, keterangan `body-m` `text-primary-container`, tombol `variant="on-secondary"` (tinggi 44px). Tidak ada sparkline.

### 6.9 Tabel dan daftar: `x-ui.table`

`<table>` semantik di dalam `x-ui.panel flush` (padding kartu diberikan pada pembungkus agar baris bulat tidak terpotong). Kepala: `label-m` `on-surface-variant`, padding 8px, `scope="col"`. Baris: padding 12px 8px, `rounded-lg` lewat `border-separate border-spacing-y-1` dengan sel pertama `rounded-l-lg` dan terakhir `rounded-r-lg`, hover `bg-surface-container-low`. Tanpa garis pemisah baris. Seluruh baris dapat diklik lewat tautan pada nama pasien (stretched link: `after:absolute after:inset-0`, baris `relative`); chevron akhir adalah hiasan (`aria-hidden`, `on-surface-variant`). Sel pasien: avatar 40px, nama `label-l`, kode `body-s` `on-surface-variant` `tabular-nums`. Sel layanan: teks, plus lencana "Gawat" bila gawat. Sel status: `x-ui.badge`. Jam: `body-s` `on-surface-variant`.

Di bawah `md` tabel menjadi daftar baris tanpa markup ganda: `thead` menjadi `sr-only`, `tr` menjadi grid tiga kolom (`40px 1fr auto`), `td` ditempatkan: avatar, (nama di atas, "kode, layanan" di bawah), (lencana status di atas, jam di bawah, rata kanan). Lihat daftar "Pesanan terbaru" di `B3-ponsel-390.png`. Pemisah antar baris di ponsel: garis 1px `surface-container-highest`.

Keadaan kosong per tab: `x-ui.empty` (6.11).

### 6.10 Cincin ketersediaan: `x-ui.ring-tile`

Petak `bg-surface-container-low rounded-lg`, padding 14px 8px, pusat, jarak 6px. Cincin SVG 64px (jari-jari 26, tebal 7, ujung bulat, trek `surface-container-highest`), isi `primary`; bila tersedia kurang dari 12% dari total (konstanta `AMBANG_MENIPIS = 0.12`) isi `chart-coral`. Di tengah cincin angka tersedia (`ring-value`). Di bawah: label (`label-l`) dan "dari {total}" (`body-s` `on-surface-variant`); bila menipis, teks menjadi "dari {total}, menipis" dengan `text-on-error-container` bobot 600. Total 0 menampilkan cincin kosong dan "dari 0". `role="img"` pada cincin dengan `aria-label` "{label}: {tersedia} dari {total}" (ditambah ", menipis").

### 6.11 Keadaan kosong, catatan, snackbar, dialog

- `x-ui.empty`: batas putus-putus 1px `outline-variant`, `rounded-md`, padding 22px, pusat, petak ikon 44px (`surface-container`, `rounded-md`, ikon `on-surface-variant`), judul `label-l`, teks `body-s` `on-surface-variant`, aksi opsional (tombol `secondary` `sm`). Teks mengarahkan tindakan ("Pesanan baru muncul di sini setelah dibuat.").
- `x-ui.note` (`tone` `warning`, `info`, `error`, `success`): `rounded-md`, padding 12px 14px, ikon 18px, pasangan container, teks bobot 600.
- Snackbar: `bg-inverse-surface text-inverse-on-surface rounded-md` padding 14px 16px, aksi `text-inverse-primary` `label-l`, `role="status"`, `shadow-overlay`.
- Dialog: `<dialog>` native, `rounded-2xl bg-surface-container-lowest shadow-overlay p-6`, lebar `min(92vw, 28rem)`, judul `title-l`, isi `body-m` `on-surface-variant`, tombol rata kanan (ghost "Batal", lalu aksi; aksi merusak = varian `danger`). Backdrop `bg-scrim/45`. Fokus awal pada elemen aman (bukan tombol merusak). Esc menutup.

### 6.12 Linimasa dan daftar info

- Linimasa pesanan (`x-ui.timeline`): garis vertikal 2px `outline-variant` di kiri (jarak 26px dari konten), titik 12px (langkah lampau: putih dengan batas 2px `outline-variant`; langkah sekarang: terisi `primary`), baris dua kolom (56px waktu `on-surface-variant` `tabular-nums`, lalu judul `label-l` dan keterangan `body-s` `on-surface-variant`); langkah sekarang berbobot 700. Waktu format "21.12".
- Daftar info (`<dl>`): label `label-m` `on-surface-variant`, nilai `body-m`, jarak 16px.

### 6.13 Ikon

Lucide lewat `x-ui.ikon`, garis 1,75 (2,25 untuk tanda merek). Ukuran: menu 21px, chip 21 atau 22px, tombol ikon 21px, tombol 16 sampai 18px. Pastikan ada di `agent/icons.txt`: `layout-dashboard`, `clipboard-list`, `bed-double`, `building-2`, `ambulance`, `user`, `users`, `log-out`, `search`, `bell`, `menu`, `clock`, `user-check`, `circle-check`, `stethoscope`, `plus`, `chevron-down`, `chevron-right`, `chevron-left`, `check`, `map-pin`. Tambahkan yang belum ada (daftar `agent/icons.txt` dan berkas ikon).

## 7. Lapisan 4: Grafik dan data

Kartu statistik memakai sparkline SVG sisi-server, cincin ketersediaan memakai SVG sisi-server, grafik garis dan donat memakai Chart.js.

### 7.1 Chart.js

- Periksa `package.json`: bila `chart.js` sudah terpasang (langkah D3b), pakai; bila belum, `npm install chart.js`.
- `resources/js/charts.js` (diimpor dari `app.js`) memuat modular saja: `Chart`, `LineController`, `DoughnutController`, `LineElement`, `PointElement`, `ArcElement`, `LinearScale`, `CategoryScale`, `Tooltip`, `Filler`. Tanpa `chart.js/auto`.
- Komponen `x-ui.chart` dengan props `type` (`line` atau `doughnut`), `labels`, `datasets` (tiap dataset: `label`, `data`, `color` berupa nama token seperti `primary` atau `chart-mint`, `fill` bool), `title` (untuk `aria-label`), `unit` ("pesanan"). Keluaran: pembungkus `relative` dengan tinggi tetap, `<canvas role="img" aria-label="...">`, dan `<table class="sr-only">` berisi semua nilai (kolom label dan tiap dataset).
- Warna dibaca dari variabel CSS (`getComputedStyle(document.documentElement).getPropertyValue('--color-' + nama)`), tidak ada hex di JavaScript.
- `responsive: true`, `maintainAspectRatio: false`. Tinggi 310px di desktop dan 232px di ponsel.
- Animasi: sekali, 700ms, `easeOutQuart`; bila `matchMedia('(prefers-reduced-motion: reduce)')` benar, `animation: false`.
- Font: `Plus Jakarta Sans`, 12px (11px di ponsel), warna `on-surface-variant`.

Grafik garis ("Pesanan 7 hari terakhir"): dua seri, "Masuk" (`primary`, tebal 3,5) dan "Selesai" (`chart-mint`, tebal 3); `tension: 0.4`; tanpa titik kecuali saat hover (jari-jari 6, isi putih, batas 3px warna seri); `interaction: { mode: 'index', intersect: false }`. Isi area untuk seri "Masuk": gradien vertikal dari `primary` 20% opasitas di atas ke 0% di dasar (dibuat lewat fungsi skrip yang membaca `chartArea`). Sumbu Y: mulai 0, langkah bilangan bulat (`ticks.precision: 0`), 3 sampai 5 garis kisi putus-putus (`[4, 6]`) warna `surface-container-highest`, tanpa garis sumbu. Sumbu X: tanpa kisi. Label X: "Sen", "Sel", dan seterusnya untuk 7 hari; tanggal "dd/mm" di tooltip; periode 30 hari menampilkan label tiap 5 hari; periode hari ini per jam (00 sampai 23, label tiap 3 jam). Legenda bukan dari Chart.js: dua pil HTML (`h-8 rounded-full bg-surface px-3`, titik 10px warna seri, teks `label-m`) di kanan judul kartu.

Tooltip: latar `inverse-surface`, `cornerRadius: 12`, padding 10px 14px, `displayColors: false`, judul (tanggal, mis. "Min 11/10") berwarna `inverse-on-surface` 70% opasitas 11px, isi "6 pesanan masuk" dan "4 pesanan selesai" `inverse-on-surface` 13px bobot 700.

Donat ("Status pesanan"): kanvas 170×170, `cutout: '76%'`, `spacing: 3`, `borderWidth: 0`, `borderRadius: 0`, mulai jam 12. Warna segmen dari tabel status (4.3), urutan: nilai terbesar dulu, seri sama menurut urutan Enum, hanya status dengan nilai lebih dari 0. Di tengah (elemen HTML absolut, bukan plugin kanvas): total `stat-l` yang diperkecil menjadi 32/36 bobot 800 dan "pesanan" `body-s` `on-surface-variant`. Total 0: cincin `surface-container-highest` penuh dan angka 0. Di bawah donat legenda `<ul>`: titik 10px, label, nilai (tebal), persentase (dibulatkan; total boleh 99 atau 101), tinggi baris 34px. Tooltip sama seperti garis.

### 7.2 Sparkline (SVG sisi-server)

Kelas PHP murni (mis. `App\Support\Chart\Sparkline`) dengan tes unit. Lebar 92, tinggi 38. Titik: `x = i * (w - 4) / (n - 1) + 2`, `y = h - 4 - v / max * (h - 8)` dengan `max = max(nilai) atau 1`. Garis dihaluskan Catmull-Rom ke Bezier: untuk tiap ruas p1 ke p2 dengan p0 = titik sebelum p1 (atau p1 bila tidak ada) dan p3 = titik setelah p2 (atau p2 bila tidak ada): `c1 = p1 + (p2 - p0) / 6`, `c2 = p2 - (p3 - p1) / 6`, perintah `C c1 c2 p2`. Garis tebal 2,5, ujung bulat, tanpa isi, titik akhir lingkaran jari-jari 3,5 berwarna sama, warna sesuai tone kartu (`primary`, `chart-amber`, `chart-mint`). `aria-hidden="true"`, karena angka dan label sudah mewakili. Seri dengan kurang dari dua nilai tidak digambar. Seri yang semua nilainya nol digambar sebagai garis datar di dasar.

### 7.3 Cincin (SVG sisi-server)

`App\Support\Chart\Ring`: `C = 2π × 26 = 163,36`; panjang isi = `max(persen × C, 0,1)`; dua lingkaran (trek dan isi) berpusat (32, 32) di ruang 64×64, tebal 7, ujung bulat, isi diputar -90° dengan `stroke-dasharray = "{panjang} {C - panjang}"`. Persen = tersedia / total, 0 bila total 0, dipotong ke 0 sampai 1. Tes unit: 0%, 100%, total 0, ambang menipis.

## 8. Lapisan 5: Halaman

Sebelum R6, buat `docs/PETA_HALAMAN_RETEMA.md`: daftar semua view di `resources/views` dan rutenya, tiap halaman dipetakan ke satu langkah R. Tidak boleh ada halaman yang terlewat.

### 8.1 Dasbor Dinkes (acuan: B1 dan B3)

Dari atas: kepala halaman (5.1), baris statistik, baris grafik, baris bawah.

1. **Baris statistik** (grid; 4 kolom di `xl`, 2 kolom di `sm` sampai `xl`, di ponsel 2 kolom dengan kartu navy dan kartu terakhir melebar dua kolom; jarak 20px, 12px di ponsel):
   - Kartu navy: "Menunggu verifikasi", angka = jumlah pesanan berstatus menunggu verifikasi (saat ini), keterangan "pesanan, terlama {n} menit" (selisih dari pesanan tertunggu tertua; tanpa pesanan: "tidak ada pesanan menunggu"), tombol "Verifikasi sekarang" ke daftar pesanan tab "Perlu tindakan". Ikon `clock`.
   - "Terverifikasi" (tone primary, ikon `user-check`), keterangan "menunggu penugasan": jumlah saat ini.
   - "Sedang berjalan" (tone warning, ikon `ambulance`), keterangan "ditugaskan sampai di jalan": jumlah semua status aktif setelah terverifikasi sampai sebelum selesai, saat ini.
   - "Selesai, {periode}" (tone success, ikon `circle-check`): jumlah pesanan selesai pada periode terpilih; chip delta "+{n} dari {periode} sebelumnya" (hijau bila naik, netral bila sama, `error` bila turun; kata "sebelumnya" menyebut periode sebanding: kemarin, 7 hari sebelumnya, 30 hari sebelumnya).
   - Sparkline: tiga kartu putih memakai seri harian 7 hari terakhir dari kejadian status pada riwayat/linimasa pesanan (periksa tabel riwayat; terverifikasi per hari, mulai berjalan per hari, selesai per hari). Jika riwayat tidak menyimpan waktu per status, pakai pendekatan terdekat dari data yang ada dan catat batasan itu di `docs/PROGRESS.md`. Sparkline bersifat dekorasi; jangan menambah kolom atau tabel.
2. **Baris grafik** (grid 12 kolom di `xl`): kartu grafik garis "Pesanan {periode}" (subjudul "Masuk dan selesai per hari", 8 kolom) dan kartu donat "Status pesanan" (subjudul "{periode}", 4 kolom). Di bawah `xl` semua kartu satu kolom.
3. **Baris bawah**: kartu "Pesanan terbaru" (8 kolom; subjudul "{n} pesanan terakhir hari ini"; tab "Semua", "Perlu tindakan" = menunggu verifikasi dan terverifikasi, "Berjalan" = status aktif; tabel 6.9; maksimum 5 baris; keadaan kosong per tab "Belum ada pesanan di tab ini.") dan kartu "Ketersediaan" (4 kolom; subjudul "Tersedia dari total, semua Faskes"; kisi 2×2 `x-ui.ring-tile` untuk Ambulans, Bed IGD, Bed rawat inap, Dokter dari data ketersediaan yang sudah ada).
4. **Filter periode** `?periode=hari|7|30` (default `7`) berlaku untuk grafik garis, donat, dan kartu "Selesai". Label periode: "hari ini", "7 hari", "30 hari". Nilai tak dikenal jatuh ke 7. Tab "Pesanan terbaru" tidak ikut periode.
5. **Data** dihitung di satu kelas layanan (mis. `App\Services\RingkasanDasbor`, perluas bila sudah ada dari D5) yang mengembalikan array tegas. Batas: tidak lebih dari empat belas query untuk seluruh dasbor, tanpa N+1 (uji dengan `DB::listen` yang menghitung query). Zona waktu `config('app.timezone')` (Sorong memakai WIT, `Asia/Jayapura`). Tes: hitungan live, hitungan periode dan periode sebelumnya, batas hari menurut zona waktu, seri harian dan per jam, urutan donat, tab, periode tak dikenal.
6. Subjudul menampilkan "Diperbarui {jam}" dari waktu server di kartu navy atau kepala halaman, bukan klaim otomatis.

### 8.2 Pesanan (daftar, detail, buat)

- **Daftar** (Dinkes, Faskes): kepala halaman "Pesanan", tab `track` (Semua, Perlu tindakan, Berjalan, Selesai, serta tab lain yang sudah ada), `x-ui.table` di dalam `x-ui.panel flush`, paginasi: tombol 40px `rounded-md`, halaman aktif `bg-primary text-on-primary`, lainnya `secondary`; pencarian `?q=` (5.1). Keadaan kosong per tab.
- **Detail** (acuan: kartu "Detail pesanan dan linimasa" di B2): satu panel. Kepala: kode `title-xl` `tabular-nums`, lencana status dan "Gawat", baris tanggal `body-s` ("Dibuat 11 Okt 2026, 20.31 WIT"), tombol aksi di kanan (`secondary` dan `danger`; satu aksi utama `primary` bila ada). Badan: dua kolom di `md` ke atas (kiri daftar info "Data pesanan": Pasien, Layanan, Alamat jemput, Faskes tujuan, Ambulans dan sopir; kanan "Linimasa" 6.12); satu kolom di ponsel (info dulu, linimasa di bawahnya). Dialog konfirmasi tolak/batal sesuai 6.11 dengan contoh judul "Tolak pesanan SGP-000041?" dan galat "Alasan wajib diisi."
- **Buat/ubah pesanan** oleh petugas: sama dengan alur publik (8.5) di dalam `x-ui.panel`, lebar maksimum 36rem.

### 8.3 Formulir dan data master

Faskes, Ambulans, Sopir, Petugas Faskes, Ketersediaan: indeks = kepala halaman + tombol utama "Tambah {objek}" di kanan + `x-ui.table` dalam panel flush (kolom sesuai yang ada; status aktif/nonaktif sebagai lencana); formulir = `x-ui.panel` satu kolom lebar maksimum 36rem dengan tombol di dasar ("Simpan" `primary`, "Batal" `ghost`); hapus selalu lewat dialog konfirmasi. **Ketersediaan** (Faskes mengubah angkanya): ringkasan di atas dengan `x-ui.ring-tile` untuk nilai saat ini, di bawahnya formulir isian angka (`x-ui.field type="number"`, 48px) dan catatan "Terakhir diperbarui {n} menit lalu" dalam `x-ui.note tone="warning"` bila lebih dari 60 menit.

### 8.4 Masuk

Sesuai 5.3.

### 8.5 Halaman publik: buat pesanan dan lacak pesanan (acuan: layar ponsel di B2)

- **Buat pesanan**: bilah atas 60px (kembali dan "Buat pesanan"); baris "Data pasien" dan "Langkah 2 dari 3" (`label-l` dan `body-s`), batang kemajuan 6px (`surface-container` dengan isi `primary`, `rounded-full`, `role="progressbar"` dengan `aria-valuenow`); isian "Nama pasien"; "Jenis layanan" dengan `x-ui.choice-group` (Darurat, Persalinan, Rujukan, ikon `ambulance`, `users`, `building-2`); "Tingkat darurat" dengan `x-ui.segmented` (Biasa, Gawat); "Alamat jemput" dengan tombol `ghost` kecil "Gunakan lokasi saya" (ikon `map-pin`); bilah bawah lekat dengan tombol penuh "Kirim pesanan". Perilaku formulir, validasi, dan jumlah langkah mengikuti alur yang sudah ada; yang berubah hanya tampilan.
- **Lacak pesanan**: kartu status di atas (kode `title-xl`, lencana status, "Diperbarui {jam}"), lalu linimasa (6.12) dan, bila ada, kartu "Ambulans dan sopir". Tanpa grafik.

### 8.6 Dasbor Faskes

Kepala halaman dengan sapaan. Baris `x-ui.stat` (jumlah pesanan tujuan Faskes ini menurut status: menunggu, sedang berjalan, selesai periode; kartu navy hanya bila ada pesanan yang menunggu respons Faskes, tombolnya ke daftar). Kartu "Ketersediaan Faskes Anda" dengan `x-ui.ring-tile` (hanya sumber daya milik Faskes ini) dan tombol "Perbarui ketersediaan". Kartu "Pesanan terbaru" (tabel 6.9). Filter periode dan grafik tidak wajib di sini.

### 8.7 Dasbor sopir (mobile-first)

Layout di bawah `lg` menggunakan bilah atas dan laci; di desktop tetap kerangka standar. Bila ada tugas aktif: satu kartu navy besar ("Tugas aktif", kode, nama pasien, alamat jemput, Faskes tujuan, tombol aksi status berikutnya yang sudah ada sebagai `on-secondary`). Tanpa tugas aktif: `x-ui.empty` ("Belum ada tugas. Tugas baru muncul di sini setelah Dinkes menugaskan Anda."). Di bawahnya daftar tugas selesai (baris 6.9 versi ponsel). Tanpa grafik.

## 9. Responsif dan aksesibilitas

- Titik henti: `sm` 640, `md` 768, `lg` 1024 (bilah samping tetap), `xl` 1280 (grid dasbor 8/4 dan 4 kolom statistik).
- Tidak ada scroll horizontal pada halaman di 390px. Periksa semua halaman peta (R6 sampai R8).
- Target sentuh: tombol 48px di bawah `lg`, 44px di atasnya; kolom isian 48px; item menu 48px.
- Fokus terlihat di semua kontrol (cincin 2px `primary`, jarak 2px). Urutan tab mengikuti urutan visual. Tautan lompat "Lewati ke isi" di awal halaman, terlihat saat fokus.
- Satu `<h1>` per halaman; judul kartu `<h2>`.
- Setiap grafik: `role="img"`, `aria-label` yang menyebut semua nilai, tabel `sr-only`, dan nilai terlihat di legenda atau tooltip.
- Warna tidak pernah satu-satunya pembawa informasi: status selalu bertulis; cincin menipis bertulis "menipis".
- Hormati `prefers-reduced-motion`.
- Kontras dijaga oleh `KontrasTokenTest` (4.8).

## 10. Gerbang (jalankan di akhir setiap langkah)

1. Lint DESIGN.md (perintah yang sama dengan M1 sampai M7; lihat `package.json` atau `agent/`).
2. Pembuat token (`agent/tokens.mjs` atau skrip yang dipakai sebelumnya); pastikan `resources/css/design-tokens.css` konsisten dengan DESIGN.md.
3. `php artisan test` (termasuk `KonsistensiTest` dan `KontrasTokenTest` sejak R1).
4. `npm run build`.
5. `php artisan route:list` tanpa galat.

Gagal di gerbang = langkah belum selesai. Laporkan nama tes yang gagal; jangan menurunkan standar tes.

Setiap laporan akhir langkah memuat bagian "Yang perlu dilihat pengguna": URL yang dibuka (`php artisan serve`), lebar jendela (1440 dan 390), dan bagian papan mana yang harus dibandingkan.

### Banding visual

Alat (`agent/shot.mjs`, `npm run shot`, langkah RS) menangkap halaman nyata pada data demo dan membandingkannya dengan `docs/referensi-b/` lewat pixelmatch. Mulai R3, gerbang di atas ditambah:

6. `npm run shot`. Buka tangkapan di `storage/shots/` bersama rujukannya di `docs/referensi-b/` (dan gambar selisih `diff-*.png` untuk dasbor admin dan `/_ui`). Perbaiki selisih yang masih kasat mata dan berada dalam cakupan langkah itu sebelum commit; selisih di luar cakupan langkah dicatat untuk langkah R berikutnya, bukan diperbaiki sekarang.
7. Laporan akhir langkah memuat persen selisih piksel untuk dasbor admin (vs `B1-dasbor-1440.png`) dan `/_ui` (vs `B2-komponen-1440.png`), serta catatan hasil banding mata untuk dasbor ponsel (vs `B3-ponsel-390.png`).

## 11. Langkah

Branch kerja: `retema-b`, dibuat dari `material-design` setelah D7 ter-commit.

**R1. Aturan, token, font.** Bagian 4 seluruhnya: front matter `DESIGN.md` (warna, tipografi, bentuk, elevasi), teks prosa (4.9), `agent/tokens.mjs` (letter spacing, `@theme static` untuk `chart-*`), `agent/konsistensi.json` dan kasusnya (4.10), `agent/kontras.json` dan `KontrasTokenTest` (4.8), pasang `@fontsource-variable/plus-jakarta-sans` dan ganti font dasar, catatan "Digantikan" di `docs/DESAIN_DASBOR_MATERIAL.md`, tambahkan bagian "Retema B (rencana)" di `docs/KIT_UI.md` berisi daftar komponen bagian 6 dengan status "belum diimplementasikan". Token lama dipertahankan. Belum ada perubahan Blade selain impor font. Selesai bila gerbang lulus dan semua token baru ada sebagai variabel CSS di hasil pembuat token. Setelah R1 aplikasi akan tampak berwarna dan berhuruf baru tetapi bentuknya belum berubah, itu wajar.

**R2. Komponen dasar.** Bagian 6.1 sampai 6.7, 6.9, dan 6.11 sampai 6.13, ditambah rute pratinjau `/dev/komponen` yang hanya terdaftar bila `app()->isLocal()` (tes membuktikan 404 di lingkungan produksi) berisi semua komponen dengan data contoh seperti B2. Tes render tiap komponen. Perbarui KIT_UI dari "rencana" menjadi dokumentasi sebenarnya. Selesai bila halaman yang sudah ada masih tampil benar dan `/dev/komponen` mirip `B2-komponen-1440.png`.

**RS. Alat banding visual.** Tidak mengubah tampilan aplikasi. `agent/shot.mjs` + `npm run shot`: server khusus `APP_ENV=shot` pada `database/shot.sqlite` (terpisah dari DB nyata), `database/seeders/ShotSeeder.php` (data demo mendekati B1, hanya mode shot), waktu dibekukan 2026-10-11 21.26 WIT. Menangkap dasbor admin, daftar pesanan, satu detail pesanan, `/_ui`, halaman "Pesan ambulans" Masyarakat, dan masuk pada lebar 1440 dan 390 ke `storage/shots/`; membandingkan dasbor admin dan `/_ui` dengan `docs/referensi-b/` lewat pixelmatch. Ini garis dasar sebelum R3: selisihnya besar dan itu wajar (kerangka, komponen dasbor, dan data demo B1 belum ada). Mulai R3, tiap langkah mengakhiri gerbangnya dengan `npm run shot` dan memperbaiki selisih yang masih kasat mata dalam cakupannya (bagian 10, "Banding visual").

**R3. Kerangka.** Bagian 5: bilah samping, kepala halaman, pencarian dan lonceng, bilah atas dan laci ponsel, layout publik dan masuk, serta tautan lompat "Lewati ke isi" (terlihat saat fokus) di awal setiap layout. Terapkan ke layout semua peran. Selesai bila setiap halaman yang ada masih terbuka (`php artisan test` menyapu rute utama), tidak ada scroll horizontal di 390px, dan kerangka mirip B1 dan B3 dengan isi sementara.

**R4. Grafik dan statistik.** Bagian 6.8, 6.10, dan bagian 7: `x-ui.stat`, `x-ui.hero-stat`, `x-ui.ring-tile`, `x-ui.chart`, `Sparkline`, `Ring`, `charts.js`, tes unit geometri dan tes render. Selesai bila halaman `/dev/komponen` menampilkan contoh grafik garis, donat, sparkline, dan cincin dengan data papan (seri masuk 5,8,6,9,7,4,6; selesai 4,6,6,7,6,3,4; donat 31,5,3,3,2,1; cincin 19/24, 1/14, 1/40, 6/9).

**R5. Dasbor Dinkes.** Bagian 8.1: layanan data (periode, delta, sparkline, urutan donat), halaman, tes, batas query. Tambahkan `database/seeders/DemoSeeder.php`: hanya menambah baris (tidak menghapus apa pun), menolak berjalan di lingkungan selain `local`, membuat sekitar 70 pesanan selama 30 hari dengan sebaran status yang wajar, riwayat status, dan data ketersediaan yang menipis untuk dua sumber daya. Jangan menjalankan seeder sendiri: laporkan perintahnya (`php artisan db:seed --class=DemoSeeder`) dan tunggu pengguna menjalankannya. Selesai bila dasbor dengan data demo mirip `B1-dasbor-1440.png` dan `B3-ponsel-390.png`.

**R6. Pesanan.** Bagian 8.2 dan peta halaman (buat `docs/PETA_HALAMAN_RETEMA.md` di awal langkah ini).

**R7. Data master, ketersediaan, masuk.** Bagian 8.3 dan 8.4.

**R8. Publik, Faskes, sopir.** Bagian 8.5 sampai 8.7.

**R9. Penutupan.** Gerbang penuh; telusuri dan hapus hex, `bg-[...]`, token dan komponen lama yang tak terpakai (`card-raised`, `nav-active`, `level-*` yang tak dipakai, `canvas` bila sama dengan `surface`, grafik batang dan donat SVG D3 beserta `BarGeometry`, `DonutGeometry`, dan tesnya, impor Roboto); periksa semua pasangan kontras; pastikan semua halaman di peta selesai; perbarui `docs/KIT_UI.md` dan `docs/PROGRESS.md`; tulis ringkasan perubahan. Setelah ini langkah penutup M8 yang lama boleh dikerjakan.

## Lampiran: nasib komponen D1 sampai D7

Nama bisa berbeda di repo; cocokkan.

| Dari D1 sampai D7 | Di retema B |
|---|---|
| `x-ui.panel` | diubah (6.1) |
| `x-ui.stat`, `x-ui.icon-chip`, `x-ui.tile`, `x-ui.avatar` | diubah (6.6 sampai 6.8); `tile` menjadi dasar `ring-tile` |
| `x-ui.tabs` (garis bawah) | diubah menjadi pil, varian `track` dan `floating` (6.5) |
| Bilah atas desktop melayang | dihapus di desktop; hanya ada di ponsel (5.2) |
| Laci navigasi 280px | menjadi bilah samping melayang 248px (5.1) |
| `x-ui.chart.bar`, `x-ui.chart.donut`, `BarGeometry`, `DonutGeometry` | diganti `x-ui.chart` (Chart.js), `Sparkline`, `Ring`; dihapus di R9 |
| `RingkasanDasbor` | diperluas (8.1) |
| Bayangan `card`, `card-raised`, `nav-active` | `card` diganti nilai; `card-raised` dan `nav-active` dihapus di R9 |
