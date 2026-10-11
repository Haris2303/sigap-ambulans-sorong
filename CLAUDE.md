# SIGAP Ambulans v2 — aturan kerja (mode interaktif)

Aplikasi Laravel 13 (PHP 8.3) dengan Tailwind CSS 4.3 lewat Vite, Blade, dan SQLite untuk tes. Alur layanan: Masyarakat, Sistem, Dinkes (verifikasi dan penugasan ambulans serta sopir), Faskes, layanan. Sesi ini dikerjakan bersama pengguna, bukan agen otonom: `CLAUDE.agent.md` tidak dimuat dan kuncinya tidak berlaku.

## Dokumen rujukan

- `docs/RANCANGAN_ALUR.md`: istilah baku, data, mesin status, route. Ikuti istilahnya (sopir, ambulans, faskes).
- `DESIGN.md`: sumber token visual (Material Design 3, bukan Expressive). `resources/css/design-tokens.css` dihasilkan darinya oleh `agent/tokens.mjs`; jangan mengubah hasilnya dengan tangan.
- `docs/KIT_UI.md`: kelas yang tersedia, resep kelas, komponen Blade, pola halaman.
- `agent/konsistensi.json`: aturan pemindai yang dijalankan `KonsistensiTest`. Baca sebelum menulis kode tampilan.
- `docs/PROGRESS.md`: catatan kemajuan, termasuk daftar langkah migrasi ke Material 3.

## Tampilan

- Gaya hanya lewat token `DESIGN.md` dan komponen `x-ui.*` (`resources/views/components/ui`). Halaman merakit komponen; tidak menulis gaya sendiri.
- Warna, ukuran teks, sudut, dan bayangan hanya dari token. Tidak ada heksadesimal, nilai sembarang (`bg-[#…]`), atau palet bawaan Tailwind.
- Cari komponen yang sudah ada sebelum membuat yang baru. Variasi berarti prop baru pada komponen yang ada, bukan komponen kedua. Komponen baru ikut ditambahkan ke katalog `/_ui`.
- Label status, peran, dan jenis diambil dari Enum (`->label()`); perubahan status hanya lewat `PesananWorkflow`.
- CSS tambahan hanya di `resources/css/tambahan.css`, dan warnanya hanya lewat `var(--color-…)`.

## Cara kerja

- Pengguna boleh ditanya bila ada pilihan yang menentukan arah dan tidak bisa diputuskan dari dokumen atau kode. Pilihan yang punya default konvensional diambil saja, lalu disebut di laporan.
- Pekerjaan dipecah kecil dan dicommit per perubahan yang utuh. Pesan commit satu baris berbahasa Indonesia: `feat(warga): …`, `fix(admin): …`, `test: …`, `docs: …`, `chore: …`.
- Sebelum menyatakan selesai, jalankan `php artisan test` dan `npm run build`. Laporkan hasil sebenarnya; bila ada yang gagal, katakan apa adanya.
- Tes tidak dilemahkan agar lulus. Bila kontraknya memang berubah, ubah tes bersama kode yang diubah.
- Tidak ada browser di sesi ini: tampilan, JS, dan peta tidak teruji secara visual. Sebutkan keterbatasan itu saat melaporkan perubahan tampilan.

## Batas keselamatan

- Jangan membaca atau mencetak `.env`. Tidak ada kredensial di kode, dokumen, atau commit.
- Basis data lokal proyek ini boleh `migrate:fresh --seed`. Jangan `db:wipe` atau menyentuh basis data lain. Tes memakai SQLite `:memory:` sesuai `phpunit.xml`.
- Git: tidak push, reset keras, atau mengubah remote tanpa diminta. Perintah yang menambah paket (`npm install`, `composer require`) dijalankan hanya bila perlu dan dilaporkan.
