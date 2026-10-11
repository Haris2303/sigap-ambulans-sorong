# Pengujian Black-Box

Hasil dari eksekusi `php artisan test` pada 2026-10-11: **418 lulus, 0 gagal** (3427 assertion), database SQLite `:memory:`. Hasil "LULUS" di bawah berasal dari eksekusi itu. Tes tampilan, JavaScript, peta, GPS, polling, dan notifikasi peramban tidak dapat diuji agen (lihat `docs/HASIL_AKHIR.md`).

Kelas: `Auth\AutentikasiTest`, `Warga\PesananWargaTest` (PW), `Admin\PesananAdminTest` (PA), `Services\PesananWorkflowTest` (WF), `Sopir\SopirTest` (SP), `Petugas\PetugasTest` (PT), `AlurPenuhTest` (AP).

| ID | Skenario | Masukan | Hasil diharapkan | Method tes | Hasil |
|---|---|---|---|---|---|
| BB-01 | Login sesuai peran | Akun tiap peran | Dialihkan ke beranda perannya | `AutentikasiTest::test_login_mengalihkan_sesuai_peran` | LULUS |
| BB-02 | Login sandi salah | Sandi keliru | Ditolak dengan galat | `AutentikasiTest::test_login_gagal_dengan_sandi_salah` | LULUS |
| BB-03 | Akun nonaktif | `aktif=false` | Ditolak dengan pesan khusus | `AutentikasiTest::test_akun_nonaktif_ditolak_dengan_pesan_sendiri` | LULUS |
| BB-04 | Pembatasan login | 5 percobaan gagal | Percobaan berikutnya dibatasi | `AutentikasiTest::test_login_dibatasi_setelah_lima_percobaan_gagal` | LULUS |
| BB-05 | Register menyisipkan peran | Isian `role=admin` | Akun tetap Masyarakat | `AutentikasiTest::test_register_tidak_bisa_menyisipkan_peran_lain` | LULUS |
| BB-06 | Tinjau belum membuat pesanan | Form valid ke tinjau | Draf di session, tabel pesanan kosong | PW `test_tinjau_belum_membuat_pesanan` | LULUS |
| BB-07 | Simpan pesanan | Konfirmasi | Pesanan `menunggu`, kode dibuat, draf dibersihkan | PW `test_simpan_membuat_pesanan_lewat_workflow_dan_membersihkan_draf` | LULUS |
| BB-08 | Simpan diulang | Kirim dua kali | Tidak ada pesanan ganda | PW `test_pesanan_ganda_tidak_terjadi_saat_simpan_diulang` | LULUS |
| BB-09 | Rujukan tanpa tujuan | `rujukan` tanpa Faskes | Ditolak validasi | PW `test_rujukan_wajib_faskes_tujuan_dan_faskes_nonaktif_ditolak` | LULUS |
| BB-10 | Pesanan milik orang lain | Warga B membuka pesanan warga A | Ditolak | PW `test_warga_lain_tidak_bisa_melihat_pesanan` | LULUS |
| BB-11 | Batal sebelum penugasan | Pesanan `menunggu`/`terverifikasi` | `dibatalkan` | PW `test_warga_membatalkan_pesanan_menunggu_dan_terverifikasi` | LULUS |
| BB-12 | Batal setelah penugasan | Pesanan `ditugaskan` | Ditolak | PW `test_warga_tidak_bisa_membatalkan_setelah_ditugaskan` | LULUS |
| BB-13 | Lokasi sopir disembunyikan | Status JSON sebelum `diterima` | Tanpa posisi sopir | PW `test_status_json_mengikuti_konvensi_dan_menyembunyikan_lokasi_sebelum_diterima` | LULUS |
| BB-14 | Urutan daftar Dinkes | Pesanan gawat dan biasa | Gawat dulu, lalu terlama | PA `test_daftar_mengurutkan_gawat_dulu_lalu_terlama_dan_status_final_terakhir` | LULUS |
| BB-15 | Verifikasi | Pesanan `menunggu` | `terverifikasi`, pelaku dan waktu tercatat | PA `test_verifikasi` | LULUS |
| BB-16 | Tolak tanpa alasan | Alasan kosong | Ditolak validasi | PA `test_tolak_wajib_alasan` | LULUS |
| BB-17 | Tugaskan | Ambulans tersedia, sopir online satu Faskes | `ditugaskan`, ambulans `bertugas` | PA `test_tugaskan_berhasil` | LULUS |
| BB-18 | Tugaskan sopir beda Faskes | Sopir Faskes lain | Ditolak, data tidak berubah | WF `test_tugaskan_ditolak_bila_sopir_beda_faskes` | LULUS |
| BB-19 | Tugaskan sopir offline | `sedang_online=false` | Ditolak | WF `test_tugaskan_ditolak_bila_sopir_offline` | LULUS |
| BB-20 | Tugaskan ambulans servis | Ambulans `servis` | Ditolak | WF `test_tugaskan_ditolak_bila_ambulans_servis` | LULUS |
| BB-21 | Tugaskan sopir sibuk | Sopir punya pesanan aktif | Ditolak | PA `test_tugaskan_sopir_yang_sibuk_ditolak` | LULUS |
| BB-22 | Saran persalinan | Pesanan persalinan | Puskesmas didahulukan | PA `test_saran_persalinan_mendahulukan_puskesmas` | LULUS |
| BB-23 | Lepas penugasan | Pesanan `ditugaskan` | `terverifikasi`, ambulans `tersedia` | PA `test_lepas_penugasan_mengembalikan_ke_terverifikasi_dan_membebaskan_ambulans` | LULUS |
| BB-24 | Batal oleh Dinkes saat berjalan | Alasan diisi | `dibatalkan`, ambulans dibebaskan | PA `test_batalkan_pesanan_yang_sedang_berjalan_membebaskan_ambulans` | LULUS |
| BB-25 | Sopir menerima, berangkat, selesai | Urutan benar | Status berurutan, ambulans `tersedia` di akhir | SP `test_urutan_penuh_terima_berangkat_selesai` | LULUS |
| BB-26 | Urutan salah | Selesai dari `diterima` | Ditolak dengan galat | SP `test_urutan_salah_ditolak_dengan_pesan_galat` | LULUS |
| BB-27 | Sopir menolak | Alasan diisi | `terverifikasi` | SP `test_tolak_penugasan_wajib_alasan_dan_mengembalikan_ke_terverifikasi` | LULUS |
| BB-28 | Sopir lain mengubah pesanan | Bukan sopir penugasan | Ditolak | SP `test_sopir_lain_tidak_bisa_mengubah_pesanan_bukan_miliknya` | LULUS |
| BB-29 | Offline saat memegang pesanan | Toggle offline | Ditolak | SP `test_tidak_bisa_offline_saat_memegang_pesanan` | LULUS |
| BB-30 | Koordinat tidak valid | Lat di luar rentang | Ditolak | SP `test_kirim_lokasi_menolak_koordinat_tidak_valid` | LULUS |
| BB-31 | Ketersediaan melebihi total | `tersedia > total` | Ditolak | PT `test_tersedia_tidak_boleh_melebihi_total` | LULUS |
| BB-32 | Petugas mengubah Faskes lain | `faskes_id` disisipkan | Diabaikan | PT `test_petugas_tidak_bisa_mengubah_faskes_lain_lewat_isian` | LULUS |
| BB-33 | Status ambulans bertugas | Petugas set `bertugas` | Ditolak | PT `test_status_bertugas_tidak_bisa_diatur_petugas` | LULUS |
| BB-34 | Ambulans Faskes lain | Ubah status ambulans lain | Ditolak | PT `test_ambulans_faskes_lain_tidak_bisa_diubah` | LULUS |
| BB-35 | Ketersediaan tampil publik | Petugas memperbarui | Tampil di `/faskes` | AP `test_petugas_memperbarui_ketersediaan_dan_tampil_di_info_faskes` | LULUS |
| BB-36 | Alur penuh | Pesan sampai selesai lintas peran | Semua status terlewati | AP `test_alur_normal_dari_pesanan_sampai_selesai` | LULUS |
| BB-37 | Sopir menolak lalu sopir lain | Dua sopir | Penugasan ulang berhasil | AP `test_sopir_menolak_lalu_ditugaskan_ke_sopir_lain` | LULUS |
| BB-38 | Jalur ditolak | Dinkes menolak | Alasan tampil ke Masyarakat | AP `test_jalur_ditolak_dinkes_tampil_dengan_alasan_ke_masyarakat` | LULUS |
| BB-39 | Laporan PDF | Unduh | Respons PDF | `LaporanTest::test_unduh_pdf` | LULUS |
| BB-40 | Laporan CSV | Unduh | Header, baris, aman dari rumus | `LaporanTest::test_unduh_csv_berisi_header_dan_baris_serta_aman_dari_rumus` | LULUS |
| BB-41 | Master tanpa hapus | Route destroy | Tidak ada | `FaskesAdminTest::test_aktif_membalik_status_dan_tidak_ada_penghapusan` | LULUS |
| BB-42 | Matriks otorisasi | Semua route × semua peran | Peran lain 403, tamu ke login | `OtorisasiMatriksTest::test_peran_lain_mendapat_403_dan_peran_yang_berhak_lolos`, `test_tamu_diarahkan_ke_login_pada_semua_route_terlindungi` | LULUS |
| BB-43 | Tes asap | Semua route GET per peran | Dapat dibuka | `SmokeRouteTest::test_dinkes_membuka_semua_halaman_admin` dan tiga tes peran lain | LULUS |
| BB-44 | Seeder idempoten | Seed dua kali | Jumlah baris sama | `SeederTest::test_seeder_idempoten_dua_kali_jumlah_baris_sama` | LULUS |
| BB-45 | Konsistensi kode | Pindai aturan `konsistensi.json` | 0 pelanggaran | `KonsistensiTest::test_semua_aturan_konsistensi_terpenuhi` | LULUS |
| BB-46 | Selesai hari ini menurut zona waktu | Pesanan selesai sebelum dan sesudah tengah malam WIT | Hanya yang selesai hari ini (Asia/Jayapura) dihitung | `RingkasanDasborTest::test_selesai_hari_ini_memakai_zona_waktu_aplikasi_asia_jayapura` | LULUS |
| BB-47 | Grafik batang 7 hari | Pesanan di dalam dan di luar rentang | Tujuh hari termasuk hari ini, hari kosong bernilai 0 | `RingkasanDasborTest::test_batang_tujuh_hari_termasuk_hari_ini_dengan_hari_kosong_bernilai_nol` | LULUS |
| BB-48 | Batas query dasbor | Dasbor dengan sedikit dan banyak data | Paling banyak 10 query dan jumlahnya sama | `RingkasanDasborTest::test_seluruh_dasbor_memakai_paling_banyak_sepuluh_query_tanpa_n_plus_satu` | LULUS |
| BB-49 | Kartu statistik dasbor | Data demo kecil | Empat kartu dengan angka, label, keterangan, dan chip | `DashboardTest::test_empat_kartu_statistik_dengan_angka_label_keterangan_dan_ikon` | LULUS |
| BB-50 | Data grafik batang | Data pratinjau (1,0,0,0,0,0,1) dan nilai 0, 1, 2, 3, 7, 10, 11 | Atribut `data-grafik` memuat label dua baris, nilai, hari ini, dan `max` genap minimal 2; `aria-label` menyebut semua nilai; tabel `sr-only` berisi data yang sama | `KomponenGrafikTest::test_batang_menjadi_kanvas_dengan_data_pratinjau_dan_tanpa_svg`, `test_batang_sumbu_y_bilangan_genap_dari_dua`, `test_batang_punya_keterangan_teks_yang_menyebut_semua_nilai`, `test_batang_punya_tabel_data_untuk_pembaca_layar` | LULUS |
| BB-51 | Pencarian pesanan | `?q=` kode atau nama; `%`, `_`, tanda kutip | Hanya kode dan nama pasien; karakter khusus sebagai teks | `PesananAdminTest::test_pencarian_q_mencari_kode_dan_nama_pasien_tetapi_tidak_lokasi`, `test_pencarian_tidak_menganggap_wildcard_dan_tanda_kutip_sebagai_perintah` | LULUS |
| BB-52 | Tab daftar pesanan | `?tab=perlu-tindakan`, `berjalan`, tidak dikenal | Menyaring status; tidak dikenal kembali ke Semua | `PesananAdminTest::test_tab_perlu_tindakan_dan_berjalan_menyaring_status` | LULUS |
| BB-53 | Lonceng pesanan menunggu | Tiga pesanan menunggu | Label dan lencana menyebut 3 | `LayoutTest::test_lonceng_menghitung_pesanan_menunggu_verifikasi` | LULUS |
| BB-54 | Kartu penugasan sopir | Penugasan aktif | Satu kartu besar dengan lokasi, kontak, dan tindakan | `SapuanD6Test::test_dasbor_sopir_menampilkan_penugasan_dalam_satu_kartu_besar` | LULUS |
| BB-55 | Token dan CSS sinkron | `DESIGN.md` dan `design-tokens.css` | Warna, tipe, bayangan, dan PDF sesuai token | `TokenDesainTest::test_setiap_warna_di_design_md_ada_di_css_dan_sebaliknya`, `test_pdf_laporan_hanya_memakai_warna_token_design_md` | LULUS |
| BB-56 | Data grafik donat | Segmen pratinjau (5,2,1,1,1), nilai 0, satu segmen, seri sama | Segmen terbesar dulu, nilai 0 tidak dikirim, persentase dibulatkan, total di tengah, legenda dan tabel `sr-only`; total 0 tanpa legenda | `KomponenGrafikTest::test_donat_menjadi_kanvas_dengan_segmen_berurutan_dan_persentase`, `test_donat_hanya_menampilkan_status_bernilai_lebih_dari_nol_dalam_urutan_terbesar_dulu`, `test_donat_kosong_mengirim_segmen_kosong_dengan_angka_nol`, `test_donat_persentase_dibulatkan` | LULUS |
| BB-57 | Pemuatan Chart.js dan token runtime | Berkas JavaScript grafik, `app.js`, `design-tokens.css` | Impor modular tanpa `chart.js/auto`; bundel dimuat dinamis hanya bila ada kanvas grafik; setiap token yang dibaca JavaScript ada di CSS; tanpa warna mentah di JavaScript | `GrafikChartJsTest::test_hanya_bagian_chart_js_yang_dipakai_yang_diimpor_dan_didaftarkan`, `test_bundel_grafik_hanya_dimuat_di_halaman_yang_punya_kanvas_grafik`, `test_token_yang_dibaca_javascript_ada_di_css_hasil_pembuat_token`, `KonsistensiTest::test_warna_js_menolak_warna_mentah_dan_menerima_pembacaan_token` | LULUS |
