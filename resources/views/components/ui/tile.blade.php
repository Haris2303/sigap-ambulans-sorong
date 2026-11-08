@props(['tone', 'icon', 'value', 'label'])
{{-- Petak tonal di dalam kartu (mis. ketersediaan): tanpa bayangan. --}}
<div {{ $attributes->merge(['class' => 'rounded-md bg-surface-container-low p-4']) }}>
    <x-ui.icon-chip :tone="$tone" :icon="$icon" />
    <p class="mt-3 text-stat-m tabular-nums" data-nilai>{{ $value }}</p>
    <p class="text-body-m text-on-surface-variant">{{ $label }}</p>
</div>
