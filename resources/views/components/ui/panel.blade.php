@props(['judul' => null])
<div {{ $attributes->merge(['class' => 'rounded-md border border-outline-variant bg-surface-container-lowest p-4 md:p-6']) }}>
    @if ($judul)
        <h2 class="mb-4 text-title-m">{{ $judul }}</h2>
    @endif
    {{ $slot }}
    @isset($kaki)
        <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-outline-variant pt-4">{{ $kaki }}</div>
    @endisset
</div>
