# Kamus data

Sumber: `docs/RANCANGAN_ALUR.md` §4 dan migrasi di `database/migrations`. Kolom bernilai terbatas bertipe string dan di-cast ke Enum.

## users
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint | Kunci utama |
| name, email | string | `email` unik |
| email_verified_at | timestamp null | |
| password | string | Ter-hash |
| role | string(20), indeks | `warga`, `admin`, `petugas_faskes`, `sopir` (`Role`) |
| aktif | bool | Akun nonaktif tidak dapat masuk |
| faskes_id | FK null | Faskes petugas atau sopir |
| no_telepon | string(20) null | |
| sedang_online | bool | Hanya bermakna untuk sopir |
| lat, lng | decimal(10,7) null | Posisi sopir |
| lokasi_diperbarui_pada | timestamp null | |

## faskes
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint | |
| nama | string unik | |
| jenis | string(20) | `rumah_sakit`, `puskesmas`, `klinik` (`JenisFaskes`) |
| kepemilikan | string(20) null | `pemerintah`, `swasta`, `militer`, `bumn` (`Kepemilikan`) |
| alamat, telepon | string null | |
| lat, lng | decimal(10,7) null | |
| aktif | bool | |
| bed_igd_total, bed_igd_tersedia | uint | tersedia ≤ total |
| bed_rawat_inap_total, bed_rawat_inap_tersedia | uint | tersedia ≤ total |
| dokter_umum_tersedia, dokter_spesialis_tersedia | uint | |
| dokter_spesialis_keterangan | text null | |
| ketersediaan_diperbarui_pada | timestamp null | Null berarti "Belum diperbarui" |

## ambulans
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint | |
| faskes_id | FK (restrict) | |
| sopir_id | FK null → users | Sopir tetap |
| plat_nomor | string unik | |
| jenis | string(20) | `darurat`, `transport` (`JenisAmbulans`) |
| merek_model | string null | |
| status | string(20) | `tersedia`, `bertugas`, `servis` (`StatusAmbulans`); `bertugas` hanya oleh workflow |

## pesanan
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint | |
| kode | string(20) unik | `SGP-` + id 6 digit |
| user_id | FK | Pemohon |
| nama_pasien, no_telepon_kontak | string | |
| jenis_layanan | string | `darurat`, `rujukan`, `persalinan` (`JenisLayanan`) |
| tingkat_darurat | string | `biasa`, `gawat` (`TingkatDarurat`) |
| lokasi_jemput | string | |
| lat_jemput, lng_jemput | decimal(10,7) null | |
| faskes_tujuan_id | FK null | Wajib untuk rujukan |
| catatan | text null | |
| status | string(30), indeks | `PesananStatus` |
| ambulans_id, sopir_id | FK null | Terisi saat `ditugaskan` |
| diverifikasi_oleh_id | FK null → users | |
| diverifikasi_pada, ditugaskan_pada, diterima_pada, berangkat_pada, selesai_pada | timestamp null | |
| alasan_penolakan, alasan_pembatalan | text null | |

## pesanan_log
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint | |
| pesanan_id | FK (cascade) | |
| dari_status | string null | Null pada pembuatan |
| ke_status | string | |
| oleh_id | FK null → users | |
| peran | string(20) null | |
| catatan | text null | |
| created_at | timestamp | |
