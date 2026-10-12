<?php

namespace Tests\Unit\Support\Chart;

use App\Support\Chart\Sparkline;
use PHPUnit\Framework\TestCase;

class SparklineTest extends TestCase
{
    public function test_seri_kurang_dari_dua_nilai_tidak_digambar(): void
    {
        $this->assertSame('', Sparkline::buat([], 'primary'));
        $this->assertSame('', Sparkline::buat([5], 'primary'));
    }

    public function test_svg_berukuran_92x38_dan_memakai_token_warna(): void
    {
        $svg = Sparkline::buat([1, 2, 3], 'chart-mint');

        $this->assertStringContainsString('width="92" height="38" viewBox="0 0 92 38"', $svg);
        $this->assertStringContainsString('aria-hidden="true"', $svg);
        $this->assertStringContainsString('stroke="var(--color-chart-mint)"', $svg);
        $this->assertStringContainsString('fill="var(--color-chart-mint)"', $svg);
        $this->assertStringNotContainsString('#', $svg);
    }

    public function test_jalur_dimulai_dan_diakhiri_pada_titik_pertama_dan_terakhir(): void
    {
        $svg = Sparkline::buat([4, 8, 2, 6], 'primary');

        // Titik pertama x=2 (rumus x = i*(92-4)/(n-1)+2, i=0 -> 2); titik terakhir x=90 (i=n-1 -> 90).
        $this->assertStringContainsString('<path d="M 2.0 ', $svg);
        $this->assertMatchesRegularExpression('/<circle cx="90\.0" cy="[\d.]+" r="3\.5"/', $svg);
    }

    public function test_seri_semua_nol_digambar_sebagai_garis_datar_di_dasar(): void
    {
        $svg = Sparkline::buat([0, 0, 0], 'primary');

        // y = tinggi - 4 = 34.0 untuk semua titik ketika max dipaksa ke 1; titik x tetap mengikuti rumus i*(w-4)/(n-1)+2.
        $this->assertStringContainsString('<path d="M 2.0 34.0 C 9.3 34.0 31.3 34.0 46.0 34.0 C 60.7 34.0 82.7 34.0 90.0 34.0"', $svg);
        $this->assertStringContainsString('<circle cx="90.0" cy="34.0"', $svg);
    }

    public function test_dua_titik_menghasilkan_satu_ruas_kubik(): void
    {
        $svg = Sparkline::buat([0, 10], 'primary');

        $this->assertSame(1, preg_match_all('/ C /', $svg));
    }
}
