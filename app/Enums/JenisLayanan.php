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

    public function deskripsi(): string
    {
        return match ($this) {
            self::Darurat => 'Kecelakaan, sesak napas, atau keadaan lain yang mengancam nyawa.',
            self::Rujukan => 'Memindahkan pasien ke Faskes lain. Faskes tujuan wajib dipilih.',
            self::Persalinan => 'Ibu hamil yang akan atau sedang bersalin.',
        };
    }

    /** Faskes tujuan wajib diisi untuk jenis ini. */
    public function wajibFaskesTujuan(): bool
    {
        return $this === self::Rujukan;
    }

    /** Ikon Lucide untuk kotak ikon x-ui.opsi-baris (docs/RETEMA_B.md 8.5). */
    public function ikon(): string
    {
        return match ($this) {
            self::Darurat => 'ambulance',
            self::Rujukan => 'building-2',
            self::Persalinan => 'users',
        };
    }

    /** Jenis ambulans yang disarankan (hanya urutan, bukan larangan). */
    public function jenisAmbulansSaran(): JenisAmbulans
    {
        return $this === self::Darurat ? JenisAmbulans::Darurat : JenisAmbulans::Transport;
    }

    /** Warna donat arah B (nama token; DESIGN.md: seri grafik hanya primary, tertiary, dan chart-*). */
    public function warnaChart(): string
    {
        return match ($this) {
            self::Darurat => 'chart-coral',
            self::Rujukan => 'chart-sky',
            self::Persalinan => 'chart-amber',
        };
    }
}
