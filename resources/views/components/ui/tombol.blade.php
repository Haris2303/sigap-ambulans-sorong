@props([
    'nada' => 'utama',
    'ukuran' => 'md',
    'href' => null,
    'tipe' => 'button',
    'ikon' => null,
    'konfirmasi' => null,
    'blok' => false,
    'ikonUkuran' => null,
    'memuat' => null,
])
{{-- nada arah B (DESIGN.md 6.2): utama (primary), tonal/sekunder (secondary: bg-surface-container, batas outline-variant),
     hantu (ghost), bahaya (danger, tonal error-container), on-primary (di atas kartu featured primary, R4), on-secondary (di
     atas kartu navy, R4/R5). Tinggi 44px di lg ke atas, 48px di bawahnya (DESIGN.md bagian 3); ukuran="sm" lebih kecil
     (36/40px); ukuran="lg" lama kini setara bawaan. Padding horizontal 20px (12px untuk hantu/terbalik). --}}
@php
    $hanyaIkon = $ikon && trim((string) $slot) === '';
    $kelas = 'lapisan-keadaan target-sentuh inline-flex items-center justify-center gap-2 rounded-md text-label-l transition-colors';
    $kelas .= ' '.match ($nada) {
        'tonal', 'sekunder' => 'bg-surface-container text-on-surface border border-outline-variant disabled:bg-on-surface/12 disabled:text-on-surface/38',
        'bahaya' => 'bg-error-container text-on-error-container disabled:bg-on-surface/12 disabled:text-on-surface/38',
        'hantu' => 'text-primary disabled:text-on-surface/38',
        'on-primary' => 'bg-on-primary text-primary disabled:bg-on-surface/12 disabled:text-on-surface/38',
        'on-secondary' => 'bg-surface-container-lowest text-secondary disabled:bg-on-surface/12 disabled:text-on-surface/38',
        'terbalik' => 'text-inverse-primary disabled:text-inverse-on-surface/38',
        default => 'bg-primary text-on-primary disabled:bg-on-surface/12 disabled:text-on-surface/38',
    };
    $kelas .= $ukuran === 'sm' ? ' min-h-10 lg:min-h-9' : ' min-h-12 lg:min-h-11'; // "lg" lama kini setara bawaan.
    $teks = in_array($nada, ['hantu', 'terbalik'], true);
    if ($hanyaIkon) {
        $kelas .= ' aspect-square px-0';
    } elseif ($ikon) {
        $kelas .= $teks ? ' px-1' : ' pl-4 pr-5';
    } else {
        $kelas .= $teks ? ' px-3' : ' px-5';
    }
    if ($blok) {
        $kelas .= ' w-full';
    }
    // ikon-ukuran menimpa ukuran bawaan (18 dalam tombol bertulisan, 24 pada tombol ikon), mis. 20 pada tombol keluar.
    $ukuranIkon = $ikonUkuran !== null ? (int) $ikonUkuran : ($hanyaIkon ? 24 : 18);
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $kelas]) }}>
        @if ($ikon)<x-ui.ikon :nama="$ikon" :ukuran="$ukuranIkon" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $tipe }}" @if ($konfirmasi) data-konfirmasi="{{ $konfirmasi }}" @endif @if ($memuat && $tipe === 'submit') data-memuat="{{ $memuat }}" @endif {{ $attributes->merge(['class' => $kelas]) }}>
        @if ($ikon)<x-ui.ikon :nama="$ikon" :ukuran="$ukuranIkon" />@endif
        {{ $slot }}
    </button>
@endif
