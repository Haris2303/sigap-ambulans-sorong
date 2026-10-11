@props(['segments', 'title', 'centerLabel' => null])
{{-- Grafik donat SVG sisi server (DESIGN.md Components, Grafik). segments: daftar ['label', 'value', 'color'] dengan color = nama token (primary, success, error, warning, outline, chart-sky). Geometri di App\Support\Chart\DonutGeometry. --}}
@php
    $g = \App\Support\Chart\DonutGeometry::buat($segments instanceof \Illuminate\Support\Collection ? $segments->all() : (array) $segments);
    $kecil = fn (string $teks) => mb_strtolower(mb_substr($teks, 0, 1)).mb_substr($teks, 1);
    $keterangan = $title.': '.($g['kosong'] ? 'tidak ada data' : implode(', ', array_map(fn (array $s) => $s['value'].' '.$kecil($s['label']), $g['segments'])));
@endphp
<div {{ $attributes->merge(['class' => 'flex flex-col gap-3']) }}>
    <div class="flex justify-center py-1">
        <svg viewBox="{{ $g['viewBox'] }}" role="img" aria-label="{{ $keterangan }}" class="size-44 shrink-0">
            <g transform="rotate(-90 {{ $g['cx'] }} {{ $g['cy'] }})" fill="none" stroke-width="{{ $g['strokeWidth'] }}">
                @if ($g['kosong'])
                    <circle cx="{{ $g['cx'] }}" cy="{{ $g['cy'] }}" r="{{ $g['r'] }}" class="stroke-surface-container-highest" />
                @else
                    @foreach ($g['segments'] as $s)
                        <circle cx="{{ $g['cx'] }}" cy="{{ $g['cy'] }}" r="{{ $g['r'] }}" class="{{ \App\Support\Chart\Warna::stroke($s['color']) }}" stroke-dasharray="{{ $s['dasharray'] }}" stroke-dashoffset="{{ $s['dashoffset'] }}" />
                    @endforeach
                @endif
            </g>
            <text x="80" y="82" text-anchor="middle" class="text-stat-l fill-on-surface tabular-nums">{{ $g['total'] }}</text>
            @if ($centerLabel)
                <text x="80" y="100" text-anchor="middle" class="text-body-s fill-on-surface-variant">{{ $centerLabel }}</text>
            @endif
        </svg>
    </div>
    @unless ($g['kosong'])
        <ul class="flex flex-col gap-0.5 text-body-m" role="list">
            @foreach ($g['segments'] as $s)
                <li class="flex h-9 items-center gap-3">
                    <span class="size-2.5 shrink-0 rounded-full {{ \App\Support\Chart\Warna::latar($s['color']) }}" aria-hidden="true"></span>
                    <span class="flex-1">{{ $s['label'] }}</span>
                    <span class="font-medium tabular-nums">{{ $s['value'] }}</span>
                    <span class="w-10 text-right tabular-nums text-on-surface-variant">{{ $s['percent'] }}%</span>
                </li>
            @endforeach
        </ul>
    @endunless
</div>
