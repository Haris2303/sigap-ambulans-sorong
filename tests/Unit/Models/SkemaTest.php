<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SkemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_tabel_inti_tersedia_dengan_kolom_sesuai_rancangan(): void
    {
        $this->assertTrue(Schema::hasColumns('users', [
            'role', 'aktif', 'faskes_id', 'no_telepon', 'sedang_online', 'lat', 'lng', 'lokasi_diperbarui_pada',
        ]));
        $this->assertTrue(Schema::hasColumns('faskes', [
            'nama', 'jenis', 'kepemilikan', 'alamat', 'telepon', 'lat', 'lng', 'aktif',
            'bed_igd_total', 'bed_igd_tersedia', 'bed_rawat_inap_total', 'bed_rawat_inap_tersedia',
            'dokter_umum_tersedia', 'dokter_spesialis_tersedia', 'dokter_spesialis_keterangan',
            'ketersediaan_diperbarui_pada',
        ]));
        $this->assertTrue(Schema::hasColumns('ambulans', ['faskes_id', 'sopir_id', 'plat_nomor', 'jenis', 'merek_model', 'status']));
        $this->assertTrue(Schema::hasColumns('pesanan', [
            'kode', 'user_id', 'nama_pasien', 'no_telepon_kontak', 'jenis_layanan', 'tingkat_darurat',
            'lokasi_jemput', 'lat_jemput', 'lng_jemput', 'faskes_tujuan_id', 'catatan', 'status',
            'ambulans_id', 'sopir_id', 'diverifikasi_oleh_id', 'diverifikasi_pada', 'ditugaskan_pada',
            'diterima_pada', 'berangkat_pada', 'selesai_pada', 'alasan_penolakan', 'alasan_pembatalan',
        ]));
        $this->assertTrue(Schema::hasColumns('pesanan_log', [
            'pesanan_id', 'dari_status', 'ke_status', 'oleh_id', 'peran', 'catatan', 'created_at',
        ]));
    }
}
