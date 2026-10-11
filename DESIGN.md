---
version: alpha
name: SIGAP Ambulans
description: Sistem Informasi Layanan Ambulans Terpadu Dinas Kesehatan Kota Sorong. Antarmuka layanan publik arah B "Terang lembut" (docs/RETEMA_B.md) dengan peran warna Material Design 3, terbaca cepat di ponsel maupun layar operator.
colors:
  primary: "#1368CE"
  on-primary: "#FFFFFF"
  primary-container: "#E4EEFF"
  on-primary-container: "#0F55A8"
  secondary: "#102A56"
  on-secondary: "#FFFFFF"
  secondary-container: "#EEF1F7"
  on-secondary-container: "#475066"
  tertiary: "#7A5AF8"
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
  surface: "#F3F5FB"
  canvas: "#F3F5FB"
  surface-container-lowest: "#FFFFFF"
  surface-container-low: "#F7F8FD"
  surface-container: "#F3F5FB"
  surface-container-high: "#EEF1F7"
  surface-container-highest: "#E7EAF3"
  on-surface: "#1B2437"
  on-surface-variant: "#5F6B80"
  outline: "#7F8AA0"
  outline-variant: "#D0D6E4"
  inverse-surface: "#1B2437"
  inverse-on-surface: "#FFFFFF"
  inverse-primary: "#8DB8FF"
  selected: "#EEF4FF"
  scrim: "#1B2437"
  chart-mint: "#0FA582"
  chart-coral: "#FF6B57"
  chart-amber: "#F5A524"
  chart-slate: "#A3ADC2"
  # Token lama: dipakai grafik batang dan donat dasbor Material, dihapus di R9.
  chart-sky: "#7FB9DA"
typography:
  stat-hero:
    fontFamily: Plus Jakarta Sans
    fontSize: 56px
    fontWeight: 800
    lineHeight: 62px
    letterSpacing: -0.03em
  stat-l:
    fontFamily: Plus Jakarta Sans
    fontSize: 38px
    fontWeight: 800
    lineHeight: 44px
    letterSpacing: -0.03em
  page-title:
    fontFamily: Plus Jakarta Sans
    fontSize: 30px
    fontWeight: 800
    lineHeight: 38px
    letterSpacing: -0.025em
  page-title-s:
    fontFamily: Plus Jakarta Sans
    fontSize: 26px
    fontWeight: 800
    lineHeight: 34px
    letterSpacing: -0.025em
  title-xl:
    fontFamily: Plus Jakarta Sans
    fontSize: 22px
    fontWeight: 800
    lineHeight: 28px
    letterSpacing: -0.02em
  title-l:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: 800
    lineHeight: 26px
    letterSpacing: -0.01em
  title-m:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: 700
    lineHeight: 24px
    letterSpacing: 0em
  brand:
    fontFamily: Plus Jakarta Sans
    fontSize: 19px
    fontWeight: 800
    lineHeight: 24px
    letterSpacing: -0.02em
  ring-value:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: 800
    lineHeight: 20px
    letterSpacing: 0em
  body-l:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: 400
    lineHeight: 24px
    letterSpacing: 0em
  body-m:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: 400
    lineHeight: 20px
    letterSpacing: 0em
  body-s:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: 400
    lineHeight: 18px
    letterSpacing: 0em
  label-l:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: 600
    lineHeight: 20px
    letterSpacing: 0em
  label-m:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: 600
    lineHeight: 16px
    letterSpacing: 0em
  chip:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: 700
    lineHeight: 16px
    letterSpacing: 0em
  # Gaya lama: masih dipakai komponen dasbor Material, dihapus di R9 bila tak terpakai.
  display-l:
    fontFamily: Plus Jakarta Sans
    fontSize: 57px
    fontWeight: 400
    lineHeight: 64px
    letterSpacing: -0.25px
  display-m:
    fontFamily: Plus Jakarta Sans
    fontSize: 45px
    fontWeight: 400
    lineHeight: 52px
    letterSpacing: 0px
  display-s:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: 400
    lineHeight: 44px
    letterSpacing: 0px
  headline-l:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: 400
    lineHeight: 40px
    letterSpacing: 0px
  headline-m:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: 400
    lineHeight: 36px
    letterSpacing: 0px
  headline-s:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: 400
    lineHeight: 32px
    letterSpacing: 0px
  title-s:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: 500
    lineHeight: 20px
    letterSpacing: 0.1px
  label-s:
    fontFamily: Plus Jakarta Sans
    fontSize: 11px
    fontWeight: 500
    lineHeight: 16px
    letterSpacing: 0.5px
  card-title:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: 500
    lineHeight: 28px
    letterSpacing: 0px
  stat-m:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: 500
    lineHeight: 32px
    letterSpacing: 0px
rounded:
  sm: 10px
  md: 14px
  lg: 18px
  xl: 24px
  2xl: 28px
  full: 9999px
  # Token lama: isian dan snackbar Material, dihapus di R9 bila tak terpakai.
  xs: 4px
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
  shadow-color: "rgb(30 41 100)"
  level-0:
    container: "{colors.surface}"
    shadow: none
  card:
    key: 0px 10px 30px -12px
    key-opacity: 0.14
    ambient: 0px 1px 2px 0px
    ambient-opacity: 0.04
  control:
    key: 0px 4px 14px -8px
    key-opacity: 0.20
  chip:
    key: 0px 2px 8px -2px
    key-opacity: 0.20
  overlay:
    key: 0px 24px 60px -20px
    key-opacity: 0.35
  # Tingkat lama: dipertahankan sampai pemakaiannya dialihkan (R2 sampai R8), dihapus di R9.
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
  duration-medium-ms: 200
  easing-standard: "cubic-bezier(0.2, 0, 0, 1)"
components:
  app-background:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-l}"
  sidebar:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface-variant}"
    rounded: "{rounded.2xl}"
    width: 248px
    padding: 16px
  brand-mark:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    rounded: "{rounded.md}"
    size: 42px
  nav-item:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    height: 48px
    padding: 14px
  nav-item-hover:
    backgroundColor: "{colors.surface-container-low}"
    textColor: "{colors.on-surface-variant}"
  nav-item-active:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.primary}"
  nav-badge:
    backgroundColor: "{colors.error}"
    textColor: "{colors.on-error}"
    typography: "{typography.chip}"
    rounded: "{rounded.full}"
    height: 22px
  user-block:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    rounded: "{rounded.lg}"
    padding: 12px
  top-app-bar:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    rounded: "{rounded.xl}"
    padding: 8px
  drawer:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface-variant}"
    rounded: "{rounded.2xl}"
    width: 316px
    padding: 16px
  icon-button:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    rounded: "{rounded.lg}"
    size: 48px
  notification-dot:
    backgroundColor: "{colors.error}"
    rounded: "{rounded.full}"
    size: 10px
  public-top-bar:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.title-m}"
    height: 60px
  bottom-bar:
    backgroundColor: "{colors.surface-container-lowest}"
    padding: 16px
  panel:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-m}"
    rounded: "{rounded.xl}"
    padding: 24px
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    height: 44px
    padding: 20px
  button-secondary:
    backgroundColor: "{colors.surface-container}"
    textColor: "{colors.on-surface}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    height: 44px
    padding: 20px
  button-ghost:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.primary}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    height: 44px
    padding: 12px
  button-danger:
    backgroundColor: "{colors.error-container}"
    textColor: "{colors.on-error-container}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    height: 44px
    padding: 20px
  button-on-secondary:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.secondary}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    height: 44px
    padding: 20px
  text-field:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-m}"
    rounded: "{rounded.md}"
    height: 48px
    padding: 14px
  text-field-outline:
    backgroundColor: "{colors.outline}"
    height: 1px
  text-field-label:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.label-l}"
  text-field-supporting:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.label-m}"
  text-field-error:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.error}"
    typography: "{typography.label-m}"
  choice:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    padding: 12px
  choice-selected:
    backgroundColor: "{colors.selected}"
    textColor: "{colors.on-surface}"
  choice-icon:
    backgroundColor: "{colors.surface-container}"
    textColor: "{colors.on-surface-variant}"
    rounded: "{rounded.sm}"
    size: 30px
  choice-icon-selected:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    rounded: "{rounded.sm}"
    size: 30px
  tab-track:
    backgroundColor: "{colors.surface-container}"
    rounded: "{rounded.md}"
    padding: 4px
  tab:
    backgroundColor: "{colors.surface-container}"
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.label-m}"
    rounded: "{rounded.sm}"
    height: 36px
  tab-active:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
  tab-floating-track:
    backgroundColor: "{colors.surface-container-lowest}"
    rounded: "{rounded.lg}"
    padding: 4px
  tab-floating:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    height: 38px
  tab-floating-active:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
  badge-primary:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.on-primary-container}"
    typography: "{typography.chip}"
    rounded: "{rounded.full}"
    height: 26px
    padding: 11px
  badge-warning:
    backgroundColor: "{colors.warning-container}"
    textColor: "{colors.on-warning-container}"
    typography: "{typography.chip}"
    rounded: "{rounded.full}"
    height: 26px
    padding: 11px
  badge-success:
    backgroundColor: "{colors.success-container}"
    textColor: "{colors.on-success-container}"
    typography: "{typography.chip}"
    rounded: "{rounded.full}"
    height: 26px
    padding: 11px
  badge-error:
    backgroundColor: "{colors.error-container}"
    textColor: "{colors.on-error-container}"
    typography: "{typography.chip}"
    rounded: "{rounded.full}"
    height: 26px
    padding: 11px
  badge-tertiary:
    backgroundColor: "{colors.tertiary-container}"
    textColor: "{colors.on-tertiary-container}"
    typography: "{typography.chip}"
    rounded: "{rounded.full}"
    height: 26px
    padding: 11px
  badge-neutral:
    backgroundColor: "{colors.secondary-container}"
    textColor: "{colors.on-secondary-container}"
    typography: "{typography.chip}"
    rounded: "{rounded.full}"
    height: 26px
    padding: 11px
  icon-chip:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.primary}"
    rounded: "{rounded.md}"
    size: 40px
  icon-chip-lg:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.primary}"
    rounded: "{rounded.md}"
    size: 44px
  avatar:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.on-primary-container}"
    typography: "{typography.chip}"
    rounded: "{rounded.full}"
    size: 40px
  stat-card:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.stat-l}"
    rounded: "{rounded.xl}"
    padding: 24px
  hero-stat:
    backgroundColor: "{colors.secondary}"
    textColor: "{colors.on-secondary}"
    typography: "{typography.stat-hero}"
    rounded: "{rounded.xl}"
    padding: 24px
  hero-stat-caption:
    backgroundColor: "{colors.secondary}"
    textColor: "{colors.primary-container}"
    typography: "{typography.body-m}"
  sparkline:
    backgroundColor: "{colors.primary}"
    width: 92px
    height: 38px
  sparkline-warning:
    backgroundColor: "{colors.chart-amber}"
    width: 92px
    height: 38px
  sparkline-success:
    backgroundColor: "{colors.chart-mint}"
    width: 92px
    height: 38px
  table-header:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.label-m}"
    padding: 8px
  table-row:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-m}"
    rounded: "{rounded.lg}"
    padding: 12px
  table-row-hover:
    backgroundColor: "{colors.surface-container-low}"
    textColor: "{colors.on-surface}"
  ring-tile:
    backgroundColor: "{colors.surface-container-low}"
    textColor: "{colors.on-surface}"
    typography: "{typography.ring-value}"
    rounded: "{rounded.lg}"
    padding: 14px
  ring-track:
    backgroundColor: "{colors.surface-container-highest}"
    size: 64px
  ring-fill:
    backgroundColor: "{colors.primary}"
    size: 64px
  ring-fill-low:
    backgroundColor: "{colors.chart-coral}"
    size: 64px
  ring-low-text:
    backgroundColor: "{colors.surface-container-low}"
    textColor: "{colors.on-error-container}"
  chart-series-masuk:
    backgroundColor: "{colors.primary}"
  chart-series-selesai:
    backgroundColor: "{colors.chart-mint}"
  chart-status-menunggu:
    backgroundColor: "{colors.chart-amber}"
  chart-status-terverifikasi:
    backgroundColor: "{colors.primary}"
  chart-status-aktif:
    backgroundColor: "{colors.tertiary}"
  chart-status-selesai:
    backgroundColor: "{colors.chart-mint}"
  chart-status-ditolak:
    backgroundColor: "{colors.chart-coral}"
  chart-status-dibatalkan:
    backgroundColor: "{colors.chart-slate}"
  chart-grid:
    backgroundColor: "{colors.surface-container-highest}"
    height: 1px
  chart-tooltip:
    backgroundColor: "{colors.inverse-surface}"
    textColor: "{colors.inverse-on-surface}"
    padding: 14px
  legend-pill:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    typography: "{typography.label-m}"
    rounded: "{rounded.full}"
    height: 32px
    padding: 12px
  empty-state:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface-variant}"
    typography: "{typography.body-s}"
    rounded: "{rounded.md}"
    padding: 22px
  empty-state-icon:
    backgroundColor: "{colors.surface-container}"
    textColor: "{colors.on-surface-variant}"
    rounded: "{rounded.md}"
    size: 44px
  note-info:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.on-primary-container}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    padding: 12px
  note-warning:
    backgroundColor: "{colors.warning-container}"
    textColor: "{colors.on-warning-container}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    padding: 12px
  note-success:
    backgroundColor: "{colors.success-container}"
    textColor: "{colors.on-success-container}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    padding: 12px
  note-error:
    backgroundColor: "{colors.error-container}"
    textColor: "{colors.on-error-container}"
    typography: "{typography.label-l}"
    rounded: "{rounded.md}"
    padding: 12px
  snackbar:
    backgroundColor: "{colors.inverse-surface}"
    textColor: "{colors.inverse-on-surface}"
    typography: "{typography.body-m}"
    rounded: "{rounded.md}"
    padding: 14px
  snackbar-action:
    backgroundColor: "{colors.inverse-surface}"
    textColor: "{colors.inverse-primary}"
    typography: "{typography.label-l}"
  dialog:
    backgroundColor: "{colors.surface-container-lowest}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-m}"
    rounded: "{rounded.2xl}"
    padding: 24px
    width: 448px
  dialog-scrim:
    backgroundColor: "{colors.scrim}"
  timeline-line:
    backgroundColor: "{colors.outline-variant}"
    width: 2px
  timeline-dot-current:
    backgroundColor: "{colors.primary}"
    rounded: "{rounded.full}"
    size: 12px
  progress-track:
    backgroundColor: "{colors.surface-container}"
    rounded: "{rounded.full}"
    height: 6px
  progress-fill:
    backgroundColor: "{colors.primary}"
    rounded: "{rounded.full}"
    height: 6px
  chart-bar:
    backgroundColor: "{colors.chart-sky}"
    rounded: "{rounded.sm}"
    width: 36px
---

# SIGAP Ambulans — sistem desain

## Overview

SIGAP Ambulans dipakai empat kelompok orang dengan kebutuhan berbeda: masyarakat yang sedang panik dan memegang ponsel, operator Dinkes yang menyaring dan menugaskan pesanan seharian di layar lebar, petugas Faskes yang memperbarui ketersediaan bed dan dokter, serta sopir yang membaca layar sekilas di dalam kendaraan. Antarmuka harus terbaca dalam satu pandang dan tidak pernah menghalangi.

Arahnya **B "Terang lembut"** (keputusan pemilik proyek, `docs/RETEMA_B.md`; papan acuan di `docs/referensi-b/`): terang dan ramah. Latar `surface` yang sangat muda kebiruan, kartu putih bersudut besar dengan bayangan lembut, bilah samping putih yang melayang, huruf Plus Jakarta Sans dengan judul tebal (800) dan angka besar. Warna dipakai untuk satu tugas: membawa makna. Biru `primary` untuk aksi dan item aktif, kartu navy (`secondary`) untuk satu hal yang paling butuh tindakan, dan keluarga warna status (kuning, biru, ungu, hijau, merah, abu) untuk chip, lencana, dan grafik. Tidak ada gradien dekoratif, ilustrasi, emoji, atau warna tanpa arti. Nama peran warna mengikuti Material Design 3; mode gelap tidak dibuat.

Data yang ditampilkan nyata dan spesifik (kode pesanan, nama, menit menunggu), bukan angka hiasan. Warna kaya hanya ada di dasbor dan di tempat status berada; halaman data master dan formulir tenang: kartu putih, tipografi, dan jarak yang rapi. Antarmuka ini bukan situs pemasaran: tidak ada hero bergambar, ilustrasi dekoratif, atau kalimat penjualan. Kata-katanya faktual, berbahasa Indonesia, huruf awal kapital hanya pada awal kalimat.

## Colors

Palet arah B diambil dari papan `docs/referensi-b/` dan dibekukan sebagai nilai heksadesimal di bagian token; palet ini tidak diturunkan dari warna benih. Nama peran M3 yang sudah ada dipertahankan supaya komponen lama tetap jalan. Pasangan kontras wajib dicatat di `agent/kontras.json` dan dijaga `KontrasTokenTest`: teks minimal 4,5:1, bukan teks (batas isian, garis grafik utama, ikon) minimal 3:1.

Peran:

- **Surface (#F3F5FB):** latar halaman. **Surface-container-lowest (#FFFFFF):** kartu, bilah samping, dialog, isian. **Surface-container-low (#F7F8FD):** hover baris, petak, kartu cincin. **Surface-container (#F3F5FB):** trek tab, blok pengguna, pil legenda, tombol sekunder. **Surface-container-high (#EEF1F7)** dan **-highest (#E7EAF3):** garis dalam kartu, garis kisi grafik, trek cincin.
- **On-surface (#1B2437):** teks utama. **On-surface-variant (#5F6B80):** satu-satunya warna teks sekunder (keterangan, label sumbu, kepala tabel, label grup menu). Tidak ada abu-abu lain untuk teks.
- **Primary (#1368CE)**, **On-primary**, **Primary-container (#E4EEFF)**, **On-primary-container (#0F55A8):** aksi, tautan, fokus, item menu aktif (latar `primary-container`, teks `primary`), dan seri grafik "Masuk".
- **Secondary (#102A56)** dan **On-secondary:** navy untuk kartu utama, paling banyak satu per halaman, hanya untuk hal yang menunggu tindakan; `secondary` juga warna teks tombol putih di atas kartu itu. **Secondary-container (#EEF1F7)** dan **On-secondary-container (#475066):** lencana dan chip netral.
- **Tertiary (#7A5AF8)**, **Tertiary-container (#EDE8FF)**, **On-tertiary-container (#5B3FD0):** status aktif di jalan.
- **Success (#0B7A53)**, **Warning (#A86300)**, dan **Error (#CC3A24)**, masing-masing dengan `on-`, container, dan on-container: makna status. Error juga untuk lencana hitungan dan titik lonceng (teks `on-error`), galat isian, dan aksi merusak (tonal `error-container`).
- **Outline (#7F8AA0):** batas isian dan kartu pilihan (kontras 3:1 ke atas). **Outline-variant (#D0D6E4):** hanya pemisah dekoratif, garis linimasa, dan batas putus-putus keadaan kosong.
- **Selected (#EEF4FF):** latar kartu pilihan terpilih.
- **Inverse-surface (#1B2437)**, **Inverse-on-surface (#FFFFFF)**, **Inverse-primary (#8DB8FF):** tooltip grafik, snackbar, dan aksi di snackbar.
- **Scrim (#1B2437):** dipakai dengan opasitas 45% (`bg-scrim/45`) di bawah dialog dan laci; token tidak membawa alfa.
- **Chart-mint (#0FA582)**, **Chart-coral (#FF6B57)**, **Chart-amber (#F5A524)**, **Chart-slate (#A3ADC2):** seri grafik. Seri grafik hanya memakai `primary`, `tertiary`, dan `chart-*`. Garis grafik utama berkontras minimal 3:1 terhadap putih (`primary`, `chart-mint`); segmen donat, sparkline, dan cincin boleh lebih terang karena nilainya selalu tertulis.
- Token lama yang masih dipakai komponen dasbor Material dan dihapus di R9: **Canvas** (sama dengan Surface) dan **Chart-sky (#7FB9DA)**.

Aturan pemakaian:

- Chip, lencana, dan avatar memakai pasangan container (`*-container` dengan `on-*-container`). Pasangan solid (`primary`, `error`, `secondary` dengan `on-*`) hanya untuk tombol utama, lencana hitungan, dan kartu navy.
- Di kartu navy: dua lingkaran dekorasi `primary` 25% dan 15% (`bg-primary/25`, `bg-primary/15`), teks kecil `primary-container`, chip ikon `on-secondary` 15%. Tidak ada token baru untuk itu.
- Warna avatar tidak membawa makna: `crc32(nama) % 5` memilih salah satu pasangan container `primary`, `error`, `success`, `tertiary`, atau `warning`. Avatar `aria-hidden`; nama selalu terlihat di sebelahnya.
- **Warna tidak pernah satu-satunya pembawa informasi:** status selalu bertulis, setiap grafik punya nilai tertulis dan legenda, cincin yang menipis bertulis "menipis".

Satu tabel status, sumber tunggal untuk lencana, chip, dan grafik (`PesananStatus::tone()`):

| Status | Tone | Chip dan lencana | Grafik |
|---|---|---|---|
| Menunggu verifikasi | `warning` | `warning-container` / `on-warning-container` | `chart-amber` |
| Terverifikasi | `primary` | `primary-container` / `on-primary-container` | `primary` |
| Ditugaskan, Diterima sopir, Dalam perjalanan | `tertiary` | `tertiary-container` / `on-tertiary-container` | `tertiary` |
| Selesai | `success` | `success-container` / `on-success-container` | `chart-mint` |
| Ditolak | `error` | `error-container` / `on-error-container` | `chart-coral` |
| Dibatalkan | `neutral` | `secondary-container` / `on-secondary-container` | `chart-slate` |

Lencana "Gawat" memakai tone `error`. Status aktif yang berbeda memakai tone dan warna grafik yang sama, tetapi legendanya tetap menulis tiap status. Status baru di Enum diberi tone dengan prinsip yang sama dan dicatat di `docs/KIT_UI.md`.

Warna di markup hanya lewat token ini (`bg-surface`, `text-on-surface-variant`, `border-outline`, `bg-primary`, ...). Palet bawaan Tailwind dimatikan; nilai heksadesimal dan nilai sembarang (`bg-[#…]`) dilarang.

## Typography

Satu keluarga huruf: **Plus Jakarta Sans**, variabel (bobot 200 sampai 800), di-host sendiri lewat paket `@fontsource-variable/plus-jakarta-sans` sehingga tidak ada unduhan font dari internet. Bobot yang dipakai: 400, 600, 700, dan 800. Tidak ada huruf kedua dan tidak ada font monospace.

Setiap gaya adalah satu kelas `text-<gaya>` yang mengatur ukuran, tinggi baris, bobot, dan jarak huruf sekaligus (`agent/tokens.mjs` mengeluarkan `--text-<gaya>` beserta `--line-height`, `--font-weight`, dan `--letter-spacing`). Jarak huruf berasal dari token, bukan dari kelas `tracking-*`; bobot berasal dari token, bukan dari kelas `font-*`.

| Gaya | Ukuran / tinggi baris / bobot / jarak huruf | Dipakai untuk |
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

- Tidak ada teks di bawah 12px. Papan memakai bobot 700 di beberapa label yang di sini 600; token yang menang.
- Semua angka (waktu, jumlah, kode pesanan, nomor telepon) memakai `tabular-nums`.
- Tidak ada huruf kapital semua (`uppercase`), tidak ada teks miring, tidak ada satu kata berwarna di tengah judul.
- Panjang baris isi paling banyak 70 karakter (`max-w-prose`).
- Gaya lama yang masih dipakai komponen dasbor Material dan dihapus di R9 bila tak terpakai: `display-l|m|s`, `headline-l|m|s`, `title-s`, `label-s` (11px, hanya hitungan lonceng lama), `card-title`, dan `stat-m`. Komponen lama yang masih memakai `font-normal` atau `font-medium` dialihkan ke gaya token di R2 sampai R8.

## Layout

Mobile-first. Titik henti Tailwind: `sm` 640px, `md` 768px, `lg` 1024px (bilah samping tetap), `xl` 1280px (kisi dasbor 8/4 dan empat kolom statistik). Skala jarak berbasis 4px; pakai skala numerik bawaan Tailwind (`p-4` = 16px, `gap-5` = 20px).

- **Desktop (`lg` ke atas):** akar halaman `bg-surface p-4`, bilah samping putih melayang selebar 248px di kiri (sudut `2xl`, `shadow-card`, lekat di atas setinggi layar dikurangi 32px, satu bilah gulir vertikal), tanpa bilah atas. Kepala halaman (`<header>`) berisi judul atau sapaan di kiri dan, di kanan, filter periode (bila halaman memakainya), tombol cari, dan lonceng. Kolom isi `min-w-0`, dibatasi `max-w-screen-2xl`.
- **Di bawah `lg`:** bilah atas melayang (putih, sudut `xl`, `shadow-card`, lekat di atas) berisi tombol menu, tanda merek, lonceng, dan avatar; menu menjadi laci modal selebar 316px yang melayang 12px dari tepi, di atas scrim. Judul memakai `page-title-s`; filter periode selebar penuh.
- **Jarak:** antar kartu 20px (12px di kisi statistik ponsel), padding halaman 16px, padding kartu 24px (20px di ponsel), jarak antar bagian di ponsel 16px.
- **Halaman publik** (buat dan lacak pesanan): tanpa bilah samping, kolom `max-w-md` di tengah layar lebar, bilah atas 60px, aksi utama formulir di bilah bawah lekat berisi satu tombol penuh lebar. **Masuk:** kartu `max-w-sm` di tengah.
- **Tabel dan daftar:** di dalam panel `flush`; di bawah `md` tabel menjadi daftar baris tanpa markup ganda. Tidak ada scroll horizontal pada halaman di lebar 390px; hanya tabel dan grafik yang boleh bergulir di dalam kartunya.
- **Formulir:** satu kolom, lebar maksimum 36rem, label di atas isian (tidak mengapung), galat tepat di bawah isian.
- **Target sentuh:** tombol 48px di bawah `lg` dan 44px di atasnya; kolom isian, item menu, dan tombol ikon 48px.
- Satu `<h1>` per halaman; judul kartu `<h2>`. Teks rata kiri; rata tengah hanya untuk kartu masuk, keadaan kosong, dan petak cincin.
- Token `spacing.drawer` (280px) dan `spacing.content-max` (1152px) masih milik kerangka lama sampai R3.

## Elevation & Depth

Hierarki dibentuk oleh **latar `surface` lawan kartu putih berbayang**, lalu jarak 20px. Bayangan hanya dari token di bawah; warnanya tinta biru tua `rgb(30 41 100)` (`elevation.shadow-color`), bukan hitam.

| Token (kelas) | Nilai | Dipakai untuk |
|---|---|---|
| `card` (`shadow-card`) | `0 10px 30px -12px rgb(30 41 100 / .14), 0 1px 2px rgb(30 41 100 / .04)` | kartu, panel, bilah samping, kartu navy |
| `control` (`shadow-control`) | `0 4px 14px -8px rgb(30 41 100 / .20)` | kontrol melayang: filter periode, tombol ikon, bilah atas ponsel |
| `chip` (`shadow-chip`) | `0 2px 8px -2px rgb(30 41 100 / .20)` | segmen aktif di trek tab dan segmented |
| `overlay` (`shadow-overlay`) | `0 24px 60px -20px rgb(30 41 100 / .35)` | dialog, menu tarik, laci ponsel, snackbar |

- Elemen di dalam kartu (petak, baris tabel, trek tab) datar (`shadow-none`); kartu berbayang tidak berada di dalam kartu berbayang. Item menu aktif tidak berbayang.
- Token lama dipertahankan sampai pemakaiannya dialihkan (R2 sampai R8) dan dihapus di R9: `level-1` sampai `level-3` (kini ikut bertinta biru tua), `card-raised`, dan `nav-active` (warna `on-surface`).
- Fokus papan ketik selalu terlihat: cincin 2px `primary` dengan jarak 2px.

## Shapes

| Token | Nilai | Dipakai untuk |
|---|---|---|
| `sm` | 10px | segmen di dalam trek tab, ikon kartu pilihan |
| `md` | 14px | tombol, isian, item menu, chip ikon, kartu pilihan, snackbar, catatan, keadaan kosong |
| `lg` | 18px | kontrol melayang (filter periode, tombol ikon), baris tabel, petak dan kartu cincin, blok pengguna |
| `xl` | 24px | kartu dan panel |
| `2xl` | 28px | bilah samping, dialog, laci ponsel |
| `full` | 9999px | lencana, chip delta, pil legenda, avatar, tombol hitungan |

Papan memakai 9, 11, 12, 13, 16, dan 22px di beberapa tempat; semuanya dilebur ke skala ini. Tidak ada sudut di luar skala dan tidak ada nilai sembarang (`rounded-[…]`). Token lama `xs` (4px) masih dipakai isian, snackbar, dan tooltip grafik lama sampai komponennya diganti, lalu dihapus di R9.

## Components

Token komponen ada di bagian `components` di atas. API Blade dan status pembangunannya ada di `docs/KIT_UI.md` (bagian Retema B); komponen dibangun di R2 sampai R4.

- **Panel (kartu):** `surface-container-lowest`, sudut `xl`, `shadow-card`, tanpa batas, padding 24px (20px di ponsel); varian `flush` tanpa padding untuk tabel. Kepala opsional: judul `title-l`, subjudul `body-s` `on-surface-variant`, aksi di kanan, jarak bawah 12px.
- **Tombol:** sudut `md`, label `label-l`, ikon 16 sampai 18px di kiri, tinggi 44px di desktop dan 48px di bawah `lg` (ukuran kecil 36 dan 40px), padding 20px (12px untuk ghost). Varian: `primary` (`primary`/`on-primary`), `secondary` (`surface-container`, batas `outline-variant`, teks `on-surface`), `ghost` (tanpa latar, teks `primary`), `danger` (tonal `error-container`/`on-error-container`), dan `on-secondary` (putih dengan teks `secondary`, hanya di atas kartu navy). Nonaktif: opasitas 40%, tanpa bayangan. Tombol yang memicu permintaan lama memakai `aria-busy` dan mencegah klik ganda.
- **Isian, pilihan, area teks:** label `label-l` di atas isian, tinggi 48px, sudut `md`, batas `outline`, latar putih, padding 14px, teks `body-m`. Fokus: batas `primary` dan cincin 3px `primary` 20%. Galat: batas `error`, pesan `label-m` `error` di bawah, `aria-invalid` dan `aria-describedby`. Bantuan `label-m` `on-surface-variant`. Wajib ditulis "(wajib)" di label, bukan bintang berwarna. Area teks minimal 84px; pilihan memakai ikon `chevron-down` 18px di kanan.
- **Kartu pilihan:** kisi tiga kolom (jarak 10px); tiap pilihan `<label>` berisi radio `sr-only`, petak ikon 30px (`surface-container`, sudut `sm`), dan teks `label-l`; batas `outline`, sudut `md`, padding 12px 10px. Terpilih: batas dan cincin dalam 1px `primary`, latar `selected`, petak ikon `primary` dengan ikon `on-primary`. Dipakai untuk jenis layanan.
- **Tab dan segmented:** tiga tampilan dari satu pola. Tab `track`: trek `surface-container` (sudut `md`, padding 4px), item 36px sudut `sm` `label-m` `on-surface-variant`, aktif putih `on-surface` dengan `shadow-chip`; item adalah tautan `?tab=` dengan `aria-current="page"`, tanpa JavaScript. Tab `floating` (filter periode): trek putih sudut `lg` dengan `shadow-control`, item 38px (40px di ponsel) sudut `md` `label-l`, aktif `primary`. Segmented: radio dalam formulir (Biasa, Gawat) dengan tampilan `track`.
- **Lencana dan chip:** lencana status berbentuk pil 26px (24px kecil), padding 11px, gaya `chip`, titik 6px `currentColor`; nada `primary`, `warning`, `success`, `error`, `tertiary`, `neutral` dengan pasangan container. Chip keterangan adalah lencana kecil tanpa titik, boleh dua baris di ponsel.
- **Chip ikon:** 40px (ikon 21px) atau 44px (ikon 22px), sudut `md`. Latar dan ikon menurut nada: `primary` (`primary-container`, ikon `primary`), `warning` (`warning-container`, ikon `warning`), `success`, `error`, dan `tertiary` (container dengan ikon on-container), `neutral` (`secondary-container`, ikon `on-secondary-container`). `aria-hidden`; teks pendamping selalu ada.
- **Avatar:** lingkaran 40px atau 36px, inisial dua kata pertama (maksimum dua huruf) bergaya `chip`, warna dari hash nama.
- **Kartu statistik:** putih, padding 24px (16px di ponsel); chip ikon 44px di kiri atas dan sparkline 92×38 di kanan atas (disembunyikan di bawah `sm`), angka `stat-l`, label `label-l`, chip keterangan; tautan hanya pada label. **Kartu navy:** `secondary` dengan `shadow-card`, dua lingkaran dekorasi di belakang isi, chip ikon `on-secondary` 15%, judul `title-m`, angka `stat-hero`, keterangan `body-m` `primary-container`, satu tombol `on-secondary`; tanpa sparkline.
- **Tabel:** `<table>` semantik di dalam panel `flush`. Kepala `label-m` `on-surface-variant`, `scope="col"`; baris bersudut `lg` (spasi antarbaris 4px), hover `surface-container-low`, tanpa garis pemisah; seluruh baris dapat diklik lewat tautan pada nama pasien, chevron akhir hanya hiasan. Di bawah `md` tabel menjadi daftar baris tiga kolom (avatar, nama dan keterangan, status dan jam) dengan garis 1px `surface-container-highest`.
- **Cincin ketersediaan:** petak `surface-container-low` sudut `lg` berisi cincin SVG 64px (tebal 7, trek `surface-container-highest`, isi `primary`, atau `chart-coral` bila tersedia kurang dari 12% total), angka `ring-value`, label `label-l`, dan "dari {total}" (ditambah ", menipis" berwarna `on-error-container`). Cincin ber-`role="img"` dengan `aria-label` lengkap.
- **Grafik:** garis dan donat Chart.js (warna dibaca dari variabel CSS token, tanpa hex di JavaScript), sparkline dan cincin SVG sisi-server. Setiap grafik punya `role="img"`, `aria-label` yang menyebut semua nilai, tabel `sr-only`, dan nilai terlihat di legenda atau tooltip. Legenda grafik garis berupa pil HTML (`surface`, `rounded-full`, tinggi 32px); tooltip `inverse-surface`.
- **Keadaan kosong:** batas putus-putus 1px `outline-variant`, sudut `md`, padding 22px, di tengah; petak ikon 44px (`surface-container`), judul `label-l`, teks `body-s` `on-surface-variant` yang mengarahkan tindakan, aksi opsional tombol `secondary` kecil.
- **Catatan:** nada `warning`, `info`, `error`, `success`; sudut `md`, padding 12px 14px, ikon 18px, pasangan container, teks bobot 600.
- **Snackbar:** `inverse-surface` dengan teks `inverse-on-surface`, sudut `md`, padding 14px 16px, aksi `inverse-primary` `label-l`, `role="status"`, `shadow-overlay`.
- **Dialog:** `<dialog>` bawaan, putih, sudut `2xl`, `shadow-overlay`, padding 24px, lebar `min(92vw, 28rem)`, judul `title-l`, isi `body-m` `on-surface-variant`, tombol rata kanan (ghost "Batal", lalu aksi; aksi merusak memakai `danger`). Backdrop `scrim` 45%. Fokus awal pada elemen aman; Esc menutup.
- **Linimasa dan daftar info:** garis tegak 2px `outline-variant`, titik 12px (langkah lampau putih berbatas 2px `outline-variant`, langkah sekarang terisi `primary`), kolom waktu 56px `on-surface-variant` `tabular-nums`, judul `label-l`, keterangan `body-s`; langkah sekarang berbobot 700. Daftar info (`<dl>`): label `label-m` `on-surface-variant`, nilai `body-m`, jarak 16px.
- **Menu:** tanda merek 42px (`primary`, sudut `md`, ikon `plus` bergaris 2,25) dan nama aplikasi gaya `brand`; label grup `label-m`; item 48px sudut `md`, ikon 21px, teks `label-l` `on-surface-variant`, hover `surface-container-low`, aktif `primary-container` dengan teks `primary` dan `aria-current="page"`. Lencana hitungan pesanan menunggu: `error`/`on-error`, `rounded-full`, minimal 22px, gaya `chip`. Di dasar: blok pengguna `surface` sudut `lg` berisi avatar, nama, peran, dan tombol keluar berupa form POST ber-CSRF.
- **Pencarian dan lonceng:** tombol ikon 48px (putih, sudut `lg`, `shadow-control`) yang membuka dialog "Cari pesanan"; lonceng adalah tautan ke daftar pesanan tab "Perlu tindakan" dengan titik `error` 10px bertepi putih hanya bila ada pesanan menunggu.

## Do's and Don'ts

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

## Interaction states

Umpan balik memakai **lapisan keadaan**: lapisan berwarna sama dengan teks atau ikon kontrol, dengan opasitas tetap, di atas latar kontrol. Opasitasnya dari token `state`: hover 8%, fokus 10%, tekan 10%, seret 16%. Warnanya mengikuti token baru; mekanismenya tetap. Baris yang dapat diklik berubah ke latar `surface-container-low` saat hover. Kontrol nonaktif memakai opasitas 40% tanpa bayangan; tidak ada kontrol nonaktif yang hanya berubah warna aksen (token `state.disabled-*` milik komponen lama sampai R9). Fokus papan ketik menambah cincin 2px `primary` dengan jarak 2px; isian memakai batas `primary` dan cincin 3px `primary` 20%. Tidak ada riak (ripple) dan tidak ada efek angkat. **Memuat:** saat formulir dikirim, tombol kirim dinonaktifkan dan labelnya berganti kata kerja proses ("Mengirim pesanan...", bawaan "Memproses..."), dan formulir diberi `aria-busy`; tidak ada spinner berputar karena tidak ada animasi berulang.

## Iconography

Ikon Lucide (garis 1,75px; 2,25px untuk tanda merek) dari paket `lucide-static`, disalin ke `resources/icons/` dan ditampilkan lewat komponen `x-ui.ikon`. Ukuran: 21px pada item menu dan tombol ikon, 21 atau 22px pada chip ikon, 16 sampai 18px di dalam tombol, 18px pada catatan dan pilihan. Warna mengikuti teks sekitarnya (`currentColor`). Ikon hanya membawa makna dan selalu disertai teks. Daftar ikon yang tersedia ada di `agent/icons.txt` dan `docs/KIT_UI.md`; ikon di luar daftar tidak boleh dipakai.

## Motion

- Interaksi: transisi 150ms (`motion.duration-short-ms`) pada `background-color`, `color`, `border-color`, dan `box-shadow`, dengan easing standar `cubic-bezier(0.2, 0, 0, 1)`. Lapisan keadaan (hover, tekan, fokus) tetap seperti sekarang; hanya warnanya mengikuti token baru. Baris yang dapat diklik berubah ke `surface-container-low` saat hover.
- Buka dan tutup: dialog memudar dan membesar dari 0,96 ke 1 dalam 200ms (`motion.duration-medium-ms`); laci ponsel bergeser dari kiri 200ms; scrim memudar 200ms.
- Grafik menggambar masuk sekali, 700ms, `easeOutQuart`, tidak diulang saat data tidak berubah.
- Tidak ada animasi masuk untuk bagian halaman, tidak ada hitung-naik angka, dan tidak ada gerak yang bukan jawaban atas aksi pengguna (kecuali gambar-masuk grafik).
- `prefers-reduced-motion: reduce`: semua transisi dan animasi dimatikan.

## Writing

Bahasa Indonesia baku yang sederhana, aktif, huruf awal kapital hanya pada awal kalimat dan nama diri ("Dalam perjalanan", bukan "Dalam Perjalanan"). Tombol berupa kata kerja ("Verifikasi sekarang", "Kirim pesanan"). Nama aksi sama di seluruh alur ("Verifikasi" di tombol menghasilkan pesan "Pesanan berhasil diverifikasi."). Galat menyebut apa yang salah dan apa yang bisa dilakukan, tanpa meminta maaf dan tanpa tanda seru. Keadaan kosong menyebut langkah berikutnya ("Pesanan baru muncul di sini setelah dibuat."). Jam memakai format Indonesia dengan titik ("21.12"); sufiks "WIT" hanya di halaman detail. Jangan menulis "Diperbarui otomatis" kecuali halaman benar-benar menyegarkan sendiri; tampilkan "Diperbarui 21.14" dari waktu server. Tidak ada tanda panah di akhir tautan, tidak ada pemisah titik tengah untuk menggabung keterangan, tidak ada label berhuruf kapital semua di atas judul.
