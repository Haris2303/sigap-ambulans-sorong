<?php

namespace App\Models;

use App\Enums\JenisLayanan;
use App\Enums\PesananStatus;
use App\Enums\TingkatDarurat;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'pesanan')]
#[Fillable([
    'user_id', 'nama_pasien', 'no_telepon_kontak', 'jenis_layanan', 'tingkat_darurat',
    'lokasi_jemput', 'lat_jemput', 'lng_jemput', 'faskes_tujuan_id', 'catatan',
])]
class Pesanan extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'jenis_layanan' => JenisLayanan::class,
            'tingkat_darurat' => TingkatDarurat::class,
            'status' => PesananStatus::class,
            'lat_jemput' => 'decimal:7',
            'lng_jemput' => 'decimal:7',
            'diverifikasi_pada' => 'datetime',
            'ditugaskan_pada' => 'datetime',
            'diterima_pada' => 'datetime',
            'berangkat_pada' => 'datetime',
            'selesai_pada' => 'datetime',
        ];
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function faskesTujuan(): BelongsTo
    {
        return $this->belongsTo(Faskes::class, 'faskes_tujuan_id');
    }

    public function ambulans(): BelongsTo
    {
        return $this->belongsTo(Ambulans::class);
    }

    public function sopir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sopir_id');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh_id');
    }

    public function log(): HasMany
    {
        return $this->hasMany(PesananLog::class)->orderBy('created_at')->orderBy('id');
    }

    /** Pesanan yang belum final. */
    public function scopeAktif(Builder $query): void
    {
        $query->whereIn('status', array_column(PesananStatus::aktif(), 'value'));
    }

    public function scopeBerstatus(Builder $query, PesananStatus ...$status): void
    {
        $query->whereIn('status', array_column($status, 'value'));
    }

    /** Gawat dulu, lalu yang paling lama menunggu. */
    public function scopePrioritas(Builder $query): void
    {
        $query->orderByRaw('CASE tingkat_darurat WHEN ? THEN 0 ELSE 1 END', [TingkatDarurat::Gawat->value])
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
