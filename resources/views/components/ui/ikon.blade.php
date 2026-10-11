@props(['nama', 'ukuran' => 24])
@php
    // Ukuran baku (DESIGN.md Iconography): 18 dalam tombol, 20 tombol keluar, 22 item menu dan chip md, 24 tombol ikon dan banner, 26 chip lg.
    if (! in_array((int) $ukuran, [18, 20, 22, 24, 26], true)) {
        throw new \InvalidArgumentException('Ukuran ikon harus 18, 20, 22, 24, atau 26 (docs/KIT_UI.md bagian 4).');
    }
@endphp
{!! \App\Support\Ikon::render($nama, (int) $ukuran, (string) $attributes->get('class', '')) !!}
