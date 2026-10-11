# KIT UI — SIGAP Ambulans v2

> Dikunci (agen tidak boleh mengubah). Pasangannya `DESIGN.md` (identitas visual, token, larangan gaya). Dokumen ini menjawab "bagaimana dibangun di kode": tumpukan, kelas yang tersedia, komponen Blade, pola halaman. Tujuan: **semua halaman terasa satu aplikasi dan tidak terlihat seperti keluaran generator**. Halaman hanya merakit komponen; gaya tidak pernah ditulis per halaman.

## 1. Tumpukan dan aset

| Hal | Ketentuan |
|---|---|
| CSS | Tailwind CSS 4.3 lewat Vite (`@tailwindcss/vite`). Token berasal dari `DESIGN.md`; runner menghasilkan `resources/css/design-tokens.css` (jangan diubah). `resources/css/app.css` dikunci. CSS tambahan hanya di `resources/css/tambahan.css` (aturan di berkasnya; warna hanya `var(--color-…)`) |
| Font | Public Sans Variable dari paket npm (`@fontsource-variable/public-sans`), diimpor di `app.css`. Tidak ada font dari internet |
| Ikon | Lucide, disalin runner ke `resources/icons/*.svg` (daftar di §6), ditampilkan lewat `<x-ui.ikon>` |
| JS | Vite: `resources/js/app.js` (impor `sigap.js`) dan `resources/js/peta.js` (Leaflet 1.9.4 + Leaflet Routing Machine 3.2.12 dari npm). Tidak ada CDN, tidak ada `<script src>` di view mana pun |
| Pemuatan | Hanya `layouts.dasar` yang memuat aset: `@vite(['resources/css/app.css','resources/js/app.js'])`, dan `@vite('resources/js/peta.js')` bila `:peta="true"` |
| Build | `npm run build` harus lulus (diperiksa runner). Tes memakai `$this->withoutVite()` (di `Tests\TestCase`) sehingga tidak perlu manifest |

**Kelas yang ada** (selain utilitas Tailwind biasa untuk tata letak: `flex`, `grid`, `gap-*`, `p-*`, `m-*`, `w-*`, `size-*`, `hidden`, `md:`, `lg:`, `truncate`, dst. dengan skala numerik bawaan):
- Warna: `bg|text|border|outline|fill|stroke|divide-<token>` dengan token `canvas`, `surface`, `on-surface`, `on-surface-muted`, `line`, `control-border`, `primary`, `primary-hover`, `primary-soft`, `on-primary`, `danger`, `danger-hover`, `danger-soft`, `warning`, `warning-soft`, `success`, `success-soft`, `neutral-soft`, `scrim`.
- Teks: satu kelas per gaya, mengatur ukuran, tinggi baris, bobot: `text-display`, `text-h1`, `text-h2`, `text-h3`, `text-body`, `text-body-sm`, `text-label`, `text-caption`. Bobot lain hanya `font-normal|font-medium|font-semibold`. Angka berderet: `tabular-nums`.
- Sudut: `rounded-sm` (4px), `rounded-md` (6px), `rounded-lg` (8px), `rounded-full`. Bayangan: hanya `shadow-overlay`.
- Ukuran: `min-h-touch`/`h-touch` (44px), `w-rail` (232px), `max-w-content` (1152px), `max-w-prose`.

**Tidak ada** (tidak menghasilkan CSS, dan dipindai): palet bawaan (`bg-red-500`, `text-gray-600`, `text-white`), `text-sm|xs|lg|xl…`, `font-bold`, `rounded-xl+`, `shadow-md+`, `blur`, `tracking-*`, `uppercase`, `italic`, gradien, `animate-pulse|bounce|ping`, `hover:scale|translate`. Boleh: `animate-spin` untuk indikator memuat, `transition-colors`.

## 2. Resep kelas (supaya hal yang sama selalu terlihat sama)

| Elemen | Kelas |
|---|---|
| Tombol (semua) | `inline-flex min-h-touch items-center justify-center gap-2 rounded-md px-4 text-label transition-colors disabled:opacity-50` |
| Tombol utama | `bg-primary text-on-primary hover:bg-primary-hover` |
| Tombol sekunder | `border border-control-border bg-surface text-on-surface hover:bg-canvas` |
| Tombol bahaya | `bg-danger text-on-primary hover:bg-danger-hover` |
| Tombol hantu | `text-primary hover:bg-primary-soft` |
| Ukuran tombol | `sm`: `min-h-9 px-3`; `md`: bawaan; `lg`: `min-h-12 px-6` (hanya Kirim pesanan di ponsel) |
| Isian / pilihan / area teks | `block w-full min-h-touch rounded-md border border-control-border bg-surface px-3 text-body text-on-surface placeholder:text-on-surface-muted`; galat: `border-danger` |
| Label isian | `mb-1 block text-label text-on-surface`; bantuan `mt-1 text-caption text-on-surface-muted`; galat `mt-1 text-body-sm text-danger` |
| Panel | `rounded-lg border border-line bg-surface p-4 md:p-6` |
| Pembungkus tabel | `overflow-x-auto rounded-lg border border-line bg-surface`; kepala `bg-canvas text-label text-on-surface-muted`; sel `px-4 py-3 text-body-sm`; garis antarbaris `divide-y divide-line` |
| Lencana | `inline-flex items-center gap-1.5 rounded-sm px-2 py-0.5 text-caption` + titik `size-1.5 rounded-full bg-current`; nada `netral: bg-neutral-soft text-on-surface-muted`, `info: bg-primary-soft text-primary`, `peringatan: bg-warning-soft text-warning`, `sukses: bg-success-soft text-success`, `bahaya: bg-danger-soft text-danger` |
| Peringatan (pesan) | sama dengan lencana tetapi `rounded-md border border-line px-4 py-3 text-body-sm`, ikon di kiri (`info`, `circle-check`, `triangle-alert`, `circle-x`) |
| Item menu rel | `flex min-h-touch items-center gap-3 rounded-md px-3 text-label text-on-surface-muted hover:bg-canvas`; aktif `bg-primary-soft text-primary` |
| Tautan teks | `text-primary underline underline-offset-2 hover:text-primary-hover` |

Catatan: di Tailwind 4 warna `border` bawaan adalah `currentColor`, jadi setiap `border` **harus** disertai warna token (`border-line` atau `border-control-border`).

## 3. Layout (`resources/views/components/layouts/`)

| Komponen | Dipakai oleh | Isi |
|---|---|---|
| `layouts.dasar` | dasar semua layout | `<html lang="id">`/`<head>`/`<body>`; judul `"{judul} - SIGAP Ambulans"`; `@vite` (lihat §1); `@stack('skrip')`; tautan "Lewati ke isi" untuk papan ketik. **Satu-satunya** tempat tag `<html>/<head>/<body>` dan `@vite` |
| `layouts.publik` | halaman tanpa login (landing, `/faskes`) | bilah atas sederhana: nama aplikasi di kiri; Info Faskes dan Masuk (sekunder) di kanan, atau "Ke dasbor" bila sudah masuk; kaki halaman satu baris (nama Dinkes) |
| `layouts.auth` | login, register | kolom tunggal rata kiri, lebar maks 24rem, nama aplikasi di atas, tanpa panel dekoratif |
| `layouts.app` | semua halaman setelah login | `lg+`: rel menu kiri `w-rail` (nama aplikasi, daftar menu dari `config('sigap.menu.<peran>')`, di dasar: nama pengguna + label peran + Keluar); ponsel: bilah atas 56px dengan tombol menu yang membuka rel sebagai laci. Isi: `max-w-content`, padding `p-4 md:p-8`. Lencana angka "Pesanan" untuk Dinkes dari View Composer (tanpa query di Blade) |

Semua layout menerima `judul` dan menampilkan `<x-ui.flash />` di atas isi. Halaman **harus** diawali `<x-layouts.app|publik|auth …>`.

## 4. Komponen (`resources/views/components/ui/…`, dipanggil `<x-ui.nama>`)

| Komponen | Props / slot | Catatan |
|---|---|---|
| `judul-halaman` | `judul`, `deskripsi?`; slot `aksi` | satu per halaman (`text-h1`); aksi utama di kanan (ponsel di bawah judul) |
| `bagian` | `judul`, `deskripsi?`; slot isi, `aksi?` | blok isi dengan `text-h2` dan jarak `mt-8` antar bagian; **bukan** kartu |
| `panel` | `judul?`; slot, `kaki?` | wadah berbingkai (formulir, aksi). Tidak boleh di dalam `panel` lain |
| `tombol` | `nada` = `utama\|sekunder\|bahaya\|hantu`, `ukuran` = `sm\|md\|lg`, `href?`, `tipe?`, `ikon?`, `konfirmasi?`, `blok?` | `href` menjadi `<a>`; selain itu `<button>`. `konfirmasi="…"` membuka `ui.dialog` lewat `sigap.js` |
| `ikon` | `nama`, `ukuran` = `16\|20` (bawaan 20) | membaca `resources/icons/{nama}.svg`, menyisipkan `class`, `stroke-width="1.75"`, `aria-hidden="true"`; melempar `InvalidArgumentException` bila ikon tidak ada di daftar |
| `lencana` | `nada` = `netral\|info\|peringatan\|sukses\|bahaya` | teks pendek; selalu dengan titik |
| `lencana-status` | `status` (enum `PesananStatus`) | memakai `->label()` dan `->nada()`; **satu-satunya** cara menampilkan status |
| `flash` | — | membaca `session('sukses')`, `session('galat')`, dan ringkasan `$errors`; memakai gaya `peringatan` |
| `peringatan` | `nada` = `info\|peringatan\|sukses\|bahaya` | pesan sebaris di halaman |
| `dialog` | `id`, `judul`, `pesan`, `aksi` (teks), `nada?` | elemen `<dialog>` bawaan; bayangan `shadow-overlay`, latar `bg-scrim`; satu instance dipakai bersama oleh semua `data-konfirmasi` |
| `tabel` | slot `kepala`, slot isi | pembungkus tabel (resep §2) |
| `kosong` | `pesan`; slot `aksi` | keadaan kosong: "Belum ada …", rata kiri, dengan satu aksi bila ada |
| `isian` | `nama`, `label`, `tipe`, `nilai?`, `bantuan?`, `wajib?` | label, pesan galat per kolom (`$errors`), `old()` otomatis |
| `pilihan` | `nama`, `label`, `opsi` (nilai→label), `nilai?`, `kosong?` | pembungkus `<select>`; opsi dari `Enum::opsi()` |
| `area-teks` | seperti `isian` + `baris` | |
| `centang` | `nama`, `label`, `nilai?` | |
| `opsi-baris` | `nama`, `nilai`, `judul`, `deskripsi?`, `terpilih?` | radio berbentuk baris besar berbingkai (jenis layanan, tingkat darurat); terpilih: `border-primary bg-primary-soft`; target sentuh penuh |
| `ringkasan` + `ringkasan-sel` | `label`, `nilai`, `nada?`, `keterangan?` | satu wadah berbingkai dengan sel dipisah garis (2 kolom ponsel, 4 desktop); angka `text-display tabular-nums`. Menggantikan "kartu statistik" |
| `rincian` | slot daftar `rincian-baris` (`label`, slot nilai) | daftar definisi `<dl>`: label `text-on-surface-muted` di kiri, nilai di kanan, garis antarbaris. Dipakai di semua halaman detail |
| `waktu` | `nilai`, `relatif?` | zona Asia/Jayapura, `d M Y H:i`; `relatif` menambah "x menit lalu"; `tabular-nums` |
| `grafik-batang` | `data` (label→angka), `nada?` | batang CSS murni; satu-satunya tempat boleh `style="width:…"` |
| `paginasi` | `data` (paginator) | membuat markup sendiri dari `$data->linkCollection()` (tampilan paginasi bawaan Laravel memakai palet Tailwind lama dan **tidak boleh** dipakai); bahasa Indonesia ("Sebelumnya", "Berikutnya", "Menampilkan 1–20 dari 134") |
| `linimasa` | `log` (koleksi `PesananLog`) | sesuai `DESIGN.md` bagian Components: kolom waktu tetap, titik, label status, pelaku/peran, catatan; langkah terakhir `font-semibold` |
| `ketersediaan` | `faskes` | bed IGD x/y, bed rawat inap x/y, dokter umum, dokter spesialis, ambulans tersedia, "diperbarui …" / "Belum diperbarui" / "Data lama" (lencana `peringatan`) |
| `peta.pemilih` | nama kolom lat/lng/alamat | peta Leaflet + tombol "Gunakan lokasi saya" + cari alamat (Nominatim) + klik peta |
| `peta.pelacak` | `lat`, `lng`, `urlPosisi?` | penanda jemput + posisi sopir (polling) + rute |
| `peta.faskes` | `daftar` | penanda Faskes berkoordinat |

Aturan komponen: dibangun dari resep §2; tidak ada kelas di luar §1; variasi baru berarti prop baru pada komponen yang ada, bukan komponen kedua.

## 5. Pola halaman

- **Daftar**: `judul-halaman` → form filter satu baris (`isian`/`pilihan` + `tombol sekunder "Cari"` + tautan "Atur ulang"; di ponsel menumpuk) → `tabel` → `paginasi`. Tanpa data: `kosong`. Ponsel: kolom sekunder `hidden md:table-cell`; baris dapat diklik penuh lewat kolom pertama berupa tautan.
- **Formulir**: satu `panel` lebar maksimum `max-w-xl`, kolom vertikal `space-y-5`; di dasar: `tombol utama "Simpan"` + `tombol hantu "Batal"`.
- **Detail**: dua kolom pada `lg` (kiri: `rincian` + `linimasa`; kanan: `panel` aksi), satu kolom di ponsel. Aksi hanya yang valid untuk status dan peran saat ini (`PesananStatus::transisiTerbuka()` + peran).
- **Aksi berbahaya** (tolak, batalkan, lepas): `tombol nada="bahaya"` dengan `konfirmasi`; alasan lewat `area-teks` pada form kecil bila aturan status menuntutnya.
- **Dasbor**: `ringkasan` → `bagian` demi `bagian` (tabel/daftar). Tidak ada kisi kartu.
- **Landing (`beranda`)**: rata kiri, `max-w-prose`. H1 faktual: "Pesan ambulans Dinas Kesehatan Kota Sorong." Satu paragraf (apa yang dilayani, siapa yang memverifikasi). `tombol utama "Pesan ambulans"` + `tombol sekunder "Info Faskes"`. Bagian "Cara kerja": **daftar bernomor** empat langkah (pesan, Dinkes memverifikasi, ambulans dan sopir ditugaskan, pantau perjalanan) karena urutannya nyata. Tanpa gambar, ilustrasi, hero, atau kisi fitur.
- **Buat Pesanan (ponsel dulu)**: satu kolom; urutan: jenis layanan (`opsi-baris`), tingkat darurat, lokasi jemput (tombol besar "Gunakan lokasi saya" + peta + alamat), Faskes tujuan, data pasien, catatan; tombol "Tinjau pesanan" tetap terlihat di dasar layar ponsel (`sticky bottom-0 border-t border-line bg-surface p-4`).
- **Judul** = kalimat biasa (huruf awal kapital saja). Satu `judul-halaman` per halaman.

## 6. Daftar ikon (satu-satunya yang tersedia)

`ambulance`, `hospital`, `house`, `layout-dashboard`, `clipboard-list`, `list-checks`, `users`, `user`, `building-2`, `stethoscope`, `bed-double`, `file-text`, `download`, `settings`, `log-out`, `menu`, `x`, `plus`, `pencil`, `search`, `check`, `circle-check`, `circle-x`, `ban`, `triangle-alert`, `info`, `clock`, `phone`, `map-pin`, `navigation`, `crosshair`, `refresh-cw`, `bell`, `chevron-down`, `chevron-right`, `arrow-left`.
Ikon hanya di menu rel, tombol yang butuh penjelas visual (mis. lokasi), peringatan, dan keadaan kosong tertentu. Tidak di dalam lingkaran berwarna, tidak di samping judul.

## 7. JavaScript

`resources/js/sigap.js` (diimpor `app.js`): `Sigap.polling(url, detik, fn)`, `Sigap.notifikasi(judul, isi)`, `Sigap.lokasiSaya(fn)`, penanganan `data-konfirmasi` (membuka `ui.dialog`), menu laci. Halaman boleh memakai `@push('skrip')` ≤ 20 baris yang **memanggil** helper itu; URL selalu dari `route()` dan dioper lewat atribut `data-*`; tidak ada `fetch` ke URL ketikan tangan; tidak ada pustaka JS lain.
`resources/js/peta.js`: `import L from 'leaflet'; import 'leaflet/dist/leaflet.css'; import 'leaflet-routing-machine'; import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';` dan perbaikan ikon penanda bawaan Leaflet untuk bundler (`import iconUrl from 'leaflet/dist/images/marker-icon.png'` … lalu `L.Icon.Default.mergeOptions({ iconUrl, iconRetinaUrl, shadowUrl })`). Ubin peta OpenStreetMap, pencarian Nominatim, dan rute OSRM demo adalah satu-satunya permintaan jaringan eksternal; catat di `ASUMSI_DAN_KEPUTUSAN.md`.

## 8. Katalog gaya

`GET /_ui` (hanya environment `local` dan `testing`, view `resources/views/dev/katalog-ui.blade.php`) menampilkan setiap komponen dan layout dengan data contoh, termasuk keadaan galat, kosong, dan data lama. Dipakai tes untuk memastikan semua komponen dapat dirender. Katalog juga mengikuti larangan §9.

## 9. Larangan di halaman (diperiksa otomatis oleh `agent/konsistensi.json`)

Pada berkas di bawah `resources/views/` (kecuali `pdf/`): kelas warna/gaya di luar §1 (palet bawaan, white/black, `bg-[#…]`, gradien, blur, bayangan dan sudut besar, `tracking-*`, `uppercase`, `italic`, `font-bold`, `text-sm|lg|…`, `animate-pulse|bounce|ping`, `hover:scale|translate`); sintaks Tailwind v3; emoji; tanda panah, titik tengah, dan em dash berspasi; tag `<script src>`/`<link href=http>`/CDN; URL ketikan tangan. Pada halaman di luar `components/`: elemen mentah `<button>`, `<table>`, `<select>`, `<textarea>`, `<input>` (kecuali `type="hidden"`); `<style>`, atribut `style=`, `<html>/<head>/<body>`; halaman yang tidak diawali `<x-layouts.*>` (fragmen `@include` diawali `_` dikecualikan). Seluruh `pdf/` berdiri sendiri (dirender dompdf) dan dikecualikan.
