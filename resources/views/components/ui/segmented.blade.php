@props(['label' => 'Pilihan'])
{{-- Segmented arah B (DESIGN.md 6.5): radio dalam formulir (mis. Biasa/Gawat), tampilan sama dengan x-ui.tabs
     variant="track". Isi dengan x-ui.segmen. --}}
<div role="radiogroup" aria-label="{{ $label }}" {{ $attributes->merge(['class' => 'gulir-tipis flex items-center gap-0.5 overflow-x-auto rounded-md bg-surface-container p-1']) }}>
    {{ $slot }}
</div>
