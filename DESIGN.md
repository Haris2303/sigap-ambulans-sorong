---
version: alpha
name: SIGAP Ambulans
description: Sistem Informasi Layanan Ambulans Terpadu Dinas Kesehatan Kota Sorong. Antarmuka layanan publik berbasis Material Design 3 (baseline, bukan Expressive) yang padat informasi dan terbaca cepat di ponsel maupun layar operator.
colors:
  primary: "#216487"
  on-primary: "#FFFFFF"
  primary-container: "#C6E7FF"
  on-primary-container: "#004C6C"
  secondary-container: "#D2E5F4"
  on-secondary-container: "#374955"
  error: "#BA1A1A"
  on-error: "#FFFFFF"
  error-container: "#FFDAD6"
  on-error-container: "#93000A"
  success: "#006D43"
  success-container: "#92F7BC"
  on-success-container: "#002111"
  warning: "#775A00"
  warning-container: "#FFDF99"
  on-warning-container: "#251A00"
  chart-sky: "#7FB9DA"
  canvas: "#EBF1F6"
  surface: "#F6FAFE"
  surface-container-lowest: "#FFFFFF"
  surface-container-low: "#F0F4F9"
  surface-container: "#EBEEF3"
  surface-container-high: "#E5E8ED"
  surface-container-highest: "#DFE3E7"
  on-surface: "#181C20"
  on-surface-variant: "#41484D"
  outline: "#71787E"
  outline-variant: "#C1C7CE"
  inverse-surface: "#2D3135"
  inverse-on-surface: "#EEF1F6"
  inverse-primary: "#92CEF5"
  scrim: "#00000052"
typography:
  display-l:
    fontFamily: Roboto
    fontSize: 57px
    fontWeight: 400
    lineHeight: 64px
    letterSpacing: -0.25px
  display-m:
    fontFamily: Roboto
    fontSize: 45px
    fontWeight: 400
    lineHeight: 52px
    letterSpacing: 0px
  display-s:
    fontFamily: Roboto
    fontSize: 36px
    fontWeight: 400
    lineHeight: 44px
    letterSpacing: 0px
  headline-l:
    fontFamily: Roboto
    fontSize: 32px
    fontWeight: 400
    lineHeight: 40px
    letterSpacing: 0px
  headline-m:
    fontFamily: Roboto
    fontSize: 28px
    fontWeight: 400
    lineHeight: 36px
    letterSpacing: 0px
  headline-s:
    fontFamily: Roboto
    fontSize: 24px
    fontWeight: 400
    lineHeight: 32px
    letterSpacing: 0px
  title-l:
    fontFamily: Roboto
    fontSize: 22px
    fontWeight: 400
    lineHeight: 28px
    letterSpacing: 0px
  title-m:
    fontFamily: Roboto
    fontSize: 16px
    fontWeight: 500
    lineHeight: 24px
    letterSpacing: 0.15px
  title-s:
    fontFamily: Roboto
    fontSize: 14px
    fontWeight: 500
    lineHeight: 20px
    letterSpacing: 0.1px
  body-l:
    fontFamily: Roboto
    fontSize: 16px
    fontWeight: 400
    lineHeight: 24px
    letterSpacing: 0.5px
  body-m:
    fontFamily: Roboto
    fontSize: 14px
    fontWeight: 400
    lineHeight: 20px
    letterSpacing: 0.25px
  body-s:
    fontFamily: Roboto
    fontSize: 12px
    fontWeight: 400
    lineHeight: 16px
    letterSpacing: 0.4px
  label-l:
    fontFamily: Roboto
    fontSize: 14px
    fontWeight: 500
    lineHeight: 20px
    letterSpacing: 0.1px
  label-m:
    fontFamily: Roboto
    fontSize: 12px
    fontWeight: 500
    lineHeight: 16px
    letterSpacing: 0.5px
  label-s:
    fontFamily: Roboto
    fontSize: 11px
    fontWeight: 500
    lineHeight: 16px
    letterSpacing: 0.5px
  page-title:
    fontFamily: Roboto
    fontSize: 28px
    fontWeight: 500
    lineHeight: 36px
    letterSpacing: 0px
  card-title:
    fontFamily: Roboto
    fontSize: 20px
    fontWeight: 500
    lineHeight: 28px
    letterSpacing: 0px
  stat-hero:
    fontFamily: Roboto
    fontSize: 56px
    fontWeight: 500
    lineHeight: 60px
    letterSpacing: 0px
  stat-l:
    fontFamily: Roboto
    fontSize: 32px
    fontWeight: 500
    lineHeight: 36px
    letterSpacing: 0px
  stat-m:
    fontFamily: Roboto
    fontSize: 28px
    fontWeight: 500
    lineHeight: 32px
    letterSpacing: 0px
rounded:
  xs: 4px
  sm: 8px
  md: 12px
  lg: 16px
  xl: 28px
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 12px
  lg: 16px
  xl: 24px
  2xl: 32px
  3xl: 48px
  touch: 48px
  drawer: 280px
  content-max: 1152px
elevation:
  shadow-color: "{colors.scrim}"
  level-0:
    container: "{colors.surface}"
    shadow: none
  level-1:
    container: "{colors.surface-container-low}"
    key: 0px 1px 2px 0px
    key-opacity: 0.30
    ambient: 0px 1px 3px 1px
    ambient-opacity: 0.15
  level-2:
    container: "{colors.surface-container}"
    key: 0px 1px 2px 0px
    key-opacity: 0.30
    ambient: 0px 2px 6px 2px
    ambient-opacity: 0.15
  level-3:
    container: "{colors.surface-container-high}"
    key: 0px 1px 3px 0px
    key-opacity: 0.30
    ambient: 0px 4px 8px 3px
    ambient-opacity: 0.15
  card:
    color: "{colors.on-surface}"
    key: 0px 1px 3px 0px
    key-opacity: 0.10
    ambient: 0px 6px 16px 0px
    ambient-opacity: 0.06
  card-raised:
    color: "{colors.on-surface}"
    key: 0px 1px 3px 0px
    key-opacity: 0.12
    ambient: 0px 8px 20px 0px
    ambient-opacity: 0.10
  nav-active:
    color: "{colors.on-surface}"
    key: 0px 2px 6px 0px
    key-opacity: 0.22
state:
  hover: 0.08
  focus: 0.10
  pressed: 0.10
  dragged: 0.16
  disabled-content: 0.38
  disabled-container: 0.12
motion:
  duration-short-ms: 150
  duration-medium-ms: 250
  easing-standard: "cubic-bezier(0.2, 0, 0, 1)"
components:
  app-background:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-l}"
  top-app-bar:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.title-l}"
    rounded: "{rounded.lg}"
    height: 64px
    padding: "{spacing.lg}"
  navigation-drawer:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface-variant}"
    width: "{spacing.drawer}"
    padding: "{spacing.lg}"
  nav-item:
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.label-l}"
    rounded: "{rounded.full}"
    height: 48px
    padding: "{spacing.lg}"
  nav-item-active:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
  nav-badge:
    backgroundColor: "{colors.error}"
    textColor: "{colors.on-error}"
    typography: "{typography.label-m}"
    rounded: "{rounded.full}"
    height: 20px
    padding: "{spacing.xs}"
  button-filled:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.label-l}"
    rounded: "{rounded.full}"
    height: 40px
    padding: "{spacing.xl}"
  button-tonal:
    backgroundColor: "{colors.secondary-container}"
    textColor: "{colors.on-secondary-container}"
    typography: "{typography.label-l}"
    rounded: "{rounded.full}"
    height: 40px
    padding: "{spacing.xl}"
  button-outlined:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.primary}"
    typography: "{typography.label-l}"
    rounded: "{rounded.full}"
    height: 40px
    padding: "{spacing.xl}"
  button-text:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.primary}"
    typography: "{typography.label-l}"
    rounded: "{rounded.full}"
    height: 40px
    padding: "{spacing.lg}"
  button-error:
    backgroundColor: "{colors.error}"
    textColor: "{colors.on-error}"
    typography: "{typography.label-l}"
    rounded: "{rounded.full}"
    height: 40px
    padding: "{spacing.xl}"
  text-field:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-l}"
    rounded: "{rounded.xs}"
    height: 56px
    padding: "{spacing.lg}"
  text-field-label:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.label-l}"
  text-field-supporting:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.body-s}"
  text-field-error:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.error}"
    typography: "{typography.body-s}"
  text-field-outline:
    backgroundColor: "{colors.outline}"
    height: 1px
  panel:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-m}"
    rounded: "{rounded.lg}"
    padding: "{spacing.xl}"
  panel-outline:
    backgroundColor: "{colors.outline-variant}"
    height: 1px
  table-header:
    backgroundColor: "{colors.surface-container-low}"
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.label-l}"
    height: 48px
    padding: "{spacing.lg}"
  table-cell:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-m}"
    height: 52px
    padding: "{spacing.lg}"
  summary-cell:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.display-s}"
    padding: "{spacing.lg}"
  badge-neutral:
    backgroundColor: "{colors.surface-container-highest}"
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.label-m}"
    rounded: "{rounded.full}"
    height: 24px
    padding: "{spacing.sm}"
  badge-info:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.on-primary-container}"
    typography: "{typography.label-m}"
    rounded: "{rounded.full}"
    height: 24px
    padding: "{spacing.sm}"
  badge-warning:
    backgroundColor: "{colors.warning-container}"
    textColor: "{colors.on-warning-container}"
    typography: "{typography.label-m}"
    rounded: "{rounded.full}"
    height: 24px
    padding: "{spacing.sm}"
  badge-success:
    backgroundColor: "{colors.success-container}"
    textColor: "{colors.on-success-container}"
    typography: "{typography.label-m}"
    rounded: "{rounded.full}"
    height: 24px
    padding: "{spacing.sm}"
  badge-danger:
    backgroundColor: "{colors.error-container}"
    textColor: "{colors.on-error-container}"
    typography: "{typography.label-m}"
    rounded: "{rounded.full}"
    height: 24px
    padding: "{spacing.sm}"
  status-dot-success:
    backgroundColor: "{colors.success}"
    rounded: "{rounded.full}"
    size: 10px
  status-dot-warning:
    backgroundColor: "{colors.warning}"
    rounded: "{rounded.full}"
    size: 10px
  dialog:
    backgroundColor: "{colors.surface-container-high}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-m}"
    rounded: "{rounded.xl}"
    padding: "{spacing.xl}"
    width: 560px
  dialog-scrim:
    backgroundColor: "{colors.scrim}"
  snackbar:
    backgroundColor: "{colors.inverse-surface}"
    textColor: "{colors.inverse-on-surface}"
    typography: "{typography.body-m}"
    rounded: "{rounded.xs}"
    padding: "{spacing.lg}"
  snackbar-action:
    backgroundColor: "{colors.inverse-surface}"
    textColor: "{colors.inverse-primary}"
    typography: "{typography.label-l}"
  button-on-primary:
    backgroundColor: "{colors.on-primary}"
    textColor: "{colors.primary}"
    typography: "{typography.label-l}"
    rounded: "{rounded.full}"
    height: 40px
    padding: "{spacing.xl}"
  stat-card:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.stat-l}"
    rounded: "{rounded.lg}"
    padding: 20px
  stat-card-featured:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.stat-hero}"
    rounded: "{rounded.lg}"
    padding: "{spacing.xl}"
  icon-chip:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.on-primary-container}"
    rounded: "{rounded.md}"
    size: 40px
  icon-chip-lg:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.on-primary-container}"
    rounded: "{rounded.md}"
    size: 52px
  tile:
    backgroundColor: "{colors.surface-container-low}"
    textColor: "{colors.on-surface}"
    typography: "{typography.stat-m}"
    rounded: "{rounded.md}"
    padding: "{spacing.lg}"
  tab:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.label-l}"
    height: 44px
    padding: "{spacing.lg}"
  tab-active:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.primary}"
  avatar:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.on-primary-container}"
    typography: "{typography.label-l}"
    rounded: "{rounded.full}"
    size: 40px
  search-field:
    backgroundColor: "{colors.surface-container}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-l}"
    rounded: "{rounded.full}"
    height: 48px
    padding: "{spacing.lg}"
  chart-bar:
    backgroundColor: "{colors.chart-sky}"
    rounded: "{rounded.sm}"
    width: 36px
  chart-bar-highlight:
    backgroundColor: "{colors.primary}"
    rounded: "{rounded.sm}"
    width: 36px
---

# SIGAP Ambulans — sistem desain

## Overview

SIGAP Ambulans dipakai empat kelompok orang dengan kebutuhan berbeda: masyarakat yang sedang panik dan memegang ponsel, operator Dinkes yang menyaring dan menugaskan pesanan seharian di layar lebar, petugas Faskes yang memperbarui ketersediaan bed dan dokter, serta sopir yang membaca layar sekilas di dalam kendaraan. Antarmuka harus terbaca dalam satu pandang dan tidak pernah menghalangi.

Sistem desainnya adalah **Material Design 3 baseline** (bukan Expressive): skema warna tonal, skala tipe M3, bentuk bersudut membulat, lapisan keadaan (state layer) untuk umpan balik sentuhan, dan, sesuai keputusan pemilik proyek (`docs/DESAIN_DASBOR_MATERIAL.md`), gaya dasbor Material yang lebih kaya: kartu putih berbayang di atas latar bertint, chip ikon pembawa makna, grafik, tab, dan avatar. Mode gelap belum dibuat, tetapi seluruh warna disusun dengan peran M3 sehingga skema gelap dapat ditambahkan tanpa mengubah markup.

Kepadatan informasi tetap menjadi tujuan utama. Dasbor boleh memiliki satu kartu utama berwarna primary; halaman lain tetap tenang. Pada halaman detail pesanan, **linimasa pesanan** (waktu, status, pelaku, catatan) tetap menjadi bagian yang paling mudah dibaca. Antarmuka ini bukan situs pemasaran: tidak ada hero bergambar, ilustrasi dekoratif, atau kalimat penjualan. Kata-katanya faktual, berbahasa Indonesia, huruf awal kapital hanya pada awal kalimat.

## Colors

Skema dihasilkan dari satu warna benih dan dibekukan sebagai nilai heksadesimal di bagian token. Cara menurunkannya dicatat di sini karena alat penghasilnya tidak disimpan di repositori:

- Benih `#0B5C80` (HCT: hue 237,9; chroma 37,1; tone 36,5), varian **TonalSpot**, kontras standar (0), spesifikasi M3 2021, skema terang. Perangkat: `@material/material-color-utilities` 0.4.0.
- Warna kustom **success** (sumber `#2E7D32`) dan **warning** (sumber `#F9A825`) diharmonisasi ke benih dengan `Blend.harmonize`, lalu diambil nada 40 (warna), 90 (container), dan 10 (on-container) dari paletnya, sama seperti peran `primary`. Peran `on-success` dan `on-warning` sengaja tidak dibuat karena tidak ada isian padat yang memerlukan teks di atasnya.
- **Canvas** (`#EBF1F6`) dan **Chart-sky** (`#7FB9DA`) ditambahkan oleh keputusan pemilik proyek untuk gaya dasbor; keduanya bukan keluaran generator dan tidak diturunkan dari skema.
- Peran `secondary` dan `tertiary` tidak dipakai kecuali `secondary-container` dan `on-secondary-container` (item menu aktif, tombol tonal, pilihan terpilih). Antarmuka ini tetap punya satu aksen.
- `primary` adalah nada 40 dari palet benih (`#216487`), bukan benih itu sendiri. Benih hanya dipakai untuk menurunkan skema.

Peran:

- **Canvas (#EBF1F6):** latar halaman semua peran. **Surface (#F6FAFE)** dan **On-surface (#181C20):** dialog, area formulir bila perlu, dan teks utama. Kontras teks utama lebih dari 16:1.
- **Surface-container-lowest (#FFFFFF)** sampai **-highest (#DFE3E7):** lima tingkat permukaan. Lowest untuk kartu (panel), tabel, laci, bilah atas, dan isian; low untuk petak di dalam kartu, blok pengguna di laci, dan kepala tabel; container untuk kolom cari dan menu tarik; high untuk dialog, garis pemisah baris di dalam kartu, garis kisi grafik, dan tepi kanan laci; highest untuk lencana netral.
- **On-surface-variant (#41484D):** teks sekunder, label isian, keterangan, ikon menu. Kontras lebih dari 8:1 di atas Surface.
- **Primary (#216487)**, **On-primary**, **Primary-container (#C6E7FF)**, **On-primary-container (#004C6C):** satu-satunya aksen. Primary untuk tombol utama, tautan, fokus, titik status info, item menu aktif, dan kartu statistik utama; container untuk lencana, pesan informasi, chip ikon biru, dan avatar. Hanya **satu tombol utama per layar**.
- **Secondary-container (#D2E5F4)** dan **On-secondary-container (#374955):** tombol tonal dan pilihan terpilih pada daftar pilihan.
- **Error (#BA1A1A)**, **Error-container (#FFDAD6)**, **On-error-container (#93000A):** hanya untuk bahaya: tolak, batalkan, galat, pesanan gawat, dan hitungan menunggu pada menu.
- **Success (#006D43)**, **Warning (#775A00)** dengan container dan on-container masing-masing: pasangan lembut pada lencana dan pesan, pasangan container pada chip ikon (maksimum 52px), warna titik status, dan segmen grafik. Hanya untuk makna status; tidak pernah sebagai latar tombol atau area besar.
- **Outline (#71787E)** untuk batas isian dan kotak centang (kontras 4,3:1 sampai 4,5:1, melampaui 3:1 yang disyaratkan WCAG untuk komponen antarmuka) serta segmen grafik "dibatalkan"; **Outline-variant (#C1C7CE)** untuk garis pemisah, garis dasar grafik, dan batang bernilai nol. Kartu tidak lagi memakai batas.
- **Inverse-surface (#2D3135)**, **Inverse-on-surface**, **Inverse-primary (#92CEF5):** snackbar.
- **Chart-sky (#7FB9DA):** batang grafik hari biasa dan segmen donat "dalam perjalanan". Kontrasnya terhadap putih di bawah 3:1, karena itu nilai grafik selalu tertulis sebagai teks.
- **Scrim:** hitam 32% di belakang dialog dan laci menu.

Warna di markup hanya lewat token ini (`bg-surface`, `text-on-surface-variant`, `border-outline-variant`, `bg-primary`, …). Palet bawaan Tailwind dimatikan; nilai heksadesimal dan nilai sembarang (`bg-[#…]`) dilarang.

## Typography

Satu keluarga huruf: **Roboto**, variabel, di-host sendiri lewat paket `@fontsource-variable/roboto` sehingga tidak ada unduhan font dari internet. Tidak ada huruf kedua, tidak ada font monospace.

Skala tipe M3 lengkap (15 gaya) ditambah lima gaya dasbor (`page-title`, `card-title`, `stat-hero`, `stat-l`, `stat-m`; bobot 500) tersedia sebagai kelas `text-<gaya>`; tiap kelas mengatur ukuran, tinggi baris, bobot, dan jarak huruf sekaligus. Pemakaian di aplikasi:

- **Page-title** (28px, 500) untuk judul halaman, satu per halaman; subjudulnya **Body-l** berwarna `on-surface-variant`. **Card-title** (20px, 500) untuk judul kartu. **Stat-hero** (56px), **Stat-l** (32px), dan **Stat-m** (28px), semuanya bobot 500, untuk angka di kartu statistik utama, kartu statistik putih, dan petak ketersediaan; selalu `tabular-nums`.
- **Title-l** (22px) untuk judul bagian pada halaman tanpa kartu, **Title-m** (16px, 500) untuk judul blok kecil dan teks utama kartu statistik utama. Gaya display, headline, dan title-s tersedia, tidak dipakai kecuali dibutuhkan.
- **Body-l** (16px) untuk isi dan subjudul halaman, **Body-m** (14px) untuk sel tabel, keterangan kartu, dan keterangan lain, **Body-s** (12px) untuk waktu, bantuan isian, dan keterangan kecil.
- **Label-l** (14px, 500) untuk tombol, item menu, label isian, label kartu statistik, tab, dan kepala tabel. **Label-m** (12px, 500) untuk lencana status dan label grup menu. **Label-s** (11px) tidak untuk teks bacaan.
- Semua angka yang berderet (waktu, jumlah, kode pesanan, nomor telepon) memakai `tabular-nums`.
- Hanya dua bobot: 400 dan 500. Tidak ada huruf kapital semua (`uppercase`), tidak ada `tracking-*` terpisah (jarak huruf sudah ada pada tiap gaya), tidak ada teks miring untuk penekanan, tidak ada satu kata berwarna di tengah judul. Penekanan memakai `font-medium`.
- Panjang baris isi paling banyak 70 karakter (`max-w-prose`).

## Layout

Mobile-first. Titik henti Tailwind: ponsel (bawaan), `md` 768px, `lg` 1024px. Kelas ukuran jendela M3 dipetakan begini: di bawah `lg` (compact dan medium) memakai bilah atas dan laci modal; `lg` ke atas (expanded) memakai laci tetap. Skala jarak berbasis 4px; pakai skala numerik bawaan Tailwind (`p-4` = 16px, `gap-6` = 24px).

- **Kerangka aplikasi (`lg` ke atas):** laci navigasi tetap selebar 280px berlatar putih (Surface-container-lowest) dengan tepi kanan 1px; isi di kanan berlatar Canvas, lebar isi maksimum 1152px, rata kiri. Bilah atas berupa kartu melayang di atas isi pada semua ukuran layar. **Di bawah `lg`:** bilah atas 64px (tombol menu, pencarian, lonceng, avatar) dan menu menjadi laci modal berlatar scrim. Jarak antar kartu 20px (`gap-5`); padding halaman 28px (`px-7`) di desktop dan 16px di ponsel.
- **Halaman:** judul di kiri, aksi utama di kanan (ponsel: di bawah judul). Di bawahnya kartu dipisahkan jarak 20px.
- **Ringkasan angka:** kartu statistik terpisah dalam satu baris yang boleh turun baris: satu kartu utama berwarna Primary dan kartu putih, masing-masing dengan chip ikon (2 kolom atau lebih di desktop, satu kolom di ponsel).
- **Tabel dan daftar** adalah bentuk utama menyajikan data: berada di dalam kartu tanpa padding (`flush`), baris dipisah garis, tinggi baris minimal 52px, kolom sekunder disembunyikan di ponsel; di bawah 640px tabel bergulir di dalam kartunya.
- **Formulir** satu kolom, lebar maksimum 36rem, label di atas isian (tidak mengapung), galat tepat di bawah isian.
- **Target sentuh** minimal 48px untuk semua kontrol. Tombol setinggi 40px (ukuran M3) mendapat area sentuh 48px lewat perluasan tak terlihat; isian 56px, item menu 48px. Jarak antar kontrol sejajar minimal 8px.
- Layar Buat Pesanan muat dalam satu alur dengan tombol kirim yang selalu terlihat di dasar layar ponsel.
- Rata kiri untuk semua teks. Rata tengah hanya untuk kartu masuk/daftar dan keadaan kosong.

## Elevation & Depth

Hierarki dibentuk oleh **latar Canvas lawan kartu putih berbayang**, lalu jarak; bayangan hanya berasal dari token elevation. Bayangan kartu memakai dua lapis berwarna `on-surface` (24 28 32), bukan hitam; bayangan melayang M3 (Level 1 sampai 3) memakai hitam. Tingkat yang dipakai:

- **Level 0** (`shadow-none`): elemen datar di dalam kartu (petak, baris tabel), laci tetap (dipisah tepi 1px), dan semua yang tidak butuh pemisahan dari latar.
- **Card** (`shadow-card`): panel, kartu statistik putih, dan bilah atas.
- **Card-raised** (`shadow-card-raised`): kartu statistik utama (Primary).
- **Nav-active** (`shadow-nav-active`): item menu aktif.
- **Level 1** (`shadow-level-1`): bilah atas dan bilah tombol bawah di ponsel ketika isi bergulir di bawahnya. Latar: Surface-container-low.
- **Level 2** (`shadow-level-2`): menu tarik dan daftar saran alamat. Latar: Surface-container.
- **Level 3** (`shadow-level-3`): dialog konfirmasi dan snackbar. Latar dialog: Surface-container-high, ditambah scrim di belakangnya.

Level 4 dan 5 tidak dipakai. Level 1 sampai 3 tetap untuk lapisan yang melayang dan tidak berubah. Fokus papan ketik selalu terlihat: cincin 2px `primary` dengan jarak 2px.

## Shapes

Skala bentuk M3: **4px** (extra-small: isian, snackbar), **8px** (small: daftar saran, batang grafik), **12px** (medium: chip ikon, petak), **16px** (large: panel dan kartu, bilah atas, blok pengguna di laci), **28px** (extra-large: dialog), **penuh** (tombol, item menu, lencana status, hitungan menu, avatar, kolom cari, titik status). Tidak ada sudut di luar skala ini, dan tidak ada kombinasi sudut tajam dan membulat dalam satu tampilan. Tombol berbentuk pil adalah bentuk baku M3.

## Components

Bentuk dan perilaku komponen didefinisikan di token `components` di atas; padanan Blade dan API-nya ada di `docs/KIT_UI.md`.

- **Tombol:** lima nada. *Filled* (Primary) untuk aksi utama, *tonal* (Secondary-container) untuk aksi penting yang bukan utama, *outlined* (batas Outline, teks Primary) untuk aksi sekunder, *text* (tanpa latar) untuk aksi tersier dan Batal, *error* (Error) untuk aksi merusak. Tinggi 40px, area sentuh 48px, label kata kerja (`Verifikasi`, `Tugaskan`, `Simpan`). Ikon 18px di kiri label hanya bila memperjelas arti.
- **Isian:** *outlined*, tinggi 56px, batas 1px Outline, sudut 4px. Label 14px di atas kolom (tidak mengapung), bantuan 12px di bawah. Fokus: batas 2px Primary. Galat: batas dan teks Error, dengan teks galat selalu ada (tidak mengandalkan warna saja).
- **Lencana status:** teks pendek di atas container tonal, berbentuk pil (tinggi 24px, padding 10px), diawali titik 6px dari warna teks. Lima nada: `netral` (Surface-container-highest), `info` (Primary-container), `peringatan` (Warning-container), `sukses` (Success-container), `bahaya` (Error-container).
- **Linimasa pesanan:** garis tegak 1px; tiap langkah satu baris: waktu (tabular, 12px, kolom tetap), titik status, label status, pelaku dan peran, catatan. Langkah terakhir (keadaan sekarang) berbobot 500; langkah yang belum terjadi tidak ditampilkan.
- **Menu (laci navigasi):** latar putih; kepala berisi tanda merek 40px (Primary, sudut 12px) dan nama aplikasi; label grup `label-m` `on-surface-variant`; item 48px berbentuk pil dengan ikon 22px di kiri; item aktif berlatar Primary, teks On-primary, `shadow-nav-active`. Hitungan pesanan menunggu berupa lencana hitungan (Error, 20px, `label-m`). Di dasar: blok pengguna (`surface-container-low`, sudut 16px) berisi avatar, nama, peran, dan tombol keluar berupa form POST.
- **Panel (kartu):** Surface-container-lowest, sudut 16px, `shadow-card`, tanpa batas, padding 24px; varian `flush` tanpa padding untuk tabel. Kartu berbayang tidak berada di dalam kartu berbayang; petak tonal tanpa bayangan di dalam kartu diizinkan.
- **Kartu statistik:** *putih* (padding 20px): chip ikon 52px, angka `stat-l`, label `label-l`, keterangan `body-s`. *Utama*: latar Primary, teks On-primary, `shadow-card-raised`, chip ikon 44px berlatar On-primary 18%, judul `title-m`, angka `stat-hero`, keterangan `body-m` berwarna Primary-container, dan satu tombol `on-primary`.
- **Chip ikon:** 40px (ikon 22px) atau 52px (ikon 26px), sudut 12px, pasangan container dan on-container sesuai nada (primary, warning, success, error). Hanya pembawa makna (status, kategori data) dan selalu disertai teks.
- **Petak:** Surface-container-low, sudut 12px, padding 16px, tanpa bayangan; berisi chip ikon, angka `stat-m`, dan label `body-m`. Dipakai di dalam kartu ketersediaan.
- **Tab:** tab primer M3, tinggi 44px, padding 16px, label `label-l`; aktif berwarna Primary dengan garis bawah 3px Primary, lainnya `on-surface-variant` dengan garis bawah transparan; garis pemisah bawah `surface-container-high`. Tab adalah tautan ke URL yang sama dengan `?tab=`.
- **Avatar:** lingkaran 40px, Primary-container dengan On-primary-container, inisial maksimum dua huruf (`label-l`); nama selalu ada di sebelahnya.
- **Bilah atas:** kartu melayang 64px berlatar putih, sudut 16px, `shadow-card`; berisi tombol menu (di bawah `lg`), kolom cari (pil 48px `surface-container`), lonceng pesanan menunggu verifikasi dengan lencana Error, dan avatar dengan nama dan peran.
- **Grafik:** SVG yang dirender di sisi server tanpa pustaka. Batang 7 hari (hari biasa Chart-sky, hari ini Primary, nilai nol berupa batang rintisan Outline-variant) dan donat status (selesai Success, dibatalkan Outline, ditolak Error, menunggu verifikasi Warning, terverifikasi Primary, ditugaskan sampai dalam perjalanan Chart-sky). Setiap grafik punya label nilai yang terlihat, legenda teks, dan keterangan `aria-label`.
- **Pilihan terpilih** (jenis layanan, tingkat darurat): baris besar berbatas dengan item terpilih berlatar Secondary-container.
- **Dialog konfirmasi:** latar Surface-container-high, sudut 28px, Level 3. Judul kalimat biasa, satu kalimat penjelas, dua tombol sejajar kanan: batal berupa *text*, aksi berupa *text* atau, bila merusak, *error*.
- **Snackbar:** pesan singkat hasil tindakan (mis. "Pesanan berhasil diverifikasi.") di dasar layar, Inverse-surface, satu aksi *text* "Tutup" berwarna Inverse-primary. Galat validasi dan pesan yang harus dibaca tetap tampil sebagai pesan sebaris di atas isi.

## Do's and Don'ts

- Do pakai satu tombol filled per layar dan beri tahu apa yang terjadi bila ditekan.
- Do tampilkan keadaan nyata: kosong ("Belum ada pesanan."), memuat, galat, dan data lama ("Data lama", "Belum diperbarui").
- Do bangun hierarki dengan latar `canvas`, kartu putih berbayang `card`, dan jarak; bayangan hanya dari token elevation, tidak ada bayangan di luar token.
- Do beri setiap grafik label nilai yang terlihat dan keterangan teks (`aria-label`) sehingga tidak bergantung pada warna.
- Do jaga kontras teks minimal 4,5:1 dan target sentuh minimal 48px.
- Do beri setiap kontrol interaktif lapisan keadaan (hover, fokus, tekan) dan tampilan nonaktif sesuai bagian Interaction states.
- Don't pakai gradien, kaca buram (`backdrop-blur`), cahaya berwarna, atau bayangan berwarna.
- Don't pakai emoji atau ikon dekoratif. Chip ikon hanya boleh membawa makna (status, kategori data) dengan pasangan container yang sesuai, dan selalu disertai teks.
- Don't susun halaman sebagai kisi kartu fitur yang bentuknya sama dan isinya promosi. Kartu statistik dan kartu data pada dasbor diizinkan. Kartu berbayang tidak berada di dalam kartu berbayang; petak tonal tanpa bayangan di dalam kartu diizinkan.
- Don't pakai hero besar, judul raksasa, ilustrasi, foto stok, testimoni, angka pemasaran, atau kalimat bernada iklan.
- Don't beri animasi masuk, efek angkat/skala saat disorot, penghitung angka bergerak, atau animasi berulang. Hanya transisi lapisan keadaan dan animasi yang menjawab tindakan pengguna.
- Don't tambahkan warna aksen kedua, sudut di luar skala, atau gaya tipe di luar skala tanpa mengubah dokumen ini. Warna semantik (success, warning, error) hanya untuk makna status. Mode gelap menunggu keputusan terpisah.
- Don't ketik ulang label status, peran, atau jenis; ambil dari Enum (`->label()`).

## Interaction states

Umpan balik memakai **lapisan keadaan**: lapisan berwarna sama dengan teks atau ikon kontrol, dengan opasitas tetap, di atas latar kontrol. Opasitasnya: hover 8%, fokus 10%, tekan 10%, seret 16%. Kontrol nonaktif memakai konten 38% dan wadah 12%; tidak ada kontrol nonaktif yang hanya berubah warna aksen. Fokus papan ketik menambah cincin 2px Primary. Tidak ada riak (ripple) dan tidak ada efek angkat.

## Iconography

Ikon Lucide (garis 1,75px) dari paket `lucide-static`, disalin ke `resources/icons/` dan ditampilkan lewat komponen `x-ui.ikon`. Ukuran menurut komponen: 18px di dalam tombol, 20px pada tombol keluar di blok pengguna, 22px pada item menu dan chip ikon 40px, 24px pada tombol ikon dan banner, 26px pada chip ikon 52px. Warna mengikuti teks sekitarnya (`currentColor`). Daftar ikon yang tersedia ada di `docs/KIT_UI.md`; ikon di luar daftar tidak boleh dipakai.

## Motion

Tidak ada gerak yang berjalan sendiri. Transisi `color`, `background-color`, `border-color`, `opacity`, dan `box-shadow` 150ms dengan easing standar M3 `cubic-bezier(0.2, 0, 0, 1)`. Dialog dan snackbar muncul dengan fade 250ms atau tanpa animasi. Hormati `prefers-reduced-motion`.

## Writing

Bahasa Indonesia baku yang sederhana, aktif, huruf awal kapital hanya pada awal kalimat. Nama aksi sama di seluruh alur ("Verifikasi" di tombol menghasilkan pesan "Pesanan berhasil diverifikasi."). Galat menyebut apa yang salah dan apa yang bisa dilakukan, tanpa meminta maaf dan tanpa tanda seru. Keadaan kosong menyebut langkah berikutnya. Tidak ada tanda panah di akhir tautan, tidak ada pemisah titik tengah untuk menggabung keterangan, tidak ada label berhuruf kapital semua di atas judul.
