<?php

namespace App\Models;

use App\Enums\JenisFaskes;
use App\Enums\Kepemilikan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'faskes')]
#[Fillable([
    'nama', 'jenis', 'kepemilikan', 'alamat', 'telepon', 'lat', 'lng', 'aktif',
    'bed_igd_total', 'bed_igd_tersedia', 'bed_rawat_inap_total', 'bed_rawat_inap_tersedia',
    'dokter_umum_tersedia', 'dokter_spesialis_tersedia', 'dokter_spesialis_keterangan',
    'ketersediaan_diperbarui_pada',
])]
class Faskes extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'jenis' => JenisFaskes::class,
            'kepemilikan' => Kepemilikan::class,
            'aktif' => 'boolean',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'ketersediaan_diperbarui_pada' => 'datetime',
        ];
    }

    public function ambulans(): HasMany
    {
        return $this->hasMany(Ambulans::class);
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeAktif(Builder $query): void
    {
        $query->where('aktif', true);
    }

    public function punyaKoordinat(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function belumDiperbarui(): bool
    {
        return $this->ketersediaan_diperbarui_pada === null;
    }

    /** Ketersediaan terakhir diperbarui lebih lama dari batas config('sigap.data_basi_jam'). */
    public function dataLama(): bool
    {
        $waktu = $this->ketersediaan_diperbarui_pada;

        return $waktu !== null && $waktu->lt(now()->subHours((int) config('sigap.data_basi_jam')));
    }
}
