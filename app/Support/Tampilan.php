<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Pemetaan nada dan bentuk ke kelas token (DESIGN.md, Material 3): satu sumber untuk lencana, peringatan, titik, chip, dan isian.
 */
class Tampilan
{
    public const NADA = ['netral', 'info', 'peringatan', 'sukses', 'bahaya'];

    /** Enam tone arah B (docs/RETEMA_B.md 4.3): sumber tunggal warna lencana, chip ikon, dan grafik. */
    public const TONE = ['primary', 'warning', 'success', 'error', 'tertiary', 'neutral'];

    public const LABEL_ISIAN = 'mb-1 block text-label-l text-on-surface-variant';

    public const BANTUAN_ISIAN = 'mt-1 text-label-m text-on-surface-variant';

    public const GALAT_ISIAN = 'mt-1 text-label-m text-error';

    /** Kartu arah B: latar putih, sudut 24px, bayangan card, tanpa batas (panel, tabel, kosong, rincian). */
    public const KARTU = 'bg-surface-container-lowest rounded-xl shadow-card';

    /** Pasangan warna avatar (crc32(nama) % 5), tidak membawa makna (docs/RETEMA_B.md 4.1, 6.7). */
    private const WARNA_AVATAR = [
        'bg-primary-container text-on-primary-container',
        'bg-error-container text-on-error-container',
        'bg-success-container text-on-success-container',
        'bg-tertiary-container text-on-tertiary-container',
        'bg-warning-container text-on-warning-container',
    ];

    /**
     * Lencana waktu tunggu: nada dibaca dari atribut data-nada supaya JavaScript cukup mengganti atribut itu saat waktu berjalan.
     * Pasangan kelasnya sama dengan kelasLembut() untuk netral, peringatan, dan bahaya (dijaga TampilanTest); ditulis utuh agar terbaca pemindai Tailwind.
     */
    public const WAKTU_TUNGGU = 'data-[nada=netral]:bg-secondary-container data-[nada=netral]:text-on-secondary-container'
        .' data-[nada=peringatan]:bg-warning-container data-[nada=peringatan]:text-on-warning-container'
        .' data-[nada=bahaya]:bg-error-container data-[nada=bahaya]:text-on-error-container';

    /**
     * Container tonal + teks di atasnya (lencana, peringatan). Menerima nada Indonesia lama (info, peringatan, sukses,
     * bahaya, netral) dan tone arah B (primary, warning, success, error, tertiary, neutral; docs/RETEMA_B.md 4.3) sebagai
     * sinonim yang sama persis, supaya PesananStatus::tone() dapat dipakai langsung sebagai prop `nada`.
     */
    public static function kelasLembut(string $nada): string
    {
        return match ($nada) {
            'info', 'primary' => 'bg-primary-container text-on-primary-container',
            'peringatan', 'warning' => 'bg-warning-container text-on-warning-container',
            'sukses', 'success' => 'bg-success-container text-on-success-container',
            'bahaya', 'error' => 'bg-error-container text-on-error-container',
            'tertiary' => 'bg-tertiary-container text-on-tertiary-container',
            default => 'bg-secondary-container text-on-secondary-container', // netral, neutral
        };
    }

    /**
     * Pasangan container dan ikon untuk chip ikon (docs/RETEMA_B.md 6.6). Primary dan warning memakai teks token
     * polos (bukan on-container) sesuai tabel arah B; ukuran chip (40/44px) menunggu R4 bersama kartu statistik
     * (x-ui.stat, x-ui.tile) yang memakai size="lg" ini, supaya keduanya pindah sekali bersama tanpa pecah di tengah jalan.
     */
    public static function kelasChip(string $tone): string
    {
        return match ($tone) {
            'primary' => 'bg-primary-container text-on-primary-container',
            'warning' => 'bg-warning-container text-on-warning-container',
            'success' => 'bg-success-container text-on-success-container',
            'error' => 'bg-error-container text-on-error-container',
            'tertiary' => 'bg-tertiary-container text-on-tertiary-container',
            'neutral' => 'bg-secondary-container text-on-secondary-container',
            default => throw new InvalidArgumentException('x-ui.icon-chip: nada "'.$tone.'" tidak dikenal (primary, warning, success, error, tertiary, neutral).'),
        };
    }

    /** Inisial avatar: huruf pertama dari dua kata pertama (kapital), "?" bila nama kosong. */
    public static function inisial(string $nama): string
    {
        $kata = preg_split('/\s+/u', trim($nama), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($kata === []) {
            return '?';
        }

        return implode('', array_map(fn (string $k) => mb_strtoupper(mb_substr($k, 0, 1)), array_slice($kata, 0, 2)));
    }

    /** Warna avatar (docs/RETEMA_B.md 4.3, 6.7): crc32(nama) % 5, tidak membawa makna; nama selalu ada di sebelahnya. */
    public static function warnaAvatar(string $nama): string
    {
        return self::WARNA_AVATAR[crc32($nama) % 5];
    }

    /** Isian arah B (isian, pilihan, area teks; docs/RETEMA_B.md 6.3): batas outline, fokus primary + cincin, galat error. */
    public static function kelasIsian(bool $galat): string
    {
        $dasar = 'block w-full min-h-12 rounded-md border bg-surface-container-lowest px-3.5 text-body-m text-on-surface'
            .' placeholder:text-on-surface-variant focus-visible:outline-hidden focus-visible:ring-3'
            .' disabled:border-on-surface/12 disabled:text-on-surface/38';

        return $dasar.' '.($galat
            ? 'border-error hover:border-on-error-container focus-visible:border-error focus-visible:ring-error/20'
            : 'border-outline hover:border-on-surface focus-visible:border-primary focus-visible:ring-primary/20');
    }

    public static function ikonNada(string $nada): string
    {
        return match ($nada) {
            'sukses' => 'circle-check',
            'peringatan' => 'triangle-alert',
            'bahaya' => 'circle-x',
            default => 'info',
        };
    }
}
