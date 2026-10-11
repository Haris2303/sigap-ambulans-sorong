# Instruksi satu sesi agen

Kamu dijalankan oleh `agent/run-agent.ps1` tanpa pengawasan manusia. Sesi ini berdiri sendiri: ingatanmu hanya `docs/PROGRESS.md`, git, dan kode.
Kerjakan sebanyak mungkin item checklist berurutan; runner memulai sesi baru setelah kamu selesai atau kehabisan giliran/konteks.

## 1. Orientasi (wajib, berurutan)

1. Baca `CLAUDE.agent.md`, `docs/RANCANGAN_ALUR.md` (seluruhnya), `DESIGN.md` (seluruhnya), `docs/KIT_UI.md` (seluruhnya), `agent/konsistensi.json`, `docs/PROGRESS.md`, lalu `agent/last-gate.txt` bila ada.
2. `git status`, `git branch --show-current`, `git log --oneline -n 15`.
3. Bila `agent/last-gate.txt` melaporkan **GAGAL** (tes merah, `route:list` gagal, migrasi tertunda, pelanggaran konsistensi, `npm run build` atau `designmd lint` gagal, `phpunit.xml` tidak aman), perbaiki itu **lebih dulu**. Pelanggaran konsistensi ditulis lengkap dengan berkas:baris — ubah kodenya, jangan mencari celah aturan.
4. Tentukan item pertama yang masih `- [ ]` di PROGRESS. Itu pekerjaanmu. Patuhi urutan tahap (UI kit sebelum halaman peran).

## 2. Siklus kerja per item

1. Cari yang sudah ada (komponen, enum, helper) dan baca kode terkait. Lihat `legacy/` hanya untuk memahami fitur, bukan untuk disalin.
2. Implementasikan sesuai spesifikasi. Tulis tes bersama kodenya.
3. `php artisan test` (boleh `--filter` saat iterasi, lalu seluruh suite sebelum commit) sampai hijau. Bila kamu mengubah view atau CSS/JS: jalankan juga `npm run build` sampai sukses. Periksa bahwa berkas baru tidak melanggar `agent/konsistensi.json`.
4. Bila skema berubah: `php artisan migrate:fresh --seed` pada DB v2 (setelah tes SQLite hijau).
5. `git add -A` lalu `git commit -m "<satu baris>"`.
6. Perbarui `docs/PROGRESS.md`: centang + hash (`git log --oneline -n 1`), `TAHAP_AKTIF`, entri di "Catatan sesi"; commit.
7. Lanjut ke item berikutnya. Jangan berhenti hanya karena satu item selesai.

## 3. Penutupan sesi

Berhenti bila: semua item selesai, atau giliran/konteks hampir habis, atau `BLOKIR`. Sebelum berhenti:
- `git status` bersih (commit semua) dan `php artisan test` hijau; bila tidak dan tak bisa diperbaiki, tuliskan persis yang rusak di "Catatan sesi".
- `docs/PROGRESS.md` mencerminkan keadaan sebenarnya.
- Bila semua item selesai: lengkapi T7.x lalu ubah baris pertama PROGRESS menjadi `STATUS_AGEN: SELESAI`. Runner memeriksa ulang sendiri (tes, konsistensi, tidak ada `- [ ]`); klaim palsu ditolak dan sesi dilanjutkan.
- Jawaban akhir (satu paragraf, Indonesia): item selesai, hasil tes (angka nyata), hambatan. Tanpa pertanyaan.
