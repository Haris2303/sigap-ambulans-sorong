<?php

namespace Tests\Unit\Support\Chart;

use App\Support\Chart\BarGeometry;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Geometri grafik batang (docs/DESAIN_DASBOR_MATERIAL.md bagian 5.11) diuji terhadap nilai pratinjau (docs/referensi). */
class BarGeometryTest extends TestCase
{
    public function test_data_pratinjau_menghasilkan_geometri_yang_sama_dengan_pratinjau(): void
    {
        $g = BarGeometry::buat([1, 0, 0, 0, 0, 0, 1], 6);

        $this->assertSame('0 0 640 346', $g['viewBox']);
        $this->assertSame(2, $g['axisMax']);

        // Kisi: y = 20 (axisMax), 160 (separuh), 300 (nol, garis dasar); teks sumbu di x = 38 dengan y + 4.
        $this->assertSame(
            [['20', '2', '24', false], ['160', '1', '164', false], ['300', '0', '304', true]],
            array_map(fn (array $k) => [$k['y'], $k['label'], $k['labelY'], $k['dasar']], $g['grid']),
        );
        $this->assertSame('38', $g['sumbuX']);
        $this->assertSame(['48', '624'], [$g['garisKiri'], $g['garisKanan']]);

        // Pusat slot dan sisi kiri batang (pusat dikurangi 18).
        $this->assertSame(['89.14', '171.43', '253.71', '336', '418.29', '500.57', '582.86'], array_column($g['bars'], 'center'));
        $this->assertSame(['71.14', '153.43', '235.71', '318', '400.29', '482.57', '564.86'], array_column($g['bars'], 'x'));

        // Batang bernilai 1 dari axisMax 2: tinggi 140, ujung atas y = 160, label nilai di y = 150.
        foreach ([0, 6] as $i) {
            $batang = $g['bars'][$i];
            $this->assertSame('36', $batang['width']);
            $this->assertSame('140', $batang['height']);
            $this->assertSame('160', $batang['top']);
            $this->assertSame('150', $batang['valueY']);
            $this->assertNull($batang['stub']);
        }
        $this->assertSame('M71.14 300V168a8 8 0 0 1 8-8h20a8 8 0 0 1 8 8v132Z', $g['bars'][0]['path']);
        $this->assertSame('M564.86 300V168a8 8 0 0 1 8-8h20a8 8 0 0 1 8 8v132Z', $g['bars'][6]['path']);

        // Nilai 0: batang rintisan setinggi 3 di y = 297 (rx 1,5), label nilai di y = 289.
        foreach ([1, 2, 3, 4, 5] as $i) {
            $batang = $g['bars'][$i];
            $this->assertNull($batang['path']);
            $this->assertSame(['x' => $batang['x'], 'y' => '297', 'width' => '36', 'height' => '3', 'rx' => '1.5'], $batang['stub']);
            $this->assertSame('289', $batang['valueY']);
        }

        // Hanya indeks yang disorot (hari ini) berwarna primary; label hari di y = 320 dan tanggal di y = 336.
        $this->assertSame([false, false, false, false, false, false, true], array_column($g['bars'], 'highlight'));
        $this->assertSame('320', $g['hariY']);
        $this->assertSame('336', $g['tanggalY']);
    }

    public static function nilaiMaksimum(): array
    {
        return [
            'semua nol' => [[0, 0, 0], 2],
            'kosong' => [[], 2],
            'satu' => [[1], 2],
            'dua' => [[2], 2],
            'tiga menjadi empat' => [[3, 1], 4],
            'empat tetap empat' => [[4], 4],
            'tujuh menjadi delapan' => [[7, 2], 8],
            'seratus tiga puluh lima' => [[135], 136],
            'seribu' => [[1000], 1000],
        ];
    }

    #[DataProvider('nilaiMaksimum')]
    public function test_sumbu_maksimum_adalah_bilangan_genap_terkecil_minimal_dua(array $nilai, int $axisMax): void
    {
        $this->assertSame($axisMax, BarGeometry::buat($nilai)['axisMax']);
    }

    public function test_kisi_mengikuti_sumbu_maksimum(): void
    {
        $g = BarGeometry::buat([7, 3]);

        $this->assertSame(8, $g['axisMax']);
        $this->assertSame(['8', '4', '0'], array_column($g['grid'], 'label'));
    }

    public function test_batang_penuh_menyentuh_garis_atas_dan_tinggi_sebanding_nilai(): void
    {
        $g = BarGeometry::buat([4, 2, 1]);

        $this->assertSame('280', $g['bars'][0]['height']);
        $this->assertSame('20', $g['bars'][0]['top']);
        $this->assertSame('10', $g['bars'][0]['valueY']);
        $this->assertSame('140', $g['bars'][1]['height']);
        $this->assertSame('70', $g['bars'][2]['height']);
        $this->assertSame('230', $g['bars'][2]['top']);
    }

    public function test_batang_sangat_pendek_tidak_menghasilkan_jalur_terbalik(): void
    {
        $g = BarGeometry::buat([1, 100]);
        $pendek = $g['bars'][0];

        // axisMax 100: tinggi 2,8px, kurang dari radius 8; radius mengecil mengikuti tinggi sehingga tidak ada koordinat terbalik.
        $this->assertSame('2.8', $pendek['height']);
        $this->assertMatchesRegularExpression('/^M[\d.]+ 300V300a2\.8 2\.8 0 0 1 2\.8-2\.8h30\.4a2\.8 2\.8 0 0 1 2\.8 2\.8v0Z$/', $pendek['path']);
    }

    public function test_lebar_batang_mengecil_bila_slot_sempit(): void
    {
        $g = BarGeometry::buat(array_fill(0, 20, 1));

        $this->assertCount(20, $g['bars']);
        $this->assertLessThan(36, (float) $g['bars'][0]['width']);
        $this->assertGreaterThan(0, (float) $g['bars'][0]['width']);
    }

    public function test_sorotan_di_luar_rentang_diabaikan(): void
    {
        $this->assertNotContains(true, array_column(BarGeometry::buat([1, 2, 3], 9)['bars'], 'highlight'));
        $this->assertNotContains(true, array_column(BarGeometry::buat([1, 2, 3], -1)['bars'], 'highlight'));
        $this->assertNotContains(true, array_column(BarGeometry::buat([1, 2, 3])['bars'], 'highlight'));
    }

    public function test_tanpa_data_hanya_kisi(): void
    {
        $g = BarGeometry::buat([]);

        $this->assertSame([], $g['bars']);
        $this->assertCount(3, $g['grid']);
    }

    public function test_nilai_negatif_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BarGeometry::buat([1, -1]);
    }

    public function test_kunci_asosiatif_diperlakukan_sebagai_urutan(): void
    {
        $g = BarGeometry::buat(['Sen' => 1, 'Sel' => 3]);

        $this->assertSame([1, 3], array_column($g['bars'], 'value'));
        $this->assertSame(4, $g['axisMax']);
    }
}
