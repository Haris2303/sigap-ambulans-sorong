<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique()->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->string('nama_pasien');
            $table->string('no_telepon_kontak', 20);
            $table->string('jenis_layanan', 20);
            $table->string('tingkat_darurat', 20);
            $table->string('lokasi_jemput');
            $table->decimal('lat_jemput', 10, 7)->nullable();
            $table->decimal('lng_jemput', 10, 7)->nullable();
            $table->foreignId('faskes_tujuan_id')->nullable()->constrained('faskes')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->string('status', 30)->default('menunggu')->index();
            $table->foreignId('ambulans_id')->nullable()->constrained('ambulans')->nullOnDelete();
            $table->foreignId('sopir_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('diverifikasi_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamp('ditugaskan_pada')->nullable();
            $table->timestamp('diterima_pada')->nullable();
            $table->timestamp('berangkat_pada')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->text('alasan_pembatalan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
