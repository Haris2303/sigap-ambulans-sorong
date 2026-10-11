<?php

namespace App\Enums;

use App\Enums\Concerns\PunyaOpsi;

enum JenisLayanan: string
{
    use PunyaOpsi;

    case Darurat = 'darurat';
    case Rujukan = 'rujukan';
    case Persalinan = 'persalinan';

    public function label(): string
    {
        return match ($this) {
            self::Darurat => 'Darurat',
            self::Rujukan => 'Rujukan',
            self::Persalinan => 'Persalinan',
        };
    }

    /** Faskes tujuan wajib diisi untuk jenis ini. */
    public function wajibFaskesTujuan(): bool
    {
        return $this === self::Rujukan;
    }

    /** Jenis ambulans yang disarankan (hanya urutan, bukan larangan). */
    public function jenisAmbulansSaran(): JenisAmbulans
    {
        return $this === self::Darurat ? JenisAmbulans::Darurat : JenisAmbulans::Transport;
    }
}
