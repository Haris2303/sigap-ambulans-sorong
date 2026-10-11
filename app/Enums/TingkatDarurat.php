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

    public function nada(): string
    {
        return $this === self::Gawat ? 'bahaya' : 'netral';
    }
}
