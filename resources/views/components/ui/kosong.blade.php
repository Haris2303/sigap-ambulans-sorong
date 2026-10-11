@props(['pesan'])
<div {{ $attributes->merge(['class' => 'rounded-lg border border-line bg-surface px-4 py-8 md:px-6']) }}>
    <p class="text-body text-on-surface-muted">{{ $pesan }}</p>
    @isset($aksi)
        <div class="mt-4">{{ $aksi }}</div>
    @endisset
</div>
