@props(['pesan'])
<div {{ $attributes->merge(['class' => 'rounded-md border border-outline-variant bg-surface-container-lowest px-4 py-8 md:px-6']) }}>
    <p class="text-body-l text-on-surface-variant">{{ $pesan }}</p>
    @isset($aksi)
        <div class="mt-4">{{ $aksi }}</div>
    @endisset
</div>
