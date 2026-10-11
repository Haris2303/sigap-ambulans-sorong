<?php

namespace Tests\Feature\Arsitektur;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Menjalankan aturan agent/konsistensi.json dari PHP (padanan pemindai runner).
 */
class KonsistensiTest extends TestCase
{
    public function test_semua_aturan_konsistensi_terpenuhi(): void
    {
        $aturan = $this->muatAturan();
        $this->assertNotEmpty($aturan, 'Tidak ada aturan konsistensi yang terbaca.');

        $pelanggaran = [];
        foreach ($aturan as $a) {
            foreach ($this->periksa($a) as $temuan) {
                $pelanggaran[] = '['.$a['id'].'] '.$temuan.' : '.$a['pesan'];
            }
        }

        $this->assertSame([], $pelanggaran, "Pelanggaran konsistensi:\n".implode("\n", $pelanggaran));
    }

    public function test_aturan_larang_benar_benar_mendeteksi_pelanggaran(): void
    {
        $istilah = collect($this->muatAturan())->firstWhere('id', 'istilah-baku');
        $this->assertNotNull($istilah);
        $this->assertSame(1, preg_match($this->regex($istilah), 'class Supir {}'));
        $this->assertSame(0, preg_match($this->regex($istilah), 'class Sopir {}'));

        $emoji = collect($this->muatAturan())->firstWhere('id', 'emoji');
        $this->assertSame(1, preg_match($this->regex($emoji), "ikon \u{1F600}"));
        $this->assertSame(0, preg_match($this->regex($emoji), 'teks biasa'));
    }

    /** @return array<int, array<string, mixed>> */
    private function muatAturan(): array
    {
        $json = json_decode((string) file_get_contents(base_path('agent/konsistensi.json')), true);

        return $json['aturan'] ?? [];
    }

    /** @param array<string, mixed> $aturan */
    private function regex(array $aturan): string
    {
        $pola = $aturan['pola_php'] ?? $aturan['pola'];

        return '~'.str_replace('~', '\~', $pola).'~u';
    }

    /**
     * @param  array<string, mixed>  $aturan
     * @return array<int, string>
     */
    private function periksa(array $aturan): array
    {
        $regex = $this->regex($aturan);
        $temuan = [];

        foreach ($this->berkas($aturan) as $path => $isi) {
            if ($aturan['tipe'] === 'awal') {
                if (! $this->awalCocok($isi, $regex)) {
                    $temuan[] = $path.':1';
                }

                continue;
            }

            foreach (preg_split('/\R/u', $isi) ?: [] as $no => $baris) {
                if (preg_match($regex, $baris) === 1) {
                    $temuan[] = $path.':'.($no + 1);
                }
            }
        }

        return $temuan;
    }

    private function awalCocok(string $isi, string $regex): bool
    {
        foreach (preg_split('/\R/u', $isi) ?: [] as $baris) {
            if (trim($baris) === '' || preg_match('/^\s*\{\{--.*--\}\}\s*$/', $baris) === 1) {
                continue;
            }

            return preg_match($regex, $baris) === 1;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $aturan
     * @return \Generator<string, string>
     */
    private function berkas(array $aturan): \Generator
    {
        $dasar = str_replace('\\', '/', base_path()).'/';
        $kecuali = $aturan['kecuali'] ?? [];

        foreach ($aturan['folder'] as $folder) {
            $akar = base_path($folder);
            if (! is_dir($akar)) {
                continue;
            }

            $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($akar, RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($iter as $file) {
                if (! $file->isFile()) {
                    continue;
                }
                $path = str_replace('\\', '/', $file->getPathname());
                $relatif = str_starts_with($path, $dasar) ? substr($path, strlen($dasar)) : $path;

                $cocokEkstensi = false;
                foreach ($aturan['ekstensi'] as $ext) {
                    if (str_ends_with($relatif, $ext)) {
                        $cocokEkstensi = true;
                    }
                }
                if (! $cocokEkstensi) {
                    continue;
                }

                foreach ($kecuali as $k) {
                    if (preg_match('~'.str_replace('~', '\~', $k).'~', $relatif) === 1) {
                        continue 2;
                    }
                }

                yield $relatif => (string) file_get_contents($path);
            }
        }
    }
}
