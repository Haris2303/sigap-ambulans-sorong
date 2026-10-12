<?php

namespace App\Enums;

use App\Enums\Concerns\PunyaOpsi;

/**
 * Filter periode dasbor Dinkes (docs/RETEMA_B.md 8.1 butir 4): ?periode=hari|7|30, nilai tak dikenal jatuh ke 7.
 * Berlaku untuk grafik garis, donat, dan kartu "Selesai"; tidak untuk tab "Pesanan terbaru".
 */
enum PeriodeDasbor: string
{
    use PunyaOpsi;

    case Hari = 'hari';
    case Tujuh = '7';
    case TigaPuluh = '30';

    public function label(): string
    {
        return match ($this) {
            self::Hari => 'Hari ini',
            self::Tujuh => '7 hari',
            self::TigaPuluh => '30 hari',
        };
    }

    /** Label huruf kecil untuk subjudul kartu ("hari ini", "7 hari", "30 hari"). */
    public function labelKecil(): string
    {
        return $this === self::Hari ? 'hari ini' : $this->label();
    }

    /** Sebutan periode sebanding sebelumnya untuk chip delta: "kemarin", "7 hari sebelumnya", "30 hari sebelumnya". */
    public function labelSebelumnya(): string
    {
        return match ($this) {
            self::Hari => 'kemarin',
            self::Tujuh => '7 hari sebelumnya',
            self::TigaPuluh => '30 hari sebelumnya',
        };
    }

    /** Jumlah hari dalam jendela, termasuk hari ini. */
    public function jumlahHari(): int
    {
        return match ($this) {
            self::Hari => 1,
            self::Tujuh => 7,
            self::TigaPuluh => 30,
        };
    }

    /** Pengelompokan grafik garis: per jam untuk hari ini, per hari untuk 7/30 hari. */
    public function perJam(): bool
    {
        return $this === self::Hari;
    }

    /** ?periode=... dari query string; nilai tak dikenal atau kosong jatuh ke 7 hari (bawaan). */
    public static function dariQuery(?string $nilai): self
    {
        return self::tryFrom((string) $nilai) ?? self::Tujuh;
    }
}
