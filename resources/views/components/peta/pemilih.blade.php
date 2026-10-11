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
    <x-ui.tombol nada="utama" ukuran="lg" ikon="crosshair" blok data-peta-lokasi>Gunakan lokasi saya</x-ui.tombol>
    <div class="flex gap-2">
        <input type="search" data-peta-kata placeholder="Cari alamat atau nama tempat" aria-label="Cari alamat"
            class="block w-full min-h-touch rounded-md border border-control-border bg-surface px-3 text-body text-on-surface placeholder:text-on-surface-muted">
        <x-ui.tombol nada="sekunder" ikon="search" aria-label="Cari" data-peta-cari />
    </div>
    <p class="text-body-sm text-on-surface-muted" role="status" data-peta-status></p>
    <div class="divide-y divide-line rounded-md bg-surface empty:hidden" data-peta-hasil></div>
    <div data-peta-kanvas class="isolate {{ $tinggi }} rounded-lg border border-line bg-neutral-soft"
        data-ubin="{{ $peta['ubin_url'] }}" data-pusat-lat="{{ $peta['pusat_lat'] }}" data-pusat-lng="{{ $peta['pusat_lng'] }}" data-zoom="{{ $peta['zoom'] }}"></div>
    <p class="text-caption text-on-surface-muted">Ketuk peta atau seret penanda untuk menggeser titik jemput.</p>
</div>
