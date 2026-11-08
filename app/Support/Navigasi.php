<?php

namespace App\Support;

/**
 * Tautan navigasi ke lokasi jemput untuk sopir. Templat ada di config/sigap.php (`navigasi`): {lat} dan {lng} diisi koordinat,
 * {alamat} diisi alamat teks yang di-URL-encode. Tanpa koordinat dipakai templat berakhiran _alamat.
 */
final class Navigasi
{
    /**
     * @param  array<string, string>|null  $templat  bawaan: config('sigap.navigasi')
     * @return array{aplikasi: string, web: string} aplikasi = aplikasi peta bawaan perangkat; web = cadangan di peramban
     */
    public static function tautan(float|int|string|null $lat, float|int|string|null $lng, string $alamat, ?array $templat = null): array
    {
        $templat ??= (array) config('sigap.navigasi');
        $punyaTitik = is_numeric($lat) && is_numeric($lng);

        $isi = $punyaTitik
            ? ['{lat}' => self::angka($lat), '{lng}' => self::angka($lng)]
            : ['{alamat}' => rawurlencode(trim($alamat))];
        $akhiran = $punyaTitik ? '' : '_alamat';

        return [
            'aplikasi' => strtr((string) $templat['aplikasi'.$akhiran], $isi),
            'web' => strtr((string) $templat['web'.$akhiran], $isi),
        ];
    }

    /** Koordinat dengan paling banyak tujuh desimal tanpa nol di belakang ("-0.88", "131.2558"). */
    private static function angka(float|int|string $nilai): string
    {
        return rtrim(rtrim(number_format((float) $nilai, 7, '.', ''), '0'), '.');
    }
}
