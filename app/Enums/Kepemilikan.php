<?php

namespace App\Enums;

use App\Enums\Concerns\PunyaOpsi;

enum Kepemilikan: string
{
    use PunyaOpsi;

    case Pemerintah = 'pemerintah';
    case Swasta = 'swasta';
    case Militer = 'militer';
    case Bumn = 'bumn';

    public function label(): string
    {
        return match ($this) {
            self::Pemerintah => 'Pemerintah',
            self::Swasta => 'Swasta',
            self::Militer => 'Militer',
            self::Bumn => 'BUMN',
        };
    }
}
