<?php

namespace App\Enums;

use App\Enums\Concerns\PunyaOpsi;

enum StatusAmbulans: string
{
    use PunyaOpsi;

    case Tersedia = 'tersedia';
    case Bertugas = 'bertugas';
    case Servis = 'servis';

    public function label(): string
    {
        return match ($this) {
            self::Tersedia => 'Tersedia',
            self::Bertugas => 'Bertugas',
            self::Servis => 'Servis',
        };
    }

    public function nada(): string
    {
        return match ($this) {
            self::Tersedia => 'sukses',
            self::Bertugas => 'info',
            self::Servis => 'peringatan',
        };
    }

    /** Status yang boleh disunting manual (bertugas hanya oleh workflow). */
    public static function opsiManual(): array
    {
        return [
            self::Tersedia->value => self::Tersedia->label(),
            self::Servis->value => self::Servis->label(),
        ];
    }
}
