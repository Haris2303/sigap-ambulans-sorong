@props(['lat' => 'lat_jemput', 'lng' => 'lng_jemput', 'alamat' => 'lokasi_jemput', 'nilaiLat' => null, 'nilaiLng' => null, 'tinggi' => 'h-64'])
@php
    $peta = config('sigap.peta');
@endphp
{{-- Isian alamat teks (id f-{alamat}) dirakit di halaman lewat x-ui.isian; peta mengisinya otomatis. --}}
<div {{ $attributes->merge(['class' => 'space-y-3']) }} data-peta="pemilih"
    data-input-lat="f-{{ $lat }}" data-input-lng="f-{{ $lng }}" data-input-alamat="f-{{ $alamat }}"
    data-nominatim="{{ $peta['nominatim_url'] }}">
    <input type="hidden" id="f-{{ $lat }}" name="{{ $lat }}" value="{{ old($lat, $nilaiLat) }}">
    <input type="hidden" id="f-{{ $lng }}" name="{{ $lng }}" value="{{ old($lng, $nilaiLng) }}">
    <x-ui.tombol nada="sekunder" ukuran="lg" ikon="crosshair" blok data-peta-lokasi>Gunakan lokasi saya</x-ui.tombol>
    <div class="flex items-center gap-2">
        <input type="search" data-peta-kata placeholder="Cari alamat atau nama tempat" aria-label="Cari alamat"
            class="{{ \App\Support\Tampilan::kelasIsian(false) }}">
        <x-ui.tombol nada="sekunder" ikon="search" aria-label="Cari" data-peta-cari />
    </div>
    <p class="text-body-m text-on-surface-variant" role="status" data-peta-status></p>
    <div class="divide-y divide-outline-variant rounded-sm bg-surface-container shadow-level-2 empty:hidden" data-peta-hasil></div>
    <div data-peta-kanvas class="isolate {{ $tinggi }} rounded-md border border-outline-variant bg-surface-container-highest"
        data-ubin="{{ $peta['ubin_url'] }}" data-pusat-lat="{{ $peta['pusat_lat'] }}" data-pusat-lng="{{ $peta['pusat_lng'] }}" data-zoom="{{ $peta['zoom'] }}"></div>
    <p class="text-body-s text-on-surface-variant">Ketuk peta atau seret penanda untuk menggeser titik jemput.</p>
</div>
