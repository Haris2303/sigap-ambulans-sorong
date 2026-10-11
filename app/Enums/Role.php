<?php

namespace App\Enums;

use App\Enums\Concerns\PunyaOpsi;

enum Role: string
{
    use PunyaOpsi;

    case Warga = 'warga';
    case Admin = 'admin';
    case PetugasFaskes = 'petugas_faskes';
    case Sopir = 'sopir';

    public function label(): string
    {
        return match ($this) {
            self::Warga => 'Masyarakat',
            self::Admin => 'Dinkes',
            self::PetugasFaskes => 'Petugas Faskes',
            self::Sopir => 'Sopir',
        };
    }

    /** Nama route dasbor/beranda peran ini. */
    public function ruteBeranda(): string
    {
        return match ($this) {
            self::Warga => 'warga.beranda',
            self::Admin => 'admin.dashboard',
            self::PetugasFaskes => 'petugas.dashboard',
            self::Sopir => 'sopir.dashboard',
        };
    }
}
