@props(['label' => 'Tab', 'variant' => 'track'])
{{-- Tab arah B (DESIGN.md 6.5). variant="track" (bawaan): trek surface-container, tautan ke URL yang sama dengan
     ?tab=..., tanpa JavaScript. variant="floating": trek putih melayang, dipakai untuk filter periode (?periode=...).
     Setiap <x-ui.tab> di dalamnya memakai variant yang sama. Bergulir horizontal di ponsel. --}}
@php
    $kelas = $variant === 'floating'
        ? 'gulir-tipis flex items-center gap-0.5 overflow-x-auto rounded-lg bg-surface-container-lowest p-1 shadow-control'
        : 'gulir-tipis flex items-center gap-0.5 overflow-x-auto rounded-md bg-surface-container p-1';
@endphp
<nav aria-label="{{ $label }}" {{ $attributes->merge(['class' => $kelas]) }}>
    {{ $slot }}
</nav>
