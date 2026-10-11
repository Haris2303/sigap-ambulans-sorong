# KIT UI — SIGAP Ambulans v2 (Material Design 3)

> Pasangan `DESIGN.md` (identitas visual, token, larangan gaya). Dokumen ini menjawab "bagaimana dibangun di kode": tumpukan, kelas yang tersedia, komponen Blade, pola halaman. Tujuan: **semua halaman terasa satu aplikasi**. Halaman hanya merakit komponen; gaya tidak pernah ditulis per halaman. Sesi agen otonom tidak boleh mengubah dokumen ini; sesi interaktif boleh, bersama `DESIGN.md`.
>
> **Arah dasbor Material (keputusan pemilik proyek, `docs/DESAIN_DASBOR_MATERIAL.md`) mengalahkan aturan Don't dan Layout lama.** Token dan aturan konsistensi sudah ada (D1), komponen dasar (D2), grafik (D3), dan kerangka aplikasi (D4: bilah atas kartu melayang, laci putih, latar `canvas`, jarak halaman, `x-ui.cari`). Dasbor Dinkes belum (D5) dan sapuan halaman lain menyusul (D6). Daftar yang belum dibangun ada di §10.
>
> **Material 3.** Token, aturan konsistensi, komponen, layout, dan halaman sudah Material 3. Nama token atau kelas lama ditolak oleh aturan `token-lama` di `agent/konsistensi.json` (pesannya memuat padanan baru). Catatan keputusan dan langkah migrasi ada di `docs/MIGRASI_M3.md`.

## 1. Tumpukan dan aset

| Hal | Ketentuan |
|---|---|
| CSS | Tailwind CSS 4.3 lewat Vite (`@tailwindcss/vite`). Token berasal dari `DESIGN.md`; `node agent/tokens.mjs` menghasilkan `resources/css/design-tokens.css` (jangan diubah dengan tangan). `resources/css/app.css` memuat dasar (latar, fokus, seleksi). CSS tambahan hanya di `resources/css/tambahan.css` (aturan di berkasnya; warna hanya `var(--color-…)`) |
| Font | Roboto Variable dari paket npm (`@fontsource-variable/roboto`), diimpor di `app.css`. Tidak ada font dari internet |
| Ikon | Lucide, disalin ke `resources/icons/*.svg` (daftar di §6), ditampilkan lewat `<x-ui.ikon>` |
| JS | Vite: `resources/js/app.js` (impor `sigap.js`) dan `resources/js/peta.js` (Leaflet 1.9.4 + Leaflet Routing Machine 3.2.12 dari npm). Tidak ada CDN, tidak ada `<script src>` di view mana pun |
| Pemuatan | Hanya `layouts.dasar` yang memuat aset: `@vite(['resources/css/app.css','resources/js/app.js'])`, dan `@vite('resources/js/peta.js')` bila `:peta="true"` |
| Build | `npm run build` harus lulus. Tes memakai `$this->withoutVite()` (di `Tests\TestCase`) sehingga tidak perlu manifest |

**Kelas yang ada** (selain utilitas Tailwind biasa untuk tata letak: `flex`, `grid`, `gap-*`, `p-*`, `m-*`, `w-*`, `size-*`, `hidden`, `md:`, `lg:`, `truncate`, dst. dengan skala numerik bawaan):
- Warna: `bg|text|border|outline|fill|stroke|divide-<token>` dengan token `primary`, `on-primary`, `primary-container`, `on-primary-container`, `secondary-container`, `on-secondary-container`, `error`, `on-error`, `error-container`, `on-error-container`, `success`, `success-container`, `on-success-container`, `warning`, `warning-container`, `on-warning-container`, `surface`, `surface-container-lowest`, `surface-container-low`, `surface-container`, `surface-container-high`, `surface-container-highest`, `on-surface`, `on-surface-variant`, `outline`, `outline-variant`, `inverse-surface`, `inverse-on-surface`, `inverse-primary`, `scrim`, serta token dasbor `canvas` (latar halaman) dan `chart-sky` (batang dan segmen grafik). Pengubah opasitas hanya untuk lapisan keadaan dan nonaktif: `/8`, `/10`, `/12`, `/38` (mis. `hover:bg-primary/8`, `disabled:text-on-surface/38`).
- Teks: satu kelas per gaya, mengatur ukuran, tinggi baris, jarak huruf, dan bobot: `text-display-l|m|s`, `text-headline-l|m|s`, `text-title-l|m|s`, `text-body-l|m|s`, `text-label-l|m|s`, serta gaya dasbor (bobot 500) `text-page-title` (28px), `text-card-title` (20px), `text-stat-hero` (56px), `text-stat-l` (32px), `text-stat-m` (28px). Bobot lain hanya `font-normal|font-medium`. Angka berderet: `tabular-nums`.
- Sudut: `rounded-xs` (4px), `rounded-sm` (8px), `rounded-md` (12px), `rounded-lg` (16px), `rounded-xl` (28px), `rounded-full`, serta `rounded-none`.
- Bayangan: `shadow-level-1`, `shadow-level-2`, `shadow-level-3` untuk lapisan yang melayang; level 0 adalah `shadow-none`. Bayangan kartu dasbor (warna `on-surface`): `shadow-card` (panel, kartu statistik putih, bilah atas), `shadow-card-raised` (kartu statistik utama), `shadow-nav-active` (item menu aktif).
- Ukuran: `min-h-touch`/`h-touch` (48px), `w-drawer` (280px), `max-w-content` (1152px), `max-w-prose`.
- Gerak: `transition-colors` dan `transition-opacity` memakai 150ms dengan easing standar M3 secara bawaan.
- Kelas di `tambahan.css`: `gulir-tipis` (bilah gulir tipis `outline-variant` untuk daftar yang bisa bergulir, mis. menu laci), `lapisan-keadaan` (lapisan hover 8%, fokus 10%, tekan 10% berwarna `currentColor` di bawah isi kontrol) dan `target-sentuh` (memperluas area sentuh tak terlihat menjadi minimal 48px).

**Tidak ada** (tidak menghasilkan CSS, dan dipindai): palet bawaan (`bg-red-500`, `text-gray-600`, `text-white`), `text-sm|xs|lg|xl…`, `font-bold|semibold`, `rounded-2xl+`, `shadow-xs…2xl`, `blur`, `tracking-*`, `uppercase`, `italic`, gradien, `animate-pulse|bounce|ping`, `hover:scale|translate`, nilai sembarang untuk `rounded` dan `shadow`. Boleh: `animate-spin` untuk indikator memuat.

## 2. Resep kelas (supaya hal yang sama selalu terlihat sama)

| Elemen | Kelas |
|---|---|
| Tombol (semua) | `lapisan-keadaan target-sentuh inline-flex min-h-10 items-center justify-center gap-2 rounded-full px-6 text-label-l transition-colors` (tombol dengan ikon di kiri: `pl-4 pr-6`; hantu: `px-3`; hanya ikon: `size-10 px-0`) |
| Tombol utama (filled) | `bg-primary text-on-primary disabled:bg-on-surface/12 disabled:text-on-surface/38` |
| Tombol tonal | `bg-secondary-container text-on-secondary-container` + nonaktif seperti utama |
| Tombol sekunder (outlined) | `border border-outline text-primary disabled:border-on-surface/12 disabled:text-on-surface/38` |
| Tombol hantu (text) | `text-primary disabled:text-on-surface/38` |
| Tombol bahaya (error) | `bg-error text-on-error` + nonaktif seperti utama |
| Tombol terbalik | `text-inverse-primary disabled:text-inverse-on-surface/38`, hanya untuk aksi di atas snackbar (bentuk dan padding seperti hantu) |
| Tombol on-primary | `bg-on-primary text-primary disabled:bg-on-surface/12 disabled:text-on-surface/38`, hanya di atas kartu statistik utama (latar `primary`) |
| Ukuran tombol | `md`: bawaan (40px, area sentuh 48px); `lg`: `min-h-12` menggantikan `min-h-10` (hanya Kirim pesanan di ponsel) |
| Isian / pilihan / area teks | `block w-full min-h-14 rounded-xs border border-outline bg-surface-container-lowest px-4 text-body-l text-on-surface placeholder:text-on-surface-variant hover:border-on-surface focus-visible:border-primary focus-visible:outline-1 focus-visible:-outline-offset-1 focus-visible:outline-primary disabled:border-on-surface/12 disabled:text-on-surface/38`; galat: `border-error hover:border-on-error-container focus-visible:border-error focus-visible:outline-error` |
| Label isian | `mb-1 block text-label-l text-on-surface-variant`; bantuan `mt-1 text-body-s text-on-surface-variant`; galat `mt-1 text-body-s text-error` |
| Panel (kartu) | `bg-surface-container-lowest rounded-lg shadow-card` (konstanta `Tampilan::KARTU`) + `p-6`; `flush`: `overflow-hidden` tanpa padding. Tanpa batas |
| Pembungkus tabel | `overflow-x-auto rounded-md border border-outline-variant bg-surface-container-lowest`; kepala `bg-surface-container-low text-label-l text-on-surface-variant`; sel `px-4 py-3 text-body-m`; garis antarbaris `divide-y divide-outline-variant`; semua baris: `[&>tr:hover]:bg-on-surface/8` |
| Lencana | `inline-flex min-h-6 items-center gap-1.5 rounded-full px-2.5 text-label-m` + titik `size-1.5 rounded-full bg-current`; nada `netral: bg-surface-container-highest text-on-surface-variant`, `info: bg-primary-container text-on-primary-container`, `peringatan: bg-warning-container text-on-warning-container`, `sukses: bg-success-container text-on-success-container`, `bahaya: bg-error-container text-on-error-container` |
| Chip ikon | `inline-flex shrink-0 items-center justify-center rounded-md` + `size-10` (ikon 22) atau `size-13` (ikon 26) + pasangan container nada (`Tampilan::kelasChip`) |
| Kartu statistik putih | `Tampilan::KARTU` + `flex items-center gap-4 p-5`; chip `lg`, angka `text-stat-l tabular-nums`, label `text-label-l`, keterangan `text-body-s text-on-surface-variant` |
| Kartu statistik utama | `rounded-lg bg-primary px-6 py-5 text-on-primary shadow-card-raised`; chip `size-11 rounded-md bg-on-primary/18` (ikon 22), judul `text-title-m`, angka `text-stat-hero tabular-nums`, keterangan `text-body-m text-primary-container` |
| Petak | `rounded-md bg-surface-container-low p-4`, tanpa bayangan; chip `md`, angka `text-stat-m tabular-nums`, label `text-body-m text-on-surface-variant` |
| Tab | wadah `gulir-tipis flex overflow-x-auto border-b border-surface-container-high`; tab `lapisan-keadaan target-sentuh flex min-h-11 shrink-0 items-center whitespace-nowrap border-b-3 px-4 text-label-l`; aktif `text-primary border-primary`, lainnya `text-on-surface-variant border-transparent` |
| Avatar | `inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-container text-label-l text-on-primary-container` |
| Teks dan lencana dalam satu sel | bungkus keduanya dengan `<span class="inline-flex items-center gap-2">…</span>` di dalam `<td>` (jangan menaruh `inline-flex` pada `<td>` itu sendiri) supaya sejajar vertikal |
| Warna titik/latar nada | `info: bg-primary`, `peringatan: bg-warning`, `sukses: bg-success`, `bahaya: bg-error`, `netral: bg-on-surface-variant`; teks nada: `text-primary`, `text-warning`, `text-success`, `text-error`, `text-on-surface` |
| Peringatan (banner) | `flex gap-3 rounded-md px-4 py-3 text-body-m` + warna container nada seperti lencana, ikon di kiri (`info`, `circle-check`, `triangle-alert`, `circle-x`) |
| Snackbar | `fixed bottom-4 left-4 right-4 z-50 flex items-center gap-2 rounded-xs bg-inverse-surface py-1 pl-4 pr-2 text-body-m text-inverse-on-surface shadow-level-3 md:left-auto md:w-96`; pesan `min-w-0 flex-1 py-2`; aksi "Tutup": tombol `terbalik` |
| Item menu laci | `lapisan-keadaan flex min-h-12 items-center gap-3.5 rounded-full px-4 text-label-l transition-colors`; aktif `bg-primary text-on-primary shadow-nav-active`, lainnya `text-on-surface-variant`; ikon 22px |
| Hitungan menu | `inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-error px-1.5 text-label-m text-on-error tabular-nums`; lonceng: `h-4.5 min-w-4.5 px-1 text-label-s` di sudut kanan atas ikon. Disembunyikan lewat atribut `hidden` saat 0 |
| Label grup menu | `px-4 pb-2 pt-5 text-label-m text-on-surface-variant` (pertama `pt-1`) |
| Pilihan baris (opsi-baris) | `lapisan-keadaan flex min-h-14 cursor-pointer items-start gap-3 rounded-md border border-outline p-4 has-[:checked]:border-secondary-container has-[:checked]:bg-secondary-container has-[:checked]:text-on-secondary-container` |
| Dialog | `m-auto w-full max-w-140 rounded-xl bg-surface-container-high p-6 text-on-surface shadow-level-3 backdrop:bg-scrim`; judul `text-headline-s`, isi `text-body-m text-on-surface-variant` |
| Tautan teks | `text-primary underline underline-offset-2 hover:text-on-primary-container` |
| Kotak centang / radio | `size-4.5 shrink-0 accent-primary` dalam baris `lapisan-keadaan -mx-3 flex min-h-12 items-center gap-3 rounded-md px-3` (radio pada `opsi-baris`: `size-5`) |

Catatan: di Tailwind 4 warna `border` bawaan adalah `currentColor`, jadi setiap `border` **harus** disertai warna token (`border-outline` untuk kontrol, `border-outline-variant` untuk pembatas). Fokus papan ketik global (cincin 2px `primary`, jarak 2px) berasal dari `app.css`; isian menggantinya dengan batas 2px (resep di atas).

## 3. Layout (`resources/views/components/layouts/`)

| Komponen | Dipakai oleh | Isi |
|---|---|---|
| `layouts.dasar` | dasar semua layout | `<html lang="id">`/`<head>`/`<body>`; judul `"{judul} - SIGAP Ambulans"`; `@vite` (lihat §1); `@stack('skrip')`; tautan "Lewati ke isi" untuk papan ketik. **Satu-satunya** tempat tag `<html>/<head>/<body>` dan `@vite` |
| `layouts.publik` | halaman tanpa login (landing, `/faskes`) | bilah atas kartu melayang (`Tampilan::KARTU`, tinggi 64px) di dalam `max-w-content` dengan `px-4 lg:px-7`: nama aplikasi di kiri; Info Faskes (hantu) dan Masuk (sekunder) di kanan, atau "Ke dasbor" bila sudah masuk; kaki halaman satu baris (nama Dinkes) |
| `layouts.auth` | login, register | kolom tunggal rata kiri, lebar maks 24rem, nama aplikasi di atas, isi dalam `panel`, tanpa panel dekoratif |
| `layouts.app` | semua halaman setelah login | **Laci** (`data-menu-rel`): putih (`bg-surface-container-lowest`), `lg+` tetap `w-drawer` (280px) `sticky top-0 h-screen` dengan tepi kanan 1px `surface-container-high`; di bawah `lg` laci modal `w-80` (maks. lebar layar dikurangi 56px) dengan `rounded-r-lg`, `shadow-level-1`, dan scrim `data-menu-scrim`, ditutup dengan tombol Tutup, klik scrim, atau Escape (`sigap.js`). Laci itu sendiri `overflow-y-auto gulir-tipis`; menu tidak punya bilah gulir lagi, sehingga hanya ada satu bilah vertikal per halaman. Kepala: tanda merek 40px (`primary`, `rounded-md`, ikon `plus`) dan nama aplikasi `text-title-l`. Menu dari `config('sigap.menu.<peran>')` dengan label grup (kunci `grup`), item pil 48px, hitungan dari View Composer (tanpa query di Blade). Dasar laci: blok pengguna (`bg-surface-container-low rounded-lg`) berisi avatar, nama, peran, dan tombol keluar ikon 20px (form POST ber-CSRF, bukan tautan). **Bilah atas** (`data-bilah-atas`): kartu melayang 64px (`Tampilan::KARTU`) di dalam pembungkus `sticky top-0 bg-canvas` di semua ukuran layar: tombol menu (hanya di bawah `lg`), kolom cari `x-ui.cari` (maks. 460px di `lg`; hanya untuk peran di `config('sigap.bilah_atas.cari')`), lonceng pesanan menunggu verifikasi (hanya Dinkes, `config('sigap.bilah_atas.lonceng')`; tautan ke daftar pesanan tab "Perlu tindakan"; `aria-label` "Pesanan menunggu verifikasi, {n}" diperbarui oleh `sigap.js`), dan avatar (di `lg` disertai nama dan peran dua baris). **Isi**: `min-w-0`, padding `px-4 lg:px-7`, `max-w-content`. Latar halaman `canvas` (dari `app.css`) |

Semua layout menerima `judul` dan menampilkan `<x-ui.flash />` di atas isi. Halaman **harus** diawali `<x-layouts.app|publik|auth …>`.

## 4. Komponen (`resources/views/components/ui/…`, dipanggil `<x-ui.nama>`)

| Komponen | Props / slot | Catatan |
|---|---|---|
| `judul-halaman` | `judul`, `deskripsi?`; slot `aksi` | satu per halaman. Judul `text-headline-s` (24px); keterangan di bawahnya `text-body-l text-on-surface-variant` (16px), sehingga keduanya jelas berbeda. Aksi utama di kanan (ponsel di bawah judul) |
| `bagian` | `judul`, `deskripsi?`; slot isi, `aksi?` | blok isi dengan judul `text-title-l`, keterangan `text-body-m text-on-surface-variant`, dan jarak `mt-8` antar bagian; **bukan** kartu |
| `panel` | `judul?`, `flush?`; slot, `kaki?` | kartu putih berbayang `shadow-card`, sudut 16px, tanpa batas (formulir, aksi, tabel, grafik). Judul `text-title-m`. `flush`: tanpa padding, untuk tabel di dalam kartu. Kartu berbayang tidak boleh di dalam kartu berbayang |
| `stat` | `tone` = `primary\|warning\|success\|error` (bawaan `primary`), `value`, `label`, `hint?`, `icon`, `featured?`, `href?`; slot `aksi` (hanya `featured`) | kartu statistik. Putih: chip ikon `lg`, angka, label, keterangan; `href` menjadikan hanya label sebuah tautan (kartu tidak dapat diklik seluruhnya). Utama (`featured`): latar `primary`, `tone` diabaikan, tombol `on-primary` lewat slot `aksi`. Atribut (mis. `data-ringkasan`) diteruskan ke akar; angka bertanda `data-nilai` untuk pembaruan otomatis |
| `icon-chip` | `tone` = `primary\|warning\|success\|error`, `icon`, `size` = `md\|lg` | chip ikon pembawa makna (40px, ikon 22; 52px, ikon 26); sudut 12px; nada dan ukuran di luar daftar melempar `InvalidArgumentException`. Selalu disertai teks pendamping |
| `tile` | `tone`, `icon`, `value`, `label` | petak tonal tanpa bayangan di dalam kartu (ketersediaan): chip `md`, angka `text-stat-m`, label |
| `tabs` dan `tab` | `tabs`: `label?` (nama navigasi, bawaan "Tab"); `tab`: `href`, `aktif?` | tab primer M3 berupa tautan ke URL yang sama dengan `?tab=...`, tanpa JavaScript; tab aktif bertanda `aria-current="page"`; baris tab bergulir horizontal di ponsel |
| `avatar` | `nama` | lingkaran 40px berinisial dua kata pertama (`Tampilan::inisial()`, "?" bila kosong); dekoratif (`aria-hidden`), nama selalu ada di sebelahnya |
| `cari` | `aksi` (URL), `label`, `nama?` (bawaan `q`), `nilai?` (bawaan query aktif), `placeholder?`, `id?` | kolom cari berbentuk pil 48px (`bg-surface-container`): form `GET` berperan `search` dengan ikon `search`, `<label class="sr-only">`, dan `<input type="search">`; `min-w-0 flex-1` agar tidak meluber di ponsel; cincin fokus pada pembungkusnya. Pemakaian: bilah atas dan, nanti, daftar pesanan (`?q=`, D6) |
| `tombol` | `nada` = `utama\|tonal\|sekunder\|hantu\|bahaya\|terbalik\|on-primary`, `ukuran` = `md\|lg`, `href?`, `tipe?`, `ikon?`, `ikon-ukuran?`, `konfirmasi?`, `blok?` | `href` menjadi `<a>`; selain itu `<button>`. Tanpa slot dan dengan `ikon` menjadi tombol ikon (wajib `aria-label`). `ikon-ukuran` menimpa ukuran ikon (18 dalam tombol bertulisan, 24 pada tombol ikon), mis. 20 pada tombol keluar. `konfirmasi="…"` membuka `ui.dialog` lewat `sigap.js` |
| `ikon` | `nama`, `ukuran` = `18\|20\|22\|24\|26` (bawaan 24; 18 dalam tombol, 20 tombol keluar, 22 item menu dan chip md, 26 chip lg) | membaca `resources/icons/{nama}.svg`, menyisipkan `class`, `stroke-width="1.75"`, `aria-hidden="true"`; melempar `InvalidArgumentException` bila ikon tidak ada di daftar |
| `lencana` | `nada` = `netral\|info\|peringatan\|sukses\|bahaya` | teks pendek berbentuk pil (tinggi 24px, padding 10px); selalu dengan titik |
| `lencana-status` | `status` (enum `PesananStatus`) | memakai `->label()` dan `->nada()`; **satu-satunya** cara menampilkan status. Label Enum berhuruf kapital awal kalimat saja ("Menunggu verifikasi", "Dalam perjalanan"), kecuali istilah baku Faskes, Dinkes, dan BUMN (dijaga `EnumTest`) |
| `flash` | — | membaca `session('sukses')` (tampil lewat `snackbar`), `session('galat')` dan ringkasan `$errors` (tampil sebagai `peringatan` bahaya di atas isi) |
| `snackbar` | `tetap` (bawaan `true`; `false` hanya untuk katalog); slot pesan | pesan singkat hasil tindakan di dasar layar, dengan tombol Tutup (`data-snackbar-tutup`, ditangani `sigap.js`). Tanpa batas waktu otomatis |
| `peringatan` | `nada` = `info\|peringatan\|sukses\|bahaya` | pesan sebaris di halaman (banner) |
| `dialog` | `id`, `judul`, `pesan`, `aksi` (teks), `nada?` | elemen `<dialog>` bawaan; Level 3, latar `bg-scrim`; satu instance dipakai bersama oleh semua `data-konfirmasi`; dua tombol sejajar kanan (batal hantu; aksi hantu, atau bahaya bila merusak) |
| `tabel` | slot `kepala`, slot isi | pembungkus tabel (resep §2) |
| `kosong` | `pesan`; slot `aksi` | keadaan kosong: "Belum ada …", rata kiri, dengan satu aksi bila ada |
| `isian` | `nama`, `label`, `tipe`, `nilai?`, `bantuan?`, `wajib?` | label di atas (tidak mengapung), pesan galat per kolom (`$errors`), `old()` otomatis |
| `pilihan` | `nama`, `label`, `opsi` (nilai→label), `nilai?`, `kosong?` | pembungkus `<select>`; opsi dari `Enum::opsi()` |
| `area-teks` | seperti `isian` + `baris` | |
| `centang` | `nama`, `label`, `nilai?` | |
| `opsi-baris` | `nama`, `nilai`, `judul`, `deskripsi?`, `terpilih?` | radio berbentuk baris besar berbingkai (jenis layanan, tingkat darurat); terpilih: `bg-secondary-container`; target sentuh penuh |
| `ringkasan` + `ringkasan-sel` | `label`, `nilai`, `nada?`, `keterangan?` | satu wadah berbingkai dengan sel dipisah garis (2 kolom ponsel, 4 desktop); angka `text-display-s tabular-nums`. Menggantikan "kartu statistik" |
| `rincian` | slot daftar `rincian-baris` (`label`, slot nilai) | daftar definisi `<dl>`: label `text-on-surface-variant` di kiri, nilai di kanan, garis antarbaris. Dipakai di semua halaman detail |
| `waktu` | `nilai`, `relatif?` | zona Asia/Jayapura, `d M Y H:i`; `relatif` menambah "x menit lalu"; `tabular-nums` |
| `grafik-batang` | `data` (label→angka), `nada?` | batang CSS murni; satu-satunya tempat boleh `style="width:…"`. Kolom label `w-24 shrink-0 whitespace-nowrap text-body-s text-on-surface-variant` (tidak pecah baris), jalur `bg-surface-container-highest`, nilai `w-10 shrink-0 text-right tabular-nums` |
| `chart.bar` | `labels` (string "Sen 05/10" yang dipecah di spasi pertama menjadi baris hari dan tanggal, atau pasangan `['Sen', '05/10']`), `values` (bilangan bulat, jumlahnya sama dengan `labels`), `highlight?` (indeks batang berwarna `primary`; lainnya `chart-sky`), `title`, `unit?` | grafik batang SVG sisi server (`<x-ui.chart.bar>`), viewBox `0 0 640 346`, dibungkus `overflow-x-auto` dengan lebar minimum 480px. Sumbu maksimum bilangan genap terkecil minimal 2; nilai 0 digambar sebagai batang rintisan; setiap batang berlabel nilai; `role="img"` dengan `aria-label` "{title}: Sen 05/10 1 pesanan, ...". Geometri di `App\Support\Chart\BarGeometry`. Menggantikan `grafik-batang` (dihapus di D7) |
| `chart.donut` | `segments` (daftar `['label', 'value', 'color']`; `color` = nama token `primary\|success\|error\|warning\|outline\|chart-sky`), `title`, `center-label?` | grafik donat SVG sisi server (`<x-ui.chart.donut>`), viewBox `0 0 160 160`: segmen berurutan nilai terbesar dulu (seri sama mengikuti urutan masukan), celah 3px, total di tengah (`text-stat-l`), legenda teks berisi titik, label, nilai, dan persentase (hanya nilai lebih dari 0). Total 0 menggambar cincin `surface-container-highest` dan tanpa legenda. `aria-label` "{title}: 5 selesai, 2 dibatalkan, ...". Geometri di `App\Support\Chart\DonutGeometry`; warna lewat `App\Support\Chart\Warna` |
| `paginasi` | `data` (paginator) | membuat markup sendiri dari `$data->linkCollection()` (tampilan paginasi bawaan Laravel memakai palet Tailwind lama dan **tidak boleh** dipakai); bahasa Indonesia ("Sebelumnya", "Berikutnya", "Menampilkan 1–20 dari 134"); halaman aktif `bg-secondary-container` |
| `linimasa` | `log` (koleksi `PesananLog`) | sesuai `DESIGN.md` bagian Components: kolom waktu tetap, titik, label status, pelaku/peran, catatan; langkah terakhir `font-medium` |
| `ketersediaan` | `faskes` | bed IGD x/y, bed rawat inap x/y, dokter umum, dokter spesialis, ambulans tersedia, "diperbarui …" / "Belum diperbarui" / "Data lama" (lencana `peringatan`) |
| `peta.pemilih` | nama kolom lat/lng/alamat | peta Leaflet + tombol "Gunakan lokasi saya" + cari alamat (Nominatim) + klik peta |
| `peta.pelacak` | `lat`, `lng`, `urlPosisi?` | penanda jemput + posisi sopir (polling) + rute |
| `peta.faskes` | `daftar` | penanda Faskes berkoordinat |

Aturan komponen: dibangun dari resep §2; tidak ada kelas di luar §1; variasi baru berarti prop baru pada komponen yang ada, bukan komponen kedua. Setiap kontrol interaktif memakai `lapisan-keadaan` dan area sentuh 48px.

## 5. Pola halaman

- **Daftar**: `judul-halaman` → form filter satu baris (`isian`/`pilihan` + `tombol sekunder "Cari"` + tautan "Atur ulang"; di ponsel menumpuk) → `tabel` → `paginasi`. Tanpa data: `kosong`. Ponsel: kolom sekunder `hidden md:table-cell`; baris dapat diklik penuh lewat kolom pertama berupa tautan.
- **Formulir**: satu `panel` lebar maksimum `max-w-xl`, kolom vertikal `space-y-5`; di dasar: `tombol utama "Simpan"` + `tombol hantu "Batal"`.
- **Detail**: dua kolom pada `lg` (kiri: `rincian` + `linimasa`; kanan: `panel` aksi), satu kolom di ponsel. Aksi hanya yang valid untuk status dan peran saat ini (`PesananStatus::transisiTerbuka()` + peran).
- **Aksi berbahaya** (tolak, batalkan, lepas): `tombol nada="bahaya"` dengan `konfirmasi`; alasan lewat `area-teks` pada form kecil bila aturan status menuntutnya.
- **Dasbor**: `ringkasan` → `bagian` demi `bagian` (tabel/daftar). Tidak ada kisi kartu.
- **Landing (`beranda`)**: rata kiri, `max-w-prose`. H1 faktual: "Pesan ambulans Dinas Kesehatan Kota Sorong." Satu paragraf (apa yang dilayani, siapa yang memverifikasi). `tombol utama "Pesan ambulans"` + `tombol sekunder "Info Faskes"`. Bagian "Cara kerja": **daftar bernomor** empat langkah (pesan, Dinkes memverifikasi, ambulans dan sopir ditugaskan, pantau perjalanan) karena urutannya nyata. Tanpa gambar, ilustrasi, hero, atau kisi fitur.
- **Buat Pesanan (ponsel dulu)**: satu kolom; urutan: jenis layanan (`opsi-baris`), tingkat darurat, lokasi jemput (tombol besar "Gunakan lokasi saya" + peta + alamat), Faskes tujuan, data pasien, catatan; tombol "Tinjau pesanan" tetap terlihat di dasar layar ponsel (`sticky bottom-0 bg-surface-container p-4 shadow-level-1`).
- **Judul** = kalimat biasa (huruf awal kapital saja). Satu `judul-halaman` per halaman.

## 6. Daftar ikon (satu-satunya yang tersedia)

`ambulance`, `hospital`, `house`, `layout-dashboard`, `clipboard-list`, `list-checks`, `users`, `user`, `user-check`, `building-2`, `stethoscope`, `bed-double`, `file-text`, `download`, `settings`, `log-out`, `menu`, `x`, `plus`, `pencil`, `search`, `check`, `circle-check`, `circle-x`, `ban`, `triangle-alert`, `info`, `clock`, `phone`, `map-pin`, `navigation`, `crosshair`, `refresh-cw`, `bell`, `chevron-down`, `chevron-right`, `arrow-left`.
Nama ikon `ambulance` adalah satu-satunya literal yang dikecualikan dari aturan `istilah-baku` (hanya sebagai string bertanda kutip utuh).
Ikon hanya di menu laci, tombol yang butuh penjelas visual (mis. lokasi), peringatan, dan keadaan kosong tertentu. Tidak di dalam lingkaran berwarna, kecuali chip ikon pembawa makna (`x-ui.icon-chip`); tidak di samping judul.

## 7. JavaScript

`resources/js/sigap.js` (diimpor `app.js`): `Sigap.polling(url, detik, fn)`, `Sigap.notifikasi(judul, isi)`, `Sigap.lokasiSaya(fn)`, penanganan `data-konfirmasi` (membuka `ui.dialog`), menu laci, penutup snackbar, dan angka hidup Dinkes (`[data-ringkasan]`, `[data-lencana]`, label `[data-lonceng]`). Halaman boleh memakai `@push('skrip')` ≤ 20 baris yang **memanggil** helper itu; URL selalu dari `route()` dan dioper lewat atribut `data-*`; tidak ada `fetch` ke URL ketikan tangan; tidak ada pustaka JS lain.
`resources/js/peta.js`: `import L from 'leaflet'; import 'leaflet/dist/leaflet.css'; import 'leaflet-routing-machine'; import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';` dan perbaikan ikon penanda bawaan Leaflet untuk bundler (`import iconUrl from 'leaflet/dist/images/marker-icon.png'` … lalu `L.Icon.Default.mergeOptions({ iconUrl, iconRetinaUrl, shadowUrl })`). Ubin peta OpenStreetMap, pencarian Nominatim, dan rute OSRM demo adalah satu-satunya permintaan jaringan eksternal; catat di `ASUMSI_DAN_KEPUTUSAN.md`.

## 8. Katalog gaya

`GET /_ui` (hanya environment `local` dan `testing`, view `resources/views/dev/katalog-ui.blade.php`) menampilkan setiap komponen dan layout dengan data contoh, termasuk keadaan galat, kosong, nonaktif, dan data lama. Dipakai tes untuk memastikan semua komponen dapat dirender. Katalog juga mengikuti larangan §9.

## 9. Larangan di halaman (diperiksa otomatis oleh `agent/konsistensi.json`)

Pada berkas di bawah `resources/views/` (kecuali `pdf/`): kelas warna/gaya di luar §1 (palet bawaan, white/black, `bg-[#…]`, gradien, blur, bayangan dan sudut di luar token (bayangan yang sah: `shadow-none`, `shadow-level-1..3`, `shadow-card`, `shadow-card-raised`, `shadow-nav-active`) atau dengan nilai sembarang, `tracking-*`, `uppercase`, `italic`, `font-bold`, `text-sm|lg|…`, `animate-pulse|bounce|ping`, `hover:scale|translate`); nama token dan kelas pra-Material 3 (aturan `token-lama`, juga pada `resources/js` dan `app/Support`); sintaks Tailwind v3; emoji; tanda panah, titik tengah, dan em dash berspasi; tag `<script src>`/`<link href=http>`/CDN; URL ketikan tangan. Pada halaman di luar `components/`: elemen mentah `<button>`, `<table>`, `<select>`, `<textarea>`, `<input>` (kecuali `type="hidden"`); `<style>`, atribut `style=`, `<html>/<head>/<body>`; halaman yang tidak diawali `<x-layouts.*>` (fragmen `@include` diawali `_` dikecualikan). Pada `resources/css/tambahan.css`: warna hanya `var(--color-…)`, `color-mix()` hanya dengan `var(--color-…)`, `box-shadow` hanya `none`, `var(--shadow-level-1..3)`, `var(--shadow-card)`, `var(--shadow-card-raised)`, atau `var(--shadow-nav-active)`, tanpa gradien, `@keyframes`, atau `animation`. Seluruh `pdf/` berdiri sendiri (dirender dompdf) dan dikecualikan.

## 10. Komponen dasbor (rencana)

Komponen dasar (D2), grafik (D3), dan kerangka aplikasi (D4) sudah dibangun dan didokumentasikan di §2, §3, dan §4; contoh di bawah tabel ini. Yang tersisa mengikuti `docs/DESAIN_DASBOR_MATERIAL.md` bagian 6, dengan status **belum diimplementasikan**; susunan lengkap ada di sana. Setelah dibangun, barisnya dipindah ke §4.

| Komponen | Props / ringkasan | Langkah | Status |
|---|---|---|---|
| Dasbor Dinkes | susunan di `docs/DESAIN_DASBOR_MATERIAL.md` bagian 6, data dari `App\Services\RingkasanDasbor` | D5 | belum diimplementasikan |

Pemakaian warna chip menurut nada: primary = `primary-container` / `on-primary-container`; warning = `warning-container` / `on-warning-container`; success = `success-container` / `on-success-container`; error = `error-container` / `on-error-container`. Warna segmen donat tetap per status Enum lewat `PesananStatus::warnaGrafik()` (selesai `success`, dibatalkan `outline`, ditolak `error`, menunggu verifikasi `warning`, terverifikasi `primary`, ditugaskan, diterima, dan dalam perjalanan `chart-sky`); status baru di Enum wajib diberi warna dengan prinsip yang sama (dijaga `WarnaTest`) dan dicatat di sini.

### Contoh pemakaian komponen dasar dasbor

```blade
{{-- Kartu statistik: utama (satu per baris) dan putih --}}
<x-ui.stat :featured="true" icon="clock" :value="$menunggu" label="Menunggu verifikasi" hint="pesanan baru" data-ringkasan="pesanan_menunggu">
    <x-slot:aksi>
        <x-ui.tombol nada="on-primary" :href="route('admin.pesanan.index', ['tab' => 'perlu-tindakan'])">Verifikasi pesanan</x-ui.tombol>
    </x-slot:aksi>
</x-ui.stat>
<x-ui.stat tone="success" icon="circle-check" :value="$selesai" label="Selesai hari ini" hint="pesanan tuntas" />

{{-- Kartu ketersediaan: petak di dalam panel --}}
<x-ui.panel judul="Ketersediaan">
    <div class="grid grid-cols-2 gap-4">
        <x-ui.tile tone="primary" icon="ambulance" :value="$ambulans" label="Ambulans tersedia" />
        <x-ui.tile tone="error" icon="bed-double" :value="$bedIgd" label="Bed IGD tersedia" />
    </div>
</x-ui.panel>

{{-- Tab filter di dalam kartu tanpa padding --}}
<x-ui.panel :flush="true">
    <x-ui.tabs label="Filter pesanan">
        <x-ui.tab :href="route('admin.dashboard', ['tab' => 'semua'])" :aktif="$tab === 'semua'">Semua</x-ui.tab>
        <x-ui.tab :href="route('admin.dashboard', ['tab' => 'perlu-tindakan'])" :aktif="$tab === 'perlu-tindakan'">Perlu tindakan</x-ui.tab>
    </x-ui.tabs>
</x-ui.panel>

{{-- Avatar selalu bersama nama --}}
<x-ui.avatar :nama="$pengguna->name" /> <span>{{ $pengguna->name }}</span>
```

### Contoh pemakaian grafik

```blade
<x-ui.panel>
    <h2 class="text-card-title">Pesanan 7 hari terakhir</h2>
    <x-ui.chart.bar :labels="$labelHari" :values="$nilaiHari" :highlight="6" title="Grafik batang pesanan per hari" unit="pesanan" />
</x-ui.panel>

<x-ui.panel>
    <h2 class="text-card-title">Status pesanan</h2>
    {{-- $segmen: [['label' => $status->label(), 'value' => $jumlah, 'color' => $status->warnaGrafik()], ...] dalam urutan Enum --}}
    <x-ui.chart.donut :segments="$segmen" center-label="pesanan" title="Diagram cincin status 10 pesanan terbaru" />
</x-ui.panel>
```

Geometri murni (`BarGeometry::buat($values, $highlight)`, `DonutGeometry::buat($segments)`) diuji unit terhadap nilai pratinjau di `docs/referensi/` (batang 1,0,0,0,0,0,1 dan donat 5,2,1,1,1). Penyimpangan yang disengaja dari teks dokumen arah: radius sudut batang mengecil bila batang lebih pendek dari 8px, dan celah donat mengecil menjadi separuh bagian bagi segmen yang lebih kecil dari 6px, supaya tidak ada koordinat terbalik dan segmen kecil tetap terlihat.
