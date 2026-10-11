@props(['label', 'nilai', 'nada' => null, 'keterangan' => null])
<div {{ $attributes->merge(['class' => 'bg-surface p-4']) }}>
    <p class="text-caption text-on-surface-muted">{{ $label }}</p>
    <p class="mt-1 text-display tabular-nums {{ \App\Support\Tampilan::kelasTeks($nada) }}" data-nilai>{{ $nilai }}</p>
    @if ($keterangan)
        <p class="mt-1 text-caption text-on-surface-muted">{{ $keterangan }}</p>
    @endif
</div>
