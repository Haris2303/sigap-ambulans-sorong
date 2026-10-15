@props(['type', 'title', 'unit' => null, 'labels' => null, 'datasets' => null, 'tickStep' => 1, 'tooltipTitles' => null, 'segments' => null, 'centerLabel' => null])
{{-- Grafik Chart.js arah B (docs/RETEMA_B.md 7.1): garis (type="line", labels + datasets), donat (type="doughnut",
     segments), atau batang horizontal (type="bar", labels + datasets, dipakai Laporan Dinkes untuk kategori dengan
     jumlah atau panjang label yang bervariasi, mis. per Faskes). Warna lewat nama token (dibaca var(--color-...) di
     charts.js), tidak ada hex di sini. Gambar dibuat resources/js/charts.js dari atribut data-chart; tabel sr-only
     dan aria-label membawa informasi tanpa warna. --}}
@php
    $tinggi = 'h-58 lg:h-77.5'; // 232px ponsel, 310px lg (DESIGN.md 7.1).
@endphp
@if ($type === 'line')
    @php
        $daftarLabel = array_values($labels);
        $daftarDataset = array_values($datasets);
        foreach ($daftarDataset as $d) {
            if (count($d['data']) !== count($daftarLabel)) {
                throw new \InvalidArgumentException('x-ui.chart: jumlah data dataset "'.$d['label'].'" harus sama dengan jumlah labels.');
            }
        }
        $tooltipTitles = $tooltipTitles ? array_values($tooltipTitles) : $daftarLabel;
        $ringkas = implode('; ', array_map(fn (array $d) => $d['label'].': '.implode(', ', array_map(
            fn ($l, $v) => $l.' '.$v.($unit ? ' '.$unit : ''),
            $daftarLabel, $d['data'],
        )), $daftarDataset));
        $keterangan = $title.'. '.$ringkas;
        $data = [
            'tipe' => 'garis',
            'labels' => $daftarLabel,
            'tickStep' => max(1, (int) $tickStep),
            'tooltipTitles' => $tooltipTitles,
            'unit' => $unit,
            'datasets' => array_map(fn (array $d) => array_intersect_key($d, array_flip(['label', 'data', 'color', 'width', 'fill'])), $daftarDataset),
        ];
    @endphp
    <div {{ $attributes->merge(['class' => 'relative '.$tinggi]) }}>
        <canvas role="img" aria-label="{{ $keterangan }}" data-chart="{{ json_encode($data, JSON_UNESCAPED_UNICODE) }}" class="size-full"></canvas>
    </div>
    <table class="sr-only">
        <caption>{{ $title }}</caption>
        <thead>
            <tr>
                <th scope="col">{{ Str::ucfirst($unit ?: 'Label') }}</th>
                @foreach ($daftarDataset as $d)
                    <th scope="col">{{ $d['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($daftarLabel as $i => $l)
                <tr>
                    <th scope="row">{{ $tooltipTitles[$i] }}</th>
                    @foreach ($daftarDataset as $d)
                        <td>{{ $d['data'][$i] }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@elseif ($type === 'doughnut')
    @php
        $ada = [];
        foreach (array_values($segments) as $urutan => $segmen) {
            $banyak = (int) $segmen['value'];
            if ($banyak > 0) {
                $ada[] = ['urutan' => $urutan, 'label' => (string) $segmen['label'], 'value' => $banyak, 'color' => (string) $segmen['color']];
            }
        }
        usort($ada, fn (array $a, array $b) => [$b['value'], $a['urutan']] <=> [$a['value'], $b['urutan']]);
        $total = array_sum(array_column($ada, 'value'));
        $ada = array_map(fn (array $s) => $s + ['percent' => $total > 0 ? (int) round($s['value'] / $total * 100) : 0], $ada);

        $kecil = fn (string $teks) => mb_strtolower(mb_substr($teks, 0, 1)).mb_substr($teks, 1);
        $keterangan = $title.': '.($ada === [] ? 'tidak ada data' : implode(', ', array_map(fn (array $s) => $s['value'].' '.$kecil($s['label']), $ada)));
        $data = ['tipe' => 'donat', 'segments' => array_map(fn (array $s) => array_intersect_key($s, array_flip(['label', 'value', 'color'])), $ada), 'unit' => $unit];
    @endphp
    <div {{ $attributes->merge(['class' => 'flex flex-col gap-3']) }}>
        <div class="relative mx-auto my-1 size-42.5 shrink-0">
            <canvas role="img" aria-label="{{ $keterangan }}" data-chart="{{ json_encode($data, JSON_UNESCAPED_UNICODE) }}" class="size-full"></canvas>
            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center" aria-hidden="true">
                <span class="text-stat-l tabular-nums text-on-surface">{{ $total }}</span>
                @if ($centerLabel)
                    <span class="text-body-s text-on-surface-variant">{{ $centerLabel }}</span>
                @endif
            </div>
        </div>
        <ul class="flex flex-col gap-0.5 text-body-m" role="list">
            @foreach ($ada as $s)
                <li class="flex h-8.5 items-center gap-2.5">
                    <span class="size-2.5 shrink-0 rounded-full" style="background: var(--color-{{ $s['color'] }})" aria-hidden="true"></span>
                    <span class="flex-1">{{ $s['label'] }}</span>
                    <span class="font-medium tabular-nums">{{ $s['value'] }}</span>
                    <span class="w-10.5 text-right tabular-nums text-on-surface-variant">{{ $s['percent'] }}%</span>
                </li>
            @endforeach
        </ul>
        <table class="sr-only">
            <caption>{{ $title }}</caption>
            <thead>
                <tr>
                    <th scope="col">Status</th>
                    <th scope="col">{{ Str::ucfirst($unit ?: 'Jumlah') }}</th>
                    <th scope="col">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ada as $s)
                    <tr>
                        <th scope="row">{{ $s['label'] }}</th>
                        <td>{{ $s['value'] }}</td>
                        <td>{{ $s['percent'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif ($type === 'bar')
    @php
        $daftarLabel = array_values($labels);
        $daftarDataset = array_values($datasets);
        foreach ($daftarDataset as $d) {
            if (count($d['data']) !== count($daftarLabel)) {
                throw new \InvalidArgumentException('x-ui.chart: jumlah data dataset "'.$d['label'].'" harus sama dengan jumlah labels.');
            }
        }
        $satuan = $unit ? ' '.$unit : '';
        $teksNilai = fn (array $d, int $i) => $d['valueLabels'][$i] ?? ($d['data'][$i].$satuan);
        $ringkasBagian = [];
        foreach ($daftarDataset as $d) {
            $potongan = [];
            foreach ($daftarLabel as $i => $l) {
                $potongan[] = $l.' '.$teksNilai($d, $i);
            }
            $ringkasBagian[] = $d['label'].': '.implode(', ', $potongan);
        }
        $keterangan = $title.'. '.implode('; ', $ringkasBagian);
        $tinggiPx = max(160, 40 * count($daftarLabel) + 48);
        $data = [
            'tipe' => 'batang',
            'labels' => $daftarLabel,
            'unit' => $unit,
            'datasets' => array_map(fn (array $d) => array_intersect_key($d, array_flip(['label', 'data', 'color', 'valueLabels'])), $daftarDataset),
        ];
    @endphp
    <div {{ $attributes->merge(['class' => 'relative']) }} style="height: {{ $tinggiPx }}px">
        <canvas role="img" aria-label="{{ $keterangan }}" data-chart="{{ json_encode($data, JSON_UNESCAPED_UNICODE) }}" class="size-full"></canvas>
    </div>
    <table class="sr-only">
        <caption>{{ $title }}</caption>
        <thead>
            <tr>
                <th scope="col">Label</th>
                @foreach ($daftarDataset as $d)
                    <th scope="col">{{ $d['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($daftarLabel as $i => $l)
                <tr>
                    <th scope="row">{{ $l }}</th>
                    @foreach ($daftarDataset as $d)
                        <td>{{ $teksNilai($d, $i) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    @php
        throw new \InvalidArgumentException('x-ui.chart: type harus "line", "doughnut", atau "bar", bukan "'.$type.'".');
    @endphp
@endif
