<?php

namespace Tests\Feature\Arsitektur;

use Illuminate\View\ViewException;
use Tests\TestCase;

/**
 * Tes render grafik Chart.js (docs/DESAIN_DASBOR_MATERIAL.md bagian 5.11 dan 5.12): x-ui.chart.bar dan x-ui.chart.donut,
 * dengan data pratinjau (docs/referensi): batang 1,0,0,0,0,0,1 dan donat 5,2,1,1,1. Yang diuji adalah HTML dan data JSON yang
 * dikirim ke resources/js/grafik.js; gambar kanvasnya sendiri tidak teruji tanpa browser.
 */
class KomponenGrafikTest extends TestCase
{
    private function html(string $blade, array $data = []): string
    {
        return (string) $this->blade($blade, $data);
    }

    /** Data JSON pada atribut data-grafik kanvas pertama. */
    private function data(string $html): array
    {
        $this->assertSame(1, preg_match('/<canvas [^>]*data-grafik="([^"]*)"/', $html, $cocok), 'Kanvas dengan data-grafik tidak ditemukan.');

        return json_decode(html_entity_decode($cocok[1], ENT_QUOTES | ENT_HTML5), true, 512, JSON_THROW_ON_ERROR);
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

    private function donat(array $segments): string
    {
        return $this->html(
            '<x-ui.chart.donut :segments="$segments" center-label="pesanan" title="Diagram cincin status 10 pesanan terbaru" />',
            ['segments' => $segments],
        );
    }

    // ---------- batang ----------

    public function test_batang_menjadi_kanvas_dengan_data_pratinjau_dan_tanpa_svg(): void
    {
        $html = $this->batang();

        $this->assertStringContainsString('<canvas role="img"', $html);
        $this->assertStringNotContainsString('<svg', $html);

        // Wadah bergulir horizontal; kanvas selebar penuh dengan lebar minimum 480px.
        $this->assertStringContainsString('overflow-x-auto', $html);
        $this->assertStringContainsString('relative h-80 min-w-120', $html);

        $this->assertSame([
            'tipe' => 'batang',
            'labels' => [['Sen', '05/10'], ['Sel', '06/10'], ['Rab', '07/10'], ['Kam', '08/10'], ['Jum', '09/10'], ['Sab', '10/10'], ['Min', '11/10']],
            'values' => [1, 0, 0, 0, 0, 0, 1],
            'highlight' => 6,
            'max' => 2,
            'unit' => 'pesanan',
        ], $this->data($html));
    }

    public function test_data_dikirim_lewat_atribut_json_bukan_skrip_sebaris(): void
    {
        $this->assertStringNotContainsString('<script', $this->batang());
        $this->assertStringNotContainsString('<script', $this->donat($this->segmenPratinjau()));
    }

    public function test_batang_sumbu_y_bilangan_genap_dari_dua(): void
    {
        foreach ([[[0, 0], 2], [[1, 0], 2], [[2], 2], [[3], 4], [[7, 1], 8], [[10], 10], [[11], 12], [[], 2]] as [$nilai, $diharapkan]) {
            $html = $this->html(
                '<x-ui.chart.bar :labels="$l" :values="$v" title="Uji" />',
                ['l' => array_map(fn ($i) => 'H'.$i, array_keys($nilai)), 'v' => $nilai],
            );

            $this->assertSame($diharapkan, $this->data($html)['max'], 'Nilai '.json_encode($nilai));
        }
    }

    public function test_batang_punya_keterangan_teks_yang_menyebut_semua_nilai(): void
    {
        $html = $this->batang();

        $this->assertStringContainsString('aria-label="Grafik batang pesanan per hari: Sen 05/10 1 pesanan, Sel 06/10 0 pesanan, Rab 07/10 0 pesanan, Kam 08/10 0 pesanan, Jum 09/10 0 pesanan, Sab 10/10 0 pesanan, Min 11/10 1 pesanan"', $html);
    }

    public function test_batang_punya_tabel_data_untuk_pembaca_layar(): void
    {
        $html = $this->batang();

        $this->assertMatchesRegularExpression('/<table class="sr-only">\s*<caption>Grafik batang pesanan per hari<\/caption>/', $html);
        $this->assertMatchesRegularExpression('/<th scope="col">Hari<\/th>\s*<th scope="col">Pesanan<\/th>/', $html);
        $this->assertSame(7, substr_count($html, '<th scope="row">'));
        $this->assertMatchesRegularExpression('/<th scope="row">Sen 05\/10<\/th>\s*<td>1<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<th scope="row">Sel 06\/10<\/th>\s*<td>0<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<th scope="row">Min 11\/10<\/th>\s*<td>1<\/td>/', $html);
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

        $data = $this->data($html);
        $this->assertSame([['Sen', '05/10'], ['Hari']], $data['labels']);
        $this->assertSame([3, 0], $data['values']);
        $this->assertNull($data['highlight']);
        $this->assertNull($data['unit']);
        $this->assertSame(4, $data['max']);
        $this->assertStringContainsString('aria-label="Uji: Sen 05/10 3, Hari 0"', $html);
        $this->assertMatchesRegularExpression('/<th scope="col">Nilai<\/th>/', $html);
    }

    public function test_batang_kosong_tetap_valid_dan_menyebut_tidak_ada_data(): void
    {
        $html = $this->html('<x-ui.chart.bar :labels="[]" :values="[]" title="Uji" />');

        $this->assertSame(['tipe' => 'batang', 'labels' => [], 'values' => [], 'highlight' => null, 'max' => 2, 'unit' => null], $this->data($html));
        $this->assertStringContainsString('aria-label="Uji: tidak ada data"', $html);
        $this->assertStringNotContainsString('<th scope="row">', $html);
    }

    public function test_batang_menolak_jumlah_label_dan_nilai_yang_berbeda(): void
    {
        $this->expectException(ViewException::class);

        $this->html('<x-ui.chart.bar :labels="[\'Sen\']" :values="[1, 2]" title="Uji" />');
    }

    public function test_batang_menolak_nilai_negatif(): void
    {
        $this->expectException(ViewException::class);

        $this->html('<x-ui.chart.bar :labels="[\'Sen\']" :values="[-1]" title="Uji" />');
    }

    // ---------- donat ----------

    public function test_donat_menjadi_kanvas_dengan_segmen_berurutan_dan_persentase(): void
    {
        $html = $this->donat($this->segmenPratinjau());

        $this->assertStringContainsString('<canvas role="img"', $html);
        $this->assertStringNotContainsString('<svg', $html);
        $this->assertStringContainsString('relative mx-auto my-1 size-44', $html);

        $this->assertSame([
            'tipe' => 'donat',
            'segments' => [
                ['label' => 'Selesai', 'value' => 5, 'color' => 'success', 'percent' => 50],
                ['label' => 'Dibatalkan', 'value' => 2, 'color' => 'outline', 'percent' => 20],
                ['label' => 'Ditolak', 'value' => 1, 'color' => 'error', 'percent' => 10],
                ['label' => 'Terverifikasi', 'value' => 1, 'color' => 'primary', 'percent' => 10],
                ['label' => 'Dalam perjalanan', 'value' => 1, 'color' => 'chart-sky', 'percent' => 10],
            ],
            'unit' => 'pesanan',
        ], $this->data($html));
    }

    public function test_donat_menampilkan_total_di_tengah_sebagai_html(): void
    {
        $html = $this->donat($this->segmenPratinjau());

        $this->assertMatchesRegularExpression('/pointer-events-none absolute inset-0[^>]*aria-hidden="true">\s*<span class="text-stat-l tabular-nums text-on-surface">10<\/span>\s*<span class="text-body-s text-on-surface-variant">pesanan<\/span>/', $html);
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

    public function test_donat_punya_tabel_data_untuk_pembaca_layar(): void
    {
        $html = $this->donat($this->segmenPratinjau());

        $this->assertMatchesRegularExpression('/<table class="sr-only">\s*<caption>Diagram cincin status 10 pesanan terbaru<\/caption>/', $html);
        $this->assertMatchesRegularExpression('/<th scope="col">Status<\/th>\s*<th scope="col">Pesanan<\/th>\s*<th scope="col">Persentase<\/th>/', $html);
        $this->assertSame(5, substr_count($html, '<th scope="row">'));
        $this->assertMatchesRegularExpression('/<th scope="row">Selesai<\/th>\s*<td>5<\/td>\s*<td>50%<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<th scope="row">Dalam perjalanan<\/th>\s*<td>1<\/td>\s*<td>10%<\/td>/', $html);
    }

    public function test_donat_hanya_menampilkan_status_bernilai_lebih_dari_nol_dalam_urutan_terbesar_dulu(): void
    {
        $html = $this->donat([
            ['label' => 'Ditolak', 'value' => 0, 'color' => 'error'],
            ['label' => 'Terverifikasi', 'value' => 1, 'color' => 'primary'],
            ['label' => 'Selesai', 'value' => 3, 'color' => 'success'],
        ]);

        $this->assertSame(2, substr_count($html, '<li '));
        $this->assertSame(['Selesai', 'Terverifikasi'], array_column($this->data($html)['segments'], 'label'));
        $this->assertStringNotContainsString('>Ditolak<', $html);
        $this->assertLessThan(strpos($html, '>Terverifikasi<'), strpos($html, '>Selesai<'));
        $this->assertStringNotContainsString('bg-error', $html);
    }

    public function test_donat_seri_sama_mengikuti_urutan_masukan(): void
    {
        $html = $this->donat([
            ['label' => 'Ditolak', 'value' => 2, 'color' => 'error'],
            ['label' => 'Dibatalkan', 'value' => 2, 'color' => 'outline'],
            ['label' => 'Selesai', 'value' => 2, 'color' => 'success'],
        ]);

        $this->assertSame(['Ditolak', 'Dibatalkan', 'Selesai'], array_column($this->data($html)['segments'], 'label'));
    }

    public function test_donat_kosong_mengirim_segmen_kosong_dengan_angka_nol(): void
    {
        $html = $this->donat([['label' => 'Selesai', 'value' => 0, 'color' => 'success']]);

        $this->assertSame([], $this->data($html)['segments']);
        $this->assertMatchesRegularExpression('/<span class="text-stat-l tabular-nums text-on-surface">0<\/span>/', $html);
        $this->assertStringNotContainsString('<li ', $html);
        $this->assertStringNotContainsString('<table', $html);
        $this->assertStringContainsString('tidak ada data', $html);
    }

    public function test_donat_satu_segmen_penuh_seratus_persen(): void
    {
        $html = $this->donat([['label' => 'Selesai', 'value' => 4, 'color' => 'success']]);

        $this->assertSame([['label' => 'Selesai', 'value' => 4, 'color' => 'success', 'percent' => 100]], $this->data($html)['segments']);
        $this->assertStringContainsString('100%', $html);
    }

    public function test_donat_persentase_dibulatkan(): void
    {
        $html = $this->donat([
            ['label' => 'A', 'value' => 1, 'color' => 'primary'],
            ['label' => 'B', 'value' => 1, 'color' => 'success'],
            ['label' => 'C', 'value' => 1, 'color' => 'error'],
        ]);

        $this->assertSame([33, 33, 33], array_column($this->data($html)['segments'], 'percent'));
    }

    public function test_donat_tanpa_gaya_sebaris_dan_tanpa_warna_mentah(): void
    {
        $html = $this->donat($this->segmenPratinjau());

        $this->assertStringNotContainsString('style=', $html);
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $html);
    }

    public function test_donat_menolak_warna_di_luar_token(): void
    {
        $this->expectException(ViewException::class);

        $this->donat([['label' => 'Selesai', 'value' => 1, 'color' => 'ungu']]);
    }

    public function test_donat_menolak_nilai_negatif_dan_segmen_tanpa_kunci_wajib(): void
    {
        foreach ([[['label' => 'Selesai', 'value' => -1, 'color' => 'success']], [['label' => 'Selesai', 'color' => 'success']]] as $segmen) {
            try {
                $this->donat($segmen);
                $this->fail('Segmen tidak sah harus ditolak.');
            } catch (ViewException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
