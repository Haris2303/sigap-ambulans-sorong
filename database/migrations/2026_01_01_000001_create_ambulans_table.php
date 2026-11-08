<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ambulans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faskes_id')->constrained('faskes')->restrictOnDelete();
            $table->foreignId('sopir_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('plat_nomor', 20)->unique();
            $table->string('jenis', 20);
            $table->string('merek_model')->nullable();
            $table->string('status', 20)->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ambulans');
    }
};
