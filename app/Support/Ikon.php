<?php

namespace App\Support;

use InvalidArgumentException;

/** Membaca ikon Lucide dari resources/icons dan memasang atribut tampilan baku. */
class Ikon
{
    /** @var array<string, string> isi di dalam tag svg, per nama ikon */
    private static array $cache = [];

    public static function tersedia(string $nama): bool
    {
        return preg_match('/^[a-z0-9-]+$/', $nama) === 1 && is_file(self::path($nama));
    }

    public static function render(string $nama, int $ukuran = 20, string $kelas = ''): string
    {
        if (! self::tersedia($nama)) {
            throw new InvalidArgumentException("Ikon \"{$nama}\" tidak ada di daftar ikon (docs/KIT_UI.md bagian 6).");
        }

        $isi = self::$cache[$nama] ??= self::bacaIsi($nama);

        return '<svg xmlns="http://www.w3.org/2000/svg" class="'.e(trim('shrink-0 '.$kelas)).'"'
            .' width="'.$ukuran.'" height="'.$ukuran.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
            .' stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            .$isi.'</svg>';
    }

    private static function bacaIsi(string $nama): string
    {
        $svg = (string) file_get_contents(self::path($nama));
        if (preg_match('/<svg\b[^>]*>(.*)<\/svg>/s', $svg, $cocok) !== 1) {
            throw new InvalidArgumentException("Berkas ikon \"{$nama}\" tidak valid.");
        }

        return trim($cocok[1]);
    }

    private static function path(string $nama): string
    {
        return resource_path('icons/'.$nama.'.svg');
    }
}
