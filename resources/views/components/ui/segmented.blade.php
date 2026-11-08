@props(['label' => 'Pilihan'])
{{-- Segmented arah B (DESIGN.md 6.5): radio dalam formulir (mis. Biasa/Gawat), tampilan sama dengan x-ui.tabs
     variant="track". Isi dengan x-ui.segmen. Selalu dua segmen sama lebar (grid, bukan flex yang bisa bergulir):
     selebar kolom, dengan max-w-sm di md ke atas. --}}
<div role="radiogroup" aria-label="{{ $label }}" {{ $attributes->merge(['class' => 'grid w-full grid-cols-2 gap-0.5 rounded-md bg-surface-container p-1 md:max-w-sm']) }}>
    {{ $slot }}
</div>
