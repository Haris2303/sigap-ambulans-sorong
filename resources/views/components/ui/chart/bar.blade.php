@props(['labels', 'values', 'highlight' => null, 'title', 'unit' => null])
{{-- Grafik batang Chart.js (docs/DESAIN_DASBOR_MATERIAL.md 5.11). labels: "Sen 05/10" (dipecah di spasi pertama menjadi hari dan tanggal) atau pasangan ['Sen', '05/10']. Data lewat atribut data-grafik; gambar dibuat resources/js/grafik.js. --}}
@php
    $nilai = array_map('intval', array_values($values instanceof \Illuminate\Support\Collection ? $values->all() : (array) $values));
    $daftarLabel = array_values($labels instanceof \Illuminate\Support\Collection ? $labels->all() : (array) $labels);
    if (count($daftarLabel) !== count($nilai)) {
        throw new \InvalidArgumentException('x-ui.chart.bar: jumlah labels ('.count($daftarLabel).') harus sama dengan jumlah values ('.count($nilai).').');
    }
    if ($nilai !== [] && min($nilai) < 0) {
        throw new \InvalidArgumentException('x-ui.chart.bar: nilai tidak boleh negatif.');
    }

    $baris = array_map(function ($label) {
        $bagian = is_array($label) ? array_values($label) : preg_split('/\s+/', trim((string) $label), 2);

        return array_values(array_filter([(string) ($bagian[0] ?? ''), isset($bagian[1]) ? (string) $bagian[1] : null], fn ($b) => $b !== null));
    }, $daftarLabel);

    // Sumbu Y bilangan bulat dari 0: batas atas = bilangan genap terkecil yang tidak kurang dari max(2, nilai maksimum), jadi tiga garis kisi (0, separuh, penuh).
    $maks = max(2, $nilai === [] ? 0 : max($nilai));
    $maks += $maks % 2;

    $ringkas = array_map(fn (array $b, int $v) => implode(' ', $b).' '.$v.($unit ? ' '.$unit : ''), $baris, $nilai);
    $keterangan = $title.': '.($ringkas === [] ? 'tidak ada data' : implode(', ', $ringkas));

    $data = ['tipe' => 'batang', 'labels' => $baris, 'values' => $nilai, 'highlight' => $highlight, 'max' => $maks, 'unit' => $unit];
@endphp
<div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <div class="relative h-80 min-w-120">
        <canvas role="img" aria-label="{{ $keterangan }}" data-grafik="{{ json_encode($data, JSON_UNESCAPED_UNICODE) }}" class="size-full"></canvas>
    </div>
    <table class="sr-only">
        <caption>{{ $title }}</caption>
        <thead>
            <tr>
                <th scope="col">Hari</th>
                <th scope="col">{{ \Illuminate\Support\Str::ucfirst($unit ?: 'Nilai') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($baris as $i => $b)
                <tr>
                    <th scope="row">{{ implode(' ', $b) }}</th>
                    <td>{{ $nilai[$i] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
