@props(['href', 'aktif' => false, 'variant' => 'track'])
{{-- Satu tab, variant harus sama dengan x-ui.tabs pembungkusnya (DESIGN.md 6.5). track: 36px rounded-sm label-m, aktif
     putih dengan shadow-chip. floating: 38px (40px di ponsel) rounded-md label-l, aktif primary. --}}
@php
    $dasar = 'lapisan-keadaan target-sentuh flex shrink-0 items-center justify-center whitespace-nowrap transition-colors';
    $kelas = $variant === 'floating'
        ? $dasar.' min-h-10 lg:min-h-9.5 rounded-md px-4 text-label-l '.($aktif ? 'bg-primary text-on-primary' : 'text-on-surface-variant')
        : $dasar.' min-h-9 rounded-sm px-4 text-label-m '.($aktif ? 'bg-surface-container-lowest text-on-surface shadow-chip' : 'text-on-surface-variant');
@endphp
<a href="{{ $href }}" @if ($aktif) aria-current="page" @endif {{ $attributes->merge(['class' => $kelas]) }}>{{ $slot }}</a>
