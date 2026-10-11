@props(['judul' => null])
<div {{ $attributes->merge(['class' => 'rounded-lg border border-line bg-surface p-4 md:p-6']) }}>
    @if ($judul)
        <h2 class="mb-4 text-h3">{{ $judul }}</h2>
    @endif
    {{ $slot }}
    @isset($kaki)
        <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-line pt-4">{{ $kaki }}</div>
    @endisset
</div>
