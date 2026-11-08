{{-- Bungkus x-ui.opsi-baris dalam kisi 3 kolom, jarak 10px (DESIGN.md 6.4, x-ui.choice-group). --}}
<div {{ $attributes->merge(['class' => 'grid grid-cols-3 gap-2.5']) }}>
    {{ $slot }}
</div>
