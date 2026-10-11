@props(['label', 'nilai', 'nada' => null, 'keterangan' => null])
<div {{ $attributes->merge(['class' => 'bg-surface-container-lowest p-4']) }}>
    <p class="text-label-l text-on-surface-variant">{{ $label }}</p>
    <p class="mt-1 text-display-s tabular-nums {{ \App\Support\Tampilan::kelasTeks($nada) }}" data-nilai>{{ $nilai }}</p>
    @if ($keterangan)
        <p class="mt-1 text-body-s text-on-surface-variant">{{ $keterangan }}</p>
    @endif
</div>
