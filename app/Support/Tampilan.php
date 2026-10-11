<?php

namespace App\Support;

/**
 * Pemetaan nada dan bentuk ke kelas token (DESIGN.md, Material 3): satu sumber untuk lencana, peringatan, titik, angka, dan isian.
 */
class Tampilan
{
    public const NADA = ['netral', 'info', 'peringatan', 'sukses', 'bahaya'];

    public const LABEL_ISIAN = 'mb-1 block text-label-l text-on-surface-variant';

    public const BANTUAN_ISIAN = 'mt-1 text-body-s text-on-surface-variant';

    public const GALAT_ISIAN = 'mt-1 text-body-s text-error';

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

    /** Warna teks saja (angka ringkasan). */
    public static function kelasTeks(?string $nada): string
    {
        return match ($nada) {
            'info' => 'text-primary',
            'peringatan' => 'text-warning',
            'sukses' => 'text-success',
            'bahaya' => 'text-error',
            default => 'text-on-surface',
        };
    }

    /** Warna isi (titik linimasa, batang grafik). */
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
