# KIT UI — SIGAP Ambulans v2 (Material Design 3)

> Pasangan `DESIGN.md` (identitas visual, token, larangan gaya). Dokumen ini menjawab "bagaimana dibangun di kode": tumpukan, kelas yang tersedia, komponen Blade, pola halaman. Tujuan: **semua halaman terasa satu aplikasi**. Halaman hanya merakit komponen; gaya tidak pernah ditulis per halaman. Sesi agen otonom tidak boleh mengubah dokumen ini; sesi interaktif boleh, bersama `DESIGN.md`.
>
> **Status migrasi ke Material 3.** Token, aturan konsistensi, dan dokumen ini sudah M3. Komponen Blade dan halaman yang belum diganti masih memakai nama kelas lama sampai langkah penggantiannya selesai (urutan dan centang di `docs/PROGRESS.md`; pemetaan nama lama ke baru di §10). Bagian §2 sampai §5 adalah kontrak target untuk komponen yang sedang diganti.

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
- Warna: `bg|text|border|outline|fill|stroke|divide-<token>` dengan token `primary`, `on-primary`, `primary-container`, `on-primary-container`, `secondary-container`, `on-secondary-container`, `error`, `on-error`, `error-container`, `on-error-container`, `success`, `success-container`, `on-success-container`, `warning`, `warning-container`, `on-warning-container`, `surface`, `surface-container-lowest`, `surface-container-low`, `surface-container`, `surface-container-high`, `surface-container-highest`, `on-surface`, `on-surface-variant`, `outline`, `outline-variant`, `inverse-surface`, `inverse-on-surface`, `inverse-primary`, `scrim`. Pengubah opasitas hanya untuk lapisan keadaan dan nonaktif: `/8`, `/10`, `/12`, `/38` (mis. `hover:bg-primary/8`, `disabled:text-on-surface/38`).
- Teks: satu kelas per gaya, mengatur ukuran, tinggi baris, jarak huruf, dan bobot: `text-display-l|m|s`, `text-headline-l|m|s`, `text-title-l|m|s`, `text-body-l|m|s`, `text-label-l|m|s`. Bobot lain hanya `font-normal|font-medium`. Angka berderet: `tabular-nums`.
- Sudut: `rounded-xs` (4px), `rounded-sm` (8px), `rounded-md` (12px), `rounded-lg` (16px), `rounded-xl` (28px), `rounded-full`, serta `rounded-none`.
- Bayangan: `shadow-level-1`, `shadow-level-2`, `shadow-level-3`; level 0 adalah `shadow-none`.
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
| Ukuran tombol | `md`: bawaan (40px, area sentuh 48px); `lg`: `min-h-12` menggantikan `min-h-10` (hanya Kirim pesanan di ponsel) |
| Isian / pilihan / area teks | `block w-full min-h-14 rounded-xs border border-outline bg-surface-container-lowest px-4 text-body-l text-on-surface placeholder:text-on-surface-variant hover:border-on-surface focus-visible:border-primary focus-visible:outline-1 focus-visible:-outline-offset-1 focus-visible:outline-primary disabled:border-on-surface/12 disabled:text-on-surface/38`; galat: `border-error hover:border-on-error-container focus-visible:border-error focus-visible:outline-error` |
| Label isian | `mb-1 block text-label-l text-on-surface-variant`; bantuan `mt-1 text-body-s text-on-surface-variant`; galat `mt-1 text-body-s text-error` |
| Panel | `rounded-md border border-outline-variant bg-surface-container-lowest p-4 md:p-6` |
| Pembungkus tabel | `overflow-x-auto rounded-md border border-outline-variant bg-surface-container-lowest`; kepala `bg-surface-container-low text-label-l text-on-surface-variant`; sel `px-4 py-3 text-body-m`; garis antarbaris `divide-y divide-outline-variant`; semua baris: `[&>tr:hover]:bg-on-surface/8` |
| Lencana | `inline-flex min-h-6 items-center gap-1.5 rounded-sm px-2 text-label-m` + titik `size-1.5 rounded-full bg-current`; nada `netral: bg-surface-container-highest text-on-surface-variant`, `info: bg-primary-container text-on-primary-container`, `peringatan: bg-warning-container text-on-warning-container`, `sukses: bg-success-container text-on-success-container`, `bahaya: bg-error-container text-on-error-container` |
| Teks dan lencana dalam satu sel | bungkus keduanya dengan `<span class="inline-flex items-center gap-2">…</span>` di dalam `<td>` (jangan menaruh `inline-flex` pada `<td>` itu sendiri) supaya sejajar vertikal |
| Warna titik/latar nada | `info: bg-primary`, `peringatan: bg-warning`, `sukses: bg-success`, `bahaya: bg-error`, `netral: bg-on-surface-variant`; teks nada: `text-primary`, `text-warning`, `text-success`, `text-error`, `text-on-surface` |
| Peringatan (banner) | `flex gap-3 rounded-md px-4 py-3 text-body-m` + warna container nada seperti lencana, ikon di kiri (`info`, `circle-check`, `triangle-alert`, `circle-x`) |
| Snackbar | `fixed bottom-4 left-4 right-4 z-50 flex items-center gap-2 rounded-xs bg-inverse-surface py-1 pl-4 pr-2 text-body-m text-inverse-on-surface shadow-level-3 md:left-auto md:w-96`; pesan `min-w-0 flex-1 py-2`; aksi "Tutup": tombol `terbalik` |
| Item menu laci | `lapisan-keadaan flex min-h-14 items-center gap-3 rounded-full px-4 text-label-l text-on-surface-variant`; aktif `bg-secondary-container text-on-secondary-container` |
| Hitungan menu | `inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-error px-1 text-label-s text-on-error tabular-nums` |
| Pilihan baris (opsi-baris) | `lapisan-keadaan flex min-h-14 cursor-pointer items-start gap-3 rounded-md border border-outline p-4 has-[:checked]:border-secondary-container has-[:checked]:bg-secondary-container has-[:checked]:text-on-secondary-container` |
| Dialog | `m-auto w-full max-w-140 rounded-xl bg-surface-container-high p-6 text-on-surface shadow-level-3 backdrop:bg-scrim`; judul `text-headline-s`, isi `text-body-m text-on-surface-variant` |
| Tautan teks | `text-primary underline underline-offset-2 hover:text-on-primary-container` |
| Kotak centang / radio | `size-4.5 shrink-0 accent-primary` dalam baris `lapisan-keadaan -mx-3 flex min-h-12 items-center gap-3 rounded-md px-3` (radio pada `opsi-baris`: `size-5`) |

Catatan: di Tailwind 4 warna `border` bawaan adalah `currentColor`, jadi setiap `border` **harus** disertai warna token (`border-outline` untuk kontrol, `border-outline-variant` untuk pembatas). Fokus papan ketik global (cincin 2px `primary`, jarak 2px) berasal dari `app.css`; isian menggantinya dengan batas 2px (resep di atas).

## 3. Layout (`resources/views/components/layouts/`)

| Komponen | Dipakai oleh | Isi |
|---|---|---|
| `layouts.dasar` | dasar semua layout | `<html lang="id">`/`<head>`/`<body>`; judul `"{judul} - SIGAP Ambulans"`; `@vite` (lihat §1); `@stack('skrip')`; tautan "Lewati ke isi" untuk papan ketik. **Satu-satunya** tempat tag `<html>/<head>/<body>` dan `@vite` |
| `layouts.publik` | halaman tanpa login (landing, `/faskes`) | bilah atas sederhana (`bg-surface-container`, tinggi 64px): nama aplikasi di kiri; Info Faskes (hantu) dan Masuk (sekunder) di kanan, atau "Ke dasbor" bila sudah masuk; kaki halaman satu baris (nama Dinkes) |
| `layouts.auth` | login, register | kolom tunggal rata kiri, lebar maks 24rem, nama aplikasi di atas, isi dalam `panel`, tanpa panel dekoratif |
| `layouts.app` | semua halaman setelah login | `lg+`: laci navigasi tetap `w-drawer` berlatar `surface-container-low` (nama aplikasi, daftar menu dari `config('sigap.menu.<peran>')`, di dasar: nama pengguna + label peran + Keluar); di bawah `lg`: bilah atas 64px (`data-bilah-atas`, `surface-container`, tombol menu di kiri lalu judul halaman; mendapat `shadow-level-1` saat isi bergulir) dan laci menjadi laci modal `w-80` (maks. lebar layar dikurangi 56px) dengan `rounded-r-lg`, `shadow-level-1`, serta scrim `data-menu-scrim` (`bg-scrim`). Laci ditutup dengan tombol Tutup, klik scrim, atau Escape (`sigap.js`). Isi: `max-w-content`, padding `p-4 md:p-8`. Hitungan "Pesanan" untuk Dinkes dari View Composer (tanpa query di Blade); sembunyi lewat atribut `hidden` (bukan kelas) saat 0 |

Semua layout menerima `judul` dan menampilkan `<x-ui.flash />` di atas isi. Halaman **harus** diawali `<x-layouts.app|publik|auth …>`.

## 4. Komponen (`resources/views/components/ui/…`, dipanggil `<x-ui.nama>`)

| Komponen | Props / slot | Catatan |
|---|---|---|
| `judul-halaman` | `judul`, `deskripsi?`; slot `aksi` | satu per halaman. Judul `text-headline-s` (24px); keterangan di bawahnya `text-body-l text-on-surface-variant` (16px), sehingga keduanya jelas berbeda. Aksi utama di kanan (ponsel di bawah judul) |
| `bagian` | `judul`, `deskripsi?`; slot isi, `aksi?` | blok isi dengan judul `text-title-l`, keterangan `text-body-m text-on-surface-variant`, dan jarak `mt-8` antar bagian; **bukan** kartu |
| `panel` | `judul?`; slot, `kaki?` | wadah berbingkai (formulir, aksi). Judul `text-title-m`. Tidak boleh di dalam `panel` lain |
| `tombol` | `nada` = `utama\|tonal\|sekunder\|hantu\|bahaya\|terbalik`, `ukuran` = `md\|lg`, `href?`, `tipe?`, `ikon?`, `konfirmasi?`, `blok?` | `href` menjadi `<a>`; selain itu `<button>`. Tanpa slot dan dengan `ikon` menjadi tombol ikon (wajib `aria-label`). `konfirmasi="…"` membuka `ui.dialog` lewat `sigap.js` |
| `ikon` | `nama`, `ukuran` = `18\|24` (bawaan 24) | membaca `resources/icons/{nama}.svg`, menyisipkan `class`, `stroke-width="1.75"`, `aria-hidden="true"`; melempar `InvalidArgumentException` bila ikon tidak ada di daftar |
| `lencana` | `nada` = `netral\|info\|peringatan\|sukses\|bahaya` | teks pendek; selalu dengan titik |
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

`ambulance`, `hospital`, `house`, `layout-dashboard`, `clipboard-list`, `list-checks`, `users`, `user`, `building-2`, `stethoscope`, `bed-double`, `file-text`, `download`, `settings`, `log-out`, `menu`, `x`, `plus`, `pencil`, `search`, `check`, `circle-check`, `circle-x`, `ban`, `triangle-alert`, `info`, `clock`, `phone`, `map-pin`, `navigation`, `crosshair`, `refresh-cw`, `bell`, `chevron-down`, `chevron-right`, `arrow-left`.
Ikon hanya di menu laci, tombol yang butuh penjelas visual (mis. lokasi), peringatan, dan keadaan kosong tertentu. Tidak di dalam lingkaran berwarna, tidak di samping judul.

## 7. JavaScript

`resources/js/sigap.js` (diimpor `app.js`): `Sigap.polling(url, detik, fn)`, `Sigap.notifikasi(judul, isi)`, `Sigap.lokasiSaya(fn)`, penanganan `data-konfirmasi` (membuka `ui.dialog`), menu laci, penutup snackbar. Halaman boleh memakai `@push('skrip')` ≤ 20 baris yang **memanggil** helper itu; URL selalu dari `route()` dan dioper lewat atribut `data-*`; tidak ada `fetch` ke URL ketikan tangan; tidak ada pustaka JS lain.
`resources/js/peta.js`: `import L from 'leaflet'; import 'leaflet/dist/leaflet.css'; import 'leaflet-routing-machine'; import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';` dan perbaikan ikon penanda bawaan Leaflet untuk bundler (`import iconUrl from 'leaflet/dist/images/marker-icon.png'` … lalu `L.Icon.Default.mergeOptions({ iconUrl, iconRetinaUrl, shadowUrl })`). Ubin peta OpenStreetMap, pencarian Nominatim, dan rute OSRM demo adalah satu-satunya permintaan jaringan eksternal; catat di `ASUMSI_DAN_KEPUTUSAN.md`.

## 8. Katalog gaya

`GET /_ui` (hanya environment `local` dan `testing`, view `resources/views/dev/katalog-ui.blade.php`) menampilkan setiap komponen dan layout dengan data contoh, termasuk keadaan galat, kosong, nonaktif, dan data lama. Dipakai tes untuk memastikan semua komponen dapat dirender. Katalog juga mengikuti larangan §9.

## 9. Larangan di halaman (diperiksa otomatis oleh `agent/konsistensi.json`)

Pada berkas di bawah `resources/views/` (kecuali `pdf/`): kelas warna/gaya di luar §1 (palet bawaan, white/black, `bg-[#…]`, gradien, blur, bayangan dan sudut di luar token M3 atau dengan nilai sembarang, `tracking-*`, `uppercase`, `italic`, `font-bold`, `text-sm|lg|…`, `animate-pulse|bounce|ping`, `hover:scale|translate`); sintaks Tailwind v3; emoji; tanda panah, titik tengah, dan em dash berspasi; tag `<script src>`/`<link href=http>`/CDN; URL ketikan tangan. Pada halaman di luar `components/`: elemen mentah `<button>`, `<table>`, `<select>`, `<textarea>`, `<input>` (kecuali `type="hidden"`); `<style>`, atribut `style=`, `<html>/<head>/<body>`; halaman yang tidak diawali `<x-layouts.*>` (fragmen `@include` diawali `_` dikecualikan). Pada `resources/css/tambahan.css`: warna hanya `var(--color-…)`, `color-mix()` hanya dengan `var(--color-…)`, `box-shadow` hanya `none` atau `var(--shadow-level-1..3)`, tanpa gradien, `@keyframes`, atau `animation`. Seluruh `pdf/` berdiri sendiri (dirender dompdf) dan dikecualikan.

## 10. Pemetaan nama lama ke baru (sementara, selama migrasi)

Dipakai untuk menyapu komponen dan halaman. Dihapus dari dokumen ini setelah tidak ada lagi nama lama di `resources/views` dan aturan pemindai nama lama diaktifkan.

| Lama | Baru |
|---|---|
| `bg-canvas` | `bg-surface` |
| `bg-surface` (putih) | `bg-surface-container-lowest` |
| `text-on-surface-muted` | `text-on-surface-variant` |
| `border-line`, `divide-line`, `bg-line` | `border-outline-variant`, `divide-outline-variant`, `bg-outline-variant` |
| `border-control-border` | `border-outline` |
| `bg-primary-soft text-primary` | info: `bg-primary-container text-on-primary-container`; aktif atau terpilih: `bg-secondary-container text-on-secondary-container` |
| `hover:bg-primary-hover`, `hover:bg-canvas`, `hover:bg-primary-soft` | `lapisan-keadaan` |
| `bg-danger text-on-primary`, `text-danger`, `border-danger` | `bg-error text-on-error`, `text-error`, `border-error` |
| `bg-danger-soft text-danger` | `bg-error-container text-on-error-container` |
| `bg-warning-soft text-warning` | `bg-warning-container text-on-warning-container` |
| `bg-success-soft text-success` | `bg-success-container text-on-success-container` |
| `bg-neutral-soft` | `bg-surface-container-highest` |
| `text-h1`, `text-h2`, `text-h3` | `text-headline-s`, `text-title-l`, `text-title-m` |
| `text-body`, `text-body-sm`, `text-label`, `text-caption`, `text-display` | `text-body-l`, `text-body-m`, `text-label-l`, `text-body-s`, `text-display-s` |
| `font-semibold` | `font-medium` |
| `rounded-sm`, `rounded-md`, `rounded-lg` (4, 6, 8px) | per komponen: lencana `rounded-sm`, tombol dan item menu `rounded-full`, isian `rounded-xs`, panel dan tabel `rounded-md` |
| `shadow-overlay` | `shadow-level-3` |
| `w-rail` | `w-drawer` |
| `ukuran="sm"` pada `x-ui.tombol` | dihapus (semua tombol 40px) |
| `min-h-touch` (44px) | `min-h-touch` (48px) |
