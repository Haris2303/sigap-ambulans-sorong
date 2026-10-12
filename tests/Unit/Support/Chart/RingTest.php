<?php

namespace Tests\Unit\Support\Chart;

use App\Support\Chart\Ring;
use PHPUnit\Framework\TestCase;

class RingTest extends TestCase
{
    public function test_persen_0_sampai_100(): void
    {
        $this->assertSame(0.0, Ring::persen(0, 24));
        $this->assertSame(1.0, Ring::persen(24, 24));
        $this->assertEqualsWithDelta(0.5, Ring::persen(12, 24), 0.0001);
    }

    public function test_total_0_menghasilkan_persen_0_dan_cincin_hampir_kosong(): void
    {
        $this->assertSame(0.0, Ring::persen(0, 0));
        $this->assertEqualsWithDelta(0.1, Ring::panjangIsi(0, 0), 0.0001);
        $this->assertFalse(Ring::menipis(0, 0));
    }

    public function test_panjang_isi_tidak_pernah_kurang_dari_0_1(): void
    {
        $this->assertEqualsWithDelta(0.1, Ring::panjangIsi(0, 100), 0.0001);
    }

    public function test_panjang_isi_100_persen_sama_dengan_keliling(): void
    {
        $this->assertEqualsWithDelta(Ring::keliling(), Ring::panjangIsi(24, 24), 0.0001);
        $this->assertEqualsWithDelta(163.36, Ring::keliling(), 0.01);
    }

    public function test_ambang_menipis_di_bawah_12_persen(): void
    {
        // 1/14 = 7,14% < 12%: menipis.
        $this->assertTrue(Ring::menipis(1, 14));
        // 3/24 = 12,5% >= 12%: tidak menipis.
        $this->assertFalse(Ring::menipis(3, 24));
        // Tepat di ambang (12%): tidak menipis (pembandingnya "kurang dari").
        $this->assertFalse(Ring::menipis(12, 100));
        $this->assertTrue(Ring::menipis(11, 100));
    }

    public function test_persen_dipotong_ke_0_sampai_1(): void
    {
        $this->assertSame(1.0, Ring::persen(30, 24));
    }
}
