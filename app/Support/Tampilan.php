<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Pemetaan nada dan bentuk ke kelas token (DESIGN.md, Material 3): satu sumber untuk lencana, peringatan, titik, chip, dan isian.
 */
class Tampilan
{
    public const NADA = ['netral', 'info', 'peringatan', 'sukses', 'bahaya'];

    public const LABEL_ISIAN = 'mb-1 block text-label-l text-on-surface-variant';

    public const BANTUAN_ISIAN = 'mt-1 text-body-s text-on-surface-variant';

    public const GALAT_ISIAN = 'mt-1 text-body-s text-error';

    /** Kartu dasbor: latar putih, sudut 16px, bayangan card, tanpa batas (panel, kartu statistik putih). */
    public const KARTU = 'bg-surface-container-lowest rounded-lg shadow-card';

    /** Container tonal + teks di atasnya (lencana, peringatan). */
    public static function kelasLembut(string $nada): string
    {
        return match ($nada) {
            'info' => 'bg-primary-container text-on-primary-container',
            'peringatan' => 'bg-warning-container text-on-warning-container',
            'sukses' => 'bg-success-container text-on-success-container',
            'bahaya' => 'bg-error-container text-on-error-container',
            default => 'bg-surface-container-highest text-on-surface-variant',
        };
    }

    /** Warna isi (titik linimasa). */
    public static function kelasLatar(?string $nada): string
    {
        return match ($nada) {
            'info' => 'bg-primary',
            'peringatan' => 'bg-warning',
            'sukses' => 'bg-success',
            'bahaya' => 'bg-error',
            default => 'bg-on-surface-variant',
        };
    }

    /** Pasangan container dan on-container untuk chip ikon (primary, warning, success, error). */
    public static function kelasChip(string $tone): string
    {
        return match ($tone) {
            'primary' => 'bg-primary-container text-on-primary-container',
            'warning' => 'bg-warning-container text-on-warning-container',
            'success' => 'bg-success-container text-on-success-container',
            'error' => 'bg-error-container text-on-error-container',
            default => throw new InvalidArgumentException('x-ui.icon-chip: nada "'.$tone.'" tidak dikenal (primary, warning, success, error).'),
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

    /** Isian outlined M3 (isian, pilihan, area teks): batas Outline, fokus 2px Primary, galat Error. */
    public static function kelasIsian(bool $galat): string
    {
        $dasar = 'block w-full min-h-14 rounded-xs border bg-surface-container-lowest px-4 text-body-l text-on-surface'
            .' placeholder:text-on-surface-variant focus-visible:outline-1 focus-visible:-outline-offset-1'
            .' disabled:border-on-surface/12 disabled:text-on-surface/38';

        return $dasar.' '.($galat
            ? 'border-error hover:border-on-error-container focus-visible:border-error focus-visible:outline-error'
            : 'border-outline hover:border-on-surface focus-visible:border-primary focus-visible:outline-primary');
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
