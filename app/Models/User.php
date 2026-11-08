<?php

namespace App\Models;

use App\Enums\PesananStatus;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Table(name: 'users')]
#[Fillable([
    'name', 'email', 'password', 'role', 'aktif', 'faskes_id', 'no_telepon',
    'sedang_online', 'lat', 'lng', 'lokasi_diperbarui_pada',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'aktif' => 'boolean',
            'sedang_online' => 'boolean',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'lokasi_diperbarui_pada' => 'datetime',
        ];
    }

    public function faskes(): BelongsTo
    {
        return $this->belongsTo(Faskes::class);
    }

    /** Ambulans tetap milik sopir ini. */
    public function ambulansTetap(): HasOne
    {
        return $this->hasOne(Ambulans::class, 'sopir_id');
    }

    public function pesananDipesan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'user_id');
    }

    public function pesananTugas(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'sopir_id');
    }

    public function scopeBerperan(Builder $query, Role $role): void
    {
        $query->where('role', $role->value);
    }

    public function scopeSopir(Builder $query): void
    {
        $query->where('role', Role::Sopir->value);
    }

    public function scopePetugas(Builder $query): void
    {
        $query->where('role', Role::PetugasFaskes->value);
    }

    public function scopeAktif(Builder $query): void
    {
        $query->where('aktif', true);
    }

    public function punyaPeran(Role ...$peran): bool
    {
        return in_array($this->role, $peran, true);
    }

    /** Sopir sedang memegang pesanan yang belum final. */
    public function sedangBertugas(): bool
    {
        return $this->pesananTugas()->whereIn('status', array_column(PesananStatus::aktif(), 'value'))->exists();
    }
}
