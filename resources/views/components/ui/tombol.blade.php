@props([
    'nada' => 'utama',
    'ukuran' => 'md',
    'href' => null,
    'tipe' => 'button',
    'ikon' => null,
    'konfirmasi' => null,
    'blok' => false,
    'ikonUkuran' => null,
])
{{-- nada: utama (filled), tonal, sekunder (outlined), hantu (text), bahaya (error), terbalik (text di atas snackbar), on-primary (hanya di atas kartu utama primary). Semua 40px dengan area sentuh 48px. --}}
@php
    $hanyaIkon = $ikon && trim((string) $slot) === '';
    $kelas = 'lapisan-keadaan target-sentuh inline-flex items-center justify-center gap-2 rounded-full text-label-l transition-colors';
    $kelas .= ' '.match ($nada) {
        'tonal' => 'bg-secondary-container text-on-secondary-container disabled:bg-on-surface/12 disabled:text-on-surface/38',
        'sekunder' => 'border border-outline text-primary disabled:border-on-surface/12 disabled:text-on-surface/38',
        'bahaya' => 'bg-error text-on-error disabled:bg-on-surface/12 disabled:text-on-surface/38',
        'hantu' => 'text-primary disabled:text-on-surface/38',
        'on-primary' => 'bg-on-primary text-primary disabled:bg-on-surface/12 disabled:text-on-surface/38',
        'terbalik' => 'text-inverse-primary disabled:text-inverse-on-surface/38',
        default => 'bg-primary text-on-primary disabled:bg-on-surface/12 disabled:text-on-surface/38',
    };
    $kelas .= $ukuran === 'lg' ? ' min-h-12' : ' min-h-10';
    $teks = in_array($nada, ['hantu', 'terbalik'], true);
    if ($hanyaIkon) {
        $kelas .= ' size-10 px-0';
    } elseif ($ikon) {
        $kelas .= $teks ? ' pl-3 pr-4' : ' pl-4 pr-6';
    } else {
        $kelas .= $teks ? ' px-3' : ' px-6';
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
    <button type="{{ $tipe }}" @if ($konfirmasi) data-konfirmasi="{{ $konfirmasi }}" @endif {{ $attributes->merge(['class' => $kelas]) }}>
        @if ($ikon)<x-ui.ikon :nama="$ikon" :ukuran="$ukuranIkon" />@endif
        {{ $slot }}
    </button>
@endif
