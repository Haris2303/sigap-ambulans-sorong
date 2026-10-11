<?php

namespace App\Support;

/**
 * Pemetaan nada ke kelas token (DESIGN.md): satu sumber untuk lencana, peringatan, titik, dan angka.
 */
class Tampilan
{
    public const NADA = ['netral', 'info', 'peringatan', 'sukses', 'bahaya'];

    /** Latar lembut + teks pekat (lencana, peringatan). */
    public static function kelasLembut(string $nada): string
    {
        return match ($nada) {
            'info' => 'bg-primary-soft text-primary',
            'peringatan' => 'bg-warning-soft text-warning',
            'sukses' => 'bg-success-soft text-success',
            'bahaya' => 'bg-danger-soft text-danger',
            default => 'bg-neutral-soft text-on-surface-muted',
        };
    }

    /** Warna teks saja (angka ringkasan). */
    public static function kelasTeks(?string $nada): string
    {
        return match ($nada) {
            'info' => 'text-primary',
            'peringatan' => 'text-warning',
            'sukses' => 'text-success',
            'bahaya' => 'text-danger',
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
            'bahaya' => 'bg-danger',
            default => 'bg-on-surface-muted',
        };
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
