<?php

namespace App\Enums;

use App\Enums\Concerns\PunyaOpsi;

/** Tab penyaring daftar pesanan (nilai dipakai sebagai ?tab= di URL). Satu sumber untuk dasbor dan daftar. */
enum TabPesanan: string
{
    use PunyaOpsi;

    case Semua = 'semua';
    case PerluTindakan = 'perlu-tindakan';
    case Berjalan = 'berjalan';

    public function label(): string
    {
        return match ($this) {
            self::Semua => 'Semua',
            self::PerluTindakan => 'Perlu tindakan',
            self::Berjalan => 'Berjalan',
        };
    }

    /** Apakah pesanan berstatus ini tampil di tab: perlu tindakan = menunggu dan terverifikasi; berjalan = memegang ambulans dan sopir. */
    public function mencakup(PesananStatus $status): bool
    {
        return match ($this) {
            self::Semua => true,
            self::PerluTindakan => in_array($status, [PesananStatus::Menunggu, PesananStatus::Terverifikasi], true),
            self::Berjalan => $status->memegangSumberDaya(),
        };
    }
}
