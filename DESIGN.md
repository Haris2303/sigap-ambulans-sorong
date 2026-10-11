---
version: alpha
name: SIGAP Ambulans
description: Sistem Informasi Layanan Ambulans Terpadu Dinas Kesehatan Kota Sorong. Antarmuka layanan publik yang tenang, padat informasi, dan terbaca cepat di ponsel maupun layar operator.
colors:
  canvas: "#F3F5F7"
  surface: "#FFFFFF"
  on-surface: "#16212B"
  on-surface-muted: "#566472"
  line: "#DCE1E6"
  control-border: "#7D8A97"
  primary: "#0B5C80"
  primary-hover: "#084A68"
  primary-soft: "#E3EFF5"
  on-primary: "#FFFFFF"
  danger: "#A8231B"
  danger-hover: "#8C1D17"
  danger-soft: "#FBEAE8"
  warning: "#7A4B00"
  warning-soft: "#FBF0DA"
  success: "#1B6B42"
  success-soft: "#E4F2EA"
  neutral-soft: "#E9ECEF"
  scrim: "#16212B80"
typography:
  display:
    fontFamily: Public Sans Variable
    fontSize: 28px
    fontWeight: 600
    lineHeight: 1.15
    letterSpacing: -0.01em
    fontFeature: '"tnum" 1'
  h1:
    fontFamily: Public Sans Variable
    fontSize: 24px
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: -0.01em
  h2:
    fontFamily: Public Sans Variable
    fontSize: 18px
    fontWeight: 600
    lineHeight: 1.3
  h3:
    fontFamily: Public Sans Variable
    fontSize: 16px
    fontWeight: 600
    lineHeight: 1.4
  body:
    fontFamily: Public Sans Variable
    fontSize: 16px
    fontWeight: 400
    lineHeight: 1.55
  body-sm:
    fontFamily: Public Sans Variable
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: Public Sans Variable
    fontSize: 14px
    fontWeight: 500
    lineHeight: 1.4
  caption:
    fontFamily: Public Sans Variable
    fontSize: 13px
    fontWeight: 400
    lineHeight: 1.4
rounded:
  sm: 4px
  md: 6px
  lg: 8px
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
  touch: 44px
  rail: 232px
  content-max: 1152px
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.label}"
    rounded: "{rounded.md}"
    height: "{spacing.touch}"
    padding: "{spacing.lg}"
  button-primary-hover:
    backgroundColor: "{colors.primary-hover}"
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    typography: "{typography.label}"
    rounded: "{rounded.md}"
    height: "{spacing.touch}"
    padding: "{spacing.lg}"
  button-secondary-hover:
    backgroundColor: "{colors.canvas}"
  button-danger:
    backgroundColor: "{colors.danger}"
    textColor: "{colors.surface}"
    typography: "{typography.label}"
    rounded: "{rounded.md}"
    height: "{spacing.touch}"
    padding: "{spacing.lg}"
  button-danger-hover:
    backgroundColor: "{colors.danger-hover}"
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body}"
    rounded: "{rounded.md}"
    height: "{spacing.touch}"
    padding: "{spacing.md}"
  panel:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    rounded: "{rounded.lg}"
    padding: "{spacing.xl}"
  nav-item:
    textColor: "{colors.on-surface-muted}"
    typography: "{typography.label}"
    rounded: "{rounded.md}"
    height: "{spacing.touch}"
    padding: "{spacing.md}"
  nav-item-active:
    backgroundColor: "{colors.primary-soft}"
    textColor: "{colors.primary}"
  divider:
    backgroundColor: "{colors.line}"
    height: 1px
  control-outline:
    backgroundColor: "{colors.control-border}"
    height: 1px
  dialog-backdrop:
    backgroundColor: "{colors.scrim}"
  badge-neutral:
    backgroundColor: "{colors.neutral-soft}"
    textColor: "{colors.on-surface-muted}"
    typography: "{typography.caption}"
    rounded: "{rounded.sm}"
    padding: "{spacing.sm}"
  badge-info:
    backgroundColor: "{colors.primary-soft}"
    textColor: "{colors.primary}"
    typography: "{typography.caption}"
    rounded: "{rounded.sm}"
    padding: "{spacing.sm}"
  badge-warning:
    backgroundColor: "{colors.warning-soft}"
    textColor: "{colors.warning}"
    typography: "{typography.caption}"
    rounded: "{rounded.sm}"
    padding: "{spacing.sm}"
  badge-success:
    backgroundColor: "{colors.success-soft}"
    textColor: "{colors.success}"
    typography: "{typography.caption}"
    rounded: "{rounded.sm}"
    padding: "{spacing.sm}"
  badge-danger:
    backgroundColor: "{colors.danger-soft}"
    textColor: "{colors.danger}"
    typography: "{typography.caption}"
    rounded: "{rounded.sm}"
    padding: "{spacing.sm}"
---

# SIGAP Ambulans — sistem desain

## Overview

SIGAP Ambulans dipakai empat kelompok orang dengan kebutuhan yang berbeda: masyarakat yang sedang panik dan memegang ponsel, operator Dinkes yang menyaring dan menugaskan pesanan seharian di layar lebar, petugas Faskes yang memperbarui ketersediaan bed dan dokter, serta sopir yang membaca layar sekilas di dalam kendaraan. Antarmuka harus terbaca dalam satu pandang dan tidak pernah menghalangi.

Arah visualnya adalah **buku catatan dispatch**: tenang, resmi, padat informasi. Informasi disusun sebagai baris-baris yang rapi dengan garis tipis dan angka waktu yang sejajar, bukan kumpulan kartu yang sama bentuknya. Satu hal dibuat menonjol, yaitu **linimasa pesanan** (waktu, status, pelaku, catatan); seluruh halaman lain dibuat sengaja diam agar linimasa dan data operasional yang terbaca lebih dulu.

Antarmuka ini bukan situs pemasaran. Tidak ada hero bergambar, ilustrasi dekoratif, atau kalimat penjualan. Kata-katanya faktual, bahasa Indonesia, huruf awal kapital saja pada awal kalimat.

## Colors

Palet sengaja kecil: netral dingin, satu warna aksen, dan empat warna pesan yang hanya muncul untuk menyatakan keadaan.

- **Canvas (#F3F5F7):** latar belakang area aplikasi. Hanya latar, tidak pernah dipakai untuk teks.
- **Surface (#FFFFFF) dan On-surface (#16212B):** permukaan isi (tabel, formulir, panel) dan teks utama. Kontras 16:1.
- **On-surface-muted (#566472):** teks sekunder, keterangan, label bantu. Kontras 6:1 di atas Surface.
- **Primary (#0B5C80):** biru petrol, satu-satunya warna aksen: tombol utama, tautan, item menu aktif, fokus. Hanya **satu tombol utama per layar**. Primary-soft dipakai sebagai latar item aktif dan lencana informasi.
- **Danger (#A8231B):** hanya untuk bahaya: tolak, batalkan, galat, pesanan gawat. Bukan hiasan.
- **Warning, Success, Neutral:** hanya sebagai pasangan lembut (teks pekat di atas latar lembut) pada lencana dan pesan. Tidak pernah sebagai latar tombol atau area besar.
- **Line (#DCE1E6)** untuk garis pemisah dan **Control-border (#7D8A97)** untuk batas isian dan kotak centang (kontras 3,5:1 sesuai WCAG untuk komponen antarmuka).

Warna di markup hanya lewat token ini (`bg-surface`, `text-on-surface-muted`, `border-line`, `bg-primary`, …). Palet bawaan Tailwind dimatikan, nilai heksadesimal dan nilai sembarang (`bg-[#…]`) dilarang.

## Typography

Satu keluarga huruf: **Public Sans** (dirancang untuk situs layanan publik), variabel, di-host sendiri lewat paket `@fontsource-variable/public-sans` sehingga tidak ada unduhan font dari internet saat aplikasi dibuka. Tidak ada huruf kedua, tidak ada font monospace.

- **Display** (28px, 600) hanya untuk angka besar pada ringkasan; selalu angka tabular (`tabular-nums`).
- **H1** (24px) satu per halaman; **H2** (18px) judul bagian; **H3** (16px) judul blok kecil. Tiga tingkat saja.
- **Body** 16px untuk isi, **Body-sm** 14px untuk sel tabel dan keterangan, **Label** 14px/500 untuk label isian dan tombol, **Caption** 13px untuk waktu dan keterangan kecil. Tidak ada teks isi di bawah 13px.
- Semua angka yang berderet (waktu, jumlah, kode pesanan, nomor telepon) memakai `tabular-nums`.
- Hanya dua bobot per layar: 400 dan 600 (label 500). Tidak ada huruf kapital semua (`uppercase`), tidak ada `tracking-*` lebar, tidak ada teks miring untuk penekanan, tidak ada satu kata berwarna di tengah judul.
- Panjang baris isi paling banyak 70 karakter (`max-w-prose`).

## Layout

Mobile-first. Titik henti: ponsel (bawaan), `md` 768px, `lg` 1024px. Skala jarak berbasis 4px; pakai skala numerik bawaan Tailwind (`p-4` = 16px, `gap-6` = 24px).

- **Kerangka aplikasi (`lg` ke atas):** rel menu kiri 232px berlatar Surface dengan garis kanan tipis, isi di kanan berlatar Canvas, lebar isi maksimum 1152px, rata kiri. Tidak ada bilah atas pada desktop. **Ponsel:** bilah atas 56px (logo, nama halaman, tombol menu), menu menjadi laci penuh lebar.
- **Halaman:** judul H1 di kiri, aksi utama di kanan (ponsel: di bawah judul). Di bawahnya blok-blok isi dipisahkan oleh jarak 24–32px dan garis tipis, bukan oleh kartu bertumpuk.
- **Ringkasan angka:** satu wadah berbatas dengan sel-sel yang dipisah garis tegak (2 kolom di ponsel, 4 di desktop). Bukan empat kartu terpisah.
- **Tabel dan daftar** adalah bentuk utama menyajikan data: wadah berbatas 1px, baris dipisah garis, tinggi baris minimal 44px, kolom sekunder disembunyikan di ponsel.
- **Formulir** satu kolom, lebar maksimum 36rem, label di atas isian, galat tepat di bawah isian.
- **Target sentuh** minimal 44px. Layar Buat Pesanan muat dalam satu alur dengan tombol kirim yang selalu terlihat di dasar layar ponsel.
- Rata kiri untuk semua teks. Rata tengah hanya untuk kartu masuk/daftar dan keadaan kosong.

## Elevation & Depth

Datar. Hierarki dibentuk oleh **garis 1px (`border-line`), jarak, dan perbedaan Canvas/Surface**, bukan bayangan. Panel, tabel, dan formulir tidak punya bayangan. Satu-satunya bayangan adalah untuk elemen yang melayang di atas isi (menu tarik, dialog konfirmasi): `0 8px 24px` hitam dengan opasitas 12%, ditambah latar `scrim` di belakang dialog. Fokus papan ketik selalu terlihat: cincin 2px `primary` dengan jarak 2px.

## Shapes

Sudut kecil dan konsisten: **4px** (lencana), **6px** (tombol, isian, item menu), **8px** (panel, tabel, dialog). Penuh (`rounded-full`) hanya untuk titik status pada linimasa. Tidak ada sudut 12px ke atas, tidak ada tombol berbentuk pil, tidak ada kombinasi sudut tajam dan membulat dalam satu tampilan.

## Components

Bentuk dan perilaku komponen didefinisikan di token `components` di atas; padanan Blade dan API-nya ada di `docs/KIT_UI.md`.

- **Tombol:** primer (Primary), sekunder (Surface dengan batas `control-border`), bahaya (Danger), dan hantu (tanpa latar). Tinggi 44px, label kata kerja (`Verifikasi`, `Tugaskan`, `Simpan`). Tombol tidak memakai ikon kecuali ikon itu memperjelas arti (mis. lokasi pada Gunakan lokasi saya).
- **Isian:** tinggi 44px, batas `control-border`, label 14px di atas, bantuan 13px di bawah, galat dengan teks Danger dan batas Danger; tidak mengandalkan warna saja (selalu ada teks galat).
- **Lencana status:** teks pendek di atas latar lembut, sudut 4px, diawali titik 6px berwarna yang sama. Lima nada: `netral`, `info`, `peringatan`, `sukses`, `bahaya`.
- **Linimasa pesanan:** garis tegak 1px; tiap langkah satu baris: waktu (tabular, 13px, kolom tetap), titik status, label status, pelaku dan peran, catatan. Langkah terakhir (keadaan sekarang) berhuruf 600; langkah yang belum terjadi tidak ditampilkan.
- **Menu rel:** item teks dengan ikon 20px di kiri; item aktif berlatar `primary-soft` dan teks `primary`. Lencana angka (pesanan menunggu) berupa angka polos bernada `peringatan`.
- **Panel:** hanya dipakai untuk blok yang benar-benar perlu dibingkai (formulir, aksi). Panel tidak berada di dalam panel.
- **Dialog konfirmasi:** judul kalimat biasa, satu kalimat penjelas, dua tombol: batal (hantu) dan aksi (bahaya bila merusak).

## Do's and Don'ts

- Do pakai satu tombol utama per layar dan beri tahu apa yang terjadi bila ditekan.
- Do tampilkan keadaan nyata: kosong ("Belum ada pesanan."), memuat, galat, dan data lama ("Data lama", "Belum diperbarui").
- Do pakai baris, tabel, dan garis tipis untuk menyusun data; pakai jarak untuk mengelompokkan.
- Do jaga kontras teks minimal 4,5:1 dan target sentuh minimal 44px.
- Don't pakai gradien, kaca buram (`backdrop-blur`), cahaya berwarna, atau bayangan berwarna.
- Don't pakai emoji atau ikon dekoratif di lingkaran berwarna; ikon hanya garis (Lucide) dan hanya bila menambah arti.
- Don't susun halaman sebagai kisi kartu fitur yang bentuknya sama, kartu di dalam kartu, atau deretan statistik berkartu terpisah.
- Don't pakai hero besar, judul raksasa, ilustrasi, foto stok, testimoni, angka pemasaran, atau kalimat bernada iklan.
- Don't beri animasi masuk, efek angkat/skala saat disorot, penghitung angka bergerak, atau animasi berulang. Hanya transisi warna 120ms pada hover/fokus dan animasi yang menjawab tindakan pengguna.
- Don't tambahkan warna aksen kedua, mode gelap, atau variasi radius baru tanpa mengubah dokumen ini.
- Don't ketik ulang label status, peran, atau jenis; ambil dari Enum (`->label()`).

## Iconography

Ikon Lucide (garis 1,75px, 16 atau 20px) dari paket `lucide-static`, disalin runner ke `resources/icons/` dan ditampilkan lewat komponen `x-ui.ikon`. Warna mengikuti teks sekitarnya (`currentColor`). Daftar ikon yang tersedia ada di `docs/KIT_UI.md`; ikon di luar daftar tidak boleh dipakai.

## Motion

Tidak ada gerak yang berjalan sendiri. Transisi `color`, `background-color`, dan `border-color` 120ms ease-out untuk hover dan fokus. Menu laci dan dialog muncul tanpa animasi atau dengan fade 120ms. Hormati `prefers-reduced-motion`.

## Writing

Bahasa Indonesia baku yang sederhana, aktif, huruf awal kapital hanya pada awal kalimat. Nama aksi sama di seluruh alur ("Verifikasi" di tombol menghasilkan pesan "Pesanan berhasil diverifikasi."). Galat menyebut apa yang salah dan apa yang bisa dilakukan, tanpa meminta maaf dan tanpa tanda seru. Keadaan kosong menyebut langkah berikutnya. Tidak ada tanda panah (→) di akhir tautan, tidak ada pemisah titik tengah (·) untuk menggabung keterangan, tidak ada label berhuruf kapital semua di atas judul.
