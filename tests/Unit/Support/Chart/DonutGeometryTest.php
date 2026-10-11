<?php

namespace Tests\Unit\Support\Chart;

use App\Support\Chart\DonutGeometry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/** Geometri grafik donat (docs/DESAIN_DASBOR_MATERIAL.md bagian 5.12) diuji terhadap nilai pratinjau (docs/referensi). */
class DonutGeometryTest extends TestCase
{
    /** @param  array<string, int>  $nilai  label => nilai */
    private function segmen(array $nilai): array
    {
        $hasil = [];
        foreach ($nilai as $label => $v) {
            $hasil[] = ['label' => (string) $label, 'value' => $v, 'color' => 'primary'];
        }

        return $hasil;
    }

    public function test_data_pratinjau_menghasilkan_dasharray_dan_dashoffset_yang_sama_dengan_pratinjau(): void
    {
        $g = DonutGeometry::buat($this->segmen(['Selesai' => 5, 'Dibatalkan' => 2, 'Ditolak' => 1, 'Terverifikasi' => 1, 'Dalam perjalanan' => 1]));

        $this->assertSame('0 0 160 160', $g['viewBox']);
        $this->assertSame(['80', '80', '56', '18'], [$g['cx'], $g['cy'], $g['r'], $g['strokeWidth']]);
        $this->assertSame('351.86', $g['circumference']);
        $this->assertSame(10, $g['total']);
        $this->assertFalse($g['kosong']);

        $this->assertSame(
            ['172.93 178.93', '67.37 284.49', '32.19 319.67', '32.19 319.67', '32.19 319.67'],
            array_column($g['segments'], 'dasharray'),
        );
        $this->assertSame(['0', '-175.93', '-246.3', '-281.49', '-316.67'], array_column($g['segments'], 'dashoffset'));
        $this->assertSame([50, 20, 10, 10, 10], array_column($g['segments'], 'percent'));
        $this->assertSame(['Selesai', 'Dibatalkan', 'Ditolak', 'Terverifikasi', 'Dalam perjalanan'], array_column($g['segments'], 'label'));
    }

    public function test_urutan_nilai_terbesar_dulu_dan_seri_sama_mengikuti_urutan_masukan(): void
    {
        $g = DonutGeometry::buat($this->segmen(['A' => 1, 'B' => 5, 'C' => 1, 'D' => 2]));

        $this->assertSame(['B', 'D', 'A', 'C'], array_column($g['segments'], 'label'));
    }

    public function test_segmen_bernilai_nol_tidak_digambar_dan_total_menghitung_sisanya(): void
    {
        $g = DonutGeometry::buat($this->segmen(['Selesai' => 3, 'Ditolak' => 0, 'Dibatalkan' => 1]));

        $this->assertSame(4, $g['total']);
        $this->assertSame(['Selesai', 'Dibatalkan'], array_column($g['segments'], 'label'));
        $this->assertSame([75, 25], array_column($g['segments'], 'percent'));
    }

    public function test_satu_segmen_penuh_tanpa_celah(): void
    {
        $g = DonutGeometry::buat($this->segmen(['Selesai' => 7]));

        $this->assertCount(1, $g['segments']);
        $this->assertSame('351.86 0', $g['segments'][0]['dasharray']);
        $this->assertSame('0', $g['segments'][0]['dashoffset']);
        $this->assertSame(100, $g['segments'][0]['percent']);
    }

    public function test_total_nol_menghasilkan_cincin_kosong(): void
    {
        foreach ([[], $this->segmen(['Selesai' => 0, 'Ditolak' => 0])] as $masukan) {
            $g = DonutGeometry::buat($masukan);

            $this->assertTrue($g['kosong']);
            $this->assertSame(0, $g['total']);
            $this->assertSame([], $g['segments']);
        }
    }

    public function test_persentase_dibulatkan_dan_total_boleh_di_atas_atau_di_bawah_100(): void
    {
        $tiga = DonutGeometry::buat($this->segmen(['A' => 1, 'B' => 1, 'C' => 1]));
        $this->assertSame([33, 33, 33], array_column($tiga['segments'], 'percent'));
        $this->assertSame(99, array_sum(array_column($tiga['segments'], 'percent')));

        $delapan = DonutGeometry::buat($this->segmen(['A' => 1, 'B' => 1, 'C' => 1, 'D' => 1, 'E' => 1, 'F' => 1, 'G' => 1, 'H' => 1]));
        $this->assertSame(array_fill(0, 8, 13), array_column($delapan['segments'], 'percent'));
        $this->assertSame(104, array_sum(array_column($delapan['segments'], 'percent')));
    }

    public function test_segmen_sangat_kecil_tetap_terlihat_dan_tidak_melewati_slotnya(): void
    {
        $g = DonutGeometry::buat($this->segmen(['Besar' => 999, 'Kecil' => 1]));
        [$panjang, $sisa] = array_map('floatval', explode(' ', $g['segments'][1]['dasharray']));

        $bagian = 2 * M_PI * 56 / 1000;
        $this->assertGreaterThan(0, $panjang);
        $this->assertLessThanOrEqual($bagian + 0.01, $panjang, 'Panjang tidak boleh melebihi bagian segmen.');
        $this->assertEqualsWithDelta(2 * M_PI * 56, $panjang + $sisa, 0.02);
    }

    public function test_celah_antar_segmen_tiga_px_untuk_segmen_cukup_besar(): void
    {
        $g = DonutGeometry::buat($this->segmen(['A' => 1, 'B' => 1]));
        [$panjang] = array_map('floatval', explode(' ', $g['segments'][0]['dasharray']));

        $this->assertEqualsWithDelta(M_PI * 56 - 3, $panjang, 0.01);
    }

    public function test_nilai_negatif_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DonutGeometry::buat($this->segmen(['A' => 3, 'B' => -1]));
    }

    public function test_segmen_tanpa_label_atau_warna_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DonutGeometry::buat([['value' => 1]]);
    }
}
