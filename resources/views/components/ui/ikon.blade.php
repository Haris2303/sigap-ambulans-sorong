@props(['nama', 'ukuran' => 24])
@php
    // Ukuran baku (docs/RETEMA_B.md 6.13): 16 sampai 18 dalam tombol, 20 tombol keluar, 21 menu/chip/tombol ikon, 22 chip md, 24 tombol ikon dan banner, 26 chip lg.
    if (! in_array((int) $ukuran, [16, 17, 18, 20, 21, 22, 24, 26], true)) {
        throw new \InvalidArgumentException('Ukuran ikon harus 16, 17, 18, 20, 21, 22, 24, atau 26 (docs/KIT_UI.md bagian 4).');
    }
@endphp
{!! \App\Support\Ikon::render($nama, (int) $ukuran, (string) $attributes->get('class', '')) !!}
