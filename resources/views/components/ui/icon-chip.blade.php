@props(['tone', 'icon', 'size' => 'md'])
{{-- Chip ikon pembawa makna (DESIGN.md Components): selalu disertai teks pendamping. md 40px dengan ikon 22px, lg 52px dengan ikon 26px. --}}
@php
    $kelasNada = \App\Support\Tampilan::kelasChip($tone);
    [$kotak, $ukuranIkon] = match ($size) {
        'md' => ['size-10', 22],
        'lg' => ['size-13', 26],
        default => throw new \InvalidArgumentException('x-ui.icon-chip: size harus md atau lg, bukan "'.$size.'".'),
    };
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-md '.$kotak.' '.$kelasNada]) }}>
    <x-ui.ikon :nama="$icon" :ukuran="$ukuranIkon" />
</span>
