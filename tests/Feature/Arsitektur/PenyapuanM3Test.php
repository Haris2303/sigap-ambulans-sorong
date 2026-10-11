<?php

namespace Tests\Feature\Arsitektur;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\Concerns\MemindaiNamaLama;
use Tests\TestCase;

/**
 * Seluruh view (kecuali pdf/) sudah Material 3: tanpa nama token lama dan tanpa ukuran tombol yang dihapus.
 * Aturan umum untuk semua berkas ada di agent/konsistensi.json (id token-lama); tes ini menjaga sampai aturan itu aktif.
 */
class PenyapuanM3Test extends TestCase
{
    use MemindaiNamaLama;

    /** @return array<int, string> path view relatif terhadap base_path() */
    private function semuaView(): array
    {
        $daftar = [];
        $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views'), RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($iter as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            if ($file->isFile() && str_ends_with($path, '.php') && ! str_contains($path, '/views/pdf/')) {
                $daftar[] = 'resources/views/'.substr($path, strlen(str_replace('\\', '/', resource_path('views'))) + 1);
            }
        }
        sort($daftar);

        return $daftar;
    }

    public function test_tidak_ada_view_yang_memuat_nama_token_lama(): void
    {
        $berkas = $this->semuaView();
        $this->assertGreaterThan(40, count($berkas), 'Daftar view terlalu sedikit; pemindaian mungkin salah folder.');

        $temuan = $this->cariNamaLama($berkas);

        $this->assertSame([], $temuan, "Nama lama masih ada:\n".implode("\n", $temuan));
    }

    public function test_ukuran_tombol_sm_sudah_dihapus_dari_semua_view(): void
    {
        $temuan = [];
        foreach ($this->semuaView() as $path) {
            if (str_contains((string) file_get_contents(base_path($path)), 'ukuran="sm"')) {
                $temuan[] = $path;
            }
        }

        $this->assertSame([], $temuan, "Tombol hanya punya ukuran md dan lg:\n".implode("\n", $temuan));
    }
}
