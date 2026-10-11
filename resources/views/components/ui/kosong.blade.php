@props(['pesan'])
<div {{ $attributes->merge(['class' => \App\Support\Tampilan::KARTU.' px-6 py-8']) }}>
    <p class="text-body-l text-on-surface-variant">{{ $pesan }}</p>
    @isset($aksi)
        <div class="mt-4">{{ $aksi }}</div>
    @endisset
</div>
