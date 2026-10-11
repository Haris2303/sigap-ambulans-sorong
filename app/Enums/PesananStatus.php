<?php

namespace App\Enums;

use App\Enums\Concerns\PunyaOpsi;

enum PesananStatus: string
{
    use PunyaOpsi;

    case Menunggu = 'menunggu';
    case Terverifikasi = 'terverifikasi';
    case Ditugaskan = 'ditugaskan';
    case Diterima = 'diterima';
    case DalamPerjalanan = 'dalam_perjalanan';
    case Selesai = 'selesai';
    case Ditolak = 'ditolak';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu verifikasi',
            self::Terverifikasi => 'Terverifikasi',
            self::Ditugaskan => 'Ditugaskan',
            self::Diterima => 'Diterima sopir',
            self::DalamPerjalanan => 'Dalam perjalanan',
            self::Selesai => 'Selesai',
            self::Ditolak => 'Ditolak',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /** Nada lencana: netral|info|peringatan|sukses|bahaya. */
    public function nada(): string
    {
        return match ($this) {
            self::Menunggu => 'peringatan',
            self::Terverifikasi, self::Ditugaskan, self::Diterima, self::DalamPerjalanan => 'info',
            self::Selesai => 'sukses',
            self::Ditolak => 'bahaya',
            self::Dibatalkan => 'netral',
        };
    }

    /** Warna tetap per status pada grafik donat (nama token; docs/DESAIN_DASBOR_MATERIAL.md 5.12). Status baru wajib diberi warna. */
    public function warnaGrafik(): string
    {
        return match ($this) {
            self::Selesai => 'success',
            self::Dibatalkan => 'outline',
            self::Ditolak => 'error',
            self::Menunggu => 'warning',
            self::Terverifikasi => 'primary',
            self::Ditugaskan, self::Diterima, self::DalamPerjalanan => 'chart-sky',
        };
    }

    public function final(): bool
    {
        return in_array($this, [self::Selesai, self::Ditolak, self::Dibatalkan], true);
    }

    /** Belum ada ambulans dan sopir: pesanan masih menunggu (waktu tunggu dihitung pada status ini). */
    public function menungguPenugasan(): bool
    {
        return in_array($this, [self::Menunggu, self::Terverifikasi], true);
    }

    /** Pesanan memegang ambulans dan sopir pada status ini. */
    public function memegangSumberDaya(): bool
    {
        return in_array($this, [self::Ditugaskan, self::Diterima, self::DalamPerjalanan], true);
    }

    /**
     * Status tujuan yang terbuka dari status ini (gabungan semua pelaku, RANCANGAN bagian 5).
     *
     * @return array<int, self>
     */
    public function transisiTerbuka(): array
    {
        return match ($this) {
            self::Menunggu => [self::Terverifikasi, self::Ditolak, self::Dibatalkan],
            self::Terverifikasi => [self::Ditugaskan, self::Dibatalkan],
            self::Ditugaskan => [self::Diterima, self::Terverifikasi, self::Dibatalkan],
            self::Diterima => [self::DalamPerjalanan, self::Dibatalkan],
            self::DalamPerjalanan => [self::Selesai, self::Dibatalkan],
            self::Selesai, self::Ditolak, self::Dibatalkan => [],
        };
    }

    public function bolehKe(self $tujuan): bool
    {
        return in_array($tujuan, $this->transisiTerbuka(), true);
    }

    /** @return array<int, self> status yang belum final */
    public static function aktif(): array
    {
        return array_values(array_filter(self::cases(), fn (self $s) => ! $s->final()));
    }
}
