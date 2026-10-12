@props(['tersedia', 'total', 'label'])
{{-- Cincin ketersediaan (docs/RETEMA_B.md 6.10): petak tonal bg-surface-container-low, cincin SVG sisi-server (App\Support\Chart\Ring)
     dengan angka tersedia di tengah, label, dan "dari {total}" (menipis bila tersedia < 12% dari total). --}}
@php
    $menipis = \App\Support\Chart\Ring::menipis($tersedia, $total);
    $keliling = \App\Support\Chart\Ring::keliling();
    $panjang = \App\Support\Chart\Ring::panjangIsi($tersedia, $total);
    $warnaIsi = $menipis ? 'chart-coral' : 'primary';
    $ariaLabel = $label.': '.$tersedia.' dari '.$total.($menipis ? ', menipis' : '');
@endphp
<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-1.5 rounded-lg bg-surface-container-low px-2 py-3.5 text-center']) }}>
    <div role="img" aria-label="{{ $ariaLabel }}" class="relative size-16">
        <svg width="64" height="64" viewBox="0 0 64 64" aria-hidden="true">
            <circle cx="32" cy="32" r="26" fill="none" stroke="var(--color-surface-container-highest)" stroke-width="7" />
            <circle cx="32" cy="32" r="26" fill="none" stroke="var(--color-{{ $warnaIsi }})" stroke-width="7" stroke-linecap="round"
                stroke-dasharray="{{ sprintf('%.2f', $panjang) }} {{ sprintf('%.2f', $keliling - $panjang) }}" transform="rotate(-90 32 32)" />
        </svg>
        <div aria-hidden="true" class="absolute inset-0 flex items-center justify-center text-ring-value tabular-nums">{{ $tersedia }}</div>
    </div>
    <p class="text-label-l">{{ $label }}</p>
    <p class="{{ $menipis ? 'text-label-m text-on-error-container' : 'text-body-s text-on-surface-variant' }}">dari {{ $total }}@if ($menipis), menipis @endif</p>
</div>
