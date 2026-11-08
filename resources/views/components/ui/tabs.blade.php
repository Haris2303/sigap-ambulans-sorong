@props(['label' => 'Tab', 'variant' => 'track'])
{{-- Tab arah B (DESIGN.md 6.5). variant="track" (bawaan): trek surface-container, tautan ke URL yang sama dengan
     ?tab=..., tanpa JavaScript. variant="floating": trek putih melayang, dipakai untuk filter periode (?periode=...).
     Setiap <x-ui.tab> di dalamnya memakai variant yang sama. Bergulir horizontal di ponsel. --}}
{{-- overflow-y-hidden sengaja ditambahkan di bawah: overflow-x-auto sendirian membuat overflow-y terhitung "auto" oleh
     peramban (CSS Overflow spec), yang memunculkan bilah gulir vertikal tipis di kanan trek pada lebar desktop walau
     isinya tidak melebihi tinggi. Bergulir tetap hanya horizontal, dan hanya perlu di ponsel. --}}
@php
    $kelas = $variant === 'floating'
        ? 'gulir-tipis flex items-center gap-0.5 overflow-x-auto overflow-y-hidden rounded-lg bg-surface-container-lowest p-1 shadow-control'
        : 'gulir-tipis flex items-center gap-0.5 overflow-x-auto overflow-y-hidden rounded-md bg-surface-container p-1';
@endphp
<nav aria-label="{{ $label }}" {{ $attributes->merge(['class' => $kelas]) }}>
    {{ $slot }}
</nav>
