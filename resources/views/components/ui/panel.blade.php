@props(['judul' => null, 'flush' => false])
{{-- Kartu: latar putih, sudut 16px, bayangan card, tanpa batas. flush: tanpa padding (tabel di dalam kartu). --}}
<div {{ $attributes->merge(['class' => \App\Support\Tampilan::KARTU.' '.($flush ? 'overflow-hidden' : 'p-6')]) }}>
    @if ($judul)
        <h2 class="mb-4 text-title-m {{ $flush ? 'px-6 pt-6' : '' }}">{{ $judul }}</h2>
    @endif
    {{ $slot }}
    @isset($kaki)
        <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-outline-variant pt-4 {{ $flush ? 'px-6 pb-6' : '' }}">{{ $kaki }}</div>
    @endisset
</div>
