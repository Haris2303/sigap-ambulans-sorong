@props(['judul' => null, 'flush' => false, 'as' => 'div'])
{{-- Kartu arah B (DESIGN.md 6.1): latar putih, sudut 24px, bayangan card, tanpa batas, padding 20px (24px di lg). flush: tanpa
     padding (tabel di dalam kartu). Kepala opsional lewat slot header (judul title-l, subjudul body-s, aksi di kanan) atau,
     untuk kasus sederhana, prop judul (judul saja, title-l). Kartu berbayang tidak boleh di dalam kartu berbayang. --}}
@php $Tag = $as; @endphp
<{{ $Tag }} {{ $attributes->merge(['class' => \App\Support\Tampilan::KARTU.' '.($flush ? 'overflow-hidden' : 'p-5 lg:p-6')]) }}>
    @isset($header)
        <div class="mb-3 flex items-start justify-between gap-3 {{ $flush ? 'px-5 pt-5 lg:px-6 lg:pt-6' : '' }}">{{ $header }}</div>
    @elseif ($judul)
        <h2 class="mb-3 text-title-l {{ $flush ? 'px-5 pt-5 lg:px-6 lg:pt-6' : '' }}">{{ $judul }}</h2>
    @endif
    {{ $slot }}
    @isset($kaki)
        <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-outline-variant pt-4 {{ $flush ? 'px-5 pb-5 lg:px-6 lg:pb-6' : '' }}">{{ $kaki }}</div>
    @endisset
</{{ $Tag }}>
