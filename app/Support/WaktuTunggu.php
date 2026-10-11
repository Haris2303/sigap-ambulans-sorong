<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Waktu tunggu pesanan yang belum ditugaskan (status Menunggu dan Terverifikasi): lama sejak pesanan dibuat, teks ringkasnya,
 * dan nada lencana menurut ambang di config/sigap.php (`waktu_tunggu`). Aturan teks dan nada yang sama dihitung ulang di klien
 * oleh resources/js/waktu-tunggu.js; ubah keduanya bersamaan.
 */
final class WaktuTunggu
{
    /** Detik sejak $dibuat sampai $sekarang (tidak pernah negatif). */
    public static function detik(CarbonInterface $dibuat, ?CarbonInterface $sekarang = null): int
    {
        $sekarang ??= now();

        return max(0, $sekarang->getTimestamp() - $dibuat->getTimestamp());
    }

    /** Menit penuh yang sudah berlalu. */
    public static function menit(CarbonInterface $dibuat, ?CarbonInterface $sekarang = null): int
    {
        return intdiv(self::detik($dibuat, $sekarang), 60);
    }

    /** @return array{peringatan: int, bahaya: int} ambang dalam menit */
    public static function ambang(): array
    {
        return [
            'peringatan' => (int) config('sigap.waktu_tunggu.peringatan_menit', 5),
            'bahaya' => (int) config('sigap.waktu_tunggu.bahaya_menit', 10),
        ];
    }

    /** netral di bawah ambang peringatan, peringatan mulai ambang peringatan, bahaya mulai ambang bahaya. */
    public static function nada(int $menit, ?array $ambang = null): string
    {
        $ambang ??= self::ambang();

        return match (true) {
            $menit >= $ambang['bahaya'] => 'bahaya',
            $menit >= $ambang['peringatan'] => 'peringatan',
            default => 'netral',
        };
    }

    /** "Baru dibuat", "Menunggu 12 mnt", "Menunggu 1 j 5 mnt", "Menunggu 2 hari". */
    public static function teks(int $menit): string
    {
        if ($menit < 1) {
            return 'Baru dibuat';
        }
        if ($menit < 60) {
            return 'Menunggu '.$menit.' mnt';
        }
        if ($menit < 1440) {
            $sisa = $menit % 60;

            return 'Menunggu '.intdiv($menit, 60).' j'.($sisa > 0 ? ' '.$sisa.' mnt' : '');
        }

        return 'Menunggu '.intdiv($menit, 1440).' hari';
    }
}
