<?php

namespace Tests\Concerns;

/**
 * Mencari nama token atau kelas pra-Material 3 pada berkas view (docs/KIT_UI.md bagian 10).
 * Dipakai untuk komponen dan layout yang sudah dimigrasikan; daftar berkasnya bertambah tiap langkah (docs/MIGRASI_M3.md).
 */
trait MemindaiNamaLama
{
    /**
     * @param  array<int, string>  $berkas  path relatif terhadap base_path()
     * @return array<int, string> "berkas: nama, nama" untuk tiap berkas yang masih memuat nama lama
     */
    protected function cariNamaLama(array $berkas): array
    {
        $lama = '/(?<![\w-])(?:(?:bg|text|border|divide|ring|outline)-(?:canvas|line|control-border|primary-soft|primary-hover'
            .'|danger|danger-soft|danger-hover|warning-soft|success-soft|neutral-soft|on-surface-muted)'
            .'|text-(?:h[123]|body|body-sm|label|caption|display)|bg-surface|shadow-overlay|font-semibold|w-rail)(?![\w-])/';

        $temuan = [];
        foreach ($berkas as $path) {
            $isi = (string) file_get_contents(base_path($path));
            if (preg_match_all($lama, $isi, $cocok) > 0) {
                $temuan[] = $path.': '.implode(', ', array_unique($cocok[0]));
            }
        }

        return $temuan;
    }
}
