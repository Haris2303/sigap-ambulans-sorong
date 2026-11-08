<?php

namespace App\Enums;

use App\Enums\Concerns\PunyaOpsi;

enum JenisAmbulans: string
{
    use PunyaOpsi;

    case Darurat = 'darurat';
    case Transport = 'transport';

    public function label(): string
    {
        return match ($this) {
            self::Darurat => 'Darurat',
            self::Transport => 'Transport',
        };
    }
}
