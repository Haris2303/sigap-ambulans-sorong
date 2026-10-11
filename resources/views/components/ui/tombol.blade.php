@props([
    'nada' => 'utama',
    'ukuran' => 'md',
    'href' => null,
    'tipe' => 'button',
    'ikon' => null,
    'konfirmasi' => null,
    'blok' => false,
])
@php
    $kelas = 'inline-flex items-center justify-center gap-2 rounded-md text-label transition-colors disabled:opacity-50';
    $kelas .= ' '.match ($nada) {
        'sekunder' => 'border border-control-border bg-surface text-on-surface hover:bg-canvas',
        'bahaya' => 'bg-danger text-on-primary hover:bg-danger-hover',
        'hantu' => 'text-primary hover:bg-primary-soft',
        default => 'bg-primary text-on-primary hover:bg-primary-hover',
    };
    $kelas .= match ($ukuran) {
        'sm' => ' min-h-9 px-3',
        'lg' => ' min-h-12 px-6',
        default => ' min-h-touch px-4',
    };
    if ($blok) {
        $kelas .= ' w-full';
    }
    $ukuranIkon = $ukuran === 'sm' ? 16 : 20;
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $kelas]) }}>
        @if ($ikon)<x-ui.ikon :nama="$ikon" :ukuran="$ukuranIkon" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $tipe }}" @if ($konfirmasi) data-konfirmasi="{{ $konfirmasi }}" @endif {{ $attributes->merge(['class' => $kelas]) }}>
        @if ($ikon)<x-ui.ikon :nama="$ikon" :ukuran="$ukuranIkon" />@endif
        {{ $slot }}
    </button>
@endif
