# Paket Agen Otonom v2.1 — SIGAP Ambulans (Laravel 13, Tailwind 4.3, DESIGN.md)

Satu perintah, lalu Claude Code **membangun ulang aplikasi dari nol** di folder baru, tanpa Anda mengetik prompt lagi.
Aplikasi lama dan database lamanya **tidak disentuh**; hanya dibaca sebagai referensi fitur.

## Yang berubah dari v2 (sesuai permintaan Anda)

| Permintaan | Yang dilakukan v2.1 |
|---|---|
| Laravel 13 yang terbaru | `composer create-project laravel/laravel:^13.0` (membutuhkan **PHP 8.3+**). Kode mengikuti idiom Laravel 13: atribut Eloquent `#[Table]`/`#[Fillable]`/`#[Hidden]`, alias middleware di `bootstrap/app.php`, Vite 8. |
| Tailwind "5" | **Tailwind CSS 5 belum ada.** Rilis terbaru adalah **4.3** (CSS-first, lewat plugin Vite, bukan CDN). Paket memakai itu. Bila v5 kelak rilis, naikkan sendiri di `package.json`. |
| Hindari tampilan "AI slop", minimalis profesional, pakai `DESIGN.md` | Berkas **`DESIGN.md`** (format Google Labs: token YAML + penjelasan desain) menjadi satu-satunya sumber tampilan. Lihat bagian berikut. |

## DESIGN.md dan cara tampilan dijaga

`DESIGN.md` di akar paket ini adalah sistem desain yang sudah jadi, bukan template kosong:

- **Arah**: "buku catatan dispatch" — tenang, resmi, padat informasi. Baris rapi dengan garis tipis, angka tabular, satu font (Public Sans, dipasang lokal lewat npm, tanpa Google Fonts), satu warna aksen (biru petrol `#0B5C80`), lima nada status (netral, info, peringatan, sukses, bahaya). Satu hal sengaja menonjol: linimasa pesanan.
- **Dilarang** (tertulis di bagian *Do's and Don'ts* dan **dipindai otomatis**): gradien, blur, bayangan dekoratif, emoji, kartu di dalam kartu, kisi kartu fitur, hero/ilustrasi/testimoni, animasi masuk, huruf kapital semua + jarak huruf lebar, miring, panah/titik tengah/em dash berspasi ala templat, warna di luar token, font/CDN luar.
- **Dari dokumen ke kode**: `agent/tokens.mjs` membaca `DESIGN.md` → menulis `resources/css/design-tokens.css` (blok `@theme` Tailwind). Palet bawaan Tailwind, ukuran huruf bawaan, bayangan, dan radius bawaan **dimatikan**, jadi yang tersedia hanya token proyek ini (`bg-surface`, `text-on-surface-muted`, `border-line`, `bg-primary`, `rounded-md`, `text-h2`, dst.).
- **Kit UI dulu**: tahap 2 membangun layout dan komponen `x-ui.*` beserta katalog `/_ui`; halaman peran hanya boleh merakit komponen itu. Resepnya ada di `docs/KIT_UI.md`.
- **Pemeriksa independen** (dijalankan runner, bukan agen): `designmd lint` pada `DESIGN.md` (0 galat), regenerasi token, `npm run build`, dan 16 aturan di `agent/konsistensi.json` (istilah baku, warna mentah, gaya terlarang, emoji, tanda templat, elemen/URL mentah, CDN, literal/mutasi status, gaya model, Tailwind v3 usang, dll.).
- **Mengubah tampilan**: edit warna/ukuran di front matter `DESIGN.md` **sebelum run pertama** (atau sesudahnya: jalankan `node agent/tokens.mjs` lalu `npm run build`). Karena semua warna adalah token, seluruh halaman ikut berubah. Agen sendiri dilarang mengubah `DESIGN.md`.

Checklist kerja: 33 item di `docs\PROGRESS.md` (Tahap 0 fondasi → 1 domain & data → 2 UI kit → 3 autentikasi/publik → 4 Masyarakat → 5 Dinkes → 6 Petugas & Sopir → 7 konsistensi & dokumen laporan).

## Sebelum mulai (sekali saja)

1. **Laragon menyala** (*Start All*; MySQL harus hidup).
2. **PHP 8.3 atau lebih baru.** Laragon: klik kanan ikon Laragon → *PHP* → *Version* → pilih 8.3+ (atau unduh PHP 8.3+ ke `C:\laragon\bin\php`). Skrip mencari PHP 8.3+ sendiri (PATH, lalu folder Laragon/XAMPP/WAMP; atau beri `-PhpBinDir`). Ekstensi `pdo_sqlite`, `sqlite3`, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl` harus aktif (skrip memeriksa).
3. **Node.js 20.19+ atau 22.12+** (syarat Vite 8) dan `npm`. Dapat dipasang dari nodejs.org atau menu Node.js di Laragon.
4. **`composer`, `git`, `claude` ada di PATH** dan Claude Code sudah login. **Butuh internet** pada run pertama (composer dan npm mengunduh paket).
5. **Folder BARU yang terpisah dari proyek lama**, misalnya `C:\laragon\www\sigap-ambulans-v2`. Skrip menolak berjalan di dalam folder proyek lama, dan menolak bila folder sudah berisi `app\`, `config\`, `package.json`, dll. (selain isi paket ini).
6. Laptop dicolok listrik. Skrip mencegah Windows tidur selama berjalan.

## Pasang dan jalankan

1. Ekstrak zip **ke dalam** folder baru itu. Setelah ekstrak, berkas `agent\run-agent.ps1` harus langsung berada di folder tersebut. Bila Windows membuat satu subfolder tambahan, pindahkan isinya ke atas satu tingkat.
2. Buka PowerShell di folder itu, lalu cek dulu (tidak mengubah apa pun):

   ```powershell
   powershell -ExecutionPolicy Bypass -File .\agent\run-agent.ps1 -CheckOnly
   ```
3. Jalankan sungguhan:

   ```powershell
   powershell -ExecutionPolicy Bypass -File .\agent\run-agent.ps1
   ```

Opsi (semua opsional): `-PhpBinDir "C:\laragon\bin\php\php-8.3.x-...."`, `-LegacyDir "C:\...\ambulance-system"` (bila proyek lama tidak ada di folder sebelah), `-LegacyRef <branch>` (default: `fitur/alur-dinkes`, lalu `audit-aplikasi`, `main`, `master`), `-DbName sigap_ambulans_v2`, `-MysqlBinDir "C:\laragon\bin\mysql\<versi>\bin"`, `-MaxIterations 60`, `-MaxTurns 200`, `-BudgetPerIterationUsd 25`, `-TotalBudgetUsd 300`, `-IterationTimeoutMinutes 150`, `-Model sonnet|opus`, `-LimitWaitMinutes 30`, `-SkipDbBackup`.
Angka biaya memakai taksiran Claude Code; pada langganan (bukan API) itu hanya penghitung pengaman.

## Yang dikerjakan skrip pada run pertama

1. `composer create-project laravel/laravel:^13.0` lalu `composer require barryvdh/laravel-dompdf` (satu-satunya paket PHP tambahan).
2. `npm install` (kerangka: Vite 8, Tailwind 4, plugin Laravel) ditambah `@google/design.md` (linter), `@fontsource-variable/public-sans`, `lucide-static`, `yaml`, dan `leaflet@1.9.4` + `leaflet-routing-machine@3.2.12` (versi dikunci). **Agen dilarang memasang paket**; hanya runner yang boleh.
3. Frontend: menyalin `app.css`, `vite.config.js`, ikon Lucide terpilih (`agent\icons.txt`, 36 ikon) → `designmd lint` → `agent\tokens.mjs` → `npm run build` (harus berhasil sebelum agen dimulai).
4. `.env` baru: `APP_NAME`, locale `id`, `DB_DATABASE=sigap_ambulans_v2`; **host/port/user/sandi MySQL disalin dari `.env` proyek lama** (tidak pernah ditampilkan atau dicatat; bila proyek lama tidak ada → `127.0.0.1:3306`, `root`, tanpa sandi). Lalu `CREATE DATABASE IF NOT EXISTS` dan `php artisan migrate --force` (database baru, kosong).
5. `legacy\` ← `git archive` dari proyek lama (hanya berkas yang ter-commit; **tanpa `.env`**). Proyek lama tidak diubah.
6. `phpunit.xml` dipastikan memakai SQLite `:memory:` (tes tidak pernah menyentuh MySQL). `CLAUDE.md` dan `AGENTS.md` bawaan kerangka Laravel 13 menyuruh memasang Laravel Boost lewat internet; keduanya **diganti** agar hanya mengimpor `CLAUDE.agent.md`. `git init`, branch kerja `agen/v2`, commit awal; backup database ke `agent\backup\`.
7. Mulai sesi agen berulang sampai checklist habis **dan** gate lulus.

Run berikutnya melewati langkah 1–6 (penanda `agent\.bootstrapped` dan `agent\.frontend-ready`) dan langsung melanjutkan dari `docs\PROGRESS.md`.

## Selama berjalan

- Biarkan jendela PowerShell terbuka; jangan mengedit file proyek baru atau menjalankan `migrate` di sana.
- Mengintip: `docs\PROGRESS.md` (centang item), `git log --oneline` (branch `agen/v2`), `agent\logs\ringkasan.md` (laporan tiap sesi), `agent\last-gate.txt` (hasil pemeriksaan runner, termasuk daftar pelanggaran desain).
- Terkena limit pemakaian → skrip menunggu 30 menit lalu mencoba lagi (hingga 12 kali). Terputus (listrik/restart/Ctrl+C) → jalankan **perintah yang sama** lagi.
- Perkiraan: satu sesi 20–60 menit; total beberapa jam hingga semalam, tergantung limit akun. Tidak bisa dipastikan dari sini.

## Jika skrip berhenti

| Pesan / kode keluar | Artinya | Tindakan |
|---|---|---|
| `PHP yang ditemukan terlalu lama` / `Node ... terlalu lama` (4) | Laravel 13 butuh PHP 8.3+, Vite 8 butuh Node 20.19+/22.12+ | Ganti versinya (lihat "Sebelum mulai"), jalankan ulang |
| `Preflight gagal` (4) | Prasyarat lain belum terpenuhi (MySQL mati, composer/npm tidak ada, folder tidak kosong, dll.) | Baca baris `ERROR`, perbaiki, jalankan ulang |
| `composer create-project gagal` / `npm install gagal` (4) | Internet atau registry bermasalah | Periksa koneksi, jalankan ulang (langkah yang sudah selesai dilewati) |
| `Claude Code belum login` (4) | Token kedaluwarsa | Jalankan `claude`, login, ulangi perintah yang sama |
| `Gagal beruntun` (4) | Sesi berulang gagal | Lihat `agent\logs\iter-NNN.err.txt` |
| `Macet` / `Limit bertahan` / `Batas iterasi` (3) | Tidak ada kemajuan, limit lama, atau 60 iterasi habis | Ulangi perintah yang sama (atau naikkan `-MaxIterations`). Bila `agent\last-gate.txt` menyebut `DESIGN.md tidak lolos designmd lint`, berkas itu rusak: pulihkan dengan `git checkout DESIGN.md` |
| `Agen melaporkan hambatan` (2) | `STATUS_AGEN: BLOKIR: …` di PROGRESS | Baca alasannya; selesaikan; ubah baris pertama PROGRESS menjadi `STATUS_AGEN: BERJALAN`; jalankan ulang |

## Pengaman

- Agen hanya boleh mengubah isi proyek baru: tidak boleh mengubah `agent\`, `legacy\`, `CLAUDE*.md`, `AGENTS.md`, `DESIGN.md`, `docs\RANCANGAN_ALUR.md`, `docs\KIT_UI.md`, `phpunit.xml`, `composer.json/lock`, `package.json/lock`, `vite.config.js`, `resources\css\app.css`, `design-tokens.css`, `resources\icons\`, `public\build\`, `.env*`, `.git`, `vendor`, `node_modules`, `storage`; tidak boleh membaca `.env`; tidak ada `curl`/`wget`/`composer require`/`npm install|ci|update|exec`/`npx`/`node` (hanya `npm run build`); tidak ada `push`/`reset`/`clean`/`rebase`/`checkout`; tidak ada perintah hapus berkas; tidak ada `db:wipe`/`tinker`/`serve`. `migrate:fresh` diizinkan **hanya** karena database v2 baru dan kosong.
- Aturan izin adalah pagar (guardrail), bukan sandbox yang menjamin. Karena itu database lama tidak dihubungkan ke proyek baru, dan backup dibuat tiap run.
- Berkas yang seharusnya dihapus (mis. `legacy\`, sisa kerangka) dicatat agen di PROGRESS → "Untuk dihapus manual".

## Setelah selesai

Skrip membunyikan bip dan membuka `docs\HASIL_AKHIR.md`: fitur, cara menjalankan (`php artisan migrate:fresh --seed`, `npm run build`, lalu buka lewat Laragon atau `php artisan serve`), akun demo, **checklist uji manual 10 menit per peran**, dan daftar item buntu. Dokumen untuk merevisi laporan (Bab 3) ada di `docs\laporan\` (kebutuhan fungsional, use case, alur status, ERD, class diagram, kamus data, peta layar, pengujian black-box dari tes yang benar-benar dijalankan, asumsi & keputusan).

## Keterbatasan yang jujur

- **Agen tidak punya browser.** Ia tidak bisa *melihat* hasilnya. Pemindai dan build menangkap pelanggaran **mekanis** (warna/kelas di luar token, emoji, gradien, CDN, elemen mentah, dll.) dan memastikan CSS/JS dapat dikompilasi, tetapi tidak menilai selera visual, ritme jarak, atau keterbacaan. Setelah selesai, buka `/_ui` dan halaman tiap peran sendiri (ukuran ponsel dan desktop); selisih yang Anda temukan bisa diperbaiki dengan sesi lanjutan atau dengan mengubah token di `DESIGN.md`.
- JavaScript, peta, GPS, polling, dan notifikasi browser juga tidak teruji otomatis; sisi server teruji lewat tes HTTP. Jalankan checklist uji manual di `HASIL_AKHIR.md`.
- `DESIGN.md` (Google Labs) masih berstatus **alpha**; linternya bisa berubah. Paket ini memasang versi yang ada saat run pertama. Bila lint mendadak gagal setelah pembaruan paket, tanda-tandanya ada di `agent\last-gate.txt`.
- Rute peta memakai server demo OSRM (butuh internet). Layak untuk demo skripsi, bukan produksi.
- Data awal (nama Faskes, plat `PB-DEMO-xx`, aturan saran ambulans, dll.) adalah asumsi dari wawancara; daftar yang menunggu konfirmasi Dinkes ada di `docs\laporan\ASUMSI_DAN_KEPUTUSAN.md`.
- **Yang sudah diuji dan yang belum.** Runner diuji di Linux dengan PowerShell 7: bootstrap sampai selesai dengan **npm, Vite 8, Tailwind 4.3, dan linter DESIGN.md yang asli**, kerangka Laravel 13 asli (tanpa vendor), serta tiga sesi agen tiruan yang sengaja melanggar aturan (18 pelanggaran tertangkap, build rusak tertangkap, klaim "SELESAI" palsu ditolak, lalu lolos setelah diperbaiki); juga kegagalan PHP/Node lama, `DESIGN.md` rusak, folder bentrok, composer gagal, dan lanjut-setelah-selesai. `composer`, `mysql`, dan `claude` pada uji itu **tiruan**. **Belum** diuji di Windows PowerShell 5.1 dengan composer, MySQL, dan Claude Code asli. Bila ada galat pada run pertama, kirim baris `ERROR` terakhir dari `agent\logs\run-*.log`.
