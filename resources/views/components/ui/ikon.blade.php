@props(['nama', 'ukuran' => 24])
@php
    // Ukuran baku Material 3 di sini: 18 (dalam tombol) dan 24 (menu, peringatan, tombol ikon).
    if (! in_array((int) $ukuran, [18, 24], true)) {
        throw new \InvalidArgumentException('Ukuran ikon harus 18 atau 24 (docs/KIT_UI.md bagian 4).');
    }
@endphp
{!! \App\Support\Ikon::render($nama, (int) $ukuran, (string) $attributes->get('class', '')) !!}
