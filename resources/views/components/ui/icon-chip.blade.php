@props(['tone', 'icon', 'size' => 'md'])
{{-- Chip ikon pembawa makna (DESIGN.md 6.6): selalu disertai teks pendamping. size="md" 40px/ikon 21px (bawaan),
     size="lg" 44px/ikon 22px (dipakai x-ui.stat dan x-ui.hero-stat). --}}
@php
    $kelasNada = \App\Support\Tampilan::kelasChip($tone);
    [$kotak, $ukuranIkon] = match ($size) {
        'md' => ['size-10', 21],
        'lg' => ['size-11', 22],
        default => throw new \InvalidArgumentException('x-ui.icon-chip: size harus md atau lg, bukan "'.$size.'".'),
    };
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-md '.$kotak.' '.$kelasNada]) }}>
    <x-ui.ikon :nama="$icon" :ukuran="$ukuranIkon" />
</span>
