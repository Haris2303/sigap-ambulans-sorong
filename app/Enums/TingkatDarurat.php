<?php

namespace App\Enums;

use App\Enums\Concerns\PunyaOpsi;

enum TingkatDarurat: string
{
    use PunyaOpsi;

    case Biasa = 'biasa';
    case Gawat = 'gawat';

    public function label(): string
    {
        return match ($this) {
            self::Biasa => 'Biasa',
            self::Gawat => 'Gawat',
        };
    }

    public function deskripsi(): string
    {
        return match ($this) {
            self::Biasa => 'Pasien dalam keadaan stabil.',
            self::Gawat => 'Pasien dalam bahaya dan butuh penanganan segera.',
        };
    }

    public function nada(): string
    {
        return $this === self::Gawat ? 'bahaya' : 'netral';
    }
}
