@props(['label' => 'Tab'])
{{-- Tab primer M3: tautan ke URL yang sama dengan ?tab=..., tanpa JavaScript. Bergulir horizontal di ponsel. --}}
<nav aria-label="{{ $label }}" {{ $attributes->merge(['class' => 'gulir-tipis flex overflow-x-auto border-b border-surface-container-high']) }}>
    {{ $slot }}
</nav>
