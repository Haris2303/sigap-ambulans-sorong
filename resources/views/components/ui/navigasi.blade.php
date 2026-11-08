@props(['lat' => null, 'lng' => null, 'alamat'])
{{-- Tombol navigasi ke lokasi jemput (App\Support\Navigasi): membuka aplikasi peta di perangkat, dengan tautan peta web sebagai cadangan. Tanpa koordinat memakai alamat teks. --}}
@php
    $tautan = \App\Support\Navigasi::tautan($lat, $lng, (string) $alamat);
@endphp
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }}>
    <x-ui.tombol nada="sekunder" ukuran="lg" ikon="navigation" :href="$tautan['aplikasi']">Buka navigasi</x-ui.tombol>
    <x-ui.tombol nada="hantu" :href="$tautan['web']" target="_blank" rel="noopener">Lihat di peta web</x-ui.tombol>
</div>
