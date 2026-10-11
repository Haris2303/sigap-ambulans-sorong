<?php

namespace Tests\Feature\Arsitektur;

use Illuminate\View\ViewException;
use Tests\TestCase;

/**
 * Tes render grafik SVG sisi server (docs/DESAIN_DASBOR_MATERIAL.md bagian 5.11 dan 5.12): x-ui.chart.bar dan x-ui.chart.donut,
 * dengan data pratinjau (docs/referensi): batang 1,0,0,0,0,0,1 dan donat 5,2,1,1,1.
 */
class KomponenGrafikTest extends TestCase
{
    private function html(string $blade, array $data = []): string
    {
        return (string) $this->blade($blade, $data);
    }

    /** @return array<string, mixed> */
    private function dataBatang(): array
    {
        return [
            'labels' => ['Sen 05/10', 'Sel 06/10', 'Rab 07/10', 'Kam 08/10', 'Jum 09/10', 'Sab 10/10', 'Min 11/10'],
            'values' => [1, 0, 0, 0, 0, 0, 1],
        ];
    }

    private function batang(array $tambahan = []): string
    {
        return $this->html(
            '<x-ui.chart.bar :labels="$labels" :values="$values" :highlight="6" title="Grafik batang pesanan per hari" unit="pesanan" />',
            $tambahan + $this->dataBatang(),
        );
    }

    /** @return array<int, array{label: string, value: int, color: string}> */
    private function segmenPratinjau(): array
    {
        return [
            ['label' => 'Selesai', 'value' => 5, 'color' => 'success'],
            ['label' => 'Dibatalkan', 'value' => 2, 'color' => 'outline'],
            ['label' => 'Ditolak', 'value' => 1, 'color' => 'error'],
            ['label' => 'Terverifikasi', 'value' => 1, 'color' => 'primary'],
            ['label' => 'Dalam perjalanan', 'value' => 1, 'color' => 'chart-sky'],
        ];
    }

    private function donat(array $segments, string $tambahan = ''): string
    {
        return $this->html(
            '<x-ui.chart.donut :segments="$segments" center-label="pesanan" title="Diagram cincin status 10 pesanan terbaru" '.$tambahan.' />',
            ['segments' => $segments],
        );
    }

    // ---------- batang ----------

    public function test_batang_menjadi_svg_dengan_geometri_pratinjau(): void
    {
        $html = $this->batang();

        $this->assertStringContainsString('viewBox="0 0 640 346"', $html);
        $this->assertStringContainsString('role="img"', $html);

        // Wadah bergulir horizontal; SVG selebar penuh dengan lebar minimum 480px.
        $this->assertStringContainsString('overflow-x-auto', $html);
        $this->assertStringContainsString('block h-auto w-full min-w-120', $html);

        // Kisi: dua garis tipis dan garis dasar outline-variant; teks sumbu rata kanan di x = 38.
        $this->assertMatchesRegularExpression('/<line x1="48" y1="20" x2="624" y2="20"[^>]*surface-container-high/', $html);
        $this->assertMatchesRegularExpression('/<line x1="48" y1="160" x2="624" y2="160"[^>]*surface-container-high/', $html);
        $this->assertMatchesRegularExpression('/<line x1="48" y1="300" x2="624" y2="300"[^>]*stroke-outline-variant/', $html);
        foreach (['24' => '2', '164' => '1', '304' => '0'] as $y => $teks) {
            $this->assertMatchesRegularExpression('/<text x="38" y="'.$y.'" text-anchor="end"[^>]*fill-on-surface-variant[^>]*text-body-s[^>]*>'.$teks.'<\/text>/', $html);
        }

        // Batang bernilai 1: jalur persis pratinjau; hari biasa chart-sky, hari ini primary.
        $this->assertMatchesRegularExpression('~<path d="M71.14 300V168a8 8 0 0 1 8-8h20a8 8 0 0 1 8 8v132Z"[^>]*fill-chart-sky~', $html);
        $this->assertMatchesRegularExpression('~<path d="M564.86 300V168a8 8 0 0 1 8-8h20a8 8 0 0 1 8 8v132Z"[^>]*fill-primary~', $html);
        $this->assertSame(2, substr_count($html, '<path d="M'));

        // Nilai nol: lima batang rintisan outline-variant setinggi 3 di y = 297.
        $this->assertSame(5, substr_count($html, '<rect '));
        foreach (['153.43', '235.71', '318', '400.29', '482.57'] as $x) {
            $this->assertMatchesRegularExpression('/<rect x="'.preg_quote($x, '/').'" y="297" width="36" height="3" rx="1.5"[^>]*fill-outline-variant/', $html);
        }
    }

    public function test_batang_menampilkan_nilai_dan_label_hari_dan_tanggal(): void
    {
        $html = $this->batang();

        // Label nilai: 150 untuk nilai 1, 289 untuk nilai 0.
        $this->assertMatchesRegularExpression('/<text x="89.14" y="150" text-anchor="middle"[^>]*fill-on-surface[^>]*text-label-l[^>]*>1<\/text>/', $html);
        $this->assertMatchesRegularExpression('/<text x="582.86" y="150" text-anchor="middle"[^>]*>1<\/text>/', $html);
        $this->assertSame(5, preg_match_all('/<text x="[\d.]+" y="289" text-anchor="middle"[^>]*>0<\/text>/', $html));

        // Label hari di y = 320 dan tanggal di y = 336, dua baris per hari.
        $this->assertMatchesRegularExpression('/<text x="89.14" y="320" text-anchor="middle"[^>]*fill-on-surface-variant[^>]*>Sen<\/text>/', $html);
        $this->assertMatchesRegularExpression('/<text x="89.14" y="336" text-anchor="middle"[^>]*fill-on-surface-variant[^>]*>05\/10<\/text>/', $html);

        // Hari ini berbobot 500 dan berwarna primary.
        $this->assertMatchesRegularExpression('/<text x="582.86" y="320" text-anchor="middle"[^>]*font-medium[^>]*fill-primary[^>]*>Min<\/text>/', $html);
        $this->assertMatchesRegularExpression('/<text x="582.86" y="336" text-anchor="middle"[^>]*font-medium[^>]*fill-primary[^>]*>11\/10<\/text>/', $html);
    }

    public function test_batang_punya_keterangan_teks_yang_menyebut_semua_nilai(): void
    {
        $html = $this->batang();

        $this->assertMatchesRegularExpression('/aria-label="Grafik batang pesanan per hari: Sen 05\/10 1 pesanan, Sel 06\/10 0 pesanan, Rab 07\/10 0 pesanan, Kam 08\/10 0 pesanan, Jum 09\/10 0 pesanan, Sab 10\/10 0 pesanan, Min 11\/10 1 pesanan"/', $html);
    }

    public function test_batang_tanpa_gaya_sebaris_dan_tanpa_warna_mentah(): void
    {
        $html = $this->batang();

        $this->assertStringNotContainsString('style=', $html);
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $html);
        $this->assertStringNotContainsString('gradient', $html);
    }

    public function test_batang_menerima_label_berupa_pasangan_dan_label_satu_baris(): void
    {
        $html = $this->html(
            '<x-ui.chart.bar :labels="$l" :values="$v" title="Uji" />',
            ['l' => [['Sen', '05/10'], 'Hari'], 'v' => [3, 0]],
        );

        $this->assertMatchesRegularExpression('/<text x="[\d.]+" y="320"[^>]*>Sen<\/text>/', $html);
        $this->assertMatchesRegularExpression('/<text x="[\d.]+" y="336"[^>]*>05\/10<\/text>/', $html);
        $this->assertMatchesRegularExpression('/<text x="[\d.]+" y="320"[^>]*>Hari<\/text>/', $html);
        $this->assertSame(1, preg_match_all('/<text x="[\d.]+" y="336"/', $html), 'Label satu baris tidak punya baris tanggal.');
        $this->assertStringContainsString('aria-label="Uji: Sen 05/10 3, Hari 0"', $html);
    }

    public function test_batang_tanpa_sorotan_semua_chart_sky(): void
    {
        $html = $this->html('<x-ui.chart.bar :labels="$labels" :values="$values" title="Uji" />', $this->dataBatang());

        $this->assertStringNotContainsString('fill-primary', $html);
        $this->assertSame(2, substr_count($html, 'fill-chart-sky'));
    }

    public function test_batang_kosong_hanya_menggambar_kisi(): void
    {
        $html = $this->html('<x-ui.chart.bar :labels="[]" :values="[]" title="Uji" />');

        $this->assertStringContainsString('<line', $html);
        $this->assertStringNotContainsString('<path', $html);
        $this->assertStringNotContainsString('<rect', $html);
    }

    public function test_batang_menolak_jumlah_label_dan_nilai_yang_berbeda(): void
    {
        $this->expectException(ViewException::class);

        $this->html('<x-ui.chart.bar :labels="[\'Sen\']" :values="[1, 2]" title="Uji" />');
    }

    // ---------- donat ----------

    public function test_donat_menggambar_segmen_dengan_dasharray_dan_dashoffset_pratinjau(): void
    {
        $html = $this->donat($this->segmenPratinjau());

        $this->assertStringContainsString('viewBox="0 0 160 160"', $html);
        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('transform="rotate(-90 80 80)"', $html);
        $this->assertStringContainsString('stroke-width="18"', $html);
        $this->assertStringContainsString('fill="none"', $html);
        $this->assertSame(5, substr_count($html, '<circle '));

        $diharapkan = [
            ['172.93 178.93', '0', 'stroke-success'],
            ['67.37 284.49', '-175.93', 'stroke-outline'],
            ['32.19 319.67', '-246.3', 'stroke-error'],
            ['32.19 319.67', '-281.49', 'stroke-primary'],
            ['32.19 319.67', '-316.67', 'stroke-chart-sky'],
        ];
        foreach ($diharapkan as [$array, $offset, $kelas]) {
            $this->assertMatchesRegularExpression(
                '/<circle cx="80" cy="80" r="56" class="[^"]*'.preg_quote($kelas, '/').'[^"]*" stroke-dasharray="'.preg_quote($array, '/').'" stroke-dashoffset="'.preg_quote($offset, '/').'"/',
                $html,
                $array.' '.$offset,
            );
        }
    }

    public function test_donat_menampilkan_total_di_tengah(): void
    {
        $html = $this->donat($this->segmenPratinjau());

        $this->assertMatchesRegularExpression('/<text x="80" y="82" text-anchor="middle"[^>]*text-stat-l[^>]*fill-on-surface[^>]*>10<\/text>/', $html);
        $this->assertMatchesRegularExpression('/<text x="80" y="100" text-anchor="middle"[^>]*text-body-s[^>]*fill-on-surface-variant[^>]*>pesanan<\/text>/', $html);
    }

    public function test_donat_punya_legenda_teks_dengan_nilai_dan_persentase(): void
    {
        $html = $this->donat($this->segmenPratinjau());

        $this->assertSame(5, substr_count($html, '<li '));
        foreach ([['Selesai', '5', '50%', 'bg-success'], ['Dibatalkan', '2', '20%', 'bg-outline'], ['Ditolak', '1', '10%', 'bg-error'],
            ['Terverifikasi', '1', '10%', 'bg-primary'], ['Dalam perjalanan', '1', '10%', 'bg-chart-sky']] as [$label, $nilai, $persen, $titik]) {
            $this->assertMatchesRegularExpression(
                '/<li [^>]*>\s*<span class="size-2\.5 shrink-0 rounded-full '.$titik.'"[^>]*><\/span>\s*<span class="flex-1">'.$label.'<\/span>\s*<span class="font-medium tabular-nums">'.$nilai.'<\/span>\s*<span class="w-10 text-right tabular-nums text-on-surface-variant">'.$persen.'<\/span>\s*<\/li>/',
                $html,
                $label,
            );
        }
        $this->assertStringContainsString('flex h-9 items-center gap-3', $html);
    }

    public function test_donat_punya_keterangan_teks_yang_menyebut_semua_segmen(): void
    {
        $html = $this->donat($this->segmenPratinjau());

        $this->assertStringContainsString('aria-label="Diagram cincin status 10 pesanan terbaru: 5 selesai, 2 dibatalkan, 1 ditolak, 1 terverifikasi, 1 dalam perjalanan"', $html);
    }

    public function test_donat_hanya_menampilkan_status_bernilai_lebih_dari_nol_dalam_urutan_terbesar_dulu(): void
    {
        $html = $this->donat([
            ['label' => 'Ditolak', 'value' => 0, 'color' => 'error'],
            ['label' => 'Terverifikasi', 'value' => 1, 'color' => 'primary'],
            ['label' => 'Selesai', 'value' => 3, 'color' => 'success'],
        ]);

        $this->assertSame(2, substr_count($html, '<li '));
        $this->assertSame(2, substr_count($html, '<circle '));
        $this->assertStringNotContainsString('>Ditolak<', $html);
        $this->assertLessThan(strpos($html, '>Terverifikasi<'), strpos($html, '>Selesai<'));
        $this->assertStringNotContainsString('stroke-error', $html);
    }

    public function test_donat_kosong_menggambar_cincin_netral_dengan_angka_nol(): void
    {
        $html = $this->donat([['label' => 'Selesai', 'value' => 0, 'color' => 'success']]);

        $this->assertSame(1, substr_count($html, '<circle '));
        $this->assertStringContainsString('stroke-surface-container-highest', $html);
        $this->assertMatchesRegularExpression('/<text x="80" y="82"[^>]*>0<\/text>/', $html);
        $this->assertStringNotContainsString('<li ', $html);
        $this->assertStringContainsString('tidak ada data', $html);
    }

    public function test_donat_satu_segmen_penuh_tanpa_celah(): void
    {
        $html = $this->donat([['label' => 'Selesai', 'value' => 4, 'color' => 'success']]);

        $this->assertStringContainsString('stroke-dasharray="351.86 0"', $html);
        $this->assertStringContainsString('100%', $html);
    }

    public function test_donat_tanpa_gaya_sebaris_dan_menolak_warna_di_luar_token(): void
    {
        $html = $this->donat($this->segmenPratinjau());

        $this->assertStringNotContainsString('style=', $html);
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $html);

        $this->expectException(ViewException::class);
        $this->donat([['label' => 'Selesai', 'value' => 1, 'color' => 'ungu']]);
    }
}
