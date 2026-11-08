<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Diurutkan sebelum tabel users (nama berkas lebih awal) karena users.faskes_id merujuk ke sini.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faskes', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('jenis', 20);
            $table->string('kepemilikan', 20)->nullable();
            $table->string('alamat')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->boolean('aktif')->default(true);
            $table->unsignedInteger('bed_igd_total')->default(0);
            $table->unsignedInteger('bed_igd_tersedia')->default(0);
            $table->unsignedInteger('bed_rawat_inap_total')->default(0);
            $table->unsignedInteger('bed_rawat_inap_tersedia')->default(0);
            $table->unsignedInteger('dokter_umum_tersedia')->default(0);
            $table->unsignedInteger('dokter_spesialis_tersedia')->default(0);
            $table->text('dokter_spesialis_keterangan')->nullable();
            $table->timestamp('ketersediaan_diperbarui_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faskes');
    }
};
