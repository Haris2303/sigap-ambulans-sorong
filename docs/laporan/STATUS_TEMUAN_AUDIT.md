# Status temuan audit aplikasi lama

Nomor mengikuti `docs/AUDIT_APLIKASI_LAMA.md` §10. "Tes" menunjuk tes yang membuktikan perbaikan; "Desain" berarti dicegah oleh struktur kode tanpa tes khusus.

| # | Temuan | Status di v2 | Bukti |
|---|---|---|---|
| 1 | Sopir sibuk tidak terdeteksi saat menugaskan | Diperbaiki: `User::sedangBertugas()` dan syarat tanpa pesanan aktif | `PesananWorkflowTest::test_tugaskan_ditolak_bila_sopir_masih_memegang_pesanan`, `PesananAdminTest::test_tugaskan_sopir_yang_sibuk_ditolak` |
| 2 | Penugasan tanpa validasi peran, status, online | Diperbaiki | `test_tugaskan_ditolak_bila_*` (WF), `test_tugaskan_semua_penolakan_mengembalikan_galat_tanpa_mengubah_data` |
| 3 | Akun peran lain dapat diubah lewat id; menghapus diri sendiri | Diperbaiki: binding `{sopir}`/`{petugas}` berfilter peran, tanpa route hapus | `MasterAdminTest::test_binding_peran_akun_peran_lain_404_dan_tanpa_hapus`, `test_dinkes_tidak_bisa_menonaktifkan_diri_sendiri_lewat_pengelolaan_akun` |
| 4 | Balapan saat sopir menerima | Diperbaiki: transaksi dengan `lockForUpdate()` di `PesananWorkflow` | Desain; `test_sopir_lain_tidak_bisa_menerima` (tidak menguji konkurensi sungguhan) |
| 5 | Urutan status tidak dicek | Diperbaiki: mesin status | `test_mulai_perjalanan_dan_selesai_harus_berurutan`, `test_pesanan_final_tidak_bisa_diubah_lagi`, `EnumTest::test_transisi_terbuka_sesuai_tabel_mesin_status` |
| 6 | Penolakan sopir tidak dicatat | Diperbaiki: log dan alasan tersimpan, penugasan dilepas | `test_sopir_menolak_mengembalikan_ke_terverifikasi_dan_membebaskan_sumber_daya`, `AlurPenuhTest::test_sopir_menolak_lalu_ditugaskan_ke_sopir_lain` |
| 7 | Koordinat tidak divalidasi; `aktif` tidak dapat diubah | Diperbaiki | `FaskesAdminTest::test_validasi_faskes`, `test_aktif_membalik_status_dan_tidak_ada_penghapusan` |
| 8 | Validasi ambulans lemah | Diperbaiki: `AmbulansRequest` | `MasterAdminTest::test_validasi_ambulans` |
| 9 | Hapus Faskes menghapus ambulans berantai | Diperbaiki: tanpa route hapus, FK `restrict` pada ambulans | `FaskesAdminTest::test_aktif_membalik_status_dan_tidak_ada_penghapusan` |
| 10 | Sandi seeder hardcode | Dikurangi: sandi demo dari `config('sigap.seed_password')`; pesanan demo hanya di `local` | `SeederTest::test_pesanan_demo_tidak_dibuat_di_luar_local` |
| 11 | Relasi pesanan ke RS lewat nama | Diperbaiki: FK `faskes_tujuan_id` | `SkemaTest::test_tabel_inti_tersedia_dengan_kolom_sesuai_rancangan` |
| 12 | Ambulans tidak terhubung ke pesanan | Diperbaiki: `pesanan.ambulans_id`, status ambulans diatur workflow | `test_tugaskan_mengisi_sumber_daya_dan_ambulans_bertugas` |
| 13 | Dua jalur penugasan tanpa verifikasi Dinkes | Diperbaiki: hanya Dinkes yang menugaskan | `test_tugaskan_hanya_oleh_admin` |
| 14 | Sopir tanpa Faskes melihat semua pesanan | Diperbaiki: sopir hanya melihat pesanan miliknya | `SopirTest::test_penugasan_json_tidak_memuat_pesanan_sopir_lain` |
| 15 | Status `ditolak`/`terverifikasi`/`ditugaskan` tidak ada | Diperbaiki | `EnumTest::test_label_dan_nada_status_pesanan` |
| 16 | Middleware peran tidak terdaftar; `/logout` tanpa `auth`; login tanpa `guest` | Diperbaiki | `OtorisasiMatriksTest::test_logout_hanya_untuk_pengguna_masuk`, `test_route_tamu_hanya_untuk_yang_belum_masuk` |
| 17 | Query di dalam view layout | Diperbaiki: `MenuComposer` | `LayoutTest::test_lencana_pesanan_menunggu_hanya_untuk_dinkes` |
| 18 | CDN tanpa kunci versi; OSRM demo | Sebagian: Leaflet dan Routing Machine dari npm (terkunci); Tailwind lewat build Vite. Rute OSRM demo masih dipakai | Dicatat di `ASUMSI_DAN_KEPUTUSAN.md`; perlu konfirmasi Dinkes |
| 19 | Kode mati | Diperbaiki: proyek baru tanpa Sanctum | Desain |
| 20 | `.env.example` bawaan | Diperbaiki: `APP_NAME="SIGAP Ambulans"` di `.env.example`; `SESSION_DRIVER=database` | Pemeriksaan berkas |
| 21 | Tidak ada jenis layanan, peran Petugas Faskes, ketersediaan | Diperbaiki | FR-04, FR-06, FR-18 sampai FR-20 |
| 22 | Tidak ada notifikasi waktu nyata | Sebagian: polling berkala (admin, sopir, warga). Notifikasi peramban tidak teruji agen | Perlu uji manual (`HASIL_AKHIR.md`) |
| 23 | Tidak ada tes dan seed | Diperbaiki | Seluruh suite; `SeederTest` |
| 24 | Tidak ada detail pesanan dan layar konfirmasi | Diperbaiki | `PesananAdminTest::test_detail_menampilkan_pemohon_lokasi_dan_aksi_sesuai_status`, `PesananWargaTest::test_konfirmasi_menampilkan_ringkasan_dan_tombol_ubah_dan_kirim` |
