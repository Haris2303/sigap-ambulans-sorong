<?php

namespace App\Enums;

use App\Enums\Concerns\PunyaOpsi;

enum JenisFaskes: string
{
    use PunyaOpsi;

    case Rs = 'rumah_sakit';
    case Puskesmas = 'puskesmas';
    case Klinik = 'klinik';

    public function label(): string
    {
        return match ($this) {
            self::Rs => 'Rumah sakit',
            self::Puskesmas => 'Puskesmas',
            self::Klinik => 'Klinik',
        };
    }
}
