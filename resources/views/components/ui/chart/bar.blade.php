@props(['labels', 'values', 'highlight' => null, 'title', 'unit' => null])
{{-- Grafik batang SVG sisi server (DESIGN.md Components, Grafik). labels: "Sen 05/10" (dipecah di spasi pertama menjadi hari dan tanggal) atau pasangan ['Sen', '05/10']. Geometri di App\Support\Chart\BarGeometry. --}}
@php
    $nilai = array_values($values instanceof \Illuminate\Support\Collection ? $values->all() : (array) $values);
    $daftarLabel = array_values($labels instanceof \Illuminate\Support\Collection ? $labels->all() : (array) $labels);
    if (count($daftarLabel) !== count($nilai)) {
        throw new \InvalidArgumentException('x-ui.chart.bar: jumlah labels ('.count($daftarLabel).') harus sama dengan jumlah values ('.count($nilai).').');
    }

    $g = \App\Support\Chart\BarGeometry::buat($nilai, $highlight);
    $baris = array_map(function ($label) {
        if (is_array($label)) {
            return [(string) ($label[0] ?? ''), isset($label[1]) ? (string) $label[1] : null];
        }
        $bagian = preg_split('/\s+/', trim((string) $label), 2);

        return [$bagian[0] ?? '', $bagian[1] ?? null];
    }, $daftarLabel);

    $ringkas = array_map(
        fn (array $b, int $v) => trim($b[0].' '.($b[1] ?? '')).' '.$v.($unit ? ' '.$unit : ''),
        $baris,
        $nilai,
    );
    $keterangan = $title.': '.($ringkas === [] ? 'tidak ada data' : implode(', ', $ringkas));
@endphp
<div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <svg viewBox="{{ $g['viewBox'] }}" role="img" aria-label="{{ $keterangan }}" class="block h-auto w-full min-w-120">
        @foreach ($g['grid'] as $k)
            <line x1="{{ $g['garisKiri'] }}" y1="{{ $k['y'] }}" x2="{{ $g['garisKanan'] }}" y2="{{ $k['y'] }}" class="{{ $k['dasar'] ? 'stroke-outline-variant' : 'stroke-surface-container-high' }}" stroke-width="1" />
            <text x="{{ $g['sumbuX'] }}" y="{{ $k['labelY'] }}" text-anchor="end" class="fill-on-surface-variant text-body-s tabular-nums">{{ $k['label'] }}</text>
        @endforeach
        @foreach ($g['bars'] as $b)
            @if ($b['path'])
                <path d="{{ $b['path'] }}" class="{{ $b['highlight'] ? 'fill-primary' : 'fill-chart-sky' }}" />
            @else
                <rect x="{{ $b['stub']['x'] }}" y="{{ $b['stub']['y'] }}" width="{{ $b['stub']['width'] }}" height="{{ $b['stub']['height'] }}" rx="{{ $b['stub']['rx'] }}" class="fill-outline-variant" />
            @endif
            <text x="{{ $b['center'] }}" y="{{ $b['valueY'] }}" text-anchor="middle" class="fill-on-surface text-label-l tabular-nums">{{ $b['value'] }}</text>
            @php($teksHari = $b['highlight'] ? 'font-medium fill-primary text-body-s' : 'fill-on-surface-variant text-body-s')
            <text x="{{ $b['center'] }}" y="{{ $g['hariY'] }}" text-anchor="middle" class="{{ $teksHari }}">{{ $baris[$b['index']][0] }}</text>
            @if ($baris[$b['index']][1] !== null)
                <text x="{{ $b['center'] }}" y="{{ $g['tanggalY'] }}" text-anchor="middle" class="{{ $teksHari }} tabular-nums">{{ $baris[$b['index']][1] }}</text>
            @endif
        @endforeach
    </svg>
</div>
