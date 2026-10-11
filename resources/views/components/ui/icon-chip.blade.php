@props(['tone', 'icon', 'size' => 'md'])
{{-- Chip ikon pembawa makna (DESIGN.md 6.6): selalu disertai teks pendamping. Tone tertiary dan neutral tersedia sejak
     R2; ukuran (40/44px) dan warna ikon primary/warning (plain, bukan on-container) menunggu R4 bersama x-ui.stat dan
     x-ui.tile yang memakai size="lg" ini. --}}
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
