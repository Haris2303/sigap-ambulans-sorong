<?php

namespace Tests\Feature\Arsitektur;

use Tests\TestCase;

/**
 * Menjaga pasangan kontras wajib token warna (agent/kontras.json, docs/RETEMA_B.md 4.8). Rasio kontras WCAG 2 dihitung dari
 * front matter DESIGN.md. Bila gagal: perbaiki nilai token di DESIGN.md; jangan menghapus pasangan atau menurunkan batas.
 */
class KontrasTokenTest extends TestCase
{
    public function test_rumus_kontras_sesuai_wcag(): void
    {
        $this->assertEqualsWithDelta(21.0, $this->rasio('#000000', '#FFFFFF'), 0.001);
        $this->assertEqualsWithDelta(1.0, $this->rasio('#1368CE', '#1368CE'), 0.001);
        $this->assertEqualsWithDelta(4.54, $this->rasio('#767676', '#FFFFFF'), 0.005);
        $this->assertEqualsWithDelta($this->rasio('#FFFFFF', '#767676'), $this->rasio('#767676', '#FFFFFF'), 0.000001, 'Urutan depan dan latar tidak mengubah rasio.');
    }

    public function test_semua_pasangan_wajib_memenuhi_batas(): void
    {
        $warna = $this->warna();
        $kontras = $this->kontras();
        $gagal = [];

        foreach (['teks', 'bukan_teks'] as $jenis) {
            $minimal = (float) $kontras[$jenis]['minimal'];
            foreach ($kontras[$jenis]['pasangan'] as [$depan, $latar]) {
                foreach ([$depan, $latar] as $nama) {
                    $this->assertArrayHasKey($nama, $warna, 'Token di agent/kontras.json tidak ada di DESIGN.md: '.$nama);
                    $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $warna[$nama], 'Kontras warna beralfa tidak dapat dihitung: '.$nama);
                }

                $rasio = $this->rasio($warna[$depan], $warna[$latar]);
                if ($rasio < $minimal) {
                    $gagal[] = sprintf('%s pada %s: %.2f:1, minimal %s:1 (%s)', $depan, $latar, $rasio, $minimal, $jenis);
                }
            }
        }

        $this->assertSame([], $gagal, "Pasangan kontras di bawah batas:\n".implode("\n", $gagal));
    }

    public function test_batas_dan_pasangan_terendah_tetap_dijaga(): void
    {
        $kontras = $this->kontras();

        $this->assertSame(4.5, (float) $kontras['teks']['minimal']);
        $this->assertSame(3.0, (float) $kontras['bukan_teks']['minimal']);
        $this->assertGreaterThanOrEqual(30, count($kontras['teks']['pasangan']), 'Pasangan teks dari docs/RETEMA_B.md 4.8 tidak boleh berkurang.');
        $this->assertGreaterThanOrEqual(7, count($kontras['bukan_teks']['pasangan']), 'Pasangan bukan teks dari docs/RETEMA_B.md 4.8 tidak boleh berkurang.');

        // Nilai terendah yang lolos (4,52; 4,61; 4,59; 4,70): perubahan token kecil pun bisa menjatuhkannya.
        foreach ([['on-tertiary', 'tertiary'], ['primary', 'primary-container'], ['error', 'surface'], ['on-success-container', 'success-container']] as $pasangan) {
            $this->assertContains($pasangan, $kontras['teks']['pasangan'], 'Pasangan terendah wajib dijaga: '.implode(' pada ', $pasangan));
        }
    }

    /** @return array<string, string> */
    private function warna(): array
    {
        $dokumen = str_replace("\r\n", "\n", (string) file_get_contents(base_path('DESIGN.md')));
        $this->assertSame(1, preg_match('/^---\n(.*?)\n---\n/s', $dokumen, $cocok), 'Front matter DESIGN.md tidak ditemukan.');
        $this->assertSame(1, preg_match('/^colors:\n((?:[ ]{2}.*\n?)+)/m', $cocok[1], $blok), 'Blok colors tidak ditemukan.');

        $warna = [];
        foreach (explode("\n", rtrim($blok[1])) as $baris) {
            if (preg_match('/^[ ]{2}([\w-]+): "?(#[0-9A-Fa-f]{6,8})"?$/', $baris, $kolom) === 1) {
                $warna[$kolom[1]] = $kolom[2];
            }
        }
        $this->assertNotEmpty($warna);

        return $warna;
    }

    /** @return array<string, mixed> */
    private function kontras(): array
    {
        return json_decode((string) file_get_contents(base_path('agent/kontras.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    /** Luminans relatif WCAG 2 dari #RRGGBB. */
    private function luminans(string $heks): float
    {
        [$r, $g, $b] = array_map(function (string $dua): float {
            $kanal = hexdec($dua) / 255;

            return $kanal <= 0.04045 ? $kanal / 12.92 : (($kanal + 0.055) / 1.055) ** 2.4;
        }, str_split(substr($heks, 1, 6), 2));

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    private function rasio(string $depan, string $latar): float
    {
        $a = $this->luminans($depan);
        $b = $this->luminans($latar);

        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    }
}
