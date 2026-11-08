<?php

namespace App\Models;

use App\Enums\JenisAmbulans;
use App\Enums\StatusAmbulans;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'ambulans')]
#[Fillable(['faskes_id', 'sopir_id', 'plat_nomor', 'jenis', 'merek_model', 'status'])]
class Ambulans extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'jenis' => JenisAmbulans::class,
            'status' => StatusAmbulans::class,
        ];
    }

    public function faskes(): BelongsTo
    {
        return $this->belongsTo(Faskes::class);
    }

    /** Sopir tetap ambulans ini. */
    public function sopirTetap(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sopir_id');
    }

    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class);
    }

    public function scopeTersedia(Builder $query): void
    {
        $query->where('status', StatusAmbulans::Tersedia->value);
    }
}
