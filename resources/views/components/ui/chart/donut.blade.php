@props(['segments', 'title', 'centerLabel' => null])
{{-- Grafik donat Chart.js (docs/DESAIN_DASBOR_MATERIAL.md 5.12). segments: daftar ['label', 'value', 'color'] dengan color = nama token (primary, success, error, warning, outline, chart-sky). Total di tengah dan legenda adalah HTML; data lewat atribut data-grafik, gambar dibuat resources/js/grafik.js. --}}
@php
    $ada = [];
    foreach (array_values($segments instanceof \Illuminate\Support\Collection ? $segments->all() : (array) $segments) as $urutan => $segmen) {
        if (! is_array($segmen) || ! isset($segmen['label'], $segmen['color']) || ! array_key_exists('value', $segmen)) {
            throw new \InvalidArgumentException('x-ui.chart.donut: segmen harus punya label, value, dan color.');
        }
        $banyak = (int) $segmen['value'];
        if ($banyak < 0) {
            throw new \InvalidArgumentException('x-ui.chart.donut: nilai segmen tidak boleh negatif.');
        }
        if ($banyak > 0) {
            $ada[] = ['urutan' => $urutan, 'label' => (string) $segmen['label'], 'value' => $banyak, 'color' => (string) $segmen['color'], 'titik' => \App\Support\Chart\Warna::latar((string) $segmen['color'])];
        }
    }

    // Nilai terbesar dulu; seri sama mengikuti urutan masukan (pemanggil memberi urutan Enum). Hanya yang bernilai lebih dari 0.
    usort($ada, fn (array $a, array $b) => [$b['value'], $a['urutan']] <=> [$a['value'], $b['urutan']]);
    $total = array_sum(array_column($ada, 'value'));
    $ada = array_map(fn (array $s) => $s + ['percent' => (int) round($s['value'] / $total * 100)], $ada);

    $kecil = fn (string $teks) => mb_strtolower(mb_substr($teks, 0, 1)).mb_substr($teks, 1);
    $keterangan = $title.': '.($ada === [] ? 'tidak ada data' : implode(', ', array_map(fn (array $s) => $s['value'].' '.$kecil($s['label']), $ada)));

    $data = [
        'tipe' => 'donat',
        'segments' => array_map(fn (array $s) => array_intersect_key($s, array_flip(['label', 'value', 'color', 'percent'])), $ada),
        'unit' => $centerLabel,
    ];
@endphp
<div {{ $attributes->merge(['class' => 'flex flex-col gap-3']) }}>
    <div class="relative mx-auto my-1 size-44 shrink-0">
        <canvas role="img" aria-label="{{ $keterangan }}" data-grafik="{{ json_encode($data, JSON_UNESCAPED_UNICODE) }}" class="size-full"></canvas>
        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center" aria-hidden="true">
            <span class="text-stat-l tabular-nums text-on-surface">{{ $total }}</span>
            @if ($centerLabel)
                <span class="text-body-s text-on-surface-variant">{{ $centerLabel }}</span>
            @endif
        </div>
    </div>
    @if ($ada !== [])
        <ul class="flex flex-col gap-0.5 text-body-m" role="list">
            @foreach ($ada as $s)
                <li class="flex h-9 items-center gap-3">
                    <span class="size-2.5 shrink-0 rounded-full {{ $s['titik'] }}" aria-hidden="true"></span>
                    <span class="flex-1">{{ $s['label'] }}</span>
                    <span class="font-medium tabular-nums">{{ $s['value'] }}</span>
                    <span class="w-10 text-right tabular-nums text-on-surface-variant">{{ $s['percent'] }}%</span>
                </li>
            @endforeach
        </ul>
        <table class="sr-only">
            <caption>{{ $title }}</caption>
            <thead>
                <tr>
                    <th scope="col">Status</th>
                    <th scope="col">{{ \Illuminate\Support\Str::ucfirst($centerLabel ?: 'Jumlah') }}</th>
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
    @endif
</div>
